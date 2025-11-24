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
    <title>citas medicas - pacientes</title>
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
            <li class="nav-item"><a href="dashboard.php" class="nav-link">panel</a></li>
            <li class="nav-item"><a href="agenda.php" class="nav-link">agenda</a></li>
            <li class="nav-item"><a href="medicos.php" class="nav-link">medicos</a></li>
            <li class="nav-item"><a href="pacientes.php" class="nav-link active">pacientes</a></li>
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
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h1 class="h5 mb-0 text-primary-custom">pacientes</h1>
          <p class="text-muted small mb-0">administracion basica de pacientes</p>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarPaciente">
          agregar paciente
        </button>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr>
                <th>nombre</th>
                <th>correo</th>
                <th>fecha nacimiento</th>
                <th>telefono</th>
                <th class="text-end">acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-pacientes">
              <tr><td colspan="5" class="text-center text-muted small">cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- modal agregar paciente -->
    <div class="modal fade" id="modalAgregarPaciente" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="form-agregar-paciente">
            <div class="modal-header">
              <h5 class="modal-title">agregar paciente</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
              <p class="text-muted small">
                primero debes tener creado un usuario con rol paciente. aqui solo se vincula ese usuario con sus datos de paciente.
              </p>
              <div class="mb-2">
                <label class="form-label">id usuario</label>
                <input type="number" name="usuario_id" class="form-control input-soft" placeholder="id usuario existente" required>
              </div>
              <div class="mb-2">
                <label class="form-label">fecha nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control input-soft">
              </div>
              <div class="mb-2">
                <label class="form-label">telefono</label>
                <input type="text" name="telefono" class="form-control input-soft">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">cerrar</button>
              <button class="btn btn-primary btn-sm">guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- modal editar paciente -->
    <div class="modal fade" id="modalEditarPaciente" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="form-editar-paciente">
            <div class="modal-header">
              <h5 class="modal-title">editar paciente</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
              <input type="hidden" name="id">
              <div class="mb-2">
                <label class="form-label">nombre</label>
                <input type="text" name="nombre" class="form-control input-soft" disabled>
              </div>
              <div class="mb-2">
                <label class="form-label">correo</label>
                <input type="email" name="email" class="form-control input-soft" disabled>
              </div>
              <div class="mb-2">
                <label class="form-label">fecha nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control input-soft">
              </div>
              <div class="mb-2">
                <label class="form-label">telefono</label>
                <input type="text" name="telefono" class="form-control input-soft">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-danger btn-sm" id="btn-eliminar-paciente">eliminar</button>
              <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">cerrar</button>
              <button class="btn btn-primary btn-sm">guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      const usuarioActual = <?php echo json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/pacientes.js"></script>
  </body>
</html>
