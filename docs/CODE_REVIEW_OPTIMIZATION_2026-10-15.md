# Rà soát và tối ưu mã ứng dụng — 15/10/2026

## Phạm vi và nguyên tắc

Rà soát mã ứng dụng đang chạy: index, config, middleware, controllers, models, includes, views, JavaScript/CSS và bộ kiểm thử. Inventory hiện có 74 file PHP/JS/CSS ngoài docs/storage, gồm test frontend mới. PHP lint áp dụng cả các công cụ PHP và bản tham khảo trong docs (66 file).

Branch `refactor/application-performance` giữ toàn bộ thay đổi chưa commit từ trước; không commit/push/deploy trong đợt này. Không thay schema, dữ liệu thật, đơn giá, học kỳ, logic giữ chỗ, thời hạn hợp đồng, vai trò hoặc provider AI. Không viết lại báo cáo Word/hình ảnh/dump SQL hay tích hợp các prototype docs/Partner vào ứng dụng.

## Các thay đổi có căn cứ

| Khu vực | Trước | Sau |
|---|---|---|
| Kết nối MySQL | Mỗi lần require config/db.php tạo kết nối mới, kể cả middleware/controller/view trong cùng request | applicationDatabaseConnection() dùng lại PDO trong request; createDatabaseConnection() vẫn tạo kết nối độc lập cho snapshot/test. Không cache quyền/tài khoản |
| Kiểm tra phòng đăng ký | Tải danh sách mọi phòng phù hợp giới tính rồi lọc bằng PHP để tìm một phòng | availableRoom() lọc MaPhong và giới tính ngay trong SQL; cả subquery hợp đồng/giữ chỗ cũng giới hạn theo phòng đó |
| Trang xác nhận UC02 | Tải toàn bộ danh sách phòng dù view chỉ cần phòng đã chọn | Chỉ lấy phòng đã chọn ở bước 3; khi hết chỗ mới tải lại danh sách bước 2 |
| Snapshot phòng chatbot | Hai truy vấn cùng phép tổng hợp cho Nam/Nữ | Một truy vấn cho toàn bộ khu, giữ nguyên cột công khai/tính chỗ/transaction chỉ đọc |
| Dashboard sinh viên | Bốn truy vấn khi có hồ sơ: hồ sơ, hợp đồng, hóa đơn, phản ánh | Ba truy vấn; gộp hai số đếm thành một SELECT, giữ cách xác định hợp đồng/phòng cũ |
| Xác nhận thanh toán | SELECT * đọc nhiều cột hồ sơ không dùng trong transaction | Chỉ lấy các cột cần để kiểm tra/ghi, giữ thứ tự khóa và rollback |
| Navbar | Route phụ thuộc tên nhãn qua nhiều if và ternary lồng, còn nhánh Chatbot/icon đã bỏ | Mỗi mục khai báo route, nhóm action riêng để đánh dấu; xóa nhánh/icon không dùng |
| Xác nhận trên trình duyệt | Có thể bấm gửi form trong dialog nhiều lần | Chặn submit lặp và khóa nút đang gửi; khi quay lại trang chỉ phục hồi nút do JS khóa, giữ nguyên nút server đã disabled |
| Bộ lọc phòng JS | Mỗi lần đổi bộ lọc tìm lại toàn bộ card/input trong DOM | Lấy tham chiếu một lần; vẫn bỏ chọn phòng bị ẩn và cập nhật thông báo rỗng |
| Đầu vào và hiển thị ngày | Ngày chứa NUL có thể làm PHP ném ValueError; view gọi format trên kết quả parse không kiểm tra | Validate UTF-8/NUL/định dạng ngày; view có fallback cho ngày không hợp lệ |
| Endpoint kiểm tra DB | Công cụ chẩn đoán có thể chạy từ truy cập web bất kỳ trên môi trường không có Apache deny rule | Chỉ CLI hoặc loopback local, ngoài phạm vi trả 404 |

Các số truy vấn ở bảng là số lần gọi truy vấn trong code, không phải số lần quét bảng hoặc kết quả benchmark tải. Không khẳng định phần trăm tăng tốc vì chưa đo trên Render hoặc tải đồng thời.

## Kiểm chứng

| Bộ kiểm thử | Kết quả |
|---|---:|
| Login/role/route, gồm kết nối dùng chung nhưng quyền thay đổi/khóa phải có hiệu lực ngay | 46/46 |
| UC02 đăng ký, thêm ngày NUL/UTF-8 sai/phương thức không hỗ trợ | 23/23 |
| Dữ liệu phòng, thêm tra cứu một phòng và giới tính sai | 16/16 |
| UC15 | 33/33 |
| Nguồn phòng chatbot | 9/9 |
| UC03 | 58/58 |
| Thanh toán | 20/20 |
| UC11 model | 6/6 |
| Chatbot | 60/60 |
| Guide | 3/3 |
| Tổng PHP | **274/274** |
| Frontend VM: mở/đóng dialog, gửi lặp, nút server disabled, khôi phục trang, lọc phòng | **8/8** |

- PHP lint 66 file đạt; toàn bộ JavaScript ứng dụng parse thành công.
- `tests/frontend.js` dùng module chuẩn Node, không cài package. Trong phiên này chạy bằng Node runtime của công cụ vì lệnh node chưa có trên PATH của PowerShell.
- HTTP Apache local: trang chủ, login, thông tin phòng và hướng dẫn trả 200.
- Các test ghi nghiệp vụ dùng bảng TEMPORARY. Test phân quyền kết nối mới thêm dùng bảng tài khoản tạm, không khóa/đổi vai trò tài khoản thật.

## Các giới hạn được giữ lại

- UC11 dùng trạng thái Còn hạn; dashboard còn xét ngày và Đang ở. Không tự thống nhất hai nghĩa vì đây là thay đổi nghiệp vụ, đặc biệt khi hợp đồng theo học kỳ tương lai.
- Phần thanh toán hiện chỉ hóa đơn tiền phòng đầu tiên, chưa thay thế toàn bộ UC07.1/UC07.2 của thành viên khác.
- Chatbot chưa có ngữ cảnh nhiều lượt; vẫn có thể có lỗi diễn giải từ AI. Không thêm cache phòng làm cũ số chỗ.
- Chưa benchmark production, test nhiều tiến trình đồng thời hoặc kiểm tra trực quan desktop/mobile trong lượt rà soát này. Chưa push/deploy Render.
