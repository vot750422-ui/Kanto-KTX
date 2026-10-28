<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Notification.php';

$passed = 0;
$failed = 0;
function checkNotification(bool $condition, string $message): void
{
    global $passed, $failed;
    $condition ? $passed++ : $failed++;
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $message . PHP_EOL;
}

try {
    foreach (['sinhvien', 'thongbao'] as $table) {
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $create = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $create);
        $create = preg_replace('/,\s*\n\)/', "\n)", $create);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $create));
    }

    $student = $pdo->prepare('INSERT INTO sinhvien(MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $student->execute(['SV01', 101, 'UT00', 'Nguyễn Văn An', '2005-01-01', 'CCCD01', 'Nam', 'Cần Thơ', '0900000001', 'an@example.test', 'KTPM01', '2024-2028', 'Đang ở']);
    $student->execute(['SV02', 102, 'UT00', 'Trần Thị Bình', '2005-02-02', 'CCCD02', 'Nữ', 'An Giang', '0900000002', null, 'KTPM02', '2024-2028', 'Đang ở']);
    $insert = $pdo->prepare('INSERT INTO thongbao(MaThongBao,MSSV,MaHoaDon,TieuDe,NoiDung,ThoiGianTao) VALUES (?,?,?,?,?,?)');
    $insert->execute(['TB001', 'SV01', null, 'Thông báo cũ', 'Nội dung cũ', '2026-10-01 08:00:00']);
    $insert->execute(['TB002', 'SV01', null, 'Thông báo mới', 'Nội dung mới', '2026-10-02 08:00:00']);
    $insert->execute(['TB003', 'SV02', null, 'Thông báo riêng', 'Không thuộc SV01', '2026-10-03 08:00:00']);

    $model = new Notification($pdo);
    $list = $model->listingForAccount(101);
    checkNotification($list['total'] === 2, 'Chỉ đếm thông báo thuộc tài khoản hiện tại');
    checkNotification(array_column($list['items'], 'MaThongBao') === ['TB002', 'TB001'], 'Sắp xếp thông báo mới nhất trước');
    checkNotification($model->findForAccount('TB001', 101)['TieuDe'] === 'Thông báo cũ', 'Đọc chi tiết thông báo thuộc sinh viên');
    checkNotification($model->findForAccount('TB003', 101) === null, 'Không đọc được thông báo của sinh viên khác');
    checkNotification((int)$pdo->query('SELECT COUNT(*) FROM thongbao')->fetchColumn() === 3, 'Luồng thông báo chỉ đọc, không sửa dữ liệu');
} catch (Throwable $error) {
    $failed++;
    echo '[FAIL] Không thể chạy kiểm thử thông báo: ' . $error->getMessage() . PHP_EOL;
}

echo "$passed kiểm thử đạt, $failed thất bại.\n";
exit($failed ? 1 : 0);
