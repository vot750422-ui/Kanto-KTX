# CHƯƠNG 3. KẾT QUẢ VÀ THẢO LUẬN

> Bản thảo phục vụ hoàn thiện báo cáo. Các số liệu kiểm thử có phụ thuộc CSDL phải được chạy lại trên môi trường hiện tại trước khi chốt bản chính thức. Ảnh giao diện và ảnh kết quả chạy thật cần bổ sung sau khi nhóm bật WAMP/MySQL.

> Khi hoàn thiện file Word, hãy tìm các dòng bắt đầu bằng `[CHÈN ẢNH` và thay từng dòng bằng ảnh sản phẩm hoặc ảnh kết quả tương ứng. Không chụp mật khẩu, token QR gốc hoặc dữ liệu cá nhân không cần thiết.

## 3.1. Môi trường triển khai và phạm vi kiểm chứng

### 3.1.1. Môi trường phát triển

Hệ thống quản lý ký túc xá trường Đại học Kanto được xây dựng theo mô hình ứng dụng Web với các thành phần chính:

- PHP 8.x và Apache để xử lý yêu cầu Web.
- MySQL làm hệ quản trị cơ sở dữ liệu.
- PDO và prepared statement để kết nối, truy vấn và hạn chế nguy cơ chèn lệnh SQL.
- HTML, CSS, Bootstrap và JavaScript để xây dựng giao diện.
- WAMP là môi trường chạy local chính; Docker, Render, Aiven MySQL và DBeaver được dùng trong phạm vi triển khai/thử nghiệm phù hợp.
- Git/GitHub được dùng để quản lý mã nguồn và theo dõi thay đổi.

### 3.1.2. Phạm vi và nguyên tắc kiểm chứng

Chương này phân biệt ba mức bằng chứng:

1. Kết quả kiểm tra tĩnh hoặc kiểm tra độc lập, chẳng hạn PHP lint và các ca JavaScript.
2. Kết quả kiểm thử nghiệp vụ có sử dụng CSDL local.
3. Kết quả kiểm tra trực tiếp trên môi trường triển khai hoặc thiết bị thật.

Không dùng kết quả của mức thứ nhất để khẳng định toàn bộ hệ thống đã ổn định ở mức thứ hai hoặc thứ ba. Các test dùng bảng tạm chỉ chứng minh logic trong phạm vi test, không thay thế cho việc kiểm tra dữ liệu nghiệp vụ thật.

Phạm vi kiểm chứng gồm:

- Cú pháp các file PHP.
- Logic frontend bằng Node.js.
- Luồng nghiệp vụ bằng các test PHP.
- Route, phân quyền, phương thức HTTP, kiểm tra dữ liệu và CSRF khi có áp dụng.
- Giao dịch, khóa bản ghi và điều kiện nghiệp vụ trước khi ghi dữ liệu.
- Giao diện desktop/mobile sau khi WAMP và MySQL hoạt động.

## 3.2. Tổng quan sản phẩm và các chức năng đã triển khai

Hệ thống đã hình thành các nhóm chức năng chính từ đăng ký lưu trú, xét duyệt, quản lý sinh viên, hợp đồng, hóa đơn, điện nước, phản ánh sự cố, thông báo, thống kê đến thanh toán QR mô phỏng. Bảng dưới đây tóm tắt trạng thái mã nguồn và minh chứng cần bổ sung vào bản báo cáo chính thức.

> [CHÈN ẢNH SẢN PHẨM 3.1: Ảnh tổng quan trang chủ hoặc dashboard của hệ thống sau khi đăng nhập. Ghi rõ vai trò và môi trường chạy.]

| Use Case/chức năng | Trạng thái mã nguồn | Minh chứng cần đưa vào báo cáo |
|---|---|---|
| UC01 Đăng nhập | Đã có | Ảnh đăng nhập, dashboard và phân quyền |
| UC02 Đăng ký lưu trú | Đã có | Ảnh các bước nhập thông tin, chọn phòng và gửi đơn |
| UC03 Xét duyệt | Đã có | Ảnh danh sách, chi tiết, thông báo và email kết quả |
| UC04.1/04.2 Quản lý sinh viên | Đã có | Ảnh tra cứu, chi tiết và cập nhật thông tin được phép |
| UC05.1/05.2 Hợp đồng | Đã có | Ảnh tra cứu, chi tiết và thanh lý theo điều kiện công nợ |
| UC06 Ghi nhận chỉ số điện nước | Đã có | Ảnh chọn phòng, nhập chỉ số và kết quả lưu |
| UC07.1/07.2 Hóa đơn và xác nhận thanh toán | Đã có | Ảnh danh sách, bộ lọc và hộp thoại xác nhận |
| UC08/UC09 Phản ánh sự cố | Đã có | Ảnh gửi phản ánh, danh sách và xử lý phản ánh |
| UC10.1/UC10.2 Lập hóa đơn định kỳ | Có script và test local | Kết quả test; ghi rõ scheduler chưa được xác minh đầy đủ |
| UC11 Thông tin lưu trú | Đã có | Ảnh hồ sơ, phòng và hợp đồng |
| UC12 Thông báo | Đã có | Ảnh danh sách và chi tiết thông báo |
| UC13 Trợ lý AI | Đã có | Ảnh hỏi đáp, giới hạn dữ liệu và cơ chế fallback |
| UC14 Thống kê và báo cáo | Đã có | Ảnh bộ lọc, bảng và số liệu |
| UC15 Thông tin phòng | Đã có | Ảnh danh sách phòng và bộ lọc |
| UC16 Hướng dẫn | Đã có | Ảnh trang hướng dẫn |
| UC17 QR thanh toán mô phỏng | Đã có | Ảnh tạo QR, trang xác nhận và biên nhận mô phỏng |

### 3.2.1. Đăng nhập, phân quyền và bảo vệ route

Người dùng truy cập hệ thống thông qua các route được điều phối tại `index.php`. Sau khi đăng nhập, session lưu thông tin cần thiết để xác định tài khoản và vai trò. Các controller kiểm tra vai trò trước khi cho phép truy cập chức năng quản lý.

Các điểm cần minh họa:

- Người dùng chưa đăng nhập được chuyển tới trang đăng nhập.
- Vai trò không phù hợp bị từ chối khi truy cập route quản lý.
- Các thao tác ghi dữ liệu dùng POST và có kiểm tra CSRF ở những luồng áp dụng.
- Dữ liệu hiển thị trên view được escape; truy vấn có input dùng PDO prepared statement.

### 3.2.2. Đăng ký lưu trú và xét duyệt

Luồng đăng ký lưu trú gồm nhập thông tin, kiểm tra dữ liệu, chọn phòng, kiểm tra điều kiện phòng và gửi đơn. Hệ thống kiểm tra MSSV/CCCD, điều kiện phòng, diện ưu tiên và cơ chế giữ chỗ. Khi đơn hợp lệ được gửi, đơn được lưu với trạng thái chờ xử lý.

Luồng xét duyệt tiếp tục kiểm tra trạng thái đơn, học kỳ, đơn giá và khả năng tạo dữ liệu liên quan. Việc tạo tài khoản, hóa đơn hoặc cập nhật trạng thái phải được đặt trong transaction phù hợp để tránh tạo dữ liệu dở dang. Email kết quả xét duyệt được xem là một bước thông báo; việc SMTP chấp nhận thư không đồng nghĩa thư đã đến Inbox.

Minh chứng cần bổ sung gồm ảnh form đăng ký, lỗi dữ liệu, danh sách đơn chờ duyệt, màn hình xác nhận và thông báo kết quả.

> [CHÈN ẢNH SẢN PHẨM 3.2: Màn hình đăng nhập và dashboard.]  
> [CHÈN ẢNH SẢN PHẨM 3.3: Form đăng ký lưu trú, bước chọn phòng và thông báo lỗi dữ liệu.]  
> [CHÈN ẢNH SẢN PHẨM 3.4: Danh sách đơn chờ duyệt, chi tiết đơn và kết quả duyệt/từ chối.]

### 3.2.3. Quản lý sinh viên, thông tin lưu trú và hợp đồng

Nhân viên quản lý KTX có thể tra cứu sinh viên, xem chi tiết và cập nhật các trường được phép. Thông tin giới tính trong hồ sơ cập nhật cần được giữ theo quy tắc nghiệp vụ hiện hành, không tự ý cho phép thay đổi nếu làm sai lệch khu vực, tòa hoặc phòng.

UC05.1 hỗ trợ tra cứu hợp đồng theo mã hợp đồng, MSSV, họ tên, phòng và trạng thái. UC05.2 xử lý thanh lý hợp đồng theo điều kiện công nợ:

- Chỉ hợp đồng còn hạn mới được xem xét thanh lý.
- Nếu sinh viên còn hóa đơn tiền phòng hoặc phần điện nước chưa thu, hệ thống từ chối thanh lý và trả thông báo công nợ.
- Tiền hoàn trả chỉ tính trên tiền phòng đã thu nhưng chưa sử dụng theo số ngày còn lại; không hoàn tiền điện nước.
- Việc thanh lý ghi nhận kết quả trong transaction và cập nhật trạng thái lưu trú khi sinh viên không còn hợp đồng còn hạn khác.

Các điểm trên cần được minh họa bằng ảnh chi tiết hợp đồng, hộp thoại xác nhận, trường hợp bị chặn do công nợ và kết quả thanh lý hợp lệ.

> [CHÈN ẢNH SẢN PHẨM 3.5: Màn hình tra cứu và chi tiết sinh viên.]  
> [CHÈN ẢNH SẢN PHẨM 3.6: Danh sách/chi tiết hợp đồng.]  
> [CHÈN ẢNH SẢN PHẨM 3.7: Hộp thoại thanh lý và trường hợp hệ thống chặn do còn công nợ.]

### 3.2.4. Hóa đơn, điện nước và thanh toán QR mô phỏng

Nhóm chức năng hóa đơn gồm tra cứu, lọc, xem chi tiết và xác nhận thanh toán. Hóa đơn tiền phòng đầu tiên có thể liên kết với đơn đăng ký và tạo hợp đồng trong cùng transaction. Hóa đơn tiền phòng định kỳ và hóa đơn điện nước chỉ cập nhật trạng thái thu theo đúng loại nghiệp vụ, không tự tạo hợp đồng mới.

Đối với điện nước, hệ thống kiểm tra chỉ số tháng hiện tại, chỉ số tháng trước, tính hợp lệ của số liệu, đơn giá và sinh viên có hợp đồng trong tháng. Tổng tiền điện nước của phòng được phân bổ thành phần phải thu riêng cho sinh viên; mỗi sinh viên thanh toán phần của mình.

UC17 là luồng thanh toán QR mô phỏng:

- Sinh viên tạo mã QR từ trang chi tiết hóa đơn.
- Mã có thời hạn và gắn với hóa đơn, tài khoản hoặc phần phải thu tương ứng.
- Quét mã chỉ mở trang mô phỏng; không được xem là giao dịch ngân hàng thật.
- Chỉ thao tác xác nhận hợp lệ mới cập nhật trạng thái thanh toán.
- Token hết hạn, đã dùng, sai chủ sở hữu, sai số tiền hoặc hóa đơn đã thu phải bị chặn.

Minh chứng cần đưa vào gồm ảnh danh sách hóa đơn, chi tiết hóa đơn chưa thu, QR sau khi tạo, trang xác nhận và trạng thái sau xác nhận.

> [CHÈN ẢNH SẢN PHẨM 3.8: Danh sách hóa đơn và bộ lọc.]  
> [CHÈN ẢNH SẢN PHẨM 3.9: Chi tiết hóa đơn chưa thu và nút tạo QR.]  
> [CHÈN ẢNH SẢN PHẨM 3.10: Mã QR sau khi tạo; che token hoặc thông tin nhạy cảm nếu có.]  
> [CHÈN ẢNH SẢN PHẨM 3.11: Trang xác nhận trên thiết bị thứ hai hoặc màn hình mô phỏng.]  
> [CHÈN ẢNH SẢN PHẨM 3.12: Trạng thái/biên nhận sau khi xác nhận mô phỏng.]

### 3.2.5. Điện nước, phản ánh, thông báo, thống kê và trợ lý AI

Hệ thống có chức năng nhập chỉ số điện nước, tạo hóa đơn định kỳ, gửi và xử lý phản ánh sự cố, hiển thị thông báo, thống kê và cung cấp trang hướng dẫn. Trợ lý AI chỉ trả lời trong phạm vi thông tin được chuẩn bị; không được cấp quyền ghi dữ liệu hoặc truy cập thông tin cá nhân ngoài phạm vi cho phép. Khi mô hình chính không hoạt động, cơ chế fallback hoặc câu trả lời được kiểm soát phải được ghi nhận rõ.

> [CHÈN ẢNH SẢN PHẨM 3.13: Màn hình nhập chỉ số điện nước và kết quả lưu.]  
> [CHÈN ẢNH SẢN PHẨM 3.14: Gửi và xử lý phản ánh sự cố.]  
> [CHÈN ẢNH SẢN PHẨM 3.15: Danh sách thông báo hoặc trang thống kê.]  
> [CHÈN ẢNH SẢN PHẨM 3.16: Trợ lý AI trả lời câu hỏi trong phạm vi KTX và trường hợp fallback.]

## 3.3. Một số luồng tích hợp tiêu biểu

| Luồng tích hợp | Các bước chính | Kết quả cần kiểm chứng |
|---|---|---|
| Đăng ký đến tạo hợp đồng | Đăng ký → xét duyệt → hóa đơn đầu tiên → xác nhận thanh toán → hợp đồng | Không tạo trùng tài khoản, đơn, hóa đơn hoặc hợp đồng |
| Hóa đơn điện nước đến thanh toán | Nhập chỉ số → kiểm tra chỉ số/đơn giá → tạo hóa đơn → phân bổ phần phải thu → thu từng sinh viên | Số tiền từng sinh viên đúng và hóa đơn cha chỉ hoàn tất khi đủ phần |
| QR mô phỏng | Xem hóa đơn → tạo QR → mở trang công khai → xác nhận → biên nhận | Token, trạng thái hóa đơn và số tiền được kiểm tra lại phía server |
| Thanh lý hợp đồng | Xem hợp đồng → kiểm tra công nợ → xác nhận → ghi thanh lý | Có công nợ thì không thanh lý; hợp lệ thì cập nhật đúng trạng thái |
| Phản ánh sự cố | Sinh viên gửi → nhân viên tiếp nhận → cập nhật trạng thái → hiển thị kết quả | Phản ánh thuộc đúng người dùng và trạng thái được cập nhật đúng |

> [CHÈN ẢNH SẢN PHẨM 3.17: Ảnh ghép hoặc sơ đồ minh họa một luồng tích hợp hoàn chỉnh, ưu tiên đăng ký → xét duyệt → thanh toán → hợp đồng.]

## 3.4. Phương pháp và kết quả kiểm thử

### 3.4.1. Kiểm tra cú pháp PHP

Trong lượt kiểm tra hiện tại, tổng số file PHP đã chạy lint là 131 file. Kết quả ghi nhận 131/131 file không có lỗi cú pháp. Đây là bằng chứng về khả năng phân tích cú pháp, không thay thế cho kiểm thử logic và dữ liệu.

> [CHÈN ẢNH KẾT QUẢ TEST 3.18: Ảnh cửa sổ terminal hiển thị kết quả PHP lint hoặc bảng tổng hợp 131/131 file đạt.]

### 3.4.2. Kiểm tra frontend

File `tests/frontend.js` đã được chạy với kết quả hiện tại là 12/12 ca đạt. Các nội dung kiểm tra gồm modal xác nhận, bộ lọc phòng, tự động tải chỉ số điện nước và luồng QR. Cần bổ sung ảnh chụp trình duyệt để chứng minh bố cục và trạng thái trực quan trên desktop/mobile.

> [CHÈN ẢNH KẾT QUẢ TEST 3.19: Kết quả chạy `tests/frontend.js` hiển thị 12/12 ca đạt.]  
> [CHÈN ẢNH KẾT QUẢ TEST 3.20: Ảnh giao diện desktop/mobile của một chức năng chính, ví dụ QR hoặc đăng ký lưu trú.]

### 3.4.3. Kiểm tra tích hợp và nghiệp vụ

| Bộ kiểm thử | Số ca đạt | Số ca lỗi | Trạng thái hiện tại |
|---|---:|---:|---|
| `tests/run.php` | Chưa chốt trong lượt hiện tại | Chưa kết luận | Cần bật WAMP/MySQL |
| Đăng ký và xét duyệt | Chờ chạy lại | Chưa kết luận | Cần CSDL local |
| Hợp đồng và thanh lý | Chờ chạy lại | Chưa kết luận | Cần CSDL local |
| Hóa đơn và thanh toán | Chờ chạy lại | Chưa kết luận | Cần CSDL local |
| Điện nước, phản ánh và thống kê | Chờ chạy lại | Chưa kết luận | Cần CSDL local |

Không sử dụng số liệu lịch sử trong tài liệu bàn giao để thay thế số liệu kiểm thử hiện tại. Sau khi bật WAMP/MySQL, cần chạy lại bộ test, ghi tổng số PASS/FAIL, chụp kết quả và cập nhật bảng này.

> [CHÈN ẢNH KẾT QUẢ TEST 3.21: Kết quả chạy `tests/run.php` sau khi bật WAMP/MySQL.]  
> [CHÈN ẢNH KẾT QUẢ TEST 3.22: Kết quả một nhóm test nghiệp vụ có CSDL, ví dụ đăng ký, hợp đồng, hóa đơn hoặc QR.]

### 3.4.4. Kiểm tra route, quyền và phương thức HTTP

Đối với mỗi route quan trọng, cần kiểm tra tối thiểu các trường hợp: khách chưa đăng nhập, đúng vai trò, sai vai trò, phương thức HTTP không hợp lệ, thiếu hoặc sai CSRF và dữ liệu đầu vào không hợp lệ. Kết quả cần ghi theo mã HTTP hoặc thông báo giao diện, không chỉ dựa vào việc trang có trả về HTTP 200.

> [CHÈN ẢNH KẾT QUẢ TEST 3.23: Kết quả kiểm tra route/phân quyền hoặc màn hình thông báo 403/405/CSRF bị từ chối.]

## 3.5. Kết quả sửa lỗi và tối ưu hóa

Trong quá trình hoàn thiện, nhóm tập trung vào các nội dung sau:

- Rà soát luồng Route → Controller → Model → View để phát hiện nhánh xử lý thiếu hoặc không đồng nhất.
- Giữ prepared statement, transaction, khóa bản ghi và kiểm tra quyền trong các thao tác nhạy cảm.
- Làm rõ điều kiện công nợ trước khi thanh lý hợp đồng.
- Duy trì cơ chế phân bổ tiền điện nước theo từng sinh viên thay vì coi toàn bộ hóa đơn phòng là một khoản phải thu chung.
- Kiểm tra lại modal xác nhận, bộ lọc, luồng tạo QR và các thao tác chống gửi lặp ở frontend.
- Tách rõ kết quả kiểm thử local, script timer và kiểm tra deployment; không trình bày scheduler hoặc email là hoàn tất nếu chưa có log tương ứng.

> [CHÈN ẢNH SẢN PHẨM 3.24: Ảnh một màn hình trước/sau khi sửa lỗi hoặc tối ưu, nếu có minh chứng trực quan.]

## 3.6. Danh sách minh chứng giao diện cần bổ sung

Để Chương 3 đủ sức thuyết phục, nhóm cần chụp và chú thích tối thiểu các màn hình sau:

1. Đăng nhập và dashboard của từng vai trò chính.
2. Các bước đăng ký lưu trú, chọn phòng và thông báo lỗi.
3. Danh sách/chi tiết xét duyệt và email hoặc hàng đợi thông báo.
4. Tra cứu sinh viên, hợp đồng và màn hình thanh lý.
5. Danh sách hóa đơn, bộ lọc, xác nhận thanh toán và kết quả sau thu.
6. Nhập chỉ số điện nước và kết quả tạo hóa đơn/phân bổ.
7. Tạo QR, trang xác nhận và biên nhận mô phỏng.
8. Phản ánh sự cố, thông báo, thống kê, thông tin phòng và hướng dẫn.
9. Trợ lý AI với câu hỏi hợp lệ, câu hỏi ngoài phạm vi và trường hợp fallback.
10. Kết quả test hoặc log chạy script timer nếu đã xác minh được.

Mỗi ảnh nên có số hình, tên hình, vai trò đang đăng nhập, dữ liệu đầu vào chính và kết quả quan sát được. Không chụp mật khẩu, token gốc hoặc thông tin cá nhân không cần thiết.

## 3.7.1. Bảng kiểm soát ảnh trước khi nộp

| Mã ảnh | Nội dung cần có | Trạng thái/Ghi chú |
|---|---|---|
| 3.1–3.7 | Đăng nhập, dashboard, đăng ký, xét duyệt, sinh viên, hợp đồng | Chưa chèn; bổ sung sau khi chạy WAMP |
| 3.8–3.12 | Hóa đơn, QR, xác nhận và biên nhận mô phỏng | Chưa chèn; che thông tin nhạy cảm |
| 3.13–3.17 | Điện nước, phản ánh, thông báo, thống kê, AI và luồng tích hợp | Chưa chèn; ưu tiên dữ liệu demo rõ |
| 3.18–3.23 | Lint, frontend, test CSDL, route và phân quyền | Chưa chèn; dùng ảnh terminal hoặc trình duyệt |
| 3.24 | Minh chứng sửa lỗi/tối ưu | Chưa chèn; có thể bỏ nếu không có ảnh phù hợp |

## 3.7. Đánh giá mức độ hoàn thành theo kế hoạch tuần 7 và tuần 8

Theo hướng dẫn chung, tuần 7 yêu cầu hoàn thiện tối thiểu khoảng 80% chức năng, tích hợp hệ thống và bắt đầu Chương 3. Mã nguồn hiện tại đã có phạm vi chức năng rộng, gồm 17 nhóm Use Case/chức năng được liệt kê trong bảng mục 3.2. Bản thảo Chương 3 cũng đã có cấu trúc kết quả, kiểm thử, hạn chế và minh chứng.

Tuy nhiên, “đã có mã nguồn” không đồng nghĩa “đã được kiểm chứng đầy đủ”. Để kết luận đạt mục tiêu tuần 7 và ổn định ở tuần 8, nhóm vẫn cần:

- Chạy lại test cần MySQL/WAMP và ghi số liệu hiện tại.
- Bổ sung ảnh giao diện thực tế.
- Đối chiếu mã Use Case giữa tài liệu tổng quan, đặc tả và mã nguồn.
- Xác minh scheduler/timer và email trên môi trường tương ứng nếu muốn đưa vào phần kết quả hoàn tất.
- Kiểm tra lại các luồng tích hợp từ đăng ký đến thanh toán và từ điện nước đến phân bổ công nợ.

## 3.8. Ưu điểm và hạn chế

### 3.8.1. Ưu điểm

- Phạm vi chức năng bao phủ nhiều nghiệp vụ chính của ký túc xá.
- Luồng phân quyền được đặt gần route/controller và có thể kiểm tra theo vai trò.
- Các nghiệp vụ có ràng buộc dữ liệu quan trọng được xử lý bằng transaction, prepared statement và kiểm tra trạng thái.
- Luồng điện nước và thanh toán QR mô phỏng đã được tách thành các bước có thể kiểm thử riêng.
- Chương trình có khả năng tiếp tục mở rộng do đã phân tách controller, model, view và script hỗ trợ.

### 3.8.2. Hạn chế

- Chưa có đủ bằng chứng kiểm tra đồng thời nhiều tiến trình trên dữ liệu thật.
- Tác vụ định kỳ mới được kiểm tra ở mức code/script và test local; scheduler triển khai chưa được xác minh đầy đủ.
- QR chỉ là mô phỏng thanh toán, chưa kết nối ngân hàng hoặc webhook thật.
- Việc SMTP tiếp nhận thư chưa thể dùng để khẳng định thư đã tới Inbox nếu chưa kiểm tra log/provider.
- Chưa hoàn thiện toàn bộ Chương 4, phụ lục, tài liệu tham khảo và gói source code cuối cùng.

## 3.9. Đánh giá chung

Hệ thống đã hình thành các luồng chính từ đăng ký, xét duyệt, lưu trú, hợp đồng, hóa đơn, điện nước, phản ánh đến thanh toán QR mô phỏng. Kết quả kiểm tra cú pháp PHP và frontend hiện tại đạt yêu cầu trong lượt kiểm tra đã thực hiện. Việc tổ chức mã nguồn và các lớp bảo vệ giúp hệ thống có cơ sở để tiếp tục kiểm thử tích hợp.

Mức độ ổn định toàn hệ thống chưa nên kết luận chỉ từ lint hoặc test frontend. Kết luận chính thức cần dựa trên test có CSDL, kiểm tra trực quan, kiểm tra các luồng tích hợp và bằng chứng deployment tương ứng. Đây là nội dung cần hoàn thành trước khi đóng gói báo cáo cuối.

## 3.10. Kết luận chương

Chương này trình bày môi trường kiểm chứng, các chức năng đã triển khai, những luồng tích hợp tiêu biểu, kết quả kiểm thử, các thay đổi sửa lỗi và đánh giá ưu điểm/hạn chế của hệ thống. Các kết quả chưa được xác minh trên môi trường chạy thật được giữ ở trạng thái chờ kết luận để tránh đánh đồng kiểm tra tĩnh hoặc bảng tạm với kiểm chứng nghiệp vụ thực tế. Sau khi bổ sung số liệu test CSDL và ảnh giao diện, Chương 3 có thể được dùng làm nền cho Chương 4, phần kết luận và hướng phát triển.
