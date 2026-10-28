<?php
function eUpdate($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật sinh viên</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/qlSinhvien.css">
</head>
<body>

<div class="page">
    <header class="page-header">
        <div>
            <h1>Cập nhật sinh viên</h1>
            <p>Chỉnh sửa thông tin sinh viên trong hệ thống</p>
        </div>
        <a href="index.php?action=ql-sinhvien" class="btn-secondary">
            Quay lại
        </a>
    </header>

    <?php if ($loi !== ''): ?>
        <div class="alert error"><?= eUpdate($loi) ?></div>
    <?php endif; ?>

    <?php if (!empty($sinhVien)): ?>
        <section class="panel">
            <form action="index.php?action=ql-sinhvien-capnhat" method="POST">
                <input
                    type="hidden"
                    name="MSSV"
                    value="<?= eUpdate($sinhVien['MSSV']) ?>">

                <div class="form-grid">
                    <div class="field">
                        <label>Mã số sinh viên</label>
                        <input
                            type="text"
                            value="<?= eUpdate($sinhVien['MSSV']) ?>"
                            readonly>
                    </div>

                    <div class="field">
                        <label>Mã tài khoản</label>
                        <input
                            type="text"
                            value="<?= eUpdate($sinhVien['MaTK']) ?>"
                            readonly>
                    </div>

                    <div class="field">
                        <label for="MaUuTien">Mã ưu tiên</label>
                        <input
                            id="MaUuTien"
                            name="MaUuTien"
                            type="text"
                            maxlength="10"
                            required
                            value="<?= eUpdate($sinhVien['MaUuTien']) ?>">
                    </div>

                    <div class="field">
                        <label for="HoTen">Họ và tên</label>
                        <input
                            id="HoTen"
                            name="HoTen"
                            type="text"
                            maxlength="100"
                            required
                            value="<?= eUpdate($sinhVien['HoTen']) ?>">
                    </div>

                    <div class="field">
                        <label for="NgaySinh">Ngày sinh</label>
                        <input
                            id="NgaySinh"
                            name="NgaySinh"
                            type="date"
                            required
                            value="<?= eUpdate($sinhVien['NgaySinh']) ?>">
                    </div>

                    <div class="field">
                        <label for="CCCD">CCCD</label>
                        <input
                            id="CCCD"
                            name="CCCD"
                            type="text"
                            maxlength="20"
                            required
                            value="<?= eUpdate($sinhVien['CCCD']) ?>">
                    </div>

                    <div class="field">
                        <label for="GioiTinh">Giới tính</label>
                        <select id="GioiTinh" name="GioiTinh" required>
                            <option value="Nam"
                                <?= $sinhVien['GioiTinh'] === 'Nam' ? 'selected' : '' ?>>
                                Nam
                            </option>
                            <option value="Nữ"
                                <?= $sinhVien['GioiTinh'] === 'Nữ' ? 'selected' : '' ?>>
                                Nữ
                            </option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="QueQuan">Quê quán</label>
                        <input
                            id="QueQuan"
                            name="QueQuan"
                            type="text"
                            maxlength="150"
                            required
                            value="<?= eUpdate($sinhVien['QueQuan']) ?>">
                    </div>

                    <div class="field">
                        <label for="SDT">Số điện thoại</label>
                        <input
                            id="SDT"
                            name="SDT"
                            type="tel"
                            maxlength="15"
                            required
                            value="<?= eUpdate($sinhVien['SDT']) ?>">
                    </div>

                    <div class="field">
                        <label for="Lop">Lớp</label>
                        <input
                            id="Lop"
                            name="Lop"
                            type="text"
                            maxlength="30"
                            required
                            value="<?= eUpdate($sinhVien['Lop']) ?>">
                    </div>

                    <div class="field">
                        <label for="NienKhoa">Niên khóa</label>
                        <input
                            id="NienKhoa"
                            name="NienKhoa"
                            type="text"
                            maxlength="20"
                            required
                            value="<?= eUpdate($sinhVien['NienKhoa']) ?>">
                    </div>

                    <div class="field">
                        <label for="TrangThaiLuuTru">Trạng thái lưu trú</label>
                        <select
                            id="TrangThaiLuuTru"
                            name="TrangThaiLuuTru"
                            required>
                            <option value="Đang ở"
                                <?= $sinhVien['TrangThaiLuuTru'] === 'Đang ở' ? 'selected' : '' ?>>
                                Đang ở
                            </option>
                            <option value="Đã rời đi"
                                <?= $sinhVien['TrangThaiLuuTru'] === 'Đã rời đi' ? 'selected' : '' ?>>
                                Đã rời đi
                            </option>
                        </select>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-primary">
                        Lưu thay đổi
                    </button>
                    <a href="index.php?action=ql-sinhvien" class="btn-secondary">
                        Hủy
                    </a>
                </div>
            </form>
        </section>
    <?php else: ?>
        <section class="panel">
            <p class="empty">Không có thông tin sinh viên để hiển thị.</p>
        </section>
    <?php endif; ?>
</div>

</body>
</html>