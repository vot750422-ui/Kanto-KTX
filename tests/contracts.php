<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
@session_start();
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Contract.php';
$passed = 0;
$failed = 0;
function checkContract(bool $ok, string $description): void
{
    global $passed, $failed;
    $ok ? $passed++ : $failed++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $description . PHP_EOL;
}
try {
    foreach (['toanha', 'phong', 'sinhvien', 'hopdong', 'hocky', 'hoadon', 'hoadontienphong', 'hoadondiennuoc', 'hoadondiennuocsinhvien', 'thanhlyhopdong'] as $table) {
        $sql = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $sql);
        $sql = preg_replace('/,\s*\n\)/', "\n)", $sql);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql));
    }
    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong (MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua) VALUES ('P1','T1','A101',1,'Nam',8),('P2','T1','A102',1,'Nữ',8)");
    $stmt = $pdo->prepare('INSERT INTO sinhvien (MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute(['SV001',1,'UT00','Nguyễn Văn An','2005-01-01','CCCD001','Nam','Cần Thơ','0900000001','an@example.test','KTPM01','2024-2028','Đang ở']);
    $stmt->execute(['SV002',2,'UT00','Trần Thị Bình','2005-02-02','CCCD002','Nữ','Cần Thơ','0900000002',null,'KTPM02','2024-2028','Đã rời đi']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD001','SV001','P1','2026-09-01','2027-01-15','Còn hạn'),('HD002','SV002','P2','2025-09-01','2026-01-15','Hết hạn')");
    $pdo->exec("INSERT INTO hocky VALUES ('HK1','Kỳ 1','2026-2027','2026-09-01','2027-01-15')");
    $pdo->exec("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai) VALUES ('HD_TIENPHONG', 'Tiền phòng', 1500000, 'Đã thu')");
    $pdo->exec("INSERT INTO hoadontienphong (MaHoaDon,MSSV,MaPhong,MaHocKy,MaDonGia) VALUES ('HD_TIENPHONG','SV001','P1','HK1','RATE')");
    $model = new Contract($pdo);
    $all = $model->listing(['contract'=>'','student'=>'','name'=>'','room'=>'','status'=>'']);
    checkContract($all['total'] === 2 && count($all['items']) === 2, 'Tra cứu toàn bộ hợp đồng');
    $combined = $model->listing(['contract'=>'HD001','student'=>'SV001','name'=>'Văn An','room'=>'A101','status'=>'Còn hạn']);
    checkContract($combined['total'] === 1 && $combined['items'][0]['MaHopDong'] === 'HD001', 'Kết hợp nhiều tiêu chí tra cứu');
    checkContract($model->listing(['contract'=>'','student'=>'','name'=>'','room'=>'A102','status'=>'Hết hạn'])['total'] === 1, 'Lọc theo số phòng và trạng thái lưu trong CSDL');
    checkContract($model->listing(['contract'=>'','student'=>"' OR 1=1 --",'name'=>'','room'=>'','status'=>''])['total'] === 0, 'Prepared statement chặn đầu vào SQL injection');
    $detail = $model->find('HD001');
    checkContract($detail !== null && $detail['HoTen'] === 'Nguyễn Văn An' && $detail['SoPhong'] === 'A101' && $detail['TenToa'] === 'Tòa A', 'Đọc đầy đủ chi tiết hợp đồng');
    checkContract($model->find('KHONGCO') === null, 'Mã không tồn tại trả về null');
    checkContract((int) $pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn() === 2, 'Tra cứu không thay đổi dữ liệu hợp đồng');

    // --- KIỂM THỬ UC05.2: THANH LÝ HỢP ĐỒNG ---
    // 1 & 2 & 3. Thanh lý HD001: ngày kết thúc tương lai đổi thành ngày thanh lý thực tế, sinh viên SV001 chuyển sang Đã rời đi
    $termResult = $model->terminate('HD001', '2026-10-27');
    $hd001After = $model->find('HD001');
    checkContract($hd001After !== null && $hd001After['TrangThai'] === 'Đã thanh lý', 'UC05.2: NVQL thanh lý hợp đồng Còn hạn thành công');
    checkContract($hd001After['NgayKetThuc'] === '2027-01-15' && $hd001After['NgayThanhLy'] === '2026-10-27' && (float) $hd001After['TienHoanTra'] > 0, 'UC05.2: Giữ ngày kết thúc gốc, ghi ngày thanh lý và tính tiền hoàn trả');
    $sv001Status = $pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV = 'SV001'")->fetchColumn();
    checkContract($sv001Status === 'Đã rời đi', 'UC05.2: Sinh viên không còn hợp đồng khác chuyển thành Đã rời đi');

    // 4. Ngày kết thúc cũ sớm hơn ngày hiện tại không bị kéo dài
    $stmt->execute(['SV003',3,'UT00','Lê Văn Cường','2005-03-03','CCCD003','Nam','Cần Thơ','0900000003',null,'KTPM03','2024-2028','Đang ở']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD003','SV003','P1','2026-08-01','2026-09-01','Còn hạn')");
    $model->terminate('HD003', '2026-10-27');
    $hd003After = $model->find('HD003');
    checkContract($hd003After['NgayKetThuc'] === '2026-09-01' && $hd003After['TrangThai'] === 'Đã thanh lý', 'UC05.2: Ngày kết thúc cũ sớm hơn ngày hiện tại không bị kéo dài');

    // 5. Sinh viên còn hợp đồng khác Còn hạn không bị đổi sai trạng thái lưu trú
    $stmt->execute(['SV004',4,'UT00','Phạm Thị Dung','2005-04-04','CCCD004','Nữ','Cần Thơ','0900000004',null,'KTPM04','2024-2028','Đang ở']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD004A','SV004','P2','2026-09-01','2027-01-15','Còn hạn'),('HD004B','SV004','P2','2027-01-16','2027-05-31','Còn hạn')");
    $model->terminate('HD004A', '2026-10-27');
    $sv004Status = $pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV = 'SV004'")->fetchColumn();
    checkContract($sv004Status === 'Đang ở', 'UC05.2: Sinh viên còn hợp đồng khác Còn hạn không bị đổi sai trạng thái');

    // 6. Hóa đơn tiền phòng chưa thu của đúng sinh viên chặn thanh lý.
    $stmt->execute(['SV006',6,'UT00','Nguyễn Văn Sáu','2005-06-06','CCCD006','Nam','Cần Thơ','0900000006',null,'KTPM06','2024-2028','Đang ở']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD006','SV006','P1','2026-09-01','2027-01-15','Còn hạn')");
    $pdo->exec("INSERT INTO hoadon (MaHoaDon,LoaiHoaDon,TongTien,TrangThai) VALUES ('UNPAID_ROOM','Tiền phòng',900000,'Chưa thu')");
    $pdo->exec("INSERT INTO hoadontienphong (MaHoaDon,MSSV,MaPhong,MaHocKy,MaDonGia) VALUES ('UNPAID_ROOM','SV006','P1','HK1','RATE')");
    $blockedByRoom = false;
    try { $model->terminate('HD006', '2026-10-27'); } catch (DomainException $e) { $blockedByRoom = str_contains($e->getMessage(), 'UNPAID_ROOM'); }
    checkContract($blockedByRoom && $model->find('HD006')['TrangThai'] === 'Còn hạn', 'UC05.2: Hóa đơn tiền phòng chưa thu của sinh viên chặn thanh lý');

    // 7. Hóa đơn điện nước chưa thu của đúng sinh viên cũng chặn thanh lý.
    $stmt->execute(['SV007',7,'UT00','Nguyễn Văn Bảy','2005-07-07','CCCD007','Nam','Cần Thơ','0900000007',null,'KTPM07','2024-2028','Đang ở']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD007','SV007','P1','2026-09-01','2027-01-15','Còn hạn')");
    $pdo->exec("INSERT INTO hoadon (MaHoaDon,LoaiHoaDon,TongTien,TrangThai) VALUES ('UNPAID_UTIL','Điện nước',200000,'Chưa thu')");
    $pdo->exec("INSERT INTO hoadondiennuoc (MaHoaDon,MaPhong,Thang,Nam,DienTieuThu,NuocTieuThu,TienDien,TienNuoc,MaDonGiaDien,MaDonGiaNuoc) VALUES ('UNPAID_UTIL','P1',10,2026,10,2,160000,40000,'RATE','RATE')");
    $pdo->exec("INSERT INTO hoadondiennuocsinhvien (MaHoaDon,MSSV,SoTienPhaiThu,TrangThai) VALUES ('UNPAID_UTIL','SV007',200000,'Chưa thu')");
    $blockedByUtility = false;
    try { $model->terminate('HD007', '2026-10-27'); } catch (DomainException $e) { $blockedByUtility = str_contains($e->getMessage(), 'UNPAID_UTIL'); }
    checkContract($blockedByUtility && $model->find('HD007')['TrangThai'] === 'Còn hạn', 'UC05.2: Phần điện nước chưa thu của sinh viên chặn thanh lý');

    // 8. Hợp đồng không tồn tại bị từ chối
    $rejectedNonexistent = false;
    try {
        $model->terminate('KHONGCO');
    } catch (DomainException $e) {
        $rejectedNonexistent = ($e->getMessage() === 'Hợp đồng không tồn tại.');
    }
    checkContract($rejectedNonexistent, 'UC05.2: Hợp đồng không tồn tại bị từ chối');

    // 9. Hợp đồng Hết hạn hoặc Đã thanh lý không được thanh lý lại
    $rejectedExpired = false;
    try {
        $model->terminate('HD002');
    } catch (DomainException $e) {
        $rejectedExpired = ($e->getMessage() === 'Chỉ có thể thanh lý hợp đồng đang còn hạn.');
    }
    checkContract($rejectedExpired, 'UC05.2: Hợp đồng Hết hạn không được thanh lý lại');

    $rejectedTerminatedAgain = false;
    try {
        $model->terminate('HD001');
    } catch (DomainException $e) {
        $rejectedTerminatedAgain = ($e->getMessage() === 'Chỉ có thể thanh lý hợp đồng đang còn hạn.');
    }
    checkContract($rejectedTerminatedAgain, 'UC05.2: Hợp đồng Đã thanh lý không được thanh lý lại');

    // 10. Lỗi giữa transaction không để lại cập nhật một phần
    $stmt->execute(['SV005',5,'UT00','Hoàng Văn Em','2005-05-05','CCCD005','Nam','Cần Thơ','0900000005',null,'KTPM05','2024-2028','Đang ở']);
    $pdo->exec("INSERT INTO hopdong VALUES ('HD005','SV005','P1','2026-09-01','2027-01-15','Còn hạn')");
    // Giả lập lỗi bằng cách tự throw ngoại lệ trong một transaction có bước cập nhật tương tự
    try {
        $pdo->beginTransaction();
        $pdo->exec("UPDATE hopdong SET TrangThai = 'Đã thanh lý' WHERE MaHopDong = 'HD005'");
        throw new RuntimeException('Lỗi hệ thống bất ngờ giữa chừng');
        $pdo->commit();
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
    $hd005Status = $pdo->query("SELECT TrangThai FROM hopdong WHERE MaHopDong = 'HD005'")->fetchColumn();
    $sv005Status = $pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV = 'SV005'")->fetchColumn();
    checkContract($hd005Status === 'Còn hạn' && $sv005Status === 'Đang ở', 'UC05.2: Lỗi giữa transaction không để lại cập nhật một phần');

    // 11. Dữ liệu hóa đơn lịch sử vẫn nguyên vẹn
    $invoiceStatus = $pdo->query("SELECT TrangThai FROM hoadon WHERE MaHoaDon = 'HD_TIENPHONG'")->fetchColumn();
    checkContract($invoiceStatus === 'Đã thu', 'UC05.2: Các hóa đơn và dữ liệu lịch sử liên quan vẫn nguyên vẹn');

    // --- KIỂM THỬ GIAO DIỆN XÁC NHẬN THANH LÝ HỢP ĐỒNG (MODAL DIALOG) ---
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../middleware/Role.php';
    $_SESSION['user'] = ['TenDangNhap' => 'nvql_test', 'VaiTro' => 'Nhân viên quản lý KTX'];
    $_GET['action'] = 'contract-detail';

    $renderContractPage = static function (array $contractData): string {
        $contract = $contractData;
        $displayName = 'Nhân viên quản lý';
        $pageTitle = 'Chi tiết hợp đồng ' . $contractData['MaHopDong'];
        $content = __DIR__ . '/../views/contracts/detail.php';
        ob_start();
        require __DIR__ . '/../views/contracts/layout.php';
        return ob_get_clean();
    };

    $activeContract = [
        'MaHopDong' => 'HD_ACTIVE_01',
        'MSSV' => 'SV001',
        'HoTen' => 'Nguyễn Văn An',
        'NgaySinh' => '2005-01-01',
        'GioiTinh' => 'Nam',
        'SDT' => '0900000001',
        'Email' => 'an@example.test',
        'Lop' => 'KTPM01',
        'NienKhoa' => '2024-2028',
        'TrangThaiLuuTru' => 'Đang ở',
        'MaPhong' => 'P1',
        'SoPhong' => 'A101',
        'TenToa' => 'Tòa A',
        'Tang' => 1,
        'GioiTinhPhong' => 'Nam',
        'NgayBatDau' => '2026-09-01',
        'NgayKetThuc' => '2027-01-15',
        'TrangThai' => 'Còn hạn',
    ];

    $htmlActive = $renderContractPage($activeContract);

    // 10. Trang hợp đồng Còn hạn có đầy đủ form, nút mở modal, dialog và script dùng chung
    checkContract(str_contains($htmlActive, 'id="contract-terminate-form"'), 'Giao diện Còn hạn có form id="contract-terminate-form"');
    checkContract(str_contains($htmlActive, 'data-open="contract-terminate-confirm"') && str_contains($htmlActive, 'class="approval-button danger"'), 'Nút mở modal có data-open="contract-terminate-confirm" và class "approval-button danger"');
    checkContract(str_contains($htmlActive, '<dialog class="approval-dialog" id="contract-terminate-confirm">'), 'Thẻ <dialog> dùng class approval-dialog và id="contract-terminate-confirm"');
    checkContract(str_contains($htmlActive, '<h2>Xác nhận thanh lý hợp đồng</h2>'), 'Tiêu đề modal là "Xác nhận thanh lý hợp đồng"');
    checkContract(str_contains($htmlActive, 'Bạn có chắc chắn muốn thanh lý hợp đồng HD_ACTIVE_01 không? Thao tác này sẽ cập nhật trạng thái hợp đồng thành Đã thanh lý.'), 'Nội dung modal chứa mã hợp đồng escape và mô tả thao tác cập nhật');
    checkContract(str_contains($htmlActive, '<button class="approval-button" type="button" data-close>Hủy</button>'), 'Nút Hủy có type="button" và data-close');
    checkContract(str_contains($htmlActive, '<button class="approval-button danger" type="submit" form="contract-terminate-form">Có, thanh lý hợp đồng</button>'), 'Nút xác nhận có type="submit", class danger và form="contract-terminate-form"');
    checkContract(str_contains($htmlActive, 'class="approval-actions"'), 'Các nút hành động trong modal nằm trong container class="approval-actions"');
    checkContract(str_contains($htmlActive, 'assets/js/approval.js'), 'Layout nạp script assets/js/approval.js');

    // 11. Không dùng window.confirm() hoặc inline onsubmit
    checkContract(!str_contains($htmlActive, 'onsubmit="return confirm(') && !str_contains($htmlActive, 'window.confirm'), 'HTML không còn onsubmit confirm hoặc window.confirm');

    // 12. Hợp đồng Hết hạn hoặc Đã thanh lý không render nút và modal thanh lý
    $expiredContract = $activeContract;
    $expiredContract['MaHopDong'] = 'HD_EXPIRED_02';
    $expiredContract['TrangThai'] = 'Hết hạn';
    $htmlExpired = $renderContractPage($expiredContract);
    checkContract(!str_contains($htmlExpired, 'contract-terminate-form') && !str_contains($htmlExpired, 'contract-terminate-confirm'), 'Hợp đồng Hết hạn không render form hoặc modal thanh lý');

    $terminatedContract = $activeContract;
    $terminatedContract['MaHopDong'] = 'HD_TERMINATED_03';
    $terminatedContract['TrangThai'] = 'Đã thanh lý';
    $htmlTerminated = $renderContractPage($terminatedContract);
    checkContract(!str_contains($htmlTerminated, 'contract-terminate-form') && !str_contains($htmlTerminated, 'contract-terminate-confirm'), 'Hợp đồng Đã thanh lý không render form hoặc modal thanh lý');
} catch (Throwable $error) {
    $failed++;
    echo '[FAIL] Không thể chạy kiểm thử UC05.1: ' . $error->getMessage() . PHP_EOL;
}
echo "$passed kiểm thử đạt, $failed thất bại.\n";
exit($failed ? 1 : 0);
