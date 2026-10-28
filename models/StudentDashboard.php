<?php

// Chuẩn bị trạng thái phòng, hợp đồng, hóa đơn và phản ánh cho dashboard sinh viên.
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
        $stmt = $this->pdo->prepare("SELECT h.MaPhong, p.SoPhong, h.NgayBatDau, h.NgayKetThuc, h.TrangThai FROM hopdong h JOIN phong p ON p.MaPhong = h.MaPhong WHERE MSSV = :student
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
            if (in_array($status, ['Còn hạn', 'Chưa bắt đầu'], true)) {
                $data['room'] = $contract['SoPhong'];
                if ($status === 'Chưa bắt đầu') {
                    $data['contract'] = 'Bắt đầu ' . (new DateTimeImmutable($contract['NgayBatDau']))->format('d/m/Y');
                }
            }
        }
        $stmt = $this->pdo->prepare("SELECT (SELECT COUNT(*) FROM hoadon h WHERE h.TrangThai = 'Chưa thu' AND (
            EXISTS (SELECT 1 FROM hoadontienphong p WHERE p.MaHoaDon = h.MaHoaDon AND p.MSSV = :room_student)
            OR EXISTS (SELECT 1 FROM hoadondiennuocsinhvien d WHERE d.MaHoaDon = h.MaHoaDon AND d.MSSV = :utility_student))) AS invoices,
            (SELECT COUNT(*) FROM phananhsuco WHERE MSSV = :report_student AND TrangThai = 'Chờ xử lý') AS reports");
        $stmt->execute(['room_student' => $student['MSSV'], 'utility_student' => $student['MSSV'], 'report_student' => $student['MSSV']]);
        $counts = $stmt->fetch();
        $data['invoices'] = (int) $counts['invoices'];
        $data['reports'] = (int) $counts['reports'];
        return $data;
    }
}
