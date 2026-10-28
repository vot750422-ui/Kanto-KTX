<?php
if (!isset($rooms,$errors,$roomId,$electricity,$water,$displayName)) { http_response_code(404); exit; }
$escape=static fn($value):string=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
$url=static fn(string $path):string=>$escape(appUrl($path));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php require __DIR__.'/../../includes/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ghi chỉ số điện nước — Kanto</title>
    <?php foreach(['style','staff-dashboard','navbar','approval','payments','utilities'] as $css): ?>
        <link rel="stylesheet" href="<?= $url('assets/css/'.$css.'.css') ?>">
    <?php endforeach; ?>
    <script defer src="<?= $url('assets/js/utility-meters.js') ?>"></script>
</head>
<body class="staff-page">
<header class="staff-header">
    <a class="site-brand" href="<?= $url('views/nvql/Dashboard.php') ?>">
        <img class="site-logo" src="<?= $url('assets/images/logo.png') ?>" alt="">
        <span class="site-brand-text"><strong>KANTO</strong><span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span></span>
    </a>
    <?php require __DIR__.'/../../includes/account-menu.php'; ?>
</header>
<div class="dashboard-layout">
    <?php require __DIR__.'/../../includes/navbar.php'; ?>
    <main class="staff-main payment-main utility-main">
        <h1>Ghi chỉ số điện nước</h1>
        <p class="staff-intro">Chọn phòng đang có sinh viên lưu trú và ghi chỉ số cho kỳ <?= date('m/Y') ?>.</p>
        <?php if($notice): ?><p class="approval-message success" role="status"><?= $escape($notice) ?></p><?php endif; ?>
        <?php foreach($errors as $error): ?><p class="approval-message error" role="alert"><?= $escape($error) ?></p><?php endforeach; ?>

        <section class="approval-panel">
            <form method="get" action="<?= $url('index.php') ?>" class="utility-room-select">
                <input type="hidden" name="action" value="utility-meters">
                <label>Phòng đang có sinh viên lưu trú
                    <select name="room_id" required data-utility-room>
                        <option value="">Chọn phòng</option>
                        <?php foreach($rooms as $room): ?>
                            <option value="<?= $escape($room['MaPhong']) ?>"<?= $roomId===$room['MaPhong']?' selected':'' ?>><?= $escape($room['TenToa'].' · '.$room['SoPhong'].' · '.$room['SoSinhVien'].' sinh viên') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <noscript><button class="approval-button" type="submit">Xem chỉ số</button></noscript>
            </form>
        </section>

        <?php if(!$rooms): ?>
            <section class="approval-panel"><p class="approval-empty">Không có phòng đang có sinh viên lưu trú.</p></section>
        <?php elseif($selected): ?>
            <section class="approval-panel utility-meter-form">
                <h2><?= $escape($selected['TenToa'].' · Phòng '.$selected['SoPhong']) ?></h2>
                <div class="utility-old">
                    <div><span>Chỉ số điện cũ</span><strong><?= $selected['ChiSoDien']===null?'Chưa có':$escape($selected['ChiSoDien']).' kWh' ?></strong></div>
                    <div><span>Chỉ số nước cũ</span><strong><?= $selected['ChiSoNuoc']===null?'Chưa có':$escape($selected['ChiSoNuoc']).' m³' ?></strong></div>
                </div>
                <form method="post" action="<?= $url('index.php?action=utility-meters') ?>">
                    <input type="hidden" name="room_id" value="<?= $escape($selected['MaPhong']) ?>">
                    <label>Chỉ số điện mới (kWh)<input type="number" min="0" max="2147483647" step="1" name="electricity" value="<?= $escape($electricity) ?>" required></label>
                    <label>Chỉ số nước mới (m³)<input type="number" min="0" max="2147483647" step="1" name="water" value="<?= $escape($water) ?>" required></label>
                    <button class="approval-button primary" type="submit">Lưu</button>
                </form>
            </section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
