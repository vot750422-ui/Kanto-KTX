-- Chạy trong CSDL qlktx_v2 (MySQL 8 trở lên).
-- Chỉ thêm dữ liệu; nếu trùng khóa thì giữ nguyên bản ghi hiện có.
START TRANSACTION;

INSERT INTO toanha (MaToa, TenToa) VALUES
('T01', 'Tòa A'), ('T02', 'Tòa B'), ('T03', 'Tòa C'), ('T04', 'Tòa D')
ON DUPLICATE KEY UPDATE MaToa = toanha.MaToa;

INSERT INTO phong (MaPhong, MaToa, SoPhong, Tang, GioiTinhPhong, SucChua)
WITH RECURSIVE
-- Sinh số thứ tự phòng từ 1 đến 13
so_thu_tu AS (
    SELECT 1 AS So
    UNION ALL
    SELECT So + 1 FROM so_thu_tu WHERE So < 13
),
danh_sach_toa AS (
    SELECT 1 AS ThuTu, 'T01' AS MaToa, 'Tòa A' AS TenToa, 'A' AS KyHieu, 'Nam' AS GioiTinh
    UNION ALL SELECT 2, 'T02', 'Tòa B', 'B', 'Nam'
    UNION ALL SELECT 3, 'T03', 'Tòa C', 'C', 'Nữ'
    UNION ALL SELECT 4, 'T04', 'Tòa D', 'D', 'Nữ'
),
danh_sach_tang AS (
    SELECT 1 AS Tang, 13 AS SoPhong, 0 AS ViTriDau
    UNION ALL SELECT 2, 13, 13
    UNION ALL SELECT 3, 12, 26
    UNION ALL SELECT 4, 12, 38
)
SELECT
    CONCAT('P', LPAD((t.ThuTu - 1) * 50 + f.ViTriDau + n.So, 4, '0')),
    t.MaToa,
    CONCAT(t.KyHieu, f.Tang, LPAD(n.So, 2, '0')),
    f.Tang,
    t.GioiTinh,
    8
FROM danh_sach_toa t
-- Không thêm phòng vào một mã tòa đã có nhưng mang tên khác bộ mẫu.
JOIN toanha existing ON existing.MaToa = t.MaToa AND existing.TenToa = t.TenToa
CROSS JOIN danh_sach_tang f
JOIN so_thu_tu n ON n.So <= f.SoPhong
WHERE 1 = 1
ON DUPLICATE KEY UPDATE MaPhong = phong.MaPhong;

COMMIT;
