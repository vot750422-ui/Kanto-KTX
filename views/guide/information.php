<?php
if (!isset($guidePage) || $guidePage !== true) {
    http_response_code(404);
    exit;
}
$escape = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require __DIR__ . '/../../includes/head.php'; ?>
    <title>Hướng dẫn đăng ký lưu trú — Kanto KTX</title>
    <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/guide.css')) ?>">
</head>
<body class="home-page guide-page">
<?php require __DIR__ . '/../../includes/header.php'; ?>
<main class="guide-shell">
    <h1>Hướng dẫn đăng ký lưu trú</h1>
    <p class="guide-introduction">Các bước thực hiện đăng ký lưu trú tại ký túc xá Kanto. Bạn không cần đăng nhập để đăng ký.</p>
    <ol class="guide-steps">
        <li>
            <h2>Nhập thông tin đăng ký</h2>
            <ul>
                <li>Mở mục Đăng ký, nhập đầy đủ thông tin cá nhân và học tập.</li>
                <li>Chọn diện ưu tiên phù hợp, hoặc chọn Không ưu tiên.</li>
                <li>Có thể tải ảnh minh chứng diện ưu tiên nếu có.</li>
                <li>Nhấn Tiếp tục để chuyển sang chọn phòng.</li>
            </ul>
        </li>
        <li>
            <h2>Chọn phòng</h2>
            <ul>
                <li>Hệ thống hiển thị các phòng còn chỗ phù hợp với giới tính đã đăng ký.</li>
                <li>Lọc theo dãy, tầng và chọn phòng mong muốn, sau đó nhấn Tiếp tục.</li>
                <li>Phòng đầy không thể chọn. Bạn có thể xem tình trạng phòng tại mục Thông tin phòng.</li>
            </ul>
        </li>
        <li>
            <h2>Xác nhận và gửi đăng ký</h2>
            <ul>
                <li>Kiểm tra lại thông tin cá nhân, diện ưu tiên và phòng đã chọn.</li>
                <li>Nhấn Gửi đăng ký.</li>
            </ul>
        </li>
        <li>
            <h2>Chờ xét duyệt</h2>
            <ul>
                <li>Chờ nhân viên quản lý KTX xem và xét duyệt hồ sơ đã gửi.</li>
            </ul>
        </li>
    </ol>
    <aside class="guide-notes" aria-labelledby="guide-notes-title">
        <h2 id="guide-notes-title">Lưu ý</h2>
        <ul>
            <li>Nhập chính xác MSSV, CCCD và các thông tin đăng ký trước khi gửi.</li>
            <li>Không gửi thêm đơn khi MSSV đã có đơn Chờ duyệt.</li>
            <li>Phòng đã chọn có thể hết chỗ trước lúc gửi. Khi hệ thống thông báo, hãy quay lại chọn phòng còn chỗ.</li>
        </ul>
    </aside>
</main>
</body>
</html>
