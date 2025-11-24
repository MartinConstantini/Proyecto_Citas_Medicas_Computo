<?php
require __DIR__ . '/../api/db.php';

$token = $_COOKIE['token'] ?? null;

if ($token) {
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE token = ?");
    $stmt->execute([$token]);

    setcookie('token', '', time() - 3600, '/', '', false, true);
}

header('Location: login.php');
exit;
