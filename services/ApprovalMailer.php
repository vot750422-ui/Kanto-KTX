<?php
require_once __DIR__ . '/SmtpMailer.php';
require_once __DIR__ . '/BrevoMailer.php';

// Chọn adapter gửi thư theo cấu hình nhưng giữ nguyên hàng đợi email xét duyệt.
class ApprovalMailer
{
    public function send(array $message): void
    {
        $transport = strtolower(trim((string) (getenv('MAIL_TRANSPORT') ?: 'smtp')));
        if ($transport === 'brevo') {
            (new BrevoMailer())->send($message);
            return;
        }
        if ($transport === 'smtp') {
            (new SmtpMailer())->send($message);
            return;
        }
        throw new RuntimeException('Phương thức gửi email không hợp lệ.');
    }
}
