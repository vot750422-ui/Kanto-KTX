<?php

class UtilityInvoiceGenerator
{
    public function __construct(private PDO $db) {}

    /**
     * Xác định tháng/năm cần tính hóa đơn và tháng liền trước.
     * Mặc định xử lý tháng vừa kết thúc.
     */
    public function resolvePeriod(?int $month = null, ?int $year = null, ?DateTimeImmutable $now = null): array
    {
        if ($month === null || $year === null) {
            $now = $now ?? new DateTimeImmutable();
            $firstOfThisMonth = new DateTimeImmutable($now->format('Y-m-01'));
            $prev = $firstOfThisMonth->modify('-1 month');
            $month = (int) $prev->format('n');
            $year = (int) $prev->format('Y');
        }

        if ($month < 1 || $month > 12) {
            throw new DomainException('Tháng không hợp lệ. Tháng phải từ 1 đến 12.');
        }

        if ($year < 2000 || $year > 2100) {
            throw new DomainException('Năm không hợp lệ.');
        }

        // Tính tháng liền trước (xử lý chuyển năm từ tháng 1 sang tháng 12 năm trước)
        if ($month === 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
        }

        $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
        $startOfMonth = sprintf('%04d-%02d-01', $year, $month);
        $endOfMonth = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        return [
            'month' => $month,
            'year' => $year,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
        ];
    }

    /**
     * Lấy đơn giá Điện và Nước mới nhất có NgayApDung <= ngày cuối tháng cần tính.
     */
    public function getRates(string $endOfMonth): array
    {
        $stmtDien = $this->db->prepare("SELECT MaDonGia, GiaTri, NgayApDung FROM dongia WHERE LoaiDonGia = 'Điện' AND NgayApDung <= ? ORDER BY NgayApDung DESC LIMIT 1");
        $stmtDien->execute([$endOfMonth]);
        $rateDien = $stmtDien->fetch();

        $stmtNuoc = $this->db->prepare("SELECT MaDonGia, GiaTri, NgayApDung FROM dongia WHERE LoaiDonGia = 'Nước' AND NgayApDung <= ? ORDER BY NgayApDung DESC LIMIT 1");
        $stmtNuoc->execute([$endOfMonth]);
        $rateNuoc = $stmtNuoc->fetch();

        if (!$rateDien || !$rateNuoc) {
            throw new DomainException('Thiếu đơn giá điện hoặc nước có hiệu lực tại ngày ' . $endOfMonth . '. Không thể tạo hóa đơn.');
        }

        return [
            'dien' => $rateDien,
            'nuoc' => $rateNuoc,
        ];
    }

    /**
     * Tìm các sinh viên có hợp đồng giao với khoảng thời gian của tháng cần tính.
     * Điều kiện: NgayBatDau <= endOfMonth AND NgayKetThuc >= startOfMonth.
     */
    public function findStudentsForRoom(string $roomId, string $startOfMonth, string $endOfMonth): array
    {
        $stmt = $this->db->prepare('SELECT DISTINCT s.MSSV, s.HoTen
            FROM hopdong h
            JOIN sinhvien s ON s.MSSV = h.MSSV
            WHERE h.MaPhong = ?
              AND h.NgayBatDau <= ?
              AND h.NgayKetThuc >= ?
            ORDER BY s.MSSV ASC');
        $stmt->execute([$roomId, $endOfMonth, $startOfMonth]);
        return $stmt->fetchAll();
    }

    /**
     * Tự động lập hóa đơn điện nước cho tất cả các phòng đủ điều kiện trong tháng.
     */
    public function generateForMonth(?int $month = null, ?int $year = null, ?DateTimeImmutable $now = null): array
    {
        $period = $this->resolvePeriod($month, $year, $now);
        $rates = $this->getRates($period['endOfMonth']);

        // Lấy danh sách tất cả các phòng
        $stmtRooms = $this->db->query('SELECT p.MaPhong, p.SoPhong, t.TenToa FROM phong p LEFT JOIN toanha t ON t.MaToa = p.MaToa ORDER BY p.MaPhong ASC');
        $rooms = $stmtRooms->fetchAll();

        $processed = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];

        foreach ($rooms as $room) {
            $roomId = $room['MaPhong'];

            // 1. Kiểm tra đã có hóa đơn điện nước cho cùng MaPhong, Thang, Nam chưa
            $stmtExists = $this->db->prepare('SELECT 1 FROM hoadondiennuoc WHERE MaPhong = ? AND Thang = ? AND Nam = ?');
            $stmtExists->execute([$roomId, $period['month'], $period['year']]);
            if ($stmtExists->fetchColumn()) {
                $skipped++;
                $details[] = [
                    'room' => $roomId,
                    'status' => 'skipped',
                    'reason' => 'Đã có hóa đơn điện nước cho tháng này.',
                ];
                continue;
            }

            // 2. Lấy chỉ số tháng cần tính
            $stmtNew = $this->db->prepare('SELECT ChiSoDien, ChiSoNuoc FROM chisodiennuoc WHERE MaPhong = ? AND Thang = ? AND Nam = ?');
            $stmtNew->execute([$roomId, $period['month'], $period['year']]);
            $newMeter = $stmtNew->fetch();
            if (!$newMeter) {
                $skipped++;
                $details[] = [
                    'room' => $roomId,
                    'status' => 'skipped',
                    'reason' => 'Thiếu bản ghi chỉ số tháng cần tính.',
                ];
                continue;
            }

            // 3. Lấy chỉ số tháng liền trước
            $stmtOld = $this->db->prepare('SELECT ChiSoDien, ChiSoNuoc FROM chisodiennuoc WHERE MaPhong = ? AND Thang = ? AND Nam = ?');
            $stmtOld->execute([$roomId, $period['prevMonth'], $period['prevYear']]);
            $oldMeter = $stmtOld->fetch();
            if (!$oldMeter) {
                $skipped++;
                $details[] = [
                    'room' => $roomId,
                    'status' => 'skipped',
                    'reason' => 'Thiếu bản ghi chỉ số tháng liền trước.',
                ];
                continue;
            }

            // 4. Kiểm tra chỉ số mới không nhỏ hơn chỉ số cũ
            $newElec = (int) $newMeter['ChiSoDien'];
            $oldElec = (int) $oldMeter['ChiSoDien'];
            $newWater = (int) $newMeter['ChiSoNuoc'];
            $oldWater = (int) $oldMeter['ChiSoNuoc'];

            if ($newElec < $oldElec || $newWater < $oldWater) {
                $skipped++;
                $details[] = [
                    'room' => $roomId,
                    'status' => 'skipped',
                    'reason' => 'Chỉ số mới nhỏ hơn chỉ số cũ.',
                ];
                continue;
            }

            // 5. Kiểm tra sinh viên có hợp đồng giao với khoảng thời gian tháng cần tính
            $students = $this->findStudentsForRoom($roomId, $period['startOfMonth'], $period['endOfMonth']);
            if (empty($students)) {
                $skipped++;
                $details[] = [
                    'room' => $roomId,
                    'status' => 'skipped',
                    'reason' => 'Không có sinh viên có hợp đồng trong tháng.',
                ];
                continue;
            }

            // 6. Tính toán lượng tiêu thụ và số tiền (làm tròn đến đồng nguyên)
            $dienTieuThu = $newElec - $oldElec;
            $nuocTieuThu = $newWater - $oldWater;
            $tienDien = round($dienTieuThu * (float) $rates['dien']['GiaTri']);
            $tienNuoc = round($nuocTieuThu * (float) $rates['nuoc']['GiaTri']);
            $tongTien = $tienDien + $tienNuoc;

            // 7. Tạo dữ liệu trong transaction cho từng phòng
            $this->db->beginTransaction();

            try {
                // Kiểm tra lại chống tạo trùng nếu có tiến trình khác chạy song song
                $stmtCheckDuplicate = $this->db->prepare('SELECT 1 FROM hoadondiennuoc WHERE MaPhong = ? AND Thang = ? AND Nam = ? FOR UPDATE');
                $stmtCheckDuplicate->execute([$roomId, $period['month'], $period['year']]);
                if ($stmtCheckDuplicate->fetchColumn()) {
                    $this->db->rollBack();
                    $skipped++;
                    $details[] = [
                        'room' => $roomId,
                        'status' => 'skipped',
                        'reason' => 'Đã có hóa đơn điện nước (trùng lặp song song).',
                    ];
                    continue;
                }

                // Sinh mã hóa đơn 12 ký tự duy nhất
                do {
                    $invoiceId = strtoupper(bin2hex(random_bytes(6)));
                    $stmtCheckId = $this->db->prepare('SELECT 1 FROM hoadon WHERE MaHoaDon = ?');
                    $stmtCheckId->execute([$invoiceId]);
                } while ($stmtCheckId->fetchColumn());

                // a. Tạo bản ghi hoadon
                $stmtHoadon = $this->db->prepare("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai) VALUES (?, 'Điện nước', ?, 'Chưa thu')");
                $stmtHoadon->execute([$invoiceId, $tongTien]);

                // b. Tạo bản ghi hoadondiennuoc
                $stmtHddn = $this->db->prepare('INSERT INTO hoadondiennuoc (MaHoaDon, MaPhong, Thang, Nam, DienTieuThu, NuocTieuThu, TienDien, TienNuoc, MaDonGiaDien, MaDonGiaNuoc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmtHddn->execute([
                    $invoiceId,
                    $roomId,
                    $period['month'],
                    $period['year'],
                    $dienTieuThu,
                    $nuocTieuThu,
                    $tienDien,
                    $tienNuoc,
                    $rates['dien']['MaDonGia'],
                    $rates['nuoc']['MaDonGia'],
                ]);

                // c. Chia tổng tiền phòng cho từng sinh viên theo snapshot của tháng.
                $allocations = $this->splitAmount($tongTien, $students);
                $allocationByStudent = [];
                $stmtHddnSv = $this->db->prepare('INSERT INTO hoadondiennuocsinhvien
                    (MaHoaDon, MSSV, SoTienPhaiThu, TrangThai) VALUES (?, ?, ?, \'Chưa thu\')');
                foreach ($students as $index => $sv) {
                    $allocationByStudent[$sv['MSSV']] = $allocations[$index];
                    $stmtHddnSv->execute([$invoiceId, $sv['MSSV'], $allocations[$index]]);
                }

                // d. Tạo thông báo cho từng sinh viên liên quan
                $stmtTb = $this->db->prepare('INSERT INTO thongbao (MaThongBao, MSSV, MaHoaDon, TieuDe, NoiDung) VALUES (?, ?, ?, ?, ?)');
                $tieuDe = sprintf('Hóa đơn điện nước tháng %02d/%d phòng %s', $period['month'], $period['year'], $room['SoPhong']);
                foreach ($students as $sv) {
                    do {
                        $tbId = strtoupper(bin2hex(random_bytes(6)));
                        $stmtCheckTb = $this->db->prepare('SELECT 1 FROM thongbao WHERE MaThongBao = ?');
                        $stmtCheckTb->execute([$tbId]);
                    } while ($stmtCheckTb->fetchColumn());

                    $noiDung = sprintf(
                        'Hóa đơn điện nước tháng %02d/%d của phòng %s đã được lập. Tổng tiền phòng là %s đ; phần của bạn là %s đ (Điện tiêu thụ: %d kWh = %s đ, Nước tiêu thụ: %d m³ = %s đ). Vui lòng thanh toán theo quy định.',
                        $period['month'],
                        $period['year'],
                        $room['SoPhong'],
                        number_format($tongTien, 0, ',', '.'),
                        number_format((float) $allocationByStudent[$sv['MSSV']], 0, ',', '.'),
                        $dienTieuThu,
                        number_format($tienDien, 0, ',', '.'),
                        $nuocTieuThu,
                        number_format($tienNuoc, 0, ',', '.')
                    );
                    $stmtTb->execute([$tbId, $sv['MSSV'], $invoiceId, $tieuDe, $noiDung]);
                }

                $this->db->commit();
                $processed++;
                $details[] = [
                    'room' => $roomId,
                    'status' => 'created',
                    'invoiceId' => $invoiceId,
                    'total' => $tongTien,
                    'studentsCount' => count($students),
                ];
            } catch (Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $failed++;
                $details[] = [
                    'room' => $roomId,
                    'status' => 'failed',
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'period' => $period,
            'rates' => $rates,
            'processed' => $processed,
            'skipped' => $skipped,
            'failed' => $failed,
            'details' => $details,
        ];
    }

    /** Chia đến đồng, phần dư được phân bổ ổn định theo thứ tự MSSV đã truy vấn. */
    private function splitAmount(float|int|string $total, array $students): array
    {
        $count = count($students);
        if ($count === 0) {
            return [];
        }

        $totalCents = (int) round((float) $total * 100);
        $baseCents = intdiv($totalCents, $count);
        $remainder = $totalCents % $count;
        $amounts = [];

        foreach ($students as $index => $_student) {
            $cents = $baseCents + ($index < $remainder ? 1 : 0);
            $amounts[] = number_format($cents / 100, 2, '.', '');
        }

        return $amounts;
    }
}
