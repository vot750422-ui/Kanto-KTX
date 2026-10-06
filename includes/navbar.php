<?php
// Dashboard phải gọi requireRole() trước khi hiển thị navbar.
(static function (): void {
    $role = $_SESSION['user']['VaiTro'] ?? '';
    $menus = [
        'Sinh viên' => [['Thông tin lưu trú', 'room'], ['Thông báo', 'notice'], ['Gửi phản ánh sự cố', 'report'], ['Chatbot AI', 'bot']],
        'Nhân viên quản lý KTX' => [['Xét duyệt đơn đăng ký', 'document'], ['Quản lý sinh viên', 'users'], ['Quản lý hợp đồng', 'document'], ['Quản lý phòng ở', 'room'], ['Ghi chỉ số điện nước', 'bolt'], ['Xử lý phản ánh sự cố', 'report']],
        'Nhân viên kế toán' => [['Quản lý hóa đơn', 'document'], ['Thu tiền và thanh toán', 'money']],
        'Quản lý KTX' => [['Tổng quan và báo cáo', 'chart'], ['Quản lý nhân viên', 'users']],
    ];
    if (!isset($menus[$role])) {
        return;
    }
    $icons = [
        'home' => '<path d="m3 10 9-7 9 7v11H3Z"/><path d="M9 21v-8h6v8"/>',
        'room' => '<rect x="5" y="3" width="14" height="18" rx="1"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M10 21v-6h4v6"/>',
        'document' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 16h5"/>',
        'users' => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-3a7 7 0 0 1 14 0v3M17 3a4 4 0 0 1 0 8M22 21v-3a7 7 0 0 0-4-6"/>',
        'notice' => '<path d="m3 10 14-6v16L3 14ZM7 16v5h3v-4M21 8v8"/>',
        'report' => '<path d="M21 11a9 9 0 0 1-9 9H6l-4 2 2-5a9 9 0 1 1 17-6Z"/><path d="M12 7v5M12 16h.01"/>',
        'bot' => '<rect x="4" y="7" width="16" height="14" rx="4"/><path d="M12 7V3M10 3h4M8 12h.01M16 12h.01M9 17h6"/>',
        'bolt' => '<path d="m13 2-9 12h7l-1 8 10-13h-7Z"/>',
        'money' => '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/>',
        'chart' => '<path d="M3 3v18h18M7 16v-5M12 16V7M17 16v-8"/>',
    ];
    $items = [['Trang chủ', 'home', dashboardForRole($role)]];
    foreach ($menus[$role] as $item) {
        $items[] = [$item[0], $item[1], null]; // Thay null bằng URL khi chức năng hoàn thành.
    }
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<aside class="dashboard-sidebar">
    <h2><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></h2>
    <nav aria-label="Chức năng theo vai trò">
        <?php foreach ($items as [$label, $icon, $url]): ?>
            <?php if ($url !== null): ?>
                <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"<?= $currentPath === $url ? ' class="dashboard-nav-active" aria-current="page"' : '' ?>>
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
