# NHIỆM VỤ SỐ 2 CHO JULES — Rà soát đồng bộ Chương 1–2–3 và tách Chương 3

> **Dành cho:** Jules (cloud agent).
> **Nhánh làm việc:** `feature/uc18-renewal` (đã tạo sẵn, đã đồng bộ `main`).
> **Ưu tiên:** nhiệm vụ này chạy **trước** hoặc **song song** với `docs/JULES_TASK_UC18.md` — không phụ thuộc nhau.
> **Ngày giao:** 01/11/2026.

---

## 0. Mục tiêu

Sinh viên phải gửi riêng **Chương 3** cho giảng viên làm minh chứng, nên cần:

1. **Rà soát đồng bộ** giữa Lời mở đầu + Chương 1 + Chương 2 + Chương 3 (không mâu thuẫn số liệu, tên chức năng, mã Use Case, công nghệ, phạm vi).
2. **Tách Chương 3 ra file riêng** `BAN_THAO_CHUONG_3.docx` để gửi riêng.
3. **Viết lại văn phong** cho tự nhiên, đúng giọng sinh viên — không học thuật hóa.

---

## 1. File nguồn cần đọc

| File | Vai trò |
|---|---|
| `docs/reports/complete/BAO_CAO_DO_AN_1_KANTO_KTX_HOAN_THIEN.docx` | Bản báo cáo đầy đủ (Lời mở đầu + Chương 1 + Chương 2 + Chương 3) — **nguồn chuẩn** |
| `docs/reports/chapter2/BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx` | Chương 2 đã đồng bộ |
| `docs/reports/chapter3/BAN_THAO_CHUONG_3.md` | Chương 3 bản nháp (179 dòng) |
| `docs/reports/chapter3/CHUONG_3_KET_QUA_VA_THAO_LUAN.md` | Bản Chương 3 dài hơn (207 dòng) — đối chiếu, chọn nội dung tốt hơn |
| `docs/02-use-cases/USE_CASE_OVERVIEW.md` | Danh sách UC chuẩn (UC01–UC18) |
| `docs/PROJECT_CONTEXT.md` | Bối cảnh, schema, trạng thái UC |
| `docs/07-progress-reports/Tuan-07-bao-cao-tien-do.md`, `Tuan-08-...md` | Số liệu kiểm thử mới nhất |

---

## 2. Việc cần làm

### Bước 1 — Lập bảng đối chiếu 4 phần

Tạo file `docs/reports/RA_SOAT_DONG_BO_C1_C2_C3.md` gồm bảng đối chiếu các mục sau giữa Lời mở đầu / Chương 1 / Chương 2 / Chương 3:

| Hạng mục cần khớp | Ghi chú |
|---|---|
| Số lượng Use Case | Phải là **18 UC** (UC01–UC18), không còn UC21 |
| Tên đề tài | "Hệ thống quản lý ký túc xá trường Đại học Kanto" |
| Công nghệ sử dụng | PHP thuần + MySQL + Apache (WAMP), không nói Bootstrap nếu code không dùng |
| Phạm vi chức năng | Danh sách nhóm chức năng phải giống nhau |
| Số liệu kiểm thử | 222/222 (106 + 70 + 30 + 16) — khớp với báo cáo tuần 8 |
| Số bảng CSDL | 20 bảng (đếm từ `sql/qlktx_v2.sql`) |
| Tác nhân | 5 tác nhân + Timer |
| Mục tiêu đề tài | Khớp giữa Lời mở đầu và Chương 1 |

Ghi rõ từng chỗ **lệch** và **cách sửa**.

### Bước 2 — Sửa đồng bộ

- Sửa trực tiếp trong file nguồn (`.md` nếu có, hoặc ghi rõ hướng dẫn sửa cho `.docx`).
- Không tự ý thêm chức năng chưa code vào Chương 1/2.
- Nếu Chương 1/2 hứa hẹn chức năng mà code chưa có → ghi vào mục "hạn chế" của Chương 3 thay vì xóa.

### Bước 3 — Viết lại Chương 3 theo văn phong sinh viên

**Quy tắc văn phong (bắt buộc):**

- Câu ngắn, mỗi câu một ý. Tránh câu ghép dài 3–4 mệnh đề.
- Dùng "nhóm em" hoặc "nhóm" khi nói về công việc — không dùng "tác giả", "nghiên cứu sinh".
- Tránh từ ngữ quá trang trọng/học thuật: "kiểm chứng", "luận cứ", "phương pháp luận", "tối ưu hóa triệt để", "đảm bảo tính toàn vẹn tuyệt đối".
- Thay bằng cách nói đơn giản: "chạy thử", "kiểm tra", "sửa lỗi", "so sánh kết quả".
- Khi nêu hạn chế, nói thẳng và ngắn: "Phần này nhóm em mới chạy trên máy cá nhân, chưa thử trên server thật."
- Không dùng bullet quá dài. Mỗi bullet tối đa 2 dòng.
- Không dùng thuật ngữ tiếng Anh khi có từ tiếng Việt tương đương (trừ tên công nghệ: PHP, MySQL, WAMP, Docker, GitHub).
- Giữ nguyên số liệu, bảng biểu, mã UC — chỉ đổi cách diễn đạt.

**Cấu trúc Chương 3 giữ nguyên 10 mục hiện có** (3.1 → 3.10), chỉ viết lại lời văn.

### Bước 4 — Tách file riêng

Tạo `docs/reports/chapter3/BAN_THAO_CHUONG_3.docx` (và `.md` tương ứng) **chỉ chứa Chương 3**, đảm bảo:

- Có tiêu đề "CHƯƠNG 3. KẾT QUẢ VÀ THẢO LUẬN" ở đầu.
- Đánh số mục bắt đầu từ 3.1 (không đổi sang 1.1).
- Không có Lời mở đầu hay Chương 1/2 lẫn vào.
- Có dòng ghi chú đầu file: *"Bản thảo Chương 3 — gửi riêng để lấy ý kiến giảng viên hướng dẫn."*
- Đánh số bảng/hình theo Chương 3 (Bảng 3.1, Hình 3.1…).

### Bước 5 — Xuất `.docx`

Dùng script có sẵn trong repo:

```
python tools/md_to_docx.py "<file.md>" "<file.docx>"
```

Yêu cầu định dạng: Times New Roman 13, lề trái 3cm, lề phải 2cm, trên/dưới 2cm, bảng kẻ khung.

### Bước 6 — Cập nhật AI_RESPONSE.md

Ghi lại: đã sửa những chỗ lệch nào, đã đổi văn phong những mục nào, file nào đã tạo.

---

## 3. Định nghĩa hoàn thành

- [ ] `docs/reports/RA_SOAT_DONG_BO_C1_C2_C3.md` có bảng đối chiếu + danh sách chỗ lệch đã sửa.
- [ ] Không còn chỗ nào trong báo cáo nhắc "UC21" (chỉ được nhắc "UC18").
- [ ] Số liệu kiểm thử trong Chương 3 khớp báo cáo tuần 8 (222/222).
- [ ] `docs/reports/chapter3/BAN_THAO_CHUONG_3.docx` tồn tại, **chỉ chứa Chương 3**, mở được bằng Word.
- [ ] Văn phong đã đổi sang giọng sinh viên, không còn câu học thuật nặng.
- [ ] `AI_RESPONSE.md` đã cập nhật.
- [ ] Mở Pull Request vào `main`.

---

## 4. Lưu ý

- **Không** sửa `docs/reports/complete/BAO_CAO_DO_AN_1_KANTO_KTX_HOAN_THIEN.docx` trực tiếp nếu không chắc — hãy tạo bản mới có hậu tố `_DONG_BO`.
- File `.docx` là nhị phân, không diff được trên GitHub — khi sửa phải ghi rõ trong PR mô tả những gì đã đổi.
- Nếu phát hiện mâu thuẫn nghiệp vụ không tự quyết được → ghi vào PR, không tự chọn.
