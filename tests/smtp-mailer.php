<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/SmtpMailer.php';

// Chạy PHPMailer thật qua SMTP giả trong RAM: không tạo socket hoặc gửi mail ra ngoài.
class MemorySmtp extends \PHPMailer\PHPMailer\SMTP
{
    public bool $open = false;
    public bool $tls = false;
    public bool $fail = false;
    public array $recipients = [];
    public string $message = '';
    public function connect($host, $port = null, $timeout = 30, $options = []) { $this->open = true; return true; }
    public function connected() { return $this->open; }
    public function hello($host = '') { $this->server_caps = ['EHLO' => 'localhost', 'STARTTLS' => true, 'AUTH' => ['LOGIN']]; return true; }
    public function startTLS() { $this->tls = true; return true; }
    public function authenticate($username, $password, $authtype = null, $OAuth = null) { return true; }
    public function mail($from) { return true; }
    public function recipient($address, $dsn = '') { $this->recipients[] = $address; return true; }
    public function data($msg_data) { $this->message = $msg_data; return !$this->fail; }
    public function reset() { return true; }
    public function quit($close_on_error = true) { $this->open = false; return true; }
    public function close() { $this->open = false; }
}
$passed = 0;
function checkMail(bool $value, string $label): void {
    global $passed;
    if (!$value) { throw new RuntimeException($label); }
    echo '[PASS] ' . $label . PHP_EOL;
    $passed++;
}
try {
    foreach (['HOST'=>'smtp.example.test', 'PORT'=>'587', 'ENCRYPTION'=>'tls', 'USERNAME'=>'sender@example.test',
        'PASSWORD'=>'test-only', 'FROM_ADDRESS'=>'sender@example.test', 'FROM_NAME'=>'Ký túc xá Kanto'] as $key=>$value) {
        putenv('MAIL_' . $key . '=' . $value);
    }
    $smtp = new MemorySmtp();
    $mailer = new class($smtp) extends SmtpMailer {
        public ?\PHPMailer\PHPMailer\PHPMailer $last = null;
        public function __construct(private MemorySmtp $smtp) {}
        protected function createMailer(): \PHPMailer\PHPMailer\PHPMailer {
            $this->last = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->last->setSMTPInstance($this->smtp);
            return $this->last;
        }
    };
    $message = ['MaDonDangKy'=>'ABC123', 'NguoiNhan'=>'student@example.test', 'TieuDe'=>'Kanto - Kết quả xét duyệt',
        'NoiDung'=>"Chào Nguyễn Văn A,\nLý do: thiếu giấy xác nhận <script>test</script>."];
    $mailer->send($message);
    checkMail($smtp->tls && $mailer->last->SMTPAuth && $mailer->last->Port === 587, 'SMTP AUTH và STARTTLS được cấu hình');
    checkMail($smtp->recipients === ['student@example.test'] && $mailer->last->From === 'sender@example.test', 'Dùng đúng email gửi và nhận');
    checkMail(str_contains($smtp->message, 'Content-Type: text/plain; charset=UTF-8')
        && str_contains($smtp->message, 'thiếu giấy xác nhận <script>test</script>'), 'Nội dung tiếng Việt được mã hóa UTF-8 và gửi dạng văn bản');
    checkMail(str_contains($smtp->message, 'Message-ID: <kanto-approval-abc123@example.test>'), 'Message-ID ổn định theo mã đơn');
    $smtp->fail = true;
    $failed = false;
    try { $mailer->send($message); } catch (\PHPMailer\PHPMailer\Exception $error) { $failed = true; }
    checkMail($failed, 'SMTP từ chối DATA được báo thành lỗi gửi');
    $failed = false;
    putenv('MAIL_PASSWORD=');
    try { $mailer->send($message); } catch (RuntimeException $error) { $failed = true; }
    checkMail($failed, 'Thiếu credential bị chặn trước khi gửi');
    putenv('MAIL_PASSWORD=test-only');
    putenv('MAIL_ENCRYPTION=none');
    $failed = false;
    try { $mailer->send($message); } catch (RuntimeException $error) { $failed = true; }
    checkMail($failed, 'Không gửi SMTP không mã hóa');
    echo "$passed kiểm thử adapter SMTP đạt.\n";
} catch (Throwable $error) {
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
