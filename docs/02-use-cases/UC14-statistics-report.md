# UC14 - Xem thống kê và báo cáo

## 1. Thông tin chung

- **Mã Use Case:** UC14
- **Tên Use Case:** Xem thống kê và báo cáo
- **Tác nhân chính:** Quản lý KTX

## 2. Mục đích

Cho phép Quản lý KTX xem các số liệu tổng hợp về tình hình lưu trú, hóa đơn và phản ánh sự cố nhằm hỗ trợ công tác theo dõi và quản lý ký túc xá.

## 3. Tiền điều kiện

- Quản lý KTX đã đăng nhập vào hệ thống.

## 4. Hậu điều kiện

- Hệ thống hiển thị kết quả thống kê phù hợp với loại thống kê và khoảng thời gian được lựa chọn.
- Không có dữ liệu nghiệp vụ nào bị thay đổi sau khi thực hiện chức năng.

## 5. Luồng sự kiện chính

| Bước | Tác nhân thực hiện hành động | Hệ thống phản hồi |
|---|---|---|
| 1 | Quản lý KTX chọn chức năng **“Xem thống kê và báo cáo”**. | |
| 2 | | Hệ thống hiển thị giao diện thống kê và báo cáo, cho phép lựa chọn loại thống kê, ngày bắt đầu và ngày kết thúc. |
| 3 | Quản lý KTX lựa chọn loại thống kê, khoảng thời gian cần xem và nhấn **“Thống kê”**. | |
| 4 | | Hệ thống kiểm tra tính hợp lệ của khoảng thời gian được chọn. Nếu hợp lệ, hệ thống truy vấn và tổng hợp dữ liệu tương ứng với loại thống kê được lựa chọn, sau đó hiển thị kết quả dưới dạng các số liệu tổng hợp và bảng dữ liệu tương ứng. |

## 6. Loại thống kê

UC14 hỗ trợ xem các nhóm thống kê tổng hợp phục vụ công tác quản lý KTX:

- Tình hình lưu trú
- Hóa đơn
- Phản ánh sự cố

Loại thống kê cụ thể được Quản lý KTX lựa chọn trên giao diện trước khi thực hiện thống kê.

## 7. Khoảng thời gian thống kê

Quản lý KTX có thể xác định khoảng thời gian thông qua:

- Ngày bắt đầu
- Ngày kết thúc

Điều kiện hợp lệ:

```text
NgayKetThuc >= NgayBatDau
Nếu không lựa chọn khoảng thời gian và chọn xem toàn bộ dữ liệu thì hệ thống thực hiện theo luồng phụ A1.
8. Luồng phụ
A1 - Xem thống kê toàn bộ thời gian
- Xảy ra tại: Bước 3.
- Điều kiện: Quản lý KTX không lựa chọn khoảng thời gian và chọn xem toàn bộ dữ liệu.
- Xử lý:
  - Hệ thống truy vấn toàn bộ dữ liệu hiện có phù hợp với loại thống kê được chọn.
  - Hệ thống tổng hợp dữ liệu.
  - Hệ thống hiển thị kết quả thống kê tương ứng.
9. Luồng ngoại lệ
E1 - Khoảng thời gian không hợp lệ
- Xảy ra tại: Bước 4.
- Điều kiện: Ngày kết thúc nhỏ hơn ngày bắt đầu.
- Xử lý:
  - Hệ thống không thực hiện thống kê.
  - Hệ thống hiển thị thông báo:
    “Khoảng thời gian không hợp lệ. Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.”
E2 - Không có dữ liệu thống kê
- Xảy ra tại: Bước 4.
- Điều kiện: Hệ thống không tìm thấy dữ liệu phù hợp với loại thống kê và khoảng thời gian được lựa chọn.
- Xử lý:
  - Hệ thống không hiển thị số liệu thống kê không tồn tại.
  - Hệ thống hiển thị thông báo:
    “Không có dữ liệu phù hợp.”
10. Quy tắc nghiệp vụ
BR-UC14-01 - Chỉ Quản lý KTX được sử dụng chức năng
UC14 dành cho tác nhân:
Quản lý KTX
Người dùng thuộc vai trò khác không được truy cập chức năng thống kê và báo cáo dành cho Quản lý KTX.
BR-UC14-02 - Chức năng chỉ đọc
UC14 chỉ thực hiện:
- truy vấn dữ liệu;
- tổng hợp dữ liệu;
- hiển thị số liệu;
- hiển thị bảng kết quả.
UC14 không cập nhật dữ liệu nghiệp vụ.
BR-UC14-03 - Kiểm tra khoảng thời gian
Nếu Quản lý KTX sử dụng khoảng thời gian thì:
NgayKetThuc >= NgayBatDau
Nếu điều kiện không được thỏa mãn, hệ thống không thực hiện thống kê.
BR-UC14-04 - Có thể thống kê toàn bộ dữ liệu
Nếu Quản lý KTX lựa chọn xem toàn bộ thời gian thì hệ thống không giới hạn kết quả theo ngày bắt đầu và ngày kết thúc.
BR-UC14-05 - Kết quả phụ thuộc loại thống kê
Hệ thống chỉ truy vấn và tổng hợp dữ liệu phù hợp với loại thống kê mà Quản lý KTX đã lựa chọn.
Các nhóm nghiệp vụ được thống kê trong UC14 gồm:
- lưu trú;
- hóa đơn;
- phản ánh sự cố.
11. Dữ liệu liên quan
Tùy theo loại thống kê được lựa chọn, UC14 sử dụng dữ liệu từ các nhóm nghiệp vụ tương ứng.
11.1. Thống kê tình hình lưu trú
Có thể sử dụng dữ liệu liên quan đến:
- SinhVien
- HopDong
- Phong
- ToaNha
11.2. Thống kê hóa đơn
Có thể sử dụng dữ liệu liên quan đến:
- HoaDon
- HoaDonTienPhong
- HoaDonDienNuoc
11.3. Thống kê phản ánh sự cố
Có thể sử dụng dữ liệu từ:
- PhanAnhSuCo
12. Luồng xử lý tổng quát
Quản lý KTX
      |
      v
UC14 - Xem thống kê và báo cáo
      |
      v
Chọn loại thống kê
      |
      v
Chọn khoảng thời gian
      |
      v
Kiểm tra điều kiện thời gian
      |
  +---+---+
  |       |
Không    Hợp lệ
hợp lệ    |
  |       v
  |    Truy vấn dữ liệu
  |       |
  |       v
  |    Tổng hợp dữ liệu
  |       |
  |       v
  |   Hiển thị kết quả
  |
  v
Thông báo lỗi
13. Ghi chú triển khai
- Backend phải kiểm tra vai trò của người dùng trước khi cho phép truy cập UC14.
- Dữ liệu thống kê được tổng hợp từ dữ liệu nghiệp vụ đã tồn tại trong hệ thống.
- UC14 không tạo, sửa hoặc xóa dữ liệu nghiệp vụ.
- Khoảng thời gian phải được kiểm tra trước khi thực hiện truy vấn thống kê.
- Nếu không có dữ liệu phù hợp, hệ thống phải hiển thị trạng thái không có dữ liệu thay vì phát sinh lỗi.
- Khi xem toàn bộ thời gian, hệ thống không áp dụng giới hạn ngày bắt đầu và ngày kết thúc.
- Kết quả được hiển thị dưới dạng số liệu tổng hợp và bảng dữ liệu tương ứng với loại thống kê.
14. Không được tự ý thay đổi
Khi triển khai UC14, không được tự ý:
- Cho phép vai trò khác sử dụng chức năng dành cho Quản lý KTX.
- Thay đổi dữ liệu nghiệp vụ trong quá trình thống kê.
- Bỏ bước kiểm tra khoảng thời gian.
- Thực hiện thống kê khi ngày kết thúc nhỏ hơn ngày bắt đầu.
- Tự thêm loại thống kê mới ngoài phạm vi đã thống nhất.
- Tự thêm chức năng chỉnh sửa dữ liệu từ giao diện thống kê.
- Tự thêm chức năng xuất PDF, Excel hoặc in báo cáo nếu chưa được nhóm thống nhất.