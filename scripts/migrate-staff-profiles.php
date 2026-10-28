<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../config/connection.php';

$db = createDatabaseConnection();
$db->beginTransaction();
try {
    $accounts = $db->query("SELECT t.MaTK,t.TenDangNhap
        FROM taikhoan t
        WHERE t.VaiTro='Nhân viên quản lý KTX'
          AND NOT EXISTS(SELECT 1 FROM nhanvien nv WHERE nv.MaTK=t.MaTK)
        ORDER BY t.MaTK FOR UPDATE")->fetchAll();

    $checkId = $db->prepare('SELECT 1 FROM nhanvien WHERE MaNV=?');
    $insert = $db->prepare("INSERT INTO nhanvien(MaNV,MaTK,HoTen,SDT,Email,TrangThaiLamViec)
        VALUES(?,?,?,NULL,NULL,'Đang làm')");
    foreach ($accounts as $account) {
        $accountId = (int)$account['MaTK'];
        if ($accountId < 1 || $accountId > 99999999) {
            throw new RuntimeException('Không thể tạo mã nhân viên từ mã tài khoản ' . $accountId . '.');
        }
        $staffId = 'NV' . str_pad((string)$accountId, 8, '0', STR_PAD_LEFT);
        $checkId->execute([$staffId]);
        if ($checkId->fetchColumn()) {
            throw new RuntimeException('Mã nhân viên dự kiến đã tồn tại: ' . $staffId . '.');
        }
        // Repository chỉ có tên đăng nhập cho tài khoản mẫu; không tự đặt họ tên cá nhân.
        $insert->execute([$staffId, $accountId, $account['TenDangNhap']]);
    }
    $db->commit();
    echo 'Đã bổ sung ' . count($accounts) . " hồ sơ nhân viên quản lý còn thiếu.\n";
} catch (Throwable $error) {
    if ($db->inTransaction()) { $db->rollBack(); }
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
