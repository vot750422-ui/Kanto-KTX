<?php

class StudentManagement
{
    public function __construct(private PDO $db) {}

    public function listing(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];
        foreach ([['s.MSSV', 'student'], ['s.HoTen', 'name'], ['s.Lop', 'class']] as [$column, $key]) {
            if (($filters[$key] ?? '') === '') {
                continue;
            }
            $where[] = $column . ' LIKE ?';
            $params[] = '%' . $filters[$key] . '%';
        }
        if (($filters['room'] ?? '') !== '') {
            $where[] = '(p.SoPhong LIKE ? OR h.MaPhong LIKE ?)';
            $params[] = '%' . $filters['room'] . '%';
            $params[] = '%' . $filters['room'] . '%';
        }
        if (($filters['status'] ?? '') !== '') {
            $where[] = 's.TrangThaiLuuTru = ?';
            $params[] = $filters['status'];
        }

        // Lấy hợp đồng mới nhất của mỗi sinh viên (ưu tiên Còn hạn, ngày bắt đầu mới nhất)
        $from = " FROM sinhvien s
            LEFT JOIN (
                SELECT ranked.* FROM (
                    SELECT source.*, ROW_NUMBER() OVER (
                        PARTITION BY source.MSSV
                        ORDER BY (source.TrangThai = 'Còn hạn') DESC, source.NgayBatDau DESC, source.MaHopDong DESC
                    ) AS rn
                    FROM hopdong source
                ) ranked WHERE ranked.rn = 1
            ) h ON h.MSSV = s.MSSV
            LEFT JOIN phong p ON p.MaPhong = h.MaPhong";

        $condition = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare('SELECT COUNT(*)' . $from . $condition);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare('SELECT s.MSSV, s.HoTen, s.Lop, s.NienKhoa, s.TrangThaiLuuTru, p.SoPhong'
            . $from . $condition . ' ORDER BY s.HoTen, s.MSSV LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function find(string $studentId, bool $forUpdate = false): ?array
    {
        $sql = "SELECT s.*, h.MaHopDong, h.MaPhong, h.NgayBatDau, h.NgayKetThuc, h.TrangThai AS TrangThaiHopDong,
                p.SoPhong, p.Tang, t.TenToa
            FROM sinhvien s
            LEFT JOIN (
                SELECT * FROM hopdong
                WHERE MSSV = ?
                ORDER BY (TrangThai = 'Còn hạn') DESC, NgayBatDau DESC, MaHopDong DESC
                LIMIT 1
            ) h ON h.MSSV = s.MSSV
            LEFT JOIN phong p ON p.MaPhong = h.MaPhong
            LEFT JOIN toanha t ON t.MaToa = p.MaToa
            WHERE s.MSSV = ? LIMIT 1" . ($forUpdate ? ' FOR UPDATE' : '');

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetch() ?: null;
    }

    public function update(string $studentId, array $data): bool
    {
        $this->db->beginTransaction();
        try {
            $current = $this->find($studentId, true);
            if (!$current) {
                throw new DomainException('Không tìm thấy sinh viên.');
            }

            $fields = ['HoTen', 'NgaySinh', 'QueQuan', 'SDT', 'Lop'];
            $changed = false;
            foreach ($fields as $field) {
                if ((string) $current[$field] !== (string) $data[$field]) {
                    $changed = true;
                    break;
                }
            }

            if (!$changed) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare('UPDATE sinhvien SET HoTen = ?, NgaySinh = ?, QueQuan = ?, SDT = ?, Lop = ? WHERE MSSV = ?');
            $stmt->execute([
                $data['HoTen'],
                $data['NgaySinh'],
                $data['QueQuan'],
                $data['SDT'],
                $data['Lop'],
                $studentId,
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('Không thể cập nhật sinh viên.');
            }

            $this->db->commit();
            return true;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }
}
