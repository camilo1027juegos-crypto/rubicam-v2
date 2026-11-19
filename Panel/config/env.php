<?php
/* ============================================================
   env.php — Configuración desde variables de entorno
   Las claves NUNCA se escriben en el código.
     - En el servidor / CI: se definen como variables de entorno
       (en GitHub Actions, desde Settings → Secrets).
     - En XAMPP (local): copie .env.example como .env en la raíz
       del proyecto y complete los valores. .env NO se sube a git.
   ============================================================ */

if (!function_exists('env')) {

    /* Carga un archivo KEY=VALOR sin pisar variables ya definidas */
    function cargar_env(string $archivo): void {
        if (!is_readable($archivo)) return;
        foreach (file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);
            if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) continue;
            [$k, $v] = array_map('trim', explode('=', $linea, 2));
            $v = trim($v, "\"'");
            if ($k !== '' && getenv($k) === false) putenv("$k=$v");
        }
    }

    /* Valor de una variable de entorno, o el valor por defecto si no existe */
    function env(string $clave, ?string $defecto = null): ?string {
        $v = getenv($clave);
        return ($v === false) ? $defecto : $v;
    }

    function env_bool(string $clave, bool $defecto): bool {
        $v = getenv($clave);
        if ($v === false || $v === '') return $defecto;
        return in_array(strtolower($v), ['1', 'true', 'si', 'sí', 'yes', 'on'], true);
    }

    cargar_env(dirname(__DIR__, 2) . '/.env');
}
