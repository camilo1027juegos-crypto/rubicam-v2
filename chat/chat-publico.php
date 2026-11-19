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

require_once __DIR__ . '/../Panel/config/env.php';
require_once __DIR__ . '/lib/chat-funciones.php';

/* ==================== CONFIGURACIÓN ==================== */
/* Las URLs y claves se leen de variables de entorno (ver .env.example).
   Nunca escriba claves aquí: este archivo está en el repositorio.        */
define('N8N_URL',       env('N8N_URL_PUBLICO', 'http://localhost:5678/webhook/chat-publico-rubicam'));
define('N8N_URL_TEST',  env('N8N_URL_PUBLICO_TEST', 'http://localhost:5678/webhook-test/chat-publico-rubicam'));
define('N8N_USAR_TEST', env_bool('N8N_USAR_TEST', true));
define('N8N_CLAVE',     env('N8N_CLAVE_PUBLICA', ''));   // la MISMA que en el nodo "Preparar contexto" del flujo público
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

// Formulario de contacto -> base de datos (tabla web_leads)
const GUARDAR_BD     = true;
const AVISO_EN_PANEL = true;    // crea un aviso en el Inicio del panel por cada contacto nuevo
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_NAME', env('DB_NOMBRE', 'rubicam_db'));
define('DB_USER', env('DB_WEB_USUARIO', 'rubicam_web'));
define('DB_PASS', env('DB_WEB_CLAVE', ''));
/* ======================================================= */

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
function limitar(string $clave, int $max, int $ventana): bool { return limitar_en(CACHE_DIR, $clave, $max, $ventana); }

/* ---------- Solo POST, solo desde el mismo sitio ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fallo('Método no permitido.', 405);
if (!origen_permitido($_SERVER['HTTP_ORIGIN'] ?? '', $_SERVER['HTTP_HOST'] ?? '')) fallo('Origen no permitido.', 403);
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) fallo('Solicitud no válida.', 400);
$IDIOMA = idioma_valido($in['idioma'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$accion = $_GET['accion'] ?? 'mensaje';

try {
    /* ======================= FORMULARIO DE CONTACTO ======================= */
    if ($accion === 'contacto') {
        if (!empty($in['web'])) responder(['ok' => true, 'respuesta' => t('ok')]);          // honeypot: los bots lo llenan
        $v = validar_contacto($in);
        if ($v['error'] !== null) fallo($v['error'], 422);
        ['nombre' => $nombre, 'email' => $email, 'telefono' => $tel, 'empresa' => $emp,
         'mensaje' => $msg, 'pagina' => $pagina] = $v['datos'];
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
                    $nombreA = texto_seguro_aviso($nombre); $empA = texto_seguro_aviso($emp);
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

    $historial = filtrar_historial($in['historial'] ?? [], MAX_HISTORIAL, MAX_MENSAJE);

    // Base de conocimiento: se ignoran las líneas pendientes "[COMPLETAR ...]" y los comentarios
    $kb = cargar_conocimiento(KB_ARCHIVO);

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
