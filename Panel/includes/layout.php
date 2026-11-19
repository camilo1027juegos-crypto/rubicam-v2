<?php
/* ============================================================
   layout.php — Cabecera y pie compartidos del área admin
   ============================================================ */
require_once __DIR__ . '/auth.php';

function layout_head(string $titulo, string $activa = ''): void {
    $u = usuario_actual();
    $b = base_rel();
    ?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= h($titulo) ?> · Rubicam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $b ?>assets/panel.css">
</head>
<body>
<div class="app">
  <aside class="sb">
    <div class="brand">
      <span class="brand-mark">R</span>
      <div><div class="brand-n">RUBICAM</div><div class="brand-s">ADMINISTRACIÓN</div></div>
    </div>
    <nav>
      <a href="<?= $b ?>admin/usuarios.php" class="<?= $activa==='usuarios'?'active':'' ?>">Usuarios</a>
      <a href="<?= $b ?>admin/permisos.php" class="<?= $activa==='permisos'?'active':'' ?>">Permisos</a>
      <a href="<?= $b ?>dashboard-nuevo.php">Abrir dashboard</a>
    </nav>
    <div class="foot">
      <div class="me"><?= h($u['nombre_completo']) ?><small><?= h($u['usuario']) ?> · <?= h($u['rol']) ?></small></div>
      <a href="<?= $b ?>logout.php">Cerrar sesión</a>
    </div>
  </aside>
  <main class="main">
<?php
}

function layout_foot(): void {
    ?>
  </main>
</div>
</body>
</html>
<?php
}
