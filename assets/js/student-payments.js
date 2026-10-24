'use strict';
document.querySelectorAll('[data-payment-qr]').forEach((element) => {
    try {
        const url = new URL(element.dataset.paymentQr, window.location.href);
        const allowedOrigin = element.dataset.qrOrigin || window.location.origin;
        if (url.origin !== allowedOrigin) throw new Error('Invalid payment origin');
        const code = qrcode(0, 'M');
        code.addData(url.href);
        code.make();
        const image = document.createElement('img');
        image.src = code.createDataURL(5, 20);
        image.alt = 'Quét mã để mở trang thanh toán mô phỏng';
        element.replaceChildren(image);
    } catch (error) {
        element.textContent = 'Chưa thể hiển thị QR. Hãy dùng nút mở trang thanh toán bên dưới.';
    }
});
document.querySelectorAll('[data-payment-confirm], [data-qr-create]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.submitting) { event.preventDefault(); return; }
        form.dataset.submitting = 'true';
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = form.hasAttribute('data-qr-create') ? 'Đang tạo…' : 'Đang xác nhận…';
    });
});

document.querySelectorAll('[data-qr-expires]').forEach((element) => {
    const expires = Number(element.dataset.qrExpires);
    const serverNow = Number(element.dataset.serverNow);
    if (!Number.isFinite(expires) || !Number.isFinite(serverNow)) return;
    const lifetime = Math.max(0, (expires - serverNow) * 1000);
    const started = performance.now();
    const label = element.querySelector('strong');
    const update = () => {
        const left = Math.max(0, Math.ceil((lifetime - (performance.now() - started)) / 1000));
        label.textContent = `${Math.floor(left / 60)}:${String(left % 60).padStart(2, '0')}`;
        if (left === 0) window.location.reload(); // GET only reads the invoice; it never creates a new QR.
    };
    update();
    setInterval(update, 1000);
});
window.addEventListener('pageshow', (event) => {
    if (event.persisted) window.location.reload();
});
