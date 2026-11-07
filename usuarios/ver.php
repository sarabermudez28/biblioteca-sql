<?php
require_once 'seguridad.php';

$dni = $_GET['dni'] ?? '';

$stmt = $pdo->prepare("
    SELECT u.*, r.desc_rol
    FROM usuario u
    INNER JOIN rol r ON u.id_rol = r.id_rol
    WHERE u.dni_usuario = ?
    AND u.id_rol IN (3, 4)
");

$stmt->execute([$dni]);
$socio = $stmt->fetch();

if (!$socio) {
    header('Location: index.php?mensaje=' . urlencode('Socio no encontrado.'));
    exit;
}

// Historial de préstamos.
$stmt = $pdo->prepare("
    SELECT
        p.id_prestamo,
        p.id_material,
        p.fecha_hora_registro,
        p.fecha_hora_devolucion,
        p.fecha_hora_limite_prestamo,
        e.desc_estado_prestamo
    FROM prestamos p
    LEFT JOIN estado_prestamo e
        ON p.id_estado_prestamo = e.id_estado_prestamo
    WHERE p.dni_usuario = ?
    ORDER BY p.fecha_hora_registro DESC
");

$stmt->execute([$dni]);
$prestamos = $stmt->fetchAll();

// Historial de puntos.
$stmt = $pdo->prepare("
    SELECT puntos_cambio, motivo, fecha_hora, id_prestamo
    FROM historial_puntos
    WHERE dni_usuario = ?
    ORDER BY fecha_hora DESC, id DESC
");

$stmt->execute([$dni]);
$historial_puntos = $stmt->fetchAll();
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Información del socio</h2>
            <p class="text-muted">Datos personales y actividad en la biblioteca.</p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Volver
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <strong>Nombre y apellido:</strong><br>
                    <?= htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>DNI:</strong><br>
                    <?= htmlspecialchars($socio['dni_usuario']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Correo:</strong><br>
                    <?= htmlspecialchars($socio['gmail']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Tipo de socio:</strong><br>
                    <?= htmlspecialchars($socio['desc_rol']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Estado:</strong><br>
                    <?= $socio['activo']
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-secondary">Inactivo</span>' ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Puntaje actual:</strong><br>
                    <span class="badge bg-primary fs-6">
                        <?= (int)$socio['puntos'] ?> puntos
                    </span>
                </div>

            </div>

            <?php if ($es_admin && $socio['activo']): ?>
                <a href="puntos.php?dni=<?= urlencode($dni) ?>"
                   class="btn btn-warning">
                    <i class="fa-solid fa-star"></i> Descontar puntos
                </a>
            <?php endif; ?>

        </div>
    </div>

    <h4 class="mb-3">Historial de préstamos</h4>

    <div class="card shadow-sm mb-4">
        <div class="card-body table-responsive">

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>N.º préstamo</th>
                        <th>Material</th>
                        <th>Fecha de registro</th>
                        <th>Fecha límite</th>
                        <th>Devolución</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($prestamos): ?>
                        <?php foreach ($prestamos as $p): ?>
                            <tr>
                                <td><?= (int)$p['id_prestamo'] ?></td>
                                <td><?= htmlspecialchars($p['id_material']) ?></td>
                                <td><?= htmlspecialchars($p['fecha_hora_registro']) ?></td>
                                <td><?= htmlspecialchars($p['fecha_hora_limite_prestamo']) ?></td>
                                <td>
                                    <?= $p['fecha_hora_devolucion']
                                        ? htmlspecialchars($p['fecha_hora_devolucion'])
                                        : 'Pendiente' ?>
                                </td>
                                <td><?= htmlspecialchars($p['desc_estado_prestamo'] ?? 'Sin estado') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Este socio todavía no tiene préstamos registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

    <h4 class="mb-3">Historial de puntos</h4>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cambio</th>
                        <th>Motivo</th>
                        <th>Préstamo relacionado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($historial_puntos): ?>
                        <?php foreach ($historial_puntos as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['fecha_hora']) ?></td>

                                <td>
                                    <?php if ($h['puntos_cambio'] > 0): ?>
                                        <span class="text-success">
                                            +<?= (int)$h['puntos_cambio'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-danger">
                                            <?= (int)$h['puntos_cambio'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($h['motivo']) ?></td>

                                <td>
                                    <?= $h['id_prestamo'] !== null
                                        ? (int)$h['id_prestamo']
                                        : 'No corresponde' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No hay movimientos de puntos registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>