<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__, 3) . '/config/db.php';
if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'qlktx_v2') {
    throw new RuntimeException('Chỉ thu thập metadata schema local qlktx_v2.');
}
$schema = [];
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    if (!preg_match('/^[a-z0-9_]+$/D', $table)) { continue; }
    $schema[$table] = [
        'columns' => $pdo->query("SHOW FULL COLUMNS FROM `$table`")->fetchAll(),
        'create' => $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1],
    ];
}
file_put_contents(__DIR__ . '/working/schema-local.json', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$reference = [];
foreach ($schema as $table => $definition) {
    $reference[$table] = [
        'columns' => array_map(fn($column) => array_intersect_key($column, array_flip(['Field', 'Type', 'Null', 'Key'])), $definition['columns']),
        'create' => preg_replace('/ AUTO_INCREMENT=\d+/', '', $definition['create']),
    ];
}
file_put_contents(__DIR__ . '/schema-reference.json', json_encode($reference, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
foreach ($schema as $table => $definition) {
    echo $table . ': ' . implode(', ', array_map(fn($c) => $c['Field'] . ' ' . $c['Type'] . ($c['Key'] === 'PRI' ? ' [PK]' : '') . ($c['Null'] === 'YES' ? ' [NULL]' : ''), $definition['columns'])) . PHP_EOL;
}
