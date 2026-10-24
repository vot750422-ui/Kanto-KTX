# Xác nhận thanh toán tiền phòng đầu tiên

## Phạm vi

- Branch `feature/uc07-payment-confirmation`; triển khai theo yêu cầu demo ngày 15/10/2026.
- GET `index.php?action=payments`: danh sách hóa đơn tiền phòng, lọc MSSV/trạng thái, phân trang 20 dòng; xem số tiền, kỳ thu, ngày thu và hợp đồng.
- POST `index.php?action=payment-confirm`: chỉ Nhân viên kế toán, có hộp thoại xác nhận đã nhận đủ tiền.
- Đây là phần tiếp nối UC03 phục vụ demo, chưa thay thế toàn bộ UC07.1 của thành viên khác: chưa tra cứu điện nước, lọc phòng/tháng/năm hoặc xử lý hóa đơn gia hạn.

## Nghiệp vụ đã thống nhất

- UC03 đã cấp tài khoản tên đăng nhập MSSV, mật khẩu băm, hồ sơ Chờ thanh toán và hóa đơn. Thanh toán giữ nguyên tài khoản/mật khẩu đó, không cấp trùng hoặc khôi phục tài khoản thiếu/khóa bằng suy đoán.
- Người dùng chọn hợp đồng theo ngày bắt đầu/kết thúc học kỳ trên hóa đơn. Không lấy kỳ mới nhất tại ngày thanh toán, không nhận ngày hoặc số tiền do trình duyệt gửi.
- Trong một transaction: hóa đơn Chưa thu → Đã thu, ghi ThoiGianThanhToan; tạo HopDong Còn hạn; liên kết DonDangKy.MaHopDong; chuyển SinhVien.TrangThaiLuuTru thành Đang ở.
- Dùng cùng named lock UC02/UC03, READ COMMITTED, khóa phòng trước rồi đơn/hóa đơn và hồ sơ liên quan. Kiểm tra phòng, giới tính, liên kết đơn/hóa đơn, tài khoản, kỳ chưa kết thúc, hợp đồng trùng thời gian và sức chứa.
- Giữ chỗ bằng đơn được thay bằng hợp đồng trong cùng transaction nên không tính hai chỗ. Lỗi bất kỳ bước nào rollback toàn bộ; hóa đơn đã thu không xác nhận lại hoặc tự backfill hợp đồng.
- Không sửa schema, cấu hình DB, Docker/Render hoặc dữ liệu thực khi triển khai/kiểm thử.

## Demo

1. Khách gửi đơn UC02; NVQL duyệt bằng UC03, ghi lại MSSV và mã hóa đơn.
2. Đăng nhập tài khoản Nhân viên kế toán, chọn Quản lý hóa đơn; tìm MSSV.
3. Chọn Xác nhận thanh toán, kiểm tra số tiền/kỳ và xác nhận sau khi đã nhận đủ tiền.
4. Danh sách Đã thu hiển thị mã hợp đồng. Đăng nhập tài khoản sinh viên đã cấp ở UC03, mở Thông tin lưu trú/Hồ sơ.
5. Hợp đồng kỳ tương lai hiển thị trong UC11; dashboard hiển thị phòng được bố trí và ngày bắt đầu.

## Kiểm chứng

- `tests/payment.php`: 29 ca, gồm luồng UC03 → thanh toán → UC11, giữ tài khoản, không đếm chỗ hai lần, gửi lặp, tài khoản khóa, trùng kỳ, vượt sức chứa, lỗi cuối transaction và thử lại; kiểm tra template/escape/form. Dữ liệu ghi trong bảng TEMPORARY.
- `tests/run.php`: 45 ca, gồm 8 ca thanh toán: khách/ba vai trò khác bị chặn, kế toán được truy cập, thiếu hóa đơn/GET xác nhận/bộ lọc mảng bị chặn.
- UC03 57 ca, UC11 model 6 ca đạt; PHP lint các file tích hợp đạt.
- Chưa xác nhận hóa đơn thật, chưa kiểm tra trực quan trình duyệt hoặc deploy Render. Nhánh bàn giao chỉ gồm UC07, UC11 và phần tích hợp liên quan.
