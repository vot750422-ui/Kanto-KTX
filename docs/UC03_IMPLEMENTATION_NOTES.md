# UC03 — Triển khai và các việc cần đồng bộ

Tài liệu bàn giao UC03 trên branch `feature/uc03-approval`. Các bản thảo trong
`docs/sources/` là tài liệu tham khảo, không tự khôi phục nghiệp vụ từ bản cũ.

## Phạm vi đã thống nhất

- Chỉ Nhân viên quản lý KTX được xét duyệt và đọc ảnh minh chứng.
- Ưu tiên cao đứng trước: UT02 (4), UT03 (3), UT01 (2), UT00 (0).
  Cùng mức ưu tiên: thời gian gửi cũ nhất trước, sau đó mã đơn.
- Duyệt tạo tài khoản (tên đăng nhập MSSV), sinh viên và hóa đơn tiền phòng đầu tiên.
  Mật khẩu ban đầu theo quy ước demo đã thống nhất, lưu bằng `password_hash()`;
  không ghi mật khẩu trong tài liệu bàn giao, URL hoặc log.
- Sinh viên ở trạng thái `Chờ thanh toán`; đơn `Đã duyệt` tiếp tục giữ đúng một chỗ.
  Không tạo hợp đồng khi duyệt. Không gán sinh viên thành `Đang ở`.
- Hóa đơn đầu tiên thu trọn học kỳ kế tiếp gần nhất sau học kỳ hiện tại,
  kể cả chuyển từ kỳ cuối năm học sang kỳ đầu năm học mới. Đơn giá Tiền phòng
  mới nhất có NgayApDung <= ngày duyệt (múi giờ Việt Nam), đơn vị `đồng/học kỳ`.
  Số tiền lấy nguyên giá trị DECIMAL từ CSDL, không tính theo ngày/tháng còn lại.
  Thiếu/trùng/chồng lấn học kỳ hoặc thiếu đơn giá thì rollback, không ghi một phần.
- Ví dụ đang ở học kỳ 3: nếu kỳ gần nhất tiếp theo là học kỳ 1 năm mới thì hóa đơn
  thu trọn học kỳ 1 đó. Việc mở cổng một tháng trước khi kết thúc kỳ là giả định
  để trình bày đồ án; UC02 không có điều kiện tự chặn theo lịch đăng ký.
- Từ chối chỉ áp dụng đơn `Chờ duyệt`, phải có lý do tối đa 500 ký tự và giải phóng chỗ.
- Ảnh tùy chọn: phân biệt không nộp ảnh và tệp đã mất. Đọc qua endpoint có phân quyền,
  không mở HTTP trực tiếp vào storage.
- Chặn UC02 khi MSSV/CCCD đã có trong sinhvien hoặc có đơn Chờ duyệt cùng MSSV;
  UC03 kiểm tra lại MSSV, CCCD, tên đăng nhập và ràng buộc UNIQUE trong transaction.

## Các file và thành phần đã triển khai

| Thành phần | File | Vai trò |
| --- | --- | --- |
| Model UC03 | `models/Approval.php` | Danh sách, thứ tự ưu tiên, chi tiết, xét duyệt, từ chối, hóa đơn và kiểm tra ảnh |
| Controller mới | `controllers/ApprovalController.php` | GET/POST, CSRF, mã lỗi, thông báo và endpoint ảnh |
| View mới | `views/approval/layout.php`, `list.php`, `detail.php`, `invoice-fields.php` | Danh sách phân trang, chi tiết, xác nhận duyệt/từ chối và xem ảnh |
| Tài nguyên mới | `assets/css/approval.css`, `assets/js/approval.js` | Giao diện và hộp thoại xác nhận |
| Route/menu | `index.php`, `includes/navbar.php` | Mở UC03 từ menu NVQL |
| UC02 tái sử dụng | `models/Registration.php`, `controllers/RegistrationController.php` | Kiểm tra hồ sơ trùng, giữ chỗ sau khi duyệt |
| Dashboard sinh viên | `views/student/Dashboard.php` | Hiển thị Chờ thanh toán |
| Middleware tái sử dụng | `middleware/Role.php` | `requireRole('Nhân viên quản lý KTX')`, không sửa middleware |
| Schema | `sql/uc03-approval.sql`, `sql/qlktx_v2.sql`, `sql/update-priorities.sql` | Migration, dump mới và mức ưu tiên đã chốt |
| Kiểm thử | `tests/approval.php`, `tests/run.php`, `tests/worker.php`, `tests/registration.php`, `tests/rooms.php` | UC03, quyền truy cập và hồi quy UC02 |

Bảng đọc: `dondangky`, `dienuutien`, `phong`, `toanha`, `hopdong`, `sinhvien`,
`taikhoan`, `hocky`, `dongia`; tên nhân viên lấy từ `nhanvien` qua model dashboard.
Duyệt ghi `taikhoan`, `sinhvien`, `hoadon`, `hoadontienphong`, `dondangky`;
từ chối chỉ ghi trạng thái và lý do trong `dondangky`. Không ghi `hopdong` trong UC03.

## Schema và migration

`sql/uc03-approval.sql` là migration chạy một lần cho CSDL hiện có:

1. Bổ sung giá trị `Chờ thanh toán` vào `sinhvien.TrangThaiLuuTru`.
2. Thêm `dondangky.MaHoaDon` nullable, UNIQUE, FK đến `hoadontienphong.MaHoaDon`.
3. Thêm `dondangky.MaHopDong` nullable, UNIQUE, FK đến `hopdong.MaHopDong`.
4. Cập nhật các mức ưu tiên 0/2/4/3.

Giữ nguyên cột Email nullable và dữ liệu cũ. Dump `sql/qlktx_v2.sql` được đồng bộ
cho CSDL mới. Không import lại dump vào CSDL đang dùng vì có DROP TABLE.
DDL MySQL tự commit: kiểm tra schema trước khi chạy, không chạy lại migration khi
các cột đã tồn tại. Kiểm tra schema production riêng trước khi áp dụng; không tự
chạy migration lên Aiven.

Trước khi áp dụng cho CSDL khác, rà soát các đơn `Đã duyệt` cũ và hợp đồng tương ứng.
Nếu đơn đã có hợp đồng, cần liên kết đúng `MaHopDong` sau khi thêm cột và trước khi
đưa code giữ chỗ mới vào sử dụng. Không tự suy đoán liên kết theo MSSV/phòng vì
một sinh viên có thể có nhiều hợp đồng lịch sử. Local hiện không có đơn Đã duyệt cũ.

## Giữ chỗ và bảo vệ UC02

```text
ConCho = SucChua
       - số hopdong Còn hạn
       - số dondangky Chờ duyệt
       - số dondangky Đã duyệt có MaHopDong NULL
```

Khi duyệt, phần giữ chỗ chuyển từ nhóm Chờ duyệt sang nhóm Đã duyệt chưa có hợp đồng,
nên ConCho không đổi. Khi từ chối, ConCho tăng một. Không cập nhật phong.SucChua.
Đơn đã liên kết hợp đồng không quay lại giữ chỗ kể cả khi hợp đồng hết hạn/thanh lý.

UC03 dùng cùng named lock `kanto_uc02_` theo tên CSDL như UC02, transaction
READ COMMITTED, khóa phong rồi dondangky bằng FOR UPDATE, đọc lại trạng thái và
giới tính/sức chứa. Phòng có ConCho bằng 0 vẫn được duyệt vì đơn đã giữ chỗ.
Tài khoản, sinh viên, hóa đơn cha/con và trạng thái/liên kết đơn commit cùng nhau;
lỗi phải rollback toàn bộ, luôn RELEASE_LOCK. Chỉ POST có CSRF được ghi dữ liệu.

## Những chức năng cần bổ sung sau UC03

- [ ] Đặc tả UC sinh viên xem hóa đơn và thanh toán mô phỏng qua QR.
- [ ] Quét QR chỉ mở trang demo; không tự coi lượt mở trang là đã thanh toán.
- [ ] Khi xác nhận hóa đơn tiền phòng đầu tiên: tạo hợp đồng đúng ngày đã chốt,
      cập nhật `dondangky.MaHopDong`, sinhvien `Đang ở` và hóa đơn `Đã thu`
      trong cùng transaction/khóa phòng. Xử lý lặp không tạo thêm hợp đồng.
- [ ] Hóa đơn phòng kỳ sau và điện nước không tự tạo hợp đồng đầu tiên.
- [ ] UC10.1 Timer: bỏ qua hóa đơn đã có theo UNIQUE(MSSV, MaHocKy),
      không lập lại hóa đơn đầu tiên đã tạo khi duyệt.
- [ ] UC10.2 Timer: điện nước theo tháng; chỉ tính sinh viên chính thức theo quy tắc
      cư trú đã chốt, không tính sinh viên Chờ thanh toán là đang ở.
- [ ] Chốt ngày bắt đầu/kết thúc hợp đồng, trường hợp thanh toán trễ/đăng ký lại,
      thời hạn giữ chỗ, hủy hồ sơ chưa thanh toán. Hiện không tự hết hạn hay tự hủy.
- [ ] Quy trình giao tài khoản cho sinh viên. Không triển khai Gmail service.
- [ ] Quy tắc mật khẩu phù hợp nếu sử dụng với hồ sơ thật; hiện là quy ước demo.

## Các tài liệu cần sửa chữa/đồng bộ

- [ ] Sequence UC03 hiện mang nội dung tra cứu sinh viên: người dùng sẽ sửa ảnh.
- [ ] Wireframe: số phòng/giới tính từ CSDL, danh mục ưu tiên 0/2/4/3,
      trạng thái ảnh thiếu, kết quả duyệt là chờ thanh toán.
- [ ] ERD và bản thảo CSDL: MaTK INT tự tăng, MatKhau chứa hash, không có
      toanha.DiaChi; bảng hoadondiennuocsinhvien thay tên ChiTietHoaDon cũ;
      thêm hai liên kết ở đơn và trạng thái Chờ thanh toán.
- [ ] Bản mô tả CSDL cũ ghi tạo hợp đồng ngay khi duyệt: đánh dấu là lịch sử.
- [ ] Lời mở đầu và Chương 1: mô tả thanh toán là mô phỏng, công nghệ thực tế
      PDO/WAMP/JavaScript thuần; không tuyên bố đã dùng Bootstrap/jQuery/AJAX
      hoặc đã tích hợp AI khi chưa có trong source.
- [ ] Lời mở đầu: xử lý đánh số trùng Chương 1 và dòng tiêu đề chương dư cuối file.
- [ ] README sources: mô tả đúng Chương 1 (lý thuyết/công nghệ/kiến trúc),
      giới thiệu PDF lộ trình; phân biệt thứ tự đọc với nguồn ưu tiên khi mâu thuẫn.
- [ ] Bổ sung Chương 2 và bảng tiến độ chức năng để chuẩn bị demo tuần 6.
- [ ] Không phục hồi tự xếp phòng, Gmail, tự từ chối khi hết phòng hoặc hạn thanh toán
      bốn ngày từ báo cáo học phần cũ nếu chưa có quyết định mới.

## Giới hạn triển khai

Không thay Docker, Render, Aiven, config DB, authentication, session hoặc middleware.
Không xóa Email. Chưa deploy/push hoặc merge UC03 vào main. Không nhập dữ liệu học kỳ/đơn giá
giả vào CSDL thật. Nếu thiếu dữ liệu lập hóa đơn, duyệt không được ghi một phần.
Local hiện chưa có học kỳ và đơn giá; cần bổ sung dữ liệu nghiệp vụ trước khi duyệt.

## Kết quả kiểm tra và cách thử

Ngày 10/10/2026: `tests/approval.php` đạt 51, `tests/run.php` đạt 28,
`tests/registration.php` đạt 20 và `tests/rooms.php` đạt 14 kiểm thử; tổng 113.
Các file PHP thay đổi đã qua kiểm tra cú pháp. Schema local có đủ hai FK mới.
Migration đã áp dụng local; không áp dụng CSDL ngoài local.

Test nghiệp vụ dùng TEMPORARY theo schema thật, giữ CHECK/UNIQUE nhưng bỏ FK để
cô lập fixture. Đã kiểm tra rollback, xử lý lặp và giải phóng khóa; chưa kiểm thử
hai tiến trình duyệt đồng thời trên cùng dữ liệu vật lý hoặc hộp thoại trong trình duyệt.

Để thử: nhập đúng học kỳ hiện tại, kỳ kế tiếp và đơn giá có hiệu lực `đồng/học kỳ`;
gửi đơn qua UC02, đăng nhập NVQL và mở menu Xét duyệt đơn đăng ký. Chi tiết hiển thị
kỳ/số tiền trước khi xác nhận. Thiếu dữ liệu hóa đơn thì vẫn có thể từ chối hồ sơ,
nhưng không thể xác nhận duyệt. Lệnh chạy test xem `tests/README.md`.
