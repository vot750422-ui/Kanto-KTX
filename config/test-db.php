<?php

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    $result = $pdo->query('SELECT DATABASE() AS database_name, NOW() AS server_time')->fetch();

    echo "Kết nối database thành công!\n";
    echo 'Database: ' . $result['database_name'] . "\n";
    echo 'Thời gian máy chủ: ' . $result['server_time'] . "\n";
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Kiểm tra database thất bại: ' . $e->getMessage();
}
