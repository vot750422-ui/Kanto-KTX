<?php
/**
 * Kịch bản kiểm thử đồng thời (Concurrency Testing) - UC02 Đăng ký lưu trú.
 * Kiểm chứng cơ chế GET_LOCK và Transaction isolation chống Race Condition.
 */
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/Room.php';

$pdo = createDatabaseConnection();

// Chế độ tiến trình con (Worker)
if (isset($argv[1]) && $argv[1] === 'worker') {
    $mssv = $argv[2];
    $cccd = $argv[3];
    $email = $argv[4];
    $roomId = $argv[5];
    $barrierFile = $argv[6];

    // Chờ tín hiệu xuất phát đồng thời từ tiến trình điều phối
    while (!file_exists($barrierFile)) {
        usleep(500); // 0.5ms
    }

    $reg = new Registration($pdo);
    $data = [
        'MSSV' => $mssv,
        'HoTen' => 'Sinh Viên ' . $mssv,
        'NgaySinh' => '2005-01-01',
        'CCCD' => $cccd,
        'GioiTinh' => 'Nam',
        'QueQuan' => 'Cần Thơ',
        'SDT' => '09' . substr(str_pad(mt_rand(10000000, 99999999), 8, '0'), 0, 8),
        'Email' => $email,
        'Lop' => 'CNTT',
        'NienKhoa' => '2024-2028',
        'MaUuTien' => 'UT01',
        'FileMinhChung' => null,
    ];

    try {
        $id = $reg->submit($data, $roomId);
        echo json_encode(['status' => 'SUCCESS', 'id' => $id, 'mssv' => $mssv], JSON_UNESCAPED_UNICODE) . "\n";
    } catch (Throwable $e) {
        echo json_encode(['status' => 'REJECTED', 'message' => $e->getMessage(), 'mssv' => $mssv], JSON_UNESCAPED_UNICODE) . "\n";
    }
    exit(0);
}

// Chế độ điều phối (Coordinator)
echo "=== KIỂM THỬ ĐỒNG THỜI ĐĂNG KÝ LƯU TRÚ (UC02) ===\n";

$stmt = $pdo->query("SELECT p.MaPhong, p.SoPhong, p.SucChua,
    COALESCE(h.DangO, 0) AS DangO, COALESCE(d.DangGiu, 0) AS DangGiu,
    p.SucChua - COALESCE(h.DangO, 0) - COALESCE(d.DangGiu, 0) AS ConCho
    FROM phong p
    LEFT JOIN (SELECT MaPhong, COUNT(*) AS DangO FROM hopdong WHERE TrangThai = 'Còn hạn' GROUP BY MaPhong) h ON h.MaPhong = p.MaPhong
    LEFT JOIN (SELECT MaPhong, COUNT(*) AS DangGiu FROM dondangky WHERE TrangThai = 'Chờ duyệt' GROUP BY MaPhong) d ON d.MaPhong = p.MaPhong
    WHERE p.GioiTinhPhong = 'Nam' AND (p.SucChua - COALESCE(h.DangO, 0) - COALESCE(d.DangGiu, 0)) >= 1
    LIMIT 1");
$room = $stmt->fetch();
if (!$room) {
    die("LỖI: Không tìm thấy phòng Nam còn chỗ để kiểm thử.\n");
}

$roomId = $room['MaPhong'];
$conCho = (int)$room['ConCho'];
echo "Chọn phòng: {$room['SoPhong']} (Mã: {$roomId}) - Sức chứa: {$room['SucChua']}, Đang ở: {$room['DangO']}, Giữ chỗ: {$room['DangGiu']}, Còn trống: {$conCho}\n";

$tempDons = [];
if ($conCho > 1) {
    echo "Tạo " . ($conCho - 1) . " đơn giữ chỗ tạm thời để đưa phòng về đúng 1 chỗ trống cuối cùng...\n";
    for ($i = 1; $i < $conCho; $i++) {
        // MaDonDangKy là VARCHAR(10) trong schema bàn giao: giữ mã giữ chỗ đúng 10 ký tự.
        $tid = 'TP' . bin2hex(random_bytes(4));
        $tmssv = 'TMP' . mt_rand(100000, 999999);
        $tcccd = '999' . mt_rand(100000000, 999999999);
        $ins = $pdo->prepare("INSERT INTO dondangky (MaDonDangKy, MSSV, HoTen, NgaySinh, CCCD, GioiTinh, QueQuan, SDT, Email, Lop, NienKhoa, MaUuTien, MaPhong, TrangThai)
            VALUES (?, ?, 'Tạm', '2005-01-01', ?, 'Nam', 'Cần Thơ', '0900000000', 'tmp@test.com', 'Test', '2024-2028', 'UT01', ?, 'Chờ duyệt')");
        $ins->execute([$tid, $tmssv, $tcccd, $roomId]);
        $tempDons[] = $tid;
    }
}

$check = (new Room($pdo))->availableRoom('Nam', $roomId);
$currentConCho = $check ? (int)$check['ConCho'] : 0;
echo "Trạng thái trước test: Phòng {$roomId} còn đúng {$currentConCho} chỗ trống duy nhất.\n";

if ($currentConCho !== 1) {
    if ($tempDons) {
        $pdo->query("DELETE FROM dondangky WHERE MaDonDangKy IN ('" . implode("','", $tempDons) . "')");
    } 
    die("LỖI: Phòng không ở trạng thái 1 chỗ trống.\n");
}
$barrierFile = sys_get_temp_dir() . '/barrier_' . uniqid() . '.tmp';
@unlink($barrierFile);

$phpExe = PHP_BINARY ?: 'F:\\wamp64\\bin\\php\\php8.2.29\\php.exe';
$script = __FILE__;

$cmd1 = "\"$phpExe\" \"$script\" worker CONC001 079205001111 conc1@example.com \"$roomId\" \"$barrierFile\"";
$cmd2 = "\"$phpExe\" \"$script\" worker CONC002 079205002222 conc2@example.com \"$roomId\" \"$barrierFile\"";

echo "Khởi chạy đồng thời 2 tiến trình PHP gửi đơn đăng ký vào cùng phòng {$roomId}...\n";

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];

try {
    $proc1 = proc_open($cmd1, $descriptors, $pipes1);
    $proc2 = proc_open($cmd2, $descriptors, $pipes2);

    usleep(50000); // 50ms cho hai tiến trình sẵn sàng ở barrier
    touch($barrierFile);

    $out1 = stream_get_contents($pipes1[1]);
    fclose($pipes1[1]);
    fclose($pipes1[0]);
    fclose($pipes1[2]);
    proc_close($proc1);

    $out2 = stream_get_contents($pipes2[1]);
    fclose($pipes2[1]);
    fclose($pipes2[0]);
    fclose($pipes2[2]);
    proc_close($proc2);

    @unlink($barrierFile);

    echo "Kết quả Tiến trình 1: " . trim($out1) . "\n";
    echo "Kết quả Tiến trình 2: " . trim($out2) . "\n";

    $res1 = json_decode(trim($out1), true);
    $res2 = json_decode(trim($out2), true);

    $successCount = ($res1['status'] === 'SUCCESS' ? 1 : 0) + ($res2['status'] === 'SUCCESS' ? 1 : 0);
    $rejectedCount = ($res1['status'] === 'REJECTED' ? 1 : 0) + ($res2['status'] === 'REJECTED' ? 1 : 0);

    echo "\n--- TỔNG KẾT KIỂM THỬ ĐỒNG THỜI ---\n";
    echo "Số yêu cầu thành công: $successCount (kỳ vọng: 1)\n";
    echo "Số yêu cầu bị từ chối: $rejectedCount (kỳ vọng: 1)\n";

    // Điều kiện đạt phải xét đúng nguyên nhân từ chối là hết chỗ, không chỉ đếm số lượng.
    $rejectedMessages = [];
    foreach ([$res1, $res2] as $res) {
        if (($res['status'] ?? '') === 'REJECTED') {
            $rejectedMessages[] = (string) ($res['message'] ?? '');
        }
    }
    $reasonIsFull = count($rejectedMessages) === 1
        && mb_stripos($rejectedMessages[0], 'hết chỗ') !== false;
    echo "Nguyên nhân từ chối: " . ($rejectedMessages[0] ?? '(không có)') . "\n";
    echo "Nguyên nhân là hết chỗ: " . ($reasonIsFull ? 'Đúng' : 'SAI') . "\n";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dondangky WHERE MaPhong = ? AND MSSV IN ('CONC001', 'CONC002') AND TrangThai = 'Chờ duyệt'");
    $stmt->execute([$roomId]);
    $dbCount = (int)$stmt->fetchColumn();
    echo "Số đơn lưu trong CSDL: $dbCount (kỳ vọng: 1, không có 2 đơn trùng chỗ)\n";

    $passed = ($successCount === 1 && $rejectedCount === 1 && $reasonIsFull && $dbCount === 1);
    if ($passed) {
        echo "[PASS] Kịch bản UC02 với 2 tiến trình đồng thời: 1 đơn được chấp thuận, 1 đơn bị từ chối vì hết chỗ; CSDL chỉ ghi nhận 1 đơn cho phòng này.\n";
        echo "Kết quả hỗ trợ cơ chế kiểm soát sức chứa trong kịch bản này; không thay thế kiểm thử tải hoặc các luồng tranh chấp khác.\n";
    } else {
        echo "[FAIL] Kịch bản kiểm thử đồng thời không đạt kỳ vọng.\n";
    }
} finally {
    $pdo->exec("DELETE FROM dondangky WHERE MaDonDangKy LIKE 'TP%' OR MSSV IN ('CONC001', 'CONC002')");
    echo "Đã dọn dẹp dữ liệu kiểm thử an toàn.\n";
}
