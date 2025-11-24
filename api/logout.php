<?php
require __DIR__ . '/api_headers.php';
require __DIR__ . '/db.php';

$token = $_COOKIE['token'] ?? null;

if ($token) {
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE token = ?");
    $stmt->execute([$token]);

    setcookie('token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

echo json_encode(['ok' => true]);
