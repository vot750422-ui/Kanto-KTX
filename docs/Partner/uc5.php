<?php
require_once __DIR__ . '/../../config/db.php';

// Lấy dữ liệu tìm kiếm từ form
$ma_hd = trim($_GET['ma_hd'] ?? '');
$mssv = trim($_GET['mssv'] ?? '');
$ho_ten = trim($_GET['ho_ten'] ?? '');
$phong = trim($_GET['phong'] ?? '');
$trang_thai = trim($_GET['trang_thai'] ?? '');

$is_searched = isset($_GET['btn_search']);
$hopdongs = [];

// Chỉ thực thi truy vấn khi người dùng nhấn "Tìm kiếm"
if ($is_searched) {

    // Trạng thái thực tế tính theo ngày kết thúc (hợp đồng đã thanh lý giữ nguyên)
    $statusExpr = "CASE
                      WHEN hd.TrangThai = 'Đã thanh lý' THEN 'Đã thanh lý'
                      WHEN hd.NgayKetThuc >= CURDATE() THEN 'Còn hạn'
                      ELSE 'Hết hạn'
                   END";

    $sql = "SELECT hd.*, $statusExpr AS TrangThaiThucTe FROM hopdong hd";
    $params = [];
    $where = [];

    // 1. Tìm theo họ tên sinh viên
    if ($ho_ten !== '') {
        $sql .= " JOIN sinhvien sv ON hd.MSSV = sv.MSSV";
        $where[] = "sv.HoTen LIKE :ho_ten";
        $params[':ho_ten'] = "%$ho_ten%";
    }

    // 2. Tìm theo mã hợp đồng
    if ($ma_hd !== '') {
        $where[] = "hd.MaHopDong LIKE :ma_hd";
        $params[':ma_hd'] = "%$ma_hd%";
    }

    // 3. Tìm theo MSSV
    if ($mssv !== '') {
        $where[] = "hd.MSSV LIKE :mssv";
        $params[':mssv'] = "%$mssv%";
    }

    // 4. Tìm theo mã phòng
    if ($phong !== '') {
        $where[] = "hd.MaPhong LIKE :phong";
        $params[':phong'] = "%$phong%";
    }

    // 5. Lọc theo trạng thái thực tế nếu người dùng có chọn
    $map_trang_thai = [
        'con_han'     => 'Còn hạn',
        'het_han'     => 'Hết hạn',
        'da_thanh_ly' => 'Đã thanh lý',
        'Còn hạn'     => 'Còn hạn',
        'Hết hạn'     => 'Hết hạn',
        'Đã thanh lý' => 'Đã thanh lý',
    ];
    if (isset($map_trang_thai[$trang_thai])) {
        $where[] = "($statusExpr) = :trang_thai";
        $params[':trang_thai'] = $map_trang_thai[$trang_thai];
    }
    // Nếu $trang_thai === 'tat_ca' hoặc rỗng thì KHÔNG thêm điều kiện lọc trạng thái

    // Ghép TẤT CẢ các điều kiện lại bằng "AND"
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Sắp xếp hợp đồng mới nhất lên đầu
    $sql .= " ORDER BY hd.NgayBatDau DESC";

    // Thực thi truy vấn SQL
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hopdongs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hợp đồng - KANTO</title>
    <link rel="stylesheet" href="uc5.css">
</head>

<body>

<!-- Thanh đầu trang -->
<header class="topbar">
    <div class="logo">
        <img src="../../assets/images/logo.png" alt="KANTO Logo">
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
            <li><a href="../../index.php">Trang chủ</a></li>
            <li><a href="uc1.php">Quản lý sinh viên</a></li>
            <li><a href="uc2.php">Quản lý phòng</a></li>
            <li><a href="uc3.php">Quản lý thanh toán</a></li>
            <li><a href="uc4.php">Quản lý hóa đơn</a></li>
            <li class="active"><a href="uc5.php">Quản lý hợp đồng</a></li>
        </ul>
    </aside>

    <!-- Nội dung chính -->
    <main class="content">

        <h1>Quản lý hợp đồng</h1>

        <!-- Form tìm kiếm -->
        <div class="panel">
            <h2>Tìm kiếm hợp đồng</h2>

            <form action="uc5.php" method="GET" autocomplete="off">
                <!-- Thẻ input ẩn gửi cờ báo hiệu đã bấm tìm kiếm -->
                <input type="hidden" name="btn_search" value="1">

                <div class="form_grid">

                    <div class="form_group">
                        <label for="ma_hd">Mã hợp đồng</label>
                        <input
                            type="text"
                            id="ma_hd"
                            name="ma_hd"
                            placeholder="Nhập mã hợp đồng"
                            value="<?= htmlspecialchars($ma_hd) ?>"
                        >
                    </div>

                    <div class="form_group">
                        <label for="mssv">Mã số sinh viên</label>
                        <input
                            type="text"
                            id="mssv"
                            name="mssv"
                            placeholder="Nhập MSSV"
                            value="<?= htmlspecialchars($mssv) ?>"
                        >
                    </div>

                    <div class="form_group">
                        <label for="ho_ten">Họ và tên</label>
                        <input
                            type="text"
                            id="ho_ten"
                            name="ho_ten"
                            placeholder="Nhập họ tên sinh viên"
                            value="<?= htmlspecialchars($ho_ten) ?>"
                        >
                    </div>

                    <div class="form_group">
                        <label for="phong">Mã phòng</label>
                        <input
                            type="text"
                            id="phong"
                            name="phong"
                            placeholder="Nhập mã phòng"
                            value="<?= htmlspecialchars($phong) ?>"
                        >
                    </div>

                </div>

                <div class="form_group">
                    <label>Trạng thái hợp đồng</label>

                    <!-- Bấm chọn trạng thái là tự lọc lại kết quả -->
                    <div class="radio_group">

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="tat_ca"
                                onchange="this.form.submit()"
                                <?= ($trang_thai === 'tat_ca' || $trang_thai === '') ? 'checked' : '' ?>
                            >
                            Tất cả
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="con_han"
                                onchange="this.form.submit()"
                                <?= ($trang_thai === 'con_han' || $trang_thai === 'Còn hạn') ? 'checked' : '' ?>
                            >
                            Còn hạn
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="het_han"
                                onchange="this.form.submit()"
                                <?= ($trang_thai === 'het_han' || $trang_thai === 'Hết hạn') ? 'checked' : '' ?>
                            >
                            Hết hạn
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="trang_thai"
                                value="da_thanh_ly"
                                onchange="this.form.submit()"
                                <?= ($trang_thai === 'da_thanh_ly' || $trang_thai === 'Đã thanh lý') ? 'checked' : '' ?>
                            >
                            Đã thanh lý
                        </label>

                    </div>
                </div>

                <div class="form_actions">
                    <button type="submit" class="btn_search">
                        Tìm kiếm
                    </button>

                    <a href="uc5.php" class="btn_reset">
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
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="no_data">
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
    // Bấm "Tìm kiếm" hoặc chọn trạng thái (gửi form) thì không bị ảnh hưởng.
    (function () {
        var nav = performance.getEntriesByType('navigation')[0];
        if (nav && nav.type === 'reload' && window.location.search) {
            window.location.replace(window.location.pathname);
        }
    })();
</script>

</body>
</html>
