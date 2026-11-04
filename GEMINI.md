# Quy Tắc Phân Biệt Nguồn Tin Nhắn (Telegram vs Trực Tiếp)

1. **Tin nhắn từ Telegram**:
   - Luôn bắt đầu bằng chữ `ai...` (ví dụ: `ai: ...`, `ai ...`, `ai [nội dung]`).
   - Đây là các yêu cầu được chuyển tiếp từ Bot Telegram từ xa. Phản hồi sẽ được luồng `watch_and_send_ai_response` tự động theo dõi và gửi về Telegram.

2. **Tin nhắn trực tiếp trên máy tính (Antigravity IDE)**:
   - KHÔNG có tiền tố `ai...` ở đầu tin nhắn.
   - Khi người dùng gửi yêu cầu không có chữ `ai...` ở đầu: Đây là trao đổi trực tiếp trên máy tính.
   - **BẮT BUỘC**: AI chỉ phản hồi trực tiếp trong giao diện chat Antigravity; TUYỆT ĐỐI KHÔNG chạy script gửi thông báo, tệp tin hay phản hồi sang Bot Telegram (trừ khi người dùng gõ lệnh rõ ràng: "gửi qua tele cho tôi").
