
<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

lb_requerir_login();

$tituloPagina = 'Descontar puntos';
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

    $descuento = filter_input(INPUT_POST, 'descuento', FILTER_VALIDATE_INT);
    $motivo = trim($_POST['motivo'] ?? '');

    if ($descuento === false || $descuento === null || $descuento <= 0) {

        $error = 'Ingresá una cantidad válida de puntos.';

    } elseif ($descuento > (int)$socio['puntos']) {

        $error = 'El descuento no puede superar los puntos actuales.';

    } elseif ($motivo === '') {

        $error = 'Tenés que indicar el motivo del descuento.';

    } else {

        try {

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE usuario
                SET puntos = puntos - ?
                WHERE dni_usuario = ?
                AND id_rol IN (3, 4)
                AND activo = 1
                AND puntos >= ?
            ");

            $stmt->execute([$descuento, $dni, $descuento]);

            if ($stmt->rowCount() !== 1) {
                throw new Exception('No se pudo actualizar el puntaje.');
            }

            // Registrar en el historial
            $stmt = $pdo->prepare("
                INSERT INTO historial_puntos
                (dni_usuario, id_prestamo, puntos_cambio, motivo, fecha_hora)
                VALUES (?, NULL, ?, ?, NOW())
            ");

            $stmt->execute([
                $dni,
                -$descuento,
                $motivo
            ]);

            $pdo->commit();

            header('Location: ver.php?dni=' . urlencode($dni));
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'No se pudo realizar el descuento. Intentá nuevamente.';
        }
    }
}

?>

<?php require_once __DIR__ . '/../includes/layout_header.php'; ?>

<div class="container-fluid py-4">

    <h2>Descontar puntos</h2>

    <p class="text-muted">
        Registrar una penalización al usuario.
    </p>

    <div class="card shadow-sm">
        <div class="card-body">

            <h5>
                <?= htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']) ?>
            </h5>

            <p>DNI: <?= htmlspecialchars($dni) ?></p>

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
                        Cantidad de puntos a descontar
                    </label>

                    <input
                        type="number"
                        name="descuento"
                        class="form-control"
                        min="1"
                        max="<?= (int)$socio['puntos'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Motivo del descuento
                    </label>

                    <textarea
                        name="motivo"
                        class="form-control"
                        rows="3"
                        maxlength="255"
                        placeholder="Ejemplo: Devolución de un libro con páginas dañadas."
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-warning">
                    <i class="fa-solid fa-star"></i>
                    Confirmar descuento
                </button>

                <a href="ver.php?dni=<?= urlencode($dni) ?>"
                   class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
