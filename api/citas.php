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
        "SELECT c.id, c.medico_id, c.paciente_id, c.fecha,
                c.hora_inicio, c.hora_fin, c.motivo, c.estado,
                CONCAT(m.nombre, ' ', m.apellido_paterno) AS medico_nombre,
                CONCAT(p.nombre, ' ', p.apellido_paterno) AS paciente_nombre
         FROM citas c
         JOIN medicos m ON m.id = c.medico_id
         JOIN pacientes p ON p.id = c.paciente_id
         ORDER BY c.fecha ASC, c.hora_inicio ASC"
    )->fetchAll();

    jexit(200, ['ok' => true, 'data' => $rows]);
}

if ($method === 'POST') {
    $inputs = json_decode(file_get_contents('php://input'), true);
    $action = $inputs['action'] ?? 'create';

    if ($action === 'create') {
        $medico_id = (int)($inputs['medico_id'] ?? 0);
        $paciente_id = (int)($inputs['paciente_id'] ?? 0);
        $fecha = $inputs['fecha'] ?? '';
        $hora_inicio = $inputs['hora_inicio'] ?? '';
        $hora_fin = $inputs['hora_fin'] ?? '';
        $motivo = trim($inputs['motivo'] ?? '');

        if ($medico_id <= 0 || $paciente_id <= 0 || $fecha === '' || $hora_inicio === '' || $hora_fin === '') {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        // validar que no haya traslape para el medico
        $sql = "SELECT COUNT(*) AS cnt
                FROM citas
                WHERE medico_id = ?
                  AND fecha = ?
                  AND NOT (hora_fin <= ? OR hora_inicio >= ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$medico_id, $fecha, $hora_inicio, $hora_fin]);
        $row = $stmt->fetch();

        if ($row && $row['cnt'] > 0) {
            jexit(409, ['ok' => false, 'message' => 'horario ocupado para este medico']);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO citas
             (medico_id, paciente_id, fecha, hora_inicio, hora_fin, motivo, estado, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 'pendiente', NOW())"
        );
        $stmt->execute([$medico_id, $paciente_id, $fecha, $hora_inicio, $hora_fin, $motivo]);

        jexit(200, ['ok' => true, 'id' => $pdo->lastInsertId()]);
    }

    if ($action === 'update') {
        $id = (int)($inputs['id'] ?? 0);
        $medico_id = (int)($inputs['medico_id'] ?? 0);
        $paciente_id = (int)($inputs['paciente_id'] ?? 0);
        $fecha = $inputs['fecha'] ?? '';
        $hora_inicio = $inputs['hora_inicio'] ?? '';
        $hora_fin = $inputs['hora_fin'] ?? '';
        $motivo = trim($inputs['motivo'] ?? '');
        $estado = trim($inputs['estado'] ?? 'pendiente');

        if ($id <= 0 || $medico_id <= 0 || $paciente_id <= 0 ||
            $fecha === '' || $hora_inicio === '' || $hora_fin === '') {
            jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
        }

        $sql = "SELECT COUNT(*) AS cnt
                FROM citas
                WHERE medico_id = ?
                  AND fecha = ?
                  AND NOT (hora_fin <= ? OR hora_inicio >= ?)
                  AND id <> ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$medico_id, $fecha, $hora_inicio, $hora_fin, $id]);
        $row = $stmt->fetch();

        if ($row && $row['cnt'] > 0) {
            jexit(409, ['ok' => false, 'message' => 'horario ocupado para este medico']);
        }

        $stmt = $pdo->prepare(
            "UPDATE citas
             SET medico_id = ?, paciente_id = ?, fecha = ?, hora_inicio = ?, hora_fin = ?,
                 motivo = ?, estado = ?
             WHERE id = ?"
        );
        $stmt->execute([$medico_id, $paciente_id, $fecha, $hora_inicio, $hora_fin, $motivo, $estado, $id]);

        jexit(200, ['ok' => true]);
    }

    if ($action === 'delete') {
        $id = (int)($inputs['id'] ?? 0);
        if ($id <= 0) {
            jexit(422, ['ok' => false, 'message' => 'id invalido']);
        }

        $stmt = $pdo->prepare("DELETE FROM citas WHERE id = ?");
        $stmt->execute([$id]);

        jexit(200, ['ok' => true]);
    }

    jexit(400, ['ok' => false, 'message' => 'accion no reconocida']);
}

jexit(405, ['ok' => false, 'message' => 'metodo no permitido']);
