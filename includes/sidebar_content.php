<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<div class="sidebar-brand">
  <a href="/biblioteka/biblioteca-sql/panel.php">
    <div class="sidebar-brand-badge">
      <img src="/biblioteka/biblioteca-sql/assets/img/logo.png" alt="Logo Panel" style="width: 130px; height: 130px; object-fit: contain;">
    </div>
    <div class="sidebar-brand-copy">
      <p class="sidebar-brand-title">Biblioteca EPET34</p>
      <p class="sidebar-brand-subtitle">Administración</p>
    </div>
  </a>
</div>

<nav class="sidebar-nav">
  <!-- Panel -->
  <a href="/biblioteka/biblioteca-sql/panel.php" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'panel' ? 'activo' : '' ?>">
    <span>Panel</span>
    <i class="fa-solid fa-chart-line"></i>
  </a>

  <!-- Inventario -->
  <a href="/biblioteka/biblioteca-sql/inventario/" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'inventario' ? 'activo' : '' ?>">
    <span>Inventario</span>
    <i class="fa-solid fa-book-bookmark"></i>
  </a>

  <!-- Sist. de Etiquetado -->
  <a href="/biblioteka/biblioteca-sql/inventario/generar_qr.php" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'qr' ? 'activo' : '' ?>">
    <span>Sist. de Etiquetado</span>
    <i class="fa-solid fa-qrcode"></i>
  </a>

  <!-- Préstamos -->
  <a href="/biblioteka/biblioteca-sql/prestamos/" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'prestamos' ? 'activo' : '' ?>">
    <span>Préstamos</span>
    <i class="fa-solid fa-clipboard-user"></i>
  </a>

  <!-- NOTIFICACIONES (con badge dinámico) -->
  <a href="/biblioteka/biblioteca-sql/notificaciones.php" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'notificaciones' ? 'activo' : '' ?>">
    <span>Notificaciones</span>
    <i class="fa-solid fa-bell" style="margin-left: auto;"></i>
    <?php if (isset($totalNotificacionesPendientes) && $totalNotificacionesPendientes > 0): ?>
      <span class="notif-badge"><?= $totalNotificacionesPendientes ?></span>
    <?php endif; ?>
  </a>

  <!-- Cerrar sesión -->
  <a href="/biblioteka/biblioteca-sql/logout.php" class="sidebar-nav-item" style="margin-top: auto; color: #fca5a5;">
    <span>Cerrar sesión</span>
    <i class="fa-solid fa-right-from-bracket"></i>
  </a>
</nav>

<div class="sidebar-footer">
  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= htmlspecialchars(mb_substr($usuario ?? 'A', 0, 1)) ?></div>
    <div class="sidebar-user-copy">
      <p class="sidebar-user-name"><?= htmlspecialchars($usuario ?? 'Usuario') ?></p>
      <p class="sidebar-user-email">admin@biblioteca.gob</p>
    </div>
  </div>
  <a href="/biblioteka/biblioteca-sql/logout.php" class="sidebar-logout">
    <?= lb_icon('log-out') ?> Cerrar sesión
  </a>
</div>