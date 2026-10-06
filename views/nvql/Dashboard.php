<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Nhân viên quản lý KTX');
require __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/StaffDashboard.php';
$model = new StaffDashboard($pdo);
$summary = $model->summary();
$displayName = $model->nameForAccount((int) $_SESSION['user']['MaTK']) ?? $_SESSION['user']['TenDangNhap'];
$icons = [
    'home' => '<path d="m3 10 9-7 9 7v11H3Z"/><path d="M9 21v-8h6v8"/>',
    'applications' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8m-8 5 2 2 5-4"/>',
    'students' => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-3a7 7 0 0 1 14 0v3M17 3a4 4 0 0 1 0 8M22 21v-3a7 7 0 0 0-4-6"/>',
    'contracts' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
    'rooms' => '<rect x="5" y="3" width="14" height="18" rx="1"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M10 21v-6h4v6"/>',
    'utilities' => '<path d="m13 2-9 12h7l-1 8 10-13h-7Z"/>',
    'reports' => '<path d="M21 11a9 9 0 0 1-9 9H6l-4 2 2-5a9 9 0 1 1 17-6Z"/><path d="M12 7v5M12 16h.01"/>',
];
$icon = static function (string $key) use ($icons): string {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$key] . '</svg>';
};
$stats = ['applications' => 'Đơn đăng ký chờ xét duyệt', 'students' => 'Sinh viên đang lưu trú', 'rooms' => 'Phòng đang sử dụng', 'reports' => 'Phản ánh chờ xử lý'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Nhân viên quản lý KTX — Kanto</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/style.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/staff-dashboard.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/navbar.css">
</head>
<body class="staff-page">
    <header class="staff-header">
        <a class="site-brand" href="/Kanto-KTX/views/nvql/Dashboard.php" aria-label="Kanto — Trang chủ Nhân viên quản lý KTX">
            <img class="site-logo" src="/Kanto-KTX/assets/images/logo.png" alt="">
            <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
        </a>
        <details class="staff-account">
            <summary>
                <span class="staff-avatar" aria-hidden="true"><svg viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="11" r="6"/><path d="M4 29a12 12 0 0 1 24 0Z"/></svg></span>
                <span class="staff-account-text"><strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong><small>NVQL KTX</small></span>
                <span class="staff-chevron" aria-hidden="true"></span>
            </summary>
            <div class="staff-account-menu">
                <span>Nhân viên quản lý KTX</span>
                <a href="/Kanto-KTX/index.php">Về trang chủ Kanto</a>
                <?php require __DIR__ . '/../../includes/logout.php'; ?>
            </div>
        </details>
    </header>
    <div class="dashboard-layout">
        <?php require __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="staff-main">
            <h1>Trang chủ</h1>
            <p class="staff-intro">Quản lý các nghiệp vụ lưu trú của ký túc xá.</p>
            <section class="staff-stats" aria-label="Tổng quan quản lý KTX">
                <?php foreach ($stats as $key => $label): ?>
                    <article class="staff-stat staff-stat--<?= $key ?>">
                        <span class="staff-stat-icon"><?= $icon($key) ?></span>
                        <p><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></p>
                        <strong><?= $summary[$key] ?></strong>
                    </article>
                <?php endforeach; ?>
            </section>
        </main>
    </div>
</body>
</html>
