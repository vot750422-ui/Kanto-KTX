# PROJECT_STATE

## Báo cáo Đồ án 1 bản gộp (31/10/2026)

- Đã tạo `docs/reports/complete/BAO_CAO_DO_AN_1_KANTO_KTX_HOAN_THIEN.docx`: bìa ghi GVHD Trịnh Thanh Duy, Phan Minh Trọng (KTPM2411047), Hồ Hoàng Như Ý (KTPM2411004); gồm Lời mở đầu, Chương 1–4, tài liệu tham khảo và Phụ lục A đối chiếu Chương 1/Chương 2/Chương 3.
- Chương 1 và 2 được đối chiếu với mã nguồn/CSDL local: dùng PHP/PDO/MySQL và JavaScript thuần, 20 bảng; quy tắc hợp đồng, thanh toán, điện nước, QR mô phỏng, AI và giới hạn vận hành được đồng bộ với kết quả Chương 3. Bản gộp giữ 25 wireframe phù hợp, 21 vị trí chèn ảnh sản phẩm thực tế trong Chương 3 và không khẳng định các kiểm chứng chưa có.
- Nội dung báo cáo ghi lại kết quả kiểm thử local đã có ở mục bên dưới, không chạy thêm test hay thay đổi mã nghiệp vụ/CSDL khi tạo báo cáo.

## Bản thảo Chương 3 và kết quả kiểm thử ngày 31/10/2026

- Viết lại `docs/reports/chapter3/CHUONG_3_KET_QUA_VA_THAO_LUAN.md` và `.docx`: mô tả sản phẩm, 14 nhóm chức năng, tích hợp, thử nghiệm, ưu điểm và hạn chế. Có 21 vị trí chèn ảnh cùng chú thích, 8 bảng; giữ nguyên bản nháp cũ. DOCX được mở bằng Microsoft Word và kiểm tra hình ảnh toàn bộ 17 trang.
- PHP lint trong phạm vi ứng dụng/cấu hình/script/test, loại trừ `vendor`, `tmp` và `docs/reports`: 124/124 file đạt. `node tests/frontend.js`: 12/12 ca đạt.
- Các bộ PHP đã chạy lại: `run.php` 106/106; `student-management.php` 6/6; `contracts.php` 31/31; `utility-meters.php` 5/5; `incidents.php` 6/6; `notifications.php` 5/5; `statistics.php` 4/4; `payment.php` 30/30; `student-payments.php` 78/78; `utility-invoice-generation.php` 22/22; `room-invoice-generation.php` 5/5. Tổng cộng 298/298 ca trong 11 bộ, không bao gồm lint và frontend.
- Đây là kết quả local, sử dụng dữ liệu thử/bảng tạm cho test nghiệp vụ. Chưa chạy lại toàn bộ bộ test đăng ký, xét duyệt, chatbot, email riêng; chưa xác minh lại giao diện trực tiếp, giao nhận email, lịch timer, đồng thời hoặc môi trường triển khai. DOCX đánh dấu phần cần bổ sung, không ghi những mục này là đã đạt. Không sửa mã nghiệp vụ hoặc schema trong lần soạn tài liệu này.

## Trạng thái hiện tại - UC03, UC17 và UC05.2 (29/10/2026)

- Theo xác minh của người dùng, UC03 Gmail và UC17 QR được xem là chạy được. Các UC timer vẫn chưa xác minh trên môi trường chạy thật; code/script timer hiện có chỉ được xem là triển khai local và đã có test riêng.
- UC05.2 đã triển khai theo quy tắc đã chốt: hóa đơn tiền phòng độc lập theo sinh viên; hóa đơn điện nước lập theo phòng nhưng chia thành phần phải thu riêng cho từng sinh viên, mỗi sinh viên thanh toán độc lập.
- Khi thanh lý, hệ thống chỉ kiểm tra hóa đơn tiền phòng và phần điện nước `Chưa thu` của đúng sinh viên trong hợp đồng. Còn công nợ thì trả HTTP 409 và không đổi trạng thái hợp đồng.
- Nếu thanh lý trước `NgayKetThuc`, hệ thống lưu số tiền phòng đã thu nhưng chưa sử dụng dự kiến trong `thanhlyhopdong.TienHoanTra`; không hoàn tiền điện nước. `hopdong.NgayKetThuc` gốc được giữ nguyên.

## Mở rộng xác nhận hóa đơn kế toán (29/10/2026)

- Kế toán được xác nhận mọi hóa đơn đang `Chưa thu` qua `payment-confirm`.
- Hóa đơn tiền phòng đầu tiên vẫn tạo hợp đồng, liên kết đơn và chuyển sinh viên từ `Chờ thanh toán` sang `Đang ở` trong cùng transaction.
- Hóa đơn tiền phòng định kỳ và hóa đơn điện nước chỉ chuyển sang `Đã thu`, không tạo hợp đồng.
- `Payment::listing()` hiển thị thao tác xác nhận cho mọi hóa đơn chưa thu; kết quả giao diện không còn giả định mọi lần thu đều có hợp đồng.
- Đồng bộ `sql/qlktx_v2.sql` để trạng thái `sinhvien.TrangThaiLuuTru` có `Chờ thanh toán`, phù hợp với code và CSDL local hiện tại.
- Kiểm thử mới: `tests/payment.php` đạt `30/30`; `tests/contracts.php` đạt `31/31`; `tests/run.php` đạt `106/106`. UC05.2 đã có kiểm tra công nợ và hoàn trả tiền phòng; CSRF và mật khẩu kích hoạt giữ nguyên theo phạm vi hiện tại.

## UC10.1 - Lập hóa đơn tiền phòng kỳ kế tiếp (29/10/2026)

- Bổ sung `models/RoomInvoiceGenerator.php` và `scripts/generate-room-invoices.php`.
- Timer kiểm tra mốc 10 ngày trước khi kết thúc học kỳ, lấy học kỳ kế tiếp và đơn giá tiền phòng đồng/tháng, tính tiền theo ngày biên, tạo hóa đơn `Chưa thu` và thông báo cho từng sinh viên có hợp đồng `Còn hạn`.
- Chống tạo trùng bằng kiểm tra `MSSV + MaHocKy` và khóa unique hiện có trong `hoadontienphong`; mỗi sinh viên được xử lý trong transaction riêng.
- Bổ sung wrapper `run-room-invoice-task.bat` và script đăng ký Task Scheduler hằng ngày. Chưa tự đăng ký hoặc tự chạy task trên máy hiện tại.
- Kiểm thử mới: `tests/room-invoice-generation.php` đạt `5/5`.

## UC08 / UC10.2 - Tự động lập hóa đơn điện nước (27/10/2026)

- Triển khai dịch vụ `models/UtilityInvoiceGenerator.php` và lệnh CLI `scripts/generate-utility-invoices.php` phục vụ timer/scheduler tự động lập hóa đơn điện nước định kỳ vào đầu tháng hoặc chạy bù theo tham số (`--month=M --year=YYYY`).
- Kiểm tra đầy đủ các điều kiện hợp lệ của phòng:
  1. Có bản ghi chỉ số tháng cần tính trong `chisodiennuoc`.
  2. Có bản ghi chỉ số tháng liền trước trong `chisodiennuoc` (xử lý đúng chuyển năm từ tháng 1 sang tháng 12 năm trước).
  3. Chỉ số mới không nhỏ hơn chỉ số cũ (`ChiSoDienMoi >= ChiSoDienCu`, `ChiSoNuocMoi >= ChiSoNuocCu`).
  4. Có ít nhất một sinh viên có hợp đồng giao với khoảng thời gian trong tháng (`NgayBatDau <= endOfMonth` và `NgayKetThuc >= startOfMonth`).
  5. Chưa tồn tại hóa đơn điện nước cho cùng phòng, tháng, năm.
- Tự động lấy đơn giá Điện và Nước mới nhất có hiệu lực tính đến ngày cuối tháng cần tính. Nếu thiếu một trong hai đơn giá, hủy toàn bộ lần chạy và báo lỗi rõ ràng.
- Ghi dữ liệu trong transaction cho từng phòng: tạo `hoadon` (loại `Điện nước`, `Chưa thu`), tạo `hoadondiennuoc`, tạo liên kết `hoadondiennuocsinhvien` cho các sinh viên liên quan, tạo thông báo `thongbao` nhắc nợ cho từng sinh viên. Chống tạo trùng qua unique key `uq_hddn_phong_thang_nam` và kiểm tra khóa bản ghi. Lỗi của một phòng rollback hoàn toàn phòng đó mà không ảnh hưởng các phòng hợp lệ khác.
- Kiểm thử liên quan: `tests/utility-invoice-generation.php` đạt 22/22 ca kiểm thử, gồm kiểm tra chia phần tiền theo từng sinh viên.

## UC05.2 - Thanh lý hợp đồng, công nợ và hoàn trả tiền phòng (29/10/2026)

- Bổ sung chức năng thanh lý hợp đồng lưu trú cho `Nhân viên quản lý KTX` qua route POST `index.php?action=contract-terminate`. Khách vãng lai bị chuyển hướng 302, vai trò khác bị chặn 403, và yêu cầu GET bị từ chối với 405 Method Not Allowed.
- Thực hiện trong một transaction có khóa bản ghi (`SELECT ... FOR UPDATE`). Chỉ cho phép thanh lý khi hợp đồng ở trạng thái `Còn hạn`; nếu MSSV còn hóa đơn tiền phòng hoặc phần điện nước `Chưa thu`, trả HTTP 409 và liệt kê công nợ.
- Tạo `thanhlyhopdong` để lưu `NgayThanhLy` và `TienHoanTra`; giữ nguyên `hopdong.NgayKetThuc` là ngày kết thúc theo hợp đồng gốc.
- Tiền hoàn trả chỉ tính trên hóa đơn tiền phòng đã thu nhưng chưa sử dụng theo số ngày còn lại của kỳ. Hóa đơn điện nước không hoàn trả và được kiểm tra theo phần phân bổ riêng của sinh viên.
- Sau khi thanh lý, nếu sinh viên không còn hợp đồng `Còn hạn` khác thì chuyển `sinhvien.TrangThaiLuuTru` thành `Đã rời đi`.
- Giao diện: nút "Thanh lý hợp đồng" màu đỏ (`approval-button danger`) hiển thị trong chi tiết hợp đồng khi hợp đồng `Còn hạn`. Đồng bộ hộp thoại xác nhận bằng modal HTML `<dialog class="approval-dialog">` dùng chung hệ thống `approval.css` và `assets/js/approval.js` (thay thế `window.confirm()`), hỗ trợ nút Hủy (`data-close`), đóng bằng phím Escape, nút xác nhận nguy hiểm liên kết form `form="contract-terminate-form"`, và hiển thị thông báo kết quả rõ ràng khi tải lại trang chi tiết.
- Kiểm thử liên quan: `tests/contracts.php` đạt 31/31 ca, `tests/run.php` đạt 106/106 ca.

## UC05.1 - Quản lý và tra cứu hợp đồng (27/10/2026)

- Đã bổ sung route GET `contracts` và `contract-detail` cho đúng vai trò `Nhân viên quản lý KTX`; nối menu Quản lý hợp đồng và trạng thái active của sidebar.
- Có thể tra cứu kết hợp theo mã hợp đồng, MSSV, họ tên, số/mã phòng và trạng thái; để trống toàn bộ tiêu chí sẽ trả toàn bộ hợp đồng. Danh sách phân trang 20 dòng, xem chi tiết hợp đồng/sinh viên/phòng. Truy vấn dùng PDO prepared statement và UC05.1 chỉ đọc dữ liệu.
- UC05.2 đã được chốt nghiệp vụ công nợ và hoàn trả; không thay đổi nghiệp vụ tạo hợp đồng từ UC07.2/UC17.
- Kiểm thử liên quan: `tests/contracts.php` cho Model/dữ liệu và các ca UC05.1 trong `tests/run.php` cho route/phân quyền/phương thức HTTP.
- Giao diện danh sách hợp đồng dùng cùng hệ panel, lưới bộ lọc, bảng, nút và phân trang của trang tra cứu hóa đơn để giữ trải nghiệm nhất quán giữa NVQL và kế toán.

## UC03 - Mật khẩu mặc định trong email duyệt (26/10/2026)

- Email duyệt đơn thêm dòng mật khẩu mặc định theo quy ước demo ngay dưới tên đăng nhập; email từ chối không có dòng này. Mật khẩu tài khoản vẫn được băm bằng `password_hash()`. Đây là thay đổi mẫu nội dung cho thông báo tạo sau khi triển khai; bản ghi `emailthongbao` đã có giữ nội dung cũ.

## UC03 - Brevo HTTPS API cho Render Free (26/10/2026)

- Render Free chặn cổng SMTP 25/465/587. Đã thêm `services/BrevoMailer.php` gửi nội dung text UC03 tới Brevo qua HTTPS và `services/ApprovalMailer.php` chọn phương thức theo `MAIL_TRANSPORT`: mặc định `smtp` cho WAMP, `brevo` cho Render. Cấu hình Render cần `MAIL_TRANSPORT=brevo`, `BREVO_API_KEY`, `MAIL_FROM_ADDRESS` đã đăng ký ở Brevo và `MAIL_FROM_NAME`. Không đưa khóa vào Git; `config/mail.local.php` tiếp tục bị bỏ qua.
- Không đổi nghiệp vụ xét duyệt, hàng đợi `emailthongbao`, trạng thái hay route gửi lại. Đã khôi phục lệnh chạy migration UC17/UC03 khi khởi động Docker trong workspace local để khớp `origin/main`. Theo xác minh hiện tại của người dùng, UC03 Gmail được xem là chạy được; việc xác minh Brevo/Render hoặc Inbox thực tế vẫn là phạm vi riêng khi triển khai.
- Cần cấu hình và xác minh người gửi trên Brevo, đặt biến môi trường rồi deploy Render; sau đó dùng **Gửi lại email** với các đơn trạng thái `Lỗi` và kiểm tra thư nhận. Địa chỉ `@gmail.com` có thể bị Brevo thay From hiển thị; muốn giữ From cần miền riêng được xác thực.

## UC17 - Tối giản thẻ QR (25/10/2026)

- Khi QR đã tạo, thẻ chỉ hiển thị tiêu đề, mã QR và thời gian còn hiệu lực. Đã bỏ các đoạn hướng dẫn/ghi chú và nút mở trang thanh toán trên cùng thiết bị. PR #9 đã merge vào `origin/main`; workspace WAMP local cũng đã đồng bộ thay đổi giao diện. Luồng quét điện thoại và xác nhận thanh toán không đổi.

## UC17 - Giao diện chi tiết hóa đơn đã thu (25/10/2026)

- Khi hóa đơn chuyển `Đã thu`, trang chi tiết chỉ hiển thị thẻ **Hóa đơn [mã]** với trạng thái, số tiền và thời gian thanh toán. Không hiển thị thẻ xác nhận/QR hay nút xem lưu trú bên cạnh. Hóa đơn `Chưa thu` vẫn giữ luồng tạo QR. Bản sửa đã merge vào `origin/main` qua PR #8 và đồng bộ giao diện vào workspace WAMP local.

## UC17 - Tạo QR theo yêu cầu, hiệu lực 5 phút (25/10/2026)

- Danh sách bỏ banner cảnh báo màu vàng; liên kết **Xem hóa đơn** có giao diện nút. GET chi tiết hóa đơn không phát hành QR. Sinh viên bấm **Tạo mã QR** trong chi tiết; POST `student-qr-create` yêu cầu vai trò Sinh viên, rồi chuyển về chi tiết để hiển thị QR. Trang xác nhận trên điện thoại vẫn mở được không cần phiên đăng nhập sinh viên.
- Mỗi hóa đơn chỉ phát hành một mã còn hiệu lực tại một thời điểm, tối đa 5 phút kể từ lúc tạo. Với hóa đơn điện nước, mã QR được gắn theo tài khoản/sinh viên và số tiền phân bổ của sinh viên đó để các sinh viên trong phòng thanh toán độc lập. Tải lại chi tiết trong cùng session hiển thị lại mã cũ, không tạo mã mới. `PaymentLink::issue` dùng named lock và transaction; các bước xem/xác nhận và `Payment::confirmLink` đều kiểm tra lại trạng thái, chủ sở hữu và số tiền. Theo xác minh hiện tại của người dùng, UC17 QR được xem là chạy được; chưa coi đó là xác nhận giao dịch ngân hàng thật.
- Kiểm thử hiện tại: UC17 82/82, auth/route 72/72, frontend 16/16 đạt. Chrome headless kiểm tra list, detail chưa tạo và QR đã tạo ở desktop 1365px/mobile 390px: nút hiển thị đúng, QR chỉ có sau tạo, không tràn ngang hay lỗi JavaScript. Đây là HTML preview từ bảng TEMPORARY; chưa quét bằng điện thoại vật lý hay xác nhận trên dữ liệu thật. Các thay đổi vẫn ở workspace local, chưa commit/push/deploy.

## UC17 - QR liên thiết bị không cần đăng nhập trên điện thoại (25/10/2026)

- Theo yêu cầu mới, sinh viên vẫn đăng nhập trên máy tính để xem hóa đơn và tạo QR; điện thoại quét QR mở trang thanh toán riêng không cần đăng nhập. Đây là thanh toán **mô phỏng**; quét/mở liên kết chưa đánh dấu hóa đơn Đã thu, chỉ nút xác nhận POST mới ghi nhận kết quả. Bản 24/10 bên dưới là trạng thái cũ của chức năng trước khi đổi luồng quét.
- Tạo bảng `phienthanhtoan` qua `sql/uc17-payment-links.sql` và `scripts/migrate-payment-links.php`: lưu SHA-256 token ngẫu nhiên 256 bit, hóa đơn, tài khoản, số tiền, hạn 15 phút và thời điểm đã dùng. Migration đã chạy trên CSDL local, không thay dữ liệu hóa đơn/hợp đồng; khi triển khai nơi khác phải chạy migration trước code mới, không import lại dump. Bảng có FK đến `hoadon` và `taikhoan`.
- QR trên chi tiết hóa đơn dẫn tới `index.php?action=payment-checkout#token`. Fragment không gửi trong GET/Referer; JavaScript xóa fragment khỏi URL hiện tại rồi POST token qua body. GET/POST `payment-checkout` chỉ xem trang/bill tối thiểu, POST `payment-checkout-confirm` kiểm tra token QR và xác nhận, GET receipt hiển thị kết quả trong session của thiết bị đó. Các route này không gọi `requireRole`; route cá nhân `student-*` vẫn yêu cầu Sinh viên. Không đưa MSSV/tên/phòng, mật khẩu hoặc token gốc vào CSDL/log ứng dụng.
- `Payment::confirmLink` sử dụng chung transaction/named lock/xử lý hợp đồng của kế toán và đường sinh viên cũ; khóa dòng token và hóa đơn, kiểm tra lại trạng thái/số tiền/quyền của tài khoản gốc rồi đánh dấu token đã dùng **trong cùng transaction**. Lỗi rollback cả token; link hết hạn/đã dùng, hóa đơn đã thu bởi QR khác hoặc kế toán, tài khoản bị khóa và số tiền đổi đều bị chặn. Hóa đơn điện nước của phòng được chia thành các phần phải thu riêng; mỗi sinh viên chỉ xác nhận phần của mình, và hóa đơn cha chỉ chuyển `Đã thu` khi tất cả phần đã thu. Không tạo hợp đồng từ hóa đơn điện nước.
- Đã cập nhật `docs/02-use-cases/UC17-student-payment.md`, `docs/04-database/DATABASE_DESIGN.md`, README test và giao diện. Truy cập Apache local GET route QR khi không đăng nhập trả HTTP 200. Kiểm thử mới: 60 ca UC17, 68 ca auth/route, 29 ca kế toán, 15 ca frontend đạt; lint các file PHP/JS mới đạt. Chrome headless với HTML sinh từ bảng TEMPORARY xác minh QR giải mã độc lập, desktop 1365px/mobile 390px, trang xác nhận/biên nhận công khai, không tràn ngang hoặc lỗi JS. Chưa thử quét bằng điện thoại vật lý, xác nhận trên dữ liệu thật, hai tiến trình đồng thời trên cùng dữ liệu vật lý hoặc Render.
- Chưa commit/push/deploy; giữ các thay đổi local ngoài UC17. Khi demo trên điện thoại, dùng địa chỉ LAN/HTTPS mà điện thoại truy cập được, không dùng QR sinh từ trang `localhost` trên máy tính. Việc ai có QR sẽ có quyền xác nhận hóa đơn là giới hạn có chủ đích của mô phỏng; không coi đây là xác nhận giao dịch ngân hàng.

## UC17 - Sinh viên thanh toán hóa đơn bằng QR mô phỏng (24/10/2026)

- Triển khai theo mục 2.3.22 của Word `Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx`; bổ sung `docs/02-use-cases/UC17-student-payment.md` và UC17 trong bảng tổng quan. Các mã UC cũ khác nhau giữa tổng quan và đặc tả vẫn được ghi chú, không tự đánh lại mã.
- Menu Sinh viên → Thanh toán đã hoạt động. GET `student-payments` liệt kê/lọc/phân trang hóa đơn của tài khoản hiện tại; GET `student-invoice` xem chi tiết và QR; GET `student-payment` mở mô phỏng; POST `student-payment-confirm` xác nhận. Cả bốn route yêu cầu `requireRole('Sinh viên')`.
- QR tạo bằng thư viện qrcode-generator MIT lưu local; chỉ chứa URL nội bộ/mã hóa đơn, không gửi dữ liệu ra dịch vụ QR. Quét/mở QR không thu tiền. Thiết bị mới phải đăng nhập; AuthController tiếp tục tới hóa đơn đã quét qua route cố định. Có nút mở cùng thiết bị cho demo localhost.
- Phiên giao dịch trong session gắn tài khoản/hóa đơn/số tiền, nonce ngẫu nhiên và hạn 15 phút. Máy chủ đọc lại quyền sở hữu/trạng thái/số tiền; không tin MSSV/MaTK/số tiền từ trình duyệt. Hóa đơn đổi số tiền cần mở lại xác nhận.
- `Payment::settle` dùng chung cho kế toán và sinh viên, cùng named lock UC02/UC03, transaction và khóa dòng. Tiền phòng đầu tiên tạo hợp đồng theo học kỳ hóa đơn, liên kết đơn, chuyển sinh viên Đang ở cùng lúc ghi Đã thu/thời gian thanh toán. Kế toán vẫn giữ phạm vi thu đầu tiên hiện có.
- UC17 thu được hóa đơn tiền phòng định kỳ và điện nước đã tồn tại; không lập hóa đơn mới hoặc tự gia hạn hợp đồng. Điện nước là tổng hóa đơn phòng được chia thành phần độc lập trong `hoadondiennuocsinhvien`; chỉ sinh viên tương ứng được xác nhận phần của mình. Schema đã bổ sung số tiền phân bổ và trạng thái thu của từng sinh viên.
- Kiểm chứng: 208 ca PHP đạt (UC17 35, auth/route 64, kế toán 29, xét duyệt 74, lưu trú 6), 12 ca frontend đạt; lint 10 file PHP đạt và diff check các file theo dõi trong phạm vi không lỗi. Test xét duyệt cần chạy ngoài sandbox để tạo/xóa ảnh kiểm thử trong storage; chạy lại đạt đủ 74 ca. Chrome headless render bốn màn hình bằng HTML từ bảng TEMPORARY ở desktop 1365px/mobile 390px: không tràn trang/lỗi JS; ảnh QR giải mã độc lập ra đúng URL; chặn double-submit. Đây là kiểm tra giao diện với dữ liệu giả, không phải xác nhận thanh toán trên dữ liệu thật qua Apache; chưa thử xác nhận đồng thời từ hai tiến trình trên cùng dữ liệu vật lý.
- Chưa commit/push/deploy; giữ các thay đổi local có sẵn. Không sửa cấu hình Apache hoặc CSDL thật. Tiếp theo: kiểm thử chấp nhận luồng trên môi trường demo/deployment và thiết bị quét thực tế trước khi bàn giao triển khai.

## Email kết quả xét duyệt và múi giờ (20/10/2026)

- Theo yêu cầu mới sau demo: UC02 bắt buộc email hợp lệ tối đa 100 ký tự; form nhập/xác nhận và chi tiết UC03 hiển thị email. Lưu `dondangky.Email`, sao chép sang `sinhvien.Email` khi duyệt; giữ nullable cho hồ sơ cũ.
- UC03 ghi `emailthongbao` cùng transaction duyệt/từ chối. Controller gửi SMTP sau commit/giải phóng khóa nghiệp vụ; email duyệt gồm mã đơn, MSSV/tên đăng nhập, mã hóa đơn, hướng dẫn liên hệ; email từ chối gồm lý do. Không gửi mật khẩu hoặc thêm luồng kích hoạt tài khoản.
- Email lỗi không hủy kết quả xét duyệt. Danh sách hiển thị thông báo cần kiểm tra (tối đa 50), chi tiết có trạng thái và nút gửi lại. Route POST `approval-email-retry` chỉ cho Nhân viên quản lý KTX, yêu cầu đúng vai trò. Khóa theo đơn ngăn gửi đồng thời; bản ghi Đã gửi không gửi lại. SMTP tiếp nhận không đồng nghĩa thư đã đến Inbox; trường hợp mất kết nối sau SMTP chấp nhận vẫn có nguy cơ trùng khi thử lại.
- Migration `sql/uc03-email.sql`/`scripts/migrate-approval-email.php` tạo bảng mới, đã áp dụng local; không nhập dump hoặc sửa hồ sơ thật. Đơn cũ thiếu email ghi Không có email; không tự gửi hồi tố đơn đã xử lý. `scripts/retry-approval-email.php` chạy CLI để thử tối đa 50 thư chờ/lỗi; chưa cài lịch chạy.
- PHP đặt `Asia/Ho_Chi_Minh`; factory PDO đặt MySQL session `+07:00`. Kiểm tra trước sửa: PHP UTC, MySQL local SYSTEM đã tương ứng +07. Sau sửa đồng hồ PHP/MySQL chênh 0 giây; kiểm thử đơn mới khớp giờ Việt Nam. Không sửa DATETIME lịch sử vì chưa xác định nguồn/mức lệch từng bản ghi.
- Cài PHPMailer 7.1.1 qua Composer, có `composer.json`/lock; vendor và `config/mail.local.php` được gitignore. Docker cài dependency và loại config local khỏi image. Người dùng chọn SMTP của trường nhưng chưa được cấp host/cổng/tài khoản; đã chuẩn bị file local trống và hướng dẫn `docs/UC03_EMAIL_SETUP.md`. Chưa gửi email thật.
- Kiểm chứng: 211 ca đạt (registration 31, approval 74, run 48, payment 29, rooms 16, accommodation 6, SMTP adapter 7); 24 file PHP lint đạt. Gửi thư được kiểm tra bằng PHPMailer thật + SMTP giả trong RAM, không gửi ra ngoài. HTTP local đăng ký 200, có input email đúng kiểu/nhãn. Hàng đợi thật local vẫn 0 dòng; các ca ghi dùng bảng TEMPORARY.
- Diff check phạm vi thay đổi không có lỗi; toàn workspace còn khoảng trắng cuối dòng sẵn có ở dump SQL và login ngoài task. Chưa kiểm tra trực quan trình duyệt hoặc Docker/deployment; chưa commit/push.
- Công việc tiếp theo: nhận thông tin SMTP từ trường, điền `config/mail.local.php` (không commit), thử đăng ký/duyệt/từ chối bằng email thử của người dùng và xác minh nhận thư. Khi triển khai, chạy migration trên CSDL đích trước code mới và cấu hình biến môi trường MAIL_*.

## Bắt buộc minh chứng cho diện ưu tiên (19/10/2026)

- UC02 yêu cầu tải ảnh minh chứng khi chọn diện ưu tiên khác `UT00` (Không có). Kiểm tra được thực hiện phía server; thiếu ảnh thì giữ người dùng ở bước nhập thông tin và báo lỗi tại trường minh chứng.
- Kiểm tra: `tests/registration.php` đạt 24/24; PHP lint `RegistrationController.php` và test đạt.

## Đồng bộ đặc tả luồng demo (17/10/2026)

- Bổ sung UC07.2: tiền điều kiện, hậu điều kiện, giao dịch tạo hợp đồng theo học kỳ hóa đơn, liên kết đơn, cập nhật trạng thái sinh viên và thời gian thu; giữ nguyên tài khoản UC03. Mô tả các ngoại lệ và phạm vi chỉ xác nhận tiền phòng đầu tiên.
- Đồng bộ UC03 với cách tính đơn giá đồng/tháng trong code: tính tỷ lệ ngày, tính cả hai ngày biên, làm tròn tổng học kỳ đến đồng. Làm rõ tài khoản được cấp khi duyệt, còn hợp đồng được tạo khi xác nhận thanh toán.
- Cách bàn giao tài khoản thực tế chưa chốt; tài liệu phân biệt thông tin đăng nhập chuẩn bị cho demo với chức năng gửi email/kích hoạt chưa có.
- Chỉ sửa tài liệu, không thay đổi code, schema, route hoặc business rule. Đối chiếu Approval/Payment, controller và giao diện xác nhận; không chạy lại test chức năng cho thay đổi tài liệu.

## Tra cứu hóa đơn theo wireframe UC07 (15/10/2026)

- Bổ sung lọc mã hóa đơn/MSSV/họ tên/số phòng/loại/trạng thái/kỳ tháng-năm, bố cục 4 cột hai hàng; giữ các bộ lọc khi phân trang, mặc định trạng thái Tất cả. Bảng tách MSSV/họ tên/loại/kỳ và STT.
- Payment::listing đọc cả tiền phòng và điện nước, gom sinh viên của hóa đơn điện nước để không lặp hóa đơn. PDO placeholders cho input; controller kiểm tra kiểu/độ dài/kỳ/loại. Không sửa schema hoặc dữ liệu thật.
- Kỳ điện nước dùng Thang/Nam; tiền phòng hiện thu theo học kỳ, chưa có cột kỳ tháng nên bộ lọc kỳ dùng tháng lập hóa đơn, không thay ngày học kỳ. Xác nhận vẫn chỉ tiền phòng đầu tiên thuộc đơn đã duyệt, không mở rộng nghiệp vụ thu điện nước. Bố cục chưa sao chép thao tác checkbox/nút xác nhận cuối bảng của wireframe.
- Kiểm tra tests/payment.php 29/29, tests/run.php 46/46; lint 5 PHP file đạt. Chưa kiểm tra trực quan trình duyệt, chưa commit/push/deploy.

## Sửa dashboard sau thanh toán và đồng bộ vai trò (15/10/2026)

- Dashboard sinh viên hiển thị số phòng đã bố trí cho hợp đồng Còn hạn chưa đến ngày bắt đầu, kèm ngày bắt đầu thay vì ẩn phòng; đổi nhãn thành Phòng được bố trí. Không sửa ngày hợp đồng hoặc dữ liệu thanh toán.
- Dashboard kế toán và quản lý KTX dùng cùng bố cục header/avatar/menu đăng xuất/thẻ tổng quan của dashboard NVQL. Kế toán đọc số hóa đơn tổng/đã thu/chưa thu và tổng tiền chưa thu; quản lý dùng summary hiện có. Giữ requireRole tại từng trang.
- Navbar kế toán gộp thu tiền vào Quản lý hóa đơn; tiêu đề trang hóa đơn đã đồng bộ, cập nhật assertion route tương ứng. Navbar sinh viên có Thanh toán đang disabled.
- Kiểm tra: tests/run.php 46/46, tests/payment.php 22/22, tests/accommodation.php 6/6; lint 7 file đạt. Ca mới xác minh UC03 → thanh toán → dashboard hiện phòng và ngày bắt đầu tương lai, số hóa đơn kế toán cập nhật. Chưa kiểm tra bố cục trực quan bằng trình duyệt; chưa commit/push/deploy.

## Rà soát và tối ưu ứng dụng (15/10/2026)

- Branch `refactor/application-performance`, giữ nguyên các thay đổi chưa commit trước đó. Chưa commit/push/deploy. Báo cáo: `docs/CODE_REVIEW_OPTIMIZATION_2026-10-15.md`.
- config/db.php dùng PDO chung trong request qua applicationDatabaseConnection(); createDatabaseConnection() vẫn dành cho kết nối độc lập. Không cache quyền: kiểm thử thay đổi vai trò/khóa trong bảng tạm chứng minh requireAuth vẫn đọc lại CSDL.
- Room dùng queryRooms chung, allPublicRooms lấy Nam/Nữ trong một SELECT thay hai. availableRoom lọc trực tiếp MaPhong/giới tính, subquery chỉ đếm phòng đó; UC02 bước xác nhận không tải cả danh sách. Công thức sức chứa và named lock/transaction giữ nguyên.
- Dashboard sinh viên gộp số đếm hóa đơn/phản ánh (4 → 3 SELECT khi có hồ sơ); Payment chỉ chọn cột cần dùng. Navbar chuyển sang khai báo route/nhóm action, bỏ nhánh/icon thừa.
- JS lọc phòng cache tham chiếu DOM; JS dialog chống gửi lặp, giữ nguyên nút server disabled khi khôi phục trang. Validate đăng ký chặn UTF-8 sai/NUL và phương thức ngoài GET/POST; UC11 xử lý ngày không hợp lệ an toàn. config/test-db.php chỉ CLI/loopback.
- Kiểm tra: 274/274 PHP test (run 46, registration 23, rooms 16, UC15 33, chatbot-rooms 9, approval 58, payment 20, accommodation 6, chatbot 60, guide 3), 8/8 frontend VM; lint 66 PHP file đạt, toàn bộ JS ứng dụng parse đạt. HTTP local home/login/rooms/guide 200.
- Không đổi business rule/schema, không ghi dữ liệu thật hoặc sửa Docker/Render. Chưa benchmark tải/concurrency thực hoặc kiểm tra bố cục bằng trình duyệt. Tiếp theo: chọn phạm vi commit các UC đang dở, kiểm tra UI và tích hợp trước khi deploy.

## UC13: ngôn ngữ tự nhiên và số liệu phòng công khai (15/10/2026)

- Branch `feature/uc13-public-room-chat`, tạo từ HEAD nhánh thanh toán và giữ các thay đổi chưa commit. Chưa commit/push/deploy.
- Theo yêu cầu mới, bỏ whitelist câu hỏi phải chứa đúng từ khóa KTX. Câu ngắn/không dấu/lỗi gõ được Gemini hiểu theo bối cảnh; vẫn chặn mẫu dữ liệu nhạy cảm trước dịch vụ và Gemini kiểm tra phạm vi lần nữa. Không thêm lịch sử nhiều lượt.
- Mở rộng phạm vi cũ không-PDO: ChatbotRoomSource đọc snapshot chỉ gồm tòa/phòng/tầng/khu/sức chứa/đang ở/đang giữ/còn chỗ và tổng theo tòa, dùng Room UC15 và transaction READ ONLY/REPEATABLE READ. Không danh sách người trong phòng/hồ sơ/đơn/hóa đơn/hợp đồng cá nhân; AI không có công cụ SQL hoặc ghi dữ liệu. Chi tiết tối đa 300 phòng, có metadata cắt danh sách; local hiện 200 phòng, không cắt.
- Tách createDatabaseConnection() vào config/connection.php, config/db.php dùng lại và giữ xử lý lỗi cũ. Adapter chatbot bắt lỗi riêng để FAQ vẫn chạy khi nguồn phòng không tải được; giữ DB environment/fallback, không sửa credential/schema. tests/approval.php dùng factory cho kết nối thứ hai thay phụ thuộc biến cục bộ từ config/db.php.
- Cập nhật kiến thức theo UC11/thanh toán/navbar hiện tại, không tự bịa giá/nội thất. Nguồn lỗi phải báo chưa có số liệu; số chỗ có thể thay đổi trước submit UC02.
- Kiểm tra: chatbot 60/60, nguồn phòng 9/9, login/route 45/45, UC15 33, dữ liệu phòng 14, UC02 20, UC03 58, thanh toán 20, UC11 6 đạt. PHP lint các file chức năng đạt.
- Apache local gọi Gemini thật qua route chatbot-send: Cách đăng ký và đăng ksy lưu trú đều trả hướng dẫn đúng; câu hỏi A101 trả sức chứa 8/còn 8 khớp snapshot DB. Cả ba HTTP 200. Chưa kiểm tra bố cục trực quan hoặc Render. Không thay dữ liệu thực trong task này.

## Đồng bộ header sinh viên (15/10/2026)

- Tách `includes/student-header.php` dùng chung cho dashboard và UC11, bổ sung avatar người dùng/chuông trên trang thông tin lưu trú. Giữ menu Hồ sơ/đăng xuất và đánh dấu Hồ sơ hiện tại; chuông vẫn disabled vì UC12 chưa triển khai.
- Navbar sinh viên đã bỏ mục Thông báo/Chatbot AI theo yêu cầu; nút chatbot trên trang vẫn giữ. PHP lint 3 file header/view đạt, tests/run.php 45/45 đạt. Chưa commit/push/deploy hoặc kiểm tra bố cục trực quan.

## Xác nhận thanh toán cho NVKT (15/10/2026)

- Branch `feature/uc07-payment-confirmation` tạo từ HEAD UC11 local, giữ các thay đổi chưa commit có sẵn. Chưa commit/push/deploy; không sửa cấu hình/schema/CSDL production.
- GET `payments` và POST `payment-confirm` chỉ Nhân viên kế toán; navbar Quản lý hóa đơn/Thu tiền và thanh toán mở trang danh sách hóa đơn tiền phòng đầu tiên. Lọc MSSV/trạng thái, phân trang, hộp thoại xác nhận.
- Payment::confirm dùng chung named lock UC02/UC03, transaction READ COMMITTED và khóa dòng. Xác nhận hóa đơn Chưa thu, tạo hợp đồng Còn hạn đúng ngày của học kỳ trên hóa đơn theo lựa chọn người dùng, liên kết DonDangKy.MaHopDong, cập nhật sinh viên Đang ở, hóa đơn Đã thu và thời gian thu. Lỗi rollback toàn bộ; không đếm trùng giữ chỗ/hợp đồng, không xác nhận lại.
- Tài khoản/mật khẩu đã tạo ở UC03 được giữ nguyên; không tạo lại, không reset mật khẩu. Tài khoản thiếu/khóa, đơn/liên kết sai, kỳ hết hạn, hợp đồng trùng thời gian hoặc phòng vượt sức chứa đều chặn.
- Phạm vi demo chưa thay thế đầy đủ UC07.1/UC07.2 của thành viên khác: chỉ tiền phòng đầu tiên, chưa điện nước/gia hạn. Không tự backfill hóa đơn đã thu nhưng thiếu hợp đồng.
- Kiểm tra: tests/payment.php 20/20 (TEMPORARY, có rollback cuối transaction và nối UC03 → UC11); tests/run.php 45/45, tests/approval.php 58/58, tests/accommodation.php 6/6, PHP lint đạt. Chưa kiểm tra bố cục trực quan trình duyệt hoặc xác nhận hóa đơn thật.
- Hợp đồng tương lai hiển thị ở UC11 theo Còn hạn; dashboard còn xét ngày, có thể báo Chưa bắt đầu/Chưa có phòng trước kỳ. Tiếp theo: kiểm tra UI và tích hợp nhánh UC11 + thanh toán trước khi deploy Render. Chi tiết docs/UC07_PAYMENT_IMPLEMENTATION_NOTES.md.

## Kiểm tra duyệt đơn local/Render (14/10/2026)

- Apache local có hocky và đơn giá Tiền phòng trống. Theo xác nhận người dùng, thêm sáu học kỳ 2026-2027/2027-2028 đúng dump và đơn giá 200.000 đồng/tháng/sinh viên (ROOM200K, NgayApDung 14/10/2026) bằng transaction có kiểm tra bảng trống; không import dump hoặc sửa đơn/hồ sơ. Script tạm đã xóa.
- Giao diện local sau tải lại đã hiển thị Kỳ 2 16/01/2027–31/05/2027, đơn giá 200.000, tổng 903.226 đồng, nút Xác nhận hoạt động. Chưa bấm duyệt đơn thật trong lượt này.
- Kiểm tra tab Render báo phiên xác nhận không hợp lệ: đơn trước đó hiện Đã duyệt, danh sách hiển thị thông báo duyệt thành công và mã hóa đơn. Model chặn duyệt lại đơn đã xử lý và không tạo tài khoản hay hóa đơn trùng.
- Render đã có đúng đơn giá 200.000 và kỳ thu như trên; xác minh dialog của một đơn Chờ duyệt, chưa bấm Xác nhận và không sửa CSDL production. UC11 hiện chưa push/deploy.
- Thêm ca tests/approval.php cho token cũ sau duyệt: bị chặn 403, không tạo trùng tài khoản. Bộ UC03 đạt 58/58.

## UC11 - Xem thông tin lưu trú (14/10/2026)

- Kiểm tra Apache local sau phản hồi thiếu thông tin: sinhvien có 0 bản ghi, hopdong có 0 bản ghi; taikhoan có 4 (1 tài khoản Sinh viên), dondangky có 3. Tài khoản sinh viên hiện chưa được liên kết hồ sơ nên UC11 hiển thị thông báo thiếu hồ sơ. Không tự tạo/ghép dữ liệu từ đơn chưa duyệt. Đã bỏ nút Quay lại dashboard và CSS tương ứng; 3 ca view UC11 đạt, PHP lint view đạt. Probe localhost đã xóa.

- Branch `feature/uc11-accommodation-info` tạo từ HEAD local UC13 để giữ nguyên các thay đổi đang làm; chưa commit/push. Workspace còn thay đổi UC13, guide, login, dashboard và dump SQL từ trước, cần tách phạm vi khi commit.
- GET `index.php?action=accommodation` chỉ dành Sinh viên; sidebar Thông tin lưu trú và menu tài khoản Hồ sơ mở cùng route. POST trả 405; khách chuyển đăng nhập, vai trò khác trả 403.
- `Accommodation` đọc hồ sơ theo MaTK của phiên, không sử dụng MSSV/MaTK từ URL. Đọc sinhvien/hopdong/phong/toanha qua PDO prepared statements; không ghi dữ liệu/schema. View escape dữ liệu, hiển thị hồ sơ, hợp đồng và phòng theo wireframe UC11; thiếu hồ sơ hoặc hợp đồng có thông báo riêng.
- Theo đặc tả chi tiết UC11, hợp đồng được chọn theo TrangThai Còn hạn, không thêm điều kiện ngày/trạng thái sinh viên. Nếu nhiều hợp đồng Còn hạn, lấy NgayBatDau mới nhất rồi MaHopDong giảm dần. Dashboard hiện có điều kiện ngày/Đang ở khác; chưa đổi nghiệp vụ này. Bảng UC tổng quan còn mã UC lệch đặc tả chi tiết.
- Khôi phục dispatch UC03/UC15 đã có trong main nhưng đang thiếu ở index.php local; giữ dispatch chatbot và guide.
- Kiểm thử tests/accommodation.php 6/6 bằng bảng TEMPORARY, tests/run.php 36/36 gồm 7 ca UC11; PHP lint đạt. HTTP Apache local xác minh khách chuyển tới đăng nhập và CSS truy cập được. Chưa kiểm tra bố cục trực quan desktop/mobile hoặc deployment.
- Tiếp theo: thử bằng tài khoản sinh viên qua sidebar/Hồ sơ; phối hợp UC07.2 tạo/liên kết hợp đồng sau thanh toán đầu tiên. Chốt thống nhất ý nghĩa Còn hạn với dashboard trước khi đổi business rule; không tạo hợp đồng trong UC11.

## Sửa route hướng dẫn local (14/10/2026)

- Bổ sung dispatch `index.php?action=guide` tới `GuideController::show()` trước khi render trang chủ; route bị thiếu khiến chọn Hướng dẫn hiển thị trang chủ.
- Kiểm tra `tests/guide.php` đạt 3/3, PHP lint `index.php` đạt. HTTP Apache local trả 200, có tiêu đề hướng dẫn và CSS guide; chưa kiểm tra bố cục trực quan bằng trình duyệt. Không commit/push.

## 1. Tổng quan dự án

Tên dự án:

Hệ thống Quản lý Ký túc xá Kanto

Công nghệ hiện tại:

- PHP 8.x
- MySQL
- PDO
- Apache
- Docker
- Git / GitHub
- Render
- Aiven MySQL
- DBeaver

---

## 2. Môi trường local

Project local chạy bằng WAMP.

Đường dẫn project:

`C:\wamp64\www\Kanto-KTX`

Local URL:

`http://localhost/Kanto-KTX/`

Local database:

`qlktx_v2`

Khi không có Environment Variables, `config/db.php` fallback về:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `qlktx_v2`
- User: `root`
- Password: rỗng

Không được phá khả năng chạy local trên WAMP.

---

## 3. Môi trường production

Project đã được deploy online.

Web hosting:

Render

Public domain hiện tại:

`https://kanto-ktx.onrender.com`

Render đã liên kết trực tiếp với GitHub repository.

Branch production:

`main`

Render Auto Deploy đang dùng source từ branch `main`.

Khi code được merge và push vào `main`, Render có thể tự động build và deploy phiên bản mới.

Không sửa cấu hình deployment nếu không cần thiết.

---

## 4. Docker

Project đã được chuẩn bị để deploy bằng Docker.

Các file liên quan hiện có:

- `Dockerfile`
- `.dockerignore`
- `config/docker-apache.conf`
- `config/docker-php.ini`

Không tự xóa hoặc thay đổi Docker config nếu chức năng đang triển khai không liên quan đến deployment.

---

## 5. Database production

Database production đang được host trên:

Aiven MySQL

Database hiện đang dùng:

`defaultdb`

Dữ liệu schema của project đã được import vào Aiven.

Ứng dụng trên Render kết nối Aiven qua Environment Variables.

Các key đang sử dụng:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

Không hard-code các giá trị production vào source code.

Không commit:

- password
- token
- URI chứa credential
- `.env`
- secret

---

## 6. Kết nối database trong source

`config/db.php` phải hỗ trợ cả:

### Local

WAMP + MySQL local

### Production

Render + Aiven MySQL

Production lấy cấu hình từ Environment Variables.

Không thay đổi cách kết nối database nếu không thực sự cần thiết cho UC đang triển khai.

---

## 7. Quản lý database

Database production hiện được quản lý bằng:

DBeaver

DBeaver đã kết nối thành công tới Aiven MySQL.

Có thể dùng DBeaver để:

- xem dữ liệu
- kiểm tra bảng
- chạy SQL
- kiểm tra kết quả sau khi thao tác trên website

Không cần tạo thêm công cụ quản trị database trong source code.

---

## 8. Encoding

Database và ứng dụng hiện sử dụng UTF-8 / `utf8mb4`.

Vấn đề hiển thị tiếng Việt ở Aiven trước đây đã được xử lý.

Không thay đổi charset/collation nếu không có lý do rõ ràng.

---

## 9. Git workflow

Không code trực tiếp trên `main` cho chức năng mới.

Mỗi Use Case triển khai trên branch riêng.

Ví dụ UC03:

`feature/uc03-approval`

Quy trình:

1. Cập nhật `main`.
2. Tạo branch feature.
3. Code.
4. Test.
5. Commit.
6. Push branch.
7. Tạo Pull Request.
8. Review.
9. Merge vào `main`.
10. Render auto deploy sau khi `main` thay đổi.

Không merge code chưa test vào `main`.

---

## 10. Trạng thái chức năng hiện tại

### Hoàn thành

- UC01 - Đăng nhập
- Authentication
- Session
- Role middleware
- CLI tests cho login/auth
- UC02 - Đăng ký lưu trú
- Dữ liệu diện ưu tiên
- Dữ liệu tòa nhà/phòng
- Chọn phòng
- Deploy Render
- Aiven MySQL
- DBeaver quản lý database

### Đang chuẩn bị triển khai

- UC17 thanh toán mô phỏng QR đã triển khai local ngày 24/10/2026; xem mục UC17 đầu tài liệu. Chưa triển khai ngân hàng thật.

### UC03 hiện tại

- UC03 đã có danh sách/phân trang, chi tiết, ảnh bảo vệ, duyệt và từ chối;
  code đã merge vào main qua PR #2. Chưa xác minh deployment trong phiên này.
  Xem `docs/UC03_IMPLEMENTATION_NOTES.md`.

---

## 11. UC02 hiện tại

UC02 đã hoạt động trên production.

Luồng chính:

1. Khách nhập thông tin.
2. Chọn diện ưu tiên.
3. Hệ thống lọc phòng phù hợp.
4. Người dùng chọn phòng.
5. Xác nhận.
6. Backend kiểm tra lại sức chứa.
7. Tạo `DonDangKy`.
8. `TrangThai = "Chờ duyệt"`.

Số chỗ khả dụng:

`SucChua - số HopDong Còn hạn - số DonDangKy Chờ duyệt - số DonDangKy Đã duyệt có MaHopDong NULL`

Một đơn `Chờ duyệt` giữ đúng 1 chỗ.

---

## 12. Quy tắc trước khi triển khai UC03

Trước khi sửa code UC03, phải đọc:

- đặc tả UC03 trong `docs/02-use-cases/`
- Sequence Diagram UC03
- Wireframe UC03
- `DATABASE_DESIGN.md`
- schema SQL hiện tại
- source code UC02
- authentication / role middleware
- code hiện có liên quan đến `DonDangKy`, `SinhVien`, `TaiKhoan`, `HopDong`

Trước khi code, phải báo lại:

- file nào có thể tái sử dụng
- file nào cần tạo mới
- model/helper/middleware nào đã có
- bảng nào sẽ đọc
- bảng nào sẽ ghi
- transaction nào cần dùng
- các điểm mâu thuẫn giữa tài liệu và schema nếu có

Không tự bổ sung nghiệp vụ.

---

## 13. Quy tắc bảo vệ phần đã ổn định

Không tự ý sửa:

- Docker config
- Render config
- Aiven config
- `config/db.php`
- authentication
- session
- role middleware
- schema database
- UC02

trừ khi UC03 thực sự yêu cầu và phải báo trước.

Nếu phát hiện vấn đề liên quan các phần trên, chỉ báo lại trước khi sửa.

## 14. Bàn giao UC03 (10/10/2026)

- Người dùng đã cho phép sửa UC02/schema để duyệt tạo tài khoản, sinh viên và hóa đơn
  tiền phòng đầu tiên, giữ chỗ chờ thanh toán. Không tạo hợp đồng khi duyệt.
- Hóa đơn thu trọn học kỳ kế tiếp gần nhất sau kỳ hiện tại, kể cả sang năm mới.
  Giá tiền phòng mới nhất có hiệu lực tại ngày duyệt, đơn vị đồng/tháng/sinh viên
  (giá trị DonViTinh trong CSDL: `đồng/tháng`). Thu cả kỳ bằng tổng tiền từng tháng;
  tháng không đủ = đơn giá × số ngày tính tiền / số ngày của tháng, tính cả hai ngày biên.
  Chỉ làm tròn tổng hóa đơn đến đồng; UI hiển thị riêng đơn giá tháng và tổng kỳ.
  Chưa giới hạn thời gian mở UC02; không tính tiền theo phần kỳ cũ còn lại.
- Tên đăng nhập MSSV, mật khẩu được băm theo quy ước demo. Không Gmail.
- Trạng thái sinh viên mới Chờ thanh toán; thêm dondangky.MaHoaDon và MaHopDong
  nullable/UNIQUE/FK. Migration `sql/uc03-approval.sql` đã chạy trên local qlktx_v2;
  chưa áp dụng Aiven. Dump mới đồng bộ; không import dump vào CSDL hiện có.
- Ưu tiên UT00/UT01/UT02/UT03 = 0/2/4/3, xếp giảm dần rồi thời gian gửi/mã đơn.
- UC02 thêm kiểm tra MSSV/CCCD đã có sinh viên (cả nhập và submit), giữ chỗ Đã duyệt
  chưa có MaHopDong. UC03 cùng named lock UC02, READ COMMITTED, khóa phòng rồi đơn,
  commit tài khoản/sinh viên/hóa đơn/trạng thái đơn cùng nhau; lỗi rollback toàn bộ.
- Các route `index.php?action=approval`, approval-detail, approval-proof,
  approval-approve và approval-reject chỉ dành NVQL. POST ghi dữ liệu kiểm tra mã đơn và trạng thái.
- Ảnh tùy chọn; chỉ endpoint có quyền đọc, đường dẫn giới hạn storage/registration;
  UI phân biệt không nộp ảnh và tệp không còn. Không đổi storage/deployment.
- Dump `sql/qlktx_v2.sql` mới có sáu học kỳ của 2026-2027 và 2027-2028;
  bảng đơn giá trong dump còn rỗng. Cần đơn giá tiền phòng `đồng/tháng` có hiệu lực
  để thử duyệt qua UI. Không tự thêm seed nghiệp vụ; test dùng TEMPORARY riêng.
- Những việc tiếp theo: người dùng sửa Sequence/Wireframe; cập nhật ERD/bản thảo;
  chốt thời hạn hợp đồng, thời hạn giữ chỗ/hủy hồ sơ và giao tài khoản; triển khai UC
  thanh toán tạo hợp đồng + liên kết MaHopDong cùng transaction; Timer bỏ hóa đơn trùng kỳ.
- Không đổi Docker/Render/Aiven/config DB/authentication/session/middleware, không xóa Email.
- Kiểm tra dữ liệu đơn Đã duyệt cũ trước migration production: nếu đã có hợp đồng,
  cần liên kết MaHopDong đúng để tránh đếm lại giữ chỗ; không tự backfill theo phỏng đoán.
- Kiểm tra ngày 10/10/2026: UC03 51, đăng nhập/phân quyền 28, UC02 20, dữ liệu phòng 14
  kiểm thử đều đạt (113 tổng). PHP lint đạt; local có đủ hai FK mới, không có đơn Đã duyệt cũ.
  Chưa kiểm thử đồng thời hai tiến trình trên cùng dữ liệu vật lý hoặc UI trong trình duyệt.
- Checklist bàn giao: `docs/UC03_IMPLEMENTATION_NOTES.md`. Giả định mở đăng ký một tháng
  trước hết kỳ chỉ dùng để trình bày đồ án; không có yêu cầu tự chặn UC02 theo lịch.

## 15. Đơn giá tháng (10/10/2026)

- Branch `fix/approval-monthly-rate`: Approval tính tổng kỳ kế tiếp theo đơn giá tháng,
  không đổi schema hoặc dữ liệu MySQL. Mức người dùng chốt: 200.000 đồng/sinh viên/tháng.
- Test UC03 cập nhật sang đơn giá tháng, thêm kỳ 1/2/3, năm nhuận, một ngày và ngày đảo ngược;
  57 kiểm thử đạt. Chưa thử giao diện trực tiếp trong trình duyệt.

## 16. UC15 - Xem thông tin phòng (11/10/2026)

- Đơn giá tháng đã commit/push `1770093` trên `fix/approval-monthly-rate`.
  Branch `fix/uc15-room-information` đã cập nhật nền bằng commit này.
- UC15 tích hợp route công khai GET `index.php?action=rooms`, link trên header chung.
  Chọn khu Nam/Nữ, tòa và tầng theo CSDL; mặc định tòa/tầng đầu tiên của khu Nam.
- Model `Room` dùng chung truy vấn phòng với `Registration::rooms`; UC02 chỉ trả
  phòng khả dụng > 0, UC15 trả cả phòng đầy. Không đổi transaction hoặc nghiệp vụ UC02/UC03.
- Khả dụng = SucChua − hợp đồng Còn hạn − đơn Chờ duyệt − đơn Đã duyệt có
  MaHopDong NULL. Hiển thị riêng đang ở/đang giữ/khả dụng; số khả dụng âm hiển thị 0/Đầy.
- Bộ lọc sai trả 400, phương thức khác GET trả 405, lỗi CSDL trả 500 với thông báo an toàn.
  Không đọc hồ sơ cá nhân hoặc ghi dữ liệu; không thay schema/config DB/auth/session/deploy.
- Code MVC và CSS giao diện nằm trong controllers/models/views/assets, không phụ thuộc
  file đối tác. Toàn bộ `docs/Partner` không đưa vào commit theo yêu cầu người dùng.
  UC15 đã commit/push `5d20973`; PR #3 đã merge vào main ngày 11/10/2026 bằng
  commit `0766c7b`, gồm UC15 và đơn giá tháng. Chưa xác minh deployment sau merge.
  Bản dump SQL vẫn chưa commit; các thay đổi local của đối tác vẫn giữ nguyên.
- Sau khi push, người dùng muốn xem bản gốc đối tác: khôi phục code gốc UC15 trong
  `docs/Partner/uc15.php` (không chuyển hướng), sửa đường dẫn config/CSS/logo và link
  trang có sẵn cho UC15/UC5. Giữ logic đối tác để review; UC15 gốc vẫn truy vấn cột
  `hienco` không có trong CSDL nên không hiện phòng. Không sửa schema để chạy bản gốc.
- UC15 cần schema UC03 đã migrate. Dump `sql/qlktx_v2.sql` đang là thay đổi riêng,
  chưa commit theo yêu cầu người dùng; không coi dump này là schema bàn giao UC15.
- Đặc tả UC15 đã đồng bộ; Sequence/Wireframe còn cần sửa theo code và dữ liệu thật.
- Kiểm tra: UC15 33, dữ liệu phòng 14, UC02 20, UC03 57, route/login/phân quyền 29
  đều đạt (153 tổng), PHP lint đạt. HTTP local WAMP kiểm tra route/filter/redirect/CSS đạt.
  Chưa kiểm tra bố cục trực quan trong trình duyệt hoặc production.
- Công việc tiếp theo: thử giao diện UC15 trên desktop/mobile, cập nhật hai hình UC15;
  kiểm tra migration UC03 trước deployment. Xem `docs/UC15_IMPLEMENTATION_NOTES.md`.
- Tài liệu đối chiếu cho thành viên: `docs/UC15_PARTNER_CODE_MAP.md`, chỉ rõ phần
  UC15 gốc được viết lại ở model/controller/view/CSS, phần dùng chung UC02 và UC5
  vẫn giữ tại docs/Partner. Tài liệu được tạo sau commit UC15, chưa commit/push.

## 17. Bản thảo Chương 2 (11/10/2026)

- Người dùng cần bản thảo để tự gửi thầy trước 20:00; xác nhận không có yêu cầu
  riêng ngoài hướng dẫn đồ án trong repository. Không gửi thư, commit hoặc push trong task này.
- Báo cáo nằm tại `docs/reports/chapter2/`, gồm bản Word, Markdown và checklist
  `RA_SOAT_TRUOC_KHI_GUI.md` (có gợi ý thư gửi). Tên chương theo hướng dẫn:
  “PHƯƠNG TIỆN NGHIÊN CỨU - PHƯƠNG PHÁP NGHIÊN CỨU”.
- Nội dung: phương pháp phân tích yêu cầu, tác nhân/Use Case, quy trình, kiến trúc
  MVC, dữ liệu 17 bảng, giao diện và kế hoạch kiểm thử; 11 hình mới, 13 bảng.
  Dựa trên Chương 1, đặc tả chi tiết, code hiện hành và metadata schema local;
  không dùng bản ghi cá nhân hoặc tự nhận đã khảo sát/triển khai đầy đủ các UC.
- Đồng bộ thiết kế UC03: duyệt tạo tài khoản/sinh viên chờ thanh toán/hóa đơn,
  chưa tạo hợp đồng; giữ chỗ tới bước tiếp nối. Tiền phòng theo đơn giá tháng;
  UC02/UC15 dùng chung công thức khả dụng từ hợp đồng và đơn.
- Checklist chỉ rõ mã UC tổng quan lệch đặc tả chi tiết; UC05.2/business rules
  còn trống, UC13 thiếu đặc tả riêng; các chính sách giữ chỗ, hợp đồng sau thu,
  điện nước và tác vụ định kỳ còn cần chốt. Không đổi nghiệp vụ hoặc schema.
- Công cụ dựng/kiểm tra nằm tại `docs/reports/chapter2-tools/`; snapshot cấu trúc
  chỉ có metadata, không có credential/bản ghi. XML, nội dung Word/Markdown,
  11 hình nhúng và 13 bảng đã kiểm tra; Word đã mở và phân trang 29 trang.
  Đã xem hình trang đầu, ERD và trang cuối; xuất PDF qua Word không hoàn tất
  trên máy hiện tại, nên file nộp là Word/Markdown.
- Tiếp theo: nhóm rà soát bản thảo, ghép vào báo cáo tổng, chỉnh số trang/mục lục
  và định dạng theo phụ lục trường; hoàn thiện các đặc tả còn thiếu trước Chương 3.

## 18. Danh sách cần sửa nghiệp vụ và sơ đồ (11/10/2026)

- Tạo `docs/NGHIEP_VU_CAN_SUA_VA_DONG_BO.md`: 18 mục đối chiếu cụ thể,
  phân biệt lỗi cần sửa, kiểm tra cần bổ sung và chính sách cần nhóm chốt.
- Xem trực tiếp PNG: file `UC03-approval-sequence.png` đang chứa luồng tra cứu
  sinh viên; UC15 thiếu lọc khu/tầng và đơn Đã duyệt chưa có MaHopDong; UC02 cần
  làm rõ giữ chỗ bằng đơn; UC07.2 thiếu bước tiếp nối thanh toán đầu tiên;
  UC10 thiếu thể hiện đủ bảng hóa đơn chi tiết/liên kết. ERD gốc thiếu liên kết UC03.
- Bổ sung vấn đề hiệu lực hợp đồng: Room/Approval tính theo trạng thái Còn hạn,
  dashboard có thêm kiểm tra ngày/trạng thái sinh viên; cần thống nhất ý nghĩa
  và cơ chế cập nhật, chưa tự đổi công thức đã chốt.
- Phát hiện lỗi trong bản thảo vừa tạo: UC06 phải là NVQL theo đặc tả, sequence,
  Use Case và wireframe hiện hành. Đã sửa actor trong Markdown và hình Use Case,
  dựng bản `BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx`; không đổi actor trong
  nghiệp vụ/code gốc. Word ban đầu đang được người dùng mở/chỉnh sửa nên không
  ghi đè; các sửa thủ công cần ghép lại khi chọn dùng bản đồng bộ từ Markdown.
- Chưa sửa các sơ đồ gốc, đặc tả, schema hay code ứng dụng; tài liệu hướng dẫn
  nhóm ưu tiên UC03/UC15/UC02, thanh toán, tiền phòng và ERD trước.

## 19. Bổ sung wireframe vào Chương 2 đã chốt (11/10/2026)

- Người dùng chốt `docs/reports/chapter2/Chuong_2_Kanto_KTX_da_sua.docx` và
  yêu cầu bổ sung mục 2.7.3 bằng wireframe đã cung cấp. Đây là nguồn ưu tiên cho
  các lần chỉnh sửa báo cáo tiếp theo; không dựng lại từ Markdown bản thảo cũ.
- Giữ nguyên nguồn, tạo `Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx`
  cùng thư mục; thêm 2.7.3 ngay sau 2.7.2 và trước tiêu đề 2.8.
- Nhúng 25 PNG gốc từ `docs/05-ui/wireframes/`, chia 7 nhóm, có chú thích
  Hình 2.8–2.32 và mô tả ngắn. Không chỉnh sửa ảnh hoặc nghiệp vụ đã chốt.
- `add-approved-wireframes.php` giữ lại các phần DOCX và kiểm tra 285 thành phần
  nội dung gốc ngoài mục bổ sung không đổi; XML hợp lệ, ảnh mới trùng byte nguồn.
  Word mở/phân trang thành công: 60 trang, 32 hình (7 cũ + 25 mới).
  Đã xem trang phần bổ sung, ảnh và chú thích cùng trang trong các trang đã xem.
- Checklist và README công cụ đã chỉ rõ bản mới nhất. Không sửa code/schema,
  không commit/push hoặc gửi thư. Tiếp theo: người dùng mở bản mới, cập nhật
  mục lục/danh mục hình khi ghép báo cáo tổng và tự gửi thầy.

## 20. Lưu tài liệu bằng commit local (12/10/2026)

- Branch `docs/project-handover-chapter2` lưu tài liệu UC15, danh sách đồng bộ
  nghiệp vụ, báo cáo Chương 2 đã chốt, các bản thảo cũ và công cụ theo nhóm commit.
- Theo yêu cầu mới, `docs/Partner` cũng đã commit local làm tài liệu tham khảo;
  các ghi chú trước đó về việc giữ ngoài commit mô tả phạm vi bàn giao UC15 cũ.
  Chưa tích hợp UC5; code đối tác vẫn giữ các hạn chế đã ghi trong tài liệu review.
- Không push branch hoặc các commit tài liệu trong đợt này theo yêu cầu người dùng.
- `sql/qlktx_v2.sql` vẫn giữ ngoài commit vì chứa dữ liệu tài khoản/đơn đăng ký.
- PHP lint đạt cho 2 file đối tác và 7 công cụ PHP; không chạy công cụ dựng lại
  báo cáo, không thay code ứng dụng hoặc schema trong đợt commit tài liệu này.

## 21. UC16 - Xem hướng dẫn (12/10/2026)

- Branch `feature/uc16-guide` tạo từ branch tài liệu local để giữ phần bàn giao.
- Route công khai GET `index.php?action=guide`, link Hướng dẫn trên header chung.
- `GuideController` và `views/guide/information.php` hiển thị nội dung tĩnh theo
  đặc tả/wireframe UC16 và UC02 hiện tại: nhập thông tin, chọn phòng, xác nhận
  gửi, chờ xét duyệt. CSS riêng `assets/css/guide.css` hỗ trợ màn hình nhỏ.
- Không truy vấn CSDL, không tạo/sửa đơn hoặc phiên đăng ký; POST trả 405.
  Không đổi schema, auth, UC02 hoặc cấu hình deployment.
- Test route riêng `tests/guide.php` đạt 3/3; PHP lint các file thay đổi đạt.
  PHP server tạm kiểm tra GET/CSS 200, POST 405. Apache local không kết nối được;
  bộ test chung `tests/run.php` lỗi kết nối MySQL, chưa xác minh hồi quy toàn bộ.
  Chưa kiểm tra bố cục bằng trình duyệt hoặc deployment.
- Tiếp theo: kiểm tra giao diện desktop/mobile và chỉ push khi người dùng yêu cầu.

## 22. UC13 - Chatbot AI (12/10/2026)

- Branch `feature/uc13-ai-chatbot`. Nguồn đặc tả: mục 12/UC-13 trong
  `docs/sources/Mô tả UC và sơ đồ trình tự.docx`, sequence và wireframe UC13.
  Khách và sinh viên đều được dùng, không cần đăng nhập. Bổ sung đặc tả Markdown,
  cập nhật actor trong bảng tổng quan và nguồn PlantUML; PNG Use Case chưa dựng lại.
- Nút chatbot trang chủ/dashboard sinh viên mở cửa sổ chat; sidebar sinh viên mở
  cùng cửa sổ. Có gửi bất đồng bộ, đóng/mở, trạng thái chờ, lỗi và văn bản thuần.
- POST `index.php?action=chatbot-send`, tối đa 500 ký tự mỗi câu hỏi.
  Không dùng PDO, không đọc/ghi dữ liệu nghiệp vụ, không lưu nội dung chat trên server.
- `Chatbot` dùng kiến thức công khai cố định và Gemini generateContent API theo yêu cầu
  mới của người dùng; `GEMINI_API_KEY`, `GEMINI_MODEL` (mặc định `gemini-3.5-flash-lite`),
  cURL/mbstring và CA hợp lệ. Thiếu key trả lỗi E3. Không tự đọc `.env`, không
  hard-code key, không thay Docker/config DB. Không dùng cấu hình OpenAI trước đó.
  Không tools; JSON Schema kiểm soát định dạng kết quả.
- Chặn mẫu nhạy cảm/ngoài phạm vi trước dịch vụ, AI kiểm tra phạm vi lần nữa;
  bộ lọc từ khóa không đảm bảo chặn mọi dữ liệu cá nhân tự nhập. AI không có
  quyền/công cụ truy cập CSDL. Câu gửi độc lập, chưa có ngữ cảnh nhiều lượt.
- Kiểm thử chatbot OpenAI 36/36; PHP lint model/test đạt. Kiểm thử đăng nhập/phân quyền
  29/29 và hướng dẫn 3/3 đạt ở lượt triển khai UC13 ban đầu; đổi provider không sửa auth/UI.
  Chrome local xác minh mở cửa sổ, gửi/nhận từ chối ngoài phạm vi và lỗi thiếu cấu hình.
  Chưa kiểm tra Gemini thật, giao diện mobile hoặc deployment; chưa commit/push.
- Tiếp theo: cấu hình key/model phía máy chủ, thử câu hỏi chung với Gemini thật;
  cập nhật kiến thức khi UC tiếp nối hoàn thành. Hướng dẫn tại UC13-ai-assistant.md.

### Chuyển Gemini (13/10/2026)

- Theo yêu cầu người dùng, thay OpenAI bằng Gemini generateContent, mặc định
  `gemini-3.5-flash-lite`; key qua header `x-goog-api-key`, không nằm trong URL.
- Apache WAMP cần `GEMINI_API_KEY` và tùy chọn `GEMINI_MODEL`, rồi restart dịch vụ.
  Model mặc định có Free Tier theo tài liệu Google; quota tùy project/model.
- Kiểm tra JSON, finishReason, prompt/candidate bị chặn; bỏ qua thought.
  Free Tier có thể dùng dữ liệu để cải thiện sản phẩm Google; không tuyên bố
  provider không lưu dữ liệu. Không có tùy chọn store=false trong request này.
- Kiểm thử Gemini 40/40 và PHP lint model/test đạt. Chưa thử API Gemini thật
  vì cần key phía máy chủ; chưa commit/push. Giữ nguyên UI, route và CSDL.

### Ghi nhận lỗi WAMP (13/10/2026)

- Kiểm tra trực tiếp qua Apache: đã nhận GEMINI_API_KEY, model
  gemini-3.5-flash-lite và có cURL. curl.cainfo chưa được cấu hình.
- Gọi Gemini với payload chatbot thất bại cURL errno 60 (xác minh chứng chỉ TLS),
  HTTP upstream 0; chưa nhận phản hồi Gemini, chưa kết luận key/quota hợp lệ.
- Công cụ Windows UI không kết nối native pipe, nên chưa thực hiện nhấn nút
  Chatbot AI trong trình duyệt. Probe tạm chỉ cho localhost và đã xóa sau kiểm tra.
- Tiếp theo: cấu hình CA certificate hợp lệ cho PHP Apache rồi thử lại;
  không tắt xác minh TLS. Chưa sửa cấu hình WAMP trong lượt ghi nhận này.
- POST chatbot-send thực tế trả HTML trang chủ HTTP 200 vì index.php thiếu route;
  đã bổ sung dispatch tới ChatbotController trước khi render HTML.

### Sửa cấu hình kết nối Gemini (13/10/2026)

- Xác minh PHP Apache đã nhận key, cURL và file CA hợp lệ. GEMINI_MODEL thực tế
  đang dùng tên hiển thị `Gemini 3.5 Flash Lite`, gây model không hợp lệ/URL malformed.
  Sửa httpd.conf ngoài repository sang `gemini-3.5-flash-lite`, có bản sao lưu.
- Code chuẩn hóa hai tên hiển thị Gemini 3.5 Flash Lite/Flash-Lite sang mã API;
  giữ nguyên model API khác. Tổng timeout tăng 25 lên 60 giây; log lỗi chỉ có
  curl_errno/http_status, không có credential hoặc nội dung chat. Giữ xác minh TLS.
- Kiểm thử chatbot 44/44, PHP lint model/test đạt. Probe localhost đã xóa.
- Gọi API thật với mã model đúng có lần nhận upstream HTTP 503 UNAVAILABLE;
  lần kiểm tra route chatbot cuối vẫn HTTP 503, log curl_errno=28/http_status=0
  sau 60 giây. Chưa xác minh được câu trả lời AI thành công hoặc quota/key hợp lệ.
- Restart-Service Apache bị từ chối quyền Windows. Code chuẩn hóa tên giúp xử lý
  cấu hình cũ ngay; cần restart Apache qua WAMP để nhận httpd.conf mới.
  Tiếp theo: thử lại khi dịch vụ phản hồi; nếu vẫn lỗi kiểm tra trạng thái/quota
  project Gemini. Chưa commit/push, không đổi provider hoặc CSDL.

### Đồng bộ timeout và chẩn đoán phản hồi (13/10/2026)

- Phát hiện JavaScript tự abort sau 30 giây, ngắn hơn timeout máy chủ 60 giây.
  Đổi trình duyệt sang 70 giây để nhận phản hồi máy chủ. HTTP 504 báo dịch vụ AI
  phản hồi quá chậm; upstream 503 báo tạm thời quá tải, vẫn không lộ lỗi upstream.
- GET model Gemini 3.5 Flash Lite qua Apache/key đang cấu hình trả HTTP 200
  trong khoảng 0,22 giây; tạo câu trả lời tối giản vẫn timeout sau 45 giây.
  Thử riêng model 3.8 Flash để chẩn đoán nhận HTTP 503 với thông báo high demand;
  không đổi model cấu hình chatbot. Chưa có câu trả lời AI thành công.
- Kiểm thử chatbot 46/46, PHP lint model/controller/test đạt; JavaScript biên dịch
  bằng node:vm.Script thành công. Probe localhost đã xóa. Chưa kiểm tra giao diện
  bằng trình duyệt hoặc commit/push. Tiếp theo: thử lại dịch vụ Gemini khi hết
  quá tải, kiểm tra route thật và nhận câu trả lời hợp lệ trước khi chốt UC13.
- Kiểm tra POST chatbot-send thật sau sửa trả HTTP 504 và thông báo phản hồi
  quá chậm đúng như dự kiến; Gemini vẫn chưa trả kết quả trong 60 giây.

### Kiểm chứng và đổi model đang timeout (14/10/2026)

- Cùng key/Apache/TLS, generateContent tối giản với gemini-3.5-flash-lite
  timeout sau 20 giây dù đã upload toàn bộ payload; dùng HTTP/1.1 cũng vậy.
  gemini-3.1-flash-lite trả HTTP 200, STOP, nội dung OK sau khoảng 8,35 giây.
- Probe dùng Chatbot thật với đầy đủ kiến thức/schema và câu hỏi
  "quy trình đăng ký lưu trú", chỉ đổi GEMINI_MODEL trong tiến trình probe,
  đã nhận được câu trả lời tiếng Việt đúng về nhập thông tin/ưu tiên, chọn phòng,
  kiểm tra, xác nhận và Chờ duyệt. Đây là API thật, không dùng transport giả.
- Đổi default trong model và cấu hình httpd.conf ngoài repo sang
  gemini-3.1-flash-lite, cập nhật hướng dẫn; giữ tùy chọn GEMINI_MODEL.
  Cấu hình Apache Syntax OK, có bản sao lưu. 46/46 test, PHP lint model đạt.
- Apache đang chạy vẫn nhận gemini-3.5-flash-lite. Lệnh httpd -k restart ghi
  Access is denied/Failed to open wampapache64 service; cần người dùng Restart
  Apache bằng WAMP (quyền quản trị nếu cần) để nạp model mới. Probe đã xóa.
  Chưa xác minh route/UI với cấu hình model mới, chưa commit/push.

## Bàn giao UC07 và UC11 (18/10/2026)

- Nhánh `feature/uc07-uc11-demo` tách từ `origin/main`, gồm tra cứu hóa đơn, xác nhận thu tiền phòng đầu tiên, tạo hợp đồng và xem thông tin lưu trú của sinh viên.
- Tích hợp route `payments`, `payment-confirm`, `accommodation`, menu theo vai trò, header sinh viên và dashboard sinh viên/kế toán. Giữ tài khoản đã tạo ở UC03; không xác nhận thu điện nước/gia hạn trong phạm vi này.
- Không thay schema, không đưa chatbot UC13 hoặc dump SQL local vào nhánh bàn giao.
- Kiểm thử trên nhánh bàn giao: payment 29/29, accommodation 6/6, đăng nhập/phân quyền/route 45/45, approval 57/57; dùng bảng TEMPORARY cho các ca ghi dữ liệu.
- Chưa kiểm tra giao diện trực quan hoặc triển khai Render. Tiếp theo: review/merge nhánh và kiểm tra demo trên môi trường triển khai.
## Đồng bộ giao diện và tối ưu code (18/10/2026)

- Bổ sung dashboard quản lý KTX, chỉnh menu tài khoản NVQL và rút gọn nội dung trang chủ/hướng dẫn/hộp thoại hóa đơn theo bản local.
- Tái sử dụng PDO trong request; giữ factory kết nối độc lập. Tối ưu truy vấn phòng được chọn, lọc phòng phía trình duyệt và chống gửi lặp hộp thoại; giữ kiểm tra quyền và nghiệp vụ hiện hành.
- Không thay schema hoặc đưa chức năng chatbot/dump SQL vào đợt này. Dashboard và menu UC07/UC11 đã được merge trước đó.
- Kiểm thử: run 46, registration 23, rooms 16, room-information 33, approval 58, payment 29, accommodation 6, guide 3 đều đạt (214 ca PHP); frontend 8/8 đạt.
- Chưa kiểm tra trực quan giao diện hoặc deployment Render. Tiếp theo: kiểm tra giao diện và luồng demo trên môi trường triển khai.

## UC13 dự phòng và thống kê token (22/10/2026)

- Thêm ChatbotGateway: model chính theo GEMINI_MODEL (mặc định 3.1 Flash Lite),
  model dự phòng GEMINI_FALLBACK_MODEL (mặc định 3.5 Flash Lite), sau đó thử lại
  model dự phòng một lần. Tối đa ba lần, tối đa 9 giây/lần và ngân sách API 28 giây;
  có backoff/jitter. 429 tôn trọng Retry-After/retryDelay và dừng nếu chờ quá ngân sách.
  Lỗi cấu hình/400/401/403/404/CA không retry. AI từ chối phạm vi không gọi tiếp.
- Khi dịch vụ không dùng được, chỉ các chủ đề khớp chính xác đăng ký/xem phòng/
  đăng nhập có câu trả lời biên soạn, ghi rõ Hướng dẫn có sẵn. Không suy đoán dữ liệu
  phòng. Hướng dẫn chung không gửi snapshot phòng để giảm input token; câu hỏi
  phòng vẫn dùng snapshot mới, giữ nghiệp vụ/phân quyền/CSDL hiện hành.
- Log attempt/result JSONL theo ngày UTC tại C:\ProgramData\KantoKTX\chatbot-metrics
  trên Windows, ngoài webroot/Git. Ghi request_id/model/lỗi/thời gian/usageMetadata;
  không ghi nội dung chat, dữ liệu phòng, tài khoản hoặc secret. Mock không vào log thật.
  Thiếu usage là unknown, tổng chỉ gồm token được API báo, không phải quota còn lại.
  JSON thành công có metrics để đối chiếu trong DevTools Network.
- CLI tools/chatbot-usage.php [YYYY-MM-DD] [log-directory] tổng hợp theo model và nguồn.
  PHP Apache cần quyền ghi log. Xem hướng dẫn chi tiết tại UC13-ai-assistant.md.
- Kiểm thử: chatbot 60/60, gateway 19/19, nguồn phòng 9/9; lint PHP các file mới/sửa đạt.
  Gọi route thật hai lần cùng câu quy trình đăng ký lưu trú: đều HTTP 200, dùng model
  dự phòng, lần đầu khoảng 8,8 giây và 5.985 token được báo; sau bỏ snapshot không cần,
  khoảng 10,4 giây và 1.189 token (input 1.104, output 85). Hai lần model chính lỗi
  không có usage; không coi là không tốn token. CLI đọc log thật thành công.
- Chưa kiểm tra trực quan browser hoặc Render, chưa commit/push. Chưa có limiter
  RPM/TPM toàn project hoặc circuit breaker liên request; hiện giới hạn session và
  chuỗi retry. Tiếp theo: người dùng kiểm thử chất lượng, đối chiếu log với AI Studio
  rồi chọn model/hạn mức phù hợp; không xoay key để tăng quota cùng project.

### Đã gửi UC13 để review (22/10/2026)

- Worktree sạch Kanto-KTX-uc13-pr, nhánh feature/uc13-chatbot-resilience từ origin/main.
- Commit bfb3061 gồm 16 file UC13, đã push; PR #5 vào main: https://github.com/vot750422-ui/Kanto-KTX/pull/5 . Chưa merge.
- Trên bản bàn giao: 88 test chatbot/nguồn phòng và 46 test auth/route đạt; lint 10 file PHP đạt.
- Giữ nguyên workspace gốc và các thay đổi ngoài UC13; không đưa SQL dump/credential vào commit.

## Giản lược luồng demo các UC (25/10/2026)

- Bỏ token CSRF của biểu mẫu đăng ký, xét duyệt, kế toán, sinh viên, trang xác nhận QR, chatbot và đăng xuất; bỏ giới hạn 5 câu/phút của chatbot và các đoạn JavaScript chặn bấm lặp.
- Giữ đăng nhập, phân quyền, mật khẩu băm, PDO prepared statement, kiểm tra dữ liệu nghiệp vụ và giao dịch CSDL. UC17 vẫn chỉ cho một QR hiệu lực trong 5 phút, xác nhận theo token, chặn thu lặp và giữ quyền sở hữu hóa đơn.
- Giữ nguyên các thay đổi riêng của workspace WAMP, gồm email thông báo UC03.

## Bổ sung các Use Case còn thiếu theo danh mục hiện hành (27/10/2026)

- Đã triển khai UC04 quản lý sinh viên: tra cứu kết hợp, xem chi tiết và chỉ cập nhật
  `HoTen`, `NgaySinh`, `GioiTinh`, `QueQuan`, `SDT`, `Lop`. MSSV, CCCD, niên khóa và
  trạng thái lưu trú vẫn chỉ đọc. Route dành riêng cho Nhân viên quản lý KTX.
- Đã triển khai UC05.1 tra cứu hợp đồng, UC07 ghi chỉ số điện nước, UC10 sinh viên gửi
  phản ánh, UC11 nhân viên xử lý phản ánh, UC12 sinh viên xem thông báo và UC14 thống kê
  lưu trú/hóa đơn/phản ánh. Các menu vai trò và route tương ứng đã được nối vào `index.php`.
- UC07 chỉ liệt kê phòng có hợp đồng `Còn hạn`, hiển thị chỉ số gần nhất, chặn chỉ số mới
  nhỏ hơn chỉ số cũ và chặn ghi trùng phòng/tháng/năm. Không thay schema.
- UC10 tự lấy sinh viên/phòng từ tài khoản và hợp đồng `Còn hạn`; UC11 cập nhật đồng thời
  nhân viên xử lý, chi phí và trạng thái trong transaction. UC12 luôn giới hạn thông báo
  theo tài khoản sinh viên hiện tại. UC14 chỉ đọc và chỉ cho vai trò Quản lý KTX.
- Danh mục `USE_CASE_OVERVIEW.md` đã đổi số so với tên các file đặc tả cũ: file UC06 cũ
  tương ứng UC07 hiện hành; UC08/UC09 cũ tương ứng UC10/UC11 hiện hành; UC11 cũ tương
  ứng UC06 hiện hành. Khi sửa tiếp phải đối chiếu theo tên nghiệp vụ, không chỉ theo số.
- Chưa triển khai UC05.2 vì `UC05.2-contract-termination.md` đang rỗng. Chưa triển khai
  UC08 lập hóa đơn điện nước tự động vì `UC10.2-utility-invoice-generation.md` bị cắt giữa
  công thức, chưa chốt cách tính tiền và tập sinh viên liên quan. Không tự suy đoán nghiệp vụ.
- Kiểm thử mới dùng bảng TEMPORARY: hợp đồng 7/7, sinh viên 6/6, chỉ số điện nước 5/5,
  phản ánh 6/6, thông báo 5/5, thống kê 4/4. Bộ đăng nhập/phân quyền/route đạt 103/103,
  gồm cả render GET bằng đúng vai trò cho sáu trang chính. Kiểm tra render đã phát hiện và
  sửa trang gửi phản ánh trả nhầm 404 khi sinh viên chưa có hợp đồng.
- Hồi quy toàn bộ 19 bộ PHP hiện có đạt 496/496 ca khi chạy tuần tự. Không chạy được
  `tests/frontend.js` vì máy hiện không có lệnh Node.js trong PATH; đây không phải kết quả
  kiểm thử đạt. Một lần chạy song song UC03 và UC17 tranh cùng named lock làm UC03 fail;
  chạy lại đúng thứ tự tuần tự thì UC03 đạt 70/70, thanh toán 29/29 và lưu trú 6/6.

### Sửa lỗi hồ sơ NVQL và giao diện UC07/UC14 (27/10/2026)

- CSDL local có tài khoản `nvql001` nhưng bảng `nhanvien` trống, làm mọi thao tác bắt buộc
  ghi `MaNV` (ghi chỉ số điện nước, xử lý phản ánh) không thể lưu. Đã thêm migration
  `scripts/migrate-staff-profiles.php`, chạy thành công một lần để liên kết tài khoản mẫu
  với `NV00000003`; lần chạy thứ hai tạo 0 dòng, bảo đảm idempotent. SQL dump cũng có
  hồ sơ mẫu tương ứng. Họ tên tạm lấy nguyên tên đăng nhập, không tự đặt tên cá nhân.
- Trang UC07 và UC14 trước đó tham chiếu `assets/css/dashboard.css` không tồn tại và thiếu
  header chung. Đã chuyển sang `style.css`, `staff-dashboard.css`, `navbar.css`, header/menu
  tài khoản giống các trang nhân viên khác. Test render chặn tái xuất hiện đường dẫn CSS sai.
- Dữ liệu local hiện có 4 phòng đủ điều kiện ghi chỉ số và 0 chỉ số đã ghi. Không tạo chỉ số
  giả vào dữ liệu thật. Kiểm thử UC07 đạt 5/5, UC14 đạt 4/4 và route/phân quyền/render đạt
  103/103.
  Chưa kiểm tra trực quan trên trình duyệt và chưa commit/push.

### Tự hiển thị danh sách và chỉ số điện nước (27/10/2026)

- Khi mở Quản lý sinh viên hoặc Quản lý hợp đồng, controller tải ngay trang đầu với toàn bộ dữ liệu;
  người dùng chỉ cần dùng biểu mẫu khi muốn lọc. Quản lý hóa đơn đã có hành vi này và mặc định trạng
  thái `Tất cả`, nên không thay đổi nghiệp vụ hoặc truy vấn của hóa đơn.
- Tại Ghi chỉ số điện nước, thay đổi phòng trong danh sách chọn sẽ tự gửi yêu cầu GET và hiển thị chỉ
  số điện/nước gần nhất của phòng. Nút `Xem chỉ số` chỉ còn là phương án dự phòng khi JavaScript bị tắt.
- Kiểm thử route xác minh sinh viên/hợp đồng render bảng ngay cả khi URL không có `search=1`, đồng thời
  xác minh trang điện nước đã nạp `utility-meters.js` và đánh dấu ô chọn phòng tự động.

## Rà soát tinh gọn code nghiệp vụ đợt 1 (28/10/2026)

- Giữ nguyên route, vai trò, trạng thái, schema và transaction; chỉ làm rõ dispatch và các khối nghiệp vụ.
- `index.php` chuẩn hóa `action` một lần trước khi chuyển request cho controller.
- Đã viết lại dạng dễ đọc cho controller/model của phản ánh sự cố, thông báo và chỉ số điện nước; đồng thời tăng kiểm tra input dạng mảng ở form chỉ số.
- Đã bổ sung comment nghiệp vụ ngắn trong toàn bộ PHP thuộc `controllers`, `models`, `services`, `middleware`.
- Kiểm chứng: PHP lint 127/127; `tests/run.php` 106/106; test sự cố 6/6; thông báo 5/5; chỉ số điện nước 5/5; các test PHP nghiệp vụ khác đạt.
- Chưa triển khai CSRF đồng loạt cho các form POST cũ; cần chốt phạm vi và cập nhật đồng bộ controller/view/test trước khi làm.
