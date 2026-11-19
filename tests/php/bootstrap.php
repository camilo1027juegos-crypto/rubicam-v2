<?php
/* Arranque de PHPUnit: sesiones sin cookies (CLI) y carga de las funciones a probar */
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');
error_reporting(E_ALL);

$raiz = dirname(__DIR__, 2);
require_once $raiz . '/Panel/config/env.php';
require_once $raiz . '/Panel/includes/seguridad.php';
require_once $raiz . '/Panel/includes/ws-mapeo.php';
require_once $raiz . '/Panel/config/ws_schema.php';
require_once $raiz . '/chat/lib/chat-funciones.php';
