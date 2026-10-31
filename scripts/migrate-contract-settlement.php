<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/connection.php';

$db = createDatabaseConnection();

$db->exec("CREATE TABLE IF NOT EXISTS thanhlyhopdong (
    MaThanhLy varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    MaHopDong varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    NgayThanhLy date NOT NULL,
    TienHoanTra decimal(12,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (MaThanhLy),
    UNIQUE KEY uq_thanhly_hopdong (MaHopDong),
    CONSTRAINT fk_thanhly_hopdong FOREIGN KEY (MaHopDong) REFERENCES hopdong (MaHopDong) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$columns = [
    'SoTienPhaiThu' => "ADD COLUMN SoTienPhaiThu decimal(12,2) NOT NULL DEFAULT 0.00 AFTER MSSV",
    'TrangThai' => "ADD COLUMN TrangThai enum('Chưa thu','Đã thu') NOT NULL DEFAULT 'Chưa thu' AFTER SoTienPhaiThu",
    'ThoiGianThanhToan' => 'ADD COLUMN ThoiGianThanhToan datetime DEFAULT NULL AFTER TrangThai',
];

foreach ($columns as $column => $definition) {
    $check = $db->prepare("SELECT COUNT(*) FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = 'hoadondiennuocsinhvien' AND column_name = ?");
    $check->execute([$column]);
    if ((int) $check->fetchColumn() === 0) {
        $db->exec("ALTER TABLE hoadondiennuocsinhvien {$definition}");
    }
}

// Backfill dữ liệu cũ: chia tổng tiền theo danh sách sinh viên đã chụp tại lúc lập hóa đơn.
$rows = $db->query("SELECT us.MaHoaDon, us.MSSV, h.TongTien, h.TrangThai AS TrangThaiHoaDon,
        h.ThoiGianThanhToan, us.SoTienPhaiThu
    FROM hoadondiennuocsinhvien us
    JOIN hoadon h ON h.MaHoaDon = us.MaHoaDon
    ORDER BY us.MaHoaDon, us.MSSV")->fetchAll();
$groups = [];
foreach ($rows as $row) {
    $groups[$row['MaHoaDon']][] = $row;
}

$update = $db->prepare('UPDATE hoadondiennuocsinhvien
    SET SoTienPhaiThu = ?, TrangThai = ?, ThoiGianThanhToan = ?
    WHERE MaHoaDon = ? AND MSSV = ?');

foreach ($groups as $invoiceRows) {
    $totalCents = (int) round((float) $invoiceRows[0]['TongTien'] * 100);
    $baseCents = intdiv($totalCents, count($invoiceRows));
    $remainder = $totalCents % count($invoiceRows);
    foreach ($invoiceRows as $index => $row) {
        $amount = ($baseCents + ($index < $remainder ? 1 : 0)) / 100;
        $paid = $row['TrangThaiHoaDon'] === 'Đã thu';
        $update->execute([
            number_format($amount, 2, '.', ''),
            $paid ? 'Đã thu' : 'Chưa thu',
            $paid ? $row['ThoiGianThanhToan'] : null,
            $row['MaHoaDon'],
            $row['MSSV'],
        ]);
    }
}

echo "Đã chuẩn bị phân bổ hóa đơn điện nước và lịch sử thanh lý hợp đồng.\n";
