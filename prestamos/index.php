<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();
require_once __DIR__ . '/../conexion.php';

$pdo = db();

// Obtener usuarios para el datalist del modal (alumnos/usuarios generales)
$usuarios = $pdo->query("SELECT dni_usuario, nombre, apellido FROM usuario WHERE id_rol IN (1, 2, 3, 4, 5) ORDER BY apellido")->fetchAll(PDO::FETCH_ASSOC);

// Obtener profesores para el datalist del modal (Rol 3 = Profesor)
$profesores = $pdo->query("SELECT dni_usuario, nombre, apellido FROM usuario WHERE id_rol = 3 ORDER BY apellido")->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías únicas para el filtro desplegable
$categorias = $pdo->query("SELECT DISTINCT nombre_categoria FROM vista_prestamos_detalle WHERE nombre_categoria IS NOT NULL ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// CAPTURAR Y VALIDAR FILTROS DESDE GET
// ==========================================
$f_desde = $_GET['f_desde'] ?? '';
$f_hasta = $_GET['f_hasta'] ?? '';
$f_categoria = $_GET['f_categoria'] ?? '';
$f_estado = $_GET['f_estado'] ?? 'todos';
$buscar = trim($_GET['buscar'] ?? '');

// ==========================================
// CONSTRUCCIÓN DE LA CONDICIÓN BASE (WHERE)
// ==========================================
$sql_condiciones = "FROM vista_prestamos_detalle v INNER JOIN prestamos p ON v.id_prestamo = p.id_prestamo WHERE 1=1";
$params = [];

$hay_filtros_activos = !empty($f_desde) || !empty($f_hasta) || !empty($f_categoria) || ($f_estado !== 'todos') || !empty($buscar);

if ($hay_filtros_activos) {
    if (!empty($f_desde)) {
        $sql_condiciones .= " AND DATE(v.fecha_hora_registro) >= ?";
        $params[] = $f_desde;
    }
    if (!empty($f_hasta)) {
        $sql_condiciones .= " AND DATE(v.fecha_hora_registro) <= ?";
        $params[] = $f_hasta;
    }
    if (!empty($f_categoria)) {
        $sql_condiciones .= " AND v.nombre_categoria = ?";
        $params[] = $f_categoria;
    }
    if ($f_estado === 'prestado') {
        $sql_condiciones .= " AND v.fecha_hora_devolucion IS NULL";
    } elseif ($f_estado === 'devuelto') {
        $sql_condiciones .= " AND v.fecha_hora_devolucion IS NOT NULL";
    }
    if (!empty($buscar)) {
        $sql_condiciones .= " AND (v.id_material LIKE ? OR v.nombre_material LIKE ? OR v.apellido_usuario LIKE ? OR v.nombre_usuario LIKE ?)";
        $params[] = "%{$buscar}%";
        $params[] = "%{$buscar}%";
        $params[] = "%{$buscar}%";
        $params[] = "%{$buscar}%";
    }
} else {
    $sql_condiciones .= " AND v.fecha_hora_registro >= DATE_SUB(NOW(), INTERVAL 20 DAY)";
}

// ==========================================
// CONFIGURACIÓN DE PAGINACIÓN
// ==========================================
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$stmt_total = $pdo->prepare("SELECT COUNT(v.id_prestamo) " . $sql_condiciones);
$stmt_total->execute($params);
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);
if ($total_paginas < 1) $total_paginas = 1;
if ($pagina_actual > $total_paginas) $pagina_actual = $total_paginas;

$sql = "SELECT p.fecha_hora_limite_prestamo, v.* " . $sql_condiciones . " ORDER BY v.fecha_hora_registro DESC LIMIT $registros_por_pagina OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/layout_header.php';
?>

<div class="titulo-pagina mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 text-dark mb-1">Gestión de Préstamos</h1>
        <p class="text-muted mb-0">Control de entrega y devolución de materiales en tiempo real.</p>
    </div>
    <button type="button" id="btnAgregar" class="btn btn-danger shadow-sm px-4 fw-medium" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalPrestamo">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Préstamo
    </button>
</div>

<!-- Alertas del sistema -->
<?php if (isset($_GET['status'])): ?>
    <div id="alertaEstado" class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= $_GET['status'] === 'success' ? 'Préstamo registrado correctamente.' : htmlspecialchars($_GET['msg'] ?? 'Ocurrió un error.') ?>
        <button type="button" class="btn-close" onclick="document.getElementById('alertaEstado').remove(); window.history.replaceState({}, document.title, window.location.pathname);" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['status_devolucion'])): ?>
    <div id="alertaDevolucion" class="alert alert-<?= $_GET['status_devolucion'] === 'success_devuelto' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= $_GET['status_devolucion'] === 'success_devuelto' ? 'Préstamo devuelto correctamente.' : htmlspecialchars($_GET['msg'] ?? 'Ocurrió un error.') ?>
        <button type="button" class="btn-close" onclick="document.getElementById('alertaDevolucion').remove(); window.history.replaceState({}, document.title, window.location.pathname);" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Barra de Búsqueda y Filtros -->
<div class="card shadow-sm mb-4 p-3 bg-light border-0 card-custom">
    <form method="GET" action="" class="d-flex gap-1 flex-wrap align-items-center">
        <div class="input-group">
            <select name="f_categoria" class="form-select bg-light text-secondary border-end" style="max-width: 200px;">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nombre_categoria']) ?>" <?= ($f_categoria === $cat['nombre_categoria']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="buscar" class="form-control shadow-none" placeholder="Buscar material o usuario..." value="<?= htmlspecialchars($buscar) ?>">

            <input type="date" name="f_desde" class="form-control shadow-none" style="max-width: 150px; max-height: 50px;" title="Desde" value="<?= htmlspecialchars($f_desde) ?>">

            <input type="date" name="f_hasta" class="form-control shadow-none" style="max-width: 150px; max-height: 50px;" title="Hasta" value="<?= htmlspecialchars($f_hasta) ?>">

            <select name="f_estado" class="form-select bg-light text-secondary border-end shadow-none" style="max-width: 160px;">
                <option value="todos" <?= ($f_estado === 'todos') ? 'selected' : '' ?>>Todos los estados</option>
                <option value="prestado" <?= ($f_estado === 'prestado') ? 'selected' : '' ?>>Prestados</option>
                <option value="devuelto" <?= ($f_estado === 'devuelto') ? 'selected' : '' ?>>Devueltos</option>
            </select>

            <?php if ($hay_filtros_activos): ?>
                <a href="?" class="btn btn-outline-secondary text-muted d-flex align-items-center justify-content-center px-3" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>

            <button type="submit" class="btn btn-danger px-4">Filtrar</button>
        </div>
    </form>
</div>

<!-- Listado en Tabla -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <div class="card-footer bg-white text-muted small py-3 px-4">
                Mostrando <?= count($prestamos) ?> registro(s) (Página <?= $pagina_actual ?> de <?= $total_paginas ?>).
            </div>
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Material</th>
                        <th>Usuario</th>
                        <th>Fecha Préstamo</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($prestamos)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                No se encontraron préstamos registrados con los filtros seleccionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($prestamos as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= $p['id_prestamo'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark">#<?= htmlspecialchars($p['id_material']) ?> <?= htmlspecialchars($p['nombre_material']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($p['nombre_categoria']) ?> (<?= htmlspecialchars($p['atributo_detalle']) ?>)</small>
                                </td>
                                <td><?= htmlspecialchars($p['apellido_usuario'] . ', ' . $p['nombre_usuario']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($p['fecha_hora_registro'])) ?></td>
                                <td>
                                    <?php if ($p['fecha_hora_devolucion']): ?>
                                        <span class="badge badge-soft-success px-2 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i>Devuelto</span>
                                    <?php else: ?>
                                        <span class="badge badge-soft-danger px-2 py-1 rounded-pill"><i class="bi bi-clock-history me-1"></i>Prestado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary me-1 btn-ver-detalle"
                                        data-id="<?= $p['id_prestamo'] ?>"
                                        data-materialid="<?= htmlspecialchars($p['id_material']) ?>"
                                        data-materialnombre="<?= htmlspecialchars($p['nombre_material']) ?>"
                                        data-categoria="<?= htmlspecialchars($p['nombre_categoria']) ?>"
                                        data-detalle="<?= htmlspecialchars($p['atributo_detalle']) ?>"
                                        data-estadomaterial="<?= htmlspecialchars($p['desc_estado_material']) ?>"
                                        data-usuario="<?= htmlspecialchars($p['apellido_usuario'] . ', ' . $p['nombre_usuario']) ?>"
                                        data-dniusuario="<?= $p['dni_usuario'] ?>"
                                        data-dnibibliotecario="<?= $p['dni_bibliotecario'] ?>"
                                        data-fecharegistro="<?= date('d/m/Y H:i', strtotime($p['fecha_hora_registro'])) ?>"
                                        data-fechalimite="<?= date('d/m/Y H:i', strtotime($p['fecha_hora_limite_prestamo'])) ?>"
                                        data-fechadevolucion="<?= $p['fecha_hora_devolucion'] ? date('d/m/Y H:i', strtotime($p['fecha_hora_devolucion'])) : 'Pendiente' ?>"
                                        data-estado="<?= $p['fecha_hora_devolucion'] ? 'Devuelto' : 'Prestado' ?>">
                                        Ver
                                    </button>
                                    <?php if (!$p['fecha_hora_devolucion']): ?>
                                        <form action="devolver_prestamo.php" method="POST" class="d-inline" onsubmit="return confirm('¿Confirma la devolución de este material?');">
                                            <input type="hidden" name="id_prestamo" value="<?= htmlspecialchars($p['id_prestamo']) ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Devolver</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL PARA CARGAR PRÉSTAMOS -->
<!-- ========================================== -->
<div class="modal-prestamo" id="modalPrestamo" style="display: none;">
    <button type="button" class="btn-cerrar-modal" id="btnCerrarModal">&times;</button>
    <form action="procesar_prestamo.php" method="POST" autocomplete="off" class="bg-white p-4 rounded-4 shadow" style="max-width: 450px; width: 100%;">
        <h4 class="mb-3 fw-bold">Registrar Préstamo</h4>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="dni_alumno" class="form-label small fw-bold text-muted m-0">DNI del Usuario</label>
                <a href="/biblioteca-sql/usuario/" class="small text-danger text-decoration-none fw-semibold">
                    <i class="bi bi-person-plus-fill me-1"></i>+ Agregar nuevo usuario
                </a>
            </div>
            <input type="text" name="dni_alumno" id="dni_alumno" class="form-control bg-light border-0" list="usuarios" placeholder="Buscar DNI..." required>
            <datalist id="usuarios">
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['dni_usuario'] ?>"><?= $u['apellido'] . ', ' . $u['nombre'] ?></option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <!-- PUNTO 3: CAMPO PROFESOR A CARGO CON OPCIÓN DE AGREGAR -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="dni_profesor" class="form-label small fw-bold text-muted m-0">Profesor a Cargo (Opcional)</label>
                <a href="/biblioteca-sql/usuario/" class="small text-danger text-decoration-none fw-semibold">
                    <i class="bi bi-person-plus-fill me-1"></i>+ Agregar nuevo profesor
                </a>
            </div>
            <input type="text" name="dni_profesor" id="dni_profesor" class="form-control bg-light border-0" list="profesores" placeholder="Buscar DNI o profesor...">
            <datalist id="profesores">
                <?php foreach ($profesores as $prof): ?>
                    <option value="<?= $prof['dni_usuario'] ?>"><?= $prof['apellido'] . ', ' . $prof['nombre'] ?></option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <div class="mb-4">
            <label for="lectorQR" class="form-label small fw-bold text-muted">ID Material (Escaneo o Selección)</label>
            <input type="text" name="id_material" id="lectorQR" class="form-control bg-light border-0" list="lista_materiales" placeholder="Escanee o busque material..." required>
            <datalist id="lista_materiales">
                <?php foreach ($materiales as $mat): ?>
                    <option value="<?= htmlspecialchars($mat['id_material']) ?>">
                        <?= htmlspecialchars($mat['id_material'] . ' - ' . $mat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3">Confirmar Préstamo</button>
    </form>
</div>

<!-- ========================================== -->
<!-- MODAL: VER DETALLES DEL PRÉSTAMO -->
<!-- ========================================== -->
<div class="modal-prestamo" id="modalVerDetallesPrestamo" style="display: none;">
    <button type="button" class="btn-cerrar-modal" id="btnCerrarModalDetalles">&times;</button>
    <div class="bg-white p-4 rounded-4 shadow" style="max-width: 550px; width: 100%; max-height: 90vh; overflow-y: auto;">
        <h4 class="mb-3 fw-bold text-danger">
            <i class="bi bi-info-circle me-2"></i>Detalles del Préstamo #<span id="ver_id_prestamo"></span>
        </h4>
        <button type="button" class="btn btn-secondary w-100 fw-bold rounded-3" id="btnCerrarModalDetallesBtn">Cerrar</button>
    </div>
</div>

<script src="../assets/js/prestamos.js"></script>
</body>
</html>