<?php
require_once __DIR__ . '/../config/app.php';

// UC16 chỉ phục vụ nội dung hướng dẫn công khai bằng request GET.
class GuideController
{
    public function show(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Allow: GET');
            http_response_code(405);
            echo 'Phương thức không được hỗ trợ.';
            return;
        }
        $guidePage = true;
        require __DIR__ . '/../views/guide/information.php';
    }
}
