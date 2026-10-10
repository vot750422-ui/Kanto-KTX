# UC02 - Đăng ký lưu trú

## 1. Thông tin chung

- **Mã Use Case:** UC02
- **Tên Use Case:** Đăng ký lưu trú
- **Tác nhân chính:**
  - Khách vãng lai
  - Nhân viên quản lý KTX (hỗ trợ sinh viên đăng ký lưu trú trực tiếp)

## 2. Mục đích

Cho phép tác nhân gửi thông tin đăng ký ký túc xá để chờ Nhân viên quản lý KTX xét duyệt.

## 3. Tiền điều kiện

- Sinh viên chưa có đơn đăng ký ở trạng thái **“Chờ duyệt”** trong cơ sở dữ liệu.
- MSSV và CCCD chưa tồn tại trong bảng `sinhvien` (kể cả hồ sơ đã rời đi).

## 4. Hậu điều kiện

- Thông tin đăng ký được lưu vào bảng `DonDangKy`.
- Đơn đăng ký có trạng thái **“Chờ duyệt”**.
- Đơn đăng ký ghi nhận phòng mà người đăng ký đã chọn.
- Một chỗ tại phòng đã chọn được tạm giữ cho đơn đăng ký đang chờ duyệt.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Khách vãng lai chọn **“Đăng ký lưu trú”** trên trang chủ. | |
| 2 | | Hệ thống hiển thị Form đăng ký gồm các thông tin cá nhân và thông tin học tập, ưu tiên. |
| 3 | Người đăng ký nhập đầy đủ thông tin, chọn diện ưu tiên, tải ảnh minh chứng và nhấn **“Xác nhận”**. | |
| 4 | | Hệ thống kiểm tra dữ liệu nhập vào và kiểm tra trùng lặp MSSV. Nếu dữ liệu hợp lệ, hệ thống truy vấn cơ sở dữ liệu để lọc các phòng còn sức chứa và phù hợp với thông tin đăng ký, sau đó hiển thị danh sách phòng để người đăng ký lựa chọn. |
| 5 | Người đăng ký xem danh sách phòng, chọn phòng mong muốn và nhấn **“Gửi đăng ký”**. | |
| 6 | | Hệ thống kiểm tra lại toàn bộ dữ liệu và sức chứa khả dụng của phòng tại thời điểm gửi đơn. Nếu vẫn còn chỗ, hệ thống tạo đơn đăng ký mới, ghi nhận phòng mong muốn, thời gian gửi và trạng thái **“Chờ duyệt”**. Đồng thời, hệ thống tạm giữ **01 chỗ** tại phòng đã chọn cho đơn đăng ký này, lưu đơn vào cơ sở dữ liệu và hiển thị thông báo **“Đăng ký lưu trú thành công. Hồ sơ đang chờ xét duyệt.”** |

### Thông tin hiển thị tại bước 2

#### Thông tin cá nhân

- Họ và tên
- Ngày sinh
- CCCD
- Giới tính
- Quê quán
- Số điện thoại

#### Thông tin học tập và ưu tiên

- MSSV
- Lớp
- Niên khóa
- Diện ưu tiên
- Ảnh minh chứng

## 6. Luồng ngoại lệ

### E1 - Dữ liệu không hợp lệ

- **Xảy ra tại:** Bước 4.
- **Điều kiện:**
  - Thiếu dữ liệu bắt buộc; hoặc
  - Dữ liệu không đúng định dạng.
- **Xử lý:**
  - Hệ thống hiển thị thông báo lỗi cụ thể.
  - Yêu cầu người đăng ký kiểm tra và nhập lại dữ liệu.
  - Không chuyển sang bước chọn phòng khi dữ liệu chưa hợp lệ.

### E2 - Tệp minh chứng không hợp lệ

- **Xảy ra tại:** Bước 3.
- **Điều kiện:**
  - Tệp tải lên không đúng định dạng ảnh cho phép; hoặc
  - Dung lượng tệp vượt quá giới hạn.

- **Định dạng ảnh cho phép:**
  - PNG
  - JPG

- **Dung lượng tối đa:** dưới 5 MB.

- **Xử lý:**
  - Hệ thống hiển thị thông báo tệp không đúng định dạng hoặc dung lượng vượt quá 5 MB.
  - Người đăng ký phải chọn lại tệp hợp lệ trước khi tiếp tục.

### E3 - MSSV đã có đơn đang chờ duyệt

- **Xảy ra tại:** Bước 4.
- **Điều kiện:** MSSV đã tồn tại một đơn đăng ký có trạng thái **“Chờ duyệt”**.
- **Xử lý:**
  - Hệ thống không cho phép tạo thêm đơn đăng ký mới.
  - Hệ thống thông báo người đăng ký đã có hồ sơ đang được xử lý.

### E4 - Phòng vừa hết chỗ

- **Xảy ra tại:** Bước 6.
- **Điều kiện:** Sau khi danh sách phòng được hiển thị nhưng trước khi người đăng ký gửi đơn, chỗ còn lại của phòng đã được một đơn đăng ký khác giữ trước.
- **Xử lý:**
  - Hệ thống không tạo đơn đăng ký với phòng đã hết chỗ.
  - Không thực hiện giữ chỗ.
  - Hệ thống hiển thị thông báo:

    **“Phòng bạn chọn vừa hết chỗ. Vui lòng chọn phòng khác.”**

  - Hệ thống đưa người đăng ký trở lại bước chọn phòng.

## 7. Quy tắc nghiệp vụ

### Danh mục diện ưu tiên đã chốt

| Mã | Diện ưu tiên | Mức ưu tiên |
|---|---|---|
| UT00 | Không có | 0 (không ưu tiên) |
| UT01 | Hộ nghèo / Cận nghèo | 2 |
| UT02 | Con thương binh / Liệt sĩ | 4 (ưu tiên đầu) |
| UT03 | Vùng sâu vùng xa / Hải đảo | 3 |

Dropdown hiển thị theo thứ tự mã trên, mặc định Không có. Khi xét duyệt, mức lớn
đứng trước: UT02 (4), UT03 (3), UT01 (2), UT00 (0), rồi thời gian gửi tăng dần.

### BR-UC02-01 - Một MSSV không được có nhiều đơn chờ duyệt

Tại một thời điểm, một MSSV chỉ được có tối đa một `DonDangKy` có trạng thái:

`Chờ duyệt`

Nếu đã tồn tại đơn đang chờ duyệt, hệ thống không cho phép tạo thêm đơn mới.

### BR-UC02-02 - Chỉ hiển thị phòng còn chỗ

Hệ thống chỉ hiển thị các phòng:

- phù hợp với thông tin đăng ký (Giới tính);
- vẫn còn sức chứa khả dụng.

### BR-UC02-03 - Cách tính số chỗ khả dụng

Số chỗ khả dụng của một phòng được tính dựa trên:

`SucChua`
trừ
số sinh viên đang có `HopDong` còn hạn tại phòng
trừ
số `DonDangKy` đang ở trạng thái `Chờ duyệt` tại phòng
trừ
số `DonDangKy` `Đã duyệt` có `MaHopDong IS NULL` tại phòng.

Đơn đã duyệt tiếp tục giữ chỗ chờ thanh toán. Khi UC thanh toán tạo hợp đồng,
phải liên kết `dondangky.MaHopDong` trong cùng transaction để không đếm hai lần.
Không giảm `phong.SucChua`. Kiểm tra MSSV/CCCD đã có sinh viên và trùng đơn
chờ duyệt ở cả bước nhập và submit dưới khóa chung.
