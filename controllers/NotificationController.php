<?php

require_once __DIR__ . '/../models/Notification.php';

class NotificationController
{
    public function __construct(private PDO $db) {}

    public function handle(string $action): void
    {
        // Chỉ cho sinh viên đọc thông báo; mọi thao tác ghi đều nằm ngoài UC12 hiện tại.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }

        $model = new Notification($this->db);
        $accountId = (int) $_SESSION['user']['MaTK'];

        if ($action === 'notification-detail') {
            // Chỉ trả chi tiết nếu mã thông báo thuộc đúng tài khoản đang đăng nhập.
            $id = $_GET['id'] ?? '';
            if (!is_string($id) || preg_match('/^[A-Za-z0-9_-]{1,12}$/D', $id) !== 1) {
                http_response_code(400);
                echo 'Mã thông báo không hợp lệ.';
                return;
            }

            $notification = $model->findForAccount($id, $accountId);
            if (!$notification) {
                http_response_code(404);
                echo 'Không tìm thấy thông báo.';
                return;
            }
        } else {
            // Phân trang danh sách để không tải toàn bộ thông báo trong một request.
            $pageInput = $_GET['page'] ?? '1';
            $page = is_scalar($pageInput)
                ? filter_var($pageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;
            if ($page === false) {
                http_response_code(400);
                echo 'Trang không hợp lệ.';
                return;
            }

            $listing = $model->listingForAccount($accountId, (int) $page);
        }

        $view = $action === 'notification-detail' ? 'detail' : 'list';
        require __DIR__ . '/../views/notifications/' . $view . '.php';
    }
}
