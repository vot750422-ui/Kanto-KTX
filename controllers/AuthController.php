<?php

require_once __DIR__ . '/../middleware/Role.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Lần đăng nhập mới phải xác thực lại, không kế thừa tài khoản cũ.
        unset($_SESSION['user'], $_SESSION['error']);

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!is_string($username) || !is_string($password)) {
            $_SESSION['error'] = 'Thông tin đăng nhập không hợp lệ.';
            header('Location: /Kanto-KTX/index.php?action=login');
            exit;
        }
        $username = trim($username);

        if ($username === '' || $password === '') {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            header('Location: /Kanto-KTX/index.php?action=login');
            exit;
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $_SESSION['error'] = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            header('Location: /Kanto-KTX/index.php?action=login');
            exit;
        }

        if (!password_verify($password, $user['MatKhau'])) {
            $_SESSION['error'] = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            header('Location: /Kanto-KTX/index.php?action=login');
            exit;
        }

        if ($user['TrangThai'] !== 'Hoạt động') {
            $_SESSION['error'] = 'Tài khoản đã bị khóa.';
            header('Location: /Kanto-KTX/index.php?action=login');
            exit;
        }

        if (dashboardForRole($user['VaiTro']) === null) {
            $_SESSION['error'] = 'Vai trò tài khoản không hợp lệ.';
            header('Location: /Kanto-KTX/index.php?action=login');
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'MaTK' => $user['MaTK'],
            'TenDangNhap' => $user['TenDangNhap'],
            'VaiTro' => $user['VaiTro']
        ];

        $this->redirectByRole($user['VaiTro']);
    }

    private function redirectByRole(string $role): void
    {
        header('Location: ' . (dashboardForRole($role) ?? '/Kanto-KTX/index.php?action=login'));
        exit;
    }
}
