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
| Từ 26/10/2026 đến 27/10/2026 | Rà soát toàn bộ route, controller, model và view; sửa lỗi phân quyền giữa các vai trò, bổ sung kiểm tra dữ liệu đầu vào và hộp thoại xác nhận cho các thao tác xóa/thanh lý. | Các chức năng chính hoạt động nhất quán; khách vãng lai và vai trò sai bị chặn đúng tại từng route. |
| Từ 27/10/2026 đến 28/10/2026 | Tối ưu truy vấn và transaction: gộp truy vấn đếm về một câu, dùng khóa dòng khi xét duyệt, chống tạo trùng hóa đơn và phiên thanh toán. | Giảm số truy vấn mỗi trang; dữ liệu không bị tạo trùng khi hai yêu cầu chạy đồng thời. |
| Từ 28/10/2026 đến 29/10/2026 | Chạy kiểm tra cú pháp toàn bộ mã nguồn PHP và kiểm thử giao diện bằng Node.js. | Toàn bộ file PHP không lỗi cú pháp; các kịch bản giao diện (modal, bộ lọc, tạo QR) chạy đúng. |
| Từ 29/10/2026 đến 30/10/2026 | Chạy lại toàn bộ bộ kiểm thử nghiệp vụ có CSDL sau khi bật WAMP/MySQL và áp dụng đầy đủ migration. | Kết quả: `tests/run.php` 106/106, `tests/approval.php` 70/70, `tests/registration.php` 30/30, `tests/rooms.php` 16/16 — tổng **222/222 đạt**. |
| Từ 30/10/2026 đến 31/10/2026 | Hợp nhất toàn bộ bối cảnh dự án vào một tài liệu duy nhất phục vụ bàn giao giữa các thành viên và các công cụ hỗ trợ. | Tạo `docs/PROJECT_CONTEXT.md`: môi trường chạy, cấu trúc thư mục, trạng thái 21 use case, schema 20 bảng, quy tắc nghiệp vụ đã chốt, quy định báo cáo tiến độ. |
| Từ 30/10/2026 đến 01/11/2026 | Phân tích và đặc tả chức năng mới **Gia hạn lưu trú (UC18)** do Timer đảm nhiệm; đối chiếu với schema CSDL hiện tại để tìm vướng mắc kỹ thuật. | Tạo `docs/02-use-cases/UC18-renewal.md` (luồng chính, 3 luồng ngoại lệ, 4 quy tắc nghiệp vụ, kế hoạch triển khai 7 bước); lập danh sách 10 vướng mắc cần chốt trước khi code; cập nhật `USE_CASE_OVERVIEW.md`. |

---

## 2. Kế hoạch công việc tiếp theo

| Thời gian | Dự kiến công việc thực hiện | Dự kiến kết quả đạt được |
|---|---|---|
| Từ 02/11/2026 đến 03/11/2026 | Chốt các điểm nghiệp vụ còn mở của UC18 với giảng viên hướng dẫn; viết migration bổ sung bảng nhật ký hệ thống và cột học kỳ cho hợp đồng. | Biên bản chốt nghiệp vụ; file `sql/uc18-renewal.sql` chạy được trên CSDL hiện tại. |
| Từ 04/11/2026 đến 05/11/2026 | Triển khai model `Renewal` chạy toàn bộ tiến trình trong một transaction, có khóa chống chạy song song; viết endpoint chỉ chấp nhận POST kèm token bí mật. | Chức năng Gia hạn chạy được trên môi trường local. |
| Từ 06/11/2026 đến 07/11/2026 | Viết bộ kiểm thử UC18 (tối thiểu 8 trường hợp) và cấu hình lịch chạy tự động trên môi trường triển khai. | Kiểm thử UC18 đạt; Timer chạy tự động theo lịch. |
| Từ 08/11/2026 đến 09/11/2026 | Hoàn thiện Chương 4 (Kết luận và Đề xuất), đóng gói sản phẩm và kiểm tra lại hướng dẫn cài đặt. | Bản thảo Chương 4; gói mã nguồn và cơ sở dữ liệu sẵn sàng nộp. |

---

## 3. Đề xuất và kiến nghị

1. **Thời điểm kích hoạt chức năng Gia hạn:** nhóm đã chốt lấy **ngày hiện tại của học kỳ hiện tại** làm mốc kích hoạt, thay vì hiểu theo nghĩa "ngày đầu tiên của học kỳ mới". Đề nghị cán bộ hướng dẫn xác nhận cách hiểu này trước khi triển khai.
2. **Sinh viên mới được duyệt nhưng chưa thanh toán:** theo UC03, sinh viên này có trạng thái `Chờ thanh toán` và một hóa đơn `Chưa thu`. Nhóm đã chốt **cho phép đăng nhập để thanh toán** thay vì khóa tài khoản ngay, nhằm tránh trường hợp sinh viên không thể xem hóa đơn của chính mình. Đề nghị cán bộ hướng dẫn góp ý thêm.
3. **Định dạng mã hợp đồng:** mã hợp đồng hiện dùng dạng chuỗi cố định độ dài, khi số lượng hợp đồng tăng qua nhiều học kỳ có nguy cơ tràn. Nhóm đề xuất chuyển sang mã sinh tự động hoặc nới độ dài cột — cần ý kiến trước khi đổi cấu trúc CSDL.
4. **Bổ sung nội dung hai tài liệu đang để trống:** đặc tả UC05.2 Thanh lý hợp đồng và nhóm quy tắc nghiệp vụ chung.

---

Cần Thơ, ngày 01 tháng 11 năm 2026

| CÁN BỘ HƯỚNG DẪN | SINH VIÊN THỰC HIỆN |
|---|---|
| | |

---

*(*): Báo cáo được cập nhật đầy đủ thông tin:*
*- Tiến độ thực hiện công việc theo kế hoạch đã được thống nhất giữa CBHD và SV;*
*- Nội dung thực hiện theo quy định tại Điều 11 Khoản 1 Điểm c và Khoản 3 Điểm c: nội dung báo cáo giữa CBHD và SV sau khi được giao đề tài: trong 3 tuần đầu trao đổi/báo cáo mỗi tuần; từ tuần 4: định kỳ 2 tuần/lần hoặc khi cần hỗ trợ.*
