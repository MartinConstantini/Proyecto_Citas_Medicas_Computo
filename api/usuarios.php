<?php
require __DIR__ . '/api_headers.php';
require __DIR__ . '/db.php';

function jexit($code, $payload) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    require __DIR__ . '/auth.php';
    $user = current_user_or_401();
    jexit(200, ['ok' => true, 'data' => $user]);
}

$inputs = json_decode(file_get_contents('php://input'), true);
$action = $inputs['action'] ?? '';

if ($method === 'POST' && $action === 'register') {
    $nombre = trim($inputs['nombre'] ?? '');
    $email = trim($inputs['email'] ?? '');
    $pass = (string)($inputs['password'] ?? '');

    if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
    }

    $rol = 'usuario';
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (nombre, email, password_hash, rol, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$nombre, $email, $hash, $rol]);
        jexit(200, ['ok' => true]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            jexit(422, ['ok' => false, 'message' => 'email ya registrado']);
        }
        jexit(500, ['ok' => false, 'message' => 'error al registrar']);
    }
}

if ($method === 'POST' && $action === 'login') {
    $email = trim($inputs['email'] ?? '');
    $pass = (string)($inputs['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
        jexit(422, ['ok' => false, 'message' => 'datos invalidos']);
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        jexit(401, ['ok' => false, 'message' => 'credenciales invalidas']);
    }

    // solo una sesion activa por usuario
    $del = $pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
    $del->execute([$user['id']]);

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 60 * 60 * 4);

    $stmt = $pdo->prepare(
        "INSERT INTO sessions (user_id, token, expires_at, created_at)
         VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$user['id'], $token, $expires]);

    setcookie('token', $token, [
        'expires' => time() + 60 * 60 * 4,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    jexit(200, [
        'ok' => true,
        'data' => [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol' => $user['rol']
        ]
    ]);
}

jexit(405, ['ok' => false, 'message' => 'metodo no permitido']);
