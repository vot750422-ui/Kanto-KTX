<?php if (!isset($application, $escape, $url)) { http_response_code(404); exit; } ?>
<?php if ($error): ?><p class="approval-message error" role="alert"><?= $escape($error) ?></p><?php endif; ?>
<div class="approval-detail">
    <section class="approval-panel">
        <h2>Thông tin cá nhân</h2>
        <dl class="approval-info">
            <?php foreach (['HoTen' => 'Họ tên', 'NgaySinh' => 'Ngày sinh', 'CCCD' => 'CCCD', 'GioiTinh' => 'Giới tính', 'QueQuan' => 'Quê quán', 'SDT' => 'Số điện thoại', 'MSSV' => 'MSSV', 'Lop' => 'Lớp', 'NienKhoa' => 'Niên khóa'] as $field => $label): ?>
                <div><dt><?= $label ?></dt><dd><?= $escape($application[$field]) ?></dd></div>
            <?php endforeach; ?>
        </dl>
    </section>
    <div class="approval-stack">
        <section class="approval-panel"><h2>Thông tin ưu tiên</h2><p><?= $escape($application['TenDienUuTien']) ?></p><p>Mức ưu tiên: <?= (int) $application['MucUuTien'] ?></p></section>
        <section class="approval-panel"><h2>Thông tin phòng đăng ký</h2><dl class="approval-info">
            <?php foreach (['TenToa' => 'Dãy', 'Tang' => 'Tầng', 'SoPhong' => 'Phòng đăng ký'] as $field => $label): ?><div><dt><?= $label ?></dt><dd><?= $escape($application[$field]) ?></dd></div><?php endforeach; ?>
        </dl></section>
        <section class="approval-panel"><h2>Thông tin đơn</h2><dl class="approval-info">
            <?php foreach (['MaDonDangKy' => 'Mã đơn', 'ThoiGianGui' => 'Ngày gửi', 'TrangThai' => 'Trạng thái'] as $field => $label): ?><div><dt><?= $label ?></dt><dd><?= $escape($application[$field]) ?></dd></div><?php endforeach; ?>
            <?php if ($application['LyDoTuChoi']): ?><div><dt>Lý do từ chối</dt><dd><?= $escape($application['LyDoTuChoi']) ?></dd></div><?php endif; ?>
        </dl></section>
    </div>
    <section class="approval-panel approval-proof"><h2>Ảnh minh chứng</h2>
        <?php if ($proof['status'] === 'available'): ?>
            <?php $proofUrl = $url('index.php?action=approval-proof&id=' . rawurlencode($application['MaDonDangKy'])); ?>
            <img src="<?= $proofUrl ?>" alt="Ảnh minh chứng của hồ sơ">
            <button class="approval-button" type="button" data-open="proof-dialog">Xem ảnh minh chứng</button>
            <dialog id="proof-dialog" class="approval-dialog proof-dialog"><button type="button" data-close>Đóng ảnh</button><img src="<?= $proofUrl ?>" alt="Ảnh minh chứng phóng lớn"></dialog>
        <?php elseif ($proof['status'] === 'none'): ?><p>Không nộp ảnh minh chứng.</p>
        <?php else: ?><p>Đã ghi nhận ảnh minh chứng nhưng tệp không còn hoặc không thể đọc.</p><?php endif; ?>
    </section>
</div>
<div class="approval-actions">
    <a class="approval-button" href="<?= $url('index.php?action=approval') ?>">Quay lại</a>
    <?php if ($application['TrangThai'] === 'Chờ duyệt'): ?>
        <button class="approval-button danger" type="button" data-open="reject-dialog">Từ chối</button>
        <button class="approval-button primary" type="button" data-open="approve-dialog">Duyệt</button>
    <?php else: ?><p>Đơn đã được xử lý. Không thể xét duyệt lại.</p><?php endif; ?>
</div>
<?php if ($application['TrangThai'] === 'Chờ duyệt'): ?>
<dialog id="reject-dialog" class="approval-dialog">
    <form method="post" action="<?= $url('index.php?action=approval-reject') ?>">
        <h2>Từ chối đơn đăng ký</h2>
        <input type="hidden" name="id" value="<?= $escape($application['MaDonDangKy']) ?>">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['approval_token']) ?>">
        <label for="reason">Lý do từ chối</label><textarea id="reason" name="reason" maxlength="500" required><?= $escape($reason) ?></textarea>
        <div class="approval-actions"><button type="button" class="approval-button" data-close>Hủy</button><button type="submit" class="approval-button danger">Xác nhận từ chối</button></div>
    </form>
</dialog>
<dialog id="approve-dialog" class="approval-dialog">
    <form method="post" action="<?= $url('index.php?action=approval-approve') ?>">
        <h2>Duyệt đơn đăng ký</h2>
        <p>Bạn có chắc chắn muốn duyệt đơn đăng ký này không?</p>
        <p>Sinh viên được cấp tài khoản và giữ chỗ chờ thanh toán hóa đơn tiền phòng đầu tiên.</p>
        <input type="hidden" name="id" value="<?= $escape($application['MaDonDangKy']) ?>">
        <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['approval_token']) ?>">
        <?php require __DIR__ . '/invoice-fields.php'; ?>
        <div class="approval-actions"><button type="button" class="approval-button" data-close>Hủy</button><button type="submit" class="approval-button primary"<?= !$billing ? ' disabled' : '' ?>>Xác nhận</button></div>
    </form>
</dialog>
<?php endif; ?>
