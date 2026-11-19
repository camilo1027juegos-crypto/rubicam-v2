<?php
/* ============================================================
   index.php — Punto de entrada: manda al login o al destino
   ============================================================ */
require_once __DIR__ . '/includes/auth.php';
$u = usuario_actual();
if (!$u) {
    header('Location: login.php');
} else {
    header('Location: ' . ($u['rol'] === 'admin' ? 'admin/usuarios.php' : 'dashboard-nuevo.php'));
}
exit;
