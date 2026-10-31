# BÁO CÁO TIẾN ĐỘ THỰC HIỆN (BM 1.8.a)

**TRƯỜNG ĐẠI HỌC KỸ THUẬT - CÔNG NGHỆ CẦN THƠ** — **KHOA CÔNG NGHỆ THÔNG TIN**

**CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM** — Độc lập - Tự do - Hạnh phúc

---

## BÁO CÁO TIẾN ĐỘ THỰC HIỆN

☐ Đồ án tốt nghiệp  ☐ Khóa luận tốt nghiệp  ☐ Thực tập tốt nghiệp  ☒ **Đồ án 1**

**Đợt 1 năm học:** 2026 – 2027
**Ngành:** Kỹ thuật Phần mềm  **Khoa:** Công nghệ Thông tin

| | |
|---|---|
| **Họ và tên sinh viên** | Phan Minh Trọng, Hồ Hoàng Như Ý |
| **MSSV** | KTPM2411047, KTPM2411004 |
| **Khóa** | 2024 |
| **Ngành đào tạo** | Kỹ thuật Phần mềm — Khoa Công nghệ Thông tin |
| **Tên đề tài** | "Hệ thống quản lý ký túc xá trường Đại học Kanto" |
| **Thời gian thực hiện đề tài** | **Tuần 7**, từ ngày 19/10/2026 đến ngày 25/10/2026 |
| **Cán bộ hướng dẫn** | Trịnh Thanh Duy |
| **Lần báo cáo thứ** | 4 |

---

## 1. Công việc đã thực hiện

| Thời gian | Công việc thực hiện | Kết quả đạt được |
|---|---|---|
| Từ 19/10/2026 đến 20/10/2026 | Hoàn thiện luồng đăng ký lưu trú (UC02) và xét duyệt đơn (UC03): kiểm tra email người đăng ký, bắt buộc minh chứng cho diện ưu tiên, chặn trùng MSSV/CCCD, giữ chỗ theo sức chứa phòng. | Luồng đăng ký và xét duyệt chạy đúng quy tắc nghiệp vụ; đơn được duyệt sinh ra tài khoản, hồ sơ sinh viên và hóa đơn tiền phòng đầu tiên ở trạng thái `Chưa thu`. |
| Từ 20/10/2026 đến 21/10/2026 | Tích hợp thông báo kết quả xét duyệt qua email (Brevo HTTPS API) và ghi nhận trạng thái gửi vào bảng `emailthongbao`. | Có cơ chế gửi lại khi thất bại, không gửi trùng; nội dung email chứa tên đăng nhập và mật khẩu mặc định. |
| Từ 21/10/2026 đến 22/10/2026 | Hoàn thiện nhóm chức năng quản lý sinh viên (UC04), hợp đồng (UC05) và thanh lý hợp đồng kèm kiểm tra công nợ. | Tra cứu/cập nhật sinh viên hoạt động; thanh lý hợp đồng chặn khi còn hóa đơn chưa thu và ghi nhận tiền hoàn trả. |
| Từ 22/10/2026 đến 23/10/2026 | Hoàn thiện nhóm chức năng điện nước (UC07), phản ánh sự cố (UC10, UC11), thông báo (UC12) và thống kê báo cáo (UC14). | Các nhóm chức năng chạy được trên local; phản ánh sự cố có luồng tiếp nhận và xử lý theo vai trò. |
| Từ 23/10/2026 đến 24/10/2026 | Hoàn thiện lập hóa đơn định kỳ: hóa đơn tiền phòng theo học kỳ và hóa đơn điện nước phân bổ theo từng sinh viên; viết script chạy tự động. | Có script `generate-room-invoices.php` và `generate-utility-invoices.php`; chống tạo trùng theo khóa duy nhất. Scheduler trên môi trường triển khai chưa được xác minh. |
| Từ 24/10/2026 đến 25/10/2026 | Bổ sung UC17 — thanh toán hóa đơn bằng QR mô phỏng: tạo mã theo hóa đơn, kiểm tra thời hạn phiên, xác nhận trên thiết bị khác. | Hoàn thiện luồng QR từ tạo mã, mở trang xác nhận đến biên nhận. Đây là thanh toán mô phỏng, không phải giao dịch ngân hàng thật. |
| Từ 24/10/2026 đến 25/10/2026 | Tích hợp toàn bộ chức năng đã lập trình và bắt đầu soạn Chương 3. | Có bản thảo Chương 3 gồm danh sách chức năng, kết quả kiểm thử, hạn chế và danh sách minh chứng cần bổ sung. |

---

## 2. Kế hoạch công việc tiếp theo

| Thời gian | Dự kiến công việc thực hiện | Dự kiến kết quả đạt được |
|---|---|---|
| Từ 26/10/2026 đến 27/10/2026 | Rà soát toàn bộ route, controller, model và view; sửa lỗi phân quyền, kiểm tra dữ liệu đầu vào và các hộp thoại xác nhận. | Các chức năng chính hoạt động nhất quán; giảm lỗi truy cập sai vai trò và lỗi nhập liệu. |
| Từ 27/10/2026 đến 29/10/2026 | Tối ưu truy vấn, transaction và thao tác giao diện; rà soát lại lập hóa đơn định kỳ, thanh toán QR và điều kiện công nợ khi thanh lý. | Luồng xử lý dễ đọc hơn, bảo toàn nghiệp vụ và hạn chế tạo trùng dữ liệu. |
| Từ 29/10/2026 đến 30/10/2026 | Chạy kiểm tra cú pháp PHP, kiểm thử giao diện và chạy lại các bộ kiểm thử nghiệp vụ có CSDL sau khi bật WAMP/MySQL. | Có số liệu PASS/FAIL hiện tại và danh sách lỗi cần xử lý trước khi chốt báo cáo. |
| Từ 30/10/2026 đến 01/11/2026 | Hoàn thiện Chương 3 bằng ảnh giao diện và kết quả kiểm thử; rà soát Chương 2 và các sơ đồ liên quan. | Bản thảo Chương 3 hoàn chỉnh hơn, tài liệu và mã nguồn được đối chiếu. |

---

## 3. Đề xuất và kiến nghị

1. **Phạm vi trình bày chức năng QR mô phỏng:** đề nghị cán bộ hướng dẫn cho ý kiến về mức độ chấp nhận của luồng thanh toán mô phỏng trong phạm vi Đồ án 1, và cách diễn đạt để không gây hiểu nhầm là đã tích hợp ngân hàng thật.
2. **Cách ghi nhận tác vụ định kỳ:** hiện nhóm mới kiểm thử script trên môi trường local, chưa có bằng chứng scheduler chạy ổn định trên môi trường triển khai. Đề nghị hướng dẫn cách trình bày trung thực phần này trong Chương 3.
3. **Số lượng ảnh giao diện:** đề nghị cho ý kiến về số lượng ảnh chụp màn hình cần đưa vào Chương 3 để vừa đủ minh họa, vừa không làm báo cáo quá dài.

---

Cần Thơ, ngày 25 tháng 10 năm 2026

| CÁN BỘ HƯỚNG DẪN | SINH VIÊN THỰC HIỆN |
|---|---|
| | |

---

*(*): Báo cáo được cập nhật đầy đủ thông tin:*
*- Tiến độ thực hiện công việc theo kế hoạch đã được thống nhất giữa CBHD và SV;*
*- Nội dung thực hiện theo quy định tại Điều 11 Khoản 1 Điểm c và Khoản 3 Điểm c: nội dung báo cáo giữa CBHD và SV sau khi được giao đề tài: trong 3 tuần đầu trao đổi/báo cáo mỗi tuần; từ tuần 4: định kỳ 2 tuần/lần hoặc khi cần hỗ trợ.*
