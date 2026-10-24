-- Liên kết QR liên thiết bị. Chỉ lưu SHA-256 của token, không lưu token gốc.
CREATE TABLE IF NOT EXISTS phienthanhtoan (
    MaPhien char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
    MaHoaDon varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    MaTK int NOT NULL,
    SoTien decimal(12,2) NOT NULL,
    TaoLuc datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    HetHanLuc datetime NOT NULL,
    DaDungLuc datetime DEFAULT NULL,
    KEY idx_phienthanhtoan_hoadon (MaHoaDon),
    KEY idx_phienthanhtoan_hethan (HetHanLuc),
    CONSTRAINT fk_phienthanhtoan_hoadon FOREIGN KEY (MaHoaDon) REFERENCES hoadon (MaHoaDon) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_phienthanhtoan_taikhoan FOREIGN KEY (MaTK) REFERENCES taikhoan (MaTK) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
