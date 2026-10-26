<?php
require_once __DIR__ . '/../services/ApprovalMailer.php';

class ApprovalEmail
{
    private Closure $sender;

    public function __construct(private PDO $db, ?callable $sender = null)
    {
        $this->sender = $sender === null ? (new ApprovalMailer())->send(...) : Closure::fromCallable($sender);
    }

    // Ghi cùng transaction xét duyệt. Chỉ gửi email sau commit.
    public function enqueue(array $application, string $status, ?string $reason = null, ?string $invoice = null): void
    {
        if (!$this->db->inTransaction() || !in_array($status, ['Đã duyệt', 'Từ chối'], true)) {
            throw new LogicException('Thông báo phải được tạo trong transaction xét duyệt.');
        }
        $id = $application['MaDonDangKy'];
        $approved = $status === 'Đã duyệt';
        $subject = 'Kanto KTX - ' . ($approved ? 'Đơn đăng ký được duyệt' : 'Đơn đăng ký bị từ chối') . ' - ' . $id;
        $body = 'Chào ' . $application['HoTen'] . ",\n\n"
            . 'Đơn đăng ký lưu trú ' . $id . ' (MSSV: ' . $application['MSSV'] . ') '
            . ($approved ? 'đã được duyệt.' : 'đã bị từ chối.') . "\n\n";
        $body .= $approved
            ? 'Tên đăng nhập: ' . $application['MSSV'] . "\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: " . $invoice
                . "\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n"
            : 'Lý do từ chối: ' . $reason . "\nVui lòng kiểm tra, bổ sung thông tin trước khi đăng ký lại hoặc liên hệ bộ phận quản lý KTX để được hỗ trợ.\n";
        $body .= "\nBan quản lý Ký túc xá Đại học Kanto";
        $email = $application['Email'] ?? null;
        $valid = is_string($email) && strlen($email) <= 100 && filter_var($email, FILTER_VALIDATE_EMAIL);
        $stmt = $this->db->prepare('INSERT INTO emailthongbao (MaDonDangKy, NguoiNhan, TieuDe, NoiDung, TrangThai) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$id, $valid ? $email : null, $subject, $body, $valid ? 'Chờ gửi' : 'Không có email']);
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM emailthongbao WHERE MaDonDangKy = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function outstanding(): array
    {
        return $this->db->query("SELECT MaDonDangKy, TrangThai FROM emailthongbao WHERE TrangThai IN ('Chờ gửi', 'Lỗi', 'Không có email') ORDER BY ThoiGianTao DESC LIMIT 50")->fetchAll();
    }

    public function deliver(string $id): string
    {
        if ($this->db->inTransaction()) { throw new LogicException('Không gửi email trong transaction.'); }
        $lockName = 'kanto_email_' . substr(hash('sha256', $this->db->query('SELECT DATABASE()')->fetchColumn() . ':' . $id), 0, 40);
        $stmt = $this->db->prepare('SELECT GET_LOCK(?, 0)');
        $stmt->execute([$lockName]);
        if ((int) $stmt->fetchColumn() !== 1) { return 'Đang gửi'; }
        try {
            $message = $this->find($id);
            if (!$message) { throw new DomainException('Đơn chưa có thông báo email.'); }
            if (!in_array($message['TrangThai'], ['Chờ gửi', 'Lỗi'], true)) { return $message['TrangThai']; }
            $stmt = $this->db->prepare('UPDATE emailthongbao SET SoLanThu = SoLanThu + 1 WHERE MaDonDangKy = ?');
            $stmt->execute([$id]);
            try {
                ($this->sender)($message);
            } catch (Throwable $error) {
                // Không ghi nội dung phản hồi từ nhà cung cấp, email hay credential vào log/UI.
                error_log('UC03 email chưa gửi được (' . get_class($error) . ').');
                $stmt = $this->db->prepare("UPDATE emailthongbao SET TrangThai = 'Lỗi' WHERE MaDonDangKy = ?");
                $stmt->execute([$id]);
                return 'Lỗi';
            }
            $stmt = $this->db->prepare("UPDATE emailthongbao SET TrangThai = 'Đã gửi', ThoiGianGui = NOW() WHERE MaDonDangKy = ?");
            $stmt->execute([$id]);
            return 'Đã gửi';
        } finally {
            $stmt = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $stmt->execute([$lockName]);
        }
    }
}
