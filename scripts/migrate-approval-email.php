<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/connection.php';
$db = createDatabaseConnection();
foreach (['dondangky', 'sinhvien'] as $table) {
    $columns = $db->query('SHOW COLUMNS FROM ' . $table)->fetchAll();
    if (!in_array('Email', array_column($columns, 'Field'), true)) {
        throw new RuntimeException('Thiếu cột Email trong ' . $table . '. Kiểm tra schema trước khi triển khai.');
    }
}
$db->exec(file_get_contents(__DIR__ . '/../sql/uc03-email.sql'));
echo "Đã chuẩn bị bảng emailthongbao. Không sửa hồ sơ hoặc thời gian cũ.\n";
