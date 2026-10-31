<?php
// Pages authorize the student role before rendering this shared header.
?>
    <header class="student-header">
        <a class="site-brand" href="<?= htmlspecialchars(appUrl('views/student/Dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Kanto — Trang chủ Sinh viên">
            <img class="site-logo" src="<?= htmlspecialchars(appUrl('assets/images/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
            <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
        </a>
        <div class="student-header-actions">
            <a class="student-notification" href="<?= htmlspecialchars(appUrl('index.php?action=notifications'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Xem thông báo" title="Xem thông báo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4M12 2V1"/></svg>
            </a>
            <?php require __DIR__ . '/account-menu.php'; ?>
        </div>
    </header>
