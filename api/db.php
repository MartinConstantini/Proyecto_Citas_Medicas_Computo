<?php
$config = require __DIR__ . '/config.php';

$host = $config['db']['host'];
$port = $config['db']['port'];
$name = $config['db']['name'];

$dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
if (!empty($port)) {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'db error',
        'error' => $e->getMessage()
    ]);
    exit;
}
