<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
session_set_save_handler(fn($p,$n)=>true, fn()=>true, fn($id)=>'', fn($id,$data)=>true, fn($id)=>true, fn($ttl)=>0);
require_once __DIR__ . '/../middleware/Role.php';
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Approval.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Accommodation.php';
require_once __DIR__ . '/../models/StudentDashboard.php';
require_once __DIR__ . '/../models/StaffDashboard.php';
$passed = 0;
function checkPayment(bool $ok, string $label): void {
    global $passed;
    if (!$ok) { throw new RuntimeException($label); }
    echo '[PASS] ' . $label . PHP_EOL; $passed++;
}
function denyPayment(callable $call): bool {
    try { $call(); return false; } catch (DomainException $error) { return true; }
}
try {
    $pdo->exec(str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TEMPORARY TABLE', file_get_contents(__DIR__ . '/../sql/uc03-email.sql')));
    foreach (['toanha','phong','dienuutien','dondangky','sinhvien','taikhoan','hopdong','hoadon','hoadontienphong','hocky','dongia','hoadondiennuocsinhvien','phananhsuco','hoadondiennuoc'] as $table) {
        $ddl = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $ddl = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $ddl);
        $ddl = preg_replace('/,\s*\n\)/', "\n)", $ddl);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $ddl));
    }
    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong VALUES ('P1','T1','A101',1,'Nam',8)");
    $pdo->exec(file_get_contents(__DIR__ . '/../sql/update-priorities.sql'));
    $year = (int)(new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y');
    $stmt = $pdo->prepare('INSERT INTO hocky VALUES (?,?,?,?,?)');
    $stmt->execute(['CURRENT','Kỳ hiện tại',(string)$year,"$year-01-01","$year-12-31"]);
    $nextYear = $year + 1;
    $stmt->execute(['NEXT','Kỳ kế tiếp',(string)$nextYear,"$nextYear-01-01","$nextYear-12-31"]);
    $pdo->exec("INSERT INTO dongia VALUES ('RATE','Tiền phòng',200000,'đồng/tháng','2020-01-01')");
    $approval = new Approval($pdo);
    $model = new Payment($pdo);
    $create = function (string $id) use ($pdo, $approval): array {
        static $serial = 0;
        $stmt = $pdo->prepare('INSERT INTO dondangky (MaDonDangKy,MSSV,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Lop,NienKhoa,MaUuTien,MaPhong) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$id,$id,'Kiểm thử <script>x</script>','2005-01-01',sprintf('%012d',++$serial),'Nam','Kiểm thử','0900000000','TEST','2024-2028','UT00','P1']);
        return $approval->approve($id);
    };
    checkPayment($model->listing()['total'] === 0, 'Danh sách rỗng');
    $first = $create('PAY1');
    $before = $pdo->query("SELECT * FROM taikhoan WHERE TenDangNhap='PAY1'")->fetch();
    $period = $pdo->query("SELECT DATE_FORMAT(ThoiGianLap,'%Y-%m') FROM hoadon LIMIT 1")->fetchColumn();
    checkPayment($model->listing('PAY1','Chưa thu',1,['invoice'=>$first['invoice'],'room'=>'A101','name'=>'Kiểm thử','type'=>'Tiền phòng','period'=>$period])['total'] === 1, 'Kết hợp đầy đủ tiêu chí tra cứu theo wireframe');
    foreach (['invoice'=>'NOPE','room'=>'B999','name'=>'Không tồn tại','type'=>'Điện nước','period'=>'2000-01'] as $key=>$value) {
        checkPayment($model->listing('PAY1','',1,[$key=>$value])['total'] === 0, 'Bộ lọc không khớp: ' . $key);
    }
    $pdo->exec("INSERT INTO hoadon (MaHoaDon,LoaiHoaDon,TongTien) VALUES ('UTILITY','Điện nước',100000)");
    $pdo->exec("INSERT INTO hoadondiennuoc VALUES ('UTILITY','P1',10,2026,1,1,50000,50000,'RATE','RATE')");
    $pdo->exec("INSERT INTO hoadondiennuocsinhvien VALUES ('UTILITY','PAY1')");
    $utilities = $model->listing('PAY1','',1,['type'=>'Điện nước','period'=>'2026-10','name'=>'Kiểm thử']);
    checkPayment($utilities['total'] === 1 && $utilities['items'][0]['KyHoaDon'] === '10/2026' && !$utilities['items'][0]['CanConfirm'], 'Tra cứu điện nước theo sinh viên/kỳ và không cho xác nhận ngoài phạm vi');
    $pdo->exec("DELETE FROM hoadondiennuocsinhvien WHERE MaHoaDon='UTILITY'");
    $pdo->exec("DELETE FROM hoadondiennuoc WHERE MaHoaDon='UTILITY'");
    $pdo->exec("DELETE FROM hoadon WHERE MaHoaDon='UTILITY'");
    $countBefore = (int)$pdo->query('SELECT COUNT(*) FROM taikhoan')->fetchColumn();
    checkPayment($model->listing('PAY1')['total'] === 1 && $model->listing('OTHER')['total'] === 0, 'Lọc đúng MSSV, không hiện hóa đơn người khác');
    checkPayment($model->listing("' OR 1=1 --")['total'] === 0, 'Prepared statement không nhận SQL injection');
    $listing = $model->listing();
    $student = ''; $status = 'unpaid'; $error = ''; $success = '';
    $_SESSION['user'] = ['MaTK'=>-1,'TenDangNhap'=>'TESTKT','VaiTro'=>'Nhân viên kế toán'];
    $_GET = ['action'=>'payments'];
    ob_start();
    require __DIR__ . '/../views/payments/list.php';
    $html = ob_get_clean();
    checkPayment(str_contains($html,'&lt;script&gt;x&lt;/script&gt;') && !str_contains($html,'<script>x</script>') && str_contains($html,'data-open="payment-0"'), 'Giao diện escape hồ sơ và có hộp thoại xác nhận');
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($dom);
    $form = $xpath->query('//dialog/form')->item(0);
    checkPayment($form !== null && str_contains($form->getAttribute('action'),'action=payment-confirm') && $xpath->query('.//input[@name="invoice"]',$form)->item(0)->getAttribute('value') === $first['invoice'], 'Form xác nhận dùng đúng hóa đơn');
    $result = $model->confirm($first['invoice']);
    $bill = $pdo->query('SELECT * FROM hoadon')->fetch();
    checkPayment($bill['TrangThai'] === 'Đã thu' && $bill['ThoiGianThanhToan'] !== null, 'Xác nhận thu và ghi thời gian thanh toán');
    $contract = $pdo->query('SELECT * FROM hopdong')->fetch();
    checkPayment($contract['MaHopDong'] === $result['contract'] && $contract['NgayBatDau'] === "$nextYear-01-01" && $contract['NgayKetThuc'] === "$nextYear-12-31", 'Hợp đồng đúng học kỳ hóa đơn');
    checkPayment($pdo->query("SELECT MaHopDong FROM dondangky WHERE MaDonDangKy='PAY1'")->fetchColumn() === $result['contract'], 'Đơn đăng ký liên kết hợp đồng');
    checkPayment($pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV='PAY1'")->fetchColumn() === 'Đang ở', 'Cập nhật sinh viên sau thanh toán');
    checkPayment($pdo->query("SELECT * FROM taikhoan WHERE TenDangNhap='PAY1'")->fetch() === $before && (int)$pdo->query('SELECT COUNT(*) FROM taikhoan')->fetchColumn() === $countBefore && password_verify('1', $before['MatKhau']), 'Giữ tài khoản/mật khẩu đã cấp, không tạo trùng');
    $profile = (new Accommodation($pdo))->forAccount((int)$before['MaTK']);
    checkPayment($profile['accommodation']['MaHopDong'] === $contract['MaHopDong'] && $profile['accommodation']['SoPhong'] === 'A101', 'UC11 đọc được hợp đồng/phòng ngay sau xác nhận');
    $dashboard = (new StudentDashboard($pdo))->forAccount((int)$before['MaTK']);
    checkPayment($dashboard['room'] === 'A101' && $dashboard['contract'] === 'Bắt đầu 01/01/' . $nextYear && $dashboard['invoices'] === 0, 'Dashboard hiện phòng đã bố trí và ngày bắt đầu hợp đồng tương lai sau thanh toán');
    checkPayment((new StaffDashboard($pdo))->invoiceSummary() === ['applications'=>1,'students'=>1,'rooms'=>0,'reports'=>0], 'Dashboard kế toán cập nhật số hóa đơn sau thu');
    $capacity = $pdo->query("SELECT (SELECT COUNT(*) FROM hopdong WHERE TrangThai='Còn hạn')+(SELECT COUNT(*) FROM dondangky WHERE TrangThai='Chờ duyệt' OR (TrangThai='Đã duyệt' AND MaHopDong IS NULL))")->fetchColumn();
    checkPayment((int)$capacity === 1, 'Chuyển giữ chỗ sang hợp đồng không đếm hai lần');
    checkPayment(denyPayment(fn() => $model->confirm($first['invoice'])) && (int)$pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn() === 1, 'Xác nhận lặp không tạo hợp đồng trùng');
    checkPayment($model->listing('PAY1','Đã thu')['total'] === 1 && $model->listing()['total'] === 0, 'Bộ lọc trạng thái sau thu');
    checkPayment(denyPayment(fn() => $model->confirm('NOTFOUND')), 'Hóa đơn không tồn tại bị chặn');
    $second = $create('PAY2');
    $pdo->exec("UPDATE taikhoan SET TrangThai='Khóa' WHERE TenDangNhap='PAY2'");
    checkPayment(denyPayment(fn() => $model->confirm($second['invoice'])), 'Tài khoản bị khóa không được hoàn tất lưu trú');
    $pdo->exec("UPDATE taikhoan SET TrangThai='Hoạt động' WHERE TenDangNhap='PAY2'");
    $pdo->exec("INSERT INTO hopdong VALUES ('OVERLAP','PAY2','P1','$nextYear-01-01','$nextYear-12-31','Còn hạn')");
    checkPayment(denyPayment(fn() => $model->confirm($second['invoice'])), 'Không tạo hợp đồng trùng thời gian');
    $pdo->exec("DELETE FROM hopdong WHERE MaHopDong='OVERLAP'");
    $pdo->exec("UPDATE phong SET SucChua=1 WHERE MaPhong='P1'");
    checkPayment(denyPayment(fn() => $model->confirm($second['invoice'])), 'Chặn dữ liệu phòng vượt sức chứa');
    $pdo->exec("UPDATE phong SET SucChua=8 WHERE MaPhong='P1'");
    $pdo->exec("ALTER TABLE hoadon ADD CONSTRAINT payment_test_failure CHECK (TrangThai='Chưa thu') NOT ENFORCED");
    // Chỉ enforce sau khi hóa đơn đã thu trước đó được bỏ khỏi bảng tạm.
    $pdo->exec("DELETE FROM hoadon WHERE TrangThai='Đã thu'");
    $pdo->exec('ALTER TABLE hoadon ALTER CHECK payment_test_failure ENFORCED');
    $failed = false;
    try { $model->confirm($second['invoice']); } catch (PDOException $error) { $failed = true; }
    checkPayment($failed && (int)$pdo->query("SELECT COUNT(*) FROM hopdong WHERE MSSV='PAY2'")->fetchColumn() === 0 && $pdo->query("SELECT MaHopDong FROM dondangky WHERE MaDonDangKy='PAY2'")->fetchColumn() === null && $pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV='PAY2'")->fetchColumn() === 'Chờ thanh toán' && (int)$model->listing('PAY2','Chưa thu')['total'] === 1, 'Lỗi cập nhật hóa đơn rollback hợp đồng, liên kết và sinh viên');
    $pdo->exec('ALTER TABLE hoadon DROP CHECK payment_test_failure');
    checkPayment($model->confirm($second['invoice'])['username'] === 'PAY2', 'Sau rollback có thể thử lại thành công');
    echo "$passed kiểm thử thanh toán đạt.\n";
} catch (Throwable $error) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL); exit(1);
}
