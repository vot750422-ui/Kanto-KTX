<?php

class Notification
{
    public function __construct(private PDO $db) {}

    public function listingForAccount(int $accountId, int $page = 1, int $perPage = 20): array
    {
        // Đếm tổng số thông báo của sinh viên để tính số trang hiển thị.
        $count = $this->db->prepare(
            'SELECT COUNT(*) FROM thongbao tb
             JOIN sinhvien s ON s.MSSV = tb.MSSV
             WHERE s.MaTK = ?'
        );
        $count->execute([$accountId]);
        $total = (int) $count->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        // Chỉ đọc thông báo gắn với tài khoản hiện tại; LIMIT/OFFSET là số nguyên nội bộ.
        $stmt = $this->db->prepare(
            'SELECT tb.MaThongBao, tb.MaHoaDon, tb.TieuDe, tb.NoiDung, tb.ThoiGianTao
             FROM thongbao tb
             JOIN sinhvien s ON s.MSSV = tb.MSSV
             WHERE s.MaTK = ?
             ORDER BY tb.ThoiGianTao DESC, tb.MaThongBao DESC
             LIMIT ' . $perPage . ' OFFSET ' . $offset
        );
        $stmt->execute([$accountId]);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function findForAccount(string $id, int $accountId): ?array
    {
        // Ràng buộc mã thông báo với tài khoản để không lộ thông báo của sinh viên khác.
        $stmt = $this->db->prepare(
            'SELECT tb.MaThongBao, tb.MaHoaDon, tb.TieuDe, tb.NoiDung, tb.ThoiGianTao
             FROM thongbao tb
             JOIN sinhvien s ON s.MSSV = tb.MSSV
             WHERE tb.MaThongBao = ? AND s.MaTK = ?
             LIMIT 1'
        );
        $stmt->execute([$id, $accountId]);

        return $stmt->fetch() ?: null;
    }
}
