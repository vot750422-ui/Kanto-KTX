<?php
// Dashboard phải gọi requireRole() trước khi hiển thị navbar.
(static function (): void {
    $role = $_SESSION['user']['VaiTro'] ?? '';
    $menus = [
        'Sinh viên' => [['Thông tin lưu trú', 'room', 'accommodation'], ['Thanh toán', 'money', 'student-payments'], ['Thông báo', 'notice', 'notifications'], ['Gửi phản ánh sự cố', 'report', 'incident-report']],
        'Nhân viên quản lý KTX' => [['Xét duyệt đơn đăng ký', 'document', 'approval'], ['Quản lý sinh viên', 'users', 'students'], ['Quản lý hợp đồng', 'document', 'contracts'], ['Ghi chỉ số điện nước', 'bolt', 'utility-meters'], ['Xử lý phản ánh sự cố', 'report', 'incidents']],
        'Nhân viên kế toán' => [['Quản lý hóa đơn', 'document', 'payments']],
        'Quản lý KTX' => [['Tổng quan và báo cáo', 'chart', 'statistics']],
    ];
    if (!isset($menus[$role])) {
        return;
    }
    $icons = [
        'home' => '<path d="m3 10 9-7 9 7v11H3Z"/><path d="M9 21v-8h6v8"/>',
        'room' => '<rect x="5" y="3" width="14" height="18" rx="1"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M10 21v-6h4v6"/>',
        'document' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 16h5"/>',
        'users' => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-3a7 7 0 0 1 14 0v3M17 3a4 4 0 0 1 0 8M22 21v-3a7 7 0 0 0-4-6"/>',
        'report' => '<path d="M21 11a9 9 0 0 1-9 9H6l-4 2 2-5a9 9 0 1 1 17-6Z"/><path d="M12 7v5M12 16h.01"/>',
        'notice' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'bolt' => '<path d="m13 2-9 12h7l-1 8 10-13h-7Z"/>',
        'money' => '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/>',
        'chart' => '<path d="M3 3v18h18M7 16v-5M12 16V7M17 16v-8"/>',
    ];
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $currentAction = $_GET['action'] ?? '';
    $actionGroups = [
        'approval' => ['approval', 'approval-detail', 'approval-proof', 'approval-approve', 'approval-reject', 'approval-email-retry'],
        'contracts' => ['contracts', 'contract-detail'],
        'students' => ['students', 'student-detail', 'student-update'],
        'incident-report' => ['incident-report'],
        'notifications' => ['notifications','notification-detail'],
        'incidents' => ['incidents','incident-detail','incident-resolve'],
        'payments' => ['payments', 'payment-confirm'],
        'accommodation' => ['accommodation'],
        'student-payments' => ['student-payments', 'student-invoice', 'student-qr-create', 'student-payment', 'student-payment-confirm'],
        'statistics' => ['statistics'],
        'utility-meters' => ['utility-meters'],
    ];
    $dashboardUrl = dashboardForRole($role);
    $items = [['Trang chủ', 'home', $dashboardUrl, $currentPath === $dashboardUrl]];
    foreach ($menus[$role] as $item) {
        $action = $item[2] ?? null;
        $url = $action === null ? null : appUrl('index.php?action=' . $action);
        $active = $action !== null && ($item[3] ?? true) && in_array($currentAction, $actionGroups[$action], true);
        $items[] = [$item[0], $item[1], $url, $active];
    }
?>
<aside class="dashboard-sidebar">
    <h2><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></h2>
    <nav aria-label="Chức năng theo vai trò">
        <?php foreach ($items as [$label, $icon, $url, $active]): ?>
            <?php if ($url !== null): ?>
                <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"<?= $active ? ' class="dashboard-nav-active" aria-current="page"' : '' ?>>
            <?php else: ?>
                <button type="button" disabled title="Chức năng đang được xây dựng">
            <?php endif; ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$icon] ?></svg>
                <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
            <?= $url !== null ? '</a>' : '</button>' ?>
        <?php endforeach; ?>
    </nav>
</aside>
<?php })(); ?>
