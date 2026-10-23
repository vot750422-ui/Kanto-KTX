<?php
// Kiểm tra dữ liệu được truyền từ Controller.
if (!isset($danhSach) || !is_array($danhSach)) {
    $danhSach = [];
}

// Hàm mã hóa dữ liệu trước khi hiển thị lên HTML.
if (!function_exists('ePhanAnh')) {
    function ePhanAnh(mixed $value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

$cacLoaiSuCo = ['Điện', 'Nước', 'Nội thất', 'Khác'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Phản ánh sự cố - KANTO</title>

    <link rel="stylesheet" href="/Kanto-KTX/assets/css/style.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/navbar.css">
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/PhanAn.css">
</head>

<body>

<?php require __DIR__ . '/../../includes/header.php'; ?>
<?php require __DIR__ . '/../../includes/navbar.php'; ?>

<main class="pa-container">

    <!-- TIÊU ĐỀ TRANG -->
    <section class="pa-page-heading">
        <h1>Phản ánh sự cố</h1>
        <p>Gửi và theo dõi các phản ánh về sự cố, hỏng hóc trong ký túc xá.</p>
    </section>

    <!-- THÔNG BÁO THÀNH CÔNG -->
    <?php if (!empty($thongBao)): ?>
        <div class="pa-alert pa-alert-success" role="status">
            <?= ePhanAnh($thongBao) ?>
        </div>
    <?php endif; ?>

    <!-- THÔNG BÁO LỖI -->
    <?php if (!empty($loi)): ?>
        <div class="pa-alert pa-alert-error" role="alert">
            <?= ePhanAnh($loi) ?>
        </div>
    <?php endif; ?>

    <!-- FORM GỬI PHẢN ÁNH -->
    <section class="pa-card">

        <div class="pa-card-heading">
            <h2>Gửi phản ánh mới</h2>
        </div>

        <form action="/Kanto-KTX/index.php?action=phan-anh"
         method="POST" class="pa-form">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= ePhanAnh($csrfToken ?? '') ?>"
            >

            <!-- PHÒNG -->
            <div class="pa-form-group">
                <label for="pa_phong">
                    Phòng <span class="pa-required">*</span>
                </label>

                <input
                    type="text"
                    id="pa_phong"
                    value="<?php
                        if (!empty($sinhVien['SoPhong'])) {
                            echo ePhanAnh($sinhVien['SoPhong']);
                        } elseif (!empty($sinhVien['MaPhong'])) {
                            echo ePhanAnh($sinhVien['MaPhong']);
                        } else {
                            echo 'Chưa có phòng lưu trú hợp lệ';
                        }
                    ?>"
                    readonly
                >
            </div>

            <!-- LOẠI SỰ CỐ -->
            <div class="pa-form-group">
                <label for="pa_loai">
                    Loại sự cố <span class="pa-required">*</span>
                </label>

                <select
                    id="pa_loai"
                    name="LoaiSuCo"
                    required
                    aria-invalid="<?= !empty($loiLoaiSuCo) ? 'true' : 'false' ?>"
                    aria-describedby="pa_loai_error"
                >
                    <option value="">Chọn loại sự cố...</option>

                    <?php foreach ($cacLoaiSuCo as $loai): ?>
                        <option
                            value="<?= ePhanAnh($loai) ?>"
                            <?= ($loaiDaChon ?? '') === $loai ? 'selected' : '' ?>
                        >
                            <?= ePhanAnh($loai) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <small
                    id="pa_loai_error"
                    class="pa-field-error"
                    aria-live="polite"
                ><?= ePhanAnh($loiLoaiSuCo ?? '') ?></small>
            </div>

            <!-- MÔ TẢ -->
            <div class="pa-form-group">
                <label for="pa_mota">
                    Mô tả <span class="pa-required">*</span>
                </label>

                <textarea
                    id="pa_mota"
                    name="MoTa"
                    rows="4"
                    minlength="10"
                    maxlength="2000"
                    required
                    placeholder="Nhập mô tả chi tiết về sự cố..."
                    aria-invalid="<?= !empty($loiMoTa) ? 'true' : 'false' ?>"
                    aria-describedby="pa_mota_error"
                ><?= ePhanAnh($moTaNhap ?? '') ?></textarea>

                <small
                    id="pa_mota_error"
                    class="pa-field-error"
                    aria-live="polite"
                ><?= ePhanAnh($loiMoTa ?? '') ?></small>
            </div>

            <!-- NÚT GỬI -->
            <div class="pa-form-actions">
                <button
                    type="submit"
                    class="pa-btn pa-btn-primary"
                    <?= empty($sinhVien['MaPhong']) ? 'disabled' : '' ?>
                >
                    Gửi phản ánh
                </button>
            </div>

        </form>

    </section>

    <!-- DANH SÁCH PHẢN ÁNH -->
    <section class="pa-card pa-history">

        <div class="pa-card-heading">
            <h2>Danh sách phản ánh</h2>
        </div>

        <div class="pa-table-wrapper">
            <table class="pa-table">

                <thead>
                    <tr>
                        <th>Mã phản ánh</th>
                        <th>Loại sự cố</th>
                        <th>Mô tả</th>
                        <th>Ngày gửi</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($danhSach)): ?>

                        <tr>
                            <td colspan="5" class="pa-empty">
                                Bạn chưa có phản ánh sự cố nào.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($danhSach as $phanAnh): ?>

                            <?php
                            // Ngày gửi: dd/mm/yyyy
                            $ngayGui = $phanAnh['ThoiGianGui'] ?? '';
                            $timestamp = $ngayGui !== '' ? strtotime($ngayGui) : false;
                            $ngayHienThi = $timestamp !== false
                                ? date('d/m/Y', $timestamp)
                                : '—';

                            // Trạng thái: chấp nhận cả "xử lý" và "xử lí".
                            $trangThai = $phanAnh['TrangThai'] ?? '';
                            $classTrangThai = in_array(
                                $trangThai,
                                ['Đã xử lý', 'Đã xử lí'],
                                true
                            ) ? 'pa-status-done' : 'pa-status-pending';
                            ?>

                            <tr>
                                <td class="pa-code">
                                    <?= ePhanAnh($phanAnh['MaPhanAnh'] ?? '') ?>
                                </td>

                                <td><?= ePhanAnh($phanAnh['LoaiSuCo'] ?? '') ?></td>

                                <td class="pa-description">
                                    <?= ePhanAnh($phanAnh['MoTa'] ?? '') ?>
                                </td>

                                <td><?= ePhanAnh($ngayHienThi) ?></td>

                                <td>
                                    <span class="pa-status <?= $classTrangThai ?>">
                                        <?= ePhanAnh($trangThai) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>
        </div>

    </section>

</main>

</body>
</html>