<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Sinh viên');
require __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/StudentDashboard.php';
$dashboard = (new StudentDashboard($pdo))->forAccount((int) $_SESSION['user']['MaTK']);
$displayName = $dashboard['student']['HoTen'] ?? $_SESSION['user']['TenDangNhap'];
$stats = [
    'Phòng hiện tại' => $dashboard['room'],
    'Hợp đồng' => $dashboard['contract'],
    'Hóa đơn chưa thu' => $dashboard['invoices'],
    'Phản ánh đang xử lý' => $dashboard['reports'],
];
$icons = [
    'room' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><path d="M9 21v-8h6v8"/>',
    'contract' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
    'invoice' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2Z"/><path d="M9 7h6M9 11h6M9 15h3"/>',
    'report' => '<path d="M21 11a9 9 0 0 1-9 9H6l-4 2 2-5a9 9 0 1 1 17-6Z"/><path d="M12 7v5M12 16h.01"/>',
    'notice' => '<path d="m3 10 14-6v16L3 14Z"/><path d="M7 16v5h3v-4M21 8v8"/>',
    'bot' => '<rect x="4" y="7" width="16" height="14" rx="4"/><path d="M12 7V3M10 3h4M1 12v5M23 12v5M8 12h.01M16 12h.01M9 17h6"/>',
];
$renderIcon = static function (string $name) use ($icons): string {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
};
$statIcons = ['Phòng hiện tại' => 'room', 'Hợp đồng' => 'contract', 'Hóa đơn chưa thu' => 'invoice', 'Phản ánh đang xử lý' => 'report'];
$quickActions = [
    ['label' => 'Xem thông tin lưu trú', 'icon' => 'room', 'color' => 'blue'],
    ['label' => 'Xem thông báo', 'icon' => 'notice', 'color' => 'purple'],
    ['label' => 'Gửi phản ánh sự cố', 'icon' => 'report', 'color' => 'orange'],
    ['label' => 'Sử dụng Chatbot AI', 'icon' => 'bot', 'color' => 'green'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Sinh viên — Kanto KTX</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/style.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/student-dashboard.css">
</head>
<body class="student-page">
    <header class="student-header">
        <a class="site-brand" href="/Kanto-KTX/views/student/Dashboard.php" aria-label="Kanto — Trang chủ Sinh viên">
            <img class="site-logo" src="/Kanto-KTX/assets/images/logo.png" alt="">
            <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
        </a>
        <div class="student-header-actions">
            <button class="student-notification" type="button" disabled aria-label="Thông báo — đang được xây dựng" title="Thông báo đang được xây dựng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4M12 2V1"/></svg>
            </button>
            <details class="student-account">
                <summary>
                    <span class="student-avatar" aria-hidden="true"><svg viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="11" r="6"/><path d="M4 29a12 12 0 0 1 24 0Z"/></svg></span>
                    <span class="student-name"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="student-chevron" aria-hidden="true"></span>
                </summary>
                <div class="student-account-menu"><span>Sinh viên</span><a href="/Kanto-KTX/index.php">Về trang chủ Kanto</a></div>
            </details>
        </div>
    </header>
    <main class="student-main">
        <h1>Trang chủ Sinh viên</h1>
        <p class="student-intro">Theo dõi thông tin lưu trú và các chức năng cá nhân.</p>
        <?php if (!$dashboard['student']): ?>
            <p class="student-empty" role="status">Tài khoản chưa có hồ sơ sinh viên. Vui lòng liên hệ quản lý KTX để cập nhật thông tin.</p>
        <?php endif; ?>
        <section class="student-stats" aria-label="Tổng quan lưu trú">
            <?php foreach ($stats as $label => $value): ?>
                <div class="student-stat">
                    <span class="student-stat-icon"><?= $renderIcon($statIcons[$label]) ?></span>
                    <p><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></p>
                    <strong><?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endforeach; ?>
        </section>
        <section class="student-quick" aria-labelledby="quick-title">
            <h2 id="quick-title">Chức năng nhanh</h2>
            <div class="student-quick-grid">
                <?php foreach ($quickActions as $action): ?>
                    <button class="student-action student-action--<?= $action['color'] ?>" type="button" disabled title="Chức năng đang được xây dựng">
                        <span class="student-action-icon"><?= $renderIcon($action['icon']) ?></span>
                        <span class="student-action-label"><?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="student-action-arrow" aria-hidden="true">›</span>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../../includes/chatbot.php'; ?>
</body>
</html>
