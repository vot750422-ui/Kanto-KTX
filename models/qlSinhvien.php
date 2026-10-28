<?php

class QlSinhvien
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Tra cứu sinh viên theo MSSV, họ tên hoặc lớp
    public function layDanhSach(array $filters = []): array
    {
        $sql = "SELECT MSSV, MaTK, MaUuTien, HoTen, NgaySinh,
                       CCCD, GioiTinh, QueQuan, SDT, Lop,
                       NienKhoa, TrangThaiLuuTru
                FROM sinhvien
                WHERE 1 = 1";

        $params = [];

        if (!empty($filters['mssv'])) {
            $sql .= " AND MSSV LIKE :mssv";
            $params[':mssv'] = '%' . trim($filters['mssv']) . '%';
        }

        if (!empty($filters['ho_ten'])) {
            $sql .= " AND HoTen LIKE :ho_ten";
            $params[':ho_ten'] = '%' . trim($filters['ho_ten']) . '%';
        }

        if (!empty($filters['lop'])) {
            $sql .= " AND Lop LIKE :lop";
            $params[':lop'] = '%' . trim($filters['lop']) . '%';
        }

        $sql .= " ORDER BY MSSV ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy thông tin chi tiết một sinh viên
    public function layChiTiet(string $mssv): ?array
    {
        $sql = "SELECT MSSV, MaTK, MaUuTien, HoTen, NgaySinh,
                       CCCD, GioiTinh, QueQuan, SDT, Lop,
                       NienKhoa, TrangThaiLuuTru
                FROM sinhvien
                WHERE MSSV = :mssv
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mssv' => $mssv]);

        $sinhVien = $stmt->fetch(PDO::FETCH_ASSOC);

        return $sinhVien ?: null;
    }

    // Cập nhật thông tin sinh viên, không đổi MSSV và MaTK
    public function capNhat(array $data): bool
    {
        $sql = "UPDATE sinhvien
                SET MaUuTien = :ma_uu_tien,
                    HoTen = :ho_ten,
                    NgaySinh = :ngay_sinh,
                    CCCD = :cccd,
                    GioiTinh = :gioi_tinh,
                    QueQuan = :que_quan,
                    SDT = :sdt,
                    Lop = :lop,
                    NienKhoa = :nien_khoa,
                    TrangThaiLuuTru = :trang_thai
                WHERE MSSV = :mssv";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':ma_uu_tien' => $data['MaUuTien'],
            ':ho_ten' => $data['HoTen'],
            ':ngay_sinh' => $data['NgaySinh'],
            ':cccd' => $data['CCCD'],
            ':gioi_tinh' => $data['GioiTinh'],
            ':que_quan' => $data['QueQuan'],
            ':sdt' => $data['SDT'],
            ':lop' => $data['Lop'],
            ':nien_khoa' => $data['NienKhoa'],
            ':trang_thai' => $data['TrangThaiLuuTru'],
            ':mssv' => $data['MSSV'],
        ]);
    }
}