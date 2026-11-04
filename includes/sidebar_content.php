<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<<<<<<< HEAD

<div class="sidebar-brand">
  <a href="/biblioteca-sql/panel.php">
    <div class="sidebar-brand-badge">
      <img src="/biblioteca-sql/assets/img/logo.png" alt="Logo Panel" style="width: 130px; height: 130px; object-fit: contain;">
    </div>
    <div class="sidebar-brand-copy">
      <p class="sidebar-brand-title">Biblioteca EPET34</p>
      <p class="sidebar-brand-subtitle">Administración</p>
    </div>
  </a>
</div>

<nav class="sidebar-nav">
  <!-- Panel -->
  <a href="/biblioteca-sql/panel.php" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'panel' ? 'activo' : '' ?>">
    <i class="fa-solid fa-gauge-high"></i>
    <span>Panel</span>
  </a>

  <!-- Inventario -->
  <a href="/biblioteca-sql/inventario/" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'inventario' ? 'activo' : '' ?>">
    <i class="fa-solid fa-book-bookmark"></i>
    <span>Inventario</span>
  </a>

  <!-- Sist. de Etiquetado -->
  <a href="/biblioteca-sql/inventario/generar_qr.php" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'qr' ? 'activo' : '' ?>">
    <i class="fa-solid fa-qrcode"></i>
    <span>Sist. de Etiquetado</span>
  </a>

  <!-- Préstamos -->
  <a href="/biblioteca-sql/prestamos/" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'prestamos' ? 'activo' : '' ?>">
    <i class="fa-solid fa-clipboard-user"></i>
    <span>Préstamos</span>
  </a>

  <!-- Notificaciones -->
  <a href="/biblioteca-sql/notificaciones.php" class="sidebar-nav-item <?= ($paginaActiva ?? '') === 'notificaciones' ? 'activo' : '' ?>">
    <i class="fa-solid fa-bell"></i>
    <span>Notificaciones</span>
    <?php if (isset($totalNotificacionesPendientes) && $totalNotificacionesPendientes > 0): ?>
      <span class="notif-badge"><?= $totalNotificacionesPendientes ?></span>
    <?php endif; ?>
  </a>

  <!-- Cerrar sesión -->
  <a href="/biblioteca-sql/logout.php" class="sidebar-nav-item" style="margin-top: auto; color: #fca5a5;">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Cerrar sesión</span>
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
  <a href="/biblioteca-sql/logout.php" class="sidebar-logout">
    <?= lb_icon('log-out') ?> Cerrar sesión
  </a>
</div>
=======
 <div class="sidebar-brand">
   <a href="/biblioteca-sql/panel.php">
     <div class="sidebar-brand-badge">
       <img src="/biblioteca-sql/assets/img/logo.png" alt="Logo Panel" style="width: 130px; height: 130px; object-fit: contain;">
     </div>
     <div class="sidebar-brand-copy">
       <p class="sidebar-brand-title">Biblioteca EPET34</p>
       <p class="sidebar-brand-subtitle">Administración</p>
     </div>
   </a>
 </div>
 <nav class="sidebar-nav">
   <a href="/biblioteca-sql/panel.php" class="sidebar-nav-item"><i class="fa-solid fa-gauge-high"></i> Panel</a>
   <a href="/biblioteca-sql/inventario/" class="sidebar-nav-item"><i class="fa-solid fa-book-bookmark"></i> Inventario</a>
   <a href="/biblioteca-sql/inventario/generar_qr.php" class="sidebar-nav-item"><i class="fa-solid fa-qrcode"></i> Sist. de Etiquetado</a>
   <a href="/biblioteca-sql/prestamos/" class="sidebar-nav-item"><i class="fa-solid fa-clipboard-user"></i> Préstamos</a>
   <a href="/biblioteca-sql/historial/" class="sidebar-nav-item"><i class="fa-solid fa-clock-rotate-left"></i> Historial</a>
   <a href="/biblioteca-sql/logout.php" class="sidebar-nav-item"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
 </nav>
 <div class="sidebar-footer">
   <div class="sidebar-user">
     <div class="sidebar-avatar"><?= htmlspecialchars(mb_substr($usuario ?? 'A', 0, 1)) ?></div>
     <div class="sidebar-user-copy">
       <p class="sidebar-user-name"><?= htmlspecialchars($usuario ?? '') ?></p>
       <p class="sidebar-user-email">admin@biblioteca.gob</p>
     </div>
   </div>
   <a href="/biblioteca-sql/logout.php" class="sidebar-logout">
     <?= lb_icon('log-out') ?>Cerrar sesión
   </a>
 </div>
>>>>>>> 47af5a9 (carpeta de historial)
