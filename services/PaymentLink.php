<?php
require_once __DIR__ . '/../models/Payment.php';

final class PaymentLink
{
    public function __construct(private PDO $db) {}

    public function current(string $invoice, int $accountId, ?string $knownToken = null): array
    {
        $bill = (new Payment($this->db))->studentInvoice($invoice, $accountId);
        if (!$bill || $bill['TrangThai'] !== 'Chưa thu') {
            return ['active' => false, 'token' => null, 'expires' => null];
        }
        $stmt = $this->db->prepare('SELECT MaPhien,MaTK,SoTien,LEAST(HetHanLuc,DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)) AS HetHanLuc FROM phienthanhtoan WHERE MaHoaDon=? AND DaDungLuc IS NULL AND HetHanLuc>NOW() AND DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)>NOW() ORDER BY TaoLuc DESC,MaPhien DESC');
        $stmt->execute([$invoice]);
        $row = $stmt->fetch();
        if ($row && (string)$row['SoTien'] === (string)$bill['TongTien']) {
            $ownsToken = (int)$row['MaTK'] === $accountId && is_string($knownToken)
                && preg_match('/^[a-f0-9]{64}$/D', $knownToken)
                && hash_equals($row['MaPhien'], hash('sha256', $knownToken));
            return ['active' => true, 'token' => $ownsToken ? $knownToken : null, 'expires' => $row['HetHanLuc']];
        }
        return ['active' => false, 'token' => null, 'expires' => null];
    }

    public function issue(string $invoice, int $accountId, ?string $knownToken = null): ?string
    {
        // Use the same lock as approval/payment to serialize QR creation with settlement.
        $name = 'kanto_uc02_' . substr(hash('sha256', (string)$this->db->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $lock = $this->db->prepare('SELECT GET_LOCK(?, 10)');
        $lock->execute([$name]);
        if ((int)$lock->fetchColumn() !== 1) { throw new DomainException('Hệ thống đang xử lý thanh toán. Vui lòng thử lại.'); }
        try {
            $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $this->db->beginTransaction();
            $bill = (new Payment($this->db))->studentInvoice($invoice, $accountId, true);
            if (!$bill || $bill['TrangThai'] !== 'Chưa thu') {
                throw new DomainException('Không thể tạo QR cho hóa đơn này.');
            }
            $stmt = $this->db->prepare('SELECT MaPhien,MaTK,SoTien FROM phienthanhtoan WHERE MaHoaDon=? AND DaDungLuc IS NULL AND HetHanLuc>NOW() AND DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)>NOW() ORDER BY TaoLuc DESC,MaPhien DESC FOR UPDATE');
            $stmt->execute([$invoice]);
            $rows = $stmt->fetchAll();
            $selected = null;
            foreach ($rows as $row) {
                if ((string)$row['SoTien'] !== (string)$bill['TongTien']) { continue; }
                if ($selected === null) { $selected = $row; }
                if ((int)$row['MaTK'] === $accountId && is_string($knownToken)
                    && preg_match('/^[a-f0-9]{64}$/D', $knownToken)
                    && hash_equals($row['MaPhien'], hash('sha256', $knownToken))) {
                    $selected = $row;
                    break;
                }
            }
            if ($rows) {
                // Retire older links and links for a changed amount.
                $stmt = $this->db->prepare('UPDATE phienthanhtoan SET HetHanLuc=NOW() WHERE MaHoaDon=? AND DaDungLuc IS NULL AND HetHanLuc>NOW() AND MaPhien<>?');
                $stmt->execute([$invoice, $selected['MaPhien'] ?? '']);
            }
            if ($selected !== null) {
                $this->db->commit();
                return (int)$selected['MaTK'] === $accountId && is_string($knownToken)
                    && hash_equals($selected['MaPhien'], hash('sha256', $knownToken)) ? $knownToken : null;
            }
            $token = bin2hex(random_bytes(32));
            $stmt = $this->db->prepare('INSERT INTO phienthanhtoan (MaPhien,MaHoaDon,MaTK,SoTien,HetHanLuc) VALUES (?,?,?,?,DATE_ADD(NOW(),INTERVAL 5 MINUTE))');
            $stmt->execute([hash('sha256', $token), $invoice, $accountId, $bill['TongTien']]);
            $this->db->commit();
            return $token;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $error;
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute([$name]);
        }
    }

    public function resolve(mixed $token): array
    {
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) {
            throw new DomainException('Liên kết thanh toán không hợp lệ hoặc đã hết hiệu lực. Vui lòng tạo QR mới.');
        }
        $stmt = $this->db->prepare('SELECT MaHoaDon,MaTK,SoTien,LEAST(HetHanLuc,DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)) AS HetHanLuc FROM phienthanhtoan WHERE MaPhien=? AND DaDungLuc IS NULL AND HetHanLuc>NOW() AND DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)>NOW()');
        $stmt->execute([hash('sha256',$token)]);
        $link = $stmt->fetch();
        if ($link) {
            $latest = $this->db->prepare('SELECT MaPhien FROM phienthanhtoan WHERE MaHoaDon=? AND DaDungLuc IS NULL AND HetHanLuc>NOW() AND DATE_ADD(TaoLuc,INTERVAL 5 MINUTE)>NOW() ORDER BY TaoLuc DESC,MaPhien DESC LIMIT 1');
            $latest->execute([$link['MaHoaDon']]);
            if (!hash_equals((string)$latest->fetchColumn(), hash('sha256', $token))) { $link = null; }
        }
        $bill = $link ? (new Payment($this->db))->studentInvoice($link['MaHoaDon'], (int)$link['MaTK']) : null;
        if (!$bill || $bill['TrangThai'] !== 'Chưa thu' || (string)$bill['TongTien'] !== (string)$link['SoTien']) {
            throw new DomainException('Liên kết thanh toán không hợp lệ hoặc đã hết hiệu lực. Vui lòng tạo QR mới.');
        }
        // Public checkout deliberately exposes no student profile or accommodation details.
        return ['MaHoaDon'=>$bill['MaHoaDon'], 'LoaiHoaDon'=>$bill['LoaiHoaDon'], 'TongTien'=>$bill['TongTien'], 'HetHanLuc'=>$link['HetHanLuc']];
    }
}
