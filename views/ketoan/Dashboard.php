<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Nhân viên kế toán');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_SESSION['user']['VaiTro'], ENT_QUOTES, 'UTF-8') ?> — Kanto</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/staff-dashboard.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/navbar.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="staff-page">
    <header class="staff-header">
        <a class="site-brand" href="<?= htmlspecialchars(appUrl('index.php'), ENT_QUOTES, 'UTF-8') ?>">
            <img class="site-logo" src="<?= htmlspecialchars(appUrl('assets/images/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Logo Kanto">
            <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
        </a>
        <span><?= htmlspecialchars($_SESSION['user']['TenDangNhap'], ENT_QUOTES, 'UTF-8') ?></span>
    </header>
    <div class="dashboard-layout">
        <?php require __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="staff-main">
<h1>Trang chủ Nhân viên kế toán</h1>
<?php require __DIR__ . '/../../includes/logout.php'; ?>
<h2>Xin chào: <?= htmlspecialchars($_SESSION['user']['TenDangNhap'] ?? '') ?></h2>

<h2>Vai trò: <?= htmlspecialchars($_SESSION['user']['VaiTro'] ?? '') ?></h2>
        </main>
    </div>
</body>
</html>
