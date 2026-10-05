<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';

$action = $_GET['action'] ?? '';

if ($action === 'login') {

    $controller = new AuthController($pdo);
    $controller->login();

    exit;
}

if (isset($_SESSION['user'])) {

    $role = $_SESSION['user']['VaiTro'];

    switch ($role) {

        case 'Sinh viên':
            header('Location: /Kanto-KTX/views/student/dashboard.php');
            break;

        case 'Nhân viên quản lý KTX':
            header('Location: /Kanto-KTX/views/nvql/dashboard.php');
            break;

        case 'Nhân viên kế toán':
            header('Location: /Kanto-KTX/views/ketoan/dashboard.php');
            break;

        case 'Quản lý KTX':
            header('Location: /Kanto-KTX/views/quanly/dashboard.php');
            break;
    }

    exit;
}

require __DIR__ . '/views/auth/login.php';