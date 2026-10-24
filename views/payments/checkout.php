<?php
require_once __DIR__ . '/../../config/app.php';
if (!isset($csrf, $error)) { http_response_code(404); exit; }
$escape = static fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
$money = static fn($value) => number_format((float)$value, 0, ',', '.') . ' đ';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Xác nhận thanh toán — Kanto KTX</title>
    <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/student-payments.css')) ?>">
    <script defer src="<?= $escape(appUrl('assets/js/payment-checkout.js')) ?>"></script>
</head>
<body class="checkout-page">
    <main class="checkout-shell">
        <p class="checkout-brand">KANTO · KÝ TÚC XÁ SINH VIÊN</p>
        <section class="payment-card payment-checkout">
            <h1><?= $receipt ? 'Thanh toán thành công' : 'Thanh toán hóa đơn' ?></h1>
            <?php if ($error !== ''): ?>
                <p class="payment-error" role="alert"><?= $escape($error) ?></p>
                <p>Bạn có thể đóng trang này. Nếu liên kết đã hết hạn, hãy mở lại hóa đơn trên tài khoản sinh viên để tạo QR mới.</p>
            <?php elseif ($receipt): ?>
                <p class="payment-success" role="status">Hóa đơn <?= $escape($receipt['MaHoaDon']) ?> đã được ghi nhận Đã thu.</p>
                <p class="payment-total"><?= $escape($money($receipt['TongTien'])) ?></p>
                <p>Bạn có thể đóng trang này và tải lại danh sách hóa đơn trên máy tính.</p>
            <?php elseif ($bill): ?>
                <dl>
                    <div><dt>Mã hóa đơn</dt><dd><?= $escape($bill['MaHoaDon']) ?></dd></div>
                    <div><dt>Loại hóa đơn</dt><dd><?= $escape($bill['LoaiHoaDon']) ?></dd></div>
                    <div><dt>Nội dung</dt><dd>Thanh toán <?= $escape($bill['MaHoaDon']) ?></dd></div>
                </dl>
                <p class="payment-total"><?= $escape($money($bill['TongTien'])) ?></p>
                <?php if ($bill['LoaiHoaDon'] === 'Điện nước'): ?><p class="payment-help">Số tiền trên là tổng hóa đơn điện nước của phòng.</p><?php endif; ?>
                <form method="post" action="<?= $escape(appUrl('index.php?action=payment-checkout-confirm')) ?>" data-checkout-confirm>
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>">
                    <input type="hidden" name="link_token" value="<?= $escape($token) ?>">
                    <button class="payment-button" type="submit">Xác nhận thanh toán mô phỏng</button>
                </form>
                <p class="payment-help">Không cần đăng nhập. Chỉ khi bạn nhấn xác nhận, hóa đơn mới được ghi nhận thanh toán. Đóng trang để hủy thao tác.</p>
            <?php else: ?>
                <p id="checkout-opening" role="status">Đang mở liên kết thanh toán…</p>
                <form hidden method="post" action="<?= $escape(appUrl('index.php?action=payment-checkout')) ?>" data-checkout-open>
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>">
                    <input type="hidden" name="link_token" value="">
                </form>
                <noscript><p>Hãy bật JavaScript rồi mở lại QR để xem hóa đơn. Không cần đăng nhập sinh viên.</p></noscript>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
