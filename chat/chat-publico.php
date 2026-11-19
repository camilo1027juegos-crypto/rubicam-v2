<?php
/* ============================================================
   chat-publico.php — Puente del asistente público del sitio (n8n)

   POST chat-publico.php                  -> mensaje del visitante -> respuesta de la IA
   POST chat-publico.php?accion=contacto  -> formulario "Hablar con un asesor"

   No exige login (es público), así que se protege con:
     - límites por IP y límite diario global (cuida tu cupo de la API),
     - validación de origen, de tamaño y de formato,
     - el asistente de n8n NO tiene acceso a datos internos.
   No guarda las conversaciones; solo guarda lo que el visitante envía por el formulario.
   ============================================================ */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

/* ==================== CONFIGURACIÓN (edita aquí) ==================== */
const N8N_URL        = 'http://localhost:5678/webhook/chat-publico-rubicam';
const N8N_URL_TEST   = 'http://localhost:5678/webhook-test/chat-publico-rubicam';
const N8N_USAR_TEST  = true;const N8N_CLAVE      = 'CAMBIA_CLAVE_PUBLICA';     // la MISMA que en el nodo "Preparar contexto" del flujo público
const N8N_TIMEOUT    = 60;

// Límites (protegen la cuota de Gemini y evitan abuso)
const LIM_MSG_IP     = 12;      // mensajes por IP ...
const LIM_MSG_SEG    = 300;     // ... cada 5 minutos
const LIM_DIA_IP     = 60;      // mensajes por IP al día
const LIM_DIA_TOTAL  = 1500;    // mensajes del sitio completo al día (ajústalo a tu plan de Gemini)
const LIM_CONTACTO   = 3;       // formularios por IP por hora

const MAX_MENSAJE    = 600;
const MAX_HISTORIAL  = 6;
const KB_ARCHIVO     = __DIR__ . '/base-conocimiento.md';
const CACHE_DIR      = __DIR__ . '/_cache';

// Formulario de contacto -> base de datos (ver web_leads.sql)
const GUARDAR_BD     = true;
const AVISO_EN_PANEL = true;    // crea un aviso en el Inicio del panel por cada contacto nuevo
const DB_HOST        = '127.0.0.1';
const DB_NAME        = 'rubicam_db';
const DB_USER        = 'rubicam_web';
const DB_PASS        = 'CAMBIA_CLAVE_WEB';
/* ==================================================================== */

const TXT = [
    'es' => ['limite' => 'Has enviado muchos mensajes seguidos. Espera unos minutos o déjanos tus datos con «Hablar con un asesor».',
             'ocupado' => 'Estamos recibiendo muchas consultas ahora mismo. Intenta de nuevo en unos minutos o escríbenos desde la sección Contáctanos.',
             'error' => 'No pude responder en este momento. Puedes intentarlo de nuevo o escribirnos desde la sección Contáctanos.',
             'vacio' => 'Escribe tu pregunta para poder ayudarte.', 'ok' => '¡Gracias! Un asesor te contactará pronto.'],
    'en' => ['limite' => 'You have sent many messages in a row. Please wait a few minutes or leave your details with "Talk to an advisor".',
             'ocupado' => 'We are receiving many requests right now. Please try again in a few minutes or write to us from the Contact section.',
             'error' => 'I could not answer right now. Please try again or write to us from the Contact section.',
             'vacio' => 'Type your question so I can help you.', 'ok' => 'Thank you! An advisor will contact you soon.'],
    'de' => ['limite' => 'Sie haben viele Nachrichten hintereinander gesendet. Bitte warten Sie einige Minuten oder hinterlassen Sie Ihre Daten über „Mit einem Berater sprechen“.',
             'ocupado' => 'Wir erhalten gerade viele Anfragen. Bitte versuchen Sie es in einigen Minuten erneut oder schreiben Sie uns im Bereich Kontakt.',
             'error' => 'Ich konnte gerade nicht antworten. Bitte versuchen Sie es erneut oder schreiben Sie uns im Bereich Kontakt.',
             'vacio' => 'Schreiben Sie Ihre Frage, damit ich helfen kann.', 'ok' => 'Vielen Dank! Ein Berater wird sich bald bei Ihnen melden.'],
];
$IDIOMA = 'es';

function t(string $k): string { global $IDIOMA; return TXT[$IDIOMA][$k] ?? TXT['es'][$k]; }
function responder(array $d, int $code = 200) {
    http_response_code($code);
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function fallo(string $msg, int $code = 200) { responder(['ok' => false, 'error' => $msg, 'respuesta' => $msg], $code); }
function limpiar($s, int $max): string {
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', ' ', (string)$s);
    return trim(mb_substr((string)$s, 0, $max));
}

/* Límite por ventana, con archivo bloqueado (sin base de datos). Devuelve false si se excede. */
function limitar(string $clave, int $max, int $ventana): bool {
    if (!is_dir(CACHE_DIR)) {
        @mkdir(CACHE_DIR, 0775, true);
        @file_put_contents(CACHE_DIR . '/.htaccess', "Require all denied\n");
    }
    $f = fopen(CACHE_DIR . '/rl_' . md5($clave) . '.json', 'c+');
    if (!$f) return true; // si no se puede limitar, no bloquear al visitante
    flock($f, LOCK_EX);
    $ahora = time();
    $raw = stream_get_contents($f);
    $arr = array_values(array_filter(json_decode($raw ?: '[]', true) ?: [], fn($x) => $x > $ahora - $ventana));
    $ok = count($arr) < $max;
    if ($ok) $arr[] = $ahora;
    ftruncate($f, 0); rewind($f); fwrite($f, json_encode($arr));
    flock($f, LOCK_UN); fclose($f);
    return $ok;
}

/* ---------- Solo POST, solo desde el mismo sitio ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fallo('Método no permitido.', 405);
$origen = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origen !== '') {
    $hostOrigen = parse_url($origen, PHP_URL_HOST) . (parse_url($origen, PHP_URL_PORT) ? ':' . parse_url($origen, PHP_URL_PORT) : '');
    if (strcasecmp($hostOrigen, $_SERVER['HTTP_HOST'] ?? '') !== 0) fallo('Origen no permitido.', 403);
}
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) fallo('Solicitud no válida.', 400);
$IDIOMA = in_array($in['idioma'] ?? '', ['es', 'en', 'de'], true) ? $in['idioma'] : 'es';
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$accion = $_GET['accion'] ?? 'mensaje';

try {
    /* ======================= FORMULARIO DE CONTACTO ======================= */
    if ($accion === 'contacto') {
        if (!empty($in['web'])) responder(['ok' => true, 'respuesta' => t('ok')]);          // honeypot: los bots lo llenan
        $nombre = limpiar($in['nombre'] ?? '', 100);
        $email  = limpiar($in['email'] ?? '', 150);
        $tel    = preg_replace('/[^0-9+() -]/', '', (string)($in['telefono'] ?? ''));
        $tel    = substr($tel, 0, 30);
        $emp    = limpiar($in['empresa'] ?? '', 150);
        $msg    = limpiar($in['mensaje'] ?? '', 1000);
        if (mb_strlen($nombre) < 2) fallo('nombre', 422);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fallo('email', 422);
        if (empty($in['acepto'])) fallo('acepto', 422);
        $pagina = limpiar($in['pagina'] ?? '', 200);
        // El límite se cuenta solo con formularios válidos (un error de tipeo no debe bloquear al visitante)
        if (!limitar("contacto|$ip", LIM_CONTACTO, 3600)) fallo(t('limite'), 429);

        $id = null;
        if (GUARDAR_BD) {
            try {
                $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
                              [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $db->prepare('INSERT INTO web_leads (nombre, empresa, email, telefono, mensaje, idioma, pagina, ip) VALUES (?,?,?,?,?,?,?,?)')
                   ->execute([$nombre, $emp ?: null, $email, $tel ?: null, $msg ?: null, $IDIOMA, $pagina ?: null, $ip]);
                $id = (int)$db->lastInsertId();
                if (AVISO_EN_PANEL) {
                    // El aviso se muestra en el panel interno: se quitan <, >, comillas y comillas invertidas por seguridad (XSS).
                    $seguro = fn(string $x): string => preg_replace('/[<>"`]/u', '', $x);
                    $nombreA = $seguro($nombre); $empA = $seguro($emp);
                    $db->prepare('INSERT INTO ws_avisos (id, texto, fijado, fecha, extra) VALUES (?,?,0,?,?)')->execute([
                        'web' . bin2hex(random_bytes(5)),
                        "📩 Nuevo contacto desde el sitio web: $nombreA" . ($empA ? " ($empA)" : '') . " · $email" . ($tel ? " · $tel" : ''),
                        date('Y-m-d'), json_encode(['origen' => 'web', 'lead_id' => $id], JSON_UNESCAPED_UNICODE)]);
                }
            } catch (Throwable $e) {
                error_log('[chat-publico] BD: ' . $e->getMessage());
                $id = null;
            }
        }
        if ($id === null) {   // respaldo: que ningún contacto se pierda
            if (!is_dir(CACHE_DIR)) { @mkdir(CACHE_DIR, 0775, true); @file_put_contents(CACHE_DIR . '/.htaccess', "Require all denied\n"); }
            file_put_contents(CACHE_DIR . '/contactos.jsonl', json_encode([
                'fecha' => date('c'), 'nombre' => $nombre, 'empresa' => $emp, 'email' => $email, 'telefono' => $tel,
                'mensaje' => $msg, 'idioma' => $IDIOMA, 'pagina' => $pagina, 'ip' => $ip], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
        }
        responder(['ok' => true, 'respuesta' => t('ok')]);
    }

    /* ============================= MENSAJE ============================= */
    $mensaje = limpiar($in['mensaje'] ?? '', MAX_MENSAJE);
    if ($mensaje === '') fallo(t('vacio'), 400);
    if (!limitar("msg|$ip", LIM_MSG_IP, LIM_MSG_SEG) || !limitar("dia|$ip", LIM_DIA_IP, 86400)) fallo(t('limite'), 429);
    if (!limitar('global', LIM_DIA_TOTAL, 86400)) fallo(t('ocupado'), 429);

    $historial = [];
    foreach (array_slice((array)($in['historial'] ?? []), -MAX_HISTORIAL) as $m) {
        if (!is_array($m) || !in_array($m['rol'] ?? '', ['user', 'assistant'], true)) continue;
        $tx = limpiar($m['texto'] ?? '', MAX_MENSAJE);
        if ($tx !== '') $historial[] = ['rol' => $m['rol'], 'texto' => $tx];
    }

    // Base de conocimiento: se ignoran las líneas pendientes "[COMPLETAR ...]" y los comentarios
    $kb = '';
    if (is_readable(KB_ARCHIVO)) {
        $lineas = array_filter(preg_split('/\R/', file_get_contents(KB_ARCHIVO)),
                               fn($l) => stripos($l, '[COMPLETAR') === false && !str_starts_with(ltrim($l), '>'));
        $kb = mb_substr(trim(implode("\n", $lineas)), 0, 12000);
    }

    $payload = json_encode(['mensaje' => $mensaje, 'historial' => $historial, 'idioma' => $IDIOMA,
                            'pagina' => limpiar($in['pagina'] ?? '', 200), 'conocimiento' => $kb],
                           JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $ch = curl_init(N8N_USAR_TEST ? N8N_URL_TEST : N8N_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 6, CURLOPT_TIMEOUT => N8N_TIMEOUT,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Rubicam-Key: ' . N8N_CLAVE],
        CURLOPT_USERAGENT => 'Rubicam-Sitio-Chat/1.0',
    ]);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);

    $r = $resp === false ? null : json_decode($resp, true);
    if ($resp === false || $http !== 200 || !is_array($r) || empty($r['ok'])) {
        error_log("[chat-publico] n8n HTTP $http " . ($cerr ?: substr((string)$resp, 0, 300)));
        fallo(t('error'));                       // al visitante nunca se le muestran detalles técnicos
    }
    responder(['ok' => true, 'respuesta' => (string)$r['respuesta'], 'ofrecer_contacto' => !empty($r['ofrecer_contacto'])]);
} catch (Throwable $e) {
    error_log('[chat-publico] ' . $e->getMessage());
    fallo(t('error'), 500);
}
