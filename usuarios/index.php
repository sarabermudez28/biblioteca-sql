
<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

$tituloPagina = 'Usuarios';
$paginaActiva = 'usuarios';

$buscar = trim($_GET['buscar'] ?? '');
$estado = $_GET['estado'] ?? 'todos';

$sql = "
    SELECT u.*, r.desc_rol
    FROM usuario u
    INNER JOIN rol r ON u.id_rol = r.id_rol
    WHERE u.id_rol IN (3, 4)
";

$params = [];

if ($estado === 'activos') {
    $sql .= " AND u.activo = 1";
} elseif ($estado === 'inactivos') {
    $sql .= " AND u.activo = 0";
}

if ($buscar !== '') {

    $sql .= "
        AND (
            u.nombre LIKE ?
            OR u.apellido LIKE ?
            OR CAST(u.dni_usuario AS CHAR) LIKE ?
            OR u.gmail LIKE ?
        )
    ";

    $valor = "%$buscar%";
    $params = [$valor, $valor, $valor, $valor];
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
            Agregar usuario
        </a>

    </div>

    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_GET['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Buscador -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">

            <form method="GET">

                <div class="row g-2">

                    <div class="col-md-7">
                        <input
                            type="text"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por nombre, apellido, DNI o correo..."
                            value="<?= htmlspecialchars($buscar) ?>"
                        >
                    </div>

                    <div class="col-md-3">

                        <select name="estado" class="form-select">

                            <option value="todos"
                                <?= $estado === 'todos' ? 'selected' : '' ?>>
                                Todos los usuarios
                            </option>

                            <option value="activos"
                                <?= $estado === 'activos' ? 'selected' : '' ?>>
                                Solo activos
                            </option>

                            <option value="inactivos"
                                <?= $estado === 'inactivos' ? 'selected' : '' ?>>
                                Solo inactivos
                            </option>

                        </select>

                    </div>

                    <div class="col-md-2 d-flex gap-2">

                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fa-solid fa-search"></i>
                            Buscar
                        </button>

                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>

                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- Tabla -->
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
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($usuarios) > 0): ?>

                        <?php foreach ($usuarios as $u): ?>

                            <?php
                                $dni = $u['dni_usuario'];
                                $activo = (int)$u['activo'] === 1;
                            ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($dni) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?>
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

                                    <?php if ($activo): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>

                                </td>

                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-1 flex-wrap">

                                        <a
                                            href="ver.php?dni=<?= urlencode($dni) ?>"
                                            class="btn btn-sm btn-info text-white"
                                            title="Ver información"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <a
                                            href="editar.php?dni=<?= urlencode($dni) ?>"
                                            class="btn btn-sm btn-primary"
                                            title="Editar usuario"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <?php if ($activo): ?>

                                            <a
                                                href="puntos.php?dni=<?= urlencode($dni) ?>"
                                                class="btn btn-sm btn-warning"
                                                title="Descontar puntos"
                                            >
                                                <i class="fa-solid fa-star"></i>
                                            </a>

                                            <a
                                                href="sumar_puntos.php?dni=<?= urlencode($dni) ?>"
                                                class="btn btn-sm btn-success"
                                                title="Sumar puntos"
                                            >
                                                <i class="fa-solid fa-plus"></i>
                                            </a>

                                            <a
                                                href="eliminar.php?dni=<?= urlencode($dni) ?>"
                                                class="btn btn-sm btn-danger"
                                                title="Dar de baja"
                                            >
                                                <i class="fa-solid fa-user-minus"></i>
                                            </a>

                                        <?php else: ?>

                                            <form
                                                action="reactivar.php"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Deseás reactivar este usuario?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="dni"
                                                    value="<?= htmlspecialchars($dni) ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-success"
                                                    title="Reactivar usuario"
                                                >
                                                    <i class="fa-solid fa-user-check"></i>
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
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
