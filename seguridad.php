<?php
session_start();

require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['dni_usuario'])) {
    header('Location: ../index.php');
    exit;
}

$pdo = db();

$stmt = $pdo->prepare("
    SELECT dni_usuario, nombre, apellido, id_rol
    FROM usuario
    WHERE dni_usuario = ?
    AND activo = 1
    AND id_rol IN (1, 5)
");

$stmt->execute([$_SESSION['dni_usuario']]);
$usuario_actual = $stmt->fetch();

if (!$usuario_actual) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// Solo el administrador puede realizar cambios de puntos o bajas.
$es_admin = ($usuario_actual['id_rol'] == 1);
?>