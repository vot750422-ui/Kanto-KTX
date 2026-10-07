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
- Chỗ khả dụng = sức chứa - số hợp đồng có trạng thái `Còn hạn` - số đơn `Chờ duyệt`. Không sửa sức chứa, không lưu thêm trạng thái phòng.
- Khi gửi: khóa MySQL GET_LOCK chung cho UC02 trong cùng CSDL, transaction READ COMMITTED và khóa dòng phòng FOR UPDATE; kiểm tra lại MSSV, diện ưu tiên, giới tính và chỗ còn lại trước INSERT. Module xét duyệt/hợp đồng tương lai cần phối hợp khóa phòng khi thay đổi sức chứa sử dụng.
- Hồ sơ nháp nằm trong Session; POST có CSRF; gửi thành công chuyển hướng 303, đổi token và xóa nháp.
- Form không có Email theo UC02; cột Email hiện có trong CSDL giữ nguyên, giá trị mặc định NULL.
- Ảnh minh chứng tùy chọn theo wireframe, chỉ JPG/PNG thực tế dưới 5 MB, tên ngẫu nhiên; lưu trong `storage/registration/`, bị chặn HTTP bằng `storage/.htaccess`, được gitignore. FileMinhChung lưu đường dẫn tương đối.
- Chưa có màn hình đọc ảnh minh chứng cho NVQL (thuộc UC03). Chưa có tác vụ dọn ảnh của hồ sơ nháp bị bỏ dở; cần xử lý vòng đời tệp khi triển khai UC03/vận hành.
- Validation: trường bắt buộc/độ dài theo schema, ngày sinh hợp lệ trước hôm nay, CCCD 12 số, SDT 10 số bắt đầu bằng 0, MSSV chữ/số/gạch ngang/gạch dưới, niên khóa dạng YYYY-YYYY.
- Không thay đổi schema hay thêm dữ liệu mẫu. Khi kiểm tra, CSDL WAMP hiện có **0 phòng**; cần dữ liệu tòa nhà/phòng để đăng ký thực tế. Trường hợp không có phòng hiển thị trạng thái rỗng.

## Kiểm thử

- `php -d xdebug.mode=off tests/run.php`: bộ kiểm thử đăng nhập/phân quyền.
- `php -d xdebug.mode=off tests/registration.php`: UC02 trên bảng TEMPORARY theo schema thực tế, không sửa dữ liệu thật; xem `tests/README.md`.
- PHP WAMP: `C:\wamp64\bin\php\php8.3.28\php.exe` (PHP chưa có trong PATH).
- Đã kiểm tra syntax các file PHP thay đổi và route đăng ký qua HTTP localhost.
- Chưa kiểm thử hình ảnh giao diện trong trình duyệt, upload ảnh hợp lệ qua HTTP hoặc hai tiến trình gửi đồng thời.

## Tài liệu và công việc tiếp theo

- `docs/02-use-cases/UC02-registration.md` và ba wireframe UC02 là tài liệu triển khai.
- `BUSINESS_RULES.md` và `DATABASE_DESIGN.md` còn trống; Use Case overview có mã UC06–UC11 không khớp đặc tả chi tiết.
- CSDL thực tế có CHECK của phòng và hợp đồng chưa thể hiện trong SQL dump; luôn đối chiếu schema thực tế trước khi sửa.
- Tiếp theo: dữ liệu tòa/phòng để demo UC02, kiểm tra giao diện/upload bằng trình duyệt; sau đó triển khai UC03 khi được yêu cầu.
