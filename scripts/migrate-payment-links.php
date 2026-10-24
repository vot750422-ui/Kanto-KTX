<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/connection.php';
$db = createDatabaseConnection();
$db->exec(file_get_contents(__DIR__ . '/../sql/uc17-payment-links.sql'));
echo "Đã chuẩn bị bảng phienthanhtoan. Không thay đổi hóa đơn hoặc hợp đồng hiện có.\n";
