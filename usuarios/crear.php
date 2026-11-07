<?php
require_once 'seguridad.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dni = trim($_POST['dni'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $gmail = trim($_POST['gmail'] ?? '');
    $contra = $_POST['contra'] ?? '';
    $rol = (int)($_POST['rol'] ?? 0);

    if (
        !ctype_digit($dni) ||
        $nombre === '' ||
        $apellido === '' ||
        !filter_var($gmail, FILTER_VALIDATE_EMAIL) ||
        $contra === '' ||
        !in_array($rol, [3, 4], true)
    ) {
        $error = 'Completá correctamente todos los campos.';
    } else {

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO usuario
                (dni_usuario, nombre, apellido, gmail, contra, id_rol, puntos, activo)
                VALUES (?, ?, ?, ?, ?, ?, 100, 1)
            ");

            $stmt->execute([
                $dni,
                $nombre,
                $apellido,
                $gmail,
                $contra,
                $rol
            ]);

            // Registrar el puntaje inicial en el historial.
            $stmt = $pdo->prepare("
                INSERT INTO historial_puntos
                (dni_usuario, id_prestamo, puntos_cambio, motivo, fecha_hora)
                VALUES (?, NULL, 100, 'Puntaje inicial por alta de socio', NOW())
            ");

            $stmt->execute([$dni]);

            $pdo->commit();

            header('Location: index.php?mensaje=' . urlencode('Socio registrado correctamente.'));
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'No se pudo registrar. Verificá que el DNI y el correo no estén registrados.';
        }
    }
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container-fluid py-4">

    <h2>Registrar nuevo socio</h2>
    <p class="text-muted">Completá los datos del alumno o profesor.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">DNI</label>
                        <input type="number" name="dni" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="gmail" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="contra" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de socio</label>
                        <select name="rol" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="4">Alumno</option>
                            <option value="3">Profesor</option>
                        </select>
                    </div>

                </div>

                <div class="alert alert-info">
                    <i class="fa-solid fa-star"></i>
                    El socio comenzará con 100 puntos.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Registrar socio
                </button>

                <a href="index.php" class="btn btn-secondary">Cancelar</a>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>