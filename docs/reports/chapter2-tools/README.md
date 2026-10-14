# Công cụ tạo bản thảo Chương 2

## Bổ sung wireframe vào bản nhóm đã chốt

Nguồn mới nhất là `../chapter2/Chuong_2_Kanto_KTX_da_sua.docx`, do người dùng chỉnh sửa và chốt. `add-approved-wireframes.php` đọc trực tiếp bản này, chèn mục 2.7.3 trước 2.8 và nhúng 25 PNG gốc trong `docs/05-ui/wireframes/`. Kết quả là `Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx`; không ghi đè nguồn và không dựng lại từ Markdown cũ.

```powershell
& 'C:/wamp64/bin/php/php8.3.28/php.exe' -d xdebug.mode=off docs/reports/chapter2-tools/add-approved-wireframes.php
powershell -NoProfile -ExecutionPolicy Bypass -File docs/reports/chapter2-tools/export-word.ps1 -DraftName Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx
```

Script kiểm tra nội dung gốc ngoài mục bổ sung không đổi sau khi lưu, XML hợp lệ, đủ 32 hình và ảnh mới trùng byte với PNG nguồn. Manifest đối chiếu nằm trong `working/approved-wireframes-manifest.json` (không phải file nộp). `inspect-approved-docx.php` chỉ đọc cấu trúc phần 2.7/2.8 và chú thích hình; `preview-wireframes.php` tạo ảnh tổng hợp để rà soát nguồn.

Word đã mở được bản bổ sung: 60 trang, 32 hình; đã xem các trang đại diện. `export-word.ps1 -Preview -PreviewPages 39,40,41,47,58` có thể chọn trang xem trước khi gọi từ PowerShell. Không chạy công cụ dựng từ Markdown bên dưới để thay thế bản nhóm đã chốt.

## Công cụ của bản thảo trước khi nhóm chốt

Bản báo cáo nằm ở `../chapter2/`. File Markdown là nguồn nội dung; Word có văn bản và bảng chỉnh sửa được, cùng 11 hình PNG được nhúng. Công cụ chạy bằng PHP CLI, không cung cấp chức năng Web.

Mặc định hiện dựng/kiểm tra `BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx`. Đây là bản đã sửa actor UC06; file Word ban đầu đang được người dùng chỉnh sửa được giữ lại. Công cụ không đưa các chỉnh sửa thủ công của file Word ban đầu vào bản dựng từ Markdown. Tham số tên file cho phép chọn bản cần dựng/kiểm tra; không chọn ghi đè một tài liệu đang chỉnh sửa.

## Dựng lại Word và hình

```powershell
& 'C:/wamp64/bin/php/php8.3.28/php.exe' -d xdebug.mode=off docs/reports/chapter2-tools/build-chapter2.php
& 'C:/wamp64/bin/php/php8.3.28/php.exe' -d xdebug.mode=off docs/reports/chapter2-tools/validate-chapter2.php
```

Cần extension PHP GD, Zip, DOM và font Arial trên Windows. `schema-reference.json` chỉ lưu cấu trúc 17 bảng đã đối chiếu ngày 11/10/2026; không chứa bản ghi, mật khẩu, tên máy chủ hoặc thông tin kết nối. Dựng báo cáo không kết nối CSDL.

`collect-schema.php` đọc metadata local để đối chiếu lại khi schema thay đổi. Chỉ chạy khi chủ động muốn cập nhật nguồn cấu trúc; script kiểm tra tên database `qlktx_v2`, dùng SHOW TABLES/COLUMNS/CREATE và không ghi dữ liệu MySQL. File cấu hình DB không được sao chép vào báo cáo.

`extract-sources.php` trích văn bản từ DOCX/PDF trong `docs/sources/` để đọc hướng dẫn. Kết quả trung gian trong `working/` được bỏ qua bởi Git và không phải file nộp.

`export-word.ps1` dùng Microsoft Word COM ở chế độ ẩn để mở DOCX và kiểm tra phân trang; `-Preview` lưu hình xem trước và `-ExportPdf` bật xuất PDF. Bước này phụ thuộc cài đặt Word của máy. Trong phiên tạo bản thảo, Word mở và phân trang được nhưng xuất PDF không hoàn tất, nên file nộp là Word. Script chỉ đóng tài liệu và phiên Word nó tạo.

Định dạng mặc định: A4, Times New Roman 13, dòng 1,5; lề trái 3 cm, phải 2 cm, trên/dưới 2,5 cm. Khi ghép báo cáo tổng, nhóm cần chỉnh số trang và kiểm tra quy định trình bày của trường.
