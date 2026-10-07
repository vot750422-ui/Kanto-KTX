# UC03 - Xét duyệt đơn đăng ký

## 1. Thông tin chung

- **Mã Use Case:** UC03
- **Tên Use Case:** Xét duyệt đơn đăng ký
- **Tác nhân chính:** Nhân viên quản lý KTX

## 2. Mục đích

Cho phép Nhân viên quản lý KTX kiểm tra thông tin trong đơn đăng ký lưu trú và thực hiện duyệt hoặc từ chối đơn đăng ký.

## 3. Tiền điều kiện

- Tác nhân đã đăng nhập vào hệ thống.
- Hệ thống có ít nhất một đơn đăng ký ở trạng thái **“Chờ duyệt”**.
- Đơn đăng ký đã được lưu cùng thông tin phòng mong muốn và chỗ tạm giữ tương ứng.

## 4. Hậu điều kiện

### Trường hợp duyệt đơn

- Trạng thái đơn đăng ký được cập nhật thành **“Đã duyệt”**.
- Thông tin từ `DonDangKy` được sử dụng để tạo thông tin sinh viên trong bảng `SinhVien`.
- Chỗ đang được tạm giữ tại phòng đã chọn được chuyển thành chỗ ở chính thức.
- Hệ thống tạo tài khoản cho sinh viên để sử dụng các chức năng dành cho Sinh viên.

### Trường hợp từ chối đơn

- Trạng thái đơn đăng ký được cập nhật thành **“Từ chối”**.
- Lý do từ chối được lưu lại.
- Chỗ đang được tạm giữ tại phòng được giải phóng.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Nhân viên quản lý KTX chọn chức năng **“Xét duyệt đơn đăng ký”**. | |
| 2 | | Hệ thống truy vấn các đơn có trạng thái **“Chờ duyệt”** và hiển thị danh sách đơn đăng ký theo thứ tự ưu tiên và thời gian gửi. |
| 3 | Nhân viên chọn một đơn đăng ký cần xem xét. | |
| 4 | | Hệ thống hiển thị chi tiết đơn đăng ký gồm thông tin cá nhân, thông tin học tập, diện ưu tiên, tệp minh chứng và phòng mà người đăng ký đã lựa chọn. |
| 5 | Nhân viên kiểm tra thông tin của đơn đăng ký và chọn **“Duyệt”**. | |
| 6 | | Hệ thống hiển thị hộp thoại xác nhận: **“Bạn có chắc chắn muốn duyệt đơn đăng ký này không?”** |
| 7 | Nhân viên chọn **“Xác nhận”**. | |
| 8 | | Hệ thống kiểm tra lại trạng thái đơn đăng ký và thông tin chỗ đang được tạm giữ tại phòng đã chọn. Nếu hợp lệ, hệ thống cập nhật trạng thái đơn thành **“Đã duyệt”**, tạo thông tin sinh viên từ dữ liệu của đơn đăng ký, ghi nhận sinh viên chính thức tại phòng đã chọn, chuyển chỗ tạm giữ thành chỗ ở chính thức và tạo tài khoản sinh viên. Sau đó hệ thống hiển thị thông báo **“Duyệt đơn đăng ký thành công.”** và cập nhật lại danh sách đơn đang chờ duyệt. |
| 9 | Nhân viên quản lý KTX tiếp tục chọn đơn khác để xét duyệt nếu danh sách vẫn còn đơn **“Chờ duyệt”**. | |

## 6. Luồng phụ

### A1 - Xem ảnh minh chứng

- **Xảy ra tại:** Sau bước 4, trong quá trình kiểm tra thông tin đơn đăng ký.
- **Hành động:** Nhân viên quản lý KTX chọn ảnh minh chứng.
- **Phản hồi:** Hệ thống hiển thị ảnh minh chứng ở kích thước lớn hơn để nhân viên kiểm tra.
- **Xử lý:**
  - Nhân viên kiểm tra nội dung ảnh minh chứng.
  - Sau khi kiểm tra xong, nhân viên đóng ảnh.
  - Tiếp tục thực hiện bước 5.

## 7. Luồng ngoại lệ

### E1 - Từ chối đơn đăng ký

- **Xảy ra tại:** Bước 5.
- **Điều kiện:** Sau khi kiểm tra hồ sơ, Nhân viên quản lý KTX xác định đơn đăng ký không đủ điều kiện để được duyệt.
- **Hành động:**
  - Nhân viên chọn **“Từ chối”**.
  - Nhập lý do từ chối.
  - Xác nhận thao tác.
- **Xử lý:**
  - Hệ thống cập nhật trạng thái đơn đăng ký thành **“Từ chối”**.
  - Hệ thống lưu nội dung lý do từ chối.
  - Hệ thống giải phóng chỗ đang được tạm giữ tại phòng đã chọn.
  - Hệ thống hiển thị thông báo:

    **“Từ chối đơn đăng ký thành công.”**

### E2 - Từ chối nhưng chưa nhập lý do

- **Xảy ra tại:** Bước 5.
- **Điều kiện:** Nhân viên chọn **“Từ chối”** nhưng không nhập nội dung vào trường **“Lý do từ chối”**.
- **Phản hồi:**
  - Hệ thống không thực hiện thao tác từ chối.
  - Hệ thống hiển thị cảnh báo:

    **“Thiếu lý do từ chối.”**

- **Xử lý:**
  - Nhân viên nhập lý do từ chối.
  - Thực hiện lại thao tác xác nhận.

### E3 - Không còn đơn chờ duyệt

- **Xảy ra tại:** Sau bước 8.
- **Điều kiện:** Sau khi cập nhật danh sách, hệ thống không còn đơn đăng ký nào có trạng thái **“Chờ duyệt”**.
- **Phản hồi:**
  - Hệ thống hiển thị thông báo:

    **“Không còn đơn chờ duyệt.”**

## 8. Quy tắc nghiệp vụ

### BR-UC03-01 - Chỉ xét duyệt đơn đang chờ duyệt

Chỉ các đơn có trạng thái:

`Chờ duyệt`

mới được phép thực hiện thao tác duyệt hoặc từ chối.

### BR-UC03-02 - Thứ tự hiển thị đơn

Danh sách đơn đăng ký chờ duyệt được sắp xếp dựa trên:

1. Mức độ ưu tiên.
2. Thời gian gửi đơn.

### BR-UC03-03 - Kiểm tra lại trước khi duyệt

Trước khi cập nhật kết quả xét duyệt, hệ thống phải kiểm tra lại:

- trạng thái hiện tại của đơn;
- thông tin phòng đã chọn;
- chỗ đang được tạm giữ cho đơn đăng ký.

### BR-UC03-04 - Chuyển chỗ tạm giữ thành chỗ ở chính thức

Khi đơn được duyệt:

- chỗ đang được giữ bởi `DonDangKy` không còn được tính là chỗ tạm giữ;
- sinh viên được ghi nhận chính thức tại phòng đã chọn.

Việc duyệt đơn không làm tăng thêm một chỗ sử dụng ngoài chỗ đã được giữ trước đó.

### BR-UC03-05 - Từ chối phải có lý do

Nhân viên không được phép xác nhận từ chối đơn nếu chưa nhập lý do từ chối.

Lý do từ chối phải được lưu cùng đơn đăng ký.

### BR-UC03-06 - Giải phóng chỗ khi từ chối

Khi đơn chuyển từ:

`Chờ duyệt`

sang:

`Từ chối`

thì chỗ đang được tạm giữ bởi đơn đó phải được giải phóng và được tính lại vào số chỗ khả dụng của phòng.

### BR-UC03-07 - Tạo tài khoản sau khi duyệt

Tài khoản Sinh viên chỉ được tạo sau khi đơn đăng ký được duyệt thành công.

Không tạo tài khoản cho:

- đơn đang `Chờ duyệt`;
- đơn đã `Từ chối`.

## 9. Dữ liệu liên quan

### Bảng `DonDangKy`

Dùng để:

- lấy thông tin đăng ký;
- lấy phòng đã chọn;
- lấy diện ưu tiên;
- lấy tệp minh chứng;
- kiểm tra trạng thái đơn;
- cập nhật kết quả xét duyệt;
- lưu lý do từ chối.

Các thuộc tính chính:

- `MaDonDangKy`
- `MSSV`
- `HoTen`
- `NgaySinh`
- `CCCD`
- `GioiTinh`
- `QueQuan`
- `SDT`
- `Lop`
- `NienKhoa`
- `MaUuTien`
- `FileMinhChung`
- `MaPhong`
- `ThoiGianGui`
- `TrangThai`
- `LyDoTuChoi`
- không có email
### Bảng `DienUuTien`

Dùng để xác định diện và mức độ ưu tiên của đơn đăng ký.

Các thuộc tính chính:

- `MaUuTien`
- `TenDienUuTien`
- `MucUuTien`

### Bảng `Phong`

Dùng để:

- xác định phòng mà người đăng ký đã lựa chọn;
- kiểm tra thông tin chỗ đang được tạm giữ;
- ghi nhận chỗ ở chính thức khi đơn được duyệt.

### Bảng `SinhVien`

Được tạo từ thông tin của `DonDangKy` khi đơn được duyệt thành công.

### Bảng `TaiKhoan`

Được sử dụng để tạo tài khoản cho Sinh viên sau khi đơn đăng ký được duyệt.

## 10. Quan hệ với Use Case khác

### UC02 - Đăng ký lưu trú

UC03 xử lý các đơn đã được tạo từ UC02.

Luồng tổng quát:

```text
UC02 - Đăng ký lưu trú
        ↓
DonDangKy = "Chờ duyệt"
        ↓
UC03 - Xét duyệt đơn đăng ký
        ↓
   ┌───────────────┐
   │               │
 Đã duyệt       Từ chối
   │               │
   ↓               ↓
SinhVien       Giải phóng
TaiKhoan       chỗ tạm giữ
Chỗ ở
chính thức