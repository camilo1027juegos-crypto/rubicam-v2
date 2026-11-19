<?php
/* ============================================================
   conexion.php — Conexión a MySQL con PDO
   Valores por defecto de XAMPP: usuario "root" sin contraseña.
   Si tu MySQL tiene contraseña, cámbiala abajo.
   ============================================================ */
$DB_HOST    = 'localhost';
$DB_NOMBRE  = 'rubicam_db';
$DB_USUARIO = 'root';
$DB_CLAVE   = '';

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
    die('No se pudo conectar a la base de datos. '
        . '¿Encendiste MySQL en XAMPP y ya importaste rubicam_db.sql? '
        . 'Detalle técnico: ' . htmlspecialchars($e->getMessage()));
}
