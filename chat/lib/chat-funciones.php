<?php
/* ============================================================
   chat-funciones.php — Validación y límites del chat público
   Funciones puras (sin red ni BD) usadas por chat-publico.php
   y probadas en tests/php/ChatFuncionesTest.php
   ============================================================ */

/* Quita caracteres de control, recorta a $max caracteres y espacios */
function limpiar($s, int $max): string {
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', ' ', (string)$s);
    return trim(mb_substr((string)$s, 0, $max));
}

/* Idioma permitido o 'es' por defecto */
function idioma_valido($x): string {
    return in_array($x, ['es', 'en', 'de'], true) ? $x : 'es';
}

/* true si el encabezado Origin (si viene) coincide con el Host del sitio */
function origen_permitido(string $origen, string $host): bool {
    if ($origen === '') return true;
    $hostOrigen = (string)parse_url($origen, PHP_URL_HOST);
    $puerto = parse_url($origen, PHP_URL_PORT);
    if ($puerto) $hostOrigen .= ':' . $puerto;
    return strcasecmp($hostOrigen, $host) === 0;
}

/* Deja solo dígitos, +, paréntesis, espacios y guiones (máx. 30) */
function sanear_telefono($tel): string {
    return substr(preg_replace('/[^0-9+() -]/', '', (string)$tel), 0, 30);
}

/* Valida el formulario "Hablar con un asesor".
   Devuelve ['error' => null|'nombre'|'email'|'acepto', 'datos' => [...]] */
function validar_contacto(array $in): array {
    $d = [
        'nombre'   => limpiar($in['nombre'] ?? '', 100),
        'email'    => limpiar($in['email'] ?? '', 150),
        'telefono' => sanear_telefono($in['telefono'] ?? ''),
        'empresa'  => limpiar($in['empresa'] ?? '', 150),
        'mensaje'  => limpiar($in['mensaje'] ?? '', 1000),
        'pagina'   => limpiar($in['pagina'] ?? '', 200),
    ];
    $error = null;
    if (mb_strlen($d['nombre']) < 2)                         $error = 'nombre';
    elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $error = 'email';
    elseif (empty($in['acepto']))                            $error = 'acepto';
    return ['error' => $error, 'datos' => $d];
}

/* Historial que se envía a la IA: solo roles válidos, textos limpios, últimos $max */
function filtrar_historial($historial, int $max, int $maxTexto): array {
    $out = [];
    foreach (array_slice((array)$historial, -$max) as $m) {
        if (!is_array($m) || !in_array($m['rol'] ?? '', ['user', 'assistant'], true)) continue;
        $tx = limpiar($m['texto'] ?? '', $maxTexto);
        if ($tx !== '') $out[] = ['rol' => $m['rol'], 'texto' => $tx];
    }
    return $out;
}

/* Base de conocimiento sin líneas "[COMPLETAR ...]" ni comentarios ">" */
function cargar_conocimiento(string $archivo, int $max = 12000): string {
    if (!is_readable($archivo)) return '';
    $lineas = array_filter(preg_split('/\R/', (string)file_get_contents($archivo)),
                           fn($l) => stripos($l, '[COMPLETAR') === false && !str_starts_with(ltrim($l), '>'));
    return mb_substr(trim(implode("\n", $lineas)), 0, $max);
}

/* Texto que se muestra en el panel interno: sin < > " ` (evita XSS) */
function texto_seguro_aviso(string $x): string {
    return preg_replace('/[<>"`]/u', '', $x);
}

/* Límite por ventana de tiempo guardado en archivo (sin BD).
   Devuelve false si la clave ya hizo $max intentos en los últimos $ventana segundos. */
function limitar_en(string $dir, string $clave, int $max, int $ventana, ?int $ahora = null): bool {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
        @file_put_contents($dir . '/.htaccess', "Require all denied\n");
    }
    $f = @fopen($dir . '/rl_' . md5($clave) . '.json', 'c+');
    if (!$f) return true; // si no se puede limitar, no bloquear al visitante
    flock($f, LOCK_EX);
    $ahora = $ahora ?? time();
    $raw = stream_get_contents($f);
    $arr = array_values(array_filter(json_decode($raw ?: '[]', true) ?: [], fn($x) => $x > $ahora - $ventana));
    $ok = count($arr) < $max;
    if ($ok) $arr[] = $ahora;
    ftruncate($f, 0); rewind($f); fwrite($f, json_encode($arr));
    flock($f, LOCK_UN); fclose($f);
    return $ok;
}
