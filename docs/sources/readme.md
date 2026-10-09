# Sources

Thư mục này chứa các tài liệu nguồn, bản thảo và tài liệu tham khảo được sử dụng trong quá trình xây dựng **Hệ thống Quản lý Ký túc xá Kanto**.

Các tài liệu trong thư mục này được lưu lại để:

- tham khảo nội dung ban đầu;
- đối chiếu quá trình phân tích và thiết kế;
- hỗ trợ viết báo cáo;
- lưu lại các phiên bản tài liệu cũ;
- tham khảo ý tưởng từ các học phần trước.

## Nội dung hiện có

### `bản thảo chương 1.docx`

Bản thảo Chương 1 của báo cáo Đồ án 1.

Có thể dùng để tham khảo các nội dung như:

- lý do chọn đề tài;
- mục tiêu đề tài;
- đối tượng nghiên cứu;
- phạm vi nghiên cứu;
- phương pháp thực hiện;
- cấu trúc báo cáo.

---

### `LỜI MỞ ĐẦU.docx`

Bản thảo phần Lời mở đầu của báo cáo.

Dùng để tham khảo khi hoàn thiện nội dung báo cáo cuối cùng.

---

### `BÁO CÁO BÀI TẬP LỚN - ...`

Tài liệu được thực hiện trước đây trong học phần **Phân tích và thiết kế hệ thống**.

Tài liệu này chỉ dùng để tham khảo:

- cách phân tích hệ thống;
- cách trình bày;
- ý tưởng mô hình hóa;
- kinh nghiệm từ bài tập trước.

Không được mặc định rằng nghiệp vụ, CSDL, Use Case hoặc thiết kế trong tài liệu này giống với hệ thống Kanto-KTX hiện tại.

---

### `Mô tả CSDL.docx`

Tài liệu mô tả thiết kế cơ sở dữ liệu ở một thời điểm trước đó của dự án.

Một số nội dung có thể đã khác với schema SQL hiện tại.

Khi có khác biệt giữa tài liệu này và CSDL hiện tại, ưu tiên:

1. schema SQL hiện tại;
2. `docs/04-database/DATABASE_DESIGN.md`;
3. ERD hiện tại;
4. tài liệu này chỉ dùng để tham khảo lịch sử thiết kế.

---

### `Mô tả UC và sơ đồ trình tự.docx`

Tài liệu mô tả Use Case và Sequence Diagram ở phiên bản trước.

Dùng để tham khảo lịch sử phân tích và thiết kế.

Các đặc tả Use Case hiện tại trong `docs/02-use-cases/` và các sơ đồ hiện tại trong `docs/06-diagrams/` được ưu tiên hơn nếu có khác biệt.

---

# Quy tắc sử dụng tài liệu

Các file trong `sources/` **không phải source of truth để triển khai code**.

Khi triển khai chức năng, phải ưu tiên đọc tài liệu hiện tại theo thứ tự:

1. `PROJECT_STATE.md`
2. `AGENTS.md`
3. `docs/02-use-cases/`
4. `docs/05-ui/`
5. `docs/06-diagrams/`
6. `docs/04-database/DATABASE_DESIGN.md`
7. schema SQL hiện tại
8. source code hiện tại

Các file trong `sources/` chỉ được dùng để:

- tham khảo;
- đối chiếu;
- tìm lại nội dung cũ;
- hỗ trợ viết báo cáo.

Nếu phát hiện mâu thuẫn giữa tài liệu trong `sources/` và tài liệu hiện tại, không tự sửa code theo tài liệu cũ.

Phải báo lại điểm mâu thuẫn trước khi thay đổi nghiệp vụ, CSDL hoặc source code.

---

# Lưu ý cho Codex

Trước khi sử dụng một tài liệu trong thư mục này để đưa ra quyết định triển khai:

- kiểm tra xem đã có tài liệu mới hơn trong `docs/` hay chưa;
- không tự khôi phục nghiệp vụ đã bị loại bỏ;
- không tự thêm bảng, cột, Use Case hoặc chức năng chỉ vì chúng xuất hiện trong tài liệu cũ;
- không xem các tài liệu của học phần trước là thiết kế chính thức của Kanto-KTX;
- chỉ sử dụng chúng làm tài liệu tham khảo bổ sung.