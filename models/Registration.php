<?php

class Registration
{
    public function __construct(private PDO $db) {}

    public function priorities(): array
    {
        return $this->db->query('SELECT * FROM dienuutien ORDER BY MucUuTien DESC, MaUuTien')->fetchAll();
    }

    public function hasPending(string $mssv): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM dondangky WHERE MSSV = ? AND TrangThai = 'Chờ duyệt' LIMIT 1");
        $stmt->execute([$mssv]);
        return (bool) $stmt->fetchColumn();
    }

    public function rooms(string $gender): array
    {
        $stmt = $this->db->prepare("SELECT p.*, t.TenToa,
            COALESCE(h.DangO, 0) AS DangO,
            p.SucChua - COALESCE(h.DangO, 0) - COALESCE(d.DangGiu, 0) AS ConCho
            FROM phong p JOIN toanha t ON t.MaToa = p.MaToa
            LEFT JOIN (SELECT MaPhong, COUNT(*) AS DangO FROM hopdong WHERE TrangThai = 'Còn hạn' GROUP BY MaPhong) h ON h.MaPhong = p.MaPhong
            LEFT JOIN (SELECT MaPhong, COUNT(*) AS DangGiu FROM dondangky WHERE TrangThai = 'Chờ duyệt' GROUP BY MaPhong) d ON d.MaPhong = p.MaPhong
            WHERE p.GioiTinhPhong = ? ORDER BY t.TenToa, p.Tang, p.SoPhong");
        $stmt->execute([$gender]);
        return array_values(array_filter($stmt->fetchAll(), fn($room) => (int) $room['ConCho'] > 0));
    }

    public function submit(array $data, string $roomId): string
    {
        // Khóa chung UC02: bảo vệ cả MSSV trùng ở hai phòng khác nhau.
        $lock = $this->db->prepare('SELECT GET_LOCK(?, 10)');
        $lockName = 'kanto_uc02_' . substr(hash('sha256', (string) $this->db->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $lock->execute([$lockName]);
        if ((int) $lock->fetchColumn() !== 1) {
            throw new RuntimeException('Hệ thống đang bận. Vui lòng gửi lại sau.');
        }
        try {
            $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $this->db->beginTransaction();
            $stmt = $this->db->prepare('SELECT MaPhong FROM phong WHERE MaPhong = ? FOR UPDATE');
            $stmt->execute([$roomId]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException('Phòng không hợp lệ. Vui lòng chọn phòng khác.', 2);
            }
            if ($this->hasPending($data['MSSV'])) {
                throw new DomainException('MSSV đã có hồ sơ đang được xử lý.', 1);
            }
            $room = array_filter($this->rooms($data['GioiTinh']), fn($r) => $r['MaPhong'] === $roomId);
            if (!$room) {
                throw new DomainException('Phòng bạn chọn vừa hết chỗ. Vui lòng chọn phòng khác.', 2);
            }
            $priority = array_filter($this->priorities(), fn($p) => $p['MaUuTien'] === $data['MaUuTien']);
            if (!$priority) {
                throw new DomainException('Diện ưu tiên không còn hợp lệ. Vui lòng kiểm tra lại.', 1);
            }
            $id = strtoupper(bin2hex(random_bytes(5)));
            $fields = ['MSSV', 'HoTen', 'NgaySinh', 'CCCD', 'GioiTinh', 'QueQuan', 'SDT', 'Lop', 'NienKhoa', 'MaUuTien', 'FileMinhChung'];
            $values = [$id];
            foreach ($fields as $field) {
                $values[] = $data[$field] ?? null;
            }
            $values[] = $roomId;
            $stmt = $this->db->prepare('INSERT INTO dondangky (MaDonDangKy, ' . implode(', ', $fields) . ', MaPhong) VALUES (' . implode(', ', array_fill(0, count($values), '?')) . ')');
            $stmt->execute($values);
            $this->db->commit();
            return $id;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute([$lockName]);
        }
    }
}
