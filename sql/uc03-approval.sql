-- Migration UC03, chạy một lần trên CSDL hiện có (không import lại dump).
-- Kiểm tra SHOW COLUMNS trước khi chạy; MySQL DDL tự commit.
-- Giữ nguyên dữ liệu, Email và các ràng buộc hiện có.
ALTER TABLE sinhvien
    MODIFY TrangThaiLuuTru ENUM('Đang ở', 'Đã rời đi', 'Chờ thanh toán')
    NOT NULL DEFAULT 'Đang ở';

ALTER TABLE dondangky
    ADD COLUMN MaHoaDon VARCHAR(12) COLLATE utf8mb4_unicode_ci NULL,
    ADD COLUMN MaHopDong VARCHAR(10) COLLATE utf8mb4_unicode_ci NULL,
    ADD UNIQUE KEY uq_dondangky_hoadon (MaHoaDon),
    ADD UNIQUE KEY uq_dondangky_hopdong (MaHopDong),
    ADD CONSTRAINT fk_dondangky_hoadon FOREIGN KEY (MaHoaDon)
        REFERENCES hoadontienphong (MaHoaDon) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_dondangky_hopdong FOREIGN KEY (MaHopDong)
        REFERENCES hopdong (MaHopDong) ON DELETE RESTRICT ON UPDATE CASCADE;

UPDATE dienuutien SET MucUuTien = CASE MaUuTien
    WHEN 'UT00' THEN 0 WHEN 'UT01' THEN 2 WHEN 'UT02' THEN 4 WHEN 'UT03' THEN 3
    END WHERE MaUuTien IN ('UT00', 'UT01', 'UT02', 'UT03');
