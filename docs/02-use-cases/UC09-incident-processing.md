# UC09 - Xử lý phản ánh sự cố

## 1. Thông tin chung

- **Mã Use Case:** UC09
- **Tên Use Case:** Xử lý phản ánh sự cố
- **Tác nhân chính:** Nhân viên quản lý KTX

## 2. Mục đích

Cho phép Nhân viên quản lý KTX tiếp nhận các phản ánh sự cố của sinh viên, xem thông tin sự cố và ghi nhận kết quả xử lý cùng chi phí sửa chữa.

## 3. Tiền điều kiện

- Nhân viên quản lý KTX đã đăng nhập vào hệ thống.
- Trong hệ thống có ít nhất một phản ánh sự cố của sinh viên ở trạng thái **“Chờ xử lý”**.

## 4. Hậu điều kiện

Sau khi xử lý thành công:

- Trạng thái phản ánh sự cố được cập nhật thành **“Đã xử lý”**.
- Chi phí sửa chữa được lưu vào hệ thống.
- Hệ thống ghi nhận Nhân viên quản lý KTX đã thực hiện xử lý phản ánh.
- Phản ánh không còn xuất hiện trong danh sách phản ánh **“Chờ xử lý”**.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Nhân viên chọn chức năng **“Xử lý phản ánh sự cố”**. | |
| 2 | | Hệ thống truy vấn và hiển thị danh sách các phản ánh có trạng thái **“Chờ xử lý”**, được sắp xếp theo thời gian gửi từ sớm đến muộn. |
| 3 | Nhân viên chọn **“Xem chi tiết”** đối với phản ánh cần xử lý. | |
| 4 | | Hệ thống hiển thị thông tin chi tiết gồm Số phòng, Loại sự cố, Nội dung mô tả và Thời gian gửi. Các thông tin này chỉ được xem và không được chỉnh sửa. Hệ thống đồng thời hiển thị ô nhập **Chi phí sửa chữa**. |
| 5 | Sau khi thực hiện xử lý sự cố, Nhân viên nhập chi phí sửa chữa và nhấn **“Xác nhận xử lý”**. | |
| 6 | | Hệ thống kiểm tra tính hợp lệ của chi phí sửa chữa. Nếu dữ liệu hợp lệ, hệ thống ghi nhận Nhân viên xử lý, lưu chi phí sửa chữa và cập nhật trạng thái phản ánh thành **“Đã xử lý”**. Sau đó hệ thống hiển thị thông báo **“Cập nhật xử lý sự cố thành công.”** và cập nhật lại danh sách phản ánh đang chờ xử lý. |

## 6. Thông tin hiển thị khi xử lý

### 6.1. Thông tin chỉ đọc

Nhân viên được xem:

- Số phòng
- Loại sự cố
- Nội dung mô tả
- Thời gian gửi

Các thông tin trên không được chỉnh sửa trong UC09.

### 6.2. Thông tin được nhập

Nhân viên nhập:

- Chi phí sửa chữa

Chi phí sửa chữa là thông tin bắt buộc khi xác nhận xử lý.

Nếu sự cố không phát sinh chi phí thì Nhân viên nhập:

`0`

## 7. Luồng ngoại lệ

### E1 - Chi phí sửa chữa không hợp lệ

- **Xảy ra tại:** Bước 6.
- **Điều kiện:**
  - Giá trị chi phí sửa chữa không đúng định dạng số; hoặc
  - Giá trị chi phí sửa chữa nhỏ hơn `0`.
- **Xử lý:**
  - Hệ thống không cập nhật phản ánh.
  - Không thay đổi trạng thái phản ánh.
  - Không ghi nhận Nhân viên xử lý.
  - Hệ thống hiển thị thông báo:

    **“Chi phí sửa chữa không hợp lệ. Vui lòng nhập giá trị lớn hơn hoặc bằng 0.”**

### E2 - Chưa nhập chi phí sửa chữa

- **Xảy ra tại:** Bước 6.
- **Điều kiện:** Nhân viên để trống trường Chi phí sửa chữa.
- **Xử lý:**
  - Hệ thống không cập nhật phản ánh.
  - Không thay đổi trạng thái phản ánh.
  - Hệ thống hiển thị thông báo:

    **“Vui lòng nhập chi phí sửa chữa. Nhập 0 nếu sự cố không phát sinh chi phí.”**

## 8. Quy tắc nghiệp vụ

### BR-UC09-01 - Chỉ xử lý phản ánh đang chờ xử lý

Chỉ phản ánh có trạng thái:

`Chờ xử lý`

mới được phép thực hiện thao tác xác nhận xử lý.

Phản ánh đã có trạng thái:

`Đã xử lý`

không được xử lý lại trong UC09.

### BR-UC09-02 - Thứ tự hiển thị phản ánh

Danh sách phản ánh **“Chờ xử lý”** được sắp xếp theo:

`ThoiGianGui`

từ sớm đến muộn.

Phản ánh được gửi trước được hiển thị trước.

### BR-UC09-03 - Thông tin phản ánh không được chỉnh sửa

Trong quá trình xử lý, Nhân viên chỉ được xem:

- phòng;
- loại sự cố;
- mô tả;
- thời gian gửi.

UC09 không cho phép Nhân viên thay đổi các thông tin phản ánh ban đầu do Sinh viên gửi.

### BR-UC09-04 - Chi phí sửa chữa là bắt buộc

Trước khi xác nhận xử lý, Nhân viên phải nhập chi phí sửa chữa.
### BR-UC09-05 - Ghi nhận Nhân viên xử lý
Khi xử lý thành công, hệ thống phải ghi nhận Nhân viên quản lý KTX đang thực hiện thao tác là Nhân viên xử lý phản ánh.
### BR-UC09-07 - Cập nhật đồng thời dữ liệu xử lý
Khi xử lý thành công, hệ thống phải ghi nhận đồng thời:
MaNhanVienXuLy
ChiPhiSuaChua
TrangThai = "Đã xử lý"
Nếu việc xử lý không thành công thì không được để phản ánh ở trạng thái cập nhật dở dang.
Điều kiện hợp lệ:

```text
ChiPhiSuaChua >= 0