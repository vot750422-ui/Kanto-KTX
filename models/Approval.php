<?php

class Approval
{
    public function __construct(private PDO $db) {}

    public function pending(int $page = 1, int $pageSize = 10): array
    {
        $total = (int) $this->db->query("SELECT COUNT(*) FROM dondangky WHERE TrangThai = 'Chờ duyệt'")->fetchColumn();
        $pageSize = max(1, min(50, $pageSize));
        $pages = max(1, (int) ceil($total / $pageSize));
        $page = max(1, min($pages, $page));
        $stmt = $this->db->prepare("SELECT d.*, u.TenDienUuTien, u.MucUuTien, p.SoPhong, p.Tang, t.TenToa
            FROM dondangky d JOIN dienuutien u ON u.MaUuTien = d.MaUuTien
            JOIN phong p ON p.MaPhong = d.MaPhong JOIN toanha t ON t.MaToa = p.MaToa
            WHERE d.TrangThai = 'Chờ duyệt'
            ORDER BY u.MucUuTien DESC, d.ThoiGianGui ASC, d.MaDonDangKy ASC
            LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(2, ($page - 1) * $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages, 'pageSize' => $pageSize];
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT d.*, u.TenDienUuTien, u.MucUuTien,
            p.SoPhong, p.Tang, p.GioiTinhPhong, p.SucChua, t.TenToa
            FROM dondangky d JOIN dienuutien u ON u.MaUuTien = d.MaUuTien
            JOIN phong p ON p.MaPhong = d.MaPhong JOIN toanha t ON t.MaToa = p.MaToa
            WHERE d.MaDonDangKy = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function reject(string $id, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('Thiếu lý do từ chối.');
        }
        if (mb_strlen($reason) > 500) {
            throw new DomainException('Lý do từ chối tối đa 500 ký tự.');
        }
        $this->process($id, function (array $application) use ($reason): void {
            $stmt = $this->db->prepare("UPDATE dondangky SET TrangThai = 'Từ chối', LyDoTuChoi = ?
                WHERE MaDonDangKy = ? AND TrangThai = 'Chờ duyệt'");
            $stmt->execute([$reason, $application['MaDonDangKy']]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('Đơn đăng ký đã được xử lý.');
            }
        });
    }

    public function billingContext(): array
    {
        $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
        $stmt = $this->db->prepare('SELECT * FROM hocky WHERE NgayBatDau <= ? AND NgayKetThuc >= ? ORDER BY NgayBatDau');
        $stmt->execute([$today, $today]);
        $current = $stmt->fetchAll();
        if (count($current) !== 1) {
            throw new DomainException('Cần có đúng một học kỳ hiện tại để xác định kỳ thu tiền tiếp theo.');
        }
        $stmt = $this->db->prepare('SELECT * FROM hocky WHERE NgayBatDau > ? ORDER BY NgayBatDau, MaHocKy LIMIT 2');
        $stmt->execute([$current[0]['NgayKetThuc']]);
        $next = $stmt->fetchAll();
        if (!$next || $next[0]['NgayKetThuc'] < $next[0]['NgayBatDau']) {
            throw new DomainException('Chưa có học kỳ kế tiếp hợp lệ để lập hóa đơn tiền phòng.');
        }
        if (isset($next[1]) && $next[1]['NgayBatDau'] <= $next[0]['NgayKetThuc']) {
            throw new DomainException('Các học kỳ kế tiếp bị chồng lấn. Vui lòng kiểm tra dữ liệu học kỳ.');
        }
        $stmt = $this->db->prepare("SELECT * FROM dongia WHERE LoaiDonGia = 'Tiền phòng' AND NgayApDung <= ? ORDER BY NgayApDung DESC, MaDonGia LIMIT 1");
        $stmt->execute([$today]);
        $rate = $stmt->fetch();
        if (!$rate || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', (string) $rate['GiaTri'])) {
            throw new DomainException('Chưa có đơn giá tiền phòng hợp lệ đang có hiệu lực.');
        }
        if (mb_strtolower(trim($rate['DonViTinh'])) !== 'đồng/học kỳ') {
            throw new DomainException('Đơn giá tiền phòng phải có đơn vị đồng/học kỳ để thu trọn một kỳ.');
        }
        return ['semester' => $next[0], 'rate' => $rate];
    }

    public function approve(string $id): array
    {
        return $this->process($id, function (array $application, array $room) use ($id): array {
            if ($room['GioiTinhPhong'] !== $application['GioiTinh']) {
                throw new DomainException('Phòng đã chọn không phù hợp giới tính của hồ sơ.');
            }
            $stmt = $this->db->prepare("SELECT
                (SELECT COUNT(*) FROM hopdong WHERE MaPhong = ? AND TrangThai = 'Còn hạn') +
                (SELECT COUNT(*) FROM dondangky WHERE MaPhong = ? AND
                    (TrangThai = 'Chờ duyệt' OR (TrangThai = 'Đã duyệt' AND MaHopDong IS NULL)))");
            $stmt->execute([$room['MaPhong'], $room['MaPhong']]);
            if ((int) $stmt->fetchColumn() > (int) $room['SucChua']) {
                throw new DomainException('Số chỗ đang sử dụng vượt sức chứa phòng. Vui lòng kiểm tra dữ liệu.');
            }
            if ($application['MaHoaDon'] !== null || $application['MaHopDong'] !== null) {
                throw new DomainException('Đơn chờ duyệt đã có liên kết hóa đơn hoặc hợp đồng không hợp lệ.');
            }
            foreach (['MSSV', 'CCCD'] as $field) {
                $stmt = $this->db->prepare("SELECT 1 FROM sinhvien WHERE $field = ? LIMIT 1");
                $stmt->execute([$application[$field]]);
                if ($stmt->fetchColumn()) {
                    throw new DomainException($field . ' đã tồn tại trong hồ sơ sinh viên.');
                }
            }
            $stmt = $this->db->prepare('SELECT 1 FROM taikhoan WHERE TenDangNhap = ? LIMIT 1');
            $stmt->execute([$application['MSSV']]);
            if ($stmt->fetchColumn()) {
                throw new DomainException('Tên đăng nhập theo MSSV đã tồn tại.');
            }
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM dondangky WHERE MSSV = ? AND TrangThai = 'Chờ duyệt'");
            $stmt->execute([$application['MSSV']]);
            if ((int) $stmt->fetchColumn() !== 1) {
                throw new DomainException('MSSV có nhiều đơn chờ duyệt. Vui lòng kiểm tra hồ sơ trùng.');
            }
            // Giá và kỳ đều đọc lại từ CSDL; không nhận số tiền từ trình duyệt.
            $billing = $this->billingContext();
            $stmt = $this->db->prepare("INSERT INTO taikhoan (TenDangNhap, MatKhau, VaiTro, TrangThai) VALUES (?, ?, 'Sinh viên', 'Hoạt động')");
            $stmt->execute([$application['MSSV'], password_hash('1', PASSWORD_DEFAULT)]);
            $accountId = (int) $this->db->lastInsertId();
            $fields = ['MSSV', 'MaUuTien', 'HoTen', 'NgaySinh', 'CCCD', 'GioiTinh', 'QueQuan', 'SDT', 'Lop', 'NienKhoa'];
            $values = [$accountId];
            foreach ($fields as $field) {
                $values[] = $application[$field];
            }
            $stmt = $this->db->prepare('INSERT INTO sinhvien (MaTK, ' . implode(', ', $fields)
                . ", TrangThaiLuuTru) VALUES (" . implode(', ', array_fill(0, count($values), '?')) . ", 'Chờ thanh toán')");
            $stmt->execute($values);
            $invoiceId = strtoupper(bin2hex(random_bytes(6)));
            $stmt = $this->db->prepare("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai) VALUES (?, 'Tiền phòng', ?, 'Chưa thu')");
            $stmt->execute([$invoiceId, $billing['rate']['GiaTri']]);
            $stmt = $this->db->prepare('INSERT INTO hoadontienphong (MaHoaDon, MSSV, MaPhong, MaHocKy, MaDonGia) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$invoiceId, $application['MSSV'], $application['MaPhong'], $billing['semester']['MaHocKy'], $billing['rate']['MaDonGia']]);
            $stmt = $this->db->prepare("UPDATE dondangky SET TrangThai = 'Đã duyệt', LyDoTuChoi = NULL, MaHoaDon = ? WHERE MaDonDangKy = ? AND TrangThai = 'Chờ duyệt'");
            $stmt->execute([$invoiceId, $id]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('Đơn đăng ký đã được xử lý.');
            }
            return ['username' => $application['MSSV'], 'invoice' => $invoiceId];
        });
    }

    private function process(string $id, callable $operation): mixed
    {
        // Dùng cùng khóa với UC02, kể cả kiểm tra MSSV giữa hai phòng khác nhau.
        $name = 'kanto_uc02_' . substr(hash('sha256', (string) $this->db->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $stmt = $this->db->prepare('SELECT GET_LOCK(?, 10)');
        $stmt->execute([$name]);
        if ((int) $stmt->fetchColumn() !== 1) {
            throw new RuntimeException('Không lấy được khóa xét duyệt.');
        }
        try {
            $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $this->db->beginTransaction();
            $stmt = $this->db->prepare('SELECT MaPhong FROM dondangky WHERE MaDonDangKy = ?');
            $stmt->execute([$id]);
            $roomId = $stmt->fetchColumn();
            if ($roomId === false) {
                throw new DomainException('Không tìm thấy đơn đăng ký.');
            }
            $stmt = $this->db->prepare('SELECT * FROM phong WHERE MaPhong = ? FOR UPDATE');
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();
            $stmt = $this->db->prepare('SELECT * FROM dondangky WHERE MaDonDangKy = ? FOR UPDATE');
            $stmt->execute([$id]);
            $application = $stmt->fetch();
            if (!$application || $application['TrangThai'] !== 'Chờ duyệt') {
                throw new DomainException('Đơn đăng ký đã được xử lý hoặc không còn chờ duyệt.');
            }
            if (!$room || $application['MaPhong'] !== $roomId) {
                throw new DomainException('Thông tin phòng đã thay đổi. Vui lòng tải lại đơn.');
            }
            $result = $operation($application, $room);
            $this->db->commit();
            return $result;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        } finally {
            $stmt = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $stmt->execute([$name]);
        }
    }

    public static function proof(?string $relativePath): array
    {
        if (!$relativePath) {
            return ['status' => 'none'];
        }
        // Chỉ nhận đường dẫn ảnh do UC02 sinh, không dùng đường dẫn từ request.
        if (!preg_match('~^storage/registration/[a-f0-9]{48}\.(jpg|png)$~D', $relativePath)) {
            return ['status' => 'missing'];
        }
        $root = realpath(__DIR__ . '/../storage/registration');
        $path = realpath(__DIR__ . '/../' . $relativePath);
        if ($root === false || $path === false || dirname($path) !== $root || !is_file($path) || !is_readable($path)) {
            return ['status' => 'missing'];
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return ['status' => 'missing'];
        }
        return ['status' => 'available', 'path' => $path, 'mime' => $mime];
    }
}
