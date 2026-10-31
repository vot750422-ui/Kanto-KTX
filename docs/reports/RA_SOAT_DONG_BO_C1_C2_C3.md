# Bảng rà soát đồng bộ Lời mở đầu, Chương 1, Chương 2, Chương 3

| Hạng mục cần khớp | Tình trạng hiện tại (Có khớp không) | Ghi chú & Cách sửa |
|---|---|---|
| Số lượng Use Case | ❌ Bị lệch: Chương 2 và mục 2.3 sử dụng "UC-21" hoặc "UC21" ở nhiều nơi. | Sửa tất cả "UC21" và "UC-21" thành "UC18". Phải là 18 UC (UC01–UC18). |
| Tên đề tài | ✅ Khớp: "Hệ thống quản lý ký túc xá trường Đại học Kanto" | Giữ nguyên. |
| Công nghệ sử dụng | ✅ Khớp: PHP thuần + MySQL + Apache (WAMP) | Không thêm Bootstrap nếu code không sử dụng thực sự (chỉ cần đảm bảo không viết dư). |
| Phạm vi chức năng | ✅ Khớp: Danh sách nhóm chức năng không hứa hẹn thêm ngoài thực tế. | Giữ nguyên. Nếu có chức năng nào đã nói mà chưa làm thì chuyển vào mục hạn chế ở Chương 3. |
| Số liệu kiểm thử | ❌ Lệch: Chương 3 cũ sử dụng số liệu "113" cho test `run.php` và các số liệu khác. Tuần 8 là `222/222` (106 + 70 + 30 + 16). | Cập nhật lại Chương 3: Test `tests/run.php` đạt 106, tổng 222/222 theo báo cáo tuần 8. |
| Số bảng CSDL | ✅ Khớp: 20 bảng | Đã check trong tài liệu mô tả CSDL (Chương 2, bảng 2.57). Giữ nguyên. |
| Tác nhân | ✅ Khớp: 5 tác nhân người dùng + Timer | Giữ nguyên, đã đúng trong phần đặc tả. |
| Mục tiêu đề tài | ✅ Khớp giữa Lời mở đầu và Chương 1 | Giữ nguyên. |
