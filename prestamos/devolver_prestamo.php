<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();

require_once __DIR__ . '/../conexion.php';

// Validar que la petición sea exclusivamente por POST por seguridad
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$pdo = db();

try {
    $id_prestamo = trim($_POST['id_prestamo'] ?? '');

    if (empty($id_prestamo)) {
        throw new Exception("Identificador de préstamo no válido.");
    }

    // Iniciar transacción para asegurar consistencia
    $pdo->beginTransaction();

    // 1. Verificar que el préstamo exista y aún no haya sido devuelto
    $stmtCheck = $pdo->prepare("SELECT id_prestamo, id_material FROM prestamos WHERE id_prestamo = ? AND fecha_hora_devolucion IS NULL");
    $stmtCheck->execute([$id_prestamo]);
    $prestamo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$prestamo) {
        throw new Exception("El préstamo seleccionado no existe o ya figura como devuelto.");
    }

    // 2. Preparar los datos de devolución
    $fecha_devolucion = date("Y-m-d H:i:s"); // Formato exacto compatible con DATETIME de MySQL
    $id_estado_prestamo = 1; // Ajusta según tu tabla de estados (ej. 1 o el ID que represente devuelto/disponible)
    
    // 3. Ejecutar la actualización de ambos campos de forma explícita
    $sqlUpdate = "UPDATE prestamos 
                  SET fecha_hora_devolucion = :fecha_dev, 
                      id_estado_prestamo = :id_estado 
                  WHERE id_prestamo = :id_prestamo";

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $resultado = $stmtUpdate->execute([
        ":fecha_dev" => $fecha_devolucion,
        ":id_estado" => $id_estado_prestamo,
        ":id_prestamo" => $id_prestamo
    ]);

    if (!$resultado) {
        throw new Exception("No se pudo actualizar el registro de devolución en la base de datos.");
    }

    $pdo->commit();

    // Redirección limpia de éxito
    header("Location: index.php?status_devolucion=success_devuelto");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Redirigir capturando el mensaje exacto para visualizarlo en pantalla
    header("Location: index.php?status_devolucion=error&msg=" . urlencode($e->getMessage()));
    exit;
}