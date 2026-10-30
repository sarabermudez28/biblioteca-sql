<?php
require_once __DIR__ . '/../includes/auth.php';
lb_requerir_login();
require_once '../conexion.php';
require_once 'obtener_inventario.php'; // Para acceder a la matriz $categorias_config

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_material = $_POST['id_material'] ?? '';
    $cat_key = $_POST['cat'] ?? 'libros';
    $id_estado_material = $_POST['id_estado_material'] ?? null;
    
    $col1 = trim($_POST['col1'] ?? '');
    $col2 = trim($_POST['col2'] ?? '');
    $col3 = trim($_POST['col3'] ?? '');

    if (empty($id_material) || !isset($categorias_config[$cat_key])) {
        header('Location: index.php?error=datos_invalidos');
        exit;
    }

    try {
        $db = db();
        $db->beginTransaction();

        // 1. Actualizar el estado general en la tabla 'inventario'
        $stmt_inv = $db->prepare("UPDATE inventario SET id_estado_material = ? WHERE id_material = ?");
        $stmt_inv->execute([$id_estado_material, $id_material]);

        // 2. Actualizar la tabla específica según la categoría activa
        switch ($cat_key) {
            case 'libros':
                $stmt_sub = $db->prepare("UPDATE libros SET nombre = ?, autor = ?, genero = ? WHERE id_material = ?");
                $stmt_sub->execute([$col1, $col2, $col3, $id_material]);
                break;
            
            case 'instrumentos_medicion':
                // Columna 3 está inactiva/vacía en esta categoría
                $stmt_sub = $db->prepare("UPDATE instrumentos_medicion SET tipo = ?, longitud_cm = ? WHERE id_material = ?");
                $stmt_sub->execute([$col1, $col2 !== '' ? $col2 : null, $id_material]);
                break;

            case 'materiales_didacticos':
                $stmt_sub = $db->prepare("UPDATE materiales_didacticos SET tipo = ?, tematica = ?, piezas = ? WHERE id_material = ?");
                $stmt_sub->execute([$col1, $col2, $col3 !== '' ? $col3 : null, $id_material]);
                break;

            case 'materiales_geograficos':
                $stmt_sub = $db->prepare("UPDATE materiales_geograficos SET tipo = ?, region = ?, escala = ? WHERE id_material = ?");
                $stmt_sub->execute([$col1, $col2, $col3, $id_material]);
                break;

            case 'materiales_dibujo_tecnico':
                // Columna 3 está inactiva/vacía en esta categoría
                $stmt_sub = $db->prepare("UPDATE materiales_dibujo_tecnico SET tipo = ?, descripcion = ? WHERE id_material = ?");
                $stmt_sub->execute([$col1, $col2, $id_material]);
                break;
        }

        $db->commit();
        header('Location: index.php?cat=' . $cat_key . '&success=actualizado');
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        header('Location: index.php?cat=' . $cat_key . '&error=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}