# USE CASE OVERVIEW

## 1. Tổng quan

Tài liệu này mô tả tổng quan các Use Case của **Hệ thống Quản lý Ký túc xá trường Đại học Kanto**.

Mục đích:
- Liệt kê các tác nhân của hệ thống.
- Liệt kê các Use Case chính.
- Xác định tác nhân thực hiện từng Use Case.
- Làm tài liệu tra cứu nhanh cho thành viên nhóm và Codex trước khi đọc đặc tả chi tiết từng Use Case.

Chi tiết luồng xử lý, tiền điều kiện, hậu điều kiện và các quy tắc nghiệp vụ được mô tả trong các file Use Case riêng.

---

## 2. Tác nhân

| Tác nhân | Mô tả |
|---|---|
| Khách vãng lai | Người chưa có tài khoản trong hệ thống, có thể xem thông tin công khai và đăng ký lưu trú. |
| Sinh viên | Sinh viên đã được xét duyệt lưu trú và có tài khoản sử dụng hệ thống. |
| Nhân viên quản lý KTX | Nhân viên phụ trách đơn đăng ký, sinh viên, hợp đồng, phòng, điện nước và phản ánh sự cố. |
| Nhân viên kế toán | Nhân viên phụ trách quản lý và xác nhận thanh toán hóa đơn. |
| Quản lý KTX | Người theo dõi thống kê và báo cáo tổng hợp của ký túc xá. |
| Timer | Tác nhân hệ thống thực hiện các nghiệp vụ tự động theo thời gian. |

---

## 3. Danh sách Use Case

| Mã | Tên Use Case | Tác nhân chính |
|---|---|---|
| UC01 | Đăng nhập | Sinh viên, Nhân viên quản lý KTX, Nhân viên kế toán, Quản lý KTX |
| UC02 | Đăng ký lưu trú | Khách vãng lai |
| UC03 | Xét duyệt đơn đăng ký | Nhân viên quản lý KTX |
| UC04 | Quản lý thông tin sinh viên | Nhân viên quản lý KTX |
| UC04.1 | Tra cứu thông tin sinh viên | Nhân viên quản lý KTX |
| UC04.2 | Cập nhật thông tin sinh viên | Nhân viên quản lý KTX |
| UC05 | Quản lý hợp đồng lưu trú | Nhân viên quản lý KTX |
| UC05.1 | Tra cứu hợp đồng | Nhân viên quản lý KTX |
| UC05.2 | Thanh lý hợp đồng | Nhân viên quản lý KTX |
| UC06 | Xem thông tin lưu trú | Sinh viên |
| UC07 | Ghi nhận chỉ số điện nước | Nhân viên quản lý KTX |
| UC08 | Lập hóa đơn điện nước | Timer |
| UC09 | Quản lý hóa đơn | Nhân viên kế toán |
| UC10 | Gửi phản ánh sự cố | Sinh viên |
| UC11 | Xử lý phản ánh sự cố | Nhân viên quản lý KTX |
| UC12 | Xem thông báo | Sinh viên |
| UC13 | Sử dụng trợ lý AI | Sinh viên |
| UC14 | Xem thống kê và báo cáo | Quản lý KTX |
| UC15 | Xem thông tin phòng | Khách vãng lai |
| UC16 | Xem hướng dẫn | Khách vãng lai |

---

## 4. Phân nhóm Use Case

### 4.1. Xác thực và phân quyền

- UC01 - Đăng nhập

Các chức năng yêu cầu đăng nhập sử dụng Session và kiểm tra quyền theo vai trò.

Đăng xuất được triển khai như một chức năng dùng chung của người dùng đã đăng nhập và hiện không tách thành một Use Case riêng.

---

### 4.2. Khách vãng lai

- UC02 - Đăng ký lưu trú
- UC15 - Xem thông tin phòng
- UC16 - Xem hướng dẫn

Khách vãng lai không cần tài khoản để sử dụng các chức năng trên.

---

### 4.3. Sinh viên

- UC06 - Xem thông tin lưu trú
- UC10 - Gửi phản ánh sự cố
- UC12 - Xem thông báo
- UC13 - Sử dụng trợ lý AI

Các chức năng này chỉ được sử dụng sau khi sinh viên đăng nhập thành công.

---

### 4.4. Nhân viên quản lý KTX

- UC03 - Xét duyệt đơn đăng ký
- UC04 - Quản lý thông tin sinh viên
- UC05 - Quản lý hợp đồng lưu trú
- UC07 - Ghi nhận chỉ số điện nước
- UC11 - Xử lý phản ánh sự cố

---

### 4.5. Nhân viên kế toán

- UC09 - Quản lý hóa đơn

Nhân viên kế toán thực hiện tra cứu và xác nhận thanh toán hóa đơn.

---

### 4.6. Quản lý KTX

- UC14 - Xem thống kê và báo cáo

Use Case này cung cấp các số liệu tổng hợp phục vụ công tác quản lý.

---

### 4.7. Timer

- UC08 - Lập hóa đơn điện nước

Timer là tác nhân hệ thống thực hiện nghiệp vụ tự động theo thời gian, không phải người dùng đăng nhập.

---

## 5. Một số Use Case có phân rã

### UC04 - Quản lý thông tin sinh viên

- Tra cứu sinh viên.
- Cập nhật thông tin sinh viên.

### UC08 - Lập hóa đơn điện nước

Việc lập hóa đơn được thực hiện dựa trên chỉ số điện nước và kỳ tính hóa đơn theo nghiệp vụ của hệ thống.

### UC13 - Sử dụng trợ lý AI

Trợ lý AI chỉ hỗ trợ:
- Hỏi đáp thông tin chung về ký túc xá.
- Hướng dẫn sử dụng hệ thống.

Trợ lý AI không được:
- Truy xuất thông tin cá nhân của sinh viên.
- Tra cứu hợp đồng cá nhân.
- Tra cứu hóa đơn cá nhân.
- Phân tích hoặc xử lý phản ánh sự cố.

---

## 6. Quy tắc chung khi triển khai Use Case

Trước khi triển khai một Use Case cần kiểm tra:

1. Đặc tả Use Case tương ứng.
2. BUSINESS_RULES.md.
3. Wireframe của chức năng.
4. Schema CSDL hiện tại.
5. Source code hiện tại.

Không tự ý:
- thêm chức năng ngoài phạm vi;
- đổi tên trạng thái nghiệp vụ;
- đổi actor;
- thay đổi luồng nghiệp vụ đã chốt;
- thay đổi cấu trúc CSDL chỉ để phù hợp với code.

Nếu tài liệu Use Case, Wireframe, Business Rule, CSDL và code có mâu thuẫn thì phải báo lại trước khi thay đổi.

---

## 7. Tài liệu liên quan

- `../01-project-scope/SCOPE_AND_REQUIREMENTS.md`
- `../03-business-rules/BUSINESS_RULES.md`
- `../04-database/DATABASE_DESIGN.md`
- `../05-ui/WIREFRAME_INDEX.md`

Các đặc tả Use Case chi tiết nằm cùng thư mục `02-use-cases/`.

---

## 8. Quy ước tên file đặc tả

Tên file nên sử dụng:

- `UC01-login.md`
- `UC02-registration.md`
- `UC03-approval.md`
- `UC04-student-management.md`
- `UC05-contract-management.md`
- `UC06-accommodation-info.md`
- `UC07-utility-meter.md`
- `UC08-utility-invoice.md`
- `UC09-invoice-management.md`
- `UC10-incident-report.md`
- `UC11-incident-processing.md`
- `UC12-notifications.md`
- `UC13-ai-assistant.md`
- `UC14-statistics-report.md`
- `UC15-room-info.md`
- `UC16-guide.md`