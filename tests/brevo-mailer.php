<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../services/BrevoMailer.php';

$passed = 0;
function checkBrevo(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) { throw new RuntimeException($label); }
    echo '[PASS] ' . $label . PHP_EOL;
    $passed++;
}

try {
    putenv('BREVO_API_KEY=test-only');
    putenv('MAIL_FROM_ADDRESS=sender@example.test');
    putenv('MAIL_FROM_NAME=Kanto KTX');
    $mailer = new class extends BrevoMailer {
        public array $request = [];
        public int $status = 201;
        protected function post(string $payload, string $apiKey): array
        {
            $this->request = [json_decode($payload, true, 512, JSON_THROW_ON_ERROR), $apiKey];
            return [$this->status, $this->status === 201 ? '{"messageId":"test-id"}' : '{"code":"unauthorized"}'];
        }
    };
    $message = [
        'NguoiNhan' => 'student@example.test',
        'TieuDe' => 'Kết quả xét duyệt',
        'NoiDung' => "Chào sinh viên,\nĐơn đã được duyệt.",
    ];
    $mailer->send($message);
    [$payload, $apiKey] = $mailer->request;
    checkBrevo($apiKey === 'test-only' && $payload['sender'] === ['email' => 'sender@example.test', 'name' => 'Kanto KTX']
        && $payload['to'] === [['email' => 'student@example.test']], 'Dùng khóa API và đúng địa chỉ gửi, nhận');
    checkBrevo($payload['subject'] === $message['TieuDe'] && $payload['textContent'] === $message['NoiDung'],
        'Giữ nguyên tiêu đề và nội dung tiếng Việt dạng text');

    $mailer->status = 401;
    $failed = false;
    try { $mailer->send($message); } catch (RuntimeException $error) { $failed = true; }
    checkBrevo($failed, 'API từ chối thì không đánh dấu đã gửi');

    putenv('BREVO_API_KEY=');
    $failed = false;
    try { $mailer->send($message); } catch (RuntimeException $error) { $failed = true; }
    checkBrevo($failed, 'Thiếu khóa API được phát hiện trước khi gửi');
    echo "$passed kiểm thử adapter Brevo đạt.\n";
} catch (Throwable $error) {
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
