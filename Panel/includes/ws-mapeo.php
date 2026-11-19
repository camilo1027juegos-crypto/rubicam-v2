<?php
/* ============================================================
   ws-mapeo.php — Conversión entre filas de MySQL y documentos
   del dashboard. Funciones puras (sin BD), probadas en tests/php.
   Tipos de columna (ws_schema.php): s = texto · b = booleano · i = número
   ============================================================ */

/* Fila de la BD -> documento que espera el dashboard */
function fila_a_doc(array $r, array $def): array {
    $doc = [];
    if (!empty($r['extra'])) {
        $x = json_decode($r['extra'], true);
        if (is_array($x)) $doc = $x;
    }
    foreach ($def['cols'] as $name => $type) {
        $v = $r[$name] ?? null;
        if ($type === 'b')      $doc[$name] = (bool)$v;
        elseif ($v === null)    { /* se omite para dejar el documento limpio */ }
        elseif ($type === 'i')  $doc[$name] = (int)$v;
        else                    $doc[$name] = (string)$v;
    }
    unset($doc['id']);
    return $doc;
}

/* Documento del dashboard -> columnas y valores para REPLACE INTO.
   Lo que no es columna conocida se guarda en el JSON "extra".
   Devuelve ['campos' => [...], 'valores' => [...]] */
function doc_a_fila(string $id, array $data, array $def): array {
    $extra  = $data;
    $campos = ['id']; $vals = [$id];
    foreach ($def['cols'] as $name => $type) {
        unset($extra[$name]);
        $v = $data[$name] ?? null;
        if ($type === 'b')       $v = !empty($v) ? 1 : 0;
        elseif ($type === 'i')   $v = ($v === null || $v === '') ? null : (int)$v;
        else                     $v = ($v === null) ? null : (string)$v;
        $campos[] = $name; $vals[] = $v;
    }
    unset($extra['id']);
    $campos[] = 'extra';
    $vals[] = $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null;
    return ['campos' => $campos, 'valores' => $vals];
}
