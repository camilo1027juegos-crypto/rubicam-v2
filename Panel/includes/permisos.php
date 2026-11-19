<?php
/* ============================================================
   permisos.php — Qué puede ver y editar cada usuario
   El administrador (rol 'admin') ve y edita TODO sin excepción.
   ============================================================ */
require_once __DIR__ . '/auth.php';

/* Permisos de un usuario como mapa: ['operaciones'=>['ver'=>true,'editar'=>false], ...] */
function cargar_permisos(int $usuario_id): array {
    global $pdo;
    $st = $pdo->prepare('SELECT seccion, puede_ver, puede_editar FROM permisos WHERE usuario_id = ?');
    $st->execute([$usuario_id]);
    $map = [];
    foreach ($st as $r) {
        $map[$r['seccion']] = [
            'ver'    => (bool)$r['puede_ver'],
            'editar' => (bool)$r['puede_editar'],
        ];
    }
    return $map;
}

/* Permisos del usuario con sesión activa (se calcula una vez) */
function permisos_actual(): array {
    static $p = null;
    if ($p === null) {
        $u = usuario_actual();
        $p = $u ? cargar_permisos((int)$u['id']) : [];
    }
    return $p;
}

function puede_ver(string $seccion): bool {
    if (es_admin()) return true;
    $p = permisos_actual();
    return !empty($p[$seccion]['ver']);
}

function puede_editar(string $seccion): bool {
    if (es_admin()) return true;
    $p = permisos_actual();
    return !empty($p[$seccion]['editar']);
}

/* Lista de secciones con permiso de ver (claves), para el dashboard */
function secciones_visibles(): array {
    $vis = [];
    foreach (secciones_todas() as $s) {
        if (puede_ver($s['clave'])) $vis[] = $s['clave'];
    }
    return $vis;
}

/* Todas las secciones definidas, ordenadas */
function secciones_todas(): array {
    global $pdo;
    static $all = null;
    if ($all === null) {
        $all = $pdo->query('SELECT clave, nombre, grupo, orden FROM secciones ORDER BY orden')->fetchAll();
    }
    return $all;
}
