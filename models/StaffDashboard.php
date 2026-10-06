<?php

class StaffDashboard
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function summary(): array
    {
        $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT
            (SELECT COUNT(*) FROM dondangky WHERE TrangThai = 'Chờ duyệt') AS applications,
            (SELECT COUNT(*) FROM sinhvien WHERE TrangThaiLuuTru = 'Đang ở') AS students,
            (SELECT COUNT(DISTINCT h.MaPhong) FROM hopdong h
                JOIN sinhvien s ON s.MSSV = h.MSSV
                WHERE h.TrangThai = 'Còn hạn' AND s.TrangThaiLuuTru = 'Đang ở'
                AND h.NgayBatDau <= :start AND h.NgayKetThuc >= :end) AS rooms,
            (SELECT COUNT(*) FROM phananhsuco WHERE TrangThai = 'Chờ xử lý') AS reports");
        $stmt->execute(['start' => $today, 'end' => $today]);
        return array_map('intval', $stmt->fetch());
    }

    public function nameForAccount(int $accountId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT HoTen FROM nhanvien WHERE MaTK = :id LIMIT 1');
        $stmt->execute(['id' => $accountId]);
        $name = $stmt->fetchColumn();
        return $name === false ? null : $name;
    }
}
