<?php

// Conexión a la base de datos
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

// Obtener conexión
$pdo = db();

$tituloPagina = 'Usuarios';
$paginaActiva = 'usuarios';

$buscar = trim($_GET['buscar'] ?? '');

// Consulta de usuarios
$sql = "
    SELECT u.*, r.desc_rol
    FROM usuario u
    INNER JOIN rol r ON u.id_rol = r.id_rol
    WHERE u.id_rol IN (3, 4)
";

$params = [];

if ($buscar !== '') {

    $sql .= " AND (
        u.nombre LIKE ?
        OR u.apellido LIKE ?
        OR CAST(u.dni_usuario AS CHAR) LIKE ?
    )";

    $valor = "%$buscar%";
    $params = [$valor, $valor, $valor];
}

$sql .= " ORDER BY u.apellido ASC, u.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php require_once __DIR__ . '/../includes/layout_header.php'; ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2>Gestión de usuarios</h2>
            <p class="text-muted">
                Administración de alumnos y profesores.
            </p>
        </div>

        <a href="crear.php" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i>
            Agregar socio
        </a>

    </div>

    <?php if (isset($_GET['mensaje'])): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($_GET['mensaje']) ?>
        </div>

    <?php endif; ?>

    <!-- Buscador -->
    <form method="GET" class="mb-3">

        <div class="input-group">

            <input
                type="text"
                name="buscar"
                class="form-control"
                placeholder="Buscar por nombre, apellido o DNI..."
                value="<?= htmlspecialchars($buscar) ?>"
            >

            <button class="btn btn-outline-primary" type="submit">
                <i class="fa-solid fa-search"></i>
                Buscar
            </button>

            <a href="index.php" class="btn btn-outline-secondary">
                Limpiar
            </a>

        </div>

    </form>

    <!-- Tabla de usuarios -->
    <div class="card shadow-sm">

        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Apellido y nombre</th>
                        <th>Correo</th>
                        <th>Tipo</th>
                        <th>Puntos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($usuarios) > 0): ?>

                        <?php foreach ($usuarios as $u): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($u['dni_usuario']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $u['apellido'] . ', ' . $u['nombre']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($u['gmail']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($u['desc_rol']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        <?= (int)$u['puntos'] ?> puntos
                                    </span>
                                </td>

                                <td>

                                    <?php if ($u['activo']): ?>

                                        <span class="badge bg-success">
                                            Activo
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactivo
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <!-- Ver usuario -->
                                    <a
                                        href="ver.php?dni=<?= urlencode($u['dni_usuario']) ?>"
                                        class="btn btn-sm btn-info text-white"
                                        title="Ver información"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <?php if ($u['activo']): ?>

                                        <!-- Descontar puntos -->
                                        <a
                                            href="puntos.php?dni=<?= urlencode($u['dni_usuario']) ?>"
                                            class="btn btn-sm btn-warning"
                                            title="Descontar puntos"
                                        >
                                            <i class="fa-solid fa-star"></i>
                                        </a>

                                        <!-- Dar de baja -->
                                        <a
                                            href="eliminar.php?dni=<?= urlencode($u['dni_usuario']) ?>"
                                            class="btn btn-sm btn-danger"
                                            title="Dar de baja"
                                        >
                                            <i class="fa-solid fa-user-minus"></i>
                                        </a>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No se encontraron usuarios.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
