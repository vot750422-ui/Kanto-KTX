# NHIỆM VỤ CHO JULES — UC18 Gia hạn lưu trú (Timer)

> **Dành cho:** Jules (cloud agent) — đọc kèm `AGENTS.md` và `docs/PROJECT_CONTEXT.md` mục 7.
> **Nhánh làm việc:** `feature/uc18-renewal` (tạo từ `main`). **Không** push trực tiếp vào `main`.
> **Ngày giao:** 01/11/2026.

---

## 0. Bối cảnh

Hệ thống Kanto-KTX (PHP thuần + MySQL, không framework) đã hoàn thành 17 use case (UC01–UC17).
Nhiệm vụ này triển khai **UC18 — Gia hạn lưu trú**, do **Timer (hệ thống)** đảm nhiệm, tự động:

1. Chuyển hợp đồng `Còn hạn` → `Hết hạn`.
2. Với sinh viên **đã thanh toán** hóa đơn tiền phòng: tạo hợp đồng mới cho học kỳ hiện tại, trạng thái `Còn hạn`.
3. Với sinh viên **chưa thanh toán**: khóa tài khoản đăng nhập.
4. Gửi thông báo hệ thống cho sinh viên được gia hạn.

Đặc tả đầy đủ: [`docs/02-use-cases/UC18-renewal.md`](../02-use-cases/UC18-renewal.md).
Danh sách 10 vướng mắc + đề xuất: [`docs/PROJECT_CONTEXT.md`](../PROJECT_CONTEXT.md) mục 7.2.

---

## 1. Quy tắc bắt buộc

- **Môi trường test:** PHP 8.0.30 — `F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\<file>.php`
  Trên cloud không có WAMP: dùng `Dockerfile` có sẵn hoặc chạy test không cần DB.
- **Không sửa:** `config/db.php`, `config/connection.php`, `Dockerfile`, `config/docker-*.conf` trừ khi task yêu cầu.
- **Không commit:** secret, mật khẩu, file `storage/backup_*.sql`, ảnh minh chứng.
- **Code style:** tường minh, dễ đọc, phù hợp trình độ sinh viên đồ án. Không viết tắt, không nén dòng.
- **Test hiện tại phải xanh:** `tests/run.php` 106/106, `tests/approval.php` 70, `tests/registration.php` 30, `tests/rooms.php` 16.
- **Mọi thay đổi CSDL phải có file migration** trong `sql/` và idempotent (`CREATE TABLE IF NOT EXISTS`).

---

## 2. Việc cần làm (theo thứ tự)

### Bước 1 — Migration `sql/uc18-renewal.sql`

```sql
-- Bảng nhật ký hệ thống (phục vụ luồng ngoại lệ E1 của UC18)
CREATE TABLE IF NOT EXISTS nhatkyhethong (...);

-- Cột liên kết học kỳ cho hợp đồng (tùy chọn nhưng khuyến nghị)
ALTER TABLE hopdong ADD COLUMN MaHocKy varchar(10) NULL;
```

Yêu cầu:
- Idempotent, chạy lại nhiều lần không lỗi.
- Có `FOREIGN KEY` tới `hocky(MaHocKy)` nếu thêm cột.
- Ghi chú rõ trong comment đầu file.

### Bước 2 — Model `models/Renewal.php`

Phương thức chính: `run(): array` trả về thống kê `['renewed' => int, 'locked' => int, 'skipped' => int, 'errors' => int]`.

Yêu cầu kỹ thuật:
- **Toàn bộ trong 1 transaction** (`beginTransaction` / `commit` / `rollBack`).
- Dùng `SELECT ... FOR UPDATE` khóa `hopdong`, `hoadontienphong`, `taikhoan` để tránh race condition.
- **Chống chạy 2 lần:** dùng `GET_LOCK('kanto_uc18_renewal', 0)` giống pattern trong `models/Approval.php` (xem cách UC03 làm).
- **Không đổi trạng thái** hợp đồng đã `Đã thanh lý`.
- **Miễn trừ sinh viên mới** (`TrangThaiLuuTru = 'Chờ thanh toán'` và chưa từng có hợp đồng) khỏi bước khóa tài khoản — đây là quyết định đã chốt trong báo cáo tuần 8, mục 3.2.
- Mọi lỗi ghi vào `nhatkyhethong` rồi `rollBack`, không để dữ liệu nửa vời.

### Bước 3 — Controller `controllers/RenewalController.php` + route

- Endpoint **chỉ chấp nhận POST**, yêu cầu header/token bí mật `RENEWAL_TOKEN` (đọc từ `getenv()`).
- Sai token → `403`, không tiết lộ thông tin.
- Route trong `index.php`: `action=renewal-run`.
- Trả JSON: `{"renewed":n,"locked":n,"skipped":n,"errors":n}`.

### Bước 4 — Test `tests/renewal.php`

Tối thiểu **8 trường hợp** (dùng `CREATE TEMPORARY TABLE` pattern giống `tests/approval.php` để không đụng dữ liệu thật):

1. Sinh viên đã thanh toán → hợp đồng mới `Còn hạn`, hợp đồng cũ `Hết hạn`.
2. Sinh viên chưa thanh toán → tài khoản `Khóa`, không tạo hợp đồng mới.
3. Sinh viên mới (`Chờ thanh toán`, chưa có hợp đồng) → **không bị khóa**.
4. Chạy 2 lần liên tiếp → lần 2 không tạo hợp đồng trùng.
5. Lỗi giữa chừng → rollback toàn bộ.
6. Không có hóa đơn tiền phòng kỳ trước → bỏ qua (`skipped`).
7. Học kỳ chồng lấn → từ chối, ghi log.
8. Hợp đồng đã thanh lý → không đổi trạng thái.

### Bước 5 — Cập nhật tài liệu

- `docs/02-use-cases/UC18-renewal.md`: đổi trạng thái `⬜ Chưa code` → `✅ Đã code`, ghi kết quả test.
- `docs/PROJECT_CONTEXT.md` mục 7: đánh dấu hoàn thành.
- `PROJECT_STATE.md`: thêm mục UC18.
- `AI_RESPONSE.md`: ghi chi tiết đã làm + kết quả test (theo quy định trong `AGENTS.md`).

---

## 3. Định nghĩa hoàn thành (Definition of Done)

- [ ] `sql/uc18-renewal.sql` chạy được trên DB trống và trên DB đã có dữ liệu.
- [ ] `models/Renewal.php` + `controllers/RenewalController.php` + route hoạt động.
- [ ] `tests/renewal.php` ≥ 8 case, **100% đạt**.
- [ ] 4 bộ test cũ vẫn xanh (106 + 70 + 30 + 16).
- [ ] Tài liệu UC18 + PROJECT_CONTEXT + PROJECT_STATE + AI_RESPONSE đã cập nhật.
- [ ] Mở Pull Request vào `main`, mô tả rõ thay đổi và kết quả test.

---

## 4. Câu hỏi cần chốt với giảng viên (KHÔNG tự quyết)

Nếu gặp các điểm sau, **dừng lại và ghi chú vào PR**, không tự chọn phương án:

1. **Thời điểm kích hoạt:** "ngày hiện tại là ngày bắt đầu học kỳ mới" — căn cứ là kỳ **liền trước** hay kỳ **hiện tại**?
2. **Sinh viên `Chờ thanh toán`:** miễn khóa hoàn toàn, hay cho đăng nhập read-only để thanh toán?
3. **Mã hợp đồng:** có nguy cơ tràn nếu format cố định độ dài — cần xác nhận format mới.

---

## 5. Ghi chú môi trường

- DB local: `qlktx_v2`, MySQL 8.4.7, user `root`, mật khẩu rỗng.
- Sau khi import export từ máy khác, **phải chạy** `sql/fk-restore-after-import.sql` và `sql/convert-innodb.sql` (export cũ xuất MyISAM, làm transaction không rollback được).
- Trên cloud: dùng `Dockerfile`, biến môi trường `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.
