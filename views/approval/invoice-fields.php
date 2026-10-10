<?php if (!isset($billingError, $escape)) { http_response_code(404); exit; } ?>
<?php if ($billing): ?>
    <dl class="approval-info">
        <div><dt>Kỳ thu tiền</dt><dd><?= $escape($billing['semester']['TenHocKy'] . ' · ' . $billing['semester']['NamHoc']) ?></dd></div>
        <div><dt>Thời gian kỳ</dt><dd><?= $escape($billing['semester']['NgayBatDau'] . ' → ' . $billing['semester']['NgayKetThuc']) ?></dd></div>
        <div><dt>Tiền phòng</dt><dd><?= $escape(number_format((float) $billing['rate']['GiaTri'], 2, ',', '.')) ?> đồng / học kỳ</dd></div>
    </dl>
    <p>Thu trọn học kỳ kế tiếp, không tính theo thời gian còn lại của kỳ hiện tại. Kỳ và đơn giá được kiểm tra lại khi xác nhận.</p>
<?php else: ?>
    <p class="approval-message error" role="alert"><?= $escape($billingError) ?> Chưa thể duyệt đơn; vẫn có thể xem hoặc từ chối.</p>
<?php endif; ?>
