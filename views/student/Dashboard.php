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

        <section class="student-notifications-widget" aria-label="Thông báo mới nhất" style="margin-top: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #17233b; display: flex; align-items: center; gap: 10px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px; color: #2563eb;" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                    Thông báo mới nhất
                </h2>
                <a href="<?= htmlspecialchars(appUrl('index.php?action=notifications'), ENT_QUOTES, 'UTF-8') ?>" style="color: #2563eb; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                    Xem tất cả &rarr;
                </a>
            </div>
            <?php if (!empty($dashboard['notifications'])): ?>
                <div style="display: grid; gap: 12px;">
                    <?php foreach ($dashboard['notifications'] as $nb): ?>
                        <div style="padding: 18px 22px; background: #fff; border: 1px solid #e1e8f2; border-radius: 12px; box-shadow: 0 4px 12px rgba(32, 55, 100, 0.04);">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 16px; margin-bottom: 8px;">
                                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">
                                    <a href="<?= htmlspecialchars(appUrl('index.php?action=notification-detail&id=' . rawurlencode($nb['MaThongBao'])), ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: none;">
                                        <?= htmlspecialchars($nb['TieuDe'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h3>
                                <time style="font-size: 13px; color: #64748b; white-space: nowrap;">
                                    <?= (new DateTimeImmutable($nb['ThoiGianTao']))->format('d/m/Y H:i') ?>
                                </time>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($nb['NoiDung'], ENT_QUOTES, 'UTF-8')) ?>
                            </p>
                            <?php if (!empty($nb['MaHoaDon'])): ?>
                                <div style="margin-top: 10px;">
                                    <span style="display: inline-block; padding: 3px 10px; background: #eff6ff; color: #1d4ed8; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        Hóa đơn: <?= htmlspecialchars($nb['MaHoaDon'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="padding: 24px; background: #fff; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; text-align: center; font-size: 14px;">
                    Hiện chưa có thông báo mới nào dành cho bạn.
                </div>
            <?php endif; ?>
        </section>
    </main>
    </div>
    <?php require __DIR__ . '/../../includes/chatbot.php'; ?>
</body>
</html>
