<?php
/**
 * Encabezado compartido por todas las páginas del panel.
 * Variables esperadas antes del include:
 *   $paginaActiva  -> 'panel' | 'prestamos' | 'inventario'
 *   $tituloPagina  -> título mostrado en <title> y en la pestaña del navegador
 */
require_once __DIR__ . '/icons.php';

$usuario = lb_usuario_actual();
$flash = lb_tomar_flash();

$NAV = [
  ['id' => 'panel', 'href' => 'panel.php', 'label' => 'Panel general', 'icon' => 'layout-dashboard'],
  ['id' => 'prestamos', 'href' => 'prestamos.php', 'label' => 'Préstamos', 'icon' => 'library-big'],
  ['id' => 'inventario', 'href' => 'inventario.php', 'label' => 'Inventario e incidencias', 'icon' => 'shield-alert'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - EPET34</title>
  <link rel="icon" href="/biblioteca-sql/assets/img/logo-epet34.jpg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Iconos de Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="/biblioteca-sql/assets/css/styles.css">

  <style>
    /* 1. Header superior fijo arriba del todo */
    .header-superior-fijo {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 45px; /* Altura controlada */
      z-index: 1050;
    }

    /* 2. El body baja para compensar el header superior */
    body {
      padding-top: 45px;
      overflow-x: hidden;
    }

    /* 3. Estructura general de la aplicación */
    .shell {
      display: flex;
      min-height: calc(100vh - 45px);
      position: relative;
    }

    /* 4. Barra lateral fija en pantallas grandes */
    @media (min-width: 992px) {
      .sidebar-desktop {
        position: fixed;
        top: 45px; /* Justo debajo del header superior */
        bottom: 0;
        left: 0;
        width: 260px; /* Ancho estándar de la sidebar (ajusta si es necesario) */
        overflow-y: auto;
        z-index: 1040;
       
      }

      /* 5. Empujamos el contenido principal a la derecha para que no quede debajo de la sidebar */
      .shell-main {
        margin-left: 260px;
        width: calc(100% - 260px);
        flex-grow: 1;
      }
    }
  </style>
</head>

<body>

  <!-- Encabezado superior fijo -->
  <header class="bg-dark text-white py-1 px-3 border-light-subtle header-superior-fijo shadow-sm d-flex justify-content-between align-items-center">
    <!-- Lado Izquierdo: Usuario desde $_SESSION -->
    <div class="d-flex align-items-center gap-2">
      <span>
        Bienvenido,
        <strong class="text-white">
          <?php
          $nombre   = $_SESSION['nombre']   ?? $_SESSION['usuario_nombre']   ?? '';
          $apellido = $_SESSION['apellido'] ?? $_SESSION['usuario_apellido'] ?? '';

          if (!empty($nombre) || !empty($apellido)) {
            echo htmlspecialchars(trim("$nombre $apellido"), ENT_QUOTES, 'UTF-8');
          } elseif (isset($_SESSION['usuario'])) {
            $u = $_SESSION['usuario'];
            $nom = is_array($u) ? ($u['nombre'] ?? '') : $u;
            $ape = is_array($u) ? ($u['apellido'] ?? '') : '';
            echo htmlspecialchars(trim("$nom $ape"), ENT_QUOTES, 'UTF-8');
          } else {
            echo 'Usuario';
          }
          ?>
        </strong>
      </span>
    </div>

    <!-- Lado Derecho: Fecha y Hora -->
    <div class="d-flex align-items-center gap-3 text-secondary small">
      <!-- Fecha -->
      <div id="fecha-header" class="d-none d-md-block fw-medium text-white">
        <?= date('d/m/Y') ?>
      </div>

      <!-- Reloj digital -->
      <div class="bg-light bg-opacity-50 text-white px-2 py-1 rounded border font-monospace d-flex align-items-center gap-1 shadow-sm">
        <i class="bi bi-clock text-dark"></i>
        <span id="reloj-header" class="fw-bold">00:00:00</span>
      </div>
    </div>
  </header>

  <!-- Script para la hora en vivo -->
  <script>
    function actualizarReloj() {
      const ahora = new Date();
      const horaFormateada = ahora.toLocaleTimeString('es-AR', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      });

      const elemReloj = document.getElementById('reloj-header');
      if (elemReloj) {
        elemReloj.textContent = horaFormateada;
      }
    }

    actualizarReloj();
    setInterval(actualizarReloj, 1000);
  </script>

  <!-- Checkbox oculto: controla el menú móvil sin JavaScript -->
  <input type="checkbox" id="menuState" class="menu-state">

  <div class="shell">
    <aside class="sidebar sidebar-desktop">
      <?php include __DIR__ . '/sidebar_content.php'; ?>
    </aside>

    <label for="menuState" class="sidebar-overlay-bg"></label>
    <div class="sidebar-drawer">
      <?php include __DIR__ . '/sidebar_content.php'; ?>
    </div>

<div class="shell-main">
      <header class="mobile-topbar bg-dark text-white py-3 px-4 shadow-sm" style="background-color: #000 !important; min-height: 70px;">
        <div class="mobile-topbar-brand d-flex align-items-center gap-2">
          <!-- Reemplazamos lb_icon por la etiqueta img de tu logo -->
          <img src="/assets/img/logo.png" alt="Logo EPET34" style="height: 40px; width: auto;" class="img-fluid">
          <span class="fs-5 fw-bold">Biblioteca Central</span>
        </div>
        <label for="menuState" class="icon-btn menu-toggle text-white fs-4" aria-label="Abrir menú">
          <?= lb_icon('menu', 'icon-open') ?>
          <?= lb_icon('x', 'icon-close') ?>
        </label>
      </header>

      <main class="view-root p-4">
        <?php if ($flash): ?>
          <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>">
            <?= lb_icon($flash['tipo'] === 'success' ? 'check-circle-2' : ($flash['tipo'] === 'warning' ? 'triangle-alert' : 'circle-x')) ?>
            <span><?= htmlspecialchars($flash['mensaje']) ?></span>
          </div>
        <?php endif; ?>