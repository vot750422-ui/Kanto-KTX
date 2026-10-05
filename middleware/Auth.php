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
    $accountId = is_array($sessionUser) ? ($sessionUser['MaTK'] ?? null) : null;
    // PDO có thể trả cột INT dưới dạng số nguyên hoặc chuỗi số.
    $validAccountId = (is_int($accountId) && $accountId > 0)
        || (is_string($accountId) && preg_match('/^[1-9][0-9]*$/D', $accountId) === 1);
    if (!$validAccountId) {
        unset($_SESSION['user']);
        header('Location: /Kanto-KTX/index.php?action=login');
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
        header('Location: /Kanto-KTX/index.php?action=login');
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
