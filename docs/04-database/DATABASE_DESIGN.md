# Thiết kế CSDL — Tòa nhà và Phòng

## UC17 — Phiên thanh toán QR liên thiết bị (25/10/2026)

- Migration: `sql/uc17-payment-links.sql`, CLI `php scripts/migrate-payment-links.php`.
- Bảng `phienthanhtoan`: `MaPhien` CHAR(64) ASCII/binary là SHA-256 token và khóa chính; `MaHoaDon` VARCHAR(12) FK đến `hoadon`; `MaTK` INT FK đến `taikhoan`; `SoTien` DECIMAL(12,2); `TaoLuc`, `HetHanLuc`, `DaDungLuc` DATETIME.
- Token gốc ngẫu nhiên 32 byte chỉ giữ trong session trình duyệt đã tạo; CSDL chỉ lưu hash. Liên kết có hiệu lực tối đa 5 phút kể từ `TaoLuc`, kể cả bản ghi cũ có `HetHanLuc` dài hơn; mất quyền thu sớm khi hóa đơn đã thu/tài khoản gốc không còn hợp lệ/số tiền thay đổi.
- Mỗi hóa đơn chỉ được phát hành một link còn hiệu lực tại một thời điểm. POST tạo QR dùng named lock và transaction chung với thanh toán, trả lại token trong cùng session nếu mã cũ còn hạn; trình duyệt khác phải đợi mã đó hết hạn. Nếu dữ liệu cũ có nhiều link, trang mở QR và xác nhận chỉ chấp nhận link mới nhất, POST tạo QR vô hiệu hóa các link còn lại. `DaDungLuc` cập nhật trong cùng transaction với hóa đơn/hợp đồng, rollback đồng thời khi lỗi.
- FK dùng ON DELETE CASCADE/ON UPDATE CASCADE. Không chỉnh sửa cấu trúc hoặc dữ liệu của bảng nghiệp vụ hiện có. Bản ghi hết hạn/đã dùng hiện được giữ lại; chưa có tác vụ tự dọn lịch sử.
- Khi tạo CSDL mới từ dump, cần chạy thêm migration này; không chỉnh sửa dump SQL local có dữ liệu sẵn trong lượt triển khai.

## Bảng hiện có

- `toanha`: `MaToa` (khóa chính), `TenToa` (duy nhất).
- `phong`: `MaPhong` (khóa chính), `MaToa` (khóa ngoại đến `toanha`), `SoPhong`, `Tang`, `GioiTinhPhong`, `SucChua`.
- Cặp `MaToa`, `SoPhong` là duy nhất. CSDL WAMP hiện có CHECK: tầng từ 1 đến 4, sức chứa lớn hơn 0. SQL dump hiện chưa thể hiện các CHECK này.
- Giao diện gọi “Dãy” sử dụng `toanha`; không có bảng `day`.

## Bộ dữ liệu mẫu đã chốt

| Mã tòa | Tên | Giới tính phòng | Số phòng |
|---|---|---|---|
| T01 | Tòa A | Nam | 50 |
| T02 | Tòa B | Nam | 50 |
| T03 | Tòa C | Nữ | 50 |
| T04 | Tòa D | Nữ | 50 |

Mỗi tòa: tầng 1 và 2 có 13 phòng, tầng 3 và 4 có 12 phòng. Mỗi phòng có sức chứa 8 sinh viên. Tổng 200 phòng, sức chứa thiết kế 1600 sinh viên.

`MaPhong` từ `P0001` đến `P0200`, theo thứ tự tòa, tầng, số phòng. `SoPhong` dùng để hiển thị: A101–A113, A201–A213, A301–A312, A401–A412; các tòa còn lại thay A bằng B/C/D.

## Seed dữ liệu

Import `sql/qlktx_v2.sql` để tạo CSDL mới, sau đó chạy `sql/seed-buildings-rooms.sql` trong CSDL đó bằng phpMyAdmin. Không import lại toàn bộ dump vào CSDL đang sử dụng chỉ để thêm phòng, vì dump có DROP TABLE.

Script seed tự sinh dữ liệu bằng CTE, chạy trên MySQL 8 trở lên. Khi trùng khóa, giữ nguyên dữ liệu đã có; không xóa, ghi đè hay thay đổi schema. Nếu CSDL đã có mã/tên xung đột với bộ mẫu, có thể không thêm đủ 200 phòng; cần đối chiếu dữ liệu thay vì tự ghi đè.

## UC02 sử dụng dữ liệu

`Registration::rooms()` nối phòng với tòa, chỉ lấy đúng giới tính và phòng có chỗ khả dụng lớn hơn 0:

`ConCho = SucChua - số HopDong Còn hạn - số DonDangKy Chờ duyệt - số DonDangKy Đã duyệt chưa có MaHopDong`.

`SucChua` luôn là sức chứa thiết kế; không giảm khi gửi đơn. Một đơn `Chờ duyệt` giữ đúng 1 chỗ. Khi submit, backend kiểm tra lại giới tính/chỗ khả dụng và lưu mã kỹ thuật `MaPhong` vào `dondangky`; trạng thái mới mặc định `Chờ duyệt` theo schema.

Wireframe UC02 minh họa tầng 5 và phòng đầy; bộ dữ liệu mới chỉ có 4 tầng và UC02 chỉ hiển thị phòng còn chỗ theo đặc tả/yêu cầu đã chốt.

## UC03 — Duyệt và giữ chỗ chờ thanh toán

- Migration cho CSDL hiện có: `sql/uc03-approval.sql`, chạy một lần; dump mới đã đồng bộ.
- `sinhvien.TrangThaiLuuTru` thêm `Chờ thanh toán`.
- `dondangky.MaHoaDon` nullable, UNIQUE, FK đến `hoadontienphong.MaHoaDon`;
  `dondangky.MaHopDong` nullable, UNIQUE, FK đến `hopdong.MaHopDong`.
- Duyệt tạo tài khoản, sinh viên Chờ thanh toán, hoadon và hoadontienphong;
  đơn Đã duyệt có MaHoaDon nhưng MaHopDong NULL. Không tạo hợp đồng.
- Hóa đơn thu trọn học kỳ kế tiếp gần nhất sau học kỳ hiện tại theo ngày Việt Nam,
  đơn giá tiền phòng mới nhất đang hiệu lực, đơn vị đồng/học kỳ. Không tính phần kỳ cũ.
- Khi thanh toán tạo hợp đồng sau này, phải cập nhật MaHopDong cùng transaction
  và đổi trạng thái sinh viên; việc đổi hóa đơn Đã thu riêng không giải phóng chỗ giữ.
- Mức ưu tiên UT00/UT01/UT02/UT03: 0/2/4/3, xét duyệt giảm dần.
- Giữ cột Email nullable. Không ghi đè hồ sơ sinh viên đã tồn tại.
- Không tự backfill hóa đơn/hợp đồng cho đơn cũ: kiểm tra dữ liệu trước khi deploy,
  đặc biệt đơn Đã duyệt trước migration có MaHopDong NULL nhưng đã có hợp đồng cũ.

## Email xét duyệt (20/10/2026)

Migration mới sql/uc03-email.sql tạo emailthongbao: MaDonDangKy là khóa chính và tham chiếu logic đến đơn (không thêm FK), NguoiNhan, TieuDe, NoiDung, TrangThai, SoLanThu, ThoiGianTao, ThoiGianGui. Trạng thái gồm Chờ gửi, Đã gửi, Lỗi, Không có email. Mỗi đơn tối đa một thông báo, ghi cùng transaction xét duyệt; SMTP sau commit. Email của đơn được sao chép vào sinhvien khi duyệt. Giữ Email nullable cho dữ liệu cũ; bắt buộc trong UC02 cho đơn mới. PHP và MySQL session thống nhất giờ Việt Nam. Không sửa DATETIME lịch sử. Xem ../UC03_EMAIL_SETUP.md; không nhập lại dump để nâng cấp.
