# PROJECT_STATE

## 1. Tổng quan dự án

Tên dự án:

Hệ thống Quản lý Ký túc xá Kanto

Công nghệ hiện tại:

- PHP 8.x
- MySQL
- PDO
- Apache
- Docker
- Git / GitHub
- Render
- Aiven MySQL
- DBeaver

---

## 2. Môi trường local

Project local chạy bằng WAMP.

Đường dẫn project:

`C:\wamp64\www\Kanto-KTX`

Local URL:

`http://localhost/Kanto-KTX/`

Local database:

`qlktx_v2`

Khi không có Environment Variables, `config/db.php` fallback về:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `qlktx_v2`
- User: `root`
- Password: rỗng

Không được phá khả năng chạy local trên WAMP.

---

## 3. Môi trường production

Project đã được deploy online.

Web hosting:

Render

Public domain hiện tại:

`https://kanto-ktx.onrender.com`

Render đã liên kết trực tiếp với GitHub repository.

Branch production:

`main`

Render Auto Deploy đang dùng source từ branch `main`.

Khi code được merge và push vào `main`, Render có thể tự động build và deploy phiên bản mới.

Không sửa cấu hình deployment nếu không cần thiết.

---

## 4. Docker

Project đã được chuẩn bị để deploy bằng Docker.

Các file liên quan hiện có:

- `Dockerfile`
- `.dockerignore`
- `config/docker-apache.conf`
- `config/docker-php.ini`

Không tự xóa hoặc thay đổi Docker config nếu chức năng đang triển khai không liên quan đến deployment.

---

## 5. Database production

Database production đang được host trên:

Aiven MySQL

Database hiện đang dùng:

`defaultdb`

Dữ liệu schema của project đã được import vào Aiven.

Ứng dụng trên Render kết nối Aiven qua Environment Variables.

Các key đang sử dụng:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

Không hard-code các giá trị production vào source code.

Không commit:

- password
- token
- URI chứa credential
- `.env`
- secret

---

## 6. Kết nối database trong source

`config/db.php` phải hỗ trợ cả:

### Local

WAMP + MySQL local

### Production

Render + Aiven MySQL

Production lấy cấu hình từ Environment Variables.

Không thay đổi cách kết nối database nếu không thực sự cần thiết cho UC đang triển khai.

---

## 7. Quản lý database

Database production hiện được quản lý bằng:

DBeaver

DBeaver đã kết nối thành công tới Aiven MySQL.

Có thể dùng DBeaver để:

- xem dữ liệu
- kiểm tra bảng
- chạy SQL
- kiểm tra kết quả sau khi thao tác trên website

Không cần tạo thêm công cụ quản trị database trong source code.

---

## 8. Encoding

Database và ứng dụng hiện sử dụng UTF-8 / `utf8mb4`.

Vấn đề hiển thị tiếng Việt ở Aiven trước đây đã được xử lý.

Không thay đổi charset/collation nếu không có lý do rõ ràng.

---

## 9. Git workflow

Không code trực tiếp trên `main` cho chức năng mới.

Mỗi Use Case triển khai trên branch riêng.

Ví dụ UC03:

`feature/uc03-approval`

Quy trình:

1. Cập nhật `main`.
2. Tạo branch feature.
3. Code.
4. Test.
5. Commit.
6. Push branch.
7. Tạo Pull Request.
8. Review.
9. Merge vào `main`.
10. Render auto deploy sau khi `main` thay đổi.

Không merge code chưa test vào `main`.

---

## 10. Trạng thái chức năng hiện tại

### Hoàn thành

- UC01 - Đăng nhập
- Authentication
- Session
- Role middleware
- CLI tests cho login/auth
- UC02 - Đăng ký lưu trú
- Dữ liệu diện ưu tiên
- Dữ liệu tòa nhà/phòng
- Chọn phòng
- Deploy Render
- Aiven MySQL
- DBeaver quản lý database

### Đang chuẩn bị triển khai

- UC03 - Xét duyệt đơn đăng ký

---

## 11. UC02 hiện tại

UC02 đã hoạt động trên production.

Luồng chính:

1. Khách nhập thông tin.
2. Chọn diện ưu tiên.
3. Hệ thống lọc phòng phù hợp.
4. Người dùng chọn phòng.
5. Xác nhận.
6. Backend kiểm tra lại sức chứa.
7. Tạo `DonDangKy`.
8. `TrangThai = "Chờ duyệt"`.

Số chỗ khả dụng:

`SucChua - số HopDong Còn hạn - số DonDangKy Chờ duyệt`

Một đơn `Chờ duyệt` giữ đúng 1 chỗ.

---

## 12. Quy tắc trước khi triển khai UC03

Trước khi sửa code UC03, phải đọc:

- đặc tả UC03 trong `docs/02-use-cases/`
- Sequence Diagram UC03
- Wireframe UC03
- `DATABASE_DESIGN.md`
- schema SQL hiện tại
- source code UC02
- authentication / role middleware
- code hiện có liên quan đến `DonDangKy`, `SinhVien`, `TaiKhoan`, `HopDong`

Trước khi code, phải báo lại:

- file nào có thể tái sử dụng
- file nào cần tạo mới
- model/helper/middleware nào đã có
- bảng nào sẽ đọc
- bảng nào sẽ ghi
- transaction nào cần dùng
- các điểm mâu thuẫn giữa tài liệu và schema nếu có

Không tự bổ sung nghiệp vụ.

---

## 13. Quy tắc bảo vệ phần đã ổn định

Không tự ý sửa:

- Docker config
- Render config
- Aiven config
- `config/db.php`
- authentication
- session
- role middleware
- schema database
- UC02

trừ khi UC03 thực sự yêu cầu và phải báo trước.

Nếu phát hiện vấn đề liên quan các phần trên, chỉ báo lại trước khi sửa.