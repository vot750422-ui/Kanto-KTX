
<?php

class HopDong
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // UC05.1: Tra cứu danh sách hợp đồng
    public function layDanhSach(array $filters): array
    {
        $ma_hd = trim($filters['ma_hd'] ?? '');
        $mssv = trim($filters['mssv'] ?? '');
        $ho_ten = trim($filters['ho_ten'] ?? '');
        $phong = trim($filters['phong'] ?? '');
        $trang_thai = trim($filters['trang_thai'] ?? '');

        // Tính trạng thái thực tế theo ngày kết thúc
        // Hợp đồng đã thanh lý giữ nguyên trạng thái
        $statusExpr = "CASE
            WHEN hd.TrangThai = 'Đã thanh lý' THEN 'Đã thanh lý'
            WHEN hd.NgayKetThuc >= CURDATE() THEN 'Còn hạn'
            ELSE 'Hết hạn'
        END";

        $sql = "SELECT hd.*, $statusExpr AS TrangThaiThucTe
                FROM hopdong hd";

        $params = [];
        $where = [];

        // Tìm theo họ tên sinh viên
        if ($ho_ten !== '') {
            $sql .= " JOIN sinhvien sv ON hd.MSSV = sv.MSSV";
            $where[] = "sv.HoTen LIKE :ho_ten";
            $params[':ho_ten'] = "%$ho_ten%";
        }

        // Tìm theo mã hợp đồng
        if ($ma_hd !== '') {
            $where[] = "hd.MaHopDong LIKE :ma_hd";
            $params[':ma_hd'] = "%$ma_hd%";
        }

        // Tìm theo mã số sinh viên
        if ($mssv !== '') {
            $where[] = "hd.MSSV LIKE :mssv";
            $params[':mssv'] = "%$mssv%";
        }

        // Tìm theo mã phòng
        if ($phong !== '') {
            $where[] = "hd.MaPhong LIKE :phong";
            $params[':phong'] = "%$phong%";
        }

        // Ánh xạ giá trị trạng thái từ form
        $map_trang_thai = [
            'con_han' => 'Còn hạn',
            'het_han' => 'Hết hạn',
            'da_thanh_ly' => 'Đã thanh lý',
            'Còn hạn' => 'Còn hạn',
            'Hết hạn' => 'Hết hạn',
            'Đã thanh lý' => 'Đã thanh lý'
        ];

        if (isset($map_trang_thai[$trang_thai])) {
            $where[] = "($statusExpr) = :trang_thai";
            $params[':trang_thai'] = $map_trang_thai[$trang_thai];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY hd.NgayBatDau DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UC05.2: Lấy thông tin chi tiết một hợp đồng
    public function layChiTiet(string $maHopDong): ?array
    {
        $statusExpr = "CASE
            WHEN hd.TrangThai = 'Đã thanh lý' THEN 'Đã thanh lý'
            WHEN hd.NgayKetThuc >= CURDATE() THEN 'Còn hạn'
            ELSE 'Hết hạn'
        END";

        $sql = "SELECT
                    hd.*,
                    $statusExpr AS TrangThaiThucTe,
                    sv.HoTen,
                    sv.SDT,
                    sv.Lop,
                    p.SoPhong
                FROM hopdong hd
                LEFT JOIN sinhvien sv ON sv.MSSV = hd.MSSV
                LEFT JOIN phong p ON p.MaPhong = hd.MaPhong
                WHERE hd.MaHopDong = :ma_hd
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ma_hd' => $maHopDong
        ]);

        $hopDong = $stmt->fetch(PDO::FETCH_ASSOC);

        return $hopDong ?: null;
    }
}
