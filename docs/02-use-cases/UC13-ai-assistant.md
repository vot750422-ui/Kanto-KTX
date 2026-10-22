# UC13 - Chatbot AI

Nguồn: `docs/sources/Mô tả UC và sơ đồ trình tự.docx`, mục 12 (UC-13),
sequence UC13 và wireframe UC13. Đặc tả gốc được ưu tiên khi bảng tổng quan cũ chỉ ghi Sinh viên.

- Tác nhân: Sinh viên, khách vãng lai; không cần đăng nhập.
- Mục đích: hỏi đáp thông tin chung về ký túc xá, hướng dẫn sử dụng hệ thống.
- Tiền điều kiện: đã truy cập website; dịch vụ AI được cấu hình để trả lời câu hỏi hợp lệ.
- Hậu điều kiện: hiển thị câu trả lời/hướng dẫn và số liệu phòng công khai; không truy xuất hồ sơ cá nhân hoặc thay đổi dữ liệu nghiệp vụ.

## Mở rộng theo yêu cầu ngày 15/10/2026

- Cho phép hỏi sức chứa, khu Nam/Nữ, tòa/tầng, số chỗ đang ở, đang giữ và còn trống theo dữ liệu UC15. Chỉ cung cấp số đếm công khai, không danh tính người trong phòng, hóa đơn, hợp đồng, tiện nghi/nội thất hoặc dữ liệu cá nhân.
- Câu ngắn/không dấu/lỗi gõ nhẹ như “Cách đăng ký”, “dang ky sao”, “đăng ksy lưu trú” được chuyển tới Gemini để hiểu theo bối cảnh KTX. Bỏ whitelist từ khóa chính xác; các câu rõ ràng ngoài KTX do Gemini từ chối, câu mơ hồ được hỏi lại.
- Đây là thay đổi phạm vi so với thiết kế ban đầu chỉ dùng kiến thức tĩnh/không PDO. Máy chủ dùng adapter cố định để đọc dữ liệu công khai; AI vẫn không có công cụ SQL hoặc quyền ghi.

## Luồng chính

1. Chọn biểu tượng Chatbot AI.
2. Hệ thống mở cửa sổ trò chuyện và ô nhập.
3. Nhập câu hỏi, nhấn Gửi.
4. Máy chủ kiểm tra nội dung/phạm vi và gửi câu hỏi hợp lệ đến dịch vụ AI.
5. Hiển thị câu trả lời. Người dùng có thể tiếp tục gửi câu hỏi khác.
6. Đóng cửa sổ bằng nút đóng hoặc Escape; mở lại giữ nội dung trên trang hiện tại.

## Ngoại lệ

- E1: bỏ trống: “Vui lòng nhập nội dung câu hỏi.” Không gọi dịch vụ.
- E2: ngoài phạm vi: chỉ hỗ trợ thông tin KTX và hướng dẫn sử dụng; không tra cứu dữ liệu cá nhân, hợp đồng, hóa đơn hoặc phân tích/xử lý phản ánh.
- E3: không kết nối/không nhận phản hồi hợp lệ: “Trợ lý AI hiện không thể phản hồi. Vui lòng thử lại sau.”

## Chi tiết triển khai

- POST `index.php?action=chatbot-send`; CSRF riêng; tối đa 500 ký tự, 5 câu/phút/session.
- Chat ở trang chủ và các trang sinh viên qua nút chat; navbar không còn mục Chatbot AI theo yêu cầu người dùng.
- Không tạo bảng, không lưu câu hỏi/câu trả lời vào CSDL hoặc log. `ChatbotRoomSource` dùng PDO trong transaction chỉ đọc, tái sử dụng `Room::rooms` và công thức UC15. Chỉ gửi cột công khai và tổng theo tòa, không gửi mã đơn, MSSV, thông tin người đang ở hoặc credential.
- Cửa sổ giữ tối đa 40 tin nhắn trong DOM, mất khi tải lại trang. Mỗi câu gửi độc lập, chưa có ngữ cảnh hội thoại nhiều lượt.
- Bộ lọc máy chủ chặn một số mẫu nhạy cảm/ngoài phạm vi trước khi gọi AI; dịch vụ tiếp tục xác định phạm vi theo system instruction. Bộ lọc từ khóa không đảm bảo nhận diện mọi cách diễn đạt dữ liệu cá nhân; người dùng được nhắc không nhập thông tin cá nhân. AI không có công cụ hoặc quyền truy cập CSDL.
- Nguồn trả lời gồm kiến thức công khai trong `models/Chatbot.php` và snapshot phòng mới khi câu hỏi có dấu hiệu hỏi phòng/tòa/tầng/chỗ trống. Không gửi toàn bộ docs/session/dữ liệu người dùng. Có thời điểm đọc, tổng theo tòa được tính bằng PHP, chi tiết tối đa 300 phòng (hiện 200); nếu bị cắt phải nói rõ danh sách chưa đầy đủ. Số chỗ có thể đổi sau khi trả lời, không thay kiểm tra phòng ở UC02.
- Nếu nguồn phòng lỗi, chỉ dẫn ghi rõ không có số liệu hiện tại; chatbot vẫn hỗ trợ hướng dẫn chung và không tự đoán số chỗ. Chưa biết nội quy/giá/lịch/tiện nghi thì đề nghị xác nhận với quản lý.
- `config/connection.php` chứa hàm tạo PDO dùng chung từ các biến DB hiện có; `config/db.php` giữ hành vi cũ cho các module khác. Adapter chatbot bắt lỗi kết nối để không dừng toàn bộ route JSON.
- Nhà cung cấp Gemini là lựa chọn kỹ thuật cho bản triển khai theo yêu cầu, không phải yêu cầu được chỉ định trong đặc tả gốc. Máy chủ gọi generateContent API và yêu cầu đầu ra JSON theo schema để kiểm tra phạm vi/câu trả lời.

## Cấu hình máy chủ

Đặt hai biến môi trường cho tiến trình PHP/Apache: `GEMINI_API_KEY` và `GEMINI_MODEL`.
Model mặc định: `gemini-3.1-flash-lite`; có thể đổi bằng `GEMINI_MODEL`. Tài khoản cần có quyền sử dụng model và quota API.
Ưu tiên mã model API, không dùng tên hiển thị. Code chấp nhận riêng tên
`Gemini 3.5 Flash Lite`/`Gemini 3.5 Flash-Lite` và đổi sang `gemini-3.5-flash-lite`; đây chỉ là chuẩn hóa tên, không tự đổi phiên bản.
Trên WAMP, mở Apache → `httpd.conf`, thêm:

```apache
SetEnv GEMINI_API_KEY "DAN_API_KEY_CUA_BAN_VAO_DAY"
SetEnv GEMINI_MODEL "gemini-3.1-flash-lite"
```

Thay chuỗi mẫu bằng API key từ Google AI Studio, lưu file rồi chọn WAMP → Restart All Services
để Apache/PHP nhận cấu hình mới. Render dùng mục Environment.
Ứng dụng không tự đọc `.env`; không đặt key trong repository hoặc JavaScript.
PHP cần cURL, mbstring và CA certificate hợp lệ. Không tắt xác minh TLS để khắc phục lỗi kết nối.
Máy chủ dùng cùng một key cho model chính và dự phòng. Tùy chọn
`GEMINI_FALLBACK_MODEL` mặc định `gemini-3.5-flash-lite`; có thể đặt qua SetEnv.
Model dự phòng cần có quyền sử dụng/quota trong cùng project; thêm key cùng project không tăng quota.

Chuỗi xử lý: model chính → model dự phòng → thử lại model dự phòng một lần →
hướng dẫn có sẵn nếu khớp chính xác một chủ đề đã duyệt. Nếu hai model trùng nhau,
không có bước đổi model; vẫn tối đa ba lần gọi và có chờ giữa các lần.
Tối đa 9 giây/lần, kết nối tối đa 3 giây, ngân sách chuỗi API 28 giây (không gồm
thời gian đọc CSDL). Chờ khoảng 1–1,25 giây trước lần thử lại cuối.
429 tôn trọng Retry-After/retryDelay; nếu thời gian chờ vượt ngân sách thì dừng.
400/401/403/404, lỗi CA hoặc cấu hình không thử lại. Từ chối phạm vi/an toàn từ AI
không chuyển model hoặc thay bằng FAQ. Phản hồi không hợp lệ không lặp lại tự động.

Nội dung dự phòng ghi rõ “Hướng dẫn có sẵn”, chỉ gồm đăng ký, xem phòng và đăng nhập;
không tự suy đoán số chỗ trống hoặc giá. Câu không khớp vẫn trả lỗi chung/timeout.
Trình duyệt giữ timeout 70 giây để có khoảng đệm khi nguồn CSDL chậm.

## Ghi nhận token và kiểm thử chất lượng

`ChatbotGateway` ghi JSONL ngoài webroot tại Windows:
`C:\ProgramData\KantoKTX\chatbot-metrics\YYYY-MM-DD.jsonl` (ngày UTC).
Linux dùng thư mục `KantoKTX/chatbot-metrics` trong thư mục tạm hệ thống.
Tiến trình PHP cần quyền ghi; nếu lỗi ghi sẽ có thông báo trong PHP error log.
Không ghi key, câu hỏi, câu trả lời, snapshot phòng, session hoặc danh tính người dùng.

- Dòng `attempt`: request_id, model, lần gọi, mã HTTP/cURL, thời gian và usageMetadata
  (prompt/candidates/thoughts/cached/total token). Dòng `result`: nguồn ai/static/refusal/error,
  tổng số lần gọi, tổng token đã được báo và số lần thiếu usage.
- Timeout/lỗi không có usage được ghi null, không có nghĩa không tiêu tốn token.
  Tổng là phần API đã báo, không phải tổng chi phí được xác nhận hoặc quota còn lại.
  Cached token có thể đã nằm trong prompt token; không cộng tất cả cột lại với nhau.
- Phản hồi JSON thành công có `metrics`, xem trong DevTools → Network → chatbot-send
  để đối chiếu request_id với log. Giao diện vẫn chỉ hiển thị câu trả lời.
- Mock tests không ghi vào log quota thật. Không có public route đọc log.

Xem thống kê:

```powershell
& C:\wamp64\bin\php\php8.3.28\php.exe tools/chatbot-usage.php
& C:\wamp64\bin\php\php8.3.28\php.exe tools/chatbot-usage.php 2026-10-22
```

Có thể truyền thư mục log làm đối số thứ hai sau ngày. Đối chiếu usage/quota thực tế
ở https://aistudio.google.com/rate-limit, chọn đúng project/model.
Giới hạn session 5 câu/phút và tối đa ba lần gọi giúp hạn chế khuếch đại lưu lượng;
chưa có bộ giới hạn RPM/TPM toàn project hoặc bộ đếm quota còn lại. Khi nhiều người
kiểm thử đồng thời, cần căn theo hạn mức thực tế và theo dõi AI Studio.
Dữ liệu phòng chỉ được đưa vào prompt khi câu hỏi cần; câu hướng dẫn chung bỏ snapshot
để giảm token. Giữ maxOutputTokens=1024 và schema hiện có để không cắt câu trả lời sớm.
API: Gemini generateContent (POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent); key gửi qua header x-goog-api-key, không đặt trong URL.
Lấy key tại https://aistudio.google.com/apikey. Model mặc định có Free Tier, hạn mức phụ thuộc project/model.
Free Tier có thể dùng nội dung để cải thiện sản phẩm Google; không nhập dữ liệu cá nhân vào chat.
Không có tùy chọn store=false tương đương OpenAI trong request này.
Tham khảo https://ai.google.dev/api/generate-content và https://ai.google.dev/gemini-api/docs/pricing.
Các biến OPENAI_API_KEY/OPENAI_MODEL cũ không còn được sử dụng.

## Kiểm thử

`php -d xdebug.mode=off tests/chatbot.php` dùng transport giả để kiểm tra logic và lỗi,
không tiêu tốn quota hoặc gửi dữ liệu ra dịch vụ. Kiểm tra API thật cần cấu hình key/model,
mở trang chủ và gửi câu hỏi chung như “Tôi muốn đăng ký lưu trú”.

`php -d xdebug.mode=off tests/chatbot-rooms.php` dùng bảng TEMPORARY để kiểm tra số liệu UC15, Nam/Nữ, giữ chỗ, hợp đồng liên kết, tổng tòa, dữ liệu cập nhật và loại bỏ thông tin cá nhân. Test không gửi dữ liệu mẫu ra Gemini.

`php -d xdebug.mode=off tests/chatbot-gateway.php` kiểm tra chuyển model, số lần gọi,
backoff/cooldown quota, không thử lại lỗi cấu hình, token chưa biết, nguồn dự phòng
và bỏ snapshot không cần thiết; không gọi API thật.

Kiểm tra Apache local ngày 15/10/2026: ba câu “Cách đăng ký”, “đăng ksy lưu trú”, “Phòng A101 có sức chứa bao nhiêu và còn mấy chỗ?” đều nhận HTTP 200 và câu trả lời thật từ Gemini. A101 trả sức chứa 8/còn 8, đối chiếu snapshot local khớp. Chưa deploy/kiểm tra trên Render.
