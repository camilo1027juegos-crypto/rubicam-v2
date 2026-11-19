<?php
/* ============================================================
   estado.php — Diagnóstico de instalación (solo administradores)
   Verifica conexión, tablas y datos. Útil para saber si falta importar algo.
   ============================================================ */
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/config/ws_schema.php';
requerir_admin();

$base = [
    'usuarios'=>'Usuarios','secciones'=>'Secciones','permisos'=>'Permisos',
    'procesos_do'=>'Operaciones (procesos_do)','documentos_do'=>'Documentos DO','recordatorios_log'=>'Recordatorios',
];
$modulos = [];
foreach (ws_schema() as $c => $def) $modulos[$def['tabla']] = 'Módulo: ' . $c;

function chequear(PDO $pdo, array $tablas): array {
    $out = [];
    foreach ($tablas as $t => $label) {
        try {
            $n = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            $out[] = ['t'=>$t, 'label'=>$label, 'ok'=>true, 'n'=>(int)$n];
        } catch (PDOException $e) {
            $out[] = ['t'=>$t, 'label'=>$label, 'ok'=>false, 'n'=>null];
        }
    }
    return $out;
}

$r1 = chequear($pdo, $base);
$r2 = chequear($pdo, $modulos);
$faltan = count(array_filter(array_merge($r1,$r2), fn($x)=>!$x['ok']));

layout_head('Estado', '');
?>
<div class="page-h"><div><h1>Estado de la instalación</h1><p>Verificación de la base de datos y los módulos.</p></div></div>

<?php if ($faltan === 0): ?>
  <div class="msg ok">Todo en orden: las <?= count($r1)+count($r2) ?> tablas existen. El dashboard puede guardar y leer datos.</div>
<?php else: ?>
  <div class="msg err">Faltan <?= $faltan ?> tabla(s). Importa en phpMyAdmin los archivos <strong>sql/rubicam_db.sql</strong> y <strong>sql/dashboard_modulos.sql</strong>.</div>
<?php endif; ?>

<div class="card">
  <h2 style="font-size:16px;margin-bottom:12px">Base y permisos</h2>
  <table>
    <thead><tr><th>Tabla</th><th>Para</th><th>Estado</th><th>Registros</th></tr></thead>
    <tbody>
    <?php foreach ($r1 as $x): ?>
      <tr><td><code><?= h($x['t']) ?></code></td><td><?= h($x['label']) ?></td>
      <td><span class="tag <?= $x['ok']?'on':'off' ?>"><?= $x['ok']?'OK':'Falta' ?></span></td>
      <td><?= $x['ok']?(int)$x['n']:'—' ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h2 style="font-size:16px;margin-bottom:12px">Módulos del dashboard</h2>
  <table>
    <thead><tr><th>Tabla</th><th>Módulo</th><th>Estado</th><th>Registros</th></tr></thead>
    <tbody>
    <?php foreach ($r2 as $x): ?>
      <tr><td><code><?= h($x['t']) ?></code></td><td><?= h($x['label']) ?></td>
      <td><span class="tag <?= $x['ok']?'on':'off' ?>"><?= $x['ok']?'OK':'Falta' ?></span></td>
      <td><?= $x['ok']?(int)$x['n']:'—' ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php layout_foot();
