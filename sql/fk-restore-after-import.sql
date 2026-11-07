-- Bo sung cac rang buoc khoa ngoai con thieu sau khi import qlktx_v2_final.sql.
--
-- Vi sao can: trong ban dump qlktx_v2_final.sql, mot so bang tham chieu
-- (dienuutien, toanha) tung duoc tao bang MyISAM nen mysqldump khong xuat duoc
-- cac FK tro toi chung; mot so bang khac bi tao sau khi dump duoc chup.
-- Ban dump hien co 20 khoa ngoai; tep nay bo sung dung 10 khoa ngoai con thieu
-- de du 30 rang buoc nhu thiet ke.
--
-- Cach dung: import qlktx_v2_final.sql truoc, sau do import tep nay.
-- Chay lai lan hai se bao loi "Duplicate foreign key constraint name" - vo hai,
-- khong lam hong du lieu; kiem tra bang: SHOW CREATE TABLE `hopdong`;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `hoadondiennuoc`
  ADD CONSTRAINT `fk_hddn_dongiadien` FOREIGN KEY (`MaDonGiaDien`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_dongianuoc` FOREIGN KEY (`MaDonGiaNuoc`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `hopdong`
  ADD CONSTRAINT `fk_hopdong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hopdong_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `phananhsuco`
  ADD CONSTRAINT `fk_phananh_nhanvien` FOREIGN KEY (`MaNhanVienXuLy`) REFERENCES `nhanvien` (`MaNV`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phananh_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phananh_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `phong`
  ADD CONSTRAINT `fk_phong_toanha` FOREIGN KEY (`MaToa`) REFERENCES `toanha` (`MaToa`) ON DELETE RESTRICT ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
