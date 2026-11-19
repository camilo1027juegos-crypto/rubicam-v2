<?php
/* ============================================================
   admin/usuarios.php — Crear, editar y desactivar usuarios
   Solo para administradores.
   ============================================================ */
require_once __DIR__ . '/../includes/layout.php';
requerir_admin();

$yo    = usuario_actual();
$aviso = '';
$error = '';
$ROLES = ['admin', 'operador', 'consulta'];

/* ---------- Guardar (crear o editar) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    if (!csrf_valido()) {
        $error = 'La sesión expiró. Intenta de nuevo.';
    } else {
        $id      = (int)($_POST['id'] ?? 0);
        $usuario = trim($_POST['usuario'] ?? '');
        $nombre  = trim($_POST['nombre_completo'] ?? '');
        $rol     = in_array($_POST['rol'] ?? '', $ROLES, true) ? $_POST['rol'] : 'consulta';
        $sucursal= trim($_POST['sucursal'] ?? '');
        $activo  = isset($_POST['activo']) ? 1 : 0;
        $clave   = $_POST['password'] ?? '';

        if ($usuario === '' || $nombre === '') {
            $error = 'El usuario y el nombre son obligatorios.';
        } elseif ($id === 0 && $clave === '') {
            $error = 'Define una contraseña para el nuevo usuario.';
        } else {
            // No permitir que te quites a ti mismo el rol admin o te desactives (evita quedar sin acceso)
            if ($id === (int)$yo['id'] && ($rol !== 'admin' || $activo === 0)) {
                $rol = 'admin'; $activo = 1;
                $aviso = 'No puedes quitarte a ti mismo el rol de administrador ni desactivarte.';
            }
            try {
                if ($id === 0) {
                    $hash = password_hash($clave, PASSWORD_DEFAULT);
                    $st = $pdo->prepare('INSERT INTO usuarios (usuario,password_hash,rol,nombre_completo,sucursal,activo) VALUES (?,?,?,?,?,?)');
                    $st->execute([$usuario, $hash, $rol, $nombre, $sucursal ?: null, $activo]);
                    $aviso = 'Usuario creado.';
                } else {
                    if ($clave !== '') {
                        $st = $pdo->prepare('UPDATE usuarios SET usuario=?,password_hash=?,rol=?,nombre_completo=?,sucursal=?,activo=? WHERE id=?');
                        $st->execute([$usuario, password_hash($clave, PASSWORD_DEFAULT), $rol, $nombre, $sucursal ?: null, $activo, $id]);
                    } else {
                        $st = $pdo->prepare('UPDATE usuarios SET usuario=?,rol=?,nombre_completo=?,sucursal=?,activo=? WHERE id=?');
                        $st->execute([$usuario, $rol, $nombre, $sucursal ?: null, $activo, $id]);
                    }
                    $aviso = $aviso ?: 'Usuario actualizado.';
                }
            } catch (PDOException $e) {
                $error = ($e->getCode() === '23000')
                    ? 'Ya existe un usuario con ese nombre de usuario.'
                    : 'No se pudo guardar: ' . h($e->getMessage());
            }
        }
    }
}

/* ---------- Eliminar ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    if (csrf_valido()) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$yo['id']) {
            $error = 'No puedes eliminar tu propia cuenta.';
        } else {
            $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
            $aviso = 'Usuario eliminado.';
        }
    }
}

/* ---------- Cargar para editar ---------- */
$edit = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
    $st->execute([(int)$_GET['editar']]);
    $edit = $st->fetch() ?: null;
}

$usuarios = $pdo->query('SELECT * FROM usuarios ORDER BY creado_en DESC')->fetchAll();

layout_head('Usuarios', 'usuarios');
?>
<div class="page-h">
  <div><h1>Usuarios</h1><p>Quién puede entrar al workspace y con qué rol.</p></div>
</div>

<?php if ($aviso): ?><div class="msg ok"><?= h($aviso) ?></div><?php endif; ?>
<?php if ($error): ?><div class="msg err"><?= $error ?></div><?php endif; ?>

<div class="card">
  <h2 style="font-size:17px;margin-bottom:16px"><?= $edit ? 'Editar usuario' : 'Nuevo usuario' ?></h2>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="accion" value="guardar">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="fgrid">
      <div class="field"><label>Nombre completo</label><input class="in" name="nombre_completo" required value="<?= h($edit['nombre_completo'] ?? '') ?>"></div>
      <div class="field"><label>Usuario (para entrar)</label><input class="in" name="usuario" required value="<?= h($edit['usuario'] ?? '') ?>"></div>
      <div class="field"><label>Rol</label>
        <select class="in" name="rol">
          <?php foreach ($ROLES as $r): ?>
            <option value="<?= $r ?>" <?= ($edit['rol'] ?? 'consulta') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Sucursal</label><input class="in" name="sucursal" value="<?= h($edit['sucursal'] ?? '') ?>" placeholder="Cúcuta, Bogotá…"></div>
      <div class="field"><label>Contraseña <?= $edit ? '(dejar en blanco para no cambiarla)' : '' ?></label><input class="in" type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
      <div class="field" style="display:flex;align-items:center;gap:10px;margin-top:26px">
        <input type="checkbox" id="activo" name="activo" value="1" <?= (!$edit || (int)$edit['activo'] === 1) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--accent)">
        <label for="activo" style="margin:0">Cuenta activa</label>
      </div>
    </div>
    <div class="rowacts" style="margin-top:12px">
      <button class="btn primary" type="submit"><?= $edit ? 'Guardar cambios' : 'Crear usuario' ?></button>
      <?php if ($edit): ?><a class="btn" href="usuarios.php">Cancelar</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Sucursal</th><th>Estado</th><th>Permisos</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($usuarios as $u): ?>
      <tr>
        <td style="font-weight:700"><?= h($u['nombre_completo']) ?></td>
        <td><?= h($u['usuario']) ?></td>
        <td><span class="tag <?= h($u['rol']) ?>"><?= ucfirst($u['rol']) ?></span></td>
        <td><?= h($u['sucursal'] ?: '—') ?></td>
        <td><span class="tag <?= $u['activo'] ? 'on' : 'off' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
        <td>
          <?php if ($u['rol'] === 'admin'): ?>
            <span class="tag admin">Todo</span>
          <?php else: ?>
            <a class="btn sm" href="permisos.php?usuario=<?= (int)$u['id'] ?>">Gestionar</a>
          <?php endif; ?>
        </td>
        <td>
          <div class="rowacts">
            <a class="btn sm" href="usuarios.php?editar=<?= (int)$u['id'] ?>">Editar</a>
            <?php if ((int)$u['id'] !== (int)$yo['id']): ?>
            <form method="post" onsubmit="return confirm('¿Eliminar a <?= h($u['usuario']) ?>? No se puede deshacer.');" style="display:inline">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="accion" value="eliminar">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button class="btn sm danger" type="submit">Eliminar</button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php layout_foot();
