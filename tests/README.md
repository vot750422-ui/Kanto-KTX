# Kiểm thử đăng nhập và phân quyền

## UC05.1 - Quản lý hợp đồng

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/contracts.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/run.php
```

`tests/contracts.php` dùng bảng TEMPORARY để kiểm tra tra cứu toàn bộ, kết hợp bộ lọc,
trạng thái lưu trong CSDL, chi tiết hợp đồng, prepared statement và tính chỉ đọc.
`tests/run.php` kiểm tra route danh sách/chi tiết, quyền NVQL và phương thức GET.

## UC16 - Xem hướng dẫn

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/guide.php
```

Kiểm tra route thực tế cho khách, người có phiên đăng nhập và POST (405).
Không kết nối CSDL; xác nhận trang không thay đổi phiên đăng ký/đăng nhập,
không tải config DB, không có form ghi dữ liệu và có link tới UC02/UC15.

## UC15 - Xem thông tin phòng

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/room-information.php
```

Test dùng bảng TEMPORARY theo schema local đã áp dụng migration UC03; không sửa
CSDL thật. Kiểm tra tòa/tầng theo dữ liệu, chỗ đang ở và đang giữ, phòng đầy,
đơn Đã duyệt chưa có hợp đồng/đã liên kết, đồng bộ UC02, GET chỉ đọc, bộ lọc sai,
SQL injection, XSS, danh sách rỗng và lỗi CSDL. `tests/run.php` kiểm tra thêm khách
mở UC15 qua route `index.php?action=rooms` thực tế, không cần đăng nhập.

Giới hạn: test bảng tạm không chứng minh FK hoặc hành vi hai tiến trình đồng thời;
chưa tự động kiểm tra bố cục trong trình duyệt.

## UC03 - Xét duyệt

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/approval.php
```

Cần schema UC03 (`sql/uc03-approval.sql`). Test sao chép schema thật vào TEMPORARY,
gỡ FK để tạo fixture riêng; giữ CHECK/UNIQUE. Không sửa dữ liệu nghiệp vụ thật.
Kiểm tra ưu tiên/phân trang, từ chối/giải phóng chỗ, duyệt phòng đã được giữ đầy,
tài khoản băm, kỳ kế tiếp sang năm mới, giá DECIMAL, hồ sơ Chờ thanh toán,
không tạo hợp đồng, trùng hồ sơ/tài khoản, rollback lỗi hóa đơn con, giải phóng khóa,
phương thức/input/XSS và endpoint ảnh. tests/run.php thêm kiểm tra quyền
toàn bộ route UC03 cho khách/vai trò khác và NVQL mở danh sách.

Giới hạn: TEMPORARY không chia sẻ giữa tiến trình; chưa kiểm thử hai NVQL thao tác
đồng thời trên cùng dữ liệu vật lý hoặc giao diện/hộp thoại trong trình duyệt.
Test không chứng minh FK bằng fixture; migration đã được kiểm tra trên schema local.

## UC02 - Đăng ký lưu trú

Kiểm tra seed tòa/phòng và UC02 trên bộ dữ liệu 200 phòng:

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/rooms.php
```

Bộ kiểm thử dùng bảng TEMPORARY theo schema thật, không sửa dữ liệu thật. Kiểm tra số lượng/tầng/mã phòng/giới tính/sức chứa, seed chạy lại giữ dữ liệu, UC02 lọc phòng và giữ chỗ khi gửi.

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/registration.php
```

Test UC02 tạo bảng TEMPORARY theo schema thực tế trong kết nối riêng, không sửa dữ liệu nghiệp vụ. Kiểm tra validation, lọc giới tính, tính chỗ còn lại, giữ chỗ, trùng MSSV, rollback, tệp giả, ba bước, quay lại và gửi lặp. Mô phỏng phòng bị đơn khác giữ trước khi gửi; chưa chạy hai tiến trình đồng thời hoặc tự động kiểm tra giao diện trình duyệt và upload ảnh hợp lệ qua HTTP.

## Đăng nhập và phân quyền

Chạy từ thư mục dự án bằng PowerShell, sau khi bật MySQL trong WAMP:

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/run.php
```

Nếu PHP đã có trong PATH: `php tests/run.php`.

Bộ kiểm thử dùng CSDL trong `config/db.php`, cần ít nhất một tài khoản hoạt động cho mỗi vai trò. Các dòng PASS/FAIL giải thích từng tình huống; mã thoát là 0 khi tất cả đạt, 1 khi có lỗi.

Không thêm, sửa hoặc xóa dữ liệu. Session kiểm thử nằm trong RAM và tách khỏi session trình duyệt. Các file kiểm thử chỉ chạy qua CLI.

Khi demo, chạy bộ kiểm thử rồi đăng nhập trên trình duyệt lần lượt với bốn tài khoản mẫu. Thử mở dashboard của vai trò khác để thấy HTTP 403, và mở dashboard trong cửa sổ riêng tư để thấy chuyển về trang đăng nhập.

Giới hạn: chưa tự động kiểm thử đăng nhập thành công bằng mật khẩu thật, tài khoản bị khóa, cookie hoặc giao diện trình duyệt. Chuỗi mật khẩu băm được nhận diện không chứng minh mật khẩu demo nhập vào là đúng.
# UC11 - Xem thông tin lưu trú

- `php -d xdebug.mode=off tests/accommodation.php`: kiểm thử model bằng bảng TEMPORARY, không sửa dữ liệu demo.
- `php -d xdebug.mode=off tests/run.php`: gồm các ca UC11 khách/sai vai trò/POST, tài khoản hiện tại thay vì MSSV trong URL, và view có/chưa có hợp đồng/thiếu hồ sơ/escape dữ liệu.
- Cần MySQL local và tài khoản hoạt động cho các vai trò của bộ kiểm thử chung.
# Xác nhận thanh toán tiền phòng đầu tiên

- `php -d xdebug.mode=off tests/payment.php`: 20 ca bằng bảng TEMPORARY, từ duyệt UC03 đến hợp đồng UC11, kiểm tra rollback/gửi lặp/giữ tài khoản/form.
- `php -d xdebug.mode=off tests/run.php`: có 9 ca route/phân quyền cho thanh toán.
- Phạm vi demo và quy tắc học kỳ: `docs/UC07_PAYMENT_IMPLEMENTATION_NOTES.md`.

# UC17 - Sinh viên thanh toán QR mô phỏng

- `php -d xdebug.mode=off tests/student-payments.php`: kiểm thử bằng bảng TEMPORARY, gồm nút tạo QR theo POST, không tạo trên GET/tải lại, chỉ một mã hiệu lực trong 5 phút, giới hạn cả token cũ 15 phút, tạo lại sau khi hết hạn, token QR liên thiết bị với session trống, thu lặp giữa QR/kế toán và rollback cả token. Giữ các ca quyền sở hữu, thanh toán đầu tiên/định kỳ/điện nước.
- `php -d xdebug.mode=off tests/run.php`: kiểm tra khách và ba vai trò khác tại cả năm route UC17, kể cả `student-qr-create`.
- Có thêm ba ca route công khai: khách mở trang QR, token sai, GET không được thu.
- `node tests/frontend.js`: có kiểm tra ảnh QR từ thư viện local, chặn URL ngoài origin, chống gửi lặp, bộ đếm 5 phút không tự tạo QR và tải lại khi quay về bằng cache trình duyệt.
- Để render HTML bằng dữ liệu giả phục vụ kiểm tra giao diện: `php tests/student-payments.php --preview-dir <thu-muc-tam-da-ton-tai>`. Xuất list/detail/qr/confirm/paid; không dùng các file này làm trang chạy thật.
- Đặc tả: `docs/02-use-cases/UC17-student-payment.md`. Test không sửa dữ liệu nghiệp vụ thật. Ứng dụng cần migration `php scripts/migrate-payment-links.php` để thêm bảng token liên thiết bị; bộ test UC17 tự tạo bảng TEMPORARY tương ứng.
# Chatbot đọc số liệu phòng công khai

- `php -d xdebug.mode=off tests/chatbot.php`: câu ngắn/lỗi gõ, phạm vi do AI đánh giá, bảo vệ đầu vào, snapshot trong prompt, nguồn phòng lỗi và JSON/giới hạn độ dài câu hỏi.
- `php -d xdebug.mode=off tests/chatbot-rooms.php`: bảng TEMPORARY, số chỗ đúng UC15, tổng tòa, loại bỏ dữ liệu cá nhân, cập nhật theo dữ liệu mới. Không gọi Gemini.
# Kiểm tra sau tối ưu ứng dụng

- `tests/run.php` có ca kết nối dùng lại nhưng thay đổi quyền/khóa tài khoản vẫn được xác thực lại, bằng bảng tài khoản TEMPORARY.
- `tests/registration.php` có đầu vào NUL/UTF-8 sai và phương thức ngoài GET/POST.
- `tests/rooms.php` đối chiếu truy vấn phòng đơn với danh sách và chặn sai giới tính.
- `node tests/frontend.js` chạy 8 ca dialog/lọc phòng bằng Node VM và DOM mô phỏng; không cần npm package. Đây không thay cho kiểm tra bố cục trong trình duyệt.

## Email UC03 và múi giờ (20/10/2026)

`php -d xdebug.mode=off tests/brevo-mailer.php` kiểm tra payload gửi qua Brevo HTTPS API bằng HTTP giả trong tiến trình; không gửi email thật. `tests/smtp-mailer.php` tiếp tục kiểm tra adapter SMTP cho WAMP.

Chạy migration scripts/migrate-approval-email.php trước để trang xét duyệt có bảng emailthongbao; các test approval/payment che bảng này bằng TEMPORARY. tests/registration.php kiểm tra email bắt buộc/hợp lệ, lưu email và giờ Việt Nam. tests/approval.php kiểm tra email sau commit, nội dung, SMTP lỗi/gửi lại, chống lặp, hồ sơ cũ thiếu email và rollback khi hàng đợi lỗi. tests/run.php kiểm tra thêm phân quyền route approval-email-retry. tests/smtp-mailer.php kiểm tra adapter thư bằng SMTP giả trong tiến trình, không kết nối người nhận thật.

## UC bổ sung ngày 27/10/2026

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/student-management.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/contracts.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/utility-meters.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/incidents.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/notifications.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/statistics.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/run.php
```

Các test model dùng bảng TEMPORARY và không sửa dữ liệu nghiệp vụ thật. `tests/run.php`
kiểm tra thêm khách/sai vai trò cho toàn bộ route mới.

Nếu tài khoản Nhân viên quản lý KTX đã tồn tại nhưng thiếu hồ sơ trong bảng `nhanvien`, chạy:

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off scripts/migrate-staff-profiles.php
```

Migration có thể chạy lại an toàn và không tự đặt họ tên cá nhân: hồ sơ thiếu dùng chính tên
đăng nhập làm giá trị tạm thời. Có thể cập nhật họ tên thật sau khi nhóm cung cấp dữ liệu.
