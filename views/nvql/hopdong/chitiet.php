
<?php
// Hàm hiển thị dữ liệu an toàn
function eHopDong($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết hợp đồng - KANTO</title>
    <link rel="stylesheet" href="/Kanto-KTX/assets/css/uc5.css">
</head>

<body>

<!-- Thanh đầu trang -->
<header class="topbar">
    <div class="logo">
        <img src="/Kanto-KTX/assets/images/logo.png" alt="KANTO Logo">
        <span>KANTO</span>
    </div>

    <div class="infor_user">
        <span>Quản lý ký túc xá</span>
    </div>
</header>

<div class="layout">

    <!-- Thanh menu bên trái -->
    <aside class="slidebar">
        <h3>QUẢN LÝ KÝ TÚC XÁ</h3>

        <ul>
            <li><a href="index.php">Trang chủ</a></li>
            <li><a href="uc1.php">Quản lý sinh viên</a></li>
            <li><a href="uc2.php">Quản lý phòng</a></li>
            <li><a href="uc3.php">Quản lý thanh toán</a></li>
            <li><a href="uc4.php">Quản lý hóa đơn</a></li>
            <li class="active">
                <a href="index.php?action=nvql-hopdong">Quản lý hợp đồng</a>
            </li>
        </ul>
    </aside>

    <!-- Nội dung chính -->
    <main class="content">

        <h1>Chi tiết hợp đồng</h1>

        <div class="panel">

            <?php if (!empty($loi)): ?>

                <p class="no_data">
                    <?= eHopDong($loi) ?>
                </p>

            <?php elseif (!empty($hopDong)): ?>

                <h2>Thông tin hợp đồng</h2>

                <div class="form_grid">

                    <div class="form_group">
                        <label>Mã hợp đồng</label>
                        <p><?= eHopDong($hopDong['MaHopDong'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Trạng thái</label>
                        <p><?= eHopDong($hopDong['TrangThaiThucTe'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Ngày bắt đầu</label>
                        <p><?= eHopDong($hopDong['NgayBatDau'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Ngày kết thúc</label>
                        <p><?= eHopDong($hopDong['NgayKetThuc'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Mã số sinh viên</label>
                        <p><?= eHopDong($hopDong['MSSV'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Họ và tên sinh viên</label>
                        <p><?= eHopDong($hopDong['HoTen'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Số điện thoại</label>
                        <p><?= eHopDong($hopDong['SDT'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Lớp</label>
                        <p><?= eHopDong($hopDong['Lop'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Mã phòng</label>
                        <p><?= eHopDong($hopDong['MaPhong'] ?? '') ?></p>
                    </div>

                    <div class="form_group">
                        <label>Số phòng</label>
                        <p><?= eHopDong($hopDong['SoPhong'] ?? '') ?></p>
                    </div>

                </div>

            <?php endif; ?>

            <div class="form_actions">
                <a href="index.php?action=nvql-hopdong" class="btn_reset">
                    Quay lại danh sách
                </a>
            </div>

        </div>

    </main>

</div>

</body>
</html>
