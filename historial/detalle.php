<?php
// ================================
// DETALLE DE PRÉSTAMO (solo lectura + observaciones)
// ================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();

$idPrestamo = (int)($_GET['id'] ?? 0);
if ($idPrestamo <= 0) {
    header('Location: index.php');
    exit;
}

$mensaje  = $_SESSION['hist_msg']  ?? '';
$tipoMsg  = $_SESSION['hist_tipo'] ?? '';
unset($_SESSION['hist_msg'], $_SESSION['hist_tipo']);

try {
    $db = db();

    // Datos del préstamo
    $sql = "SELECT
                p.*,
                u.nombre AS nombre_usuario, u.apellido AS apellido_usuario,
                u.gmail AS email_usuario,
                ub.nombre AS nombre_bibliotecario, ub.apellido AS apellido_bibliotecario,
                c.nombre AS categoria,
                COALESCE(l.nombre, im.tipo, md.tipo, mg.tipo, mdt.tipo, 'Material') AS nombre_material,
                COALESCE(l.autor, md.tematica, mg.region, im.tipo, mdt.descripcion, '-') AS detalle_material,
                em.desc_estado_material AS estado_material
            FROM prestamos p
            LEFT JOIN usuario u  ON u.dni_usuario  = p.dni_usuario
            LEFT JOIN usuario ub ON ub.dni_usuario = p.dni_bibliotecario
            LEFT JOIN inventario i     ON i.id_material    = p.id_material
            LEFT JOIN categorias c     ON c.id_categoria   = i.id_categoria
            LEFT JOIN estado_material em ON em.id_estado_material = i.id_estado_material
            LEFT JOIN libros l         ON l.id_material    = i.id_material
            LEFT JOIN instrumentos_medicion im ON im.id_material = i.id_material
            LEFT JOIN materiales_didacticos md ON md.id_material = i.id_material
            LEFT JOIN materiales_geograficos mg  ON mg.id_material  = i.id_material
            LEFT JOIN materiales_dibujo_tecnico mdt ON mdt.id_material = i.id_material
            WHERE p.id_prestamo = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $idPrestamo]);
    $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prestamo) {
        header('Location: index.php');
        exit;
    }

    // Observaciones
    $stmt = $db->prepare("
        SELECT ho.*, u.nombre, u.apellido
        FROM historial_observaciones ho
        LEFT JOIN usuario u ON u.dni_usuario = ho.dni_usuario
        WHERE ho.id_prestamo = :id
        ORDER BY ho.fecha_hora DESC
    ");
    $stmt->execute([':id' => $idPrestamo]);
    $observaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $prestamo      = null;
    $observaciones = [];
}

$devuelto = $prestamo && ((int)$prestamo['id_estado_prestamo'] === 1);

include __DIR__ . '/../includes/layout_header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle Préstamo #<?= $idPrestamo ?> - Biblioteca</title>
</head>
<body>
<div class="view">

    <!-- Encabezado -->
    <div class="titulo-pagina mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>
                <a href="index.php" class="text-muted text-decoration-none">Historial</a>
                <span class="text-muted">/</span>
                Préstamo #<?= $idPrestamo ?>
            </h1>
            <p class="text-muted mb-0">
                Vista detallada.
                <span class="badge bg-secondary">Solo lectura</span>
                <span class="badge bg-info text-white">Puede agregar observaciones</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm fw-medium shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al historial
            </a>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipoMsg === 'error' ? 'error' : 'success' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if (!$prestamo): ?>
        <div class="alert alert-error">No se encontró el préstamo solicitado.</div>
    <?php else: ?>

    <div class="grid" style="display:grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

        <!-- Información del préstamo -->
        <div class="card p-4 shadow-sm border-0">
            <h2 class="h5 mb-3"><i class="bi bi-journal-text me-2"></i>Información del préstamo</h2>

            <table class="table-custom w-100">
                <tbody>
                    <tr>
                        <th style="width:35%" class="text-muted">ID Préstamo</th>
                        <td><strong>#<?= (int)$prestamo['id_prestamo'] ?></strong></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Material</th>
                        <td>
                            <strong><?= htmlspecialchars($prestamo['nombre_material']) ?></strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($prestamo['id_material']) ?> ·
                                <?= htmlspecialchars($prestamo['categoria']) ?>
                            </small>
                            <div class="mt-1"><small><?= htmlspecialchars($prestamo['detalle_material']) ?></small></div>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Estado del material</th>
                        <td><?= htmlspecialchars($prestamo['estado_material']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Usuario</th>
                        <td>
                            <strong><?= htmlspecialchars($prestamo['apellido_usuario'] . ', ' . $prestamo['nombre_usuario']) ?></strong><br>
                            <small class="text-muted">DNI <?= (int)$prestamo['dni_usuario'] ?> · <?= htmlspecialchars($prestamo['email_usuario']) ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Registrado por</th>
                        <td>
                            <?= htmlspecialchars($prestamo['apellido_bibliotecario'] . ', ' . $prestamo['nombre_bibliotecario']) ?>
                            <br><small class="text-muted">DNI <?= (int)$prestamo['dni_bibliotecario'] ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Fecha y hora de préstamo</th>
                        <td><?= date('d/m/Y H:i:s', strtotime($prestamo['fecha_hora_registro'])) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Fecha límite</th>
                        <td><?= date('d/m/Y H:i:s', strtotime($prestamo['fecha_hora_limite_prestamo'])) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Fecha de devolución</th>
                        <td>
                            <?php if ($prestamo['fecha_hora_devolucion']): ?>
                                <?= date('d/m/Y H:i:s', strtotime($prestamo['fecha_hora_devolucion'])) ?>
                            <?php else: ?>
                                <span class="sin-devolver">Sin devolver</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Estado del préstamo</th>
                        <td>
                            <span class="estado-<?= $devuelto ? 'devuelto' : 'prestado' ?>">
                                <?= $devuelto ? 'Devuelto' : 'Prestado' ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Observaciones -->
        <div class="card p-4 shadow-sm border-0">
            <h2 class="h5 mb-3"><i class="bi bi-chat-left-text me-2"></i>Observaciones</h2>

            <?php if (empty($observaciones)): ?>
                <p class="text-muted small mb-3">Sin observaciones registradas.</p>
            <?php else: ?>
                <div style="max-height: 320px; overflow-y: auto;" class="mb-3">
                    <?php foreach ($observaciones as $obs): ?>
                        <div class="border rounded p-2 mb-2" style="background:#f8f9fa;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="small">
                                    <?= htmlspecialchars($obs['apellido'] . ', ' . $obs['nombre']) ?>
                                </strong>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($obs['fecha_hora'])) ?>
                                </small>
                            </div>
                            <p class="small mb-0" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($obs['observacion'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario para agregar observación -->
            <form method="POST" action="agregar_observacion.php" class="border-top pt-3">
                <input type="hidden" name="id_prestamo" value="<?= (int)$prestamo['id_prestamo'] ?>">
                <label class="small fw-semibold d-block mb-1">Agregar nueva observación</label>
                <textarea name="observacion" rows="4" required maxlength="1000"
                    placeholder="Escriba una observación sobre este préstamo..."
                    class="form-control mb-2" style="resize:vertical; width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;"></textarea>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-1"></i> Agregar observación
                </button>
                <small class="text-muted d-block mt-2">
                    Las observaciones quedan registradas con su usuario y <strong>no pueden editarse ni eliminarse</strong>.
                </small>
            </form>
        </div>

    </div>

    <?php endif; ?>

</div>
<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
</body>
</html>