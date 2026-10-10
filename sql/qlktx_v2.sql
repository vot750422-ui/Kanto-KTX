-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 09, 2026 lúc 09:50 AM
-- Phiên bản máy phục vụ: 8.4.7
-- Phiên bản PHP: 8.3.28
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlktx_v2`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chisodiennuoc`
--

DROP TABLE IF EXISTS `chisodiennuoc`;
CREATE TABLE IF NOT EXISTS `chisodiennuoc` (
  `MaChiSo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaNV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ChiSoDien` int NOT NULL,
  `ChiSoNuoc` int NOT NULL,
  `Thang` tinyint UNSIGNED NOT NULL,
  `Nam` smallint UNSIGNED NOT NULL,
  `ThoiGianGhi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaChiSo`),
  UNIQUE KEY `uq_chiso_phong_thang_nam` (`MaPhong`,`Thang`,`Nam`),
  KEY `idx_chiso_manv` (`MaNV`)
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dienuutien`
--

DROP TABLE IF EXISTS `dienuutien`;
CREATE TABLE IF NOT EXISTS `dienuutien` (
  `MaUuTien` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenDienUuTien` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MucUuTien` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`MaUuTien`),
  UNIQUE KEY `uq_dienuutien_ten` (`TenDienUuTien`)
) ;

--
-- Đang đổ dữ liệu cho bảng `dienuutien`
--

INSERT INTO `dienuutien` (`MaUuTien`, `TenDienUuTien`, `MucUuTien`) VALUES
('UT00', 'Không có', 0),
('UT01', 'Hộ nghèo / Cận nghèo', 2),
('UT02', 'Con thương binh / Liệt sĩ', 4),
('UT03', 'Vùng sâu vùng xa / Hải đảo', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dondangky`
--

DROP TABLE IF EXISTS `dondangky`;
CREATE TABLE IF NOT EXISTS `dondangky` (
  `MaDonDangKy` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgaySinh` date NOT NULL,
  `CCCD` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GioiTinh` enum('Nam','Nữ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `QueQuan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SDT` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lop` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NienKhoa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaUuTien` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FileMinhChung` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaPhong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianGui` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TrangThai` enum('Chờ duyệt','Đã duyệt','Từ chối') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ duyệt',
  `LyDoTuChoi` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHoaDon` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHopDong` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`MaDonDangKy`),
  KEY `idx_dondangky_mssv` (`MSSV`),
  KEY `idx_dondangky_trangthai` (`TrangThai`),
  KEY `idx_dondangky_maphong` (`MaPhong`),
  KEY `idx_dondangky_mauutien` (`MaUuTien`),
  UNIQUE KEY `uq_dondangky_hoadon` (`MaHoaDon`),
  UNIQUE KEY `uq_dondangky_hopdong` (`MaHopDong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `dondangky`
--

INSERT INTO `dondangky` (`MaDonDangKy`, `MSSV`, `HoTen`, `NgaySinh`, `CCCD`, `GioiTinh`, `QueQuan`, `SDT`, `Email`, `Lop`, `NienKhoa`, `MaUuTien`, `FileMinhChung`, `MaPhong`, `ThoiGianGui`, `TrangThai`, `LyDoTuChoi`) VALUES
('41E119DC87', 'KTPM2411040', 'Phan Minh Trọng', '2020-07-02', '093206010838', 'Nam', 'Cần Thơ', '0797439616', NULL, 'KTPM2411', '2024-2028', 'UT00', 'storage/registration/2123978f89e66762cd3f6038b255e266522c1a94d2637b3d.jpg', 'P0039', '2026-10-08 20:04:20', 'Chờ duyệt', NULL),
('6B543826B3', 'KTPM2411047', 'Phan Minh Trọng', '2006-09-26', '093206010838', 'Nam', 'Cần Thơ', '0797439616', NULL, 'KTPM2411', '2024-2028', 'UT01', 'storage/registration/8ed74a8f1530ea234395e5d6a1c206ffd52bcdbc56c1db47.png', 'P0058', '2026-10-08 03:25:44', 'Chờ duyệt', NULL),
('FF8A9D65CA', 'KTPM2411049', 'Phan Minh Trọng', '2006-03-26', '053590350350', 'Nam', 'Cần Thơ', '0797439616', NULL, 'KTPM2411', '2024-2028', 'UT00', NULL, 'P0038', '2026-10-08 15:31:38', 'Chờ duyệt', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dongia`
--

DROP TABLE IF EXISTS `dongia`;
CREATE TABLE IF NOT EXISTS `dongia` (
  `MaDonGia` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LoaiDonGia` enum('Tiền phòng','Điện','Nước') COLLATE utf8mb4_unicode_ci NOT NULL,
  `GiaTri` decimal(12,2) NOT NULL,
  `DonViTinh` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayApDung` date NOT NULL,
  PRIMARY KEY (`MaDonGia`),
  UNIQUE KEY `uq_dongia_loai_ngay` (`LoaiDonGia`,`NgayApDung`)
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadon`
--

DROP TABLE IF EXISTS `hoadon`;
CREATE TABLE IF NOT EXISTS `hoadon` (
  `MaHoaDon` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LoaiHoaDon` enum('Tiền phòng','Điện nước') COLLATE utf8mb4_unicode_ci NOT NULL,
  `TongTien` decimal(12,2) NOT NULL,
  `ThoiGianLap` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TrangThai` enum('Chưa thu','Đã thu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa thu',
  `ThoiGianThanhToan` datetime DEFAULT NULL,
  PRIMARY KEY (`MaHoaDon`),
  KEY `idx_hoadon_loai` (`LoaiHoaDon`),
  KEY `idx_hoadon_trangthai` (`TrangThai`),
  KEY `idx_hoadon_thoigianlap` (`ThoiGianLap`),
  KEY `idx_hoadon_thoigianthanhtoan` (`ThoiGianThanhToan`)
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadondiennuoc`
--

DROP TABLE IF EXISTS `hoadondiennuoc`;
CREATE TABLE IF NOT EXISTS `hoadondiennuoc` (
  `MaHoaDon` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Thang` tinyint UNSIGNED NOT NULL,
  `Nam` smallint UNSIGNED NOT NULL,
  `DienTieuThu` int NOT NULL,
  `NuocTieuThu` int NOT NULL,
  `TienDien` decimal(12,2) NOT NULL,
  `TienNuoc` decimal(12,2) NOT NULL,
  `MaDonGiaDien` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaDonGiaNuoc` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MaHoaDon`),
  UNIQUE KEY `uq_hddn_phong_thang_nam` (`MaPhong`,`Thang`,`Nam`),
  KEY `idx_hddn_dongiadien` (`MaDonGiaDien`),
  KEY `idx_hddn_dongianuoc` (`MaDonGiaNuoc`)
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadondiennuocsinhvien`
--

DROP TABLE IF EXISTS `hoadondiennuocsinhvien`;
CREATE TABLE IF NOT EXISTS `hoadondiennuocsinhvien` (
  `MaHoaDon` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MaHoaDon`,`MSSV`),
  KEY `idx_hddnsv_mssv` (`MSSV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadontienphong`
--

DROP TABLE IF EXISTS `hoadontienphong`;
CREATE TABLE IF NOT EXISTS `hoadontienphong` (
  `MaHoaDon` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHocKy` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaDonGia` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MaHoaDon`),
  UNIQUE KEY `uq_hdphong_sv_hocky` (`MSSV`,`MaHocKy`),
  KEY `idx_hdphong_maphong` (`MaPhong`),
  KEY `idx_hdphong_mahocky` (`MaHocKy`),
  KEY `idx_hdphong_madongia` (`MaDonGia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hocky`
--

DROP TABLE IF EXISTS `hocky`;
CREATE TABLE IF NOT EXISTS `hocky` (
  `MaHocKy` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenHocKy` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NamHoc` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  PRIMARY KEY (`MaHocKy`),
  UNIQUE KEY `uq_hocky_ten_namhoc` (`TenHocKy`,`NamHoc`)
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hopdong`
--

DROP TABLE IF EXISTS `hopdong`;
CREATE TABLE IF NOT EXISTS `hopdong` (
  `MaHopDong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `TrangThai` enum('Còn hạn','Hết hạn','Đã thanh lý') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Còn hạn',
  PRIMARY KEY (`MaHopDong`),
  KEY `idx_hopdong_mssv` (`MSSV`),
  KEY `idx_hopdong_maphong` (`MaPhong`),
  KEY `idx_hopdong_trangthai` (`TrangThai`)
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

DROP TABLE IF EXISTS `nhanvien`;
CREATE TABLE IF NOT EXISTS `nhanvien` (
  `MaNV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` int NOT NULL,
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SDT` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TrangThaiLamViec` enum('Đang làm','Nghỉ việc') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang làm',
  PRIMARY KEY (`MaNV`),
  UNIQUE KEY `uq_nhanvien_matk` (`MaTK`),
  UNIQUE KEY `uq_nhanvien_email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phananhsuco`
--

DROP TABLE IF EXISTS `phananhsuco`;
CREATE TABLE IF NOT EXISTS `phananhsuco` (
  `MaPhanAnh` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaNhanVienXuLy` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LoaiSuCo` enum('Điện','Nước','Nội thất','Khác') COLLATE utf8mb4_unicode_ci NOT NULL,
  `MoTa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianGui` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ChiPhiSuaChua` decimal(12,2) DEFAULT NULL,
  `TrangThai` enum('Chờ xử lý','Đã xử lý') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ xử lý',
  PRIMARY KEY (`MaPhanAnh`),
  KEY `idx_phananh_mssv` (`MSSV`),
  KEY `idx_phananh_maphong` (`MaPhong`),
  KEY `idx_phananh_nhanvien` (`MaNhanVienXuLy`),
  KEY `idx_phananh_trangthai` (`TrangThai`),
  KEY `idx_phananh_thoigiangui` (`ThoiGianGui`)
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phong`
--

DROP TABLE IF EXISTS `phong`;
CREATE TABLE IF NOT EXISTS `phong` (
  `MaPhong` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaToa` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SoPhong` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tang` tinyint UNSIGNED NOT NULL,
  `GioiTinhPhong` enum('Nam','Nữ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `SucChua` int NOT NULL,
  PRIMARY KEY (`MaPhong`),
  UNIQUE KEY `uq_phong_toa_sophong` (`MaToa`,`SoPhong`),
  KEY `idx_phong_matoa` (`MaToa`),
  KEY `idx_phong_toa_tang` (`MaToa`,`Tang`)
) ;

--
-- Đang đổ dữ liệu cho bảng `phong`
--

INSERT INTO `phong` (`MaPhong`, `MaToa`, `SoPhong`, `Tang`, `GioiTinhPhong`, `SucChua`) VALUES
('P0001', 'T01', 'A101', 1, 'Nam', 8),
('P0002', 'T01', 'A102', 1, 'Nam', 8),
('P0003', 'T01', 'A103', 1, 'Nam', 8),
('P0004', 'T01', 'A104', 1, 'Nam', 8),
('P0005', 'T01', 'A105', 1, 'Nam', 8),
('P0006', 'T01', 'A106', 1, 'Nam', 8),
('P0007', 'T01', 'A107', 1, 'Nam', 8),
('P0008', 'T01', 'A108', 1, 'Nam', 8),
('P0009', 'T01', 'A109', 1, 'Nam', 8),
('P0010', 'T01', 'A110', 1, 'Nam', 8),
('P0011', 'T01', 'A111', 1, 'Nam', 8),
('P0012', 'T01', 'A112', 1, 'Nam', 8),
('P0013', 'T01', 'A113', 1, 'Nam', 8),
('P0014', 'T01', 'A201', 2, 'Nam', 8),
('P0015', 'T01', 'A202', 2, 'Nam', 8),
('P0016', 'T01', 'A203', 2, 'Nam', 8),
('P0017', 'T01', 'A204', 2, 'Nam', 8),
('P0018', 'T01', 'A205', 2, 'Nam', 8),
('P0019', 'T01', 'A206', 2, 'Nam', 8),
('P0020', 'T01', 'A207', 2, 'Nam', 8),
('P0021', 'T01', 'A208', 2, 'Nam', 8),
('P0022', 'T01', 'A209', 2, 'Nam', 8),
('P0023', 'T01', 'A210', 2, 'Nam', 8),
('P0024', 'T01', 'A211', 2, 'Nam', 8),
('P0025', 'T01', 'A212', 2, 'Nam', 8),
('P0026', 'T01', 'A213', 2, 'Nam', 8),
('P0027', 'T01', 'A301', 3, 'Nam', 8),
('P0028', 'T01', 'A302', 3, 'Nam', 8),
('P0029', 'T01', 'A303', 3, 'Nam', 8),
('P0030', 'T01', 'A304', 3, 'Nam', 8),
('P0031', 'T01', 'A305', 3, 'Nam', 8),
('P0032', 'T01', 'A306', 3, 'Nam', 8),
('P0033', 'T01', 'A307', 3, 'Nam', 8),
('P0034', 'T01', 'A308', 3, 'Nam', 8),
('P0035', 'T01', 'A309', 3, 'Nam', 8),
('P0036', 'T01', 'A310', 3, 'Nam', 8),
('P0037', 'T01', 'A311', 3, 'Nam', 8),
('P0038', 'T01', 'A312', 3, 'Nam', 8),
('P0039', 'T01', 'A401', 4, 'Nam', 8),
('P0040', 'T01', 'A402', 4, 'Nam', 8),
('P0041', 'T01', 'A403', 4, 'Nam', 8),
('P0042', 'T01', 'A404', 4, 'Nam', 8),
('P0043', 'T01', 'A405', 4, 'Nam', 8),
('P0044', 'T01', 'A406', 4, 'Nam', 8),
('P0045', 'T01', 'A407', 4, 'Nam', 8),
('P0046', 'T01', 'A408', 4, 'Nam', 8),
('P0047', 'T01', 'A409', 4, 'Nam', 8),
('P0048', 'T01', 'A410', 4, 'Nam', 8),
('P0049', 'T01', 'A411', 4, 'Nam', 8),
('P0050', 'T01', 'A412', 4, 'Nam', 8),
('P0051', 'T02', 'B101', 1, 'Nam', 8),
('P0052', 'T02', 'B102', 1, 'Nam', 8),
('P0053', 'T02', 'B103', 1, 'Nam', 8),
('P0054', 'T02', 'B104', 1, 'Nam', 8),
('P0055', 'T02', 'B105', 1, 'Nam', 8),
('P0056', 'T02', 'B106', 1, 'Nam', 8),
('P0057', 'T02', 'B107', 1, 'Nam', 8),
('P0058', 'T02', 'B108', 1, 'Nam', 8),
('P0059', 'T02', 'B109', 1, 'Nam', 8),
('P0060', 'T02', 'B110', 1, 'Nam', 8),
('P0061', 'T02', 'B111', 1, 'Nam', 8),
('P0062', 'T02', 'B112', 1, 'Nam', 8),
('P0063', 'T02', 'B113', 1, 'Nam', 8),
('P0064', 'T02', 'B201', 2, 'Nam', 8),
('P0065', 'T02', 'B202', 2, 'Nam', 8),
('P0066', 'T02', 'B203', 2, 'Nam', 8),
('P0067', 'T02', 'B204', 2, 'Nam', 8),
('P0068', 'T02', 'B205', 2, 'Nam', 8),
('P0069', 'T02', 'B206', 2, 'Nam', 8),
('P0070', 'T02', 'B207', 2, 'Nam', 8),
('P0071', 'T02', 'B208', 2, 'Nam', 8),
('P0072', 'T02', 'B209', 2, 'Nam', 8),
('P0073', 'T02', 'B210', 2, 'Nam', 8),
('P0074', 'T02', 'B211', 2, 'Nam', 8),
('P0075', 'T02', 'B212', 2, 'Nam', 8),
('P0076', 'T02', 'B213', 2, 'Nam', 8),
('P0077', 'T02', 'B301', 3, 'Nam', 8),
('P0078', 'T02', 'B302', 3, 'Nam', 8),
('P0079', 'T02', 'B303', 3, 'Nam', 8),
('P0080', 'T02', 'B304', 3, 'Nam', 8),
('P0081', 'T02', 'B305', 3, 'Nam', 8),
('P0082', 'T02', 'B306', 3, 'Nam', 8),
('P0083', 'T02', 'B307', 3, 'Nam', 8),
('P0084', 'T02', 'B308', 3, 'Nam', 8),
('P0085', 'T02', 'B309', 3, 'Nam', 8),
('P0086', 'T02', 'B310', 3, 'Nam', 8),
('P0087', 'T02', 'B311', 3, 'Nam', 8),
('P0088', 'T02', 'B312', 3, 'Nam', 8),
('P0089', 'T02', 'B401', 4, 'Nam', 8),
('P0090', 'T02', 'B402', 4, 'Nam', 8),
('P0091', 'T02', 'B403', 4, 'Nam', 8),
('P0092', 'T02', 'B404', 4, 'Nam', 8),
('P0093', 'T02', 'B405', 4, 'Nam', 8),
('P0094', 'T02', 'B406', 4, 'Nam', 8),
('P0095', 'T02', 'B407', 4, 'Nam', 8),
('P0096', 'T02', 'B408', 4, 'Nam', 8),
('P0097', 'T02', 'B409', 4, 'Nam', 8),
('P0098', 'T02', 'B410', 4, 'Nam', 8),
('P0099', 'T02', 'B411', 4, 'Nam', 8),
('P0100', 'T02', 'B412', 4, 'Nam', 8),
('P0101', 'T03', 'C101', 1, 'Nữ', 8),
('P0102', 'T03', 'C102', 1, 'Nữ', 8),
('P0103', 'T03', 'C103', 1, 'Nữ', 8),
('P0104', 'T03', 'C104', 1, 'Nữ', 8),
('P0105', 'T03', 'C105', 1, 'Nữ', 8),
('P0106', 'T03', 'C106', 1, 'Nữ', 8),
('P0107', 'T03', 'C107', 1, 'Nữ', 8),
('P0108', 'T03', 'C108', 1, 'Nữ', 8),
('P0109', 'T03', 'C109', 1, 'Nữ', 8),
('P0110', 'T03', 'C110', 1, 'Nữ', 8),
('P0111', 'T03', 'C111', 1, 'Nữ', 8),
('P0112', 'T03', 'C112', 1, 'Nữ', 8),
('P0113', 'T03', 'C113', 1, 'Nữ', 8),
('P0114', 'T03', 'C201', 2, 'Nữ', 8),
('P0115', 'T03', 'C202', 2, 'Nữ', 8),
('P0116', 'T03', 'C203', 2, 'Nữ', 8),
('P0117', 'T03', 'C204', 2, 'Nữ', 8),
('P0118', 'T03', 'C205', 2, 'Nữ', 8),
('P0119', 'T03', 'C206', 2, 'Nữ', 8),
('P0120', 'T03', 'C207', 2, 'Nữ', 8),
('P0121', 'T03', 'C208', 2, 'Nữ', 8),
('P0122', 'T03', 'C209', 2, 'Nữ', 8),
('P0123', 'T03', 'C210', 2, 'Nữ', 8),
('P0124', 'T03', 'C211', 2, 'Nữ', 8),
('P0125', 'T03', 'C212', 2, 'Nữ', 8),
('P0126', 'T03', 'C213', 2, 'Nữ', 8),
('P0127', 'T03', 'C301', 3, 'Nữ', 8),
('P0128', 'T03', 'C302', 3, 'Nữ', 8),
('P0129', 'T03', 'C303', 3, 'Nữ', 8),
('P0130', 'T03', 'C304', 3, 'Nữ', 8),
('P0131', 'T03', 'C305', 3, 'Nữ', 8),
('P0132', 'T03', 'C306', 3, 'Nữ', 8),
('P0133', 'T03', 'C307', 3, 'Nữ', 8),
('P0134', 'T03', 'C308', 3, 'Nữ', 8),
('P0135', 'T03', 'C309', 3, 'Nữ', 8),
('P0136', 'T03', 'C310', 3, 'Nữ', 8),
('P0137', 'T03', 'C311', 3, 'Nữ', 8),
('P0138', 'T03', 'C312', 3, 'Nữ', 8),
('P0139', 'T03', 'C401', 4, 'Nữ', 8),
('P0140', 'T03', 'C402', 4, 'Nữ', 8),
('P0141', 'T03', 'C403', 4, 'Nữ', 8),
('P0142', 'T03', 'C404', 4, 'Nữ', 8),
('P0143', 'T03', 'C405', 4, 'Nữ', 8),
('P0144', 'T03', 'C406', 4, 'Nữ', 8),
('P0145', 'T03', 'C407', 4, 'Nữ', 8),
('P0146', 'T03', 'C408', 4, 'Nữ', 8),
('P0147', 'T03', 'C409', 4, 'Nữ', 8),
('P0148', 'T03', 'C410', 4, 'Nữ', 8),
('P0149', 'T03', 'C411', 4, 'Nữ', 8),
('P0150', 'T03', 'C412', 4, 'Nữ', 8),
('P0151', 'T04', 'D101', 1, 'Nữ', 8),
('P0152', 'T04', 'D102', 1, 'Nữ', 8),
('P0153', 'T04', 'D103', 1, 'Nữ', 8),
('P0154', 'T04', 'D104', 1, 'Nữ', 8),
('P0155', 'T04', 'D105', 1, 'Nữ', 8),
('P0156', 'T04', 'D106', 1, 'Nữ', 8),
('P0157', 'T04', 'D107', 1, 'Nữ', 8),
('P0158', 'T04', 'D108', 1, 'Nữ', 8),
('P0159', 'T04', 'D109', 1, 'Nữ', 8),
('P0160', 'T04', 'D110', 1, 'Nữ', 8),
('P0161', 'T04', 'D111', 1, 'Nữ', 8),
('P0162', 'T04', 'D112', 1, 'Nữ', 8),
('P0163', 'T04', 'D113', 1, 'Nữ', 8),
('P0164', 'T04', 'D201', 2, 'Nữ', 8),
('P0165', 'T04', 'D202', 2, 'Nữ', 8),
('P0166', 'T04', 'D203', 2, 'Nữ', 8),
('P0167', 'T04', 'D204', 2, 'Nữ', 8),
('P0168', 'T04', 'D205', 2, 'Nữ', 8),
('P0169', 'T04', 'D206', 2, 'Nữ', 8),
('P0170', 'T04', 'D207', 2, 'Nữ', 8),
('P0171', 'T04', 'D208', 2, 'Nữ', 8),
('P0172', 'T04', 'D209', 2, 'Nữ', 8),
('P0173', 'T04', 'D210', 2, 'Nữ', 8),
('P0174', 'T04', 'D211', 2, 'Nữ', 8),
('P0175', 'T04', 'D212', 2, 'Nữ', 8),
('P0176', 'T04', 'D213', 2, 'Nữ', 8),
('P0177', 'T04', 'D301', 3, 'Nữ', 8),
('P0178', 'T04', 'D302', 3, 'Nữ', 8),
('P0179', 'T04', 'D303', 3, 'Nữ', 8),
('P0180', 'T04', 'D304', 3, 'Nữ', 8),
('P0181', 'T04', 'D305', 3, 'Nữ', 8),
('P0182', 'T04', 'D306', 3, 'Nữ', 8),
('P0183', 'T04', 'D307', 3, 'Nữ', 8),
('P0184', 'T04', 'D308', 3, 'Nữ', 8),
('P0185', 'T04', 'D309', 3, 'Nữ', 8),
('P0186', 'T04', 'D310', 3, 'Nữ', 8),
('P0187', 'T04', 'D311', 3, 'Nữ', 8),
('P0188', 'T04', 'D312', 3, 'Nữ', 8),
('P0189', 'T04', 'D401', 4, 'Nữ', 8),
('P0190', 'T04', 'D402', 4, 'Nữ', 8),
('P0191', 'T04', 'D403', 4, 'Nữ', 8),
('P0192', 'T04', 'D404', 4, 'Nữ', 8),
('P0193', 'T04', 'D405', 4, 'Nữ', 8),
('P0194', 'T04', 'D406', 4, 'Nữ', 8),
('P0195', 'T04', 'D407', 4, 'Nữ', 8),
('P0196', 'T04', 'D408', 4, 'Nữ', 8),
('P0197', 'T04', 'D409', 4, 'Nữ', 8),
('P0198', 'T04', 'D410', 4, 'Nữ', 8),
('P0199', 'T04', 'D411', 4, 'Nữ', 8),
('P0200', 'T04', 'D412', 4, 'Nữ', 8);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sinhvien`
--

DROP TABLE IF EXISTS `sinhvien`;
CREATE TABLE IF NOT EXISTS `sinhvien` (
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` int NOT NULL,
  `MaUuTien` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgaySinh` date NOT NULL,
  `CCCD` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GioiTinh` enum('Nam','Nữ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `QueQuan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SDT` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lop` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NienKhoa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThaiLuuTru` enum('Đang ở','Đã rời đi','Chờ thanh toán') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang ở',
  PRIMARY KEY (`MSSV`),
  UNIQUE KEY `uq_sinhvien_matk` (`MaTK`),
  UNIQUE KEY `uq_sinhvien_cccd` (`CCCD`),
  KEY `idx_sinhvien_mauutien` (`MaUuTien`),
  KEY `idx_sinhvien_trangthai` (`TrangThaiLuuTru`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan`
--

DROP TABLE IF EXISTS `taikhoan`;
CREATE TABLE IF NOT EXISTS `taikhoan` (
  `MaTK` int NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MatKhau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `VaiTro` enum('Sinh viên','Nhân viên quản lý KTX','Nhân viên kế toán','Quản lý KTX') COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` enum('Hoạt động','Khóa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hoạt động',
  PRIMARY KEY (`MaTK`),
  UNIQUE KEY `uq_taikhoan_tendangnhap` (`TenDangNhap`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `taikhoan`
--

INSERT INTO `taikhoan` (`MaTK`, `TenDangNhap`, `MatKhau`, `VaiTro`, `TrangThai`) VALUES
(1, 'sv001', '$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.', 'Sinh viên', 'Hoạt động'),
(3, 'nvql001', '$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.', 'Nhân viên quản lý KTX', 'Hoạt động'),
(5, 'nvkt001', '$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.', 'Nhân viên kế toán', 'Hoạt động'),
(6, 'ql001', '$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.', 'Quản lý KTX', 'Hoạt động');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongbao`
--

DROP TABLE IF EXISTS `thongbao`;
CREATE TABLE IF NOT EXISTS `thongbao` (
  `MaThongBao` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHoaDon` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TieuDe` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoiDung` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianTao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaThongBao`),
  KEY `idx_thongbao_mssv` (`MSSV`),
  KEY `idx_thongbao_mahoadon` (`MaHoaDon`),
  KEY `idx_thongbao_thoigiantao` (`ThoiGianTao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `toanha`
--

DROP TABLE IF EXISTS `toanha`;
CREATE TABLE IF NOT EXISTS `toanha` (
  `MaToa` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenToa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MaToa`),
  UNIQUE KEY `uq_toanha_ten` (`TenToa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `toanha`
--

INSERT INTO `toanha` (`MaToa`, `TenToa`) VALUES
('T01', 'Tòa A'),
('T02', 'Tòa B'),
('T03', 'Tòa C'),
('T04', 'Tòa D');

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `chisodiennuoc`
--
ALTER TABLE `chisodiennuoc`
  ADD CONSTRAINT `fk_chiso_nhanvien` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chiso_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `dondangky`
--
ALTER TABLE `dondangky`
  ADD CONSTRAINT `fk_dondangky_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadontienphong` (`MaHoaDon`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dondangky_hopdong` FOREIGN KEY (`MaHopDong`) REFERENCES `hopdong` (`MaHopDong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dondangky_dienuutien` FOREIGN KEY (`MaUuTien`) REFERENCES `dienuutien` (`MaUuTien`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dondangky_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `hoadondiennuoc`
--
ALTER TABLE `hoadondiennuoc`
  ADD CONSTRAINT `fk_hddn_dongiadien` FOREIGN KEY (`MaDonGiaDien`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_dongianuoc` FOREIGN KEY (`MaDonGiaNuoc`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `hoadondiennuocsinhvien`
--
ALTER TABLE `hoadondiennuocsinhvien`
  ADD CONSTRAINT `fk_hddnsv_hddn` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadondiennuoc` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddnsv_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `hoadontienphong`
--
ALTER TABLE `hoadontienphong`
  ADD CONSTRAINT `fk_hdphong_dongia` FOREIGN KEY (`MaDonGia`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_hocky` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `hopdong`
--
ALTER TABLE `hopdong`
  ADD CONSTRAINT `fk_hopdong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hopdong_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD CONSTRAINT `fk_nhanvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`);

--
-- Ràng buộc cho bảng `phananhsuco`
--
ALTER TABLE `phananhsuco`
  ADD CONSTRAINT `fk_phananh_nhanvien` FOREIGN KEY (`MaNhanVienXuLy`) REFERENCES `nhanvien` (`MaNV`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phananh_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phananh_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `phong`
--
ALTER TABLE `phong`
  ADD CONSTRAINT `fk_phong_toanha` FOREIGN KEY (`MaToa`) REFERENCES `toanha` (`MaToa`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `sinhvien`
--
ALTER TABLE `sinhvien`
  ADD CONSTRAINT `fk_sinhvien_dienuutien` FOREIGN KEY (`MaUuTien`) REFERENCES `dienuutien` (`MaUuTien`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sinhvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`);

--
-- Ràng buộc cho bảng `thongbao`
--
ALTER TABLE `thongbao`
  ADD CONSTRAINT `fk_thongbao_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_thongbao_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
