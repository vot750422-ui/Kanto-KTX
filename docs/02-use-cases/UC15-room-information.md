# UC15 - Xem thông tin phòng

## 1. Thông tin chung

- **Mã Use Case:** UC15
- **Tên Use Case:** Xem thông tin phòng
- **Tác nhân chính:** Khách vãng lai

## 2. Mục đích

Cho phép Khách vãng lai xem thông tin các phòng trong ký túc xá theo dãy và tầng, bao gồm tình trạng sử dụng và khả năng còn chỗ của từng phòng.

UC15 chỉ phục vụ việc xem thông tin phòng và không cho phép người dùng thực hiện lựa chọn hoặc giữ chỗ trực tiếp.

## 3. Tiền điều kiện

- Người dùng đã truy cập Website.
- Người dùng không bắt buộc phải đăng nhập để sử dụng chức năng.

## 4. Hậu điều kiện

- Thông tin các phòng phù hợp với dãy và tầng được lựa chọn được hiển thị.
- Người dùng có thể biết phòng còn chỗ hay đã đầy.
- Không có dữ liệu nào trong hệ thống bị thay đổi sau khi thực hiện chức năng.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Người dùng chọn chức năng **“Thông tin phòng”**. | |
| 2 | | Hệ thống hiển thị giao diện thông tin phòng và các lựa chọn dãy, tầng. |
| 3 | Người dùng lựa chọn dãy và tầng muốn xem. | |
| 4 | | Hệ thống truy vấn danh sách phòng thuộc dãy và tầng đã chọn. Hệ thống xác định tình trạng sử dụng của từng phòng dựa trên sức chứa, số chỗ đang được sử dụng và số chỗ đang được giữ. Hệ thống hiển thị danh sách phòng gồm Số phòng, Giới tính phòng, Số người hiện tại/Sức chứa và trạng thái **“Còn chỗ”** hoặc **“Đầy”**. |
| 5 | Người dùng có thể tiếp tục lựa chọn dãy hoặc tầng khác để xem. | |
| 6 | | Hệ thống truy vấn và cập nhật lại danh sách phòng tương ứng với lựa chọn mới. |

## 6. Thông tin hiển thị

Đối với mỗi phòng, hệ thống hiển thị các thông tin:

- Số phòng
- Giới tính phòng
- Số người hiện tại
- Sức chứa
- Trạng thái phòng

Trạng thái phòng gồm:

- `Còn chỗ`
- `Đầy`

Ví dụ:

```text
Phòng: 201
Giới tính phòng: Nam
Số người hiện tại / Sức chứa: 4 / 6
Trạng thái: Còn chỗ
BR-UC15-02 - Chức năng chỉ đọc
UC15 chỉ thực hiện:
- lựa chọn dãy;
- lựa chọn tầng;
- xem danh sách phòng;
- xem tình trạng còn chỗ của phòng.
UC15 không thực hiện thao tác thêm, sửa hoặc xóa dữ liệu.
BR-UC15-03 - Không cho phép chọn phòng để đăng ký trong UC15
UC15 chỉ hiển thị thông tin phòng.
Người dùng không thực hiện các thao tác sau trong UC15:
- chọn phòng để đăng ký lưu trú;
- giữ chỗ;
- tạo đơn đăng ký;
- thay đổi thông tin phòng.
Việc chọn phòng mong muốn và giữ chỗ thuộc:
UC02 - Đăng ký lưu trú
BR-UC15-04 - Xác định số chỗ đang được sử dụng
Số chỗ đang được sử dụng chính thức của một phòng được xác định từ số hợp đồng lưu trú của phòng đang ở trạng thái:
Còn hạn
Có thể biểu diễn:
SoChoDangSuDung
=
Số HopDong của phòng có TrangThai = "Còn hạn"
BR-UC15-05 - Đơn đăng ký chờ duyệt giữ một chỗ
Mỗi đơn đăng ký có trạng thái:
Chờ duyệt
đối với một phòng được tính là đang tạm giữ một chỗ của phòng đó.
Một đơn đăng ký không giữ toàn bộ phòng.
Có thể biểu diễn:
SoChoDangGiu
=
Số DonDangKy của phòng có TrangThai = "Chờ duyệt"
BR-UC15-06 - Công thức xác định số chỗ khả dụng
Số chỗ khả dụng của phòng được xác định:
SoChoKhaDung
=
SucChua
- SoChoDangSuDung
- SoChoDangGiu
Tương đương:
SoChoKhaDung
=
Phong.SucChua
- số HopDong "Còn hạn"
- số DonDangKy "Chờ duyệt"
BR-UC15-07 - Xác định trạng thái phòng
Nếu:
SoChoKhaDung > 0
thì trạng thái hiển thị:
Còn chỗ
Nếu:
SoChoKhaDung <= 0
thì trạng thái hiển thị:
Đầy
BR-UC15-08 - Không lưu trực tiếp trạng thái Còn chỗ/Đầy
Trạng thái:
- Còn chỗ
- Đầy
được hệ thống tính từ dữ liệu hiện tại.
Không cần lưu trực tiếp hai trạng thái này vào bảng Phong.
BR-UC15-09 - Không thay đổi sức chứa khi có sinh viên vào hoặc rời phòng
Phong.SucChua là sức chứa thiết kế của phòng.
Khi:
- sinh viên được bố trí vào phòng;
- sinh viên rời phòng;
- một đơn đăng ký giữ chỗ;
- một đơn đăng ký bị từ chối;
hệ thống không thay đổi SucChua.
Chỉ số chỗ khả dụng được tính lại từ dữ liệu hợp đồng và đơn đăng ký.