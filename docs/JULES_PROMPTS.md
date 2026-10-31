# PROMPT DÁN VÀO JULES (jules.google.com)

## Cách dùng

1. Mở `https://jules.google.com`
2. Chọn repo `vot750422-ui/Kanto-KTX`
3. Chọn nhánh **`feature/uc18-renewal`** (đã tạo sẵn, đã đồng bộ `main`)
4. Dán **Prompt A** → Approve plan → để chạy
5. Sau khi A xong, tạo session mới cùng nhánh → dán **Prompt B** → Approve plan

---

## Prompt A — Rà soát đồng bộ báo cáo + tách Chương 3

```
Đọc các file sau theo thứ tự:
1. AGENTS.md
2. docs/JULES_TASK_REPORT_SYNC.md   ← nhiệm vụ chính của bạn
3. docs/02-use-cases/USE_CASE_OVERVIEW.md
4. docs/PROJECT_CONTEXT.md

Thực hiện đúng 6 bước trong docs/JULES_TASK_REPORT_SYNC.md:

Bước 1: Tạo docs/reports/RA_SOAT_DONG_BO_C1_C2_C3.md với bảng đối chiếu
        Lời mở đầu / Chương 1 / Chương 2 / Chương 3.
Bước 2: Sửa các chỗ lệch (đặc biệt: mọi "UC21" phải thành "UC18";
        số liệu kiểm thử phải là 222/222; số bảng CSDL là 20).
Bước 3: Viết lại Chương 3 theo văn phong sinh viên — câu ngắn,
        dùng "nhóm em", bỏ từ học thuật nặng, giữ nguyên số liệu và cấu trúc 3.1–3.10.
Bước 4: Tách Chương 3 ra file riêng docs/reports/chapter3/BAN_THAO_CHUONG_3.md
        (chỉ chứa Chương 3, có dòng ghi chú "Bản thảo Chương 3 — gửi riêng...").
Bước 5: Xuất .docx bằng: python tools/md_to_docx.py "<file.md>" "<file.docx>"
Bước 6: Cập nhật AI_RESPONSE.md.

Ràng buộc:
- Không sửa docs/reports/complete/*.docx trực tiếp — tạo bản mới hậu tố _DONG_BO.
- Không tự thêm chức năng chưa code vào Chương 1/2.
- Không đổi cấu trúc 10 mục của Chương 3.
- Nếu gặp mâu thuẫn không tự quyết được thì ghi vào PR, không tự chọn.

Khi xong: mở Pull Request vào main, mô tả rõ những gì đã sửa.
```

---

## Prompt B — Triển khai UC18 Gia hạn (chạy sau hoặc song song)

```
Đọc các file sau theo thứ tự:
1. AGENTS.md
2. docs/PROJECT_CONTEXT.md mục 7
3. docs/JULES_TASK_UC18.md   ← nhiệm vụ chính của bạn
4. docs/02-use-cases/UC18-renewal.md

Thực hiện đúng 5 bước trong docs/JULES_TASK_UC18.md:
Bước 1: sql/uc18-renewal.sql (bảng nhatkyhethong + cột hopdong.MaHocKy)
Bước 2: models/Renewal.php (1 transaction, GET_LOCK, chống chạy 2 lần)
Bước 3: controllers/RenewalController.php + route action=renewal-run (chỉ POST + token)
Bước 4: tests/renewal.php (tối thiểu 8 case, dùng CREATE TEMPORARY TABLE)
Bước 5: Cập nhật tài liệu

QUYẾT ĐỊNH NGHIỆP VỤ ĐÃ CHỐT (mục 4 của file task) — KHÔNG hỏi lại:
1. Kích hoạt theo ngày hiện tại của học kỳ hiện tại
   (hocky có NgayBatDau <= CURDATE() <= NgayKetThuc).
2. Sinh viên "Chờ thanh toán" ĐƯỢC đăng nhập để thanh toán, KHÔNG khóa.
   Chỉ khóa sinh viên đã từng có hợp đồng mà hóa đơn kỳ trước vẫn Chưa thu.
3. Mã hợp đồng: kiểm tra độ dài trước khi sinh mã, vượt thì ghi log và dừng.
   Không đổi kiểu cột trong task này.

Ràng buộc:
- Chạy test bằng PHP 8.0.30: F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\<file>.php
- 4 bộ test cũ phải vẫn xanh: run.php 106, approval.php 70, registration.php 30, rooms.php 16.
- Không sửa config/db.php, config/connection.php, Dockerfile.
- Mọi thay đổi CSDL phải idempotent (CREATE TABLE IF NOT EXISTS).

Khi xong: mở Pull Request vào main, ghi rõ kết quả test.
```

---

## Kiểm tra sau khi Jules chạy

Mở máy, chạy các lệnh sau để xác nhận:

```powershell
cd F:\Kanto-KTX
git fetch origin
git log --oneline origin/feature/uc18-renewal -5

# Xem Jules đã làm gì
gh pr list --state all

# Chạy test
F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\run.php
F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\renewal.php
```

Kiểm tra file Chương 3 riêng:
`docs/reports/chapter3/BAN_THAO_CHUONG_3.docx` — mở bằng Word xem có đúng chỉ Chương 3 không.
