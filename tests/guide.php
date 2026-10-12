<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// Chạy route trong tiến trình riêng vì index.php kết thúc bằng exit.
if (isset($argv[1])) {
    session_set_save_handler(fn($path, $name) => true, fn() => true, fn($id) => '', fn($id, $data) => true, fn($id) => true, fn($lifetime) => 0);
    require_once __DIR__ . '/../middleware/Role.php';
    $case = $argv[1];
    $_SESSION['registration_draft'] = ['step' => 2, 'data' => ['MSSV' => 'test-guide']];
    if ($case === 'signed_in') {
        $_SESSION['user'] = ['MaTK' => -1, 'VaiTro' => 'Sinh viên'];
    }
    $before = $_SESSION;
    $_GET = ['action' => 'guide'];
    $_SERVER['REQUEST_METHOD'] = $case === 'post' ? 'POST' : 'GET';
    http_response_code(200);
    ob_start();
    register_shutdown_function(function () use ($case, $before) {
        $html = ob_get_clean();
        $checks = [
            error_get_last() === null,
            $_SESSION === $before,
            !in_array(realpath(__DIR__ . '/../config/db.php'), get_included_files(), true),
            http_response_code() === ($case === 'post' ? 405 : 200),
        ];
        if ($case !== 'post') {
            $checks[] = str_contains($html, '<h1>Hướng dẫn đăng ký lưu trú</h1>');
            $checks[] = str_contains($html, 'action=register') && str_contains($html, 'action=rooms');
            $checks[] = str_contains($html, 'aria-current="page"') && str_contains($html, 'assets/css/guide.css');
            $checks[] = !str_contains($html, '<form') && !str_contains($html, 'name="csrf_token"');
        } else {
            $checks[] = !str_contains($html, 'guide-shell');
        }
        echo in_array(false, $checks, true) ? 'FAIL' : 'PASS';
    });
    require __DIR__ . '/../index.php';
}

$failed = 0;
foreach (['guest', 'signed_in', 'post'] as $case) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, $case], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
    if (!is_resource($process)) {
        $failed++;
        echo "[FAIL] $case: không khởi chạy được PHP.\n";
        continue;
    }
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $passed = proc_close($process) === 0 && trim($output) === 'PASS' && trim($errors) === '';
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $case . PHP_EOL;
    if (!$passed) {
        $failed++;
        echo $output . $errors . PHP_EOL;
    }
}
echo (3 - $failed) . "/3 kiểm thử đạt.\n";
exit($failed ? 1 : 0);
