<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../config/db.php';require_once __DIR__.'/../models/StatisticsReport.php';
$passed=0;$failed=0;function checkStatistics(bool $ok,string $message):void{global $passed,$failed;$ok?$passed++:$failed++;echo($ok?'[PASS] ':'[FAIL] ').$message.PHP_EOL;}
try{
foreach(['toanha','phong','sinhvien','hopdong','hoadon','phananhsuco'] as $table){$sql=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];$sql=preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m','',$sql);$sql=preg_replace('/,\s*\n\)/',"\n)",$sql);$pdo->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$sql));}
$pdo->exec("INSERT INTO toanha VALUES('T1','Tòa A')");$pdo->exec("INSERT INTO phong(MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua)VALUES('P1','T1','A101',1,'Nam',8)");
$stmt=$pdo->prepare('INSERT INTO sinhvien(MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');$stmt->execute(['SV01',101,'UT00','Nguyễn Văn An','2005-01-01','CCCD01','Nam','Cần Thơ','0900000001',null,'KTPM','2024-2028','Đang ở']);
$pdo->exec("INSERT INTO hopdong VALUES('HD01','SV01','P1','2026-10-01','2027-01-01','Còn hạn')");$pdo->exec("INSERT INTO hoadon VALUES('INV01','Tiền phòng',200000,'2026-10-05 08:00:00','Chưa thu',NULL),('INV02','Điện nước',100000,'2026-09-05 08:00:00','Đã thu','2026-09-06 08:00:00')");$pdo->exec("INSERT INTO phananhsuco(MaPhanAnh,MSSV,MaPhong,LoaiSuCo,MoTa,ThoiGianGui,TrangThai)VALUES('PA01','SV01','P1','Điện','Mô tả sự cố điện','2026-10-08 08:00:00','Chờ xử lý')");
$model=new StatisticsReport($pdo);$stay=$model->generate('accommodation',null,null);checkStatistics((int)$stay['summary']['total']===1&&(int)$stay['summary']['active']===1,'Tổng hợp tình hình lưu trú');
$invoice=$model->generate('invoices','2026-10-01','2026-10-31');checkStatistics((int)$invoice['summary']['total']===1&&(float)$invoice['summary']['amount']===200000.0&&$invoice['items'][0]['MaHoaDon']==='INV01','Lọc và tổng hợp hóa đơn theo thời gian');
$incident=$model->generate('incidents','2026-10-08','2026-10-08');checkStatistics((int)$incident['summary']['pending']===1&&count($incident['items'])===1,'Khoảng ngày kết thúc bao gồm trọn ngày');
$invalid=false;try{$model->generate('other',null,null);}catch(InvalidArgumentException){$invalid=true;}checkStatistics($invalid,'Từ chối loại thống kê ngoài đặc tả');
}catch(Throwable $error){$failed++;echo'[FAIL] Không thể chạy kiểm thử thống kê: '.$error->getMessage().PHP_EOL;}echo"$passed kiểm thử đạt, $failed thất bại.\n";exit($failed?1:0);
