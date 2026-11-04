-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: localhost    Database: qlktx_v2
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `qlktx_v2`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `qlktx_v2` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `qlktx_v2`;

--
-- Table structure for table `chisodiennuoc`
--

DROP TABLE IF EXISTS `chisodiennuoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chisodiennuoc` (
  `MaChiSo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaNV` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ChiSoDien` int NOT NULL,
  `ChiSoNuoc` int NOT NULL,
  `Thang` tinyint unsigned NOT NULL,
  `Nam` smallint unsigned NOT NULL,
  `ThoiGianGhi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaChiSo`),
  UNIQUE KEY `uq_chiso_phong_thang_nam` (`MaPhong`,`Thang`,`Nam`),
  KEY `idx_chiso_manv` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chisodiennuoc`
--

LOCK TABLES `chisodiennuoc` WRITE;
/*!40000 ALTER TABLE `chisodiennuoc` DISABLE KEYS */;
INSERT INTO `chisodiennuoc` VALUES ('CS261001','P0001','NV00000003',380,75,10,2026,'2026-11-01 13:19:46'),('CS261002','P0002','NV00000003',290,62,10,2026,'2026-11-01 13:19:46'),('CS261003','P0101','NV00000003',310,58,10,2026,'2026-11-01 13:19:46'),('CS261004','P0102','NV00000003',275,54,10,2026,'2026-11-01 13:19:46');
/*!40000 ALTER TABLE `chisodiennuoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dienuutien`
--

DROP TABLE IF EXISTS `dienuutien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dienuutien` (
  `MaUuTien` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenDienUuTien` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MucUuTien` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`MaUuTien`),
  UNIQUE KEY `uq_dienuutien_ten` (`TenDienUuTien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dienuutien`
--

LOCK TABLES `dienuutien` WRITE;
/*!40000 ALTER TABLE `dienuutien` DISABLE KEYS */;
INSERT INTO `dienuutien` VALUES ('UT00','Không có',0),('UT01','Hộ nghèo / Cận nghèo',2),('UT02','Con thương binh / Liệt sĩ',4),('UT03','Vùng sâu vùng xa / Hải đảo',3);
/*!40000 ALTER TABLE `dienuutien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dondangky`
--

DROP TABLE IF EXISTS `dondangky`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dondangky` (
  `MaDonDangKy` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `HoTen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgaySinh` date NOT NULL,
  `CCCD` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `GioiTinh` enum('Nam','Nữ') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `QueQuan` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SDT` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lop` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NienKhoa` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaUuTien` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `FileMinhChung` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianGui` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TrangThai` enum('Chờ duyệt','Đã duyệt','Từ chối') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ duyệt',
  `LyDoTuChoi` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHoaDon` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHopDong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`MaDonDangKy`),
  UNIQUE KEY `uq_dondangky_hoadon` (`MaHoaDon`),
  UNIQUE KEY `uq_dondangky_hopdong` (`MaHopDong`),
  KEY `idx_dondangky_mssv` (`MSSV`),
  KEY `idx_dondangky_trangthai` (`TrangThai`),
  KEY `idx_dondangky_maphong` (`MaPhong`),
  KEY `idx_dondangky_mauutien` (`MaUuTien`),
  CONSTRAINT `fk_dondangky_dienuutien` FOREIGN KEY (`MaUuTien`) REFERENCES `dienuutien` (`MaUuTien`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dondangky_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadontienphong` (`MaHoaDon`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dondangky_hopdong` FOREIGN KEY (`MaHopDong`) REFERENCES `hopdong` (`MaHopDong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dondangky_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dondangky`
--

LOCK TABLES `dondangky` WRITE;
/*!40000 ALTER TABLE `dondangky` DISABLE KEYS */;
INSERT INTO `dondangky` VALUES ('091912584B','KTPM2411022','Phan Minh Trọng','2006-09-26','093206010833','Nam','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','UT00',NULL,'P0001','2026-10-20 07:45:34','Đã duyệt',NULL,'43224990B492','1D6572CA54'),('181B6784AF','KTPM2411050','Nguyễn Hoàng Long','2006-03-15','079206001122','Nam','Cần Thơ','0912345671','nhlong.ktpm@ctuet.edu.vn','KTPM01-K24','2024-2028','UT01',NULL,'P0002','2026-11-01 13:19:45','Chờ duyệt',NULL,NULL,NULL),('41E119DC87','KTPM2411040','Phan Minh Trọng','2020-07-02','093206010838','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','UT00','storage/registration/2123978f89e66762cd3f6038b255e266522c1a94d2637b3d.jpg','P0039','2026-10-08 20:04:20','Đã duyệt',NULL,'880F734B36EB','0AF880F5EF'),('46253B50F6','KTPM2411057','Hoàng Thu Trang','2006-12-12','079306008899','Nữ','Kiên Giang','0912345678','httrang.ktpm@ctuet.edu.vn','KTPM02-K24','2024-2028','UT00',NULL,'P0101','2026-11-01 13:19:45','Đã duyệt',NULL,'48DCB4AA1421','16FDF09C64'),('5AFBD6B292','KTPM2411052','Lê Quốc Bảo','2006-11-05','079206003344','Nam','An Giang','0912345673','lqbao.ktpm@ctuet.edu.vn','KTPM01-K24','2024-2028','UT00',NULL,'P0003','2026-11-01 13:19:45','Chờ duyệt',NULL,NULL,NULL),('6B543826B3','KTPM2411047','Phan Minh Trọng','2006-09-26','093206010838','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','UT01','storage/registration/8ed74a8f1530ea234395e5d6a1c206ffd52bcdbc56c1db47.png','P0058','2026-10-08 03:25:44','Từ chối','Đơn không phù hợp',NULL,NULL),('76671E47AF','KTPM2411444','Trần Minh Quân','2006-06-26','093206010222','Nam','Cần Thơ','0797439616','vothuy14091972@gmail.com','KTPM2411','2024-2028','UT00',NULL,'P0077','2026-10-20 08:45:31','Từ chối','Không đủ điều kiện lưu trú',NULL,NULL),('7828FABEC1','KTPM2411056','Bùi Minh Khang','2006-01-25','079206007788','Nam','Cà Mau','0912345677','bmkhang.ktpm@ctuet.edu.vn','KTPM01-K24','2024-2028','UT02',NULL,'P0001','2026-11-01 13:19:45','Đã duyệt',NULL,'806BC81C435E','6B2E16BF44'),('A1134B4BD7','KTPM8385772','Trần Minh Quân','2005-08-17','093206010811','Nữ','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','UT00',NULL,'P0101','2026-10-25 00:22:05','Đã duyệt',NULL,'756032F08505',NULL),('C0BD30F8C7','KTPM2411033','Phan Minh Trọng','2006-09-26','093206010822','Nam','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','UT00',NULL,'P0001','2026-10-20 08:42:54','Đã duyệt',NULL,'1EA2081D6F6B','3C2F6A184D'),('D671CECC7D','KTPM2413587','Phan Minh Trọng','2005-06-26','093292827758','Nam','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','UT01','storage/registration/8263f0a973a75ad8736781167a4e31650ef1c7db5daf5a97.png','P0014','2026-10-22 18:19:41','Đã duyệt',NULL,'BCEA096A886B','41B4A1819A'),('D7D47E064E','KTPM2411051','Trần Thị Mai Phương','2006-07-22','079306002233','Nữ','Vĩnh Long','0912345672','ttmphuong.ktpm@ctuet.edu.vn','KTPM02-K24','2024-2028','UT02',NULL,'P0102','2026-11-01 13:19:45','Chờ duyệt',NULL,NULL,NULL),('DB48B4989D','KTPM2411736','Phan Minh Trọng','2007-10-28','093206453535','Nữ','Cần Thơ','0797439616','pmtrongktpm2411047@student.ctuet.edu.vn','KTPM2411','2024-2028','UT02','storage/registration/aedce54b11ff21e4890bd02cb298a13cfdef696c22490d03.jpg','P0101','2026-10-22 17:58:37','Đã duyệt',NULL,'1801B3BFD58F',NULL),('E292640DEA','KTPM2411055','Võ Thị Ngọc Ánh','2006-08-30','079306006677','Nữ','Bạc Liêu','0912345676','vtnanh.ktpm@ctuet.edu.vn','KTPM02-K24','2024-2028','UT01',NULL,'P0102','2026-11-01 13:19:45','Đã duyệt',NULL,'FCB9799265CE',NULL),('EA7B60BB81','KTPM2411053','Phạm Thúy Hằng','2006-09-18','079306004455','Nữ','Đồng Tháp','0912345674','pthang.ktpm@ctuet.edu.vn','KTPM02-K24','2024-2028','UT03',NULL,'P0103','2026-11-01 13:19:45','Chờ duyệt',NULL,NULL,NULL),('FF3CB652F5','KTPM2411054','Đỗ Quang Huy','2006-04-10','079206005566','Nam','Hậu Giang','0912345675','dqhuy.ktpm@ctuet.edu.vn','KTPM01-K24','2024-2028','UT00',NULL,'P0002','2026-11-01 13:19:45','Đã duyệt',NULL,'327F466FBE63',NULL),('FF8A9D65CA','KTPM2411049','Phan Minh Trọng','2006-03-26','053590350350','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','UT00',NULL,'P0038','2026-10-08 15:31:38','Đã duyệt',NULL,'0B676F285B31','50DFEBB6BE');
/*!40000 ALTER TABLE `dondangky` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dongia`
--

DROP TABLE IF EXISTS `dongia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dongia` (
  `MaDonGia` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `LoaiDonGia` enum('Tiền phòng','Điện','Nước') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `GiaTri` decimal(12,2) NOT NULL,
  `DonViTinh` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayApDung` date NOT NULL,
  PRIMARY KEY (`MaDonGia`),
  UNIQUE KEY `uq_dongia_loai_ngay` (`LoaiDonGia`,`NgayApDung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dongia`
--

LOCK TABLES `dongia` WRITE;
/*!40000 ALTER TABLE `dongia` DISABLE KEYS */;
INSERT INTO `dongia` VALUES ('ROOM200K','Tiền phòng',200000.00,'đồng/tháng','2026-10-14');
/*!40000 ALTER TABLE `dongia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailthongbao`
--

DROP TABLE IF EXISTS `emailthongbao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emailthongbao` (
  `MaDonDangKy` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NguoiNhan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TieuDe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoiDung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` enum('Chờ gửi','Đã gửi','Lỗi','Không có email') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ gửi',
  `SoLanThu` int unsigned NOT NULL DEFAULT '0',
  `ThoiGianTao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ThoiGianGui` datetime DEFAULT NULL,
  PRIMARY KEY (`MaDonDangKy`),
  KEY `idx_emailthongbao_trangthai` (`TrangThai`,`ThoiGianTao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailthongbao`
--

LOCK TABLES `emailthongbao` WRITE;
/*!40000 ALTER TABLE `emailthongbao` DISABLE KEYS */;
INSERT INTO `emailthongbao` VALUES ('091912584B','vot750422@gmail.com','Kanto KTX - Đơn đăng ký được duyệt - 091912584B','Chào Phan Minh Trọng,\n\nĐơn đăng ký lưu trú 091912584B (MSSV: KTPM2411022) đã được duyệt.\n\nTên đăng nhập: KTPM2411022\nMã hóa đơn tiền phòng: 43224990B492\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Đã gửi',1,'2026-10-20 08:41:22','2026-10-20 08:41:30'),('095531F1DE','bmkhang.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - 095531F1DE','Chào Bùi Minh Khang,\n\nĐơn đăng ký lưu trú 095531F1DE (MSSV: KTPM2411056) đã được duyệt.\n\nTên đăng nhập: KTPM2411056\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 3FE1504CA0D1\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:18:34',NULL),('22EEE881EC','httrang.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - 22EEE881EC','Chào Hoàng Thu Trang,\n\nĐơn đăng ký lưu trú 22EEE881EC (MSSV: KTPM2411057) đã được duyệt.\n\nTên đăng nhập: KTPM2411057\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 85BC24336B6B\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:18:34',NULL),('3C1B357DC6','dqhuy.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - 3C1B357DC6','Chào Đỗ Quang Huy,\n\nĐơn đăng ký lưu trú 3C1B357DC6 (MSSV: KTPM2411054) đã được duyệt.\n\nTên đăng nhập: KTPM2411054\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 525AC586A979\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:18:34',NULL),('46253B50F6','httrang.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - 46253B50F6','Chào Hoàng Thu Trang,\n\nĐơn đăng ký lưu trú 46253B50F6 (MSSV: KTPM2411057) đã được duyệt.\n\nTên đăng nhập: KTPM2411057\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 48DCB4AA1421\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:19:46',NULL),('76671E47AF','vothuy14091972@gmail.com','Kanto KTX - Đơn đăng ký bị từ chối - 76671E47AF','Chào Trần Minh Quân,\n\nĐơn đăng ký lưu trú 76671E47AF (MSSV: KTPM2411444) đã bị từ chối.\n\nLý do từ chối: Không đủ điều kiện lưu trú\nVui lòng kiểm tra, bổ sung thông tin trước khi đăng ký lại hoặc liên hệ bộ phận quản lý KTX để được hỗ trợ.\n\nBan quản lý Ký túc xá Đại học Kanto','Đã gửi',1,'2026-10-20 08:45:54','2026-10-20 08:46:02'),('7828FABEC1','bmkhang.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - 7828FABEC1','Chào Bùi Minh Khang,\n\nĐơn đăng ký lưu trú 7828FABEC1 (MSSV: KTPM2411056) đã được duyệt.\n\nTên đăng nhập: KTPM2411056\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 806BC81C435E\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:19:45',NULL),('8F8B4F749C','vtnanh.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - 8F8B4F749C','Chào Võ Thị Ngọc Ánh,\n\nĐơn đăng ký lưu trú 8F8B4F749C (MSSV: KTPM2411055) đã được duyệt.\n\nTên đăng nhập: KTPM2411055\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 3F631EFC9ADF\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:18:34',NULL),('A1134B4BD7','vot750422@gmail.com','Kanto KTX - Đơn đăng ký được duyệt - A1134B4BD7','Chào Trần Minh Quân,\n\nĐơn đăng ký lưu trú A1134B4BD7 (MSSV: KTPM8385772) đã được duyệt.\n\nTên đăng nhập: KTPM8385772\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 756032F08505\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Đã gửi',1,'2026-10-25 00:22:23','2026-10-25 00:22:28'),('C0BD30F8C7','vot750422@gmail.com','Kanto KTX - Đơn đăng ký được duyệt - C0BD30F8C7','Chào Phan Minh Trọng,\n\nĐơn đăng ký lưu trú C0BD30F8C7 (MSSV: KTPM2411033) đã được duyệt.\n\nTên đăng nhập: KTPM2411033\nMã hóa đơn tiền phòng: 1EA2081D6F6B\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Đã gửi',1,'2026-10-20 08:43:14','2026-10-20 08:43:21'),('D671CECC7D','vot750422@gmail.com','Kanto KTX - Đơn đăng ký được duyệt - D671CECC7D','Chào Phan Minh Trọng,\n\nĐơn đăng ký lưu trú D671CECC7D (MSSV: KTPM2413587) đã được duyệt.\n\nTên đăng nhập: KTPM2413587\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: BCEA096A886B\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Đã gửi',1,'2026-10-22 18:20:25','2026-10-22 18:20:30'),('DB48B4989D','pmtrongktpm2411047@student.ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - DB48B4989D','Chào Phan Minh Trọng,\n\nĐơn đăng ký lưu trú DB48B4989D (MSSV: KTPM2411736) đã được duyệt.\n\nTên đăng nhập: KTPM2411736\nMã hóa đơn tiền phòng: 1801B3BFD58F\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Đã gửi',1,'2026-10-22 17:59:36','2026-10-22 17:59:41'),('E292640DEA','vtnanh.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - E292640DEA','Chào Võ Thị Ngọc Ánh,\n\nĐơn đăng ký lưu trú E292640DEA (MSSV: KTPM2411055) đã được duyệt.\n\nTên đăng nhập: KTPM2411055\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: FCB9799265CE\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:19:45',NULL),('FF3CB652F5','dqhuy.ktpm@ctuet.edu.vn','Kanto KTX - Đơn đăng ký được duyệt - FF3CB652F5','Chào Đỗ Quang Huy,\n\nĐơn đăng ký lưu trú FF3CB652F5 (MSSV: KTPM2411054) đã được duyệt.\n\nTên đăng nhập: KTPM2411054\nMật khẩu mặc định: 1\nMã hóa đơn tiền phòng: 327F466FBE63\nChỗ ở được giữ trong thời gian chờ thanh toán. Vui lòng đăng nhập để thực hiện thanh toán hoặc liên hệ bộ phận quản lý KTX để nhận hướng dẫn đăng nhập và thanh toán. Hợp đồng được lập sau khi xác nhận thanh toán.\n\nBan quản lý Ký túc xá Đại học Kanto','Chờ gửi',0,'2026-11-01 13:19:45',NULL);
/*!40000 ALTER TABLE `emailthongbao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoadon`
--

DROP TABLE IF EXISTS `hoadon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoadon` (
  `MaHoaDon` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `LoaiHoaDon` enum('Tiền phòng','Điện nước') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TongTien` decimal(12,2) NOT NULL,
  `ThoiGianLap` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TrangThai` enum('Chưa thu','Đã thu') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa thu',
  `ThoiGianThanhToan` datetime DEFAULT NULL,
  PRIMARY KEY (`MaHoaDon`),
  KEY `idx_hoadon_loai` (`LoaiHoaDon`),
  KEY `idx_hoadon_trangthai` (`TrangThai`),
  KEY `idx_hoadon_thoigianlap` (`ThoiGianLap`),
  KEY `idx_hoadon_thoigianthanhtoan` (`ThoiGianThanhToan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoadon`
--

LOCK TABLES `hoadon` WRITE;
/*!40000 ALTER TABLE `hoadon` DISABLE KEYS */;
INSERT INTO `hoadon` VALUES ('0B676F285B31','Tiền phòng',903226.00,'2026-10-15 18:45:22','Đã thu','2026-10-15 18:54:12'),('1801B3BFD58F','Tiền phòng',903226.00,'2026-10-22 17:59:36','Chưa thu',NULL),('1EA2081D6F6B','Tiền phòng',903226.00,'2026-10-20 08:43:14','Đã thu','2026-10-20 08:44:02'),('327F466FBE63','Tiền phòng',903226.00,'2026-11-01 13:19:45','Chưa thu',NULL),('43224990B492','Tiền phòng',903226.00,'2026-10-20 08:41:22','Đã thu','2026-10-28 12:56:11'),('48DCB4AA1421','Tiền phòng',903226.00,'2026-11-01 13:19:46','Đã thu','2026-11-01 13:19:46'),('756032F08505','Tiền phòng',903226.00,'2026-10-25 00:22:23','Chưa thu',NULL),('806BC81C435E','Tiền phòng',903226.00,'2026-11-01 13:19:45','Đã thu','2026-11-01 13:19:45'),('880F734B36EB','Tiền phòng',903226.00,'2026-10-14 23:14:07','Đã thu','2026-10-15 02:46:51'),('BCEA096A886B','Tiền phòng',903226.00,'2026-10-22 18:20:25','Đã thu','2026-10-22 18:23:05'),('FCB9799265CE','Tiền phòng',903226.00,'2026-11-01 13:19:45','Chưa thu',NULL);
/*!40000 ALTER TABLE `hoadon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoadondiennuoc`
--

DROP TABLE IF EXISTS `hoadondiennuoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoadondiennuoc` (
  `MaHoaDon` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Thang` tinyint unsigned NOT NULL,
  `Nam` smallint unsigned NOT NULL,
  `DienTieuThu` int NOT NULL,
  `NuocTieuThu` int NOT NULL,
  `TienDien` decimal(12,2) NOT NULL,
  `TienNuoc` decimal(12,2) NOT NULL,
  `MaDonGiaDien` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaDonGiaNuoc` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MaHoaDon`),
  UNIQUE KEY `uq_hddn_phong_thang_nam` (`MaPhong`,`Thang`,`Nam`),
  KEY `idx_hddn_dongiadien` (`MaDonGiaDien`),
  KEY `idx_hddn_dongianuoc` (`MaDonGiaNuoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoadondiennuoc`
--

LOCK TABLES `hoadondiennuoc` WRITE;
/*!40000 ALTER TABLE `hoadondiennuoc` DISABLE KEYS */;
/*!40000 ALTER TABLE `hoadondiennuoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoadondiennuocsinhvien`
--

DROP TABLE IF EXISTS `hoadondiennuocsinhvien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoadondiennuocsinhvien` (
  `MaHoaDon` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SoTienPhaiThu` decimal(12,2) NOT NULL DEFAULT '0.00',
  `TrangThai` enum('Chưa thu','Đã thu') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa thu',
  `ThoiGianThanhToan` datetime DEFAULT NULL,
  PRIMARY KEY (`MaHoaDon`,`MSSV`),
  KEY `idx_hddnsv_mssv` (`MSSV`),
  CONSTRAINT `fk_hddnsv_hddn` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadondiennuoc` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hddnsv_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoadondiennuocsinhvien`
--

LOCK TABLES `hoadondiennuocsinhvien` WRITE;
/*!40000 ALTER TABLE `hoadondiennuocsinhvien` DISABLE KEYS */;
/*!40000 ALTER TABLE `hoadondiennuocsinhvien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoadontienphong`
--

DROP TABLE IF EXISTS `hoadontienphong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoadontienphong` (
  `MaHoaDon` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHocKy` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaDonGia` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MaHoaDon`),
  UNIQUE KEY `uq_hdphong_sv_hocky` (`MSSV`,`MaHocKy`),
  KEY `idx_hdphong_maphong` (`MaPhong`),
  KEY `idx_hdphong_mahocky` (`MaHocKy`),
  KEY `idx_hdphong_madongia` (`MaDonGia`),
  CONSTRAINT `fk_hdphong_dongia` FOREIGN KEY (`MaDonGia`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_hdphong_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hdphong_hocky` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_hdphong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_hdphong_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoadontienphong`
--

LOCK TABLES `hoadontienphong` WRITE;
/*!40000 ALTER TABLE `hoadontienphong` DISABLE KEYS */;
INSERT INTO `hoadontienphong` VALUES ('0B676F285B31','KTPM2411049','P0038','HK22627','ROOM200K'),('1801B3BFD58F','KTPM2411736','P0101','HK22627','ROOM200K'),('1EA2081D6F6B','KTPM2411033','P0001','HK22627','ROOM200K'),('327F466FBE63','KTPM2411054','P0002','HK22627','ROOM200K'),('43224990B492','KTPM2411022','P0001','HK22627','ROOM200K'),('48DCB4AA1421','KTPM2411057','P0101','HK22627','ROOM200K'),('756032F08505','KTPM8385772','P0101','HK22627','ROOM200K'),('806BC81C435E','KTPM2411056','P0001','HK22627','ROOM200K'),('880F734B36EB','KTPM2411040','P0039','HK22627','ROOM200K'),('BCEA096A886B','KTPM2413587','P0014','HK22627','ROOM200K'),('FCB9799265CE','KTPM2411055','P0102','HK22627','ROOM200K');
/*!40000 ALTER TABLE `hoadontienphong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hocky`
--

DROP TABLE IF EXISTS `hocky`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hocky` (
  `MaHocKy` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenHocKy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NamHoc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  PRIMARY KEY (`MaHocKy`),
  UNIQUE KEY `uq_hocky_ten_namhoc` (`TenHocKy`,`NamHoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hocky`
--

LOCK TABLES `hocky` WRITE;
/*!40000 ALTER TABLE `hocky` DISABLE KEYS */;
INSERT INTO `hocky` VALUES ('HK12627','Kỳ 1','2026-2027','2026-09-01','2027-01-15'),('HK12728','Kỳ 1','2027-2028','2027-09-01','2028-01-15'),('HK22627','Kỳ 2','2026-2027','2027-01-16','2027-05-31'),('HK22728','Kỳ 2','2027-2028','2028-01-16','2028-05-31'),('HK32627','Kỳ 3','2026-2027','2027-06-01','2027-08-31'),('HK32728','Kỳ 3','2027-2028','2028-06-01','2028-08-31');
/*!40000 ALTER TABLE `hocky` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hopdong`
--

DROP TABLE IF EXISTS `hopdong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hopdong` (
  `MaHopDong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `TrangThai` enum('Còn hạn','Hết hạn','Đã thanh lý') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Còn hạn',
  `MaHocKy` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`MaHopDong`),
  KEY `idx_hopdong_mssv` (`MSSV`),
  KEY `idx_hopdong_maphong` (`MaPhong`),
  KEY `idx_hopdong_trangthai` (`TrangThai`),
  KEY `fk_hopdong_hocky` (`MaHocKy`),
  CONSTRAINT `fk_hopdong_hocky` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hopdong`
--

LOCK TABLES `hopdong` WRITE;
/*!40000 ALTER TABLE `hopdong` DISABLE KEYS */;
INSERT INTO `hopdong` VALUES ('0AF880F5EF','KTPM2411040','P0039','2027-01-16','2027-05-31','Hết hạn',NULL),('16FDF09C64','KTPM2411057','P0101','2026-11-01','2027-05-31','Còn hạn',NULL),('1D6572CA54','KTPM2411022','P0001','2027-01-16','2027-05-31','Hết hạn',NULL),('3C2F6A184D','KTPM2411033','P0001','2027-01-16','2027-05-31','Hết hạn',NULL),('41B4A1819A','KTPM2413587','P0014','2027-01-16','2027-05-31','Đã thanh lý',NULL),('50DFEBB6BE','KTPM2411049','P0038','2027-01-16','2027-05-31','Đã thanh lý',NULL),('6B2E16BF44','KTPM2411056','P0001','2026-11-01','2027-05-31','Còn hạn',NULL);
/*!40000 ALTER TABLE `hopdong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nhanvien`
--

DROP TABLE IF EXISTS `nhanvien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nhanvien` (
  `MaNV` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` int NOT NULL,
  `HoTen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SDT` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TrangThaiLamViec` enum('Đang làm','Nghỉ việc') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang làm',
  PRIMARY KEY (`MaNV`),
  UNIQUE KEY `uq_nhanvien_matk` (`MaTK`),
  UNIQUE KEY `uq_nhanvien_email` (`Email`),
  CONSTRAINT `fk_nhanvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nhanvien`
--

LOCK TABLES `nhanvien` WRITE;
/*!40000 ALTER TABLE `nhanvien` DISABLE KEYS */;
INSERT INTO `nhanvien` VALUES ('NV00000003',3,'nvql001',NULL,NULL,'Đang làm');
/*!40000 ALTER TABLE `nhanvien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nhatkyhethong`
--

DROP TABLE IF EXISTS `nhatkyhethong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nhatkyhethong` (
  `MaNhatKy` int NOT NULL AUTO_INCREMENT,
  `ThoiGian` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LoaiNhatKy` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoiDung` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `MucDo` enum('INFO','WARNING','ERROR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INFO',
  PRIMARY KEY (`MaNhatKy`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nhatkyhethong`
--

LOCK TABLES `nhatkyhethong` WRITE;
/*!40000 ALTER TABLE `nhatkyhethong` DISABLE KEYS */;
INSERT INTO `nhatkyhethong` VALUES (1,'2026-11-01 10:19:24','RENEWAL','Gia hạn học kỳ HK12627: 0 gia hạn thành công, 0 tài khoản bị khóa, 3 bỏ qua.','INFO'),(2,'2026-11-01 10:19:26','RENEWAL','Gia hạn học kỳ HK12627: 0 gia hạn thành công, 0 tài khoản bị khóa, 0 bỏ qua.','INFO');
/*!40000 ALTER TABLE `nhatkyhethong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phananhsuco`
--

DROP TABLE IF EXISTS `phananhsuco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phananhsuco` (
  `MaPhanAnh` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaNhanVienXuLy` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LoaiSuCo` enum('Điện','Nước','Nội thất','Khác') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MoTa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianGui` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ChiPhiSuaChua` decimal(12,2) DEFAULT NULL,
  `TrangThai` enum('Chờ xử lý','Đã xử lý') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ xử lý',
  PRIMARY KEY (`MaPhanAnh`),
  KEY `idx_phananh_mssv` (`MSSV`),
  KEY `idx_phananh_maphong` (`MaPhong`),
  KEY `idx_phananh_nhanvien` (`MaNhanVienXuLy`),
  KEY `idx_phananh_trangthai` (`TrangThai`),
  KEY `idx_phananh_thoigiangui` (`ThoiGianGui`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phananhsuco`
--

LOCK TABLES `phananhsuco` WRITE;
/*!40000 ALTER TABLE `phananhsuco` DISABLE KEYS */;
INSERT INTO `phananhsuco` VALUES ('778D3E85F3','KTPM2411049','P0038','NV00000003','Nước','rò rỉ nước','2026-10-29 09:52:29',200000.00,'Đã xử lý'),('E6980AA7E4','KTPM2411022','P0001','NV00000003','Khác','Cửa bị hư chốt gài','2026-10-28 12:57:02',200000.00,'Đã xử lý'),('PA2610001','KTPM2411056','P0001',NULL,'Nước','Vòi nước bồn rửa mặt bị rò rỉ nước liên tục, đề nghị bộ phận kỹ thuật hỗ trợ thay đệm cao su.','2026-11-01 13:19:46',NULL,'Chờ xử lý'),('PA2610002','KTPM2411057','P0101','NV00000003','Điện','Bóng đèn tuýp LED bàn tự học phòng C101 bị chớp nháy gây mỏi mắt.','2026-11-01 13:19:46',45000.00,'Đã xử lý'),('PA2610003','KTPM2411054','P0002',NULL,'Nội thất','Khóa cửa phòng A102 bị kẹt ổ khóa khi mở từ bên ngoài.','2026-11-01 13:19:46',NULL,'Chờ xử lý');
/*!40000 ALTER TABLE `phananhsuco` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phienthanhtoan`
--

DROP TABLE IF EXISTS `phienthanhtoan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phienthanhtoan` (
  `MaPhien` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `MaHoaDon` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` int NOT NULL,
  `SoTien` decimal(12,2) NOT NULL,
  `TaoLuc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `HetHanLuc` datetime NOT NULL,
  `DaDungLuc` datetime DEFAULT NULL,
  PRIMARY KEY (`MaPhien`),
  KEY `idx_phienthanhtoan_hoadon` (`MaHoaDon`),
  KEY `idx_phienthanhtoan_hethan` (`HetHanLuc`),
  KEY `fk_phienthanhtoan_taikhoan` (`MaTK`),
  CONSTRAINT `fk_phienthanhtoan_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_phienthanhtoan_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phienthanhtoan`
--

LOCK TABLES `phienthanhtoan` WRITE;
/*!40000 ALTER TABLE `phienthanhtoan` DISABLE KEYS */;
INSERT INTO `phienthanhtoan` VALUES ('102fd54c9017eb2e2cba13841ed543dac8e4cabd2b914c3afd5e12700554a2f4','756032F08505',13,903226.00,'2026-10-25 04:34:34','2026-10-25 04:49:34',NULL),('140cbbde1daa127858b33e3fd911435c0804fb92934fb0eab194b67fc2007956','327F466FBE63',18,903226.00,'2026-11-01 13:21:11','2026-11-01 13:26:11',NULL),('20e700cde412cd79c8661738bfe6a86a770b93b593ddfa74566d7c97a2adba53','756032F08505',13,903226.00,'2026-10-25 04:34:34','2026-10-25 04:49:34',NULL),('3197cda0f4dbb3087a770e2e964237f97cdd936a17a1ce5b2a3e0302dbd75d61','756032F08505',13,903226.00,'2026-10-25 04:35:37','2026-10-25 04:50:37',NULL),('37214112d90a9d72fd493ab4745fb0e72b8f2ec72e8f7aa3a4416271efa92906','756032F08505',13,903226.00,'2026-10-25 00:40:39','2026-10-25 00:55:39',NULL),('40781f0cd7e460bf0452c63b41e235599096eabc0f4646dd7ef92ce8394e8bfb','756032F08505',13,903226.00,'2026-10-25 04:34:48','2026-10-25 04:49:48',NULL),('41a7d32a11a7000b52b09dbd5c3bf43db49f3045905aca579d2d92239cc27ca2','756032F08505',13,903226.00,'2026-10-25 04:24:37','2026-10-25 04:39:37',NULL),('49ae55f6a65222c97c5694f4513969d09fe4341e5b559a0b1d20e00436e8293f','756032F08505',13,903226.00,'2026-10-25 00:40:43','2026-10-25 00:55:43',NULL),('5828891439fdf624080418e624ddf2542686b5356bce24abcb9441af1c507bba','43224990B492',9,903226.00,'2026-10-28 12:54:36','2026-10-28 12:59:36',NULL),('7695719e84f38515375cfe672660b40c6955e55c9a3a67b4ca5fbb97a9428c86','756032F08505',13,903226.00,'2026-10-25 04:34:40','2026-10-25 04:49:40',NULL),('83a665733bf122147ce43270f37be16476925c6a694192372f227d54a85b579b','756032F08505',13,903226.00,'2026-10-25 04:48:07','2026-10-25 04:53:07',NULL),('94008eeeed3d6df301a73f2c0bcfc59693cb13af627555cd7028bbaa31381dfa','756032F08505',13,903226.00,'2026-10-25 04:34:36','2026-10-25 04:49:36',NULL),('9cd9a13cf5f59fe325b370e93c88fea029ea89d26007c1597a9a2e2d66a31e98','756032F08505',13,903226.00,'2026-10-25 04:34:37','2026-10-25 04:49:37',NULL),('bc72997aff1681d017ec2a8912458bd3386c5b9a6be85bccaa9c85bf7efd2299','756032F08505',13,903226.00,'2026-10-25 04:34:34','2026-10-25 04:49:34',NULL),('bea8a4b03ea134f4f9dcf42de1ac20d6797f35065c12df2be836bee4fb2d28c7','756032F08505',13,903226.00,'2026-10-25 04:34:33','2026-10-25 04:49:33',NULL),('c3c60425b7fa517d9d53d2aa655f548380c64837c14cab7c691a72949fb77c75','756032F08505',13,903226.00,'2026-10-25 04:35:47','2026-10-25 04:50:47',NULL);
/*!40000 ALTER TABLE `phienthanhtoan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phong`
--

DROP TABLE IF EXISTS `phong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phong` (
  `MaPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaToa` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SoPhong` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tang` tinyint unsigned NOT NULL,
  `GioiTinhPhong` enum('Nam','Nữ') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SucChua` int NOT NULL,
  PRIMARY KEY (`MaPhong`),
  UNIQUE KEY `uq_phong_toa_sophong` (`MaToa`,`SoPhong`),
  KEY `idx_phong_matoa` (`MaToa`),
  KEY `idx_phong_toa_tang` (`MaToa`,`Tang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phong`
--

LOCK TABLES `phong` WRITE;
/*!40000 ALTER TABLE `phong` DISABLE KEYS */;
INSERT INTO `phong` VALUES ('P0001','T01','A101',1,'Nam',8),('P0002','T01','A102',1,'Nam',8),('P0003','T01','A103',1,'Nam',8),('P0004','T01','A104',1,'Nam',8),('P0005','T01','A105',1,'Nam',8),('P0006','T01','A106',1,'Nam',8),('P0007','T01','A107',1,'Nam',8),('P0008','T01','A108',1,'Nam',8),('P0009','T01','A109',1,'Nam',8),('P0010','T01','A110',1,'Nam',8),('P0011','T01','A111',1,'Nam',8),('P0012','T01','A112',1,'Nam',8),('P0013','T01','A113',1,'Nam',8),('P0014','T01','A201',2,'Nam',8),('P0015','T01','A202',2,'Nam',8),('P0016','T01','A203',2,'Nam',8),('P0017','T01','A204',2,'Nam',8),('P0018','T01','A205',2,'Nam',8),('P0019','T01','A206',2,'Nam',8),('P0020','T01','A207',2,'Nam',8),('P0021','T01','A208',2,'Nam',8),('P0022','T01','A209',2,'Nam',8),('P0023','T01','A210',2,'Nam',8),('P0024','T01','A211',2,'Nam',8),('P0025','T01','A212',2,'Nam',8),('P0026','T01','A213',2,'Nam',8),('P0027','T01','A301',3,'Nam',8),('P0028','T01','A302',3,'Nam',8),('P0029','T01','A303',3,'Nam',8),('P0030','T01','A304',3,'Nam',8),('P0031','T01','A305',3,'Nam',8),('P0032','T01','A306',3,'Nam',8),('P0033','T01','A307',3,'Nam',8),('P0034','T01','A308',3,'Nam',8),('P0035','T01','A309',3,'Nam',8),('P0036','T01','A310',3,'Nam',8),('P0037','T01','A311',3,'Nam',8),('P0038','T01','A312',3,'Nam',8),('P0039','T01','A401',4,'Nam',8),('P0040','T01','A402',4,'Nam',8),('P0041','T01','A403',4,'Nam',8),('P0042','T01','A404',4,'Nam',8),('P0043','T01','A405',4,'Nam',8),('P0044','T01','A406',4,'Nam',8),('P0045','T01','A407',4,'Nam',8),('P0046','T01','A408',4,'Nam',8),('P0047','T01','A409',4,'Nam',8),('P0048','T01','A410',4,'Nam',8),('P0049','T01','A411',4,'Nam',8),('P0050','T01','A412',4,'Nam',8),('P0051','T02','B101',1,'Nam',8),('P0052','T02','B102',1,'Nam',8),('P0053','T02','B103',1,'Nam',8),('P0054','T02','B104',1,'Nam',8),('P0055','T02','B105',1,'Nam',8),('P0056','T02','B106',1,'Nam',8),('P0057','T02','B107',1,'Nam',8),('P0058','T02','B108',1,'Nam',8),('P0059','T02','B109',1,'Nam',8),('P0060','T02','B110',1,'Nam',8),('P0061','T02','B111',1,'Nam',8),('P0062','T02','B112',1,'Nam',8),('P0063','T02','B113',1,'Nam',8),('P0064','T02','B201',2,'Nam',8),('P0065','T02','B202',2,'Nam',8),('P0066','T02','B203',2,'Nam',8),('P0067','T02','B204',2,'Nam',8),('P0068','T02','B205',2,'Nam',8),('P0069','T02','B206',2,'Nam',8),('P0070','T02','B207',2,'Nam',8),('P0071','T02','B208',2,'Nam',8),('P0072','T02','B209',2,'Nam',8),('P0073','T02','B210',2,'Nam',8),('P0074','T02','B211',2,'Nam',8),('P0075','T02','B212',2,'Nam',8),('P0076','T02','B213',2,'Nam',8),('P0077','T02','B301',3,'Nam',8),('P0078','T02','B302',3,'Nam',8),('P0079','T02','B303',3,'Nam',8),('P0080','T02','B304',3,'Nam',8),('P0081','T02','B305',3,'Nam',8),('P0082','T02','B306',3,'Nam',8),('P0083','T02','B307',3,'Nam',8),('P0084','T02','B308',3,'Nam',8),('P0085','T02','B309',3,'Nam',8),('P0086','T02','B310',3,'Nam',8),('P0087','T02','B311',3,'Nam',8),('P0088','T02','B312',3,'Nam',8),('P0089','T02','B401',4,'Nam',8),('P0090','T02','B402',4,'Nam',8),('P0091','T02','B403',4,'Nam',8),('P0092','T02','B404',4,'Nam',8),('P0093','T02','B405',4,'Nam',8),('P0094','T02','B406',4,'Nam',8),('P0095','T02','B407',4,'Nam',8),('P0096','T02','B408',4,'Nam',8),('P0097','T02','B409',4,'Nam',8),('P0098','T02','B410',4,'Nam',8),('P0099','T02','B411',4,'Nam',8),('P0100','T02','B412',4,'Nam',8),('P0101','T03','C101',1,'Nữ',8),('P0102','T03','C102',1,'Nữ',8),('P0103','T03','C103',1,'Nữ',8),('P0104','T03','C104',1,'Nữ',8),('P0105','T03','C105',1,'Nữ',8),('P0106','T03','C106',1,'Nữ',8),('P0107','T03','C107',1,'Nữ',8),('P0108','T03','C108',1,'Nữ',8),('P0109','T03','C109',1,'Nữ',8),('P0110','T03','C110',1,'Nữ',8),('P0111','T03','C111',1,'Nữ',8),('P0112','T03','C112',1,'Nữ',8),('P0113','T03','C113',1,'Nữ',8),('P0114','T03','C201',2,'Nữ',8),('P0115','T03','C202',2,'Nữ',8),('P0116','T03','C203',2,'Nữ',8),('P0117','T03','C204',2,'Nữ',8),('P0118','T03','C205',2,'Nữ',8),('P0119','T03','C206',2,'Nữ',8),('P0120','T03','C207',2,'Nữ',8),('P0121','T03','C208',2,'Nữ',8),('P0122','T03','C209',2,'Nữ',8),('P0123','T03','C210',2,'Nữ',8),('P0124','T03','C211',2,'Nữ',8),('P0125','T03','C212',2,'Nữ',8),('P0126','T03','C213',2,'Nữ',8),('P0127','T03','C301',3,'Nữ',8),('P0128','T03','C302',3,'Nữ',8),('P0129','T03','C303',3,'Nữ',8),('P0130','T03','C304',3,'Nữ',8),('P0131','T03','C305',3,'Nữ',8),('P0132','T03','C306',3,'Nữ',8),('P0133','T03','C307',3,'Nữ',8),('P0134','T03','C308',3,'Nữ',8),('P0135','T03','C309',3,'Nữ',8),('P0136','T03','C310',3,'Nữ',8),('P0137','T03','C311',3,'Nữ',8),('P0138','T03','C312',3,'Nữ',8),('P0139','T03','C401',4,'Nữ',8),('P0140','T03','C402',4,'Nữ',8),('P0141','T03','C403',4,'Nữ',8),('P0142','T03','C404',4,'Nữ',8),('P0143','T03','C405',4,'Nữ',8),('P0144','T03','C406',4,'Nữ',8),('P0145','T03','C407',4,'Nữ',8),('P0146','T03','C408',4,'Nữ',8),('P0147','T03','C409',4,'Nữ',8),('P0148','T03','C410',4,'Nữ',8),('P0149','T03','C411',4,'Nữ',8),('P0150','T03','C412',4,'Nữ',8),('P0151','T04','D101',1,'Nữ',8),('P0152','T04','D102',1,'Nữ',8),('P0153','T04','D103',1,'Nữ',8),('P0154','T04','D104',1,'Nữ',8),('P0155','T04','D105',1,'Nữ',8),('P0156','T04','D106',1,'Nữ',8),('P0157','T04','D107',1,'Nữ',8),('P0158','T04','D108',1,'Nữ',8),('P0159','T04','D109',1,'Nữ',8),('P0160','T04','D110',1,'Nữ',8),('P0161','T04','D111',1,'Nữ',8),('P0162','T04','D112',1,'Nữ',8),('P0163','T04','D113',1,'Nữ',8),('P0164','T04','D201',2,'Nữ',8),('P0165','T04','D202',2,'Nữ',8),('P0166','T04','D203',2,'Nữ',8),('P0167','T04','D204',2,'Nữ',8),('P0168','T04','D205',2,'Nữ',8),('P0169','T04','D206',2,'Nữ',8),('P0170','T04','D207',2,'Nữ',8),('P0171','T04','D208',2,'Nữ',8),('P0172','T04','D209',2,'Nữ',8),('P0173','T04','D210',2,'Nữ',8),('P0174','T04','D211',2,'Nữ',8),('P0175','T04','D212',2,'Nữ',8),('P0176','T04','D213',2,'Nữ',8),('P0177','T04','D301',3,'Nữ',8),('P0178','T04','D302',3,'Nữ',8),('P0179','T04','D303',3,'Nữ',8),('P0180','T04','D304',3,'Nữ',8),('P0181','T04','D305',3,'Nữ',8),('P0182','T04','D306',3,'Nữ',8),('P0183','T04','D307',3,'Nữ',8),('P0184','T04','D308',3,'Nữ',8),('P0185','T04','D309',3,'Nữ',8),('P0186','T04','D310',3,'Nữ',8),('P0187','T04','D311',3,'Nữ',8),('P0188','T04','D312',3,'Nữ',8),('P0189','T04','D401',4,'Nữ',8),('P0190','T04','D402',4,'Nữ',8),('P0191','T04','D403',4,'Nữ',8),('P0192','T04','D404',4,'Nữ',8),('P0193','T04','D405',4,'Nữ',8),('P0194','T04','D406',4,'Nữ',8),('P0195','T04','D407',4,'Nữ',8),('P0196','T04','D408',4,'Nữ',8),('P0197','T04','D409',4,'Nữ',8),('P0198','T04','D410',4,'Nữ',8),('P0199','T04','D411',4,'Nữ',8),('P0200','T04','D412',4,'Nữ',8);
/*!40000 ALTER TABLE `phong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sinhvien`
--

DROP TABLE IF EXISTS `sinhvien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinhvien` (
  `MSSV` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` int NOT NULL,
  `MaUuTien` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `HoTen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgaySinh` date NOT NULL,
  `CCCD` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `GioiTinh` enum('Nam','Nữ') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `QueQuan` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SDT` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lop` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NienKhoa` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThaiLuuTru` enum('Đang ở','Đã rời đi','Chờ thanh toán') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang ở',
  PRIMARY KEY (`MSSV`),
  UNIQUE KEY `uq_sinhvien_matk` (`MaTK`),
  UNIQUE KEY `uq_sinhvien_cccd` (`CCCD`),
  KEY `idx_sinhvien_mauutien` (`MaUuTien`),
  KEY `idx_sinhvien_trangthai` (`TrangThaiLuuTru`),
  CONSTRAINT `fk_sinhvien_dienuutien` FOREIGN KEY (`MaUuTien`) REFERENCES `dienuutien` (`MaUuTien`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sinhvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sinhvien`
--

LOCK TABLES `sinhvien` WRITE;
/*!40000 ALTER TABLE `sinhvien` DISABLE KEYS */;
INSERT INTO `sinhvien` VALUES ('KTPM2411022',9,'UT00','Phan Minh Trọng','2006-09-26','093206010833','Nam','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','Đang ở'),('KTPM2411033',10,'UT00','Phan Minh Trọng','2006-09-26','093206010822','Nam','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','Đang ở'),('KTPM2411040',7,'UT00','Phan Minh Trọng','2020-07-02','093206010838','Nam','Cần Thơ','0797439687',NULL,'KTPM2411','2024-2028','Đang ở'),('KTPM2411049',8,'UT00','Phan Minh Trọng','2006-03-26','053590350350','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','Đã rời đi'),('KTPM2411054',18,'UT00','Đỗ Quang Huy','2006-04-10','079206005566','Nam','Hậu Giang','0912345675','dqhuy.ktpm@ctuet.edu.vn','KTPM01-K24','2024-2028','Chờ thanh toán'),('KTPM2411055',19,'UT01','Võ Thị Ngọc Ánh','2006-08-30','079306006677','Nữ','Bạc Liêu','0912345676','vtnanh.ktpm@ctuet.edu.vn','KTPM02-K24','2024-2028','Chờ thanh toán'),('KTPM2411056',20,'UT02','Bùi Minh Khang','2006-01-25','079206007788','Nam','Cà Mau','0912345677','bmkhang.ktpm@ctuet.edu.vn','KTPM01-K24','2024-2028','Đang ở'),('KTPM2411057',21,'UT00','Hoàng Thu Trang','2006-12-12','079306008899','Nữ','Kiên Giang','0912345678','httrang.ktpm@ctuet.edu.vn','KTPM02-K24','2024-2028','Đang ở'),('KTPM2411736',11,'UT02','Phan Minh Trọng','2007-10-28','093206453535','Nữ','Cần Thơ','0797439616','pmtrongktpm2411047@student.ctuet.edu.vn','KTPM2411','2024-2028','Chờ thanh toán'),('KTPM2413587',12,'UT01','Phan Minh Trọng','2005-06-26','093292827758','Nam','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','Đã rời đi'),('KTPM8385772',13,'UT00','Trần Minh Quân','2005-08-17','093206010811','Nữ','Cần Thơ','0797439616','vot750422@gmail.com','KTPM2411','2024-2028','Chờ thanh toán');
/*!40000 ALTER TABLE `sinhvien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taikhoan`
--

DROP TABLE IF EXISTS `taikhoan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taikhoan` (
  `MaTK` int NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MatKhau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `VaiTro` enum('Sinh viên','Nhân viên quản lý KTX','Nhân viên kế toán','Quản lý KTX') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` enum('Hoạt động','Khóa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hoạt động',
  PRIMARY KEY (`MaTK`),
  UNIQUE KEY `uq_taikhoan_tendangnhap` (`TenDangNhap`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taikhoan`
--

LOCK TABLES `taikhoan` WRITE;
/*!40000 ALTER TABLE `taikhoan` DISABLE KEYS */;
INSERT INTO `taikhoan` VALUES (1,'sv001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Sinh viên','Hoạt động'),(3,'nvql001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Nhân viên quản lý KTX','Hoạt động'),(5,'nvkt001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Nhân viên kế toán','Hoạt động'),(6,'ql001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Quản lý KTX','Hoạt động'),(7,'KTPM2411040','$2y$10$e193FArEe/xV.DJR4r7Qmu9AIXW0uuvhEDfsh3zPq.N4gqsu2Fk3a','Sinh viên','Hoạt động'),(8,'KTPM2411049','$2y$10$JLxyLqFznlcR3RX85jY9p.mS3Yd5Wms1x5NjVQXw5uTjXpDaiPU4S','Sinh viên','Hoạt động'),(9,'KTPM2411022','$2y$10$ak0RcxIl1Ho0/wVG2j0ELOglOr/mDchVYCEJQuR3tuVIWwa/tVlse','Sinh viên','Hoạt động'),(10,'KTPM2411033','$2y$10$QcrFNAsTHEOi5oFRLc7trOhI3JZsZ6EUwfp61..7vPECVBtZEwjm.','Sinh viên','Hoạt động'),(11,'KTPM2411736','$2y$10$Hbi2W94s2ffPfOpDrkasbuIKY9dpGuh3Fsh459hK7wlTK63v64Eum','Sinh viên','Hoạt động'),(12,'KTPM2413587','$2y$10$wvFdVXG3g/9sgmEhyeaf2.8C75nBnA7pNbzjeYb6kT1IATylSh/EO','Sinh viên','Hoạt động'),(13,'KTPM8385772','$2y$10$1QUcEgxeO57BPRxUHDhLeew2xGEpfFWBfsDratusN7pU/qYUDi9XW','Sinh viên','Hoạt động'),(18,'KTPM2411054','$2y$10$WZnGhn8KJ60hZDYbEYx0F.2u0U7lSU72cHGlDBMmPpgxZrVdE4iYe','Sinh viên','Hoạt động'),(19,'KTPM2411055','$2y$10$4TiTRPAqxbuXlgk1AqjFTOAdroCn4eGYwhKR4DATKcvm4ztBmNkBC','Sinh viên','Hoạt động'),(20,'KTPM2411056','$2y$10$b/8ENDnkjBGlfynJKDDmU.doPzT.YJOEN90LNJx6Ph8QfLczhmvL.','Sinh viên','Hoạt động'),(21,'KTPM2411057','$2y$10$tdNcH.j0zLAx7SxjQVYxeONaGm.oPB8N.0vSEJ.QG1xdWedv222RC','Sinh viên','Hoạt động');
/*!40000 ALTER TABLE `taikhoan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thanhlyhopdong`
--

DROP TABLE IF EXISTS `thanhlyhopdong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thanhlyhopdong` (
  `MaThanhLy` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHopDong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayThanhLy` date NOT NULL,
  `TienHoanTra` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`MaThanhLy`),
  UNIQUE KEY `uq_thanhly_hopdong` (`MaHopDong`),
  CONSTRAINT `fk_thanhly_hopdong` FOREIGN KEY (`MaHopDong`) REFERENCES `hopdong` (`MaHopDong`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanhlyhopdong`
--

LOCK TABLES `thanhlyhopdong` WRITE;
/*!40000 ALTER TABLE `thanhlyhopdong` DISABLE KEYS */;
INSERT INTO `thanhlyhopdong` VALUES ('73DFF0F321FC','41B4A1819A','2026-10-29',903226.00),('7A02DD86C9A3','50DFEBB6BE','2026-10-29',903226.00);
/*!40000 ALTER TABLE `thanhlyhopdong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thongbao`
--

DROP TABLE IF EXISTS `thongbao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thongbao` (
  `MaThongBao` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MSSV` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHoaDon` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TieuDe` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoiDung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianTao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaThongBao`),
  KEY `idx_thongbao_mssv` (`MSSV`),
  KEY `idx_thongbao_mahoadon` (`MaHoaDon`),
  KEY `idx_thongbao_thoigiantao` (`ThoiGianTao`),
  CONSTRAINT `fk_thongbao_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_thongbao_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thongbao`
--

LOCK TABLES `thongbao` WRITE;
/*!40000 ALTER TABLE `thongbao` DISABLE KEYS */;
INSERT INTO `thongbao` VALUES ('TB26100001','KTPM2411056',NULL,'Chào mừng tân sinh viên nhận phòng KTX Kanto','Chúc mừng bạn Bùi Minh Khang đã hoàn tất thủ tục đăng ký và nộp lệ phí lưu trú phòng A101 (Tòa A). Vui lòng gặp quản lý tòa nhà để nhận chìa khóa phòng.','2026-11-01 13:19:46'),('TB26100002','KTPM2411057',NULL,'Thông báo v/v Phản ánh sự cố điện nước phòng C101','Phản ánh sự cố bóng đèn tuýp LED phòng C101 của bạn đã được nhân viên kỹ thuật xử lý thay thế hoàn tất vào sáng nay.','2026-11-01 13:19:46'),('TB26100003','KTPM2411054',NULL,'Nhắc nhở hoàn thành nộp lệ phí lưu trú Học kỳ 1','Hồ sơ lưu trú của bạn đã được ban quản lý phê duyệt. Vui lòng truy cập mục Hóa đơn để quét mã QR thanh toán trước hạn chót.','2026-11-01 13:19:46');
/*!40000 ALTER TABLE `thongbao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `toanha`
--

DROP TABLE IF EXISTS `toanha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `toanha` (
  `MaToa` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenToa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MaToa`),
  UNIQUE KEY `uq_toanha_ten` (`TenToa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `toanha`
--

LOCK TABLES `toanha` WRITE;
/*!40000 ALTER TABLE `toanha` DISABLE KEYS */;
INSERT INTO `toanha` VALUES ('T01','Tòa A'),('T02','Tòa B'),('T03','Tòa C'),('T04','Tòa D');
/*!40000 ALTER TABLE `toanha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'qlktx_v2'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-01 15:42:09
