-- Chuyen cac bang du lieu nghiep vu sang InnoDB de transaction/rollback hoat dong.
-- Ban export tu may khac xuat ra MyISAM cho cac bang nay, khien transaction khong rollback duoc.
ALTER TABLE `chisodiennuoc` ENGINE = InnoDB;
ALTER TABLE `dienuutien` ENGINE = InnoDB;
ALTER TABLE `dongia` ENGINE = InnoDB;
ALTER TABLE `hoadon` ENGINE = InnoDB;
ALTER TABLE `hoadondiennuoc` ENGINE = InnoDB;
ALTER TABLE `hocky` ENGINE = InnoDB;
ALTER TABLE `hopdong` ENGINE = InnoDB;
ALTER TABLE `phananhsuco` ENGINE = InnoDB;
ALTER TABLE `phong` ENGINE = InnoDB;
