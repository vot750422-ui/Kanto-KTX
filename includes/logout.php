<?php
if (!isset($_SESSION['logout_token'])) {
    $_SESSION['logout_token'] = bin2hex(random_bytes(32));
}
?>
<link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/logout.css'), ENT_QUOTES, 'UTF-8') ?>">
<form class="logout-form" method="POST" action="<?= htmlspecialchars(appUrl('index.php?action=logout'), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['logout_token'], ENT_QUOTES, 'UTF-8') ?>">
    <button class="logout-button" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 3H4v18h6M10 12h11M16 7l5 5-5 5"/></svg>
        Đăng xuất
    </button>
</form>
