# Kiểm thử đăng nhập và phân quyền

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
CSRF/phương thức/input/XSS và endpoint ảnh. tests/run.php thêm kiểm tra quyền
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

Test UC02 tạo bảng TEMPORARY theo schema thực tế trong kết nối riêng, không sửa dữ liệu nghiệp vụ. Kiểm tra validation, lọc giới tính, tính chỗ còn lại, giữ chỗ, trùng MSSV, rollback, CSRF, tệp giả, ba bước, quay lại và gửi lặp. Mô phỏng phòng bị đơn khác giữ trước khi gửi; chưa chạy hai tiến trình đồng thời hoặc tự động kiểm tra giao diện trình duyệt và upload ảnh hợp lệ qua HTTP.

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
# Kiểm tra sau tối ưu

- `php -d xdebug.mode=off tests/run.php`: 30 ca, có kết nối dùng lại và quyền/trạng thái tài khoản vẫn được đọc mới.
- `php -d xdebug.mode=off tests/registration.php`: 23 ca, gồm UTF-8/NUL và phương thức không hỗ trợ.
- `php -d xdebug.mode=off tests/rooms.php`: 16 ca, gồm tra cứu trực tiếp một phòng và giới tính sai.
- `node tests/frontend.js`: 8 ca dialog/lọc phòng, không cần cài npm package.
