<?php
// generar_qr.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../conexion.php';

lb_requerir_login();

// =================================
// Captura y saneamiento de Filtros
// =================================
$buscar       = trim($_GET['buscar'] ?? '');
$id           = trim($_GET['id'] ?? ''); 
$categoria    = trim($_GET['categoria'] ?? '');
$autor_libro  = trim($_GET['autor'] ?? '');
$region_geo   = trim($_GET['region'] ?? '');

// =================================
// Construcción de la Consulta Base (Filtros)
// =================================
$sql_base = "
FROM inventario i
INNER JOIN categorias c ON c.id_categoria = i.id_categoria
LEFT JOIN libros l ON i.id_material = l.id_material
LEFT JOIN instrumentos_medicion im ON i.id_material = im.id_material
LEFT JOIN materiales_didacticos md ON i.id_material = md.id_material
LEFT JOIN materiales_geograficos mg ON i.id_material = mg.id_material
LEFT JOIN materiales_dibujo_tecnico mdt ON i.id_material = mdt.id_material
WHERE 1 = 1
";

$parametros = [];

// Filtro Buscador General
if ($buscar !== '') {
    $sql_base .= " AND (
        i.id_material LIKE :bus_id
        OR l.nombre LIKE :bus_lnom
        OR l.autor LIKE :bus_aut
        OR im.tipo LIKE :bus_im
        OR md.tipo LIKE :bus_md
        OR mg.tipo LIKE :bus_mg
        OR mdt.tipo LIKE :bus_mdt
    )";
    $valor_busqueda = "%{$buscar}%";
    $parametros[':bus_id']   = $valor_busqueda;
    $parametros[':bus_lnom'] = $valor_busqueda;
    $parametros[':bus_aut']  = $valor_busqueda;
    $parametros[':bus_im']   = $valor_busqueda;
    $parametros[':bus_md']   = $valor_busqueda;
    $parametros[':bus_mg']   = $valor_busqueda;
    $parametros[':bus_mdt']  = $valor_busqueda;
}

// Filtro por ID alfanumérico
if ($id !== '') {
    $sql_base .= " AND i.id_material LIKE :id";
    $parametros[':id'] = "%{$id}%";
}

// Filtro por Categoría
if ($categoria !== '') {
    $sql_base .= " AND i.id_categoria = :categoria";
    $parametros[':categoria'] = $categoria;
}

// Filtro por Autor (Libros)
if ($autor_libro !== '') {
    $sql_base .= " AND l.autor LIKE :autor";
    $parametros[':autor'] = "%{$autor_libro}%";
}

// Filtro por Región (Materiales geográficos)
if ($region_geo !== '') {
    $sql_base .= " AND mg.region LIKE :region";
    $parametros[':region'] = "%{$region_geo}%";
}

// =================================
// CONFIGURACIÓN DE PAGINACIÓN
// =================================
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// 1. Obtener el total de registros con los filtros actuales
$stmt_total = db()->prepare("SELECT COUNT(i.id_material) " . $sql_base);
$stmt_total->execute($parametros);
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);
if ($total_paginas < 1) $total_paginas = 1;
if ($pagina_actual > $total_paginas) $pagina_actual = $total_paginas;

// 2. Consulta final con SELECT, LIMIT y OFFSET
$sql = "
SELECT
    i.id_material,
    c.id_categoria,
    c.nombre,
    CASE i.id_categoria
        WHEN 1 THEN l.nombre
        WHEN 3 THEN mg.tipo
        WHEN 4 THEN im.tipo
        WHEN 5 THEN mdt.tipo
        WHEN 6 THEN mdt.tipo
        WHEN 7 THEN md.tipo
        ELSE 'Material Genérico'
    END AS col1,
    CASE i.id_categoria
        WHEN 1 THEN l.autor
        WHEN 3 THEN mg.region
        WHEN 4 THEN ''
        WHEN 5 THEN mdt.descripcion
        WHEN 6 THEN mdt.descripcion
        WHEN 7 THEN md.tematica
        ELSE '-'
    END AS col2,
    CASE i.id_categoria
        WHEN 1 THEN l.genero
        WHEN 3 THEN mg.escala
        WHEN 4 THEN CONCAT(im.longitud_cm, ' cm')
        WHEN 5 THEN ''
        WHEN 6 THEN ''
        WHEN 7 THEN CONCAT(md.piezas, ' piezas')
        ELSE '-'
    END AS col3
" . $sql_base . " ORDER BY i.id_material DESC LIMIT $registros_por_pagina OFFSET $offset";

$stmt = db()->prepare($sql);
$stmt->execute($parametros);
$materiales = $stmt->fetchAll();

// Obtener categorías para el select de filtrado
$sqlCategorias = "
SELECT id_categoria, nombre
FROM categorias
WHERE activo = '1'
ORDER BY nombre ASC
";
$stmtCategorias = db()->prepare($sqlCategorias);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll();

include __DIR__ . '/../includes/layout_header.php';
?>

<div class="titulo-pagina mb-4">
    <div>
        <h1>Generar códigos QR</h1>
        <p class="text-muted">Utiliza los filtros y selecciona materiales de diferentes categorías; la selección se mantiene al cambiar de página.</p>
    </div>
</div>

<!-- Barra de Búsqueda y Filtros con diseño unificado -->
<div class="card shadow-sm mb-4 p-3 bg-light">
    <form method="GET" action="generar_qr.php" class="d-flex gap-1 flex-wrap align-items-center">
        <div class="input-group">
            <select name="categoria" class="form-select bg-light text-secondary border-end" style="max-width: 200px;">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id_categoria'] ?>" <?= ($categoria == $cat['id_categoria']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="buscar" class="form-control shadow-none" placeholder="Buscar material..." value="<?= htmlspecialchars($buscar) ?>">

            <input type="text" name="id" class="form-control shadow-none" style="max-width: 130px;" placeholder="ID (ej: LIB0001)" value="<?= htmlspecialchars($id) ?>">

            <input type="text" name="autor" class="form-control shadow-none" style="max-width: 170px;" placeholder="Filtrar por autor..." value="<?= htmlspecialchars($autor_libro) ?>">

            <input type="text" name="region" class="form-control shadow-none" style="max-width: 170px;" placeholder="Filtrar por región..." value="<?= htmlspecialchars($region_geo) ?>">

            <?php if ($buscar !== '' || $id !== '' || $categoria !== '' || $autor_libro !== '' || $region_geo !== ''): ?>
                <a href="generar_qr.php" class="btn btn-outline-secondary text-muted d-flex align-items-center justify-content-center px-3" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>

            <button type="submit" class="btn btn-danger px-4">Filtrar</button>
        </div>
    </form>
</div>

<!-- Contenedor del Listado en Tabla -->
<div class="card shadow-sm">
    <form id="formQR" action="crear_pdf_qr.php" method="POST" target="_blank">

        <!-- Barra de Acciones y Paginación Superior -->
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div>
                    <span class="text-muted">Ítems seleccionados:</span>
                    <span id="contador" class="badge bg-danger fs-6">0</span>
                </div>
                <button type="button" id="btnDeseleccionarPagina" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.75rem;" title="Deselecciona los ítems visibles en esta página">Deseleccionar página</button>
                <button type="button" id="btnLimpiarSeleccion" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.75rem;" title="Borra toda la selección global">Limpiar todo</button>
            </div>

            <!-- Paginación compacta -->
            <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                <?php 
                $params_url = $_GET;
                unset($params_url['pagina']);
                $query_string = http_build_query($params_url);
                $query_string = $query_string ? '&' . $query_string : '';
                ?>
                <nav aria-label="Paginación compacta">
                    <ul class="pagination pagination-sm m-0">
                        <!-- Botón Anterior -->
                        <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link text-dark px-2 py-1" href="generar_qr.php?pagina=<?= $pagina_actual - 1 ?><?= $query_string ?>" title="Anterior">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>

                        <!-- Indicador compacto (Ej: 1/5) -->
                        <li class="page-item disabled">
                            <span class="page-link bg-light text-dark px-2 py-1 border-0">
                                <?= $pagina_actual ?>/<?= $total_paginas ?>
                            </span>
                        </li>

                        <!-- Botón Siguiente -->
                        <li class="page-item <?= ($pagina_actual >= $total_paginas) ? 'disabled' : '' ?>">
                            <a class="page-link text-dark px-2 py-1" href="generar_qr.php?pagina=<?= $pagina_actual + 1 ?><?= $query_string ?>" title="Siguiente">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

            <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-qr-code"></i> Generar PDF con QRs seleccionados
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" id="seleccionarTodos" class="form-check-input" title="Seleccionar todos en esta página">
                        </th>
                        <th>ID</th>
                        <th>Nombre / Tipo</th>
                        <th>Categoría</th>
                        <th>Detalle (Autor / Región / Descripción)</th>
                        <th>Atributo (Género / Escala / Medida)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materiales)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No se encontraron materiales registrados con los filtros seleccionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($materiales as $m): ?>
                            <tr>
                                <td class="text-center">
                                    <input
                                        type="checkbox"
                                        class="check-material form-check-input"
                                        value="<?= htmlspecialchars($m['id_material']) ?>">
                                </td>
                                <td><strong>#<?= htmlspecialchars($m['id_material']) ?></strong></td>
                                <td><?= htmlspecialchars($m['col1'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($m['nombre'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($m['col2'] ?? '') ?></td>
                                <td><?= htmlspecialchars($m['col3'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checks = document.querySelectorAll(".check-material");
        const contador = document.getElementById("contador");
        const seleccionarTodos = document.getElementById("seleccionarTodos");
        const formQR = document.getElementById("formQR");
        const btnLimpiar = document.getElementById("btnLimpiarSeleccion");
        const btnDeseleccionarPagina = document.getElementById("btnDeseleccionarPagina");

        const STORAGE_KEY = "qr_seleccionados_inventario";

        function obtenerSeleccionados() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch (e) {
                return [];
            }
        }

        function guardarSeleccionados(seleccionados) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(seleccionados));
        }

        function actualizarContador() {
            const seleccionados = obtenerSeleccionados();
            contador.textContent = seleccionados.length;
        }

        function sincronizarCheckboxes() {
            const seleccionados = obtenerSeleccionados();
            let todosMarcados = true;
            if (checks.length === 0) todosMarcados = false;

            checks.forEach(c => {
                const marcado = seleccionados.includes(c.value);
                c.checked = marcado;
                if (!marcado) todosMarcados = false;
            });

            if (seleccionarTodos) {
                seleccionarTodos.checked = todosMarcados && checks.length > 0;
            }
            actualizarContador();
        }

        sincronizarCheckboxes();

        // Escuchar cambios en cada checkbox individual
        checks.forEach(c => {
            c.addEventListener("change", function() {
                let seleccionados = obtenerSeleccionados();
                if (this.checked) {
                    if (!seleccionados.includes(this.value)) {
                        seleccionados.push(this.value);
                    }
                } else {
                    seleccionados = seleccionados.filter(id => id !== this.value);
                }
                guardarSeleccionados(seleccionados);
                sincronizarCheckboxes();
            });
        });

        // Checkbox maestro de la página actual
        if (seleccionarTodos) {
            seleccionarTodos.addEventListener("change", function() {
                let seleccionados = obtenerSeleccionados();
                checks.forEach(c => {
                    c.checked = seleccionarTodos.checked;
                    if (seleccionarTodos.checked) {
                        if (!seleccionados.includes(c.value)) {
                            seleccionados.push(c.value);
                        }
                    } else {
                        seleccionados = seleccionados.filter(id => id !== c.value);
                    }
                });
                guardarSeleccionados(seleccionados);
                actualizarContador();
            });
        }

        // Botón para deseleccionar únicamente los ítems de la página actual
        if (btnDeseleccionarPagina) {
            btnDeseleccionarPagina.addEventListener("click", function() {
                let seleccionados = obtenerSeleccionados();
                checks.forEach(c => {
                    c.checked = false;
                    seleccionados = seleccionados.filter(id => id !== c.value);
                });
                if (seleccionarTodos) seleccionarTodos.checked = false;
                guardarSeleccionados(seleccionados);
                actualizarContador();
            });
        }

        // Botón para limpiar toda la selección global almacenada
        if (btnLimpiar) {
            btnLimpiar.addEventListener("click", function() {
                if (confirm("¿Deseas limpiar todos los materiales seleccionados en general?")) {
                    localStorage.removeItem(STORAGE_KEY);
                    checks.forEach(c => c.checked = false);
                    if (seleccionarTodos) seleccionarTodos.checked = false;
                    actualizarContador();
                }
            });
        }

        // Al enviar el formulario, inyectar inputs ocultos con los IDs guardados de todas las páginas
        formQR.addEventListener("submit", function(e) {
            document.querySelectorAll(".input-oculto-material").forEach(el => el.remove());

            const seleccionados = obtenerSeleccionados();
            if (seleccionados.length === 0) {
                alert("Por favor, selecciona al menos un material para generar el PDF.");
                e.preventDefault();
                return;
            }

            seleccionados.forEach(id => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "materiales[]";
                input.value = id;
                input.className = "input-oculto-material";
                formQR.appendChild(input);
            });
        });
    });
</script>