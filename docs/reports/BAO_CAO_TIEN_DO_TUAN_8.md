# Báo cáo tiến độ tuần 8

## Thông tin chung

- Họ và tên sinh viên: Phan Minh Trọng, Hồ Hoàng Như Ý.
- MSSV: KTPM2411047, KTPM2411004.
- Khóa: 2024. Ngành đào tạo: Kỹ Thuật Phần Mềm. Khoa: Công Nghệ Thông Tin.
- Tên đề tài: “Hệ thống quản lý ký túc xá trường Đại học Kanto”.
- Thời gian thực hiện đề tài: Tuần 8, từ ngày 26/10/2026 đến ngày 01/11/2026.
- Cán bộ hướng dẫn: Trịnh Thanh Duy.
- Lần báo cáo thứ: 7.

## 1. Công việc đã thực hiện

| Thời gian | Công việc thực hiện | Kết quả đạt được |
|---|---|---|
| Từ 26/10/2026 đến 27/10/2026 | Rà soát toàn bộ route, controller, model và view; kiểm tra luồng phân quyền 4 vai trò, bảo vệ CSRF và cơ chế bảo vệ phiên đăng nhập. | Hệ thống chuẩn hóa 100% route, chặn triệt để các truy cập trái quyền (HTTP 403) và phương thức không hợp lệ (HTTP 405). Bộ test `tests/run.php` đạt 106/106 ca kiểm thử. |
| Từ 27/10/2026 đến 29/10/2026 | Tối ưu hóa các giao dịch CSDL (transaction, prepared statement) khi duyệt đơn và thanh lý hợp đồng; chia nhỏ công nợ điện nước theo từng sinh viên; bổ sung UC18 - Tự động gia hạn lưu trú cho sinh viên đủ điều kiện. | Hoàn thiện chức năng UC18 với cơ chế khóa dữ liệu (mutex lock) chống gia hạn trùng lặp; bảo vệ toàn vẹn dữ liệu hợp đồng và công nợ; kiểm thử `tests/renewal.php` đạt 13/13 ca. |
| Từ 29/10/2026 đến 30/10/2026 | Chạy kiểm tra cú pháp PHP (PHP lint), kiểm thử logic frontend (Node.js) và kiểm thử nghiệp vụ toàn diện trên môi trường WAMP/MySQL cục bộ. | Quét sạch 125/125 file PHP không lỗi cú pháp; `tests/frontend.js` đạt 12/12 ca; toàn bộ hệ thống đạt 235/235 ca kiểm thử tích hợp (106 route + 70 duyệt đơn + 30 đăng ký + 16 tra cứu phòng + 13 gia hạn). |
| Từ 30/10/2026 đến 01/11/2026 | Khởi tạo dữ liệu sinh viên mẫu sinh động, kiểm thử luồng thực tế và chụp 24 ảnh minh chứng giao diện; hoàn thiện Bản thảo Chương 3 (Kết quả và thảo luận) đính kèm. | Hoàn thành tệp `BAN_THAO_CHUONG_3.docx` (4.68 MB) với 24 hình ảnh minh chứng thực tế trên hệ thống đang chạy, có mục lục hyperlink 2 chiều và bảng kiểm soát ảnh chi tiết. |

## 2. Kế hoạch công việc tiếp theo

| Thời gian | Dự kiến công việc thực hiện | Dự kiến kết quả đạt được |
|---|---|---|
| Từ 02/11/2026 đến 03/11/2026 | Tiếp thu ý kiến góp ý của Cán bộ hướng dẫn về Bản thảo Chương 3 và phương án thanh toán QR mô phỏng để chỉnh sửa, hoàn thiện. | Bản thảo Chương 3 được chuẩn hóa theo đúng yêu cầu học thuật của Khoa và Nhà trường. |
| Từ 04/11/2026 đến 06/11/2026 | Soạn thảo Chương 4 (Kết luận và hướng phát triển); tổng kết ưu điểm, hạn chế và đề xuất mở rộng hệ thống (như cổng thanh toán trực tuyến, thông báo qua ứng dụng di động). | Hoàn thành nội dung Chương 4, lời cảm ơn và tài liệu tham khảo. |
| Từ 07/11/2026 đến 08/11/2026 | Ghép toàn bộ báo cáo hoàn chỉnh (Lời mở đầu, Chương 1, 2, 3, 4, Phụ lục) thành một file Word và xuất file PDF chính thức; rà soát format BM 1.8.a. | Bản báo cáo toàn văn Đồ án 1 hoàn chỉnh, đồng bộ thuật ngữ, mã Use Case (UC01–UC18) và bảng biểu. |

## 3. Đề xuất và kiến nghị

- Nhóm kính gửi Thầy xem xét và cho ý kiến về **Bản thảo Chương 3** (đính kèm tệp `BAN_THAO_CHUONG_3.docx`) với đầy đủ 24 ảnh giao diện và kết quả kiểm thử thực tế.
- Xin ý kiến Thầy về mức độ chấp nhận của cơ chế thanh toán QR mô phỏng trong phạm vi đề tài Đồ án 1 và định hướng hoàn thiện Chương 4 trong tuần tới.
