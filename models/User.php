<?php

class User
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function findByUsername(string $username)
    {
        $sql = "SELECT MaTK, TenDangNhap, MatKhau, VaiTro, TrangThai
                FROM taikhoan
                WHERE TenDangNhap = :username
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':username' => $username
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}