<?php
require_once __DIR__ . '/../conexion.php'; // Ajusta la ruta a tu conexión

header('Content-Type: application/json');

$dni = $_GET['dni'] ?? '';

if (empty($dni)) {
    echo json_encode(['error' => 'DNI no proporcionado']);
    exit;
}

try {
    $pdo = db();

    // 1. Obtener nombre, apellido y puntaje actual del usuario
    $stmtUser = $pdo->prepare("SELECT nombre, apellido, puntos FROM usuario WHERE dni_usuario = ?");
    $stmtUser->execute([$dni]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }

    // 2. Contar cuántos verdes, amarillos y rojos tiene en el historial de puntos
    $stmtHistorial = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN motivo LIKE '%Semáforo Verde%' THEN 1 ELSE 0 END) as verdes,
            SUM(CASE WHEN motivo LIKE '%Semáforo Amarillo%' THEN 1 ELSE 0 END) as amarillos,
            SUM(CASE WHEN motivo LIKE '%Semáforo Rojo%' THEN 1 ELSE 0 END) as rojos
        FROM historial_puntos 
        WHERE dni_usuario = ?
    ");
    $stmtHistorial->execute([$dni]);
    $estadisticas = $stmtHistorial->fetch(PDO::FETCH_ASSOC);

    // Devolver los datos incluyendo nombre y apellido
    echo json_encode([
        'nombre' => $usuario['nombre'],
        'apellido' => $usuario['apellido'],
        'puntos' => intval($usuario['puntos']),
        'verdes' => intval($estadisticas['verdes'] ?? 0),
        'amarillos' => intval($estadisticas['amarillos'] ?? 0),
        'rojos' => intval($estadisticas['rojos'] ?? 0)
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}