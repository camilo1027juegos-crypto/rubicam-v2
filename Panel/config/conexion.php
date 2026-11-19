<?php
/* ============================================================
   conexion.php — Conexión a MySQL con PDO
   Los datos de conexión se leen de variables de entorno (env.php).
   Por defecto usa los valores de XAMPP: usuario "root" sin contraseña.
   Si $pdo ya existe (pruebas automáticas), no se vuelve a conectar.
   ============================================================ */
require_once __DIR__ . '/env.php';

if (!isset($pdo)) {
    $DB_HOST    = env('DB_HOST', 'localhost');
    $DB_NOMBRE  = env('DB_NOMBRE', 'rubicam_db');
    $DB_USUARIO = env('DB_USUARIO', 'root');
    $DB_CLAVE   = env('DB_CLAVE', '');

    try {
        $pdo = new PDO(
            "mysql:host=$DB_HOST;dbname=$DB_NOMBRE;charset=utf8mb4",
            $DB_USUARIO,
            $DB_CLAVE,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('[conexion] ' . $e->getMessage());
        die('No se pudo conectar a la base de datos. '
            . '¿Encendiste MySQL en XAMPP y ya importaste rubicam_db.sql?');
    }
}
