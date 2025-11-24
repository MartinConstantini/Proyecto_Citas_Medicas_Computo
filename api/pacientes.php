<?php
require __DIR__ . '/api_headers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

function jexit($code, $payload) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$user = current_user_or_401();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query(
        "SELECT p.id, p.usuario_id, u.nombre, u.email, p.fecha_nacimiento, p.telefono
         FROM pacientes p
         JOIN usuarios u ON u.id = p.usuario_id
         ORDER BY u.nombre ASC"
    )->fetchAll();

    jexit(200, ['ok' => true, 'data' => $rows]);
}

if ($method === 'POST') {
    $inputs = json_decode(file_get_contents('php://input'), true);
    $action = $inputs['action'] ?? 'create';

    if (!in_array($user['rol'], ['admin'], true)) {
        jexit(403, ['ok' => false, 'message' => 'solo admin']);
    }

    if ($action === 'create') {
        $usuario_id = (int)($inputs['usuario_id'] ?? 0);
        $fecha_nacimiento = $inputs['fecha_nacimiento'] ?? null;
        $telefono = trim($inputs['telefono'] ?? '');

        if ($usuario_id <= 0) {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO pacientes (usuario_id, fecha_nacimiento, telefono)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$usuario_id, $fecha_nacimiento, $telefono]);

        jexit(200, ['ok' => true, 'id' => $pdo->lastInsertId()]);
    }

    if ($action === 'update') {
        $id = (int)($inputs['id'] ?? 0);
        $fecha_nacimiento = $inputs['fecha_nacimiento'] ?? null;
        $telefono = trim($inputs['telefono'] ?? '');

        if ($id <= 0) {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        $stmt = $pdo->prepare(
            "UPDATE pacientes
             SET fecha_nacimiento = ?, telefono = ?
             WHERE id = ?"
        );
        $stmt->execute([$fecha_nacimiento, $telefono, $id]);

        jexit(200, ['ok' => true]);
    }

    if ($action === 'delete') {
        $id = (int)($inputs['id'] ?? 0);
        if ($id <= 0) {
            jexit(422, ['ok' => false, 'message' => 'id invalido']);
        }

        $stmt = $pdo->prepare("DELETE FROM pacientes WHERE id = ?");
        $stmt->execute([$id]);

        jexit(200, ['ok' => true]);
    }

    jexit(400, ['ok' => false, 'message' => 'accion no reconocida']);
}

jexit(405, ['ok' => false, 'message' => 'metodo no permitido']);
