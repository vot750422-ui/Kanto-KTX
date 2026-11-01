<?php

class Renewal
{
    public function __construct(private PDO $db) {}

    /**
     * Tiến trình gia hạn lưu trú (Timer - UC18).
     * @return array Thống kê kết quả: ['renewed' => int, 'locked' => int, 'skipped' => int, 'errors' => int]
     */
    public function run(?string $targetSemesterId = null): array
    {
        $lockName = 'kanto_uc18_renewal_' . substr(hash('sha256', (string) $this->db->query('SELECT DATABASE()')->fetchColumn()), 0, 24);
        $lockStmt = $this->db->prepare('SELECT GET_LOCK(?, 0)');
        $lockStmt->execute([$lockName]);
        if ((int) $lockStmt->fetchColumn() !== 1) {
            throw new DomainException('Tiến trình gia hạn đang chạy trên phiên khác.');
        }

        $stats = [
            'renewed' => 0,
            'locked'  => 0,
            'skipped' => 0,
            'errors'  => 0,
        ];

        try {
            // 1. Xác định học kỳ hiện tại
            if ($targetSemesterId !== null) {
                $stmt = $this->db->prepare('SELECT * FROM hocky WHERE MaHocKy = ?');
                $stmt->execute([$targetSemesterId]);
                $semester = $stmt->fetch();
            } else {
                $stmt = $this->db->query('SELECT * FROM hocky WHERE NgayBatDau <= CURDATE() AND CURDATE() <= NgayKetThuc ORDER BY NgayBatDau DESC LIMIT 1');
                $semester = $stmt->fetch();
                if (!$semester) {
                    $stmt = $this->db->query('SELECT * FROM hocky ORDER BY NgayBatDau DESC LIMIT 1');
                    $semester = $stmt->fetch();
                }
            }

            if (!$semester) {
                $this->log('RENEWAL', 'Không tìm thấy học kỳ hợp lệ để thực hiện gia hạn.', 'WARNING');
                $stats['errors']++;
                return $stats;
            }

            // Kiểm tra học kỳ chồng lấn
            $overlapStmt = $this->db->prepare('SELECT COUNT(*) FROM hocky WHERE MaHocKy <> ? AND NgayBatDau <= ? AND NgayKetThuc >= ?');
            $overlapStmt->execute([$semester['MaHocKy'], $semester['NgayKetThuc'], $semester['NgayBatDau']]);
            if ((int) $overlapStmt->fetchColumn() > 0) {
                $this->log('RENEWAL', 'Phát hiện học kỳ chồng lấn thời gian với ' . $semester['MaHocKy'], 'ERROR');
                $stats['errors']++;
                return $stats;
            }

            $this->db->beginTransaction();

            // 2. Quét tất cả hợp đồng "Còn hạn"
            $contractStmt = $this->db->query("SELECT h.* FROM hopdong h WHERE h.TrangThai = 'Còn hạn' FOR UPDATE");
            $contracts = $contractStmt->fetchAll();

            foreach ($contracts as $contract) {
                // Nếu hợp đồng đã thuộc học kỳ hiện tại và còn hạn thì bỏ qua
                if (isset($contract['MaHocKy']) && $contract['MaHocKy'] === $semester['MaHocKy']) {
                    $stats['skipped']++;
                    continue;
                }

                // 3. Kiểm tra hóa đơn tiền phòng của sinh viên trong học kỳ hiện tại
                $invoiceStmt = $this->db->prepare('SELECT h.MaHoaDon, h.TrangThai FROM hoadontienphong p JOIN hoadon h ON h.MaHoaDon = p.MaHoaDon WHERE p.MSSV = ? AND p.MaHocKy = ?');
                $invoiceStmt->execute([$contract['MSSV'], $semester['MaHocKy']]);
                $invoice = $invoiceStmt->fetch();

                // Chuyển hợp đồng cũ sang "Hết hạn"
                $updateOldStmt = $this->db->prepare("UPDATE hopdong SET TrangThai = 'Hết hạn' WHERE MaHopDong = ?");
                $updateOldStmt->execute([$contract['MaHopDong']]);

                if ($invoice && $invoice['TrangThai'] === 'Đã thu') {
                    // ĐỦ ĐIỀU KIỆN GIA HẠN
                    $newContractId = $this->uniqueId('hopdong', 'MaHopDong', 10);
                    if (strlen($newContractId) > 10) {
                        $this->log('RENEWAL', 'Mã hợp đồng vượt quá độ dài 10 ký tự: ' . $newContractId, 'ERROR');
                        throw new RuntimeException('Mã hợp đồng vượt quá độ dài quy định.');
                    }

                    // Thêm hợp đồng mới
                    $insertContractStmt = $this->db->prepare("INSERT INTO hopdong (MaHopDong, MSSV, MaPhong, NgayBatDau, NgayKetThuc, TrangThai, MaHocKy) VALUES (?, ?, ?, ?, ?, 'Còn hạn', ?)");
                    $insertContractStmt->execute([
                        $newContractId,
                        $contract['MSSV'],
                        $contract['MaPhong'],
                        $semester['NgayBatDau'],
                        $semester['NgayKetThuc'],
                        $semester['MaHocKy'],
                    ]);

                    // Cập nhật trạng thái sinh viên Đang ở
                    $updateSvStmt = $this->db->prepare("UPDATE sinhvien SET TrangThaiLuuTru = 'Đang ở' WHERE MSSV = ?");
                    $updateSvStmt->execute([$contract['MSSV']]);

                    // Gửi thông báo gia hạn thành công
                    $tbId = $this->uniqueId('thongbao', 'MaThongBao', 12);
                    $tbTitle = 'Gia hạn lưu trú thành công - ' . $semester['TenHocKy'];
                    $tbContent = 'Hợp đồng lưu trú phòng ' . $contract['MaPhong'] . ' của bạn đã được tự động gia hạn cho ' . $semester['TenHocKy'] . '. Mã hợp đồng mới: ' . $newContractId;
                    $insertTbStmt = $this->db->prepare('INSERT INTO thongbao (MaThongBao, MSSV, MaHoaDon, TieuDe, NoiDung) VALUES (?, ?, ?, ?, ?)');
                    $insertTbStmt->execute([$tbId, $contract['MSSV'], $invoice['MaHoaDon'], $tbTitle, $tbContent]);

                    $stats['renewed']++;
                } else {
                    // KHÔNG ĐỦ ĐIỀU KIỆN: sinh viên đã có hợp đồng cũ nhưng hóa đơn học kỳ này chưa thanh toán
                    if ($invoice && $invoice['TrangThai'] === 'Chưa thu') {
                        $lockAccStmt = $this->db->prepare("UPDATE taikhoan t JOIN sinhvien s ON s.MaTK = t.MaTK SET t.TrangThai = 'Khóa' WHERE s.MSSV = ?");
                        $lockAccStmt->execute([$contract['MSSV']]);

                        $updateSvStmt = $this->db->prepare("UPDATE sinhvien SET TrangThaiLuuTru = 'Đã rời đi' WHERE MSSV = ?");
                        $updateSvStmt->execute([$contract['MSSV']]);

                        $stats['locked']++;
                    } else {
                        // Không tìm thấy hóa đơn học kỳ mới cho sinh viên này -> bỏ qua
                        $stats['skipped']++;
                    }
                }
            }

            // Ghi nhật ký hệ thống thành công
            $logMsg = sprintf(
                'Gia hạn học kỳ %s: %d gia hạn thành công, %d tài khoản bị khóa, %d bỏ qua.',
                $semester['MaHocKy'],
                $stats['renewed'],
                $stats['locked'],
                $stats['skipped']
            );
            $this->log('RENEWAL', $logMsg, 'INFO');

            $this->db->commit();
            return $stats;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->log('RENEWAL', 'Lỗi tiến trình gia hạn: ' . $e->getMessage(), 'ERROR');
            throw $e;
        } finally {
            $unlockStmt = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $unlockStmt->execute([$lockName]);
        }
    }

    private function log(string $type, string $message, string $level = 'INFO'): void
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO nhatkyhethong (LoaiNhatKy, NoiDung, MucDo) VALUES (?, ?, ?)');
            $stmt->execute([$type, $message, $level]);
        } catch (Throwable) {
            // Không để lỗi ghi log cản trở nếu đang trong môi trường không có bảng
        }
    }

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
