# Báo cáo tiến độ tuần 7

## Thông tin chung

- Họ và tên sinh viên: Phan Minh Trọng, Hồ Hoàng Như Ý.
- MSSV: KTPM2411047, KTPM2411004.
- Khóa: 2024. Ngành đào tạo: Kỹ Thuật Phần Mềm. Khoa: Công Nghệ Thông Tin.
- Tên đề tài: “Hệ thống quản lý ký túc xá trường Đại học Kanto”.
- Thời gian thực hiện đề tài: Tuần 7, từ ngày 19/10/2026 đến ngày 25/10/2026.
- Cán bộ hướng dẫn: Trịnh Thanh Duy.
- Lần báo cáo thứ: 6.

## 1. Công việc đã thực hiện

| Thời gian | Công việc thực hiện | Kết quả đạt được |
|---|---|---|
| Từ 19/10/2026 đến 20/10/2026 | Hoàn thiện và rà soát các chức năng mở rộng của đăng ký/xét duyệt: kiểm tra email người đăng ký, minh chứng diện ưu tiên, trạng thái đơn và thông báo kết quả. | Luồng xét duyệt có kiểm tra dữ liệu và trạng thái; có cơ chế ghi nhận thông báo/email kết quả. Các bước gửi thư thật cần được xác minh thêm bằng log/provider. |
| Từ 20/10/2026 đến 22/10/2026 | Tích hợp và kiểm tra các chức năng điện nước, phản ánh sự cố, thông báo, thống kê và trợ lý AI trong phạm vi hệ thống. | Hệ thống có các nhóm chức năng mở rộng; trợ lý AI được giới hạn trong phạm vi thông tin KTX và có phương án fallback. |
| Từ 22/10/2026 đến 24/10/2026 | Hoàn thiện chức năng lập hóa đơn định kỳ tiền phòng và điện nước bằng script; kiểm tra điều kiện chỉ số, đơn giá, sinh viên có hợp đồng và chống tạo trùng. | Có script và test local cho tác vụ định kỳ; dữ liệu điện nước được phân bổ theo từng sinh viên. Scheduler trên môi trường triển khai chưa được xác minh đầy đủ. |
| Từ 24/10/2026 đến 25/10/2026 | Bổ sung UC17 - thanh toán hóa đơn bằng QR mô phỏng; tạo QR theo yêu cầu, kiểm tra thời hạn, token, số tiền và luồng xác nhận trên thiết bị khác. | Hoàn thiện luồng QR mô phỏng từ tạo mã, mở trang xác nhận đến biên nhận; không xem đây là giao dịch ngân hàng thật. Giao diện được rà soát trên desktop/mobile trong phạm vi kiểm tra hiện có. |
| Từ 24/10/2026 đến 25/10/2026 | Tích hợp các chức năng đã lập trình và bắt đầu soạn thảo Chương 3 theo yêu cầu tuần 7. | Có bản thảo Chương 3 gồm chức năng, kết quả kiểm thử, hạn chế, đánh giá và danh sách minh chứng cần bổ sung. |

## 2. Kế hoạch công việc tiếp theo

| Thời gian | Dự kiến công việc thực hiện | Dự kiến kết quả đạt được |
|---|---|---|
| Từ 26/10/2026 đến 27/10/2026 | Rà soát toàn bộ route, controller, model và view; sửa lỗi luồng phân quyền, CSRF, kiểm tra dữ liệu và các modal xác nhận. | Các chức năng chính hoạt động nhất quán; giảm lỗi truy cập sai vai trò và lỗi nhập liệu. |
| Từ 27/10/2026 đến 29/10/2026 | Tối ưu các truy vấn, transaction và thao tác frontend; rà soát lập hóa đơn định kỳ, thanh toán QR và điều kiện công nợ khi thanh lý. | Luồng xử lý dễ đọc hơn, bảo toàn nghiệp vụ và hạn chế tạo trùng dữ liệu. |
| Từ 29/10/2026 đến 30/10/2026 | Chạy kiểm tra cú pháp PHP, kiểm thử frontend và chạy lại các test nghiệp vụ có CSDL sau khi bật WAMP/MySQL. | Có số liệu PASS/FAIL hiện tại và danh sách lỗi cần xử lý trước khi chốt báo cáo. |
| Từ 30/10/2026 đến 01/11/2026 | Hoàn thiện Chương 3 bằng ảnh giao diện, kết quả kiểm thử và phần đánh giá; rà soát Chương 2 và các sơ đồ liên quan. | Có bản thảo Chương 3 hoàn chỉnh hơn, tài liệu và mã nguồn được đối chiếu. |

## 3. Đề xuất và kiến nghị

Đề nghị cán bộ hướng dẫn góp ý về phạm vi trình bày chức năng QR mô phỏng, cách ghi nhận các tác vụ scheduler mới kiểm tra local và số lượng ảnh giao diện cần đưa vào Chương 3.

