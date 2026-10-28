<?php
if (!isset($filters, $errors, $listing, $escape, $url)) {
    http_response_code(404);
    exit;
}
$queryUrl = static function (int $page) use ($filters): string {
    return appUrl('index.php?' . http_build_query(array_merge(['action' => 'contracts'], $filters, ['page' => $page])));
};
?>
<form class="approval-panel payment-filters" method="get" action="<?= $url('index.php') ?>" aria-label="Tiêu chí tra cứu hợp đồng">
    <input type="hidden" name="action" value="contracts">
    <label>Mã hợp đồng
        <input name="contract" maxlength="10" value="<?= $escape($filters['contract']) ?>" placeholder="Nhập mã hợp đồng">
        <?php if (isset($errors['contract'])): ?><span class="contract-field-error"><?= $escape($errors['contract']) ?></span><?php endif; ?>
    </label>
    <label>MSSV
        <input name="student" maxlength="15" value="<?= $escape($filters['student']) ?>" placeholder="Nhập MSSV">
        <?php if (isset($errors['student'])): ?><span class="contract-field-error"><?= $escape($errors['student']) ?></span><?php endif; ?>
    </label>
    <label>Họ và tên
        <input name="name" maxlength="100" value="<?= $escape($filters['name']) ?>" placeholder="Nhập họ và tên">
        <?php if (isset($errors['name'])): ?><span class="contract-field-error"><?= $escape($errors['name']) ?></span><?php endif; ?>
    </label>
    <label>Số phòng
        <input name="room" maxlength="20" value="<?= $escape($filters['room']) ?>" placeholder="Nhập số phòng">
        <?php if (isset($errors['room'])): ?><span class="contract-field-error"><?= $escape($errors['room']) ?></span><?php endif; ?>
    </label>
    <label>Trạng thái
        <select name="status">
            <option value="">Tất cả</option>
            <?php foreach (['Còn hạn', 'Hết hạn', 'Đã thanh lý'] as $status): ?>
                <option value="<?= $escape($status) ?>"<?= $filters['status'] === $status ? ' selected' : '' ?>><?= $escape($status) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['status'])): ?><span class="contract-field-error"><?= $escape($errors['status']) ?></span><?php endif; ?>
    </label>
    <div class="payment-filter-actions">
        <button class="approval-button primary" type="submit">Tìm kiếm</button>
        <a class="approval-button" href="<?= $url('index.php?action=contracts') ?>">Đặt lại</a>
    </div>
</form>

<section class="approval-panel" aria-labelledby="contract-results-title">
    <h2 id="contract-results-title">Danh sách hợp đồng</h2>
    <?php if ($errors): ?>
        <p class="contract-message error">Vui lòng sửa các tiêu chí chưa hợp lệ trước khi tra cứu.</p>
    <?php elseif (!$listing['items']): ?>
        <p class="contract-empty">Không tìm thấy hợp đồng.</p>
    <?php else: ?>
        <div class="payment-table-scroll">
            <table class="payment-table">
                <thead><tr><th>STT</th><th>Mã hợp đồng</th><th>MSSV</th><th>Họ và tên</th><th>Phòng</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($listing['items'] as $index => $item): ?>
                    <tr>
                        <td><?= ($listing['page'] - 1) * 20 + $index + 1 ?></td>
                        <td><?= $escape($item['MaHopDong']) ?></td>
                        <td><?= $escape($item['MSSV']) ?></td>
                        <td><?= $escape($item['HoTen']) ?></td>
                        <td><?= $escape($item['SoPhong']) ?></td>
                        <td><?= $escape(date('d/m/Y', strtotime($item['NgayBatDau']))) ?></td>
                        <td><?= $escape(date('d/m/Y', strtotime($item['NgayKetThuc']))) ?></td>
                        <td><?= $escape($item['TrangThai']) ?></td>
                        <td><a class="approval-button" href="<?= $url('index.php?action=contract-detail&id=' . rawurlencode($item['MaHopDong'])) ?>">Xem chi tiết</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p>Tổng <?= (int) $listing['total'] ?> hợp đồng · Trang <?= (int) $listing['page'] ?>/<?= (int) $listing['pages'] ?></p>
        <nav class="contract-pagination" aria-label="Phân trang hợp đồng">
            <?php if ($listing['page'] > 1): ?><a class="approval-button" href="<?= $escape($queryUrl($listing['page'] - 1)) ?>">Trang trước</a><?php endif; ?>
            <?php if ($listing['page'] < $listing['pages']): ?><a class="approval-button" href="<?= $escape($queryUrl($listing['page'] + 1)) ?>">Trang sau</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
