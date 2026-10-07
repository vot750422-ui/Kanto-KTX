# WIREFRAME_INDEX - Danh mục Wireframe

## 1. Mục đích

Tài liệu này dùng để quản lý và liên kết các wireframe của Hệ thống Quản lý Ký túc xá Kanto với các Use Case tương ứng.

Wireframe thể hiện bố cục và các thành phần chính của giao diện.

Khi triển khai giao diện, cần đối chiếu:

1. Use Case tương ứng.
2. Wireframe.
3. Thiết kế cơ sở dữ liệu hiện tại.
4. Mã nguồn hiện tại.

Nếu Wireframe mâu thuẫn với Use Case thì không tự ý thay đổi nghiệp vụ. Cần kiểm tra lại tài liệu Use Case trước khi triển khai.

---

## 2. Quy ước trạng thái

- `Hoàn thành`: Wireframe đã được chốt.
- `Cần rà soát`: Đã có Wireframe nhưng còn điểm cần kiểm tra.
- `Chưa có`: Chưa tạo Wireframe.
- `Không cần UI`: Nghiệp vụ tự động, không có màn hình thao tác trực tiếp.

---

## 3. Danh mục Wireframe

| UC | Chức năng | Tác nhân | Màn hình | File Wireframe | Trạng thái |
|---|---|---|---|---|---|
| UC01 | Đăng nhập | Sinh viên, NVQL, Kế toán, Quản lý KTX | Đăng nhập | `wireframes/UC01-login.png` | Hoàn thành |
| UC02 | Đăng ký lưu trú | Khách vãng lai | Bước 1 - Nhập thông tin đăng ký | `wireframes/UC02-registration-step1-personal-info.png` | Hoàn thành |
| UC02 | Đăng ký lưu trú | Khách vãng lai | Bước 2 - Chọn phòng mong muốn | `wireframes/UC02-registration-step2-room-selection.png` | Hoàn thành |
| UC02 | Đăng ký lưu trú | Khách vãng lai | Bước 3 - Xem lại và xác nhận thông tin | `wireframes/UC02-registration-step3-review-confirmation.png` | Hoàn thành |
| UC03 | Xét duyệt đơn đăng ký | NVQL | Danh sách đơn đăng ký | `wireframes/UC03-approval-list.png` | Hoàn thành |
| UC03 | Xét duyệt đơn đăng ký | NVQL | Chi tiết đơn đăng ký | `wireframes/UC03-approval-detail.png` | Hoàn thành |
| UC04.1 | Tra cứu sinh viên | NVQL | Danh sách / Tra cứu sinh viên | `wireframes/UC04.1-student-search.png` | Hoàn thành |
| UC04.2 | Cập nhật thông tin sinh viên | NVQL | Chi tiết / Cập nhật sinh viên | `wireframes/UC04.2-student-update.png` | Hoàn thành |
| UC05.1 | Tra cứu hợp đồng | NVQL | Tra cứu hợp đồng | `wireframes/UC05.1-contract-search.png` | Hoàn thành |
| UC05.2 | Thanh lý hợp đồng | NVQL | Chi tiết hợp đồng | `wireframes/UC05.2-contract-termination.png` | Hoàn thành |
| UC06 | Ghi nhận chỉ số điện nước | NVQL | Danh sách phòng cần ghi chỉ số | `wireframes/UC06-utility-meter-room-list.png` | Hoàn thành |
| UC06 | Ghi nhận chỉ số điện nước | NVQL | Nhập chỉ số điện nước của phòng | `wireframes/UC06-utility-meter-entry.png` | Hoàn thành |
| UC07.1 | Tra cứu hóa đơn | Nhân viên kế toán | Tra cứu hóa đơn | `wireframes/UC07.1-invoice-search.png` | Hoàn thành |
| UC07.2 | Xác nhận thanh toán | Nhân viên kế toán | Tra cứu hóa đơn / Xác nhận thanh toán | `wireframes/UC07.2-payment-confirmation.png` | Hoàn thành |
| UC08 | Gửi phản ánh sự cố | Sinh viên | Gửi phản ánh sự cố | `wireframes/UC08-incident-report.png` | Hoàn thành |
| UC09 | Xử lý phản ánh sự cố | NVQL | Danh sách phản ánh | `wireframes/UC09-incident-list.png` | Hoàn thành |
| UC09 | Xử lý phản ánh sự cố | NVQL | Chi tiết xử lý phản ánh | `wireframes/UC09-incident-detail.png` | Hoàn thành |
| UC10.1 | Lập hóa đơn tiền phòng | Timer | Không có giao diện thao tác | — | Không cần UI |
| UC10.2 | Lập hóa đơn điện nước | Timer | Không có giao diện thao tác | — | Không cần UI |
| UC11 | Xem thông tin lưu trú | Sinh viên | Hồ sơ / Thông tin lưu trú | `wireframes/UC11-accommodation-info.png` | Hoàn thành |
| UC12 | Xem thông báo | Sinh viên | Danh sách thông báo | `wireframes/UC12-notifications.png` | Hoàn thành |
| UC12 | Xem thông báo | Sinh viên | Chi tiết thông báo | `wireframes/UC12-notification-detail.png` | Chưa Hoàn thành | hiện tại tôi chưa vẽ wire đọc chi tiết
| UC13 | Sử dụng trợ lý AI | Khách vãng lai, Sinh viên | Trợ lý AI | `wireframes/UC13-ai-assistant.png` | Hoàn thành |
| UC14 | Xem thống kê và báo cáo | Quản lý KTX | Thống kê và báo cáo | `wireframes/UC14-statistics-report.png` | Hoàn thành |
| UC15 | Xem thông tin phòng | Khách vãng lai | Thông tin phòng | `wireframes/UC15-room-information.png` | Hoàn thành |
| UC16 | Xem hướng dẫn | Khách vãng lai | Hướng dẫn | `wireframes/UC16-guide.png` | Hoàn thành |

---

## 4. Quy tắc liên kết Wireframe với Use Case

### UI-01 - Wireframe phải tuân theo Use Case

Wireframe không được tự thêm:

- chức năng;
- trường dữ liệu;
- nút thao tác;
- trạng thái nghiệp vụ;

nếu chưa được quy định hoặc thống nhất trong Use Case.

### UI-02 - Một Use Case có thể có nhiều màn hình

Ví dụ:

```text
UC03
├── Danh sách đơn đăng ký
└── Chi tiết đơn đăng ký