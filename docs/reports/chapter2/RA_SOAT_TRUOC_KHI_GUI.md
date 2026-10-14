# Rà soát bản thảo Chương 2 trước khi gửi

Ngày đối chiếu: 11/10/2026. Đây là ghi chú làm việc của nhóm, không cần đính kèm cho thầy cùng bản thảo.

Danh sách sửa cụ thể từng sơ đồ, ERD, đặc tả và wireframe: [NGHIEP_VU_CAN_SUA_VA_DONG_BO.md](../../NGHIEP_VU_CAN_SUA_VA_DONG_BO.md).

## Bản đã chốt và bổ sung wireframe

- Bản dùng để gửi sau lần bổ sung này: [Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx](Chuong_2_Kanto_KTX_da_sua_bo_sung_wireframe.docx).
- Nguồn là bản nhóm đã chốt [Chuong_2_Kanto_KTX_da_sua.docx](Chuong_2_Kanto_KTX_da_sua.docx), được giữ nguyên. Chỉ bổ sung **2.7.3. Phác thảo giao diện** ngay sau 2.7.2 và trước 2.8; không dựng lại nội dung từ Markdown cũ.
- Nhúng đủ **25 ảnh gốc** trong `docs/05-ui/wireframes/`, chia thành 7 nhóm chức năng, thêm chú thích **Hình 2.8–2.32** và mô tả ngắn. 7 hình có sẵn và toàn bộ nội dung ngoài mục mới được giữ lại.
- Microsoft Word đã mở và phân trang được: **60 trang, 32 hình**. Đã đối chiếu 285 thành phần nội dung gốc không thay đổi và kiểm tra ảnh nhúng trùng byte với wireframe nguồn; đã xem các trang đại diện của phần bổ sung.
- Khi ghép báo cáo tổng, cập nhật mục lục và danh mục hình theo bản này. Các thông tin 29 trang, 11 hình và nguồn Markdown bên dưới thuộc bản thảo trước khi nhóm chốt.

## Bản thảo trước khi nhóm chốt (tham khảo)

- Bản Word đồng bộ, đã sửa actor UC06: [BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx](BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx).
- [Bản Word ban đầu](BAN_THAO_CHUONG_2_KANTO_KTX.docx) đang được người dùng chỉnh sửa nên được giữ lại. Bản đồng bộ dựng từ Markdown; các chỉnh sửa thủ công trong bản ban đầu cần được ghép lại nếu muốn dùng chúng.
- Bản nguồn để sửa và theo dõi thay đổi: [BAN_THAO_CHUONG_2_KANTO_KTX.md](BAN_THAO_CHUONG_2_KANTO_KTX.md).
- Tên chương theo `DA1 KTPM2026-Hướng dẫn chung.pdf`: **PHƯƠNG TIỆN NGHIÊN CỨU - PHƯƠNG PHÁP NGHIÊN CỨU**. Nội dung gồm khảo sát yêu cầu, phân tích và thiết kế, công cụ phát triển.
- Word dùng A4, Times New Roman 13, nội dung giãn dòng 1,5; lề trái 3 cm, phải 2 cm, trên/dưới 2,5 cm; tiêu đề có cấp Heading, bảng có hàng tiêu đề lặp và hình được nhúng vào file. Đây là định dạng mặc định của bản thảo; chưa xác nhận toàn bộ quy định trong quyết định/phụ lục của trường vì repository chỉ có hướng dẫn tóm tắt dẫn tới các tài liệu đó.
- Các hình là sơ đồ/phác thảo mới phục vụ bản thảo. Không dùng ảnh lỗi Render làm ảnh sản phẩm và không dùng dữ liệu hồ sơ cá nhân.
- Bản Word đã mở được bằng Microsoft Word: 29 trang, 13 bảng, 11 hình; đã xem trang đầu, một trang ERD và trang cuối. Nội dung Word được đối chiếu tự động với từng đoạn và ô bảng trong Markdown. Chưa có bản PDF vì bước xuất qua Word không hoàn tất trên máy hiện tại.
- Khi ghép với Chương 1, chỉnh lại số trang liên tục và mục lục chung; giữ số hình 2.1–2.11.

## Nội dung đã đối chiếu

| Nội dung | Nguồn chính | Cách thể hiện trong bản thảo |
| --- | --- | --- |
| Phạm vi đề tài và công nghệ | `docs/sources/bản thảo chương 1.docx`, `PROJECT_STATE.md` | Nối tiếp Chương 1; không tự nhận đã khảo sát thực tế. |
| Danh mục UC | Các đặc tả chi tiết trong `docs/02-use-cases/`, sơ đồ Use Case và Wireframe index | UC06 chỉ số; UC07 hóa đơn; UC08 gửi phản ánh; UC09 xử lý; UC10 lập hóa đơn; UC11 thông tin lưu trú. |
| Đăng ký và giữ chỗ | `models/Registration.php`, đặc tả UC02 | Ba bước; gửi đơn giữ chỗ; chưa tạo tài khoản/hợp đồng. |
| Xét duyệt | `models/Approval.php`, đặc tả UC03, `docs/UC03_IMPLEMENTATION_NOTES.md` | Duyệt tạo tài khoản, sinh viên chờ thanh toán, hóa đơn ban đầu; chưa tạo hợp đồng. |
| Đơn giá tháng | `models/Approval.php` (billingContext, roomTotal), `PROJECT_STATE.md` | Lấy đơn giá có hiệu lực; tính tháng lẻ theo ngày; không viết thành giá/học kỳ. |
| Thông tin phòng | `models/Room.php`, `controllers/RoomController.php`, đặc tả UC15 | Bộ lọc lấy từ CSDL; tính chỗ từ hợp đồng và đơn; UC15 gồm cả phòng đầy. |
| Schema | Metadata local của `qlktx_v2` thu ngày 11/10/2026 | 17 bảng, có MaHoaDon/MaHopDong trong dondangky; sơ đồ chỉ dùng khóa ngoại thực có. |
| Các chức năng còn lại | Đặc tả chi tiết UC04–UC12, UC14, UC16 | Mô tả yêu cầu/thiết kế; không tuyên bố tất cả đã triển khai. |

## Những chỗ nhóm cần thống nhất tiếp

1. **Mã UC trong tài liệu tổng quan:** `docs/02-use-cases/USE_CASE_OVERVIEW.md` đang khác các đặc tả chi tiết từ UC06 tới UC11. Bản thảo theo đặc tả chi tiết và sơ đồ; chưa sửa tài liệu tổng quan trong task này.
2. **Thanh lý hợp đồng:** `docs/02-use-cases/UC05.2-contract-termination.md` đang trống. Chưa có đủ căn cứ chốt ngày hiệu lực, xử lý hóa đơn còn nợ, hoàn phí hoặc chuyển trạng thái sinh viên. Bản thảo chỉ ghi mục tiêu và phần cần hoàn thiện.
3. **Thanh toán đầu tiên:** cần hoàn thiện cách lập/gắn hợp đồng sau thu hóa đơn, ngày bắt đầu/kết thúc, đổi trạng thái sinh viên và xử lý giao dịch lặp. Không coi QR mô phỏng là tích hợp ngân hàng thực.
4. **Giữ chỗ sau duyệt:** chưa chốt hạn thanh toán và cách hủy hoặc giải phóng chỗ khi quá hạn. Không tự đưa một thời hạn vào báo cáo.
5. **Điện nước:** cần chốt chỉ số mốc của phòng mới, dữ liệu thiếu và phân bổ phí khi sinh viên vào/ra giữa tháng. Bản thảo không mặc định chia đều.
6. **Timer:** Use Case nêu mốc chạy nhưng cần hoàn thiện cơ chế lập lịch, chống chạy trùng, retry và log. Local hiện chưa có trigger/event; không tuyên bố tự động hóa đã chạy.
7. **Trợ lý AI:** chưa có đặc tả UC13 riêng đầy đủ; sơ đồ tổng quan cũ và Chương 1 chưa thống nhất hoàn toàn về Guest. Bản thảo dùng phạm vi thông tin cho khách/sinh viên theo Chương 1; nhóm nên bổ sung đặc tả và đồng bộ sơ đồ. Không mở quyền dữ liệu cá nhân hay thao tác nghiệp vụ.
8. **Tài liệu dữ liệu:** `docs/03-business-rules/BUSINESS_RULES.md` đang trống; `docs/04-database/DATABASE_DESIGN.md` còn nội dung giá theo kỳ. Dump `sql/qlktx_v2.sql` không đồng bộ đầy đủ với schema local UC03 nên không dùng làm nguồn duy nhất.
9. **Render/Aiven:** chưa có xác nhận migration UC03 trên Aiven. Bản thảo chỉ mô tả yêu cầu đồng bộ môi trường, không ghi rằng lỗi production đã được giải quyết.
10. **Chương 3:** cần kiểm thử trực quan desktop/mobile, đồng thời nhiều tiến trình, và xác nhận môi trường triển khai. Không chuyển kết quả test giả lập thành bằng chứng đã kiểm tra các nội dung này.

## Đọc nhanh trước khi gửi

- Mở Word, kiểm tra dấu tiếng Việt, bảng, chú thích hình và phân trang.
- Đọc lại tên đề tài, phạm vi và các quy tắc nhóm đã chốt ở 2.4.
- Giữ các chỗ nêu “thiết kế” hoặc “cần hoàn thiện” nếu chức năng chưa làm xong; không đổi thành tuyên bố đã hoàn thành.
- UC06 ghi chỉ số điện nước thuộc Nhân viên quản lý KTX theo đặc tả/sơ đồ hiện hành; bản thảo và hình Use Case đã được sửa lỗi ghi nhầm kế toán trong lần rà soát tiếp theo.
- Chỉ đính kèm bản thảo báo cáo; không cần gửi folder công cụ, metadata hoặc checklist nội bộ này.

## Gợi ý nội dung thư để bạn tự gửi

**Tiêu đề:** Bản thảo Chương 2 - Đồ án 1 - Hệ thống quản lý ký túc xá trường Đại học Kanto

Kính gửi thầy,

Em gửi thầy bản thảo Chương 2 của đồ án “Hệ thống quản lý ký túc xá trường Đại học Kanto”. Nội dung tập trung vào xác định yêu cầu, phân tích Use Case, quy trình nghiệp vụ, thiết kế kiến trúc, cơ sở dữ liệu và giao diện. Một số nghiệp vụ chi tiết đang được nhóm tiếp tục rà soát và hoàn thiện.

Em mong nhận được góp ý của thầy để nhóm điều chỉnh trước khi triển khai và hoàn thiện báo cáo.

Em cảm ơn thầy.
