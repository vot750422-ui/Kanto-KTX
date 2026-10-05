<?php

class StudentDashboard
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function forAccount(int $accountId): array
    {
        $stmt = $this->pdo->prepare('SELECT MSSV, HoTen, TrangThaiLuuTru FROM sinhvien WHERE MaTK = :id LIMIT 1');
        $stmt->execute(['id' => $accountId]);
        $student = $stmt->fetch();
        $data = ['student' => $student ?: null, 'room' => 'Chưa có phòng', 'contract' => 'Chưa có hợp đồng', 'invoices' => 0, 'reports' => 0];
        if (!$student) {
            return $data;
        }
        $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT MaPhong, NgayBatDau, NgayKetThuc, TrangThai FROM hopdong WHERE MSSV = :student
            ORDER BY (TrangThai = 'Còn hạn' AND NgayBatDau <= :start AND NgayKetThuc >= :end) DESC, NgayBatDau DESC, MaHopDong DESC LIMIT 1");
        $stmt->execute(['student' => $student['MSSV'], 'start' => $today, 'end' => $today]);
        $contract = $stmt->fetch();
        if ($contract) {
            $status = $contract['TrangThai'];
            if ($status === 'Còn hạn') {
                $status = $contract['NgayKetThuc'] < $today ? 'Hết hạn'
                    : ($contract['NgayBatDau'] > $today ? 'Chưa bắt đầu' : 'Còn hạn');
            }
            $data['contract'] = $status;
            if ($status === 'Còn hạn' && $student['TrangThaiLuuTru'] === 'Đang ở') {
                $data['room'] = $contract['MaPhong'];
            }
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM hoadon h WHERE h.TrangThai = 'Chưa thu' AND (
            EXISTS (SELECT 1 FROM hoadontienphong p WHERE p.MaHoaDon = h.MaHoaDon AND p.MSSV = :room_student)
            OR EXISTS (SELECT 1 FROM hoadondiennuocsinhvien d WHERE d.MaHoaDon = h.MaHoaDon AND d.MSSV = :utility_student))");
        $stmt->execute(['room_student' => $student['MSSV'], 'utility_student' => $student['MSSV']]);
        $data['invoices'] = (int) $stmt->fetchColumn();
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM phananhsuco WHERE MSSV = :student AND TrangThai = 'Chờ xử lý'");
        $stmt->execute(['student' => $student['MSSV']]);
        $data['reports'] = (int) $stmt->fetchColumn();
        return $data;
    }
}
