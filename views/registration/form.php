<?php
if (!isset($priorities, $step, $data)) {
    http_response_code(404);
    exit;
}
$escape = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$labels = ['HoTen' => 'Họ tên', 'NgaySinh' => 'Ngày sinh', 'CCCD' => 'CCCD', 'GioiTinh' => 'Giới tính', 'QueQuan' => 'Quê quán', 'SDT' => 'Số điện thoại', 'MSSV' => 'MSSV', 'Lop' => 'Lớp', 'NienKhoa' => 'Niên khóa'];
$token = $escape($_SESSION['registration_token']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký lưu trú — Kanto KTX</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/style.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/registration.css">
</head>
<body class="home-page registration-page">
<?php require __DIR__ . '/../../includes/header.php'; ?>
<main class="registration-shell">
    <h1>Đăng ký lưu trú</h1>
    <?php if ($success): ?>
        <div class="registration-success" role="status">
            <h2>Đăng ký lưu trú thành công. Hồ sơ đang chờ xét duyệt.</h2>
            <p>Mã đơn đăng ký: <strong><?= $escape($success) ?></strong></p>
            <p>Tài khoản sinh viên sẽ được tạo sau khi hồ sơ được duyệt.</p>
            <a href="/Kanto-KTX/index.php">Về trang chủ</a>
        </div>
    <?php else: ?>
        <p class="registration-subtitle">Bước <?= $step ?>: <?= [1 => 'Thông tin đăng ký', 2 => 'Chọn phòng', 3 => 'Xác nhận'][$step] ?> — Vui lòng kiểm tra thông tin trước khi tiếp tục.</p>
        <ol class="registration-steps" aria-label="Các bước đăng ký">
            <?php foreach (['Thông tin đăng ký', 'Chọn phòng', 'Xác nhận'] as $i => $label): ?>
                <li class="<?= $step === $i + 1 ? 'current' : '' ?>"<?= $step === $i + 1 ? ' aria-current="step"' : '' ?>><span><?= $step > $i + 1 ? '✓' : $i + 1 ?></span><?= $label ?></li>
            <?php endforeach; ?>
        </ol>
        <?php if ($message): ?><div class="registration-error" role="alert"><?= $escape($message) ?></div><?php endif; ?>
        <?php if ($step === 1): ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="operation" value="information">
            <div class="registration-layout">
                <div class="registration-fields">
                    <?php foreach ($labels as $field => $label): ?>
                        <div class="registration-field">
                            <label for="<?= $field ?>"><?= $label ?> <span aria-hidden="true">*</span></label>
                            <?php if ($field === 'GioiTinh'): ?>
                                <select id="<?= $field ?>" name="<?= $field ?>" required>
                                    <option value="">Chọn giới tính</option>
                                    <?php foreach (['Nam', 'Nữ'] as $gender): ?><option value="<?= $gender ?>"<?= ($data[$field] ?? '') === $gender ? ' selected' : '' ?>><?= $gender ?></option><?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input id="<?= $field ?>" name="<?= $field ?>" type="<?= $field === 'NgaySinh' ? 'date' : ($field === 'SDT' ? 'tel' : 'text') ?>" value="<?= $escape($data[$field] ?? '') ?>" maxlength="<?= ['HoTen' => 100, 'QueQuan' => 150, 'Lop' => 30, 'MSSV' => 15, 'NienKhoa' => 20, 'CCCD' => 12, 'SDT' => 10, 'NgaySinh' => 10][$field] ?>"<?= in_array($field, ['CCCD', 'SDT']) ? ' inputmode="numeric"' : '' ?><?= $field === 'NienKhoa' ? ' placeholder="2024-2028"' : '' ?> required<?= isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="error-' . $field . '"' : '' ?>>
                            <?php endif; ?>
                            <?php if (isset($errors[$field])): ?><small class="field-error" id="error-<?= $field ?>"><?= $escape($errors[$field]) ?></small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="registration-field">
                        <label for="MaUuTien">Diện ưu tiên *</label>
                        <select id="MaUuTien" name="MaUuTien" required>
                            <option value="">Chọn diện ưu tiên</option>
                            <?php foreach ($priorities as $priority): ?><option value="<?= $escape($priority['MaUuTien']) ?>"<?= ($data['MaUuTien'] ?? 'UT00') === $priority['MaUuTien'] ? ' selected' : '' ?>><?= $escape($priority['TenDienUuTien']) ?></option><?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['MaUuTien'])): ?><small class="field-error"><?= $escape($errors['MaUuTien']) ?></small><?php endif; ?>
                    </div>
                </div>
                <aside class="registration-upload">
                    <label for="proof">Tải ảnh minh chứng</label>
                    <div class="upload-box">
                        <span aria-hidden="true">▧</span>
                        <input id="proof" name="proof" type="file" accept="image/jpeg,image/png">
                        <p><?= !empty($data['FileMinhChung']) ? 'Đã lưu ảnh minh chứng. Chọn ảnh mới nếu muốn thay thế.' : 'Chọn ảnh giấy tờ minh chứng diện ưu tiên (nếu có).' ?></p>
                    </div>
                    <p>JPG, PNG • Dung lượng dưới 5 MB.</p>
                    <?php if (isset($errors['proof'])): ?><small class="field-error" role="alert"><?= $escape($errors['proof']) ?></small><?php endif; ?>
                </aside>
            </div>
            <div class="registration-actions"><p>* Thông tin bắt buộc.</p><button class="primary" type="submit">Tiếp tục →</button></div>
        </form>
        <?php elseif ($step === 2): ?>
            <p><strong>Giới tính: <?= $escape($data['GioiTinh']) ?></strong></p>
            <div class="room-filters">
                <label>Dãy <select id="building-filter"><option value="">Tất cả dãy</option><?php $buildings = []; foreach ($rooms as $room) { $buildings[$room['MaToa']] = $room['TenToa']; } foreach ($buildings as $id => $name): ?><option value="<?= $escape($id) ?>"><?= $escape($name) ?></option><?php endforeach; ?></select></label>
                <label>Tầng <select id="floor-filter"><option value="">Tất cả tầng</option><?php $floors = array_unique(array_column($rooms, 'Tang')); sort($floors); foreach ($floors as $floor): ?><option value="<?= $escape($floor) ?>"><?= $escape($floor) ?></option><?php endforeach; ?></select></label>
            </div>
            <p class="registration-subtitle">Chỉ hiển thị phòng phù hợp còn chỗ. Chỗ được giữ sau khi gửi đăng ký thành công.</p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                <div class="room-grid">
                    <?php foreach ($rooms as $room): ?>
                    <label class="room-card" data-building="<?= $escape($room['MaToa']) ?>" data-floor="<?= $escape($room['Tang']) ?>">
                        <input type="radio" name="MaPhong" value="<?= $escape($room['MaPhong']) ?>"<?= ($draft['room'] ?? '') === $room['MaPhong'] ? ' checked' : '' ?>>
                        <strong>Phòng <?= $escape($room['SoPhong']) ?></strong>
                        <span><?= $escape($room['TenToa']) ?> • Tầng <?= $escape($room['Tang']) ?></span>
                        <span><?= (int) $room['DangO'] ?>/<?= (int) $room['SucChua'] ?> sinh viên đang ở</span>
                        <b>Còn <?= (int) $room['ConCho'] ?> chỗ</b>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p id="room-empty" role="status"<?= $rooms ? ' hidden' : '' ?>>Không có phòng phù hợp còn chỗ.</p>
                <div class="registration-actions"><button name="operation" value="back">Quay lại</button><button class="primary" name="operation" value="room"<?= !$rooms ? ' disabled' : '' ?>>Tiếp tục →</button></div>
            </form>
        <?php else: ?>
            <section class="registration-review"><h2>Thông tin đăng ký</h2><dl>
                <?php foreach ($labels as $field => $label): ?><div><dt><?= $label ?></dt><dd><?= $escape($data[$field]) ?></dd></div><?php endforeach; ?>
                <div><dt>Diện ưu tiên</dt><dd><?php foreach ($priorities as $priority) { if ($priority['MaUuTien'] === $data['MaUuTien']) { echo $escape($priority['TenDienUuTien']); } } ?></dd></div>
                <div><dt>Ảnh minh chứng</dt><dd><?= !empty($data['FileMinhChung']) ? 'Đã tải lên' : 'Không có' ?></dd></div>
            </dl></section>
            <section class="registration-review"><h2>Thông tin phòng đã chọn</h2><dl>
                <?php foreach (['TenToa' => 'Dãy', 'Tang' => 'Tầng', 'SoPhong' => 'Phòng', 'SucChua' => 'Sức chứa', 'ConCho' => 'Số chỗ còn lại'] as $field => $label): ?><div><dt><?= $label ?></dt><dd><?= $escape($selected[$field]) ?></dd></div><?php endforeach; ?>
            </dl></section>
            <p class="registration-info">Kiểm tra kỹ thông tin trước khi xác nhận. Sau khi gửi, hồ sơ sẽ được chuyển đến nhân viên quản lý KTX để xét duyệt.</p>
            <form method="post" class="registration-actions">
                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                <button name="operation" value="back">Quay lại</button><button class="primary" name="operation" value="submit">Gửi đăng ký</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</main>
<script src="/Kanto-KTX/assets/js/registration.js" defer></script>
</body>
</html>
