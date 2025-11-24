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
        "SELECT id, nombre, apellido_paterno, apellido_materno,
                edad, sexo, direccion
         FROM pacientes
         ORDER BY nombre ASC, apellido_paterno ASC"
    )->fetchAll();

    jexit(200, ['ok' => true, 'data' => $rows]);
}

if ($method === 'POST') {
    $inputs = json_decode(file_get_contents('php://input'), true);
    $action = $inputs['action'] ?? 'create';

    if ($action === 'create') {
        $nombre = trim($inputs['nombre'] ?? '');
        $ap_p = trim($inputs['apellido_paterno'] ?? '');
        $ap_m = trim($inputs['apellido_materno'] ?? '');
        $edad = (int)($inputs['edad'] ?? 0);
        $sexo = trim($inputs['sexo'] ?? '');
        $direccion = trim($inputs['direccion'] ?? '');

        if ($nombre === '' || $ap_p === '' || $ap_m === '' ||
            $edad <= 0 || $sexo === '' || $direccion === '') {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO pacientes
             (nombre, apellido_paterno, apellido_materno, edad, sexo, direccion)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $ap_p, $ap_m, $edad, $sexo, $direccion]);

        jexit(200, ['ok' => true, 'id' => $pdo->lastInsertId()]);
    }

    if ($action === 'update') {
        $id = (int)($inputs['id'] ?? 0);
        $nombre = trim($inputs['nombre'] ?? '');
        $ap_p = trim($inputs['apellido_paterno'] ?? '');
        $ap_m = trim($inputs['apellido_materno'] ?? '');
        $edad = (int)($inputs['edad'] ?? 0);
        $sexo = trim($inputs['sexo'] ?? '');
        $direccion = trim($inputs['direccion'] ?? '');

        if ($id <= 0 || $nombre === '' || $ap_p === '' || $ap_m === '' ||
            $edad <= 0 || $sexo === '' || $direccion === '') {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        $stmt = $pdo->prepare(
            "UPDATE pacientes
             SET nombre = ?, apellido_paterno = ?, apellido_materno = ?,
                 edad = ?, sexo = ?, direccion = ?
             WHERE id = ?"
        );
        $stmt->execute([$nombre, $ap_p, $ap_m, $edad, $sexo, $direccion, $id]);

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
