<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Quản lý KTX');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_SESSION['user']['VaiTro'], ENT_QUOTES, 'UTF-8') ?> — Kanto</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/style.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/staff-dashboard.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/navbar.css">
</head>
<body class="staff-page">
    <header class="staff-header">
        <a class="site-brand" href="/Kanto-KTX/index.php">
            <img class="site-logo" src="/Kanto-KTX/assets/images/logo.png" alt="Logo Kanto">
            <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
        </a>
        <details class="staff-account">
            <summary>
                <span class="staff-avatar" aria-hidden="true"><svg viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="11" r="6"/><path d="M4 29a12 12 0 0 1 24 0Z"/></svg></span>
                <span class="staff-account-text"><strong><?= htmlspecialchars($_SESSION['user']['TenDangNhap'], ENT_QUOTES, 'UTF-8') ?></strong><small>Quản lý KTX</small></span>
                <span class="staff-chevron" aria-hidden="true"></span>
            </summary>
            <div class="staff-account-menu">
                <span>Quản lý KTX</span>
                <a href="/Kanto-KTX/index.php">Về trang chủ Kanto</a>
                <?php require __DIR__ . '/../../includes/logout.php'; ?>
            </div>
        </details>
    </header>
    <div class="dashboard-layout">
        <?php require __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="staff-main">
            <h1>Trang chủ Quản lý KTX</h1>
        </main>
    </div>
</body>
</html>
