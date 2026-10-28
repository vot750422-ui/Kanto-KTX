<?php

// Tổng hợp dữ liệu lưu trú, hóa đơn và phản ánh theo loại báo cáo được chọn.
class StatisticsReport
{
    public const TYPES = ['accommodation', 'invoices', 'incidents'];

    public function __construct(private PDO $db) {}

    public function generate(string $type, ?string $startDate, ?string $endDate): array
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Loại thống kê không hợp lệ.');
        }
        [$where, $params] = $this->dateCondition($this->dateColumn($type), $startDate, $endDate);
        return match ($type) {
            'accommodation' => $this->accommodation($where, $params),
            'invoices' => $this->invoices($where, $params),
            'incidents' => $this->incidents($where, $params),
        };
    }

    private function dateColumn(string $type): string
    {
        return match ($type) {
            'accommodation' => 'h.NgayBatDau',
            'invoices' => 'hd.ThoiGianLap',
            'incidents' => 'pa.ThoiGianGui',
        };
    }

    private function dateCondition(string $column, ?string $startDate, ?string $endDate): array
    {
        $parts = [];
        $params = [];
        if ($startDate !== null) { $parts[] = "$column >= :start_date"; $params['start_date'] = $startDate . ' 00:00:00'; }
        if ($endDate !== null) { $parts[] = "$column < DATE_ADD(:end_date, INTERVAL 1 DAY)"; $params['end_date'] = $endDate . ' 00:00:00'; }
        return [$parts ? ' WHERE ' . implode(' AND ', $parts) : '', $params];
    }

    private function accommodation(string $where, array $params): array
    {
        $from = ' FROM hopdong h JOIN sinhvien s ON s.MSSV=h.MSSV JOIN phong p ON p.MaPhong=h.MaPhong JOIN toanha t ON t.MaToa=p.MaToa' . $where;
        $summary = $this->fetchOne("SELECT COUNT(*) AS total,COUNT(DISTINCT h.MSSV) AS students,COUNT(DISTINCT h.MaPhong) AS rooms,SUM(h.TrangThai='Còn hạn') AS active" . $from, $params);
        $items = $this->fetchAll('SELECT h.MaHopDong,h.MSSV,s.HoTen,t.TenToa,p.SoPhong,h.NgayBatDau,h.NgayKetThuc,h.TrangThai' . $from . ' ORDER BY h.NgayBatDau DESC,h.MaHopDong DESC', $params);
        return ['summary' => $summary, 'items' => $items];
    }

    private function invoices(string $where, array $params): array
    {
        $from = ' FROM hoadon hd' . $where;
        $summary = $this->fetchOne("SELECT COUNT(*) AS total,SUM(hd.TrangThai='Đã thu') AS paid,SUM(hd.TrangThai='Chưa thu') AS unpaid,COALESCE(SUM(hd.TongTien),0) AS amount" . $from, $params);
        $items = $this->fetchAll('SELECT hd.MaHoaDon,hd.LoaiHoaDon,hd.TongTien,hd.ThoiGianLap,hd.TrangThai,hd.ThoiGianThanhToan' . $from . ' ORDER BY hd.ThoiGianLap DESC,hd.MaHoaDon DESC', $params);
        return ['summary' => $summary, 'items' => $items];
    }

    private function incidents(string $where, array $params): array
    {
        $from = ' FROM phananhsuco pa JOIN sinhvien s ON s.MSSV=pa.MSSV JOIN phong p ON p.MaPhong=pa.MaPhong' . $where;
        $summary = $this->fetchOne("SELECT COUNT(*) AS total,SUM(pa.TrangThai='Chờ xử lý') AS pending,SUM(pa.TrangThai='Đã xử lý') AS resolved,COALESCE(SUM(pa.ChiPhiSuaChua),0) AS cost" . $from, $params);
        $items = $this->fetchAll('SELECT pa.MaPhanAnh,pa.MSSV,s.HoTen,p.SoPhong,pa.LoaiSuCo,pa.ThoiGianGui,pa.ChiPhiSuaChua,pa.TrangThai' . $from . ' ORDER BY pa.ThoiGianGui DESC,pa.MaPhanAnh DESC', $params);
        return ['summary' => $summary, 'items' => $items];
    }

    private function fetchOne(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt->fetch() ?: [];
    }

    private function fetchAll(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }
}
