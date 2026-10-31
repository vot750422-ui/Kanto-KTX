-- Bo sung rang buoc khoa ngoai con thieu (dienuutien/toanha la MyISAM nen
-- mysqldump khong xuat duoc FK tham chieu toi chung).
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `hoadondiennuoc`
  ADD CONSTRAINT `fk_hddn_dongiadien` FOREIGN KEY (`MaDonGiaDien`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_dongianuoc` FOREIGN KEY (`MaDonGiaNuoc`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddn_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `hoadondiennuocsinhvien`
  ADD CONSTRAINT `fk_hddnsv_hddn` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadondiennuoc` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hddnsv_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `hoadontienphong`
  ADD CONSTRAINT `fk_hdphong_dongia` FOREIGN KEY (`MaDonGia`) REFERENCES `dongia` (`MaDonGia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_hocky` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hdphong_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `hopdong`
  ADD CONSTRAINT `fk_hopdong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hopdong_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `nhanvien`
  ADD CONSTRAINT `fk_nhanvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`);

ALTER TABLE `phananhsuco`
  ADD CONSTRAINT `fk_phananh_nhanvien` FOREIGN KEY (`MaNhanVienXuLy`) REFERENCES `nhanvien` (`MaNV`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phananh_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phananh_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `phienthanhtoan`
  ADD CONSTRAINT `fk_phienthanhtoan_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phienthanhtoan_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `phong`
  ADD CONSTRAINT `fk_phong_toanha` FOREIGN KEY (`MaToa`) REFERENCES `toanha` (`MaToa`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `sinhvien`
  ADD CONSTRAINT `fk_sinhvien_dienuutien` FOREIGN KEY (`MaUuTien`) REFERENCES `dienuutien` (`MaUuTien`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sinhvien_taikhoan` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`);

ALTER TABLE `thanhlyhopdong`
  ADD CONSTRAINT `fk_thanhly_hopdong` FOREIGN KEY (`MaHopDong`) REFERENCES `hopdong` (`MaHopDong`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `thongbao`
  ADD CONSTRAINT `fk_thongbao_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_thongbao_sinhvien` FOREIGN KEY (`MSSV`) REFERENCES `sinhvien` (`MSSV`) ON DELETE RESTRICT ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
