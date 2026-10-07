# AGENTS.md

## Project
Đây là đồ án 1: Hệ thống quản lý ký túc xá trường Đại học Kanto.

Stack chính:
- PHP
- MySQL
- PDO
- HTML/CSS/Bootstrap
- JavaScript
- WAMP
- Git/GitHub

## Nguyên tắc làm việc

- Luôn ưu tiên code và CSDL hiện tại trong repository.
- Không tự ý thay đổi nghiệp vụ đã chốt nếu chưa được yêu cầu.
- Không tự ý thêm chức năng ngoài phạm vi Use Case hiện tại.
- Khi sửa CSDL, phải kiểm tra ảnh hưởng đến Model, Controller, View, test và tài liệu liên quan.
- Giữ tên bảng, thuộc tính, trạng thái và vai trò nhất quán với CSDL thực tế.
- Không lưu mật khẩu dạng plaintext.
- Dùng prepared statement/PDO cho truy vấn có input người dùng.
- Không đưa thông tin bí mật, mật khẩu hoặc credential vào repository.

## Trước khi code

Với task nhỏ:
- Chỉ đọc những file liên quan trực tiếp đến task.

Với task lớn hoặc liên quan nhiều module:
- Đọc PROJECT_STATE.md trước.
- Sau đó đọc các file cần thiết cho task.
- Không cần quét toàn bộ repository nếu không cần.

## Kiểm thử

Sau khi sửa chức năng:
- Chạy các test liên quan.
- Không tuyên bố chức năng hoàn thành nếu test còn fail.
- Với đăng nhập/phân quyền, dùng:
  php -d xdebug.mode=off tests/run.php

## PROJECT_STATE.md

PROJECT_STATE.md là tài liệu bàn giao trạng thái hiện tại của dự án.

Hãy cập nhật PROJECT_STATE.md khi:
- hoàn thành một chức năng lớn;
- có thay đổi kiến trúc;
- thay đổi schema CSDL;
- thay đổi business rule;
- thay đổi route hoặc phân quyền;
- thêm hoặc sửa test quan trọng;
- context hiện tại trở nên dài hoặc sắp cần chuyển sang thread mới.

Nếu PROJECT_STATE.md khác với code hoặc CSDL hiện tại:
- ưu tiên code và CSDL;
- sau đó cập nhật lại PROJECT_STATE.md.

Không ghi:
- password;
- token;
- credential;
- thông tin nhạy cảm.

## Khi bắt đầu thread mới

Nếu task liên quan đến công việc trước đó:
1. Đọc PROJECT_STATE.md.
2. Kiểm tra code hiện tại.
3. Chỉ đọc thêm các file liên quan.
4. Tiếp tục từ mục "Công việc tiếp theo".