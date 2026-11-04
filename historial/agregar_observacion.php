<?php
// ================================
// Agregar observación (POST)
// ================================
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$idPrestamo  = (int)($_POST['id_prestamo']  ?? 0);
$observacion = trim($_POST['observacion']   ?? '');
$volver      = trim($_POST['volver']        ?? 'detalle');
$dniUser     = (int)($_SESSION['dni_usuario'] ?? 0);

// Validaciones
if ($idPrestamo <= 0) {
    header('Location: index.php');
    exit;
}
if ($observacion === '' || mb_strlen($observacion) > 1000) {
    $_SESSION['hist_msg']  = 'La observación es obligatoria y no puede superar los 1000 caracteres.';
    $_SESSION['hist_tipo'] = 'error';
    header("Location: detalle.php?id=$idPrestamo");
    exit;
}
if ($dniUser <= 0) {
    $_SESSION['hist_msg']  = 'No se pudo identificar al usuario.';
    $_SESSION['hist_tipo'] = 'error';
    header("Location: detalle.php?id=$idPrestamo");
    exit;
}

try {
    $db = db();

    // Verificar que el préstamo existe
    $stmt = $db->prepare("SELECT COUNT(*) FROM prestamos WHERE id_prestamo = ?");
    $stmt->execute([$idPrestamo]);
    if ((int)$stmt->fetchColumn() === 0) {
        throw new Exception('Préstamo inexistente.');
    }

    // Insertar observación
    $stmt = $db->prepare("
        INSERT INTO historial_observaciones (id_prestamo, dni_usuario, observacion, fecha_hora)
        VALUES (:id, :dni, :obs, NOW())
    ");
    $stmt->execute([
        ':id'  => $idPrestamo,
        ':dni' => $dniUser,
        ':obs' => $observacion,
    ]);

    $_SESSION['hist_msg']  = 'Observación agregada correctamente.';
    $_SESSION['hist_tipo'] = 'success';

} catch (Exception $e) {
    $_SESSION['hist_msg']  = 'Error al guardar la observación: ' . $e->getMessage();
    $_SESSION['hist_tipo'] = 'error';
}

// Redirigir según de dónde vino
if ($volver === 'listado') {
    header("Location: index.php");
} else {
    header("Location: detalle.php?id=$idPrestamo");
}
exit;