<?php
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();
require_once 'obtener_inventario.php';

// Obtener la lista de categorías directamente de la base de datos (activo = 1)
$stmt_cats = db()->query("SELECT id_categoria, nombre FROM categorias WHERE activo = 1");
$lista_categorias = $stmt_cats->fetchAll();

// Obtener la lista de estados de materiales desde la tabla 'estado_material'
$stmt_estados = db()->query("SELECT id_estado_material, desc_estado_material FROM estado_material");
$lista_estados = $stmt_estados->fetchAll();

include __DIR__ . '/../includes/layout_header.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Biblioteca</title>
</head>

<body class="bg-light">

    <div class="titulo-pagina mb-4">
        <div>
            <h1>Inventario</h1>
            <p class="text-muted">Utiliza los filtros y busca los materiales que se encuentran en la biblioteca.</p>
        </div>
        <!-- Botón para ir a registrar nuevo material -->
        <a href="registrar_material.php" class="btn btn-danger">
            <i class="bi bi-plus-lg"></i> Nuevo Material
        </a>
    </div>

    <!-- Formulario para Pestañas y Filtros Específicos -->
    <form method="GET" action="index.php">
        <input type="hidden" name="cat" value="<?= htmlspecialchars($cat_key) ?>">
        <?php if (!empty($_GET['q'])): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>">
        <?php endif; ?>
        <?php if (!empty($_GET['id_categoria_filtro'])): ?>
            <input type="hidden" name="id_categoria_filtro" value="<?= htmlspecialchars($_GET['id_categoria_filtro']) ?>">
        <?php endif; ?>

        <!-- Pestañas automáticas y botón para deseleccionar categoría -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <ul class="nav nav-pills mb-0">
                <?php foreach ($categorias_config as $key => $data): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $cat_key === $key ? 'bg-danger text-white active' : 'text-danger' ?>"
                            href="index.php?cat=<?= $key ?>">
                            <?= $data['titulo'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Botón para deseleccionar / quitar categoría -->
            <?php if (!empty($cat_key)): ?>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i> Quitar selección
                </a>
            <?php endif; ?>
        </div>

        <!-- Filtros dinámicos específicos (Solo se muestran si hay una categoría seleccionada) -->
        <?php if ($config !== null): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body bg-white py-0 px-3">
                    <div class="row g-2 align-items-center py-2">
                        <?php foreach ($config['filtros'] as $filtro): ?>
                            <div class="col-md-3 mb-2">
                                <?php if (isset($filtro['type']) && $filtro['type'] === 'select'): ?>
                                    <select name="<?= htmlspecialchars($filtro['name']) ?>" class="form-select form-select-sm">
                                        <option value=""><?= htmlspecialchars($filtro['placeholder']) ?></option>
                                        <?php foreach ($filtro['options'] as $generoTexto): ?>
                                            <?php
                                            $esSeleccionado = (isset($_GET[$filtro['name']]) && $_GET[$filtro['name']] !== '' && $_GET[$filtro['name']] == $generoTexto);
                                            ?>
                                            <option value="<?= htmlspecialchars($generoTexto) ?>" <?= $esSeleccionado ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($generoTexto) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php elseif (isset($filtro['type']) && $filtro['type'] === 'select_estado'): ?>
                                    <select name="<?= htmlspecialchars($filtro['name']) ?>" class="form-select form-select-sm">
                                        <option value=""><?= htmlspecialchars($filtro['placeholder']) ?></option>
                                        <?php foreach ($filtro['options'] as $est): ?>
                                            <?php
                                            $esEstSeleccionado = (isset($_GET[$filtro['name']]) && $_GET[$filtro['name']] !== '' && $_GET[$filtro['name']] == $est['id_estado_material']);
                                            ?>
                                            <option value="<?= $est['id_estado_material'] ?>" <?= $esEstSeleccionado ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($est['desc_estado_material']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php else: ?>
                                    <input type="text" name="<?= htmlspecialchars($filtro['name']) ?>" class="form-control form-control-sm" placeholder="<?= htmlspecialchars($filtro['placeholder']) ?>" value="<?= htmlspecialchars($_GET[$filtro['name']] ?? '') ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-md-auto d-flex gap-2 ms-auto mb-2">
                            <button type="submit" class="btn btn-danger btn-sm px-3">
                                <i class="bi bi-filter"></i> Filtrar
                            </button>
                            <a href="index.php?cat=<?= htmlspecialchars($cat_key) ?>" class="btn btn-outline-secondary btn-sm px-3" title="Limpiar filtros específicos">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>

    <!-- Tabla dinámica / Contenido -->
    <div class="card shadow-sm border-0">
        <?php if ($config === null): ?>
            <div class="alert alert-info text-center py-5 m-0 border-0">
                <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
                <h4>Bienvenido al Inventario</h4>
                <p class="mb-0 text-muted">Por favor, selecciona una categoría en las pestañas superiores para comenzar a visualizar y filtrar los materiales.</p>
            </div>
        <?php else: ?>
            <!-- Paginación con Flechas y Salto Directo a Página -->
            <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                <?php
                // Construir query string auxiliar para mantener filtros y categoría en la paginación
                $params_url = $_GET;
                unset($params_url['pagina']);
                $query_string = http_build_query($params_url);
                $query_string = $query_string ? '&' . $query_string : '';
                ?>
                <div class="d-flex justify-content-center align-items-center py-3 bg-white border-top flex-wrap gap-3">
                    <nav aria-label="Paginación compacta centrado">
                        <ul class="pagination pagination-sm m-0">
                            <!-- Botón Anterior -->
                            <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link text-dark px-2 py-1" href="index.php?cat=<?= htmlspecialchars($cat_key) ?>&pagina=<?= $pagina_actual - 1 ?><?= $query_string ?>" title="Anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Indicador compacto (Ej: 1/5) -->
                            <li class="page-item disabled">
                                <span class="page-link bg-light text-dark px-2 py-1 border-0">
                                    <?= $pagina_actual ?> / <?= $total_paginas ?>
                                </span>
                            </li>

                            <!-- Botón Siguiente -->
                            <li class="page-item <?= ($pagina_actual >= $total_paginas) ? 'disabled' : '' ?>">
                                <a class="page-link text-dark px-2 py-1" href="index.php?cat=<?= htmlspecialchars($cat_key) ?>&pagina=<?= $pagina_actual + 1 ?><?= $query_string ?>" title="Siguiente">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <!-- Formulario de Salto Directo a Página -->
                    <form method="GET" action="index.php" class="d-flex align-items-center gap-1 m-0">
                        <!-- Mantenemos todos los parámetros actuales en campos ocultos -->
                        <input type="hidden" name="cat" value="<?= htmlspecialchars($cat_key) ?>">
                        <?php if (!empty($_GET['q'])): ?>
                            <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>">
                        <?php endif; ?>
                        <?php if (!empty($_GET['id_categoria_filtro'])): ?>
                            <input type="hidden" name="id_categoria_filtro" value="<?= htmlspecialchars($_GET['id_categoria_filtro']) ?>">
                        <?php endif; ?>
                        <?php foreach ($config['filtros'] as $filtro): ?>
                            <?php if (!empty($_GET[$filtro['name']])): ?>
                                <input type="hidden" name="<?= htmlspecialchars($filtro['name']) ?>" value="<?= htmlspecialchars($_GET[$filtro['name']]) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <span class="text-muted small">Ir a pág:</span>
                        <input type="number" name="pagina" min="1" max="<?= $total_paginas ?>" value="<?= $pagina_actual ?>" class="form-control form-control-sm text-center" style="width: 65px;" required>
                        <button type="submit" class="btn btn-outline-secondary btn-sm px-2" title="Ir a la página">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <?php foreach ($config['columnas'] as $col): ?>
                                <th class="py-3 px-3"><?= $col ?></th>
                            <?php endforeach; ?>
                            <th class="py-3 px-3 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultados)): ?>
                            <tr>
                                <td colspan="<?= count($config['columnas']) + 1 ?>" class="text-center py-4 text-muted">
                                    No se encontraron registros que coincidan con la búsqueda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultados as $row): ?>
                                <?php
                                $estadoTexto = $row['estado_material'] ?? $row['estado'] ?? '-';
                                $esRoto = (stripos($estadoTexto, 'roto') !== false || stripos($estadoTexto, 'dañado') !== false || stripos($estadoTexto, 'malo') !== false);
                                $badgeClass = $esRoto ? 'bg-danger' : 'bg-success';
                                ?>
                                <tr>
                                    <td class="px-3"><strong><?= htmlspecialchars($row['id_material']) ?></strong></td>
                                    <td class="px-3"><?= htmlspecialchars($row['col1'] ?? '-') ?></td>
                                    <td class="px-3"><?= htmlspecialchars($row['col2'] ?? '-') ?></td>
                                    <td class="px-3"><?= htmlspecialchars($row['col3'] ?? '-') ?></td>
                                    <td class="px-3"><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($estadoTexto) ?></span></td>
                                    <td class="px-3 text-end">
                                        <!-- Botón Ver Detalles -->
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalVerDetalles"
                                            data-id="<?= htmlspecialchars($row['id_material']) ?>"
                                            data-col1="<?= htmlspecialchars($row['col1'] ?? '-') ?>"
                                            data-col2="<?= htmlspecialchars($row['col2'] ?? '-') ?>"
                                            data-col3="<?= htmlspecialchars($row['col3'] ?? '-') ?>"
                                            data-estado="<?= htmlspecialchars($estadoTexto) ?>"
                                            title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Botón Editar Material -->
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarMaterial"
                                            data-id="<?= htmlspecialchars($row['id_material']) ?>"
                                            data-col1="<?= htmlspecialchars($row['col1'] ?? '') ?>"
                                            data-col2="<?= htmlspecialchars($row['col2'] ?? '') ?>"
                                            data-col3="<?= htmlspecialchars($row['col3'] ?? '') ?>"
                                            data-estado="<?= htmlspecialchars($estadoTexto) ?>"
                                            title="Editar material">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: VER DETALLES DEL MATERIAL         -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalVerDetalles" tabindex="-1" aria-labelledby="modalVerDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalVerDetallesLabel">
                        <i class="bi bi-info-circle me-2"></i>Detalles del Material
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">ID Material:</span>
                            <span id="ver_id_material" class="text-secondary"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold"><?= htmlspecialchars($config['columnas'][1] ?? 'Columna 1') ?>:</span>
                            <span id="ver_col1" class="text-secondary"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold"><?= htmlspecialchars($config['columnas'][2] ?? 'Columna 2') ?>:</span>
                            <span id="ver_col2" class="text-secondary"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold"><?= htmlspecialchars($config['columnas'][3] ?? 'Columna 3') ?>:</span>
                            <span id="ver_col3" class="text-secondary"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">Estado:</span>
                            <span id="ver_estado" class="badge"></span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: EDITAR DATOS DEL MATERIAL         -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalEditarMaterial" tabindex="-1" aria-labelledby="modalEditarMaterialLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="actualizar_material.php" method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalEditarMaterialLabel">
                            <i class="bi bi-pencil-square me-2"></i>Editar Material (<?= htmlspecialchars($config['titulo'] ?? '') ?>)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <!-- IDs y Categoría Ocultos para procesar en backend -->
                        <input type="hidden" name="id_material" id="edit_id_material">
                        <input type="hidden" name="cat" value="<?= htmlspecialchars($cat_key) ?>">

                        <div class="mb-3">
                            <label for="edit_col1" class="form-label fw-semibold"><?= htmlspecialchars($config['columnas'][1] ?? 'Campo 1') ?></label>
                            <input type="text" class="form-control" id="edit_col1" name="col1" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_col2" class="form-label fw-semibold"><?= htmlspecialchars($config['columnas'][2] ?? 'Campo 2') ?></label>
                            <input type="text" class="form-control" id="edit_col2" name="col2">
                        </div>

                        <?php if (isset($config) && ($config['columnas'][3] ?? '-') !== '-'): ?>
                            <div class="mb-3">
                                <label for="edit_col3" class="form-label fw-semibold"><?= htmlspecialchars($config['columnas'][3]) ?></label>
                                <input type="text" class="form-control" id="edit_col3" name="col3">
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="col3" id="edit_col3" value="">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="edit_estado" class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="edit_estado" name="id_estado_material">
                                <?php foreach ($lista_estados as $est): ?>
                                    <option value="<?= $est['id_estado_material'] ?>"><?= htmlspecialchars($est['desc_estado_material']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript para cargar datos en los modales dinámicamente -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Ver Detalles
            const modalVer = document.getElementById('modalVerDetalles');
            if (modalVer) {
                modalVer.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const estado = button.getAttribute('data-estado') || '-';

                    modalVer.querySelector('#ver_id_material').textContent = button.getAttribute('data-id') || '-';
                    modalVer.querySelector('#ver_col1').textContent = button.getAttribute('data-col1') || '-';
                    modalVer.querySelector('#ver_col2').textContent = button.getAttribute('data-col2') || '-';
                    modalVer.querySelector('#ver_col3').textContent = button.getAttribute('data-col3') || '-';

                    const badgeEstado = modalVer.querySelector('#ver_estado');
                    badgeEstado.textContent = estado;

                    // Color dinámico en el modal de detalles
                    badgeEstado.className = 'badge ';
                    const estadoLower = estado.toLowerCase();
                    if (estadoLower.includes('roto') || estadoLower.includes('dañado') || estadoLower.includes('malo')) {
                        badgeEstado.classList.add('bg-danger');
                    } else {
                        badgeEstado.classList.add('bg-success');
                    }
                });
            }

            // Modal Editar Material
            const modalEditar = document.getElementById('modalEditarMaterial');
            if (modalEditar) {
                modalEditar.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    modalEditar.querySelector('#edit_id_material').value = button.getAttribute('data-id') || '';
                    modalEditar.querySelector('#edit_col1').value = button.getAttribute('data-col1') || '';
                    modalEditar.querySelector('#edit_col2').value = button.getAttribute('data-col2') || '';
                    modalEditar.querySelector('#edit_col3').value = button.getAttribute('data-col3') || '';

                    // Seleccionar el estado correspondiente en el select de edición
                    const estadoTexto = button.getAttribute('data-estado') || '';
                    const selectEstado = modalEditar.querySelector('#edit_estado');
                    for (let option of selectEstado.options) {
                        if (option.text.trim() === estadoTexto.trim()) {
                            option.selected = true;
                            break;
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>