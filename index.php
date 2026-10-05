<?php
require_once __DIR__ . '/middleware/Role.php';
if (($_GET['action'] ?? '') === 'login') {
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
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ — Kanto KTX</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/style.css">
</head>
<body class="home-page">
    <div class="home-shell">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="home-hero">
            <div class="home-hero-content">
                <p class="home-eyebrow"><span aria-hidden="true"></span> KANTO · KÝ TÚC XÁ SINH VIÊN</p>
                <h1>Nơi khởi đầu<br><span>hành trình đại học</span><br>của bạn</h1>
                <p class="home-description">Theo dõi thông tin lưu trú, cập nhật thông báo và quản lý các nhu cầu cá nhân trong một hệ thống.</p>
                <div class="home-hero-actions">
                    <button class="home-register" type="button" disabled title="Chức năng đăng ký lưu trú đang được xây dựng">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
                        Đăng ký lưu trú
                        <span aria-hidden="true">→</span>
                    </button>
                    <a class="home-secondary" href="/Kanto-KTX/index.php?action=login">Đăng nhập tài khoản <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            <?php require __DIR__ . '/includes/chatbot.php'; ?>
        </main>
    </div>
</body>
</html>
