<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();

require_once __DIR__ . '/../conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$pdo = db();

try {
    $id_prestamo = trim($_POST['id_prestamo'] ?? '');
    $estado_devolucion = trim($_POST['estado_devolucion'] ?? '');

    if (empty($id_prestamo) || empty($estado_devolucion)) {
        throw new Exception("Datos incompletos para procesar la devolución.");
    }

    $pdo->beginTransaction();

    $stmtCheck = $pdo->prepare("SELECT id_prestamo, id_material, dni_usuario FROM prestamos WHERE id_prestamo = ? AND fecha_hora_devolucion IS NULL");
    $stmtCheck->execute([$id_prestamo]);
    $prestamo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$prestamo) {
        throw new Exception("El préstamo seleccionado no existe o ya figura como devuelto.");
    }

    $dni_usuario = $prestamo['dni_usuario'];
    $id_material = $prestamo['id_material'];

    $puntos_cambio = 0;
    $motivo = "";
    $id_estado_material = 1;

    switch ($estado_devolucion) {
        case 'verde':
            $puntos_cambio = 5;
            $motivo = "Devolución en buenas condiciones (Verde)";
            $id_estado_material = 1;
            break;
        case 'amarillo':
            $puntos_cambio = -5;
            $motivo = "Devolución con tardanza (Amarillo)";
            $id_estado_material = 1;
            break;
        case 'rojo':
            $puntos_cambio = -30;
            $motivo = "Material roto o desaparecido (Rojo)";
            $id_estado_material = 2;
            break;
        default:
            throw new Exception("Estado de semáforo no válido.");
    }

    $fecha_devolucion = date("Y-m-d H:i:s");
    $id_estado_prestamo = 1;

    $sqlUpdate = "UPDATE prestamos 
                  SET fecha_hora_devolucion = :fecha_dev, 
                      id_estado_prestamo = :id_estado 
                  WHERE id_prestamo = :id_prestamo";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ":fecha_dev" => $fecha_devolucion,
        ":id_estado" => $id_estado_prestamo,
        ":id_prestamo" => $id_prestamo
    ]);

    $stmtMaterial = $pdo->prepare("UPDATE inventario SET id_estado_material = ? WHERE id_material = ?");
    $stmtMaterial->execute([$id_estado_material, $id_material]);

    // LÍMITE DE PUNTOS: LEAST(100, ...) evita que suba de 100, y GREATEST(0, ...) evita que baje de 0
    $stmtUpdatePuntos = $pdo->prepare("UPDATE usuario SET puntos = GREATEST(0, LEAST(100, puntos + ?)) WHERE dni_usuario = ?");
    $stmtUpdatePuntos->execute([$puntos_cambio, $dni_usuario]);

    $stmtHistorial = $pdo->prepare("INSERT INTO historial_puntos (dni_usuario, id_prestamo, puntos_cambio, motivo, fecha_hora) VALUES (?, ?, ?, ?, ?)");
    $stmtHistorial->execute([$dni_usuario, $id_prestamo, $puntos_cambio, $motivo, $fecha_devolucion]);

    $pdo->commit();

    header("Location: index.php?status_devolucion=success_devuelto");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // MUESTRA EL ERROR EN PANTALLA EN VEZ DE OCULTARLO
    echo "<div style='background: #ffe6e6; color: #d00; padding: 20px; margin: 20px; border: 2px solid #d00; font-family: monospace; font-size: 16px;'>";
    echo "<h3>⚠️ Error capturado por el sistema:</h3>";
    echo "<p><b>Mensaje:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>Línea:</b> " . $e->getLine() . "</p>";
    echo "</div>";
    exit;
}