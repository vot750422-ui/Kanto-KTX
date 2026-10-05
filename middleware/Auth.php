<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'cookie_httponly' => true,
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'cookie_samesite' => 'Lax',
    ]);
}

function requireAuth(): array
{
    $sessionUser = $_SESSION['user'] ?? null;
    if (!is_array($sessionUser) || !is_string($sessionUser['MaTK'] ?? null)) {
        unset($_SESSION['user']);
        header('Location: /Kanto-KTX/');
        exit;
    }

    // Đọc lại quyền và trạng thái để tài khoản bị khóa mất quyền ngay.
    require __DIR__ . '/../config/db.php';
    $stmt = $pdo->prepare('SELECT MaTK, TenDangNhap, VaiTro, TrangThai FROM taikhoan WHERE MaTK = :id LIMIT 1');
    $stmt->execute(['id' => $sessionUser['MaTK']]);
    $user = $stmt->fetch();

    if (!$user || $user['TrangThai'] !== 'Hoạt động') {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        $_SESSION['error'] = 'Phiên đăng nhập không còn hợp lệ. Vui lòng đăng nhập lại.';
        header('Location: /Kanto-KTX/');
        exit;
    }

    $_SESSION['user'] = [
        'MaTK' => $user['MaTK'],
        'TenDangNhap' => $user['TenDangNhap'],
        'VaiTro' => $user['VaiTro'],
    ];
    header('Cache-Control: no-store');
    return $_SESSION['user'];
}