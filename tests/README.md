# Kiểm thử đăng nhập và phân quyền

Chạy từ thư mục dự án bằng PowerShell, sau khi bật MySQL trong WAMP:

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -d xdebug.mode=off tests/run.php
```

Nếu PHP đã có trong PATH: `php tests/run.php`.

Bộ kiểm thử dùng CSDL trong `config/db.php`, cần ít nhất một tài khoản hoạt động cho mỗi vai trò. Các dòng PASS/FAIL giải thích từng tình huống; mã thoát là 0 khi tất cả đạt, 1 khi có lỗi.

Không thêm, sửa hoặc xóa dữ liệu. Session kiểm thử nằm trong RAM và tách khỏi session trình duyệt. Các file kiểm thử chỉ chạy qua CLI.

Khi demo, chạy bộ kiểm thử rồi đăng nhập trên trình duyệt lần lượt với bốn tài khoản mẫu. Thử mở dashboard của vai trò khác để thấy HTTP 403, và mở dashboard trong cửa sổ riêng tư để thấy chuyển về trang đăng nhập.

Giới hạn: chưa tự động kiểm thử đăng nhập thành công bằng mật khẩu thật, tài khoản bị khóa, cookie hoặc giao diện trình duyệt. Chuỗi mật khẩu băm được nhận diện không chứng minh mật khẩu demo nhập vào là đúng.
