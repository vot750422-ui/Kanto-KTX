<?php if (!isset($billingError, $escape)) { http_response_code(404); exit; } ?>
<?php if ($billing): ?>
    <dl class="approval-info">
        <div><dt>Kỳ thu tiền</dt><dd><?= $escape($billing['semester']['TenHocKy'] . ' · ' . $billing['semester']['NamHoc']) ?></dd></div>
        <div><dt>Thời gian kỳ</dt><dd><?= $escape($billing['semester']['NgayBatDau'] . ' → ' . $billing['semester']['NgayKetThuc']) ?></dd></div>
        <div><dt>Đơn giá phòng</dt><dd><?= $escape(number_format((float) $billing['rate']['GiaTri'], 2, ',', '.')) ?> đồng / sinh viên / tháng</dd></div>
        <div><dt>Tổng tiền phòng</dt><dd><?= $escape(number_format((float) $billing['total'], 0, ',', '.')) ?> đồng</dd></div>
    </dl>
<?php else: ?>
    <p class="approval-message error" role="alert"><?= $escape($billingError) ?> Chưa thể duyệt đơn; vẫn có thể xem hoặc từ chối.</p>
<?php endif; ?>
