# PROJECT_STATE

## Cập nhật giao diện UC16 (14/10/2026)

- Bỏ hai liên kết hành động ở cuối trang hướng dẫn và CSS tương ứng; điều hướng vẫn nằm trên header chung.
- Branch guide đã có dispatch `index.php?action=guide` tới `GuideController::show()`. Không đưa các thay đổi chatbot, đăng nhập, cấu hình DB hoặc dump SQL từ workspace hiện tại vào commit guide.
- Kiểm tra route hướng dẫn bằng `tests/guide.php` đạt 3/3; PHP lint index/controller/view đạt.

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

- UC thanh toán (mô phỏng QR), chưa triển khai.

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
  approval-approve và approval-reject chỉ dành NVQL. POST ghi dữ liệu có CSRF.
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

## Bàn giao UC07 và UC11 (18/10/2026)

- Nhánh `feature/uc07-uc11-demo` tách từ `origin/main`, gồm tra cứu hóa đơn, xác nhận thu tiền phòng đầu tiên, tạo hợp đồng và xem thông tin lưu trú của sinh viên.
- Tích hợp route `payments`, `payment-confirm`, `accommodation`, menu theo vai trò, header sinh viên và dashboard sinh viên/kế toán. Giữ tài khoản đã tạo ở UC03; không xác nhận thu điện nước/gia hạn trong phạm vi này.
- Không thay schema, không đưa chatbot UC13 hoặc dump SQL local vào nhánh bàn giao.
- Kiểm thử trên nhánh bàn giao: payment 29/29, accommodation 6/6, đăng nhập/phân quyền/route 45/45, approval 57/57; dùng bảng TEMPORARY cho các ca ghi dữ liệu.
- Chưa kiểm tra giao diện trực quan hoặc triển khai Render. Tiếp theo: review/merge nhánh và kiểm tra demo trên môi trường triển khai.