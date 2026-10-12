# Những nội dung nghiệp vụ cần sửa và đồng bộ

Ngày đối chiếu: **11/10/2026**. Tài liệu dùng để chia việc sửa đặc tả, sơ đồ trình tự, Use Case, ERD, wireframe và báo cáo của nhóm.

Các nhận xét dưới đây dựa trên mã nguồn hiện hành, metadata CSDL local và việc xem trực tiếp các hình được dẫn trong từng mục. Những chức năng chưa triển khai được đối chiếu theo đặc tả hiện có; không xem mọi chi tiết trong một hình cũ là nghiệp vụ đã được chốt.

Quy ước:

- **Cần sửa:** đã có căn cứ xác định nội dung sai, thiếu hoặc không đồng bộ.
- **Cần chốt:** còn quyết định nghiệp vụ; nhóm phải thống nhất trước khi sửa luồng hoặc triển khai.
- **Cần bổ sung kiểm tra:** luồng cơ bản đúng nhưng chưa thể hiện đầy đủ trường hợp lỗi, quyền hoặc thao tác đồng thời.

## 1. Thứ tự nên xử lý

| Ưu tiên | Nội dung | Kết quả cần có |
| --- | --- | --- |
| 1 | UC03 đang lưu nhầm nội dung hình | Có sơ đồ xét duyệt đúng, thể hiện tài khoản, sinh viên chờ thanh toán và hóa đơn; chưa tạo hợp đồng. |
| 1 | UC15 và UC02 tính giữ chỗ | Thống nhất tính cả đơn đã duyệt chưa có hợp đồng; không giảm SucChua. |
| 1 | Thanh toán đầu tiên và ERD | Có luồng tiếp nối UC03, liên kết hóa đơn/hợp đồng và transaction; ERD có các khóa mới. |
| 1 | Tiền phòng | Dùng đơn giá tháng và tổng học kỳ; bỏ nội dung đơn giá đồng/học kỳ đã cũ. |
| 2 | Mã UC, actor và tác vụ lập hóa đơn | Tổng quan, đặc tả, hình và báo cáo cùng mã, vai trò, bảng dữ liệu. |
| 2 | Các chính sách chưa chốt | Hoàn thiện thanh lý, hạn giữ chỗ, thời hạn hợp đồng và tình huống điện nước. |
| 3 | Wireframe, đường dẫn, Markdown | Hình khớp dữ liệu hiện tại; link mở được; không có phần đặc tả bị cắt hoặc code fence chưa đóng. |

## 2. Sơ đồ trình tự cần sửa

### NV01 - UC03: file hình hiện chứa luồng khác

**Trạng thái: Cần sửa.**

File: [UC03-approval-sequence.png](06-diagrams/sequence/UC03-approval-sequence.png).

Khi mở hình, nội dung là **tra cứu sinh viên**: có `Giao diện tra cứu sinh viên`, `TraCuuSinhVienControl`, tiêu chí tìm kiếm và bảng `SinhVien`. Đây không phải sơ đồ xét duyệt đăng ký. Cần xuất lại đúng hình UC03; đổi tên lifeline trên hình hiện có không đủ vì toàn bộ luồng khác nghiệp vụ.

Luồng mới cần thể hiện:

1. Nhân viên quản lý xem danh sách đơn chờ duyệt, thứ tự ưu tiên giảm dần rồi thời gian gửi/mã đơn tăng dần; chọn xem chi tiết.
2. Chọn duyệt hoặc từ chối; hủy xác nhận không ghi dữ liệu.
3. Khi duyệt: lấy khóa, mở transaction, đọc lại phòng và đơn; kiểm tra đơn còn chờ duyệt, giới tính, sức chứa, hồ sơ/tài khoản trùng, kỳ học và đơn giá.
4. Tạo tài khoản với mật khẩu băm, tạo sinh viên **Chờ thanh toán**, tạo `hoadon` và `hoadontienphong` **Chưa thu**.
5. Cập nhật đơn **Đã duyệt**, gắn `MaHoaDon`; `MaHopDong` vẫn NULL. Commit rồi hiển thị thành công. Chỗ giữ được duy trì.
6. Khi từ chối: lý do bắt buộc, cập nhật **Từ chối** và lý do; không tạo tài khoản/sinh viên/hóa đơn. Đơn không còn được tính giữ chỗ.
7. Khi kiểm tra hoặc ghi lỗi: rollback thay đổi, trả thông báo phù hợp và giải phóng khóa. Không báo thành công trước commit.

Các bảng tham gia: `dondangky`, `dienuutien`, `phong`, `hopdong` (đọc), `taikhoan`, `sinhvien`, `hocky`, `dongia`, `hoadon`, `hoadontienphong`. Có thể tách hình danh sách/chi tiết khỏi hình quyết định để dễ đọc.

Căn cứ: [đặc tả UC03](02-use-cases/UC03-approval.md), [Approval.php](../models/Approval.php), [ghi chú UC03](UC03_IMPLEMENTATION_NOTES.md). Hình trong [bản thảo Chương 2](reports/chapter2/assets/sequence-approval.png) mô tả luồng duyệt thành công, có thể tham khảo; chưa thay thế sơ đồ đầy đủ các nhánh của UC03.

### NV02 - UC15: thiếu bộ lọc và một phần chỗ đang giữ

**Trạng thái: Cần sửa.**

File: [UC15-room-information-sequence.png](06-diagrams/sequence/UC15-room-information-sequence.png).

Hình hiện chỉ gửi `MaToa`, truy vấn phòng theo tòa và đếm đơn **Chờ duyệt**. Cần bổ sung khu Nam/Nữ, tầng và đơn **Đã duyệt có MaHopDong NULL**. Danh sách tòa phải theo giới tính của các phòng; không chỉ lấy toàn bộ tòa rồi cho chọn tùy ý.

```text
Đang ở = COUNT(hopdong: TrangThai = 'Còn hạn')
Đang giữ = COUNT(dondangky: TrangThai = 'Chờ duyệt'
            OR (TrangThai = 'Đã duyệt' AND MaHopDong IS NULL))
Khả dụng = SucChua - Đang ở - Đang giữ
```

Luồng cần có: nhận bộ lọc → kiểm tra định dạng → lấy tòa theo khu → xác định tòa → lấy tầng của tòa → xác định tầng → truy vấn phòng và số chỗ → hiển thị. Mặc định khu Nam, tòa/tầng đầu tiên phù hợp. Khi đổi khu/tòa, chọn lại bộ lọc con phù hợp.

Bổ sung nhánh bộ lọc sai, không có dữ liệu và lỗi CSDL. Phòng đầy vẫn hiển thị; số khả dụng không dương hiển thị 0/Đầy. Trang chỉ đọc dữ liệu, không giữ chỗ hoặc xuất hồ sơ cá nhân. Không cần vẽ một truy vấn riêng cho từng phòng nếu mô tả theo truy vấn tổng hợp hiện hành.

Căn cứ: [Room.php](../models/Room.php), [RoomController.php](../controllers/RoomController.php), [đặc tả UC15](02-use-cases/UC15-room-information.md).

### NV03 - UC02: làm rõ giữ chỗ bằng đơn và kiểm tra trước khi ghi

**Trạng thái: Cần sửa/bổ sung kiểm tra.**

File: [UC02-registration-sequence.png](06-diagrams/sequence/UC02-registration-sequence.png).

Hình có bước `tamGiuCho(phong)` gửi tới `Phong` sau khi tạo đơn. Cần sửa hoặc thêm chú thích rằng **bản ghi đơn hợp lệ chính là căn cứ giữ chỗ**, không giảm `phong.SucChua`, không cập nhật cột `hienco` hay một trạng thái phòng lưu riêng.

Bổ sung kiểm tra MSSV/CCCD đã có hồ sơ sinh viên ở bước nhập và lúc gửi; không chỉ kiểm tra MSSV có đơn chờ duyệt. Lúc gửi phải đọc lại phòng và kiểm tra khả dụng trong cơ chế khóa/transaction; nếu phòng vừa hết chỗ thì không tạo đơn. Thể hiện ba bước nhập thông tin → chọn phòng → xem lại/xác nhận theo giao diện hiện hành. UC02 chỉ trả phòng còn khả dụng, khác UC15 có cả phòng đầy.

Căn cứ: [Registration.php](../models/Registration.php), [đặc tả UC02](02-use-cases/UC02-registration.md).

### NV04 - UC07.2: còn thiếu nhánh thanh toán hóa đơn đầu tiên

**Trạng thái: Cần bổ sung thiết kế; một số chính sách cần chốt.**

File: [UC07.2-payment-confirmation-sequence.png](06-diagrams/sequence/UC07.2-payment-confirmation-sequence.png), [đặc tả UC07.2](02-use-cases/UC07.2-payment-confirmation.md).

Luồng hiện chỉ đổi `hoadon.TrangThai` thành **Đã thu**. Với quy trình mới của UC03, cách xử lý này chưa đủ cho hóa đơn tiền phòng đầu tiên: sinh viên vẫn chờ thanh toán và đơn vẫn giữ chỗ vì chưa có hợp đồng.

Cần mô tả riêng hai trường hợp:

- Hóa đơn thông thường: kiểm tra lại **Chưa thu**, cập nhật trạng thái và `ThoiGianThanhToan`; ngăn xác nhận lặp.
- Hóa đơn đầu tiên gắn với đơn đã duyệt: ngoài xác nhận thu, tạo hợp đồng, gắn `dondangky.MaHopDong`, chuyển sinh viên sang **Đang ở** theo quy trình đã định hướng. Toàn bộ thay đổi phải cùng transaction; lỗi thì rollback, không để hóa đơn đã thu nhưng hợp đồng chưa tạo.

Khi đơn đã có hợp đồng, không tạo thêm hợp đồng hoặc tính giữ chỗ lần nữa. Cần xác định ngày bắt đầu/kết thúc hợp đồng và mốc sinh viên thực sự được ghi nhận đang ở trước khi triển khai. Nếu tách UC thanh toán mô phỏng QR khỏi UC07.2, nhóm phải xác định rõ UC nào thực hiện bước tiếp nối này và đồng bộ các hình; không tự đổi actor của UC07.2 vốn là nhân viên kế toán.

### NV05 - UC10.1 và UC10.2: tách bảng hóa đơn chung và chi tiết

**Trạng thái: Cần sửa/bổ sung.**

Files: [UC10.1](06-diagrams/sequence/UC10.1-room-invoice-generation-sequence.png), [UC10.2](06-diagrams/sequence/UC10.2-utility-invoice-generation-sequence.png).

Hai hình đang đưa nhiều trường chi tiết vào thông điệp tạo hóa đơn gửi tới `HoaDon`, nhưng schema chia các trường đó sang bảng riêng. Có thể giữ thông điệp nghiệp vụ cấp cao nếu kèm sơ đồ hoặc chú thích rõ các thao tác ghi bên dưới:

| Luồng | Bảng bắt buộc thể hiện | Điểm cần đồng bộ |
| --- | --- | --- |
| UC10.1 tiền phòng | `hoadon`, `hoadontienphong`, `thongbao` | Chi tiết chứa MSSV, phòng, học kỳ và đơn giá. Kiểm tra trùng theo MSSV + MaHocKy, bao gồm hóa đơn đầu tiên đã tạo ở UC03. |
| UC10.2 điện nước | `hoadon`, `hoadondiennuoc`, `hoadondiennuocsinhvien`, `thongbao` | Tạo chi tiết phòng/tháng/năm và liên kết sinh viên; không chỉ tạo thông báo cho sinh viên rồi bỏ bảng liên kết. |

Giữ transaction tạo hóa đơn/chi tiết/liên kết/thông báo; commit khi đầy đủ, rollback khi lỗi. Kiểm tra trùng và ràng buộc duy nhất phải phối hợp để hai lần chạy đồng thời không tạo trùng.

UC10.1 phải ghi mốc **10 ngày trước ngày kết thúc kỳ hiện tại**, thu kỳ kế tiếp; tiền theo đơn giá tháng và tháng lẻ theo ngày. UC10.2 ghi mốc **ngày 1 tháng mới**, thu tháng trước; bổ sung chuyển năm khi chạy tháng 1, không tạo kỳ tháng 0. Hình UC10.2 đã có nhánh thiếu chỉ số và bỏ qua phòng: cần đồng bộ nhánh này vào đặc tả, đồng thời chốt cách xử lý lại khi dữ liệu được bổ sung.

### NV06 - UC08: phòng và sinh viên phải xác định ở máy chủ

**Trạng thái: Cần bổ sung kiểm tra.**

File: [UC08-incident-report-sequence.png](06-diagrams/sequence/UC08-incident-report-sequence.png).

Hình lấy phòng theo hợp đồng lúc mở form, nhưng khi gửi lại có `maSinhVien`, `maPhong` đi qua giao diện. Cần thể hiện máy chủ lấy danh tính từ phiên và đọc lại hợp đồng/phòng khi ghi; không coi trường phòng chỉ đọc ở giao diện là đủ bảo vệ. Bổ sung nhánh không có hợp đồng còn hạn hoặc hợp đồng đã thay đổi trong thời gian nhập form. Khi không đủ điều kiện, không tạo phản ánh.

### NV07 - UC09 và UC12: đọc lại trạng thái và giới hạn chủ sở hữu

**Trạng thái: Cần bổ sung kiểm tra.**

- [UC09](06-diagrams/sequence/UC09-incident-processing-sequence.png): trước khi cập nhật, kiểm tra lại phản ánh vẫn **Chờ xử lý**; nhân viên xử lý lấy từ phiên. Ghi `MaNhanVienXuLy`, `ChiPhiSuaChua`, `TrangThai = 'Đã xử lý'` đồng thời. Chi phí 0 hợp lệ; không thêm trạng thái “Đang xử lý” khi schema chưa có.
- [UC12](06-diagrams/sequence/UC12-notification-view-sequence.png): bước lấy chi tiết hiện chỉ ghi `maThongBao`. Cần thể hiện điều kiện **MaThongBao + MSSV của phiên**, để sinh viên không đổi mã và đọc thông báo người khác. Không thêm đã đọc/chưa đọc vì schema không có thuộc tính này.

## 3. Những quyết định nghiệp vụ cần chốt trước khi vẽ lại

### NV08 - Thanh lý hợp đồng chưa có đặc tả đầy đủ

[UC05.2-contract-termination.md](02-use-cases/UC05.2-contract-termination.md) đang trống, trong khi [sơ đồ UC05.2](06-diagrams/sequence/UC05.2-contract-termination-sequence.png) đã đưa ra quy tắc chặn khi có hóa đơn chưa thu, chuyển hợp đồng thành **Đã thanh lý** và sinh viên thành **Đã rời đi**.

Cần thống nhất và ghi vào đặc tả:

- Cho thanh lý trạng thái nào: chỉ Còn hạn hay cả Hết hạn? Hình hiện kiểm tra Đã thanh lý và ghi nhánh Còn hạn, chưa xử lý rõ Hết hạn.
- Hóa đơn nào chặn thanh lý: mọi hóa đơn của sinh viên hay khoản gắn với đợt lưu trú này? Cách xét hóa đơn điện nước phải đi qua bảng liên kết sinh viên.
- Ngày hiệu lực thanh lý, nghĩa vụ còn nợ và có/không hoàn phí.
- Điều kiện chuyển sinh viên Đã rời đi nếu còn hợp đồng khác; không cập nhật mù chỉ từ một hợp đồng được chọn.
- Ghi các thay đổi trong một transaction, chống thanh lý lặp và cập nhật danh sách sau commit.

Các dòng trên là câu hỏi cần quyết định, chưa phải chính sách mới của hệ thống.

### NV09 - Hạn giữ chỗ, thời hạn hợp đồng và bàn giao tài khoản

Hiện UC03 duyệt xong tiếp tục giữ chỗ chờ thanh toán. Cần chốt: hạn thanh toán bao lâu; quá hạn thì đơn/hóa đơn/sinh viên/tài khoản chuyển thế nào; cách giải phóng chỗ; ai có quyền thực hiện và có cho nộp lại không. Các enum hiện tại chưa có trạng thái đơn “Hết hạn” hoặc “Hủy”, nên không tự vẽ thêm các trạng thái này như đã tồn tại.

Thời hạn hợp đồng khi thanh toán cũng chưa được chốt đầy đủ. Việc giao tài khoản cho sinh viên cần xác định cách thực hiện; không vẽ bước gửi Gmail hoặc tích hợp ngân hàng thật khi chưa có nghiệp vụ và triển khai tương ứng.

### NV10 - Hợp đồng “Còn hạn” và hiệu lực theo ngày chưa được dùng thống nhất

Đây là điểm cần thống nhất giữa sơ đồ và các module:

- [Room.php](../models/Room.php) và [Approval.php](../models/Approval.php) tính người đang ở từ hợp đồng có trạng thái **Còn hạn**.
- [StaffDashboard.php](../models/StaffDashboard.php) thống kê phòng có điều kiện thêm ngày hợp đồng và sinh viên **Đang ở**.
- [StudentDashboard.php](../models/StudentDashboard.php) còn diễn giải trạng thái hiển thị **Chưa bắt đầu/Hết hạn** từ ngày, dù đó không phải các giá trị mới đã ghi vào enum hợp đồng.

Cần xác định cách quản lý hợp đồng tới ngày bắt đầu/hết hạn, ai hoặc tác vụ nào cập nhật trạng thái, và ý nghĩa từng chỉ số thống kê. Trong lúc chưa có quyết định mới, sơ đồ UC02/UC03/UC15 phải phản ánh công thức hiện hành; không tự thêm điều kiện ngày vào công thức chỉ vì module khác đang dùng nó. Trạng thái hiển thị suy ra từ ngày không đồng nghĩa trạng thái đã được ghi vào CSDL.

### NV11 - Điện nước và tác vụ theo lịch

Các quyết định cần có: chỉ số mốc cho phòng mới; thiếu tháng liền trước; thay đồng hồ/chỉ số giảm; phòng có sinh viên vào/ra giữa tháng; cách chọn sinh viên liên quan và phân bổ phí; đơn giá lấy theo ngày nào nếu thay đổi giữa kỳ.

Cơ chế chạy Timer cần xác định lịch, chống chạy trùng, xử lý lại khi lỗi và ghi nhận kết quả. Metadata local đã kiểm tra chưa có trigger/event; không vẽ hệ thống như đã có trigger tự lập hóa đơn hoặc tự giải phóng chỗ. Trigger dữ liệu không tự được kích hoạt chỉ vì đến một ngày trong lịch. Việc chọn cơ chế lập lịch là phần cần thiết kế, không phải lý do tự thêm trigger vào CSDL.

### NV12 - UC13: phạm vi người dùng và hỗ trợ AI

[Sơ đồ UC13](06-diagrams/sequence/UC13-ai-assistant-sequence.png), wireframe index và Chương 1 có khách/sinh viên; [sơ đồ Use Case tổng quan](<06-diagrams/use-case/Use Case Overview Diagram/use-case-overview.puml>) chỉ nối sinh viên với UC13. Chưa có file đặc tả UC13 riêng đầy đủ.

Cần bổ sung đặc tả và thống nhất các liên kết actor. Giữ phạm vi hỏi đáp thông tin/hướng dẫn; không mở quyền đọc hồ sơ, hợp đồng, hóa đơn hay ghi nghiệp vụ. Sơ đồ hiện gửi câu hỏi sang dịch vụ AI trước nhánh ngoài phạm vi: cần bổ sung kiểm tra phạm vi/nội dung được phép ở máy chủ trước khi gửi theo thiết kế. Hình có “dịch vụ AI” chưa đủ căn cứ nói hệ thống đã tích hợp dịch vụ thật.

## 4. Use Case, ERD và tài liệu dữ liệu

### NV13 - Sửa mã UC trong tài liệu tổng quan

File: [USE_CASE_OVERVIEW.md](02-use-cases/USE_CASE_OVERVIEW.md).

Từ UC06 đến UC11, bảng tổng quan và các mục phân nhóm/đặt tên file đang lệch đặc tả chi tiết. Nên đồng bộ về bộ mã hiện được dùng trong đặc tả, sequence và wireframe:

| Mã đúng theo đặc tả hiện hành | Chức năng | Actor |
| --- | --- | --- |
| UC06 | Ghi nhận chỉ số điện nước | Nhân viên quản lý KTX |
| UC07.1 / UC07.2 | Tra cứu hóa đơn / Xác nhận thanh toán | Nhân viên kế toán |
| UC08 | Gửi phản ánh sự cố | Sinh viên |
| UC09 | Xử lý phản ánh sự cố | Nhân viên quản lý KTX |
| UC10.1 / UC10.2 | Lập hóa đơn tiền phòng / Điện nước | Timer |
| UC11 | Xem thông tin lưu trú | Sinh viên |

**UC06 hiện giao cho NVQL.** Đặc tả UC06, hình sequence, Use Case `.puml` và wireframe index đang cùng quy định đó. Bản thảo Chương 2 đã ghi nhầm nhân viên kế toán; đã sửa Markdown và dựng [bản Word đồng bộ](reports/chapter2/BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx). Bản Word ban đầu đang được người dùng chỉnh sửa nên được giữ lại; không cần đổi actor của các tài liệu gốc sang kế toán.

### NV14 - ERD thiếu liên kết mới; mô tả đơn giá còn cũ

Files: [ERD.drawio](04-database/ERD.drawio), [ERD.png](04-database/ERD.png), [DATABASE_DESIGN.md](04-database/DATABASE_DESIGN.md).

Cần cập nhật ERD:

- `dondangky.MaHoaDon`: varchar(12), NULL được, UNIQUE, FK tới **hoadontienphong.MaHoaDon**.
- `dondangky.MaHopDong`: varchar(10), NULL được, UNIQUE, FK tới **hopdong.MaHopDong**.
- Cardinality: mỗi đơn có thể chưa có hoặc có một liên kết; một hóa đơn/hợp đồng được liên kết tối đa một đơn bởi UNIQUE. Không vẽ liên kết bắt buộc ngay khi nộp đơn.
- Miền `sinhvien.TrangThaiLuuTru` có **Chờ thanh toán**. `hoadondiennuoc.MaHoaDon` là PK đồng thời FK tới hóa đơn chung; không chỉ ghi PK.
- Giữ MSSV của đơn như thông tin nộp hồ sơ; schema hiện không có FK `dondangky.MSSV → sinhvien.MSSV`, vì sinh viên có thể chưa tồn tại khi nộp đơn.

`DATABASE_DESIGN.md` còn ghi đơn giá **đồng/học kỳ** ở UC03: đổi thành **đồng/tháng**, tổng thu trọn kỳ kế tiếp bằng tổng tiền tháng và phần tháng lẻ. Mức đã chốt 200.000 đồng/sinh viên/tháng; chương trình lấy giá có hiệu lực từ `dongia`, không gán cứng số tiền. Tháng lẻ tính tỷ lệ ngày, tính cả hai ngày biên và làm tròn tổng cuối.

Không dùng dump cũ làm nguồn duy nhất cho ERD: đối chiếu schema hiện hành và [migration UC03](../sql/uc03-approval.sql). Việc migration đã chạy local không chứng minh Aiven đã được cập nhật.

### NV15 - Tập hợp Business Rules và hoàn thiện đặc tả bị thiếu

[BUSINESS_RULES.md](03-business-rules/BUSINESS_RULES.md) đang trống. Cần tập hợp các quy tắc đã chốt về giữ chỗ, ưu tiên 0/2/4/3, xét duyệt, tính tiền, trạng thái, quyền và chống trùng; dẫn lại UC nguồn. Những quyết định NV08–NV12 chưa chốt nên nằm ở danh sách vấn đề mở, không ghi thành quy tắc đã áp dụng.

[UC10.2-utility-invoice-generation.md](02-use-cases/UC10.2-utility-invoice-generation.md) hiện kết thúc ngay ở công thức điện tiêu thụ với code fence chưa đóng. Cần hoàn thiện phần nước, tổng tiền, các bảng ghi, thông báo, chống trùng, thiếu dữ liệu và rollback theo sơ đồ/CSDL; không chỉ thêm dấu đóng fence rồi coi đặc tả đã đủ.

## 5. Wireframe, đường dẫn và hình thức tài liệu

### NV16 - Wireframe UC02, UC03 và UC15

- [UC02 bước chọn phòng](05-ui/wireframes/UC02-registration-step2-room-selection.png): đang có tầng 5 và phòng đầy. Bộ dữ liệu hiện hành có 4 tầng; UC02 chỉ trả phòng khả dụng > 0. Hình cần khớp cách hiển thị hiện hành, dùng đúng tên/số phòng và giải thích chỗ đang giữ.
- [UC15](05-ui/wireframes/UC15-room-information.png): bỏ ghi cố định “5 tầng, 10 phòng/tầng”; hiện mỗi tòa 50 phòng, phân bố 13/13/12/12 trên 4 tầng. Bộ lọc vẫn lấy từ CSDL. Hiển thị riêng đang ở, đang giữ và khả dụng, vì chỉ “5/8 sinh viên” chưa nói được phòng có bao nhiêu chỗ còn đăng ký được.
- [UC03 chi tiết](05-ui/wireframes/UC03-approval-detail.png): bổ sung thông tin kỳ thu, đơn giá tháng, tổng kỳ và kết quả sau duyệt; thể hiện nhập lý do từ chối và trường hợp không có/mất file minh chứng. Có thể dùng màn hình xác nhận/kết quả riêng. Không diễn đạt duyệt xong là đã có hợp đồng.

### NV17 - Wireframe index đang dẫn một số file không có đúng tên

File: [Wireframe-index.md](05-ui/Wireframe-index.md).

| Đường dẫn đang ghi trong index | File thực có | Cách xử lý |
| --- | --- | --- |
| UC06-utility-meter-room-list.png | UC06-utility-meter-room-list.png.png | Sửa link đúng tên hoặc đổi tên file và cập nhật mọi nơi dùng. |
| UC06-utility-meter-entry.png | UC06-utility-meter-entry.png.png | Tương tự. |
| UC07.1-invoice-search.png và UC07.2-payment-confirmation.png | UC07.1-invoice-search and UC07.2-payment-confirmation.png | Dẫn đúng hình ghép hoặc tách thành hai file. |
| UC09-incident-list.png và UC09-incident-detail.png | UC09-incident-list and UC09-incident-detail.png | Dẫn đúng hình ghép hoặc tách thành hai file. |
| UC13-ai-assistant.png | UC13-ai-assistant.png.png | Sửa link hoặc đổi tên và đồng bộ. |
| UC12-notification-detail.png | Chưa có file này | Giữ trạng thái chưa có/cần làm; bổ sung khi hoàn thiện hình. |

Các hình lệch nghiệp vụ nêu trên cần được đánh dấu **Cần rà soát** thay vì coi “Hoàn thành” là xác nhận đã khớp code mới. Trạng thái hoàn thành wireframe không đồng nghĩa hoàn thành chức năng.

### NV18 - Đóng code fence và đồng bộ báo cáo

Các file có số code fence lẻ khi kiểm tra: UC04.1, UC04.2, UC05.1, UC06, UC07.1, UC07.2, UC08, UC09, UC10.1, UC10.2, UC11, UC14, UC16 và Wireframe-index.md. Cần đóng đúng vị trí để quy tắc nghiệp vụ không bị hiển thị như một khối code; đọc phần cuối từng file để phân biệt lỗi định dạng với nội dung chưa viết xong.

Sau khi chốt và sửa hình gốc, đồng bộ bản báo cáo, hình xuất, danh mục hình và tài liệu nguồn DOCX nếu nhóm còn dùng chúng. Các PNG trong báo cáo Chương 2 là hình mới riêng; việc chúng đã đúng không có nghĩa PNG/ERD gốc trong `docs/06-diagrams` và `docs/04-database` đã được thay.

## 6. Checklist nghiệm thu tài liệu sau khi sửa

- [ ] UC03 mở đúng hình xét duyệt, đủ nhánh duyệt/từ chối/hủy/lỗi.
- [ ] UC02/UC03/UC15 dùng cùng công thức chỗ khả dụng và không sửa SucChua.
- [ ] Thanh toán đầu tiên có luồng tạo/gắn hợp đồng, chống lặp và rollback.
- [ ] Giá tháng, tháng lẻ và kỳ kế tiếp thống nhất ở đặc tả, hình, UI và báo cáo.
- [ ] ERD khớp khóa, NULL, UNIQUE và enum của schema hiện hành.
- [ ] UC06 actor NVQL; mã UC06–UC11 đúng ở mọi bảng tổng quan.
- [ ] UC10 thể hiện đúng hóa đơn chung/chi tiết/bảng liên kết và lịch xử lý.
- [ ] Các quyết định chưa chốt được ghi rõ, không tự thêm trạng thái/hoàn phí/tích hợp thật.
- [ ] UC08/UC12 giới hạn dữ liệu theo phiên; UC09 kiểm tra lại trạng thái trước ghi.
- [ ] Link hình mở được, code fence đóng đúng và danh mục phản ánh mức hoàn thiện thật.
- [ ] Phân biệt thiết kế dự kiến với chức năng đã triển khai/đã kiểm thử.

Tài liệu này hướng dẫn công việc cần làm; các sơ đồ gốc và chính sách còn mở chưa được tự sửa trong đợt lập danh sách. Riêng lỗi actor UC06 trong bản thảo Chương 2 đã được sửa lại theo đặc tả hiện hành.
