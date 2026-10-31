-- Bổ sung phân bổ hóa đơn điện nước theo từng sinh viên và lịch sử thanh lý.
CREATE TABLE IF NOT EXISTS thanhlyhopdong (
    MaThanhLy varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    MaHopDong varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    NgayThanhLy date NOT NULL,
    TienHoanTra decimal(12,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (MaThanhLy),
    UNIQUE KEY uq_thanhly_hopdong (MaHopDong),
    CONSTRAINT fk_thanhly_hopdong FOREIGN KEY (MaHopDong) REFERENCES hopdong (MaHopDong) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE hoadondiennuocsinhvien
    ADD COLUMN SoTienPhaiThu decimal(12,2) NOT NULL DEFAULT 0.00 AFTER MSSV,
    ADD COLUMN TrangThai enum('Chưa thu','Đã thu') NOT NULL DEFAULT 'Chưa thu' AFTER SoTienPhaiThu,
    ADD COLUMN ThoiGianThanhToan datetime DEFAULT NULL AFTER TrangThai;
