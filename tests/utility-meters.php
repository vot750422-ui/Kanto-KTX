<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../config/db.php';require_once __DIR__.'/../models/UtilityMeter.php';
$passed=0;$failed=0;function checkMeter(bool $ok,string $message):void{global $passed,$failed;$ok?$passed++:$failed++;echo($ok?'[PASS] ':'[FAIL] ').$message.PHP_EOL;}
try{
foreach(['toanha','phong','sinhvien','hopdong','nhanvien','chisodiennuoc'] as $table){$sql=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];$sql=preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m','',$sql);$sql=preg_replace('/,\s*\n\)/',"\n)",$sql);$pdo->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$sql));}
$pdo->exec("INSERT INTO toanha VALUES('T1','Tòa A')");$pdo->exec("INSERT INTO phong(MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua)VALUES('P1','T1','A101',1,'Nam',8),('P2','T1','A102',1,'Nam',8)");
$stmt=$pdo->prepare('INSERT INTO sinhvien(MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');$stmt->execute(['SV01',101,'UT00','Nguyễn Văn An','2005-01-01','CCCD01','Nam','Cần Thơ','0900000001',null,'KTPM','2024-2028','Đang ở']);
$pdo->exec("INSERT INTO hopdong VALUES('HD01','SV01','P1','2026-01-01','2027-01-01','Còn hạn')");$pdo->exec("INSERT INTO nhanvien(MaNV,MaTK,HoTen,TrangThaiLamViec)VALUES('NV01',201,'Nhân viên Một','Đang làm')");$pdo->exec("INSERT INTO chisodiennuoc VALUES('CSOLD','P1','NV01',100,50,9,2026,'2026-09-30 08:00:00')");
$model=new UtilityMeter($pdo);$rooms=$model->occupiedRooms();checkMeter(count($rooms)===1&&$rooms[0]['MaPhong']==='P1'&&(int)$rooms[0]['ChiSoDien']===100,'Chỉ liệt kê phòng đang có sinh viên và chỉ số gần nhất');
$lower=false;try{$model->record('P1',201,99,50,new DateTimeImmutable('2026-10-20 08:00:00',new DateTimeZone('Asia/Ho_Chi_Minh')));}catch(DomainException){$lower=true;}checkMeter($lower,'Từ chối chỉ số mới nhỏ hơn chỉ số cũ');
$id=$model->record('P1',201,120,55,new DateTimeImmutable('2026-10-20 08:00:00',new DateTimeZone('Asia/Ho_Chi_Minh')));$row=$model->latest('P1');checkMeter(strlen($id)===10&&(int)$row['ChiSoDien']===120&&(int)$row['Thang']===10,'Lưu chỉ số và kỳ ghi nhận hiện tại');
$duplicate=false;try{$model->record('P1',201,121,56,new DateTimeImmutable('2026-10-21 08:00:00',new DateTimeZone('Asia/Ho_Chi_Minh')));}catch(DomainException){$duplicate=true;}checkMeter($duplicate,'Không ghi lặp cùng phòng trong một tháng');
$empty=false;try{$model->record('P2',201,1,1,new DateTimeImmutable('2026-10-20 08:00:00'));}catch(DomainException){$empty=true;}checkMeter($empty,'Không ghi chỉ số cho phòng trống');
}catch(Throwable $error){$failed++;echo'[FAIL] Không thể chạy kiểm thử chỉ số điện nước: '.$error->getMessage().PHP_EOL;}echo"$passed kiểm thử đạt, $failed thất bại.\n";exit($failed?1:0);
