<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/RoomController.php';
require_once __DIR__ . '/../models/Registration.php';
// Buffer để controller còn có thể đặt HTTP header sau các dòng PASS.
ob_start();
$passed = 0;
function checkRoomInformation(bool $condition, string $description): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException($description);
    }
    echo '[PASS] ' . $description . PHP_EOL;
    $passed++;
}
function renderRoomInformation(PDO $db, array $filters = [], string $method = 'GET'): array
{
    $_GET = ['action' => 'rooms'] + $filters;
    $_SERVER['REQUEST_METHOD'] = $method;
    http_response_code(200);
    ob_start();
    try {
        (new RoomController($db))->show();
        return [http_response_code(), ob_get_contents()];
    } finally {
        ob_end_clean();
    }
}
try {
    // Sao chép cấu trúc vào bảng tạm của kết nối test; không sửa bảng thật.
    foreach (['toanha', 'phong', 'hopdong', 'dondangky'] as $table) {
        $sql = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $sql);
        $sql = preg_replace('/,\s*\n\)/', "\n)", $sql);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql));
    }
    $pdo->exec(file_get_contents(__DIR__ . '/../sql/seed-buildings-rooms.sql'));
    $model = new Room($pdo);
    checkRoomInformation(array_column($model->buildings('Nam'), 'MaToa') === ['T01', 'T02'], 'Khu Nam chỉ có tòa A/B theo CSDL');
    checkRoomInformation(array_column($model->buildings('Nữ'), 'MaToa') === ['T03', 'T04'], 'Khu Nữ có tòa C/D theo CSDL');
    checkRoomInformation($model->floors('Nam', 'T01') === [1, 2, 3, 4], 'Danh sách tầng theo dữ liệu thật, không hard-code tầng 5');
    $rooms = $model->rooms('Nam', 'T01', 2);
    checkRoomInformation(count($rooms) === 13 && $rooms[0]['SoPhong'] === 'A201', 'Lọc đồng thời giới tính, mã tòa và tầng');
    checkRoomInformation($model->rooms('Nam', 'T03', 1) === [], 'Không trả phòng của khu khác');
    checkRoomInformation($model->rooms('Nam', "T01' OR 1=1 --", 1) === [], 'Mã tòa chứa SQL injection không trả phòng');
    $pdo->exec("INSERT INTO hopdong (MaHopDong, MSSV, MaPhong, NgayBatDau, NgayKetThuc, TrangThai) VALUES
        ('LIVE','TESTLIVE','P0001','2020-01-01','2030-12-31','Còn hạn'),
        ('EXPIRED','TESTEXP','P0001','2020-01-01','2020-12-31','Hết hạn'),
        ('ENDED','TESTEND','P0001','2020-01-01','2030-12-31','Đã thanh lý')");
    $stmt = $pdo->prepare('INSERT INTO dondangky
        (MaDonDangKy, MSSV, HoTen, NgaySinh, CCCD, GioiTinh, QueQuan, SDT, Lop, NienKhoa, MaUuTien, MaPhong, TrangThai, MaHopDong)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ([['WAIT','Chờ duyệt',null], ['APPROVED','Đã duyệt',null], ['LINKED','Đã duyệt','LIVE'], ['REJECTED','Từ chối',null]] as [$id, $status, $contract]) {
        $stmt->execute([$id, $id, 'Sinh viên kiểm thử', '2006-01-01', '000000000001', 'Nam', 'Kiểm thử', '0900000000', 'TEST', '2024-2028', 'UT00', 'P0001', $status, $contract]);
    }
    $first = $model->rooms('Nam', 'T01', 1)[0];
    checkRoomInformation((int) $first['DangO'] === 1, 'Chỉ hợp đồng Còn hạn tính là đang ở');
    checkRoomInformation((int) $first['DangGiu'] === 2 && (int) $first['ConCho'] === 5, 'Giữ chỗ cả Chờ duyệt và Đã duyệt chưa có hợp đồng; bỏ đơn từ chối/đã liên kết');
    checkRoomInformation((int) $first['SucChua'] === 8, 'Sức chứa thiết kế không đổi khi tính khả dụng');
    $pdo->exec("UPDATE phong SET SucChua = 3 WHERE MaPhong = 'P0001'");
    checkRoomInformation((int) $model->rooms('Nam', 'T01', 1)[0]['ConCho'] === 0, 'Phòng đầy vẫn xuất hiện trong UC15');
    checkRoomInformation(!in_array('P0001', array_column((new Registration($pdo))->rooms('Nam'), 'MaPhong'), true), 'Cùng dữ liệu: UC02 loại phòng đầy');
    [$status, $html] = renderRoomInformation($pdo);
    checkRoomInformation($status === 200 && substr_count($html, '<article ') === 13 && str_contains($html, 'Tòa A · Tầng 1'), 'Mặc định hiển thị tòa/tầng đầu tiên cho khách');
    checkRoomInformation(str_contains($html, 'room-information-card full') && str_contains($html, '1/3 sinh viên đang ở') && str_contains($html, '2 chỗ đang giữ · 0 chỗ khả dụng'), 'Phòng đầy do giữ chỗ hiển thị đúng số đang ở và trạng thái');
    checkRoomInformation(!str_contains($html, 'name="MaPhong"') && !str_contains($html, 'csrf_token') && !str_contains($html, 'TESTLIVE'), 'Trang công khai không có thao tác giữ chỗ hay dữ liệu sinh viên');
    [$status, $html] = renderRoomInformation($pdo, ['GioiTinhPhong' => 'Nữ']);
    checkRoomInformation($status === 200 && str_contains($html, 'Phòng C101') && !str_contains($html, 'Phòng A101') && str_contains($html, 'Tòa D'), 'Đổi khu chọn lại tòa/tầng phù hợp, thấy đủ tòa C/D');
    [$status, $html] = renderRoomInformation($pdo, ['GioiTinhPhong' => 'Nữ', 'MaToa' => 'T04', 'Tang' => '4']);
    checkRoomInformation($status === 200 && substr_count($html, '<article ') === 12 && str_contains($html, 'Phòng D401'), 'Chọn tòa D tầng 4 hiển thị đúng 12 phòng');
    foreach ([['GioiTinhPhong' => ['Nam']], ['MaToa' => ['T01']], ['Tang' => ['1']], ['GioiTinhPhong' => 'Khác'], ['Tang' => '1 OR 1=1'], ['MaToa' => "T01' OR 1=1"], ['MaToa' => 'T03'], ['Tang' => '5'], ['Tang' => '0'], ['MaToa' => '']] as $filters) {
        [$status, $html] = renderRoomInformation($pdo, $filters);
        checkRoomInformation($status === 400 && !str_contains($html, '<article ') && str_contains($html, 'role="alert"'), 'Bộ lọc sai trả 400 và không hiển thị phòng: ' . json_encode($filters, JSON_UNESCAPED_UNICODE));
    }
    [$status, $html] = renderRoomInformation($pdo, [], 'POST');
    checkRoomInformation($status === 405 && $html === '', 'POST bị chặn, UC15 chỉ đọc qua GET');
    $pdo->exec("UPDATE phong SET SucChua = 2 WHERE MaPhong = 'P0001'");
    [$status, $html] = renderRoomInformation($pdo);
    checkRoomInformation($status === 200 && str_contains($html, '0 chỗ khả dụng') && !str_contains($html, '-1 chỗ khả dụng'), 'Dữ liệu vượt sức chứa hiển thị Đầy, không hiển thị chỗ âm');
    $pdo->exec("UPDATE toanha SET TenToa = '<script>alert(1)</script>' WHERE MaToa = 'T01'");
    $pdo->exec("UPDATE phong SET SoPhong = '<img src=x>' WHERE MaPhong = 'P0001'");
    [$status, $html] = renderRoomInformation($pdo, ['MaToa' => 'T01']);
    checkRoomInformation(str_contains($html, '&lt;script&gt;') && str_contains($html, '&lt;img src=x&gt;') && !str_contains($html, '<script>'), 'Escape tên tòa và số phòng từ CSDL để chống XSS');
    $pdo->exec("UPDATE dondangky SET TrangThai = 'Từ chối' WHERE MaDonDangKy = 'WAIT'");
    checkRoomInformation((int) $model->rooms('Nam', 'T01', 1)[0]['ConCho'] === 0, 'Đọc lại dữ liệu sau từ chối cập nhật ngay chỗ khả dụng');
    $pdo->exec("UPDATE phong SET SucChua = 8 WHERE MaPhong = 'P0001'");
    $registration = (new Registration($pdo))->rooms('Nam');
    checkRoomInformation($registration === $model->rooms('Nam', availableOnly: true), 'UC02 và UC15 dùng chung dữ liệu khả dụng');
    $pdo->exec('DELETE FROM phong');
    [$status, $html] = renderRoomInformation($pdo);
    checkRoomInformation($status === 200 && str_contains($html, 'Không có thông tin phòng phù hợp.'), 'CSDL không có phòng: trạng thái rỗng rõ ràng');
    // Giả lập lỗi truy vấn trong bảng tạm, không làm hỏng schema thật.
    $pdo->exec('ALTER TABLE phong RENAME COLUMN MaToa TO MissingBuilding');
    [$status, $html] = renderRoomInformation($pdo);
    checkRoomInformation($status === 500 && str_contains($html, 'Chưa thể tải thông tin phòng.') && !str_contains($html, 'SQLSTATE'), 'Lỗi CSDL trả 500 và thông báo an toàn, không nhầm thành danh sách rỗng');
    echo "\n$passed kiểm thử UC15 đạt.\n";
    ob_end_flush();
} catch (Throwable $error) {
    ob_end_clean();
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
