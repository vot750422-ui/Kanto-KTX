<?php

// Lưu ý định thanh toán ngắn hạn trong session và buộc xác nhận đúng tài khoản/số tiền.
final class StudentPaymentIntent
{
    public static function issue(string $invoice, int $accountId, string $amount): string
    {
        foreach ($_SESSION['student_payment_intents'] ?? [] as $key => $entry) {
            if ($entry['expires'] <= time() || $entry['account'] !== $accountId) {
                unset($_SESSION['student_payment_intents'][$key]);
            }
        }
        $existing = $_SESSION['student_payment_intents'][$invoice] ?? null;
        if ($existing && $existing['amount'] === $amount) {
            return $existing['token'];
        }
        if (count($_SESSION['student_payment_intents'] ?? []) >= 20) {
            array_shift($_SESSION['student_payment_intents']);
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['student_payment_intents'][$invoice] = [
            'account' => $accountId, 'amount' => $amount, 'token' => $token, 'expires' => time() + 900,
        ];
        return $token;
    }

    public static function amount(string $invoice, int $accountId, mixed $token): string
    {
        $entry = $_SESSION['student_payment_intents'][$invoice] ?? null;
        if (!$entry || !is_string($token) || $entry['account'] !== $accountId
            || $entry['expires'] <= time() || !hash_equals($entry['token'], $token)) {
            throw new DomainException('Phiên thanh toán không hợp lệ hoặc đã hết hạn. Vui lòng mở lại trang thanh toán.');
        }
        return $entry['amount'];
    }

    public static function forget(string $invoice): void
    {
        unset($_SESSION['student_payment_intents'][$invoice]);
    }
}
