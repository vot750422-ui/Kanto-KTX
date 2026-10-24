'use strict';
const opening = document.querySelector('[data-checkout-open]');
if (opening) {
    const token = window.location.hash.slice(1);
    // Remove the capability from history; send it only in a POST body.
    window.history.replaceState(null, '', window.location.pathname + window.location.search);
    if (/^[a-f0-9]{64}$/.test(token)) {
        opening.querySelector('[name="link_token"]').value = token;
        opening.submit();
    } else {
        document.getElementById('checkout-opening').textContent = 'Liên kết không hợp lệ. Vui lòng mở lại mã QR từ hóa đơn.';
    }
}
const confirmation = document.querySelector('[data-checkout-confirm]');
if (confirmation) {
    confirmation.addEventListener('submit', (event) => {
        if (confirmation.dataset.submitting) { event.preventDefault(); return; }
        confirmation.dataset.submitting = 'true';
        const button = confirmation.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Đang xác nhận…';
    });
}
window.addEventListener('pageshow', (event) => {
    if (event.persisted) window.location.reload();
});