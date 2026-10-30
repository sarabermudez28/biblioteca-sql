<!-- INDEX.PHP -->
<!-- Inicio del sistema y login -->

<?php
session_start();
require_once __DIR__ . '/conexion.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  $sql = "SELECT * FROM usuario WHERE gmail = ? AND contra = ? AND id_rol IN (1, 5)";
  $stmt = db()->prepare($sql);
  $stmt->execute([$email, $password]);

  $usuario = $stmt->fetch();
  
  if ($usuario) {

    $_SESSION['lb_usuario'] = $usuario['gmail'];
    $_SESSION['dni_usuario'] = $usuario['dni_usuario'];


    header("Location: panel.php");
    exit;
  } else {
    $error = "Credenciales incorrectas.";
  }
}

require_once __DIR__ . '/includes/icons.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca Central · Panel de Administración</title>
  <meta name="description" content="Sistema de administración de biblioteca con métricas en tiempo real, gestión de préstamos y estadísticas anuales.">
  <link rel="icon" href="assets/img/logo-epet34.webp" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="login-body">

  <main class="login-layout">
    <section class="login-brand">
      <div class="brand-row">
        <div class="brand-badge"><?= lb_icon('book-marked') ?></div>
        <div class="brand-copy">
          <p class="brand-title">Biblioteca EPET34</p>
          <p class="brand-subtitle">Panel de Administración</p>
        </div>
      </div>

      <div class="brand-hero">
        <h1>Gestión inteligente del material bibliográfico</h1>
        <p>Supervisa préstamos, controla pérdidas y daños, y consulta estadísticas anuales desde un solo lugar.</p>
      </div>

      <p class="brand-footer">© <?= date('Y') ?> Biblioteca EPET34. Todos los derechos reservados.</p>
    </section>

    <section class="login-form-section">
      <div class="login-form-wrap">
        <div class="login-form-header">
          <h2>Iniciar sesión</h2>
          <p>Acceso exclusivo para personal administrativo.</p>
        </div>

        <form method="post" class="login-form" action="index.php">
          <div class="field">
            <label for="email">Correo electrónico</label>
            <input id="email" name="email" type="email" placeholder="admin@biblioteca.gob" required>
          </div>

          <div class="field">
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password"  placeholder="••••••••" required>
          </div>

          <?php if ($error): ?>
            <p class="form-error" role="alert"><?= htmlspecialchars($error) ?></p>
          <?php endif; ?>

          <input type="submit" value="Entrar al panel" class="btn btn-default w-full">

        </form>

        <div class="demo-box">
          <p class="demo-title">Credenciales de demostración</p>
          <p>Correo: <span class="mono">admin@biblioteca.gob</span><br>
            Contraseña: <span class="mono">biblioteca2026</span></p>
        </div>
      </div>
    </section>
  </main>

</body>

</html>