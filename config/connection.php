<?php
require_once __DIR__ . '/timezone.php';

function createDatabaseConnection(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_NAME') ?: 'qlktx_v2';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD');
    return new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password === false ? '' : $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'",
    ]);
}

// Reuse a connection within one request, while re-reading permissions every time.
// createDatabaseConnection() remains available for independent connections.
function applicationDatabaseConnection(): PDO
{
    static $connection = null;
    return $connection ??= createDatabaseConnection();
}
