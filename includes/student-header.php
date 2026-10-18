<?php
// Pages authorize the student role before rendering this shared header.
?>
    <header class="student-header">
        <a class="site-brand" href="<?= htmlspecialchars(appUrl('views/student/Dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Kanto — Trang chủ Sinh viên">
            <img class="site-logo" src="<?= htmlspecialchars(appUrl('assets/images/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
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
                <div class="student-account-menu">
                    <span>Sinh viên</span>
                    <?php require __DIR__ . '/logout.php'; ?>
                </div>
            </details>
        </div>
    </header>
