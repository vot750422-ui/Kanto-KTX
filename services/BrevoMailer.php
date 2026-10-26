<?php

class BrevoMailer
{
    public function send(array $message): void
    {
        $apiKey = getenv('BREVO_API_KEY');
        $from = getenv('MAIL_FROM_ADDRESS');
        $name = getenv('MAIL_FROM_NAME') ?: 'Ký túc xá Đại học Kanto';
        if (!is_string($apiKey) || trim($apiKey) === ''
            || !is_string($from) || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Chưa cấu hình Brevo API và địa chỉ gửi.');
        }

        $payload = json_encode([
            'sender' => ['email' => $from, 'name' => $name],
            'to' => [['email' => $message['NguoiNhan']]],
            'subject' => $message['TieuDe'],
            'textContent' => $message['NoiDung'],
        ], JSON_THROW_ON_ERROR);
        [$status, $body] = $this->post($payload, $apiKey);
        $response = json_decode($body, true);
        if ($status !== 201 || !is_array($response) || empty($response['messageId'])) {
            throw new RuntimeException('Brevo chưa tiếp nhận email (HTTP ' . $status . ').');
        }
    }

    protected function post(string $payload, string $apiKey): array
    {
        $context = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "accept: application/json\r\ncontent-type: application/json\r\napi-key: {$apiKey}\r\n",
            'content' => $payload,
            'timeout' => 15,
            'ignore_errors' => true,
        ]]);
        $body = @file_get_contents('https://api.brevo.com/v3/smtp/email', false, $context);
        if ($body === false) {
            throw new RuntimeException('Không kết nối được Brevo API.');
        }
        $statusLine = $http_response_header[0] ?? '';
        preg_match('/^HTTP\/\S+\s+(\d{3})/', $statusLine, $matches);
        return [(int) ($matches[1] ?? 0), $body];
    }
}
