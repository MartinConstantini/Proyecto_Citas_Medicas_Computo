<?php
?><!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>citas medicas - registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
  </head>
  <body class="bg-soft-blue d-flex align-items-center justify-content-center vh-100">

    <div class="login-card shadow-lg rounded-4 p-4 p-md-5">
      <div class="text-center mb-4">
        <img src="../assets/img/logo.png" alt="logo" class="login-logo mb-2">
        <h1 class="h4 mb-0 text-primary-custom">sistema de citas medicas</h1>
        <p class="text-muted small mb-0">crear nueva cuenta</p>
      </div>

      <form id="form-signup" class="mt-2">
        <div class="mb-2">
          <label class="form-label small text-muted">nombre</label>
          <input type="text" name="nombre" class="form-control input-soft" placeholder="nombre completo" required>
        </div>
        <div class="mb-2">
          <label class="form-label small text-muted">correo</label>
          <input type="email" name="email" class="form-control input-soft" placeholder="correo" required>
        </div>
        <div class="mb-3">
          <label class="form-label small text-muted">contrasena</label>
          <input type="password" name="password" class="form-control input-soft" placeholder="minimo 6 caracteres" minlength="6" required>
        </div>
        <p class="text-muted small mb-2">
          las cuentas creadas desde aqui se registran con rol paciente
        </p>
        <button class="btn btn-primary w-100 mt-1">crear cuenta</button>
      </form>

      <p class="small mt-3 mb-0 text-center text-muted">
        ya tienes cuenta?
        <a href="login.php" class="link-primary">iniciar sesion</a>
      </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/signup.js"></script>
  </body>
</html>
