<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Sinh viên');
if (!isset($data)) {
    header('Location: ' . appUrl('index.php?action=accommodation'));
    exit;
}
$student = $data['student'];
$accommodation = $data['accommodation'];
$displayName = $student['HoTen'] ?? $_SESSION['user']['TenDangNhap'];
$escape = static fn($value) => htmlspecialchars((string) ($value ?? 'Chưa cập nhật'), ENT_QUOTES, 'UTF-8');
$date = static function ($value): string {
    if (!is_string($value) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $value)) {
        return 'Chưa cập nhật';
    }
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $parsed && $parsed->format('Y-m-d') === $value ? $parsed->format('d/m/Y') : 'Chưa cập nhật';
};
$personalFields = ['HoTen' => 'Họ tên', 'MSSV' => 'MSSV', 'GioiTinh' => 'Giới tính', 'NgaySinh' => 'Ngày sinh', 'CCCD' => 'CCCD', 'SDT' => 'Số điện thoại', 'Email' => 'Email', 'Lop' => 'Lớp', 'NienKhoa' => 'Niên khóa', 'QueQuan' => 'Quê quán', 'TrangThaiLuuTru' => 'Trạng thái lưu trú'];
$roomFields = ['TenToa' => 'Tòa nhà', 'Tang' => 'Tầng', 'SoPhong' => 'Phòng', 'SucChua' => 'Sức chứa', 'GioiTinhPhong' => 'Giới tính phòng'];
$contractFields = ['MaHopDong' => 'Mã hợp đồng', 'NgayBatDau' => 'Ngày bắt đầu', 'NgayKetThuc' => 'Ngày kết thúc', 'TrangThai' => 'Trạng thái'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php require __DIR__ . '/../../includes/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin lưu trú — Kanto KTX</title>
    <?php foreach (['style', 'student-dashboard', 'navbar', 'accommodation'] as $stylesheet): ?>
        <link rel="stylesheet" href="<?= $escape(appUrl('assets/css/' . $stylesheet . '.css')) ?>">
    <?php endforeach; ?>
</head>
<body class="student-page">
    <?php require __DIR__ . '/../../includes/student-header.php'; ?>
    <div class="dashboard-layout">
        <?php require __DIR__ . '/../../includes/navbar.php'; ?>
        <main class="student-main accommodation-main">
            <h1>Thông tin lưu trú</h1>
            <p class="student-intro">Xem thông tin cá nhân, thông tin phòng ở và hợp đồng lưu trú hiện tại.</p>
            <?php if (!$student): ?>
                <p class="student-empty" role="status">Tài khoản chưa có hồ sơ sinh viên. Vui lòng liên hệ quản lý KTX để cập nhật thông tin.</p>
            <?php else: ?>
                <div class="accommodation-grid">
                    <section class="accommodation-card">
                        <h2>Thông tin sinh viên</h2>
                        <dl>
                        <?php foreach ($personalFields as $field => $label): ?>
                            <div><dt><?= $escape($label) ?></dt><dd><?= $escape($field === 'NgaySinh' ? $date($student[$field]) : $student[$field]) ?></dd></div>
                        <?php endforeach; ?>
                        </dl>
                    </section>
                    <div class="accommodation-details">
                        <?php if (!$accommodation): ?>
                            <section class="accommodation-card">
                                <h2>Thông tin lưu trú hiện tại</h2>
                                <p role="status">Hiện tại bạn không có hợp đồng lưu trú còn hiệu lực.</p>
                            </section>
                        <?php else: ?>
                            <?php foreach (['Thông tin lưu trú hiện tại' => $roomFields, 'Thông tin hợp đồng' => $contractFields] as $heading => $fields): ?>
                                <section class="accommodation-card">
                                    <h2><?= $escape($heading) ?></h2>
                                    <dl>
                                    <?php foreach ($fields as $field => $label): ?>
                                        <div><dt><?= $escape($label) ?></dt><dd><?= $escape(in_array($field, ['NgayBatDau', 'NgayKetThuc'], true) ? $date($accommodation[$field]) : $accommodation[$field]) ?><?= $field === 'SucChua' ? ' người' : '' ?></dd></div>
                                    <?php endforeach; ?>
                                    </dl>
                                </section>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <?php require __DIR__ . '/../../includes/chatbot.php'; ?>
</body>
</html>
