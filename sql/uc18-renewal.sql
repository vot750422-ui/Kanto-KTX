-- Migration UC18: Gia hạn hợp đồng lưu trú (Timer)
-- Tạo bảng nhật ký hệ thống để ghi vết tự động
CREATE TABLE IF NOT EXISTS `nhatkyhethong` (
  `MaNhatKy` INT AUTO_INCREMENT PRIMARY KEY,
  `ThoiGian` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LoaiNhatKy` VARCHAR(50) NOT NULL,
  `NoiDung` TEXT NOT NULL,
  `MucDo` ENUM('INFO', 'WARNING', 'ERROR') NOT NULL DEFAULT 'INFO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột MaHocKy vào bảng hopdong nếu chưa có
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hopdong' AND COLUMN_NAME = 'MaHocKy');
SET @sql := IF(@exist = 0, 'ALTER TABLE hopdong ADD COLUMN MaHocKy VARCHAR(10) NULL, ADD CONSTRAINT fk_hopdong_hocky FOREIGN KEY (MaHocKy) REFERENCES hocky(MaHocKy) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
