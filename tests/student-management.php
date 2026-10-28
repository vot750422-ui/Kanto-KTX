<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../config/db.php';require_once __DIR__.'/../models/StudentManagement.php';
$passed=0;$failed=0;function checkStudentManagement(bool $ok,string $message):void{global $passed,$failed;$ok?$passed++:$failed++;echo($ok?'[PASS] ':'[FAIL] ').$message.PHP_EOL;}
try{
foreach(['toanha','phong','sinhvien','hopdong'] as $table){$sql=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];$sql=preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m','',$sql);$sql=preg_replace('/,\s*\n\)/',"\n)",$sql);$pdo->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$sql));}
$pdo->exec("INSERT INTO toanha VALUES('T1','Tòa A')");$pdo->exec("INSERT INTO phong(MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua)VALUES('P1','T1','A101',1,'Nam',8)");
$stmt=$pdo->prepare('INSERT INTO sinhvien(MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');
$stmt->execute(['SV01',1,'UT00','Nguyễn Văn An','2005-01-01','CCCD01','Nam','Cần Thơ','0900000001','an@example.test','KTPM01','2024-2028','Đang ở']);
$stmt->execute(['SV02',2,'UT00','Trần Thị Bình','2005-02-02','CCCD02','Nữ','An Giang','0900000002',null,'KTPM02','2024-2028','Đã rời đi']);
$pdo->exec("INSERT INTO hopdong VALUES('HD01','SV01','P1','2026-01-01','2027-01-01','Còn hạn')");$model=new StudentManagement($pdo);
$all=$model->listing(['student'=>'','name'=>'','room'=>'','class'=>'','status'=>'']);checkStudentManagement($all['total']===2,'Tra cứu toàn bộ sinh viên');
$one=$model->listing(['student'=>'SV01','name'=>'Văn An','room'=>'A101','class'=>'KTPM01','status'=>'Đang ở']);checkStudentManagement($one['total']===1&&$one['items'][0]['SoPhong']==='A101','Kết hợp bộ lọc và phòng qua hợp đồng');
$before=$model->find('SV01');$changed=$model->update('SV01',['HoTen'=>'Nguyễn Văn An Mới','NgaySinh'=>'2005-01-01','GioiTinh'=>'Nam','QueQuan'=>'Cần Thơ','SDT'=>'0900000001','Lop'=>'KTPM01']);$after=$model->find('SV01');
checkStudentManagement($changed&&$after['HoTen']==='Nguyễn Văn An Mới','Cập nhật trường được phép');
checkStudentManagement($after['CCCD']===$before['CCCD']&&$after['NienKhoa']===$before['NienKhoa']&&$after['TrangThaiLuuTru']===$before['TrangThaiLuuTru'],'Giữ nguyên trường chỉ đọc');
checkStudentManagement(!$model->update('SV01',['HoTen'=>'Nguyễn Văn An Mới','NgaySinh'=>'2005-01-01','GioiTinh'=>'Nam','QueQuan'=>'Cần Thơ','SDT'=>'0900000001','Lop'=>'KTPM01']),'Không UPDATE khi dữ liệu không đổi');
checkStudentManagement((int)$pdo->query('SELECT COUNT(*) FROM sinhvien')->fetchColumn()===2,'Không thêm hoặc xóa hồ sơ');
}catch(Throwable $error){$failed++;echo'[FAIL] Không thể chạy kiểm thử UC04: '.$error->getMessage().PHP_EOL;}
echo"$passed kiểm thử đạt, $failed thất bại.\n";exit($failed?1:0);
