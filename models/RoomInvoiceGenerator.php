<?php

require_once __DIR__ . '/Approval.php';

class RoomInvoiceGenerator
{
    private const TIMEZONE = 'Asia/Ho_Chi_Minh';

    public function __construct(private PDO $db) {}

    /**
     * Lập hóa đơn tiền phòng kỳ kế tiếp khi đến mốc 10 ngày trước khi hết kỳ.
     */
    public function generate(?DateTimeImmutable $now = null): array
    {
        $timezone = new DateTimeZone(self::TIMEZONE);
        $now = $now?->setTimezone($timezone) ?? new DateTimeImmutable('now', $timezone);
        $today = $now->format('Y-m-d');
        $current = $this->currentSemester($today);
        $triggerDate = (new DateTimeImmutable($current['NgayKetThuc'], $timezone))
            ->modify('-10 days')->format('Y-m-d');

        if ($today < $triggerDate) {
            throw new DomainException('Chưa đến thời điểm lập hóa đơn tiền phòng kỳ kế tiếp.');
        }

        $next = $this->nextSemester($current['NgayKetThuc']);
        $rate = $this->roomRate($today);
        $total = Approval::roomTotal($rate['GiaTri'], $next['NgayBatDau'], $next['NgayKetThuc']);
        $students = $this->eligibleStudents($current['NgayBatDau'], $current['NgayKetThuc']);

        $result = [
            'currentSemester' => $current['MaHocKy'],
            'nextSemester' => $next['MaHocKy'],
            'rate' => $rate,
            'total' => $total,
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($students as $student) {
            try {
                $created = $this->createForStudent($student, $next, $rate, $total);
                if ($created === null) {
                    $result['skipped']++;
                    $result['details'][] = ['student' => $student['MSSV'], 'status' => 'skipped', 'reason' => 'Hóa đơn đã tồn tại.'];
                    continue;
                }

                $result['processed']++;
                $result['details'][] = ['student' => $student['MSSV'], 'status' => 'created', 'invoice' => $created];
            } catch (Throwable $error) {
                $result['failed']++;
                $result['details'][] = ['student' => $student['MSSV'], 'status' => 'failed', 'reason' => $error->getMessage()];
            }
        }

        return $result;
    }

    /** Lấy đúng một học kỳ đang diễn ra để làm nguồn sinh hóa đơn. */
    private function currentSemester(string $today): array
    {
        $stmt = $this->db->prepare('SELECT * FROM hocky WHERE NgayBatDau <= ? AND NgayKetThuc >= ? ORDER BY NgayBatDau');
        $stmt->execute([$today, $today]);
        $semesters = $stmt->fetchAll();
        if (count($semesters) !== 1) {
            throw new DomainException('Cần có đúng một học kỳ hiện tại để lập hóa đơn tiền phòng.');
        }
        return $semesters[0];
    }

    /** Lấy học kỳ liền sau và chặn dữ liệu học kỳ bị đảo hoặc chồng lấn. */
    private function nextSemester(string $currentEnd): array
    {
        $stmt = $this->db->prepare('SELECT * FROM hocky WHERE NgayBatDau > ? ORDER BY NgayBatDau, MaHocKy LIMIT 2');
        $stmt->execute([$currentEnd]);
        $semesters = $stmt->fetchAll();
        $next = $semesters[0] ?? null;
        if (!$next || $next['NgayBatDau'] > $next['NgayKetThuc']) {
            throw new DomainException('Chưa có học kỳ kế tiếp hợp lệ để lập hóa đơn tiền phòng.');
        }
        if (isset($semesters[1]) && $semesters[1]['NgayBatDau'] <= $next['NgayKetThuc']) {
            throw new DomainException('Các học kỳ kế tiếp bị chồng lấn.');
        }
        return $next;
    }

    /** Lấy đơn giá tiền phòng đang có hiệu lực tại ngày Timer chạy. */
    private function roomRate(string $today): array
    {
        $stmt = $this->db->prepare("SELECT MaDonGia, GiaTri, DonViTinh FROM dongia
            WHERE LoaiDonGia = 'Tiền phòng' AND NgayApDung <= ?
            ORDER BY NgayApDung DESC, MaDonGia LIMIT 1");
        $stmt->execute([$today]);
        $rate = $stmt->fetch();
        if (!$rate || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', (string) $rate['GiaTri'])) {
            throw new DomainException('Chưa có đơn giá tiền phòng hợp lệ đang có hiệu lực.');
        }
        if (mb_strtolower(trim($rate['DonViTinh'])) !== 'đồng/tháng') {
            throw new DomainException('Đơn giá tiền phòng phải có đơn vị đồng/tháng.');
        }
        return $rate;
    }

    /** Chọn sinh viên có hợp đồng còn hạn giao với học kỳ hiện tại. */
    private function eligibleStudents(string $semesterStart, string $semesterEnd): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT s.MSSV, s.HoTen, h.MaPhong, p.SoPhong
            FROM hopdong h
            JOIN sinhvien s ON s.MSSV = h.MSSV
            JOIN phong p ON p.MaPhong = h.MaPhong
            WHERE h.TrangThai = 'Còn hạn'
              AND h.NgayBatDau <= ?
              AND h.NgayKetThuc >= ?
            ORDER BY s.MSSV, h.MaPhong");
        $stmt->execute([$semesterEnd, $semesterStart]);
        return $stmt->fetchAll();
    }

    /** Tạo hóa đơn, chi tiết và thông báo trong cùng transaction cho một sinh viên. */
    private function createForStudent(array $student, array $semester, array $rate, string $total): ?string
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT MaHoaDon FROM hoadontienphong WHERE MSSV = ? AND MaHocKy = ? FOR UPDATE');
            $stmt->execute([$student['MSSV'], $semester['MaHocKy']]);
            if ($stmt->fetchColumn() !== false) {
                $this->db->commit();
                return null;
            }

            $invoice = $this->uniqueId('hoadon', 'MaHoaDon', 12);
            $stmt = $this->db->prepare("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai)
                VALUES (?, 'Tiền phòng', ?, 'Chưa thu')");
            $stmt->execute([$invoice, $total]);

            $stmt = $this->db->prepare('INSERT INTO hoadontienphong (MaHoaDon, MSSV, MaPhong, MaHocKy, MaDonGia) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$invoice, $student['MSSV'], $student['MaPhong'], $semester['MaHocKy'], $rate['MaDonGia']]);

            $notification = $this->uniqueId('thongbao', 'MaThongBao', 12);
            $title = 'Hóa đơn tiền phòng học kỳ ' . $semester['TenHocKy'];
            $content = 'Hóa đơn tiền phòng học kỳ ' . $semester['TenHocKy'] . ' năm học ' . $semester['NamHoc']
                . ' của phòng ' . $student['SoPhong'] . ' đã được lập với số tiền '
                . number_format((float) $total, 0, ',', '.') . ' đồng. Vui lòng thanh toán theo quy định.';
            $stmt = $this->db->prepare('INSERT INTO thongbao (MaThongBao, MSSV, MaHoaDon, TieuDe, NoiDung) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$notification, $student['MSSV'], $invoice, $title, $content]);

            $this->db->commit();
            return $invoice;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    /** Tạo mã ngẫu nhiên và kiểm tra không trùng trước khi ghi dữ liệu. */
    private function uniqueId(string $table, string $column, int $length): string
    {
        do {
            $id = strtoupper(bin2hex(random_bytes((int) ceil($length / 2))));
            $id = substr($id, 0, $length);
            $stmt = $this->db->prepare("SELECT 1 FROM {$table} WHERE {$column} = ?");
            $stmt->execute([$id]);
        } while ($stmt->fetchColumn());
        return $id;
    }
}
