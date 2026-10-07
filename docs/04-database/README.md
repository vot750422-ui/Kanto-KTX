# Database Design

Thư mục này chứa tài liệu thiết kế cơ sở dữ liệu của Hệ thống Quản lý Ký túc xá Kanto.

## File chính

- `DATABASE_DESIGN.md`: mô tả cấu trúc và quan hệ cơ sở dữ liệu.
- `ERD.png`: sơ đồ ERD dùng để xem nhanh.
- `ERD.drawio`: file nguồn dùng để chỉnh sửa sơ đồ ERD.

## Ghi chú

Cơ sở dữ liệu hiện tại là nguồn tham chiếu kỹ thuật khi triển khai hệ thống.

Khi triển khai một chức năng, cần đối chiếu:

1. Use Case tương ứng.
2. Sequence Diagram.
3. Wireframe.
4. ERD.
5. Schema SQL hiện tại.

Nếu phát hiện mâu thuẫn giữa nghiệp vụ và CSDL, không tự ý thay đổi mà cần kiểm tra lại trước khi triển khai.