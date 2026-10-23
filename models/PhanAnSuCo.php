<?php

class PhanAnhSuCo
{
    public function __construct(private PDO $db) {}

    // Lấy thông tin sinh viên và phòng đang ở theo hợp đồng còn hạn.
    public function layThongTinSinhVien(int $maTK): ?array
    {
        $sql = "SELECT
                    sv.MSSV,
                    sv.HoTen,
                    p.MaPhong,
                    p.SoPhong
                FROM sinhvien sv
                LEFT JOIN hopdong h
                    ON h.MSSV = sv.MSSV
                    AND h.TrangThai = 'Còn hạn'
                    AND h.NgayBatDau <= CURDATE()
                    AND h.NgayKetThuc >= CURDATE()
                LEFT JOIN phong p
                    ON p.MaPhong = h.MaPhong
                WHERE sv.MaTK = :maTK
                ORDER BY h.NgayBatDau DESC, h.MaHopDong DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'maTK' => $maTK
        ]);

        $sinhVien = $stmt->fetch(PDO::FETCH_ASSOC);

        return $sinhVien ?: null;
    }

    // Lấy lịch sử phản ánh của sinh viên.
    public function layDanhSach(string $mssv): array
    {
        $sql = "SELECT MaPhanAnh, MaPhong, LoaiSuCo,
                       MoTa, ThoiGianGui, TrangThai
                FROM phananhsuco
                WHERE MSSV = :mssv
                ORDER BY ThoiGianGui DESC, MaPhanAnh DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mssv' => $mssv]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo mã phản ánh: 10 ký tự, bắt đầu bằng 'PA' + 8 chữ số, không trùng.
    private function taoMaPhanAnh(): string
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM phananhsuco WHERE MaPhanAnh = :ma"
        );

        do {
            $maPhanAnh = 'PA' . str_pad(
                (string) random_int(0, 99999999),
                8,
                '0',
                STR_PAD_LEFT
            );

            $stmt->execute(['ma' => $maPhanAnh]);
            $daTonTai = (int) $stmt->fetchColumn() > 0;
        } while ($daTonTai);

        return $maPhanAnh;
    }

    // Tạo phản ánh sự cố mới, trả về mã phản ánh.
    // ThoiGianGui và TrangThai dùng giá trị DEFAULT của bảng.
    public function taoPhanAnh(
        string $mssv,
        string $maPhong,
        string $loaiSuCo,
        string $moTa
    ): string {
        $maPhanAnh = $this->taoMaPhanAnh();

        $sql = "INSERT INTO phananhsuco
                    (MaPhanAnh, MSSV, MaPhong, LoaiSuCo, MoTa)
                VALUES
                    (:maPhanAnh, :mssv, :maPhong, :loaiSuCo, :moTa)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'maPhanAnh' => $maPhanAnh,
            'mssv'      => $mssv,
            'maPhong'   => $maPhong,
            'loaiSuCo'  => $loaiSuCo,
            'moTa'      => $moTa,
        ]);

        return $maPhanAnh;
    }
}