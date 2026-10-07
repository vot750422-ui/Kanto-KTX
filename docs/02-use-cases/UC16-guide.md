# UC16 - Xem hướng dẫn

## 1. Thông tin chung

- **Mã Use Case:** UC16
- **Tên Use Case:** Xem hướng dẫn
- **Tác nhân chính:** Khách vãng lai

## 2. Mục đích

Hỗ trợ người dùng xem hướng dẫn thực hiện đăng ký lưu trú và sử dụng các chức năng cơ bản của hệ thống.

## 3. Tiền điều kiện

- Người dùng đã truy cập Website.
- Người dùng không bắt buộc phải đăng nhập để sử dụng chức năng.

## 4. Hậu điều kiện

- Nội dung hướng dẫn được hiển thị cho người dùng.
- Không có dữ liệu nào trong hệ thống bị thay đổi sau khi thực hiện chức năng.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Người dùng chọn chức năng **“Hướng dẫn”** trên giao diện Website. | |
| 2 | | Hệ thống hiển thị trang hướng dẫn đăng ký lưu trú và các bước hướng dẫn thực hiện đăng ký lưu trú. |

## 6. Nội dung hướng dẫn

Trong phạm vi đặc tả hiện tại, UC16 hiển thị nội dung hướng dẫn thực hiện đăng ký lưu trú.

Nội dung hướng dẫn cần mô tả các bước cơ bản để Khách vãng lai biết cách thực hiện chức năng:

`UC02 - Đăng ký lưu trú`

Nội dung cụ thể của từng bước hướng dẫn phải phù hợp với quy trình đăng ký lưu trú đã được xác định trong UC02.

## 7. Quy tắc nghiệp vụ

### BR-UC16-01 - Chức năng công khai

UC16 dành cho:

`Khách vãng lai`

Người dùng không cần đăng nhập để xem nội dung hướng dẫn.

### BR-UC16-02 - Chức năng chỉ đọc

UC16 chỉ thực hiện việc hiển thị nội dung hướng dẫn.

UC16 không:

- tạo dữ liệu;
- cập nhật dữ liệu;
- xóa dữ liệu;
- thay đổi trạng thái nghiệp vụ.

### BR-UC16-03 - Nội dung hướng dẫn phải phù hợp với nghiệp vụ thực tế

Hướng dẫn đăng ký lưu trú phải phản ánh đúng quy trình của:

`UC02 - Đăng ký lưu trú`

Không được mô tả các bước hoặc chức năng không tồn tại trong hệ thống.

### BR-UC16-04 - Không yêu cầu dữ liệu cá nhân

Người dùng không cần cung cấp thông tin cá nhân chỉ để xem trang hướng dẫn.

### BR-UC16-05 - Không tạo đơn đăng ký

Việc xem hướng dẫn không đồng nghĩa với việc tạo đơn đăng ký lưu trú.

UC16 chỉ cung cấp thông tin hướng dẫn.

Việc tạo đơn được thực hiện tại:

`UC02 - Đăng ký lưu trú`

## 8. Dữ liệu liên quan

UC16 chủ yếu hiển thị nội dung hướng dẫn tĩnh trên Website.

Trong phạm vi thiết kế hiện tại:

- không yêu cầu bảng dữ liệu riêng cho hướng dẫn;
- không tạo bản ghi mới trong cơ sở dữ liệu;
- không cập nhật dữ liệu nghiệp vụ.

Nội dung hướng dẫn có thể được lưu trực tiếp trong mã nguồn hoặc giao diện của Website theo cách triển khai của hệ thống.

## 9. Quan hệ với Use Case khác

UC16 có liên quan trực tiếp đến:

`UC02 - Đăng ký lưu trú`

Luồng sử dụng có thể được hiểu như sau:

```text
Khách vãng lai
      |
      v
UC16 - Xem hướng dẫn
      |
      v
Xem các bước đăng ký lưu trú
      |
      v
Hiểu cách sử dụng UC02
      |
      v
UC02 - Đăng ký lưu trú