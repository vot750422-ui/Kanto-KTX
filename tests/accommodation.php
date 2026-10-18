<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Accommodation.php';
$passed = 0;
$failed = 0;
function checkAccommodation(bool $ok, string $description): void
{
    global $passed, $failed;
    $ok ? $passed++ : $failed++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $description . PHP_EOL;
}
try {
    // Các bảng tạm che bảng thật trong kết nối này, không sửa dữ liệu demo.
    foreach (['sinhvien', 'hopdong', 'phong', 'toanha'] as $table) {
        $sql = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $sql);
        $sql = preg_replace('/,\s*\n\)/', "\n)", $sql);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql));
    }
    $pdo->exec("INSERT INTO toanha VALUES ('TEST','Tòa kiểm thử')");
    $pdo->exec("INSERT INTO phong (MaPhong, MaToa, SoPhong, Tang, GioiTinhPhong, SucChua) VALUES ('PTEST','TEST','A101',1,'Nam',8)");
    $stmt = $pdo->prepare('INSERT INTO sinhvien (MSSV, MaTK, MaUuTien, HoTen, NgaySinh, CCCD, GioiTinh, QueQuan, SDT, Email, Lop, NienKhoa, TrangThaiLuuTru) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ([1 => 'SELF', 2 => 'OTHER'] as $id => $mssv) {
        $stmt->execute([$mssv, $id, 'UT00', '<script>alert(1)</script>', '2005-01-01', 'TESTCCCD' . $id, 'Nam', 'Kiểm thử', '0900000000', null, 'TEST', '2024-2028', 'Đang ở']);
    }
    $model = new Accommodation($pdo);
    checkAccommodation($model->forAccount(999)['student'] === null, 'Tài khoản không có hồ sơ');
    $data = $model->forAccount(1);
    checkAccommodation($data['student']['MSSV'] === 'SELF' && $data['accommodation'] === null, 'Có hồ sơ nhưng chưa có hợp đồng');
    $pdo->exec("INSERT INTO hopdong VALUES ('HOTHER','OTHER','PTEST','2026-01-01','2027-01-01','Còn hạn'), ('HEXPIRED','SELF','PTEST','2025-01-01','2025-12-31','Hết hạn'), ('HCLOSED','SELF','PTEST','2026-01-01','2026-12-31','Đã thanh lý')");
    checkAccommodation($model->forAccount(1)['accommodation'] === null, 'Không lấy hợp đồng người khác, hết hạn hoặc đã thanh lý');
    $pdo->exec("INSERT INTO hopdong VALUES ('HSELF','SELF','PTEST','2026-09-01','2027-08-31','Còn hạn')");
    $data = $model->forAccount(1);
    checkAccommodation($data['accommodation']['MaHopDong'] === 'HSELF' && $data['accommodation']['SoPhong'] === 'A101' && $data['accommodation']['TenToa'] === 'Tòa kiểm thử', 'Lấy phòng/tòa qua hợp đồng của chính sinh viên');
    $pdo->exec("INSERT INTO hopdong VALUES ('HNEW','SELF','PTEST','2027-09-01','2028-08-31','Còn hạn')");
    checkAccommodation($model->forAccount(1)['accommodation']['MaHopDong'] === 'HNEW', 'Chọn hợp đồng Còn hạn mới nhất theo đặc tả, kể cả chưa bắt đầu');
    checkAccommodation((int) $pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn() === 5 && (int) $pdo->query('SELECT COUNT(*) FROM sinhvien')->fetchColumn() === 2, 'Xem hồ sơ không thay đổi dữ liệu');
} catch (Throwable $error) {
    $failed++;
    echo '[FAIL] Không thể chạy kiểm thử UC11: ' . $error->getMessage() . PHP_EOL;
}
echo "$passed kiểm thử đạt, $failed thất bại.\n";
exit($failed ? 1 : 0);
