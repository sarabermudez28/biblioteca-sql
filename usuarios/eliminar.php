<?php
require_once 'seguridad.php';

if (!$es_admin) {
    header('Location: index.php');
    exit;
}

$dni = $_GET['dni'] ?? '';

$stmt = $pdo->prepare("
    SELECT *
    FROM usuario
    WHERE dni_usuario = ?
    AND id_rol IN (3, 4)
    AND activo = 1
");

$stmt->execute([$dni]);
$socio = $stmt->fetch();

if (!$socio) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        UPDATE usuario
        SET activo = 0
        WHERE dni_usuario = ?
        AND id_rol IN (3, 4)
    ");

    $stmt->execute([$dni]);

    header('Location: index.php?mensaje=' . urlencode('El socio fue dado de baja correctamente.'));
    exit;
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container-fluid py-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <h2>Dar de baja a un socio</h2>

            <div class="alert alert-warning mt-3">
                ¿Estás seguro de que querés dar de baja a
                <strong>
                    <?= htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']) ?>
                </strong>?

                <br><br>
                El socio quedará inactivo, pero sus datos y su historial se conservarán.
            </div>

            <form method="POST">

                <button type="submit" class="btn btn-danger">
                    Confirmar baja
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>