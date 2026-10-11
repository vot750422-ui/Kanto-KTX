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

- UC thanh toán (mô phỏng QR), chưa triển khai.

### UC03 hiện tại

- UC03 đã có danh sách/phân trang, chi tiết, ảnh bảo vệ, duyệt và từ chối;
  code đã merge vào main qua PR #2. Chưa xác minh deployment trong phiên này.
  Xem `docs/UC03_IMPLEMENTATION_NOTES.md`.

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

`SucChua - số HopDong Còn hạn - số DonDangKy Chờ duyệt - số DonDangKy Đã duyệt có MaHopDong NULL`

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

## 14. Bàn giao UC03 (10/10/2026)

- Người dùng đã cho phép sửa UC02/schema để duyệt tạo tài khoản, sinh viên và hóa đơn
  tiền phòng đầu tiên, giữ chỗ chờ thanh toán. Không tạo hợp đồng khi duyệt.
- Hóa đơn thu trọn học kỳ kế tiếp gần nhất sau kỳ hiện tại, kể cả sang năm mới.
  Giá tiền phòng mới nhất có hiệu lực tại ngày duyệt, đơn vị đồng/tháng/sinh viên
  (giá trị DonViTinh trong CSDL: `đồng/tháng`). Thu cả kỳ bằng tổng tiền từng tháng;
  tháng không đủ = đơn giá × số ngày tính tiền / số ngày của tháng, tính cả hai ngày biên.
  Chỉ làm tròn tổng hóa đơn đến đồng; UI hiển thị riêng đơn giá tháng và tổng kỳ.
  Chưa giới hạn thời gian mở UC02; không tính tiền theo phần kỳ cũ còn lại.
- Tên đăng nhập MSSV, mật khẩu được băm theo quy ước demo. Không Gmail.
- Trạng thái sinh viên mới Chờ thanh toán; thêm dondangky.MaHoaDon và MaHopDong
  nullable/UNIQUE/FK. Migration `sql/uc03-approval.sql` đã chạy trên local qlktx_v2;
  chưa áp dụng Aiven. Dump mới đồng bộ; không import dump vào CSDL hiện có.
- Ưu tiên UT00/UT01/UT02/UT03 = 0/2/4/3, xếp giảm dần rồi thời gian gửi/mã đơn.
- UC02 thêm kiểm tra MSSV/CCCD đã có sinh viên (cả nhập và submit), giữ chỗ Đã duyệt
  chưa có MaHopDong. UC03 cùng named lock UC02, READ COMMITTED, khóa phòng rồi đơn,
  commit tài khoản/sinh viên/hóa đơn/trạng thái đơn cùng nhau; lỗi rollback toàn bộ.
- Các route `index.php?action=approval`, approval-detail, approval-proof,
  approval-approve và approval-reject chỉ dành NVQL. POST ghi dữ liệu có CSRF.
- Ảnh tùy chọn; chỉ endpoint có quyền đọc, đường dẫn giới hạn storage/registration;
  UI phân biệt không nộp ảnh và tệp không còn. Không đổi storage/deployment.
- Dump `sql/qlktx_v2.sql` mới có sáu học kỳ của 2026-2027 và 2027-2028;
  bảng đơn giá trong dump còn rỗng. Cần đơn giá tiền phòng `đồng/tháng` có hiệu lực
  để thử duyệt qua UI. Không tự thêm seed nghiệp vụ; test dùng TEMPORARY riêng.
- Những việc tiếp theo: người dùng sửa Sequence/Wireframe; cập nhật ERD/bản thảo;
  chốt thời hạn hợp đồng, thời hạn giữ chỗ/hủy hồ sơ và giao tài khoản; triển khai UC
  thanh toán tạo hợp đồng + liên kết MaHopDong cùng transaction; Timer bỏ hóa đơn trùng kỳ.
- Không đổi Docker/Render/Aiven/config DB/authentication/session/middleware, không xóa Email.
- Kiểm tra dữ liệu đơn Đã duyệt cũ trước migration production: nếu đã có hợp đồng,
  cần liên kết MaHopDong đúng để tránh đếm lại giữ chỗ; không tự backfill theo phỏng đoán.
- Kiểm tra ngày 10/10/2026: UC03 51, đăng nhập/phân quyền 28, UC02 20, dữ liệu phòng 14
  kiểm thử đều đạt (113 tổng). PHP lint đạt; local có đủ hai FK mới, không có đơn Đã duyệt cũ.
  Chưa kiểm thử đồng thời hai tiến trình trên cùng dữ liệu vật lý hoặc UI trong trình duyệt.
- Checklist bàn giao: `docs/UC03_IMPLEMENTATION_NOTES.md`. Giả định mở đăng ký một tháng
  trước hết kỳ chỉ dùng để trình bày đồ án; không có yêu cầu tự chặn UC02 theo lịch.

## 15. Đơn giá tháng (10/10/2026)

- Branch `fix/approval-monthly-rate`: Approval tính tổng kỳ kế tiếp theo đơn giá tháng,
  không đổi schema hoặc dữ liệu MySQL. Mức người dùng chốt: 200.000 đồng/sinh viên/tháng.
- Test UC03 cập nhật sang đơn giá tháng, thêm kỳ 1/2/3, năm nhuận, một ngày và ngày đảo ngược;
  57 kiểm thử đạt. Chưa thử giao diện trực tiếp trong trình duyệt.

## 16. UC15 - Xem thông tin phòng (11/10/2026)

- Đơn giá tháng đã commit/push `1770093` trên `fix/approval-monthly-rate`.
  Branch `fix/uc15-room-information` đã cập nhật nền bằng commit này.
- UC15 tích hợp route công khai GET `index.php?action=rooms`, link trên header chung.
  Chọn khu Nam/Nữ, tòa và tầng theo CSDL; mặc định tòa/tầng đầu tiên của khu Nam.
- Model `Room` dùng chung truy vấn phòng với `Registration::rooms`; UC02 chỉ trả
  phòng khả dụng > 0, UC15 trả cả phòng đầy. Không đổi transaction hoặc nghiệp vụ UC02/UC03.
- Khả dụng = SucChua − hợp đồng Còn hạn − đơn Chờ duyệt − đơn Đã duyệt có
  MaHopDong NULL. Hiển thị riêng đang ở/đang giữ/khả dụng; số khả dụng âm hiển thị 0/Đầy.
- Bộ lọc sai trả 400, phương thức khác GET trả 405, lỗi CSDL trả 500 với thông báo an toàn.
  Không đọc hồ sơ cá nhân hoặc ghi dữ liệu; không thay schema/config DB/auth/session/deploy.
- Bản local `docs/Partner/uc15.php` chuyển về route mới; code MVC và CSS giao diện nằm trong
  controllers/models/views/assets và không phụ thuộc file đối tác. Toàn bộ `docs/Partner`
  không đưa vào commit theo yêu cầu người dùng. Không sửa UC5 hoặc commit bản dump SQL.
- UC15 cần schema UC03 đã migrate. Dump `sql/qlktx_v2.sql` đang là thay đổi riêng,
  chưa commit theo yêu cầu người dùng; không coi dump này là schema bàn giao UC15.
- Đặc tả UC15 đã đồng bộ; Sequence/Wireframe còn cần sửa theo code và dữ liệu thật.
- Kiểm tra: UC15 33, dữ liệu phòng 14, UC02 20, UC03 57, route/login/phân quyền 29
  đều đạt (153 tổng), PHP lint đạt. HTTP local WAMP kiểm tra route/filter/redirect/CSS đạt.
  Chưa kiểm tra bố cục trực quan trong trình duyệt hoặc production.
- Công việc tiếp theo: thử giao diện UC15 trên desktop/mobile, cập nhật hai hình UC15;
  kiểm tra migration UC03 trước deployment. Xem `docs/UC15_IMPLEMENTATION_NOTES.md`.
