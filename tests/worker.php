<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// Session riêng trong RAM: không đụng tới phiên đăng nhập của người demo.
session_set_save_handler(
    fn($path, $name) => true,
    fn() => true,
    fn($id) => '',
    fn($id, $data) => true,
    fn($id) => true,
    fn($lifetime) => 0
);
require_once __DIR__ . '/../middleware/Role.php';
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// Middleware/controller có exit; kiểm tra kết quả trong shutdown callback.
function expectExit(callable $verify): void
{
    ob_start();
    register_shutdown_function(function () use ($verify) {
        ob_end_clean();
        try {
            check(error_get_last() === null, 'Có lỗi PHP trong lúc xử lý.');
            $verify();
            echo 'PASS';
        } catch (Throwable $error) {
            echo 'FAIL: ' . $error->getMessage();
            exit(1);
        }
    });
}

$users = $pdo->query('SELECT MaTK, TenDangNhap, MatKhau, VaiTro, TrangThai FROM taikhoan')->fetchAll();
$active = array_values(array_filter($users, fn($user) => $user['TrangThai'] === 'Hoạt động'));
$case = $argv[1] ?? '';
try {
    if (preg_match('/^newroute_authorized_(.+)$/D', $case, $parts)) {
        $route = $parts[1];
        $role = in_array($route, ['incident-report','notifications'], true) ? 'Sinh viên'
            : ($route === 'statistics' ? 'Quản lý KTX' : 'Nhân viên quản lý KTX');
        $matches = array_values(array_filter($active, fn($user) => $user['VaiTro'] === $role));
        check(count($matches) > 0, 'Cần tài khoản đúng vai trò để kiểm thử route mới.');
        $_SESSION['user'] = $matches[0];
        $_GET = ['action'=>$route];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () use ($route) {
            $html = ob_get_clean();
            try {
                check(error_get_last() === null, 'Có lỗi PHP khi render route mới.');
                check(http_response_code() === 200, 'Route đúng vai trò trả HTTP ' . http_response_code() . ', không phải 200.');
                $headings = [
                    'students'=>'Quản lý sinh viên', 'utility-meters'=>'Ghi chỉ số điện nước',
                    'incidents'=>'Xử lý phản ánh sự cố', 'incident-report'=>'Gửi phản ánh sự cố',
                    'notifications'=>'Thông báo', 'statistics'=>'Thống kê và báo cáo',
                ];
                check(str_contains($html, '<h1>' . $headings[$route] . '</h1>'), 'Trang không render đúng tiêu đề chức năng.');
                check(str_contains($html, 'aria-current="page"'), 'Navbar chưa đánh dấu chức năng hiện tại.');
                check(!str_contains($html, 'assets/css/dashboard.css'), 'Trang đang tham chiếu tệp dashboard.css không tồn tại.');
                if (in_array($route, ['students','utility-meters','incidents','statistics'], true)) {
                    check(str_contains($html, 'class="staff-header"'), 'Trang nhân viên chưa dùng header chung.');
                    check(str_contains($html, 'assets/css/staff-dashboard.css'), 'Trang nhân viên thiếu stylesheet dashboard chung.');
                }
                if ($route === 'students') {
                    check(str_contains($html, 'class="payment-table"'), 'Danh sách sinh viên chưa tự hiển thị khi mở chức năng.');
                    check(!str_contains($html, 'để xem toàn bộ sinh viên'), 'Trang sinh viên vẫn yêu cầu bấm tìm kiếm lần đầu.');
                }
                if ($route === 'utility-meters') {
                    check(str_contains($html, 'data-utility-room'), 'Ô chọn phòng chưa được nối với thao tác tải chỉ số tự động.');
                    check(str_contains($html, 'assets/js/utility-meters.js'), 'Trang ghi chỉ số thiếu JavaScript tự tải phòng.');
                }
                echo 'PASS';
            } catch (Throwable $error) { echo 'FAIL: ' . $error->getMessage(); exit(1); }
        });
        require __DIR__ . '/../index.php';
    }
    if (preg_match('/^newroute_(guest|forbidden)_(.+)$/D', $case, $parts)) {
        [, $actor, $route] = $parts;
        unset($_SESSION['user']);
        if ($actor === 'forbidden') {
            $required = in_array($route, ['incident-report','notifications','notification-detail'], true) ? 'Sinh viên'
                : ($route === 'statistics' ? 'Quản lý KTX' : 'Nhân viên quản lý KTX');
            $matches = array_values(array_filter($active, fn($user) => $user['VaiTro'] !== $required));
            check(count($matches) > 0, 'Cần tài khoản sai vai trò để kiểm thử route mới.');
            $_SESSION['user'] = $matches[0];
        }
        $_GET = ['action'=>$route,'id'=>'KHONGCO'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        http_response_code(200);
        expectExit(function () use ($actor) {
            check(http_response_code() === ($actor === 'guest' ? 302 : 403), 'Sai phân quyền route mới.');
        });
        require __DIR__ . '/../index.php';
    }
    if (str_starts_with($case, 'checkout_')) {
        $_SESSION = [];
        $confirm = $case === 'checkout_get_confirm';
        $_GET = ['action'=>$confirm ? 'payment-checkout-confirm' : 'payment-checkout'];
        $_SERVER['REQUEST_METHOD'] = $case === 'checkout_invalid' ? 'POST' : 'GET';
        $_POST = ['link_token'=>'invalid'];
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () use ($case) {
            $html = ob_get_clean();
            try {
                check(error_get_last() === null, 'Có lỗi PHP ở route QR công khai.');
                $expected = ['checkout_open'=>200,'checkout_invalid'=>409,'checkout_get_confirm'=>405][$case];
                check(http_response_code() === $expected, 'HTTP status QR không đúng.');
                check(!isset($_SESSION['user']), 'QR công khai không được tạo phiên sinh viên.');
                if ($case === 'checkout_open') { check(str_contains($html,'data-checkout-open'), 'Chưa đăng nhập phải mở được trang QR.'); }
                echo 'PASS';
            } catch (Throwable $e) { echo 'FAIL: ' . $e->getMessage(); exit(1); }
        });
        require __DIR__ . '/../index.php';
    }
    if (preg_match('/^uc17_(guest|accountant|staff|manager)_(.+)$/D', $case, $parts)) {
        [, $actor, $route] = $parts;
        unset($_SESSION['user']);
        if ($actor !== 'guest') {
            $role = ['accountant'=>'Nhân viên kế toán','staff'=>'Nhân viên quản lý KTX','manager'=>'Quản lý KTX'][$actor];
            $matches = array_values(array_filter($active, fn($user) => $user['VaiTro'] === $role));
            check(count($matches)>0, 'Cần tài khoản hoạt động cho kiểm thử phân quyền.');
            $_SESSION['user'] = $matches[0];
        }
        $_GET = ['action'=>$route,'invoice'=>'TEST17'];
        $_POST = ['invoice'=>'TEST17'];
        $_SERVER['REQUEST_METHOD'] = in_array($route, ['student-payment-confirm','student-qr-create'], true) ? 'POST' : 'GET';
        expectExit(function () use ($actor, $route) {
            check(http_response_code() === ($actor === 'guest' ? 302 : 403), 'Sai phân quyền UC17.');
        });
        require __DIR__ . '/../index.php';
    }
    if ($case === 'connection_refresh') {
        $firstConnection = $pdo;
        require __DIR__ . '/../config/db.php';
        check($pdo === $firstConnection, 'Module phải dùng lại kết nối trong request.');
        $independent = createDatabaseConnection();
        check($independent !== $pdo, 'Kết nối độc lập không được dùng chung transaction.');
        $ddl = $pdo->query('SHOW CREATE TABLE taikhoan')->fetch(PDO::FETCH_NUM)[1];
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $ddl));
        $stmt = $pdo->prepare("INSERT INTO taikhoan (MaTK,TenDangNhap,MatKhau,VaiTro,TrangThai) VALUES (1,'TESTAUTH',?,'Sinh viên','Hoạt động')");
        $stmt->execute([password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)]);
        $_SESSION['user'] = ['MaTK'=>1];
        check(requireAuth()['VaiTro'] === 'Sinh viên', 'Đọc quyền sinh viên từ CSDL.');
        $pdo->exec("UPDATE taikhoan SET VaiTro='Nhân viên kế toán' WHERE MaTK=1");
        check(requireAuth()['VaiTro'] === 'Nhân viên kế toán', 'Không cache quyền cũ trong cùng request.');
        $pdo->exec("UPDATE taikhoan SET TrangThai='Khóa' WHERE MaTK=1");
        expectExit(function () {
            check(http_response_code() === 302 && !isset($_SESSION['user']), 'Tài khoản bị khóa phải mất quyền ngay.');
        });
        requireAuth();
        throw new RuntimeException('Tài khoản bị khóa chưa bị chặn.');
    }
    if (in_array($case, ['contracts_guest', 'contracts_forbidden', 'contracts_staff', 'contracts_post', 'contract_detail_guest', 'contract_detail_forbidden'], true)) {
        $guest = str_ends_with($case, '_guest');
        $forbidden = str_ends_with($case, '_forbidden');
        unset($_SESSION['user']);
        if (!$guest) {
            $role = $forbidden ? 'Sinh viên' : 'Nhân viên quản lý KTX';
            $matches = array_values(array_filter($active, fn($user) => $user['VaiTro'] === $role));
            check(count($matches) > 0, 'Cần tài khoản hoạt động để kiểm thử UC05.1.');
            $_SESSION['user'] = $matches[0];
        }
        $detail = str_starts_with($case, 'contract_detail_');
        $_GET = ['action' => $detail ? 'contract-detail' : 'contracts', 'id' => 'KHONGCO'];
        $_SERVER['REQUEST_METHOD'] = $case === 'contracts_post' ? 'POST' : 'GET';
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () use ($case, $guest, $forbidden) {
            $html = ob_get_clean();
            try {
                check(error_get_last() === null, 'Có lỗi PHP trong UC05.1.');
                $expected = $guest ? 302 : ($forbidden ? 403 : ($case === 'contracts_post' ? 405 : 200));
                check(http_response_code() === $expected, 'HTTP status UC05.1 không đúng.');
                if ($case === 'contracts_staff') {
                    check(str_contains($html, '<h1>Quản lý hợp đồng</h1>') && str_contains($html, 'Danh sách hợp đồng'), 'NVQL chưa mở được trang quản lý hợp đồng.');
                    check(str_contains($html, 'aria-current="page"'), 'Navbar chưa đánh dấu chức năng hợp đồng hiện tại.');
                    check(str_contains($html, 'approval-panel payment-filters') && str_contains($html, 'payment-table'), 'Giao diện hợp đồng chưa đồng bộ với tra cứu hóa đơn.');
                    check(!str_contains($html, 'để xem toàn bộ hợp đồng'), 'Trang hợp đồng vẫn yêu cầu bấm tra cứu lần đầu.');
                }
                echo 'PASS';
            } catch (Throwable $error) {
                echo 'FAIL: ' . $error->getMessage();
                exit(1);
            }
        });
        require __DIR__ . '/../index.php';
    }
    if (str_starts_with($case, 'payment_')) {
        $forbiddenRoles = ['payment_student' => 'Sinh viên', 'payment_staff' => 'Nhân viên quản lý KTX', 'payment_manager' => 'Quản lý KTX'];
        unset($_SESSION['user']);
        if ($case !== 'payment_guest') {
            $role = $forbiddenRoles[$case] ?? 'Nhân viên kế toán';
            $usersForRole = array_values(array_filter($active, fn($u) => $u['VaiTro'] === $role));
            check(count($usersForRole) > 0, 'Cần tài khoản hoạt động cho vai trò kiểm thử.');
            $_SESSION['user'] = $usersForRole[0];
        }
        $post = $case === 'payment_missing';
        $_GET = ['action' => $post || $case === 'payment_get_confirm' ? 'payment-confirm' : 'payments'];
        if ($case === 'payment_filters') { $_GET['student'] = ['bad']; }
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () use ($case, $forbiddenRoles) {
            $html = ob_get_clean();
            try {
                check(error_get_last() === null, 'Có lỗi PHP trong thanh toán.');
                $expected = match (true) {
                    $case === 'payment_guest' => 302,
                    isset($forbiddenRoles[$case]) => 403,
                    $case === 'payment_missing', $case === 'payment_filters' => 400,
                    $case === 'payment_get_confirm' => 405,
                    default => 200,
                };
                check(http_response_code() === $expected, 'HTTP status thanh toán không đúng.');
                if ($case === 'payment_accountant') {
                    check(str_contains($html, '<h1>Quản lý hóa đơn</h1>') && str_contains($html, 'Danh sách hóa đơn'), 'NVKT phải mở được danh sách hóa đơn.');
                    check(str_contains($html, 'aria-current="page"'), 'Navbar thanh toán được đánh dấu.');
                }
                echo 'PASS';
            } catch (Throwable $error) { echo 'FAIL: ' . $error->getMessage(); exit(1); }
        });
        require __DIR__ . '/../index.php';
    }
    if (in_array($case, ['accommodation_view_contract', 'accommodation_view_pending', 'accommodation_view_missing'], true)) {
        $students = array_values(array_filter($active, fn($u) => $u['VaiTro'] === 'Sinh viên'));
        check(count($students) > 0, 'Cần tài khoản sinh viên để kiểm tra view.');
        $_SESSION['user'] = $students[0];
        $data = ['student' => null, 'accommodation' => null];
        if ($case !== 'accommodation_view_missing') {
            $data['student'] = ['MSSV' => 'VIEWTEST', 'HoTen' => '<script>alert(1)</script>', 'GioiTinh' => 'Nam', 'NgaySinh' => '2005-01-01', 'CCCD' => 'TEST', 'SDT' => '0900000000', 'Email' => null, 'Lop' => 'TEST', 'NienKhoa' => '2024-2028', 'QueQuan' => 'Kiểm thử', 'TrangThaiLuuTru' => 'Chờ thanh toán'];
        }
        if ($case === 'accommodation_view_contract') {
            $data['accommodation'] = ['MaHopDong' => 'HVIEW', 'NgayBatDau' => '2026-09-01', 'NgayKetThuc' => '2027-08-31', 'TrangThai' => 'Còn hạn', 'SoPhong' => 'A101', 'Tang' => 1, 'TenToa' => 'Tòa kiểm thử', 'SucChua' => 8, 'GioiTinhPhong' => 'Nam'];
        }
        $_GET = ['action' => 'accommodation'];
        ob_start();
        require __DIR__ . '/../views/student/accommodation.php';
        $html = ob_get_clean();
        check(!str_contains($html, '<script>alert(1)</script>'), 'View phải escape dữ liệu hồ sơ.');
        if ($case === 'accommodation_view_missing') {
            check(str_contains($html, 'Tài khoản chưa có hồ sơ sinh viên'), 'View phải xử lý tài khoản thiếu hồ sơ.');
        } else {
            check(str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;') && str_contains($html, '01/01/2005'), 'Hiển thị hồ sơ và định dạng ngày.');
            check(str_contains($html, $case === 'accommodation_view_contract' ? 'HVIEW' : 'Hiện tại bạn không có hợp đồng lưu trú còn hiệu lực.'), 'Hiển thị đúng trạng thái hợp đồng.');
            check($case === 'accommodation_view_contract' || !str_contains($html, 'Thông tin hợp đồng'), 'Chưa có hợp đồng không hiển thị hợp đồng/phòng giả.');
        }
        echo 'PASS';
        exit;
    }
    if (in_array($case, ['accommodation_guest', 'accommodation_forbidden', 'accommodation_student', 'accommodation_post'], true)) {
        unset($_SESSION['user']);
        if ($case !== 'accommodation_guest') {
            $isStudent = $case !== 'accommodation_forbidden';
            $candidates = array_values(array_filter($active, fn($u) => ($u['VaiTro'] === 'Sinh viên') === $isStudent));
            check(count($candidates) > 0, 'Cần tài khoản kiểm thử cho vai trò tương ứng.');
            $_SESSION['user'] = $candidates[0];
        }
        $_GET = ['action' => 'accommodation', 'MSSV' => 'OTHER_STUDENT', 'MaTK' => '999999'];
        $_SERVER['REQUEST_METHOD'] = $case === 'accommodation_post' ? 'POST' : 'GET';
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () use ($case) {
            $html = ob_get_clean();
            try {
                check(error_get_last() === null, 'Có lỗi PHP trong UC11.');
                $status = match ($case) { 'accommodation_guest' => 302, 'accommodation_forbidden' => 403, 'accommodation_post' => 405, default => 200 };
                check(http_response_code() === $status, 'HTTP status UC11 không đúng.');
                if ($case === 'accommodation_student') {
                    check(str_contains($html, '<h1>Thông tin lưu trú</h1>'), 'Route không hiển thị UC11.');
                    require __DIR__ . '/../config/db.php';
                    $stmt = $pdo->prepare('SELECT MSSV FROM sinhvien WHERE MaTK = ?');
                    $stmt->execute([$_SESSION['user']['MaTK']]);
                    $mssv = $stmt->fetchColumn();
                    check($mssv ? str_contains($html, htmlspecialchars($mssv, ENT_QUOTES, 'UTF-8')) : str_contains($html, 'Tài khoản chưa có hồ sơ sinh viên'), 'UC11 phải đọc hồ sơ từ tài khoản hiện tại.');
                    check(!str_contains($html, 'OTHER_STUDENT'), 'Không nhận MSSV từ URL.');
                    check(str_contains($html, 'aria-current="page"'), 'Có đánh dấu điều hướng hiện tại.');
                }
                echo 'PASS';
            } catch (Throwable $error) {
                echo 'FAIL: ' . $error->getMessage();
                exit(1);
            }
        });
        require __DIR__ . '/../index.php';
    }
    if ($case === 'room_information_guest') {
        unset($_SESSION['user']);
        $_GET = ['action' => 'rooms'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () {
            $html = ob_get_clean();
            try {
                check(error_get_last() === null, 'Có lỗi PHP khi mở UC15.');
                check(!isset($_SESSION['user']), 'UC15 không tạo phiên đăng nhập.');
                check(http_response_code() === 200, 'UC15 phải trả HTTP 200 cho khách.');
                check(str_contains($html, 'room-information-shell') && str_contains($html, 'Thông tin phòng'), 'Route phải hiển thị UC15.');
                check(!str_contains($html, 'name="csrf_token"'), 'UC15 không có form ghi dữ liệu.');
                echo 'PASS';
            } catch (Throwable $error) {
                echo 'FAIL: ' . $error->getMessage();
                exit(1);
            }
        });
        require __DIR__ . '/../index.php';
    }
    if (str_starts_with($case, 'approval_guest_') || str_starts_with($case, 'approval_forbidden_')) {
        $guest = str_starts_with($case, 'approval_guest_');
        $route = substr($case, strlen($guest ? 'approval_guest_' : 'approval_forbidden_'));
        if ($guest) {
            unset($_SESSION['user']);
        } else {
            $otherRoles = array_values(array_filter($active, fn($u) => $u['VaiTro'] !== 'Nhân viên quản lý KTX'));
            check(count($otherRoles) > 0, 'Cần tài khoản vai trò khác NVQL.');
            $_SESSION['user'] = $otherRoles[0];
        }
        $_GET = ['action' => $route, 'id' => 'test'];
        $_SERVER['REQUEST_METHOD'] = in_array($route, ['approval-approve','approval-reject','approval-email-retry'], true) ? 'POST' : 'GET';
        $_POST = ['id' => 'test', 'reason' => 'test'];
        expectExit(fn() => check(http_response_code() === ($guest ? 302 : 403), 'Quyền route UC03 không đúng.'));
        require __DIR__ . '/../index.php';
        throw new RuntimeException('Route phải dừng khi không đủ quyền.');
    }
    if ($case === 'approval_staff_list') {
        $staff = array_values(array_filter($active, fn($u) => $u['VaiTro'] === 'Nhân viên quản lý KTX'));
        check(count($staff) > 0, 'Cần tài khoản NVQL.');
        $_SESSION['user'] = $staff[0];
        $_GET = ['action' => 'approval'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        register_shutdown_function(function () {
            $html = ob_get_clean();
            check(error_get_last() === null && str_contains($html, 'Danh sách đơn chờ duyệt'), 'NVQL không mở được trang xét duyệt.');
            echo 'PASS';
        });
        require __DIR__ . '/../index.php';
    }
    switch ($case) {
        case 'logout':
        case 'logout_get':
            check(count($active) > 0, 'Cần tài khoản hoạt động.');
            $_SESSION['user'] = $active[0];
            $_SERVER['REQUEST_METHOD'] = $case === 'logout_get' ? 'GET' : 'POST';
            $_GET['action'] = 'logout';
            expectExit(function () use ($case) {
                if ($case === 'logout') {
                    check(http_response_code() === 303, 'Đăng xuất phải chuyển hướng bằng 303.');
                    check($_SESSION === [] && session_status() !== PHP_SESSION_ACTIVE, 'Session chưa được xóa hoàn toàn.');
                } else {
                    check(http_response_code() === 405, 'Sai mã HTTP.');
                    check(isset($_SESSION['user']), 'Yêu cầu không hợp lệ không được xóa tài khoản.');
                }
            });
            require __DIR__ . '/../index.php';
            throw new RuntimeException('Yêu cầu đăng xuất không được xử lý.');
        case 'staff_dashboard':
            require_once __DIR__ . '/../models/StaffDashboard.php';
            $model = new StaffDashboard($pdo);
            $data = $model->summary();
            check($data['applications'] === (int) $pdo->query("SELECT COUNT(*) FROM dondangky WHERE TrangThai = 'Chờ duyệt'")->fetchColumn(), 'Sai số đơn chờ duyệt.');
            check($data['students'] === (int) $pdo->query("SELECT COUNT(*) FROM sinhvien WHERE TrangThaiLuuTru = 'Đang ở'")->fetchColumn(), 'Sai số sinh viên đang ở.');
            check($data['reports'] === (int) $pdo->query("SELECT COUNT(*) FROM phananhsuco WHERE TrangThai = 'Chờ xử lý'")->fetchColumn(), 'Sai số phản ánh chờ xử lý.');
            $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
            $rooms = [];
            foreach ($pdo->query('SELECT h.MaPhong, h.NgayBatDau, h.NgayKetThuc, h.TrangThai, s.TrangThaiLuuTru FROM hopdong h JOIN sinhvien s ON s.MSSV = h.MSSV')->fetchAll() as $contract) {
                if ($contract['TrangThai'] === 'Còn hạn' && $contract['TrangThaiLuuTru'] === 'Đang ở' && $contract['NgayBatDau'] <= $today && $contract['NgayKetThuc'] >= $today) {
                    $rooms[$contract['MaPhong']] = true;
                }
            }
            check($data['rooms'] === count($rooms), 'Phòng có nhiều hợp đồng chỉ được đếm một lần.');
            check($model->nameForAccount(-1) === null, 'Tài khoản chưa có hồ sơ phải trả null.');
            foreach ($pdo->query('SELECT MaTK, HoTen FROM nhanvien')->fetchAll() as $employee) {
                check($model->nameForAccount((int) $employee['MaTK']) === $employee['HoTen'], 'Sai tên nhân viên.');
            }
            break;
        case 'student_dashboard':
            require_once __DIR__ . '/../models/StudentDashboard.php';
            $model = new StudentDashboard($pdo);
            $empty = $model->forAccount(-1);
            check($empty['student'] === null && $empty['invoices'] === 0 && $empty['reports'] === 0, 'Tài khoản chưa có hồ sơ phải có trạng thái trống.');
            $students = array_filter($active, fn($user) => $user['VaiTro'] === 'Sinh viên');
            check(count($students) > 0, 'Cần tài khoản sinh viên.');
            foreach ($students as $user) {
                $data = $model->forAccount((int) $user['MaTK']);
                check(is_int($data['invoices']) && $data['invoices'] >= 0, 'Số hóa đơn phải là số nguyên không âm.');
                check(is_int($data['reports']) && $data['reports'] >= 0, 'Số phản ánh phải là số nguyên không âm.');
                $stmt = $pdo->prepare('SELECT MSSV FROM sinhvien WHERE MaTK = :id');
                $stmt->execute(['id' => $user['MaTK']]);
                $studentId = $stmt->fetchColumn();
                check(($data['student']['MSSV'] ?? false) === $studentId, 'Hồ sơ phải thuộc tài khoản đăng nhập.');
            }
            // Chạy đầy đủ các truy vấn với hồ sơ thật, kể cả hồ sơ chưa có tài khoản mẫu hoạt động.
            foreach ($pdo->query('SELECT MaTK FROM sinhvien')->fetchAll() as $student) {
                $data = $model->forAccount((int) $student['MaTK']);
                check($data['student'] !== null, 'Không đọc được hồ sơ sinh viên.');
            }
            break;
        case 'schema':
            foreach (['taikhoan', 'sinhvien', 'nhanvien'] as $table) {
                $column = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'MaTK'")->fetch();
                check($column && preg_match('/^int(?:\(|$)/', $column['Type']) === 1, "$table.MaTK phải là INT.");
                if ($table === 'taikhoan') {
                    check(str_contains($column['Extra'], 'auto_increment'), 'MaTK phải tự tăng.');
                } else {
                    $orphans = $pdo->query("SELECT COUNT(*) FROM `$table` c LEFT JOIN taikhoan t ON c.MaTK = t.MaTK WHERE t.MaTK IS NULL")->fetchColumn();
                    check((int) $orphans === 0, 'Có khóa ngoại không khớp tài khoản.');
                }
            }
            check(count($users) > 0, 'Cần tài khoản mẫu trong CSDL.');
            foreach ($users as $user) {
                check(password_get_info($user['MatKhau'])['algoName'] !== 'unknown', 'Có mật khẩu chưa được băm.');
            }
            break;
        case 'model':
            check(count($active) > 0, 'Cần tài khoản hoạt động.');
            $model = new User($pdo);
            check($model->findByUsername($active[0]['TenDangNhap'])['MaTK'] === $active[0]['MaTK'], 'Model trả sai tài khoản.');
            check($model->findByUsername("' OR 1=1 -- ") === false, 'Đầu vào SQL injection không được trả tài khoản.');
            break;
        case 'roles':
            $roles = ['Sinh viên', 'Nhân viên quản lý KTX', 'Nhân viên kế toán', 'Quản lý KTX'];
            foreach ($roles as $role) {
                $matches = array_values(array_filter($active, fn($user) => $user['VaiTro'] === $role));
                check(count($matches) > 0, "Thiếu tài khoản mẫu cho vai trò: $role");
                $_SESSION['user'] = $matches[0];
                requireRole($role);
                $url = dashboardForRole($role);
                $base = rtrim(appUrl(), '/');
                check($url !== null && is_file(dirname(__DIR__) . substr($url, strlen($base))), 'Dashboard không tồn tại.');
            }
            check(dashboardForRole('unknown') === null, 'Vai trò lạ phải bị từ chối.');
            break;
        case 'forbidden':
            check(count($active) > 0, 'Cần tài khoản hoạt động.');
            $_SESSION['user'] = $active[0];
            expectExit(fn() => check(http_response_code() === 403, 'Sai vai trò phải trả 403.'));
            requireRole('unknown');
            throw new RuntimeException('Không chặn sai vai trò.');
        case 'guest':
        case 'deleted':
        case 'invalid_id':
            if ($case === 'deleted') {
                // ID âm không có trong tài khoản demo; xác nhận trước khi dùng.
                check((int) $pdo->query('SELECT COUNT(*) FROM taikhoan WHERE MaTK = -1')->fetchColumn() === 0, 'ID kiểm thử đang tồn tại.');
                $_SESSION['user'] = ['MaTK' => -1];
            }
            if ($case === 'invalid_id') {
                $_SESSION['user'] = ['MaTK' => ['invalid']];
            }
            expectExit(function () {
                check(http_response_code() === 302, 'Phải chuyển hướng về đăng nhập.');
                check(!isset($_SESSION['user']), 'Session tài khoản phải được xóa.');
            });
            requireRole('Sinh viên');
            throw new RuntimeException('Không chặn truy cập.');
        case 'refresh':
        case 'string_id':
            check(count($active) > 0, 'Cần tài khoản hoạt động.');
            $_SESSION['user'] = [
                'MaTK' => $case === 'string_id' ? (string) $active[0]['MaTK'] : $active[0]['MaTK'],
                'VaiTro' => 'fake',
            ];
            $current = requireAuth();
            check($current['VaiTro'] === $active[0]['VaiTro'], 'Chưa cập nhật quyền từ CSDL.');
            check(!isset($current['MatKhau']), 'Không được lưu mật khẩu trong session.');
            break;
        case 'invalid_input':
        case 'wrong_password':
        case 'existing_session_wrong_password':
            require_once __DIR__ . '/../controllers/AuthController.php';
            $_SERVER['REQUEST_METHOD'] = 'POST';
            if ($case === 'invalid_input') {
                $_POST = ['username' => ['invalid'], 'password' => 'invalid'];
            } else {
                check(count($active) > 0, 'Cần tài khoản hoạt động.');
                $wrong = bin2hex(random_bytes(32));
                check(!password_verify($wrong, $active[0]['MatKhau']), 'Mật khẩu thử phải khác mật khẩu thật.');
                $_POST = ['username' => $active[0]['TenDangNhap'], 'password' => $wrong];
            }
            if ($case === 'existing_session_wrong_password') {
                $students = array_values(array_filter($active, fn($user) => $user['VaiTro'] === 'Sinh viên'));
                check(count($students) > 0, 'Cần tài khoản sinh viên để tạo session cũ.');
                $_SESSION['user'] = $students[0];
                $_GET['action'] = 'login';
            }
            expectExit(function () {
                check(http_response_code() === 302, 'Đăng nhập thất bại phải chuyển hướng.');
                check(!isset($_SESSION['user']) && !empty($_SESSION['error']), 'Phải có lỗi và không tạo phiên đăng nhập.');
            });
            if ($case === 'existing_session_wrong_password') {
                require __DIR__ . '/../index.php';
            } else {
                (new AuthController($pdo))->login();
            }
            throw new RuntimeException('Đăng nhập lỗi không được xử lý.');
        default:
            throw new RuntimeException('Trường hợp kiểm thử không tồn tại.');
    }
    echo 'PASS';
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
