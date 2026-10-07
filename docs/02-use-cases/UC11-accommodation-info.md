# UC11 - Xem thông tin lưu trú

## 1. Thông tin chung

- **Mã Use Case:** UC11
- **Tên Use Case:** Xem thông tin lưu trú
- **Tác nhân chính:** Sinh viên

## 2. Mục đích

Cho phép Sinh viên xem thông tin cá nhân và thông tin lưu trú hiện tại của mình, bao gồm thông tin hợp đồng và phòng đang ở.

## 3. Tiền điều kiện

- Sinh viên đã đăng nhập vào hệ thống.
- Thông tin Sinh viên đã tồn tại trong hệ thống.

## 4. Hậu điều kiện

- Hệ thống hiển thị thông tin cá nhân của Sinh viên.
- Hệ thống hiển thị thông tin lưu trú hiện tại của Sinh viên nếu có.
- Không có dữ liệu nào bị thay đổi sau khi thực hiện chức năng.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Sinh viên nhấn vào tên tài khoản của mình ở góc phải trên màn hình hoặc chọn chức năng **“Hồ sơ”**. | |
| 2 | | Hệ thống xác định Sinh viên đang đăng nhập và truy vấn thông tin cá nhân tương ứng. Hệ thống truy vấn hợp đồng lưu trú hiện tại của Sinh viên, sau đó lấy thông tin phòng tương ứng với hợp đồng đó. Hệ thống hiển thị thông tin cá nhân và thông tin lưu trú hiện tại. Tất cả thông tin chỉ được xem và không cho phép chỉnh sửa. |

## 6. Thông tin được hiển thị

### 6.1. Thông tin cá nhân

Hệ thống hiển thị các thông tin cá nhân của Sinh viên được lưu trong hệ thống.

Các dữ liệu có thể bao gồm:

- MSSV
- Họ tên
- Ngày sinh
- CCCD
- Giới tính
- Quê quán
- Số điện thoại
- Lớp
- Niên khóa
- Trạng thái lưu trú

### 6.2. Thông tin hợp đồng lưu trú

Nếu Sinh viên có hợp đồng ở trạng thái:

`Còn hạn`

hệ thống hiển thị thông tin hợp đồng hiện tại.

Thông tin có thể gồm:

- Mã hợp đồng
- Ngày bắt đầu
- Ngày kết thúc
- Trạng thái hợp đồng

### 6.3. Thông tin phòng

Hệ thống lấy thông tin phòng từ hợp đồng lưu trú hiện tại của Sinh viên.

Thông tin có thể gồm:

- Số phòng
- Tòa nhà
- Tầng
- Giới tính phòng
- Sức chứa

## 7. Luồng phụ

### A1 - Sinh viên không có hợp đồng lưu trú hiện tại

- **Xảy ra tại:** Bước 2.
- **Điều kiện:** Hệ thống không tìm thấy hợp đồng của Sinh viên có trạng thái:

  `Còn hạn`

- **Xử lý:**
  - Hệ thống vẫn hiển thị thông tin cá nhân của Sinh viên.
  - Khu vực thông tin lưu trú không hiển thị hợp đồng hoặc phòng hiện tại.
  - Hệ thống hiển thị thông báo:

    **“Hiện tại bạn không có hợp đồng lưu trú còn hiệu lực.”**

## 8. Quy tắc nghiệp vụ

### BR-UC11-01 - Chỉ xem thông tin của chính mình

Sinh viên chỉ được xem thông tin cá nhân và thông tin lưu trú gắn với tài khoản đang đăng nhập.

Sinh viên không được sử dụng UC11 để xem hồ sơ của Sinh viên khác.

### BR-UC11-02 - Xác định Sinh viên từ phiên đăng nhập

Hệ thống phải xác định Sinh viên từ tài khoản đang đăng nhập.

Không cho phép người dùng tự nhập MSSV để yêu cầu xem thông tin lưu trú của một Sinh viên khác.

### BR-UC11-03 - Xác định hợp đồng lưu trú hiện tại

Thông tin lưu trú hiện tại được xác định từ hợp đồng có trạng thái:

`Còn hạn`

của Sinh viên đang đăng nhập.

### BR-UC11-04 - Xác định phòng từ hợp đồng

Phòng hiện tại của Sinh viên không được lưu trực tiếp trong bảng `SinhVien`.
### BR-UC11-05 - Chức năng chỉ đọc
UC11 chỉ cho phép Sinh viên:
- xem thông tin cá nhân;
- xem hợp đồng hiện tại;
- xem thông tin phòng hiện tại.
UC11 không cho phép chỉnh sửa bất kỳ dữ liệu nào
### BR-UC11-06 - Không có hợp đồng vẫn được xem thông tin cá nhân
Nếu Sinh viên không có hợp đồng còn hạn thì:
- thông tin cá nhân vẫn được hiển thị;
- chỉ khu vực thông tin lưu trú hiển thị trạng thái không có hợp đồng hiện tại.
Hệ thống xác định phòng theo quan hệ:

```text
SinhVien
    |
    | MSSV
    v
HopDong có TrangThai = "Còn hạn"
    |
    | MaPhong
    v
Phong