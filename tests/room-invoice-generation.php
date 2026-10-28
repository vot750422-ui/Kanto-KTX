<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/RoomInvoiceGenerator.php';

$passed = 0;
function checkRoomInvoice(bool $condition, string $message): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $passed++;
    echo '[PASS] ' . $message . PHP_EOL;
}

try {
    foreach (['toanha', 'phong', 'dienuutien', 'taikhoan', 'sinhvien', 'hocky', 'dongia', 'hopdong', 'hoadon', 'hoadontienphong', 'thongbao'] as $table) {
        $ddl = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $ddl = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $ddl);
        $ddl = preg_replace('/,\s*\n\)/', "\n)", $ddl);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $ddl));
    }

    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong VALUES ('P1','T1','A101',1,'Nam',8)");
    $pdo->exec("INSERT INTO hocky VALUES
        ('CUR','Kỳ hiện tại','2026-2027','2026-09-01','2027-01-15'),
        ('NEXT','Kỳ kế tiếp','2026-2027','2027-01-16','2027-05-31')");
    $pdo->exec("INSERT INTO dongia VALUES ('RATE','Tiền phòng',200000,'đồng/tháng','2026-01-01')");
    $pdo->exec("INSERT INTO taikhoan (TenDangNhap,MatKhau,VaiTro) VALUES ('SV101','x','Sinh viên'),('SV102','x','Sinh viên')");
    $accounts = $pdo->query("SELECT MaTK,TenDangNhap FROM taikhoan ORDER BY MaTK")->fetchAll();
    $stmt = $pdo->prepare('INSERT INTO sinhvien (MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Lop,NienKhoa,TrangThaiLuuTru) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute(['SV101', $accounts[0]['MaTK'], 'UT00', 'Sinh viên 101', '2005-01-01', '000000000101', 'Nam', 'Cần Thơ', '0900000001', 'TEST', '2024-2028', 'Đang ở']);
    $stmt->execute(['SV102', $accounts[1]['MaTK'], 'UT00', 'Sinh viên 102', '2005-01-01', '000000000102', 'Nam', 'Cần Thơ', '0900000002', 'TEST', '2024-2028', 'Đang ở']);
    $pdo->exec("INSERT INTO hopdong VALUES
        ('HD101','SV101','P1','2026-09-01','2027-01-15','Còn hạn'),
        ('HD102','SV102','P1','2026-09-01','2027-01-15','Còn hạn')");
    $pdo->exec("INSERT INTO hoadon (MaHoaDon,LoaiHoaDon,TongTien,TrangThai) VALUES ('EXISTING','Tiền phòng',903226,'Chưa thu')");
    $pdo->exec("INSERT INTO hoadontienphong VALUES ('EXISTING','SV102','P1','NEXT','RATE')");

    $generator = new RoomInvoiceGenerator($pdo);
    $beforeTrigger = false;
    try {
        $generator->generate(new DateTimeImmutable('2027-01-04', new DateTimeZone('Asia/Ho_Chi_Minh')));
    } catch (DomainException $error) {
        $beforeTrigger = str_contains($error->getMessage(), 'Chưa đến thời điểm');
    }
    checkRoomInvoice($beforeTrigger, 'Chưa đến mốc Timer thì không lập hóa đơn');

    $result = $generator->generate(new DateTimeImmutable('2027-01-05', new DateTimeZone('Asia/Ho_Chi_Minh')));
    checkRoomInvoice($result['processed'] === 1 && $result['skipped'] === 1, 'Lập mới sinh viên đủ điều kiện và bỏ qua hóa đơn đã tồn tại');
    checkRoomInvoice($pdo->query("SELECT TongTien FROM hoadon h JOIN hoadontienphong p ON p.MaHoaDon=h.MaHoaDon WHERE p.MSSV='SV101'")->fetchColumn() === '903226.00', 'Tính tiền phòng theo trọn học kỳ và đơn giá tháng');
    checkRoomInvoice((int) $pdo->query("SELECT COUNT(*) FROM thongbao WHERE MSSV='SV101'")->fetchColumn() === 1, 'Tạo thông báo cho hóa đơn tiền phòng mới');

    $rerun = $generator->generate(new DateTimeImmutable('2027-01-05', new DateTimeZone('Asia/Ho_Chi_Minh')));
    checkRoomInvoice($rerun['processed'] === 0 && $rerun['skipped'] === 2, 'Chạy lại cùng mốc không tạo hóa đơn trùng');
    echo $passed . " kiểm thử lập hóa đơn tiền phòng đạt.\n";
} catch (Throwable $error) {
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
