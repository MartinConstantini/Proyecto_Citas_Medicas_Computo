<?php
require __DIR__ . '/db.php';
require __DIR__ . '/api_headers.php';

/**
 * retorna el usuario autenticado o responde 401
 * requiere cookie token con valor existente en tabla sessions
 */
function current_user_or_401() {
    $token = $_COOKIE['token'] ?? null;
    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'no token']);
        exit;
    }

    global $pdo;
    $stmt = $pdo->prepare(
        "SELECT u.id, u.nombre, u.email, u.rol
         FROM sessions s
         JOIN usuarios u ON u.id = s.user_id
         WHERE s.token = ? AND s.expires_at > NOW()"
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'token invalido']);
        exit;
    }
    return $user;
}
