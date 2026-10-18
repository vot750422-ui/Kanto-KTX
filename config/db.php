<?php

require_once __DIR__ . '/connection.php';
try {
    $pdo = applicationDatabaseConnection();
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed.');
}
