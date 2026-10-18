<?php
require_once __DIR__ . '/../middleware/Role.php';
require_once __DIR__ . '/../models/Accommodation.php';

class AccommodationController
{
    public function show(): void
    {
        requireRole('Sinh viên');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Allow: GET');
            http_response_code(405);
            echo 'Phương thức không được hỗ trợ.';
            return;
        }
        require __DIR__ . '/../config/db.php';
        try {
            $data = (new Accommodation($pdo))->forAccount((int) $_SESSION['user']['MaTK']);
        } catch (PDOException $error) {
            http_response_code(500);
            echo 'Không thể tải thông tin lưu trú. Vui lòng thử lại sau.';
            return;
        }
        require __DIR__ . '/../views/student/accommodation.php';
    }
}
