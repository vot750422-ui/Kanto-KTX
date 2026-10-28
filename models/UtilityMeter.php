<?php

class UtilityMeter
{
    public function __construct(private PDO $db) {}

    public function occupiedRooms(): array
    {
        // Chỉ liệt kê phòng có hợp đồng còn hạn và kèm chỉ số mới nhất để nhân viên chọn nhanh.
        $sql = "SELECT p.MaPhong, p.SoPhong, t.TenToa, COUNT(DISTINCT h.MSSV) AS SoSinhVien,
                       last_meter.ChiSoDien, last_meter.ChiSoNuoc,
                       last_meter.Thang, last_meter.Nam
                FROM phong p
                JOIN toanha t ON t.MaToa = p.MaToa
                JOIN hopdong h ON h.MaPhong = p.MaPhong AND h.TrangThai = 'Còn hạn'
                LEFT JOIN (
                    SELECT ranked.MaPhong, ranked.ChiSoDien, ranked.ChiSoNuoc,
                           ranked.Thang, ranked.Nam
                    FROM (
                        SELECT c.*,
                               ROW_NUMBER() OVER (
                                   PARTITION BY c.MaPhong
                                   ORDER BY c.Nam DESC, c.Thang DESC,
                                            c.ThoiGianGhi DESC, c.MaChiSo DESC
                               ) AS rn
                        FROM chisodiennuoc c
                    ) ranked
                    WHERE ranked.rn = 1
                ) last_meter ON last_meter.MaPhong = p.MaPhong
                GROUP BY p.MaPhong, p.SoPhong, t.TenToa,
                         last_meter.ChiSoDien, last_meter.ChiSoNuoc,
                         last_meter.Thang, last_meter.Nam
                ORDER BY t.TenToa, p.SoPhong";

        return $this->db->query($sql)->fetchAll();
    }

    public function latest(string $roomId): ?array
    {
        // Lấy chỉ số gần nhất để kiểm tra số mới không thấp hơn số cũ.
        $stmt = $this->db->prepare(
            'SELECT MaChiSo, ChiSoDien, ChiSoNuoc, Thang, Nam, ThoiGianGhi
             FROM chisodiennuoc
             WHERE MaPhong = ?
             ORDER BY Nam DESC, Thang DESC, ThoiGianGhi DESC, MaChiSo DESC
             LIMIT 1'
        );
        $stmt->execute([$roomId]);

        return $stmt->fetch() ?: null;
    }

    public function record(
        string $roomId,
        int $accountId,
        int $electricity,
        int $water,
        ?DateTimeImmutable $now = null
    ): string {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $month = (int) $now->format('n');
        $year = (int) $now->format('Y');
        $this->db->beginTransaction();

        try {
            // Khóa phòng và xác nhận phòng còn người ở trước khi ghi chỉ số.
            $stmt = $this->db->prepare(
                "SELECT p.MaPhong
                 FROM phong p
                 WHERE p.MaPhong = ?
                   AND EXISTS (
                       SELECT 1 FROM hopdong h
                       WHERE h.MaPhong = p.MaPhong AND h.TrangThai = 'Còn hạn'
                   ) FOR UPDATE"
            );
            $stmt->execute([$roomId]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException('Phòng không có sinh viên đang lưu trú.');
            }

            // Chỉ nhân viên đang làm mới được ghi nhận chỉ số.
            $stmt = $this->db->prepare(
                "SELECT MaNV FROM nhanvien
                 WHERE MaTK = ? AND TrangThaiLamViec = 'Đang làm' FOR UPDATE"
            );
            $stmt->execute([$accountId]);
            $staffId = $stmt->fetchColumn();
            if ($staffId === false) {
                throw new DomainException('Không tìm thấy hồ sơ nhân viên đang làm việc.');
            }

            // So sánh chỉ số mới với bản ghi gần nhất trong cùng transaction.
            $stmt = $this->db->prepare(
                'SELECT ChiSoDien, ChiSoNuoc
                 FROM chisodiennuoc
                 WHERE MaPhong = ?
                 ORDER BY Nam DESC, Thang DESC, ThoiGianGhi DESC, MaChiSo DESC
                 LIMIT 1 FOR UPDATE'
            );
            $stmt->execute([$roomId]);
            $old = $stmt->fetch();
            if ($old && ($electricity < (int) $old['ChiSoDien'] || $water < (int) $old['ChiSoNuoc'])) {
                throw new DomainException('Chỉ số mới không được nhỏ hơn chỉ số cũ!');
            }

            // Không cho ghi trùng một phòng trong cùng tháng và năm.
            $stmt = $this->db->prepare(
                'SELECT 1 FROM chisodiennuoc WHERE MaPhong = ? AND Thang = ? AND Nam = ?'
            );
            $stmt->execute([$roomId, $month, $year]);
            if ($stmt->fetchColumn()) {
                throw new DomainException('Phòng này đã được ghi nhận chỉ số điện nước trong tháng hiện tại.');
            }

            // Tạo mã chỉ số ngẫu nhiên rồi lưu dữ liệu cùng nhân viên thực hiện.
            do {
                $id = strtoupper(bin2hex(random_bytes(5)));
                $check = $this->db->prepare('SELECT 1 FROM chisodiennuoc WHERE MaChiSo = ?');
                $check->execute([$id]);
            } while ($check->fetchColumn());

            $stmt = $this->db->prepare(
                'INSERT INTO chisodiennuoc
                    (MaChiSo, MaPhong, MaNV, ChiSoDien, ChiSoNuoc, Thang, Nam, ThoiGianGhi)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $id,
                $roomId,
                $staffId,
                $electricity,
                $water,
                $month,
                $year,
                $now->format('Y-m-d H:i:s'),
            ]);
            $this->db->commit();

            return $id;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }
}
