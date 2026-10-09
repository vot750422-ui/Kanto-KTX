# Trạng thái dự án Kanto-KTX

## Đã triển khai

- UC01: đăng nhập bằng mật khẩu băm, Session và phân quyền theo bốn vai trò; đăng xuất POST có CSRF.
- Trang chủ, dashboard theo vai trò; dashboard sinh viên và NVQL có model đọc dữ liệu.
- UC02: `index.php?action=register`, gồm nhập thông tin, chọn phòng, xác nhận và gửi đơn `Chờ duyệt`.
- UC02 không tạo tài khoản/sinh viên/hợp đồng; các nghiệp vụ đó thuộc bước xét duyệt tiếp theo.

## UC02 - Chi tiết bàn giao

- Model: `models/Registration.php`; controller: `controllers/RegistrationController.php`; view: `views/registration/form.php`.
- CSS/JS riêng: `assets/css/registration.css`, `assets/js/registration.js`.
- Diện ưu tiên lấy từ `dienuutien`; phòng lấy từ `phong`/`toanha`, lọc theo giới tính và chỗ khả dụng. Dãy/tầng được lấy từ dữ liệu thật.
- Danh mục diện ưu tiên đã chốt: UT00 Không có (0), UT01 Hộ nghèo / Cận nghèo (1), UT02 Con thương binh / Liệt sĩ (2), UT03 Vùng sâu vùng xa / Hải đảo (3). Dropdown mặc định Không có; khi xét duyệt ưu tiên 1 rồi 2 rồi 3, không ưu tiên (0) cuối cùng. Script cập nhật dữ liệu: `sql/update-priorities.sql`; SQL dump đã đồng bộ danh mục.
- Chỗ khả dụng = sức chứa - số hợp đồng có trạng thái `Còn hạn` - số đơn `Chờ duyệt`. Không sửa sức chứa, không lưu thêm trạng thái phòng.
- Khi gửi: khóa MySQL GET_LOCK chung cho UC02 trong cùng CSDL, transaction READ COMMITTED và khóa dòng phòng FOR UPDATE; kiểm tra lại MSSV, diện ưu tiên, giới tính và chỗ còn lại trước INSERT. Module xét duyệt/hợp đồng tương lai cần phối hợp khóa phòng khi thay đổi sức chứa sử dụng.
- Hồ sơ nháp nằm trong Session; POST có CSRF; gửi thành công chuyển hướng 303, đổi token và xóa nháp.
- Form không có Email theo UC02; cột Email hiện có trong CSDL giữ nguyên, giá trị mặc định NULL.
- Ảnh minh chứng tùy chọn theo wireframe, chỉ JPG/PNG thực tế dưới 5 MB, tên ngẫu nhiên; lưu trong `storage/registration/`, bị chặn HTTP bằng `storage/.htaccess`, được gitignore. FileMinhChung lưu đường dẫn tương đối.
- Chưa có màn hình đọc ảnh minh chứng cho NVQL (thuộc UC03). Chưa có tác vụ dọn ảnh của hồ sơ nháp bị bỏ dở; cần xử lý vòng đời tệp khi triển khai UC03/vận hành.
- Validation: trường bắt buộc/độ dài theo schema, ngày sinh hợp lệ trước hôm nay, CCCD 12 số, SDT 10 số bắt đầu bằng 0, MSSV chữ/số/gạch ngang/gạch dưới, niên khóa dạng YYYY-YYYY.
- Không thay đổi schema. Đã cập nhật danh mục diện ưu tiên và seed tòa/phòng theo yêu cầu.

## Dữ liệu tòa nhà và phòng (08/10/2026)

- Đã chạy `sql/seed-buildings-rooms.sql` trên CSDL WAMP: T01 Tòa A, T02 Tòa B (Nam); T03 Tòa C, T04 Tòa D (Nữ).
- Mỗi tòa 50 phòng, 4 tầng: 13/13/12/12 phòng. Tổng 200 phòng, sức chứa thiết kế 1600 sinh viên; mỗi phòng 8 chỗ.
- Mã kỹ thuật P0001–P0200; số phòng hiển thị A101–A113, A201–A213, A301–A312, A401–A412 và tương tự B/C/D.
- Script SQL dùng CTE tự sinh phòng, không sửa schema/không tạo bảng day. Chạy lại giữ nguyên dữ liệu trùng khóa; nếu có mã/tên xung đột thì không tự ghi đè (xem DATABASE_DESIGN.md).
- Khi tạo CSDL mới: import dump rồi chạy riêng script seed; không import lại dump vào CSDL đang dùng chỉ để bổ sung phòng.
- View đã hiển thị rõ sức chứa; dữ liệu lọc dãy/tầng vẫn lấy từ CSDL. Không cần sửa Model/Controller vì logic UC02 hiện tại đáp ứng yêu cầu.
- Wireframe minh họa có tầng 5/phòng đầy; áp dụng yêu cầu mới và UC02: 4 tầng, chỉ hiển thị phòng còn chỗ.

## Kiểm thử

- `php -d xdebug.mode=off tests/run.php`: bộ kiểm thử đăng nhập/phân quyền.
- `php -d xdebug.mode=off tests/registration.php`: UC02 trên bảng TEMPORARY theo schema thực tế, không sửa dữ liệu thật; xem `tests/README.md`.
- `php -d xdebug.mode=off tests/rooms.php`: seed và UC02 trên bộ 200 phòng, dùng bảng TEMPORARY, kiểm tra chạy lại không trùng/không ghi đè, lưu MaPhong, giữ chỗ và phòng đầy.
- Đã kiểm tra qua HTTP WAMP bước chọn phòng: Nam 100 phòng Tòa A/B, Nữ 100 phòng Tòa C/D. Chỉ tạo nháp Session để kiểm tra HTTP, không lưu đơn demo vào dữ liệu thật.
- PHP WAMP: `C:\wamp64\bin\php\php8.3.28\php.exe` (PHP chưa có trong PATH).
- Đã kiểm tra syntax các file PHP thay đổi và route đăng ký qua HTTP localhost.
- Chưa kiểm thử hình ảnh giao diện trong trình duyệt, upload ảnh hợp lệ qua HTTP hoặc hai tiến trình gửi đồng thời.

## Tài liệu và công việc tiếp theo

- 09/10/2026: đã chuẩn bị Docker/Render, chưa deploy. Dockerfile PHP 8.3 Apache có pdo_mysql/mbstring; cấu hình Apache chặn HTTP vào thư mục nội bộ/storage, cấu hình PHP upload 5M/post 8M. .dockerignore loại file môi trường, SQL, docs/tests và upload local.
- config/db.php giữ $pdo, đọc DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASSWORD, mặc định tương thích WAMP; lỗi kết nối trả HTTP 500 chung. Không thêm credential production.
- config/app.php cung cấp appUrl(): WAMP mặc định /Kanto-KTX; Docker đặt APP_BASE_PATH=/. Các link/redirect hiện tại đã dùng helper. Auth giữ session security, cookie Secure bật khi HTTPS trực tiếp hoặc RENDER=true.
- Kiểm tra: syntax PHP đạt; local login/role 17/17, UC02 20/20, rooms 14/14; APP_BASE_PATH=/ cũng đạt login/role 17/17 và UC02 20/20. Chưa build/chạy Docker vì máy chưa có Docker CLI.
- Hướng dẫn: docs/DEPLOY_RENDER.md. Cần MySQL online trước deploy; xác nhận yêu cầu TLS của nhà cung cấp. Render filesystem không bền vững, ảnh UC02 và session/nháp có thể mất khi restart/redeploy; chưa thêm cloud storage.

- `docs/02-use-cases/UC02-registration.md` và ba wireframe UC02 là tài liệu triển khai.
- `DATABASE_DESIGN.md` đã mô tả phần tòa/phòng và cách seed. `BUSINESS_RULES.md` còn trống; Use Case overview có mã UC06–UC11 không khớp đặc tả chi tiết.
- CSDL thực tế có CHECK của phòng và hợp đồng chưa thể hiện trong SQL dump; luôn đối chiếu schema thực tế trước khi sửa.
- Tiếp theo: kiểm tra giao diện/upload bằng trình duyệt để demo UC02; sau đó triển khai UC03 khi được yêu cầu.
