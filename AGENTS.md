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


## Quy trình làm việc với AI_TASK.md và AI_RESPONSE.md

- Đọc và thực hiện đúng yêu cầu trong `AI_TASK.md`.
- Sử dụng phong cách code cơ bản, tường minh, dễ hiểu, phù hợp với trình độ sinh viên đồ án; tránh viết tắt, nén dòng hoặc lạm dụng cú pháp nâng cao không cần thiết.
- Sau khi hoàn thành bất kỳ nhiệm vụ nào trong `AI_TASK.md`, **luôn cập nhật file `AI_RESPONSE.md`** để ghi nhận chi tiết những gì đã làm, kết quả test và thông tin bàn giao cho Codex hoặc các AI khác đọc và tiếp quản.

## Bối cảnh mở rộng (bắt buộc đọc khi làm task lớn)

| File | Nội dung | Khi nào đọc |
|---|---|---|
| `docs/PROJECT_CONTEXT.md` | Bối cảnh hợp nhất: môi trường, cấu trúc, trạng thái UC, schema CSDL, quy tắc nghiệp vụ, báo cáo tiến độ | Task lớn, task liên quan nhiều module, hoặc khi cần biết "đã làm gì rồi" |
| `docs/CODEX_HISTORY.md` | Lịch sử 6 phiên làm việc Codex (đã lọc secret) | Cần tra quyết định cũ / lý do một đoạn code được viết như hiện tại |

Thứ tự ưu tiên khi tài liệu mâu thuẫn:
**code + CSDL thực tế > PROJECT_STATE.md > docs/PROJECT_CONTEXT.md > docs/CODEX_HISTORY.md > docs/sources/**

## Môi trường chạy test (quan trọng)

- Dùng **PHP 8.0.30**: `F:\wamp64\bin\php\php8.0.30\php.exe`
- **KHÔNG dùng PHP 8.4** — `session_set_save_handler()` bị deprecate làm toàn bộ test fail (0/28), đây là lỗi harness chứ không phải lỗi nghiệp vụ.
- Lệnh:
  ```
  F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\run.php
  F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\approval.php
  F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\registration.php
  F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\rooms.php
  ```
- Baseline hiện tại: **113/113 đạt** (28 + 51 + 20 + 14).

## Môi trường cloud (Jules / Codex Cloud / Muse)

- Không có WAMP/MySQL local trên cloud → dùng `Dockerfile` có sẵn, hoặc chạy test không cần DB.
- Không có quyền truy cập `storage/` thật; không commit ảnh minh chứng.
- Mọi thay đổi phải mở **Pull Request**, **KHÔNG** push trực tiếp vào `main`.
- Không sửa `config/db.php`, `Dockerfile`, `.dockerignore`, `config/docker-*.conf` trừ khi task yêu cầu rõ.

## Cấu trúc thư mục nhanh

```
index.php            Router chính (action=login|register|logout|approval*)
config/db.php        PDO, ENV vars → fallback WAMP local
controllers/         AuthController, RegistrationController, ApprovalController
models/              User, Registration, Approval, StaffDashboard, StudentDashboard
middleware/          Auth.php (session), Role.php (phân quyền)
includes/            head, header, navbar, chatbot
views/               Auth/, registration/, approval/, student/, nvql/, ketoan/, quanly/
sql/                 qlktx_v2.sql, uc03-approval.sql, seed-buildings-rooms.sql
tests/               run.php, worker.php, approval.php, registration.php, rooms.php
docs/                PROJECT_CONTEXT.md, CODEX_HISTORY.md, 01-project-scope → 06-diagrams, sources/
```

## Phạm vi đang làm

- Branch hiện tại: `feature/uc03-approval` (UC03 đã code xong, 51 test đạt, **chưa merge `main`**).
- Yêu cầu mới chưa triển khai: **UC Gia hạn (UC18) do Timer đảm nhiệm** — xem mục 7 trong `docs/PROJECT_CONTEXT.md`, có 10 vướng mắc cần chốt trước khi code.
- Không động vào UC khác nếu không được yêu cầu.

## Cảnh báo tài liệu lệch nhau

Hai bộ tài liệu đánh số Use Case **khác nhau** (`docs/02-use-cases/` vs `docs/sources/BÁO CÁO BÀI TẬP LỚN...docx`).
Bảng đối chiếu ở mục 8 của `docs/PROJECT_CONTEXT.md`. Khi viết báo cáo phải đồng bộ lại.

Ngoài ra:
- `docs/02-use-cases/UC05.2-contract-termination.md` — **rỗng (0 KB)**
- `docs/03-business-rules/BUSINESS_RULES.md` — **rỗng (0 KB)**
- `docs/06-diagrams/sequence/README.md` — đánh số lệch 1 đơn vị so với `docs/02-use-cases/`
