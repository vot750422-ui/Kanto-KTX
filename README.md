# HỆ THỐNG QUẢN LÝ KÍ TÚC XÁ – TRƯỜNG ĐẠI HỌC KANTO

Đồ án 1 – Ngành Kỹ thuật Phần mềm – Lớp KTPM2411
Trường Đại học Kỹ thuật – Công nghệ Cần Thơ, Khoa Công nghệ Thông tin

| | |
|---|---|
| Sinh viên thực hiện | Phan Minh Trọng – KTPM2411047 |
| | Hồ Hoàng Như Ý – KTPM2411004 |
| Giảng viên hướng dẫn | ThS. Trịnh Thanh Duy |
| Phiên bản mã nguồn | `db32e89` (nhánh `main`) |
| Ngày đóng gói | 07/11/2026 |

Ứng dụng web PHP thuần (mô hình MVC, không framework), CSDL MySQL, giao diện tiếng Việt,
phục vụ 18 Use Case (UC01–UC18) với bốn vai trò tài khoản và một tác nhân hệ thống (Timer).

---

## 1. Yêu cầu môi trường

| Thành phần | Yêu cầu | Đã kiểm thử |
|---|---|---|
| PHP | ≥ 8.1, có `pdo_mysql`, `mbstring`, `openssl` | PHP 8.3 / 8.5 |
| MySQL | 8.0 trở lên (hoặc MariaDB 10.4+) | MySQL 8.4.7 |
| Web server | Apache có `mod_rewrite`, hoặc WAMP/XAMPP | WAMP 3.x |
| Composer | Không bắt buộc – chỉ cần khi dùng gửi email qua SMTP (PHPMailer) | Composer 2 |
| Node.js | Không bắt buộc – chỉ để chạy 12 ca kiểm thử frontend | Node 20+ |

Ứng dụng **không cần `composer install`** để chạy các chức năng nghiệp vụ. Thư mục `vendor/`
chỉ chứa PHPMailer phục vụ adapter SMTP; nếu thiếu, hệ thống vẫn hoạt động và bỏ qua bước gửi thư.

---

## 2. Cài đặt trên WAMP (khuyến nghị khi chấm/demo)

### Bước 1 – Sao chép mã nguồn

Chép thư mục `Kanto-KTX` vào thư mục web root của WAMP:

```
F:\wamp64\www\Kanto-KTX        (hoặc C:\wamp64\www\Kanto-KTX)
```

Địa chỉ truy cập mặc định: **http://localhost/Kanto-KTX/**

### Bước 2 – Tạo cơ sở dữ liệu

Mở phpMyAdmin (http://localhost/phpmyadmin) → tab **Import**:

1. Nhập `sql/qlktx_v2_final.sql` — tệp này tự tạo CSDL `qlktx_v2` (utf8mb4) và nạp đủ 21 bảng kèm dữ liệu mẫu.
2. Nhập tiếp `sql/fk-restore-after-import.sql` — bổ sung **10 khóa ngoại còn thiếu** (bản dump đã có 20),
   đưa CSDL về đủ **30 ràng buộc** như thiết kế. Không chạy tệp này thì các bảng
   `hoadondiennuoc`, `hopdong`, `phananhsuco`, `phong` thiếu ràng buộc tham chiếu.

Hoặc dùng dòng lệnh:

```powershell
& 'F:\wamp64\bin\mysql\mysql8.4.7\bin\mysql.exe' -u root < sql\qlktx_v2_final.sql
& 'F:\wamp64\bin\mysql\mysql8.4.7\bin\mysql.exe' -u root qlktx_v2 < sql\fk-restore-after-import.sql
```

> Các tệp `sql/uc*.sql`, `sql/seed-buildings-rooms.sql`, `sql/update-priorities.sql` là migration
> lịch sử, **đã nằm trong bản dump cuối**; chỉ chạy lại khi cần dựng CSDL trống từ bản cũ.

### Bước 3 – Cấu hình kết nối

`config/connection.php` đọc biến môi trường, có giá trị mặc định đúng cho WAMP:

| Biến | Mặc định | Ghi chú |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | |
| `DB_PORT` | `3306` | |
| `DB_NAME` | `qlktx_v2` | |
| `DB_USER` | `root` | |
| `DB_PASSWORD` | *(rỗng)* | Mật khẩu MySQL của máy chạy |
| `APP_BASE_PATH` | `/Kanto-KTX` | Đặt `/` khi chạy Docker/domain gốc |

Không cần sửa mã nguồn: đặt biến môi trường của Windows/Apache, hoặc tạo `SetEnv` trong VirtualHost.
Kiểm tra nhanh kết nối: mở `config/test-db.php` bằng trình duyệt (chỉ dùng khi cài đặt).

### Bước 4 – Phân quyền thư mục lưu minh chứng

Thư mục `storage/registration/` là nơi lưu ảnh minh chứng đăng ký (đã có `.htaccess` chặn truy cập trực tiếp).
Trên Windows thường không cần làm gì; trên Linux phải cho phép ghi:

```bash
chmod -R 775 storage && chown -R www-data:www-data storage
```

### Bước 5 – Chạy

Mở http://localhost/Kanto-KTX/ → trang chủ công khai (đăng ký, xem phòng, hướng dẫn, trợ lý AI) → **Đăng nhập**.

---

## 3. Tài khoản mẫu (có sẵn trong CSDL)

| Tên đăng nhập | Mật khẩu | Vai trò | Khu vực sau đăng nhập |
|---|---|---|---|
| `sv001` | `123456` | Sinh viên | `student-dashboard` |
| `nvql001` | `123456` | Nhân viên quản lý KTX | `staff-dashboard` |
| `nvkt001` | `123456` | Nhân viên kế toán | `payment` (tra cứu/thu phí) |
| `ql001` | `123456` | Quản lý KTX | `statistics` |

Ngoài ra CSDL có các tài khoản sinh viên khác (`KTPM2411xxx`) với mật khẩu riêng do nhóm đặt.

> Khi một đơn đăng ký được **duyệt** (UC03), hệ thống tự tạo tài khoản sinh viên với
> **tên đăng nhập = MSSV** và **mật khẩu tạm = `1`** (`controllers/Approval.php:145`).

---

## 4. Cấu hình tùy chọn

### 4.1. Gửi email xét duyệt (UC03)

```powershell
Copy-Item config\mail.example.php config\mail.local.php
# điền host / port / username / password / from_address
```

- `MAIL_TRANSPORT=smtp` (mặc định) → dùng PHPMailer, cần `composer install` để có `vendor/`.
- `MAIL_TRANSPORT=brevo` → gọi HTTPS API, cần `BREVO_API_KEY`, không cần PHPMailer.
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` ghi đè thông tin người gửi.

Không cấu hình email thì luồng nghiệp vụ vẫn chạy; thư xét duyệt được xếp hàng trong bảng
`emailthongbao` và có thể gửi lại ở trang xét duyệt.

### 4.2. Mã QR thanh toán (UC17)

```powershell
Copy-Item config\payment.example.php config\payment.local.php
# đổi thành IPv4 Wi-Fi của máy chạy Apache, ví dụ http://192.168.1.10
```

Hoặc đặt `PAYMENT_PUBLIC_ORIGIN`. Mục đích: điện thoại quét mã QR vẫn mở được trang xác nhận
khi máy chủ chạy trên máy tính nội bộ. Không cấu hình thì hệ thống dùng origin của trang hiện tại.

### 4.3. Trợ lý AI (UC13)

Đặt `GEMINI_API_KEY` (tùy chọn `GEMINI_MODEL`, `GEMINI_FALLBACK_MODEL`).
Không có khóa, chatbot trả lời bằng bộ câu hỏi – đáp án tĩnh đã định nghĩa sẵn; không lỗi giao diện.

### 4.4. Tác vụ gia hạn tự động (UC18)

`RENEWAL_TOKEN` (mặc định `kanto_renewal_secret_2026`) là khóa chia sẻ giữa bộ định thời và
endpoint `renewal-run`; gọi bằng header `X-Renewal-Token`.

---

## 5. Migration (chỉ cần khi nâng cấp từ CSDL cũ)

```powershell
php scripts/migrate-payment-links.php        # bảng token QR liên thiết bị (UC17)
php scripts/migrate-approval-email.php       # bảng hàng đợi email xét duyệt (UC03)
php scripts/migrate-contract-settlement.php  # cột thanh lý hợp đồng (UC05.2)
php scripts/migrate-staff-profiles.php       # hồ sơ nhân viên (chạy lại an toàn)
```

Các script đều chạy lại được nhiều lần (idempotent). Bản dump `qlktx_v2_final.sql` đã bao gồm
toàn bộ thay đổi trên.

---

## 6. Tác vụ định kỳ (Timer – UC10, UC18)

| Việc | Lệnh | Lịch gợi ý |
|---|---|---|
| Lập hóa đơn tiền phòng | `php scripts/generate-room-invoices.php` | 00:05 ngày 01 mỗi tháng |
| Lập hóa đơn điện nước | `php scripts/generate-utility-invoices.php` | 00:10 ngày 01 mỗi tháng |
| Gia hạn hợp đồng | `POST index.php?action=renewal-run` + header `X-Renewal-Token` | 01:00 mỗi ngày |

Windows: dùng `scripts/register-room-invoice-task.ps1`, `scripts/register-utility-task.ps1`
(đăng ký Task Scheduler) hoặc `scripts/run-room-invoice-task.bat`, `scripts/run-utility-task.bat`.
Có thể chạy tay các script trên để tạo dữ liệu demo.

---

## 7. Kiểm thử

Chạy từ thư mục gốc dự án, sau khi đã bật MySQL và nạp CSDL:

```powershell
php -d xdebug.mode=off tests\run.php          # 106 ca: route + phân quyền 4 vai trò
php -d xdebug.mode=off tests\registration.php # UC02
php -d xdebug.mode=off tests\approval.php     # UC03
php -d xdebug.mode=off tests\rooms.php        # UC02/UC15 – seed 200 phòng
php -d xdebug.mode=off tests\room-information.php
php -d xdebug.mode=off tests\contracts.php    # UC05
php -d xdebug.mode=off tests\utility-meters.php
php -d xdebug.mode=off tests\incidents.php
php -d xdebug.mode=off tests\notifications.php
php -d xdebug.mode=off tests\statistics.php
php -d xdebug.mode=off tests\payment.php      # UC07
php -d xdebug.mode=off tests\room-invoice-generation.php   # UC10.1
php -d xdebug.mode=off tests\utility-invoice-generation.php # UC10.2
php -d xdebug.mode=off tests\student-payments.php  # UC17
php -d xdebug.mode=off tests\renewal.php      # UC18
php -d xdebug.mode=off tests\chatbot.php
php -d xdebug.mode=off tests\chatbot-rooms.php
php -d xdebug.mode=off tests\chatbot-gateway.php
php -d xdebug.mode=off tests\brevo-mailer.php
php -d xdebug.mode=off tests\smtp-mailer.php
php -d xdebug.mode=off tests\guide.php
php -d xdebug.mode=off tests\worker.php
node tests\frontend.js                        # 12 ca giao diện (không cần npm)
```

- Tổng: **576 ca** trong 24 tệp kiểm thử, cộng kịch bản đồng thời `tests/concurrency-registration.php`
  (kiểm tra hai tiến trình tranh chỗ, chạy bằng 2 cửa sổ PowerShell).
- Các bộ kiểm thử dùng bảng `TEMPORARY` nên **không sửa dữ liệu nghiệp vụ thật**.
- Kiểm tra cú pháp toàn bộ mã nguồn: `powershell -File tools\lint-all.ps1`.
- Chi tiết từng bộ: xem `tests/README.md`.

Kịch bản kiểm thử đồng thời (cần 2 cửa sổ, chạy lần lượt trong cùng khoảng thời gian):

```powershell
php tests\concurrency-registration.php
```

---

## 8. Cấu trúc thư mục

```
Kanto-KTX/
├── index.php                # Front controller + bảng route
├── config/                  # Kết nối CSDL, hằng môi trường, cấu hình Apache/PHP cho Docker
├── middleware/              # Auth.php (phiên), Role.php (phân quyền theo vai trò)
├── controllers/             # 18 controller ứng với các Use Case
├── models/                  # Truy cập dữ liệu (PDO, prepared statement)
├── services/                # PaymentLink, SmtpMailer, BrevoMailer, ApprovalMailer…
├── views/                   # Giao diện theo vai trò (public / student / staff / manager)
├── includes/                # Header, navbar, menu tài khoản, chatbot widget
├── assets/                  # CSS, JS, ảnh; thư viện QR nội bộ (không gọi CDN)
├── scripts/                 # Tác vụ định kỳ + migration + đăng ký Task Scheduler
├── sql/                     # DDL và dữ liệu mẫu (nguồn sự thật: qlktx_v2_final.sql)
├── storage/registration/    # Ảnh minh chứng đăng ký (chặn truy cập trực tiếp bằng .htaccess)
├── tests/                   # 25 tệp kiểm thử (PHP CLI + Node)
├── tools/                   # lint-all.ps1, tiện ích nội bộ
├── Dockerfile               # Ảnh php:8.3-apache + cấu hình bảo vệ thư mục nội bộ
└── composer.json            # Chỉ phụ thuộc PHPMailer (tùy chọn)
```

---

## 9. Triển khai bằng Docker (tùy chọn)

```bash
docker build -t kanto-ktx .
docker run -d -p 8080:80 \
  -e DB_HOST=host.docker.internal -e DB_NAME=qlktx_v2 \
  -e DB_USER=root -e DB_PASSWORD=yourpass -e APP_BASE_PATH=/ \
  kanto-ktx
```

`config/docker-apache.conf` đặt `AllowOverride None` và dùng `DirectoryMatch … Require all denied`
để chặn truy cập trực tiếp vào `config`, `controllers`, `models`, `middleware`, `services`,
`storage`, `scripts`, `vendor` cùng các tệp `.sql`, `.ini`, `.conf`, `.log`.

---

## 10. Giới hạn đã biết

- Thanh toán là **mô phỏng** (VietQR sinh mã, không kết nối cổng thanh toán thật).
- Trợ lý AI phụ thuộc khóa Gemini; khi thiếu khóa hoặc nhà cung cấp lỗi, hệ thống trả lời bằng
  bộ đáp án tĩnh cho một số câu hỏi đã định nghĩa.
- Không có chức năng đổi/khôi phục mật khẩu qua email; tài khoản do bộ phận quản lý KTX cấp.
- Ứng dụng chưa có API công khai cho thiết bị di động; giao diện tối ưu cho trình duyệt máy tính
  và có responsive cơ bản cho điện thoại khi quét QR thanh toán.

---

## 11. Liên hệ

Mọi góp ý về mã nguồn xin gửi về nhóm thực hiện:
Phan Minh Trọng (KTPM2411047), Hồ Hoàng Như Ý (KTPM2411004) – lớp KTPM2411.
