<?php
/* ============================================================
   ws-chat.php — Puente entre el panel y el asistente (n8n)

   POST  ws-chat.php                       -> envía un mensaje y devuelve la respuesta
   GET   ws-chat.php?accion=conversaciones -> historial de conversaciones del usuario
   GET   ws-chat.php?accion=mensajes&sesion=ID -> mensajes de una conversación

   Qué hace en cada mensaje:
     1. Identifica al usuario y lee su rol y permisos DESDE LA BASE DE DATOS
        (nunca confía en lo que mande el navegador).
     2. Guarda el mensaje y los adjuntos, y recupera el historial reciente.
     3. Llama a n8n con una clave compartida.
     4. Guarda la respuesta y la devuelve al panel.
   ============================================================ */
require __DIR__ . '/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

/* ==================== CONFIGURACIÓN (edita aquí) ==================== */
const N8N_URL        = 'http://localhost:5678/webhook/asistente-rubicam';       // flujo ACTIVO
const N8N_URL_TEST   = 'http://localhost:5678/webhook-test/asistente-rubicam';  // modo "Execute workflow"
const N8N_USAR_TEST  = true;                       // true solo mientras pruebas en el editor de n8n
const N8N_CLAVE      = 'CAMBIA_ESTA_CLAVE_COMPARTIDA';   // la MISMA que pusiste en el nodo "Preparar contexto"
const N8N_TIMEOUT    = 120;                         // segundos
const DB_HOST        = '127.0.0.1';
const DB_NAME        = 'rubicam_db';
const DB_USER        = 'root';
const DB_PASS        = '';
const MAX_ARCHIVO_MB = 10;                          // por archivo
const MAX_TOTAL_MB   = 15;                          // por mensaje
const CARPETA_ADJ    = __DIR__ . '/uploads/chat';
const MIMES_OK       = ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif',
                        'text/plain', 'text/csv', 'text/markdown', 'application/json'];
/* ==================================================================== */

set_time_limit(N8N_TIMEOUT + 30);

function responder(array $d, int $code = 200) {
    http_response_code($code);
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function fallo(string $reply, int $code = 200, array $extra = []) {
    responder(['ok' => false, 'reply' => $reply, 'error' => $reply, 'acciones' => []] + $extra, $code);
}
function pdo(): PDO {
    static $p = null;
    if (!$p) {
        $p = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $p;
}

try {
    /* ---------- 1) Usuario (siempre releído de la BD) ---------- */
    $sess = usuario_actual();
    if (!$sess) fallo('Sesión no válida.', 401);
    if (!is_array($sess)) $sess = ['usuario' => (string)$sess];

    $db = pdo();
    $uid = $sess['id'] ?? $sess['usuario_id'] ?? null;
    if ($uid) {
        $st = $db->prepare('SELECT id, usuario, rol, nombre_completo, sucursal, activo FROM usuarios WHERE id = ?');
        $st->execute([$uid]);
    } else {
        $st = $db->prepare('SELECT id, usuario, rol, nombre_completo, sucursal, activo FROM usuarios WHERE usuario = ?');
        $st->execute([$sess['usuario'] ?? '']);
    }
    $usr = $st->fetch();
    if (!$usr || !(int)$usr['activo']) fallo('Tu usuario no está activo.', 403);

    // Soltar el bloqueo de sesión: si no, el sondeo de ws-api.php se congela mientras la IA piensa.
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

    /* ---------- 2) Permisos ---------- */
    $permisos = [];
    if ($usr['rol'] === 'admin') {
        foreach ($db->query('SELECT clave FROM secciones') as $r) $permisos[$r['clave']] = ['ver' => true, 'editar' => true];
    } else {
        $st = $db->prepare('SELECT seccion, puede_ver, puede_editar FROM permisos WHERE usuario_id = ?');
        $st->execute([$usr['id']]);
        foreach ($st as $r) $permisos[$r['seccion']] = ['ver' => (bool)$r['puede_ver'], 'editar' => (bool)$r['puede_editar']];
    }
    if (empty($permisos['chatbot']['ver'])) fallo('No tienes acceso al asistente.', 403);

    /* ---------- GET: historial ---------- */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $accion = $_GET['accion'] ?? '';
        if ($accion === 'conversaciones') {
            $st = $db->prepare('SELECT id, titulo, actualizada FROM ws_chat_conversaciones WHERE usuario_id = ? ORDER BY actualizada DESC LIMIT 50');
            $st->execute([$usr['id']]);
            responder(['ok' => true, 'conversaciones' => $st->fetchAll()]);
        }
        if ($accion === 'mensajes') {
            $sid = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($_GET['sesion'] ?? ''));
            $st = $db->prepare('SELECT m.rol, m.texto, m.acciones, m.adjuntos, m.creado_en
                                  FROM ws_chat_mensajes m JOIN ws_chat_conversaciones c ON c.id = m.conversacion_id
                                 WHERE c.id = ? AND c.usuario_id = ? ORDER BY m.id');
            $st->execute([$sid, $usr['id']]);
            $msgs = array_map(function ($m) {
                $m['acciones'] = $m['acciones'] ? json_decode($m['acciones'], true) : [];
                $m['adjuntos'] = $m['adjuntos'] ? json_decode($m['adjuntos'], true) : [];
                return $m;
            }, $st->fetchAll());
            responder(['ok' => true, 'mensajes' => $msgs]);
        }
        fallo('Acción no válida.', 400);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') fallo('Método no permitido.', 405);

    /* ---------- 3) Leer el mensaje ---------- */
    $in = json_decode(file_get_contents('php://input'), true);
    if (!is_array($in)) $in = $_POST;
    $mensaje = trim((string)($in['message'] ?? $in['mensaje'] ?? $in['texto'] ?? ''));
    $sid = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($in['sessionId'] ?? $in['sesion_id'] ?? ''));
    $sid = substr($sid, 0, 40);
    if ($sid === '') $sid = 'ws-' . bin2hex(random_bytes(8));

    // Conversación: debe ser del usuario; si es de otro, se crea una nueva.
    $st = $db->prepare('SELECT usuario_id FROM ws_chat_conversaciones WHERE id = ?');
    $st->execute([$sid]);
    $dueno = $st->fetchColumn();
    if ($dueno !== false && (int)$dueno !== (int)$usr['id']) { $sid = 'ws-' . bin2hex(random_bytes(8)); $dueno = false; }

    /* ---------- 4) Adjuntos ---------- */
    $archivosN8n = [];
    $adjMeta = [];
    $total = 0;
    foreach ((array)($in['archivos'] ?? []) as $a) {
        if (!is_array($a)) continue;
        $nombre = preg_replace('/[^\p{L}\p{N}._ ()-]/u', '_', basename((string)($a['nombre'] ?? 'archivo')));
        $mime = strtolower(trim((string)($a['mime'] ?? '')));
        $b64 = (string)($a['base64'] ?? '');
        if (preg_match('/^data:[^;]+;base64,/', $b64)) $b64 = preg_replace('/^data:[^;]+;base64,/', '', $b64);
        if (!in_array($mime, MIMES_OK, true)) {
            fallo("No puedo leer «{$nombre}» ({$mime}). Formatos admitidos: PDF, imágenes (PNG, JPG, WEBP, GIF), TXT, CSV, MD y JSON. Si es Excel o Word, expórtalo a PDF o CSV.");
        }
        $bin = base64_decode($b64, true);
        if ($bin === false || $bin === '') fallo("El archivo «{$nombre}» llegó dañado.");
        $total += strlen($bin);
        if (strlen($bin) > MAX_ARCHIVO_MB * 1048576) fallo("«{$nombre}» supera el límite de " . MAX_ARCHIVO_MB . ' MB.');
        if ($total > MAX_TOTAL_MB * 1048576) fallo('Los archivos juntos superan el límite de ' . MAX_TOTAL_MB . ' MB.');

        $dir = CARPETA_ADJ . '/' . $sid;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) fallo('No pude guardar el archivo en el servidor.', 500);
        $ruta = $dir . '/' . bin2hex(random_bytes(4)) . '_' . $nombre;
        file_put_contents($ruta, $bin);
        $archivosN8n[] = ['nombre' => $nombre, 'mime' => $mime, 'base64' => base64_encode($bin)];
        $adjMeta[] = ['nombre' => $nombre, 'mime' => $mime, 'kb' => (int)round(strlen($bin) / 1024),
                      'ruta' => 'uploads/chat/' . $sid . '/' . basename($ruta)];
    }
    if ($mensaje === '' && !$archivosN8n) fallo('Escribe un mensaje o adjunta un archivo.', 400);

    /* ---------- 5) Conversación e historial ---------- */
    if ($dueno === false) {
        $titulo = mb_substr($mensaje !== '' ? $mensaje : ($adjMeta[0]['nombre'] ?? 'Nueva conversación'), 0, 60);
        $db->prepare('INSERT INTO ws_chat_conversaciones (id, usuario_id, titulo) VALUES (?, ?, ?)')->execute([$sid, $usr['id'], $titulo]);
    }
    $st = $db->prepare('SELECT rol, texto FROM ws_chat_mensajes WHERE conversacion_id = ? ORDER BY id DESC LIMIT 12');
    $st->execute([$sid]);
    $historial = array_reverse($st->fetchAll());
    $db->prepare('INSERT INTO ws_chat_mensajes (conversacion_id, rol, texto, adjuntos) VALUES (?, "user", ?, ?)')
       ->execute([$sid, $mensaje, $adjMeta ? json_encode($adjMeta, JSON_UNESCAPED_UNICODE) : null]);

    /* ---------- 6) Llamar a n8n ---------- */
    $payload = json_encode([
        'sesion_id' => $sid,
        'usuario'   => ['id' => (int)$usr['id'], 'usuario' => $usr['usuario'], 'nombre' => $usr['nombre_completo'],
                        'rol' => $usr['rol'], 'sucursal' => $usr['sucursal']],
        'permisos'  => $permisos,
        'mensaje'   => $mensaje,
        'historial' => $historial,
        'archivos'  => $archivosN8n,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    $ch = curl_init(N8N_USAR_TEST ? N8N_URL_TEST : N8N_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => N8N_TIMEOUT,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Rubicam-Key: ' . N8N_CLAVE],
        CURLOPT_USERAGENT => 'Rubicam-Workspace-Chat/2.0',
    ]);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        error_log('[ws-chat] cURL: ' . $cerr);
        fallo('No pude conectar con la IA en este momento. Detalle: no hay respuesta de n8n (' . $cerr . '). Revisa que n8n esté encendido y el flujo activo.', 200, ['sesion_id' => $sid]);
    }
    $r = json_decode($resp, true);
    if ($http !== 200 || !is_array($r)) {
        error_log("[ws-chat] n8n HTTP $http: " . substr($resp, 0, 500));
        $hint = $http === 404 ? ' El flujo no está activo (o la URL es de prueba).' : ($http === 500 ? ' Abre n8n → Executions para ver qué nodo falló.' : '');
        fallo("No pude conectar con la IA en este momento. Detalle: n8n respondió HTTP $http." . $hint, 200, ['sesion_id' => $sid]);
    }
    if (empty($r['ok'])) {
        fallo((string)($r['error'] ?? 'El asistente no pudo responder.'), 200, ['sesion_id' => $sid]);
    }

    /* ---------- 7) Guardar y responder ---------- */
    $texto = (string)($r['respuesta'] ?? '');
    $pasos = is_array($r['acciones'] ?? null) ? $r['acciones'] : [];
    $db->prepare('INSERT INTO ws_chat_mensajes (conversacion_id, rol, texto, acciones) VALUES (?, "assistant", ?, ?)')
       ->execute([$sid, $texto, $pasos ? json_encode($pasos, JSON_UNESCAPED_UNICODE) : null]);
    $db->prepare('UPDATE ws_chat_conversaciones SET actualizada = NOW() WHERE id = ?')->execute([$sid]);

    responder([
        'ok'       => true,
        'reply'    => $texto,
        'acciones' => [],      // compatibilidad con el front actual
        'pasos'    => $pasos,  // detalle de lo que hizo el asistente (herramienta, entrada, sin_efecto)
        'sesion_id' => $sid,
    ]);
} catch (Throwable $e) {
    error_log('[ws-chat] ' . $e->getMessage());
    fallo('Ocurrió un error interno en el asistente. Avisa al administrador.', 500);
}