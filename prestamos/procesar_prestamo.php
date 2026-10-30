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
    $id_material = trim($_POST['id_material'] ?? '');
    $dni_usuario = trim($_POST['dni_alumno'] ?? '');
    $fecha_registro = date("Y-m-d H:i:s");
    $dni_bibliotecario = $_SESSION['dni_usuario'] ?? $_SESSION['dni'] ?? null;

    if (!$dni_bibliotecario) {
        throw new Exception("Sesión inválida del bibliotecario.");
    }

    $pdo->beginTransaction();

    // Validar usuario
    $stmt = $pdo->prepare("SELECT dni_usuario FROM usuario WHERE dni_usuario = ?");
    $stmt->execute([$dni_usuario]);
    if (!$stmt->fetch()) {
        throw new Exception("El DNI del usuario no está registrado.");
    }

    // Validar inventario
    $stmt = $pdo->prepare("SELECT id_material FROM inventario WHERE id_material = ?");
    $stmt->execute([$id_material]);
    if (!$stmt->fetch()) {
        throw new Exception("El material no existe en el inventario.");
    }

    // Validar disponibilidad (que no esté prestado actualmente)
    $stmt = $pdo->prepare("SELECT id_prestamo FROM prestamos WHERE id_material = ? AND fecha_hora_devolucion IS NULL");
    $stmt->execute([$id_material]);
    if ($stmt->fetch()) {
        throw new Exception("El material ya se encuentra en préstamo activo.");
    }

    // --- CÁLCULO DE LA HORA LÍMITE DE PRÉSTAMO ---
    $solo_fecha = date("Y-m-d", strtotime($fecha_registro));
    $hora_actual = date("H:i:s", strtotime($fecha_registro));

    // Si es antes de las 12:00:00 es de mañana (11:50), caso contrario es de tarde (18:50)
    if ($hora_actual < '12:00:00') {
        $fecha_hora_limite = $solo_fecha . ' 11:50:00';
    } else {
        $fecha_hora_limite = $solo_fecha . ' 18:50:00';
    }

    // Registrar préstamo incluyendo la fecha_hora_limite_prestamo calculada
    $sql = "INSERT INTO prestamos (id_material, dni_usuario, dni_bibliotecario, fecha_hora_registro, fecha_hora_devolucion, fecha_hora_limite_prestamo, id_estado_prestamo) 
            VALUES (?, ?, ?, ?, NULL, ?, 1)";
    $pdo->prepare($sql)->execute([$id_material, $dni_usuario, $dni_bibliotecario, $fecha_registro, $fecha_hora_limite]);

    $pdo->commit();
    header("Location: index.php?status=success");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Redirigir con mensaje de error por URL o sesión (Patrón PRG)
    header("Location: index.php?status=error&msg=" . urlencode($e->getMessage()));
    exit;
}