<?php

class Room
{
    public function __construct(private PDO $db) {}

    public function buildings(string $gender): array
    {
        $stmt = $this->db->prepare('SELECT DISTINCT t.MaToa, t.TenToa
            FROM toanha t JOIN phong p ON p.MaToa = t.MaToa
            WHERE p.GioiTinhPhong = ? ORDER BY t.TenToa, t.MaToa');
        $stmt->execute([$gender]);
        return $stmt->fetchAll();
    }

    public function floors(string $gender, string $building): array
    {
        $stmt = $this->db->prepare('SELECT DISTINCT Tang FROM phong
            WHERE GioiTinhPhong = ? AND MaToa = ? ORDER BY Tang');
        $stmt->execute([$gender, $building]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function rooms(string $gender, ?string $building = null, ?int $floor = null, bool $availableOnly = false): array
    {
        // Dùng cùng công thức cho UC02/UC15; đơn đã chuyển sang hợp đồng không giữ chỗ lần hai.
        $sql = "SELECT p.*, t.TenToa,
            COALESCE(h.DangO, 0) AS DangO, COALESCE(d.DangGiu, 0) AS DangGiu,
            p.SucChua - COALESCE(h.DangO, 0) - COALESCE(d.DangGiu, 0) AS ConCho
            FROM phong p JOIN toanha t ON t.MaToa = p.MaToa
            LEFT JOIN (SELECT MaPhong, COUNT(*) AS DangO FROM hopdong
                WHERE TrangThai = 'Còn hạn' GROUP BY MaPhong) h ON h.MaPhong = p.MaPhong
            LEFT JOIN (SELECT MaPhong, COUNT(*) AS DangGiu FROM dondangky
                WHERE TrangThai = 'Chờ duyệt' OR (TrangThai = 'Đã duyệt' AND MaHopDong IS NULL)
                GROUP BY MaPhong) d ON d.MaPhong = p.MaPhong
            WHERE p.GioiTinhPhong = ?";
        $params = [$gender];
        if ($building !== null) {
            $sql .= ' AND p.MaToa = ?';
            $params[] = $building;
        }
        if ($floor !== null) {
            $sql .= ' AND p.Tang = ?';
            $params[] = $floor;
        }
        if ($availableOnly) {
            $sql .= ' HAVING ConCho > 0';
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY t.TenToa, p.Tang, p.SoPhong, p.MaPhong');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
