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
