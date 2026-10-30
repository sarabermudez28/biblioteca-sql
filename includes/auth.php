<?php
/**
 * Autenticación de demostración y mensajes flash.
 * La sesión aquí solo guarda "¿quién inició sesión?" y el mensaje a mostrar
 * tras una acción — nunca datos del acervo, que siempre se leen de la base
 * de datos en cada página.
 */
session_start();

function lb_esta_autenticado(): bool {
    return !empty($_SESSION['lb_usuario']);
}

function lb_requerir_login(): void {
    if (!lb_esta_autenticado()) {
        header('Location: index.php');
        exit;
    }
}

function lb_usuario_actual(): ?string {
    return $_SESSION['lb_usuario'] ?? null;
}

/** Guarda un mensaje para mostrarlo una sola vez después de un redirect (patrón POST-Redirect-GET). */
function lb_flash(string $tipo, string $mensaje): void {
    $_SESSION['lb_flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function lb_tomar_flash(): ?array {
    if (empty($_SESSION['lb_flash'])) return null;
    $f = $_SESSION['lb_flash'];
    unset($_SESSION['lb_flash']);
    return $f;
}
