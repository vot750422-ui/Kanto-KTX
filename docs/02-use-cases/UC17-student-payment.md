# UC17 - Thanh toán hóa đơn của sinh viên

## Nguồn và phạm vi

Triển khai theo mục 2.3.22 của `docs/reports/chapter2/Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx`, cập nhật theo yêu cầu ngày 25/10/2026: điện thoại quét QR mở trang xác nhận riêng, không cần đăng nhập sinh viên. Quy định mới này thay thế yêu cầu đăng nhập trên thiết bị quét trong bản triển khai 24/10.
Tác nhân: **Sinh viên**. QR mô phỏng phục vụ đồ án; không tích hợp ngân hàng, không chuyển tiền thật, không có webhook hoặc đối soát.

Tài liệu chi tiết cũ gọi quản lý hóa đơn là UC07, xem lưu trú là UC11; bảng tổng quan gọi hai chức năng tương ứng là UC09 và UC06. UC17 giữ nguyên mã ở cả hai nguồn. Không đổi mã các UC khác trong lần triển khai này.

## Tiền điều kiện và hậu điều kiện

- Sinh viên đã đăng nhập bằng tài khoản đang hoạt động để xem danh sách và tạo QR; có hồ sơ liên kết qua `sinhvien.MaTK`.
- Thiết bị quét chỉ cần token QR hợp lệ; không cần tài khoản hoặc phiên đăng nhập sinh viên. Người có liên kết có quyền xác nhận đúng hóa đơn được gắn trong token.
- Hóa đơn thuộc sinh viên và đang `Chưa thu`.
- Thành công: chuyển hóa đơn sang `Đã thu`, ghi `ThoiGianThanhToan` theo giờ Việt Nam.
- Nếu hóa đơn tiền phòng đầu tiên gắn với đơn `Đã duyệt` chưa có hợp đồng: tạo hợp đồng theo học kỳ trên hóa đơn, gắn `dondangky.MaHopDong`, chuyển sinh viên sang `Đang ở` trong cùng transaction. Giữ tài khoản đã cấp tại UC03.
- Hóa đơn định kỳ/điện nước không tự tạo hoặc gia hạn hợp đồng.
- Thất bại: rollback toàn bộ; không tạo hợp đồng hoặc thay đổi trạng thái dở dang.

## Luồng chính

1. Sinh viên mở **Thanh toán** trên menu, xem hóa đơn của mình; lọc Chưa thu/Đã thu/Tất cả và phân trang.
2. Chọn **Xem hóa đơn** để xem mã, loại, phòng, kỳ, số tiền, trạng thái và thời gian. Chỉ mở hoặc tải lại chi tiết không tạo QR.
3. Sinh viên bấm **Tạo mã QR**. POST có CSRF tạo token ngẫu nhiên 256 bit, lưu SHA-256 của token cùng hóa đơn, tài khoản, số tiền và hạn 5 phút trong `phienthanhtoan`. Chi tiết mới hiển thị QR và liên kết mở cùng thiết bị. Mỗi hóa đơn chỉ có một QR còn hiệu lực tại một thời điểm; tải lại trang giữ nguyên QR trong phiên trình duyệt đã tạo.
4. Quét QR bằng camera. Trang riêng mở được khi chưa đăng nhập. JavaScript chuyển token từ fragment `#...` sang POST để lấy hóa đơn, xóa fragment khỏi lịch sử hiện tại. Bước này không ghi nhận thanh toán.
5. Trang mô phỏng hiển thị mã hóa đơn, số tiền, nội dung và nút **Xác nhận thanh toán mô phỏng**.
6. Sinh viên xác nhận bằng POST. Máy chủ kiểm tra CSRF, phiên giao dịch, quyền sở hữu, trạng thái và số tiền hiện tại.
7. Hệ thống thực hiện transaction và đánh dấu token đã dùng cùng lúc cập nhật hóa đơn/hợp đồng; sau đó chuyển tới kết quả thanh toán trên thiết bị quét. Máy tính tải lại danh sách để thấy trạng thái mới.

## Luồng khác

- Hủy/quay lại hoặc chỉ quét/mở QR: không thay đổi dữ liệu nghiệp vụ.
- Không tồn tại/không thuộc sinh viên tại trang quản lý cá nhân: không hiển thị nội dung; HTTP 404.
- Sai vai trò tại trang cá nhân: HTTP 403; chưa đăng nhập: chuyển tới đăng nhập. Trang QR công khai không kiểm tra đăng nhập.
- CSRF không hợp lệ: HTTP 403. Mã hóa đơn hoặc bộ lọc sai định dạng: HTTP 400. Sai phương thức: HTTP 405.
- Token sai, hết 5 phút, đã dùng, số tiền đã thay đổi, tài khoản gốc bị khóa hoặc hóa đơn đã thu: HTTP 409 tại trang QR, không lộ chi tiết hóa đơn. Sau khi mã cũ hết hiệu lực, chủ tài khoản bấm **Tạo mã QR** để tạo mã mới. Trình duyệt khác không xem lại được token gốc và không tạo mã thứ hai khi mã cũ còn hiệu lực.
- Lỗi lưu: rollback và hiển thị thông báo chung, không công khai lỗi CSDL.

## Quy tắc dữ liệu và bảo vệ giao dịch

- Không nhận MSSV/MaTK hay số tiền từ trình duyệt để quyết định quyền/số tiền thu.
- Tiền phòng thuộc sinh viên qua `hoadontienphong.MSSV`; điện nước thuộc các sinh viên đã ghi trong `hoadondiennuocsinhvien`, không suy từ phòng đang ở hiện tại.
- Schema hiện có một trạng thái và tổng tiền cho cả hóa đơn điện nước của phòng. Một sinh viên có tên trong danh sách liên quan xác nhận **toàn bộ hóa đơn phòng**; các sinh viên còn lại thấy Đã thu. Không tự chia tiền hoặc bổ sung trạng thái thu theo từng sinh viên.
- QR chứa URL nội bộ và token cấp quyền cho một hóa đơn trong fragment; không chứa tên, MSSV, mật khẩu hoặc số tiền. Token không gửi trong URL HTTP/Referer; trang đặt `Cache-Control: no-store`, `Referrer-Policy: no-referrer`, `X-Frame-Options: DENY`. Không ghi token hoặc nội dung hóa đơn vào log ứng dụng.
- Token gốc của mã đang hiệu lực chỉ giữ trong session trình duyệt đã tạo; CSDL chỉ lưu hash. POST tạo QR dùng named lock và transaction để tránh phát hành mã thứ hai cho cùng hóa đơn. Nếu dữ liệu cũ có nhiều mã chưa hết hạn, chỉ mã mới nhất được phép mở/xác nhận cho đến khi POST tạo QR xử lý mã trùng. Các token cũ có thời hạn lưu 15 phút cũng bị giới hạn hiệu lực thực tế ở 5 phút kể từ `TaoLuc`.
- Trang QR chỉ hiện mã hóa đơn, loại, tổng tiền và nội dung thanh toán. Không hiện hồ sơ, thông tin phòng hoặc menu cá nhân. Session ẩn danh chỉ bảo vệ CSRF và giữ kết quả; không đăng nhập hoặc cấp quyền sinh viên.
- Token được kiểm tra và đánh dấu đã dùng bên trong transaction của `Payment::settle`. Khi hóa đơn đã thu bởi kế toán hoặc QR khác, mọi QR còn lại của hóa đơn cũng không thể thu lại. Lỗi rollback khôi phục cả token để có thể thử lại nếu còn hạn.
- Route xác nhận trực tiếp `student-payment`/`student-payment-confirm` vẫn giữ kiểm tra đăng nhập và nonce session cho trường hợp mở trên tài khoản hiện tại; QR mới sử dụng route công khai riêng.
- UC17 và kế toán dùng chung `Payment::settle`, named lock với UC02/UC03, transaction và khóa dòng. Kiểm tra lại trạng thái trước cập nhật; không thu lặp hoặc tạo trùng hợp đồng.
- Phạm vi kế toán hiện có vẫn chỉ xác nhận tiền phòng đầu tiên. UC17 hỗ trợ thêm hóa đơn định kỳ/điện nước đã tồn tại; không triển khai tiến trình tự lập hóa đơn.

## Route và file chính

| Phương thức | Action | Chức năng |
|---|---|---|
| GET | `student-payments` | Danh sách của sinh viên |
| GET | `student-invoice&invoice=...` | Chi tiết; chỉ hiển thị QR đã tạo còn hiệu lực của phiên này |
| POST | `student-qr-create` | Tạo QR theo yêu cầu, kiểm tra CSRF và tránh tạo mã trùng |
| GET | `student-payment&invoice=...` | Trang mô phỏng, tạo phiên xác nhận |
| POST | `student-payment-confirm` | Xác nhận bằng CSRF và phiên giao dịch |
| GET | `payment-checkout#token` | Trang mở liên kết QR, không yêu cầu đăng nhập |
| POST | `payment-checkout` | Trao đổi token qua body, hiển thị số tiền; chưa thu |
| POST | `payment-checkout-confirm` | Thu theo token và CSRF, không yêu cầu đăng nhập |
| GET | `payment-checkout&receipt=...` | Kết quả thuộc session của thiết bị xác nhận |

Năm route `student-*` yêu cầu `requireRole('Sinh viên')`. Controller cá nhân: `controllers/StudentPaymentController.php`; controller trang QR: `controllers/PaymentCheckoutController.php`; model dùng chung: `models/Payment.php`; token liên thiết bị: `services/PaymentLink.php`; view công khai: `views/payments/checkout.php`.

QR được tạo trong trình duyệt bằng bản thư viện qrcode-generator lưu tại `assets/js/vendor/`, không gọi dịch vụ tạo QR bên ngoài. Khi chạy WAMP qua localhost, điện thoại không truy cập localhost của máy tính. Để quét và xác nhận trên điện thoại cùng Wi-Fi:

1. Xem IPv4 của bộ điều hợp Wi-Fi trên máy chạy WAMP (ví dụ `192.168.1.10`), sao chép `config/payment.example.php` thành `config/payment.local.php`, rồi thay địa chỉ mẫu bằng `http://<IPv4-Wi-Fi>`. Tệp `.local.php` bị Git bỏ qua. Môi trường khác có thể đặt `PAYMENT_PUBLIC_ORIGIN` bằng origin HTTPS công khai. Không thêm `/Kanto-KTX` vào giá trị origin.
2. Cho phép điện thoại truy cập riêng dự án trong VirtualHost Apache theo `config/wamp-phone.example.conf` và mở cổng 80 cho mạng Wi-Fi nội bộ trong Windows Firewall. Giữ chặn truy cập trực tiếp `config`, `sql`, `tests` và các file nội bộ. Khởi động lại Apache sau khi sửa cấu hình.
3. Trên điện thoại, mở `http://<IPv4-Wi-Fi>/Kanto-KTX/index.php?action=payment-checkout` để kiểm tra trang công khai tải được, rồi quét QR mới được tạo từ trang sinh viên. Điện thoại không cần đăng nhập. Quét QR chỉ mở trang; bấm **Xác nhận thanh toán mô phỏng** mới ghi nhận hóa đơn. Nếu IPv4 Wi-Fi thay đổi, cập nhật `payment.local.php` và tạo QR mới.

## Kiểm thử

Docker container chạy `php scripts/migrate-payment-links.php` trước khi Apache nhận request, để thêm bảng `phienthanhtoan` và các khóa ngoại trên CSDL đã cấu hình. Nếu chạy ứng dụng ngoài Docker, chạy lệnh đó trước khi dùng UC17. Migration không sửa dữ liệu hóa đơn/hợp đồng; `CREATE TABLE IF NOT EXISTS` có thể chạy lặp. Không import lại SQL dump. Khi khởi động container thất bại, kiểm tra log và quyền `CREATE TABLE` của tài khoản CSDL.

```powershell
php -d xdebug.mode=off tests/student-payments.php
php -d xdebug.mode=off tests/payment.php
php -d xdebug.mode=off tests/run.php
```

Kiểm thử nghiệp vụ dùng bảng TEMPORARY, không ghi vào hóa đơn/tài khoản thật. Bao phủ quyền sở hữu, CSRF, nonce, hết hạn, số tiền thay đổi, thu đầu tiên/định kỳ/điện nước, thu lặp giữa hai luồng, rollback cả token và form. Kiểm tra trang QR với session hoàn toàn mới không có `user`, mã hóa đơn/tài khoản/số tiền giả, không tiết lộ hồ sơ. Các ca route gọi `index.php` trong tiến trình CLI riêng.
