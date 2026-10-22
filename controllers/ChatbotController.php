<?php
require_once __DIR__ . '/../models/Chatbot.php';
require_once __DIR__ . '/../models/ChatbotRoomSource.php';

class ChatbotController
{
    public function __construct(private ?Chatbot $chatbot = null) {}

    public function send(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            $this->respond(405, 'Phương thức không được hỗ trợ.');
            return;
        }
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || !isset($_SESSION['chatbot_token']) || !hash_equals($_SESSION['chatbot_token'], $token)) {
            $this->respond(403, 'Phiên trò chuyện không hợp lệ. Vui lòng tải lại trang.');
            return;
        }
        $question = $_POST['question'] ?? null;
        if (!is_string($question) || !mb_check_encoding($question, 'UTF-8') || trim($question) === '') {
            $this->respond(422, 'Vui lòng nhập nội dung câu hỏi.');
            return;
        }
        $question = trim($question);
        if (mb_strlen($question) > 500 || strlen($question) > 2000) {
            $this->respond(422, 'Câu hỏi tối đa 500 ký tự.');
            return;
        }
        // Giới hạn theo session, gồm cả câu hỏi bị từ chối; không lưu nội dung chat.
        $now = time();
        $requests = array_values(array_filter($_SESSION['chatbot_requests'] ?? [], fn($at) => $at > $now - 60));
        if (count($requests) >= 5) {
            header('Retry-After: 60');
            $this->respond(429, 'Bạn gửi quá nhanh. Vui lòng thử lại sau một phút.');
            return;
        }
        $requests[] = $now;
        $_SESSION['chatbot_requests'] = $requests;
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        try {
            $bot = $this->chatbot ?? new Chatbot(roomSource: fn() => (new ChatbotRoomSource())->snapshot());
            $answer = $bot->answer($question);
            $this->respond(200, $answer, $bot->metrics());
        } catch (Throwable $error) {
            if ($error instanceof RuntimeException && $error->getCode() === 504 && $error->getMessage() === Chatbot::TIMEOUT) {
                $this->respond(504, Chatbot::TIMEOUT);
            } elseif ($error instanceof RuntimeException && $error->getCode() === 503 && $error->getMessage() === Chatbot::BUSY) {
                $this->respond(503, Chatbot::BUSY);
            } else {
                $this->respond(503, Chatbot::UNAVAILABLE);
            }
        }
    }

    private function respond(int $status, string $message, ?array $metrics = null): void
    {
        http_response_code($status);
        echo json_encode(['message' => $message] + ($metrics === null ? [] : ['metrics' => $metrics]), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
