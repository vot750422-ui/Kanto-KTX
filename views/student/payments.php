<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Sinh viên');
if (!isset($screen)) {
    header('Location: ' . appUrl('index.php?action=student-payments'));
    exit;
}
$displayName = $profile['HoTen'] ?? $_SESSION['user']['TenDangNhap'];
$escape = static fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
$money = static fn($value) => number_format((float)$value, 0, ',', '.') . ' đ';
$route = static fn(string $action, string $id) => appUrl('index.php?action=' . $action . '&invoice=' . rawurlencode($id));
$date = static fn($value) => $value ? date('d/m/Y H:i', strtotime($value)) : '—';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php require __DIR__ . '/../../includes/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán hóa đơn — Kanto KTX</title>
    <?php foreach (['style', 'student-dashboard', 'navbar', 'student-payments'] as $stylesheet): ?>
        <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/' . $stylesheet . '.css')) ?>">
    <?php endforeach; ?>
    <script defer src="<?= $escape(appUrl('assets/js/vendor/qrcode.js')) ?>"></script>
    <script defer src="<?= $escape(appUrl('assets/js/student-payments.js')) ?>"></script>
</head>
<body class="student-page">
    <?php require __DIR__ . '/../../includes/student-header.php'; ?>
    <div class="dashboard-layout">
        <?php require __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="student-main student-payments-main">
            <h1>Thanh toán hóa đơn</h1>
            <p class="student-intro">Theo dõi hóa đơn của bạn và thực hiện thanh toán mô phỏng.</p>
            <?php if ($success !== ''): ?><p class="payment-success" role="status"><?= $escape($success) ?></p><?php endif; ?>
            <?php if ($error !== ''): ?>
                <p class="payment-error" role="alert"><?= $escape($error) ?></p>
                <a class="payment-button payment-secondary" href="<?= $escape(appUrl('index.php?action=student-payments')) ?>">Về danh sách hóa đơn</a>
                <?php if ($bill): ?><a class="payment-button" href="<?= $escape($route('student-invoice', $bill['MaHoaDon'])) ?>">Mở lại hóa đơn</a><?php endif; ?>
            <?php elseif ($screen === 'student-payments'): ?>
                <form class="payment-filters" method="get" action="<?= $escape(appUrl('index.php')) ?>">
                    <input type="hidden" name="action" value="student-payments">
                    <label for="payment-status">Trạng thái</label>
                    <select id="payment-status" name="status">
                        <?php foreach (['unpaid' => 'Chưa thu', 'paid' => 'Đã thu', 'all' => 'Tất cả'] as $value => $label): ?>
                            <option value="<?= $value ?>"<?= $status === $value ? ' selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="payment-button" type="submit">Lọc hóa đơn</button>
                    <span><?= (int)$listing['total'] ?> hóa đơn</span>
                </form>
                <?php if (!$profile): ?>
                    <p class="student-empty" role="status">Tài khoản chưa có hồ sơ sinh viên. Vui lòng liên hệ quản lý KTX.</p>
                <?php elseif (!$listing['items']): ?>
                    <p class="student-empty" role="status">Bạn chưa có hóa đơn phù hợp với trạng thái đã chọn.</p>
                <?php else: ?>
                    <div class="payment-table-wrap">
                        <table class="payment-table">
                            <caption>Danh sách hóa đơn của bạn</caption>
                            <thead><tr><th scope="col">Mã hóa đơn</th><th scope="col">Loại hóa đơn</th><th scope="col">Phòng</th><th scope="col">Kỳ</th><th scope="col">Tổng tiền</th><th scope="col">Trạng thái</th><th scope="col">Chi tiết</th></tr></thead>
                            <tbody>
                            <?php foreach ($listing['items'] as $item): ?>
                                <tr>
                                    <td><?= $escape($item['MaHoaDon']) ?></td><td><?= $escape($item['LoaiHoaDon']) ?></td><td><?= $escape($item['SoPhong']) ?></td>
                                    <td><?= $escape($item['LoaiHoaDon'] === 'Tiền phòng' ? $item['TenHocKy'] . ' · ' . $item['NamHoc'] : $item['KyHoaDon']) ?></td>
                                    <td class="payment-amount"><?= $escape($money($item['SoTienPhaiThu'])) ?></td><td><span class="payment-badge<?= $item['TrangThai'] === 'Đã thu' ? ' is-paid' : '' ?>"><?= $escape($item['TrangThai']) ?></span></td>
                                    <td><a class="payment-view-button" href="<?= $escape($route('student-invoice', $item['MaHoaDon'])) ?>" aria-label="Xem hóa đơn <?= $escape($item['MaHoaDon']) ?>">Xem hóa đơn</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($listing['pages'] > 1): ?>
                        <nav class="payment-pagination" aria-label="Phân trang hóa đơn">
                            <?php if ($listing['page'] > 1): ?><a href="<?= $escape(appUrl('index.php?action=student-payments&status=' . $status . '&page=' . ($listing['page'] - 1))) ?>">← Trang trước</a><?php endif; ?>
                            <span>Trang <?= (int)$listing['page'] ?> / <?= (int)$listing['pages'] ?></span>
                            <?php if ($listing['page'] < $listing['pages']): ?><a href="<?= $escape(appUrl('index.php?action=student-payments&status=' . $status . '&page=' . ($listing['page'] + 1))) ?>">Trang sau →</a><?php endif; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            <?php elseif ($bill): ?>
                <a class="payment-back" href="<?= $escape(appUrl('index.php?action=student-payments&status=all')) ?>">← Danh sách hóa đơn</a>
                <div class="payment-detail-grid<?= $bill['TrangThai'] === 'Đã thu' ? ' is-paid' : '' ?>">
                    <section class="payment-card">
                        <h2>Hóa đơn <?= $escape($bill['MaHoaDon']) ?></h2>
                        <span class="payment-badge<?= $bill['TrangThai'] === 'Đã thu' ? ' is-paid' : '' ?>"><?= $escape($bill['TrangThai']) ?></span>
                        <p class="payment-total"><?= $escape($money($bill['TongTien'])) ?></p>
                        <dl>
                            <div><dt>Loại hóa đơn</dt><dd><?= $escape($bill['LoaiHoaDon']) ?></dd></div>
                            <div><dt>Phòng</dt><dd><?= $escape($bill['SoPhong']) ?></dd></div>
                            <div><dt>Kỳ hóa đơn</dt><dd><?= $escape($bill['LoaiHoaDon'] === 'Tiền phòng' ? $bill['TenHocKy'] . ' · ' . $bill['NamHoc'] : $bill['Thang'] . '/' . $bill['Nam']) ?></dd></div>
                            <div><dt>Ngày lập</dt><dd><?= $escape($date($bill['ThoiGianLap'])) ?></dd></div>
                            <?php if ($bill['LoaiHoaDon'] === 'Điện nước'): ?>
                                <div><dt>Điện tiêu thụ</dt><dd><?= $escape($bill['DienTieuThu']) ?> kWh · <?= $escape($money($bill['TienDien'])) ?></dd></div>
                                <div><dt>Nước tiêu thụ</dt><dd><?= $escape($bill['NuocTieuThu']) ?> m³ · <?= $escape($money($bill['TienNuoc'])) ?></dd></div>
                            <?php endif; ?>
                            <?php if ($bill['TrangThai'] === 'Đã thu'): ?><div><dt>Thời gian thanh toán</dt><dd><?= $escape($date($bill['ThoiGianThanhToan'])) ?></dd></div><?php endif; ?>
                        </dl>
                        <?php if ($bill['LoaiHoaDon'] === 'Điện nước'): ?><p class="payment-help">Đây là phần tiền điện nước được phân bổ riêng cho bạn từ tổng hóa đơn của phòng. Các sinh viên khác thanh toán độc lập.</p><?php endif; ?>
                    </section>
                    <?php if ($bill['TrangThai'] !== 'Đã thu'): ?>
                    <section class="payment-card payment-checkout">
                        <?php if ($screen === 'student-payment'): ?>
                            <h2>Xác nhận thanh toán mô phỏng</h2>
                            <p>Nội dung: Thanh toán hóa đơn <?= $escape($bill['MaHoaDon']) ?>.</p>
                            <p>Nhấn nút dưới đây để mô phỏng thanh toán thành công số tiền <strong><?= $escape($money($bill['TongTien'])) ?></strong>.</p>
                            <form method="post" action="<?= $escape(appUrl('index.php?action=student-payment-confirm')) ?>" data-payment-confirm>
                                <input type="hidden" name="invoice" value="<?= $escape($bill['MaHoaDon']) ?>">
                                <input type="hidden" name="transaction_token" value="<?= $escape($intent) ?>">
                                <button class="payment-button" type="submit">Xác nhận thanh toán mô phỏng</button>
                            </form>
                            <a class="payment-cancel" href="<?= $escape($route('student-invoice', $bill['MaHoaDon'])) ?>">Hủy, quay lại hóa đơn</a>
                            <p class="payment-help">Phiên xác nhận có hiệu lực 15 phút. Mở trang hoặc quét QR chưa làm thay đổi trạng thái hóa đơn.</p>
                        <?php else: ?>
                            <h2>Mã QR thanh toán</h2>
                            <?php if ($checkoutUrl !== ''): ?>
                                <div class="payment-qr" data-payment-qr="<?= $escape($checkoutUrl) ?>" data-qr-origin="<?= $escape($checkoutOrigin) ?>" aria-label="Mã QR mở trang thanh toán"></div>
                                <p class="payment-qr-timer" data-qr-expires="<?= $escape(strtotime($qrState['expires'])) ?>" data-server-now="<?= time() ?>">Mã QR còn hiệu lực trong <strong>5:00</strong>.</p>
                            <?php elseif ($qrState['active']): ?>
                                <p role="status">Hóa đơn đang có mã QR còn hiệu lực trên trình duyệt đã tạo. Bạn có thể mở lại ở đó.</p>
                                <p class="payment-qr-timer" data-qr-expires="<?= $escape(strtotime($qrState['expires'])) ?>" data-server-now="<?= time() ?>">Có thể tạo mã mới sau <strong>5:00</strong>.</p>
                            <?php else: ?>
                                <p>Tạo mã QR khi bạn sẵn sàng thanh toán. Mã có hiệu lực 5 phút và chỉ dùng một lần.</p>
                                <form method="post" action="<?= $escape(appUrl('index.php?action=student-qr-create')) ?>" data-qr-create>
                                    <input type="hidden" name="invoice" value="<?= $escape($bill['MaHoaDon']) ?>">
                                    <button class="payment-button" type="submit">Tạo mã QR</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
