<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Sinh viên');
require __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/StudentDashboard.php';
$dashboard = (new StudentDashboard($pdo))->forAccount((int) $_SESSION['user']['MaTK']);
$displayName = $dashboard['student']['HoTen'] ?? $_SESSION['user']['TenDangNhap'];
$stats = [
    'Phòng được bố trí' => $dashboard['room'],
    'Hợp đồng' => $dashboard['contract'],
    'Hóa đơn chưa thu' => $dashboard['invoices'],
    'Phản ánh đang xử lý' => $dashboard['reports'],
];
$icons = [
    'room' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><path d="M9 21v-8h6v8"/>',
    'contract' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
    'invoice' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2Z"/><path d="M9 7h6M9 11h6M9 15h3"/>',
    'report' => '<path d="M21 11a9 9 0 0 1-9 9H6l-4 2 2-5a9 9 0 1 1 17-6Z"/><path d="M12 7v5M12 16h.01"/>',
];
$renderIcon = static function (string $name) use ($icons): string {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
};
$statIcons = ['Phòng được bố trí' => 'room', 'Hợp đồng' => 'contract', 'Hóa đơn chưa thu' => 'invoice', 'Phản ánh đang xử lý' => 'report'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php require __DIR__ . '/../../includes/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Sinh viên — Kanto KTX</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/student-dashboard.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/navbar.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="student-page">
    <?php require __DIR__ . '/../../includes/student-header.php'; ?>
    <div class="dashboard-layout">
    <?php require __DIR__ . '/../../includes/navbar.php'; ?>
    <main class="student-main">
        <h1>Trang chủ Sinh viên</h1>
        <p class="student-intro">Theo dõi thông tin lưu trú và các chức năng cá nhân.</p>
        <?php if (!$dashboard['student']): ?>
            <p class="student-empty" role="status">Tài khoản chưa có hồ sơ sinh viên. Vui lòng liên hệ quản lý KTX để cập nhật thông tin.</p>
        <?php elseif ($dashboard['student']['TrangThaiLuuTru'] === 'Chờ thanh toán'): ?>
            <p class="student-empty" role="status">Hồ sơ đã được duyệt, chỗ đã chọn đang được giữ. Bạn đang chờ thanh toán hóa đơn tiền phòng đầu tiên để hoàn tất thủ tục lưu trú; chưa có hợp đồng.</p>
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
    </main>
    </div>
    <?php require __DIR__ . '/../../includes/chatbot.php'; ?>
</body>
</html>
