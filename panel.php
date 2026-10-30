<?php

// ================================
// Verifica que el usuario inició sesión
// ================================
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/auth.php';
lb_requerir_login();









// ================================
// LÓGICA DE ESTADÍSTICAS Y CONSULTAS SQL
// ================================
try {
    $database = db();

    // 1. Tarjetas de Indicadores (Contadores)
    $stmt = $database->query("SELECT COUNT(*) FROM prestamos WHERE fecha_hora_devolucion IS NULL");
    $totalPrestamosActivos = $stmt->fetchColumn();

    $stmt = $database->query("SELECT COUNT(*) FROM prestamos WHERE DATE(fecha_hora_registro) = CURDATE()");
    $prestadosHoy = $stmt->fetchColumn();

    $stmt = $database->query("SELECT COUNT(*) FROM inventario WHERE id_estado_material = 2");
    $totalRotos = $stmt->fetchColumn();

    $totalRobados = 0; 

    // 2. Distribución del Inventario
    $stmt = $database->query("
        SELECT em.desc_estado_material, COUNT(*) as cantidad 
        FROM inventario i 
        JOIN estado_material em ON i.id_estado_material = em.id_estado_material 
        GROUP BY em.desc_estado_material
    ");
    $distribucionInventario = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $totalInventario = array_sum($distribucionInventario) ?: 1;

    // 3. Actividad Reciente
    $stmt = $database->query("SELECT * FROM vista_prestamos_detalle ORDER BY id_prestamo DESC LIMIT 5");
    $actividadReciente = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Estadística Anual
    $stmt = $database->query("
        SELECT MONTH(fecha_hora_registro) as mes, COUNT(*) as total 
        FROM prestamos 
        WHERE YEAR(fecha_hora_registro) = YEAR(CURDATE()) 
        GROUP BY MONTH(fecha_hora_registro)
    ");
    $prestamosAnualesRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $mesesAnio = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
    ];
    
    $datosGrafico = [];
    $maxValorAnual = 1;
    foreach ($mesesAnio as $numMes => $nombreMes) {
        $cantidad = $prestamosAnualesRaw[$numMes] ?? 0;
        $datosGrafico[$nombreMes] = $cantidad;
        if ($cantidad > $maxValorAnual) {
            $maxValorAnual = $cantidad;
        }
    }

} catch (Exception $e) {
    $totalPrestamosActivos = 0;
    $prestadosHoy = 0;
    $totalRotos = 0;
    $totalRobados = 0;
    $distribucionInventario = [];
    $totalInventario = 1;
    $actividadReciente = [];
    $datosGrafico = array_fill_keys(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'], 0);
    $maxValorAnual = 1;
}

// ================================
// Encabezado del sitio
// ================================
include __DIR__ . '/includes/layout_header.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel General - Biblioteca</title>
  <style>
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .stat-card {
      background: #fff;
      padding: 1.25rem;
      border-radius: 0.5rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      border-left: 4px solid #0d6efd;
    }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.danger { border-left-color: #dc3545; }
    .stat-card.success { border-left-color: #198754; }
    .stat-number {
      font-size: 1.75rem;
      font-weight: 700;
      margin-top: 0.25rem;
    }

    .chart-container-custom {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      height: 220px;
      padding: 1rem 0 0 0;
      border-bottom: 2px solid #dee2e6;
      gap: 10px;
    }
    .chart-bar-col {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100%;
      justify-content: flex-end;
    }
    .chart-bar {
      width: 100%;
      max-width: 40px;
      background-color: #0d6efd;
      border-radius: 4px 4px 0 0;
      transition: height 0.4s ease;
      position: relative;
    }
    .chart-bar:hover {
      background-color: #0b5ed7;
    }
    .chart-bar-value {
      position: absolute;
      top: -22px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 0.75rem;
      font-weight: bold;
      color: #495057;
    }
    .chart-label {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: 0.5rem;
      text-align: center;
    }
  </style>
</head>

<body>

  <div class="view">

    <!-- Título del panel y Acciones Rápidas -->
    <div class="titulo-pagina mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Panel General</h1>
            <p class="text-muted mb-0">Información general del estado de la biblioteca.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/biblioteca-sql/prestamos/" class="btn btn-outline-primary btn-sm fw-medium shadow-sm">
                <i class="bi bi-journal-text me-1"></i> Ver todos los Préstamos
            </a>
            <a href="/biblioteca-sql/inventario/" class="btn btn-outline-secondary btn-sm fw-medium shadow-sm">
                <i class="bi bi-box-seam me-1"></i> Ver Inventario Completo
            </a>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
      <div class="stat-card success">
        <span class="text-muted small fw-semibold">Préstamos Activos</span>
        <div class="stat-number"><?= $totalPrestamosActivos ?></div>
      </div>

      <div class="stat-card">
        <span class="text-muted small fw-semibold">Prestados Hoy</span>
        <div class="stat-number"><?= $prestadosHoy ?></div>
      </div>

      <div class="stat-card warning">
        <span class="text-muted small fw-semibold">Materiales Rotos</span>
        <div class="stat-number"><?= $totalRotos ?></div>
      </div>

      <div class="stat-card danger">
        <span class="text-muted small fw-semibold">Robados / Perdidos</span>
        <div class="stat-number"><?= $totalRobados ?></div>
      </div>
    </div>

    <!-- Gráfico anual -->
    <div class="card card-lg mb-4 p-4 shadow-sm border-0">
      <div class="chart-head mb-3">
        <div>
          <h2>Estadística anual</h2>
          <p class="text-muted mb-0">
            Cantidad de préstamos registrados mes a mes durante el año actual (<?= date('Y') ?>).
          </p>
        </div>
      </div>

      <div class="chart-container-custom">
        <?php foreach ($datosGrafico as $mes => $cantidad): 
          $alturaPorcentaje = ($maxValorAnual > 0) ? ($cantidad / $maxValorAnual) * 100 : 0;
          $alturaCss = ($cantidad > 0) ? max($alturaPorcentaje, 8) : 4;
        ?>
          <div class="chart-bar-col">
            <div class="chart-bar" style="height: <?= $alturaCss ?>%;">
              <?php if ($cantidad > 0): ?>
                <span class="chart-bar-value"><?= $cantidad ?></span>
              <?php endif; ?>
            </div>
            <span class="chart-label"><?= $mes ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="d-flex justify-content-end align-items-center gap-3 mt-3 text-muted small">
        <div class="d-flex align-items-center gap-1">
          <span style="display:inline-block; width:12px; height:12px; background-color:#0d6efd; border-radius:2px;"></span>
          <span>Préstamos mensuales</span>
        </div>
      </div>
    </div>

    <!-- Distribución del inventario y Actividad reciente -->
    <section class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">

      <!-- Distribución del Inventario -->
      <div class="card p-4 shadow-sm border-0 d-flex flex-column justify-content-between">
        <div>
          <h2 class="h5 mb-3">Distribución del inventario</h2>
          
          <div class="progress mb-3" style="height: 25px;">
            <?php 
            $buenEstado = $distribucionInventario['buen estado'] ?? 0;
            $roto       = $distribucionInventario['roto'] ?? 0;
            $nuevo      = $distribucionInventario['Nuevo'] ?? 0;

            $pBuen  = ($buenEstado / $totalInventario) * 100;
            $pRoto  = ($roto / $totalInventario) * 100;
            $pNuevo = ($nuevo / $totalInventario) * 100;
            ?>
            <div class="progress-bar bg-success" style="width: <?= $pBuen ?>%" title="Buen estado: <?= $buenEstado ?>">Buen estado</div>
            <div class="progress-bar bg-warning text-dark" style="width: <?= $pRoto ?>%" title="Rotos: <?= $roto ?>">Roto</div>
            <div class="progress-bar bg-info text-dark" style="width: <?= $pNuevo ?>%" title="Nuevos: <?= $nuevo ?>">Nuevo</div>
          </div>

          <ul class="list-unstyled small text-muted mb-4">
            <li>🟢 Buen estado: <strong><?= $buenEstado ?></strong></li>
            <li>🟡 Rotos: <strong><?= $roto ?></strong></li>
            <li>🔵 Nuevos: <strong><?= $nuevo ?></strong></li>
          </ul>
        </div>

        <div>
          <a href="/biblioteca-sql/inventario/" class="btn btn-light btn-sm w-100 text-dark border fw-medium">
            <i class="bi bi-eye me-1"></i> Ver detalles de inventario
          </a>
        </div>
      </div>

      <!-- Actividad reciente -->
      <div class="card p-4 shadow-sm border-0 d-flex flex-column justify-content-between">
        <div>
          <h2 class="h5 mb-3">Actividad reciente</h2>
          
          <?php if (!empty($actividadReciente)): ?>
            <ul class="list-group list-group-flush mb-3">
              <?php foreach ($actividadReciente as $mov): ?>
                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                  <div>
                    <i class="bi bi-journal-arrow-up text-primary me-2"></i>
                    <span>Préstamo de <strong><?= htmlspecialchars($mov['nombre_material'] ?? 'Material') ?></strong> a <strong><?= htmlspecialchars($mov['apellido_usuario'] . ', ' . $mov['nombre_usuario']) ?></strong></span>
                  </div>
                  <small class="text-muted"><?= date('d/m H:i', strtotime($mov['fecha_hora_registro'])) ?></small>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted small">No hay movimientos recientes registrados.</p>
          <?php endif; ?>
        </div>

        <div>
          <a href="/biblioteca-sql/prestamos/" class="btn btn-light btn-sm w-100 text-dark border fw-medium">
            <i class="bi bi-list-ul me-1"></i> Ver listado completo de préstamos
          </a>
        </div>
      </div>

    </section>

  </div>

  <?php
  include __DIR__ . '/includes/layout_footer.php';
  ?>

</body>

</html>