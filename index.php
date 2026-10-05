<?php
require_once __DIR__ . '/middleware/Role.php';
// Xử lý form trước khi chuyển hướng theo phiên đăng nhập có sẵn.
if (($_GET['action'] ?? '') === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/controllers/AuthController.php';
    $controller = new AuthController($pdo);
    $controller->login();
}
if (isset($_SESSION['user'])) {
    $user = requireAuth();
    $dashboard = dashboardForRole($user['VaiTro']);
    if ($dashboard !== null) {
        header('Location: ' . $dashboard);
        exit;
    }
    unset($_SESSION['user']);
}
require __DIR__ . '/views/auth/login.php';
