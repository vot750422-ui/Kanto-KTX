# UC01 - Đăng nhập

## 1. Thông tin chung

- **Mã Use Case:** UC01
- **Tên Use Case:** Đăng nhập
- **Tác nhân chính:**
  - Sinh viên
  - Nhân viên quản lý KTX
  - Nhân viên kế toán
  - Quản lý KTX

## 2. Mục đích

Xác thực danh tính người dùng và cấp quyền truy cập vào các chức năng tương ứng trong hệ thống.

## 3. Tiền điều kiện

- Người dùng đã được cấp tài khoản trong hệ thống.

## 4. Hậu điều kiện

- Người dùng truy cập thành công vào giao diện chính phù hợp với vai trò.
- Hệ thống khởi tạo phiên làm việc (Session).

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Tác nhân chọn **“Đăng nhập”** từ trang chủ. | |
| 2 | | Hệ thống hiển thị giao diện đăng nhập gồm các trường: **Tên đăng nhập** và **Mật khẩu**. |
| 3 | Tác nhân nhập tên đăng nhập và mật khẩu, sau đó chọn nút **“Đăng nhập”**. | |
| 4 | | Hệ thống kiểm tra dữ liệu nhập vào, truy vấn bảng `TaiKhoan` để đối chiếu thông tin và kiểm tra trạng thái tài khoản. Nếu xác thực thành công, hệ thống khởi tạo phiên đăng nhập (Session) và điều hướng người dùng đến giao diện chính tương ứng với vai trò. |

## 6. Luồng ngoại lệ

### E1 - Để trống thông tin

- **Xảy ra tại:** Bước 4.
- **Điều kiện:** Hệ thống nhận thấy một hoặc cả hai trường dữ liệu bị bỏ trống.
- **Xử lý:**
  - Hệ thống hiển thị thông báo:

    **“Vui lòng nhập đầy đủ Tên đăng nhập và Mật khẩu!”**

  - Hệ thống làm nổi bật ô dữ liệu còn thiếu.
  - Người dùng nhập lại thông tin để tiếp tục đăng nhập.

### E2 - Sai thông tin đăng nhập

- **Xảy ra tại:** Bước 4.
- **Điều kiện:** Hệ thống không tìm thấy tên đăng nhập hoặc mật khẩu không khớp với dữ liệu trong bảng `TaiKhoan`.
- **Xử lý:**
  - Hệ thống hiển thị thông báo:

    **“Tên đăng nhập hoặc Mật khẩu không chính xác”**

  - Người dùng nhập lại thông tin đăng nhập.

### E3 - Tài khoản đang bị khóa

- **Xảy ra tại:** Bước 4.
- **Điều kiện:** Hệ thống xác định trạng thái tài khoản trong cơ sở dữ liệu là **“Khóa”**.
- **Xử lý:**
  - Hệ thống không cho phép đăng nhập.
  - Hệ thống hiển thị thông báo:

    **“Tài khoản của bạn hiện đang bị khóa!”**

## 7. Dữ liệu liên quan

### Bảng `TaiKhoan`

Các dữ liệu chính được sử dụng trong Use Case:

- `MaTK`
- `TenDangNhap`
- `MatKhau`
- `VaiTro`
- `TrangThai`

## 8. Quy tắc nghiệp vụ

- Chỉ tài khoản có trạng thái **“Hoạt động”** mới được phép đăng nhập.
- Sau khi xác thực thành công, hệ thống phải khởi tạo Session cho người dùng.
- Người dùng được điều hướng đến giao diện phù hợp với `VaiTro`.
- Các vai trò hợp lệ của hệ thống gồm:
  - Sinh viên
  - Nhân viên quản lý KTX
  - Nhân viên kế toán
  - Quản lý KTX
- Đăng nhập thất bại không được tạo phiên đăng nhập hợp lệ.

## 9. Kết quả điều hướng theo vai trò

| Vai trò | Khu vực truy cập |
|---|---|
| Sinh viên | Giao diện dành cho Sinh viên |
| Nhân viên quản lý KTX | Giao diện dành cho Nhân viên quản lý KTX |
| Nhân viên kế toán | Giao diện dành cho Nhân viên kế toán |
| Quản lý KTX | Giao diện dành cho Quản lý KTX |

## 10. Ghi chú triển khai

- Chức năng đăng nhập sử dụng Session để lưu trạng thái đăng nhập của người dùng.
- Việc kiểm tra quyền truy cập sau khi đăng nhập phải dựa trên vai trò của tài khoản.
- Trạng thái tài khoản phải được kiểm tra trước khi cho phép người dùng truy cập hệ thống.
- Use Case này không bao gồm chức năng đăng ký tài khoản.
- Tài khoản sinh viên được tạo thông qua quy trình xét duyệt đăng ký lưu trú, không được tạo trực tiếp tại màn hình đăng nhập.

## 11. Không được tự ý thay đổi

Khi triển khai UC01, không được tự ý:

- Thêm chức năng đăng ký tài khoản vào màn hình đăng nhập.
- Thêm vai trò mới ngoài các vai trò đã được xác định.
- Bỏ bước kiểm tra trạng thái tài khoản.
- Thay đổi giá trị trạng thái **“Hoạt động”** và **“Khóa”** nếu chưa cập nhật đồng bộ CSDL và tài liệu nghiệp vụ.
- Cho phép người dùng truy cập khu vực không đúng với vai trò của tài khoản.