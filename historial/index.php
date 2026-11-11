<?php
// ================================
// HISTORIAL COMPLETO (Préstamos, Bajas, Modificaciones, Pérdidos y Observaciones)
// ================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();

// ================================
// Filtros recibidos por GET
// ================================
$filtroTexto   = trim($_GET['q']     ?? '');
$filtroTipo    = trim($_GET['tipo']    ?? ''); // prestamo, baja, perdido, modificacion, observacion
$filtroDesde   = trim($_GET['desde']    ?? '');
$filtroHasta   = trim($_GET['hasta']    ?? '');
$pagina        = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina     = 20;
$offset        = ($pagina - 1) * $porPagina;

try {
    $db = db();

    // =========================================================================
    // Construcción de consultas unificadas (UNION ALL) para un historial global
    // =========================================================================
    
    // Partes comunes de filtrado de texto por SQL para cada bloque
    $params = [];
    
    // 1. Préstamos
    $sqlPrestamos = "SELECT 
                        CONCAT('prestamo_', p.id_prestamo) AS id_unico,
                        'Préstamo' AS tipo_evento,
                        'primary' AS badge_tipo,
                        p.fecha_hora_registro AS fecha_hora,
                        p.id_prestamo AS referencia_id,
                        CONCAT('Préstamo de material: ', COALESCE(l.nombre, im.tipo, md.tipo, mg.tipo, mdt.tipo, 'Material'), ' (ID: ', p.id_material, ')') AS descripcion,
                        CONCAT(u.apellido, ', ', u.nombre) AS usuario_responsable,
                        p.id_prestamo
                       FROM prestamos p
                       LEFT JOIN usuario u ON u.dni_usuario = p.dni_usuario
                       LEFT JOIN inventario i ON i.id_material = p.id_material
                       LEFT JOIN libros l ON l.id_material = i.id_material
                       LEFT JOIN instrumentos_medicion im ON im.id_material = i.id_material
                       LEFT JOIN materiales_didacticos md ON md.id_material = i.id_material
                       LEFT JOIN materiales_geograficos mg ON mg.id_material = i.id_material
                       LEFT JOIN materiales_dibujo_tecnico mdt ON mdt.id_material = i.id_material";

    // 2. Observaciones
    $sqlObservaciones = "SELECT 
                            CONCAT('obs_', ho.id_observacion) AS id_unico,
                            'Observación' AS tipo_evento,
                            'info' AS badge_tipo,
                            ho.fecha_hora,
                            ho.id_prestamo AS referencia_id,
                            CONCAT('Observación en Préstamo #', ho.id_prestamo, ': ', ho.observacion) AS descripcion,
                            CONCAT(u.apellido, ', ', u.nombre) AS usuario_responsable,
                            ho.id_prestamo
                           FROM historial_observaciones ho
                           LEFT JOIN usuario u ON u.dni_usuario = ho.dni_usuario";

    // Unimos los eventos principales disponibles
    $sqlUnificado = "SELECT * FROM ($sqlPrestamos UNION ALL $sqlObservaciones) AS historial_global WHERE 1=1";

    // Aplicar filtros dinámicos sobre la vista unificada
    $whereClause = "";
    if ($filtroTexto !== '') {
        $whereClause .= " AND (descripcion LIKE :q OR usuario_responsable LIKE :q OR referencia_id LIKE :q)";
        $params[':q'] = "%$filtroTexto%";
    }
    if ($filtroTipo !== '') {
        $whereClause .= " AND tipo_evento = :tipo";
        $params[':tipo'] = $filtroTipo;
    }
    if ($filtroDesde !== '') {
        $whereClause .= " AND DATE(fecha_hora) >= :desde";
        $params[':desde'] = $filtroDesde;
    }
    if ($filtroHasta !== '') {
        $whereClause .= " AND DATE(fecha_hora) <= :hasta";
        $params[':hasta'] = $filtroHasta;
    }

    // Conteo total para paginación
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM ($sqlUnificado) AS t WHERE 1=1 " . $whereClause);
    $stmtCount->execute($params);
    $totalRegistros = (int)$stmtCount->fetchColumn();
    $totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));

    // Consulta final con orden y límites
    $sqlFinal = "SELECT * FROM ($sqlUnificado) AS t WHERE 1=1 " . $whereClause . " ORDER BY fecha_hora DESC LIMIT $porPagina OFFSET $offset";
    $stmt = $db->prepare($sqlFinal);
    $stmt->execute($params);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $registros      = [];
    $totalRegistros = 0;
    $totalPaginas   = 1;
}

// Layout header
include __DIR__ . '/../includes/layout_header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial Completo - Biblioteca</title>
<style>
/* Estilos coherentes con el diseño original */
.modal-obs-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; justify-content: center; align-items: center; }
.modal-obs-overlay.activo { display: flex; }
.modal-obs-box { background: #fff; border-radius: 12px; padding: 25px; width: 90%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; }
.modal-obs-box h3 { margin: 0 0 15px 0; font-size: 1.1rem; color: #333; }
.modal-obs-box textarea { width: 100%; min-height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; resize: vertical; box-sizing: border-box; }
.modal-obs-box textarea:focus { outline: none; border-color: #eb2525; box-shadow: 0 0 0 3px rgba(235,37,37,0.12); }
.modal-obs-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; }
.btn-obs-cancel { padding: 8px 16px; border: 1px solid #ddd; background: #fff; border-radius: 8px; cursor: pointer; font-size: 14px; }
.btn-obs-cancel:hover { background: #f5f5f5; }
.btn-obs-save { padding: 8px 16px; border: none; background: #eb2525; color: #fff; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
.btn-obs-save:hover { background: #d81d1d; }
.btn-obs-close { position: absolute; top: 10px; right: 12px; background: none; border: none; font-size: 22px; cursor: pointer; color: #777; }
.btn-obs-close:hover { color: #d71919; }

.filtros-historial { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end; }
@media (max-width: 1024px) { .filtros-historial { grid-template-columns: 1fr 1fr; } }
@media (max-width: 600px) { .filtros-historial { grid-template-columns: 1fr; } }

.filtro-grupo { display: flex; flex-direction: column; gap: 0.375rem; }
.filtro-grupo label { font-size: 0.75rem; font-weight: 600; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: 0.03em; display: flex; align-items: center; gap: 0.375rem; }
.filtro-grupo label i { font-size: 0.85rem; color: var(--primary); }
.filtro-grupo input, .filtro-grupo select { height: 2.5rem; padding: 0 0.75rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--card); color: var(--foreground); font-size: 0.875rem; font-family: inherit; }
.filtro-grupo input:focus, .filtro-grupo select:focus { outline: none; border-color: var(--ring); box-shadow: 0 0 0 3px color-mix(in oklch, var(--ring) 20%, transparent); }

.filtro-acciones { display: flex; gap: 0.5rem; align-items: end; height: 2.5rem; }
.btn-filtrar, .btn-limpiar { display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem; height: 2.5rem; padding: 0 1rem; border-radius: 0.5rem; border: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; text-decoration: none; white-space: nowrap; }
.btn-filtrar { background: var(--primary); color: var(--primary-foreground); }
.btn-filtrar:hover { background: color-mix(in oklch, var(--primary) 85%, transparent); }
.btn-limpiar { background: var(--muted); color: var(--muted-foreground); border: 1px solid var(--border); }
.btn-limpiar:hover { background: var(--background); color: var(--destructive); border-color: var(--destructive); }
</style>
</head>
<body>
<div class="view">

    <!-- Encabezado -->
    <div class="titulo-pagina mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Historial Completo del Sistema</h1>
            <p class="text-muted mb-0">
                Registro unificado de préstamos, modificaciones, observaciones y eventos generales.
                <span class="badge bg-secondary">Solo lectura</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/biblioteca-sql/panel.php" class="btn btn-outline-secondary btn-sm fw-medium shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card p-4 shadow-sm border-0 mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-funnel-fill" style="font-size: 1.1rem; color: var(--primary);"></i>
            <h2 class="h6 mb-0 fw-semibold">Filtros de búsqueda avanzada</h2>
        </div>
        <form method="GET" action="" class="filtros-historial">
            <div class="filtro-grupo">
                <label for="filtro-q"><i class="bi bi-search"></i> Buscar</label>
                <input type="text" id="filtro-q" name="q" placeholder="Descripción, usuario, ID..." value="<?= htmlspecialchars($filtroTexto) ?>">
            </div>
            <div class="filtro-grupo">
                <label for="filtro-tipo"><i class="bi bi-tag"></i> Tipo de Evento</label>
                <select id="filtro-tipo" name="tipo">
                    <option value="">Todos los eventos</option>
                    <option value="Préstamo" <?= $filtroTipo==='Préstamo'?'selected':'' ?>>Préstamo</option>
                    <option value="Observación" <?= $filtroTipo==='Observación'?'selected':'' ?>>Observación</option>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="filtro-desde"><i class="bi bi-calendar-event"></i> Desde</label>
                <input type="date" id="filtro-desde" name="desde" value="<?= htmlspecialchars($filtroDesde) ?>">
            </div>
            <div class="filtro-grupo">
                <label for="filtro-hasta"><i class="bi bi-calendar-check"></i> Hasta</label>
                <input type="date" id="filtro-hasta" name="hasta" value="<?= htmlspecialchars($filtroHasta) ?>">
            </div>
            <div class="filtro-acciones">
                <button type="submit" class="btn-filtrar">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="/biblioteca-sql/historial/" class="btn-limpiar">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Resultado Tabla -->
    <div class="card p-0 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <strong>
                <?= $totalRegistros ?> registro<?= $totalRegistros===1?'':'s' ?> encontrado<?= $totalRegistros===1?'':'s' ?>
            </strong>
            <small class="text-muted">Página <?= $pagina ?> de <?= $totalPaginas ?></small>
        </div>

        <div class="tabla-responsive">
            <table class="tabla-prestamos w-100">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Tipo</th>
                        <th>Descripción del Evento</th>
                        <th>Responsable / Usuario</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="5" class="cell-empty text-center py-4">No hay registros que coincidan con los filtros.</td></tr>
                <?php else: ?>
                    <?php foreach ($registros as $row): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i:s', strtotime($row['fecha_hora'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['badge_tipo'] ?? 'secondary' ?>">
                                <?= htmlspecialchars($row['tipo_evento']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                        <td><?= htmlspecialchars($row['usuario_responsable'] ?? 'Sistema') ?></td>
                        <td class="text-right">
                            <?php if (!empty($row['referencia_id']) && $row['tipo_evento'] === 'Préstamo'): ?>
                                <a href="detalle.php?id=<?= (int)$row['referencia_id'] ?>" class="btn-ver btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Préstamo
                                </a>
                            <?php elseif (!empty($row['referencia_id']) && $row['tipo_evento'] === 'Observación'): ?>
                                <a href="detalle.php?id=<?= (int)$row['referencia_id'] ?>" class="btn-ver btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Préstamo Relacionado
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div class="d-flex justify-content-center align-items-center gap-2 p-3 border-top">
            <?php
            $qs = $_GET;
            $buildUrl = function($pag) use ($qs) {
                $qs['pagina'] = $pag;
                return '?' . http_build_query($qs);
            };
            ?>
            <?php if ($pagina > 1): ?>
                <a href="<?= $buildUrl(1) ?>" class="btn btn-outline-secondary btn-sm">« Primera</a>
                <a href="<?= $buildUrl($pagina - 1) ?>" class="btn btn-outline-secondary btn-sm">‹ Anterior</a>
            <?php endif; ?>
            <span class="text-muted small">Página <?= $pagina ?> / <?= $totalPaginas ?></span>
            <?php if ($pagina < $totalPaginas): ?>
                <a href="<?= $buildUrl($pagina + 1) ?>" class="btn btn-outline-secondary btn-sm">Siguiente ›</a>
                <a href="<?= $buildUrl($totalPaginas) ?>" class="btn btn-outline-secondary btn-sm">Última »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
</body>
</html>