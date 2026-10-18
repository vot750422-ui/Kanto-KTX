<?php
// The calling page checks authentication and permissions before rendering.
$accountRole = (string) ($_SESSION['user']['VaiTro'] ?? '');
$accountName = (string) ($displayName ?? $_SESSION['user']['TenDangNhap'] ?? '');
?>
<link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/account-menu.css'), ENT_QUOTES, 'UTF-8') ?>">
<details class="account-menu">
    <summary aria-label="Menu tài khoản">
        <span class="account-avatar" aria-hidden="true"><svg viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="11" r="6"/><path d="M4 29a12 12 0 0 1 24 0Z"/></svg></span>
        <span class="account-text"><strong><?= htmlspecialchars($accountName, ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($accountRole, ENT_QUOTES, 'UTF-8') ?></small></span>
        <span class="account-chevron" aria-hidden="true"></span>
    </summary>
    <div class="account-dropdown">
        <span><?= htmlspecialchars($accountRole, ENT_QUOTES, 'UTF-8') ?></span>
        <?php require __DIR__ . '/logout.php'; ?>
    </div>
</details>
