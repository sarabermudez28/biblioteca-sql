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
    $dni_profesor = trim($_POST['dni_profesor'] ?? '');
    $fecha_registro = date("Y-m-d H:i:s");
    $dni_bibliotecario = $_SESSION['dni_usuario'] ?? $_SESSION['dni'] ?? null;

    if (!$dni_bibliotecario) {
        throw new Exception("Sesión inválida del bibliotecario.");
    }

    $pdo->beginTransaction();

    // Validar usuario solicitante
    $stmt = $pdo->prepare("SELECT dni_usuario FROM usuario WHERE dni_usuario = ?");
    $stmt->execute([$dni_usuario]);
    if (!$stmt->fetch()) {
        throw new Exception("El DNI del usuario no está registrado.");
    }

    // Validar profesor a cargo (si se ingresó)
    if (!empty($dni_profesor)) {
        $stmtProf = $pdo->prepare("SELECT dni_usuario FROM usuario WHERE dni_usuario = ? AND id_rol = 3");
        $stmtProf->execute([$dni_profesor]);
        if (!$stmtProf->fetch()) {
            throw new Exception("El DNI ingresado para el profesor a cargo no corresponde a un profesor registrado.");
        }
    }

    // Validar inventario y estado del material
    $stmt = $pdo->prepare("SELECT i.id_material, i.id_estado_material, em.desc_estado_material 
                           FROM inventario i 
                           JOIN estado_material em ON i.id_estado_material = em.id_estado_material 
                           WHERE i.id_material = ?");
    $stmt->execute([$id_material]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$material) {
        throw new Exception("El material no existe en el inventario.");
    }

    // ==========================================
    // PUNTO 2: VALIDACIÓN DE ESTADO "ROTO"
    // ==========================================
    if ($material['id_estado_material'] == 2 || strtolower($material['desc_estado_material']) === 'roto') {
        $stmtRec = $pdo->prepare("SELECT id_material FROM inventario WHERE id_estado_material IN (1, 3) AND id_material != ? LIMIT 3");
        $stmtRec->execute([$id_material]);
        $recomendados = $stmtRec->fetchAll(PDO::FETCH_COLUMN);
        
        $msgSugerencia = !empty($recomendados) 
            ? " Materiales sugeridos en buen estado: " . implode(', ', $recomendados) 
            : " No hay otros materiales disponibles en este momento.";

        throw new Exception("¡Atención! El material seleccionado está roto y no se puede prestar." . $msgSugerencia);
    }

    // Validar disponibilidad
    $stmt = $pdo->prepare("SELECT id_prestamo FROM prestamos WHERE id_material = ? AND fecha_hora_devolucion IS NULL");
    $stmt->execute([$id_material]);
    if ($stmt->fetch()) {
        throw new Exception("El material ya se encuentra en préstamo activo.");
    }

    // --- CÁLCULO DE LA HORA LÍMITE DE PRÉSTAMO ---
    $solo_fecha = date("Y-m-d", strtotime($fecha_registro));
    $hora_actual = date("H:i:s", strtotime($fecha_registro));

    if ($hora_actual < '12:00:00') {
        $fecha_hora_limite = $solo_fecha . ' 11:50:00';
    } else {
        $fecha_hora_limite = $solo_fecha . ' 18:50:00';
    }

    // Registrar préstamo (Manejo seguro por si la columna dni_profesor existe o no en la BD)
    $stmtCol = $pdo->query("SHOW COLUMNS FROM prestamos LIKE 'dni_profesor'");
    if (!$stmtCol->fetch()) {
        $pdo->exec("ALTER TABLE prestamos ADD COLUMN dni_profesor INT DEFAULT NULL");
    }

    $sql = "INSERT INTO prestamos (id_material, dni_usuario, dni_profesor, dni_bibliotecario, fecha_hora_registro, fecha_hora_devolucion, fecha_hora_limite_prestamo, id_estado_prestamo) 
            VALUES (?, ?, ?, ?, ?, NULL, ?, 1)";
    $pdo->prepare($sql)->execute([$id_material, $dni_usuario, ($dni_profesor !== '' ? $dni_profesor : null), $dni_bibliotecario, $fecha_registro, $fecha_hora_limite]);

    $pdo->commit();
    header("Location: index.php?status=success");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header("Location: index.php?status=error&msg=" . urlencode($e->getMessage()));
    exit;
}