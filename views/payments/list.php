<?php
if (!isset($listing, $model, $student, $status)) { http_response_code(404); exit; }
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$filters ??= ['invoice'=>'','name'=>'','room'=>'','type'=>'','period'=>''];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hóa đơn — Kanto</title>
    <?php require __DIR__ . '/../../includes/head.php'; ?>
    <?php foreach (['style','staff-dashboard','navbar','approval','payments'] as $css): ?>
        <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/' . $css . '.css')) ?>">
    <?php endforeach; ?>
</head>
<body class="staff-page">
    <header class="staff-header">
        <a class="site-brand" href="<?= $escape(appUrl('views/ketoan/Dashboard.php')) ?>">
            <img class="site-logo" src="<?= $escape(appUrl('assets/images/logo.png')) ?>" alt="Logo Kanto">
            <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
        </a>
        <?php require __DIR__ . '/../../includes/account-menu.php'; ?>
    </header>
    <div class="dashboard-layout">
        <?php require __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="staff-main payment-main">
            <h1>Quản lý hóa đơn</h1>
            <p class="staff-intro">Tra cứu, theo dõi và xử lý các hóa đơn của sinh viên.</p>
            <?php if ($error): ?><p class="approval-message error" role="alert"><?= $escape($error) ?></p><?php endif; ?>
            <?php if ($success): ?><p class="approval-message" role="status"><?= $escape($success) ?></p><?php endif; ?>
            <form class="approval-panel payment-filters" method="get" action="<?= $escape(appUrl('index.php')) ?>">
                <input type="hidden" name="action" value="payments">
                <label>Mã hóa đơn<input name="invoice" maxlength="12" value="<?= $escape($filters['invoice']) ?>" placeholder="Nhập mã hóa đơn"></label>
                <label>MSSV<input type="text" name="student" maxlength="15" value="<?= $escape($student) ?>" placeholder="Nhập MSSV"></label>
                <label>Họ và tên<input name="name" maxlength="100" value="<?= $escape($filters['name']) ?>" placeholder="Nhập họ và tên"></label>
                <label>Số phòng<input name="room" maxlength="20" value="<?= $escape($filters['room']) ?>" placeholder="Nhập số phòng"></label>
                <label>Loại hóa đơn<select name="type">
                    <?php foreach ([''=>'Tất cả','Tiền phòng'=>'Tiền phòng','Điện nước'=>'Điện nước'] as $value=>$label): ?>
                        <option value="<?= $escape($value) ?>"<?= $value === $filters['type'] ? ' selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select></label>
                <label>Trạng thái<select name="status">
                    <?php foreach (['unpaid'=>'Chưa thu','paid'=>'Đã thu','all'=>'Tất cả'] as $value=>$label): ?>
                        <option value="<?= $value ?>"<?= $value === $status ? ' selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select></label>
                <label>Kỳ hóa đơn<input type="month" name="period" value="<?= $escape($filters['period']) ?>"></label>
                <div class="payment-filter-actions"><button class="approval-button primary" type="submit">Tìm kiếm</button>
                <a class="approval-button" href="<?= $escape(appUrl('index.php?action=payments')) ?>">Đặt lại</a>
                </div>
            </form>
            <section class="approval-panel">
                <h2>Danh sách hóa đơn</h2>
                <div class="payment-table-scroll">
                    <table class="payment-table">
                        <thead><tr><th>STT</th><th>Mã hóa đơn</th><th>Loại hóa đơn</th><th>MSSV</th><th>Họ và tên</th><th>Phòng</th><th>Kỳ</th><th>Số tiền (VND)</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                        <tbody>
                        <?php foreach ($listing['items'] as $i => $item): ?>
                            <tr>
                                <td><?= ($listing['page'] - 1) * 20 + $i + 1 ?></td>
                                <td><?= $escape($item['MaHoaDon']) ?></td>
                                <td><?= $escape($item['LoaiHoaDon']) ?></td>
                                <td><?= $escape($item['MSSV']) ?></td>
                                <td><?= $escape($item['HoTen']) ?></td>
                                <td><?= $escape($item['SoPhong']) ?></td>
                                <td><?= $escape($item['KyHoaDon']) ?></td>
                                <td><?= $escape(number_format((float)$item['TongTien'], 0, ',', '.')) ?> đồng</td>
                                <td><?= $escape($item['TrangThai']) ?><?php if ($item['ThoiGianThanhToan']): ?><br><?= $escape($item['ThoiGianThanhToan']) ?><?php endif; ?></td>
                                <td><?php if ($item['TrangThai'] === 'Chưa thu' && $item['CanConfirm']): ?><button class="approval-button primary" type="button" data-open="payment-<?= $i ?>">Xác nhận thanh toán</button><?php elseif ($item['TrangThai'] === 'Đã thu'): ?>Đã hoàn tất<?php else: ?>Chưa hỗ trợ xác nhận<?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$listing['items']): ?><tr><td colspan="10">Không tìm thấy hóa đơn.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p>Tổng <?= (int)$listing['total'] ?> hóa đơn · Trang <?= (int)$listing['page'] ?>/<?= (int)$listing['pages'] ?></p>
                <nav aria-label="Phân trang hóa đơn">
                    <?php foreach (['Trang trước' => $listing['page'] - 1, 'Trang sau' => $listing['page'] + 1] as $label=>$target): ?>
                        <?php if ($target >= 1 && $target <= $listing['pages']): ?><a class="approval-button" href="<?= $escape(appUrl('index.php?' . http_build_query(array_merge($filters, ['action'=>'payments','student'=>$student,'status'=>$status,'page'=>$target])))) ?>"><?= $label ?></a><?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </section>
            <?php foreach ($listing['items'] as $i => $item): ?>
                <?php if ($item['TrangThai'] !== 'Chưa thu' || !$item['CanConfirm']) { continue; } ?>
                <dialog class="approval-dialog" id="payment-<?= $i ?>" aria-labelledby="payment-title-<?= $i ?>">
                    <form method="post" action="<?= $escape(appUrl('index.php?action=payment-confirm')) ?>">
                        <h2 id="payment-title-<?= $i ?>">Xác nhận thanh toán</h2>
                        <p>Bạn có chắc chắn muốn xác nhận thanh toán hóa đơn này không?</p>
                        <p><?= $escape($item['MSSV']) ?> · <?= $escape($item['MaHoaDon']) ?> · <strong><?= $escape(number_format((float)$item['TongTien'], 0, ',', '.')) ?> đồng</strong></p>
                        <p>Chỉ xác nhận sau khi đã nhận đủ tiền. Hợp đồng sẽ theo kỳ <?= $escape($item['NgayBatDau'] . ' → ' . $item['NgayKetThuc']) ?>. Sinh viên dùng tài khoản đã được cấp khi duyệt đơn để xem thông tin lưu trú.</p>
                        <input type="hidden" name="invoice" value="<?= $escape($item['MaHoaDon']) ?>">
                        <div class="approval-actions"><button class="approval-button" type="button" data-close>Hủy</button><button class="approval-button primary" type="submit">Có, đã nhận đủ tiền</button></div>
                    </form>
                </dialog>
            <?php endforeach; ?>
        </main>
    </div>
    <script src="<?= $escape(appUrl('assets/js/approval.js')) ?>" defer></script>
</body>
</html>
