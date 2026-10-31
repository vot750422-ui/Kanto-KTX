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
| Từ 19/10/2026 đến 20/10/2026 | Triển khai chức năng **UC03 – Xét duyệt đơn đăng ký lưu trú** trên nhánh `feature/uc03-approval`: danh sách đơn chờ duyệt có phân trang, trang chi tiết hồ sơ, xem ảnh minh chứng có phân quyền, thao tác duyệt và từ chối. | Hoàn thành 5 route `approval`, `approval-detail`, `approval-proof`, `approval-approve`, `approval-reject`; chỉ Nhân viên quản lý KTX được truy cập; POST ghi dữ liệu có CSRF. |
| Từ 20/10/2026 đến 21/10/2026 | Xây dựng nghiệp vụ duyệt đơn: tạo tài khoản sinh viên, tạo hồ sơ sinh viên, lập hóa đơn tiền phòng kỳ kế tiếp, giữ chỗ và giải phóng chỗ. | Transaction dùng named lock dùng chung với UC02, mức cô lập `READ COMMITTED`, khóa phòng rồi khóa đơn; lỗi ở bất kỳ bước nào đều rollback toàn bộ. |
| Từ 21/10/2026 đến 22/10/2026 | Thiết kế migration CSDL cho UC03: thêm cột `MaHoaDon`, `MaHopDong` vào bảng `dondangky` kèm khóa ngoại và ràng buộc duy nhất; cập nhật lại mức ưu tiên `dienuutien`. | File `sql/uc03-approval.sql`; áp dụng thành công trên CSDL local `qlktx_v2`; giữ nguyên dữ liệu và ràng buộc cũ. |
| Từ 22/10/2026 đến 23/10/2026 | Bổ sung kiểm tra trùng MSSV/CCCD cho UC02 ở cả bước nhập và bước gửi; đồng bộ logic đếm số chỗ khả dụng giữa UC02 và UC03. | UC02 chặn trùng MSSV/CCCD trước khi tạo đơn; công thức số chỗ khả dụng thống nhất giữa hai Use Case. |
| Từ 23/10/2026 đến 24/10/2026 | Viết bộ kiểm thử tự động cho UC03 và chạy hồi quy toàn bộ hệ thống. | **51 kiểm thử UC03 đạt**; hồi quy **28/28** đăng nhập - phân quyền, **20/20** UC02, **14/14** dữ liệu phòng. Tổng **113/113 đạt**. |
| Từ 24/10/2026 đến 25/10/2026 | Rà soát tính nhất quán giữa đặc tả Use Case, sơ đồ tuần tự, wireframe và CSDL thực tế; ghi nhận các điểm lệch cần đồng bộ. | Tài liệu bàn giao `docs/UC03_IMPLEMENTATION_NOTES.md`; danh sách việc cần đồng bộ tài liệu và các điểm mâu thuẫn giữa hai bộ tài liệu. |

---

## 2. Kế hoạch công việc tiếp theo

| Thời gian | Dự kiến công việc thực hiện | Dự kiến kết quả đạt được |
|---|---|---|
| Từ 26/10/2026 đến 28/10/2026 | Hoàn thiện các Use Case Module 2: UC04.1 Tra cứu sinh viên, UC04.2 Cập nhật thông tin sinh viên, UC05.1 Tra cứu hợp đồng. | Giao diện tra cứu và cập nhật hoạt động; có kiểm thử tự động cho từng Use Case. |
| Từ 29/10/2026 đến 30/10/2026 | Triển khai UC06 Ghi nhận chỉ số điện nước và UC07.1 Tra cứu hóa đơn. | Chức năng ghi chỉ số có kiểm tra chỉ số không giảm và duy nhất theo tháng; màn hình tra cứu hóa đơn hoạt động. |
| Từ 31/10/2026 đến 01/11/2026 | Merge nhánh `feature/uc03-approval` vào `main`; soạn thảo Chương 3 báo cáo. | UC03 chạy trên môi trường production; bản thảo Chương 3 (Kết quả và Thảo luận). |

---

## 3. Đề xuất và kiến nghị

1. **Đề nghị thống nhất cách đánh số Use Case.** Hiện tồn tại hai cách đánh số khác nhau giữa bộ đặc tả mới (`docs/02-use-cases/`) và báo cáo phân tích thiết kế trước đây (ví dụ: Lập hóa đơn tiền phòng là UC10.1 theo bộ mới nhưng là UC-20 theo báo cáo cũ). Đề nghị chốt một bảng mã duy nhất trước khi hoàn thiện Chương 2 và Chương 3.
2. **Đề nghị bổ sung đặc tả cho hai Use Case còn thiếu nội dung:** UC05.2 Thanh lý hợp đồng và nhóm quy tắc nghiệp vụ chung hiện đang để trống trong repository.
3. **Đề nghị góp ý hướng triển khai chức năng Gia hạn (UC-21).** Chức năng này do Timer đảm nhiệm, tự động chuyển hợp đồng cũ sang trạng thái `Hết hạn`, tạo hợp đồng mới cho sinh viên đã thanh toán và khóa tài khoản sinh viên còn nợ. Nhóm đã phân tích và nhận thấy một số vướng mắc về dữ liệu: bảng `hopdong` hiện chưa có cột liên kết học kỳ, chưa có bảng nhật ký hệ thống, và cần làm rõ thời điểm xác định "học kỳ hiện tại". Nhóm xin ý kiến hướng xử lý trước khi triển khai.

---

Cần Thơ, ngày 25 tháng 10 năm 2026

| CÁN BỘ HƯỚNG DẪN | SINH VIÊN THỰC HIỆN |
|---|---|
| | |

---

*(*): Báo cáo được cập nhật đầy đủ thông tin:*
*- Tiến độ thực hiện công việc theo kế hoạch đã được thống nhất giữa CBHD và SV;*
*- Nội dung thực hiện theo quy định tại Điều 11 Khoản 1 Điểm c và Khoản 3 Điểm c: nội dung báo cáo giữa CBHD và SV sau khi được giao đề tài: trong 3 tuần đầu trao đổi/báo cáo mỗi tuần; từ tuần 4: định kỳ 2 tuần/lần hoặc khi cần hỗ trợ.*
