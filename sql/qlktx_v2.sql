-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 05, 2026 lúc 08:18 AM
-- Phiên bản máy phục vụ: 8.4.7
-- Phiên bản PHP: 8.3.28

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
('UT00', 'Không thuộc diện ưu tiên', 0);

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
  PRIMARY KEY (`MaDonDangKy`),
  KEY `idx_dondangky_mssv` (`MSSV`),
  KEY `idx_dondangky_trangthai` (`TrangThai`),
  KEY `idx_dondangky_maphong` (`MaPhong`),
  KEY `idx_dondangky_mauutien` (`MaUuTien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `MaTK` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
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

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sinhvien`
--

DROP TABLE IF EXISTS `sinhvien`;
CREATE TABLE IF NOT EXISTS `sinhvien` (
  `MSSV` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `TrangThaiLuuTru` enum('Đang ở','Đã rời đi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang ở',
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
  `MaTK` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenDangNhap` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MatKhau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `VaiTro` enum('Sinh viên','Nhân viên quản lý KTX','Nhân viên kế toán','Quản lý KTX') COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` enum('Hoạt động','Khóa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hoạt động',
  PRIMARY KEY (`MaTK`),
  UNIQUE KEY `uq_taikhoan_tendangnhap` (`TenDangNhap`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD CONSTRAINT `fk_nhanvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE RESTRICT ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_sinhvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ràng buộc cho bảng `thongbao`
--
ALTER TABLE `thongbao`
  ADD CONSTRAINT `fk_thongbao_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_thongbao_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
