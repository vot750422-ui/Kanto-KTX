(() => {
    'use strict';
    const launcher = document.getElementById('chatbot-launcher');
    const panel = document.getElementById('chatbot-panel');
    if (!launcher || !panel) return;
    const close = document.getElementById('chatbot-close');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-question');
    const send = document.getElementById('chatbot-send');
    const messages = document.getElementById('chatbot-messages');
    const status = document.getElementById('chatbot-status');
    function toggle(open) {
        panel.hidden = !open;
        launcher.setAttribute('aria-expanded', String(open));
        (open ? input : launcher).focus();
    }
    launcher.addEventListener('click', () => toggle(panel.hidden));
    close.addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !panel.hidden) toggle(false);
    });
    document.querySelector('[data-chatbot-open]')?.addEventListener('click', () => toggle(true));
    function append(text, role) {
        const item = document.createElement('p');
        item.className = `chatbot-message chatbot-message-${role}`;
        item.textContent = text;
        messages.append(item);
        while (messages.children.length > 40) messages.firstElementChild.remove();
        messages.scrollTop = messages.scrollHeight;
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (send.disabled) return;
        const question = input.value.trim();
        if (!question) {
            status.textContent = 'Vui lòng nhập nội dung câu hỏi.';
            input.focus();
            return;
        }
        const data = new FormData(form);
        data.set('question', question);
        append(question, 'user');
        input.value = '';
        send.disabled = true;
        input.disabled = true;
        status.textContent = 'Trợ lý đang trả lời…';
        const controller = new AbortController();
        // Đệm cho chuỗi API tối đa 28 giây và thời gian đọc nguồn phòng trên máy chủ.
        const timer = setTimeout(() => controller.abort(), 70000);
        try {
            const response = await fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin', signal: controller.signal });
            const result = await response.json();
            if (typeof result.message !== 'string') throw new Error('Invalid response');
            append(result.message, 'bot');
            if (!response.ok) input.value = question;
          } catch (error) {
            append(error.name === 'AbortError'
              ? 'Dịch vụ AI phản hồi quá chậm. Vui lòng thử lại sau ít phút.'
              : 'Trợ lý AI hiện không thể phản hồi. Vui lòng thử lại sau.', 'bot');
            input.value = question;
        } finally {
            clearTimeout(timer);
            send.disabled = false;
            input.disabled = false;
            status.textContent = '';
            if (!panel.hidden) input.focus();
        }
    });
})();
