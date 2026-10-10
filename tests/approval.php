<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Approval.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/StudentDashboard.php';
require_once __DIR__ . '/../controllers/ApprovalController.php';
session_set_save_handler(fn($p, $n) => true, fn() => true, fn($id) => '', fn($id, $data) => true, fn($id) => true, fn($ttl) => 0);
session_start();
ob_start();
$passed = 0;
function expectApproval(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) { throw new RuntimeException($label); }
    $passed++;
    echo "[PASS] $label\n";
}
function deniedApproval(callable $call, string $part = ''): bool
{
    try { $call(); return false; } catch (DomainException $e) { return $part === '' || str_contains($e->getMessage(), $part); }
}
function fixtureApplication(PDO $db, string $id, string $priority = 'UT00', string $room = 'P1', ?string $student = null, ?string $cccd = null): array
{
    static $serial = 0;
    $data = ['MaDonDangKy' => $id, 'MSSV' => $student ?? $id, 'HoTen' => 'Sinh viên <script>test</script>',
        'NgaySinh' => '2006-01-01', 'CCCD' => $cccd ?? sprintf('%012d', ++$serial), 'GioiTinh' => 'Nam',
        'QueQuan' => 'Cần Thơ', 'SDT' => '0900000000', 'Lop' => 'TEST', 'NienKhoa' => '2024-2028',
        'MaUuTien' => $priority, 'MaPhong' => $room, 'ThoiGianGui' => '2026-01-01 10:00:00'];
    $stmt = $db->prepare('INSERT INTO dondangky (' . implode(', ', array_keys($data)) . ') VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')');
    $stmt->execute(array_values($data));
    return $data;
}
function approvalPage(ApprovalController $controller, string $action, array $get = [], array $post = []): string
{
    $_GET = $get;
    $_POST = $post;
    $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
    http_response_code(200);
    ob_start();
    $controller->handle($action);
    return ob_get_clean();
}
try {
    // Chỉ ghi bảng TEMPORARY trong kết nối này; không sửa dữ liệu người dùng.
    foreach (['toanha','phong','dienuutien','dondangky','sinhvien','taikhoan','hopdong','hoadon','hoadontienphong','hocky','dongia','nhanvien','hoadondiennuocsinhvien','phananhsuco'] as $table) {
        $ddl = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $ddl = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $ddl);
        $ddl = preg_replace('/,\s*\n\)/', "\n)", $ddl);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $ddl));
    }
    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong (MaPhong,MaToa,SoPhong,Tang,GioiTinhPhong,SucChua) VALUES ('P1','T1','A101',1,'Nam',100),('FULL','T1','A102',1,'Nam',1),('WRONG','T1','A103',1,'Nữ',10)");
    $pdo->exec(file_get_contents(__DIR__ . '/../sql/update-priorities.sql'));
    $model = new Approval($pdo);
    $registration = new Registration($pdo);
    expectApproval($model->pending()['total'] === 0, 'Danh sách rỗng và trang đầu hợp lệ');
    foreach (['NONE'=>'UT00','POOR'=>'UT01','REGION'=>'UT03','WAR'=>'UT02'] as $id=>$priority) { fixtureApplication($pdo, $id, $priority); }
    expectApproval(array_column($model->pending()['items'], 'MaDonDangKy') === ['WAR','REGION','POOR','NONE'], 'Ưu tiên 4 → 3 → 2 → 0');
    fixtureApplication($pdo, 'OLDER','UT02');
    $pdo->exec("UPDATE dondangky SET ThoiGianGui='2025-01-01 00:00:00' WHERE MaDonDangKy='OLDER'");
    expectApproval($model->pending()['items'][0]['MaDonDangKy'] === 'OLDER', 'Cùng ưu tiên: thời gian cũ nhất trước');
    fixtureApplication($pdo, 'AAA','UT00');
    expectApproval(array_slice(array_column($model->pending()['items'], 'MaDonDangKy'), -2) === ['AAA','NONE'], 'Trùng thời gian: mã đơn giữ thứ tự ổn định');
    $listing = $model->pending(999, 2);
    expectApproval($listing['page'] === 3 && count($listing['items']) === 2, 'Phân trang giới hạn đúng trang cuối');
    expectApproval($model->find("' OR 1=1 --") === null, 'Mã đơn chứa SQL injection không trả hồ sơ');
    expectApproval(deniedApproval(fn() => $model->reject('NONE', '   '), 'Thiếu lý do'), 'Từ chối thiếu lý do không ghi dữ liệu');
    expectApproval(deniedApproval(fn() => $model->reject('NONE', str_repeat('ạ', 501)), '500'), 'Giới hạn lý do theo ký tự Unicode');
    $before = (int) $registration->rooms('Nam')[0]['ConCho'];
    $model->reject('NONE', '  Hồ sơ không hợp lệ  ');
    expectApproval($model->find('NONE')['LyDoTuChoi'] === 'Hồ sơ không hợp lệ' && (int) $registration->rooms('Nam')[0]['ConCho'] === $before + 1, 'Từ chối lưu lý do và giải phóng một chỗ');
    expectApproval(deniedApproval(fn() => $model->reject('NONE', 'lần nữa')) && deniedApproval(fn() => $model->approve('NONE')), 'Đơn Từ chối không được duyệt/từ chối lại');
    expectApproval(deniedApproval(fn() => $model->approve('MISSING')), 'Đơn không tồn tại bị chặn');
    expectApproval(deniedApproval(fn() => $model->approve('WAR'), 'học kỳ hiện tại') && !$pdo->inTransaction(), 'Thiếu kỳ hiện tại: rollback, không tạo tài khoản');
    expectApproval((int) $pdo->query('SELECT COUNT(*) FROM taikhoan')->fetchColumn() === 0, 'Không tạo tài khoản một phần khi thiếu dữ liệu hóa đơn');
    $today = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
    $year = (int) $today->format('Y');
    $stmt = $pdo->prepare('INSERT INTO hocky VALUES (?,?,?,?,?)');
    $stmt->execute(['CUR','Học kỳ 3', "$year-" . ($year+1), "$year-01-01", "$year-12-31"]);
    expectApproval(deniedApproval(fn() => $model->approve('WAR'), 'kế tiếp'), 'Thiếu kỳ kế tiếp bị chặn');
    $stmt->execute(['NEXT','Học kỳ 1', ($year+1) . '-' . ($year+2), ($year+1) . '-01-01', ($year+1) . '-06-30']);
    expectApproval(deniedApproval(fn() => $model->approve('WAR'), 'đơn giá'), 'Thiếu đơn giá bị chặn');
    $stmt = $pdo->prepare("INSERT INTO dongia VALUES (?, 'Tiền phòng', ?, 'đồng/học kỳ', ?)");
    $stmt->execute(['OLD','1000000.00','2020-01-01']);
    $stmt->execute(['RATE','1500000.25',$today->format('Y-m-d')]);
    $stmt->execute(['FUTURE','9999999.00',$today->modify('+2 days')->format('Y-m-d')]);
    fixtureApplication($pdo, 'ONLY','UT00','FULL');
    expectApproval(!in_array('FULL', array_column($registration->rooms('Nam'), 'MaPhong'), true), 'Phòng đủ chỗ giữ bị loại khỏi danh sách UC02');
    $result = $model->approve('ONLY');
    expectApproval(!in_array('FULL', array_column($registration->rooms('Nam'), 'MaPhong'), true), 'Duyệt phòng đã đầy do giữ chỗ không làm tăng ConCho');
    $account = $pdo->query("SELECT * FROM taikhoan WHERE TenDangNhap='ONLY'")->fetch();
    expectApproval($account['VaiTro'] === 'Sinh viên' && password_verify('1', $account['MatKhau']) && $account['MatKhau'] !== '1', 'Tài khoản MSSV, vai trò đúng, mật khẩu được băm');
    $student = $pdo->query("SELECT * FROM sinhvien WHERE MSSV='ONLY'")->fetch();
    expectApproval($student['TrangThaiLuuTru'] === 'Chờ thanh toán' && (int) $student['MaTK'] === (int) $account['MaTK'], 'Hồ sơ liên kết tài khoản và Chờ thanh toán');
    $invoice = $pdo->query('SELECT h.*, p.MaHocKy, p.MaPhong, p.MaDonGia FROM hoadon h JOIN hoadontienphong p ON p.MaHoaDon=h.MaHoaDon')->fetch();
    expectApproval($invoice['MaHocKy'] === 'NEXT' && $invoice['TongTien'] === '1500000.25' && $invoice['MaDonGia'] === 'RATE' && $invoice['TrangThai'] === 'Chưa thu', 'Thu trọn kỳ kế tiếp qua năm mới, giá hiệu lực chính xác DECIMAL');
    $pdo->exec("UPDATE dongia SET DonViTinh='đồng/tháng' WHERE MaDonGia='RATE'");
    expectApproval(deniedApproval(fn() => $model->billingContext(), 'đơn vị'), 'Không coi giá theo tháng là giá trọn học kỳ');
    $pdo->exec("UPDATE dongia SET DonViTinh='đồng/học kỳ' WHERE MaDonGia='RATE'");
    $stmt = $pdo->prepare('INSERT INTO hocky VALUES (?,?,?,?,?)');
    $stmt->execute(['OVERLAP','Kỳ trùng', "$year-" . ($year+1), "$year-01-01", "$year-12-31"]);
    expectApproval(deniedApproval(fn() => $model->billingContext(), 'đúng một'), 'Không xác định kỳ thu khi có nhiều kỳ hiện tại');
    $pdo->exec("DELETE FROM hocky WHERE MaHocKy='OVERLAP'");
    $stmt->execute(['OVERLAP','Kỳ trùng', ($year+1) . '-' . ($year+2), ($year+1) . '-02-01', ($year+1) . '-07-31']);
    expectApproval(deniedApproval(fn() => $model->billingContext(), 'chồng lấn'), 'Kỳ kế tiếp chồng lấn bị chặn');
    $pdo->exec("DELETE FROM hocky WHERE MaHocKy='OVERLAP'");
    expectApproval($model->find('ONLY')['MaHoaDon'] === $result['invoice'] && $model->find('ONLY')['MaHopDong'] === null && (int) $pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn() === 0, 'Đơn liên kết hóa đơn, chưa tạo hợp đồng');
    expectApproval(deniedApproval(fn() => $model->approve('ONLY')) && deniedApproval(fn() => $model->reject('ONLY', 'test')), 'Xử lý lặp không tạo thêm tài khoản/hóa đơn hoặc đảo kết quả');
    $dashboard = (new StudentDashboard($pdo))->forAccount((int) $account['MaTK']);
    expectApproval($dashboard['invoices'] === 1 && $dashboard['contract'] === 'Chưa có hợp đồng', 'Dashboard sinh viên nhận hóa đơn đầu tiên và chưa có hợp đồng');
    fixtureApplication($pdo,'DUPSV','UT00','P1','ONLY');
    expectApproval(deniedApproval(fn() => $model->approve('DUPSV'), 'MSSV') && $registration->studentConflict('ONLY','123') !== null, 'UC02/UC03 chặn MSSV đã có hồ sơ');
    fixtureApplication($pdo,'DUPCCCD','UT00','P1',null,$student['CCCD']);
    expectApproval(deniedApproval(fn() => $model->approve('DUPCCCD'), 'CCCD') && str_starts_with($registration->studentConflict('NEW', $student['CCCD']), 'CCCD'), 'UC02/UC03 chặn CCCD đã có hồ sơ');
    $stmt = $pdo->prepare("INSERT INTO taikhoan (TenDangNhap,MatKhau,VaiTro) VALUES (?,?,'Sinh viên')");
    $stmt->execute(['COLLISION',password_hash('test',PASSWORD_DEFAULT)]);
    fixtureApplication($pdo,'COLLISION');
    expectApproval(deniedApproval(fn() => $model->approve('COLLISION'), 'Tên đăng nhập'), 'Tên đăng nhập tồn tại không bị ghi đè');
    fixtureApplication($pdo,'DUP1','UT00','P1','SAME');fixtureApplication($pdo,'DUP2','UT00','P1','SAME');
    expectApproval(deniedApproval(fn() => $model->approve('DUP1'), 'nhiều đơn'), 'Dữ liệu nhiều đơn chờ cùng MSSV bị chặn');
    fixtureApplication($pdo,'BADGENDER','UT00','WRONG');
    expectApproval(deniedApproval(fn() => $model->approve('BADGENDER'), 'giới tính'), 'Đọc lại giới tính phòng khi duyệt');
    fixtureApplication($pdo,'OVERFLOW','UT00','FULL');
    expectApproval(deniedApproval(fn() => $model->approve('OVERFLOW'), 'sức chứa'), 'Dữ liệu giữ chỗ vượt sức chứa bị chặn');
    $pdo->exec("ALTER TABLE hoadontienphong ADD CONSTRAINT test_invoice_failure CHECK (MSSV <> 'FAIL')");
    fixtureApplication($pdo,'FAIL');
    try { $model->approve('FAIL'); throw new RuntimeException('Cần lỗi INSERT hóa đơn con'); } catch (PDOException $e) {}
    expectApproval((int) $pdo->query("SELECT COUNT(*) FROM taikhoan WHERE TenDangNhap='FAIL'")->fetchColumn() === 0
        && (int) $pdo->query("SELECT COUNT(*) FROM sinhvien WHERE MSSV='FAIL'")->fetchColumn() === 0
        && (int) $pdo->query('SELECT COUNT(*) FROM hoadon')->fetchColumn() === 1
        && $model->find('FAIL')['TrangThai'] === 'Chờ duyệt' && !$pdo->inTransaction(), 'Lỗi hóa đơn con rollback tài khoản, sinh viên, hóa đơn cha, đơn');
    $lockName = 'kanto_uc02_' . substr(hash('sha256', (string) $pdo->query('SELECT DATABASE()')->fetchColumn()),0,32);
    $second = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $stmt = $second->prepare('SELECT GET_LOCK(?,0)');$stmt->execute([$lockName]);
    expectApproval((int) $stmt->fetchColumn() === 1, 'Transaction thành công/thất bại đều giải phóng named lock cho kết nối khác');
    $stmt = $second->prepare('SELECT RELEASE_LOCK(?)');$stmt->execute([$lockName]);
    expectApproval(Approval::proof(null)['status'] === 'none' && Approval::proof('storage/registration/' . str_repeat('a',48) . '.png')['status'] === 'missing', 'Phân biệt không nộp ảnh và tệp không còn');
    expectApproval(Approval::proof('storage/registration/../../config/db.php')['status'] === 'missing' && Approval::proof('config/db.php')['status'] === 'missing', 'Không đọc file ngoài storage qua đường dẫn minh chứng');
    $proofDir = __DIR__ . '/../storage/registration';
    if (!is_dir($proofDir)) { mkdir($proofDir, 0700, true); }
    $proofName = bin2hex(random_bytes(24)) . '.png';
    $proofPath = $proofDir . '/' . $proofName;
    file_put_contents($proofPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jx1sAAAAASUVORK5CYII='));
    try {
        expectApproval(Approval::proof('storage/registration/' . $proofName)['status'] === 'available', 'Ảnh UC02 hợp lệ được đọc qua helper bảo vệ');
    } finally { unlink($proofPath); }
    $_SESSION['user'] = ['MaTK' => 99999, 'TenDangNhap' => 'NVQL'];
    $controller = new ApprovalController($pdo);
    $html = approvalPage($controller,'approval');
    expectApproval(str_contains($html,'Xem chi tiết') && str_contains($html,'&lt;script&gt;test&lt;/script&gt;') && !str_contains($html,'<script>test</script>'), 'Danh sách hiển thị và escape dữ liệu hồ sơ');
    $html = approvalPage($controller,'approval-detail',['id'=>'WAR']);
    expectApproval(str_contains($html,'Không nộp ảnh minh chứng') && str_contains($html,'Học kỳ 1'), 'Chi tiết có minh chứng thiếu và hóa đơn kỳ kế tiếp');
    $html = approvalPage($controller,'approval-detail',['id'=>[]]);
    expectApproval(http_response_code() === 404, 'Mã đơn dạng mảng bị chặn');
    $token = $_SESSION['approval_token'];
    approvalPage($controller,'approval-reject',[],['id'=>'WAR','csrf_token'=>'bad','reason'=>'test']);
    expectApproval(http_response_code() === 403 && $model->find('WAR')['TrangThai'] === 'Chờ duyệt', 'CSRF sai không từ chối đơn');
    approvalPage($controller,'approval-approve',[],['id'=>'WAR','csrf_token'=>[]]);
    expectApproval(http_response_code() === 403, 'CSRF dạng mảng bị chặn');
    approvalPage($controller,'approval-approve',['id'=>'WAR']);
    expectApproval(http_response_code() === 405, 'GET không được duyệt đơn');
    approvalPage($controller,'approval-reject',[],['id'=>'WAR','csrf_token'=>$token,'reason'=>[]]);
    expectApproval(http_response_code() === 409 && $model->find('WAR')['TrangThai'] === 'Chờ duyệt', 'Lý do dạng mảng không cập nhật đơn');
    approvalPage($controller,'approval-reject',[],['id'=>'WAR','csrf_token'=>$token,'reason'=>'Không đủ điều kiện']);
    expectApproval(http_response_code() === 303 && $model->find('WAR')['TrangThai'] === 'Từ chối' && $_SESSION['approval_token'] !== $token, 'POST từ chối chuyển hướng 303 và xoay token');
    fixtureApplication($pdo,'UIAPPROVE');
    approvalPage($controller,'approval-approve',[],['id'=>'UIAPPROVE','csrf_token'=>$_SESSION['approval_token'],'TongTien'=>'1']);
    expectApproval(http_response_code() === 303 && $model->find('UIAPPROVE')['TrangThai'] === 'Đã duyệt', 'POST duyệt thành công, không dùng số tiền do client gửi');
    $token = $_SESSION['approval_token'];
    approvalPage($controller,'approval-approve',[],['id'=>'UIAPPROVE','csrf_token'=>$token]);
    expectApproval(http_response_code() === 409 && (int) $pdo->query("SELECT COUNT(*) FROM taikhoan WHERE TenDangNhap='UIAPPROVE'")->fetchColumn() === 1, 'POST lặp với token mới vẫn không tạo thêm tài khoản');
    $pdo->exec("UPDATE dondangky SET TrangThai='Từ chối' WHERE MaDonDangKy='OVERFLOW'");
    $pdo->beginTransaction();
    $pdo->exec("INSERT INTO hopdong (MaHopDong,MSSV,MaPhong,NgayBatDau,NgayKetThuc,TrangThai) VALUES ('HONLY','ONLY','FULL','2026-01-01','2026-12-31','Còn hạn')");
    $pdo->exec("UPDATE dondangky SET MaHopDong='HONLY' WHERE MaDonDangKy='ONLY'");
    $pdo->commit();
    expectApproval(!in_array('FULL',array_column($registration->rooms('Nam'),'MaPhong'),true), 'Mô phỏng chuyển giữ chỗ sang hợp đồng không đếm hai lần');
    $pdo->exec("UPDATE hopdong SET TrangThai='Đã thanh lý' WHERE MaHopDong='HONLY'");
    expectApproval(in_array('FULL',array_column($registration->rooms('Nam'),'MaPhong'),true), 'Đơn đã liên kết hợp đồng thanh lý không giữ chỗ lại');
    $new = fixtureApplication($pdo,'NEWROOM');
    $registerData = $new;
    unset($registerData['MaDonDangKy']);
    $registerData['MSSV'] = 'ONLY';
    expectApproval(deniedApproval(fn() => $registration->submit($registerData,'P1'),'MSSV'), 'UC02 submit kiểm tra lại MSSV đã có sinh viên dưới khóa');
    $registerData['MSSV'] = 'FRESH'; $registerData['CCCD'] = $student['CCCD'];
    expectApproval(deniedApproval(fn() => $registration->submit($registerData,'P1'),'CCCD'), 'UC02 submit kiểm tra lại CCCD đã có sinh viên dưới khóa');
    echo "\n$passed kiểm thử UC03 đạt.\n";
    ob_end_flush();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR, '[FAIL] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
