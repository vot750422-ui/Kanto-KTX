<?php

session_start();

?>

<h1>Trang chủ Sinh viên</h1>

<p>
    Xin chào:
    <?= htmlspecialchars($_SESSION['user']['TenDangNhap'] ?? '') ?>
</p>

<p>
    Vai trò:
    <?= htmlspecialchars($_SESSION['user']['VaiTro'] ?? '') ?>
</p>