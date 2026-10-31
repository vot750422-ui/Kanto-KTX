-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: kanto-qlktx-mysql-vot750422-998f.d.aivencloud.com    Database: defaultdb
-- ------------------------------------------------------
-- Server version	8.4.11

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'a2219685-c3c3-11f1-8695-12ef25727b32:1-291';

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
  KEY `idx_chiso_manv` (`MaNV`),
  CONSTRAINT `fk_chiso_nhanvien` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_chiso_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chisodiennuoc`
--

LOCK TABLES `chisodiennuoc` WRITE;
/*!40000 ALTER TABLE `chisodiennuoc` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dienuutien`
--

LOCK TABLES `dienuutien` WRITE;
/*!40000 ALTER TABLE `dienuutien` DISABLE KEYS */;
INSERT INTO `dienuutien` VALUES ('UT00','Không có',0),('UT01','Hộ nghèo / Cận nghèo',1),('UT02','Con thương binh / Liệt sĩ',2),('UT03','Vùng sâu vùng xa / Hải đảo',3);
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
  PRIMARY KEY (`MaDonDangKy`),
  KEY `idx_dondangky_mssv` (`MSSV`),
  KEY `idx_dondangky_trangthai` (`TrangThai`),
  KEY `idx_dondangky_maphong` (`MaPhong`),
  KEY `idx_dondangky_mauutien` (`MaUuTien`),
  CONSTRAINT `fk_dondangky_dienuutien` FOREIGN KEY (`MaUuTien`) REFERENCES `dienuutien` (`MaUuTien`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dondangky_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dondangky`
--

LOCK TABLES `dondangky` WRITE;
/*!40000 ALTER TABLE `dondangky` DISABLE KEYS */;
INSERT INTO `dondangky` VALUES ('41E119DC87','KTPM2411040','Phan Minh Trọng','2020-07-02','093206010838','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','UT00','storage/registration/2123978f89e66762cd3f6038b255e266522c1a94d2637b3d.jpg','P0039','2026-10-08 20:04:20','Chờ duyệt',NULL),('6B543826B3','KTPM2411047','Phan Minh Trọng','2006-09-26','093206010838','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','UT01','storage/registration/8ed74a8f1530ea234395e5d6a1c206ffd52bcdbc56c1db47.png','P0058','2026-10-08 03:25:44','Chờ duyệt',NULL),('A37599C9E2','KTPM2411045','Phan Minh Trọng','2006-09-26','093206010838','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','UT01',NULL,'P0001','2026-10-09 16:26:25','Chờ duyệt',NULL),('FF8A9D65CA','KTPM2411049','Phan Minh Trọng','2006-03-26','053590350350','Nam','Cần Thơ','0797439616',NULL,'KTPM2411','2024-2028','UT00',NULL,'P0038','2026-10-08 15:31:38','Chờ duyệt',NULL);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dongia`
--

LOCK TABLES `dongia` WRITE;
/*!40000 ALTER TABLE `dongia` DISABLE KEYS */;
/*!40000 ALTER TABLE `dongia` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoadon`
--

LOCK TABLES `hoadon` WRITE;
/*!40000 ALTER TABLE `hoadon` DISABLE KEYS */;
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
  KEY `idx_hddn_dongianuoc` (`MaDonGiaNuoc`),
  CONSTRAINT `fk_hddn_dongiadien` FOREIGN KEY (`MaDonGiaDien`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_hddn_dongianuoc` FOREIGN KEY (`MaDonGiaNuoc`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_hddn_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hddn_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
  PRIMARY KEY (`MaHopDong`),
  KEY `idx_hopdong_mssv` (`MSSV`),
  KEY `idx_hopdong_maphong` (`MaPhong`),
  KEY `idx_hopdong_trangthai` (`TrangThai`),
  CONSTRAINT `fk_hopdong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_hopdong_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hopdong`
--

LOCK TABLES `hopdong` WRITE;
/*!40000 ALTER TABLE `hopdong` DISABLE KEYS */;
/*!40000 ALTER TABLE `hopdong` ENABLE KEYS */;
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
/*!40000 ALTER TABLE `thanhlyhopdong` ENABLE KEYS */;
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
  KEY `idx_phananh_thoigiangui` (`ThoiGianGui`),
  CONSTRAINT `fk_phananh_nhanvien` FOREIGN KEY (`MaNhanVienXuLy`) REFERENCES `nhanvien` (`MaNV`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_phananh_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_phananh_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phananhsuco`
--

LOCK TABLES `phananhsuco` WRITE;
/*!40000 ALTER TABLE `phananhsuco` DISABLE KEYS */;
/*!40000 ALTER TABLE `phananhsuco` ENABLE KEYS */;
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
  KEY `idx_phong_toa_tang` (`MaToa`,`Tang`),
  CONSTRAINT `fk_phong_toanha` FOREIGN KEY (`MaToa`) REFERENCES `toanha` (`MaToa`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taikhoan`
--

LOCK TABLES `taikhoan` WRITE;
/*!40000 ALTER TABLE `taikhoan` DISABLE KEYS */;
INSERT INTO `taikhoan` VALUES (1,'sv001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Sinh viên','Hoạt động'),(3,'nvql001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Nhân viên quản lý KTX','Hoạt động'),(5,'nvkt001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Nhân viên kế toán','Hoạt động'),(6,'ql001','$2y$10$GBEExmOUOdtEOAfOAxRkKudSQVuho9szhCgsx83fUJ8cbmlPf34P.','Quản lý KTX','Hoạt động');
/*!40000 ALTER TABLE `taikhoan` ENABLE KEYS */;
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
-- Dumping routines for database 'defaultdb'
--
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-10 16:17:19
