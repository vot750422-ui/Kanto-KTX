<?php

class Incident
{
    public function __construct(private PDO $db) {}

    public function accommodationForAccount(int $accountId): ?array
    {
        // Tìm hợp đồng còn hạn để phản ánh luôn gắn với đúng phòng đang lưu trú.
        $stmt = $this->db->prepare(
            "SELECT s.MSSV, h.MaPhong, p.SoPhong
             FROM sinhvien s
             JOIN hopdong h ON h.MSSV = s.MSSV
             JOIN phong p ON p.MaPhong = h.MaPhong
             WHERE s.MaTK = ? AND h.TrangThai = 'Còn hạn'
             ORDER BY h.NgayBatDau DESC, h.MaHopDong DESC
             LIMIT 1"
        );
        $stmt->execute([$accountId]);

        return $stmt->fetch() ?: null;
    }

    public function createForAccount(int $accountId, string $type, string $description): string
    {
        $this->db->beginTransaction();

        try {
            // Khóa hợp đồng trước khi lấy MSSV/phòng để không ghi sai nơi ở khi dữ liệu thay đổi đồng thời.
            $stmt = $this->db->prepare(
                "SELECT s.MSSV, h.MaPhong
                 FROM sinhvien s
                 JOIN hopdong h ON h.MSSV = s.MSSV
                 WHERE s.MaTK = ? AND h.TrangThai = 'Còn hạn'
                 ORDER BY h.NgayBatDau DESC, h.MaHopDong DESC
                 LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([$accountId]);
            $stay = $stmt->fetch();
            if (!$stay) {
                throw new DomainException('Bạn chưa có hợp đồng lưu trú còn hạn để gửi phản ánh.');
            }

            // Tạo mã phản ánh ngẫu nhiên và kiểm tra trùng trước khi lưu.
            do {
                $id = strtoupper(bin2hex(random_bytes(5)));
                $check = $this->db->prepare('SELECT 1 FROM phananhsuco WHERE MaPhanAnh = ?');
                $check->execute([$id]);
            } while ($check->fetchColumn());

            // Ghi phản ánh ở trạng thái chờ xử lý để nhân viên tiếp nhận sau.
            $stmt = $this->db->prepare(
                "INSERT INTO phananhsuco
                    (MaPhanAnh, MSSV, MaPhong, LoaiSuCo, MoTa, TrangThai)
                 VALUES (?, ?, ?, ?, ?, 'Chờ xử lý')"
            );
            $stmt->execute([$id, $stay['MSSV'], $stay['MaPhong'], $type, $description]);
            $this->db->commit();

            return $id;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function pending(int $page = 1, int $perPage = 20): array
    {
        // Chỉ lấy các phản ánh chưa xử lý để hàng đợi nhân viên không chứa dữ liệu đã hoàn tất.
        $total = (int) $this->db
            ->query("SELECT COUNT(*) FROM phananhsuco WHERE TrangThai = 'Chờ xử lý'")
            ->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT pa.MaPhanAnh, pa.MSSV, s.HoTen, p.SoPhong,
                    pa.LoaiSuCo, pa.MoTa, pa.ThoiGianGui, pa.TrangThai
             FROM phananhsuco pa
             JOIN sinhvien s ON s.MSSV = pa.MSSV
             JOIN phong p ON p.MaPhong = pa.MaPhong
             WHERE pa.TrangThai = 'Chờ xử lý'
             ORDER BY pa.ThoiGianGui, pa.MaPhanAnh
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function find(string $id): ?array
    {
        // Đọc chi tiết kèm tên sinh viên, phòng và nhân viên đã xử lý nếu có.
        $stmt = $this->db->prepare(
            'SELECT pa.*, s.HoTen, p.SoPhong, nv.HoTen AS NhanVienXuLy
             FROM phananhsuco pa
             JOIN sinhvien s ON s.MSSV = pa.MSSV
             JOIN phong p ON p.MaPhong = pa.MaPhong
             LEFT JOIN nhanvien nv ON nv.MaNV = pa.MaNhanVienXuLy
             WHERE pa.MaPhanAnh = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function resolve(string $id, int $accountId, string $cost): void
    {
        $this->db->beginTransaction();

        try {
            // Xác định đúng hồ sơ nhân viên đang làm trước khi cập nhật sự cố.
            $stmt = $this->db->prepare(
                "SELECT MaNV FROM nhanvien
                 WHERE MaTK = ? AND TrangThaiLamViec = 'Đang làm' FOR UPDATE"
            );
            $stmt->execute([$accountId]);
            $staffId = $stmt->fetchColumn();
            if ($staffId === false) {
                throw new DomainException('Không tìm thấy hồ sơ nhân viên đang làm việc.');
            }

            // Khóa bản ghi và chỉ cho phép chuyển từ chờ xử lý sang đã xử lý một lần.
            $stmt = $this->db->prepare(
                'SELECT TrangThai FROM phananhsuco WHERE MaPhanAnh = ? FOR UPDATE'
            );
            $stmt->execute([$id]);
            $status = $stmt->fetchColumn();
            if ($status === false) {
                throw new DomainException('Không tìm thấy phản ánh.');
            }
            if ($status !== 'Chờ xử lý') {
                throw new DomainException('Phản ánh đã được xử lý trước đó.');
            }

            $stmt = $this->db->prepare(
                "UPDATE phananhsuco
                 SET MaNhanVienXuLy = ?, ChiPhiSuaChua = ?, TrangThai = 'Đã xử lý'
                 WHERE MaPhanAnh = ? AND TrangThai = 'Chờ xử lý'"
            );
            $stmt->execute([$staffId, $cost, $id]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('Phản ánh đã thay đổi.');
            }

            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }
}
