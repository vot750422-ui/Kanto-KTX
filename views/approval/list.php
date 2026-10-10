<?php if (!isset($listing, $escape, $url)) { http_response_code(404); exit; } ?>
<?php if ($success): ?><p class="approval-message success" role="status"><?= $escape($success) ?></p><?php endif; ?>
<section class="approval-panel">
    <h2>Danh sách đơn chờ duyệt</h2>
    <p class="approval-note">Ưu tiên cao trước (4 → 3 → 2 → 0), cùng mức ưu tiên thì hồ sơ gửi trước đứng trước.</p>
    <?php if (!$listing['total']): ?>
        <p role="status">Không còn đơn chờ duyệt.</p>
    <?php else: ?>
    <div class="approval-table-wrap">
        <table class="approval-table">
            <thead><tr><th>STT</th><th>Mã đơn</th><th>MSSV</th><th>Họ tên</th><th>Giới tính</th><th>Diện ưu tiên</th><th>Phòng đã chọn</th><th>Ngày gửi</th><th>Thao tác</th></tr></thead>
            <tbody>
                <?php foreach ($listing['items'] as $offset => $item): ?>
                <tr>
                    <td><?= ($listing['page'] - 1) * $listing['pageSize'] + $offset + 1 ?></td>
                    <?php foreach (['MaDonDangKy', 'MSSV', 'HoTen', 'GioiTinh', 'TenDienUuTien', 'SoPhong', 'ThoiGianGui'] as $field): ?><td><?= $escape($item[$field]) ?></td><?php endforeach; ?>
                    <td><a class="approval-button" href="<?= $url('index.php?action=approval-detail&id=' . rawurlencode($item['MaDonDangKy'])) ?>">Xem chi tiết</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="approval-pagination">
        <span>Tổng <?= $listing['total'] ?> đơn chờ duyệt · Trang <?= $listing['page'] ?>/<?= $listing['pages'] ?></span>
        <nav aria-label="Phân trang đơn đăng ký">
            <?php if ($listing['page'] > 1): ?><a class="approval-button" href="<?= $url('index.php?action=approval&page=' . ($listing['page'] - 1)) ?>">Trang trước</a><?php endif; ?>
            <?php if ($listing['page'] < $listing['pages']): ?><a class="approval-button" href="<?= $url('index.php?action=approval&page=' . ($listing['page'] + 1)) ?>">Trang sau</a><?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
</section>
