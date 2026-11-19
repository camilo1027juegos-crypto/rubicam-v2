<?php
/* ============================================================
   ws-api.php — API de datos del dashboard (lee/escribe en MySQL)
   Recibe JSON por POST: { op, col, id, data }
     op = 'listall'  -> devuelve todos los módulos que el usuario puede ver
          'list'     -> una colección
          'set'      -> crear/actualizar un documento (requiere permiso de editar)
          'delete'   -> borrar un documento (requiere permiso de editar)
   Respeta los permisos por sección definidos en el panel.
   ============================================================ */
require __DIR__ . '/includes/permisos.php';
require __DIR__ . '/config/ws_schema.php';
require __DIR__ . '/includes/ws-mapeo.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function salir($arr, int $http = 200) { http_response_code($http); echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

if (!usuario_actual()) salir(['ok'=>false, 'code'=>'unauthenticated', 'error'=>'Sesión no válida.'], 401);

$SCHEMA = ws_schema();
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$op  = $in['op']  ?? '';
$col = $in['col'] ?? '';

/* fila_a_doc() y doc_a_fila() están en includes/ws-mapeo.php */

/* --- Lee una colección (si el usuario tiene permiso de ver) --- */
function leer_coleccion(string $col, array $def): array {
    global $pdo;
    $sec = $def['seccion'];
    if ($sec !== null && !puede_ver($sec)) return [];      // oculta: devuelve vacío
    try {
        $rows = $pdo->query("SELECT * FROM {$def['tabla']}")->fetchAll();
    } catch (PDOException $e) {
        return [];   // la tabla aún no existe (falta importar dashboard_modulos.sql)
    }
    $out = [];
    foreach ($rows as $r) $out[] = ['id'=>$r['id'], 'data'=>fila_a_doc($r, $def)];
    return $out;
}

if ($op === 'listall') {
    $res = [];
    foreach ($SCHEMA as $c => $def) $res[$c] = leer_coleccion($c, $def);
    salir(['ok'=>true, 'data'=>$res]);
}

if (!isset($SCHEMA[$col])) salir(['ok'=>false, 'code'=>'invalid_argument', 'error'=>'Colección desconocida.'], 400);
$def = $SCHEMA[$col];
$sec = $def['seccion'];

if ($op === 'list') {
    salir(['ok'=>true, 'data'=>leer_coleccion($col, $def)]);
}

if ($op === 'set' || $op === 'delete') {
    // Permiso de edición (la colección 'actividad' no tiene sección: la puede escribir cualquiera con sesión)
    if ($sec !== null && !puede_editar($sec)) {
        salir(['ok'=>false, 'code'=>'invalid_argument', 'error'=>'No tienes permiso para editar esta sección.'], 403);
    }
    $id = (string)($in['id'] ?? '');
    if ($id === '') salir(['ok'=>false, 'code'=>'invalid_argument', 'error'=>'Falta el id.'], 400);

    try {
        if ($op === 'delete') {
            $pdo->prepare("DELETE FROM {$def['tabla']} WHERE id = ?")->execute([$id]);
            salir(['ok'=>true]);
        }
        // set: separar columnas conocidas del resto (que va a "extra")
        $data = is_array($in['data'] ?? null) ? $in['data'] : [];
        $fila = doc_a_fila($id, $data, $def);
        $marcas = implode(',', array_fill(0, count($fila['campos']), '?'));
        $sql = "REPLACE INTO {$def['tabla']} (" . implode(',', $fila['campos']) . ") VALUES ($marcas)";
        $pdo->prepare($sql)->execute($fila['valores']);
        salir(['ok'=>true]);
    } catch (PDOException $e) {
        salir(['ok'=>false, 'code'=>'unavailable', 'error'=>'Error de base de datos: ' . $e->getMessage()], 500);
    }
}

salir(['ok'=>false, 'code'=>'invalid_argument', 'error'=>'Operación no reconocida.'], 400);
