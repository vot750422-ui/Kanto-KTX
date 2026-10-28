<?php
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sinh viên</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/qlSinhvien.css">
</head>
<body>

<div class="page">
    <header class="page-header">
        <div>
            <h1>Quản lý sinh viên</h1>
            <p>Tra cứu và cập nhật thông tin sinh viên</p>
        </div>
    </header>

    <?php if (($_GET['thong_bao'] ?? '') === 'cap_nhat_thanh_cong'): ?>
        <div class="alert success">Cập nhật thông tin sinh viên thành công.</div>
    <?php endif; ?>

    <section class="panel">
        <h2>Tra cứu sinh viên</h2>

        <form action="index.php" method="GET">
            <input type="hidden" name="action" value="ql-sinhvien">

            <div class="search-grid">
                <div class="field">
                    <label for="mssv">Mã số sinh viên</label>
                    <input
                        type="text"
                        id="mssv"
                        name="mssv"
                        value="<?= e($_GET['mssv'] ?? '') ?>"
                        placeholder="Nhập MSSV">
                </div>

                <div class="field">
                    <label for="ho_ten">Họ và tên</label>
                    <input
                        type="text"
                        id="ho_ten"
                        name="ho_ten"
                        value="<?= e($_GET['ho_ten'] ?? '') ?>"
                        placeholder="Nhập họ tên">
                </div>

                <div class="field">
                    <label for="lop">Lớp</label>
                    <input
                        type="text"
                        id="lop"
                        name="lop"
                        value="<?= e($_GET['lop'] ?? '') ?>"
                        placeholder="Nhập lớp">
                </div>
            </div>

            <div class="actions">
                <button type="submit" name="btn_search" value="1" class="btn-primary">
                    Tra cứu
                </button>
                <a href="index.php?action=ql-sinhvien" class="btn-secondary">
                    Làm mới
                </a>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>Danh sách sinh viên</h2>

        <?php if (!$daTimKiem): ?>
            <p class="empty">Nhập điều kiện và nhấn Tra cứu để xem danh sách.</p>
        <?php elseif (empty($sinhviens)): ?>
            <p class="empty">Không tìm thấy sinh viên phù hợp.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>MSSV</th>
                            <th>Họ tên</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Số điện thoại</th>
                            <th>Lớp</th>
                            <th>Niên khóa</th>
                            <th>Trạng thái lưu trú</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sinhviens as $sv): ?>
                        <tr>
                            <td><?= e($sv['MSSV']) ?></td>
                            <td><?= e($sv['HoTen']) ?></td>
                            <td><?= e($sv['NgaySinh']) ?></td>
                            <td><?= e($sv['GioiTinh']) ?></td>
                            <td><?= e($sv['SDT']) ?></td>
                            <td><?= e($sv['Lop']) ?></td>
                            <td><?= e($sv['NienKhoa']) ?></td>
                            <td><?= e($sv['TrangThaiLuuTru']) ?></td>
                            <td>
                                <a
                                    class="btn-link"
                                    href="index.php?action=ql-sinhvien-capnhat&amp;mssv=<?= urlencode($sv['MSSV']) ?>">
                                    Cập nhật
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="result-count">
                Tìm thấy <?= count($sinhviens) ?> sinh viên.
            </p>
        <?php endif; ?>
    </section>
</div>

</body>
</html>