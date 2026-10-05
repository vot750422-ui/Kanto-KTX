<?php
require_once __DIR__ . '/../../middleware/Role.php';
requireRole('Nhân viên kế toán');
?>

<h1>Trang chủ Nhân viên kế toán</h1>
<h2>Xin chào: <?= htmlspecialchars($_SESSION['user']['TenDangNhap'] ?? '') ?></h2>

<h2>Vai trò: <?= htmlspecialchars($_SESSION['user']['VaiTro'] ?? '') ?></h2>
