# Email kết quả xét duyệt và giờ Việt Nam

## Hành vi

- UC02 bắt buộc email hợp lệ, tối đa 100 ký tự; hiển thị ở bước xác nhận và lưu vào `dondangky.Email`.
- UC03 duyệt: sao chép email vào `sinhvien.Email`; thông báo gồm họ tên, mã đơn, MSSV/tên đăng nhập, mã hóa đơn và hướng dẫn liên hệ quản lý KTX để đăng nhập/thanh toán. Không gửi mật khẩu; chưa thêm luồng kích hoạt tài khoản.
- UC03 từ chối: gửi kết quả và lý do từ chối đến email trong đơn.
- Bản ghi `emailthongbao` được tạo trong cùng transaction xét duyệt. Sau commit và giải phóng khóa xét duyệt, controller mới gửi SMTP qua [PHPMailer](https://github.com/PHPMailer/PHPMailer).
- SMTP lỗi/thiếu cấu hình: kết quả xét duyệt vẫn được giữ; hiển thị cảnh báo và trạng thái `Lỗi`. Nhân viên quản lý KTX mở mục **Thông báo email cần kiểm tra**, vào chi tiết đơn rồi chọn **Gửi lại email**. POST cần đúng vai trò qua middleware của route.
- Mỗi đơn có một thông báo; đơn đã gửi không gửi lại. Named lock theo CSDL/mã đơn ngăn hai yêu cầu cùng gửi. `Đã gửi` nghĩa là máy chủ SMTP đã tiếp nhận, chưa khẳng định thư nằm trong Inbox; SMTP có thể chuyển thư vào Spam hoặc trả thư sau đó.
- Hồ sơ cũ thiếu email vẫn duyệt/từ chối được, ghi `Không có email`; không đoán email sinh viên. Đơn đã xử lý trước khi cài chức năng không được gửi hồi tố.
- Nếu tiến trình dừng sau commit nhưng trước SMTP, yêu cầu `Chờ gửi` vẫn còn trong CSDL. Nếu SMTP đã nhận nhưng kết nối bị ngắt hoặc ghi trạng thái DB thất bại, lần thử sau có thể tạo thư trùng; Message-ID ổn định hỗ trợ đối chiếu nhưng không bảo đảm exactly-once.

## Chuẩn bị WAMP

1. Cài dependency từ lock: `composer install --no-dev --prefer-dist`.
2. Chạy `php -d xdebug.mode=off scripts/migrate-approval-email.php` trên CSDL đích. Script kiểm tra cột Email có sẵn rồi tạo bảng mới nếu chưa có; không sửa hồ sơ cũ, không nhập lại dump SQL. Local đã chạy ngày 20/10/2026.
3. Sao chép `config/mail.example.php` thành `config/mail.local.php` và điền thông tin SMTP của trường. File local đã có trong `.gitignore` và `.dockerignore`.

Các thông tin cần xin quản trị email của trường:

| Khóa | Nội dung |
| --- | --- |
| `host` | Máy chủ SMTP, ví dụ minh họa `smtp.example.edu.vn` |
| `port` | Cổng được trường cung cấp, thường 587 hoặc 465 |
| `encryption` | `tls` cho STARTTLS, `ssl` cho TLS ngay khi kết nối |
| `username` | Tên đăng nhập SMTP |
| `password` | Mật khẩu SMTP hoặc app password theo chính sách trường |
| `from_address` | Địa chỉ gửi được máy chủ cho phép |
| `from_name` | Tên hiển thị của KTX |

Nếu trường chỉ cho phép OAuth2 thay vì SMTP username/password, cần tích hợp phương thức xác thực đó sau khi nhận đặc tả; adapter hiện tại dùng SMTP AUTH username/password. Không tắt xác minh chứng chỉ TLS để vượt lỗi cấu hình.

Khi chưa có cấu hình SMTP, có thể kiểm tra toàn bộ luồng nghiệp vụ và trạng thái lỗi/gửi lại, nhưng chưa thể gửi thư thật. Không điền mật khẩu vào tài liệu, chat hoặc Git.

## Triển khai và gửi lại

Máy chủ triển khai có thể dùng `MAIL_HOST`, `MAIL_PORT`, `MAIL_ENCRYPTION`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`; biến môi trường ưu tiên hơn file local. Ứng dụng không tự nạp `.env`.

Dockerfile cài dependency từ `composer.lock`, không chép cấu hình SMTP local vào image. Khi container khởi động, migration email chạy sau migration QR và trước Apache; nếu CSDL chưa có cột `Email` ở `dondangky` hoặc `sinhvien`, container sẽ báo lỗi để kiểm tra schema. Chưa kiểm tra build/deploy Docker trong lần thay đổi này.

`php -d xdebug.mode=off scripts/retry-approval-email.php` thử tối đa 50 thông báo Chờ gửi/Lỗi theo thứ tự cũ nhất. Có thể gọi định kỳ bằng Task Scheduler/cron; chưa cài lịch tự động. Script chỉ chạy CLI và dùng chung cơ chế khóa/trạng thái với nút gửi lại.

## Thời gian

`config/timezone.php` đặt PHP `Asia/Ho_Chi_Minh`; mọi kết nối từ `createDatabaseConnection()` đặt MySQL session `+07:00`. `dondangky.ThoiGianGui` vẫn dùng `CURRENT_TIMESTAMP`, được lưu/hiển thị theo giờ Việt Nam. Không đổi global timezone của MySQL.

Kiểm tra trước sửa trên máy local: PHP UTC, MySQL SYSTEM đang tương ứng UTC+7. Dữ liệu DATETIME cũ không mang thông tin múi giờ, nên không cộng đồng loạt 7 giờ; cần xác định bản ghi/môi trường gốc nếu muốn sửa dữ liệu lịch sử. Đơn mới đã được kiểm thử lưu giờ trùng thời điểm hiện tại Việt Nam.

## Kiểm thử

`tests/registration.php`, `tests/approval.php`, `tests/run.php`, `tests/payment.php`, `tests/rooms.php`, `tests/accommodation.php`. Các ca ghi dùng bảng TEMPORARY. Kiểm thử gửi thành công/thất bại dùng transport giả và email miền `.test`, không gửi thư đến người thật. Kiểm thử adapter SMTP riêng nằm ở `tests/smtp-mailer.php`.
