
<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

lb_requerir_login();

$tituloPagina = 'Editar usuario';
$paginaActiva = 'usuarios';

$usuarioActual = lb_usuario_actual();

// Verificar administrador
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

// Buscar usuario
$stmt = $pdo->prepare("
    SELECT *
    FROM usuario
    WHERE dni_usuario = ?
    AND id_rol IN (3, 4)
");

$stmt->execute([$dni]);
$socio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$socio) {
    header('Location: index.php?error=' . urlencode('Usuario no encontrado.'));
    exit;
}

$error = '';

$nombre = $socio['nombre'];
$apellido = $socio['apellido'];
$gmail = $socio['gmail'];
$rol = (int)$socio['id_rol'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $gmail = trim($_POST['gmail'] ?? '');
    $contra = $_POST['contra'] ?? '';
    $rol = filter_input(INPUT_POST, 'rol', FILTER_VALIDATE_INT);

    if (
        $nombre === '' ||
        $apellido === '' ||
        !filter_var($gmail, FILTER_VALIDATE_EMAIL) ||
        !in_array($rol, [3, 4], true)
    ) {

        $error = 'Completá correctamente todos los campos.';

    } elseif (strlen($contra) > 0 && strlen($contra) < 4) {

        $error = 'La contraseña debe tener al menos 4 caracteres.';

    } else {

        try {

            // Comprobar que el correo no pertenezca a otro usuario
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM usuario
                WHERE gmail = ?
                AND dni_usuario <> ?
            ");

            $stmt->execute([$gmail, $dni]);

            if ($stmt->fetchColumn() > 0) {

                $error = 'Ese correo ya está registrado en otro usuario.';

            } else {

                if ($contra !== '') {

                    $stmt = $pdo->prepare("
                        UPDATE usuario
                        SET nombre = ?,
                            apellido = ?,
                            gmail = ?,
                            contra = ?,
                            id_rol = ?
                        WHERE dni_usuario = ?
                        AND id_rol IN (3, 4)
                    ");

                    $stmt->execute([
                        $nombre,
                        $apellido,
                        $gmail,
                        $contra,
                        $rol,
                        $dni
                    ]);

                } else {

                    $stmt = $pdo->prepare("
                        UPDATE usuario
                        SET nombre = ?,
                            apellido = ?,
                            gmail = ?,
                            id_rol = ?
                        WHERE dni_usuario = ?
                        AND id_rol IN (3, 4)
                    ");

                    $stmt->execute([
                        $nombre,
                        $apellido,
                        $gmail,
                        $rol,
                        $dni
                    ]);

                }

                header('Location: index.php?mensaje=' . urlencode('Los datos del usuario fueron actualizados correctamente.'));
                exit;
            }

        } catch (PDOException $e) {

            $error = 'No se pudieron actualizar los datos. Intentá nuevamente.';

        }
    }
}

?>

<?php require_once __DIR__ . '/../includes/layout_header.php'; ?>

<div class="container-fluid py-4">

    <h2>Editar usuario</h2>

    <p class="text-muted">
        Modificá los datos del alumno o profesor.
    </p>

    <div class="card shadow-sm">

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">DNI</label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($dni) ?>"
                            disabled
                        >

                        <small class="text-muted">
                            El DNI no se puede modificar.
                        </small>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Nombre</label>

                        <input
                            type="text"
                            name="nombre"
                            class="form-control"
                            value="<?= htmlspecialchars($nombre) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Apellido</label>

                        <input
                            type="text"
                            name="apellido"
                            class="form-control"
                            value="<?= htmlspecialchars($apellido) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Correo electrónico</label>

                        <input
                            type="email"
                            name="gmail"
                            class="form-control"
                            value="<?= htmlspecialchars($gmail) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Nueva contraseña</label>

                        <div class="input-group">

                            <input
                                type="password"
                                name="contra"
                                id="contra"
                                class="form-control"
                                placeholder="Dejar vacío para conservar la actual"
                            >

                            <button
                                class="btn btn-outline-secondary"
                                type="button"
                                onclick="mostrarContrasena()"
                            >
                                <i class="fa-solid fa-eye" id="iconoContra"></i>
                            </button>

                        </div>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Tipo de usuario</label>

                        <select name="rol" class="form-select" required>

                            <option value="4" <?= $rol === 4 ? 'selected' : '' ?>>
                                Alumno
                            </option>

                            <option value="3" <?= $rol === 3 ? 'selected' : '' ?>>
                                Profesor
                            </option>

                        </select>

                    </div>

                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                    Guardar cambios
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>

    </div>

</div>

<script>
function mostrarContrasena() {

    const campo = document.getElementById('contra');
    const icono = document.getElementById('iconoContra');

    if (campo.type === 'password') {

        campo.type = 'text';
        icono.classList.replace('fa-eye', 'fa-eye-slash');

    } else {

        campo.type = 'password';
        icono.classList.replace('fa-eye-slash', 'fa-eye');

    }
}
</script>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
