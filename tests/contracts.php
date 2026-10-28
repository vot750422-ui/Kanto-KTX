<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Contract.php';

$passed = 0;
$failed = 0;

function checkContract(bool $condition, string $message): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo '[PASS] ' . $message . PHP_EOL;
        return;
    }
    $failed++;
    echo '[FAIL] ' . $message . PHP_EOL;
}

try {
    foreach (['toanha', 'phong', 'sinhvien', 'hopdong'] as $table) {
        $sql = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $sql);
        $sql = preg_replace('/,\s*\n\)/', "\n)", $sql);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql));
    }

    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong (MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua) VALUES ('P1','T1','A101',1,'Nam',8),('P2','T1','A102',1,'Nữ',8)");
    $student = $pdo->prepare('INSERT INTO sinhvien (MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $student->execute(['SV001', 1, 'UT00', 'Nguyễn Văn An', '2005-01-01', 'CCCD001', 'Nam', 'Cần Thơ', '0900000001', 'an@example.test', 'KTPM01', '2024-2028', 'Đang ở']);
    $student->execute(['SV002', 2, 'UT00', 'Trần Thị Bình', '2005-02-02', 'CCCD002', 'Nữ', 'Cần Thơ', '0900000002', null, 'KTPM02', '2024-2028', 'Đã rời đi']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD001','SV001','P1','2026-09-01','2027-01-15','Còn hạn'),('HD002','SV002','P2','2025-09-01','2026-01-15','Hết hạn')");

    $model = new Contract($pdo);
    $all = $model->listing(['contract' => '', 'student' => '', 'name' => '', 'room' => '', 'status' => '']);
    checkContract($all['total'] === 2 && count($all['items']) === 2, 'Tra cứu toàn bộ hợp đồng');

    $combined = $model->listing(['contract' => 'HD001', 'student' => 'SV001', 'name' => 'Văn An', 'room' => 'A101', 'status' => 'Còn hạn']);
    checkContract($combined['total'] === 1 && $combined['items'][0]['MaHopDong'] === 'HD001', 'Kết hợp nhiều tiêu chí tra cứu');
    checkContract($model->listing(['contract' => '', 'student' => '', 'name' => '', 'room' => 'A102', 'status' => 'Hết hạn'])['total'] === 1, 'Lọc theo phòng và trạng thái');
    checkContract($model->listing(['contract' => '', 'student' => "' OR 1=1 --", 'name' => '', 'room' => '', 'status' => ''])['total'] === 0, 'Prepared statement chặn SQL injection');

    $detail = $model->find('HD001');
    checkContract($detail !== null && $detail['HoTen'] === 'Nguyễn Văn An' && $detail['SoPhong'] === 'A101' && $detail['TenToa'] === 'Tòa A', 'Đọc đầy đủ chi tiết hợp đồng');
    checkContract($model->find('KHONGCO') === null, 'Mã không tồn tại trả về null');
    checkContract((int) $pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn() === 2, 'Tra cứu không thay đổi dữ liệu');
} catch (Throwable $error) {
    $failed++;
    echo '[FAIL] Không thể chạy kiểm thử UC05.1: ' . $error->getMessage() . PHP_EOL;
}

echo "$passed kiểm thử đạt, $failed thất bại.\n";
exit($failed ? 1 : 0);
