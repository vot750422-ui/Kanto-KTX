<?php
require_once __DIR__ . '/timezone.php';

// WAMP uses a subdirectory; Docker sets APP_BASE_PATH=/.
function appUrl(string $path = ''): string
{
    $base = getenv('APP_BASE_PATH');
    $base = $base === false ? '/Kanto-KTX' : $base;
    return '/' . trim(trim($base, '/') . '/' . ltrim($path, '/'), '/');
}

// A QR scanned on another device needs an address reachable from that device.
// WAMP can set this in ignored config/payment.local.php; hosted environments
// can use PAYMENT_PUBLIC_ORIGIN. Without either, use the current page's origin.
function paymentPublicOrigin(): string
{
    $origin = getenv('PAYMENT_PUBLIC_ORIGIN');
    if ($origin === false && is_file(__DIR__ . '/payment.local.php')) {
        $origin = require __DIR__ . '/payment.local.php';
    }
    if ($origin === false || $origin === '') { return ''; }
    if (!is_string($origin) || !preg_match('~^https?://(?:[a-zA-Z0-9.-]+|\[[a-fA-F0-9:]+\])(?::[0-9]{1,5})?/?$~D', $origin)) {
        throw new RuntimeException('PAYMENT_PUBLIC_ORIGIN phải là địa chỉ http(s) chỉ gồm giao thức, tên máy và cổng.');
    }
    return rtrim($origin, '/');
}
