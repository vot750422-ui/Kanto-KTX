# CHƯƠNG 3
# KẾT QUẢ VÀ THẢO LUẬN

Chương này trình bày sản phẩm của đề tài “Hệ thống quản lý ký túc xá trường Đại học Kanto”, các chức năng đã cài đặt, giao diện sử dụng, kết quả thử nghiệm và đánh giá ưu điểm, hạn chế. Kết quả được tổng hợp từ mã nguồn hiện có và đợt kiểm thử local ngày 31/10/2026. Hệ thống đã có các nhóm nghiệp vụ chính phục vụ đăng ký lưu trú, quản lý sinh viên, hợp đồng, hóa đơn và các hoạt động hỗ trợ trong ký túc xá.

> GHI CHÚ HOÀN THIỆN: Các vị trí [CHÈN ẢNH] là nơi bổ sung ảnh chụp sản phẩm chạy thực tế. Thay dòng ghi chú bằng ảnh và giữ lại chú thích Hình 3.x bên dưới. Các mục [BỔ SUNG KẾT QUẢ] chỉ được điền sau khi thực hiện thử nghiệm tương ứng. Xóa ghi chú này trước khi nộp bản chính thức.

## 3.1. Sản phẩm phần mềm thực tế

### 3.1.1. Tổng quan sản phẩm

Sản phẩm là một ứng dụng Web hỗ trợ quản lý ký túc xá. Người sử dụng truy cập qua trình duyệt để thực hiện công việc theo vai trò được cấp. Dữ liệu được lưu tập trung trong MySQL, cho phép các chức năng đăng ký, xét duyệt, lưu trú và thanh toán sử dụng chung thông tin sinh viên, phòng, hợp đồng và hóa đơn.

Giao diện công khai cung cấp trang chủ, thông tin phòng, hướng dẫn và đăng ký lưu trú. Sau khi đăng nhập, người dùng được chuyển đến giao diện phù hợp với vai trò. Các màn hình quản lý tổ chức thông tin dưới dạng danh sách, bộ lọc, trang chi tiết và biểu mẫu xử lý. Cách tổ chức này hỗ trợ người dùng tìm bản ghi cần thao tác, xem dữ liệu liên quan và theo dõi kết quả xử lý.

Sản phẩm hiện sử dụng thanh toán QR mô phỏng trong phạm vi đồ án. Các lệnh lập hóa đơn định kỳ đã được cài đặt và có kiểm thử riêng; việc vận hành tự động theo lịch cần được xác nhận bằng nhật ký chạy trên môi trường triển khai.

### 3.1.2. Môi trường cài đặt và chạy thử

Bảng 3.1 trình bày các thành phần của môi trường phát triển và thử nghiệm. Đợt kiểm thử được thực hiện trên máy Windows sử dụng WAMP. Mã nguồn được tổ chức theo các thành phần điều phối yêu cầu, xử lý nghiệp vụ, truy cập dữ liệu và hiển thị giao diện.

:::table Bảng 3.1. Môi trường cài đặt và thử nghiệm
| Thành phần | Công nghệ hoặc công cụ | Vai trò trong hệ thống |
|---|---|---|
| Máy chủ Web | Apache trong WAMP | Tiếp nhận yêu cầu từ trình duyệt và chạy PHP |
| Ngôn ngữ phía máy chủ | PHP 8.3.28 | Xử lý đăng nhập, nghiệp vụ và truy cập dữ liệu |
| Cơ sở dữ liệu | MySQL, truy cập qua PDO | Lưu trữ dữ liệu và thực hiện giao dịch |
| Giao diện | HTML, CSS, Bootstrap, JavaScript | Hiển thị màn hình và xử lý tương tác |
| Kiểm thử | PHP CLI và Node.js | Chạy các bộ kiểm thử tự động |
| Quản lý mã nguồn | Git và GitHub | Theo dõi phiên bản và phối hợp phát triển |
| Môi trường triển khai thử | Docker, Render, Aiven MySQL | Phục vụ triển khai ứng dụng ngoài máy local |

Kết quả thử nghiệm tại mục 3.4 thuộc môi trường local. Chương này chưa đưa ra số liệu về tốc độ phản hồi, khả năng chịu tải hoặc độ sẵn sàng của môi trường trực tuyến vì chưa có phép đo tương ứng trong đợt tổng hợp.

### 3.1.3. Đối tượng sử dụng và phạm vi chức năng

Hệ thống phân quyền cho bốn vai trò tài khoản. Khách truy cập sử dụng các chức năng công khai trước khi được cấp tài khoản. Phạm vi của từng nhóm được tóm tắt trong Bảng 3.2.

:::table Bảng 3.2. Nhóm người dùng và chức năng chính
| Nhóm người dùng | Chức năng sử dụng |
|---|---|
| Khách truy cập | Xem thông tin phòng, hướng dẫn, hỏi đáp và gửi đăng ký lưu trú |
| Sinh viên | Xem thông tin lưu trú, hóa đơn, thông báo; gửi phản ánh; tạo QR thanh toán mô phỏng |
| Nhân viên quản lý KTX | Xét duyệt đăng ký; quản lý sinh viên, hợp đồng; ghi chỉ số điện nước; xử lý phản ánh |
| Nhân viên kế toán | Tra cứu hóa đơn và xác nhận các khoản phải thu theo nghiệp vụ |
| Quản lý KTX | Xem thống kê lưu trú, hóa đơn và phản ánh theo khoảng thời gian |

:::figure 3.1|Giao diện trang chủ của hệ thống Kanto KTX|Chụp toàn bộ vùng nội dung trang chủ, thể hiện tên hệ thống, menu và nút đăng ký lưu trú. Dùng ảnh từ ứng dụng đang chạy, không dùng wireframe Chương 2.

## 3.2. Các chức năng đã cài đặt và giao diện minh họa

Các chức năng dưới đây được mô tả theo đối tượng sử dụng, thao tác chính và kết quả xử lý trong mã nguồn. Mỗi vị trí chèn ảnh đi kèm chú thích để bổ sung hình ảnh sản phẩm. Các kết quả kiểm thử tự động của từng nhóm được trình bày riêng ở mục 3.4.

### 3.2.1. Đăng nhập và phân quyền

Chức năng đăng nhập tiếp nhận tên đăng nhập và mật khẩu, kiểm tra tài khoản rồi thiết lập phiên làm việc. Người dùng hợp lệ được chuyển đến dashboard tương ứng. Những trường hợp thông tin không hợp lệ được trả về thông báo để người dùng kiểm tra lại.

Khi truy cập chức năng được bảo vệ, hệ thống kiểm tra tài khoản và vai trò từ cơ sở dữ liệu. Người chưa đăng nhập được chuyển đến trang đăng nhập; người đăng nhập bằng vai trò không phù hợp bị từ chối. Đăng xuất kết thúc phiên làm việc để các trang quản lý không tiếp tục được sử dụng bằng phiên cũ.

Dashboard cung cấp điểm bắt đầu cho các công việc theo vai trò. Kết quả mong muốn là người dùng nhìn thấy các nhóm chức năng phù hợp và truy cập được đúng dữ liệu trong phạm vi được cấp.

:::figure 3.2|Giao diện đăng nhập và dashboard theo vai trò|Chèn ảnh trang đăng nhập và một hoặc hai dashboard tiêu biểu, ghi rõ vai trò dưới từng ảnh. Che tên đăng nhập cá nhân nếu cần; không hiển thị mật khẩu.

### 3.2.2. Đăng ký lưu trú

Người đăng ký nhập thông tin cá nhân và học tập, chọn diện ưu tiên, bổ sung minh chứng khi thuộc diện yêu cầu, chọn phòng rồi kiểm tra lại thông tin trước khi gửi. Email được thu thập để phục vụ việc thông báo kết quả xét duyệt.

Hệ thống kiểm tra dữ liệu đầu vào, các thông tin định danh và điều kiện phòng. Danh sách phòng được lựa chọn theo giới tính và số chỗ khả dụng. Số chỗ được tính có xét đến hợp đồng và đơn đăng ký đang giữ chỗ, giúp hạn chế tiếp nhận vượt sức chứa.

Sau khi gửi hợp lệ, đơn được ghi nhận ở trạng thái “Chờ duyệt”. Giai đoạn này chưa tạo hợp đồng lưu trú. Nếu dữ liệu không hợp lệ hoặc điều kiện phòng thay đổi, hệ thống thông báo để người đăng ký điều chỉnh trước khi gửi lại.

:::figure 3.3|Giao diện đăng ký lưu trú và lựa chọn phòng|Chèn ảnh bước nhập thông tin, bước chọn phòng và màn hình xác nhận hoặc thông báo gửi đơn thành công. Có thể ghép thành ba ảnh nhỏ được đánh dấu a, b, c.

### 3.2.3. Xét duyệt đăng ký và thông báo kết quả

Nhân viên quản lý KTX xem danh sách đơn, mở thông tin chi tiết và kiểm tra điều kiện xét duyệt. Khi duyệt đơn hợp lệ, hệ thống tạo các dữ liệu liên quan đến tài khoản sinh viên, hồ sơ và hóa đơn tiền phòng ban đầu. Sinh viên ở trạng thái “Chờ thanh toán”; hợp đồng được hình thành ở bước xác nhận thanh toán đầu tiên.

Trường hợp từ chối, nhân viên nhập lý do để lưu cùng kết quả xử lý. Hệ thống kiểm tra trạng thái đơn trước khi ghi nhận, hạn chế xử lý lặp một đơn đã có kết quả. Các cập nhật liên quan được thực hiện trong giao dịch để có thể hoàn tác khi một bước thất bại.

Kết quả xét duyệt được đưa vào thông báo email. Lỗi gửi thư được theo dõi riêng để nhân viên có thể xử lý hoặc gửi lại, trong khi kết quả duyệt đã lưu vẫn được giữ. Việc thư đến hộp thư người nhận cần được minh họa bằng thư thực nhận hoặc nhật ký nhà cung cấp.

:::figure 3.4|Giao diện danh sách và chi tiết xét duyệt đăng ký|Chèn ảnh danh sách đơn chờ duyệt và màn hình chi tiết có thao tác duyệt hoặc từ chối. Ảnh kết quả cần thể hiện trạng thái sau xử lý.
:::figure 3.5|Thông báo kết quả xét duyệt qua email|Chèn thư duyệt hoặc từ chối đã nhận từ một lượt thử được phép. Che địa chỉ cá nhân, mật khẩu và các thông tin đăng nhập trong thư. Nếu chưa có thư thực nhận, giữ ghi chú chờ bổ sung.

### 3.2.4. Tra cứu và cập nhật thông tin sinh viên

Nhân viên quản lý tìm sinh viên theo MSSV, họ tên, lớp, phòng và trạng thái lưu trú. Kết quả được phân trang để thuận tiện theo dõi khi số lượng hồ sơ tăng. Trang chi tiết liên kết thông tin cá nhân với dữ liệu phòng và hợp đồng có liên quan.

Chức năng cập nhật kiểm tra định dạng, độ dài và tính hợp lệ của thông tin trước khi lưu. Các lỗi như số điện thoại không hợp lệ hoặc ngày sinh không phù hợp được phản hồi cho người thao tác. Hệ thống thông báo khi cập nhật thành công hoặc khi nội dung không có thay đổi.

Qua việc tập trung hồ sơ, nhân viên có thể tra cứu thông tin phục vụ các công việc quản lý mà không nhập lại dữ liệu ở từng chức năng. Các trường ảnh hưởng tới điều kiện bố trí phòng cần được rà soát thống nhất với quy tắc nghiệp vụ trước khi chốt sản phẩm.

:::figure 3.6|Giao diện tra cứu và cập nhật thông tin sinh viên|Chụp danh sách có bộ lọc và một hồ sơ demo tại trang chi tiết. Thể hiện một thông báo cập nhật hoặc kiểm tra dữ liệu; che CCCD, số điện thoại và email thật.

### 3.2.5. Tra cứu và thanh lý hợp đồng

Chức năng tra cứu cho phép tìm hợp đồng theo mã, sinh viên, phòng và trạng thái. Trang chi tiết hiển thị thời hạn hợp đồng cùng thông tin người lưu trú, giúp nhân viên kiểm tra trước khi thực hiện thanh lý.

Khi nhận yêu cầu thanh lý, hệ thống kiểm tra hợp đồng còn hạn và các khoản công nợ của đúng sinh viên. Nếu còn hóa đơn tiền phòng hoặc phần tiền điện nước “Chưa thu”, yêu cầu bị từ chối và các khoản liên quan được thông báo. Công nợ điện nước được xét theo phần phải thu riêng, không mặc định toàn bộ khoản nợ của phòng thuộc về một sinh viên.

Với yêu cầu hợp lệ, hệ thống ghi nhận ngày thanh lý và số tiền phòng đã thu nhưng chưa sử dụng dự kiến hoàn trả. Tiền điện nước không được hoàn trả theo luồng này. Ngày kết thúc gốc của hợp đồng được giữ để phục vụ đối chiếu. Nếu sinh viên không còn hợp đồng còn hạn khác, trạng thái lưu trú được chuyển thành “Đã rời đi”. Số tiền ghi nhận là kết quả tính trong hệ thống; việc chi hoàn thực tế là bước nghiệp vụ riêng.

:::figure 3.7|Giao diện hợp đồng và kiểm tra công nợ khi thanh lý|Chèn ảnh chi tiết hợp đồng, hộp thoại xác nhận và trường hợp còn nợ bị chặn. Có thể bổ sung ảnh thông báo thanh lý hợp lệ bằng dữ liệu demo.

### 3.2.6. Ghi nhận chỉ số điện nước

Nhân viên quản lý chọn phòng có hợp đồng còn hạn để xem chỉ số gần nhất và nhập chỉ số điện, nước mới. Thông tin tháng, năm và nhân viên thực hiện được lưu cùng bản ghi để phục vụ việc tính hóa đơn.

Chức năng kiểm tra số nguyên không âm, không cho chỉ số mới nhỏ hơn số cũ và không ghi trùng một phòng trong cùng tháng, năm. Sau khi lưu thành công, giao diện quay lại phòng đã chọn và hiển thị thông báo kết quả. Khi bật JavaScript, việc chọn phòng tự tải lại phần thông tin tương ứng.

:::figure 3.8|Giao diện ghi nhận chỉ số điện nước|Chụp màn hình đã chọn phòng, có chỉ số cũ và ô nhập chỉ số mới. Bổ sung ảnh thông báo lưu hoặc cảnh báo chỉ số mới nhỏ hơn chỉ số cũ.

### 3.2.7. Tra cứu hóa đơn và xác nhận thanh toán

Nhân viên kế toán tra cứu hóa đơn theo các tiêu chí như mã hóa đơn, MSSV, họ tên, phòng, loại hóa đơn, kỳ và trạng thái. Danh sách hiển thị số tiền và tình trạng thu, hỗ trợ tìm các khoản chưa thanh toán.

Khi xác nhận hóa đơn tiền phòng ban đầu, hệ thống kiểm tra lại dữ liệu và tạo hợp đồng tương ứng, liên kết đơn đăng ký, cập nhật trạng thái lưu trú và thời điểm thanh toán trong cùng giao dịch. Đối với hóa đơn định kỳ hoặc điện nước, hệ thống xử lý khoản thu theo loại hóa đơn mà không tạo thêm hợp đồng từ các khoản này.

Hóa đơn đã thu không được xử lý như một khoản chưa thu mới. Với điện nước có phân bổ, việc ghi nhận thanh toán cần theo đúng phần phải thu của từng sinh viên. Điều này giúp quá trình thanh toán và kiểm tra công nợ sử dụng cùng dữ liệu.

:::figure 3.9|Giao diện tra cứu hóa đơn và xác nhận thanh toán|Chèn ảnh danh sách hóa đơn có bộ lọc, thao tác xác nhận một khoản chưa thu và trạng thái sau khi xác nhận. Dùng dữ liệu demo có số tiền dễ đối chiếu.

### 3.2.8. Gửi và xử lý phản ánh sự cố

Sinh viên nhập loại sự cố và nội dung mô tả để gửi phản ánh. Hệ thống xác định sinh viên, phòng từ tài khoản và hợp đồng còn hạn, sau đó ghi nhận phản ánh ở trạng thái “Chờ xử lý”. Trường hợp chưa có hợp đồng phù hợp được thông báo rõ trên giao diện.

Nhân viên quản lý xem danh sách cần xử lý, mở chi tiết, ghi nhận chi phí sửa chữa và hoàn tất xử lý. Hệ thống lưu nhân viên phụ trách và chuyển trạng thái sang “Đã xử lý”. Việc kiểm tra trạng thái trước khi cập nhật giúp hạn chế xác nhận lặp một phản ánh đã hoàn tất.

:::figure 3.10|Giao diện gửi và xử lý phản ánh sự cố|Chèn ảnh form gửi của sinh viên và ảnh trang xử lý của nhân viên, thể hiện loại sự cố, mô tả, chi phí và trạng thái.

### 3.2.9. Lập hóa đơn định kỳ

Nhóm đã cài đặt hai dịch vụ lập hóa đơn. Dịch vụ tiền phòng kiểm tra mốc thời gian trước khi kết thúc học kỳ, xác định kỳ kế tiếp và đơn giá có hiệu lực, sau đó tạo hóa đơn cho sinh viên đủ điều kiện. Việc kiểm tra sinh viên và học kỳ giúp ngăn tạo trùng khi chạy lại lệnh.

Dịch vụ điện nước lấy chỉ số của tháng cần tính và tháng liền trước, kiểm tra mức tiêu thụ và đơn giá có hiệu lực rồi lập hóa đơn cho phòng hợp lệ. Khoản phải thu được phân bổ cho sinh viên liên quan, đồng thời tạo thông báo. Các trường hợp thiếu chỉ số, thiếu đơn giá hoặc đã có hóa đơn được xử lý theo điều kiện của dịch vụ.

Hai dịch vụ có thể được gọi từ dòng lệnh và đã có kết quả kiểm thử bằng bảng tạm. Cần ghi nhận lịch chạy và nhật ký thực thi trước khi đánh giá chức năng tự động theo lịch trên môi trường triển khai.

:::figure 3.11|Kết quả thử nghiệm chức năng lập hóa đơn định kỳ|Chèn ảnh kết quả bộ kiểm thử hoặc nhật ký chạy trên môi trường demo đã được phép. Nếu dùng ảnh test, chú thích rõ là test; nếu có lịch chạy thật, bổ sung thời điểm và kết quả một lần thực thi.

### 3.2.10. Xem thông tin lưu trú và thông báo

Sinh viên xem hồ sơ, phòng và hợp đồng gắn với tài khoản đang đăng nhập. Dữ liệu được lấy theo tài khoản trong phiên làm việc, hạn chế việc thay đổi mã sinh viên trên địa chỉ để đọc hồ sơ người khác. Khi chưa có hồ sơ hoặc hợp đồng, giao diện hiển thị trạng thái phù hợp để người dùng biết thông tin còn thiếu.

Danh sách thông báo hiển thị tiêu đề, nội dung và thời điểm tạo. Sinh viên mở chi tiết để xem thông tin liên quan, chẳng hạn nội dung nhắc thanh toán. Truy vấn danh sách và chi tiết đều giới hạn theo sinh viên của phiên hiện tại.

:::figure 3.12|Giao diện thông tin lưu trú và thông báo của sinh viên|Chèn ảnh trang thông tin lưu trú và ảnh chi tiết một thông báo. Ưu tiên dữ liệu demo có phòng, hợp đồng và nội dung thông báo rõ ràng.

### 3.2.11. Trợ lý AI hỗ trợ hỏi đáp

Trợ lý AI hỗ trợ giải đáp các câu hỏi về ký túc xá, hướng dẫn sử dụng và thông tin phòng công khai. Người dùng nhập câu hỏi trên giao diện, hệ thống xử lý và trả về câu trả lời trong phạm vi hỗ trợ.

Nguồn thông tin phòng dùng cho hỏi đáp gồm các dữ liệu công khai về tòa, phòng và số chỗ; không cung cấp danh sách hồ sơ sinh viên hay cho phép AI thực hiện lệnh ghi cơ sở dữ liệu. Khi dịch vụ mô hình gặp lỗi, bộ xử lý có mô hình dự phòng, số lần thử lại giới hạn và câu trả lời dự phòng phù hợp.

Độ chính xác của câu trả lời cần được đánh giá theo câu hỏi và dữ liệu tại thời điểm thử. Nội dung quan trọng về khoản thu hoặc hồ sơ cá nhân cần được đối chiếu tại chức năng nghiệp vụ tương ứng.

:::figure 3.13|Giao diện trợ lý AI hỗ trợ thông tin ký túc xá|Chèn ảnh một câu hỏi về đăng ký và một câu hỏi về phòng công khai. Chỉ thêm ảnh chế độ dự phòng nếu đã thực sự thử được tình huống đó.

### 3.2.12. Thống kê và báo cáo

Quản lý KTX chọn loại thống kê và khoảng thời gian để tổng hợp tình hình lưu trú, hóa đơn hoặc phản ánh. Kết quả gồm số liệu tổng quan và danh sách chi tiết dùng để kiểm tra các bản ghi thành phần.

Nhóm lưu trú tổng hợp hợp đồng, sinh viên và phòng. Nhóm hóa đơn thể hiện số lượng hóa đơn, trạng thái đã thu/chưa thu và tổng giá trị hóa đơn trong phạm vi lọc. Nhóm phản ánh tổng hợp số lượng chờ xử lý, đã xử lý và chi phí sửa chữa. Tổng giá trị hóa đơn cần được đọc đúng ý nghĩa, không mặc định là toàn bộ số tiền đã thực thu.

:::figure 3.14|Giao diện thống kê theo loại và khoảng thời gian|Chèn ảnh bộ lọc và kết quả của ít nhất một loại thống kê. Giữ cả số tổng hợp và một phần danh sách chi tiết để dễ đối chiếu.

### 3.2.13. Xem thông tin phòng và hướng dẫn

Chức năng thông tin phòng cung cấp danh sách phòng cùng tiêu chí lọc theo tòa, tầng và các thông tin liên quan. Người dùng có thể xem sức chứa và tình trạng chỗ để chuẩn bị đăng ký. Danh sách công khai có thể hiển thị cả phòng đầy, trong khi bước chọn phòng đăng ký phải xét điều kiện còn chỗ.

Trang hướng dẫn trình bày các thao tác cơ bản và liên kết tới chức năng liên quan. Hai chức năng này hỗ trợ người mới tiếp cận hệ thống trước khi thực hiện nghiệp vụ có yêu cầu đăng nhập.

:::figure 3.15|Giao diện thông tin phòng và hướng dẫn sử dụng|Chèn ảnh danh sách phòng sau khi lọc và ảnh phần hướng dẫn đăng ký. Thể hiện rõ tòa, phòng hoặc tiêu chí đang chọn.

### 3.2.14. Thanh toán hóa đơn bằng QR mô phỏng

Sinh viên xem các hóa đơn thuộc tài khoản và mở chi tiết khoản chưa thu. Mã QR chỉ được phát hành khi người dùng chọn tạo mã. Mỗi mã có thời hạn hiệu lực tối đa năm phút; mở lại chi tiết không tự tạo thêm mã mới khi mã đang dùng còn hiệu lực.

Thiết bị quét QR mở trang xác nhận mô phỏng. Việc mở trang chưa thay đổi trạng thái hóa đơn. Khi gửi xác nhận, hệ thống kiểm tra mã, thời hạn, trạng thái tài khoản, số tiền và trạng thái khoản phải thu rồi mới xử lý giao dịch. Mã đã dùng hoặc hết hạn không được tiếp tục xác nhận.

Đối với điện nước, QR gắn với phần phải thu của sinh viên tương ứng. Hóa đơn chung chỉ hoàn tất khi các phần phải thu đã được thanh toán theo quy tắc hệ thống. Luồng này phục vụ minh họa nghiệp vụ, chưa kết nối ngân hàng hoặc nhận xác nhận giao dịch thực từ cổng thanh toán.

:::figure 3.16|Giao diện tạo mã QR thanh toán mô phỏng|Chụp chi tiết hóa đơn trước và sau khi bấm tạo mã, có thời gian hiệu lực. Dùng mã đã hết hạn cho ảnh báo cáo hoặc che phần mã có thể quét được.
:::figure 3.17|Giao diện xác nhận và kết quả thanh toán mô phỏng|Chèn ảnh trang mở sau khi quét và trang kết quả sau xác nhận bằng dữ liệu demo. Chú thích rõ thiết bị và đây là thanh toán mô phỏng.

## 3.3. Kết quả tích hợp các chức năng

### 3.3.1. Liên kết đăng ký với lưu trú và thanh toán

Luồng chính bắt đầu từ đơn đăng ký “Chờ duyệt”. Khi duyệt, hệ thống chuẩn bị tài khoản, hồ sơ sinh viên và hóa đơn ban đầu. Xác nhận khoản tiền phòng đầu tiên tạo hợp đồng và chuyển sinh viên sang trạng thái “Đang ở”. Thông tin này được sử dụng ở màn hình lưu trú, tra cứu hợp đồng và các chức năng liên quan.

Điểm cần kiểm tra trong luồng tích hợp là sự đồng nhất giữa đơn, hóa đơn và hợp đồng. Nếu giao dịch thanh toán thất bại, các thay đổi chưa được hoàn tất phải được hoàn tác. Nếu người dùng gửi yêu cầu lại, hệ thống cần kiểm tra trạng thái đã xử lý để tránh tạo thêm hợp đồng hoặc ghi nhận thu lặp.

### 3.3.2. Liên kết điện nước với công nợ và thanh lý

Chỉ số điện nước được sử dụng để tính hóa đơn phòng và phân bổ số tiền phải thu. Khi một sinh viên thanh toán phần của mình, dữ liệu phần thu được cập nhật. Chức năng thanh lý hợp đồng sử dụng các khoản chưa thu của chính sinh viên này để quyết định có cho phép thực hiện hay không.

Sự liên kết giữa các bước giúp công nợ được kiểm tra tại thời điểm kết thúc lưu trú. Một trường hợp cần lưu ý là sinh viên đã trả đủ phần điện nước của mình trong khi thành viên khác của phòng vẫn còn nợ; kết quả thanh lý phải được xét theo phần phân bổ và các khoản tiền phòng của đúng sinh viên.

:::table Bảng 3.3. Các điểm liên kết dữ liệu quan trọng
| Luồng nghiệp vụ | Dữ liệu liên quan | Tiêu chí đối chiếu |
|---|---|---|
| Đăng ký và xét duyệt | Đơn, sinh viên, tài khoản, hóa đơn | Đúng người đăng ký, phòng và kỳ thu |
| Thu tiền phòng đầu tiên | Hóa đơn, đơn, hợp đồng | Một kết quả thu hợp lệ; liên kết hợp đồng đúng |
| Điện nước và thanh toán | Chỉ số, hóa đơn, phần phải thu | Tổng và phần thu nhất quán; tránh thu lặp |
| Công nợ và thanh lý | Hợp đồng, khoản chưa thu, thanh lý | Chặn khi còn nợ; lưu kết quả đúng sinh viên |

Các bộ test thanh toán, QR và hợp đồng đã kiểm tra nhiều tình huống liên kết bằng bảng tạm. Việc trình diễn toàn bộ quy trình trên trình duyệt với dữ liệu demo vẫn cần bổ sung ảnh và biên bản quan sát.

:::figure 3.18|Minh họa kết quả liên kết đơn đăng ký hóa đơn và hợp đồng|Chèn các ảnh của cùng một hồ sơ demo sau duyệt và sau thanh toán đầu tiên. Dùng một mã minh họa thống nhất để người đọc nhận ra quan hệ giữa các bước.

## 3.4. Thử nghiệm và kết quả

### 3.4.1. Mục tiêu và phương pháp thử nghiệm

Thử nghiệm nhằm đánh giá khả năng chạy mã, tính đúng của các điều kiện nghiệp vụ và khả năng hạn chế thao tác ngoài phạm vi được phép. Nhóm sử dụng kiểm tra cú pháp PHP, bộ kiểm thử frontend, bộ kiểm thử route/phân quyền và các bộ kiểm thử nghiệp vụ.

Các test nghiệp vụ được chạy với bảng TEMPORARY và dữ liệu thử riêng trong kết nối cơ sở dữ liệu. Cách thực hiện này cho phép tạo tình huống đủ nợ, hết nợ, hóa đơn đã thu hoặc mã hết hạn mà không thay đổi hồ sơ nghiệp vụ đang lưu. Một số test mô phỏng dữ liệu đầu vào và môi trường trình duyệt; phạm vi kiểm chứng được ghi cụ thể cùng kết quả.

Đợt tổng hợp ngày 31/10/2026 kiểm tra 124 file PHP, chạy 11 bộ kiểm thử PHP với tổng cộng 298 ca và một bộ frontend gồm 12 ca. Các số lượng này phản ánh đúng phạm vi đã chạy, không phải tỷ lệ bao phủ mọi chức năng hoặc mọi dòng mã của hệ thống.

### 3.4.2. Kết quả kiểm tra cú pháp và frontend

Kiểm tra cú pháp được chạy trên các file PHP của ứng dụng, cấu hình, script và kiểm thử được liệt kê trong dự án, loại trừ thư viện vendor, thư mục tạm và công cụ tạo báo cáo trong docs/reports. Kết quả đạt 124/124 file; không ghi nhận lỗi cú pháp trong phạm vi này.

Bộ tests/frontend.js chạy 12 ca bằng môi trường JavaScript mô phỏng. Các ca kiểm tra mở/hủy hộp thoại, bộ lọc phòng, tự tải chỉ số sau chọn phòng, tạo QR nội bộ, chặn QR khác nguồn, xử lý thời hạn và gửi token xác nhận. Toàn bộ 12 ca đạt. Kết quả này đánh giá logic tương tác, chưa thay thế việc quan sát bố cục trên trình duyệt thật.

:::table Bảng 3.4. Kết quả kiểm tra cú pháp và frontend
| Hạng mục | Tổng số | Đạt | Lỗi |
|---|---|---|---|
| PHP lint | 124 file | 124 | 0 |
| Frontend JavaScript | 12 ca | 12 | 0 |

:::figure 3.19|Kết quả chạy kiểm tra cú pháp và frontend|Chèn ảnh terminal của lần chạy được dùng trong báo cáo, thể hiện phạm vi PHP lint và dòng 12/12 frontend checks passed. Nếu chạy lại cho số lượng khác, cập nhật cả bảng và mô tả.

### 3.4.3. Kết quả kiểm thử route và nghiệp vụ

Bảng 3.5 tổng hợp kết quả mới chạy trên môi trường local. Tất cả 11 bộ kiểm thử dưới đây kết thúc thành công. Các số liệu trong bảng được tính theo ca kiểm thử của từng file, không cộng số file PHP lint vào tổng số ca.

:::table Bảng 3.5. Kết quả các bộ kiểm thử PHP ngày 31/10/2026
| Bộ kiểm thử | Phạm vi chính | Đạt | Lỗi |
|---|---|---|---|
| run.php | Route, đăng nhập, phân quyền | 106 | 0 |
| student-management.php | Tra cứu, cập nhật sinh viên | 6 | 0 |
| contracts.php | Hợp đồng, công nợ, thanh lý | 31 | 0 |
| utility-meters.php | Ghi chỉ số điện nước | 5 | 0 |
| incidents.php | Gửi và xử lý phản ánh | 6 | 0 |
| notifications.php | Phạm vi đọc thông báo | 5 | 0 |
| statistics.php | Thống kê và bộ lọc | 4 | 0 |
| payment.php | Xác nhận thu và hợp đồng | 30 | 0 |
| student-payments.php | Thanh toán QR mô phỏng | 78 | 0 |
| utility-invoice-generation.php | Lập hóa đơn điện nước | 22 | 0 |
| room-invoice-generation.php | Lập hóa đơn tiền phòng | 5 | 0 |
| Tổng cộng | 11 bộ kiểm thử PHP | 298 | 0 |

Các file trong bảng nằm tại thư mục tests. Đợt tổng hợp này chưa chạy lại toàn bộ các bộ đăng ký, xét duyệt, chatbot hoặc gửi email riêng biệt. Những chức năng đó có thể được gọi trong các kịch bản tích hợp đã chọn, nhưng không vì vậy mà coi toàn bộ bộ test chuyên biệt của chúng đã được thực hiện.

:::figure 3.20|Kết quả kiểm thử phân quyền và nghiệp vụ|Chèn ảnh dòng tổng kết của tests/run.php và một số bộ test tiêu biểu như contracts.php, payment.php, student-payments.php. Ảnh cần đọc được số đạt và số lỗi.

### 3.4.4. Phân tích các tình huống thử nghiệm tiêu biểu

Kết quả phân quyền cho thấy các yêu cầu chưa đăng nhập hoặc sai vai trò được xử lý theo kỳ vọng của bộ test. Các ca hợp đồng kiểm tra việc từ chối khi còn công nợ, trạng thái sau thanh lý và giao diện xác nhận. Nhóm thanh toán và QR kiểm tra quyền sở hữu khoản phải thu, trạng thái hóa đơn, thời hạn mã và khả năng hoàn tác khi một bước của giao dịch thất bại.

:::table Bảng 3.6. Một số tình huống đã được kiểm tra tự động
| Tình huống | Kết quả cần có | Kết quả ghi nhận |
|---|---|---|
| Truy cập chức năng bằng sai vai trò | Từ chối truy cập | Đạt trong run.php |
| Thanh lý khi còn khoản chưa thu | Giữ hợp đồng và báo công nợ | Đạt trong contracts.php |
| Thu tiền phòng đầu tiên hợp lệ | Ghi thu và tạo liên kết hợp đồng | Đạt trong payment.php |
| Lỗi giữa giao dịch thanh toán | Hoàn tác để có thể thử lại | Đạt trong payment.php |
| QR hết hạn hoặc đã sử dụng | Không tiếp tục xác nhận | Đạt trong student-payments.php |
| Thu phần điện nước của sinh viên | Đúng phần tiền và trạng thái | Đạt trong bộ test QR và điện nước |
| Ghi chỉ số không hợp lệ hoặc trùng kỳ | Từ chối dữ liệu không phù hợp | Đạt trong utility-meters.php |
| Xử lý lại phản ánh đã hoàn tất | Không ghi xử lý lần hai | Đạt trong incidents.php |
| Xem thông báo của người khác | Không trả dữ liệu ngoài tài khoản | Đạt trong notifications.php |
| Chạy lại lệnh lập hóa đơn | Không tạo hóa đơn trùng | Đạt trong các bộ lập hóa đơn |

Các ca đạt cho thấy logic đã đáp ứng các tình huống được xây dựng trong bộ test. Tuy nhiên, bảng tạm không chứng minh đầy đủ ràng buộc khóa ngoại trên dữ liệu triển khai hoặc hành vi khi hai tiến trình cùng thao tác trên một bản ghi vật lý. Các nội dung này cần được thử riêng khi đánh giá vận hành.

### 3.4.5. Thử nghiệm giao diện và sử dụng thực tế

Thử nghiệm thủ công tập trung vào tính rõ ràng của thông tin, khả năng hoàn thành một thao tác và tính nhất quán giữa các màn hình. Khi thực hiện, nhóm sử dụng tài khoản demo phù hợp, ghi lại thao tác, kết quả quan sát và ảnh minh họa. Bảng 3.7 được để trạng thái chờ bổ sung vì đợt tổng hợp hiện tại chưa quan sát lại các luồng này trên trình duyệt.

:::table Bảng 3.7. Các thử nghiệm trực tiếp cần bổ sung kết quả
| Nội dung thử | Tiêu chí quan sát | Kết quả thực tế |
|---|---|---|
| Đăng ký đến tạo hợp đồng | Trạng thái và dữ liệu liên kết đúng qua từng màn hình | [BỔ SUNG KẾT QUẢ] |
| Quét QR từ điện thoại | Mở được trang mô phỏng, xác nhận đúng khoản thu | [BỔ SUNG KẾT QUẢ] |
| Giao diện desktop và mobile | Đọc được nội dung, không tràn ngang, dùng được nút | [BỔ SUNG KẾT QUẢ] |
| Email kết quả xét duyệt | Thư thực nhận đúng nội dung và trạng thái xử lý | [BỔ SUNG KẾT QUẢ] |
| Lịch chạy lập hóa đơn | Có thời điểm chạy, kết quả và nhật ký rõ ràng | [BỔ SUNG KẾT QUẢ] |
| Thao tác đồng thời | Không thu lặp hoặc vượt sức chứa khi cạnh tranh | [BỔ SUNG KẾT QUẢ] |

:::figure 3.21|Giao diện trên máy tính và điện thoại|Chèn ảnh cùng một chức năng ở hai kích thước màn hình, ưu tiên danh sách hóa đơn hoặc đăng ký lưu trú. Ghi trình duyệt và độ rộng màn hình đã thử nếu có.

### 3.4.6. Nhận xét kết quả thử nghiệm

Đợt kiểm tra đạt 298/298 ca PHP và 12/12 ca frontend, đồng thời 124 file PHP trong phạm vi đã chọn không có lỗi cú pháp. Kết quả tạo cơ sở để tiếp tục hoàn thiện giao diện, bổ sung minh chứng và chuẩn bị demo các nghiệp vụ chính.

Phạm vi kiểm thử còn giới hạn bởi dữ liệu và tình huống được xây dựng. Chưa có số đo để kết luận hệ thống đáp ứng một lượng người dùng đồng thời cụ thể hoặc đạt thời gian phản hồi cố định. Khi chốt báo cáo, các kết quả thử trực tiếp và ảnh giao diện cần được bổ sung vào những vị trí đã đánh dấu.

## 3.5. Đánh giá ưu điểm và hạn chế

### 3.5.1. Ưu điểm của sản phẩm

Về chức năng, hệ thống đã kết nối nhiều công việc thường gặp trong quản lý ký túc xá. Dữ liệu từ đăng ký và xét duyệt được dùng tiếp trong thanh toán, hợp đồng và lưu trú. Người sử dụng có thể theo dõi tiến trình xử lý qua các trạng thái được lưu thay vì chỉ dựa vào thông tin trao đổi rời rạc.

Về quản lý dữ liệu, cơ sở dữ liệu tập trung tạo điều kiện đối chiếu sinh viên, phòng, hóa đơn và hợp đồng. Các nghiệp vụ quan trọng sử dụng giao dịch và kiểm tra trạng thái trước khi cập nhật. Phân bổ khoản điện nước theo sinh viên hỗ trợ việc theo dõi công nợ và thanh lý đúng đối tượng.

Về sử dụng, danh sách có bộ lọc, trang chi tiết và thông báo kết quả giúp người dùng tiếp cận từng công việc theo trình tự rõ ràng. Chức năng tra cứu phòng và hướng dẫn hỗ trợ người đăng ký mới; nhóm thông báo và hỏi đáp bổ sung các kênh tiếp cận thông tin trong ứng dụng.

Về khả năng bảo trì, việc tách controller, model, view và các dịch vụ hỗ trợ giúp xác định phạm vi khi điều chỉnh chức năng. Bộ kiểm thử hiện có hỗ trợ phát hiện sai lệch sau thay đổi; kết quả của các nhóm thanh toán và hợp đồng cung cấp những trường hợp cụ thể để kiểm tra lại nghiệp vụ.

### 3.5.2. Hạn chế của sản phẩm và thử nghiệm

Thanh toán QR đang dừng ở mô phỏng nghiệp vụ. Ứng dụng chưa nhận kết quả thu tiền từ ngân hàng hoặc cổng thanh toán, vì vậy trạng thái “Đã thu” của một lượt demo không phải bằng chứng về giao dịch tài chính thực tế.

Các tác vụ lập hóa đơn đã có mã xử lý và test, nhưng khả năng chạy ổn định theo lịch phụ thuộc việc cấu hình, giám sát và xử lý lỗi trên môi trường vận hành. Tương tự, gửi email phụ thuộc cấu hình dịch vụ và khả năng giao nhận; việc tạo thông báo hoặc gửi yêu cầu thành công chưa tự chứng minh thư đã đến hộp thư đích.

Kết quả kiểm thử local chưa bao phủ đầy đủ thao tác đồng thời, tải lớn, sao lưu và khôi phục dữ liệu. Các test dùng bảng tạm có ưu điểm kiểm tra độc lập nhưng vẫn cần bổ sung thử nghiệm trên bộ dữ liệu demo gần thực tế và môi trường triển khai.

Trợ lý AI có thể đưa ra câu trả lời chưa chính xác hoặc bị gián đoạn do dịch vụ bên ngoài. Phạm vi dữ liệu công khai và cơ chế dự phòng giúp hạn chế ảnh hưởng, song câu trả lời liên quan nghiệp vụ vẫn cần được kiểm tra với dữ liệu chính thức của hệ thống.

Ngoài ra, việc hoàn thiện giao diện trên nhiều thiết bị và sự nhất quán giữa tài liệu thiết kế với mã nguồn cần tiếp tục được rà soát. Các quy tắc ảnh hưởng trực tiếp tới bố trí phòng, quyền cập nhật hồ sơ và công nợ phải thống nhất trước khi chuyển từ demo sang sử dụng thực tế.

### 3.5.3. Mức độ đáp ứng mục tiêu đề tài

:::table Bảng 3.8. Đánh giá mức độ đáp ứng theo nhóm mục tiêu
| Mục tiêu | Kết quả hiện có | Nội dung cần hoàn thiện |
|---|---|---|
| Hỗ trợ đăng ký và quản lý lưu trú | Có luồng đăng ký, duyệt, hồ sơ và hợp đồng | Minh chứng trình duyệt xuyên suốt |
| Quản lý khoản thu | Có hóa đơn, phân bổ điện nước và QR mô phỏng | Thanh toán thực nếu mở rộng phạm vi |
| Hỗ trợ vận hành KTX | Có chỉ số, phản ánh, thông báo và thống kê | Xác nhận lịch chạy và giao nhận email |
| Kiểm soát quyền truy cập | Có vai trò và các test route đạt | Rà soát toàn bộ thao tác ghi và thử trực tiếp |
| Chứng minh chất lượng sản phẩm | Có kết quả kiểm thử tự động mới | Giao diện, tải đồng thời và triển khai |

Sản phẩm đã có cơ sở để trình bày các chức năng chính và kết quả kiểm thử trong phạm vi đồ án. Mức độ hoàn thành được đánh giá theo chức năng và tiêu chí kiểm chứng cụ thể; chưa quy đổi thành tỷ lệ phần trăm vì chưa thống nhất trọng số và tiêu chí nghiệm thu cho toàn bộ Use Case.

## 3.6. Kết luận chương

Hệ thống Kanto KTX đã cài đặt các nhóm chức năng phục vụ đăng ký, xét duyệt, lưu trú, hợp đồng, hóa đơn, điện nước và hỗ trợ người dùng. Các luồng nghiệp vụ có liên kết dữ liệu để theo dõi quá trình từ tiếp nhận đơn đến quản lý lưu trú và khoản thu.

Kết quả thử nghiệm local ngày 31/10/2026 ghi nhận 298 ca PHP và 12 ca frontend đạt, cùng 124 file PHP đạt kiểm tra cú pháp trong phạm vi lựa chọn. Những kết quả này hỗ trợ đánh giá logic đã cài đặt. Minh chứng giao diện, các thử nghiệm sử dụng trực tiếp và xác nhận tác vụ triển khai cần được bổ sung để hoàn thiện đánh giá sản phẩm. Các ưu điểm và hạn chế nêu trong chương là cơ sở cho phần kết luận và hướng phát triển của đề tài.
