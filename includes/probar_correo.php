
<?php

require_once __DIR__ . '/correo.php';

// Correo de destino para realizar la prueba
$destinatario = 'jasmine.rivero.d@gmail.com';

$asunto = 'Prueba de correo - Sistema Biblioteca';

$mensaje = '
    <h2>Prueba de correo exitosa</h2>
    <p>El sistema de biblioteca pudo conectarse correctamente con Gmail.</p>
    <p>El envío de notificaciones está funcionando.</p>
';

try {
    $resultado = enviarCorreo(
        $destinatario,
        $asunto,
        $mensaje
    );

    echo '<h2>Correo enviado correctamente</h2>';

} catch (Exception $e) {

    echo '<h2>Error al enviar el correo</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';

}
