# UC12 - Xem thông báo

## 1. Thông tin chung

- **Mã Use Case:** UC12
- **Tên Use Case:** Xem thông báo
- **Tác nhân chính:** Sinh viên

## 2. Mục đích

Cho phép Sinh viên xem các thông báo do hệ thống gửi liên quan đến quá trình lưu trú, đặc biệt là các thông báo về hóa đơn cần thanh toán.

## 3. Tiền điều kiện

- Sinh viên đã đăng nhập vào hệ thống.
- Thông tin Sinh viên đã tồn tại trong hệ thống.

## 4. Hậu điều kiện

- Hệ thống hiển thị các thông báo thuộc về Sinh viên đang đăng nhập.
- Sinh viên có thể xem nội dung chi tiết của từng thông báo.
- Không có dữ liệu nghiệp vụ nào bị thay đổi sau khi thực hiện chức năng.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Sinh viên nhấn vào biểu tượng **Thông báo (chuông)** trên giao diện. | |
| 2 | | Hệ thống xác định Sinh viên đang đăng nhập và truy vấn các thông báo tương ứng. Hệ thống hiển thị danh sách thông báo của Sinh viên, được sắp xếp theo thời gian tạo từ mới nhất đến cũ nhất. |
| 3 | Sinh viên chọn một thông báo trong danh sách để xem chi tiết. | |
| 4 | | Hệ thống hiển thị nội dung chi tiết của thông báo được chọn. |

## 6. Thông tin hiển thị

### 6.1. Danh sách thông báo

Danh sách thông báo có thể hiển thị các thông tin:

- Tiêu đề thông báo
- Nội dung tóm tắt
- Thời gian tạo

Danh sách được sắp xếp theo:

`ThoiGianTao`

theo thứ tự:

`Mới nhất → Cũ nhất`

### 6.2. Chi tiết thông báo

Khi Sinh viên chọn một thông báo, hệ thống hiển thị:

- Tiêu đề
- Nội dung
- Thời gian tạo

Nếu thông báo có liên quan đến hóa đơn thì hệ thống có thể sử dụng thông tin hóa đơn được liên kết để xác định nguồn của thông báo.

## 7. Luồng phụ

### A1 - Không có thông báo

- **Xảy ra tại:** Bước 2.
- **Điều kiện:** Hệ thống không tìm thấy thông báo nào thuộc về Sinh viên đang đăng nhập.
- **Xử lý:**
  - Hệ thống hiển thị danh sách thông báo trống.
  - Hệ thống hiển thị thông báo:

    **“Bạn chưa có thông báo nào.”**

## 8. Quy tắc nghiệp vụ

### BR-UC12-01 - Chỉ xem thông báo của chính mình

Sinh viên chỉ được xem các thông báo được gắn với MSSV của chính mình.

Sinh viên không được xem thông báo của Sinh viên khác.

### BR-UC12-02 - Xác định Sinh viên từ phiên đăng nhập

Hệ thống phải xác định Sinh viên dựa trên tài khoản và Session đang đăng nhập.

Không cho phép Sinh viên tự nhập MSSV để truy vấn thông báo của người khác.

### BR-UC12-03 - Thứ tự hiển thị

Danh sách thông báo phải được sắp xếp theo thời gian tạo:

`ThoiGianTao DESC`

Tức là:

`Thông báo mới nhất → Thông báo cũ nhất`

### BR-UC12-04 - Chức năng chỉ đọc

UC12 chỉ thực hiện:

- xem danh sách thông báo;
- xem nội dung chi tiết thông báo.

UC12 không làm thay đổi dữ liệu nghiệp vụ.

### BR-UC12-05 - Không có trạng thái đã đọc/chưa đọc

Trong phạm vi hiện tại, hệ thống không quản lý trạng thái:

- `Đã đọc`
- `Chưa đọc`

Việc Sinh viên mở một thông báo không làm thay đổi trạng thái hoặc dữ liệu của thông báo.

### BR-UC12-06 - Thông báo có thể liên quan đến hóa đơn

Một thông báo có thể được tạo từ nghiệp vụ lập hóa đơn.

Trong trường hợp đó, thông báo có thể được liên kết với hóa đơn tương ứng.

Tuy nhiên, không phải mọi thông báo đều bắt buộc phải có hóa đơn liên quan.

## 9. Dữ liệu liên quan

### Bảng `ThongBao`

Là bảng dữ liệu chính của UC12.

Các thuộc tính liên quan:

- `MaThongBao`
- `MSSV`
- `MaHoaDon`
- `TieuDe`
- `NoiDung`
- `ThoiGianTao`

### `MSSV`

Dùng để xác định Sinh viên nhận thông báo.

Hệ thống chỉ truy vấn các thông báo có:

`ThongBao.MSSV = MSSV của Sinh viên đang đăng nhập`

### `MaHoaDon`

Có thể được sử dụng để liên kết thông báo với hóa đơn tương ứng.

`MaHoaDon` có thể không có giá trị đối với những thông báo không liên quan đến hóa đơn.

### `ThoiGianTao`

Được sử dụng để sắp xếp danh sách thông báo từ mới nhất đến cũ nhất.

### Bảng `SinhVien`

Được sử dụng để xác định Sinh viên hiện tại thông qua tài khoản đang đăng nhập.

## 10. Quan hệ dữ liệu chính

Một Sinh viên có thể có nhiều thông báo:

`SinhVien 1 -------- N ThongBao`

Một hóa đơn có thể liên quan đến thông báo, nhưng thông báo không bắt buộc phải có hóa đơn:

`HoaDon 0..1 -------- N ThongBao`

Luồng xác định dữ liệu:

`TaiKhoan → SinhVien → ThongBao`

## 11. Quan hệ với Use Case khác

Thông báo có thể được tạo từ các nghiệp vụ lập hóa đơn:

- UC10.1 - Lập hóa đơn tiền phòng
- UC10.2 - Lập hóa đơn điện nước

Luồng tổng quát:

`UC10.1 / UC10.2`
→ `Tạo HoaDon`
→ `Tạo ThongBao`
→ `UC12 - Xem thông báo`

Sinh viên sử dụng UC12 để xem các thông báo đã được hệ thống tạo trước đó.

## 12. Ghi chú triển khai

- Sinh viên phải được xác định từ Session/tài khoản đang đăng nhập.
- Không lấy MSSV từ dữ liệu do người dùng tự nhập để truy vấn thông báo.
- Backend phải đảm bảo chỉ trả về thông báo thuộc về Sinh viên hiện tại.
- Danh sách phải được sắp xếp theo `ThoiGianTao` giảm dần.
- UC12 không cập nhật hoặc xóa thông báo.
- UC12 không có chức năng đánh dấu đã đọc/chưa đọc.
- Việc mở xem chi tiết một thông báo không làm thay đổi dữ liệu.
- Nếu không có thông báo, giao diện vẫn phải hiển thị bình thường với thông báo **“Bạn chưa có thông báo nào.”**

## 13. Không được tự ý thay đổi

Khi triển khai UC12, không được tự ý:

- Cho phép Sinh viên xem thông báo của Sinh viên khác.
- Cho phép nhập MSSV để truy vấn thông báo của người khác.
- Thêm trạng thái **“Đã đọc”** hoặc **“Chưa đọc”** nếu chưa được nhóm thống nhất.
- Tự động thay đổi dữ liệu khi Sinh viên mở thông báo.
- Cho phép Sinh viên chỉnh sửa nội dung thông báo.
- Cho phép Sinh viên xóa thông báo nếu chưa có trong đặc tả.
- Thay đổi thứ tự mặc định từ mới nhất đến cũ nhất.
- Yêu cầu mọi thông báo bắt buộc phải liên kết với một hóa đơn.