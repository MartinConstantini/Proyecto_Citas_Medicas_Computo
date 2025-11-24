<?php
require __DIR__ . '/../api/db.php';

$token = $_COOKIE['token'] ?? null;
if (!$token) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT u.id, u.nombre, u.email, u.rol
     FROM sessions s
     JOIN usuarios u ON u.id = s.user_id
     WHERE s.token = ? AND s.expires_at > NOW()"
);
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}
?><!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>citas medicas - panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
  </head>
  <body class="bg-body-custom">

    <nav class="navbar navbar-expand-lg navbar-dark bg-nav shadow-sm">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
          <img src="../assets/img/logo.png" alt="logo" class="nav-logo me-2">
          <span class="fw-semibold">citas medicas</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a href="dashboard.php" class="nav-link active">panel</a></li>
            <li class="nav-item"><a href="agenda.php" class="nav-link">agenda</a></li>
            <li class="nav-item"><a href="medicos.php" class="nav-link">medicos</a></li>
            <li class="nav-item"><a href="pacientes.php" class="nav-link">pacientes</a></li>
          </ul>
          <div class="d-flex align-items-center">
            <span class="text-light small me-3">
              <?php echo htmlspecialchars($user['nombre']); ?> (<?php echo htmlspecialchars($user['rol']); ?>)
            </span>
            <button id="btn-logout" class="btn btn-outline-light btn-sm">salir</button>
          </div>
        </div>
      </div>
    </nav>

    <main class="container py-4">
      <!-- contenido igual que antes -->
      <div class="row g-4 align-items-stretch">
        <div class="col-md-8">
          <div class="card card-soft-blue border-0 shadow-sm h-100">
            <div class="card-body">
              <h2 class="h5 mb-1 text-primary-custom">bienvenido</h2>
              <p class="text-muted small mb-3">
                este panel te permite ver un resumen rapido de las citas medicas registradas en el sistema
              </p>

              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <div class="mini-card text-center">
                    <div class="mini-label">citas hoy</div>
                    <div class="mini-value" id="stat-hoy">0</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mini-card text-center">
                    <div class="mini-label">pendientes</div>
                    <div class="mini-value" id="stat-pendientes">0</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mini-card text-center">
                    <div class="mini-label">total citas</div>
                    <div class="mini-value" id="stat-total">0</div>
                  </div>
                </div>
              </div>

              <h3 class="h6 mb-2">citas recientes</h3>
              <ul id="lista-citas" class="list-unstyled small mb-0"></ul>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
              <h3 class="h6 mb-2 text-primary-custom">informacion visual</h3>
              <p class="text-muted small">
                este es un sistema academico minimalista inspirado en plataformas modernas de clinicas
              </p>
              <div class="text-center mt-auto">
                <img src="../assets/img/imagen1.png" alt="imagen demo" class="img-fluid rounded-3 shadow-sm">
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <script>
      const usuarioActual = <?php echo json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/dashboard.js"></script>
  </body>
</html>
