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
    <title>citas medicas - agenda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
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
            <li class="nav-item"><a href="dashboard.php" class="nav-link">panel</a></li>
            <li class="nav-item"><a href="agenda.php" class="nav-link active">agenda</a></li>
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
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div id="calendar"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h2 class="h6 text-primary-custom mb-3">Citas medicas</h2>

              <form id="form-cita" class="small">
                <input type="hidden" name="id">

                <div class="mb-2">
                  <label class="form-label">Medico</label>
                  <select name="medico_id" class="form-select input-soft" required>
                    <option value="">cargando...</option>
                  </select>
                </div>

                <div class="mb-2">
                  <label class="form-label">Nombre Paciente</label>
                  <select name="paciente_id" class="form-select input-soft" required>
                    <option value="">cargando...</option>
                  </select>
                </div>

                <div class="mb-2">
                  <label class="form-label">Fecha</label>
                  <input type="date" name="fecha" class="form-control input-soft" required>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label">Hora inicio de la consulta</label>
                    <input type="time" name="hora_inicio" class="form-control input-soft" required>
                  </div>
                  <div class="col-6">
                    <label class="form-label">hora fin consulta</label>
                    <input type="time" name="hora_fin" class="form-control input-soft" required>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Motivo</label>
                  <textarea name="motivo" rows="3" class="form-control input-soft" placeholder="Escribe los motivos de la consulta medica"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                  <button type="button" id="btn-eliminar-cita" class="btn btn-outline-danger btn-sm">
                    eliminar
                  </button>
                  <button class="btn btn-primary btn-sm">
                    guardar cita
                  </button>
                </div>

                <p class="text-muted small mt-3 mb-0">
                  Informacion de sitas medicas
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <script>
      const usuarioActual = <?php echo json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- version global de fullcalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>

    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/agenda.js"></script>
  </body>
</html>
