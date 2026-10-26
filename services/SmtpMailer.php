<?php

class SmtpMailer
{
    protected function createMailer(): \PHPMailer\PHPMailer\PHPMailer
    {
        return new \PHPMailer\PHPMailer\PHPMailer(true);
    }

    public function send(array $message): void
    {
        $local = __DIR__ . '/../config/mail.local.php';
        $config = is_file($local) ? require $local : [];
        if (!is_array($config)) {
            throw new RuntimeException('Cấu hình email không hợp lệ.');
        }
        foreach (['host', 'port', 'encryption', 'username', 'password', 'from_address', 'from_name'] as $key) {
            $value = getenv('MAIL_' . strtoupper($key));
            if ($value !== false) { $config[$key] = $value; }
        }
        foreach (['host', 'username', 'password', 'from_address'] as $key) {
            if (!is_string($config[$key] ?? null) || trim($config[$key]) === '') {
                throw new RuntimeException('Chưa cấu hình SMTP.');
            }
        }
        $port = filter_var($config['port'] ?? 587, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        $encryption = $config['encryption'] ?? 'tls';
        if (!$port || !in_array($encryption, ['tls', 'ssl'], true)
            || !preg_match('/^[a-zA-Z0-9.-]+$/D', $config['host'])
            || !filter_var($config['from_address'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Cấu hình SMTP không hợp lệ.');
        }
        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (!is_file($autoload)) { throw new RuntimeException('Chưa cài thư viện gửi email.'); }
        require_once $autoload;
        $mail = $this->createMailer();
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = $port;
        $mail->SMTPSecure = $encryption;
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->Timeout = 10;
        $mail->getSMTPInstance()->Timelimit = 15;
        $mail->SMTPDebug = 0;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['from_address'], $config['from_name'] ?? 'Ký túc xá Đại học Kanto');
        $mail->addAddress($message['NguoiNhan']);
        // Nội dung thuần văn bản: tên/lý do từ đơn không được diễn giải thành HTML.
        $mail->isHTML(false);
        $mail->Subject = $message['TieuDe'];
        $mail->Body = $message['NoiDung'];
        $mail->MessageID = '<kanto-approval-' . strtolower($message['MaDonDangKy']) . '@' . substr(strrchr($config['from_address'], '@'), 1) . '>';
        $mail->send();
    }
}
