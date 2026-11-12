
<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

lb_requerir_login();

$tituloPagina = 'Dar de baja usuario';
$paginaActiva = 'usuarios';

$usuarioActual = lb_usuario_actual();

$stmt = $pdo->prepare("
    SELECT id_rol
    FROM usuario
    WHERE gmail = ? AND activo = 1
");

$stmt->execute([$usuarioActual]);
$administrador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$administrador || (int)$administrador['id_rol'] !== 1) {
    header('Location: index.php?error=' . urlencode('No tenés permiso para realizar esta acción.'));
    exit;
}

$dni = trim($_GET['dni'] ?? '');

if ($dni === '') {
    header('Location: index.php');
    exit;
}

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
    header('Location: index.php?error=' . urlencode('Usuario no encontrado o ya inactivo.'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $stmt = $pdo->prepare("
            UPDATE usuario
            SET activo = 0
            WHERE dni_usuario = ?
            AND id_rol IN (3, 4)
            AND activo = 1
        ");

        $stmt->execute([$dni]);

        if ($stmt->rowCount() === 1) {

            header('Location: index.php?mensaje=' . urlencode('El usuario fue dado de baja correctamente.'));
            exit;

        }

        $error = 'No se pudo realizar la baja.';

    } catch (PDOException $e) {

        $error = 'Ocurrió un error al dar de baja al usuario.';

    }
}

?>

<?php require_once __DIR__ . '/../includes/layout_header.php'; ?>

<div class="container-fluid py-4">

    <div class="card shadow-sm">

        <div class="card-body">

            <h2>Dar de baja a un usuario</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="alert alert-warning mt-3">

                ¿Estás seguro de que querés dar de baja a

                <strong>
                    <?= htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']) ?>
                </strong>?

                <br><br>

                El usuario quedará inactivo, pero sus datos,
                puntos e historial se conservarán.

            </div>

            <form method="POST">

                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-user-minus"></i>
                    Confirmar baja
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
