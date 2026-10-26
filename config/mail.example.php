<?php
// WAMP: sao chép thành mail.local.php (đã được gitignore), rồi điền tại máy.
// Không gửi mật khẩu qua chat, không commit mail.local.php.
return [
    'host' => 'smtp.example.edu.vn', // Thay bằng máy chủ do trường cung cấp.
    'port' => 587,
    'encryption' => 'tls', // tls: STARTTLS/587; ssl: SMTPS/465.
    'username' => '',
    'password' => '', // Mật khẩu SMTP/app password theo hướng dẫn của trường.
    'from_address' => '',
    'from_name' => 'Ký túc xá Đại học Kanto',
];
