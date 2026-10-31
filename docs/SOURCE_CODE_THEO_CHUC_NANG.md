# Bản đồ source code theo chức năng — Kanto-KTX

Ngày đối chiếu: 18/10/2026. Phạm vi: source ứng dụng hiện có trong workspace, bao gồm chatbot local. Đây là tài liệu tra cứu code, không phải xác nhận mọi chức năng đã được triển khai lên máy chủ.

## 1. Cách đọc source

Luồng thông thường: **Trình duyệt → index.php → Controller → Model/CSDL → View**.

- Controller nhận yêu cầu, kiểm tra dữ liệu và điều phối các bước.
- Model truy vấn CSDL và thực hiện nghiệp vụ.
- View tạo HTML; CSS định dạng giao diện; JavaScript xử lý tương tác trên trình duyệt.
- Một số dashboard gọi model trực tiếp trong file view, không có controller riêng.
- Các đường dẫn dưới đây tính từ thư mục gốc repository. File dùng chung được liệt kê tại mục 11.

### Lưu ý mã Use Case

Bảng `docs/02-use-cases/USE_CASE_OVERVIEW.md` hiện dùng mã mới, trong khi tên tài liệu chi tiết và chú thích code/test còn dùng mã cũ. Tài liệu này lấy **tên chức năng** làm căn cứ, ghi rõ khác biệt để tránh nhầm:

| Chức năng | Mã trong tổng quan hiện tại | Mã/tên tài liệu cũ |
|---|---|---|
| Xem thông tin lưu trú | UC06 | UC11-accommodation-info.md |
| Ghi chỉ số điện nước | UC07 | UC06-utility-meter.md |
| Lập hóa đơn điện nước | UC08 | UC10.2-utility-invoice-generation.md |
| Quản lý hóa đơn/thanh toán | UC09 | UC07.1-invoice-search.md, UC07.2-payment-confirmation.md |
| Gửi phản ánh | UC10 | UC08-incident-report.md |
| Xử lý phản ánh | UC11 | UC09-incident-processing.md |

Không tự đổi mã trong code hoặc đặc tả trong lượt lập bảng này.

## 2. Trang chủ công khai

**Tác nhân:** khách vãng lai. **Đường dẫn:** `index.php`.

| File | Tác dụng |
|---|---|
| `index.php` | Điểm vào ứng dụng, phân nhánh theo action; hiển thị trang chủ nếu không vào một nhánh chức năng. |
| `includes/header.php` | Logo và menu công khai: đăng ký, thông tin phòng, hướng dẫn, đăng nhập. |
| `assets/css/style.css` | Kiểu hiển thị chung và trang chủ. |
| `assets/images/logo.png` | Logo Kanto dùng trên giao diện. |

Trang chủ có nạp `includes/chatbot.php`; phần chatbot được mô tả riêng ở mục 10.

## 3. Đăng ký lưu trú — UC02

**Tác nhân:** khách vãng lai. **Route:** `index.php?action=register` (GET/POST).

**Luồng:** nhập thông tin → chọn phòng → xem lại → gửi đơn → chờ duyệt.

| File | Tác dụng |
|---|---|
| `controllers/RegistrationController.php` | `show()` điều khiển ba bước, lưu nháp vào session, nhận ảnh minh chứng; `validate()` kiểm tra dữ liệu nhập. |
| `models/Registration.php` | Lấy diện ưu tiên, kiểm tra MSSV/CCCD, gọi dữ liệu phòng; `submit()` kiểm tra lại và lưu đơn trong transaction. |
| `models/Room.php` | Lấy phòng đúng giới tính và còn chỗ; dùng chung cách tính chỗ với trang xem phòng. |
| `views/registration/form.php` | Form của cả ba bước, thông báo lỗi và kết quả gửi đơn. |
| `assets/js/registration.js` | Lọc thẻ phòng theo tòa/tầng và bỏ lựa chọn phòng bị ẩn. |
| `assets/css/registration.css` | Bố cục form, tiến trình và thẻ phòng. |
| `tests/registration.php` | Kiểm tra dữ liệu, các bước, gửi đơn, trùng hồ sơ và rollback. |
| `tests/rooms.php` | Kiểm tra dữ liệu phòng, sức chứa và sự thống nhất với đăng ký. |

**CSDL:** đọc `dienuutien`, `sinhvien`, dữ liệu phòng; ghi `dondangky`. Ảnh được lưu tại thư mục `storage/registration/`, trong đơn lưu đường dẫn. Chưa tạo tài khoản hoặc hợp đồng tại bước này.

**Điểm nên đọc:** `RegistrationController::show()` → `Registration::submit()` → `Room::availableRoom()`.

## 4. Xem thông tin phòng — UC15

**Tác nhân:** khách vãng lai. **Route:** `index.php?action=rooms` (GET).

**Luồng:** chọn khu Nam/Nữ → tòa → tầng → xem trạng thái phòng.

| File | Tác dụng |
|---|---|
| `controllers/RoomController.php` | Nhận và kiểm tra bộ lọc, chọn tòa/tầng, lấy dữ liệu cho view. |
| `models/Room.php` | `buildings()`, `floors()`, `rooms()` truy vấn danh sách; `queryRooms()` tính số chỗ. |
| `views/rooms/information.php` | Hiển thị bộ lọc, sức chứa, chỗ đang giữ và trạng thái đầy/còn chỗ. |
| `assets/css/room-information.css` | Giao diện bộ lọc và thẻ phòng. |
| `tests/room-information.php` | Kiểm tra trang phòng, bộ lọc, dữ liệu hiển thị và tính chỉ đọc. |
| `tests/rooms.php` | Kiểm tra dữ liệu và công thức chỗ khả dụng. |

**CSDL:** `toanha`, `phong`, `hopdong`, `dondangky`. Chỗ khả dụng = sức chứa − hợp đồng Còn hạn − đơn Chờ duyệt − đơn Đã duyệt chưa liên kết hợp đồng. Xem phòng không giữ chỗ; bộ lọc dùng GET tải lại trang.

## 5. Xem hướng dẫn — UC16

**Tác nhân:** khách vãng lai. **Route:** `index.php?action=guide` (GET).

| File | Tác dụng |
|---|---|
| `controllers/GuideController.php` | Chỉ nhận GET và nạp trang hướng dẫn. |
| `views/guide/information.php` | Nội dung các bước đăng ký lưu trú và liên kết liên quan. |
| `assets/css/guide.css` | Trình bày nội dung hướng dẫn. |
| `tests/guide.php` | Kiểm tra route, liên kết và tính chỉ đọc. |

Đây là nội dung tĩnh, không ghi CSDL hay thay đổi bản nháp đăng ký.

## 6. Đăng nhập, phân quyền và đăng xuất

**Tác nhân:** sinh viên, NVQL KTX, kế toán, quản lý KTX.

**Route:** `action=login` (GET/POST); `action=logout` (POST).

| File | Tác dụng |
|---|---|
| `controllers/AuthController.php` | Nhận thông tin đăng nhập, kiểm tra tài khoản/mật khẩu, tạo session và chuyển tới dashboard theo vai trò. |
| `models/User.php` | Truy vấn thông tin tài khoản phục vụ xác thực. |
| `views/Auth/login.php` | Form đăng nhập; đây là cách viết hoa đường dẫn được Git theo dõi. |
| `assets/css/login.css` | Giao diện đăng nhập. |
| `middleware/Auth.php` | Khởi tạo/quản lý session và `requireAuth()` kiểm tra tài khoản đang đăng nhập. |
| `middleware/Role.php` | `requireRole()` kiểm tra vai trò; `dashboardForRole()` xác định trang đích. |
| `controllers/logout.php` | Kiểm tra POST/token, xóa phiên đăng nhập và chuyển hướng. |
| `includes/logout.php` | Form Đăng xuất dùng chung với biểu tượng đăng xuất. |
| `assets/css/logout.css` | Kiểu hiển thị nút đăng xuất. |
| `tests/run.php`, `tests/worker.php` | Bộ kiểm thử đăng nhập, session, quyền truy cập và route. |

**CSDL:** `taikhoan`. Mật khẩu được kiểm tra theo bản băm. File hệ thống trên Windows có thể hiển thị thư mục `auth` khác cách viết hoa trong Git; cần chú ý khi triển khai Linux.

## 7. Xét duyệt đơn — UC03

**Tác nhân:** Nhân viên quản lý KTX.

**Route:** `approval` (danh sách), `approval-detail` (chi tiết), `approval-proof` (ảnh), `approval-approve` (POST duyệt), `approval-reject` (POST từ chối).

| File | Tác dụng |
|---|---|
| `controllers/ApprovalController.php` | `handle()` phân nhánh thao tác, kiểm tra request/token, trả ảnh minh chứng và thông báo kết quả. |
| `models/Approval.php` | `pending()` xếp đơn theo ưu tiên/thời gian; `find()` lấy chi tiết; `approve()` duyệt; `reject()` từ chối. |
| `models/Approval.php` — `billingContext()`, `roomTotal()` | Xác định học kỳ kế tiếp, đơn giá tháng và tổng tiền phòng. |
| `models/Approval.php` — `process()` | Gom các thay đổi vào transaction, khóa dữ liệu và rollback khi có lỗi. |
| `models/StaffDashboard.php` | Lấy tên nhân viên cho header. |
| `views/approval/layout.php` | Khung trang xét duyệt, menu tài khoản và điều hướng. |
| `views/approval/list.php` | Bảng đơn chờ duyệt và phân trang. |
| `views/approval/detail.php` | Hồ sơ, ảnh minh chứng và hộp thoại duyệt/từ chối. |
| `views/approval/invoice-fields.php` | Thông tin học kỳ, đơn giá và tổng tiền trong hộp thoại duyệt. |
| `assets/js/approval.js` | Mở/đóng hộp thoại, hạn chế gửi lặp. |
| `assets/css/approval.css` | Giao diện xét duyệt. |
| `tests/approval.php` | Kiểm tra duyệt/từ chối, tính tiền, dữ liệu trùng và rollback. |

**Khi duyệt:** tạo `taikhoan`, `sinhvien` Chờ thanh toán, `hoadon` Chưa thu và `hoadontienphong`; cập nhật `dondangky` Đã duyệt. Đọc thêm `hocky`, `dongia`, `phong`, `hopdong`, `dienuutien`. **Chưa tạo hợp đồng.**

**Khi từ chối:** cập nhật trạng thái và lý do; đơn không còn được tính là giữ chỗ.

## 8. Quản lý hóa đơn và xác nhận thanh toán — UC09 (code cũ UC07)

**Tác nhân:** Nhân viên kế toán. **Route:** `payments` (GET), `payment-confirm` (POST).

| File | Tác dụng |
|---|---|
| `controllers/PaymentController.php` | Kiểm tra quyền kế toán, bộ lọc, token và yêu cầu xác nhận; gọi model, hiển thị kết quả. |
| `models/Payment.php` | `listing()` tra cứu hóa đơn; `confirm()` xác nhận mọi hóa đơn, đồng thời hoàn tất liên kết lưu trú riêng cho tiền phòng đầu tiên. |
| `models/StaffDashboard.php` | Lấy tên kế toán và thống kê hóa đơn cho dashboard. |
| `views/payments/list.php` | Bộ lọc, bảng hóa đơn, phân trang và hộp thoại xác nhận. |
| `assets/css/payments.css` | Định dạng trang hóa đơn. |
| `assets/js/approval.js` | Tương tác hộp thoại dùng chung. |
| `tests/payment.php` | Kiểm tra tra cứu, xác nhận, hợp đồng, chống xác nhận lặp và rollback. |
| `tests/run.php`, `tests/worker.php` | Kiểm tra quyền/route thanh toán. |

**CSDL:** danh sách đọc hóa đơn tiền phòng/điện nước và các liên kết sinh viên, phòng. Khi xác nhận tiền phòng đầu tiên: tạo `hopdong`, liên kết `dondangky.MaHopDong`, chuyển `sinhvien` sang Đang ở, cập nhật `hoadon` Đã thu và thời gian thu. Giữ nguyên tài khoản cấp ở bước duyệt.

**Quy tắc hiện tại:** kế toán xác nhận mọi hóa đơn `Chưa thu`; tiền phòng đầu tiên tạo hợp đồng, tiền phòng định kỳ và điện nước chỉ cập nhật trạng thái `Đã thu`. Gia hạn hợp đồng chưa thuộc luồng này.

## 9. Xem thông tin lưu trú — UC06 (code cũ UC11)

**Tác nhân:** Sinh viên. **Route:** `index.php?action=accommodation` (GET).

| File | Tác dụng |
|---|---|
| `controllers/AccommodationController.php` | Kiểm tra quyền sinh viên và lấy thông tin theo tài khoản trong session. |
| `models/Accommodation.php` | `forAccount()` lấy hồ sơ, diện ưu tiên, hợp đồng, phòng và tòa của chính sinh viên. |
| `views/student/accommodation.php` | Hiển thị hồ sơ/lưu trú, xử lý trường hợp chưa có hồ sơ hoặc hợp đồng. |
| `includes/student-header.php` | Header sinh viên dùng chung với dashboard. |
| `assets/css/accommodation.css` | Kiểu hiển thị thông tin lưu trú. |
| `assets/css/student-dashboard.css` | Bố cục chung cho giao diện sinh viên. |
| `tests/accommodation.php` | Kiểm tra đọc đúng hồ sơ, chọn hợp đồng và không thay đổi dữ liệu. |
| `tests/run.php`, `tests/worker.php` | Kiểm tra quyền, route và trạng thái hiển thị. |

**CSDL:** `sinhvien`, `dienuutien`, `hopdong`, `phong`, `toanha`. Không nhận MSSV từ URL để chọn người cần xem; dùng tài khoản đăng nhập.

## 10. Trợ lý AI — UC13 (chức năng còn local)

**Tác nhân:** khách vãng lai/sinh viên. **Endpoint:** `index.php?action=chatbot-send` (POST).

**Luồng:** mở cửa sổ chat → JavaScript gửi câu hỏi → controller → model Gemini và nguồn phòng → JSON → hiển thị câu trả lời.

| File | Tác dụng |
|---|---|
| `includes/chatbot.php` | Nút mở chat, khung hội thoại, form và token. |
| `assets/js/chatbot.js` | Gửi câu hỏi, hiển thị tin nhắn, trạng thái tải và lỗi. |
| `assets/css/chatbot.css` | Giao diện cửa sổ chat. |
| `controllers/ChatbotController.php` | Kiểm tra request, câu hỏi, token/tần suất; trả JSON. |
| `models/Chatbot.php` | Tạo hướng dẫn cho AI, gọi Gemini và xử lý phản hồi. |
| `models/ChatbotRoomSource.php` | Đọc snapshot số liệu phòng công khai, không cung cấp hồ sơ cá nhân. |
| `models/Room.php` | Dữ liệu phòng và cách tính chỗ dùng chung. |
| `config/connection.php` | Kết nối riêng phục vụ đọc snapshot. |
| `tests/chatbot.php` | Kiểm tra xử lý câu hỏi, phản hồi và tình huống lỗi bằng kiểm thử. |
| `tests/chatbot-rooms.php` | Kiểm tra nguồn số liệu phòng công khai. |

Cần cấu hình dịch vụ trên máy chủ để gọi AI thật; việc có code không đồng nghĩa đã triển khai hoặc API hiện hoạt động.

## 11. Dashboard và thành phần dùng chung

| File | Tác dụng |
|---|---|
| `views/student/Dashboard.php` | Tổng quan sinh viên: phòng được bố trí, hợp đồng, hóa đơn/phản ánh. |
| `models/StudentDashboard.php` | Truy vấn dữ liệu tổng quan của sinh viên đăng nhập. |
| `views/nvql/Dashboard.php` | Tổng quan dành cho nhân viên quản lý KTX. |
| `views/ketoan/Dashboard.php` | Tổng quan hóa đơn cho kế toán. |
| `views/quanly/Dashboard.php` | Các thẻ số liệu tổng quan cho quản lý KTX. |
| `models/StaffDashboard.php` | `summary()` thống kê nghiệp vụ; `invoiceSummary()` thống kê hóa đơn; `nameForAccount()` lấy tên nhân viên. |
| `assets/css/staff-dashboard.css` | Bố cục header và thẻ thống kê của nhân viên/quản lý. |
| `assets/css/student-dashboard.css` | Bố cục dashboard sinh viên. |
| `includes/navbar.php` | Sidebar theo vai trò, liên kết trang đã có và đánh dấu mục hiện tại. |
| `assets/css/navbar.css` | Giao diện sidebar/menu điều hướng. |
| `includes/account-menu.php` | Menu tài khoản dùng chung cho 4 vai trò: avatar, tên/vai trò và chỉ có Đăng xuất. |
| `assets/css/account-menu.css` | Kiểu hiển thị avatar, menu thả xuống và màn hình nhỏ. |
| `includes/student-header.php` | Logo, vùng thao tác và menu tài khoản trên các trang sinh viên. |
| `includes/head.php` | Khai báo phần đầu trang dùng chung như favicon. |
| `config/app.php` | `appUrl()` tạo đường dẫn theo vị trí cài đặt ứng dụng. |
| `config/connection.php` | Factory PDO và tái sử dụng kết nối trong cùng request. |
| `config/db.php` | Cung cấp kết nối `$pdo`, xử lý lỗi kết nối. |
| `config/test-db.php` | Công cụ kiểm tra kết nối, giới hạn CLI/localhost; không phải trang nghiệp vụ. |
| `tests/frontend.js` | Kiểm tra lọc phòng và tương tác hộp thoại bằng DOM mô phỏng. |

## 12. Chức năng chưa có module nghiệp vụ nối vào ứng dụng chính

Các mục sau có đặc tả hoặc menu nhưng chưa tìm thấy controller/model/view và route tương ứng trong cây ứng dụng chính đã kiểm tra. Tài liệu hoặc code tham khảo của đối tác không được xem là chức năng đã tích hợp.

| Chức năng | Phần hiện có / giới hạn |
|---|---|
| Quản lý sinh viên: tra cứu/cập nhật (UC04) | Có đặc tả UC04.1/UC04.2; chưa có module quản lý riêng. `Accommodation.php` là sinh viên tự xem, không thay thế chức năng này. |
| Quản lý hợp đồng: tra cứu/thanh lý (UC05) | Có đặc tả UC05.1/UC05.2; `Payment.php` tạo hợp đồng khi thu tiền nhưng chưa thay thế module tra cứu/thanh lý. |
| Ghi chỉ số điện nước (UC07 mới) | Có `UC06-utility-meter.md`; chưa có màn hình/controller ghi chỉ số trong ứng dụng chính. |
| Lập hóa đơn tự động (UC10.1/UC10.2) | `RoomInvoiceGenerator.php` và `UtilityInvoiceGenerator.php` xử lý nghiệp vụ; các script CLI là điểm gọi cho Task Scheduler. Việc đăng ký/chạy task thật vẫn phải xác minh riêng trên máy triển khai. |
| Gửi phản ánh (UC10 mới) | Có `UC08-incident-report.md`; chưa có module gửi phản ánh. |
| Xử lý phản ánh (UC11 mới) | Có `UC09-incident-processing.md`; chưa có module xử lý phản ánh. |
| Xem thông báo (UC12) | Có đặc tả; nút thông báo sinh viên đang vô hiệu hóa. |
| Thống kê/báo cáo đầy đủ (UC14) | Có thẻ thống kê dashboard; chưa có module báo cáo chi tiết/xuất báo cáo riêng. |

## 13. Thứ tự đọc để hiểu luồng demo

1. `index.php`: xác định route và controller được gọi.
2. `RegistrationController.php` → `Registration.php` → `Room.php`: đăng ký và giữ chỗ.
3. `RoomController.php` → `views/rooms/information.php`: xem thông tin phòng.
4. `AuthController.php` → `User.php` → `middleware/Role.php`: đăng nhập và quyền.
5. `ApprovalController.php` → `Approval.php`: duyệt đơn, tạo hồ sơ/tài khoản/hóa đơn.
6. `PaymentController.php` → `Payment.php`: xác nhận tiền phòng và tạo hợp đồng.
7. `AccommodationController.php` → `Accommodation.php`: sinh viên xem thông tin lưu trú.
8. Mở view tương ứng để xem biến từ controller được đưa lên giao diện như thế nào.

Tài liệu được tổng hợp bằng đọc source; lượt này không thay đổi nghiệp vụ, CSDL hoặc chạy lại kiểm thử chức năng.
