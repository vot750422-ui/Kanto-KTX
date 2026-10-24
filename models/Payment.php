<?php

class Payment
{
    public function __construct(private PDO $db) {}

    public function listing(string $student = '', string $status = 'Chưa thu', int $page = 1, array $filters = []): array
    {
        $where = '1=1';
        $params = [];
        foreach (['student'=>$student, 'status'=>$status, 'invoice'=>$filters['invoice'] ?? '', 'room'=>$filters['room'] ?? '', 'type'=>$filters['type'] ?? ''] as $key=>$value) {
            if ($value === '') { continue; }
            $column = ['student'=>'s.MSSV','status'=>'h.TrangThai','invoice'=>'h.MaHoaDon','room'=>'r.SoPhong','type'=>'h.LoaiHoaDon'][$key];
            $where .= ' AND ' . $column . '=?'; $params[] = $value;
        }
        if (($filters['name'] ?? '') !== '') { $where .= ' AND LOCATE(?,s.HoTen)>0'; $params[] = $filters['name']; }
        if (($filters['period'] ?? '') !== '') {
            $where .= " AND COALESCE(CONCAT(u.Nam,'-',LPAD(u.Thang,2,'0')),DATE_FORMAT(h.ThoiGianLap,'%Y-%m'))=?";
            $params[] = $filters['period'];
        }
        $from = ' FROM hoadon h LEFT JOIN hoadontienphong p ON p.MaHoaDon=h.MaHoaDon
            LEFT JOIN hoadondiennuoc u ON u.MaHoaDon=h.MaHoaDon
            LEFT JOIN hoadondiennuocsinhvien us ON us.MaHoaDon=u.MaHoaDon
            LEFT JOIN sinhvien s ON s.MSSV=COALESCE(p.MSSV,us.MSSV)
            JOIN phong r ON r.MaPhong=COALESCE(p.MaPhong,u.MaPhong)
            LEFT JOIN hocky k ON k.MaHocKy=p.MaHocKy LEFT JOIN dondangky a ON a.MaHoaDon=h.MaHoaDon';
        $stmt = $this->db->prepare('SELECT COUNT(DISTINCT h.MaHoaDon)' . $from . ' WHERE ' . $where);
        $stmt->execute($params); $total = (int)$stmt->fetchColumn();
        $pages = max(1, (int)ceil($total / 20)); $page = max(1, min($page, $pages));
        $stmt = $this->db->prepare("SELECT h.*, GROUP_CONCAT(DISTINCT s.MSSV ORDER BY s.MSSV SEPARATOR ', ') AS MSSV,
            GROUP_CONCAT(DISTINCT s.HoTen ORDER BY s.HoTen SEPARATOR ', ') AS HoTen,
            r.SoPhong, k.TenHocKy, k.NamHoc, k.NgayBatDau, k.NgayKetThuc, a.MaHopDong,
            COALESCE(CONCAT(LPAD(u.Thang,2,'0'),'/',u.Nam),DATE_FORMAT(h.ThoiGianLap,'%m/%Y')) AS KyHoaDon,
            (h.LoaiHoaDon='Tiền phòng' AND a.TrangThai='Đã duyệt' AND a.MaHopDong IS NULL) AS CanConfirm"
            . $from . ' WHERE ' . $where . ' GROUP BY h.MaHoaDon, r.SoPhong, k.MaHocKy, a.MaDonDangKy, u.MaHoaDon'
            . ' ORDER BY h.ThoiGianLap DESC, h.MaHoaDon DESC LIMIT 20 OFFSET ' . (($page - 1) * 20));
        $stmt->execute($params);
        return ['items'=>$stmt->fetchAll(), 'total'=>$total, 'page'=>$page, 'pages'=>$pages];
    }
    public function confirm(string $invoice): array
    {
        return $this->settle($invoice);
    }

    public function studentForAccount(int $accountId): ?array
    {
        $stmt = $this->db->prepare("SELECT s.MSSV, s.HoTen FROM sinhvien s JOIN taikhoan t ON t.MaTK=s.MaTK
            WHERE s.MaTK=? AND t.VaiTro='Sinh viên' AND t.TrangThai='Hoạt động'");
        $stmt->execute([$accountId]);
        return $stmt->fetch() ?: null;
    }

    public function studentInvoice(string $invoice, int $accountId, bool $forUpdate = false): ?array
    {
        // The utility invoice is shared by its recorded students, not by current room occupants.
        $stmt = $this->db->prepare("SELECT h.*, r.SoPhong, k.TenHocKy, k.NamHoc,
                u.Thang, u.Nam, u.DienTieuThu, u.NuocTieuThu, u.TienDien, u.TienNuoc
            FROM hoadon h
            LEFT JOIN hoadontienphong p ON p.MaHoaDon=h.MaHoaDon AND h.LoaiHoaDon='Tiền phòng'
            LEFT JOIN hoadondiennuoc u ON u.MaHoaDon=h.MaHoaDon AND h.LoaiHoaDon='Điện nước'
            LEFT JOIN hoadondiennuocsinhvien us ON us.MaHoaDon=u.MaHoaDon
            JOIN sinhvien s ON s.MSSV=COALESCE(p.MSSV,us.MSSV)
            JOIN taikhoan t ON t.MaTK=s.MaTK
            JOIN phong r ON r.MaPhong=COALESCE(p.MaPhong,u.MaPhong)
            LEFT JOIN hocky k ON k.MaHocKy=p.MaHocKy
            WHERE h.MaHoaDon=? AND s.MaTK=? AND t.VaiTro='Sinh viên' AND t.TrangThai='Hoạt động'"
            . ($forUpdate ? ' FOR UPDATE' : ''));
        $stmt->execute([$invoice, $accountId]);
        return $stmt->fetch() ?: null;
    }

    public function confirmForStudent(string $invoice, int $accountId, string $expectedAmount): array
    {
        return $this->settle($invoice, $accountId, $expectedAmount);
    }

    public function confirmLink(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
            throw new DomainException('Liên kết thanh toán không hợp lệ.');
        }
        return $this->settle('', null, null, $token);
    }

    private function settle(string $invoice, ?int $accountId = null, ?string $expectedAmount = null, ?string $linkToken = null): array
    {
        $name = 'kanto_uc02_' . substr(hash('sha256', (string)$this->db->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $lock = $this->db->prepare('SELECT GET_LOCK(?, 10)');
        $lock->execute([$name]);
        if ((int)$lock->fetchColumn() !== 1) { throw new DomainException('Hệ thống đang xử lý đăng ký. Vui lòng thử lại.'); }
        try {
            $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $this->db->beginTransaction();
            if ($linkToken !== null) {
                $hash = hash('sha256', $linkToken);
                $stmt = $this->db->prepare('SELECT MaHoaDon,MaTK,SoTien FROM phienthanhtoan WHERE MaPhien=? AND DaDungLuc IS NULL AND HetHanLuc>NOW() AND DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)>NOW() FOR UPDATE');
                $stmt->execute([$hash]);
                $link = $stmt->fetch();
                if (!$link) { throw new DomainException('Liên kết thanh toán không hợp lệ hoặc đã hết hiệu lực.'); }
                $latest = $this->db->prepare('SELECT MaPhien FROM phienthanhtoan WHERE MaHoaDon=? AND DaDungLuc IS NULL AND HetHanLuc>NOW() AND DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)>NOW() ORDER BY TaoLuc DESC,MaPhien DESC LIMIT 1 FOR UPDATE');
                $latest->execute([$link['MaHoaDon']]);
                if (!hash_equals((string)$latest->fetchColumn(), $hash)) { throw new DomainException('Liên kết thanh toán không còn hiệu lực.'); }
                $invoice = $link['MaHoaDon'];
                $accountId = (int)$link['MaTK'];
                $expectedAmount = (string)$link['SoTien'];
                // Consuming the link rolls back together with payment/contract on any failure.
                $stmt = $this->db->prepare('UPDATE phienthanhtoan SET DaDungLuc=NOW() WHERE MaPhien=?');
                $stmt->execute([$hash]);
            }
            if ($accountId !== null) {
                // UC17 and accounting share the same lock and first-payment transaction below.
                $stmt = $this->db->prepare('SELECT MaHoaDon FROM hoadon WHERE MaHoaDon=? FOR UPDATE');
                $stmt->execute([$invoice]);
                $stmt->fetchColumn();
                $owned = $this->studentInvoice($invoice, $accountId, true);
                if (!$owned) { throw new DomainException('Hóa đơn không tồn tại hoặc không thuộc về bạn.'); }
                if ($owned['TrangThai'] !== 'Chưa thu') { throw new DomainException('Hóa đơn đã được thanh toán. Không thể xác nhận lại.'); }
                if ((string)$owned['TongTien'] !== $expectedAmount) {
                    throw new DomainException('Số tiền hóa đơn đã thay đổi. Vui lòng mở lại trang thanh toán.');
                }
                $stmt = $this->db->prepare('SELECT MaDonDangKy FROM dondangky WHERE MaHoaDon=? FOR UPDATE');
                $stmt->execute([$invoice]);
                $initialApplication = $stmt->fetchColumn();
                if ($initialApplication === false) {
                    // Periodic room and utility invoices only change payment status.
                    $this->markPaid($invoice);
                    $this->db->commit();
                    return ['contract' => null];
                }
            }
            $stmt = $this->db->prepare('SELECT MaPhong FROM hoadontienphong WHERE MaHoaDon=?');
            $stmt->execute([$invoice]);
            $roomId = $stmt->fetchColumn();
            if ($roomId === false) { throw new DomainException('Không tìm thấy hóa đơn tiền phòng.'); }
            $stmt = $this->db->prepare('SELECT MaPhong, GioiTinhPhong, SucChua FROM phong WHERE MaPhong=? FOR UPDATE');
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();
            $stmt = $this->db->prepare('SELECT MaDonDangKy, MSSV, MaPhong, TrangThai, MaHopDong FROM dondangky WHERE MaHoaDon=? FOR UPDATE');
            $stmt->execute([$invoice]);
            $application = $stmt->fetch();
            $stmt = $this->db->prepare('SELECT LoaiHoaDon, TrangThai FROM hoadon WHERE MaHoaDon=? FOR UPDATE');
            $stmt->execute([$invoice]);
            $bill = $stmt->fetch();
            if (!$bill || $bill['LoaiHoaDon'] !== 'Tiền phòng') { throw new DomainException('Không tìm thấy hóa đơn tiền phòng.'); }
            if ($bill['TrangThai'] === 'Đã thu') { throw new DomainException('Hóa đơn đã được thanh toán. Không thể xác nhận lại.'); }
            if ($bill['TrangThai'] !== 'Chưa thu' || !$application || $application['TrangThai'] !== 'Đã duyệt' || $application['MaHopDong'] !== null) {
                throw new DomainException('Hóa đơn không thuộc đơn đã duyệt đang chờ thanh toán đầu tiên.');
            }
            $stmt = $this->db->prepare('SELECT MSSV, MaPhong, MaHocKy FROM hoadontienphong WHERE MaHoaDon=? FOR UPDATE');
            $stmt->execute([$invoice]);
            $detail = $stmt->fetch();
            if (!$room || !$detail || $detail['MaPhong'] !== $roomId || $application['MaPhong'] !== $roomId || $detail['MSSV'] !== $application['MSSV']) {
                throw new DomainException('Thông tin đơn, phòng và hóa đơn không khớp.');
            }
            $stmt = $this->db->prepare('SELECT NgayBatDau, NgayKetThuc FROM hocky WHERE MaHocKy=? FOR UPDATE');
            $stmt->execute([$detail['MaHocKy']]);
            $semester = $stmt->fetch();
            $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
            if (!$semester || $semester['NgayBatDau'] > $semester['NgayKetThuc'] || $semester['NgayKetThuc'] < $today) {
                throw new DomainException('Học kỳ trên hóa đơn không hợp lệ hoặc đã kết thúc.');
            }
            $stmt = $this->db->prepare('SELECT s.TrangThaiLuuTru, s.GioiTinh, t.TenDangNhap, t.VaiTro, t.TrangThai AS AccountStatus FROM sinhvien s JOIN taikhoan t ON t.MaTK=s.MaTK WHERE s.MSSV=? FOR UPDATE');
            $stmt->execute([$detail['MSSV']]);
            $student = $stmt->fetch();
            if (!$student || $student['TrangThaiLuuTru'] !== 'Chờ thanh toán' || $student['GioiTinh'] !== $room['GioiTinhPhong'] || $student['VaiTro'] !== 'Sinh viên' || $student['AccountStatus'] !== 'Hoạt động' || $student['TenDangNhap'] !== $detail['MSSV']) {
                throw new DomainException('Hồ sơ hoặc tài khoản sinh viên không hợp lệ để hoàn tất lưu trú.');
            }
            $stmt = $this->db->prepare("SELECT MaHopDong FROM hopdong WHERE MSSV=? AND TrangThai='Còn hạn' AND NgayBatDau <= ? AND NgayKetThuc >= ? FOR UPDATE");
            $stmt->execute([$detail['MSSV'], $semester['NgayKetThuc'], $semester['NgayBatDau']]);
            if ($stmt->fetch()) { throw new DomainException('Sinh viên đã có hợp đồng còn hạn trùng thời gian.'); }
            $stmt = $this->db->prepare("SELECT (SELECT COUNT(*) FROM hopdong WHERE MaPhong=? AND TrangThai='Còn hạn') + (SELECT COUNT(*) FROM dondangky WHERE MaPhong=? AND (TrangThai='Chờ duyệt' OR (TrangThai='Đã duyệt' AND MaHopDong IS NULL)))");
            $stmt->execute([$roomId, $roomId]);
            if ((int)$stmt->fetchColumn() > (int)$room['SucChua']) { throw new DomainException('Số chỗ đang sử dụng vượt sức chứa phòng.'); }
            $contract = strtoupper(bin2hex(random_bytes(5)));
            $stmt = $this->db->prepare("INSERT INTO hopdong (MaHopDong,MSSV,MaPhong,NgayBatDau,NgayKetThuc,TrangThai) VALUES (?,?,?,?,?,'Còn hạn')");
            $stmt->execute([$contract, $detail['MSSV'], $roomId, $semester['NgayBatDau'], $semester['NgayKetThuc']]);
            $stmt = $this->db->prepare('UPDATE dondangky SET MaHopDong=? WHERE MaDonDangKy=? AND MaHopDong IS NULL');
            $stmt->execute([$contract, $application['MaDonDangKy']]);
            if ($stmt->rowCount() !== 1) { throw new DomainException('Đơn đăng ký đã thay đổi. Vui lòng tải lại trang.'); }
            $stmt = $this->db->prepare("UPDATE sinhvien SET TrangThaiLuuTru='Đang ở' WHERE MSSV=?");
            $stmt->execute([$detail['MSSV']]);
            $this->markPaid($invoice);
            $this->db->commit();
            return ['contract' => $contract, 'username' => $student['TenDangNhap']];
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $error;
        } finally {
            $lock = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $lock->execute([$name]);
        }
    }

    private function markPaid(string $invoice): void
    {
        $stmt = $this->db->prepare("UPDATE hoadon SET TrangThai='Đã thu', ThoiGianThanhToan=NOW() WHERE MaHoaDon=? AND TrangThai='Chưa thu'");
        $stmt->execute([$invoice]);
        if ($stmt->rowCount() !== 1) { throw new DomainException('Hóa đơn đã thay đổi.'); }
    }
}
