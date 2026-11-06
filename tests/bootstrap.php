<?php
/**
 * Test Bootstrap & Assertions Helper
 * Hệ thống Quản lý Kí túc xá Trường Đại học Kanto (Kanto-KTX)
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('TEST_ROOT', dirname(__DIR__));
require_once TEST_ROOT . '/config/app.php';

function assert_true(bool $condition, string $message = ''): void {
    if (!$condition) {
        throw new RuntimeException("Assertion failed: " . ($message ?: 'Expected true but got false'));
    }
}

function assert_equals($expected, $actual, string $message = ''): void {
    if ($expected !== $actual) {
        $expStr = var_export($expected, true);
        $actStr = var_export($actual, true);
        throw new RuntimeException("Assertion failed: " . ($message ?: "Expected {$expStr}, got {$actStr}"));
    }
}

function assert_contains(string $needle, string $haystack, string $message = ''): void {
    if (strpos($haystack, $needle) === false) {
        throw new RuntimeException("Assertion failed: " . ($message ?: "'{$needle}' not found in haystack"));
    }
}
