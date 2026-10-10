<?php
// Không cho truy cập view trực tiếp để bỏ qua controller/phân quyền.
if (!isset($content, $displayName)) {
    http_response_code(404);
    exit;
}
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$url = static fn(string $value): string => htmlspecialchars(appUrl($value), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xét duyệt đơn đăng ký — Kanto</title>
    <link rel="stylesheet" href="<?= $url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= $url('assets/css/staff-dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $url('assets/css/navbar.css') ?>">
    <link rel="stylesheet" href="<?= $url('assets/css/approval.css') ?>">
</head>
<body class="staff-page">
    <header class="staff-header">
        <a class="site-brand" href="<?= $url('views/nvql/Dashboard.php') ?>">
            <img class="site-logo" src="<?= $url('assets/images/logo.png') ?>" alt="">
            <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
        </a>
        <details class="staff-account">
            <summary><span class="staff-account-text"><strong><?= $escape($displayName) ?></strong><small>NVQL KTX</small></span><span class="staff-chevron" aria-hidden="true"></span></summary>
            <div class="staff-account-menu">
                <a href="<?= $url('index.php') ?>">Về trang chủ Kanto</a>
                <?php require __DIR__ . '/../../includes/logout.php'; ?>
            </div>
        </details>
    </header>
    <div class="dashboard-layout">
        <?php require __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="staff-main approval-main">
            <h1>Xét duyệt đơn đăng ký</h1>
            <p class="staff-intro">Kiểm tra thông tin và xử lý các đơn đăng ký lưu trú đang chờ duyệt.</p>
            <?php require $content; ?>
        </main>
    </div>
    <script src="<?= $url('assets/js/approval.js') ?>" defer></script>
</body>
</html>
