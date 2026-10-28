<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Incident.php';

$passed = 0;
$failed = 0;
function checkIncident(bool $condition, string $message): void
{
    global $passed, $failed;
    $condition ? $passed++ : $failed++;
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $message . PHP_EOL;
}

try {
    foreach (['toanha', 'phong', 'sinhvien', 'hopdong', 'nhanvien', 'phananhsuco'] as $table) {
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $create = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $create);
        $create = preg_replace('/,\s*\n\)/', "\n)", $create);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $create));
    }

    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong(MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua) VALUES ('P1','T1','A101',1,'Nam',8)");
    $student = $pdo->prepare('INSERT INTO sinhvien(MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $student->execute(['SV01', 101, 'UT00', 'Nguyễn Văn An', '2005-01-01', 'CCCD01', 'Nam', 'Cần Thơ', '0900000001', 'an@example.test', 'KTPM01', '2024-2028', 'Đang ở']);
    $student->execute(['SV02', 102, 'UT00', 'Trần Thị Bình', '2005-02-02', 'CCCD02', 'Nữ', 'An Giang', '0900000002', null, 'KTPM02', '2024-2028', 'Đã rời đi']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD01','SV01','P1','2026-01-01','2027-01-01','Còn hạn')");
    $pdo->exec("INSERT INTO nhanvien(MaNV,MaTK,HoTen,SDT,Email,TrangThaiLamViec) VALUES ('NV01',201,'Nhân viên Một',NULL,NULL,'Đang làm')");

    $model = new Incident($pdo);
    $stay = $model->accommodationForAccount(101);
    checkIncident($stay && $stay['MSSV'] === 'SV01' && $stay['SoPhong'] === 'A101', 'Tự xác định sinh viên và phòng từ tài khoản đăng nhập');

    $id = $model->createForAccount(101, 'Điện', 'Ổ cắm trong phòng không hoạt động.');
    $created = $model->find($id);
    checkIncident($created && $created['MSSV'] === 'SV01' && $created['MaPhong'] === 'P1' && $created['TrangThai'] === 'Chờ xử lý', 'Tạo phản ánh ở trạng thái chờ xử lý');

    $denied = false;
    try { $model->createForAccount(102, 'Nước', 'Không có hợp đồng còn hạn.'); } catch (DomainException) { $denied = true; }
    checkIncident($denied, 'Từ chối tài khoản không có hợp đồng còn hạn');

    $pending = $model->pending();
    checkIncident($pending['total'] === 1 && $pending['items'][0]['MaPhanAnh'] === $id, 'Danh sách chỉ gồm phản ánh chờ xử lý');

    $model->resolve($id, 201, '125000.00');
    $resolved = $model->find($id);
    checkIncident($resolved && $resolved['TrangThai'] === 'Đã xử lý' && $resolved['MaNhanVienXuLy'] === 'NV01' && (float)$resolved['ChiPhiSuaChua'] === 125000.0, 'Nhân viên xử lý và lưu chi phí sửa chữa');

    $duplicate = false;
    try { $model->resolve($id, 201, '1.00'); } catch (DomainException) { $duplicate = true; }
    checkIncident($duplicate, 'Không cho xử lý lặp một phản ánh đã hoàn tất');
} catch (Throwable $error) {
    $failed++;
    echo '[FAIL] Không thể chạy kiểm thử phản ánh sự cố: ' . $error->getMessage() . PHP_EOL;
}

echo "$passed kiểm thử đạt, $failed thất bại.\n";
exit($failed ? 1 : 0);
