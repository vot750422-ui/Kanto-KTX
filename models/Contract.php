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

        // Đếm tổng số bản ghi: không cần join toanha
        $stmt = $this->db->prepare('SELECT COUNT(*)' . $baseFrom . $condition);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        // Truy vấn danh sách có phân trang
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
                h.NgayBatDau, h.NgayKetThuc, h.TrangThai
            FROM hopdong h
            JOIN sinhvien s ON s.MSSV = h.MSSV
            JOIN phong p ON p.MaPhong = h.MaPhong
            LEFT JOIN toanha t ON t.MaToa = p.MaToa
            WHERE h.MaHopDong = ? LIMIT 1');
        $stmt->execute([$contractId]);
        return $stmt->fetch() ?: null;
    }

}
