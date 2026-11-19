<?php
/* ============================================================
   login.php — Inicio de sesión
   ============================================================ */
require_once __DIR__ . '/includes/auth.php';

// Si ya hay sesión, no mostrar el login otra vez
if (usuario_actual()) {
    header('Location: ' . (es_admin() ? 'admin/usuarios.php' : 'dashboard-nuevo.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $error = 'La sesión expiró. Intenta de nuevo.';
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $clave   = $_POST['password'] ?? '';
        $st = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? LIMIT 1');
        $st->execute([$usuario]);
        $u = $st->fetch();

        if ($u && (int)$u['activo'] === 1 && password_verify($clave, $u['password_hash'])) {
            session_regenerate_id(true);          // evita fijación de sesión
            $_SESSION['uid'] = (int)$u['id'];
            header('Location: ' . ($u['rol'] === 'admin' ? 'admin/usuarios.php' : 'dashboard-nuevo.php'));
            exit;
        }
        $error = 'Usuario o contraseña incorrectos, o la cuenta está inactiva.';
    }
}
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Iniciar sesión · Rubicam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/panel.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="brand">
      <span class="brand-mark">R</span>
      <div><div class="brand-n">RUBICAM</div><div class="brand-s">WORKSPACE · EQUIPOS</div></div>
    </div>
    <h1>Iniciar sesión</h1>
    <p class="sub">Ingresa con tu cuenta del equipo.</p>

    <?php if ($error): ?><div class="msg err"><?= h($error) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="field">
        <label for="usuario">Usuario</label>
        <input class="in" type="text" id="usuario" name="usuario" required autofocus value="<?= h($_POST['usuario'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="password">Contraseña</label>
        <input class="in" type="password" id="password" name="password" required>
      </div>
      <button class="btn primary block" type="submit">Entrar</button>
    </form>
  </div>
</div>
</body>
</html>
