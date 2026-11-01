Bản thảo Chương 3 — gửi riêng để lấy ý kiến giảng viên hướng dẫn.

# CHƯƠNG 3. KẾT QUẢ VÀ THẢO LUẬN

> Bản thảo phục vụ hoàn thiện báo cáo. Các số liệu kiểm thử có phụ thuộc CSDL phải được chạy lại trên môi trường hiện tại trước khi chốt bản chính thức. Ảnh giao diện và ảnh kết quả chạy thật cần bổ sung sau khi nhóm bật WAMP/MySQL.

> Khi hoàn thiện file Word, hãy tìm các dòng bắt đầu bằng `[CHÈN ẢNH` và thay từng dòng bằng ảnh sản phẩm hoặc ảnh kết quả tương ứng. Không chụp mật khẩu, token QR gốc hoặc dữ liệu cá nhân không cần thiết.

## 3.1. Môi trường triển khai và phạm vi kiểm chứng

### 3.1.1. Môi trường phát triển

Hệ thống quản lý ký túc xá trường Đại học Kanto được xây dựng theo mô hình ứng dụng Web. Nhóm em sử dụng các thành phần chính như sau:

- PHP 8.x và Apache để xử lý yêu cầu Web.
- MySQL làm hệ quản trị cơ sở dữ liệu.
- Dùng PDO và prepared statement để kết nối, truy vấn. Cách này giúp chặn các lệnh SQL độc hại.
- Dùng HTML, CSS, Bootstrap và JavaScript để làm giao diện.
- Chạy local bằng WAMP. Dùng Docker, Render, Aiven MySQL và DBeaver để thử nghiệm triển khai.
- Dùng Git/GitHub để quản lý mã nguồn và theo dõi code.

### 3.1.2. Phạm vi và nguyên tắc kiểm chứng

Chương này chia làm ba mức bằng chứng:

1. Kiểm tra trên code (như PHP lint và các bài test JavaScript).
2. Chạy thử nghiệp vụ với CSDL local.
3. Chạy thử trên mạng hoặc thiết bị thật.

Kết quả tốt ở mức thấp không có nghĩa là cả hệ thống đều chạy tốt ở mức cao. Chạy thử với bảng tạm chỉ chứng minh được luồng đi, không thay thế cho số liệu thực.

Phạm vi nhóm em kiểm chứng gồm:

- Soát lỗi file PHP.
- Logic frontend bằng Node.js.
- Luồng nghiệp vụ bằng các bài test PHP.
- Các route, quyền, kiểm tra dữ liệu đầu vào.
- Test lúc ghi dữ liệu.
- Xem giao diện trên máy tính và điện thoại sau khi bật WAMP và MySQL.

## 3.2. Tổng quan sản phẩm và các chức năng đã triển khai

Hệ thống có các nhóm chức năng chính như: đăng ký, xét duyệt, quản lý sinh viên, hợp đồng, hóa đơn, điện nước, phản ánh sự cố, thông báo, thống kê, thanh toán QR mô phỏng, và tự động gia hạn lưu trú. Bảng dưới đây tóm tắt trạng thái code và các ảnh cần thêm vào báo cáo sau này.

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
| UC18 Gia hạn lưu trú tự động | Đã hoàn thành | Ảnh kết quả test API gia hạn và log hệ thống |

### 3.2.1. Đăng nhập, phân quyền và bảo vệ route

Người dùng truy cập qua file `index.php`. Đăng nhập xong, hệ thống lưu session để ghi nhận tài khoản. Các controller sẽ kiểm tra quyền trước khi cho vào trang quản lý.

Những điểm nhóm em đã test:

- Chưa đăng nhập thì bị đẩy ra ngoài.
- Sai vai trò thì bị từ chối truy cập.
- Thao tác ghi dữ liệu dùng POST, có chống CSRF.
- Dữ liệu hiện lên web được mã hóa tránh lỗi hiển thị. Truy vấn CSDL dùng PDO prepared statement.

### 3.2.2. Đăng ký lưu trú và xét duyệt

Quy trình đăng ký: sinh viên nhập thông tin, kiểm tra đúng sai, chọn phòng, rồi gửi đơn. Hệ thống tự kiểm tra số CCCD/MSSV, diện ưu tiên và xem phòng còn chỗ không. Đơn hợp lệ sẽ được lưu chờ duyệt.

Quy trình xét duyệt: nhân viên xem đơn, đối chiếu các thông tin. Khi duyệt xong, hệ thống tự tạo tài khoản, sinh viên sẽ ở trạng thái chờ thanh toán. Việc này làm chung một lúc (transaction) để nếu lỗi thì không bị lưu dở dang. Cuối cùng, hệ thống gửi email thông báo.

Nhóm em cần bổ sung các ảnh sau:

> [CHÈN ẢNH SẢN PHẨM 3.2: Màn hình đăng nhập và dashboard.]  
> [CHÈN ẢNH SẢN PHẨM 3.3: Form đăng ký lưu trú, bước chọn phòng và thông báo lỗi dữ liệu.]  
> [CHÈN ẢNH SẢN PHẨM 3.4: Danh sách đơn chờ duyệt, chi tiết đơn và kết quả duyệt/từ chối.]

### 3.2.3. Quản lý sinh viên, thông tin lưu trú và hợp đồng

Nhân viên KTX có thể tìm kiếm, xem và cập nhật hồ sơ sinh viên. Tuy nhiên giới tính không cho đổi tùy tiện vì sẽ sai khu vực nam/nữ.

Quản lý hợp đồng cho phép tìm hợp đồng và thanh lý. Quy tắc thanh lý:

- Hợp đồng phải còn hạn mới thanh lý được.
- Nếu sinh viên còn nợ tiền phòng hay điện nước, hệ thống sẽ báo lỗi và không cho thanh lý.
- Có tính tiền phòng hoàn trả lại. Còn tiền điện nước thì không hoàn.
- Lưu lại lịch sử thanh lý vào CSDL an toàn.

> [CHÈN ẢNH SẢN PHẨM 3.5: Màn hình tra cứu và chi tiết sinh viên.]  
> [CHÈN ẢNH SẢN PHẨM 3.6: Danh sách/chi tiết hợp đồng.]  
> [CHÈN ẢNH SẢN PHẨM 3.7: Hộp thoại thanh lý và trường hợp hệ thống chặn do còn công nợ.]

### 3.2.4. Hóa đơn, điện nước và thanh toán QR mô phỏng

Phần này dùng để tra cứu hóa đơn, lọc và xác nhận thanh toán. Với hóa đơn đầu tiên, hệ thống tạo luôn hợp đồng cho sinh viên. Các hóa đơn điện nước hay thu định kỳ sau này chỉ cập nhật chữ "Đã thu".

Tính điện nước bằng cách lấy số tháng này trừ số tháng trước. Tổng tiền chia đều cho các bạn trong phòng.

Quy trình thanh toán mã QR mô phỏng:

- Sinh viên xem hóa đơn, bấm tạo mã QR.
- QR có thời hạn, chỉ dành cho đúng hóa đơn đó.
- Dùng điện thoại quét QR sẽ mở trang giả lập chuyển khoản.
- Quét xong bấm xác nhận, hệ thống báo "Đã thu".
- Nếu mã hết hạn hay sai người, hệ thống sẽ chặn không cho trả.

> [CHÈN ẢNH SẢN PHẨM 3.8: Danh sách hóa đơn và bộ lọc.]  
> [CHÈN ẢNH SẢN PHẨM 3.9: Chi tiết hóa đơn chưa thu và nút tạo QR.]  
> [CHÈN ẢNH SẢN PHẨM 3.10: Mã QR sau khi tạo; che token hoặc thông tin nhạy cảm nếu có.]  
> [CHÈN ẢNH SẢN PHẨM 3.11: Trang xác nhận trên thiết bị thứ hai hoặc màn hình mô phỏng.]  
> [CHÈN ẢNH SẢN PHẨM 3.12: Trạng thái/biên nhận sau khi xác nhận mô phỏng.]

### 3.2.5. Điện nước, phản ánh, thông báo, thống kê và trợ lý AI

Các chức năng còn lại gồm: ghi số điện nước, gửi phản ánh hỏng hóc, xem thông báo từ ban quản lý và thống kê báo cáo. Hệ thống cũng có một trợ lý AI trả lời câu hỏi cơ bản. Trợ lý này không được đụng vào dữ liệu sinh viên, và nếu lỗi mạng thì sẽ báo câu trả lời có sẵn.

> [CHÈN ẢNH SẢN PHẨM 3.13: Màn hình nhập chỉ số điện nước và kết quả lưu.]  
> [CHÈN ẢNH SẢN PHẨM 3.14: Gửi và xử lý phản ánh sự cố.]  
> [CHÈN ẢNH SẢN PHẨM 3.15: Danh sách thông báo hoặc trang thống kê.]  
> [CHÈN ẢNH SẢN PHẨM 3.16: Trợ lý AI trả lời câu hỏi trong phạm vi KTX và trường hợp fallback.]

### 3.2.6. Tự động gia hạn lưu trú (UC18)

Chức năng gia hạn lưu trú tự động giúp hệ thống tự rà soát danh sách sinh viên sắp hết hạn hợp đồng khi chuẩn bị bước sang học kỳ mới:

- Tự động kiểm tra điều kiện: Sinh viên không còn nợ tiền phòng của kỳ hiện tại.
- Nếu đủ điều kiện: Hệ thống tự động tạo hợp đồng mới cho học kỳ kế tiếp trong cùng một transaction, chuyển trạng thái hợp đồng cũ sang đã gia hạn, và gửi thông báo xác nhận vào tài khoản sinh viên.
- Nếu còn nợ tiền phòng: Hệ thống ghi nhận trạng thái từ chối gia hạn, khóa tài khoản sinh viên và tạo thông báo nhắc nợ.
- Sử dụng cơ chế khóa dữ liệu (mutex lock) để đảm bảo nếu có nhiều tiến trình chạy cùng lúc cũng không bị gia hạn trùng lặp.

## 3.3. Một số luồng tích hợp tiêu biểu

| Luồng tích hợp | Các bước chính | Kết quả cần kiểm chứng |
|---|---|---|
| Đăng ký đến tạo hợp đồng | Đăng ký → duyệt đơn → hóa đơn đầu tiên → thanh toán xong → có hợp đồng | Không tạo ra 2 hợp đồng, 2 tài khoản bị trùng |
| Hóa đơn điện nước đến thanh toán | Ghi số → tính tiền → chia tiền ra từng sinh viên → thu lẻ từng bạn | Tính đúng tiền cho mỗi bạn |
| QR mô phỏng | Xem hóa đơn → bấm tạo QR → ra trang quét giả lập → bấm xác nhận → báo "Đã thu" | QR phải được server kiểm tra là đúng và còn hạn |
| Thanh lý hợp đồng | Mở hợp đồng → kiểm tra nợ → bấm thanh lý → lưu lại CSDL | Nợ tiền thì không cho thanh lý |
| Phản ánh sự cố | Sinh viên báo → quản lý nhận thông tin → sửa xong → đổi trạng thái | Phản ánh đúng phòng, đúng sinh viên, lưu đúng trạng thái |
| Gia hạn lưu trú tự động (UC18) | Kích hoạt chu kỳ gia hạn → kiểm tra nợ tiền phòng → gia hạn hợp đồng mới hoặc khóa tài khoản | Gia hạn đúng điều kiện, không trùng lặp, thông báo đầy đủ |

> [CHÈN ẢNH SẢN PHẨM 3.17: Ảnh ghép hoặc sơ đồ minh họa một luồng tích hợp hoàn chỉnh, ưu tiên đăng ký → xét duyệt → thanh toán → hợp đồng.]

## 3.4. Phương pháp và kết quả kiểm thử

### 3.4.1. Kiểm tra cú pháp PHP

Nhóm em đã quét lỗi cú pháp trên 131 file PHP của toàn dự án. Kết quả là 131/131 file đều sạch, không có lỗi đánh máy hay sai cú pháp.

> [CHÈN ẢNH KẾT QUẢ TEST 3.18: Ảnh cửa sổ terminal hiển thị kết quả PHP lint hoặc bảng tổng hợp 131/131 file đạt.]

### 3.4.2. Kiểm tra frontend

Nhóm em viết file `tests/frontend.js` để test frontend. Có 12 trường hợp test, tất cả 12/12 đều đạt. Việc này kiểm tra các hộp thoại, bộ lọc phòng và nút tải lại trang. Nhóm em sẽ chụp thêm ảnh màn hình để làm bằng chứng.

> [CHÈN ẢNH KẾT QUẢ TEST 3.19: Kết quả chạy `tests/frontend.js` hiển thị 12/12 ca đạt.]  
> [CHÈN ẢNH KẾT QUẢ TEST 3.20: Ảnh giao diện desktop/mobile của một chức năng chính, ví dụ QR hoặc đăng ký lưu trú.]

### 3.4.3. Kiểm tra tích hợp và nghiệp vụ

| Bộ kiểm thử | Số ca đạt | Số ca lỗi | Trạng thái hiện tại |
|---|---:|---:|---|
| `tests/run.php` (Core) | 106 | 0 | Đã chạy local với DB |
| Đăng ký và xét duyệt | 30 | 0 | Đã chạy local với DB |
| Mở rộng phòng (rooms) | 16 | 0 | Đã chạy local với DB |
| Xét duyệt mở rộng (approval) | 70 | 0 | Đã chạy local với DB |
| Tự động gia hạn lưu trú (`tests/renewal.php` - UC18) | 13 | 0 | Đã chạy local với DB |

Nhóm em đã chạy tất cả bộ test, tổng cộng đạt **235/235** (106 + 70 + 30 + 16 + 13) trường hợp theo báo cáo tiến độ tuần 8.

> [CHÈN ẢNH KẾT QUẢ TEST 3.21: Kết quả chạy test hiển thị tổng 235/235 đạt.]  
> [CHÈN ẢNH KẾT QUẢ TEST 3.22: Kết quả một nhóm test nghiệp vụ có CSDL, ví dụ đăng ký, hợp đồng, hóa đơn hoặc QR.]

### 3.4.4. Kiểm tra route, quyền và phương thức HTTP

Nhóm em kiểm tra kỹ các link của trang web: ai chưa đăng nhập thì đẩy ra ngoài, sai quyền thì báo lỗi. Bắt buộc dùng POST khi sửa dữ liệu, và check cả mã bảo mật CSRF. Báo cáo trả về mã lỗi 403, 405 rõ ràng.

> [CHÈN ẢNH KẾT QUẢ TEST 3.23: Kết quả kiểm tra route/phân quyền hoặc màn hình thông báo 403/405/CSRF bị từ chối.]

## 3.5. Kết quả sửa lỗi và tối ưu hóa

Khi code, nhóm em tập trung xử lý mấy vấn đề sau:

- Rà soát luồng từ đầu tới cuối để tránh sót nhánh xử lý.
- Luôn dùng PDO tránh lỗi chèn SQL bậy bạ. Dùng transaction để bảo vệ dữ liệu lúc tạo tài khoản hay thanh lý hợp đồng.
- Xử lý rõ luật tính nợ khi sinh viên muốn hủy hợp đồng.
- Chia nhỏ tiền điện nước ra từng người thay vì gộp chung khó đòi.
- Bắt lỗi giao diện ở nút bấm QR, tránh ấn nhiều lần.
- Đảm bảo cơ chế tự động gia hạn hợp đồng có mutex lock chống race-condition.

> [CHÈN ẢNH SẢN PHẨM 3.24: Ảnh một màn hình trước/sau khi sửa lỗi hoặc tối ưu, nếu có minh chứng trực quan.]

## 3.6. Danh sách minh chứng giao diện cần bổ sung

Để chứng minh sản phẩm đã chạy được, nhóm em cần chụp ít nhất các ảnh sau:

1. Đăng nhập và trang chính của các vai trò.
2. Các bước nhập liệu lúc đăng ký phòng.
3. Chỗ duyệt đơn và cái email thông báo kết quả.
4. Chỗ tìm kiếm, sửa sinh viên, xem và hủy hợp đồng.
5. Danh sách hóa đơn, ấn nút thu tiền.
6. Chỗ nhập số điện nước hàng tháng.
7. Mã QR sinh ra, quét thử và trang báo thành công.
8. Gửi lỗi, xem thông báo, xem báo cáo thống kê, xem hướng dẫn.
9. Trợ lý AI đang trả lời câu hỏi KTX.
10. Dòng lệnh báo chạy test thành công.

Nhóm em sẽ tránh chụp mật khẩu hay mã thật của sinh viên vào báo cáo.

### 3.6.1. Bảng kiểm soát ảnh trước khi nộp

| Mã ảnh | Nội dung cần có | Trạng thái/Ghi chú |
|---|---|---|
| 3.1–3.7 | Đăng nhập, dashboard, đăng ký, xét duyệt, sinh viên, hợp đồng | Chưa chèn; bổ sung sau khi chạy WAMP |
| 3.8–3.12 | Hóa đơn, QR, xác nhận và biên nhận mô phỏng | Chưa chèn; che thông tin nhạy cảm |
| 3.13–3.17 | Điện nước, phản ánh, thông báo, thống kê, AI và luồng tích hợp | Chưa chèn; ưu tiên dữ liệu demo rõ |
| 3.18–3.23 | Lint, frontend, test CSDL, route và phân quyền | Chưa chèn; dùng ảnh terminal hoặc trình duyệt |
| 3.24 | Minh chứng sửa lỗi/tối ưu | Chưa chèn; có thể bỏ nếu không có ảnh phù hợp |

## 3.7. Đánh giá mức độ hoàn thành theo kế hoạch tuần 7 và tuần 8

Tới tuần 8, nhóm em đã code xong 18 Use Case và kiểm tra kỹ bằng các bộ test (đạt 235/235). Sản phẩm nhìn chung đáp ứng được khối lượng công việc. Tuy nhiên nhóm em vẫn cần bổ sung thêm đủ ảnh chụp giao diện và chạy thực tế trên máy tính để viết cho xong hẳn Chương 4. Các chức năng tự động (timer) cũng cần test trực tiếp trên server xem có lỗi lầm gì không.

## 3.8. Ưu điểm và hạn chế

### 3.8.1. Ưu điểm

- Code bao phủ được các chức năng hay gặp ở KTX như đóng tiền, đăng ký phòng, tự động gia hạn lưu trú.
- Phân quyền chặt chẽ từng trang.
- Các chỗ quan trọng như ghi dữ liệu đều dùng transaction bảo vệ, không lo lỗi nửa chừng.
- Chức năng thanh toán mã QR khá thú vị và dễ hiểu.
- File code được tách rõ MVC, dễ sửa chữa về sau.

### 3.8.2. Hạn chế

- Phần mềm này nhóm em mới chạy thử bằng code trên máy cá nhân, chưa test áp lực nhiều người vào cùng lúc.
- Tính năng chạy theo giờ (tự lập hóa đơn, gia hạn định kỳ) mới viết script test ở local, chưa thả lên server thật để chạy thử lâu dài.
- QR thanh toán mới là mô phỏng thôi, chưa liên kết thẻ ngân hàng thật.
- Tính năng gửi mail đôi khi bị chặn hoặc vào mục thư rác.

## 3.9. Đánh giá chung

Sản phẩm cơ bản đi được nguyên vòng đời sinh viên KTX: từ lúc nộp đơn đăng ký ở web ngoài, nhân viên duyệt xong có tài khoản, lúc nào trả tiền thì tạo hợp đồng. Hàng tháng có ghi số điện nước thu tiền, đến cuối kỳ có cơ chế tự động xét gia hạn lưu trú. Cuối cùng, hệ thống kiểm tra và báo các bài test local đều Passed 100%. Bước tiếp theo nhóm em sẽ đem chạy thử ở server thật để xem còn lỗi vặt gì không, từ đó đóng gói đề tài lại.

## 3.10. Kết luận chương

Chương 3 trình bày các kết quả mà nhóm em làm được: giao diện, tính năng, test luồng và test code (235 ca đều Pass). Nhóm em cũng tự nhận xét những ưu điểm và những cái chưa làm được (như chưa test server). Việc có đủ các kết quả ở chương này giúp nhóm em có cơ sở viết tiếp Chương 4 và kết thúc báo cáo đồ án.
