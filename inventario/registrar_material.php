<?php
// registrar_material.php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();
require_once __DIR__ . '/../config.php';

$dni_usuario = $_SESSION['dni_usuario'];
$mensaje = '';
$tipo_mensaje = '';
$codigo_generado = '';

// ============================================
// PROCESAR EL FORMULARIO DE MATERIAL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_categoria   = (int)($_POST['id_categoria'] ?? 0);
    $id_estado      = (int)($_POST['id_estado_material'] ?? 0);
    $fecha_hora     = date('Y-m-d H:i:s');

    if ($id_categoria <= 0 || $id_estado <= 0) {
        $mensaje = "Debe seleccionar categoría y estado.";
        $tipo_mensaje = "error";
    } else {
        try {
            db()->beginTransaction();

            // ----------------------------------------
            // 1) GENERAR EL ID DE FORMA SEGURA
            // ----------------------------------------
            $stmtLast = db()->prepare("
                SELECT id_material 
                FROM inventario 
                WHERE id_categoria = :id 
                ORDER BY id_material DESC 
                LIMIT 1
            ");
            $stmtLast->execute([':id' => $id_categoria]);
            $lastMat = $stmtLast->fetch(PDO::FETCH_ASSOC);

            $numero_existente = 0;
            if ($lastMat && isset($lastMat['id_material'])) {
                $numero_existente = (int)preg_replace('/[^0-9]/', '', $lastMat['id_material']);
            }

            $stmtCat = db()->prepare("
                SELECT prefijo, hasta_num, actual_num 
                FROM categorias 
                WHERE id_categoria = :id
                FOR UPDATE
            ");
            $stmtCat->execute([':id' => $id_categoria]);
            $cat = $stmtCat->fetch(PDO::FETCH_ASSOC);

            if (!$cat) {
                throw new Exception("Categoría no encontrada.");
            }

            $numero_categorias = (int)$cat['actual_num'];
            $siguiente = max($numero_existente, $numero_categorias) + 1;

            if ($siguiente > $cat['hasta_num']) {
                throw new Exception("Rango agotado para la categoría '{$cat['prefijo']}' (máximo: {$cat['hasta_num']}).");
            }

            // ID generado (ej: LIB0001)
            $id_material = $cat['prefijo'] . str_pad($siguiente, 4, '0', STR_PAD_LEFT);

            // ----------------------------------------
            // 2) INSERTAR EN INVENTARIO
            // ----------------------------------------
            $stmtInv = db()->prepare("
                INSERT INTO inventario 
                    (id_material, fecha_hora_ingreso, dni_registro, id_categoria, id_estado_material)
                VALUES 
                    (:id_material, :fecha, :dni, :cat, :estado)
            ");
            $stmtInv->execute([
                ':id_material' => $id_material,
                ':fecha'       => $fecha_hora,
                ':dni'         => $dni_usuario,
                ':cat'         => $id_categoria,
                ':estado'      => $id_estado
            ]);

            // ----------------------------------------
            // 3) ACTUALIZAR CONTADOR DE LA CATEGORÍA
            // ----------------------------------------
            $stmtUpd = db()->prepare("
                UPDATE categorias 
                SET actual_num = :actual 
                WHERE id_categoria = :id
            ");
            $stmtUpd->execute([':actual' => $siguiente, ':id' => $id_categoria]);

            // ----------------------------------------
            // 4) INSERTAR EN LA TABLA ESPECÍFICA
            // ----------------------------------------
            switch ($id_categoria) {
                case 1: // LIBROS[cite: 2]
                    $nombre  = trim($_POST['nombre'] ?? '');
                    $autor   = trim($_POST['autor'] ?? '');
                    $genero  = trim($_POST['genero'] ?? '');
                    if ($nombre === '') throw new Exception("El nombre es obligatorio.");
                    
                    $stmtDet = db()->prepare("
                        INSERT INTO libros (id_material, nombre, autor, id_estado, genero)
                        VALUES (:id, :nombre, :autor, :estado, :genero)
                    ");
                    $stmtDet->execute([
                        ':id'     => $id_material,
                        ':nombre' => $nombre,
                        ':autor'  => $autor,
                        ':estado' => $id_estado,
                        ':genero' => $genero !== '' ? $genero : null
                    ]);
                    break;

                case 3: // MAPAS / MATERIALES GEOGRÁFICOS[cite: 2]
                    $tipo   = trim($_POST['tipo_geo'] ?? '');
                    $region = trim($_POST['region'] ?? '');
                    $escala = trim($_POST['escala'] ?? '');
                    $anio   = !empty($_POST['anio']) ? (int)$_POST['anio'] : null;
                    if ($tipo === '') throw new Exception("El tipo es obligatorio.");
                    
                    $stmtDet = db()->prepare("
                        INSERT INTO materiales_geograficos (id_material, tipo, region, escala, anio)
                        VALUES (:id, :tipo, :region, :escala, :anio)
                    ");
                    $stmtDet->execute([
                        ':id'     => $id_material,
                        ':tipo'   => $tipo,
                        ':region' => $region,
                        ':escala' => $escala,
                        ':anio'   => $anio
                    ]);
                    break;

                case 4: // INSTRUMENTOS DE MEDICIÓN (Reglas)[cite: 2]
                    $tipo_inst  = trim($_POST['tipo_inst'] ?? '');
                    $longitud   = !empty($_POST['longitud_cm']) ? (float)$_POST['longitud_cm'] : null;
                    if ($tipo_inst === '') throw new Exception("El tipo es obligatorio.");
                    
                    $stmtDet = db()->prepare("
                        INSERT INTO instrumentos_medicion (id_material, tipo, longitud_cm)
                        VALUES (:id, :tipo, :longitud)
                    ");
                    $stmtDet->execute([
                        ':id'       => $id_material,
                        ':tipo'     => $tipo_inst,
                        ':longitud' => $longitud
                    ]);
                    break;

                case 5: // TABLEROS (Dibujo Técnico)[cite: 2]
                case 6: // CAJAS (Dibujo Técnico)[cite: 2]
                    $tipo_dt     = trim($_POST['tipo_dt'] ?? 'tablero');
                    $descripcion = trim($_POST['descripcion_dt'] ?? '');

                    $stmtDet = db()->prepare("
                        INSERT INTO materiales_dibujo_tecnico (id_material, tipo, descripcion)
                        VALUES (:id, :tipo, :descripcion)
                    ");
                    $stmtDet->execute([
                        ':id'          => $id_material,
                        ':tipo'        => $tipo_dt,
                        ':descripcion' => $descripcion !== '' ? $descripcion : null
                    ]);
                    break;

                case 7: // JUEGOS DIDÁCTICOS[cite: 2]
                    $tipo_did  = trim($_POST['tipo_did'] ?? '');
                    $tematica  = trim($_POST['tematica'] ?? '');
                    $piezas    = !empty($_POST['piezas']) ? (int)$_POST['piezas'] : null;
                    if ($tipo_did === '') throw new Exception("El tipo es obligatorio.");
                    
                    $stmtDet = db()->prepare("
                        INSERT INTO materiales_didacticos (id_material, tipo, tematica, piezas)
                        VALUES (:id, :tipo, :tematica, :piezas)
                    ");
                    $stmtDet->execute([
                        ':id'       => $id_material,
                        ':tipo'     => $tipo_did,
                        ':tematica' => $tematica,
                        ':piezas'   => $piezas
                    ]);
                    break;

                default:
                    break;
            }

            db()->commit();

            $codigo_generado = $id_material;
            $mensaje = "✅ Material registrado correctamente. ID: $id_material";
            $tipo_mensaje = "exito";
        } catch (Exception $e) {
            if (db()->inTransaction()) db()->rollBack();
            $mensaje = "❌ Error: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// ============================================
// CARGAR DATOS PARA LOS SELECTS
// ============================================
$categorias = db()->query("
    SELECT id_categoria, nombre, prefijo, desde_num, hasta_num, actual_num 
    FROM categorias WHERE activo = 1 
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

$estados = db()->query("
    SELECT id_estado_material, desc_estado_material 
    FROM estado_material 
    ORDER BY desc_estado_material ASC
")->fetchAll(PDO::FETCH_ASSOC);

$generos = db()->query("
    SELECT DISTINCT genero 
    FROM libros 
    WHERE genero IS NOT NULL AND genero != '' 
    ORDER BY genero ASC
")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/layout_header.php';
?>
<div class="material-register-container">
    <a href="index.php" class="btn-back">← Volver al inventario</a>

    <div class="page-header">
        <h1>Registrar nuevo material</h1>
        <p>Completá los datos según la categoría. El sistema generará automáticamente el ID único y el código QR.</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?>">
            <?= htmlspecialchars($mensaje) ?>
            <?php if ($codigo_generado): ?>
                <div class="qr-container">
                    <p><strong>Código QR para: <?= htmlspecialchars($codigo_generado) ?></strong></p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($codigo_generado) ?>"
                        alt="QR <?= htmlspecialchars($codigo_generado) ?>">
                    <br>
                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=<?= urlencode($codigo_generado) ?>"
                        target="_blank" class="btn-print">🖨️ Imprimir QR en tamaño grande</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="material-form-main" id="materialForm">

        <fieldset class="form-fieldset">
            <legend>1. Datos generales</legend>

            <div class="form-field">
                <label for="category_id">Categoría *</label>
                <select name="id_categoria" id="category_id" required>
                    <option value="">-- Seleccionar categoría --</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"
                            data-prefijo="<?= htmlspecialchars($c['prefijo']) ?>"
                            data-hasta="<?= $c['hasta_num'] ?>"
                            data-actual="<?= $c['actual_num'] ?>">
                            <?= htmlspecialchars($c['nombre']) ?>
                            (<?= $c['prefijo'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="material_status_id">Estado del material *</label>
                <select name="id_estado_material" id="material_status_id" required>
                    <option value="">-- Seleccionar estado --</option>
                    <?php foreach ($estados as $e): ?>
                        <option value="<?= $e['id_estado_material'] ?>">
                            <?= htmlspecialchars($e['desc_estado_material']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>

        <!-- FORMULARIOS DINÁMICOS -->
        <fieldset class="form-fieldset dynamic-form-section" id="booksForm" style="display:none;">
            <legend>2. Datos del libro</legend>
            <div class="form-field">
                <label>Título / Nombre *</label>
                <input type="text" name="nombre">
            </div>
            <div class="form-field">
                <label>Autor</label>
                <input type="text" name="autor">
            </div>
            <div class="form-field">
                <label>Género</label>
                <input type="text" name="genero" list="listaGeneros" placeholder="Ej: Matemática, Biología, etc.">
                <datalist id="listaGeneros">
                    <?php foreach ($generos as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
        </fieldset>

        <fieldset class="form-fieldset dynamic-form-section" id="geographicForm" style="display:none;">
            <legend>2. Datos del material geográfico (Mapa)</legend>
            <div class="form-field">
                <label>Tipo (mapa, plano...) *</label>
                <input type="text" name="tipo_geo">
            </div>
            <div class="form-field">
                <label>Región</label>
                <input type="text" name="region" placeholder="Ej: Misiones, Argentina...">
            </div>
            <div class="form-field">
                <label>Escala</label>
                <input type="text" name="escala" placeholder="Ej: 1:50.000">
            </div>
            <div class="form-field">
                <label>Año</label>
                <input type="number" name="anio" min="1800" max="2100">
            </div>
        </fieldset>

        <fieldset class="form-fieldset dynamic-form-section" id="instrumentsForm" style="display:none;">
            <legend>2. Datos del instrumento de medición (Regla...)</legend>
            <div class="form-field">
                <label>Tipo (regla 45°, regla 60°...) *</label>
                <input type="text" name="tipo_inst">
            </div>
            <div class="form-field">
                <label>Longitud (cm)</label>
                <input type="number" name="longitud_cm" step="0.1" min="0">
            </div>
        </fieldset>

        <fieldset class="form-fieldset dynamic-form-section" id="drawingForm" style="display:none;">
            <legend>2. Datos de Dibujo Técnico / Almacenamiento</legend>
            <div class="form-field">
                <label>Tipo *</label>
                <input type="text" name="tipo_dt" placeholder="Ej: tablero, soporte de tablero">
            </div>
            <div class="form-field">
                <label>Descripción / Observación</label>
                <input type="text" name="descripcion_dt" placeholder="Detalles o estado específico">
            </div>
        </fieldset>

        <fieldset class="form-fieldset dynamic-form-section" id="didacticForm" style="display:none;">
            <legend>2. Datos del material didáctico</legend>
            <div class="form-field">
                <label>Tipo de juego/material *</label>
                <input type="text" name="tipo_did" placeholder="Ej: Juego de mesa, rompecabezas...">
            </div>
            <div class="form-field">
                <label>Temática</label>
                <input type="text" name="tematica">
            </div>
            <div class="form-field">
                <label>Cantidad de piezas</label>
                <input type="number" name="piezas" min="0">
            </div>
        </fieldset>

        <div class="form-actions">
            <a href="index.php" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Guardar material</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectCategoria = document.getElementById('category_id');
        const formularios = {
            1: 'booksForm',
            3: 'geographicForm',
            4: 'instrumentsForm',
            5: 'drawingForm',
            6: 'drawingForm',
            7: 'didacticForm'
        };

        selectCategoria.addEventListener('change', function() {
            document.querySelectorAll('.dynamic-form-section').forEach(f => {
                f.style.display = 'none';
                f.querySelectorAll('[required]').forEach(input => {
                    input.removeAttribute('required');
                    input.dataset.requeridoOriginal = 'true';
                });
            });

            const idCat = this.value;
            if (!idCat) return;

            const idForm = formularios[idCat];
            if (idForm) {
                const form = document.getElementById(idForm);
                if (form) {
                    form.style.display = 'block';
                    form.querySelectorAll('[data-requerido-original]').forEach(input => {
                        input.setAttribute('required', '');
                        delete input.dataset.requeridoOriginal;
                    });
                }
            }
        });
    });
</script>