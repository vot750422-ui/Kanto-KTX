<?php
if(!isset($type,$errors,$result,$displayName)||!array_key_exists('startDate',get_defined_vars())||!array_key_exists('endDate',get_defined_vars())){http_response_code(404);exit;}
$escape=static fn($value):string=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
$url=static fn(string $path):string=>$escape(appUrl($path));
$labels=['accommodation'=>'Tình hình lưu trú','invoices'=>'Hóa đơn','incidents'=>'Phản ánh sự cố'];
$summaryLabels=[
    'accommodation'=>['total'=>'Tổng hợp đồng','students'=>'Sinh viên','rooms'=>'Phòng','active'=>'Còn hạn'],
    'invoices'=>['total'=>'Tổng hóa đơn','paid'=>'Đã thu','unpaid'=>'Chưa thu','amount'=>'Tổng tiền'],
    'incidents'=>['total'=>'Tổng phản ánh','pending'=>'Chờ xử lý','resolved'=>'Đã xử lý','cost'=>'Chi phí sửa chữa'],
];
$moneyKeys=['amount','cost'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php require __DIR__.'/../../includes/head.php'; ?>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Thống kê và báo cáo — Kanto</title>
    <?php foreach(['style','staff-dashboard','navbar','approval','payments','statistics'] as $css): ?>
        <link rel="stylesheet" href="<?= $url('assets/css/'.$css.'.css') ?>">
    <?php endforeach; ?>
</head>
<body class="staff-page">
<header class="staff-header">
    <a class="site-brand" href="<?= $url('views/quanly/Dashboard.php') ?>">
        <img class="site-logo" src="<?= $url('assets/images/logo.png') ?>" alt="">
        <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
    </a>
    <?php require __DIR__.'/../../includes/account-menu.php'; ?>
</header>
<div class="dashboard-layout">
<?php require __DIR__.'/../../includes/navbar.php'; ?>
<main class="staff-main payment-main statistics-main">
    <h1>Thống kê và báo cáo</h1>
    <p class="staff-intro">Xem số liệu tổng hợp và bảng dữ liệu theo khoảng thời gian.</p>
    <section class="approval-panel">
        <form class="statistics-filter" method="get" action="<?= $url('index.php') ?>">
            <input type="hidden" name="action" value="statistics">
            <label>Loại thống kê<select name="type"><?php foreach($labels as $key=>$label):?><option value="<?= $key ?>"<?= $type===$key?' selected':'' ?>><?= $label ?></option><?php endforeach;?></select></label>
            <label>Ngày bắt đầu<input type="date" name="start_date" value="<?= $escape($startDate) ?>"></label>
            <label>Ngày kết thúc<input type="date" name="end_date" value="<?= $escape($endDate) ?>"></label>
            <button class="approval-button primary" type="submit">Thống kê</button>
        </form>
        <?php foreach($errors as $error):?><p class="approval-message error"><?= $escape($error) ?></p><?php endforeach;?>
    </section>

    <?php if(!$errors): ?>
        <section class="statistics-cards">
        <?php foreach($summaryLabels[$type] as $key=>$label):$value=$result['summary'][$key]??0;?>
            <article><span><?= $label ?></span><strong><?= in_array($key,$moneyKeys,true)?number_format((float)$value,0,',','.').' đ':number_format((int)$value,0,',','.') ?></strong></article>
        <?php endforeach;?>
        </section>
        <section class="approval-panel"><h2><?= $labels[$type] ?></h2>
        <?php if(!$result['items']):?><p class="approval-empty">Không có dữ liệu phù hợp.</p>
        <?php else:?><div class="statistics-table-wrap"><table><thead><tr>
            <?php if($type==='accommodation'):?><th>Mã HĐ</th><th>Sinh viên</th><th>Tòa / phòng</th><th>Thời hạn</th><th>Trạng thái</th>
            <?php elseif($type==='invoices'):?><th>Mã hóa đơn</th><th>Loại</th><th>Tổng tiền</th><th>Thời gian lập</th><th>Trạng thái</th>
            <?php else:?><th>Mã phản ánh</th><th>Sinh viên</th><th>Phòng</th><th>Loại</th><th>Thời gian gửi</th><th>Chi phí</th><th>Trạng thái</th><?php endif;?>
        </tr></thead><tbody>
        <?php foreach($result['items'] as $row):?><tr>
            <?php if($type==='accommodation'):?><td><?= $escape($row['MaHopDong']) ?></td><td><?= $escape($row['MSSV'].' · '.$row['HoTen']) ?></td><td><?= $escape($row['TenToa'].' / '.$row['SoPhong']) ?></td><td><?= $escape($row['NgayBatDau'].' → '.$row['NgayKetThuc']) ?></td><td><?= $escape($row['TrangThai']) ?></td>
            <?php elseif($type==='invoices'):?><td><?= $escape($row['MaHoaDon']) ?></td><td><?= $escape($row['LoaiHoaDon']) ?></td><td><?= number_format((float)$row['TongTien'],0,',','.') ?> đ</td><td><?= $escape($row['ThoiGianLap']) ?></td><td><?= $escape($row['TrangThai']) ?></td>
            <?php else:?><td><?= $escape($row['MaPhanAnh']) ?></td><td><?= $escape($row['MSSV'].' · '.$row['HoTen']) ?></td><td><?= $escape($row['SoPhong']) ?></td><td><?= $escape($row['LoaiSuCo']) ?></td><td><?= $escape($row['ThoiGianGui']) ?></td><td><?= $row['ChiPhiSuaChua']===null?'—':number_format((float)$row['ChiPhiSuaChua'],0,',','.').' đ' ?></td><td><?= $escape($row['TrangThai']) ?></td><?php endif;?>
        </tr><?php endforeach;?></tbody></table></div><?php endif;?></section>
    <?php endif;?>
</main></div></body></html>
