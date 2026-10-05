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
    switch ($case) {
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
                check($url !== null && is_file(dirname(__DIR__) . substr($url, strlen('/Kanto-KTX'))), 'Dashboard không tồn tại.');
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
