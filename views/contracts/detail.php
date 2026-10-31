<?php
if (!isset($contract, $escape, $url)) {
    http_response_code(404);
    exit;
}
$date = static fn(string $value): string => date('d/m/Y', strtotime($value));
$notice = $notice ?? null;
?>
<?php if (!empty($notice)): ?>
    <p class="approval-message <?= ($notice['type'] ?? '') === 'error' ? 'error' : 'success' ?>" role="alert">
        <?= $escape($notice['message'] ?? '') ?>
    </p>
<?php endif; ?>
<div class="contract-detail-actions">
    <a class="approval-button" href="<?= $url('index.php?action=contracts') ?>">← Quay lại danh sách</a>
    <?php if ($contract['TrangThai'] === 'Còn hạn'): ?>
        <form id="contract-terminate-form" method="POST" action="<?= $url('index.php?action=contract-terminate') ?>">
            <input type="hidden" name="id" value="<?= $escape($contract['MaHopDong']) ?>">
            <button type="button" class="approval-button danger" data-open="contract-terminate-confirm">Thanh lý hợp đồng</button>
        </form>
    <?php endif; ?>
</div>
<div class="contract-detail-grid">
    <section class="approval-panel contract-panel">
        <h2>Thông tin hợp đồng</h2>
        <dl class="contract-info">
            <div><dt>Mã hợp đồng</dt><dd><?= $escape($contract['MaHopDong']) ?></dd></div>
            <div><dt>Ngày bắt đầu</dt><dd><?= $escape($date($contract['NgayBatDau'])) ?></dd></div>
            <div><dt>Ngày kết thúc</dt><dd><?= $escape($date($contract['NgayKetThuc'])) ?></dd></div>
            <?php if (!empty($contract['NgayThanhLy'])): ?><div><dt>Ngày thanh lý</dt><dd><?= $escape($date($contract['NgayThanhLy'])) ?></dd></div><?php endif; ?>
            <?php if (isset($contract['TienHoanTra']) && $contract['TienHoanTra'] !== null && $contract['TienHoanTra'] !== ''): ?><div><dt>Tiền hoàn trả dự kiến</dt><dd><?= $escape(number_format((float) $contract['TienHoanTra'], 0, ',', '.')) ?> đồng</dd></div><?php endif; ?>
            <div><dt>Trạng thái</dt><dd><span class="contract-status"><?= $escape($contract['TrangThai']) ?></span></dd></div>
        </dl>
    </section>
    <section class="approval-panel contract-panel">
        <h2>Thông tin sinh viên</h2>
        <dl class="contract-info">
            <div><dt>MSSV</dt><dd><?= $escape($contract['MSSV']) ?></dd></div>
            <div><dt>Họ và tên</dt><dd><?= $escape($contract['HoTen']) ?></dd></div>
            <div><dt>Ngày sinh</dt><dd><?= $escape($date($contract['NgaySinh'])) ?></dd></div>
            <div><dt>Giới tính</dt><dd><?= $escape($contract['GioiTinh']) ?></dd></div>
            <div><dt>Số điện thoại</dt><dd><?= $escape($contract['SDT']) ?></dd></div>
            <div><dt>Email</dt><dd><?= $escape($contract['Email'] ?: 'Chưa cập nhật') ?></dd></div>
            <div><dt>Lớp</dt><dd><?= $escape($contract['Lop']) ?></dd></div>
            <div><dt>Niên khóa</dt><dd><?= $escape($contract['NienKhoa']) ?></dd></div>
            <div><dt>Lưu trú</dt><dd><?= $escape($contract['TrangThaiLuuTru']) ?></dd></div>
        </dl>
    </section>
    <section class="approval-panel contract-panel">
        <h2>Thông tin phòng</h2>
        <dl class="contract-info">
            <div><dt>Mã phòng</dt><dd><?= $escape($contract['MaPhong']) ?></dd></div>
            <div><dt>Số phòng</dt><dd><?= $escape($contract['SoPhong']) ?></dd></div>
            <div><dt>Tòa nhà</dt><dd><?= $escape($contract['TenToa'] ?: 'Chưa cập nhật') ?></dd></div>
            <div><dt>Tầng</dt><dd><?= $escape($contract['Tang']) ?></dd></div>
            <div><dt>Loại phòng</dt><dd><?= $escape($contract['GioiTinhPhong']) ?></dd></div>
        </dl>
    </section>
</div>
<?php if ($contract['TrangThai'] === 'Còn hạn'): ?>
    <dialog class="approval-dialog" id="contract-terminate-confirm">
        <h2>Xác nhận thanh lý hợp đồng</h2>
        <p>Bạn có chắc chắn muốn thanh lý hợp đồng <?= $escape($contract['MaHopDong']) ?> không? Thao tác này sẽ cập nhật trạng thái hợp đồng thành Đã thanh lý.</p>
        <div class="approval-actions">
            <button class="approval-button" type="button" data-close>Hủy</button>
            <button class="approval-button danger" type="submit" form="contract-terminate-form">Có, thanh lý hợp đồng</button>
        </div>
    </dialog>
<?php endif; ?>
