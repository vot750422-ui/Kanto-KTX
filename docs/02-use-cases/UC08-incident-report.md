# UC08 - Gửi phản ánh sự cố

## 1. Thông tin chung

- **Mã Use Case:** UC08
- **Tên Use Case:** Gửi phản ánh sự cố
- **Tác nhân chính:** Sinh viên

## 2. Mục đích

Cho phép Sinh viên phản ánh các sự cố, hư hỏng liên quan đến phòng ở hoặc cơ sở vật chất trong quá trình lưu trú để Nhân viên quản lý KTX tiếp nhận và xử lý.

## 3. Tiền điều kiện

- Sinh viên đã đăng nhập vào hệ thống.
- Sinh viên đang có hợp đồng lưu trú ở trạng thái **“Còn hạn”**.

## 4. Hậu điều kiện

Sau khi gửi phản ánh thành công:

- Một phản ánh sự cố mới được lưu vào hệ thống.
- Phản ánh có trạng thái ban đầu là **“Chờ xử lý”**.
- Phản ánh được gắn với sinh viên gửi phản ánh.
- Phản ánh được gắn với phòng hiện tại của sinh viên.
- Hệ thống tự động ghi nhận thời gian gửi phản ánh.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Sinh viên chọn chức năng **“Gửi phản ánh sự cố”**. | |
| 2 | | Hệ thống xác định phòng hiện tại của sinh viên từ thông tin lưu trú và hiển thị giao diện gửi phản ánh. Số phòng được tự động hiển thị và không cho phép chỉnh sửa. Giao diện gồm trường **Loại sự cố** và **Mô tả chi tiết**. |
| 3 | Sinh viên chọn loại sự cố, nhập mô tả chi tiết và nhấn **“Gửi phản ánh”**. | |
| 4 | | Hệ thống kiểm tra tính hợp lệ của thông tin được nhập. Nếu dữ liệu hợp lệ, hệ thống tạo phản ánh mới, ghi nhận sinh viên, phòng, loại sự cố, nội dung mô tả, thời gian gửi và trạng thái **“Chờ xử lý”**. Hệ thống hiển thị thông báo **“Gửi phản ánh sự cố thành công.”** |

## 6. Thông tin trên giao diện

### 6.1. Số phòng

- Được hệ thống tự động xác định từ hợp đồng lưu trú còn hạn của sinh viên.
- Chỉ được hiển thị để sinh viên kiểm tra.
- Sinh viên không được phép chỉnh sửa.

### 6.2. Loại sự cố

Sinh viên lựa chọn một trong các loại sự cố đã được hệ thống quy định:

- `Điện`
- `Nước`
- `Nội thất`
- `Khác`

### 6.3. Mô tả chi tiết

Sinh viên nhập nội dung mô tả sự cố hoặc hư hỏng cần phản ánh.

## 7. Luồng ngoại lệ

### E1 - Chưa chọn loại sự cố

- **Xảy ra tại:** Bước 4.
- **Điều kiện:** Sinh viên chưa lựa chọn loại sự cố.
- **Xử lý:**
  - Hệ thống không tạo phản ánh.
  - Hệ thống hiển thị thông báo:

    **“Vui lòng chọn loại sự cố.”**

  - Sinh viên lựa chọn loại sự cố và thực hiện gửi lại.

### E2 - Mô tả không hợp lệ

- **Xảy ra tại:** Bước 4.
- **Điều kiện:**
  - Nội dung mô tả bị bỏ trống; hoặc
  - Nội dung mô tả chưa đáp ứng độ dài tối thiểu theo quy định.
- **Xử lý:**
  - Hệ thống không tạo phản ánh.
  - Hệ thống hiển thị lỗi tại trường mô tả.
  - Sinh viên nhập lại nội dung hợp lệ trước khi gửi.

## 8. Quy tắc nghiệp vụ

### BR-UC08-01 - Chỉ sinh viên đang lưu trú được gửi phản ánh

Sinh viên chỉ được sử dụng chức năng gửi phản ánh khi có hợp đồng lưu trú ở trạng thái:

`Còn hạn`

Nếu sinh viên không có hợp đồng còn hạn thì không được tạo phản ánh sự cố theo UC08.

### BR-UC08-02 - Phòng được xác định tự động

Phòng của phản ánh không do sinh viên tự lựa chọn.

Hệ thống xác định phòng dựa trên hợp đồng còn hạn của sinh viên:

```text
SinhVien
    |
    v
HopDong có TrangThai = "Còn hạn"
    |
    v
MaPhong