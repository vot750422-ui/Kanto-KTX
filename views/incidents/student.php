<?php
if(!array_key_exists('stay',get_defined_vars())||!isset($errors,$type,$description,$message)){http_response_code(404);exit;}
$escape=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
$url=static fn($v)=>$escape(appUrl($v));
?><!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Gửi phản ánh sự cố — Kanto</title><?php require __DIR__.'/../../includes/head.php';?><?php foreach(['style','student-dashboard','navbar','approval','incidents'] as $css):?><link rel="stylesheet" href="<?= $url('assets/css/'.$css.'.css') ?>"><?php endforeach;?></head><body class="student-page"><?php require __DIR__.'/../../includes/student-header.php';?><div class="dashboard-layout"><?php require __DIR__.'/../../includes/navbar.php';?><main class="student-main incident-student-main"><h1>Gửi phản ánh sự cố</h1><p class="staff-intro">Thông báo sự cố tại phòng đang lưu trú để nhân viên KTX tiếp nhận và xử lý.</p>
<?php if($message):?><p class="approval-message success"><?= $escape($message) ?></p><?php endif;?>
<?php if(isset($errors['general'])):?><p class="approval-message error"><?= $escape($errors['general']) ?></p><?php endif;?>
<section class="approval-panel incident-form-panel">
<?php if(!$stay):?><p class="approval-message error"><?= $escape($errors['stay']??'Bạn chưa có hợp đồng lưu trú còn hạn để gửi phản ánh.') ?></p>
<?php else:?><form method="post" action="<?= $url('index.php?action=incident-report') ?>">
<label>Phòng hiện tại<input value="<?= $escape($stay['SoPhong']) ?>" readonly></label>
<label>Loại sự cố<select name="type" required><option value="">Chọn loại sự cố</option><?php foreach(['Điện','Nước','Nội thất','Khác'] as $item):?><option value="<?= $item ?>"<?= $type===$item?' selected':'' ?>><?= $item ?></option><?php endforeach;?></select><?php if(isset($errors['type'])):?><span><?= $escape($errors['type']) ?></span><?php endif;?></label>
<label>Mô tả chi tiết<textarea name="description" maxlength="65535" required><?= $escape($description) ?></textarea><?php if(isset($errors['description'])):?><span><?= $escape($errors['description']) ?></span><?php endif;?></label>
<button class="approval-button primary" type="submit">Gửi phản ánh</button></form><?php endif;?>
</section></main></div></body></html>
