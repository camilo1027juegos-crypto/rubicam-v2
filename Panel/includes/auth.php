<?php
/* ============================================================
   auth.php — Sesión, usuario actual y guardias de acceso
   Incluye este archivo al principio de cualquier página protegida.
   ============================================================ */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/seguridad.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* Devuelve el usuario con sesión activa (o null) */
function usuario_actual() {
    global $pdo;
    static $cache = null;
    if ($cache !== null) return $cache ?: null;
    if (empty($_SESSION['uid'])) { $cache = false; return null; }
    $st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? AND activo = 1');
    $st->execute([$_SESSION['uid']]);
    $cache = $st->fetch() ?: false;
    return $cache ?: null;
}

function es_admin(): bool {
    $u = usuario_actual();
    return $u && $u['rol'] === 'admin';
}

/* Obliga a tener sesión; si no, manda al login */
function requerir_login(): void {
    if (!usuario_actual()) {
        header('Location: ' . base_rel() . 'login.php');
        exit;
    }
}

/* Obliga a ser administrador */
function requerir_admin(): void {
    requerir_login();
    if (!es_admin()) {
        http_response_code(403);
        die('Acceso restringido: esta sección es solo para administradores.');
    }
}

/* csrf_token(), csrf_valido(), h() y base_rel() están en seguridad.php */
