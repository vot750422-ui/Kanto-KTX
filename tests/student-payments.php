<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
session_set_save_handler(fn($p,$n)=>true, fn()=>true, fn($id)=>'', fn($id,$data)=>true, fn($id)=>true, fn($ttl)=>0);
require_once __DIR__ . '/../middleware/Role.php';
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Approval.php';
require_once __DIR__ . '/../controllers/StudentPaymentController.php';
require_once __DIR__ . '/../controllers/PaymentCheckoutController.php';
$checks = [];
function uc17preview(string $name, string $html): void {
    global $argv;
    if (($argv[1] ?? '') === '--preview-dir' && isset($argv[2]) && is_dir($argv[2])) {
        file_put_contents($argv[2] . DIRECTORY_SEPARATOR . $name . '.html', $html);
    }
}
function uc17check(bool $ok, string $label): void {
    global $checks;
    if (!$ok) { throw new RuntimeException($label); }
    $checks[] = $label;
}
function uc17deny(callable $call): bool {
    try { $call(); return false; } catch (DomainException $e) { return true; }
}
function uc17request(string $action, array $get = [], array $post = [], string $method = 'GET'): array {
    $_GET = ['action' => $action] + $get;
    $_POST = $post;
    $_SERVER['REQUEST_METHOD'] = $method;
    http_response_code(200);
    ob_start();
    try {
        $controller = str_starts_with($action, 'payment-checkout') ? new PaymentCheckoutController() : new StudentPaymentController();
        $controller->handle($action);
        return [http_response_code(), ob_get_contents()];
    }
    finally { ob_end_clean(); }
}
try {
    $pdo->exec(str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TEMPORARY TABLE', file_get_contents(__DIR__ . '/../sql/uc03-email.sql')));
    foreach (['toanha','phong','dienuutien','dondangky','sinhvien','taikhoan','hopdong','hoadon','hoadontienphong','hocky','dongia','hoadondiennuocsinhvien','hoadondiennuoc'] as $table) {
        $ddl = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $ddl = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $ddl);
        $ddl = preg_replace('/,\s*\n\)/', "\n)", $ddl);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $ddl));
    }
    $ddl = file_get_contents(__DIR__ . '/../sql/uc17-payment-links.sql');
    $ddl = preg_replace('/^\s*CONSTRAINT .*\r?\n/m', '', $ddl);
    $ddl = preg_replace('/,\s*\n\)/', "\n)", $ddl);
    $pdo->exec(str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TEMPORARY TABLE', $ddl));
    $pdo->exec("INSERT INTO toanha VALUES ('T1','Tòa A')");
    $pdo->exec("INSERT INTO phong VALUES ('P1','T1','A101',1,'Nam',8)");
    $pdo->exec(file_get_contents(__DIR__ . '/../sql/update-priorities.sql'));
    $year = (int)date('Y'); $next = $year + 1;
    $pdo->exec("INSERT INTO hocky VALUES ('CUR','Kỳ hiện tại','$year','$year-01-01','$year-12-31'),('NEXT','Kỳ kế tiếp','$next','$next-01-01','$next-12-31')");
    $pdo->exec("INSERT INTO dongia VALUES ('RATE','Tiền phòng',200000,'đồng/tháng','2020-01-01')");
    $approval = new Approval($pdo);
    $model = new Payment($pdo);
    $create = function (string $id, int $serial) use ($pdo, $approval): array {
        $stmt = $pdo->prepare('INSERT INTO dondangky (MaDonDangKy,MSSV,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Lop,NienKhoa,MaUuTien,MaPhong) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$id,$id,'Tên <script>x</script>','2005-01-01',sprintf('%012d',$serial),'Nam','Test','0900000000','TEST','2024-2028','UT00','P1']);
        $result = $approval->approve($id);
        $stmt = $pdo->prepare('SELECT MaTK FROM sinhvien WHERE MSSV=?'); $stmt->execute([$id]);
        $result['account'] = (int)$stmt->fetchColumn();
        return $result;
    };
    $first = $create('STUDENT1',1); $other = $create('STUDENT2',2);
    $_SESSION['user'] = ['MaTK' => $first['account']];
    $bill = $model->studentInvoice($first['invoice'],$first['account']);
    $amount = (string)$bill['TongTien'];
    uc17check($model->studentInvoice($other['invoice'],$first['account']) === null, 'Không đọc hóa đơn người khác');
    uc17check($model->studentInvoice("' OR 1=1 --",$first['account']) === null, 'Mã hóa đơn dùng prepared statement');
    uc17check($model->studentForAccount(999999) === null, 'Tài khoản thiếu hồ sơ không nhận hóa đơn');
    uc17check(uc17deny(fn() => $model->confirmForStudent($other['invoice'],$first['account'],$amount)), 'Model từ chối thanh toán hộ hóa đơn cá nhân');
    [$code,$html] = uc17request('student-payments',['MSSV'=>'STUDENT2','MaTK'=>$other['account']]);
    uc17preview('list', $html);
    uc17check($code===200 && str_contains($html,$first['invoice']) && !str_contains($html,$other['invoice']), 'Danh sách lấy sinh viên từ session, bỏ qua MSSV/MaTK giả');
    uc17check(str_contains($html,'&lt;script&gt;x&lt;/script&gt;') && !str_contains($html,'<script>x</script>'), 'Escape tên sinh viên trong giao diện');
    uc17check(!str_contains($html,'Chế độ mô phỏng phục vụ đồ án') && str_contains($html,'class="payment-view-button"'), 'Bỏ banner và hiển thị Xem hóa đơn như nút');
    uc17check(str_contains($html,'aria-current="page"'), 'Menu thanh toán sinh viên được kích hoạt');
    uc17check(uc17request('student-payments',['status'=>[]])[0]===400, 'Chặn bộ lọc dạng mảng');
    uc17check(uc17request('student-payments',['page'=>'0'])[0]===400, 'Chặn trang không hợp lệ');
    uc17check(uc17request('student-invoice',['invoice'=>[]])[0]===400, 'Chặn mã hóa đơn dạng mảng');
    uc17check(uc17request('student-invoice',['invoice'=>$other['invoice']])[0]===404, 'Route chi tiết chặn hóa đơn người khác');
    uc17check(uc17request('student-payment-confirm',['invoice'=>$first['invoice']])[0]===405, 'GET không được xác nhận');
    uc17check(uc17request('student-qr-create',['invoice'=>$first['invoice']])[0]===405, 'GET không được tạo mã QR');
    [$code,$html] = uc17request('student-invoice',['invoice'=>$first['invoice']]);
    uc17preview('detail', $html);
    uc17check($code===200 && str_contains($html,'data-qr-create') && !str_contains($html,'data-payment-qr=') && !str_contains($html,'data-payment-confirm'), 'Mở chi tiết chỉ thấy nút tạo, chưa sinh mã QR');
    uc17check((int)$pdo->query('SELECT COUNT(*) FROM phienthanhtoan')->fetchColumn()===0, 'Tải lại chi tiết không tạo phiên QR');
    $qrPost = ['invoice'=>$first['invoice']];
    uc17check(uc17request('student-qr-create',[],$qrPost,'POST')[0]===303, 'Bấm Tạo mã QR tạo phiên và chuyển về chi tiết');
    [$code,$html] = uc17request('student-invoice',['invoice'=>$first['invoice']]);
    uc17preview('qr', $html);
    uc17check($code===200 && str_contains($html,'data-payment-qr=') && !str_contains($html,'data-qr-create'), 'Sau POST mới hiện mã QR');
    $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($dom);
    $firstQrUrl = $xpath->query('//*[@data-payment-qr]')->item(0)->getAttribute('data-payment-qr');
    $firstQrCount = (int)$pdo->query('SELECT COUNT(*) FROM phienthanhtoan')->fetchColumn();
    [$code,$html] = uc17request('student-invoice',['invoice'=>$first['invoice']]);
    $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($dom);
    uc17check($code===200 && $xpath->query('//*[@data-payment-qr]')->item(0)->getAttribute('data-payment-qr')===$firstQrUrl && (int)$pdo->query('SELECT COUNT(*) FROM phienthanhtoan')->fetchColumn()===$firstQrCount, 'Tải lại trang hiển thị lại cùng QR, không tạo mã mới');
    [$code,$html] = uc17request('student-payment',['invoice'=>$first['invoice']]);
    uc17preview('confirm', $html);
    $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($dom);
    $token = $_SESSION['student_payment_intents'][$first['invoice']]['token'];
    uc17check($code===200 && str_contains($html,'data-payment-confirm') && str_contains($html,$token), 'Trang mô phỏng tạo phiên gắn đúng hóa đơn');
    uc17check($model->studentInvoice($first['invoice'],$first['account'])['TrangThai']==='Chưa thu' && (int)$pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn()===0, 'Mở chi tiết/quét QR không thu tiền hoặc tạo hợp đồng');
    $post = ['invoice'=>$first['invoice'],'transaction_token'=>$token];
    uc17check(uc17request('student-payment-confirm',[],array_replace($post,['transaction_token'=>'fake']),'POST')[0]===409, 'Mã giao dịch sai bị từ chối');
    uc17check(uc17deny(fn()=>StudentPaymentIntent::amount($first['invoice'],$other['account'],$token)), 'Phiên thanh toán không dùng cho tài khoản khác');
    $_SESSION['student_payment_intents'][$first['invoice']]['expires'] = time()-1;
    uc17check(uc17request('student-payment-confirm',[],$post,'POST')[0]===409, 'Phiên hết hạn bị từ chối');
    uc17request('student-payment',['invoice'=>$first['invoice']]);
    $post['transaction_token'] = $_SESSION['student_payment_intents'][$first['invoice']]['token'];
    $stmt = $pdo->prepare('UPDATE hoadon SET TongTien=TongTien+1 WHERE MaHoaDon=?'); $stmt->execute([$first['invoice']]);
    uc17check(uc17request('student-payment-confirm',[],$post,'POST')[0]===409, 'Số tiền thay đổi phải xem và xác nhận lại');
    $stmt = $pdo->prepare('UPDATE hoadon SET TongTien=? WHERE MaHoaDon=?'); $stmt->execute([$amount,$first['invoice']]);
    uc17check(uc17request('student-payment-confirm',[], $post + ['amount'=>'1','MSSV'=>'STUDENT2'],'POST')[0]===303, 'POST hợp lệ thu đúng số tiền từ server và redirect');
    $paid = $model->studentInvoice($first['invoice'],$first['account']);
    uc17check($paid['TrangThai']==='Đã thu' && $paid['ThoiGianThanhToan']!==null && $paid['TongTien']===$amount, 'Ghi trạng thái, thời gian và giữ tổng tiền');
    $contract = $pdo->query("SELECT * FROM hopdong WHERE MSSV='STUDENT1'")->fetch();
    uc17check($contract && $contract['NgayBatDau']==="$next-01-01" && $contract['NgayKetThuc']==="$next-12-31", 'Hợp đồng đầu tiên theo học kỳ trên hóa đơn');
    uc17check($pdo->query("SELECT MaHopDong FROM dondangky WHERE MSSV='STUDENT1'")->fetchColumn()===$contract['MaHopDong'] && $pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV='STUDENT1'")->fetchColumn()==='Đang ở', 'Liên kết đơn và cập nhật sinh viên cùng transaction');
    uc17check(uc17request('student-payment-confirm',[],$post,'POST')[0]===409 && uc17deny(fn()=>$model->confirm($first['invoice'])), 'Sinh viên gửi lại hoặc kế toán xác nhận sau đều bị chặn');
    [$code,$html] = uc17request('student-payment',['invoice'=>$first['invoice']]);
    uc17preview('paid', $html);
    uc17check($code===200 && substr_count($html,'class="payment-card"')===1 && str_contains($html,'payment-detail-grid is-paid') && str_contains($html,'Hóa đơn ' . $first['invoice']) && !str_contains($html,'Đã ghi nhận thanh toán') && !str_contains($html,'data-payment-confirm') && !str_contains($html,'data-payment-qr='), 'Hóa đơn đã thu chỉ hiện một thẻ chi tiết, không hiện thẻ QR/xác nhận');
    $model->confirm($other['invoice']);
    uc17check(uc17deny(fn()=>$model->confirmForStudent($other['invoice'],$other['account'],$amount)), 'Kế toán thu trước thì sinh viên không được thu lại');
    $pdo->exec("INSERT INTO hoadon (MaHoaDon,LoaiHoaDon,TongTien) VALUES ('UTIL17','Điện nước',100000),('PERIOD17','Tiền phòng',200000)");
    $pdo->exec("INSERT INTO hoadondiennuoc VALUES ('UTIL17','P1',10,2026,10,2,80000,20000,'RATE','RATE')");
    $pdo->exec("INSERT INTO hoadondiennuocsinhvien (MaHoaDon,MSSV,SoTienPhaiThu,TrangThai) VALUES ('UTIL17','STUDENT1',50000,'Chưa thu'),('UTIL17','STUDENT2',50000,'Chưa thu')");
    $pdo->exec("INSERT INTO hoadontienphong VALUES ('PERIOD17','STUDENT1','P1','CUR','RATE')");
    $contracts = (int)$pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn();
    uc17check($model->studentInvoice('UTIL17',$other['account'])!==null, 'Sinh viên liên quan được xem hóa đơn chung của phòng');
    uc17check($model->confirmForStudent('UTIL17',$first['account'],'50000.00')['contract']===null && $pdo->query("SELECT TrangThai FROM hoadondiennuocsinhvien WHERE MaHoaDon='UTIL17' AND MSSV='STUDENT1'")->fetchColumn()==='Đã thu', 'Thu phần điện nước của một sinh viên không tạo hợp đồng');
    uc17check($model->confirmForStudent('UTIL17',$other['account'],'50000.00')['contract']===null && $pdo->query("SELECT TrangThai FROM hoadon WHERE MaHoaDon='UTIL17'")->fetchColumn()==='Đã thu', 'Sinh viên khác thanh toán độc lập và hóa đơn phòng chỉ hoàn tất khi đủ các phần');
    uc17check($model->confirmForStudent('PERIOD17',$first['account'],'200000.00')['contract']===null && (int)$pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn()===$contracts, 'Hóa đơn phòng định kỳ chỉ thu tiền, không tạo/gia hạn hợp đồng');
    $third = $create('STUDENT3',3);
    $pdo->exec("UPDATE taikhoan SET TrangThai='Khóa' WHERE MaTK=".$third['account']);
    uc17check(uc17deny(fn()=>$model->confirmForStudent($third['invoice'],$third['account'],$amount)), 'Model chặn tài khoản đã khóa');
    $pdo->exec("UPDATE taikhoan SET TrangThai='Hoạt động' WHERE MaTK=".$third['account']);
    $pdo->exec("DELETE FROM hoadon WHERE TrangThai='Đã thu'");
    $pdo->exec("ALTER TABLE hoadon ADD CONSTRAINT uc17_failure CHECK (TrangThai='Chưa thu')");
    $failed = false;
    try { $model->confirmForStudent($third['invoice'],$third['account'],$amount); } catch (PDOException $e) { $failed = true; }
    uc17check($failed && (int)$pdo->query("SELECT COUNT(*) FROM hopdong WHERE MSSV='STUDENT3'")->fetchColumn()===0 && $pdo->query("SELECT MaHopDong FROM dondangky WHERE MSSV='STUDENT3'")->fetchColumn()===null && $pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV='STUDENT3'")->fetchColumn()==='Chờ thanh toán', 'Lỗi thu tiền rollback hợp đồng, đơn, trạng thái sinh viên');
    $pdo->exec('ALTER TABLE hoadon DROP CHECK uc17_failure');
    uc17check($model->confirmForStudent($third['invoice'],$third['account'],$amount)['contract']!==null, 'Có thể thanh toán lại sau rollback');
    $fourth = $create('QR4',4); $fifth = $create('QR5',5);
    $links = new PaymentLink($pdo);
    uc17check(uc17deny(fn()=>$links->issue($fourth['invoice'],$fifth['account'])), 'Không tạo liên kết QR cho hóa đơn người khác');
    $_SESSION['user'] = ['MaTK'=>$fourth['account']];
    [$code,$html] = uc17request('student-invoice',['invoice'=>$fourth['invoice']]);
    uc17check($code===200 && str_contains($html,'data-qr-create') && !str_contains($html,'data-payment-qr='), 'Hóa đơn mới chưa hiển thị QR trước khi bấm tạo');
    $fourthPost = ['invoice'=>$fourth['invoice']];
    uc17check(uc17request('student-qr-create',[],$fourthPost,'POST')[0]===303, 'Tạo QR cho hóa đơn cần thanh toán');
    [$code,$html] = uc17request('student-invoice',['invoice'=>$fourth['invoice']]);
    uc17preview('public-source',$html);
    $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($dom);
    $linkUrl = $xpath->query('//*[@data-payment-qr]')->item(0)->getAttribute('data-payment-qr');
    $raw = parse_url($linkUrl, PHP_URL_FRAGMENT);
    uc17check($code===200 && str_contains($linkUrl,'action=payment-checkout#') && !str_contains($linkUrl,'invoice=') && strlen($raw)===64, 'QR trên trang sinh viên trỏ sang trang riêng bằng token fragment');
    $state = $links->current($fourth['invoice'],$fourth['account'],$raw);
    uc17check($state['active'] && $state['token']===$raw, 'Cùng tài khoản và trình duyệt đọc lại token đang hiệu lực');
    $stmt = $pdo->prepare('SELECT TIMESTAMPDIFF(SECOND,TaoLuc,HetHanLuc) FROM phienthanhtoan WHERE MaPhien=?');
    $stmt->execute([hash('sha256',$raw)]);
    uc17check((int)$stmt->fetchColumn()===300, 'Mã QR mới có hạn đúng 5 phút');
    $pdo->prepare('UPDATE phienthanhtoan SET TaoLuc=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE MaPhien=?')->execute([hash('sha256',$raw)]);
    $extra = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('INSERT INTO phienthanhtoan (MaPhien,MaHoaDon,MaTK,SoTien,HetHanLuc) VALUES (?,?,?,?,DATE_ADD(NOW(),INTERVAL 5 MINUTE))');
    $stmt->execute([hash('sha256',$extra),$fourth['invoice'],$fourth['account'],$amount]);
    uc17check(uc17deny(fn()=>$links->resolve($raw)) && uc17deny(fn()=>$model->confirmLink($raw)) && $links->resolve($extra)['MaHoaDon']===$fourth['invoice'], 'Nếu dữ liệu cũ có mã trùng, chỉ mã mới nhất dùng được');
    uc17check($links->issue($fourth['invoice'],$fourth['account'],$raw)===$raw && uc17deny(fn()=>$links->resolve($extra)), 'Tạo lại dùng mã cũ và vô hiệu hóa mã trùng sẵn có');
    uc17check($links->issue($fourth['invoice'],$fourth['account'])===null, 'Trình duyệt khác không tạo được mã khi QR đang có hiệu lực');
    $activeCount = $pdo->prepare('SELECT COUNT(*) FROM phienthanhtoan WHERE MaHoaDon=? AND DaDungLuc IS NULL AND HetHanLuc>NOW()');
    $activeCount->execute([$fourth['invoice']]);
    uc17check((int)$activeCount->fetchColumn()===1, 'Chỉ còn một QR hiệu lực cho hóa đơn');
    $ownerSession = $_SESSION;
    $_SESSION = ['user'=>['MaTK'=>$fourth['account']]];
    [$code,$html] = uc17request('student-invoice',['invoice'=>$fourth['invoice']]);
    uc17check($code===200 && !str_contains($html,'data-payment-qr=') && !str_contains($html,'data-qr-create') && str_contains($html,'đang có mã QR'), 'Trình duyệt khác chỉ thấy thông báo mã đang hoạt động');
    $secondBrowserPost = ['invoice'=>$fourth['invoice']];
    uc17check(uc17request('student-qr-create',[],$secondBrowserPost,'POST')[0]===303 && $links->issue($fourth['invoice'],$fourth['account'])===null, 'Bấm tạo từ trình duyệt khác không phát hành QR thứ hai');
    $_SESSION = $ownerSession;
    $otherRaw = $links->issue($fifth['invoice'],$fifth['account']);
    $hash = hash('sha256',$raw);
    $stmt = $pdo->prepare('SELECT MaPhien FROM phienthanhtoan WHERE MaPhien=?'); $stmt->execute([$hash]);
    uc17check(strlen($raw)===64 && $stmt->fetchColumn()===$hash && $raw!==$hash, 'CSDL chỉ lưu hash của token QR ngẫu nhiên 256 bit');
    uc17check(uc17deny(fn()=>$links->resolve(str_repeat('0',64))) && uc17deny(fn()=>$links->resolve([])), 'Token giả hoặc dạng mảng không tiết lộ hóa đơn');
    $_SESSION = []; // A completely separate phone/browser: no student login or prior session.
    [$code,$html] = uc17request('payment-checkout',['invoice'=>$fourth['invoice']]);
    uc17preview('public-open',$html);
    uc17check($code===200 && str_contains($html,'data-checkout-open') && !str_contains($html,$fourth['invoice']), 'Điện thoại chưa đăng nhập mở được trang QR, chỉ mã hóa đơn không đủ quyền xem');
    $publicPost = ['link_token'=>$raw];
    uc17check(uc17request('payment-checkout-confirm')[0]===405, 'GET trang công khai không được xác nhận');
    [$code,$html] = uc17request('payment-checkout',[],$publicPost,'POST');
    uc17preview('public-confirm',$html);
    uc17check($code===200 && str_contains($html,$fourth['invoice']) && str_contains($html,'data-checkout-confirm') && !isset($_SESSION['user']), 'Token hợp lệ mở trang xác nhận trên phiên điện thoại mới');
    uc17check(!str_contains($html,'QR4') && !str_contains($html,'Tên') && !str_contains($html,'A101') && !str_contains($html,'dashboard-sidebar'), 'Trang QR chỉ hiển thị hóa đơn/số tiền, không hồ sơ/phòng/menu cá nhân');
    uc17check($model->studentInvoice($fourth['invoice'],$fourth['account'])['TrangThai']==='Chưa thu', 'Mở QR/trao đổi token không thu tiền');
    $beforeContracts = (int)$pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn();
    $postTampered = $publicPost + ['invoice'=>$fifth['invoice'],'MaTK'=>$fifth['account'],'amount'=>'1'];
    uc17check(uc17request('payment-checkout-confirm',[],$postTampered,'POST')[0]===303, 'Không đăng nhập vẫn xác nhận bằng token, bỏ qua invoice/tài khoản/số tiền giả');
    uc17check($model->studentInvoice($fourth['invoice'],$fourth['account'])['TrangThai']==='Đã thu' && $model->studentInvoice($fifth['invoice'],$fifth['account'])['TrangThai']==='Chưa thu', 'Token chỉ thu đúng hóa đơn đã liên kết');
    uc17check((int)$pdo->query('SELECT COUNT(*) FROM hopdong')->fetchColumn()===$beforeContracts+1 && $pdo->query("SELECT TrangThaiLuuTru FROM sinhvien WHERE MSSV='QR4'")->fetchColumn()==='Đang ở', 'QR công khai dùng cùng transaction tạo hợp đồng đầu tiên');
    $stmt = $pdo->prepare('SELECT DaDungLuc FROM phienthanhtoan WHERE MaPhien=?'); $stmt->execute([$hash]);
    uc17check($stmt->fetchColumn()!==null, 'Token được đánh dấu đã dùng cùng transaction');
    $receiptId = array_key_last($_SESSION['checkout_receipts']);
    [$code,$html] = uc17request('payment-checkout',['receipt'=>$receiptId]);
    uc17preview('public-paid',$html);
    uc17check($code===200 && str_contains($html,'Thanh toán thành công') && !str_contains($html,'data-checkout-confirm'), 'Kết quả thanh toán mở được trên điện thoại, không cần đăng nhập');
    uc17check(uc17request('payment-checkout-confirm',[],$publicPost,'POST')[0]===409 && uc17deny(fn()=>$model->confirmLink($raw)), 'Token đã dùng bị chặn ở controller và model');
    uc17check(uc17deny(fn()=>$model->confirmLink($extra)) && uc17deny(fn()=>$model->confirm($fourth['invoice'])), 'QR khác của cùng hóa đơn và kế toán không thu lần hai');
    $_SESSION = [];
    uc17check(uc17request('payment-checkout',['receipt'=>$receiptId])[0]===404, 'Kết quả không được xem từ phiên thiết bị khác bằng mã receipt');
    uc17request('payment-checkout');
    $fifthPost = ['link_token'=>$otherRaw];
    $otherHash = hash('sha256',$otherRaw);
    $stmt = $pdo->prepare('UPDATE phienthanhtoan SET HetHanLuc=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE MaPhien=?'); $stmt->execute([$otherHash]);
    uc17check(uc17request('payment-checkout-confirm',[],$fifthPost,'POST')[0]===409 && uc17deny(fn()=>$model->confirmLink($otherRaw)), 'Token hết hạn bị chặn kể cả xác nhận trực tiếp tại model');
    $stmt = $pdo->prepare('UPDATE phienthanhtoan SET HetHanLuc=DATE_ADD(NOW(),INTERVAL 15 MINUTE) WHERE MaPhien=?'); $stmt->execute([$otherHash]);
    $stmt = $pdo->prepare('UPDATE phienthanhtoan SET TaoLuc=DATE_SUB(NOW(),INTERVAL 6 MINUTE) WHERE MaPhien=?'); $stmt->execute([$otherHash]);
    uc17check(uc17deny(fn()=>$links->resolve($otherRaw)) && uc17deny(fn()=>$model->confirmLink($otherRaw)) && !$links->current($fifth['invoice'],$fifth['account'],$otherRaw)['active'], 'Quá 5 phút phải hết hiệu lực dù thời hạn cũ còn dài hơn');
    $stmt = $pdo->prepare('UPDATE phienthanhtoan SET TaoLuc=NOW() WHERE MaPhien=?'); $stmt->execute([$otherHash]);
    $stmt = $pdo->prepare('UPDATE hoadon SET TongTien=TongTien+1 WHERE MaHoaDon=?'); $stmt->execute([$fifth['invoice']]);
    uc17check(uc17deny(fn()=>$model->confirmLink($otherRaw)), 'Số tiền thay đổi sau tạo QR không được thu bằng token cũ');
    $stmt = $pdo->prepare('UPDATE hoadon SET TongTien=? WHERE MaHoaDon=?'); $stmt->execute([$amount,$fifth['invoice']]);
    $pdo->exec("UPDATE taikhoan SET TrangThai='Khóa' WHERE MaTK=".$fifth['account']);
    uc17check(uc17deny(fn()=>$model->confirmLink($otherRaw)), 'QR không vượt qua kiểm tra tài khoản chủ hóa đơn đã khóa');
    $pdo->exec("UPDATE taikhoan SET TrangThai='Hoạt động' WHERE MaTK=".$fifth['account']);
    $pdo->exec("DELETE FROM hoadon WHERE TrangThai='Đã thu'");
    $pdo->exec("ALTER TABLE hoadon ADD CONSTRAINT uc17_link_failure CHECK (TrangThai='Chưa thu')");
    $failed = false;
    try { $model->confirmLink($otherRaw); } catch (PDOException $e) { $failed = true; }
    $stmt = $pdo->prepare('SELECT DaDungLuc FROM phienthanhtoan WHERE MaPhien=?'); $stmt->execute([$otherHash]);
    uc17check($failed && $stmt->fetchColumn()===null && (int)$pdo->query("SELECT COUNT(*) FROM hopdong WHERE MSSV='QR5'")->fetchColumn()===0 && $pdo->query("SELECT MaHopDong FROM dondangky WHERE MSSV='QR5'")->fetchColumn()===null, 'Lỗi cuối transaction rollback cả token, hợp đồng và liên kết đơn');
    $pdo->exec('ALTER TABLE hoadon DROP CHECK uc17_link_failure');
    uc17check(uc17request('payment-checkout-confirm',[],$fifthPost,'POST')[0]===303, 'Có thể dùng lại cùng token sau rollback');
    $sixth = $create('QR6',6);
    $sixthRaw = $links->issue($sixth['invoice'],$sixth['account']);
    $model->confirm($sixth['invoice']);
    uc17check(uc17deny(fn()=>$model->confirmLink($sixthRaw)), 'Kế toán thu trước thì QR cũ mất hiệu lực');
    $pdo->exec("INSERT INTO hoadon (MaHoaDon,LoaiHoaDon,TongTien) VALUES ('UTILQR','Điện nước',100000)");
    $pdo->exec("INSERT INTO hoadondiennuoc VALUES ('UTILQR','P1',11,2026,10,2,80000,20000,'RATE','RATE')");
    $pdo->exec("INSERT INTO hoadondiennuocsinhvien (MaHoaDon,MSSV,SoTienPhaiThu,TrangThai) VALUES ('UTILQR','STUDENT1',50000,'Chưa thu'),('UTILQR','STUDENT2',50000,'Chưa thu')");
    $utilityRaw = $links->issue('UTILQR',$first['account']);
    $secondUtility = $links->issue('UTILQR',$other['account']);
    uc17check($secondUtility!==null && $model->confirmLink($utilityRaw)['contract']===null && $model->confirmLink($secondUtility)['contract']===null && uc17deny(fn()=>$links->issue('UTILQR',$other['account'])), 'QR điện nước tạo theo từng sinh viên, thanh toán độc lập và không tạo hợp đồng');
    $seventh = $create('QR7',7);
    $_SESSION['user'] = ['MaTK'=>$seventh['account']];
    uc17request('student-invoice',['invoice'=>$seventh['invoice']]);
    $createPost = ['invoice'=>$seventh['invoice']];
    uc17check(uc17request('student-qr-create',[],$createPost,'POST')[0]===303, 'Tạo QR mới qua POST cho hóa đơn khác');
    $oldToken = $_SESSION['student_qr_tokens'][$seventh['invoice']]['token'];
    $oldHash = hash('sha256',$oldToken);
    $stmt = $pdo->prepare('UPDATE phienthanhtoan SET TaoLuc=DATE_SUB(NOW(),INTERVAL 6 MINUTE),HetHanLuc=DATE_ADD(NOW(),INTERVAL 15 MINUTE) WHERE MaPhien=?');
    $stmt->execute([$oldHash]);
    [$code,$html] = uc17request('student-invoice',['invoice'=>$seventh['invoice']]);
    uc17check($code===200 && str_contains($html,'data-qr-create') && !str_contains($html,'data-payment-qr='), 'Sau 5 phút chi tiết trở lại nút Tạo mã QR, không tự phát hành');
    uc17check(uc17request('student-qr-create',[],$createPost,'POST')[0]===303, 'Hết hạn thì có thể bấm tạo mã mới');
    $newToken = $_SESSION['student_qr_tokens'][$seventh['invoice']]['token'];
    uc17check($newToken!==$oldToken && strlen($newToken)===64 && uc17deny(fn()=>$links->resolve($oldToken)) && $links->current($seventh['invoice'],$seventh['account'],$newToken)['token']===$newToken, 'Mã mới khác mã cũ, chỉ mã mới còn hiệu lực');
    foreach ($checks as $label) { echo '[PASS] '.$label.PHP_EOL; }
    echo count($checks)." kiểm thử UC17 đạt.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR,'[FAIL] '.$e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL); exit(1);
}
