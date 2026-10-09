# Deploy Kanto-KTX bằng Docker trên Render

## Local WAMP

Chạy như trước tại `/Kanto-KTX/index.php`. Khi không cấu hình biến môi trường,
PDO dùng MySQL local ở 127.0.0.1:3306, CSDL qlktx_v2 và tài khoản local mặc định.
Không cần tạo `.env`; ứng dụng đọc biến môi trường tiến trình, không tự đọc file `.env`.

## Render

Sau khi có MySQL online và import schema/dữ liệu cần thiết:

1. Tạo Web Service từ repository GitHub, branch `main`.
2. Language: Docker; Root Directory: để trống; Dockerfile: `./Dockerfile`.
3. Compute: Free nếu có sẵn cho tài khoản. Giữ Docker Command mặc định.
4. Cấu hình `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.
   DB_HOST phải là hostname của MySQL online, không phải localhost.
5. `APP_BASE_PATH=/` đã được đặt trong image. Render cung cấp `RENDER=true`,
   được dùng để bật cookie Secure sau proxy HTTPS; WAMP vẫn xét HTTPS trực tiếp.
6. Chỉ deploy sau khi cấu hình DB đúng. Kiểm tra trang chủ, login, phân quyền,
   đăng xuất, ba bước UC02, CSS/JS/logo và việc HTTP không đọc được storage/config.

Apache nghe cổng 80; Render phát hiện cổng HTTP. Không dùng Node/yarn.
Không tự import SQL lúc container khởi động. Không đưa credential vào source,
build arguments hoặc Dockerfile. Nhà cung cấp MySQL phải cho phép kết nối từ
Render; yêu cầu TLS/chứng chỉ của nhà cung cấp cần được xác nhận trước khi dùng
DB thật (cấu hình PDO hiện chưa bổ sung tùy chọn chứng chỉ TLS).

## Docker local (khi đã cài Docker)

```sh
docker build -t kanto-ktx .
docker run --rm -p 8080:80 --env-file .env kanto-ktx
```

Tự tạo `.env` ngoài Git với các biến DB; với Docker Desktop, có thể dùng
`DB_HOST=host.docker.internal` để đến MySQL WAMP nếu MySQL cho phép kết nối.
Mở `http://localhost:8080/`. `.env` không được COPY vào image.

## Phạm vi thay đổi

- `config/app.php`: appUrl(), mặc định /Kanto-KTX; Docker dùng /.
- Link/redirect được chuyển sang appUrl() ở index, Auth/Role middleware,
  AuthController, logout controller, RegistrationController, header/logout include,
  login view, registration view và bốn dashboard. Không đổi route hay quyền.
- `config/db.php`: giữ $pdo, đọc biến DB, prepared statements native,
  trả lỗi chung HTTP 500 khi kết nối thất bại.
- Docker cài pdo_mysql và mbstring cho UC02; cấu hình dung lượng upload phù hợp.
- Apache chặn thư mục nội bộ/storage và không cho liệt kê thư mục.
- .dockerignore loại tài liệu, tests, SQL, upload local và file môi trường.
- tests/worker.php xử lý đường dẫn dashboard với cả root và thư mục con.
- Model và schema/nghiệp vụ không thay đổi.

## Giới hạn deploy thử nghiệm

RegistrationController lưu ảnh trong `storage/registration/`, đường dẫn tương đối
lưu tại FileMinhChung. Filesystem Render không bền vững: restart/redeploy có thể
làm mất ảnh trong khi bản ghi MySQL vẫn còn. Session PHP và hồ sơ nháp cũng có
thể mất. Chưa thêm cloud storage; chưa phù hợp để nhận hồ sơ thật cần lưu ảnh lâu dài.

Chưa có MySQL online thì login, dashboard có truy vấn DB và UC02 chưa thể hoạt động.
Docker không kèm MySQL. Không thay MySQL bằng PostgreSQL.

Tham khảo: https://render.com/docs/docker, https://render.com/docs/web-services,
https://render.com/docs/disks.
