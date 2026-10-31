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
| **Thời gian thực hiện đề tài** | **Tuần 8**, từ ngày 26/10/2026 đến ngày 01/11/2026 |
| **Cán bộ hướng dẫn** | Trịnh Thanh Duy |
| **Lần báo cáo thứ** | 5 |

---

## 1. Công việc đã thực hiện

| Thời gian | Công việc thực hiện | Kết quả đạt được |
|---|---|---|
| Từ 26/10/2026 đến 27/10/2026 | Kiểm tra tính ổn định của hệ thống sau khi bổ sung UC03: chạy lại toàn bộ kiểm thử trên môi trường local, đối chiếu schema CSDL thực tế với file migration. | Phát hiện CSDL local thiếu hai cột `MaHoaDon`, `MaHopDong` trong bảng `dondangky`; đã áp dụng lại migration `sql/uc03-approval.sql` và xác minh hai khóa ngoại tồn tại. |
| Từ 27/10/2026 đến 28/10/2026 | Rà soát và xác định nguyên nhân kiểm thử thất bại trên phiên bản PHP mới. | Xác định nguyên nhân là `session_set_save_handler()` bị deprecate từ PHP 8.4, làm cảnh báo bắn ra trước `session_start()` và khiến bộ kiểm thử báo sai. Đây là lỗi tương thích của bộ kiểm thử, không phải lỗi nghiệp vụ. |
| Từ 28/10/2026 đến 29/10/2026 | Chuẩn hóa môi trường chạy kiểm thử và ghi lại quy trình. | Chốt dùng PHP 8.0.30 cho bộ kiểm thử; ghi lại 4 lệnh chạy và kết quả chuẩn **113/113 đạt** vào tài liệu hướng dẫn làm việc của dự án. |
| Từ 29/10/2026 đến 30/10/2026 | Hợp nhất toàn bộ bối cảnh dự án vào một tài liệu duy nhất phục vụ bàn giao và làm việc nhóm. | Tạo `docs/PROJECT_CONTEXT.md`: môi trường chạy, cấu trúc thư mục, trạng thái 20 Use Case, schema 17 bảng, quy tắc nghiệp vụ đã chốt, quy định báo cáo tiến độ. |
| Từ 30/10/2026 đến 31/10/2026 | Phân tích chức năng **Gia hạn (UC-21)** theo yêu cầu mới trong báo cáo phân tích thiết kế; đối chiếu với schema CSDL hiện tại. | Lập danh sách **10 vướng mắc kỹ thuật** cần giải quyết trước khi code (thiếu cột học kỳ trong bảng hợp đồng, thiếu bảng nhật ký hệ thống, nguy cơ tràn mã hợp đồng, ảnh hưởng dây chuyền tới UC02/UC03, chưa có cơ chế Timer trên môi trường production). |
| Từ 31/10/2026 đến 01/11/2026 | Viết đặc tả chuẩn hóa cho UC21 Gia hạn; bổ sung vào danh mục Use Case của hệ thống. | Tạo `docs/02-use-cases/UC21-renewal.md` (luồng chính, 3 luồng ngoại lệ, 4 quy tắc nghiệp vụ, kế hoạch triển khai 7 bước); cập nhật `USE_CASE_OVERVIEW.md`. |

---

## 2. Kế hoạch công việc tiếp theo

| Thời gian | Dự kiến công việc thực hiện | Dự kiến kết quả đạt được |
|---|---|---|
| Từ 02/11/2026 đến 03/11/2026 | Chốt hướng xử lý các vướng mắc của UC21 với giảng viên hướng dẫn; thiết kế migration bổ sung bảng nhật ký hệ thống. | Biên bản chốt nghiệp vụ; file `sql/uc21-renewal.sql` sẵn sàng. |
| Từ 04/11/2026 đến 05/11/2026 | Triển khai model `Renewal` chạy toàn bộ tiến trình trong một transaction; viết endpoint chỉ chấp nhận POST kèm token bí mật. | Chức năng Gia hạn chạy được trên môi trường local. |
| Từ 06/11/2026 đến 07/11/2026 | Viết bộ kiểm thử UC21 (tối thiểu 8 trường hợp) và cấu hình lịch chạy tự động trên môi trường production. | Kiểm thử UC21 đạt; Timer chạy tự động theo lịch. |
| Từ 08/11/2026 đến 09/11/2026 | Hoàn thiện Chương 4 báo cáo (Kết luận và Đề xuất), đóng gói sản phẩm. | Bản thảo Chương 4; gói source code và cơ sở dữ liệu. |

---

## 3. Đề xuất và kiến nghị

1. **Đề nghị cho ý kiến về thời điểm kích hoạt chức năng Gia hạn.** Đặc tả ghi "ngày hiện tại là ngày bắt đầu học kỳ mới", nhưng tại thời điểm đó hóa đơn tiền phòng của kỳ vừa kết thúc mới là căn cứ xét điều kiện. Nhóm đề xuất xác định kỳ vừa kết thúc là kỳ liền trước kỳ bắt đầu trong ngày, thay vì "học kỳ hiện tại" theo cách hiểu thông thường.
2. **Đề nghị cho ý kiến về sinh viên mới được duyệt nhưng chưa thanh toán.** Theo UC03, sinh viên được duyệt có trạng thái `Chờ thanh toán` và một hóa đơn `Chưa thu`. Nếu chức năng Gia hạn chạy trước khi sinh viên thanh toán, tài khoản sẽ bị khóa ngay và sinh viên không thể đăng nhập để xem hóa đơn. Nhóm đề xuất miễn trừ nhóm sinh viên chưa từng có hợp đồng, hoặc cho phép đăng nhập ở chế độ chỉ xem để thanh toán.
3. **Đề nghị bổ sung nội dung cho hai tài liệu đang để trống trong repository:** đặc tả UC05.2 Thanh lý hợp đồng và nhóm quy tắc nghiệp vụ chung.

---

Cần Thơ, ngày 01 tháng 11 năm 2026

| CÁN BỘ HƯỚNG DẪN | SINH VIÊN THỰC HIỆN |
|---|---|
| | |

---

*(*): Báo cáo được cập nhật đầy đủ thông tin:*
*- Tiến độ thực hiện công việc theo kế hoạch đã được thống nhất giữa CBHD và SV;*
*- Nội dung thực hiện theo quy định tại Điều 11 Khoản 1 Điểm c và Khoản 3 Điểm c: nội dung báo cáo giữa CBHD và SV sau khi được giao đề tài: trong 3 tuần đầu trao đổi/báo cáo mỗi tuần; từ tuần 4: định kỳ 2 tuần/lần hoặc khi cần hỗ trợ.*
