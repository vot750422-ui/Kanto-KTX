# Kanto-KTX — Bối cảnh dự án (hợp nhất từ Codex)

> File này được tổng hợp tự động từ lịch sử làm việc của Codex trên máy
> (`~/.codex/memories/MEMORY.md`, 22 rollout summaries, ad-hoc notes)
> và từ việc đọc trực tiếp repository + CSDL local ngày 2026-11-01.
>
> Mục đích: để **bất kỳ agent nào** (Antigravity, Jules, Muse, Codex sau này)
> đọc file này là hiểu dự án như Codex đã hiểu, không cần quota Codex.
>
> Quy tắc: nếu file này khác code/CSDL thực tế → **ưu tiên code và CSDL**,
> sau đó cập nhật lại file này.

---

## 1. Thông tin dự án

| Mục | Giá trị |
|---|---|
| Tên đề tài | Hệ thống quản lý ký túc xá trường Đại học Kanto |
| Môn học | Đồ án 1 (DA1) — KTPM2026 |
| Sinh viên | Phan Minh Trọng (KTPM2411047), Hồ Hoàng Như Ý (KTPM2411004) |
| Khóa | 2024 — Kỹ thuật phần mềm, Khoa CNTT, ĐH Kỹ thuật - Công nghệ Cần Thơ |
| CBHD | Trịnh Thanh Duy (ttduy@ctuet.edu.vn) |
| Repo | https://github.com/vot750422-ui/Kanto-KTX |
| Repo local | `F:\Kanto-KTX` (bản WAMP cũ: `C:\wamp64\www\Kanto-KTX`) |
| Production | https://kanto-ktx.onrender.com (Render auto-deploy từ `main`) |
| DB production | Aiven MySQL, database `defaultdb` |
| DB local | WAMP MySQL 8.4.7, database `qlktx_v2` (user `root`, pass rỗng) |

**Stack:** PHP 8.x (PDO) · MySQL 8 · HTML/CSS/Bootstrap · JavaScript · Apache · Docker · Git/GitHub · Render · Aiven · DBeaver

---

## 2. Môi trường chạy & lệnh thường dùng

```powershell
# PHP dùng cho test (quan trọng: PHP 8.4 KHÔNG chạy được test suite)
F:\wamp64\bin\php\php8.0.30\php.exe

# Chạy toàn bộ test
cd F:\Kanto-KTX
F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\run.php          # 28 case
F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\approval.php     # 51 case
F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\registration.php # 20 case
F:\wamp64\bin\php\php8.0.30\php.exe -d xdebug.mode=off tests\rooms.php        # 14 case

# MySQL CLI
F:\wamp64\bin\mysql\mysql8.4.7\bin\mysql.exe -u root qlktx_v2
```

> ⚠️ **PHP 8.4.15 làm test fail 0/28** vì `session_set_save_handler()` với callback
> riêng đã bị deprecate → warning bắn ra trước `session_start()`. Đây là **vấn đề
> tương thích test harness**, không phải lỗi nghiệp vụ. Luôn dùng PHP 8.0.30 để test.

**Kết quả test hiện tại (2026-11-01):** 28/28 + 51/51 + 20/20 + 14/14 = **113/113 đạt**.

---

## 3. Cấu trúc thư mục

```
index.php                  # Router chính (action=login|register|logout|approval*)
config/db.php              # PDO: ENV vars → fallback local WAMP
controllers/               # AuthController, RegistrationController, ApprovalController
models/                    # User, Registration, Approval, StaffDashboard, StudentDashboard
middleware/                # Auth.php (session), Role.php (phân quyền)
includes/                  # head, header, navbar, chatbot
views/                     # Auth/, registration/, approval/, student/, nvql/, ketoan/, quanly/
sql/                       # qlktx_v2.sql (schema gốc), uc03-approval.sql, seed-buildings-rooms.sql
tests/                     # run.php, worker.php, approval.php, registration.php, rooms.php
docs/                      # 01-project-scope → 06-diagrams, sources/ (báo cáo gốc)
storage/                   # ảnh minh chứng đăng ký (giới hạn quyền đọc)
```

---

## 4. Trạng thái Use Case (đối chiếu docs/02-use-cases)

| UC | Tên | Trạng thái code | Ghi chú |
|---|---|---|---|
| UC01 | Đăng nhập | ✅ Hoàn thành | Session + role middleware + 28 test |
| UC02 | Đăng ký lưu trú | ✅ Hoàn thành | Giữ chỗ tạm, kiểm tra MSSV/CCCD |
| UC03 | Xét duyệt đơn | ✅ Code xong, **chưa merge main** | Branch `feature/uc03-approval`, 51 test |
| UC04.1 | Tra cứu sinh viên | ⬜ Chưa code | Chỉ có đặc tả |
| UC04.2 | Cập nhật sinh viên | ⬜ Chưa code | |
| UC05.1 | Tra cứu hợp đồng | ⬜ Chưa code | |
| UC05.2 | Thanh lý hợp đồng | ⬜ Chưa code | File đặc tả **rỗng (0 KB)** |
| UC06 | Ghi nhận chỉ số điện nước | ⬜ Chưa code | |
| UC07.1 | Tra cứu hóa đơn | ⬜ Chưa code | |
| UC07.2 | Xác nhận thanh toán | ⬜ Chưa code | |
| UC08 | Gửi phản ánh sự cố | ⬜ Chưa code | |
| UC09 | Xử lý phản ánh sự cố | ⬜ Chưa code | |
| UC10.1 | Lập hóa đơn tiền phòng (Timer) | ⬜ Chưa code | Logic đã có một phần trong `Approval::billingContext()` |
| UC10.2 | Lập hóa đơn điện nước (Timer) | ⬜ Chưa code | |
| UC11 | Xem thông tin lưu trú | ⬜ Chưa code | |
| UC12 | Xem thông báo | ⬜ Chưa code | Bảng `thongbao` đã có |
| UC13 | Trợ lý AI | 🟡 Có `includes/chatbot.php` | Chưa rõ mức hoàn thiện |
| UC14 | Thống kê báo cáo | ⬜ Chưa code | |
| UC15 | Xem thông tin phòng | ⬜ Chưa code | |
| UC16 | Xem hướng dẫn | ⬜ Chưa code | |
| **UC-18** | **Gia hạn (Timer)** | ⬜ **Chưa code** | ⭐ Yêu cầu mới, xem mục 7 |

> ⚠️ **Lệch mã UC giữa hai bộ tài liệu** — xem mục 8.

---

## 5. Quy tắc nghiệp vụ đã chốt (từ MEMORY.md + PROJECT_STATE.md)

### Đăng ký & xét duyệt
- Số chỗ khả dụng = `SucChua − số HopDong 'Còn hạn' − số DonDangKy 'Chờ duyệt' − số DonDangKy 'Đã duyệt' có MaHopDong NULL`
- Một đơn `Chờ duyệt` giữ đúng **1 chỗ**.
- Ưu tiên: UT02=4 > UT03=3 > UT01=2 > UT00=0; cùng mức → thời gian gửi cũ nhất → mã đơn.
- Duyệt: tạo `taikhoan` (TenDangNhap = MSSV, `password_hash('1')` demo), `sinhvien` (`Chờ thanh toán`), `hoadon` tiền phòng kỳ kế tiếp (`Chưa thu`). **Không tạo hợp đồng khi duyệt.**
- Từ chối: chỉ đơn `Chờ duyệt`, lý do ≤ 500 ký tự, giải phóng chỗ.
- Khóa: dùng named lock `kanto_uc02_<hash>`, `READ COMMITTED`, khóa phòng rồi đơn, rollback toàn bộ khi lỗi.

### Hợp đồng & lưu trú
- Cập nhật sinh viên **không** dùng để đổi phòng hoặc đổi trạng thái lưu trú.
- Thanh lý hợp đồng: phải kiểm tra trạng thái hợp đồng và **công nợ** trước.
- Số người trong phòng = số `HopDong` có `TrangThai = 'Còn hạn'`.

### Điện nước & hóa đơn
- Chỉ ghi chỉ số cho phòng **có sinh viên đang lưu trú**.
- Chỉ số mới **≥** chỉ số cũ; mỗi phòng **1 lần/tháng**.
- Hóa đơn chỉ có 2 trạng thái: `Chưa thu`, `Đã thu`.
- Mỗi sinh viên chỉ 1 hóa đơn tiền phòng / học kỳ (UNIQUE `uq_hdphong_sv_hocky`).

### Phân quyền
- 4 vai trò: `Sinh viên`, `Nhân viên quản lý KTX`, `Nhân viên kế toán`, `Quản lý KTX` + tác nhân hệ thống `Timer`.
- Quản lý KTX là người duy nhất có toàn quyền + quyền sửa bảng `dongia`.
- Không cho vượt quyền bằng sửa URL/request.

### An toàn dữ liệu
- Không xóa dữ liệu thật để test. Dùng DB test riêng cho test có ghi.
- Không đổi tên bảng/cột/vai trò/trạng thái tùy tiện.
- Không hard-code credential; không commit `.env`, password, token.

---

## 6. Schema CSDL thực tế (đã verify trên `qlktx_v2` ngày 2026-11-01)

**17 bảng:** `toanha`, `phong`, `dienuutien`, `dondangky`, `sinhvien`, `taikhoan`, `nhanvien`, `hopdong`, `hocky`, `dongia`, `hoadon`, `hoadontienphong`, `hoadondiennuoc`, `hoadondiennuocsinhvien`, `chisodiennuoc`, `phananhsuco`, `thongbao`

Các bảng then chốt:

```sql
hopdong(MaHopDong PK, MSSV, MaPhong, NgayBatDau, NgayKetThuc,
        TrangThai ENUM('Còn hạn','Hết hạn','Đã thanh lý') DEFAULT 'Còn hạn')

hocky(MaHocKy PK, TenHocKy, NamHoc, NgayBatDau, NgayKetThuc)

dongia(MaDonGia PK, LoaiDonGia ENUM('Tiền phòng','Điện','Nước'),
       GiaTri DECIMAL(12,2), DonViTinh, NgayApDung,
       UNIQUE(LoaiDonGia, NgayApDung))

hoadon(MaHoaDon PK, LoaiHoaDon ENUM('Tiền phòng','Điện nước'),
       TongTien DECIMAL(12,2), ThoiGianLap, 
       TrangThai ENUM('Chưa thu','Đã thu') DEFAULT 'Chưa thu', ThoiGianThanhToan)

hoadontienphong(MaHoaDon PK/FK, MSSV, MaPhong, MaHocKy, MaDonGia,
                UNIQUE(MSSV, MaHocKy))

taikhoan(MaTK PK AI, TenDangNhap UNIQUE, MatKhau, 
         VaiTro ENUM('Sinh viên','Nhân viên quản lý KTX','Nhân viên kế toán','Quản lý KTX'),
         TrangThai ENUM('Hoạt động','Khóa') DEFAULT 'Hoạt động')

sinhvien(MSSV PK, MaTK UNIQUE, MaUuTien, HoTen, NgaySinh, CCCD UNIQUE,
         GioiTinh, QueQuan, SDT, Email, Lop, NienKhoa,
         TrangThaiLuuTru ENUM('Đang ở','Đã rời đi','Chờ thanh toán'))

thongbao(MaThongBao PK, MSSV, MaHoaDon NULL, TieuDe, NoiDung, ThoiGianTao)
```

**Dữ liệu local hiện có:** 4 tài khoản, 200 phòng, 4 tòa, 6 học kỳ (2026-2028).
**Trống:** `sinhvien`, `hopdong`, `hoadon`, `hoadontienphong`, `dongia`, `thongbao`.
→ Muốn test nghiệp vụ qua UI phải nhập `dongia` và `sinhvien`/`hopdong` thật.

**Migration đã áp dụng local:** `sql/uc03-approval.sql` (thêm `dondangky.MaHoaDon`, `dondangky.MaHopDong` + 2 FK + UNIQUE). **Chưa áp dụng Aiven.**

---

## 7. ⭐ Yêu cầu mới: UC Gia hạn (UC-18) — Timer

### 7.1. Đặc tả gốc (từ `docs/sources/BÁO CÁO BÀI TẬP LỚN...docx`, mục 3.15)

- **Mã:** UC-18 · **Tên:** Gia hạn · **Tác nhân:** Timer (Hệ thống)
- **Mục đích:** Tự động duy trì quyền lưu trú cho sinh viên đã hoàn thành nghĩa vụ tài chính và tạm khóa quyền truy cập của sinh viên nợ phí.
- **Tiền điều kiện:**
  1. Ngày hiện tại là **ngày bắt đầu học kỳ mới**.
  2. Tồn tại hóa đơn tiền phòng trong CSDL (UC-20 Lập hóa đơn tiền phòng chạy thành công).
- **Hậu điều kiện:**
  1. Toàn bộ hợp đồng cũ chuyển `Hết hạn`.
  2. Khởi tạo hợp đồng mới cho sinh viên có hóa đơn tiền phòng kỳ vừa rồi `Đã thu`.
  3. `taikhoan.TrangThai` của sinh viên có hóa đơn `Chưa thu` → `Khóa`.

**Luồng chính (Bảng 24):**

| Bước | Hệ thống phản hồi |
|---|---|
| 1 | Timer kích hoạt "Gia hạn" |
| 2 | ① Quét `HopDong`, chuyển tất cả `Còn hạn` → `Hết hạn`<br>② Truy vấn `HoaDonTienPhong` của học kỳ hiện tại cho từng sinh viên<br>③ Nếu trạng thái hóa đơn `Đã thu`: khởi tạo `HopDong` mới, chép thông tin từ hợp đồng cũ, cập nhật học kỳ hiện tại, đặt `NgayBatDau`/`NgayKetThuc` theo `hocky`, trạng thái `Còn hạn`<br>④ Gửi thông báo hệ thống đến sinh viên gia hạn thành công |

**Luồng ngoại lệ:**
- **E1 — Lỗi hệ thống (mất kết nối CSDL):** ghi `NhatKyHeThong` (thời gian, tên chức năng, nội dung lỗi), gửi thông báo đến Quản lý KTX, **Rollback**.
- **E2 — Hóa đơn `Chưa thu`:** cập nhật `taikhoan.TrangThai = 'Khóa'`, gửi thông báo (email) cho sinh viên yêu cầu liên hệ nhân viên thanh toán và mở khóa.

**Quy tắc nghiệp vụ liên quan (Bảng 1 — Quy tắc ràng buộc):**
- *Khoá tài khoản:* khi Timer chạy Gia hạn xong, hóa đơn tiền phòng `Chưa thu` → trạng thái đăng nhập `Khóa`.
- *Điều kiện gia hạn:* chỉ gia hạn sinh viên có hóa đơn tiền phòng `Đã thu`.

### 7.2. ⚠️ Các vướng mắc triển khai thực tế (đã phân tích trên schema hiện tại)

| # | Vướng mắc | Chi tiết | Hướng xử lý đề xuất |
|---|---|---|---|
| **V1** | **"Học kỳ hiện tại" bị mơ hồ** | Đặc tả nói "truy vấn HoaDonTienPhong của học kỳ hiện tại", nhưng tại ngày bắt đầu kỳ mới, `hocky` có `NgayBatDau = today` chính là kỳ mới. Hóa đơn `Đã thu` lại thuộc kỳ **vừa kết thúc**. | Dùng chính `billingContext()` đã có trong `Approval.php` để xác định kỳ; hóa đơn cần kiểm tra là kỳ **liền trước** kỳ bắt đầu hôm nay. Cần chốt lại với GVHD. |
| **V2** | **Không có cột liên kết hợp đồng ↔ học kỳ** | `hopdong` chỉ có `NgayBatDau`, `NgayKetThuc`, không có `MaHocKy`. Đặc tả nói "cập nhật học kỳ là học kỳ hiện tại" → không có chỗ ghi. | Hoặc (a) suy ra kỳ từ ngày, hoặc (b) thêm cột `MaHocKy` vào `hopdong` (migration mới). |
| **V3** | **`MaHopDong` varchar(10) — nguy cơ tràn** | Sinh mã kiểu `HD` + timestamp sẽ vượt 10 ký tự. | Dùng `HD` + 8 ký tự base36 tăng dần, hoặc nới cột lên varchar(20). |
| **V4** | **Bảng `NhatKyHeThong` chưa tồn tại** | E1 yêu cầu ghi log lỗi vào bảng này. | Tạo migration `sql/uc18-renewal.sql` thêm bảng `nhatkyhethong`. |
| **V5** | **Gửi email (E2) chưa có hạ tầng** | Đặc tả nói "gửi thông báo qua gmail". Repo chưa có mailer. | Dùng bảng `thongbao` trước (in-app); email để giai đoạn sau hoặc dùng Brevo API (đã có branch `feature/uc03-brevo-api`). |
| **V6** | **Hợp đồng "Hết hạn" hàng loạt phá vỡ UC02/UC03** | Nếu chạy Gia hạn mà không có hóa đơn `Đã thu`, toàn bộ hợp đồng thành `Hết hạn` → số chỗ khả dụng nhảy vọt, đơn `Đã duyệt` cũ có thể bị đếm sai. | Phải chạy trong **1 transaction duy nhất**, khóa `hopdong` + `hoadontienphong` + `taikhoan`, và kiểm tra `dondangky.MaHopDong` trước khi đổi trạng thái. |
| **V7** | **Chạy 2 lần trong cùng ngày** | Timer có thể trigger lặp → tạo hợp đồng trùng. | Idempotency: kiểm tra đã có `hopdong` với `NgayBatDau = today` cho MSSV đó chưa; hoặc dùng bảng `tiendo_timer` ghi lần chạy cuối. |
| **V8** | **Sinh viên `Chờ thanh toán` (từ UC03) có bị khóa không?** | UC03 tạo `sinhvien.TrangThaiLuuTru = 'Chờ thanh toán'` + hóa đơn `Chưa thu`. Nếu Gia hạn chạy trước khi họ thanh toán → tài khoản bị khóa ngay, không đăng nhập được để xem hóa đơn. | Cần chốt: miễn trừ sinh viên mới (chưa từng có hợp đồng) khỏi bước khóa, hoặc cho phép đăng nhập read-only để thanh toán. |
| **V9** | **Không có cơ chế Timer thật** | Repo chưa có cron/scheduler. Render free tier không chạy cron nội bộ. | Dùng **Render Cron Job** (gọi 1 endpoint có token bí mật) hoặc **GitHub Actions scheduled workflow**. |
| **V10** | **Đồng bộ tài liệu** | Chưa có file `docs/02-use-cases/UC18-renewal.md`, chưa có sequence diagram, chưa có trong `USE_CASE_OVERVIEW.md`. | Viết đặc tả UC18 theo cùng format UC10.1, thêm vào overview, vẽ sequence diagram. |

### 7.3. Đề xuất thứ tự triển khai UC18

1. **Chốt nghiệp vụ** (V1, V2, V8) với GVHD trước khi code.
2. Viết `docs/02-use-cases/UC18-renewal.md` (đặc tả chuẩn hóa).
3. Migration `sql/uc18-renewal.sql`: bảng `nhatkyhethong`, (tùy chọn) cột `hopdong.MaHocKy`.
4. Model `models/Renewal.php` — 1 method `run(): array` chạy toàn bộ trong transaction.
5. Endpoint `index.php?action=renewal-run` — chỉ chấp nhận POST + token bí mật (không cho user thường).
6. Test `tests/renewal.php` — tối thiểu 8 case: đã thu → gia hạn; chưa thu → khóa; chạy 2 lần; rollback khi lỗi; không có hóa đơn; học kỳ chồng lấn; không có đơn giá; hợp đồng đã thanh lý.
7. Cấu hình Render Cron Job.
8. Cập nhật `PROJECT_STATE.md`, `USE_CASE_OVERVIEW.md`, ERD, báo cáo.

---

## 8. ⚠️ Lệch mã Use Case giữa hai bộ tài liệu

Hai bộ tài liệu dùng **cách đánh số khác nhau** — phải chốt trước khi viết báo cáo:

| Nội dung | `docs/02-use-cases/` (bộ mới) | `docs/sources/BÁO CÁO BÀI TẬP LỚN` (bộ cũ) |
|---|---|---|
| Đăng nhập | UC01 | UC-01 |
| Đăng ký lưu trú | UC02 | UC-02 |
| Xét duyệt đơn | UC03 | UC-03 |
| Quản lý thông tin sinh viên | UC04 | UC-04 |
| Quản lý hợp đồng | UC05 | UC-05 |
| Xem thông tin lưu trú | UC06 (docs) / UC11 (sequence) | UC-06 |
| Ghi nhận chỉ số điện nước | UC06 (docs) / UC07 (sequence) | UC-07 |
| **Lập hóa đơn điện nước** | **UC08** | **UC-08** ✅ khớp |
| Quản lý hóa đơn | UC09 | UC-09 |
| Gửi phản ánh sự cố | UC10 | UC-10 |
| Xử lý phản ánh | UC11 | UC-11 |
| Xem thông báo | UC12 | UC-12 |
| Trợ lý AI | UC13 | UC-13 |
| Thống kê báo cáo | UC14 | UC-14 |
| Xem thông tin phòng | UC15 | UC-15 |
| Xem hướng dẫn | UC16 | UC-16 |
| Lập hóa đơn tiền phòng | UC10.1 | UC-20 |
| Lập hóa đơn điện nước | UC10.2 | UC-08 |
| **Gia hạn** | **UC18** | UC-21 (chỉ tham khảo) |

**Lỗi cụ thể phát hiện được:**
- `docs/06-diagrams/sequence/README.md` đánh số **lệch 1 đơn vị** so với `docs/02-use-cases/` — ví dụ file `UC06-utility-meter-sequence.png` nhưng `02-use-cases` gọi là UC06 = Xem thông tin lưu trú.
- `docs/02-use-cases/UC05.2-contract-termination.md` và `docs/03-business-rules/BUSINESS_RULES.md` đều **rỗng (0 KB)**.
- File `docs/sources/readme.md` ghi rõ: *"Không được mặc định rằng nghiệp vụ, CSDL, Use Case hoặc thiết kế trong tài liệu này giống với hệ thống Kanto-KTX hiện tại."*

> **Khuyến nghị:** lấy `docs/02-use-cases/` làm chuẩn cho code, và khi viết báo cáo thì **đồng bộ lại toàn bộ số UC** theo một bảng duy nhất. Đây là việc phải làm trước khi nộp Chương 2/3.

---

## 9. Báo cáo tiến độ — quy định & mẫu

### 9.1. Quy định (từ `docs/sources/DA1 KTPM2026-Hướng dẫn chung.pdf`)

- **Nộp qua email** `ttduy@ctuet.edu.vn`, cc các thành viên còn lại, **trước 20h00 Chủ nhật hàng tuần**.
- Các lần sau chỉ cần **reply cùng 1 luồng mail**.
- Mẫu: **BM 1.8.a** (Phụ lục I).
- Riêng **Tuần 6**: báo cáo trực tiếp tại Khoa CNTT.
- Từ tuần 4: định kỳ **2 tuần/lần** hoặc khi cần hỗ trợ.

### 9.2. Lộ trình 10 tuần (bảng gốc)

| Tuần | Đầu công việc | Sản phẩm cần đạt |
|---|---|---|
| 1–2 | Thống nhất đề tài, khảo sát, phân tích yêu cầu, nghiên cứu công nghệ | Đề cương, phân công, Use Case + mô tả, bản thảo Chương 1 |
| 3 | Phân tích & thiết kế hệ thống, thiết kế CSDL | Class Diagram, Sequence Diagram, ERD chi tiết |
| 4 | Thiết kế UI/UX, khởi tạo khung dự án + CSDL | Wireframe/Mockup, khung phần mềm & CSDL |
| 5 | Lập trình Module 1; soạn Chương 2 | Demo Module 1; bản thảo Chương 2 |
| **6** | **Báo cáo giữa kỳ trực tiếp** — demo ≥ 50% | Slide + demo ≥ 50% chức năng |
| 7 | Lập trình Module 2, tích hợp; soạn Chương 3 | Phần mềm ≥ 80%; bản thảo Chương 3 |
| **8** | **Sửa lỗi và tối ưu hóa hệ thống** | Phần mềm hoạt động ổn định |
| 9 | Hoàn thiện báo cáo (Chương 4, KL, Phụ lục, TLTK); đóng gói | File báo cáo hoàn chỉnh + Source code |
| 10 | Rà soát cuối, nộp sản phẩm chính thức | Báo cáo chính thức + sản phẩm đóng gói |

### 9.3. Mẫu BM 1.8.a (dùng lại cho Tuần 7 & 8)

Cấu trúc báo cáo đã nộp Tuần 1–2 (`Báo cáo tiến độ - Tuần 1,2.pdf`):

```
BÁO CÁO TIẾN ĐỘ THỰC HIỆN (BM 1.8.a)
Đợt 1 năm học 2026-2027 | Ngành KTPM | Khoa CNTT
Họ và tên: Phan Minh Trọng, Hồ Hoàng Như Ý
MSSV: KTPM2411047, KTPM2411004 | Khóa 2024
Tên đề tài: "Hệ thống quản lý ký túc xá trường Đại học Kanto"
Thời gian thực hiện: Tuần X–Y, từ ngày ... đến ngày ...
Cán bộ hướng dẫn: Trịnh Thanh Duy
Lần báo cáo thứ: N

1. Công việc đã thực hiện
   | Thời gian | Công việc thực hiện | Kết quả đạt được |
2. Kế hoạch công việc tiếp theo
   | Thời gian | Dự kiến công việc | Dự kiến kết quả |
3. Đề xuất và kiến nghị (nếu có)

Cần Thơ, ngày ... tháng ... năm 2026
```

---

## 10. Việc ưu tiên tiếp theo

1. **Báo cáo tiến độ Tuần 7 + Tuần 8** (deadline Chủ nhật 2026-11-01 20:00) — xem mục 9.
2. **Đồng bộ số UC** giữa docs và báo cáo (mục 8).
3. **Chốt nghiệp vụ UC18 Gia hạn** (mục 7.2 — V1, V2, V8).
4. Push 64 commit local lên `origin/main` trước khi giao agent cloud.
5. Merge `feature/uc03-approval` vào `main` sau review.

---

## 11. Quy tắc làm việc (kế thừa từ Codex Tech Lead protocol)

Từ `~/.codex/memories/extensions/ad_hoc/notes/20261027-175055-tech-lead-thefourthfloor-kanto.md`:

- Codex/Antigravity/Jules mặc định là **Tech Lead / Reviewer**, không tự sửa code trừ khi được yêu cầu rõ.
- Trả lời bằng **tiếng Việt**, trừ khi được yêu cầu khác.
- Khi có thắc mắc: đưa ra **các lựa chọn ngắn** + 1 lựa chọn tự nhập.
- Kết thúc công việc: hỏi **"Kết thúc công việc" / "Điều chỉnh / làm tiếp"**.
- Kết luận review chỉ dùng 3 trạng thái: **ĐẠT**, **CẦN SỬA**, **CHƯA ĐỦ BẰNG CHỨNG**.
- Tiết kiệm quota: không đọc toàn repo, không chạy full test suite, không viết báo cáo dài khi không cần.
- **Không** coi kế hoạch là công việc đã hoàn thành; **không** coi báo cáo agent là bằng chứng tuyệt đối.
