<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
@session_start();
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Renewal.php';
require_once __DIR__ . '/../controllers/RenewalController.php';

$passed = 0;
$failed = 0;
function checkRenewal(bool $ok, string $description): void
{
    global $passed, $failed;
    $ok ? $passed++ : $failed++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $description . PHP_EOL;
}

try {
    // 1. Tạo các bảng TEMPORARY cô lập dữ liệu kiểm thử
    foreach (['toanha', 'phong', 'taikhoan', 'sinhvien', 'hopdong', 'hocky', 'hoadon', 'hoadontienphong', 'thongbao', 'nhatkyhethong'] as $table) {
        $sql = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $sql);
        $sql = preg_replace('/,\s*\n\)/', "\n)", $sql);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql));
    }

    // Seed dữ liệu nền
    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong (MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua) VALUES ('P1','T1','A101',1,'Nam',8),('P2','T1','A102',1,'Nam',8),('P3','T1','A103',1,'Nam',8)");
    
    // Tạo tài khoản & sinh viên
    $pdo->exec("INSERT INTO taikhoan (MaTK, TenDangNhap, MatKhau, VaiTro, TrangThai) VALUES
        (101, 'SV001', 'hash1', 'Sinh viên', 'Hoạt động'),
        (102, 'SV002', 'hash2', 'Sinh viên', 'Hoạt động'),
        (103, 'SV003', 'hash3', 'Sinh viên', 'Hoạt động'),
        (104, 'SV004', 'hash4', 'Sinh viên', 'Hoạt động')");

    $pdo->exec("INSERT INTO sinhvien (MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru) VALUES
        ('SV001',101,'UT00','Nguyễn Văn An','2005-01-01','CCCD001','Nam','Cần Thơ','0900000001','an@test.com','KTPM01','2024-2028','Đang ở'),
        ('SV002',102,'UT00','Trần Văn Bình','2005-02-02','CCCD002','Nam','Cần Thơ','0900000002','binh@test.com','KTPM02','2024-2028','Đang ở'),
        ('SV003',103,'UT00','Lê Văn Cường','2005-03-03','CCCD003','Nam','Cần Thơ','0900000003','cuong@test.com','KTPM03','2024-2028','Chờ thanh toán'),
        ('SV004',104,'UT00','Phạm Văn Dũng','2005-04-04','CCCD004','Nam','Cần Thơ','0900000004','dung@test.com','KTPM04','2024-2028','Đã rời đi')");

    // Học kỳ 1 cũ & Học kỳ 2 hiện tại
    $pdo->exec("INSERT INTO hocky (MaHocKy, TenHocKy, NamHoc, NgayBatDau, NgayKetThuc) VALUES
        ('HK1','Học kỳ 1','2026-2027','2026-08-01','2026-12-31'),
        ('HK2','Học kỳ 2','2026-2027','2027-01-01','2027-05-31')");

    // Hợp đồng cũ HK1
    $pdo->exec("INSERT INTO hopdong (MaHopDong, MSSV, MaPhong, NgayBatDau, NgayKetThuc, TrangThai, MaHocKy) VALUES
        ('HD001', 'SV001', 'P1', '2026-08-01', '2026-12-31', 'Còn hạn', 'HK1'),
        ('HD002', 'SV002', 'P2', '2026-08-01', '2026-12-31', 'Còn hạn', 'HK1'),
        ('HD004', 'SV004', 'P3', '2026-08-01', '2026-12-31', 'Đã thanh lý', 'HK1')");

    // Hóa đơn HK2: SV001 đã thu, SV002 chưa thu
    $pdo->exec("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai) VALUES
        ('INV_SV1', 'Tiền phòng', 1500000, 'Đã thu'),
        ('INV_SV2', 'Tiền phòng', 1500000, 'Chưa thu')");
    $pdo->exec("INSERT INTO hoadontienphong (MaHoaDon, MSSV, MaPhong, MaHocKy, MaDonGia) VALUES
        ('INV_SV1', 'SV001', 'P1', 'HK2', 'RATE'),
        ('INV_SV2', 'SV002', 'P2', 'HK2', 'RATE')");

    $renewal = new Renewal($pdo);

    // ==========================================
    // Case 1: SV001 đã thanh toán -> gia hạn thành công (hợp đồng mới Còn hạn, cũ Hết hạn, có thông báo)
    // Case 2: SV002 chưa thanh toán -> bị khóa tài khoản, hợp đồng cũ Hết hạn
    // ==========================================
    $res = $renewal->run('HK2');
    checkRenewal($res['renewed'] === 1 && $res['locked'] === 1, 'UC18 Case 1 & 2: Sinh viên đã nộp tiền được gia hạn, chưa nộp bị khóa tài khoản');

    // Kiểm tra hợp đồng mới của SV001
    $newContract = $pdo->query("SELECT * FROM hopdong WHERE MSSV='SV001' AND TrangThai='Còn hạn'")->fetch();
    checkRenewal($newContract !== false && $newContract['MaHocKy'] === 'HK2', 'Hợp đồng mới được tạo với trạng thái Còn hạn và liên kết học kỳ mới');

    // Kiểm tra hợp đồng cũ của SV001 đã hết hạn
    $oldContract = $pdo->query("SELECT TrangThai FROM hopdong WHERE MaHopDong='HD001'")->fetchColumn();
    checkRenewal($oldContract === 'Hết hạn', 'Hợp đồng cũ của sinh viên chuyển sang Hết hạn');

    // Kiểm tra thông báo gia hạn thành công
    $noti = $pdo->query("SELECT COUNT(*) FROM thongbao WHERE MSSV='SV001'")->fetchColumn();
    checkRenewal((int) $noti > 0, 'Hệ thống gửi thông báo gia hạn thành công cho sinh viên');

    // Kiểm tra tài khoản SV002 bị khóa
    $statusSV2 = $pdo->query("SELECT TrangThai FROM taikhoan WHERE MaTK=102")->fetchColumn();
    checkRenewal($statusSV2 === 'Khóa', 'Tài khoản sinh viên chưa nộp tiền chuyển thành Khóa');

    // ==========================================
    // Case 3: Sinh viên mới (SV003: Chờ thanh toán, chưa có hợp đồng) không bị khóa
    // ==========================================
    $statusSV3 = $pdo->query("SELECT TrangThai FROM taikhoan WHERE MaTK=103")->fetchColumn();
    checkRenewal($statusSV3 === 'Hoạt động', 'UC18 Case 3: Sinh viên mới (chưa từng có hợp đồng) không bị khóa khi tiến trình chạy');

    // ==========================================
    // Case 4: Chạy 2 lần liên tiếp -> lần 2 skipped, không sinh hợp đồng trùng
    // ==========================================
    $res2 = $renewal->run('HK2');
    checkRenewal($res2['renewed'] === 0 && $res2['skipped'] >= 1, 'UC18 Case 4: Chạy lần 2 không tạo hợp đồng trùng (bỏ qua hợp đồng đã gia hạn)');

    // ==========================================
    // Case 5: Hợp đồng đã thanh lý (HD004) không bị đổi trạng thái
    // ==========================================
    $statusHD4 = $pdo->query("SELECT TrangThai FROM hopdong WHERE MaHopDong='HD004'")->fetchColumn();
    checkRenewal($statusHD4 === 'Đã thanh lý', 'UC18 Case 5: Hợp đồng đã thanh lý được giữ nguyên trạng thái');

    // ==========================================
    // Case 6: Học kỳ chồng lấn thời gian -> từ chối gia hạn và ghi nhận lỗi
    // ==========================================
    $pdo->exec("INSERT INTO hocky (MaHocKy, TenHocKy, NamHoc, NgayBatDau, NgayKetThuc) VALUES
        ('HK_BAD', 'Học kỳ lỗi', '2026-2027', '2027-02-01', '2027-04-30')");
    $resOverlap = $renewal->run('HK2');
    checkRenewal($resOverlap['errors'] > 0, 'UC18 Case 6: Học kỳ chồng lấn thời gian được phát hiện và từ chối gia hạn');
    $pdo->exec("DELETE FROM hocky WHERE MaHocKy='HK_BAD'");

    // ==========================================
    // Case 7: Kiểm tra tính nguyên tử (Rollback khi gặp lỗi)
    // ==========================================
    $pdo->exec("INSERT INTO hopdong (MaHopDong, MSSV, MaPhong, NgayBatDau, NgayKetThuc, TrangThai, MaHocKy) VALUES
        ('HD_FAIL', 'SV001', 'P1', '2027-01-01', '2027-05-31', 'Còn hạn', 'HK2')");
    $pdo->exec("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai) VALUES ('INV_FAIL', 'Tiền phòng', 1500000, 'Đã thu')");
    $pdo->exec("INSERT INTO hoadontienphong (MaHoaDon, MSSV, MaPhong, MaHocKy, MaDonGia) VALUES ('INV_FAIL', 'SV001', 'P1', 'HK3', 'RATE')");
    $pdo->exec("INSERT INTO hocky (MaHocKy, TenHocKy, NamHoc, NgayBatDau, NgayKetThuc) VALUES ('HK3', 'Học kỳ 3', '2026-2027', '2027-06-01', '2027-08-31')");
    $pdo->exec("ALTER TABLE hopdong ADD CONSTRAINT test_renewal_fail CHECK (MaHocKy <> 'HK3')");

    $rollbackOk = false;
    try {
        $renewal->run('HK3');
    } catch (Throwable) {
        $rollbackOk = !$pdo->inTransaction();
    }
    checkRenewal($rollbackOk, 'UC18 Case 7: Gặp lỗi giữa chừng thì rollback toàn bộ transaction');

    // ==========================================
    // Case 8: Kiểm tra Controller route action=renewal-run
    // Chặn GET, chặn token sai, đúng token trả JSON 200
    // ==========================================
    $controller = new RenewalController($pdo);

    // Thử GET
    $_SERVER['REQUEST_METHOD'] = 'GET';
    http_response_code(200);
    ob_start();
    $controller->handle();
    ob_end_clean();
    checkRenewal(http_response_code() === 405, 'UC18 Case 8a: Endpoint renewal-run chặn phương thức GET (HTTP 405)');

    // Thử POST sai token
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_RENEWAL_TOKEN'] = 'wrong_token';
    http_response_code(200);
    ob_start();
    $controller->handle();
    ob_end_clean();
    checkRenewal(http_response_code() === 403, 'UC18 Case 8b: Endpoint renewal-run chặn sai token bí mật (HTTP 403)');

    // Thử POST đúng token
    $_SERVER['HTTP_X_RENEWAL_TOKEN'] = getenv('RENEWAL_TOKEN') ?: 'kanto_renewal_secret_2026';
    $_POST['semester_id'] = 'HK2';
    http_response_code(200);
    ob_start();
    $controller->handle();
    $responseJson = ob_get_clean();
    $responseData = json_decode($responseJson, true);
    checkRenewal(http_response_code() === 200 && ($responseData['status'] ?? '') === 'success', 'UC18 Case 8c: Endpoint renewal-run chấp nhận đúng token và trả kết quả JSON 200');

    echo "\n----------------------------------------\n";
    echo "Tổng kết kiểm thử UC18: {$passed} đạt, {$failed} thất bại.\n";
    if ($failed > 0) {
        exit(1);
    }
} catch (Throwable $e) {
    echo 'Lỗi kiểm thử không mong muốn: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
