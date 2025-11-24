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

// reporte pdf
if ($method === 'GET' && ($_GET['reporte'] ?? '') === 'pdf') {
    $exists = file_exists(__DIR__ . '/fpdf.php');
    if (!$exists) {
        http_response_code(500);
        echo 'faltante archivo fpdf.php para generar pdf';
        exit;
    }

    require_once __DIR__ . '/fpdf.php';

    $stmt = $pdo->query(
        "SELECT c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.motivo,
                c.estado,
                um.nombre as nombre_medico,
                up.nombre as nombre_paciente
         FROM citas c
         JOIN medicos m ON m.id = c.medico_id
         JOIN usuarios um ON um.id = m.usuario_id
         JOIN pacientes p ON p.id = c.paciente_id
         JOIN usuarios up ON up.id = p.usuario_id
         ORDER BY c.fecha, c.hora_inicio"
    );
    $rows = $stmt->fetchAll();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'reporte de citas', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);

    foreach ($rows as $r) {
        $line = '#' . $r['id'] .
            ' ' . $r['fecha'] .
            ' ' . $r['hora_inicio'] . '-' . $r['hora_fin'] .
            ' medico: ' . $r['nombre_medico'] .
            ' paciente: ' . $r['nombre_paciente'] .
            ' estado: ' . $r['estado'];
        $pdf->Cell(0, 6, $line, 0, 1, 'L');
    }

    header('Content-Type: application/pdf');
    $pdf->Output();
    exit;
}

// eventos para fullcalendar
if ($method === 'GET' && ($_GET['mode'] ?? '') === 'calendar') {
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;

    if (!$start || !$end) {
        jexit(422, ['ok' => false, 'message' => 'rango invalido']);
    }

    $stmt = $pdo->prepare(
        "SELECT c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                um.nombre as nombre_medico,
                up.nombre as nombre_paciente
         FROM citas c
         JOIN medicos m ON m.id = c.medico_id
         JOIN usuarios um ON um.id = m.usuario_id
         JOIN pacientes p ON p.id = c.paciente_id
         JOIN usuarios up ON up.id = p.usuario_id
         WHERE c.fecha BETWEEN ? AND ?
         ORDER BY c.fecha, c.hora_inicio"
    );
    $stmt->execute([$start, $end]);
    $rows = $stmt->fetchAll();

    $events = [];
    foreach ($rows as $r) {
        $events[] = [
            'id' => $r['id'],
            'title' => $r['nombre_medico'] . ' - ' . $r['nombre_paciente'],
            'start' => $r['fecha'] . 'T' . $r['hora_inicio'],
            'end' => $r['fecha'] . 'T' . $r['hora_fin'],
        ];
    }

    echo json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// listar citas
if ($method === 'GET') {
    $stmt = $pdo->query(
        "SELECT c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.motivo,
                c.estado,
                um.nombre as nombre_medico,
                up.nombre as nombre_paciente
         FROM citas c
         JOIN medicos m ON m.id = c.medico_id
         JOIN usuarios um ON um.id = m.usuario_id
         JOIN pacientes p ON p.id = c.paciente_id
         JOIN usuarios up ON up.id = p.usuario_id
         ORDER BY c.fecha DESC, c.hora_inicio DESC"
    );
    $rows = $stmt->fetchAll();
    jexit(200, ['ok' => true, 'data' => $rows]);
}

// crear o borrar
if ($method === 'POST') {
    $inputs = json_decode(file_get_contents('php://input'), true);
    $action = $inputs['action'] ?? 'create';

    if ($action === 'delete') {
        $id = (int)($inputs['id'] ?? 0);
        if ($id <= 0) {
            jexit(422, ['ok' => false, 'message' => 'id invalido']);
        }
        $stmt = $pdo->prepare("DELETE FROM citas WHERE id = ?");
        $stmt->execute([$id]);
        jexit(200, ['ok' => true]);
    }

    $medico_id = (int)($inputs['medico_id'] ?? 0);
    $paciente_id = (int)($inputs['paciente_id'] ?? 0);
    $fecha = $inputs['fecha'] ?? '';
    $hora_inicio = $inputs['hora_inicio'] ?? '';
    $hora_fin = $inputs['hora_fin'] ?? '';
    $motivo = trim($inputs['motivo'] ?? '');

    if ($medico_id <= 0 || $paciente_id <= 0 || $fecha === '' || $hora_inicio === '' || $hora_fin === '') {
        jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
    }

    if ($hora_inicio >= $hora_fin) {
        jexit(422, ['ok' => false, 'message' => 'hora inicio debe ser menor que fin']);
    }

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as total
         FROM citas
         WHERE medico_id = ?
           AND fecha = ?
           AND hora_inicio < ?
           AND hora_fin > ?"
    );
    $stmt->execute([$medico_id, $fecha, $hora_fin, $hora_inicio]);
    $row = $stmt->fetch();

    if ($row && (int)$row['total'] > 0) {
        jexit(422, ['ok' => false, 'message' => 'el medico ya tiene cita en ese horario']);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO citas
         (medico_id, paciente_id, fecha, hora_inicio, hora_fin, motivo, estado, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 'pendiente', NOW())"
    );
    $stmt->execute([$medico_id, $paciente_id, $fecha, $hora_inicio, $hora_fin, $motivo]);

    $id = $pdo->lastInsertId();

    try {
        $stmtMail = $pdo->prepare(
            "SELECT u.email, u.nombre
             FROM pacientes p
             JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.id = ?"
        );
        $stmtMail->execute([$paciente_id]);
        $pac = $stmtMail->fetch();

        if ($pac && filter_var($pac['email'], FILTER_VALIDATE_EMAIL)) {
            $to = $pac['email'];
            $subject = 'confirmacion de cita';
            $message = "hola " . $pac['nombre'] .
                " tu cita es el dia " . $fecha .
                " a las " . $hora_inicio . " hasta " . $hora_fin;
            $headers = 'From: no-reply@citas.local';
            @mail($to, $subject, $message, $headers);
        }
    } catch (Throwable $e) {
        // ignorar errores de correo
    }

    jexit(200, ['ok' => true, 'id' => $id]);
}

jexit(405, ['ok' => false, 'message' => 'metodo no permitido']);
