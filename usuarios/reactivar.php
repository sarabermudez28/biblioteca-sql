
<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

lb_requerir_login();

$usuarioActual = lb_usuario_actual();

// Verificar administrador
$stmt = $pdo->prepare("
    SELECT id_rol
    FROM usuario
    WHERE gmail = ? AND activo = 1
");

$stmt->execute([$usuarioActual]);
$administrador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$administrador || (int)$administrador['id_rol'] !== 1) {
    header('Location: index.php?error=' . urlencode('No tenés permiso para realizar esta acción.'));
    exit;
}

// Solo se permite reactivar mediante POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$dni = trim($_POST['dni'] ?? '');

if ($dni === '') {
    header('Location: index.php');
    exit;
}

try {

    $stmt = $pdo->prepare("
        UPDATE usuario
        SET activo = 1
        WHERE dni_usuario = ?
        AND id_rol IN (3, 4)
        AND activo = 0
    ");

    $stmt->execute([$dni]);

    if ($stmt->rowCount() === 1) {

        header('Location: index.php?mensaje=' . urlencode('El usuario fue reactivado correctamente.'));

    } else {

        header('Location: index.php?error=' . urlencode('No se pudo reactivar el usuario. Verificá su estado.'));

    }

    exit;

} catch (PDOException $e) {

    header('Location: index.php?error=' . urlencode('Ocurrió un error al reactivar el usuario.'));
    exit;

}
