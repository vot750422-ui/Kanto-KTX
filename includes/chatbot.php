<?php
require_once __DIR__ . '/../middleware/Auth.php';
?>
<link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/chatbot.css'), ENT_QUOTES, 'UTF-8') ?>">
<button class="chatbot-launcher" id="chatbot-launcher" type="button" aria-controls="chatbot-panel" aria-expanded="false">
    <span class="chatbot-launcher-icon" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M24 14V8M13 22H8v12h5M35 22h5v12h-5"/>
            <circle cx="24" cy="5" r="3"/>
            <rect x="13" y="16" width="22" height="24" rx="5"/>
            <circle cx="20" cy="26" r="1" fill="currentColor"/>
            <circle cx="28" cy="26" r="1" fill="currentColor"/>
            <path d="M20 33h8"/>
        </svg>
    </span>
    <span>Chatbot AI</span>
</button>
<section class="chatbot-panel" id="chatbot-panel" aria-labelledby="chatbot-title" hidden>
    <header class="chatbot-heading">
        <div><strong id="chatbot-title">Chatbot AI</strong><span>Trợ lý thông tin Kanto</span></div>
        <button id="chatbot-close" type="button" aria-label="Đóng trò chuyện">×</button>
    </header>
    <p class="chatbot-note">Hỏi về KTX và cách sử dụng hệ thống. Không nhập thông tin cá nhân vào chat.</p>
    <div class="chatbot-messages" id="chatbot-messages" role="log" aria-live="polite" aria-relevant="additions text">
        <p class="chatbot-message chatbot-message-bot">Xin chào! Tôi có thể hướng dẫn đăng ký lưu trú, cho biết sức chứa và chỗ còn trống của phòng. Bạn muốn hỏi gì?</p>
    </div>
    <form id="chatbot-form" action="<?= htmlspecialchars(appUrl('index.php?action=chatbot-send'), ENT_QUOTES, 'UTF-8') ?>" method="post">
        <label class="chatbot-label" for="chatbot-question">Nội dung câu hỏi</label>
        <div class="chatbot-input-row">
            <input id="chatbot-question" name="question" type="text" maxlength="500" autocomplete="off" placeholder="Ví dụ: Cách đăng ký? Phòng A101 còn chỗ không?" required>
            <button id="chatbot-send" type="submit">Gửi</button>
        </div>
        <p id="chatbot-status" role="status"></p>
    </form>
</section>
<script src="<?= htmlspecialchars(appUrl('assets/js/chatbot.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
