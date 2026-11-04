<?php
// ================================
// HISTORIAL COMPLETO — Solo lectura + observaciones inline
// ================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();

// ================================
// Filtros recibidos por GET
// ================================
$filtroTexto   = trim($_GET['q']         ?? '');
$filtroEstado  = trim($_GET['estado']    ?? '');
$filtroDesde   = trim($_GET['desde']     ?? '');
$filtroHasta   = trim($_GET['hasta']     ?? '');
$pagina        = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina     = 20;
$offset        = ($pagina - 1) * $porPagina;

// ================================
// Construcción de la consulta
// ================================
$where  = [];
$params = [];

if ($filtroTexto !== '') {
    $where[] = "(p.id_material LIKE :q OR u.nombre LIKE :q OR u.apellido LIKE :q
                 OR CONCAT(u.nombre,' ',u.apellido) LIKE :q)";
    $params[':q'] = "%$filtroTexto%";
}
if ($filtroEstado !== '') {
    $where[] = "p.id_estado_prestamo = :estado";
    $params[':estado'] = (int)$filtroEstado;
}
if ($filtroDesde !== '') {
    $where[] = "DATE(p.fecha_hora_registro) >= :desde";
    $params[':desde'] = $filtroDesde;
}
if ($filtroHasta !== '') {
    $where[] = "DATE(p.fecha_hora_registro) <= :hasta";
    $params[':hasta'] = $filtroHasta;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $db = db();

    // Total de registros
    $sqlCount = "SELECT COUNT(*) FROM prestamos p
                 LEFT JOIN usuario u ON u.dni_usuario = p.dni_usuario
                 $whereSQL";
    $stmt = $db->prepare($sqlCount);
    $stmt->execute($params);
    $totalRegistros = (int)$stmt->fetchColumn();
    $totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));

    // Consulta principal con última observación
    $sql = "SELECT
                p.id_prestamo,
                p.id_material,
                p.fecha_hora_registro,
                p.fecha_hora_devolucion,
                p.fecha_hora_limite_prestamo,
                p.id_estado_prestamo,
                u.nombre    AS nombre_usuario,
                u.apellido  AS apellido_usuario,
                c.nombre    AS categoria,
                COALESCE(l.nombre, im.tipo, md.tipo, mg.tipo, mdt.tipo, 'Material') AS nombre_material,
                em.desc_estado_material AS estado_material,
                (SELECT COUNT(*) FROM historial_observaciones ho WHERE ho.id_prestamo = p.id_prestamo) AS cant_observaciones,
                (SELECT CONCAT(u2.apellido, ', ', u2.nombre)
                   FROM historial_observaciones ho2
                   LEFT JOIN usuario u2 ON u2.dni_usuario = ho2.dni_usuario
                  WHERE ho2.id_prestamo = p.id_prestamo
                  ORDER BY ho2.fecha_hora DESC LIMIT 1) AS ultimo_obs_autor,
                (SELECT ho2.fecha_hora
                   FROM historial_observaciones ho2
                  WHERE ho2.id_prestamo = p.id_prestamo
                  ORDER BY ho2.fecha_hora DESC LIMIT 1) AS ultimo_obs_fecha
            FROM prestamos p
            LEFT JOIN usuario u        ON u.dni_usuario    = p.dni_usuario
            LEFT JOIN inventario i     ON i.id_material    = p.id_material
            LEFT JOIN categorias c     ON c.id_categoria   = i.id_categoria
            LEFT JOIN estado_material em ON em.id_estado_material = i.id_estado_material
            LEFT JOIN libros l         ON l.id_material    = i.id_material
            LEFT JOIN instrumentos_medicion im ON im.id_material = i.id_material
            LEFT JOIN materiales_didacticos md ON md.id_material = i.id_material
            LEFT JOIN materiales_geograficos mg  ON mg.id_material  = i.id_material
            LEFT JOIN materiales_dibujo_tecnico mdt ON mdt.id_material = i.id_material
            $whereSQL
            ORDER BY p.fecha_hora_registro DESC, p.id_prestamo DESC
            LIMIT $porPagina OFFSET $offset";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $prestamos      = [];
    $totalRegistros = 0;
    $totalPaginas   = 1;
}

// ================================
// Layout
// ================================
include __DIR__ . '/../includes/layout_header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial Completo - Biblioteca</title>
<style>
/* Modal de observación */
.modal-obs-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1050;
    justify-content: center;
    align-items: center;
}
.modal-obs-overlay.activo {
    display: flex;
}
.modal-obs-box {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
}
.modal-obs-box h3 {
    margin: 0 0 15px 0;
    font-size: 1.1rem;
    color: #333;
}
.modal-obs-box textarea {
    width: 100%;
    min-height: 100px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    box-sizing: border-box;
}
.modal-obs-box textarea:focus {
    outline: none;
    border-color: #eb2525;
    box-shadow: 0 0 0 3px rgba(235,37,37,0.12);
}
.modal-obs-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}
.btn-obs-cancel {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
}
.btn-obs-cancel:hover { background: #f5f5f5; }
.btn-obs-save {
    padding: 8px 16px;
    border: none;
    background: #eb2525;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}
.btn-obs-save:hover { background: #d81d1d; }
.btn-obs-close {
    position: absolute;
    top: 10px;
    right: 12px;
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #777;
}
.btn-obs-close:hover { color: #d71919; }
.ultima-obs {
    font-size: 11px;
    color: #666;
    margin-top: 3px;
}
.ultima-obs strong { color: #333; }
.btn-obs-row {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    background: #6c757d;
    color: white;
    cursor: pointer;
    font-size: 12px;
    margin-right: 4px;
}
.btn-obs-row:hover { background: #5a6268; }
/* ============================================================
   FILTROS HISTORIAL — Diseño mejorado
   ============================================================ */
.filtros-historial {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

@media (max-width: 1024px) {
    .filtros-historial {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 600px) {
    .filtros-historial {
        grid-template-columns: 1fr;
    }
}

.filtro-grupo {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.filtro-grupo label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--muted-foreground);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.filtro-grupo label i {
    font-size: 0.85rem;
    color: var(--primary);
}

.filtro-grupo input,
.filtro-grupo select {
    height: 2.5rem;
    padding: 0 0.75rem;
    border: 1px solid var(--border);
    border-radius: 0.5rem;
    background: var(--card);
    color: var(--foreground);
    font-size: 0.875rem;
    font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.filtro-grupo input:focus,
.filtro-grupo select:focus {
    outline: none;
    border-color: var(--ring);
    box-shadow: 0 0 0 3px color-mix(in oklch, var(--ring) 20%, transparent);
}

.filtro-grupo input::placeholder {
    color: var(--muted-foreground);
}

.filtro-acciones {
    display: flex;
    gap: 0.5rem;
    align-items: end;
    height: 2.5rem;
}

.btn-filtrar,
.btn-limpiar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    height: 2.5rem;
    padding: 0 1rem;
    border-radius: 0.5rem;
    border: none;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    text-decoration: none;
}

.btn-filtrar {
    background: var(--primary);
    color: var(--primary-foreground);
}

.btn-filtrar:hover {
    background: color-mix(in oklch, var(--primary) 85%, transparent);
    transform: translateY(-1px);
    box-shadow: 0 2px 6px color-mix(in oklch, var(--primary) 30%, transparent);
}

.btn-limpiar {
    background: var(--muted);
    color: var(--muted-foreground);
    border: 1px solid var(--border);
}

.btn-limpiar:hover {
    background: var(--background);
    color: var(--destructive);
    border-color: var(--destructive);
}
</style>
</head>
<body>
<div class="view">

    <!-- Encabezado -->
    <div class="titulo-pagina mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Historial Completo</h1>
            <p class="text-muted mb-0">
                Registro histórico de préstamos.
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
        <h2 class="h6 mb-0 fw-semibold">Filtros de búsqueda</h2>
    </div>
    <form method="GET" action="" class="filtros-historial">
        <div class="filtro-grupo">
            <label for="filtro-q"><i class="bi bi-search"></i> Buscar</label>
            <input type="text" id="filtro-q" name="q" placeholder="Material, usuario, DNI..." value="<?= htmlspecialchars($filtroTexto) ?>">
        </div>
        <div class="filtro-grupo">
            <label for="filtro-estado"><i class="bi bi-tag"></i> Estado</label>
            <select id="filtro-estado" name="estado">
                <option value="">Todos</option>
                <option value="1" <?= $filtroEstado==='1'?'selected':'' ?>>✅ Devuelto</option>
                <option value="2" <?= $filtroEstado==='2'?'selected':'' ?>>⏳ No devuelto</option>
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

    <!-- Resultado -->
    <div class="card p-0 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <strong>
                <?= $totalRegistros ?> registro<?= $totalRegistros===1?'':'s' ?> encontrado<?= $totalRegistros===1?'':'s' ?>
            </strong>
            <small class="text-muted">Página <?= $pagina ?> de <?= $totalPaginas ?></small>
        </div>

        <div class="tabla-responsive">
            <table class="tabla-prestamos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material</th>
                        <th>Usuario</th>
                        <th>Fecha préstamo</th>
                        <th>Devolución</th>
                        <th>Estado</th>
                        <th>Última observación</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($prestamos)): ?>
                    <tr><td colspan="8" class="cell-empty">No hay registros que coincidan con los filtros.</td></tr>
                <?php else: ?>
                    <?php foreach ($prestamos as $p):
                        $devuelto = ((int)$p['id_estado_prestamo'] === 1);
                        $obsAutor = $p['ultimo_obs_autor'] ?? '';
                        $obsFecha = $p['ultimo_obs_fecha'] ?? '';
                        $obsResumen = '';
                        if ($obsAutor && $obsFecha) {
                            $obsResumen = '<strong>' . htmlspecialchars($obsAutor) . '</strong> · ' . date('d/m/Y H:i', strtotime($obsFecha));
                        }
                    ?>
                    <tr>
                        <td><strong>#<?= (int)$p['id_prestamo'] ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($p['nombre_material']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($p['id_material']) ?> · <?= htmlspecialchars($p['categoria']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['apellido_usuario'] . ', ' . $p['nombre_usuario']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($p['fecha_hora_registro'])) ?></td>
                        <td>
                            <?php if ($p['fecha_hora_devolucion']): ?>
                                <?= date('d/m/Y H:i', strtotime($p['fecha_hora_devolucion'])) ?>
                            <?php else: ?>
                                <span class="sin-devolver">Sin devolver</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="estado-<?= $devuelto ? 'devuelto' : 'prestado' ?>">
                                <?= $devuelto ? 'Devuelto' : 'Prestado' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($obsResumen): ?>
                                <div class="ultima-obs"><?= $obsResumen ?></div>
                                <small class="text-muted">(<?= (int)$p['cant_observaciones'] ?> en total)</small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn-obs-row" onclick="abrirModalObs(<?= (int)$p['id_prestamo'] ?>, '<?= htmlspecialchars(addslashes($p['nombre_material']), ENT_QUOTES) ?>')">
                                <i class="bi bi-plus-circle"></i> Observación
                            </button>
                            <a href="detalle.php?id=<?= (int)$p['id_prestamo'] ?>" class="btn-ver">
                                <i class="bi bi-eye"></i> Ver
                            </a>
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

<!-- Modal de observación -->
<div class="modal-obs-overlay" id="modalObs">
    <div class="modal-obs-box">
        <button type="button" class="btn-obs-close" onclick="cerrarModalObs()">×</button>
        <h3>Agregar observación</h3>
        <p class="text-muted small" id="modalObsTitulo">Préstamo #—</p>
        <form method="POST" action="agregar_observacion.php" id="formObs">
            <input type="hidden" name="id_prestamo" id="modalObsId" value="">
            <input type="hidden" name="volver" value="listado">
            <textarea name="observacion" id="modalObsTexto" required maxlength="1000"
                placeholder="Escriba la observación sobre este préstamo..."></textarea>
            <div class="modal-obs-actions">
                <button type="button" class="btn-obs-cancel" onclick="cerrarModalObs()">Cancelar</button>
                <button type="submit" class="btn-obs-save">Guardar observación</button>
            </div>
            <small class="text-muted d-block mt-2">
                La observación quedará registrada con su usuario y fecha. No podrá editarse ni eliminarse.
            </small>
        </form>
    </div>
</div>

<script>
function abrirModalObs(id, material) {
    document.getElementById('modalObsId').value = id;
    document.getElementById('modalObsTitulo').textContent = 'Préstamo #' + id + ' — ' + material;
    document.getElementById('modalObsTexto').value = '';
    document.getElementById('modalObs').classList.add('activo');
    document.getElementById('modalObsTexto').focus();
}
function cerrarModalObs() {
    document.getElementById('modalObs').classList.remove('activo');
}
// Cerrar al hacer clic fuera del modal
document.getElementById('modalObs').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalObs();
});
// Cerrar con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModalObs();
});
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
</body>
</html>