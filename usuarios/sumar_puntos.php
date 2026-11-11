
<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

lb_requerir_login();

$tituloPagina = 'Sumar puntos';
$paginaActiva = 'usuarios';

$usuarioActual = lb_usuario_actual();

// Verificar administrador
$stmt = $pdo->prepare("
    SELECT id_rol
    FROM usuario
    WHERE gmail = ? AND activo = 1
");
$stmt->execute([$usuarioActual]);
$usuarioSesion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuarioSesion || (int)$usuarioSesion['id_rol'] !== 1) {
    header('Location: index.php?mensaje=' . urlencode('No tenés permiso para realizar esta acción.'));
    exit;
}

$dni = trim($_GET['dni'] ?? '');

if ($dni === '') {
    header('Location: index.php');
    exit;
}

// Buscar usuario
$stmt = $pdo->prepare("
    SELECT *
    FROM usuario
    WHERE dni_usuario = ?
    AND id_rol IN (3, 4)
    AND activo = 1
");

$stmt->execute([$dni]);
$socio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$socio) {
    header('Location: index.php?error=' . urlencode('El usuario no existe o está inactivo.'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
    $motivo = trim($_POST['motivo'] ?? '');

    if ($cantidad === false || $cantidad === null || $cantidad <= 0) {

        $error = 'Ingresá una cantidad válida de puntos.';

    } elseif ($motivo === '') {

        $error = 'Tenés que indicar el motivo del aumento.';

    } else {

        try {

            $pdo->beginTransaction();

            // Sumar puntos
            $stmt = $pdo->prepare("
                UPDATE usuario
                SET puntos = puntos + ?
                WHERE dni_usuario = ?
                AND id_rol IN (3, 4)
                AND activo = 1
            ");

            $stmt->execute([$cantidad, $dni]);

            if ($stmt->rowCount() !== 1) {
                throw new Exception('No se pudo actualizar el puntaje.');
            }

            // Registrar movimiento en el historial
            $stmt = $pdo->prepare("
                INSERT INTO historial_puntos
                (dni_usuario, id_prestamo, puntos_cambio, motivo, fecha_hora)
                VALUES (?, NULL, ?, ?, NOW())
            ");

            $stmt->execute([
                $dni,
                $cantidad,
                $motivo
            ]);

            $pdo->commit();

            header('Location: ver.php?dni=' . urlencode($dni));
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'No se pudieron sumar los puntos. Intentá nuevamente.';
        }
    }
}

?>

<?php require_once __DIR__ . '/../includes/layout_header.php'; ?>

<div class="container-fluid py-4">

    <h2>Sumar puntos</h2>

    <p class="text-muted">
        Registrar una recompensa o actividad reparadora.
    </p>

    <div class="card shadow-sm">

        <div class="card-body">

            <h5>
                <?= htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']) ?>
            </h5>

            <p>
                DNI: <?= htmlspecialchars($dni) ?>
            </p>

            <p>
                Puntaje actual:
                <strong><?= (int)$socio['puntos'] ?> puntos</strong>
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Cantidad de puntos a sumar
                    </label>

                    <input
                        type="number"
                        name="cantidad"
                        class="form-control"
                        min="1"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Motivo del aumento
                    </label>

                    <textarea
                        name="motivo"
                        class="form-control"
                        rows="3"
                        maxlength="255"
                        placeholder="Ejemplo: Actividad reparadora realizada correctamente."
                        required
                    ></textarea>

                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-plus"></i>
                    Confirmar aumento
                </button>

                <a
                    href="ver.php?dni=<?= urlencode($dni) ?>"
                    class="btn btn-secondary"
                >
                    Cancelar
                </a>

            </form>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
