# Tối ưu code đã có trên GitHub

Branch này xuất phát từ main `e9d683d`, chỉ tối ưu chức năng đã công bố. UC11, thanh toán và chatbot mới giữ ở workspace local, không nằm trong bản này. Không thay schema, dữ liệu thật, tính tiền hoặc quy tắc giữ chỗ.

## Thay đổi

- Dùng lại PDO trong một HTTP request, vẫn xác thực lại quyền từ CSDL; hàm tạo kết nối độc lập giữ riêng cho test và transaction độc lập.
- Kiểm tra phòng đăng ký bằng truy vấn theo MaPhong/giới tính, không tải toàn bộ danh sách để lọc trong PHP. Giới hạn cả phép đếm hợp đồng/giữ chỗ theo phòng cần kiểm tra.
- Bước 3 đăng ký chỉ lấy phòng đã chọn; khi hết chỗ quay về bước 2 mới tải danh sách.
- Dashboard sinh viên gộp hai số đếm thành một SELECT, giảm từ 4 xuống 3 truy vấn khi có hồ sơ.
- JS lọc phòng lấy tham chiếu DOM một lần; dialog chặn gửi lặp, khôi phục nút khi quay lại trang và giữ nguyên nút đã bị server disabled.
- Chặn dữ liệu đăng ký sai UTF-8/NUL và phương thức ngoài GET/POST; giới hạn công cụ kiểm tra DB ở CLI/loopback.

## Kiểm thử

- PHP: run 30, registration 23, rooms 16, room-information 33, approval 58, guide 3 — **163/163** đạt.
- `node tests/frontend.js`: **8/8** kiểm tra dialog và lọc phòng, dùng module chuẩn Node/DOM mô phỏng. Trong phiên thực hiện đã chạy bằng Node runtime của công cụ.
- PHP lint **52 file** và parse toàn bộ JavaScript ứng dụng đạt.
- Các test ghi nghiệp vụ dùng bảng TEMPORARY; không gửi dữ liệu mẫu sang dịch vụ ngoài hoặc sửa dữ liệu thật.
- Chưa benchmark trên Render, kiểm tra bố cục trực quan hoặc deploy. Giảm số truy vấn không phải số đo phần trăm tăng tốc.
