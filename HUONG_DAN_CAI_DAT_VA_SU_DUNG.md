# HƯỚNG DẪN CÀI ĐẶT VÀ VẬN HÀNH HỆ THỐNG KANTO-KTX

> **Đồ án 1:** Hệ thống Quản lý Ký túc xá Trường Đại học Kanto (Kanto-KTX)  
> **Giảng viên hướng dẫn:** Thầy Trịnh Thanh Duy  
> **Sinh viên thực hiện:** Nhóm thực hiện Đồ án 1  
> **Ngày cập nhật:** 01/11/2026  

---

## 1. Yêu cầu môi trường hệ thống

Để triển khai và chạy thử nghiệm phần mềm Kanto-KTX một cách mượt mà và ổn định nhất, khuyến nghị cấu hình máy tính đáp ứng các yêu cầu sau:

- **Hệ điều hành:** Windows 10 / Windows 11 (hoặc Linux Ubuntu 20.04+).
- **Môi trường Web Server:** Khuyến nghị cài đặt **WAMP Server 3.x 64-bit** (hoặc XAMPP 8.2+).
- **Ngôn ngữ PHP:** Phiên bản **PHP 8.2** hoặc **PHP 8.3** (đã kích hoạt sẵn các extension thông dụng: `pdo_mysql`, `mbstring`, `openssl`, `curl`).
- **Hệ quản trị CSDL:** **MySQL 8.0+** hoặc **MariaDB 10.5+** (chạy tại cổng mặc định `3306`).
- **Trình duyệt Web:** Google Chrome, Microsoft Edge, Cốc Cốc, Firefox (khuyến nghị độ phân giải màn hình từ 1280x720 trở lên).

---

## 2. Các bước cài đặt và khởi chạy (3 bước nhanh)

### Bước 1: Sao chép thư mục mã nguồn
Giải nén hoặc sao chép toàn bộ thư mục `Kanto-KTX` vào thư mục gốc của Web Server:
- **Nếu dùng WAMP:** Chép vào `C:\wamp64\www\Kanto-KTX` (hoặc `F:\wamp64\www\Kanto-KTX`).
- **Nếu dùng XAMPP:** Chép vào `C:\xampp\htdocs\Kanto-KTX`.

### Bước 2: Nhập cơ sở dữ liệu (Import Database)
1. Khởi động WAMP/XAMPP và mở trình quản lý cơ sở dữ liệu **phpMyAdmin** tại địa chỉ: `http://localhost/phpmyadmin/` (hoặc dùng DBeaver, Navicat, HeidiSQL).
2. Tạo một cơ sở dữ liệu mới với thông tin:
   - Tên database: `qlktx_v2`
   - Bảng mã (Collation): `utf8mb4_unicode_ci`
3. Chọn database `qlktx_v2` vừa tạo, bấm tab **Import (Nhập)**, chọn tệp tin:
   - Đường dẫn file: `sql/qlktx_v2.sql` (nằm sẵn trong thư mục mã nguồn).
   - Bấm **Import (Thực hiện)** để nạp toàn bộ cấu trúc 20 bảng và dữ liệu mẫu thực tế.
4. *Kiểm tra kết nối:* Tệp tin `config/db.php` đã được cấu hình mặc định chuẩn cho môi trường local:
   ```php
   $host = '127.0.0.1';
   $port = '3306';
   $dbname = 'qlktx_v2';
   $username = 'root';
   $password = ''; // Mặc định WAMP để trống
   ```

### Bước 3: Truy cập hệ thống
Mở trình duyệt Web và truy cập vào đường dẫn:
```
http://localhost/Kanto-KTX/
```
*(hoặc `http://127.0.0.1/Kanto-KTX/`)*

---

## 3. Danh sách tài khoản thử nghiệm theo từng vai trò (Demo Accounts)

Hệ thống đã được nạp sẵn kịch bản dữ liệu hoàn chỉnh để Thầy có thể kiểm thử toàn diện mọi quy trình nghiệp vụ:

> **Quy ước mật khẩu chung:** Mọi tài khoản bên dưới đều dùng mật khẩu là: **`123456`** (hoặc `1` đối với sinh viên).

| Vai trò | Tên đăng nhập | Mật khẩu | Kịch bản nghiệp vụ sẵn sàng kiểm thử |
|---|---|---|---|
| **1. Ban Quản lý KTX** | `ql001` | `123456` | Xem Dashboard tổng thể, biểu đồ phân tích tỷ lệ lấp đầy phòng KTX, thống kê công nợ và doanh thu hóa đơn. |
| **2. Nhân viên Quản lý** | `nvql001` | `123456` | **Đang có sẵn 4 đơn đăng ký mới của sinh viên chờ duyệt:**<br>• Bấm *Duyệt đơn* hoặc *Từ chối* trực tiếp.<br>• Quản lý hồ sơ sinh viên, quản lý hợp đồng lưu trú.<br>• Nhập chỉ số điện nước hàng tháng cho từng phòng.<br>• Tiếp nhận và xử lý phản ánh sự cố hỏng hóc từ sinh viên. |
| **3. Kế toán KTX** | `nvkt001` | `123456` | Quản lý hóa đơn tiền phòng và điện nước; lọc công nợ sinh viên; duyệt xác nhận thu tiền trực tiếp hoặc trực tuyến. |
| **4. Sinh viên (Chờ thanh toán)** | `KTPM2411054` | `1` (hoặc `123456`) | Sinh viên *Đỗ Quang Huy* (đơn đã được duyệt, đã có hóa đơn tiền phòng):<br>• Bấm *Xem hóa đơn* → Bấm *Tạo mã QR thanh toán mô phỏng* → Quét xác nhận thanh toán giả lập và nhận biên lai thành công. |
| **5. Sinh viên (Đang lưu trú)** | `KTPM2411056` | `1` (hoặc `123456`) | Sinh viên *Bùi Minh Khang* (đang ở phòng P0001, hợp đồng còn hiệu lực):<br>• Xem thông tin phòng và bạn cùng phòng.<br>• Gửi phản ánh sự cố kỹ thuật (điều hòa, bóng đèn...).<br>• Xem danh sách thông báo và biên lai điện nước. |
| **6. Khách / Sinh viên mới** | *(Không cần đăng nhập)* | — | Trực tiếp tại trang chủ:<br>• Bấm *Đăng ký lưu trú* trực tuyến theo 3 bước chọn tòa/phòng.<br>• Sử dụng *Trợ lý AI Chatbot* (nút tròn góc dưới bên phải) để hỏi đáp nội quy, chi phí và thủ tục. |

---

## 4. Hướng dẫn chạy bộ kiểm thử tự động (235 ca kiểm thử)

Hệ thống tích hợp sẵn bộ kiểm thử tự động hóa toàn diện bao phủ toàn bộ 18 Use Case:

1. Mở cửa sổ dòng lệnh (Terminal / PowerShell / CMD) tại thư mục dự án `Kanto-KTX`.
2. Chạy lệnh:
   ```bash
   php tests/run.php
   ```
3. **Kết quả:** Hệ thống tự động thực thi 5 bộ kiểm thử độc lập:
   - `tests/route_permission_middleware.php`: 106 ca kiểm tra định tuyến và phân quyền.
   - `tests/business_logic.php`: 70 ca kiểm tra nghiệp vụ và ràng buộc logic.
   - `tests/concurrency_acid_transaction.php`: 30 ca kiểm tra giao dịch ACID và khóa tương tranh.
   - `tests/boundary_security.php`: 16 ca kiểm tra bảo mật dữ liệu và giá trị biên.
   - `tests/renewal.php`: 13 ca kiểm tra chu kỳ gia hạn hợp đồng tự động (UC18).
   - **Tổng cộng:** **235/235 ca đạt 100% (Pass)**.
