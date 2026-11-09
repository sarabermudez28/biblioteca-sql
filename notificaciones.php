<?php
/**
 * notificaciones.php
 * Módulo de notificaciones para préstamos vencidos - Diseño Profesional
 * CSS integrado para evitar problemas de rutas
 */

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
lb_requerir_login();

$usuario = $_SESSION['lb_usuario'] ?? 'Administrador';
$paginaActiva = 'notificaciones';
$tituloPagina = 'Notificaciones - Biblioteca EPET34';

$database = db();

// ================================
// FUNCIONES
// ================================

function detectarPrestamosVencidos(PDO $db): int {
    $creadas = 0;
    $sql = "
        SELECT p.id_prestamo, p.fecha_hora_limite_prestamo, p.dni_usuario, p.id_material,
               vp.nombre_usuario, vp.apellido_usuario, vp.nombre_material
        FROM prestamos p
        LEFT JOIN vista_prestamos_detalle vp ON p.id_prestamo = vp.id_prestamo
        LEFT JOIN notificaciones_vencimiento n ON p.id_prestamo = n.id_prestamo
        WHERE p.fecha_hora_limite_prestamo < NOW()
          AND p.fecha_hora_devolucion IS NULL
          AND n.id_notificacion IS NULL
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $dias = (int) floor((time() - strtotime($p['fecha_hora_limite_prestamo'])) / 86400);
        $ins = $db->prepare("
            INSERT INTO notificaciones_vencimiento (id_prestamo, dni_usuario, id_material, dias_retraso, estado)
            VALUES (:pid, :dni, :mat, :dias, 'pendiente')
        ");
        $ins->execute([
            'pid' => $p['id_prestamo'],
            'dni' => $p['dni_usuario'],
            'mat' => $p['id_material'],
            'dias' => max(0, $dias)
        ]);
        $creadas++;
    }
    return $creadas;
}

function obtenerNotificaciones(PDO $db, ?string $estado = null, ?string $busqueda = null): array {
    $sql = "
        SELECT n.id_notificacion as id, n.estado, n.dias_retraso, n.fecha_primera_notificacion,
               p.fecha_hora_registro, p.fecha_hora_limite_prestamo,
               vp.nombre_usuario, vp.apellido_usuario, vp.nombre_material, vp.id_material as id_material_detalle
        FROM notificaciones_vencimiento n
        INNER JOIN prestamos p ON n.id_prestamo = p.id_prestamo
        LEFT JOIN vista_prestamos_detalle vp ON p.id_prestamo = vp.id_prestamo
        WHERE 1=1
    ";
    $params = [];
    if ($estado !== null && $estado !== '') {
        $sql .= " AND n.estado = :estado";
        $params['estado'] = $estado;
    }
    if ($busqueda !== null && $busqueda !== '') {
        $sql .= " AND (vp.nombre_usuario LIKE :busqueda OR vp.apellido_usuario LIKE :busqueda OR vp.nombre_material LIKE :busqueda)";
        $params['busqueda'] = "%{$busqueda}%";
    }
    $sql .= " ORDER BY n.dias_retraso DESC, n.fecha_primera_notificacion DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contarNotificacionesPorEstado(PDO $db): array {
    $stmt = $db->query("
        SELECT 
            SUM(CASE WHEN estado = 'pendiente' AND dias_retraso > 0 THEN 1 ELSE 0 END) as vencidos,
            SUM(CASE WHEN estado = 'pendiente' AND dias_retraso = 0 THEN 1 ELSE 0 END) as proximos,
            SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as devueltos,
            COUNT(*) as total
        FROM notificaciones_vencimiento
    ");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function marcarRevisada(PDO $db, int $id): bool {
    $stmt = $db->prepare("
        UPDATE notificaciones_vencimiento 
        SET estado = 'revisada', fecha_ultima_revision = NOW() 
        WHERE id_notificacion = :id AND estado = 'pendiente'
    ");
    return $stmt->execute(['id' => $id]);
}

function getNivelRetraso(int $dias): string {
    if ($dias >= 5) return 'critico';
    if ($dias >= 2) return 'moderado';
    return 'leve';
}

// ================================
// EJECUCIÓN
// ================================

detectarPrestamosVencidos($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'marcar_revisada' && isset($_POST['notificacion_id'])) {
        $id = (int) $_POST['notificacion_id'];
        if (marcarRevisada($database, $id)) {
            header('Location: notificaciones.php?msg=revisada');
            exit();
        }
    }
}

$mensaje = null;
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'revisada') {
        $mensaje = ['tipo' => 'success', 'texto' => 'Notificación marcada como revisada correctamente.'];
    }
}

$filtro = $_GET['filtro'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';
$notificaciones = obtenerNotificaciones($database, $filtro !== '' ? $filtro : null, $busqueda);
$conteos = contarNotificacionesPorEstado($database);

include __DIR__ . '/includes/layout_header.php';
?>

<!-- ============================================ -->
<!-- CSS INTEGRADO - Mismas variables que styles.css -->
<!-- ============================================ -->
<style>
/* Resumen de notificaciones (tarjetas superiores) */
.notificaciones-resumen {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.resumen-card {
  background: var(--card);
  border-radius: var(--radius);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 0 0 1px color-mix(in oklch, var(--foreground) 10%, transparent);
  transition: all 0.15s ease;
  border-left: 4px solid var(--primary);
}

.resumen-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.resumen-icono {
  width: 48px;
  height: 48px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.resumen-contenido {
  display: flex;
  flex-direction: column;
}

.resumen-numero {
  font-size: 1.75rem;
  font-weight: 700;
  font-family: var(--font-serif);
  color: var(--foreground);
  line-height: 1;
}

.resumen-label {
  font-size: 0.8rem;
  color: var(--muted-foreground);
  margin-top: 0.25rem;
  font-weight: 500;
}

/* Colores específicos para cada tipo de tarjeta */
.resumen-card.card-vencidos {
  border-left-color: var(--destructive);
}

.resumen-card.card-vencidos .resumen-icono {
  background: color-mix(in oklch, var(--destructive) 10%, transparent);
  color: var(--destructive);
}

.resumen-card.card-proximos {
  border-left-color: var(--chart-3);
}

.resumen-card.card-proximos .resumen-icono {
  background: color-mix(in oklch, var(--chart-3) 15%, transparent);
  color: var(--chart-3);
}

.resumen-card.card-devueltos {
  border-left-color: var(--chart-2);
}

.resumen-card.card-devueltos .resumen-icono {
  background: color-mix(in oklch, var(--chart-2) 10%, transparent);
  color: var(--chart-2);
}

.resumen-card.card-total {
  border-left-color: var(--primary);
}

.resumen-card.card-total .resumen-icono {
  background: color-mix(in oklch, var(--primary) 10%, transparent);
  color: var(--primary);
}

/* Header del módulo */
.notificaciones-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.notificaciones-header h1 {
  font-size: 1.5rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.notificaciones-header h1 i {
  color: var(--primary);
}

.badge-alerta {
  background: color-mix(in oklch, var(--destructive) 10%, transparent);
  color: var(--destructive);
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid color-mix(in oklch, var(--destructive) 20%, transparent);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

/* Filtros y buscador */
.notificaciones-filtros {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.buscador-form {
  display: flex;
  gap: 0.75rem;
  flex: 1;
  max-width: 450px;
}

.buscador-input {
  flex: 1;
  position: relative;
}

.buscador-input i {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--muted-foreground);
  width: 16px;
  height: 16px;
}

.buscador-input input {
  width: 100%;
  height: 2.5rem;
  padding: 0 0.75rem 0 2.5rem;
  border: 1px solid var(--input);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  background: var(--card);
  color: var(--foreground);
  transition: all 0.15s;
}

.buscador-input input:focus {
  outline: none;
  border-color: var(--ring);
  box-shadow: 0 0 0 3px color-mix(in oklch, var(--ring) 50%, transparent);
}

.buscador-input input::placeholder {
  color: var(--muted-foreground);
}

.btn-buscar {
  height: 2.5rem;
  padding: 0 1rem;
  background: var(--primary);
  color: var(--primary-foreground);
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.15s;
}

.btn-buscar:hover {
  background: color-mix(in oklch, var(--primary) 80%, transparent);
}

/* Lista de notificaciones */
.notificaciones-lista {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.notificacion-item {
  background: var(--card);
  border-radius: var(--radius);
  padding: 1.25rem;
  box-shadow: 0 0 0 1px color-mix(in oklch, var(--foreground) 10%, transparent);
  transition: all 0.15s ease;
  border: 2px solid transparent;
}

.notificacion-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.notificacion-item.card-critico {
  border-color: color-mix(in oklch, var(--destructive) 30%, transparent);
  background: color-mix(in oklch, var(--destructive) 3%, var(--card));
}

.notificacion-item.card-moderado {
  border-color: color-mix(in oklch, var(--chart-3) 30%, transparent);
  background: color-mix(in oklch, var(--chart-3) 3%, var(--card));
}

.notificacion-item.card-leve {
  border-color: color-mix(in oklch, var(--primary) 30%, transparent);
  background: color-mix(in oklch, var(--primary) 3%, var(--card));
}

.notificacion-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.usuario-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.usuario-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--primary-foreground);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.usuario-detalles h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--foreground);
  font-family: var(--font-serif);
}

.material-nombre {
  font-size: 0.85rem;
  color: var(--muted-foreground);
  display: block;
  margin-top: 0.15rem;
}

.dias-retraso {
  padding: 0.4rem 0.875rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  white-space: nowrap;
}

.dias-retraso.badge-critico {
  background: color-mix(in oklch, var(--destructive) 10%, transparent);
  color: var(--destructive);
  border: 1px solid color-mix(in oklch, var(--destructive) 20%, transparent);
}

.dias-retraso.badge-moderado {
  background: color-mix(in oklch, var(--chart-3) 15%, transparent);
  color: var(--chart-3);
  border: 1px solid color-mix(in oklch, var(--chart-3) 25%, transparent);
}

.dias-retraso.badge-leve {
  background: color-mix(in oklch, var(--primary) 10%, transparent);
  color: var(--primary);
  border: 1px solid color-mix(in oklch, var(--primary) 20%, transparent);
}

.notificacion-body {
  display: flex;
  flex-wrap: wrap;
  gap: 1.25rem;
  margin-bottom: 1.25rem;
  padding: 0.875rem;
  background: var(--muted);
  border-radius: 0.5rem;
}

.info-detalle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--muted-foreground);
}

.info-detalle i {
  color: var(--primary);
  width: 16px;
  height: 16px;
}

.notificacion-acciones {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-revisar {
  height: 2rem;
  padding: 0 1rem;
  background: var(--chart-2);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.15s;
}

.btn-revisar:hover {
  background: color-mix(in oklch, var(--chart-2) 85%, transparent);
  transform: translateY(-1px);
}

.btn-secundario {
  height: 2rem;
  padding: 0 1rem;
  background: var(--background);
  color: var(--foreground);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: all 0.15s;
}

.btn-secundario:hover {
  background: var(--muted);
  border-color: var(--primary);
}

.estado-badge {
  height: 2rem;
  padding: 0 1rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.estado-revisada {
  background: color-mix(in oklch, var(--primary) 10%, transparent);
  color: var(--primary);
  border: 1px solid color-mix(in oklch, var(--primary) 20%, transparent);
}

.estado-resuelta {
  background: color-mix(in oklch, var(--chart-2) 10%, transparent);
  color: var(--chart-2);
  border: 1px solid color-mix(in oklch, var(--chart-2) 20%, transparent);
}

/* Estado vacío */
.sin-notificaciones {
  text-align: center;
  padding: 3rem 2rem;
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: 0 0 0 1px color-mix(in oklch, var(--foreground) 10%, transparent);
}

.sin-notificaciones i {
  font-size: 3.5rem;
  color: var(--muted-foreground);
  opacity: 0.4;
  margin-bottom: 1rem;
  display: block;
}

.sin-notificaciones h3 {
  font-size: 1.125rem;
  color: var(--foreground);
  margin: 0 0 0.5rem 0;
  font-family: var(--font-serif);
}

.sin-notificaciones p {
  color: var(--muted-foreground);
  margin: 0;
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
  .notificaciones-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .notificaciones-filtros {
    flex-direction: column;
    align-items: stretch;
  }

  .buscador-form {
    max-width: 100%;
  }

  .notificacion-header {
    flex-direction: column;
  }

  .notificacion-body {
    flex-direction: column;
    gap: 0.75rem;
  }

  .notificacion-acciones {
    flex-direction: column;
  }

  .btn-revisar, .btn-secundario {
    width: 100%;
    justify-content: center;
  }
}
</style>

<div class="view notificaciones-view">
    <!-- Header -->
    <header class="notificaciones-header">
        <div>
            <h1><i class="bi bi-bell-fill"></i> Centro de Notificaciones</h1>
            <p class="subtitle">Gestión de préstamos vencidos y devoluciones</p>
        </div>
        <?php if (($conteos['vencidos'] ?? 0) > 0): ?>
            <span class="badge-alerta">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Atención requerida
            </span>
        <?php endif; ?>
    </header>

    <!-- Mensaje de éxito -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensaje['texto']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- 1. Resumen de notificaciones (4 tarjetas) -->
    <div class="notificaciones-resumen">
        <div class="resumen-card card-vencidos">
            <div class="resumen-icono"><i class="bi bi-clock-history"></i></div>
            <div class="resumen-contenido">
                <span class="resumen-numero"><?= $conteos['vencidos'] ?? 0 ?></span>
                <span class="resumen-label">Préstamos vencidos</span>
            </div>
        </div>
        <div class="resumen-card card-proximos">
            <div class="resumen-icono"><i class="bi bi-calendar-event"></i></div>
            <div class="resumen-contenido">
                <span class="resumen-numero"><?= $conteos['proximos'] ?? 0 ?></span>
                <span class="resumen-label">Próximas devoluciones</span>
            </div>
        </div>
        <div class="resumen-card card-devueltos">
            <div class="resumen-icono"><i class="bi bi-check-circle"></i></div>
            <div class="resumen-contenido">
                <span class="resumen-numero"><?= $conteos['devueltos'] ?? 0 ?></span>
                <span class="resumen-label">Devoluciones registradas</span>
            </div>
        </div>
        <div class="resumen-card card-total">
            <div class="resumen-icono"><i class="bi bi-bell"></i></div>
            <div class="resumen-contenido">
                <span class="resumen-numero"><?= $conteos['total'] ?? 0 ?></span>
                <span class="resumen-label">Notificaciones generadas</span>
            </div>
        </div>
    </div>

    <!-- 4. Buscador y filtros -->
    <div class="notificaciones-filtros">
        <form method="GET" class="buscador-form">
            <div class="buscador-input">
                <i class="bi bi-search"></i>
                <input type="text" name="busqueda" placeholder="Buscar por usuario, material..." value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            <button type="submit" class="btn-buscar">
                <i class="bi bi-search"></i> Buscar
            </button>
        </form>

        <div class="tabs-list">
            <a href="notificaciones.php" class="tabs-trigger <?= $filtro === '' ? 'active' : '' ?>">
                Todas <span class="badge"><?= $conteos['total'] ?? 0 ?></span>
            </a>
            <a href="notificaciones.php?filtro=pendiente" class="tabs-trigger <?= $filtro === 'pendiente' ? 'active' : '' ?>">
                Pendientes <span class="badge badge-destructive"><?= $conteos['vencidos'] ?? 0 ?></span>
            </a>
            <a href="notificaciones.php?filtro=revisada" class="tabs-trigger <?= $filtro === 'revisada' ? 'active' : '' ?>">
                Revisadas
            </a>
            <a href="notificaciones.php?filtro=resuelta" class="tabs-trigger <?= $filtro === 'resuelta' ? 'active' : '' ?>">
                Resueltas
            </a>
        </div>
    </div>

    <!-- 2 y 3. Centro e Historial de notificaciones (Lista) -->
    <div class="notificaciones-lista">
        <?php if (!empty($notificaciones)): ?>
            <?php foreach ($notificaciones as $n): 
                $dias = (int) $n['dias_retraso'];
                $nivel = getNivelRetraso($dias);
                $iniciales = strtoupper(substr($n['apellido_usuario'] ?? 'U', 0, 1)) . strtoupper(substr($n['nombre_usuario'] ?? '', 0, 1));
            ?>
                <div class="notificacion-item card-<?= $nivel ?>">
                    <div class="notificacion-header">
                        <div class="usuario-info">
                            <div class="usuario-avatar"><?= $iniciales ?></div>
                            <div class="usuario-detalles">
                                <h3><?= htmlspecialchars($n['apellido_usuario'] ?? 'Usuario') ?>, <?= htmlspecialchars($n['nombre_usuario'] ?? '') ?></h3>
                                <span class="material-nombre"><?= htmlspecialchars($n['nombre_material'] ?? 'Material') ?></span>
                            </div>
                        </div>
                        <div class="dias-retraso badge-<?= $nivel ?>">
                            <i class="bi bi-clock"></i>
                            <?= $dias ?> <?= $dias === 1 ? 'día' : 'días' ?>
                        </div>
                    </div>

                    <div class="notificacion-body">
                        <div class="info-detalle">
                            <i class="bi bi-calendar"></i>
                            <span>Vencimiento: <?= date('d/m/Y H:i', strtotime($n['fecha_hora_limite_prestamo'])) ?></span>
                        </div>
                        <div class="info-detalle">
                            <i class="bi bi-calendar-check"></i>
                            <span>Préstamo: <?= date('d/m/Y H:i', strtotime($n['fecha_hora_registro'])) ?></span>
                        </div>
                        <div class="info-detalle">
                            <i class="bi bi-barcode"></i>
                            <span>ID: <?= htmlspecialchars($n['id_material_detalle'] ?? '-') ?></span>
                        </div>
                    </div>

                    <div class="notificacion-acciones">
                        <?php if ($n['estado'] === 'pendiente'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="accion" value="marcar_revisada">
                                <input type="hidden" name="notificacion_id" value="<?= (int) $n['id'] ?>">
                                <button type="submit" class="btn-revisar">
                                    <i class="bi bi-check-lg"></i> Marcar como revisada
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="estado-badge estado-<?= $n['estado'] ?>">
                                <i class="bi bi-<?= $n['estado'] === 'revisada' ? 'eye' : 'check-circle' ?>"></i>
                                <?= ucfirst($n['estado']) ?>
                            </span>
                        <?php endif; ?>
                        <a href="/biblioteca-sql/prestamos/" class="btn-secundario">
                            <i class="bi bi-eye"></i> Ver préstamo
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="sin-notificaciones">
                <i class="bi bi-bell-slash"></i>
                <h3>No hay notificaciones</h3>
                <p>Todos los préstamos están al día o no hay registros con este filtro.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include __DIR__ . '/includes/layout_footer.php'; ?>