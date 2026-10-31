# UC21 - Gia hạn lưu trú (Timer)

## 1. Thông tin chung

- **Mã Use Case:** UC21 (đánh số theo `docs/sources/BÁO CÁO BÀI TẬP LỚN`, mục 3.15 — mã gốc "UC-21")
- **Tên Use Case:** Gia hạn
- **Tác nhân chính:** Timer (Hệ thống)
- **Use Case liên quan:** UC10.1 (Lập hóa đơn tiền phòng), UC05 (Quản lý hợp đồng), UC12 (Xem thông báo)
- **Trạng thái triển khai:** ⬜ Chưa code — đang chờ chốt nghiệp vụ

## 2. Mục đích

Tự động duy trì quyền lưu trú cho sinh viên đã hoàn thành nghĩa vụ tài chính, đồng thời tạm khóa quyền truy cập của sinh viên còn nợ phí tiền phòng.

## 3. Tiền điều kiện

1. Ngày hiện tại là **ngày bắt đầu học kỳ mới** (khớp `hocky.NgayBatDau` của một kỳ).
2. Tồn tại hóa đơn tiền phòng trong CSDL (UC10.1 đã chạy thành công cho kỳ vừa kết thúc).

## 4. Hậu điều kiện

Sau khi tiến trình thực hiện thành công:

1. Toàn bộ hợp đồng cũ chuyển trạng thái `Hết hạn`.
2. Bản ghi `hopdong` mới được khởi tạo cho sinh viên có hóa đơn tiền phòng kỳ vừa rồi `Đã thu`:
   - `NgayBatDau` = `hocky.NgayBatDau` của kỳ mới
   - `NgayKetThuc` = `hocky.NgayKetThuc` của kỳ mới
   - `TrangThai` = `Còn hạn`
3. `taikhoan.TrangThai` của sinh viên có hóa đơn `Chưa thu` chuyển thành `Khóa`.
4. Thông báo được gửi cho từng sinh viên (gia hạn thành công / bị khóa).

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Timer kích hoạt chức năng **"Gia hạn"** vào ngày bắt đầu học kỳ mới. | |
| 2 | | Hệ thống thực hiện tuần tự trong **một transaction duy nhất**:<br>① Quét `hopdong`, chuyển tất cả bản ghi `Còn hạn` → `Hết hạn`.<br>② Truy vấn `hoadontienphong` của kỳ vừa kết thúc cho từng sinh viên.<br>③ Nếu hóa đơn `Đã thu`: khởi tạo `hopdong` mới, chép `MSSV` + `MaPhong` từ hợp đồng cũ, đặt `NgayBatDau`/`NgayKetThuc` theo `hocky` kỳ mới, trạng thái `Còn hạn`. Cập nhật `dondangky.MaHopDong` nếu đơn gốc còn liên kết.<br>④ Nếu hóa đơn `Chưa thu`: cập nhật `taikhoan.TrangThai = 'Khóa'`.<br>⑤ Gửi thông báo hệ thống (bảng `thongbao`) cho từng sinh viên.<br>⑥ Ghi nhật ký chạy vào `nhatkyhethong`. |

## 6. Luồng ngoại lệ

### E1 - Lỗi hệ thống

- **Xảy ra tại:** Bước 2.
- **Điều kiện:** Mất kết nối CSDL hoặc lỗi kỹ thuật giữa chừng.
- **Xử lý:**
  - Ghi `nhatkyhethong`: thời gian, tên chức năng (`renewal`), nội dung lỗi.
  - Gửi thông báo đến Quản lý KTX.
  - **Rollback** toàn bộ — không để dữ liệu dở dang.

### E2 - Hóa đơn `Chưa thu`

- **Xảy ra tại:** Bước 2 (nhánh ④).
- **Điều kiện:** Sinh viên có hóa đơn tiền phòng kỳ vừa rồi ở trạng thái `Chưa thu`.
- **Xử lý:**
  - Cập nhật `taikhoan.TrangThai = 'Khóa'`.
  - Gửi thông báo cho sinh viên: tài khoản bị khóa, yêu cầu liên hệ nhân viên để thanh toán và mở khóa.
  - **Không** tạo hợp đồng mới cho sinh viên này.

### E3 - Chạy lặp trong cùng ngày (bổ sung, ngoài đặc tả gốc)

- **Điều kiện:** Timer trigger 2 lần trong cùng ngày.
- **Xử lý:** Bỏ qua nếu đã có `hopdong` với `NgayBatDau = today` cho MSSV đó, hoặc nếu `nhatkyhethong` đã ghi lần chạy `renewal` thành công trong ngày.

## 7. Quy tắc nghiệp vụ

### BR-UC21-01 - Thời điểm kích hoạt

```text
Ngày hiện tại = NgayBatDau của một học kỳ trong bảng hocky
```

### BR-UC21-02 - Điều kiện gia hạn

Chỉ gia hạn sinh viên có hóa đơn tiền phòng kỳ vừa kết thúc ở trạng thái `Đã thu`.

### BR-UC21-03 - Khóa tài khoản

Sau khi Gia hạn chạy xong, sinh viên có hóa đơn tiền phòng `Chưa thu` chuyển `taikhoan.TrangThai` thành `Khóa`.

### BR-UC21-04 - Tính nguyên tử

Toàn bộ tiến trình chạy trong **một transaction**. Lỗi ở bất kỳ bước nào → rollback toàn bộ, kể cả các hợp đồng đã chuyển `Hết hạn`.

## 8. Vướng mắc triển khai cần chốt

Xem mục 7.2 của [`docs/PROJECT_CONTEXT.md`](../PROJECT_CONTEXT.md). Tóm tắt:

| # | Vấn đề | Cần quyết định |
|---|---|---|
| V1 | "Học kỳ hiện tại" mơ hồ | Hóa đơn cần kiểm tra thuộc kỳ **liền trước** kỳ bắt đầu hôm nay |
| V2 | `hopdong` không có `MaHocKy` | Suy ra từ ngày, hoặc thêm cột |
| V3 | `MaHopDong` varchar(10) dễ tràn | Đổi cách sinh mã hoặc nới cột |
| V4 | Chưa có bảng `nhatkyhethong` | Tạo migration |
| V5 | Chưa có mailer cho E2 | Dùng `thongbao` trước |
| V6 | Đổi `Hết hạn` hàng loạt ảnh hưởng UC02/UC03 | Transaction + khóa bảng |
| V7 | Timer trigger lặp | Idempotency |
| V8 | Sinh viên `Chờ thanh toán` từ UC03 bị khóa ngay | Miễn trừ hay cho đăng nhập read-only |
| V9 | Chưa có cron trên Render | Render Cron Job hoặc GitHub Actions |
| V10 | Thiếu tài liệu | Chính file này + sequence diagram |

## 9. Kế hoạch triển khai

1. Chốt V1, V2, V8 với GVHD.
2. Migration `sql/uc21-renewal.sql`: bảng `nhatkyhethong`, (tùy chọn) `hopdong.MaHocKy`.
3. `models/Renewal.php` — method `run(): array` chạy trong transaction.
4. `index.php?action=renewal-run` — chỉ POST + token bí mật.
5. `tests/renewal.php` — tối thiểu 8 case.
6. Render Cron Job.
7. Cập nhật `PROJECT_STATE.md`, `USE_CASE_OVERVIEW.md`, ERD, báo cáo.
