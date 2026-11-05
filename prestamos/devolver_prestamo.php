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
    $estado_devolucion = trim($_POST['estado_devolucion'] ?? ''); // Recibido del semáforo ('verde', 'amarillo', 'rojo')

    if (empty($id_prestamo) || empty($estado_devolucion)) {
        throw new Exception("Datos incompletos para procesar la devolución.");
    }

    // Iniciar transacción para asegurar consistencia
    $pdo->beginTransaction();

    // 1. Verificar que el préstamo exista, no haya sido devuelto y obtener el dni_usuario
    $stmtCheck = $pdo->prepare("SELECT id_prestamo, id_material, dni_usuario FROM prestamos WHERE id_prestamo = ? AND fecha_hora_devolucion IS NULL");
    $stmtCheck->execute([$id_prestamo]);
    $prestamo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$prestamo) {
        throw new Exception("El préstamo seleccionado no existe o ya figura como devuelto.");
    }

    $dni_usuario = $prestamo['dni_usuario'];

    // 2. Definir los puntos y motivo según el color del semáforo seleccionado
    $puntos_cambio = 0;
    $motivo = "";

    switch ($estado_devolucion) {
        case 'verde':
            $puntos_cambio = 5;
            $motivo = "Devolución en buenas condiciones, en tiempo y forma (Semáforo Verde)";
            break;
        case 'amarillo':
            $puntos_cambio = -5;
            $motivo = "Devolución con tardanza (Semáforo Amarillo)";
            break;
        case 'rojo':
            $puntos_cambio = -30;
            $motivo = "Material roto, robado o desaparecido (Semáforo Rojo)";
            break;
        default:
            throw new Exception("Estado de semáforo no válido.");
    }

    // 3. Preparar los datos de devolución
    $fecha_devolucion = date("Y-m-d H:i:s"); // Formato exacto compatible con DATETIME de MySQL
    $id_estado_prestamo = 1; // Ajusta según tu tabla de estados

    // 4. Ejecutar la actualización del préstamo
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

    // 5. Actualizar los puntos en la única tabla de usuarios usando el DNI
    // (Asegúrate de que 'usuarios' sea el nombre real de tu tabla y 'dni' el nombre de la columna del documento)
    // 5. Actualizar los puntos en la única tabla de usuarios usando el DNI
    // LEAST(100, ...) evita que supere los 100 puntos
    // GREATEST(0, ...) evita que baje de 0 puntos
    $stmtUpdatePuntos = $pdo->prepare("UPDATE usuario SET puntos = LEAST(100, GREATEST(0, puntos + ?)) WHERE dni_usuario = ?");
    $stmtUpdatePuntos->execute([$puntos_cambio, $dni_usuario]);


    // 6. Registrar el movimiento en la tabla de auditoría (historial_puntos)
    $stmtHistorial = $pdo->prepare("INSERT INTO historial_puntos (dni_usuario, id_prestamo, puntos_cambio, motivo, fecha_hora) VALUES (?, ?, ?, ?, ?)");
    $stmtHistorial->execute([$dni_usuario, $id_prestamo, $puntos_cambio, $motivo, $fecha_devolucion]);

    // Confirmar toda la transacción
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
