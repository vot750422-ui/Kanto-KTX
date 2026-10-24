<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hợp đồng - KANTO</title>
    <link rel="stylesheet" href="assets/css/uc5.css">
</head>

<body>

<!-- Thanh đầu trang -->
<header class="topbar">
    <div class="logo">
        <img src="assets/images/logo.png" alt="KANTO Logo">
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
            <li><a href="#">Quản lý sinh viên</a></li>
            <li><a href="#">Quản lý phòng</a></li>
            <li><a href="#">Quản lý thanh toán</a></li>
            <li><a href="#">Quản lý hóa đơn</a></li>
            <li class="active">
                <a href="index.php?action=nvql-hopdong">Quản lý hợp đồng</a>
            </li>
        </ul>
    </aside>

    <!-- Nội dung chính -->
    <main class="content">

        <h1>Quản lý hợp đồng</h1>

        <!-- Form tìm kiếm -->
        <div class="panel">
            <h2>Tìm kiếm hợp đồng</h2>

            <form action="index.php" method="GET" autocomplete="off">
                <input type="hidden" name="action" value="nvql-hopdong">
                <input type="hidden" name="btn_search" value="1">

                <div class="form_grid">

                    <div class="form_group">
                        <label for="ma_hd">Mã hợp đồng</label>
                        <input
                            type="text"
                            id="ma_hd"
                            name="ma_hd"
                            placeholder="Nhập mã hợp đồng"
                            value="<?= htmlspecialchars($ma_hd ?? '') ?>"
                        >
                    </div>

                    <div class="form_group">
                        <label for="mssv">Mã số sinh viên</label>
                        <input
                            type="text"
                            id="mssv"
                            name="mssv"
                            placeholder="Nhập MSSV"
                            value="<?= htmlspecialchars($mssv ?? '') ?>"
                        >
                    </div>

                    <div class="form_group">
                        <label for="ho_ten">Họ và tên</label>
                        <input
                            type="text"
                            id="ho_ten"
                            name="ho_ten"
                            placeholder="Nhập họ tên sinh viên"
                            value="<?= htmlspecialchars($ho_ten ?? '') ?>"
                        >
                    </div>

                    <div class="form_group">
                        <label for="phong">Mã phòng</label>
                        <input
                            type="text"
                            id="phong"
                            name="phong"
                            placeholder="Nhập mã phòng"
                            value="<?= htmlspecialchars($phong ?? '') ?>"
                        >
                    </div>

                </div>

                <div class="form_group">
                    <label>Trạng thái hợp đồng</label>

                    <div class="radio_group">

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="tat_ca"
                                onchange="this.form.submit()"
                                <?= (($trang_thai ?? '') === 'tat_ca' || ($trang_thai ?? '') === '') ? 'checked' : '' ?>
                            >
                            Tất cả
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="con_han"
                                onchange="this.form.submit()"
                                <?= (($trang_thai ?? '') === 'con_han' || ($trang_thai ?? '') === 'Còn hạn') ? 'checked' : '' ?>
                            >
                            Còn hạn
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="het_han"
                                onchange="this.form.submit()"
                                <?= (($trang_thai ?? '') === 'het_han' || ($trang_thai ?? '') === 'Hết hạn') ? 'checked' : '' ?>
                            >
                            Hết hạn
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="da_thanh_ly"
                                onchange="this.form.submit()"
                                <?= (($trang_thai ?? '') === 'da_thanh_ly' || ($trang_thai ?? '') === 'Đã thanh lý') ? 'checked' : '' ?>
                            >
                            Đã thanh lý
                        </label>

                    </div>
                </div>

                <div class="form_actions">
                    <button type="submit" class="btn_search">
                        Tìm kiếm
                    </button>

                    <a href="index.php?action=nvql-hopdong" class="btn_reset">
                        Làm mới
                    </a>
                </div>

            </form>
        </div>

        <!-- Danh sách hợp đồng -->
        <div class="panel2">

            <h2>Danh sách hợp đồng</h2>

            <div class="table_container">

                <table>
                    <thead>
                        <tr>
                            <th>Mã hợp đồng</th>
                            <th>MSSV</th>
                            <th>Mã phòng</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($is_searched && !empty($hopdongs)): ?>

                            <?php foreach ($hopdongs as $hd): ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars($hd['MaHopDong'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($hd['MSSV'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($hd['MaPhong'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($hd['NgayBatDau'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($hd['NgayKetThuc'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($hd['TrangThaiThucTe'] ?? '') ?>
                                    </td>

                                    <td>
                                        <a href="index.php?action=nvql-hopdong-chitiet&amp;ma_hd=<?= urlencode($hd['MaHopDong'] ?? '') ?>">
                                            Xem chi tiết
                                        </a>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" class="no_data">
                                    <?= $is_searched
                                        ? 'Không tìm thấy hợp đồng phù hợp.'
                                        : 'Vui lòng chọn thông tin và nhấn Tìm kiếm để xem kết quả.'
                                    ?>
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>

    </main>

</div>

<script>
    // Giữ nguyên hành vi làm mới khi tải lại trang
    (function () {
        var nav = performance.getEntriesByType('navigation')[0];
        if (nav && nav.type === 'reload' && window.location.search) {
            window.location.replace(window.location.pathname);
        }
    })();
</script>

</body>
</html>
