<?php
// Chạy CLI để không công khai bộ kiểm thử qua trình duyệt.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$cases = [
    'logout' => 'Đăng xuất xóa session và chuyển về đăng nhập',
    'logout_invalid_token' => 'Token đăng xuất sai không xóa session',
    'logout_get' => 'Yêu cầu GET không được đăng xuất',
    'staff_dashboard' => 'Dashboard NVQL trả đúng số liệu tổng quan và tên nhân viên',
    'student_dashboard' => 'Dashboard sinh viên đọc dữ liệu và xử lý tài khoản chưa có hồ sơ',
    'schema' => 'MaTK và khóa ngoại có kiểu INT, tài khoản dùng mật khẩu băm',
    'model' => 'Model tìm tài khoản và chống đầu vào SQL injection',
    'roles' => 'Bốn vai trò được vào đúng dashboard',
    'forbidden' => 'Sai vai trò bị chặn với HTTP 403',
    'guest' => 'Chưa đăng nhập được chuyển hướng',
    'deleted' => 'Tài khoản không tồn tại bị xóa khỏi session',
    'refresh' => 'Vai trò giả trong session được thay bằng vai trò từ CSDL',
    'string_id' => 'MaTK dạng chuỗi số vẫn được xác thực từ CSDL',
    'invalid_id' => 'MaTK sai kiểu bị từ chối và xóa khỏi session',
    'invalid_input' => 'Form chứa dữ liệu dạng mảng bị từ chối',
    'wrong_password' => 'Mật khẩu sai không tạo phiên đăng nhập',
    'existing_session_wrong_password' => 'Có session cũ vẫn phải kiểm tra mật khẩu mới qua index.php',
];
$failed = 0;
foreach (['approval', 'approval-detail', 'approval-proof', 'approval-approve', 'approval-reject'] as $route) {
    $cases['approval_guest_' . $route] = 'UC03: khách bị chặn tại ' . $route;
    $cases['approval_forbidden_' . $route] = 'UC03: vai trò khác bị chặn tại ' . $route;
}
$cases['approval_staff_list'] = 'UC03: NVQL mở được danh sách qua route thực tế';
foreach ($cases as $case => $description) {
    $process = proc_open(
        [PHP_BINARY, '-d', 'xdebug.mode=off', __DIR__ . '/worker.php', $case],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        dirname(__DIR__)
    );
    if (!is_resource($process)) {
        echo "[FAIL] $description: không khởi chạy được PHP.\n";
        $failed++;
        continue;
    }
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    $passed = $exitCode === 0 && trim($output) === 'PASS' && trim($errors) === '';
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $description . PHP_EOL;
    if (!$passed) {
        $failed++;
        echo trim($output . PHP_EOL . $errors) . PHP_EOL;
    }
}
echo PHP_EOL . (count($cases) - $failed) . '/' . count($cases) . ' kiểm thử đạt.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
