<?php
require_once __DIR__ . '/../../config/db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Truy vấn lấy tất cả thông tin phòng từ bảng 'phong'
    $sql = "SELECT MaPhong, MaToa, SoPhong, GioiTinhPhong, SucChua, hienco FROM phong";
    $stmt =$pdo->prepare($sql);$stmt->execute();
    $phongList =$stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $phongList = [];$errorMsg = "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem thông tin phòng</title>
    <link rel="stylesheet" href="uc15.css">
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="KANTO Logo">
            <span>KANTO</span>
        </div>
        <nav class="menu">
            <a href="../../index.php">Trang chủ</a>
            <a href="../../index.php?action=register">Đăng ký</a>
            <a href="uc15.php" class="active">Thông tin phòng</a>
            <a href="#">Hướng dẫn</a>
            <a href="../../index.php?action=login">Đăng nhập</a>
        </nav>
    </header>

    <!-- Nội dung chính -->
    <main class="content">
        <h1>Thông tin phòng</h1>

        <!-- Dòng chú thích màu (Legend) - Không dùng Checkbox -->
        <div class="legend">
            <span><span class="box"></span> Còn chỗ</span>
            <span><span class="box full"></span> Đầy</span>
        </div>

        <!-- Bộ lọc -->
        <div class="filters">
            <div class="khu-row">
                <strong>Khu</strong>
                <button class="active">Khu Nam</button>
                <button>Khu Nữ</button>
            </div>

            <div class="toa-row">
                <strong>Tòa</strong>
                <button class="active">Tòa A</button>
                <button>Tòa B</button>
            </div>
        </div>

        <!-- Tiêu đề kết quả -->
        <div class="room-header">
            <h3 id="header-title">Tòa A</h3>
        </div>

        <!-- Danh sách phòng -->
        <div class="room-grid">
            <?php if (!empty($phongList)): ?>
                <?php foreach ($phongList as$phong): ?>
                    <?php
                        $sucChua = (int)$phong['SucChua'];$hienCo = (int)$phong['hienco'];$choTrong = $sucChua -$hienCo;

                        $isFull = ($hienCo >=$sucChua);
                        $statusText =$isFull ? 'Đầy' : 'Còn chỗ';
                        $statusClass =$isFull ? 'full' : 'available';

                        $gioiTinh = isset($phong['GioiTinhPhong']) ? $phong['GioiTinhPhong'] : 'Nam';$toa = isset($phong['MaToa']) ?$phong['MaToa'] : '';
                        $trangThai =$isFull ? 'day' : 'con-cho';
                    ?>
                    <div class="room-card <?= $statusClass ?>"
                         data-gioitinh="<?= htmlspecialchars($gioiTinh) ?>"
                         data-toa="<?= htmlspecialchars($toa) ?>"
                         data-trangthai="<?= $trangThai ?>">
                        <h4>Phòng <?= htmlspecialchars($phong['SoPhong']) ?></h4>
                        <p class="capacity"><?= $hienCo ?>/<?= $sucChua ?> sinh viên</p>
                        <span class="status-label"><?= $statusText ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Không có thông tin phòng.</p>
            <?php endif; ?>
        </div>

        <!-- Ghi chú quy định chân trang -->
        <div class="notes">
            <p>Dãy A và Dãy B: Nam • Dãy C và Dãy D: Nữ</p>
            <p>Mỗi dãy có 5 tầng • 10 phòng/tầng • 8 sinh viên/phòng • Chỉ có một loại phòng</p>
            <p>Thông tin số chỗ được cập nhật theo tình trạng lưu trú hiện tại.</p>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentFilters = {
        gioiTinh: 'Nam',
        toa: 'A'
    };

    // Chọn Khu, Tòa
    const buttonGroups = [
        { selector: '.khu-row', key: 'gioiTinh', parse: txt => txt.includes('Nam') ? 'Nam' : 'Nữ' },
        { selector: '.toa-row', key: 'toa', parse: txt => txt.replace('Tòa ', '').replace('Dãy ', '').trim() }
    ];

    buttonGroups.forEach(groupConfig => {
        const groupEl = document.querySelector(groupConfig.selector);
        if (groupEl) {
            const buttons = groupEl.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    buttons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    currentFilters[groupConfig.key] = groupConfig.parse(this.innerText);
                    filterRooms();
                });
            });
        }
    });

    // Hàm lọc dữ liệu phòng
    function filterRooms() {
        const cards = document.querySelectorAll('.room-card');
        let availableCount = 0;

        cards.forEach(card => {
            const cGioiTinh = card.getAttribute('data-gioitinh');
            const cToa = card.getAttribute('data-toa');
            const cTrangThai = card.getAttribute('data-trangthai');

            const matchGioiTinh = !currentFilters.gioiTinh || cGioiTinh === currentFilters.gioiTinh;
            const matchToa = !currentFilters.toa || cToa === currentFilters.toa;

            if (matchGioiTinh && matchToa) {
                card.style.display = 'block';
                if (cTrangThai === 'con-cho') availableCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const titleEl = document.getElementById('header-title');
        const summaryEl = document.getElementById('header-summary');

        if (titleEl) titleEl.textContent = `Tòa ${currentFilters.toa || ''}`;
        if (summaryEl) summaryEl.textContent = `${availableCount} phòng trống • ${currentFilters.gioiTinh || ''}`;
    }

    filterRooms();
});
</script>
</body>
</html>
