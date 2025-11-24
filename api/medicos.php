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
        "SELECT id, cedula, nombre, apellido_paterno, apellido_materno,
                edad, genero, especialidad, telefono
         FROM medicos
         ORDER BY nombre ASC, apellido_paterno ASC"
    )->fetchAll();

    jexit(200, ['ok' => true, 'data' => $rows]);
}

if ($method === 'POST') {
    $inputs = json_decode(file_get_contents('php://input'), true);
    $action = $inputs['action'] ?? 'create';

    if ($action === 'create') {
        $cedula = trim($inputs['cedula'] ?? '');
        $nombre = trim($inputs['nombre'] ?? '');
        $ap_p = trim($inputs['apellido_paterno'] ?? '');
        $ap_m = trim($inputs['apellido_materno'] ?? '');
        $edad = (int)($inputs['edad'] ?? 0);
        $genero = trim($inputs['genero'] ?? '');
        $especialidad = trim($inputs['especialidad'] ?? '');
        $telefono = trim($inputs['telefono'] ?? '');

        if ($cedula === '' || $nombre === '' || $ap_p === '' || $ap_m === '' ||
            $edad <= 0 || $genero === '' || $especialidad === '' || $telefono === '') {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO medicos
             (cedula, nombre, apellido_paterno, apellido_materno, edad, genero, especialidad, telefono)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$cedula, $nombre, $ap_p, $ap_m, $edad, $genero, $especialidad, $telefono]);

        jexit(200, ['ok' => true, 'id' => $pdo->lastInsertId()]);
    }

    if ($action === 'update') {
        $id = (int)($inputs['id'] ?? 0);
        $cedula = trim($inputs['cedula'] ?? '');
        $nombre = trim($inputs['nombre'] ?? '');
        $ap_p = trim($inputs['apellido_paterno'] ?? '');
        $ap_m = trim($inputs['apellido_materno'] ?? '');
        $edad = (int)($inputs['edad'] ?? 0);
        $genero = trim($inputs['genero'] ?? '');
        $especialidad = trim($inputs['especialidad'] ?? '');
        $telefono = trim($inputs['telefono'] ?? '');

        if ($id <= 0 || $cedula === '' || $nombre === '' || $ap_p === '' || $ap_m === '' ||
            $edad <= 0 || $genero === '' || $especialidad === '' || $telefono === '') {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        $stmt = $pdo->prepare(
            "UPDATE medicos
             SET cedula = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?,
                 edad = ?, genero = ?, especialidad = ?, telefono = ?
             WHERE id = ?"
        );
        $stmt->execute([$cedula, $nombre, $ap_p, $ap_m, $edad, $genero, $especialidad, $telefono, $id]);

        jexit(200, ['ok' => true]);
    }

    if ($action === 'delete') {
        $id = (int)($inputs['id'] ?? 0);
        if ($id <= 0) {
            jexit(422, ['ok' => false, 'message' => 'id invalido']);
        }

        $stmt = $pdo->prepare("DELETE FROM medicos WHERE id = ?");
        $stmt->execute([$id]);

        jexit(200, ['ok' => true]);
    }

    jexit(400, ['ok' => false, 'message' => 'accion no reconocida']);
}

jexit(405, ['ok' => false, 'message' => 'metodo no permitido']);
