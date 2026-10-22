<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ChatbotRoomSource.php';
$passed = 0;
function checkPublicRooms(bool $ok, string $label): void {
    global $passed;
    if (!$ok) { throw new RuntimeException($label); }
    $passed++; echo '[PASS] ' . $label . PHP_EOL;
}
try {
    foreach (['toanha','phong','hopdong','dondangky'] as $table) {
        $ddl = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $ddl = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $ddl);
        $ddl = preg_replace('/,\s*\n\)/', "\n)", $ddl);
        $pdo->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$ddl));
    }
    $source = new ChatbotRoomSource($pdo);
    checkPublicRooms($source->snapshot()['total_rooms'] === 0 && !$pdo->inTransaction(), 'Nguồn rỗng và kết thúc transaction đọc');
    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A'),('T2','Tòa B')");
    $pdo->exec("INSERT INTO phong VALUES ('P1','T1','A101',1,'Nam',8),('P2','T2','B101',1,'Nữ',4),('P3','T1','A102',1,'Nam',1)");
    $pdo->exec("INSERT INTO hopdong VALUES ('H1','PRIVATE1','P1','2026-01-01','2027-12-31','Còn hạn'),('H2','PRIVATE2','P3','2026-01-01','2027-12-31','Còn hạn')");
    $stmt = $pdo->prepare('INSERT INTO dondangky (MaDonDangKy,MSSV,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Lop,NienKhoa,MaUuTien,MaPhong,TrangThai,MaHopDong) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ([['D1','P1','Chờ duyệt',null],['D2','P1','Đã duyệt',null],['D3','P1','Đã duyệt','H1'],['D4','P3','Chờ duyệt',null]] as $i=>$row) {
        [$id,$room,$status,$contract] = $row;
        $stmt->execute([$id,'PRIVATE'.$i,'PRIVATE_NAME','2005-01-01',sprintf('%012d',$i),'Nam','PRIVATE_ADDRESS','0900000000','PRIVATE_CLASS','2024-2028','UT00',$room,$status,$contract]);
    }
    $snapshot = $source->snapshot();
    $rows = array_column($snapshot['rooms'], null, 1);
    checkPublicRooms($snapshot['total_rooms'] === 3 && count($snapshot['rooms']) === 3 && count($snapshot['summary']) === 2, 'Bao gồm khu Nam và Nữ, không chỉ tòa mặc định');
    checkPublicRooms($rows['A101'][4] === 8 && $rows['A101'][5] === 1 && $rows['A101'][6] === 2 && $rows['A101'][7] === 5, 'Sức chứa, đang ở, đang giữ và còn chỗ đúng UC15');
    checkPublicRooms($rows['B101'][7] === 4 && $rows['A102'][7] === 0, 'Phòng trống và số chỗ âm được giới hạn về 0');
    checkPublicRooms($snapshot['summary'][0]['con_cho'] === 5 && $snapshot['summary'][0]['so_phong'] === 2 && $snapshot['summary'][0]['phong_con_cho'] === 1, 'Tổng theo tòa được tính ở máy chủ');
    $json = json_encode($snapshot);
    checkPublicRooms(!str_contains($json,'PRIVATE') && !str_contains($json,'0900000000') && !str_contains($json,'MaHopDong') && !str_contains($json,'MSSV'), 'Không gửi danh tính, liên hệ, hợp đồng hoặc hồ sơ sang AI');
    checkPublicRooms($snapshot['columns'] === ['toa','phong','tang','khu','suc_chua','dang_o','dang_giu','con_cho'] && $snapshot['truncated'] === false && isset($snapshot['updated_at']), 'Cột công khai, thời điểm đọc và độ đầy đủ rõ ràng');
    $pdo->exec("UPDATE dondangky SET TrangThai='Từ chối' WHERE MaDonDangKy='D1'");
    $rows = array_column($source->snapshot()['rooms'], null, 1);
    checkPublicRooms($rows['A101'][7] === 6, 'Đọc số liệu mới, không giữ cache chỗ trống cũ');
    checkPublicRooms((int)$pdo->query('SELECT COUNT(*) FROM dondangky')->fetchColumn() === 4 && (int)$pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn() === 2, 'Snapshot không thay đổi dữ liệu');
    echo "$passed kiểm thử nguồn phòng đạt.\n";
} catch (Throwable $error) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR,'[FAIL] ' . $error->getMessage() . PHP_EOL); exit(1);
}
