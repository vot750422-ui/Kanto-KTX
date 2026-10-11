<?php
if (!isset($rooms, $buildings, $floors, $gender, $message)) {
    http_response_code(404);
    exit;
}
$escape = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$filterUrl = fn(array $filters) => appUrl('index.php?' . http_build_query(['action' => 'rooms'] + $filters));
$available = count(array_filter($rooms, fn($room) => (int) $room['ConCho'] > 0));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require __DIR__ . '/../../includes/head.php'; ?>
    <title>Thông tin phòng — Kanto KTX</title>
    <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/room-information.css')) ?>">
</head>
<body class="home-page room-information-page">
<?php require __DIR__ . '/../../includes/header.php'; ?>
<main class="room-information-shell">
    <h1>Thông tin phòng</h1>
    <p class="room-introduction">Xem tình trạng phòng theo khu, tòa và tầng.</p>
    <div class="room-legend" aria-label="Chú thích trạng thái phòng">
        <span><i aria-hidden="true"></i>Còn chỗ</span>
        <span><i class="full" aria-hidden="true"></i>Đầy</span>
    </div>
    <nav class="room-information-filters" aria-label="Bộ lọc phòng">
        <div class="room-filter-row">
            <strong>Khu</strong>
            <?php foreach (['Nam', 'Nữ'] as $option): ?>
                <a href="<?= $escape($filterUrl(['GioiTinhPhong' => $option])) ?>"<?= $gender === $option ? ' aria-current="true"' : '' ?>>Khu <?= $option ?></a>
            <?php endforeach; ?>
        </div>
        <?php if ($buildings): ?>
        <div class="room-filter-row">
            <strong>Tòa</strong>
            <?php foreach ($buildings as $building): ?>
                <a href="<?= $escape($filterUrl(['GioiTinhPhong' => $gender, 'MaToa' => $building['MaToa']])) ?>"<?= $buildingId === $building['MaToa'] ? ' aria-current="true"' : '' ?>><?= $escape($building['TenToa']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($floors): ?>
        <div class="room-filter-row">
            <strong>Tầng</strong>
            <?php foreach ($floors as $option): ?>
                <a href="<?= $escape($filterUrl(['GioiTinhPhong' => $gender, 'MaToa' => $buildingId, 'Tang' => $option])) ?>"<?= $floor === $option ? ' aria-current="true"' : '' ?>>Tầng <?= $option ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </nav>
    <?php if ($message): ?>
        <div class="room-information-error" role="alert">
            <p><?= $escape($message) ?></p>
            <a href="<?= $escape(appUrl('index.php?action=rooms')) ?>">Chọn lại bộ lọc</a>
        </div>
    <?php else: ?>
        <?php if ($selectedBuilding): ?>
        <div class="room-results-heading">
            <h2><?= $escape($selectedBuilding['TenToa']) ?> · Tầng <?= $floor ?></h2>
            <p><?= count($rooms) ?> phòng · <?= $available ?> phòng còn chỗ · <?= $escape($gender) ?></p>
        </div>
        <?php endif; ?>
        <div class="room-information-grid">
            <?php foreach ($rooms as $room): ?>
                <?php $isFull = (int) $room['ConCho'] <= 0; ?>
                <article class="room-information-card<?= $isFull ? ' full' : '' ?>">
                    <h3>Phòng <?= $escape($room['SoPhong']) ?></h3>
                    <p><?= $escape($room['GioiTinhPhong']) ?> · <?= (int) $room['DangO'] ?>/<?= (int) $room['SucChua'] ?> sinh viên đang ở</p>
                    <p><?= (int) $room['DangGiu'] ?> chỗ đang giữ · <?= max(0, (int) $room['ConCho']) ?> chỗ khả dụng</p>
                    <strong class="room-status"><?= $isFull ? 'Đầy' : 'Còn chỗ' ?></strong>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (!$rooms): ?><p class="room-empty" role="status">Không có thông tin phòng phù hợp.</p><?php endif; ?>
    <?php endif; ?>
    <p class="room-information-note">Chỗ khả dụng đã trừ số sinh viên đang ở và số chỗ đang giữ. Thông tin được cập nhật mỗi lần tải trang. Việc chọn và giữ chỗ được thực hiện trong Đăng ký lưu trú.</p>
</main>
</body>
</html>
