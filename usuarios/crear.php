
<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

lb_requerir_login();

$tituloPagina = 'Registrar nuevo usuario';
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
    header('Location: index.php?error=' . urlencode('No tenés permiso para registrar usuarios.'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dni = trim($_POST['dni'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $gmail = trim($_POST['gmail'] ?? '');
    $contra = $_POST['contra'] ?? '';
    $rol = filter_input(INPUT_POST, 'rol', FILTER_VALIDATE_INT);

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

            // Comprobar DNI y correo
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM usuario
                WHERE dni_usuario = ? OR gmail = ?
            ");

            $stmt->execute([$dni, $gmail]);

            if ($stmt->fetchColumn() > 0) {

                throw new Exception(
                    'El DNI o el correo ya están registrados. Si el usuario está inactivo, podés reactivarlo desde el panel.'
                );

            }

            // Crear usuario
            $stmt = $pdo->prepare("
                INSERT INTO usuario
                (
                    dni_usuario,
                    nombre,
                    apellido,
                    gmail,
                    contra,
                    id_rol,
                    puntos,
                    activo
                )
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

            // Registrar puntaje inicial
            $stmt = $pdo->prepare("
                INSERT INTO historial_puntos
                (dni_usuario, id_prestamo, puntos_cambio, motivo, fecha_hora)
                VALUES (?, NULL, 100, 'Puntaje inicial por alta de usuario', NOW())
            ");

            $stmt->execute([$dni]);

            $pdo->commit();

            header('Location: index.php?mensaje=' . urlencode('Usuario registrado correctamente.'));
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e instanceof PDOException
                ? 'No se pudo registrar el usuario. Verificá los datos.'
                : $e->getMessage();

        }
    }
}

?>

<?php require_once __DIR__ . '/../includes/layout_header.php'; ?>

<div class="container-fluid py-4">

    <h2>Registrar nuevo usuario</h2>

    <p class="text-muted">
        Completá los datos del alumno o profesor.
    </p>

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

                        <input
                            type="text"
                            name="dni"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>

                        <input
                            type="text"
                            name="nombre"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido</label>

                        <input
                            type="text"
                            name="apellido"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo electrónico</label>

                        <input
                            type="email"
                            name="gmail"
                            autocomplete="off"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($_POST['gmail'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Contraseña</label>

                        <div class="input-group">

                            <input
                                type="password"
                                name="contra"
                                id="contra"
                                class="form-control"
                                required
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

                            <option value="">Seleccionar...</option>

                            <option value="4"
                                <?= (($_POST['rol'] ?? '') == '4') ? 'selected' : '' ?>>
                                Alumno
                            </option>

                            <option value="3"
                                <?= (($_POST['rol'] ?? '') == '3') ? 'selected' : '' ?>>
                                Profesor
                            </option>

                        </select>

                    </div>

                </div>

                <div class="alert alert-info">
                    <i class="fa-solid fa-star"></i>
                    El usuario comenzará con 100 puntos.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                    Registrar usuario
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
