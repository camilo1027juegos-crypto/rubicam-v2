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
    return mapa_permisos($st);
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
    return permiso_permitido(es_admin(), permisos_actual(), $seccion, 'ver');
}

function puede_editar(string $seccion): bool {
    return permiso_permitido(es_admin(), permisos_actual(), $seccion, 'editar');
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
