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
    if (str_starts_with($case, 'payment_')) {
        $forbiddenRoles = ['payment_student' => 'Sinh viên', 'payment_staff' => 'Nhân viên quản lý KTX', 'payment_manager' => 'Quản lý KTX'];
        unset($_SESSION['user']);
        if ($case !== 'payment_guest') {
            $role = $forbiddenRoles[$case] ?? 'Nhân viên kế toán';
            $usersForRole = array_values(array_filter($active, fn($u) => $u['VaiTro'] === $role));
            check(count($usersForRole) > 0, 'Cần tài khoản hoạt động cho vai trò kiểm thử.');
            $_SESSION['user'] = $usersForRole[0];
        }
        $post = in_array($case, ['payment_csrf','payment_missing'], true);
        $_GET = ['action' => $post || $case === 'payment_get_confirm' ? 'payment-confirm' : 'payments'];
        if ($case === 'payment_filters') { $_GET['student'] = ['bad']; }
        $_SESSION['payment_token'] = bin2hex(random_bytes(32));
        $_POST = $post ? ['csrf_token' => $case === 'payment_csrf' ? [] : $_SESSION['payment_token']] : [];
        $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () use ($case, $forbiddenRoles) {
            $html = ob_get_clean();
            try {
                check(error_get_last() === null, 'Có lỗi PHP trong thanh toán.');
                $expected = match (true) {
                    $case === 'payment_guest' => 302,
                    isset($forbiddenRoles[$case]), $case === 'payment_csrf' => 403,
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
        $_SERVER['REQUEST_METHOD'] = in_array($route, ['approval-approve','approval-reject'], true) ? 'POST' : 'GET';
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
        case 'logout_invalid_token':
        case 'logout_get':
            check(count($active) > 0, 'Cần tài khoản hoạt động.');
            $_SESSION['user'] = $active[0];
            $_SESSION['logout_token'] = bin2hex(random_bytes(32));
            $_SERVER['REQUEST_METHOD'] = $case === 'logout_get' ? 'GET' : 'POST';
            $_GET['action'] = 'logout';
            $_POST['csrf_token'] = $case === 'logout_invalid_token' ? 'invalid' : $_SESSION['logout_token'];
            expectExit(function () use ($case) {
                if ($case === 'logout') {
                    check(http_response_code() === 303, 'Đăng xuất phải chuyển hướng bằng 303.');
                    check($_SESSION === [] && session_status() !== PHP_SESSION_ACTIVE, 'Session chưa được xóa hoàn toàn.');
                } else {
                    check(http_response_code() === ($case === 'logout_get' ? 405 : 403), 'Sai mã HTTP.');
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
