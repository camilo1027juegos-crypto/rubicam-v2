<?php
/* ============================================================
   seguridad.php — Funciones puras de seguridad y permisos
   No dependen de la base de datos, por eso se pueden probar
   de forma aislada (tests/php). auth.php y permisos.php las usan.
   ============================================================ */

/* Prefijo de ruta: '' en la raíz, '../' dentro de /admin/ */
function base_rel(): string {
    return (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false) ? '../' : '';
}

/* Token CSRF para proteger los formularios */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_valido(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && is_string($_POST['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/* Atajo para escapar texto al imprimirlo en HTML */
function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* Convierte filas (seccion, puede_ver, puede_editar) en un mapa de permisos */
function mapa_permisos(iterable $filas): array {
    $map = [];
    foreach ($filas as $r) {
        $map[$r['seccion']] = [
            'ver'    => (bool)$r['puede_ver'],
            'editar' => (bool)$r['puede_editar'],
        ];
    }
    return $map;
}

/* Regla de acceso: el admin puede todo; los demás según su mapa. $tipo = 'ver' | 'editar' */
function permiso_permitido(bool $esAdmin, array $mapa, string $seccion, string $tipo): bool {
    if ($esAdmin) return true;
    return !empty($mapa[$seccion][$tipo]);
}
