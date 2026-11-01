<?php

require_once __DIR__ . '/../models/Renewal.php';

class RenewalController
{
    public function __construct(private PDO $db) {}

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method !== 'POST') {
            http_response_code(405);
            if (!headers_sent()) {
                header('Allow: POST');
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['error' => 'Phương thức không được hỗ trợ. Yêu cầu POST.']);
            return;
        }

        // Xác thực bí mật của Timer (RENEWAL_TOKEN)
        $expectedToken = getenv('RENEWAL_TOKEN') ?: 'kanto_renewal_secret_2026';
        $providedToken = $_SERVER['HTTP_X_RENEWAL_TOKEN'] ?? null;
        if (!$providedToken && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $providedToken = $matches[1];
            }
        }
        if (!$providedToken) {
            $providedToken = $_POST['token'] ?? null;
        }

        if (!$providedToken || !hash_equals($expectedToken, $providedToken)) {
            http_response_code(403);
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['error' => 'Từ chối truy cập. Token gia hạn không hợp lệ.']);
            return;
        }

        $semesterId = is_string($_POST['semester_id'] ?? null) ? trim($_POST['semester_id']) : null;

        try {
            $renewal = new Renewal($this->db);
            $result = $renewal->run($semesterId);
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'status' => 'success',
                'data'   => $result,
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
