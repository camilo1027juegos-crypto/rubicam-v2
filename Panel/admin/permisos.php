<?php
/* ============================================================
   admin/permisos.php — Qué secciones ve y edita cada usuario
   Solo para administradores. El rol 'admin' siempre ve/edita todo.
   ============================================================ */
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/permisos.php';
requerir_admin();

$aviso = '';
$error = '';

/* Usuarios a los que se les asignan permisos (los admin no: tienen todo) */
$usuarios = $pdo->query("SELECT id, usuario, nombre_completo, rol FROM usuarios WHERE rol <> 'admin' ORDER BY nombre_completo")->fetchAll();
$secciones = secciones_todas();

/* Usuario seleccionado */
$uid = (int)($_GET['usuario'] ?? ($_POST['usuario'] ?? 0));
$sel = null;
if ($uid) {
    $st = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND rol <> 'admin'");
    $st->execute([$uid]);
    $sel = $st->fetch() ?: null;
}

/* ---------- Guardar permisos ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar' && $sel) {
    if (!csrf_valido()) {
        $error = 'La sesión expiró. Intenta de nuevo.';
    } else {
        $ver    = $_POST['ver']    ?? [];   // ['operaciones'=>'1', ...]
        $editar = $_POST['editar'] ?? [];
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM permisos WHERE usuario_id = ?')->execute([$sel['id']]);
            $ins = $pdo->prepare('INSERT INTO permisos (usuario_id, seccion, puede_ver, puede_editar) VALUES (?,?,?,?)');
            foreach ($secciones as $s) {
                $cv = !empty($ver[$s['clave']]);
                $ce = !empty($editar[$s['clave']]);
                if ($ce) $cv = true;          // para editar hay que poder ver
                if ($cv || $ce) {
                    $ins->execute([$sel['id'], $s['clave'], $cv ? 1 : 0, $ce ? 1 : 0]);
                }
            }
            $pdo->commit();
            $aviso = 'Permisos guardados para ' . h($sel['nombre_completo']) . '.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'No se pudieron guardar los permisos: ' . h($e->getMessage());
        }
    }
}

/* Permisos actuales del usuario seleccionado */
$actual = $sel ? cargar_permisos((int)$sel['id']) : [];

layout_head('Permisos', 'permisos');
?>
<div class="page-h">
  <div><h1>Permisos</h1><p>Elige una persona y marca qué secciones puede ver y editar.</p></div>
</div>

<?php if ($aviso): ?><div class="msg ok"><?= $aviso ?></div><?php endif; ?>
<?php if ($error): ?><div class="msg err"><?= $error ?></div><?php endif; ?>

<div class="card">
  <form method="get" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div class="field" style="margin:0;min-width:260px">
      <label>Persona</label>
      <select class="in" name="usuario" onchange="this.form.submit()">
        <option value="">— Selecciona un usuario —</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= (int)$u['id'] ?>" <?= $uid === (int)$u['id'] ? 'selected' : '' ?>>
            <?= h($u['nombre_completo']) ?> (<?= h($u['usuario']) ?> · <?= h($u['rol']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <noscript><button class="btn" type="submit">Ver</button></noscript>
  </form>
</div>

<?php if (!$usuarios): ?>
  <div class="card"><p class="sub" style="color:var(--muted);margin:0">Todavía no hay usuarios de tipo operador o consulta. Crea uno en <a href="usuarios.php">Usuarios</a>. Los administradores ya ven y editan todo.</p></div>
<?php elseif ($sel): ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="accion" value="guardar">
    <input type="hidden" name="usuario" value="<?= (int)$sel['id'] ?>">
    <div class="card">
      <h2 style="font-size:17px;margin-bottom:4px">Permisos de <?= h($sel['nombre_completo']) ?></h2>
      <p style="color:var(--muted);margin:0 0 16px">Marca <strong>Ver</strong> para que aparezca la sección en su menú. Marca <strong>Editar</strong> para que además pueda crear y cambiar datos. (Editar activa Ver automáticamente.)</p>
      <table class="perm-table">
        <thead><tr><th>Sección</th><th class="c">Ver</th><th class="c">Editar</th></tr></thead>
        <tbody>
          <?php $grupo = ''; foreach ($secciones as $s):
            if ($s['grupo'] !== $grupo): $grupo = $s['grupo']; ?>
              <tr class="grp-row"><td colspan="3"><?= h($grupo) ?></td></tr>
          <?php endif;
            $cv = !empty($actual[$s['clave']]['ver']);
            $ce = !empty($actual[$s['clave']]['editar']); ?>
            <tr>
              <td style="font-weight:600"><?= h($s['nombre']) ?></td>
              <td class="c"><input type="checkbox" name="ver[<?= h($s['clave']) ?>]" value="1" <?= $cv ? 'checked' : '' ?> data-sec="<?= h($s['clave']) ?>" class="chk-ver"></td>
              <td class="c"><input type="checkbox" name="editar[<?= h($s['clave']) ?>]" value="1" <?= $ce ? 'checked' : '' ?> data-sec="<?= h($s['clave']) ?>" class="chk-ed"></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="rowacts" style="margin-top:16px">
        <button class="btn primary" type="submit">Guardar permisos</button>
        <button class="btn" type="button" id="todo">Marcar todo Ver</button>
        <button class="btn" type="button" id="nada">Quitar todo</button>
      </div>
    </div>
  </form>
  <script>
    // Editar implica Ver; y botones de ayuda
    document.querySelectorAll('.chk-ed').forEach(ed=>ed.addEventListener('change',()=>{
      if(ed.checked){ const v=document.querySelector('.chk-ver[data-sec="'+ed.dataset.sec+'"]'); if(v) v.checked=true; }
    }));
    document.querySelectorAll('.chk-ver').forEach(v=>v.addEventListener('change',()=>{
      if(!v.checked){ const e=document.querySelector('.chk-ed[data-sec="'+v.dataset.sec+'"]'); if(e) e.checked=false; }
    }));
    document.getElementById('todo').onclick=()=>document.querySelectorAll('.chk-ver').forEach(c=>c.checked=true);
    document.getElementById('nada').onclick=()=>document.querySelectorAll('.perm-table input').forEach(c=>c.checked=false);
  </script>
<?php endif; ?>
<?php layout_foot();
