<?php

// Truy vấn hồ sơ và hợp đồng còn hạn theo tài khoản sinh viên đã xác thực.
class Accommodation
{
    public function __construct(private PDO $pdo) {}

    public function forAccount(int $accountId): array
    {
        $stmt = $this->pdo->prepare('SELECT MSSV, HoTen, NgaySinh, CCCD, GioiTinh, QueQuan, SDT, Email, Lop, NienKhoa, TrangThaiLuuTru FROM sinhvien WHERE MaTK = :account LIMIT 1');
        $stmt->execute(['account' => $accountId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            return ['student' => null, 'accommodation' => null];
        }
        $stmt = $this->pdo->prepare("SELECT h.MaHopDong, h.NgayBatDau, h.NgayKetThuc, h.TrangThai,
            p.SoPhong, p.Tang, p.GioiTinhPhong, p.SucChua, t.TenToa
            FROM hopdong h JOIN phong p ON p.MaPhong = h.MaPhong
            JOIN toanha t ON t.MaToa = p.MaToa
            WHERE h.MSSV = :student AND h.TrangThai = 'Còn hạn'
            ORDER BY h.NgayBatDau DESC, h.MaHopDong DESC LIMIT 1");
        $stmt->execute(['student' => $student['MSSV']]);
        return ['student' => $student, 'accommodation' => $stmt->fetch(PDO::FETCH_ASSOC) ?: null];
    }
}
