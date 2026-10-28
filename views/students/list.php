<?php
if(!isset($filters,$errors,$listing,$escape,$url)){http_response_code(404);exit;}
$pageUrl=static fn(int $page):string=>appUrl('index.php?'.http_build_query(array_merge(['action'=>'students'],$filters,['page'=>$page])));
?>
<form class="approval-panel payment-filters" method="get" action="<?= $url('index.php') ?>">
<input type="hidden" name="action" value="students">
<label>MSSV<input name="student" maxlength="15" value="<?= $escape($filters['student']) ?>" placeholder="Nhập MSSV"><?php if(isset($errors['student'])):?><span class="student-field-error"><?= $escape($errors['student']) ?></span><?php endif;?></label>
<label>Họ và tên<input name="name" maxlength="100" value="<?= $escape($filters['name']) ?>" placeholder="Nhập họ và tên"></label>
<label>Số phòng<input name="room" maxlength="20" value="<?= $escape($filters['room']) ?>" placeholder="Nhập số phòng"></label>
<label>Lớp<input name="class" maxlength="30" value="<?= $escape($filters['class']) ?>" placeholder="Nhập lớp"></label>
<label>Trạng thái<select name="status"><option value="">Tất cả</option><?php foreach(['Đang ở','Đã rời đi'] as $status):?><option value="<?= $escape($status) ?>"<?= $filters['status']===$status?' selected':'' ?>><?= $escape($status) ?></option><?php endforeach;?></select></label>
<div class="payment-filter-actions"><button class="approval-button primary" type="submit">Tìm kiếm</button><a class="approval-button" href="<?= $url('index.php?action=students') ?>">Đặt lại</a></div>
</form>
<section class="approval-panel"><h2>Danh sách sinh viên</h2>
<?php if($errors):?><p class="approval-message error">Vui lòng sửa tiêu chí tra cứu chưa hợp lệ.</p>
<?php else:?><div class="payment-table-scroll"><table class="payment-table"><thead><tr><th>STT</th><th>MSSV</th><th>Họ và tên</th><th>Lớp</th><th>Phòng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach($listing['items'] as $index=>$item):?><tr><td><?=($listing['page']-1)*20+$index+1?></td><td><?= $escape($item['MSSV']) ?></td><td><?= $escape($item['HoTen']) ?></td><td><?= $escape($item['Lop']) ?></td><td><?= $escape($item['SoPhong']?:'—') ?></td><td><?= $escape($item['TrangThaiLuuTru']) ?></td><td><a class="approval-button" href="<?= $url('index.php?action=student-detail&id='.rawurlencode($item['MSSV'])) ?>">Xem chi tiết</a></td></tr><?php endforeach;?>
<?php if(!$listing['items']):?><tr><td colspan="7">Không tìm thấy sinh viên.</td></tr><?php endif;?></tbody></table></div>
<p>Tổng <?= (int)$listing['total'] ?> sinh viên · Trang <?= (int)$listing['page'] ?>/<?= (int)$listing['pages'] ?></p><nav class="student-pagination"><?php if($listing['page']>1):?><a class="approval-button" href="<?= $escape($pageUrl($listing['page']-1)) ?>">Trang trước</a><?php endif;?><?php if($listing['page']<$listing['pages']):?><a class="approval-button" href="<?= $escape($pageUrl($listing['page']+1)) ?>">Trang sau</a><?php endif;?></nav>
<?php endif;?></section>
