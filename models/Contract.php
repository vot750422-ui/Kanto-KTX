<?php

class Contract
{
    public function __construct(private PDO $db) {}

    public function listing(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];
        foreach ([
            ['h.MaHopDong', $filters['contract'] ?? ''],
            ['h.MSSV', $filters['student'] ?? ''],
            ['s.HoTen', $filters['name'] ?? ''],
        ] as [$column, $value]) {
            if ($value === '') {
                continue;
            }
            $where[] = $column . ' LIKE ?';
            $params[] = '%' . $value . '%';
        }
        if (($filters['room'] ?? '') !== '') {
            $where[] = '(p.SoPhong LIKE ? OR h.MaPhong LIKE ?)';
            $params[] = '%' . $filters['room'] . '%';
            $params[] = '%' . $filters['room'] . '%';
        }
        if (($filters['status'] ?? '') !== '') {
            $where[] = 'h.TrangThai = ?';
            $params[] = $filters['status'];
        }

        $baseFrom = ' FROM hopdong h
            JOIN sinhvien s ON s.MSSV = h.MSSV
            JOIN phong p ON p.MaPhong = h.MaPhong';
        $condition = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare('SELECT COUNT(*)' . $baseFrom . $condition);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare('SELECT h.MaHopDong, h.MSSV, s.HoTen, h.MaPhong,
                p.SoPhong, t.TenToa, h.NgayBatDau, h.NgayKetThuc, h.TrangThai'
            . $baseFrom . ' LEFT JOIN toanha t ON t.MaToa = p.MaToa' . $condition
            . ' ORDER BY h.NgayBatDau DESC, h.MaHopDong DESC LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function find(string $contractId): ?array
    {
        $stmt = $this->db->prepare('SELECT h.MaHopDong, h.MSSV, s.HoTen, s.NgaySinh,
                s.GioiTinh, s.SDT, s.Email, s.Lop, s.NienKhoa, s.TrangThaiLuuTru,
                h.MaPhong, p.SoPhong, p.Tang, p.GioiTinhPhong, t.TenToa,
                h.NgayBatDau, h.NgayKetThuc, h.TrangThai,
                tl.NgayThanhLy, tl.TienHoanTra
            FROM hopdong h
            JOIN sinhvien s ON s.MSSV = h.MSSV
            JOIN phong p ON p.MaPhong = h.MaPhong
            LEFT JOIN toanha t ON t.MaToa = p.MaToa
            LEFT JOIN thanhlyhopdong tl ON tl.MaHopDong = h.MaHopDong
            WHERE h.MaHopDong = ? LIMIT 1');
        $stmt->execute([$contractId]);
        return $stmt->fetch() ?: null;
    }

    public function terminate(string $contractId, ?string $today = null): array
    {
        $today = $today ?? date('Y-m-d');
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('SELECT MaHopDong, MSSV, MaPhong, NgayBatDau, NgayKetThuc, TrangThai
                FROM hopdong WHERE MaHopDong = ? FOR UPDATE');
            $stmt->execute([$contractId]);
            $contract = $stmt->fetch();

            if (!$contract) {
                throw new DomainException('Hợp đồng không tồn tại.');
            }
            if ($contract['TrangThai'] !== 'Còn hạn') {
                throw new DomainException('Chỉ có thể thanh lý hợp đồng đang còn hạn.');
            }

            $unpaid = $this->unpaidInvoices($contract['MSSV']);
            if ($unpaid) {
                $items = array_map(static function (array $invoice): string {
                    return $invoice['MaHoaDon'] . ' (' . $invoice['LoaiHoaDon'] . ', '
                        . number_format((float) $invoice['SoTienPhaiThu'], 0, ',', '.') . ' đồng)';
                }, $unpaid);
                throw new DomainException('Không thể thanh lý. Sinh viên còn hóa đơn chưa thu: ' . implode(', ', $items) . '.');
            }

            $refund = $this->roomRefund($contract, $today);
            $settlementId = $this->uniqueId('thanhlyhopdong', 'MaThanhLy', 12);
            $stmt = $this->db->prepare('INSERT INTO thanhlyhopdong (MaThanhLy, MaHopDong, NgayThanhLy, TienHoanTra)
                VALUES (?, ?, ?, ?)');
            $stmt->execute([$settlementId, $contractId, $today, number_format($refund, 2, '.', '')]);

            // Giữ NgayKetThuc là ngày kết thúc theo hợp đồng gốc để bảo toàn lịch sử.
            $updateStmt = $this->db->prepare("UPDATE hopdong SET TrangThai = 'Đã thanh lý' WHERE MaHopDong = ?");
            $updateStmt->execute([$contractId]);

            $checkOtherStmt = $this->db->prepare('SELECT COUNT(*) FROM hopdong WHERE MSSV = ? AND TrangThai = ?');
            $checkOtherStmt->execute([$contract['MSSV'], 'Còn hạn']);
            $remainingActive = (int) $checkOtherStmt->fetchColumn();
            if ($remainingActive === 0) {
                $updateStudentStmt = $this->db->prepare('UPDATE sinhvien SET TrangThaiLuuTru = ? WHERE MSSV = ?');
                $updateStudentStmt->execute(['Đã rời đi', $contract['MSSV']]);
            }

            $this->db->commit();
            return [
                'MaHopDong' => $contractId,
                'MSSV' => $contract['MSSV'],
                'NgayKetThuc' => $contract['NgayKetThuc'],
                'NgayThanhLy' => $today,
                'TienHoanTra' => number_format($refund, 2, '.', ''),
                'TrangThai' => 'Đã thanh lý',
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function unpaidInvoices(string $student): array
    {
        $room = $this->db->prepare("SELECT h.MaHoaDon, h.LoaiHoaDon, h.TongTien AS SoTienPhaiThu
            FROM hoadon h
            JOIN hoadontienphong p ON p.MaHoaDon = h.MaHoaDon
            WHERE p.MSSV = ? AND h.TrangThai = 'Chưa thu'
            FOR UPDATE");
        $room->execute([$student]);

        $utility = $this->db->prepare("SELECT h.MaHoaDon, h.LoaiHoaDon, us.SoTienPhaiThu
            FROM hoadon h
            JOIN hoadondiennuocsinhvien us ON us.MaHoaDon = h.MaHoaDon
            WHERE us.MSSV = ? AND us.TrangThai = 'Chưa thu'
            FOR UPDATE");
        $utility->execute([$student]);

        return array_merge($room->fetchAll(), $utility->fetchAll());
    }

    private function roomRefund(array $contract, string $today): float
    {
        $stmt = $this->db->prepare("SELECT h.TongTien, k.NgayBatDau AS KyBatDau, k.NgayKetThuc AS KyKetThuc
            FROM hoadon h
            JOIN hoadontienphong p ON p.MaHoaDon = h.MaHoaDon
            JOIN hocky k ON k.MaHocKy = p.MaHocKy
            WHERE p.MSSV = ? AND p.MaPhong = ? AND h.TrangThai = 'Đã thu'
              AND k.NgayBatDau <= ? AND k.NgayKetThuc >= ?
            FOR UPDATE");
        $stmt->execute([$contract['MSSV'], $contract['MaPhong'], $contract['NgayKetThuc'], $contract['NgayBatDau']]);

        $refund = 0.0;
        foreach ($stmt->fetchAll() as $invoice) {
            $periodStart = max($contract['NgayBatDau'], $invoice['KyBatDau']);
            $periodEnd = min($contract['NgayKetThuc'], $invoice['KyKetThuc']);
            $periodDays = $this->daysInclusive($periodStart, $periodEnd);
            if ($periodDays <= 0) {
                continue;
            }

            if ($today < $periodStart) {
                $unusedDays = $periodDays;
            } elseif ($today >= $periodEnd) {
                $unusedDays = 0;
            } else {
                $unusedDays = $this->daysInclusive($this->addDays($today, 1), $periodEnd);
            }
            $refund += (float) $invoice['TongTien'] * $unusedDays / $periodDays;
        }

        return round(max(0, $refund), 2);
    }

    private function daysInclusive(string $start, string $end): int
    {
        $from = new DateTimeImmutable($start);
        $to = new DateTimeImmutable($end);
        return $from > $to ? 0 : (int) $from->diff($to)->days + 1;
    }

    private function addDays(string $date, int $days): string
    {
        return (new DateTimeImmutable($date))->modify(($days >= 0 ? '+' : '') . $days . ' days')->format('Y-m-d');
    }

    private function uniqueId(string $table, string $column, int $length): string
    {
        do {
            $id = strtoupper(substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length));
            $stmt = $this->db->prepare("SELECT 1 FROM {$table} WHERE {$column} = ?");
            $stmt->execute([$id]);
        } while ($stmt->fetchColumn());
        return $id;
    }
}
