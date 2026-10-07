# UC06 - Ghi nhận chỉ số điện nước

## 1. Thông tin chung

- **Mã Use Case:** UC06
- **Tên Use Case:** Ghi nhận chỉ số điện nước
- **Tác nhân chính:** Nhân viên quản lý KTX

## 2. Mục đích

Cho phép Nhân viên quản lý KTX ghi nhận chỉ số điện và nước của từng phòng theo tháng, làm cơ sở để hệ thống lập hóa đơn điện nước cho kỳ tương ứng.

## 3. Tiền điều kiện

- Nhân viên quản lý KTX đã đăng nhập vào hệ thống.
- Trong hệ thống đã có dữ liệu phòng.
- Phòng cần ghi nhận chỉ số đang có ít nhất một sinh viên lưu trú.

## 4. Hậu điều kiện

Sau khi ghi nhận thành công:

- Chỉ số điện mới của phòng được lưu vào hệ thống.
- Chỉ số nước mới của phòng được lưu vào hệ thống.
- Hệ thống ghi nhận thời gian thực hiện.
- Bản ghi chỉ số được gắn với phòng và kỳ ghi nhận tương ứng.
- Dữ liệu chỉ số được sử dụng làm cơ sở cho việc lập hóa đơn điện nước.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Nhân viên chọn chức năng **“Ghi nhận chỉ số điện nước”**. | |
| 2 | | Hệ thống hiển thị giao diện ghi nhận chỉ số điện nước. Danh sách phòng chỉ gồm các phòng đang có sinh viên lưu trú. |
| 3 | Nhân viên chọn phòng cần ghi nhận chỉ số. | |
| 4 | | Hệ thống truy vấn bản ghi chỉ số điện nước gần nhất của phòng, sau đó hiển thị **chỉ số điện cũ** và **chỉ số nước cũ**. Các giá trị này chỉ được xem và không được phép chỉnh sửa. |
| 5 | Nhân viên nhập **chỉ số điện mới**, **chỉ số nước mới** và nhấn **“Lưu”**. | |
| 6 | | Hệ thống kiểm tra tính hợp lệ của dữ liệu. Nếu hợp lệ, hệ thống lưu chỉ số điện, chỉ số nước và thời gian ghi nhận vào bảng `ChiSoDienNuoc`, sau đó hiển thị thông báo **“Lưu thành công.”** |

## 6. Luồng ngoại lệ

### E1 - Chỉ số mới nhỏ hơn chỉ số cũ

- **Xảy ra tại:** Bước 6.
- **Điều kiện:** Hệ thống phát hiện:
  - Chỉ số điện mới nhỏ hơn chỉ số điện cũ; hoặc
  - Chỉ số nước mới nhỏ hơn chỉ số nước cũ.
- **Xử lý:**
  - Hệ thống không lưu bản ghi chỉ số mới.
  - Hệ thống hiển thị thông báo:

    **“Chỉ số mới không được nhỏ hơn chỉ số cũ!”**

  - Nhân viên kiểm tra và nhập lại dữ liệu.

### E2 - Dữ liệu điện nước không hợp lệ

- **Xảy ra tại:** Bước 6.
- **Điều kiện:**
  - Chỉ số điện hoặc chỉ số nước bị bỏ trống; hoặc
  - Dữ liệu nhập không đúng định dạng quy định.
- **Xử lý:**
  - Hệ thống không lưu dữ liệu.
  - Hệ thống hiển thị lỗi tại trường tương ứng.
  - Nhân viên nhập lại dữ liệu hợp lệ.

### E3 - Phòng đã được ghi nhận chỉ số trong kỳ hiện tại

- **Xảy ra tại:** Bước 6.
- **Điều kiện:** Hệ thống phát hiện phòng đã có bản ghi chỉ số điện nước của tháng và năm hiện tại.
- **Xử lý:**
  - Hệ thống không tạo thêm bản ghi mới.
  - Hệ thống hiển thị thông báo:

    **“Phòng này đã được ghi nhận chỉ số điện nước trong tháng hiện tại.”**

## 7. Quy tắc nghiệp vụ

### BR-UC06-01 - Chỉ ghi nhận cho phòng đang có sinh viên lưu trú

Danh sách phòng tại giao diện ghi nhận chỉ bao gồm các phòng đang có ít nhất một sinh viên lưu trú.

Phòng không có sinh viên lưu trú không được đưa vào danh sách ghi nhận chỉ số của UC06.

### BR-UC06-02 - Chỉ số cũ chỉ được xem

Chỉ số điện cũ và chỉ số nước cũ được lấy từ bản ghi gần nhất của phòng.

Các giá trị này:

- chỉ dùng để đối chiếu;
- không được phép chỉnh sửa trong UC06.

### BR-UC06-03 - Chỉ số mới không được nhỏ hơn chỉ số cũ

Điều kiện hợp lệ:

```text
ChiSoDienMoi >= ChiSoDienCu