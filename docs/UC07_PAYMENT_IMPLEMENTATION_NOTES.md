# Xác nhận thanh toán tiền phòng đầu tiên

## Cập nhật UC17 (24/10/2026)

Sinh viên có luồng QR mô phỏng riêng, được mô tả tại `02-use-cases/UC17-student-payment.md`. Cả UC17 và kế toán dùng chung `Payment::settle`. Kế toán được xác nhận mọi hóa đơn chưa thu; tiền phòng đầu tiên tạo hợp đồng, còn tiền phòng định kỳ và điện nước chỉ ghi nhận đã thu. UC17 vẫn thu được hóa đơn định kỳ/điện nước đã có. Các mô tả ngày 15/10 bên dưới là ghi nhận lúc bàn giao ban đầu.

Ngày 25/10/2026, QR mở trang riêng trên thiết bị quét và không yêu cầu đăng nhập sinh viên trên thiết bị đó. Token một lần có hạn 15 phút được kiểm tra/đánh dấu đã dùng cùng transaction; kế toán và QR vẫn dùng chung `Payment::settle`. Xem migration và các ngoại lệ tại đặc tả UC17.

## Phạm vi

- Branch `feature/uc07-payment-confirmation`; triển khai theo yêu cầu demo ngày 15/10/2026.
- GET `index.php?action=payments`: danh sách hóa đơn tiền phòng/điện nước, lọc MSSV/trạng thái/loại/phòng/kỳ, phân trang 20 dòng; xem số tiền, kỳ thu, ngày thu và hợp đồng.
- POST `index.php?action=payment-confirm`: chỉ Nhân viên kế toán, có hộp thoại xác nhận đã nhận đủ tiền.
- Hóa đơn gia hạn chưa tạo hợp đồng trong UC07.2; sẽ được xử lý trong UC riêng nếu chốt triển khai.

## Nghiệp vụ đã thống nhất

- UC03 đã cấp tài khoản tên đăng nhập MSSV, mật khẩu băm, hồ sơ Chờ thanh toán và hóa đơn. Thanh toán giữ nguyên tài khoản/mật khẩu đó, không cấp trùng hoặc khôi phục tài khoản thiếu/khóa bằng suy đoán.
- Người dùng chọn hợp đồng theo ngày bắt đầu/kết thúc học kỳ trên hóa đơn. Không lấy kỳ mới nhất tại ngày thanh toán, không nhận ngày hoặc số tiền do trình duyệt gửi.
- Với tiền phòng đầu tiên, trong một transaction: hóa đơn Chưa thu → Đã thu, ghi ThoiGianThanhToan; tạo HopDong Còn hạn; liên kết DonDangKy.MaHopDong; chuyển SinhVien.TrangThaiLuuTru thành Đang ở.
- Với hóa đơn định kỳ, transaction chỉ đổi hóa đơn sang Đã thu và không tạo hợp đồng. Với điện nước, transaction thu phần phân bổ của đúng sinh viên; hóa đơn tổng chỉ chuyển Đã thu khi tất cả phần đã thu.
- Dùng cùng named lock UC02/UC03, READ COMMITTED, khóa phòng trước rồi đơn/hóa đơn và hồ sơ liên quan. Kiểm tra phòng, giới tính, liên kết đơn/hóa đơn, tài khoản, kỳ chưa kết thúc, hợp đồng trùng thời gian và sức chứa.
- Giữ chỗ bằng đơn được thay bằng hợp đồng trong cùng transaction nên không tính hai chỗ. Lỗi bất kỳ bước nào rollback toàn bộ; hóa đơn đã thu không xác nhận lại hoặc tự backfill hợp đồng.
- Không sửa schema, cấu hình DB, Docker/Render hoặc dữ liệu thực khi triển khai/kiểm thử.

## Demo

1. Khách gửi đơn UC02; NVQL duyệt bằng UC03, ghi lại MSSV và mã hóa đơn.
2. Đăng nhập tài khoản Nhân viên kế toán, chọn Thu tiền và thanh toán; tìm MSSV.
3. Chọn Xác nhận thanh toán, kiểm tra số tiền/kỳ và xác nhận sau khi đã nhận đủ tiền.
4. Danh sách Đã thu hiển thị mã hợp đồng. Đăng nhập tài khoản sinh viên đã cấp ở UC03, mở Thông tin lưu trú/Hồ sơ.
5. Hợp đồng thu kỳ tương lai vẫn hiển thị trong UC11 theo trạng thái Còn hạn; dashboard hiện phân biệt Chưa bắt đầu theo ngày, nên Phòng hiện tại có thể vẫn chưa hiển thị trước ngày bắt đầu. Không tự đổi quy tắc dashboard.

## Kiểm chứng

- `tests/payment.php`: 20 ca, gồm luồng UC03 → thanh toán → UC11, giữ tài khoản, không đếm chỗ hai lần, gửi lặp, tài khoản khóa, trùng kỳ, vượt sức chứa, lỗi cuối transaction và thử lại; kiểm tra template/escape/form. Dữ liệu ghi trong bảng TEMPORARY.
- `tests/run.php`: 45 ca, gồm 9 ca thanh toán: khách/ba vai trò khác bị chặn, kế toán được truy cập, thiếu hóa đơn/GET xác nhận/bộ lọc mảng bị chặn.
- UC03 58 ca, UC11 model 6 ca đạt; PHP lint các file tích hợp đạt.
- Chưa xác nhận hóa đơn thật, chưa kiểm tra trực quan trình duyệt hoặc deploy Render. Workspace còn các thay đổi UC11/chatbot/guide/login từ trước; phải chọn phạm vi khi commit.
