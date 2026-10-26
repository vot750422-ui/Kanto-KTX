-- Chạy một lần trên CSDL hiện có; không nhập lại dump dữ liệu.
-- Email đã có ở dondangky/sinhvien, giữ nullable cho hồ sơ cũ.
CREATE TABLE IF NOT EXISTS emailthongbao (
    MaDonDangKy varchar(10) NOT NULL PRIMARY KEY,
    NguoiNhan varchar(100) DEFAULT NULL,
    TieuDe varchar(255) NOT NULL,
    NoiDung text NOT NULL,
    TrangThai enum('Chờ gửi','Đã gửi','Lỗi','Không có email') NOT NULL DEFAULT 'Chờ gửi',
    SoLanThu int unsigned NOT NULL DEFAULT 0,
    ThoiGianTao datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ThoiGianGui datetime DEFAULT NULL,
    KEY idx_emailthongbao_trangthai (TrangThai, ThoiGianTao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
