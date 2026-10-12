# UC15 - Xem thông tin phòng (11/10/2026)

- Branch: `fix/uc15-room-information`, nền gồm commit `1770093` của monthly-rate.
- Mở `index.php?action=rooms`, chọn Khu → Tòa → Tầng. Các lựa chọn là link GET,
  hoạt động không cần JavaScript và giữ được bộ lọc khi tải lại trang.
- `models/Room.php` dùng chung cho UC02 và UC15. UC02 chỉ lấy phòng còn chỗ;
  UC15 lấy cả phòng đầy. Truy vấn tổng hợp số hợp đồng/đơn theo phòng, không truy vấn
  riêng từng thẻ; input dùng prepared statement. UC03 giữ kiểm tra riêng trong transaction.
- Công thức: SucChua − HopDong Còn hạn − DonDangKy Chờ duyệt − DonDangKy Đã duyệt
  có MaHopDong NULL. Đơn đã liên kết hợp đồng không giữ chỗ thêm lần nữa.
- Trang hiển thị riêng đang ở/đang giữ/khả dụng; không đọc hoặc hiển thị hồ sơ cá nhân.
  Chỉ GET, không giữ chỗ, không chọn phòng đăng ký, không ghi dữ liệu.
- Dùng config/app.php, config/db.php, header và style chung; không tạo lại PDO.
  Không đổi schema, dữ liệu thật, auth, session hoặc cấu hình deploy.
- Cần schema UC03 đã áp dụng (`dondangky.MaHopDong`). Không import bản dump chưa commit
  để chạy UC15; kiểm tra migration UC03 trước khi triển khai lên môi trường khác.
- Toàn bộ `docs/Partner` giữ ở máy local, không đưa vào commit theo yêu cầu người dùng.
  Sau khi push UC15, đã khôi phục bản gốc đối tác và sửa đường dẫn UC15/UC5 để xem
  riêng tại `docs/Partner/uc15.php` và `docs/Partner/uc5.php`. Bản gốc UC15 vẫn có
  lỗi cột `hienco`/bộ lọc mã tòa. Giao diện thực tế dùng `assets/css/room-information.css`
  và không phụ thuộc file của đối tác.
- Đặc tả UC15 đã đồng bộ quy tắc giữ chỗ UC03. Sequence cũ còn thiếu trường hợp
  Đã duyệt chưa có hợp đồng; Wireframe ghi 5 tầng/10 phòng một tầng, trong khi seed
  hiện có 4 tầng với 13/13/12/12 phòng. Cần cập nhật hai hình khi hoàn thiện tài liệu.

Kiểm tra: UC15 33, phòng 14, UC02 20, UC03 57, route/login/phân quyền 29 đều đạt
(153 tổng); PHP lint đạt. HTTP WAMP: mặc định 13 phòng, nữ/T04/tầng 4 có 12 phòng;
tầng 5 trả 400; đường dẫn đối tác chuyển về UC15; CSS trả 200. Chưa kiểm tra bố cục
trực quan trên trình duyệt hoặc dữ liệu production.
