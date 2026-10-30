    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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
      <a href="/biblioteca-sql/panel.php" class="sidebar-nav-item">Panel <i class="fa-solid fa-bars"></i></i></a>
      <a href="/biblioteca-sql/inventario/" class="sidebar-nav-item">Inventario <i class="fa-solid fa-book-bookmark"></i></a>
      <a href="/biblioteca-sql/inventario/generar_qr.php" class="sidebar-nav-item">Sist. de Etiquetado <i class="fa-solid fa-qrcode"></i></a>
      <a href="/biblioteca-sql/prestamos/" class="sidebar-nav-item">Prestamos <i class="fa-solid fa-clipboard-user"></i></a>
      <a href="/biblioteca-sql/logout.php" class="sidebar-nav-item">Cerrar sesión <i class="fa-solid fa-right-from-bracket"></i></a>

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