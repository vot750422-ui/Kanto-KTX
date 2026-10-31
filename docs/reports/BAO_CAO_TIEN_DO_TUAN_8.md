# Báo cáo tiến độ tuần 8

## 1. Thông tin chung

- Đề tài: Website Quản lý Ký túc xá trường Đại học Kanto
- Tuần báo cáo: Tuần 8
- Thời điểm lập bản nháp: 31/10/2026
- Trọng tâm tuần: sửa lỗi, tối ưu hóa và kiểm tra mức độ ổn định của hệ thống

## 2. Công việc đã thực hiện

### 2.1. Phát triển và hoàn thiện chức năng

Nhóm đã xây dựng và tiếp tục hoàn thiện các nhóm chức năng sau:

- Đăng nhập, đăng xuất và phân quyền theo vai trò.
- Đăng ký lưu trú, chọn phòng và giữ chỗ.
- Xét duyệt đơn đăng ký và tạo dữ liệu phục vụ bước thanh toán.
- Quản lý thông tin sinh viên và tra cứu hợp đồng.
- Thanh lý hợp đồng theo điều kiện công nợ đã chốt.
- Tra cứu và xác nhận hóa đơn.
- Tạo QR thanh toán mô phỏng cho sinh viên.
- Xem thông tin lưu trú, thông báo và thống kê.
- Ghi nhận chỉ số điện nước và xử lý phản ánh sự cố.
- Lập hóa đơn tiền phòng, hóa đơn điện nước theo script/tác vụ định kỳ.
- Trợ lý AI hỏi đáp thông tin chung về ký túc xá.

### 2.2. Sửa lỗi và tối ưu hóa

- Rà soát lại luồng Route → Controller → Model → View.
- Làm rõ cấu trúc một số Controller và Model khó đọc.
- Giữ lại prepared statement, transaction, kiểm tra quyền và kiểm tra dữ liệu đầu vào.
- Bổ sung và duy trì các test nghiệp vụ cho các Use Case chính.
- Kiểm tra giao diện JavaScript liên quan đến modal, bộ lọc và tạo QR.

## 3. Kết quả kiểm tra hiện tại

| Hạng mục | Kết quả hiện tại | Ghi chú |
|---|---:|---|
| PHP lint | 131/131 file đạt | Kiểm tra cú pháp tĩnh |
| Frontend JavaScript | 12/12 đạt | Chạy được bằng Node.js |
| Test tích hợp `tests/run.php` | Chưa xác nhận trong lượt hiện tại | MySQL/WAMP chưa chạy, trả `Database connection failed` |
| Test nghiệp vụ có dùng CSDL | Chờ chạy lại | Cần bật MySQL trước khi kết luận |
| Kiểm tra triển khai Render/Brevo | Chưa xác nhận đầy đủ | Không đánh đồng với kiểm tra local |

Kết quả PHP lint và frontend là kết quả kiểm tra hiện tại. Các số liệu test nghiệp vụ được ghi trong tài liệu bàn giao trước đó cần được chạy lại sau khi bật WAMP/MySQL trước khi đưa vào báo cáo chính thức.

## 4. Vấn đề còn tồn tại

- Chưa hoàn thành Chương 3 về kết quả và thảo luận.
- Chưa hoàn thành Chương 4, kết luận, kiến nghị, phụ lục và tài liệu tham khảo.
- Chưa ghép thành một file báo cáo cuối `.docx` hoặc `.pdf`.
- Chưa bổ sung đầy đủ ảnh chụp giao diện sản phẩm chạy thật.
- `BUSINESS_RULES.md` còn trống và `USE_CASE_OVERVIEW.md` cần đồng bộ lại mã Use Case với đặc tả và mã nguồn hiện hành.
- Tác vụ lập hóa đơn định kỳ mới có code/script và test local; chưa dùng kết quả đó để khẳng định scheduler đã chạy ổn định trên môi trường triển khai.
- QR hiện là luồng thanh toán mô phỏng, chưa phải tích hợp ngân hàng thật.

## 5. Kế hoạch tiếp theo

### Trước khi gửi báo cáo tuần 8

1. Bật WAMP/MySQL.
2. Chạy lại `tests/run.php` và các test nghiệp vụ chính.
3. Ghi lại chính xác số ca PASS/FAIL hiện tại.
4. Cập nhật bảng kết quả kiểm thử trong bản báo cáo.
5. Rà soát lại các tài liệu đang lệch mã Use Case.

### Tuần 9

1. Hoàn thiện Chương 3 bằng kết quả kiểm thử và ảnh giao diện thực tế.
2. Viết Chương 4, kết luận, hạn chế và hướng phát triển.
3. Hoàn thiện phụ lục, tài liệu tham khảo và mục lục.
4. Ghép báo cáo thành `.docx` và xuất `.pdf`.
5. Đóng gói source code và kiểm tra lại hướng dẫn cài đặt.

## 6. Nội dung cần xin ý kiến giảng viên

- Mức độ chấp nhận của thanh toán QR mô phỏng trong phạm vi Đồ án 1.
- Cách trình bày các tác vụ định kỳ khi nhóm mới kiểm thử local, chưa có bằng chứng chạy ổn định trên môi trường triển khai.
- Phạm vi ảnh giao diện và số lượng Use Case cần minh họa trong Chương 3.

