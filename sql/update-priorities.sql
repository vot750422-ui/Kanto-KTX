-- Cập nhật danh mục đã chốt, giữ nguyên mã UT00 đang được tham chiếu.
INSERT INTO dienuutien (MaUuTien, TenDienUuTien, MucUuTien) VALUES
('UT00', 'Không có', 0),
('UT01', 'Hộ nghèo / Cận nghèo', 2),
('UT02', 'Con thương binh / Liệt sĩ', 4),
('UT03', 'Vùng sâu vùng xa / Hải đảo', 3)
ON DUPLICATE KEY UPDATE
TenDienUuTien = VALUES(TenDienUuTien),
MucUuTien = VALUES(MucUuTien);
