<?php

require_once __DIR__ . '/../config/db.php';
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

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            header('Location: /Kanto-KTX/');
            exit;
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $_SESSION['error'] = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            header('Location: /Kanto-KTX/');
            exit;
        }

        if ($user['TrangThai'] === 'Khóa') {
            $_SESSION['error'] = 'Tài khoản đã bị khóa.';
            header('Location: /Kanto-KTX/');
            exit;
        }

        if (!password_verify($password, $user['MatKhau'])) {
            $_SESSION['error'] = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            header('Location: /Kanto-KTX/');
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
        switch ($role) {

            case 'Sinh viên':
                header('Location: /Kanto-KTX/views/student/Dashboard.php');
                break;

            case 'Nhân viên quản lý KTX':
                header('Location: /Kanto-KTX/views/nvql/Dashboard.php');
                break;

            case 'Nhân viên kế toán':
                header('Location: /Kanto-KTX/views/ketoan/Dashboard.php');
                break;

            case 'Quản lý KTX':
                header('Location: /Kanto-KTX/views/quanly/Dashboard.php');
                break;

            default:
                session_destroy();
                header('Location: /Kanto-KTX/');
                break;
        }

        exit;
    }
}