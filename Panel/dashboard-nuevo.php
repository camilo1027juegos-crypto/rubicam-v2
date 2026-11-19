<?php
/* Rubicam Workspace - dashboard-nuevo.php
   FASE 1: protegido con login real (includes/auth.php).
   FASE 2 (pendiente): conectar los datos a MySQL y ocultar las secciones
   según $PERMISOS. Por ahora el panel exige sesión para abrirse. */
require __DIR__ . '/includes/permisos.php';
requerir_login();                         // sin sesión válida → login.php
$yo = usuario_actual();

/* Permisos de este usuario por sección (los consumirá la Fase 2 para el menú) */
$PERMISOS = [];
foreach (secciones_todas() as $s) {
    $PERMISOS[$s['clave']] = ['ver' => puede_ver($s['clave']), 'editar' => puede_editar($s['clave'])];
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
$WS = [
    'api'       => 'ws-api.php',
    'chat'      => 'ws-chat.php',
    'loginUrl'  => 'login.php',
    'logoutUrl' => 'logout.php',
    'user'      => ['name' => $yo['nombre_completo'], 'rol' => $yo['rol'], 'id' => (string)$yo['id']],
    'permisos'  => $PERMISOS,
    'esAdmin'   => es_admin(),
];
$WS_JSON = json_encode($WS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Rubicam Workspace</title>
<meta name="claude:viewport-fit-sides" content="cover">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* Layout: barra lateral fija (menú) + columna de contenido con barra superior pegajosa; cada módulo es una vista por #hash */
:root{
  --bg:#F3F5F8; --surface:#FFFFFF; --surface-2:#F6F8FB; --line:#E3E8EF; --line-2:#EEF1F5; --line-3:#D5DCE6; --dash:#BFC9D6;
  --fg:#0F1F33; --fg-2:#33445C; --fg-3:#44546A; --muted:#5B6B82;
  --accent:#CC4B06; --accent-fg:#FFFFFF; --accent-soft:#FFF3EA; --accent-line:#FFD9BF; --accent-text:#A83B00;
  --link:#1D5FA8; --link-h:#134577;
  --nav:#0C2036; --nav-fg:#C5D0DC; --nav-head:#8FA3B8; --nav-active:#0F7A55; --nav-hover:rgba(255,255,255,.06); --nav-line:rgba(255,255,255,.1); --nav-badge:rgba(255,255,255,.14); --nav-avatar:#1E3A57; --nav-brand2:#FF9A66; --nav-white:#FFFFFF;
  --chip:#0C2036; --chip-fg:#FFFFFF;
  --ok:#0B6B47; --ok-bg:#E4F5EC; --ok-dot:#14A36B; --ok-bar:#0F7A55;
  --warn:#9A5B00; --warn-bg:#FFF0D9;
  --ora:#A83B00; --ora-bg:#FFEDE0; --ora-dot:#F26B1D;
  --crit:#B42318; --crit-bg:#FDE8E6; --crit-dot:#D92D20;
  --info:#1D5FA8; --info-bg:#E6F0FB; --info-dot:#2F7BD0; --info-ink:#16365C;
  --pur:#5B33A6; --pur-bg:#EAE3F8;
  --neu:#44546A; --neu-bg:#E8EDF3; --neu-dot:#7A8799;
  --shadow:0 1px 2px rgba(15,31,51,.04); --shadow-pop:0 18px 48px rgba(12,32,54,.18); --overlay:rgba(12,32,54,.45);
  --font:'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
}
@media (prefers-color-scheme: dark){ :root:not([data-theme="light"]){
  --bg:#0A1522; --surface:#111F2E; --surface-2:#162737; --line:#22354A; --line-2:#1B2D40; --line-3:#2E4560; --dash:#3B5470;
  --fg:#E8EEF5; --fg-2:#C6D2DE; --fg-3:#AEBccb; --muted:#8FA3B8;
  --accent:#E8641C; --accent-fg:#FFFFFF; --accent-soft:#33200F; --accent-line:#6B3A1C; --accent-text:#FFA36B;
  --link:#7DB3EE; --link-h:#A8CCF5;
  --nav:#07121F; --nav-active:#0F7A55; --nav-avatar:#1E3A57;
  --chip:#E8EEF5; --chip-fg:#0C2036;
  --ok:#62D3A2; --ok-bg:#12352A; --ok-dot:#22B87B; --ok-bar:#22B87B;
  --warn:#F1C26B; --warn-bg:#3A2D12;
  --ora:#FFA36B; --ora-bg:#3A2416; --ora-dot:#F2792F;
  --crit:#FF8F86; --crit-bg:#3D1C1B; --crit-dot:#EF4B3F;
  --info:#8CC0F5; --info-bg:#14304F; --info-dot:#4C93E0; --info-ink:#C9E0FA;
  --pur:#BCA6F2; --pur-bg:#2B2347;
  --neu:#C3CFDC; --neu-bg:#23364B; --neu-dot:#8496AB;
  --shadow:0 1px 2px rgba(0,0,0,.3); --shadow-pop:0 18px 48px rgba(0,0,0,.5); --overlay:rgba(0,0,0,.6);
  color-scheme:dark;
}}
:root[data-theme="dark"]{
  --bg:#0A1522; --surface:#111F2E; --surface-2:#162737; --line:#22354A; --line-2:#1B2D40; --line-3:#2E4560; --dash:#3B5470;
  --fg:#E8EEF5; --fg-2:#C6D2DE; --fg-3:#AEBccb; --muted:#8FA3B8;
  --accent:#E8641C; --accent-fg:#FFFFFF; --accent-soft:#33200F; --accent-line:#6B3A1C; --accent-text:#FFA36B;
  --link:#7DB3EE; --link-h:#A8CCF5;
  --nav:#07121F; --nav-active:#0F7A55; --nav-avatar:#1E3A57;
  --chip:#E8EEF5; --chip-fg:#0C2036;
  --ok:#62D3A2; --ok-bg:#12352A; --ok-dot:#22B87B; --ok-bar:#22B87B;
  --warn:#F1C26B; --warn-bg:#3A2D12;
  --ora:#FFA36B; --ora-bg:#3A2416; --ora-dot:#F2792F;
  --crit:#FF8F86; --crit-bg:#3D1C1B; --crit-dot:#EF4B3F;
  --info:#8CC0F5; --info-bg:#14304F; --info-dot:#4C93E0; --info-ink:#C9E0FA;
  --pur:#BCA6F2; --pur-bg:#2B2347;
  --neu:#C3CFDC; --neu-bg:#23364B; --neu-dot:#8496AB;
  --shadow:0 1px 2px rgba(0,0,0,.3); --shadow-pop:0 18px 48px rgba(0,0,0,.5); --overlay:rgba(0,0,0,.6);
  color-scheme:dark;
}
*{box-sizing:border-box}
[hidden]{display:none!important}
body{background:var(--bg); color:var(--fg); font-family:var(--font); font-size:14px; line-height:1.4; margin:0}
body::before{content:""; position:fixed; inset:0 0 auto; height:env(safe-area-inset-top,0px); z-index:10; background:var(--surface)}
a{color:var(--link)} a:hover{color:var(--link-h)}
button,input,select,textarea{font-family:inherit}
button{color:inherit}
:focus-visible{outline:2px solid var(--link); outline-offset:2px}
h1,h2,h3{text-wrap:balance}
.num{font-variant-numeric:tabular-nums}

/* ---------- Estructura ---------- */
.app{display:flex; min-height:100vh; background:linear-gradient(to right, var(--nav) 248px, var(--bg) 248px)}
.sb{flex:0 0 248px; width:248px; background:var(--nav); display:flex; flex-direction:column; gap:22px; padding-block:20px; padding-left:max(16px, env(safe-area-inset-left,0px)); padding-right:16px; position:sticky; top:0; height:100vh; overflow-y:auto; z-index:40}
.brand{display:flex; align-items:center; gap:12px; padding:4px 10px 18px; border-bottom:1px solid var(--nav-line)}
.brand-mark{width:38px; height:38px; border-radius:11px; background:var(--accent); color:var(--nav-white); font-weight:800; font-size:18px; display:inline-flex; align-items:center; justify-content:center; flex:none}
.brand-n{color:var(--nav-white); font-weight:800; font-size:16px; letter-spacing:.02em}
.brand-s{color:var(--nav-brand2); font-size:11px; font-weight:700; letter-spacing:.08em}
.nav{display:flex; flex-direction:column; gap:20px}
.nav-g{display:flex; flex-direction:column; gap:2px}
.nav-h{font-size:11px; font-weight:700; letter-spacing:.08em; color:var(--nav-head); padding:0 12px; margin-bottom:6px}
.nav a{display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:10px; color:var(--nav-fg); text-decoration:none; font-size:14px; font-weight:500}
.nav a:hover{background:var(--nav-hover); color:var(--nav-white)}
.nav a[aria-current="page"]{background:var(--nav-active); color:var(--nav-white); font-weight:600}
.nav a span.l{flex:1}
.nb{background:var(--nav-badge); color:#E6ECF2; font-size:11px; font-weight:700; padding:2px 7px; border-radius:999px}
.nb.hot{background:var(--accent); color:var(--nav-white)}
.sb-foot{margin-top:auto; border-top:1px solid var(--nav-line); padding-top:16px; display:flex; flex-direction:column; gap:10px}
.me{display:flex; align-items:center; gap:12px; padding:0 10px}
.me-av{width:36px; height:36px; border-radius:50%; background:var(--nav-avatar); color:var(--nav-white); font-size:13px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex:none}
.me-n{color:var(--nav-white); font-weight:700; font-size:14px}
.me-r{color:#9FB0C3; font-size:12px}
.sb-note{color:var(--nav-head); font-size:12px; line-height:1.45; padding:0 10px}
.sb-btn{display:block; color:var(--nav-fg); font-size:13px; font-weight:500; padding:8px 12px; border-radius:10px; text-decoration:none}
.sb-btn:hover{background:var(--nav-hover); color:var(--nav-white)}

.main{flex:1 1 auto; min-width:0; display:flex; flex-direction:column}
.top{position:sticky; top:env(safe-area-inset-top,0px); z-index:30; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px 20px; padding-block:12px; padding-left:max(32px, env(safe-area-inset-left,0px)); padding-right:max(32px, env(safe-area-inset-right,0px)); background:var(--surface); border-bottom:1px solid var(--line)}
.crumb{display:flex; align-items:center; gap:8px; font-size:14px; min-width:0}
.crumb span{color:var(--muted)}
.top-r{display:flex; flex-wrap:wrap; align-items:center; gap:12px}
.menu-btn{display:none}
.search{position:relative; width:340px; max-width:100%}
.search-box{display:flex; align-items:center; gap:8px; background:var(--surface); border:1px solid var(--line-3); border-radius:12px; padding:0 12px; height:44px; color:var(--muted)}
.search-box input{flex:1; min-width:0; border:0; background:transparent; font-size:14px; color:var(--fg); height:40px; outline:none}
.kbd{font-size:11px; font-weight:700; color:var(--muted); border:1px solid var(--line-3); border-radius:6px; padding:2px 6px; white-space:nowrap}
.pop{position:absolute; top:calc(100% + 6px); right:0; width:min(420px, calc(100vw - 32px)); background:var(--surface); border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow-pop); padding:8px; z-index:60; max-height:70vh; overflow:auto}
.search .pop{left:0; right:auto}
.pop-i{display:flex; align-items:center; gap:10px; width:100%; text-align:left; background:none; border:0; border-radius:10px; padding:10px; cursor:pointer; font-size:13px}
.pop-i:hover,.pop-i:focus-visible{background:var(--surface-2)}
.pop-i .k{font-size:11px; font-weight:700; letter-spacing:.06em; color:var(--muted); width:86px; flex:none}
.pop-i .t{flex:1; min-width:0}
.pop-i .t b{display:block; font-weight:600; color:var(--fg)}
.pop-i .t small{color:var(--muted)}
.pop-empty{padding:14px 10px; color:var(--muted); font-size:13px}
.bell{position:relative}
.bell-dot{position:absolute; top:6px; right:6px; min-width:18px; height:18px; padding:0 5px; border-radius:999px; background:var(--accent); color:var(--accent-fg); border:2px solid var(--surface); font-size:10px; font-weight:800; display:inline-flex; align-items:center; justify-content:center}

.content{padding-block:28px 48px; padding-left:max(32px, env(safe-area-inset-left,0px)); padding-right:max(32px, env(safe-area-inset-right,0px)); display:flex; flex-direction:column; gap:24px}

/* ---------- Piezas ---------- */
.ph{display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px}
.ph h1{margin:0; font-size:30px; font-weight:800; letter-spacing:-0.02em; line-height:1.15}
.ph p{margin:6px 0 0; font-size:15px; color:var(--muted)}
.ph-a{display:flex; flex-wrap:wrap; gap:10px; align-items:center}
.upd{font-size:12px; color:var(--muted)} .upd strong{color:var(--fg)}
.row{display:flex; flex-wrap:wrap; gap:20px; align-items:stretch}
.card{flex:1 1 320px; min-width:0; background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:20px; box-shadow:var(--shadow); display:flex; flex-direction:column; gap:14px}
.card.w2{flex:2 1 560px} .card.w-full{flex:1 1 100%}
.ch{display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px}
.ch h2{margin:0; font-size:17px; font-weight:700; letter-spacing:-0.01em}
.ch .sub{font-size:12px; color:var(--muted); margin-top:2px}
.lnk{font-size:13px; font-weight:600; color:var(--link); text-decoration:none; display:inline-flex; align-items:center; gap:6px; background:none; border:0; padding:0; cursor:pointer}
.lnk:hover{color:var(--link-h)}
.card > .lnk:last-child{margin-top:auto}
.btn{height:44px; padding:0 16px; border-radius:12px; border:1px solid var(--line-3); background:var(--surface); color:var(--fg); font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:8px; white-space:nowrap}
.btn:hover{background:var(--surface-2)}
.btn.primary{background:var(--accent); color:var(--accent-fg); border-color:transparent; font-weight:700}
.btn.primary:hover{filter:brightness(1.06)}
.btn.sm{height:36px; padding:0 12px; font-size:13px; border-radius:10px}
.btn.xs{height:30px; padding:0 10px; font-size:12px; border-radius:8px}
.btn.danger{background:var(--crit-dot); color:#fff; border-color:transparent}
.btn.ghost-danger{color:var(--crit); border-color:transparent; background:transparent}
.btn:disabled{opacity:.55; cursor:not-allowed}
.ib{width:44px; height:44px; padding:0; border-radius:12px; border:1px solid var(--line-3); background:var(--surface); color:var(--fg-2); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; flex:none}
.ib:hover{background:var(--surface-2)}
.ib.sm{width:32px; height:32px; border-radius:9px}
.spin svg{animation:sp .7s linear}
@keyframes sp{to{transform:rotate(360deg)}}
.chips{display:flex; flex-wrap:wrap; align-items:center; gap:8px}
.cf{height:36px; padding:0 14px; border-radius:999px; border:1px solid var(--line-3); background:var(--surface); color:var(--fg-2); font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap}
.cf.on{background:var(--chip); color:var(--chip-fg); border-color:transparent}
.cf .c{opacity:.7; margin-left:4px}
.pill{display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; white-space:nowrap}
.pill .dot{width:7px; height:7px; border-radius:50%; background:currentColor}
.t-ok{background:var(--ok-bg); color:var(--ok)} .t-warn{background:var(--warn-bg); color:var(--warn)} .t-ora{background:var(--ora-bg); color:var(--ora)}
.t-crit{background:var(--crit-bg); color:var(--crit)} .t-info{background:var(--info-bg); color:var(--info)} .t-pur{background:var(--pur-bg); color:var(--pur)} .t-neu{background:var(--neu-bg); color:var(--neu)}
.hchip{display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:999px; font-size:12px; font-weight:600; border:0; white-space:nowrap}
button.hchip{cursor:pointer}
.av{width:28px; height:28px; border-radius:50%; font-size:11px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex:none}
.av.lg{width:32px; height:32px; font-size:12px}
.av.xs{width:26px; height:26px; font-size:10px; border:2px solid var(--surface)}
.avs{display:flex; align-items:center} .avs .av + .av{margin-left:-8px}
.bar{height:6px; border-radius:999px; background:var(--line); overflow:hidden; flex:1; min-width:40px}
.bar > i{display:block; height:100%; border-radius:999px}
.barrow{display:flex; align-items:center; gap:10px}
.barrow .pct{font-size:12px; font-weight:700; color:var(--fg-3); min-width:34px; text-align:right; white-space:nowrap}
.muted{color:var(--muted)} .small{font-size:12px}
.stack{display:flex; flex-direction:column; gap:8px}
.sec{border-top:1px solid var(--line-2); padding-top:14px; display:flex; flex-direction:column; gap:8px}
.lr{display:flex; align-items:center; gap:12px; padding:12px 0; border-top:1px solid var(--line-2); flex-wrap:wrap}
.lr .grow{flex:1 1 200px; min-width:0}
.lr .tt{font-size:14px; font-weight:600; display:block}
.lr .ts{font-size:12px; color:var(--muted); margin-top:2px; display:block}
.plain{background:none; border:0; padding:0; text-align:left; cursor:pointer; font:inherit; color:inherit}
.plain:hover .tt{color:var(--link)}
.empty{border:1px dashed var(--dash); border-radius:12px; padding:18px; color:var(--muted); font-size:13px; line-height:1.5; display:flex; flex-direction:column; gap:10px; align-items:flex-start}
.empty b{color:var(--fg)}
.ck{width:18px; height:18px; margin:0; accent-color:var(--nav-active); flex:none; cursor:pointer}

/* KPI */
.kpis{display:flex; flex-wrap:wrap; gap:16px}
.kpi{flex:1 1 190px; background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:18px 20px; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; box-shadow:var(--shadow); text-align:left; cursor:pointer; font:inherit; color:inherit}
.kpi:hover{border-color:var(--line-3)}
.kpi .n{font-size:30px; font-weight:800; letter-spacing:-0.02em; line-height:1; font-variant-numeric:tabular-nums}
.kpi .k{font-size:11px; font-weight:700; letter-spacing:.08em; color:var(--muted); margin-top:8px}
.kpi .s{font-size:12px; font-weight:600; margin-top:4px; color:var(--muted)}
.kpi .s.hot{color:var(--accent-text)} .kpi .s.good{color:var(--ok)}
.kpi .ic{width:40px; height:40px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; flex:none}

/* Tareas */
.trow{display:flex; align-items:center; gap:14px; padding:12px 0; border-top:1px solid var(--line-2); flex-wrap:wrap}
.trow .main-t{flex:1 1 220px; min-width:0}
.trow .tt{display:block; font-size:14px; font-weight:600}
.trow .ts{display:block; margin-top:2px}
.trow.done .tt{color:var(--muted); text-decoration:line-through}
.tdue{font-size:12px; color:var(--muted); min-width:112px}
.tdue.late{color:var(--crit); font-weight:600}
.qadd{display:flex; align-items:center; gap:10px; border:1px dashed var(--dash); border-radius:12px; padding:0 14px; height:44px; color:var(--muted)}
.qadd input{flex:1; min-width:0; border:0; background:transparent; font-size:14px; color:var(--fg); height:40px; outline:none}
.rowacts{display:flex; gap:6px}

/* Agenda */
.tl{display:flex; flex-direction:column}
.tl-i{display:flex; gap:12px}
.tl-h{flex:0 0 70px; text-align:right; font-size:12px; font-weight:700; color:var(--muted); padding-top:1px; font-variant-numeric:tabular-nums}
.tl-r{display:flex; flex-direction:column; align-items:center; flex:none}
.tl-d{width:10px; height:10px; border-radius:50%; margin-top:4px; box-sizing:border-box}
.tl-l{flex:1; width:2px; background:var(--line); margin-top:4px}
.tl-b{flex:1; min-width:0; padding-bottom:16px}
.tl-b.next{background:var(--accent-soft); border:1px solid var(--accent-line); border-radius:12px; padding:10px 12px; margin-bottom:12px}
.tl-now{display:flex; align-items:center; gap:8px; margin:0 0 14px}
.tl-now span:first-child{background:var(--accent); color:var(--accent-fg); font-size:11px; font-weight:700; padding:3px 9px; border-radius:999px; white-space:nowrap}
.tl-now span:last-child{flex:1; height:2px; background:var(--accent); opacity:.35}
.week{display:grid; grid-template-columns:repeat(7, minmax(0,1fr)); gap:10px}
.day{background:var(--surface); border:1px solid var(--line); border-radius:14px; padding:10px; display:flex; flex-direction:column; gap:8px; min-height:220px; min-width:0}
.day.today{border-color:var(--accent); box-shadow:0 0 0 1px var(--accent) inset}
.day-h{display:flex; align-items:baseline; justify-content:space-between; gap:6px}
.day-h b{font-size:13px} .day-h span{font-size:22px; font-weight:800; font-variant-numeric:tabular-nums}
.ev{background:var(--info-bg); color:var(--info-ink); border-radius:10px; padding:8px 9px; font-size:12px; text-align:left; border:0; cursor:pointer; display:flex; flex-direction:column; gap:2px; width:100%}
.ev b{font-size:12.5px; color:var(--fg)}
.ev.past{background:var(--surface-2); color:var(--muted)}
.ev.task{background:transparent; border:1px dashed var(--line-3); color:var(--fg-2)}
.ev.task.done{text-decoration:line-through; color:var(--muted)}
.fest{font-size:11px; font-weight:700; color:var(--ora); background:var(--ora-bg); border-radius:8px; padding:4px 8px}
.add-day{margin-top:auto; border:1px dashed var(--line-3); background:transparent; border-radius:10px; height:32px; color:var(--muted); font-size:12px; font-weight:600; cursor:pointer}

/* Tabla */
.tbl{overflow-x:auto; -webkit-overflow-scrolling:touch}
.tbl table{width:100%; border-collapse:collapse; min-width:720px}
.tbl th{font-size:11px; font-weight:700; letter-spacing:.08em; color:var(--muted); text-align:left; padding:8px 10px 8px 0; white-space:nowrap}
.tbl td{padding:12px 10px 12px 0; border-top:1px solid var(--line-2); font-size:13px; vertical-align:middle}
.tbl td.r,.tbl th.r{text-align:right}
.tbl td.num,.tbl td.strong,.tbl td.r{white-space:nowrap}
.tbl .strong{font-weight:700} .tbl .name{font-size:14px; font-weight:600}
.tbl tr.click{cursor:pointer} .tbl tr.click:hover td{background:var(--surface-2)}
.sel-inline{height:32px; border-radius:999px; border:1px solid var(--line-3); padding:0 10px; font-size:12px; font-weight:600; background:var(--surface); color:var(--fg)}
.funnel{display:flex; flex-wrap:wrap; gap:12px}
.fn{flex:1 1 120px; background:var(--surface-2); border-radius:12px; padding:12px 14px; border:1px solid transparent; text-align:left; cursor:pointer; font:inherit; color:inherit}
.fn.on{border-color:var(--line-3); background:var(--surface)}
.fn .top-n{display:flex; align-items:baseline; gap:8px} .fn .top-n b{font-size:22px; font-weight:800}
.fn .top-n span{font-size:12px; font-weight:600}
.fn .v{font-size:12px; color:var(--muted); margin-top:2px}
.fn .bar{margin-top:10px}
.mini{flex:1; background:var(--surface-2); border-radius:12px; padding:10px 12px}
.mini b{font-size:22px; font-weight:800; line-height:1.1; display:block}
.mini span{display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; color:var(--fg-3); margin-top:4px}
.mini i{width:8px; height:8px; border-radius:50%; display:inline-block}

/* Toolbar */
.toolbar{display:flex; flex-wrap:wrap; align-items:center; gap:10px}
.toolbar .grow{flex:1 1 220px}
.in{height:44px; border:1px solid var(--line-3); border-radius:12px; padding:0 12px; background:var(--surface); color:var(--fg); font-size:14px; width:100%; min-width:0}
textarea.in{height:auto; min-height:96px; padding:10px 12px; resize:vertical; line-height:1.5}
select.in{padding-right:8px}
.in.sm{height:36px; border-radius:10px; font-size:13px; width:auto}
.sbox{display:flex; align-items:center; gap:8px; border:1px solid var(--line-3); border-radius:12px; padding:0 12px; height:44px; color:var(--muted); background:var(--surface)}
.sbox input{flex:1; min-width:0; border:0; background:transparent; font-size:14px; color:var(--fg); height:40px; outline:none}

/* Plantillas, guías */
.tpls{display:flex; flex-wrap:wrap; gap:12px}
.tpl{flex:1 1 130px; text-align:left; background:var(--surface-2); border:1px solid var(--line); border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:10px; cursor:pointer; font:inherit; color:inherit}
.tpl:hover{border-color:var(--line-3)}
.tpl .ic{width:36px; height:36px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center}
.tpl b{display:block; font-size:13.5px} .tpl small{display:block; font-size:12px; color:var(--muted); margin-top:2px}
.step-n{width:32px; height:32px; border-radius:50%; background:var(--chip); color:var(--chip-fg); font-size:13px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex:none}
.guide-item{border-top:1px solid var(--line-2); padding-top:14px; display:flex; gap:12px; text-align:left; background:none; border-left:0; border-right:0; border-bottom:0; cursor:pointer; width:100%; font:inherit; color:inherit; padding-bottom:0}
.guide-item.on .gt{color:var(--link)}
.gt{font-size:14px; font-weight:700}
.steps{display:flex; flex-direction:column; gap:0}
.step{display:flex; gap:12px; padding:14px 0; border-top:1px solid var(--line-2)}
.step label{display:flex; gap:12px; cursor:pointer; flex:1; min-width:0}
.step .st{font-size:14px; font-weight:700}
.step .sd{font-size:13px; color:var(--fg-2); line-height:1.5; margin-top:3px; max-width:65ch}
.step.done .st{color:var(--muted); text-decoration:line-through}
.notice{background:var(--info-bg); border-radius:12px; padding:12px 14px; display:flex; flex-direction:column; gap:4px}
.notice .k{font-size:11px; font-weight:700; letter-spacing:.08em; color:var(--info)}
.notice .x{font-size:13px; color:var(--info-ink); line-height:1.5}
.notice-row{display:flex; gap:10px; align-items:flex-start; justify-content:space-between}
.kb{display:flex; align-items:center; gap:12px; padding:10px 0; border-top:1px solid var(--line-2); width:100%; text-align:left; background:none; border-left:0; border-right:0; border-bottom:0; cursor:pointer; font:inherit; color:inherit}
.kb .ic{width:32px; height:32px; border-radius:10px; background:var(--neu-bg); color:var(--fg-2); display:inline-flex; align-items:center; justify-content:center; flex:none}
.kb b{font-size:14px; font-weight:600; display:block} .kb small{font-size:12px; color:var(--muted)}
.kb:hover b{color:var(--link)}
.prose{font-size:14px; line-height:1.6; color:var(--fg-2); max-width:68ch}
.prose h3{font-size:15px; color:var(--fg); margin:18px 0 6px}
.prose ul{padding-left:20px; margin:6px 0} .prose li{margin:4px 0}
.dl{display:grid; grid-template-columns:max-content 1fr; gap:8px 16px; font-size:13px}
.dl dt{color:var(--muted); font-weight:600} .dl dd{margin:0; min-width:0; overflow-wrap:anywhere}
.copyable{display:inline-flex; align-items:center; gap:6px; user-select:all}
.out{font-family:var(--font); font-size:13px; line-height:1.6; min-height:360px; white-space:pre-wrap}
.two{display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.4fr); gap:20px}

/* Reportes */
.hb{display:grid; grid-template-columns:minmax(90px,200px) 1fr auto; align-items:center; gap:10px; font-size:13px}
.hb-l{color:var(--fg-2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap}
.hb-t{height:12px; background:var(--line-2); border-radius:6px; overflow:hidden; display:flex}
.hb-t i{display:block; height:100%}
.hb-v{font-weight:700; font-variant-numeric:tabular-nums; min-width:70px; text-align:right}
.legend{display:flex; flex-wrap:wrap; gap:14px; font-size:12px; color:var(--muted)}
.legend i{display:inline-block; width:10px; height:10px; border-radius:3px; margin-right:6px; vertical-align:-1px}
.big{font-size:30px; font-weight:800; letter-spacing:-0.02em; font-variant-numeric:tabular-nums}

/* Modal */
.ov{position:fixed; inset:0; background:var(--overlay); display:flex; align-items:flex-start; justify-content:center; padding-block:max(32px, env(safe-area-inset-top,0px)) 32px; padding-left:max(16px, env(safe-area-inset-left,0px)); padding-right:max(16px, env(safe-area-inset-right,0px)); overflow-y:auto; z-index:100}
.modal{background:var(--surface); border-radius:18px; width:100%; max-width:640px; box-shadow:var(--shadow-pop); display:flex; flex-direction:column; border:1px solid var(--line)}
.modal.wide{max-width:960px}
.mh{padding:18px 20px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; gap:12px}
.mh h2{margin:0; font-size:18px; font-weight:800}
.mb{padding:20px; display:flex; flex-direction:column; gap:16px}
.mf{padding:14px 20px; border-top:1px solid var(--line); display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap; align-items:center}
.mf .sp{flex:1}
.fgrid{display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:14px}
.fl{display:flex; flex-direction:column; gap:6px; min-width:0}
.fl.full{grid-column:1/-1}
.fl .lb{font-size:12px; font-weight:700; color:var(--fg-3)}
.fl .hint{font-size:12px; color:var(--muted)}
.fl.chk{flex-direction:row; align-items:center; gap:10px; font-size:14px}
.mchecks{display:flex; flex-wrap:wrap; gap:8px}
.mchecks label{display:inline-flex; align-items:center; gap:8px; border:1px solid var(--line-3); border-radius:999px; padding:6px 12px 6px 8px; font-size:13px; cursor:pointer}
.ferr{color:var(--crit); font-size:13px; margin:0}
.confirm-q{font-size:13px; font-weight:600; color:var(--crit)}
.ai-row{display:flex; align-items:center; gap:10px; flex-wrap:wrap}
.toast{position:fixed; left:50%; transform:translateX(-50%); bottom:calc(20px + env(safe-area-inset-bottom,0px)); background:var(--chip); color:var(--chip-fg); padding:12px 18px; border-radius:12px; font-size:13px; font-weight:600; box-shadow:var(--shadow-pop); z-index:200; max-width:calc(100vw - 32px)}
.scrim{display:none}
.loading{padding:40px 0; color:var(--muted)}

@media (max-width: 960px){
  .app{background:var(--bg)}
  .menu-btn{display:inline-flex}
  .sb{position:fixed; inset:0 auto 0 0; height:100%; transform:translateX(-105%); transition:transform .2s ease; box-shadow:var(--shadow-pop); padding-top:max(20px, env(safe-area-inset-top,0px))}
  .app.nav-open .sb{transform:none}
  .app.nav-open .scrim{display:block; position:fixed; inset:0; background:var(--overlay); z-index:35}
  .week{grid-template-columns:repeat(auto-fill, minmax(150px,1fr))}
  .two{grid-template-columns:1fr}
}
@media (max-width: 640px){
  .top{padding-left:max(16px, env(safe-area-inset-left,0px)); padding-right:max(16px, env(safe-area-inset-right,0px))}
  .content{padding-left:max(16px, env(safe-area-inset-left,0px)); padding-right:max(16px, env(safe-area-inset-right,0px)); padding-block:20px 40px}
  .search{width:100%; order:5}
  .top-r{width:100%}
  .ph h1{font-size:24px}
  .fgrid{grid-template-columns:1fr}
  .week{grid-template-columns:1fr}
  .day{min-height:0}
  .hb{grid-template-columns:minmax(80px,120px) 1fr auto}
  .kpi{flex-basis:140px}
}
@media (prefers-reduced-motion: reduce){ *{animation:none!important; transition:none!important} }
</style>

</head>
<body>
<div class="app" id="app">
  <aside class="sb" id="sb" aria-label="Menú">
    <div class="brand">
      <span class="brand-mark">R</span>
      <div><div class="brand-n">RUBICAM</div><div class="brand-s">WORKSPACE · EQUIPOS</div></div>
    </div>
    <nav class="nav" id="nav" aria-label="Menú principal"></nav>
    <div class="sb-foot" id="sbfoot"></div>
  </aside>
  <div class="scrim" data-act="closeNav"></div>

  <div class="main">
    <header class="top">
      <div class="crumb">
        <button type="button" class="ib sm menu-btn" data-act="openNav" aria-label="Abrir menú"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
        <span>Workspace</span><span aria-hidden="true">›</span><strong id="crumb">Inicio</strong>
      </div>
      <div class="top-r">
        <div class="search" id="gsearch">
          <div class="search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/></svg>
            <input type="search" id="gq" aria-label="Buscar en el workspace" placeholder="Buscar tareas, cotizaciones, clientes, DO…" autocomplete="off">
            <span class="kbd">Ctrl K</span>
          </div>
          <div class="pop" id="gres" hidden></div>
        </div>
        <span id="modechip"></span>
        <span class="hchip t-ok"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>Cúcuta</span>
        <div class="bell">
          <button type="button" class="ib" id="bellbtn" data-act="toggleBell" aria-label="Notificaciones">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 2h-15z"/><path d="M10 21h4"/></svg>
            <span class="bell-dot" id="belldot" hidden></span>
          </button>
          <div class="pop" id="bellpop" hidden></div>
        </div>
      </div>
    </header>
    <main class="content" id="view"><div class="loading">Cargando el workspace…</div></main>
  </div>
</div>
<div id="mroot"></div>
<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>

<script>window.RUBICAM_CFG = <?= $WS_JSON ?>;</script>
<script src="ws-adapter.js?v=<?= (int)@filemtime(__DIR__ . '/ws-adapter.js') ?>"></script>
<script>
(function(){
'use strict';

/* Permisos por sección (llegan del servidor en RUBICAM_CFG). El admin ve todo. */
var _CFG = window.RUBICAM_CFG || {};
var _PERM = _CFG.permisos || null;
var _ESADMIN = !!_CFG.esAdmin;
function puedeVer(k){ if(_ESADMIN || !_PERM) return true; return !!(_PERM[k] && _PERM[k].ver); }
function primeraVista(){ for(var gi=0; gi<NAV.length; gi++){ var it=NAV[gi][1]; for(var ii=0; ii<it.length; ii++){ if(puedeVer(it[ii][0])) return it[ii][0]; } } return 'inicio'; }

/* =========================================================
   Catálogos
   ========================================================= */
const SERV = [
  {k:'importacion', label:'Importación – Agenciamiento aduanero', corto:'Importación', desc:'Nacionalización de mercancías: clasificación, declaración de importación, liquidación de tributos y gestión del levante ante la DIAN.'},
  {k:'exportacion', label:'Exportación – Agenciamiento aduanero', corto:'Exportación', desc:'Salida de mercancías: solicitud de autorización de embarque, declaración de exportación y coordinación con el puerto, aeropuerto o paso de frontera.'},
  {k:'clasificacion', label:'Clasificación arancelaria y asesoría técnica aduanera', corto:'Clasificación arancelaria', desc:'Determinación de la subpartida arancelaria y asesoría sobre requisitos, vistos buenos y tratamiento tributario.'},
  {k:'compra', label:'Compra internacional / Comercialización', corto:'Compra internacional', desc:'Acompañamiento en la compra a proveedores del exterior y en la comercialización de la mercancía.'},
  {k:'logistica', label:'Logística y transporte internacional', corto:'Logística y transporte', desc:'Coordinación del flete internacional marítimo, aéreo o terrestre y del transporte hasta el destino.'},
  {k:'transito', label:'Tránsito Aduanero / Zona Franca', corto:'Tránsito / Zona Franca', desc:'Movimiento de mercancía bajo control aduanero entre aduanas y operaciones con zonas francas.'},
  {k:'dian', label:'Requerimientos DIAN y defensa aduanera: requerimientos, aprehensiones, sanciones.', corto:'Defensa aduanera DIAN', desc:'Atención de requerimientos de la DIAN y defensa en aprehensiones, decomisos y sanciones.'},
  {k:'otro', label:'Otro', corto:'Otro', desc:'Solicitudes que no encajan en las categorías anteriores.'}
];
const AREAS = ['Comercial','Aduanas','Operaciones','Documentación','Administrativo'];
const EQUIPOS = [
  {k:'operaciones', n:'Operaciones', areas:['Operaciones']},
  {k:'comercial', n:'Comercial', areas:['Comercial']},
  {k:'aduanas', n:'Aduanas y documentación', areas:['Aduanas','Documentación']},
  {k:'administrativo', n:'Administrativo', areas:['Administrativo']}
];
const PRIO_TONE = {Alta:'crit', Media:'warn', Baja:'neu'};
const COT_EST = ['Borrador','Enviada','En negociación','Aprobada','Rechazada'];
const COT_TONE = {'Borrador':'neu','Enviada':'info','En negociación':'ora','Aprobada':'ok','Rechazada':'crit'};
const COT_BAR = {'Borrador':'var(--neu-dot)','Enviada':'var(--info-dot)','En negociación':'var(--ora-dot)','Aprobada':'var(--ok-dot)','Rechazada':'var(--crit-dot)'};
const DO_EST = ['Abierta','En trámite','Levante','Cerrada'];
const DO_TONE = {'Abierta':'neu','En trámite':'info','Levante':'pur','Cerrada':'ok'};
const DO_TIPOS = [['IMP','Importación'],['EXP','Exportación'],['TRA','Tránsito']];
const CIUDADES = [['CUC','Cúcuta'],['BOG','Bogotá'],['MED','Medellín'],['CTG','Cartagena'],['BUN','Buenaventura'],['BAQ','Barranquilla']];
const PLAZO_DO = 90;
const ESPERANDO = ['Cliente','Dirección','Transportista','DIAN','Proveedor','Otro'];
const DOC_TIPOS = ['Factura comercial','Lista de empaque','Documento de transporte (BL/AWB/CRT)','Certificado de origen','Mandato aduanero','Declaración Andina del Valor','Declaración de importación (F500)','Declaración de exportación (DEX)','Registro o licencia de importación','Levante','Otro'];
const DOC_EST = ['Pendiente','Recibido','Revisado'];
const DOC_TONE = {Pendiente:'ora', Recibido:'info', Revisado:'ok'};
const SOL_TIPOS = ['Permiso','Vacaciones','Compra','Soporte TI','Viáticos','Otro'];
const SOL_TONE = {Pendiente:'warn', Aprobada:'ok', Rechazada:'crit'};
const AUS_TIPOS = ['Vacaciones','Permiso','Incapacidad','Capacitación'];
const COLORES = [['info','Azul'],['ok','Verde'],['ora','Naranja'],['pur','Morado'],['neu','Gris']];
const CHECK_IMP = ['Factura comercial','Lista de empaque','Documento de transporte (BL/AWB/CRT)','Mandato aduanero vigente','RUT del importador actualizado','Clasificación arancelaria definida','Registro o licencia / vistos buenos','Declaración Andina del Valor','Liquidación de tributos enviada al cliente','Declaración de importación presentada y pagada','Levante autorizado','Entrega realizada y legajo archivado'];
const FESTIVOS = [['2026-01-01','Año Nuevo'],['2026-01-12','Día de los Reyes Magos'],['2026-03-23','Día de San José'],['2026-04-02','Jueves Santo'],['2026-04-03','Viernes Santo'],['2026-05-01','Día del Trabajo'],['2026-05-18','Ascensión del Señor'],['2026-06-08','Corpus Christi'],['2026-06-15','Sagrado Corazón'],['2026-06-29','San Pedro y San Pablo'],['2026-07-20','Día de la Independencia'],['2026-08-07','Batalla de Boyacá'],['2026-08-17','Asunción de la Virgen'],['2026-10-12','Día de la Raza'],['2026-11-02','Todos los Santos'],['2026-11-16','Independencia de Cartagena'],['2026-12-08','Inmaculada Concepción'],['2026-12-25','Navidad']];

const GUIAS = [
  {id:'g-imp', t:'Abrir una DO de importación', area:'Operaciones', pasos:[
    ['Recibir la solicitud del cliente','Confirma el alcance aprobado en la cotización y pide factura comercial, lista de empaque y documento de transporte.'],
    ['Verificar mandato y RUT','Revisa que el mandato aduanero esté vigente y que el RUT del importador esté actualizado.'],
    ['Crear la DO en el workspace','En Operaciones DO › Nueva DO elige ciudad, tipo y cliente. El número se asigna al guardar.'],
    ['Clasificar la mercancía','Determina la subpartida arancelaria y revisa si requiere registro o licencia de importación, vistos buenos o certificados.'],
    ['Preparar la declaración de valor','Con la factura, el flete y el seguro, elabora la Declaración Andina del Valor cuando aplique.'],
    ['Liquidar tributos y pedir anticipo','Calcula arancel e IVA y envía al cliente la solicitud de anticipo con el detalle.'],
    ['Presentar la declaración de importación','Presenta y paga el Formulario 500 en los servicios informáticos de la DIAN y atiende la inspección si la asignan.'],
    ['Obtener el levante y entregar','Con el levante autorizado coordina el retiro y la entrega. Sube los soportes en Documentos.']
  ]},
  {id:'g-cot', t:'Cómo elaborar una cotización', area:'Comercial', pasos:[
    ['Identificar el servicio','Elige el servicio de la lista de Rubicam (importación, exportación, clasificación, logística…) y la modalidad de transporte.'],
    ['Pedir los datos de la carga','Origen y destino, descripción, peso y volumen, valor FOB y subpartida si el cliente la conoce.'],
    ['Calcular honorarios y gastos','Separa los honorarios de Rubicam de los gastos de terceros: fletes, bodegaje y tributos.'],
    ['Revisar con el líder comercial','Valida el margen y las condiciones antes de enviar.'],
    ['Crear la cotización','Regístrala en Cotizaciones y genera el texto con la plantilla «Cotización estándar».'],
    ['Enviar y hacer seguimiento','Cambia el estado a Enviada, fija la fecha de vencimiento y crea una tarea de seguimiento.']
  ]},
  {id:'g-cierre', t:'Cierre y legajo de una operación', area:'Documentación', pasos:[
    ['Confirmar levante y entrega','Verifica que la mercancía se entregó y que no quedan novedades con el cliente o el transportista.'],
    ['Reunir los soportes','Factura, lista de empaque, documento de transporte, declaraciones, levante y comprobantes de pago.'],
    ['Conciliar gastos y facturar','Cruza los gastos de terceros con el anticipo y emite la factura de honorarios.'],
    ['Digitalizar y archivar el legajo','Marca cada documento como Revisado y conserva los soportes durante el tiempo que exige la norma aduanera.'],
    ['Cerrar la DO','Cambia el estado de la DO a Cerrada para que salga del semáforo.']
  ]},
  {id:'g-exp', t:'Registrar una exportación', area:'Operaciones', pasos:[
    ['Recibir factura y lista de empaque','Confirma con el cliente las cantidades, el valor y el destino.'],
    ['Verificar requisitos','Revisa autorizaciones, vistos buenos y certificado de origen según el producto y el destino.'],
    ['Crear la DO de exportación','En Operaciones DO › Nueva DO con tipo Exportación.'],
    ['Solicitar la autorización de embarque','Presenta la solicitud en los servicios informáticos de la DIAN.'],
    ['Coordinar ingreso e inspección','Agenda el ingreso a zona primaria y atiende la inspección si la asignan.'],
    ['Confirmar el embarque','Obtén del transportista la confirmación y el manifiesto de carga.'],
    ['Presentar la declaración de exportación','Presenta la DEX dentro del plazo y sube los soportes en Documentos.']
  ]}
];

const TPLS = [
  {k:'cot', t:'Cotización estándar', area:'Comercial', tone:'pur', icon:'doc'},
  {k:'acta', t:'Acta de reunión', area:'Reuniones', tone:'ok', icon:'video'},
  {k:'carta', t:'Carta a cliente', area:'Comunicación', tone:'info', icon:'mail'},
  {k:'check', t:'Checklist de importación', area:'Operaciones', tone:'ora', icon:'check'}
];

const KB = [
  {k:'directorio', t:'Directorio y contactos útiles', area:'Interno', icon:'users', words:'directorio contactos teléfono correo equipo clientes dian vuce'},
  {k:'festivos', t:'Horarios y calendario de festivos', area:'Interno', icon:'cal', words:'horario festivos calendario 2026 feriados'},
  {k:'normativa', t:'Normativa aduanera: guía rápida', area:'Aduanas', icon:'book', words:'normativa decreto 1165 resolución 046 920 arancel formulario 500 560 600 dex dian'},
  {k:'servicios', t:'Servicios de Rubicam', area:'Comercial', icon:'doc', words:'servicios importación exportación clasificación logística tránsito zona franca dian defensa compra'}
];

/* =========================================================
   Iconos
   ========================================================= */
const I = (d, s=18, w=1.8) => `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${w}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
const P = {
  home:'<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
  check:'<rect x="3.5" y="3.5" width="17" height="17" rx="3"/><path d="m8 12 3 3 5-6"/>',
  cal:'<rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
  users:'<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><circle cx="17" cy="9" r="2.4"/><path d="M17 14.5c2.4 0 4 1.7 4 4.5"/>',
  doc:'<path d="M6 3.5h8l4 4V20.5H6z"/><path d="M14 3.5v4h4M9 12h6M9 16h6"/>',
  clock:'<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
  video:'<rect x="3" y="6" width="13" height="12" rx="3"/><path d="m16 10.5 5-3v9l-5-3"/>',
  grid:'<rect x="3.5" y="3.5" width="17" height="17" rx="3"/><path d="M3.5 9.5h17M9.5 9.5v11"/>',
  stairs:'<path d="M4 20h5v-5h5v-5h6"/><path d="M17 6l3 4"/>',
  book:'<path d="M5 4.5h10a3 3 0 0 1 3 3V20H8a3 3 0 0 1-3-3z"/><path d="M5 17a3 3 0 0 1 3-3h10"/>',
  route:'<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.5 6H14a3 3 0 0 1 0 6h-4a3 3 0 0 0 0 6h5.5"/>',
  building:'<rect x="4" y="3.5" width="16" height="17" rx="2.5"/><path d="M8.5 8h2M13.5 8h2M8.5 12h2M13.5 12h2M10 20.5v-4h4v4"/>',
  folder:'<path d="M3.5 7.5a2 2 0 0 1 2-2H10l2 2.5h6.5a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2z"/>',
  inbox:'<path d="M3.5 13.5 6 5.5h12l2.5 8v5a1.5 1.5 0 0 1-1.5 1.5H5a1.5 1.5 0 0 1-1.5-1.5z"/><path d="M3.5 13.5h5l1 2.5h5l1-2.5h5"/>',
  chart:'<path d="M4 20V4M4 20h16"/><path d="M8.5 16v-4M12.5 16V8M16.5 16v-6"/>',
  plus:'<path d="M12 5v14M5 12h14"/>',
  refresh:'<path d="M20 11a8 8 0 0 0-14-4.5L4 9M4 4v5h5M4 13a8 8 0 0 0 14 4.5L20 15M20 20v-5h-5"/>',
  arrow:'<path d="M5 12h14M13 6l6 6-6 6"/>',
  mail:'<rect x="3" y="5.5" width="18" height="13" rx="3"/><path d="m4 8 8 6 8-6"/>',
  spark:'<path d="M12 3.5 13.8 9l5.7 1.8-5.7 1.8L12 18l-1.8-5.4L4.5 10.8 10.2 9z"/>',
  x:'<path d="M6 6l12 12M18 6 6 18"/>',
  edit:'<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
  trash:'<path d="M4.5 7h15M9.5 7V4.5h5V7M6.5 7l1 13h9l1-13"/>',
  copy:'<rect x="8" y="8" width="12" height="12" rx="2.5"/><path d="M16 8V5.5A1.5 1.5 0 0 0 14.5 4h-9A1.5 1.5 0 0 0 4 5.5v9A1.5 1.5 0 0 0 5.5 16H8"/>',
  pin:'<path d="M9 4h6l-1 6 3 3H7l3-3z"/><path d="M12 13v7"/>',
  chevL:'<path d="m15 6-6 6 6 6"/>', chevR:'<path d="m9 6 6 6-6 6"/>',
  bell:'<path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 2h-15z"/><path d="M10 21h4"/>'
};

const NAV = [
  ['PRINCIPAL', [['inicio','Inicio','home'],['chatbot','Chatbot','spark'],['tareas','Mis tareas','check'],['agenda','Agenda','cal']]],
  ['TRABAJO', [['equipos','Equipos','users'],['cotizaciones','Cotizaciones','doc'],['pendientes','Pendientes','clock'],['reuniones','Reuniones','video']]],
  ['CONOCIMIENTO', [['plantillas','Plantillas','grid'],['guias','Paso a paso','stairs'],['informacion','Información','book']]],
  ['GESTIÓN', [['operaciones','Operaciones DO','route'],['clientes','Clientes','building'],['documentos','Documentos','folder'],['solicitudes','Solicitudes internas','inbox'],['reportes','Reportes','chart']]]
];
const VIEW_NAMES = {}; NAV.forEach(g=>g[1].forEach(([k,n])=>VIEW_NAMES[k]=n));

/* =========================================================
   Utilidades
   ========================================================= */
const $ = s => document.querySelector(s);
const esc = s => String(s==null?'':s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const pad = n => String(n).padStart(2,'0');
const NOW = () => new Date();
const parseD = s => { if(!s) return null; s=String(s); if(s.length===10) s+='T00:00'; const d=new Date(s); return isNaN(d)?null:d; };
const isoD = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
const isoDT = d => `${isoD(d)}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
const day0 = d => new Date(d.getFullYear(), d.getMonth(), d.getDate());
const addDays = (d,n) => { const x=new Date(d); x.setDate(x.getDate()+n); return x; };
const sameDay = (a,b) => !!a && !!b && a.getFullYear()===b.getFullYear() && a.getMonth()===b.getMonth() && a.getDate()===b.getDate();
const diffDays = (a,b) => Math.round((day0(a)-day0(b))/864e5);
const weekStart = d => { const x=day0(d); return addDays(x, -((x.getDay()+6)%7)); };
const DOW = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
const DOWL = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
const MES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
const MESL = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const cap = s => s ? s[0].toUpperCase()+s.slice(1) : s;
const hora = d => { let h=d.getHours(); const s=h<12?'a. m.':'p. m.'; h=h%12||12; return `${h}:${pad(d.getMinutes())} ${s}`; };
const fDia = d => `${DOW[d.getDay()]} ${d.getDate()} ${MES[d.getMonth()]}`;
const fNum = d => d ? `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()}` : '—';
const fLarga = d => `${DOWL[d.getDay()]} ${d.getDate()} de ${MESL[d.getMonth()]} de ${d.getFullYear()}`;
const fDM = d => `${d.getDate()} de ${MESL[d.getMonth()]}`;
const hasTime = s => !!s && String(s).length>10;
function fWhen(s){
  const d=parseD(s); if(!d) return 'Sin fecha';
  const n=diffDays(d,NOW()); const h=hasTime(s)?', '+hora(d):'';
  if(n===0) return 'Hoy'+h; if(n===1) return 'Mañana'+h; if(n===-1) return 'Ayer'+h;
  return fDia(d);
}
function fHace(s){
  const d=parseD(s); if(!d) return '';
  const min=Math.round((NOW()-d)/6e4);
  if(min<1) return 'Ahora'; if(min<60) return `Hace ${min} min`;
  const n=diffDays(NOW(),d);
  if(n===0) return `Hace ${Math.round(min/60)} h`;
  if(n===1) return 'Ayer, '+hora(d);
  return fDia(d)+', '+hora(d);
}
function fDesde(s){ const d=parseD(s); if(!d) return '—'; const n=diffDays(NOW(),d); return n<=0?'Hoy':n===1?'Hace 1 día':`Hace ${n} días`; }
const cop = n => '$ '+Math.round(+n||0).toLocaleString('es-CO');
const copM = n => { n=+n||0; if(n>=1e6) return '$ '+(n/1e6).toLocaleString('es-CO',{maximumFractionDigits:1})+' M'; return cop(n); };
const uid = p => p+'-'+Date.now().toString(36)+Math.random().toString(36).slice(2,6);
const plural = (n, a, b) => `${n} ${n===1?a:b}`;
const pill = (t, tone, dot) => `<span class="pill t-${tone||'neu'}">${dot?'<span class="dot"></span>':''}${esc(t)}</span>`;
const sleep = ms => new Promise(r=>setTimeout(r,ms));

/* =========================================================
   Datos
   ========================================================= */
const COLS = ['tareas','reuniones','cotizaciones','pendientes','operaciones','clientes','documentos','solicitudes','equipo','avisos','actividad','progreso'];
const S = {}; COLS.forEach(c=>S[c]={});
let MODE = 'cargando';  // 'db' | 'local' | 'cargando'
let DB = null, USER = null, SAMPLE = null, READONLY = false, IS_OWNER = false, LOAD_TIMEOUT = false;
const loading = () => MODE==='cargando' || (MODE==='db' && !LOAD_TIMEOUT && LOADED.size < COLS.length);
const ME = {name:'', id:null};
const PROF = {}; const PROF_REQ = new Set();
const LOADED = new Set();
let LAST_SYNC = null;
const LS_KEY = 'rubicam-workspace-v1';

const all = c => Object.values(S[c]);
const get = (c,id) => id ? S[c][id] : null;
const mem = id => get('equipo', id);
const cli = id => get('clientes', id);
const cliName = id => (cli(id)||{}).nombre || '—';
const serv = k => SERV.find(s=>s.k===k) || {k, label:k||'—', corto:k||'—', desc:''};

function lsLoad(){ try{ const raw=localStorage.getItem(LS_KEY); if(raw){ const o=JSON.parse(raw); COLS.forEach(c=>{ if(o && o[c]) S[c]=o[c]; }); } }catch(e){} }
function lsSave(){ try{ localStorage.setItem(LS_KEY, JSON.stringify(S)); }catch(e){} }

const WQ = {};
function onWriteErr(e){
  const code = e && e.code;
  if(code==='invalid_argument'){ READONLY=true; toast('No tienes permiso para cambiar estos datos. Pide acceso de colaborador al dueño.'); }
  else if(code==='quota_exceeded') toast('La base de datos está llena. Borra registros antiguos e intenta de nuevo.');
  else toast('No se pudo guardar el cambio. Revisa tu conexión e intenta de nuevo.');
  scheduleRender();
}
async function retry(fn){ try{ return await fn(); }catch(e){ if(e && e.code==='unavailable'){ await sleep(300+Math.random()*500); return fn(); } throw e; } }
function dbWrite(path, fn){ WQ[path]=(WQ[path]||Promise.resolve()).then(()=>retry(fn)).catch(onWriteErr); return WQ[path]; }
function clean(o){ return JSON.parse(JSON.stringify(o)); }

function put(col, obj){
  if(READONLY){ toast('Estás en modo solo lectura.'); return null; }
  if(loading()){ toast('Espera un momento, aún se están cargando los datos.'); return null; }
  const id = obj.id || uid(col.slice(0,3));
  const body = clean(obj); delete body.id; delete body.ejemplo_;
  S[col][id] = Object.assign({id}, body);
  scheduleRender();
  if(MODE==='db') dbWrite(col+'/'+id, ()=>DB.collection(col).doc(id).set(body));
  else lsSave();
  return id;
}
function patch(col, id, changes){ const cur=get(col,id); if(!cur) return; const next=Object.assign({}, cur, changes); return put(col, next); }
function del(col, id){
  if(READONLY){ toast('Estás en modo solo lectura.'); return; }
  delete S[col][id]; scheduleRender();
  if(MODE==='db') dbWrite(col+'/'+id, ()=>DB.collection(col).doc(id).delete());
  else lsSave();
}
function logAct(texto){
  put('actividad', {texto, uid:ME.id||null, fecha:isoDT(NOW())});
  const acts = all('actividad').sort((a,b)=>(a.fecha||'').localeCompare(b.fecha||''));
  if(acts.length>60) acts.slice(0, acts.length-60).forEach(a=>del('actividad', a.id));
}
const hasExamples = () => COLS.some(c=>all(c).some(d=>d.ejemplo));
const isEmpty = () => COLS.every(c=>c==='progreso' || all(c).length===0);

/* =========================================================
   Cálculos de negocio
   ========================================================= */
const openTasks = () => all('tareas').filter(t=>!t.hecha);
const isLate = t => { const d=parseD(t.vence); if(!d||t.hecha) return false; return hasTime(t.vence) ? d<NOW() : diffDays(d,NOW())<0; };
const dueToday = t => { const d=parseD(t.vence); return !!d && sameDay(d,NOW()); };
const inWeek = (s, ws) => { const d=parseD(s); if(!d) return false; const w=ws||weekStart(NOW()); return d>=w && d<addDays(w,7); };
const cotActivas = () => all('cotizaciones').filter(c=>['Borrador','Enviada','En negociación'].includes(c.estado));
const cotPorVencer = c => { if(!['Enviada','En negociación'].includes(c.estado)) return false; const d=parseD(c.vence); if(!d) return false; const n=diffDays(d,NOW()); return n>=0 && n<=3; };
const cotVencida = c => { if(!['Enviada','En negociación'].includes(c.estado)) return false; const d=parseD(c.vence); return !!d && diffDays(d,NOW())<0; };
const pendAbiertos = () => all('pendientes').filter(p=>!p.resuelto);
const doActivas = () => all('operaciones').filter(o=>o.estado!=='Cerrada');
function doDias(o){ const d=parseD(o.apertura); return d ? Math.max(0, diffDays(NOW(), d)) : 0; }
function doSem(o){ if(o.estado==='Cerrada') return {k:'cerrada', n:'Cerrada', c:'var(--neu-dot)', tone:'neu'}; const n=doDias(o); if(n>75) return {k:'crit', n:'Crítica', c:'var(--crit-dot)', tone:'crit'}; if(n>=45) return {k:'seg', n:'Seguimiento', c:'var(--ora-dot)', tone:'ora'}; return {k:'ok', n:'Al día', c:'var(--ok-dot)', tone:'ok'}; }
const reunionesDia = d => all('reuniones').filter(r=>sameDay(parseD(r.inicio), d)).sort((a,b)=>a.inicio.localeCompare(b.inicio));
const reuFin = r => { const d=parseD(r.inicio); return d ? new Date(d.getTime()+(+r.duracion||30)*6e4) : null; };
const solPend = () => all('solicitudes').filter(s=>s.estado==='Pendiente');
const teamOf = area => EQUIPOS.find(e=>e.areas.includes(area));
function teamStats(team){
  const ws = weekStart(NOW());
  const ts = all('tareas').filter(t=>team.areas.includes(t.area) && (inWeek(t.vence, ws) || (t.hecha && inWeek(t.completada, ws)) || isLate(t)));
  const done = ts.filter(t=>t.hecha).length;
  const soon = ts.filter(t=>!t.hecha && !isLate(t) && parseD(t.vence) && diffDays(parseD(t.vence),NOW())<=2).length;
  const late = ts.filter(isLate).length;
  return {total:ts.length, done, soon, late, pct: ts.length? Math.round(done/ts.length*100) : 0};
}
function ausente(m, d){ if(!m.ausTipo||!m.ausDesde) return false; const a=parseD(m.ausDesde), b=parseD(m.ausHasta||m.ausDesde); return day0(d)>=day0(a) && day0(d)<=day0(b); }
function cumpleProx(m){ if(!m.cumple) return null; const c=parseD(m.cumple); if(!c) return null; const t=day0(NOW()); let d=new Date(t.getFullYear(), c.getMonth(), c.getDate()); if(d<t) d=new Date(t.getFullYear()+1, c.getMonth(), c.getDate()); return d; }
function nextCotNum(){ const n=Math.max(0, ...all('cotizaciones').map(c=>parseInt(String(c.numero||'').replace(/\D/g,''),10)||0)); return 'COT-'+String(n+1).padStart(4,'0'); }
function nextDONum(ciudad, tipo, fecha){ const n=Math.max(0, ...all('operaciones').map(o=>parseInt(String(o.numero||'').slice(-4).replace(/\D/g,''),10)||0)); const yy=String((parseD(fecha)||NOW()).getFullYear()).slice(-2); return `${ciudad||'CUC'}${yy}${tipo||'IMP'}${String(n+1).padStart(4,'0')}`; }
function alerts(){
  const a=[];
  const late=openTasks().filter(isLate); if(late.length) a.push({tone:'crit', t:`${plural(late.length,'tarea vencida','tareas vencidas')}`, s:late.map(x=>x.titulo).slice(0,2).join(' · '), go:'tareas', f:'vencidas'});
  const hoy=openTasks().filter(t=>dueToday(t)&&!isLate(t)); if(hoy.length) a.push({tone:'ora', t:`${plural(hoy.length,'tarea vence','tareas vencen')} hoy`, s:hoy.map(x=>x.titulo).slice(0,2).join(' · '), go:'tareas', f:'hoy'});
  const cv=all('cotizaciones').filter(cotPorVencer); if(cv.length) a.push({tone:'ora', t:`${plural(cv.length,'cotización por vencer','cotizaciones por vencer')}`, s:cv.map(c=>c.numero+' · '+cliName(c.cliente)).join(' · '), go:'cotizaciones'});
  const crit=doActivas().filter(o=>doSem(o).k==='crit'); if(crit.length) a.push({tone:'crit', t:`${plural(crit.length,'DO crítica','DO críticas')}`, s:crit.map(o=>o.numero).join(' · '), go:'operaciones'});
  const viejos=pendAbiertos().filter(p=>diffDays(NOW(),parseD(p.desde)||NOW())>=3); if(viejos.length) a.push({tone:'warn', t:`${plural(viejos.length,'pendiente lleva','pendientes llevan')} 3 días o más`, s:viejos.map(p=>p.titulo).slice(0,2).join(' · '), go:'pendientes'});
  const sp=solPend(); if(sp.length) a.push({tone:'info', t:`${plural(sp.length,'solicitud interna espera','solicitudes internas esperan')} respuesta`, s:sp.map(s=>s.tipo).join(' · '), go:'solicitudes'});
  const nx=reunionesDia(NOW()).find(r=>parseD(r.inicio)>NOW() && (parseD(r.inicio)-NOW())<=60*6e4); if(nx) a.push({tone:'info', t:`Reunión en ${Math.max(1,Math.round((parseD(nx.inicio)-NOW())/6e4))} min`, s:nx.titulo, go:'agenda'});
  return a;
}

/* =========================================================
   Componentes
   ========================================================= */
function avatar(id, cls=''){
  const m = mem(id);
  if(!m) return `<span class="av t-neu ${cls}" title="Sin asignar">—</span>`;
  return `<span class="av t-${esc(m.color||'neu')} ${cls}" title="${esc(m.nombre)}">${esc(m.iniciales||initials(m.nombre))}</span>`;
}
function initials(n){ return String(n||'?').split(/\s+/).filter(Boolean).slice(0,2).map(w=>w[0]).join('').toUpperCase(); }
function memName(id){ const m=mem(id); return m?m.nombre:'Sin asignar'; }
function actorName(a){
  if(a.miembro) return memName(a.miembro);
  if(a.uid){ if(a.uid===ME.id) return ME.name||'Tú'; return PROF[a.uid] || 'Alguien del equipo'; }
  return ME.name || 'Tú';
}
const bar = (pct, color) => `<div class="bar"><i style="width:${Math.max(0,Math.min(100,pct))}%;background:${color}"></i></div>`;
const lnk = (label, act, attrs='') => `<button type="button" class="lnk" data-act="${act}" ${attrs}>${esc(label)} ${I(P.arrow,14,2.2)}</button>`;
const go = (view, label, extra='') => `<a href="#${view}" class="lnk" ${extra}>${esc(label)} ${I(P.arrow,14,2.2)}</a>`;
const emptyBox = (t, s, btn) => `<div class="empty"><div><b>${esc(t)}</b><br>${esc(s)}</div>${btn||''}</div>`;

function taskRow(t, compact){
  const late = isLate(t);
  const meta = t.hecha ? `${esc(t.area||'')} · Completada ${esc(fWhen(t.completada).split(',')[0].toLowerCase())}` : [t.area, t.ref].filter(Boolean).map(esc).join(' · ');
  return `<div class="trow ${t.hecha?'done':''}">
    <input type="checkbox" class="ck" data-chg="toggleTask" data-id="${esc(t.id)}" ${t.hecha?'checked':''} aria-label="${t.hecha?'Reabrir':'Completar'} tarea: ${esc(t.titulo)}">
    <button type="button" class="plain main-t" data-act="editTask" data-id="${esc(t.id)}"><span class="tt">${esc(t.titulo)}</span><span class="ts small muted">${meta||'&nbsp;'}</span></button>
    ${t.hecha ? pill('Lista','ok') : pill(t.prioridad||'Media', PRIO_TONE[t.prioridad]||'warn')}
    <span class="tdue ${late?'late':''}">${late?'Vencida · ':''}${esc(t.hecha?fWhen(t.completada):fWhen(t.vence))}</span>
    ${avatar(t.responsable)}
  </div>`;
}

/* =========================================================
   Vistas
   ========================================================= */
const UI = {homeT:'hoy', tF:'abiertas', tArea:'', tResp:'', tQ:'', wk:0, cotF:'', cotQ:'', doF:'activas', solF:'Pendiente', pendRes:false, kbQ:'', guia:'g-imp', docF:'', docDO:'', cliQ:'', reuF:'proximas'};
let VIEW = 'inicio';

function pageHead(title, sub, actions){ return `<div class="ph"><div style="min-width:0"><h1>${esc(title)}</h1>${sub?`<p>${sub}</p>`:''}</div><div class="ph-a">${actions||''}</div></div>`; }
const btnNew = (label, act) => `<button type="button" class="btn primary" data-act="${act}">${I(P.plus,16,2.4)}${esc(label)}</button>`;

const V = {};

V.inicio = function(){
  const now = NOW();
  const h = now.getHours();
  const saludo = h<12?'Buenos días':h<19?'Buenas tardes':'Buenas noches';
  const nombre = (ME.name||'').split(' ')[0];
  const head = `<div class="ph"><div style="min-width:0"><h1>${saludo}${nombre?', '+esc(nombre):''}</h1><p>${cap(fLarga(now))} · Esto es lo que necesita tu atención hoy.</p></div>
    <div class="ph-a"><span class="upd">Actualizado: <strong class="num">${LAST_SYNC?fNum(LAST_SYNC)+' '+pad(LAST_SYNC.getHours())+':'+pad(LAST_SYNC.getMinutes()):'—'}</strong></span>
    <button type="button" class="ib" data-act="refresh" aria-label="Actualizar">${I(P.refresh)}</button>
    <button type="button" class="btn" data-act="newReu">Agendar reunión</button>
    <button type="button" class="btn" data-act="newCot">Nueva cotización</button>
    ${btnNew('Nueva tarea','newTask')}</div></div>`;

  if(isEmpty()) return head + `<div class="card w-full"><div class="ch"><div><h2>El workspace está vacío</h2><div class="sub">Todavía no hay registros. Empieza por lo que más usas.</div></div></div>
    <div class="chips"><button class="btn" data-act="newMember">Añadir persona al equipo</button><button class="btn" data-act="newCli">Añadir cliente</button><button class="btn" data-act="newCot">Crear cotización</button><button class="btn" data-act="newDO">Abrir DO</button>${btnNew('Crear tarea','newTask')}</div></div>`;

  const ot = openTasks(); const hoyN = ot.filter(dueToday).length; const lateN = ot.filter(isLate).length;
  const ca = cotActivas(); const cpv = all('cotizaciones').filter(cotPorVencer).length;
  const pa = pendAbiertos(); const pc = pa.filter(p=>p.esperando==='Cliente').length;
  const rh = reunionesDia(now); const nx = rh.find(r=>parseD(r.inicio)>now);
  const da = doActivas(); const dc = da.filter(o=>doSem(o).k==='crit').length;
  const kpi = (n,k,s,cls,ic,tone,href) => `<a href="#${href}" class="kpi" style="text-decoration:none"><div><div class="n">${n}</div><div class="k">${k}</div><div class="s ${cls}">${s}</div></div><span class="ic t-${tone}">${I(P[ic],20)}</span></a>`;
  const kpis = `<div class="kpis">
    ${kpi(ot.length,'TAREAS ABIERTAS', lateN?`${lateN} vencidas · ${hoyN} hoy`:`${hoyN} vencen hoy`, (hoyN||lateN)?'hot':'', 'check','info','tareas')}
    ${kpi(ca.length,'COTIZACIONES EN CURSO', `${cpv} por vencer`, cpv?'hot':'', 'doc','pur','cotizaciones')}
    ${kpi(pa.length,'PENDIENTES', `${pc} esperando al cliente`, '', 'clock','ora','pendientes')}
    ${kpi(rh.length,'REUNIONES HOY', nx?`Próxima: ${hora(parseD(nx.inicio))}`:'Sin más reuniones hoy', '', 'video','ok','agenda')}
    ${kpi(da.length,'DO ACTIVAS', `${dc} críticas`, dc?'hot':'good', 'route','neu','operaciones')}
  </div>`;

  // Mis tareas
  const ws = weekStart(now);
  let tl = all('tareas');
  if(UI.homeT==='hoy') tl = tl.filter(t=> t.hecha ? sameDay(parseD(t.completada),now) : (dueToday(t)||isLate(t)));
  else if(UI.homeT==='semana') tl = tl.filter(t=> t.hecha ? inWeek(t.completada,ws) : (inWeek(t.vence,ws)||isLate(t)));
  tl.sort(taskSort);
  const tcard = `<section class="card w2" style="gap:12px"><div class="ch"><h2>Mis tareas</h2><div class="chips">
      ${[['hoy','Hoy'],['semana','Esta semana'],['todas','Todas']].map(([k,n])=>`<button type="button" class="cf ${UI.homeT===k?'on':''}" data-act="ui" data-k="homeT" data-v="${k}">${n}</button>`).join('')}</div></div>
    ${tl.length? tl.slice(0,7).map(t=>taskRow(t)).join('') : emptyBox('Nada pendiente en esta vista','Añade una tarea rápida abajo o cambia el filtro.')}
    <div class="qadd">${I(P.plus,16,2.2)}<input type="text" id="qadd-home" data-key="quickTask" aria-label="Añadir tarea rápida" placeholder="Añadir tarea rápida y presionar Enter…"></div>
    ${tl.length>7?go('tareas',`Ver las ${tl.length} tareas`):''}
  </section>`;

  const agenda = `<section class="card" style="gap:16px"><div class="ch"><h2>Agenda de hoy</h2><a href="#agenda" class="lnk">Abrir agenda</a></div>
    ${timeline(rh)}
    <button type="button" class="btn" style="margin-top:auto" data-act="newReu">${I(P.plus,16,2.2)} Agendar reunión</button></section>`;

  // Cotizaciones
  const cots = all('cotizaciones');
  const fmax = Math.max(1, ...['Borrador','Enviada','En negociación','Aprobada'].map(e=>cots.filter(c=>c.estado===e).length));
  const mesTxt = `${MESL[now.getMonth()]} ${now.getFullYear()}`;
  const recent = cots.slice().sort((a,b)=>String(b.numero).localeCompare(String(a.numero))).slice(0,4);
  const ccard = `<section class="card w2" style="gap:16px"><div class="ch"><div><h2>Cotizaciones</h2><div class="sub">Embudo comercial · ${mesTxt}</div></div>${go('cotizaciones','Ver todas')}</div>
    <div class="funnel">${['Borrador','Enviada','En negociación','Aprobada'].map(e=>{ const n=cots.filter(c=>c.estado===e).length; return `<a href="#cotizaciones" class="fn" data-act="ui" data-k="cotF" data-v="${e}" style="text-decoration:none"><div class="top-n"><b>${n}</b><span style="color:var(--${COT_TONE[e]})">${e==='Enviada'?'Enviadas':e==='Aprobada'?'Aprobadas':e==='En negociación'?'Negociación':e}</span></div>${bar(n/fmax*100, COT_BAR[e])}</a>`; }).join('')}</div>
    ${recent.length? cotTable(recent, true) : emptyBox('Aún no hay cotizaciones','Crea la primera con el botón Nueva cotización.')}
  </section>`;

  const reus = all('reuniones').filter(r=>r.resumen).sort((a,b)=>b.inicio.localeCompare(a.inicio)).slice(0,3);
  const rcard = `<section class="card"><div class="ch"><h2>Resúmenes de reuniones</h2>${SAMPLE?`<span class="pill t-pur">${I(P.spark,12,2)} Con IA</span>`:''}</div>
    ${reus.length? reus.map(r=>reuSummary(r)).join('') : emptyBox('Sin resúmenes todavía','Abre una reunión y escribe o genera su resumen.')}
    ${go('reuniones','Ver todas las reuniones')}</section>`;

  const pend = pa.slice().sort((a,b)=>(a.desde||'').localeCompare(b.desde||'')).slice(0,4);
  const pcard = `<section class="card" style="gap:12px"><div><div class="ch"><h2>Pendientes</h2></div><div class="sub small muted">En espera de terceros</div></div>
    ${pend.length? pend.map(pendRow).join('') : emptyBox('Sin pendientes','Nada está esperando respuesta de terceros.')}
    ${pa.length?go('pendientes',`Ver los ${pa.length} pendientes`):''}</section>`;

  const dcard = `<section class="card"><div class="ch" style="align-items:flex-start"><div><h2>Operaciones DO</h2><div class="sub">Semáforo DIAN · plazo de ${PLAZO_DO} días</div></div><a href="#operaciones" class="lnk">Abrir módulo</a></div>
    ${semaforo(da)}
    ${da.slice().sort((a,b)=>doDias(b)-doDias(a)).slice(0,3).map(doMini).join('') || emptyBox('Sin DO activas','Abre una DO desde Operaciones.')}</section>`;

  const ecard = `<section class="card"><div><div class="ch"><h2>Equipos</h2></div><div class="sub small muted">Avance de tareas de la semana</div></div>
    ${EQUIPOS.map(teamBlock).join('')}</section>`;

  const plcard = `<section class="card"><div><div class="ch"><h2>Plantillas</h2></div><div class="sub small muted">Documentos listos para usar</div></div>
    <div class="tpls">${TPLS.map(tplBtn).join('')}</div>${go('plantillas','Ver todas las plantillas')}</section>`;

  const gcard = `<section class="card"><div><div class="ch"><h2>Paso a paso</h2></div><div class="sub small muted">Guías de los procesos del equipo</div></div>
    ${GUIAS.map((g,i)=>guideItem(g,i)).join('')}${go('guias','Ver todas las guías')}</section>`;

  const icard = `<section class="card"><div><div class="ch"><h2>Información</h2></div><div class="sub small muted">Base de conocimiento de la empresa</div></div>
    <div class="sbox">${I('<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',16,2)}<input type="search" id="kbq-home" data-inp="kbQ" value="${esc(UI.kbQ)}" aria-label="Buscar en la base de conocimiento" placeholder="Buscar un proceso, contacto o política…"></div>
    ${avisosPinned()}
    ${kbList()}</section>`;

  const disp = dispList();
  const acard = `<section class="card" style="flex:1 1 420px;gap:12px"><div><div class="ch"><h2>Disponibilidad del equipo</h2></div><div class="sub small muted">Ausencias y fechas importantes</div></div>
    ${disp.length? disp.join('') : emptyBox('Todo el equipo disponible','No hay ausencias ni cumpleaños en los próximos 7 días.')}</section>`;
  const acts = all('actividad').sort((a,b)=>(b.fecha||'').localeCompare(a.fecha||'')).slice(0,5);
  const accard = `<section class="card" style="flex:1 1 420px;gap:12px"><div><div class="ch"><h2>Actividad reciente</h2></div><div class="sub small muted">Lo último que hizo el equipo</div></div>
    ${acts.length? acts.map(actRow).join('') : emptyBox('Sin actividad','Lo que el equipo cree o cambie aparecerá aquí.')}</section>`;

  return head + kpis + `<div class="row">${tcard}${agenda}</div><div class="row">${ccard}${rcard}</div><div class="row">${pcard}${dcard}${ecard}</div><div class="row">${plcard}${gcard}${icard}</div><div class="row">${acard}${accard}</div>`;
};

function taskSort(a,b){
  if(a.hecha!==b.hecha) return a.hecha?1:-1;
  const pa={Alta:0,Media:1,Baja:2};
  const la=isLate(a), lb=isLate(b); if(la!==lb) return la?-1:1;
  const da=a.vence||'9999', db=b.vence||'9999';
  if(da!==db) return da.localeCompare(db);
  return (pa[a.prioridad]||1)-(pa[b.prioridad]||1);
}

function timeline(rs){
  if(!rs.length) return emptyBox('Sin reuniones hoy','Agenda una reunión para verla aquí.');
  const now=NOW(); let html='<div class="tl">'; let marked=false; let nextDone=false;
  rs.forEach((r,i)=>{
    const ini=parseD(r.inicio), fin=reuFin(r); const last=i===rs.length-1;
    if(!marked && ini>now){ html+=`<div class="tl-now"><span>Ahora · ${hora(now)}</span><span></span></div>`; marked=true; }
    const past = fin<=now, live = ini<=now && fin>now;
    const isNext = !past && !live && !nextDone; if(isNext) nextDone=true;
    const mins = Math.round((ini-now)/6e4);
    const asis = (r.asistentes||[]).length + (+r.externos||0);
    const sub = past ? `Finalizada · ${plural(asis,'asistente','asistentes')}` : live ? `En curso · termina ${hora(fin)}` : isNext ? `${mins<60?`En ${mins} min`:'A las '+hora(ini)} · ${esc(r.area||'')} · ${r.duracion||30} min` : `${r.duracion||30} min · ${esc(r.area||'')}`;
    const dot = past ? 'background:var(--ok-dot)' : live ? 'background:var(--accent)' : isNext ? 'background:var(--surface);border:2px solid var(--fg)' : 'background:var(--surface);border:2px solid var(--neu-dot)';
    html += `<div class="tl-i"><div class="tl-h" ${isNext?'style="color:var(--fg);padding-top:11px"':''}>${hora(ini)}</div>
      <div class="tl-r"><span class="tl-d" style="${dot};${isNext?'margin-top:14px':''}"></span>${last?'':'<span class="tl-l"></span>'}</div>
      <button type="button" class="plain tl-b ${isNext?'next':''}" data-act="editReu" data-id="${esc(r.id)}"><span class="tt" style="font-size:14px;font-weight:${isNext?700:600};display:block">${esc(r.titulo)}</span><span class="small" style="display:block;margin-top:2px;color:${isNext?'var(--accent-text)':'var(--muted)'};font-weight:${isNext?600:400}">${sub}</span></button></div>`;
  });
  if(!marked) html+=`<div class="tl-now"><span>Ahora · ${hora(now)}</span><span></span></div>`;
  return html+'</div>';
}

function reuSummary(r){
  const ac=(r.acuerdos||[]).length, tc=(r.tareasCreadas||[]).length;
  const asis=(r.asistentes||[]).length+(+r.externos||0);
  return `<div class="sec"><button type="button" class="plain" data-act="editReu" data-id="${esc(r.id)}"><span class="tt" style="font-size:14px;font-weight:700;display:block">${esc(r.titulo)}</span></button>
    <div class="small muted">${esc(fWhen(r.inicio))} · ${plural(asis,'asistente','asistentes')}</div>
    <p style="margin:0;font-size:13px;color:var(--fg-2);line-height:1.5">${esc(r.resumen)}</p>
    ${ac||tc?`<div class="chips">${ac?pill(plural(ac,'acuerdo','acuerdos'),'neu'):''}${tc?pill(plural(tc,'tarea creada','tareas creadas'),'info'):''}${ac-tc>0?pill(plural(ac-tc,'sin tarea','sin tarea'),'ora'):''}</div>`:''}</div>`;
}
function pendRow(p){
  const n=diffDays(NOW(), parseD(p.desde)||NOW());
  const tone = p.resuelto?'ok': n>=3?'crit': n>=2?'warn':'neu';
  return `<div class="lr"><button type="button" class="plain grow" data-act="editPend" data-id="${esc(p.id)}"><span class="tt">${esc(p.titulo)}</span><span class="ts">${p.resuelto?'Resuelto':'Esperando '+esc(waitLabel(p.esperando))}${p.ref?' · '+esc(p.ref):''}</span></button>${pill(p.resuelto?'Resuelto':fDesde(p.desde), tone)}</div>`;
}
const waitLabel = w => ({Cliente:'al cliente','Dirección':'a dirección',Transportista:'al transportista',DIAN:'a la DIAN',Proveedor:'al proveedor'}[w] || 'a un tercero');
function semaforo(list){
  const c={ok:0,seg:0,crit:0}; list.forEach(o=>{ const s=doSem(o).k; if(c[s]!=null) c[s]++; });
  return `<div style="display:flex;gap:10px;flex-wrap:wrap">
    <div class="mini"><b>${c.ok}</b><span><i style="background:var(--ok-dot)"></i>Al día</span></div>
    <div class="mini"><b>${c.seg}</b><span><i style="background:var(--ora-dot)"></i>Seguimiento</span></div>
    <div class="mini"><b>${c.crit}</b><span><i style="background:var(--crit-dot)"></i>Críticas</span></div></div>`;
}
function doMini(o){
  const n=doDias(o), s=doSem(o), rest=PLAZO_DO-n;
  return `<button type="button" class="plain sec" style="width:100%" data-act="editDO" data-id="${esc(o.id)}">
    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px"><span class="tt" style="font-size:14px;font-weight:700">${esc(o.numero)}</span><span class="small" style="font-weight:600;color:var(--${s.tone==='ok'?'ok':s.tone==='ora'?'ora':'crit'})">${n} días</span></div>
    <div class="small muted">${esc(cliName(o.cliente))} · ${rest>=0?`Vence en ${rest} días`:`Vencida hace ${-rest} días`}</div>
    ${bar(n/PLAZO_DO*100, s.c)}</button>`;
}
function teamBlock(team){
  const st=teamStats(team);
  const ms=all('equipo').filter(m=>m.area===team.k);
  const avs = ms.slice(0,2).map(m=>avatar(m.id,'xs')).join('') + (ms.length>2?`<span class="av xs t-neu">+${ms.length-2}</span>`:'');
  const sub = st.total ? `${plural(st.total,'tarea','tareas')} · ${st.late?`${st.late} vencidas`: st.soon?`${st.soon} por vencer`:'Al día'}` : 'Sin tareas esta semana';
  return `<div class="sec"><div style="display:flex;align-items:center;justify-content:space-between;gap:12px"><a href="#equipos" class="tt" style="font-size:14px;font-weight:700;color:var(--fg);text-decoration:none">${esc(team.n)}</a><div class="avs">${avs}</div></div>
    <div class="barrow">${bar(st.pct,'var(--ok-bar)')}<span class="pct">${st.pct}%</span></div><div class="small muted">${sub}</div></div>`;
}
function tplBtn(t){ return `<button type="button" class="tpl" data-act="openTpl" data-k="${t.k}"><span class="ic t-${t.tone}">${I(P[t.icon])}</span><span><b>${esc(t.t)}</b><small>${esc(t.area)}</small></span></button>`; }
function guideProg(g){ const p=get('progreso', g.id); const h=(p&&p.hechos)||[]; return h.filter(i=>i<g.pasos.length).length; }
function guideItem(g,i){
  const d=guideProg(g); const n=g.pasos.length;
  return `<button type="button" class="guide-item ${VIEW==='guias'&&UI.guia===g.id?'on':''}" data-act="openGuide" data-g="${g.id}"><span class="step-n">${i+1}</span><span style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px">
    <span class="gt">${esc(g.t)}</span><span class="small muted">${n} pasos · ${esc(g.area)}</span>
    ${d>0?`<span class="barrow">${bar(d/n*100,'var(--accent)')}<span class="pct">${d===n?'Completa':`Paso ${Math.min(d+1,n)} de ${n}`}</span></span>`:''}</span></button>`;
}
function avisosPinned(){
  return all('avisos').filter(a=>a.fijado).sort((a,b)=>(b.fecha||'').localeCompare(a.fecha||'')).map(a=>`<div class="notice"><div class="k">COMUNICADO FIJADO</div><div class="x">${esc(a.texto)}</div></div>`).join('');
}
function kbList(){
  const q=UI.kbQ.trim().toLowerCase();
  const items=KB.filter(k=>!q || (k.t+' '+k.words).toLowerCase().includes(q));
  const ppl = q ? all('equipo').filter(m=>(m.nombre+' '+(m.rol||'')).toLowerCase().includes(q)).map(m=>`<button type="button" class="kb" data-act="editMember" data-id="${esc(m.id)}"><span class="ic">${I(P.users,16)}</span><span><b>${esc(m.nombre)}</b><small>${esc(m.rol||'')} · Equipo</small></span></button>`) : [];
  const clis = q ? all('clientes').filter(c=>(c.nombre+' '+(c.contacto||'')+' '+(c.nit||'')).toLowerCase().includes(q)).map(c=>`<button type="button" class="kb" data-act="cliDetail" data-id="${esc(c.id)}"><span class="ic">${I(P.building,16)}</span><span><b>${esc(c.nombre)}</b><small>${esc(c.contacto||'')} · Cliente</small></span></button>`) : [];
  const out = items.map(k=>`<button type="button" class="kb" data-act="openKB" data-k="${k.k}"><span class="ic">${I(P[k.icon],16)}</span><span><b>${esc(k.t)}</b><small>${esc(k.area)}</small></span></button>`).concat(ppl, clis);
  return out.length? out.join('') : `<div class="small muted">Sin resultados para «${esc(UI.kbQ)}».</div>`;
}
function dispList(){
  const t=day0(NOW()), out=[];
  all('equipo').forEach(m=>{
    if(m.ausTipo && m.ausDesde){
      const a=parseD(m.ausDesde), b=parseD(m.ausHasta||m.ausDesde);
      if(ausente(m,t)) out.push({o:0, h:`${avatar(m.id,'lg')}<div class="grow"><span class="tt">${esc(m.nombre)}</span><span class="ts">${esc(m.ausTipo)}${sameDay(b,t)?' hasta hoy':` hasta el ${fDM(b)}`}${m.ausNota?' · '+esc(m.ausNota):''}</span></div>${pill('Ausente','ora')}`});
      else if(a>t && diffDays(a,t)<=7) out.push({o:1, h:`${avatar(m.id,'lg')}<div class="grow"><span class="tt">${esc(m.nombre)}</span><span class="ts">${esc(m.ausTipo)} ${diffDays(a,t)===1?'mañana':'desde el '+fDM(a)}${m.ausNota?', '+esc(m.ausNota).toLowerCase():''}</span></div>${pill(diffDays(a,t)===1?'Mañana':fDia(a),'neu')}`});
    }
    const c=cumpleProx(m);
    if(c && diffDays(c,t)<=7) out.push({o:2, h:`${avatar(m.id,'lg')}<div class="grow"><span class="tt">${esc(m.nombre)}</span><span class="ts">Cumpleaños el ${DOWL[c.getDay()]} ${fDM(c)}</span></div>${pill(diffDays(c,t)===0?'Hoy':cap(DOWL[c.getDay()]),'info')}`});
  });
  return out.sort((a,b)=>a.o-b.o).map(x=>`<div class="lr" style="flex-wrap:nowrap">${x.h}</div>`);
}
function actRow(a){
  const m=a.miembro?mem(a.miembro):null;
  const av = m? avatar(m.id,'lg') : `<span class="av lg t-info">${esc(initials(actorName(a)))}</span>`;
  return `<div class="lr" style="flex-wrap:nowrap">${av}<div class="grow" style="font-size:13px;color:var(--fg-2)"><strong style="color:var(--fg)">${esc(actorName(a))}</strong> ${esc(a.texto)}</div><span class="small muted" style="white-space:nowrap">${esc(fHace(a.fecha))}</span></div>`;
}

/* ----- Mis tareas ----- */
V.tareas = function(){
  const ws=weekStart(NOW());
  const base=all('tareas');
  const filters=[['abiertas','Abiertas',t=>!t.hecha],['hoy','Hoy',t=>!t.hecha&&(dueToday(t)||isLate(t))],['semana','Esta semana',t=>!t.hecha&&(inWeek(t.vence,ws)||isLate(t))],['vencidas','Vencidas',isLate],['hechas','Completadas',t=>t.hecha],['todas','Todas',()=>true]];
  const f=filters.find(x=>x[0]===UI.tF)||filters[0];
  const q=UI.tQ.trim().toLowerCase();
  let l=base.filter(f[2]).filter(t=>(!UI.tArea||t.area===UI.tArea)&&(!UI.tResp||t.responsable===UI.tResp)&&(!q||(t.titulo+' '+(t.ref||'')).toLowerCase().includes(q)));
  l.sort(taskSort);
  return pageHead('Mis tareas', `${plural(openTasks().length,'tarea abierta','tareas abiertas')} · ${openTasks().filter(isLate).length} vencidas`, btnNew('Nueva tarea','newTask')) +
  `<section class="card w-full" style="gap:12px">
    <div class="chips">${filters.map(([k,n,fn])=>`<button type="button" class="cf ${UI.tF===k?'on':''}" data-act="ui" data-k="tF" data-v="${k}">${n}<span class="c">${base.filter(fn).length}</span></button>`).join('')}</div>
    <div class="toolbar"><div class="sbox grow">${I('<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',16,2)}<input type="search" id="tq" data-inp="tQ" value="${esc(UI.tQ)}" placeholder="Buscar por título o referencia…" aria-label="Buscar tareas"></div>
      <select class="in sm" id="tarea-f" data-chg="ui" data-k="tArea" aria-label="Filtrar por área"><option value="">Todas las áreas</option>${AREAS.map(a=>`<option ${UI.tArea===a?'selected':''}>${a}</option>`).join('')}</select>
      <select class="in sm" id="tresp-f" data-chg="ui" data-k="tResp" aria-label="Filtrar por responsable"><option value="">Todo el equipo</option>${all('equipo').map(m=>`<option value="${esc(m.id)}" ${UI.tResp===m.id?'selected':''}>${esc(m.nombre)}</option>`).join('')}</select></div>
    ${l.length? l.map(t=>taskRow(t)).join('') : emptyBox('No hay tareas con estos filtros','Cambia el filtro o crea una tarea nueva.')}
    <div class="qadd">${I(P.plus,16,2.2)}<input type="text" id="qadd-t" data-key="quickTask" aria-label="Añadir tarea rápida" placeholder="Añadir tarea rápida para hoy y presionar Enter…"></div>
  </section>`;
};

/* ----- Agenda ----- */
V.agenda = function(){
  const ws=addDays(weekStart(NOW()), UI.wk*7); const we=addDays(ws,6);
  const label = ws.getMonth()===we.getMonth() ? `${ws.getDate()} – ${we.getDate()} de ${MESL[we.getMonth()]} de ${we.getFullYear()}` : `${fDM(ws)} – ${fDM(we)} de ${we.getFullYear()}`;
  const days=[...Array(7)].map((_,i)=>addDays(ws,i));
  const cols = days.map(d=>{
    const fest=FESTIVOS.find(f=>f[0]===isoD(d));
    const rs=reunionesDia(d);
    const ts=all('tareas').filter(t=>sameDay(parseD(t.vence),d)).sort((a,b)=>(a.vence||'').localeCompare(b.vence||''));
    const isT=sameDay(d,NOW());
    return `<div class="day ${isT?'today':''}"><div class="day-h"><b>${DOW[d.getDay()]}</b><span>${d.getDate()}</span></div>
      ${fest?`<div class="fest">Festivo · ${esc(fest[1])}</div>`:''}
      ${rs.map(r=>`<button type="button" class="ev ${reuFin(r)<NOW()?'past':''}" data-act="editReu" data-id="${esc(r.id)}"><span class="num">${hora(parseD(r.inicio))} · ${r.duracion||30} min</span><b>${esc(r.titulo)}</b></button>`).join('')}
      ${ts.map(t=>`<button type="button" class="ev task ${t.hecha?'done':''}" data-act="editTask" data-id="${esc(t.id)}"><span>Tarea${hasTime(t.vence)?' · '+hora(parseD(t.vence)):''}</span><b style="color:inherit">${esc(t.titulo)}</b></button>`).join('')}
      <button type="button" class="add-day" data-act="newReu" data-d="${isoD(d)}">+ Reunión</button></div>`;
  }).join('');
  const nextF = FESTIVOS.filter(f=>parseD(f[0])>=day0(NOW())).slice(0,3);
  return pageHead('Agenda', `Semana del ${label}`, `<button type="button" class="ib" data-act="wk" data-v="-1" aria-label="Semana anterior">${I(P.chevL)}</button><button type="button" class="btn" data-act="wk" data-v="0">Hoy</button><button type="button" class="ib" data-act="wk" data-v="1" aria-label="Semana siguiente">${I(P.chevR)}</button>${btnNew('Agendar reunión','newReu')}`) +
    `<div class="week">${cols}</div>
    <div class="row"><section class="card"><div class="ch"><h2>Hoy</h2></div>${timeline(reunionesDia(NOW()))}</section>
    <section class="card"><div class="ch"><h2>Próximos festivos</h2></div>${nextF.map(f=>`<div class="lr"><div class="grow"><span class="tt">${esc(f[1])}</span><span class="ts">${cap(fLarga(parseD(f[0])))}</span></div>${pill(diffDays(parseD(f[0]),NOW())===0?'Hoy':`En ${diffDays(parseD(f[0]),NOW())} días`,'ora')}</div>`).join('')||'<div class="small muted">No quedan festivos este año.</div>'}</section></div>`;
};

/* ----- Equipos ----- */
V.equipos = function(){
  const cards = EQUIPOS.map(team=>{
    const st=teamStats(team); const ms=all('equipo').filter(m=>m.area===team.k);
    return `<section class="card"><div class="ch"><div><h2>${esc(team.n)}</h2><div class="sub">${plural(ms.length,'persona','personas')} · ${team.areas.join(' y ')}</div></div>${pill(st.pct+'% de la semana', st.late?'crit':st.pct>=60?'ok':'neu')}</div>
      <div class="barrow">${bar(st.pct,'var(--ok-bar)')}<span class="pct">${st.done}/${st.total}</span></div>
      ${ms.length? ms.map(m=>{ const op=openTasks().filter(t=>t.responsable===m.id); const aus=ausente(m,NOW());
        return `<div class="lr" style="flex-wrap:nowrap">${avatar(m.id,'lg')}<button type="button" class="plain grow" data-act="editMember" data-id="${esc(m.id)}"><span class="tt">${esc(m.nombre)}</span><span class="ts">${esc(m.rol||'')} · ${plural(op.length,'tarea abierta','tareas abiertas')}${op.filter(isLate).length?` · ${op.filter(isLate).length} vencidas`:''}</span></button>${aus?pill(m.ausTipo,'ora'):pill('Disponible','ok')}</div>`; }).join('') : emptyBox('Sin personas','Añade a alguien a este equipo.')}
    </section>`;
  }).join('');
  return pageHead('Equipos', 'Personas, carga de trabajo y avance semanal por equipo', btnNew('Añadir persona','newMember')) + `<div class="row">${cards}</div>`;
};

/* ----- Cotizaciones ----- */
function cotTable(list, compact){
  return `<div class="tbl"><table><thead><tr><th>N.º</th><th>CLIENTE</th><th>SERVICIO</th><th class="r">VALOR</th><th>ESTADO</th><th>VENCE</th>${compact?'':'<th></th>'}</tr></thead><tbody>
  ${list.map(c=>{ const pv=cotPorVencer(c), vd=cotVencida(c); const sv=serv(c.servicio); const hasDO=all('operaciones').some(o=>o.cotizacion===c.id);
    return `<tr><td class="strong num">${esc(c.numero)}</td><td class="name"><button type="button" class="plain" data-act="editCot" data-id="${esc(c.id)}"><span class="tt">${esc(cliName(c.cliente))}</span></button></td>
    <td class="muted" style="color:var(--fg-3)">${esc(sv.corto)}</td><td class="r strong num">${cop(c.valor)}</td>
    <td>${compact? pill(c.estado, COT_TONE[c.estado], true) : `<select class="sel-inline" id="ce-${esc(c.id)}" data-chg="cotEstado" data-id="${esc(c.id)}" aria-label="Estado de ${esc(c.numero)}">${COT_EST.map(e=>`<option ${e===c.estado?'selected':''}>${e}</option>`).join('')}</select>`}</td>
    <td class="num" style="color:${vd?'var(--crit)':pv?'var(--accent-text)':'var(--fg-3)'};font-weight:${pv||vd?600:400}">${c.vence?fNum(parseD(c.vence)):'Sin enviar'}</td>
    ${compact?'':`<td class="r"><div class="rowacts" style="justify-content:flex-end">${c.estado==='Aprobada'&&['importacion','exportacion','transito'].includes(c.servicio)&&!hasDO?`<button type="button" class="btn xs" data-act="doFromCot" data-id="${esc(c.id)}">Abrir DO</button>`:''}<button type="button" class="btn xs" data-act="tplCot" data-id="${esc(c.id)}">Texto</button><button type="button" class="ib sm" data-act="editCot" data-id="${esc(c.id)}" aria-label="Editar ${esc(c.numero)}">${I(P.edit,15)}</button></div></td>`}</tr>`; }).join('')}
  </tbody></table></div>`;
}
V.cotizaciones = function(){
  const cs=all('cotizaciones'); const q=UI.cotQ.trim().toLowerCase();
  const l=cs.filter(c=>(!UI.cotF||c.estado===UI.cotF)&&(!q||(c.numero+' '+cliName(c.cliente)+' '+serv(c.servicio).label+' '+(c.detalle||'')).toLowerCase().includes(q))).sort((a,b)=>String(b.numero).localeCompare(String(a.numero)));
  const fmax=Math.max(1,...COT_EST.map(e=>cs.filter(c=>c.estado===e).reduce((s,c)=>s+(+c.valor||0),0)));
  return pageHead('Cotizaciones', `${plural(cotActivas().length,'en curso','en curso')} · ${cop(cotActivas().reduce((s,c)=>s+(+c.valor||0),0))} en juego`, btnNew('Nueva cotización','newCot')) +
  `<section class="card w-full" style="gap:16px">
    <div class="funnel">${COT_EST.map(e=>{ const sub=cs.filter(c=>c.estado===e); const v=sub.reduce((s,c)=>s+(+c.valor||0),0);
      return `<button type="button" class="fn ${UI.cotF===e?'on':''}" data-act="ui" data-k="cotF" data-v="${UI.cotF===e?'':e}"><div class="top-n"><b>${sub.length}</b><span style="color:var(--${COT_TONE[e]})">${e}</span></div><div class="v num">${copM(v)}</div>${bar(v/fmax*100, COT_BAR[e])}</button>`; }).join('')}</div>
    <div class="toolbar"><div class="sbox grow">${I('<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',16,2)}<input type="search" id="cq" data-inp="cotQ" value="${esc(UI.cotQ)}" placeholder="Buscar por número, cliente o servicio…" aria-label="Buscar cotizaciones"></div>${UI.cotF?`<button type="button" class="cf on" data-act="ui" data-k="cotF" data-v="">${esc(UI.cotF)} ✕</button>`:''}</div>
    ${l.length? cotTable(l,false) : emptyBox('No hay cotizaciones aquí','Crea una o cambia el filtro.')}
  </section>`;
};

/* ----- Pendientes ----- */
V.pendientes = function(){
  const ab=pendAbiertos(); const res=all('pendientes').filter(p=>p.resuelto);
  const groups=ESPERANDO.map(w=>[w, ab.filter(p=>p.esperando===w).sort((a,b)=>(a.desde||'').localeCompare(b.desde||''))]).filter(g=>g[1].length);
  const row=p=>{ const n=diffDays(NOW(), parseD(p.desde)||NOW()); return `<div class="lr"><button type="button" class="plain grow" data-act="editPend" data-id="${esc(p.id)}"><span class="tt">${esc(p.titulo)}</span><span class="ts">${p.ref?esc(p.ref)+' · ':''}${esc(memName(p.responsable))}${p.recordatorio?` · Recordado ${esc(fWhen(p.recordatorio).toLowerCase())}`:''}</span></button>
    ${p.resuelto?pill('Resuelto','ok'):pill(fDesde(p.desde), n>=3?'crit':n>=2?'warn':'neu')}
    ${p.resuelto?`<button type="button" class="btn xs" data-act="pendReopen" data-id="${esc(p.id)}">Reabrir</button>`:`<button type="button" class="btn xs" data-act="pendRemind" data-id="${esc(p.id)}">Recordé hoy</button><button type="button" class="btn xs" data-act="pendDone" data-id="${esc(p.id)}">Resuelto</button>`}</div>`; };
  return pageHead('Pendientes', `${plural(ab.length,'cosa espera','cosas esperan')} respuesta de terceros`, btnNew('Nuevo pendiente','newPend')) +
  `<div class="row">${groups.length? groups.map(([w,l])=>`<section class="card"><div class="ch"><h2>Esperando ${esc(waitLabel(w))}</h2>${pill(String(l.length),'neu')}</div>${l.map(row).join('')}</section>`).join('') : `<section class="card w-full">${emptyBox('No hay pendientes abiertos','Cuando algo dependa de un cliente, la DIAN o un transportista, regístralo aquí para no perderlo de vista.')}</section>`}</div>
  ${res.length?`<section class="card w-full"><div class="ch"><h2>Resueltos</h2><button type="button" class="lnk" data-act="ui" data-k="pendRes" data-v="${UI.pendRes?'':'1'}">${UI.pendRes?'Ocultar':'Mostrar '+res.length}</button></div>${UI.pendRes?res.map(row).join(''):''}</section>`:''}`;
};

/* ----- Reuniones ----- */
V.reuniones = function(){
  const now=NOW(); const rs=all('reuniones');
  const prox=rs.filter(r=>reuFin(r)>=now).sort((a,b)=>a.inicio.localeCompare(b.inicio));
  const pas=rs.filter(r=>reuFin(r)<now).sort((a,b)=>b.inicio.localeCompare(a.inicio));
  const l = UI.reuF==='proximas'?prox:pas;
  const card=r=>{ const ac=r.acuerdos||[]; const tc=r.tareasCreadas||[]; const asis=r.asistentes||[];
    return `<section class="card"><div class="ch" style="align-items:flex-start"><div style="min-width:0"><h2>${esc(r.titulo)}</h2><div class="sub">${cap(fWhen(r.inicio))}${!hasTime(r.inicio)?'':''} · ${r.duracion||30} min · ${esc(r.area||'')}${r.lugar?' · '+esc(r.lugar):''}</div></div><button type="button" class="ib sm" data-act="editReu" data-id="${esc(r.id)}" aria-label="Editar reunión">${I(P.edit,15)}</button></div>
      <div style="display:flex;align-items:center;gap:10px"><div class="avs">${asis.slice(0,5).map(id=>avatar(id,'xs')).join('')}</div><span class="small muted">${plural(asis.length+(+r.externos||0),'asistente','asistentes')}${r.externos?` (${r.externos} externos)`:''}</span></div>
      ${r.resumen?`<p style="margin:0;font-size:13px;color:var(--fg-2);line-height:1.5">${esc(r.resumen)}</p>`:`<div class="small muted">Sin resumen todavía.</div>`}
      ${ac.length?`<div class="stack"><div class="small" style="font-weight:700;color:var(--fg-3)">ACUERDOS</div>${ac.map((a,i)=>`<div style="display:flex;align-items:center;gap:10px;justify-content:space-between;border-top:1px solid var(--line-2);padding-top:8px"><span style="font-size:13px">${esc(a)}</span>${tc.includes(a)?pill('Tarea creada','info'):`<button type="button" class="btn xs" data-act="acuerdoTask" data-id="${esc(r.id)}" data-i="${i}">Crear tarea</button>`}</div>`).join('')}</div>`:''}
      <div class="chips" style="margin-top:auto"><button type="button" class="btn sm" data-act="tplActa" data-id="${esc(r.id)}">Generar acta</button></div>
    </section>`; };
  return pageHead('Reuniones', `${plural(prox.length,'próxima','próximas')} · ${plural(pas.length,'realizada','realizadas')}`, btnNew('Agendar reunión','newReu')) +
    `<div class="chips"><button type="button" class="cf ${UI.reuF==='proximas'?'on':''}" data-act="ui" data-k="reuF" data-v="proximas">Próximas<span class="c">${prox.length}</span></button><button type="button" class="cf ${UI.reuF==='pasadas'?'on':''}" data-act="ui" data-k="reuF" data-v="pasadas">Realizadas<span class="c">${pas.length}</span></button>${SAMPLE?`<span class="pill t-pur">${I(P.spark,12,2)} Resúmenes con IA disponibles al editar</span>`:''}</div>
    <div class="row">${l.length? l.map(card).join('') : `<section class="card w-full">${emptyBox(UI.reuF==='proximas'?'No hay reuniones próximas':'Aún no hay reuniones realizadas','Agenda una con el botón de arriba.')}</section>`}</div>`;
};

/* ----- Plantillas ----- */
V.plantillas = function(){
  const desc={cot:'Propuesta formal a partir de una cotización registrada: cliente, servicio, valor y validez.', acta:'Acta con asistentes, resumen y acuerdos de una reunión.', carta:'Carta a un cliente para pedir documentos, confirmar levante o hacer seguimiento.', check:'Lista de control de una DO de importación. Lo que marques queda guardado en la DO.'};
  return pageHead('Plantillas', 'Elige una plantilla; el texto se llena con los datos del workspace y puedes editarlo y copiarlo.') +
    `<div class="row">${TPLS.map(t=>`<section class="card"><div style="display:flex;gap:12px;align-items:center"><span class="tpl" style="flex:none;padding:0;border:0;background:none"><span class="ic t-${t.tone}">${I(P[t.icon])}</span></span><div><h2 style="margin:0;font-size:17px">${esc(t.t)}</h2><div class="small muted">${esc(t.area)}</div></div></div><p style="margin:0;font-size:13px;color:var(--fg-2);line-height:1.5">${desc[t.k]}</p><button type="button" class="btn" style="margin-top:auto" data-act="openTpl" data-k="${t.k}">Usar plantilla</button></section>`).join('')}</div>`;
};

/* ----- Paso a paso ----- */
V.guias = function(){
  const g=GUIAS.find(x=>x.id===UI.guia)||GUIAS[0];
  const p=get('progreso',g.id); const h=new Set((p&&p.hechos)||[]); const d=g.pasos.filter((_,i)=>h.has(i)).length;
  return pageHead('Paso a paso', 'Guías de los procesos del equipo. El avance que marques lo ve todo el equipo.') +
    `<div class="row"><section class="card" style="flex:1 1 300px;max-width:100%">${GUIAS.map((x,i)=>guideItem(x,i)).join('')}</section>
    <section class="card w2"><div class="ch"><div><h2>${esc(g.t)}</h2><div class="sub">${g.pasos.length} pasos · ${esc(g.area)}</div></div><button type="button" class="btn sm" data-act="guideReset" data-g="${g.id}" ${d?'':'disabled'}>Reiniciar</button></div>
      <div class="barrow">${bar(d/g.pasos.length*100,'var(--accent)')}<span class="pct">${d}/${g.pasos.length}</span></div>
      <div class="steps">${g.pasos.map((s,i)=>`<div class="step ${h.has(i)?'done':''}"><label><input type="checkbox" class="ck" style="margin-top:6px" data-chg="guideStep" data-g="${g.id}" data-i="${i}" ${h.has(i)?'checked':''}><span class="step-n" style="width:28px;height:28px;font-size:12px">${i+1}</span><span style="min-width:0"><span class="st" style="display:block">${esc(s[0])}</span><span class="sd" style="display:block">${esc(s[1])}</span></span></label></div>`).join('')}</div>
      <div class="small muted">Guía interna de referencia. Ajusta los pasos con el equipo según cómo trabaja Rubicam.</div>
    </section></div>`;
};

/* ----- Información ----- */
V.informacion = function(){
  const av=all('avisos').sort((a,b)=>(b.fijado?1:0)-(a.fijado?1:0) || (b.fecha||'').localeCompare(a.fecha||''));
  return pageHead('Información', 'Base de conocimiento, comunicados y directorio', btnNew('Nuevo comunicado','newAviso')) +
  `<div class="row"><section class="card w2"><div class="ch"><h2>Comunicados</h2></div>
    ${av.length? av.map(a=>`<div class="notice" style="${a.fijado?'':'background:var(--surface-2)'}"><div class="notice-row"><div style="min-width:0"><div class="k" style="${a.fijado?'':'color:var(--muted)'}">${a.fijado?'FIJADO':'COMUNICADO'} · ${esc(fWhen(a.fecha).toUpperCase())}</div><div class="x" style="${a.fijado?'':'color:var(--fg-2)'}">${esc(a.texto)}</div></div>
      <div class="rowacts"><button type="button" class="ib sm" data-act="pinAviso" data-id="${esc(a.id)}" aria-label="${a.fijado?'Desfijar':'Fijar'}" title="${a.fijado?'Desfijar':'Fijar en Inicio'}">${I(P.pin,15)}</button><button type="button" class="ib sm" data-act="editAviso" data-id="${esc(a.id)}" aria-label="Editar">${I(P.edit,15)}</button></div></div></div>`).join('') : emptyBox('Sin comunicados','Publica uno para que el equipo lo vea en Inicio.')}
  </section>
  <section class="card"><div class="ch"><h2>Buscar</h2></div><div class="sbox">${I('<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',16,2)}<input type="search" id="kbq" data-inp="kbQ" value="${esc(UI.kbQ)}" aria-label="Buscar en la base de conocimiento" placeholder="Proceso, persona, cliente, norma…"></div>${kbList()}</section></div>`;
};

/* ----- Operaciones DO ----- */
V.operaciones = function(){
  const os=all('operaciones');
  const fl=[['activas','Activas',o=>o.estado!=='Cerrada'],['crit','Críticas',o=>doSem(o).k==='crit'],['seg','Seguimiento',o=>doSem(o).k==='seg'],['cerradas','Cerradas',o=>o.estado==='Cerrada'],['todas','Todas',()=>true]];
  const f=fl.find(x=>x[0]===UI.doF)||fl[0];
  const l=os.filter(f[2]).sort((a,b)=>doDias(b)-doDias(a));
  const da=doActivas();
  return pageHead('Operaciones DO', `Semáforo DIAN · plazo de ${PLAZO_DO} días desde la apertura · Al día menos de 45 días, seguimiento de 45 a 75, crítica más de 75`, btnNew('Nueva DO','newDO')) +
  `<section class="card w-full" style="gap:16px">${semaforo(da)}
    <div class="chips">${fl.map(([k,n,fn])=>`<button type="button" class="cf ${UI.doF===k?'on':''}" data-act="ui" data-k="doF" data-v="${k}">${n}<span class="c">${os.filter(fn).length}</span></button>`).join('')}</div>
    ${l.length?`<div class="tbl"><table><thead><tr><th>DO</th><th>CLIENTE</th><th>TIPO</th><th>APERTURA</th><th style="min-width:160px">DÍAS</th><th>ESTADO</th><th>LEGAJO</th><th></th></tr></thead><tbody>
    ${l.map(o=>{ const n=doDias(o), s=doSem(o); const docs=all('documentos').filter(d=>d.operacion===o.id); const rev=docs.filter(d=>d.estado!=='Pendiente').length;
      return `<tr><td class="strong num"><button type="button" class="plain" data-act="editDO" data-id="${esc(o.id)}"><span class="tt">${esc(o.numero)}</span></button></td><td class="name">${esc(cliName(o.cliente))}</td><td>${esc((DO_TIPOS.find(t=>t[0]===o.tipo)||['',o.tipo])[1])}</td><td class="num">${fNum(parseD(o.apertura))}</td>
      <td>${o.estado==='Cerrada'?'<span class="muted">—</span>':`<div class="barrow">${bar(n/PLAZO_DO*100, s.c)}<span class="pct" style="color:var(--${s.tone==='ok'?'ok':s.tone==='ora'?'ora':'crit'})">${n} d</span></div>`}</td>
      <td><select class="sel-inline" id="de-${esc(o.id)}" data-chg="doEstado" data-id="${esc(o.id)}" aria-label="Estado de ${esc(o.numero)}">${DO_EST.map(e=>`<option ${e===o.estado?'selected':''}>${e}</option>`).join('')}</select></td>
      <td><a href="#documentos" class="lnk" data-act="ui" data-k="docDO" data-v="${esc(o.id)}">${docs.length?`${rev}/${docs.length} docs`:'Sin docs'}</a></td>
      <td class="r"><div class="rowacts" style="justify-content:flex-end">${o.tipo==='IMP'?`<button type="button" class="btn xs" data-act="chkDO" data-id="${esc(o.id)}">Checklist ${Object.values(o.checklist||{}).filter(Boolean).length}/${CHECK_IMP.length}</button>`:''}<button type="button" class="ib sm" data-act="editDO" data-id="${esc(o.id)}" aria-label="Editar ${esc(o.numero)}">${I(P.edit,15)}</button></div></td></tr>`; }).join('')}
    </tbody></table></div>` : emptyBox('No hay DO en esta vista','Abre una DO nueva o desde una cotización aprobada.')}
  </section>`;
};

/* ----- Clientes ----- */
V.clientes = function(){
  const q=UI.cliQ.trim().toLowerCase();
  const l=all('clientes').filter(c=>!q||(c.nombre+' '+(c.nit||'')+' '+(c.contacto||'')+' '+(c.ciudad||'')).toLowerCase().includes(q)).sort((a,b)=>a.nombre.localeCompare(b.nombre));
  return pageHead('Clientes', `${plural(all('clientes').length,'cliente','clientes')} registrados`, btnNew('Nuevo cliente','newCli')) +
  `<section class="card w-full"><div class="sbox">${I('<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',16,2)}<input type="search" id="cliq" data-inp="cliQ" value="${esc(UI.cliQ)}" placeholder="Buscar por nombre, NIT, contacto o ciudad…" aria-label="Buscar clientes"></div>
  ${l.length?`<div class="tbl"><table><thead><tr><th>CLIENTE</th><th>NIT</th><th>CIUDAD</th><th>CONTACTO</th><th class="r">COTIZACIONES</th><th class="r">DO ACTIVAS</th><th class="r">APROBADO</th></tr></thead><tbody>
  ${l.map(c=>{ const cs=all('cotizaciones').filter(x=>x.cliente===c.id); const ap=cs.filter(x=>x.estado==='Aprobada').reduce((s,x)=>s+(+x.valor||0),0); const ds=doActivas().filter(o=>o.cliente===c.id).length;
    return `<tr class="click" data-act="cliDetail" data-id="${esc(c.id)}"><td class="name">${esc(c.nombre)}</td><td class="num">${esc(c.nit||'—')}</td><td>${esc(c.ciudad||'—')}</td><td>${esc(c.contacto||'—')}</td><td class="r num">${cs.length}</td><td class="r num">${ds}</td><td class="r strong num">${cop(ap)}</td></tr>`; }).join('')}
  </tbody></table></div>`: emptyBox('No hay clientes','Registra el primero con Nuevo cliente.')}</section>`;
};

/* ----- Documentos ----- */
V.documentos = function(){
  const ds=all('documentos');
  const l=ds.filter(d=>(!UI.docF||d.estado===UI.docF)&&(!UI.docDO||d.operacion===UI.docDO));
  const byDO={}; l.forEach(d=>{ (byDO[d.operacion||'_']=byDO[d.operacion||'_']||[]).push(d); });
  const blocks=Object.keys(byDO).sort().map(k=>{ const o=get('operaciones',k); const list=byDO[k]; const all_=ds.filter(d=>d.operacion===k); const ok=all_.filter(d=>d.estado==='Revisado').length;
    return `<section class="card"><div class="ch"><div><h2>${o?esc(o.numero):'Sin DO'}</h2><div class="sub">${o?esc(cliName(o.cliente)):'Documentos generales'}</div></div>${pill(`${ok}/${all_.length} revisados`, ok===all_.length?'ok':'neu')}</div>
      ${list.map(d=>`<div class="lr"><button type="button" class="plain grow" data-act="editDoc" data-id="${esc(d.id)}"><span class="tt">${esc(d.nombre||d.tipo)}</span><span class="ts">${esc(d.tipo)}${d.fecha?' · '+fNum(parseD(d.fecha)):''} · ${esc(memName(d.responsable))}</span></button>
        <select class="sel-inline" id="dd-${esc(d.id)}" data-chg="docEstado" data-id="${esc(d.id)}" aria-label="Estado de ${esc(d.nombre||d.tipo)}">${DOC_EST.map(e=>`<option ${e===d.estado?'selected':''}>${e}</option>`).join('')}</select>
        ${d.estado==='Pendiente'?`<button type="button" class="btn xs" data-act="docAsk" data-id="${esc(d.id)}">Pedir al cliente</button>`:''}</div>`).join('')}</section>`; }).join('');
  return pageHead('Documentos', 'Legajo de cada DO: qué falta, qué llegó y qué ya se revisó', btnNew('Registrar documento','newDoc')) +
  `<div class="toolbar"><div class="chips">${['','Pendiente','Recibido','Revisado'].map(e=>`<button type="button" class="cf ${UI.docF===e?'on':''}" data-act="ui" data-k="docF" data-v="${e}">${e||'Todos'}<span class="c">${ds.filter(d=>!e||d.estado===e).length}</span></button>`).join('')}</div>
    <select class="in sm" id="docdo-f" data-chg="ui" data-k="docDO" aria-label="Filtrar por DO"><option value="">Todas las DO</option>${all('operaciones').map(o=>`<option value="${esc(o.id)}" ${UI.docDO===o.id?'selected':''}>${esc(o.numero)}</option>`).join('')}</select></div>
  <div class="row">${blocks || `<section class="card w-full">${emptyBox('No hay documentos en esta vista','Registra los documentos que recibes de cada operación.')}</section>`}</div>`;
};

/* ----- Solicitudes ----- */
V.solicitudes = function(){
  const ss=all('solicitudes'); const l=ss.filter(s=>!UI.solF||s.estado===UI.solF).sort((a,b)=>(b.creada||'').localeCompare(a.creada||''));
  return pageHead('Solicitudes internas', 'Permisos, compras y soporte. Al aprobar un permiso o vacaciones, la ausencia aparece en Disponibilidad.', btnNew('Nueva solicitud','newSol')) +
  `<section class="card w-full" style="gap:12px"><div class="chips">${['Pendiente','Aprobada','Rechazada',''].map(e=>`<button type="button" class="cf ${UI.solF===e?'on':''}" data-act="ui" data-k="solF" data-v="${e}">${e?e+'s':'Todas'}<span class="c">${ss.filter(s=>!e||s.estado===e).length}</span></button>`).join('')}</div>
  ${l.length? l.map(s=>`<div class="lr">${avatar(s.solicitante,'lg')}<button type="button" class="plain grow" data-act="editSol" data-id="${esc(s.id)}"><span class="tt">${esc(s.tipo)} · ${esc(memName(s.solicitante))}</span><span class="ts">${esc(s.detalle||'')}${s.desde?` · ${fNum(parseD(s.desde))}${s.hasta&&s.hasta!==s.desde?' – '+fNum(parseD(s.hasta)):''}`:''}${s.comentario?' · «'+esc(s.comentario)+'»':''}</span></button>
    ${pill(s.estado, SOL_TONE[s.estado])}${s.estado==='Pendiente'?`<button type="button" class="btn xs" data-act="solDecide" data-id="${esc(s.id)}" data-v="Aprobada">Aprobar</button><button type="button" class="btn xs" data-act="solDecide" data-id="${esc(s.id)}" data-v="Rechazada">Rechazar</button>`:''}</div>`).join('') : emptyBox('Nada por aquí','No hay solicitudes en esta vista.')}</section>`;
};

/* ----- Reportes ----- */
function hbars(rows, fmt){ const max=Math.max(1,...rows.map(r=>r.v)); return `<div class="stack" style="gap:10px">${rows.map(r=>`<div class="hb"><span class="hb-l" title="${esc(r.l)}">${esc(r.l)}</span><div class="hb-t"><i style="width:${r.v/max*100}%;background:${r.c||'var(--info-dot)'}"></i></div><span class="hb-v">${fmt?fmt(r.v):r.v}</span></div>`).join('')}</div>`; }
V.reportes = function(){
  const cs=all('cotizaciones');
  const ap=cs.filter(c=>c.estado==='Aprobada'), rj=cs.filter(c=>c.estado==='Rechazada');
  const tasa=(ap.length+rj.length)?Math.round(ap.length/(ap.length+rj.length)*100):null;
  const vAp=ap.reduce((s,c)=>s+(+c.valor||0),0); const vCur=cotActivas().reduce((s,c)=>s+(+c.valor||0),0);
  const bySt=COT_EST.map(e=>({l:e, v:cs.filter(c=>c.estado===e).reduce((s,c)=>s+(+c.valor||0),0), c:COT_BAR[e]}));
  const bySv=SERV.map(s=>({l:s.corto, v:cs.filter(c=>c.servicio===s.k).length, c:'var(--pur)'})).filter(r=>r.v).sort((a,b)=>b.v-a.v);
  const tmax=Math.max(1,...EQUIPOS.map(t=>teamStats(t).total));
  const teams=EQUIPOS.map(t=>{ const s=teamStats(t); return `<div class="hb"><span class="hb-l">${esc(t.n)}</span><div class="hb-t"><i style="width:${s.done/tmax*100}%;background:var(--ok-dot)"></i><i style="width:${(s.total-s.done-s.late)/tmax*100}%;background:var(--neu-dot)"></i><i style="width:${s.late/tmax*100}%;background:var(--crit-dot)"></i></div><span class="hb-v">${s.done}/${s.total}</span></div>`; }).join('');
  const da=doActivas(); const prom=da.length?Math.round(da.reduce((s,o)=>s+doDias(o),0)/da.length):0;
  const pa=pendAbiertos(); const pprom=pa.length?(pa.reduce((s,p)=>s+diffDays(NOW(),parseD(p.desde)||NOW()),0)/pa.length).toFixed(1).replace('.',','):'0';
  const tile=(n,k,s)=>`<div class="kpi" style="cursor:default"><div><div class="n">${n}</div><div class="k">${k}</div><div class="s">${s}</div></div></div>`;
  return pageHead('Reportes', 'Calculados en vivo con los datos del workspace') +
  `<div class="kpis">${tile(copM(vAp),'VALOR APROBADO',`${plural(ap.length,'cotización','cotizaciones')}`)}${tile(copM(vCur),'EN CURSO',`${plural(cotActivas().length,'cotización','cotizaciones')}`)}${tile(tasa==null?'—':tasa+'%','TASA DE CIERRE','Aprobadas sobre decididas')}${tile(prom,'DÍAS PROMEDIO DO',`${plural(da.length,'DO activa','DO activas')}`)}${tile(pprom,'DÍAS DE ESPERA',`Promedio de ${plural(pa.length,'pendiente','pendientes')}`)}</div>
  <div class="row"><section class="card"><div class="ch"><h2>Valor por estado</h2></div>${hbars(bySt, copM)}</section>
  <section class="card"><div class="ch"><h2>Cotizaciones por servicio</h2></div>${bySv.length?hbars(bySv):emptyBox('Sin datos','Aún no hay cotizaciones.')}</section></div>
  <div class="row"><section class="card"><div class="ch"><div><h2>Tareas de la semana por equipo</h2><div class="sub">Completadas, abiertas y vencidas</div></div></div><div class="stack" style="gap:10px">${teams}</div><div class="legend"><span><i style="background:var(--ok-dot)"></i>Completadas</span><span><i style="background:var(--neu-dot)"></i>Abiertas</span><span><i style="background:var(--crit-dot)"></i>Vencidas</span></div></section>
  <section class="card"><div class="ch"><h2>DO activas por semáforo</h2></div>${hbars([{l:'Al día',v:da.filter(o=>doSem(o).k==='ok').length,c:'var(--ok-dot)'},{l:'Seguimiento',v:da.filter(o=>doSem(o).k==='seg').length,c:'var(--ora-dot)'},{l:'Críticas',v:da.filter(o=>doSem(o).k==='crit').length,c:'var(--crit-dot)'}])}
  <div class="ch" style="margin-top:6px"><h2>DO por tipo</h2></div>${hbars(DO_TIPOS.map(([k,n])=>({l:n, v:all('operaciones').filter(o=>o.tipo===k).length, c:'var(--info-dot)'})))}</section></div>`;
};

/* =========================================================
   Render
   ========================================================= */
let rq=false;
function scheduleRender(){ if(rq) return; rq=true; requestAnimationFrame(()=>{ rq=false; render(); }); }
function render(){
  const view=$('#view');
  const ae=document.activeElement; let fid=null, ss=null, se=null;
  if(ae && ae.id && view.contains(ae)){ fid=ae.id; try{ ss=ae.selectionStart; se=ae.selectionEnd; }catch(e){} }
  renderNav(); renderTop();
  view.innerHTML = loading() ? `<div class="ph"><div><h1>${esc(VIEW_NAMES[VIEW]||'Inicio')}</h1><p>Conectando con los datos del equipo…</p></div></div>` : (V[VIEW]||V.inicio)();
  if(fid){ const el=document.getElementById(fid); if(el){ el.focus({preventScroll:true}); try{ if(ss!=null) el.setSelectionRange(ss,se); }catch(e){} } }
  resolveProfiles();
  if(VIEW==='chatbot') cbAfter();
}
function renderNav(){
  const badge={tareas:[openTasks().length, openTasks().some(t=>isLate(t)||dueToday(t))], cotizaciones:[cotActivas().length], pendientes:[pendAbiertos().length], solicitudes:[solPend().length]};
  $('#nav').innerHTML = NAV.map(([h,items])=>{ const vis=items.filter(it=>puedeVer(it[0])); if(!vis.length) return ''; return `<div class="nav-g"><div class="nav-h">${h}</div>${vis.map(([k,n,ic])=>{ const b=badge[k]; return `<a href="#${k}" ${VIEW===k?'aria-current="page"':''}>${I(P[ic])}<span class="l">${n}</span>${b&&b[0]?`<span class="nb ${b[1]?'hot':''}">${b[0]}</span>`:''}</a>`; }).join('')}</div>`; }).join('');
  const nm = ME.name || 'Tú';
  const role = MODE==='local' ? 'Modo local' : READONLY ? 'Solo lectura' : IS_OWNER ? 'Administrador' : 'Equipo Rubicam';
  $('#sbfoot').innerHTML = `<div class="me"><span class="me-av">${esc(initials(nm))}</span><div style="min-width:0"><div class="me-n">${esc(nm)}</div><div class="me-r">${esc(role)}</div></div></div>
    ${MODE==='local'?'<div class="sb-note">Los cambios se guardan solo en este navegador.</div>':''}
    <a class="sb-btn" href="${esc((window.RUBICAM_CFG||{}).logoutUrl||'logout.php')}">Cerrar sesión</a>`;
}
function renderTop(){
  $('#crumb').textContent = VIEW_NAMES[VIEW] || 'Inicio';
  let chip='';
  if(MODE==='db') chip = READONLY ? `<span class="hchip t-neu">Solo lectura</span>` : `<span class="hchip t-info">Datos compartidos</span>`;
  else if(MODE==='local') chip = `<span class="hchip t-neu" title="Sin conexión con la base de datos">Solo en este navegador</span>`;
  if(hasExamples()) chip += ` <button type="button" class="hchip t-warn" data-act="clearExamples" title="Borrar los datos de ejemplo">Datos de ejemplo · Borrar</button>`;
  $('#modechip').innerHTML = chip;
  const n=alerts().length; const dot=$('#belldot'); dot.hidden=!n; dot.textContent=n;
  $('#bellbtn').setAttribute('aria-label', n?`Notificaciones: ${n} nuevas`:'Notificaciones');
  if(!$('#bellpop').hidden) fillBell();
}
function resolveProfiles(){
  if(!USER || !USER.profiles) return;
  const need = all('actividad').map(a=>a.uid).filter(id=>id && id!==ME.id && !(id in PROF) && !PROF_REQ.has(id));
  if(!need.length) return;
  need.forEach(id=>PROF_REQ.add(id));
  USER.profiles(need).then(ps=>{ need.forEach(id=>{ PROF[id] = (ps && ps[id] && ps[id].name) || ''; }); scheduleRender(); }).catch(()=>{});
}

/* =========================================================
   Modal y formularios
   ========================================================= */
let MC = null;
function openModal({title, body, foot, wide, onMount}){
  $('#mroot').innerHTML = `<div class="ov" data-act="ovClick"><div class="modal ${wide?'wide':''}" role="dialog" aria-modal="true" aria-labelledby="mtitle"><div class="mh"><h2 id="mtitle">${esc(title)}</h2><button type="button" class="ib sm" data-act="mClose" aria-label="Cerrar">${I(P.x,16,2)}</button></div><div class="mb" id="mbody">${body}</div>${foot!=null?`<div class="mf" id="mfoot">${foot}</div>`:''}</div></div>`;
  if(onMount) onMount();
  const first=$('#mroot').querySelector('input:not([type=checkbox]):not([readonly]),textarea,select'); if(first) first.focus({preventScroll:true});
}
function closeModal(){ $('#mroot').innerHTML=''; MC=null; }

function opts(list, v, empty){ return (empty!=null?`<option value="">${esc(empty)}</option>`:'') + list.map(o=>{ const [val,lab]=Array.isArray(o)?o:[o,o]; return `<option value="${esc(val)}" ${String(v)===String(val)?'selected':''}>${esc(lab)}</option>`; }).join(''); }
function fieldHTML(f, v){
  const id='f_'+f.k; const full=f.full?'full':''; const req=f.req?' *':'';
  const lb=`<span class="lb">${esc(f.l)}${req}</span>`; const hint=f.hint?`<span class="hint">${esc(f.hint)}</span>`:'';
  if(f.t==='html') return `<div class="fl ${full}">${f.html}</div>`;
  if(f.t==='textarea') return `<label class="fl ${full}">${lb}<textarea class="in" id="${id}" name="${f.k}" rows="${f.rows||4}" placeholder="${esc(f.ph||'')}">${esc(v||'')}</textarea>${hint}</label>`;
  if(f.t==='select') return `<label class="fl ${full}">${lb}<select class="in" id="${id}" name="${f.k}">${opts(typeof f.o==='function'?f.o():f.o, v==null?'':v, f.empty)}</select>${hint}</label>`;
  if(f.t==='checkbox') return `<label class="fl chk ${full}"><input type="checkbox" class="ck" id="${id}" name="${f.k}" ${v?'checked':''}> ${esc(f.l)}</label>`;
  if(f.t==='members'){ const sel=new Set(v||[]); return `<div class="fl ${full}">${lb}<div class="mchecks" id="${id}">${all('equipo').map(m=>`<label><input type="checkbox" class="ck" value="${esc(m.id)}" ${sel.has(m.id)?'checked':''}>${avatar(m.id)} ${esc(m.nombre)}</label>`).join('')||'<span class="small muted">Añade personas en Equipos.</span>'}</div>${hint}</div>`; }
  return `<label class="fl ${full}">${lb}<input class="in" id="${id}" name="${f.k}" type="${f.t||'text'}" value="${esc(v==null?'':v)}" placeholder="${esc(f.ph||'')}" ${f.t==='number'?'inputmode="numeric" min="0" step="'+(f.step||1)+'"':''}>${hint}</label>`;
}
function gather(fields){
  const o={};
  fields.forEach(f=>{
    if(f.t==='html') return;
    const el=document.getElementById('f_'+f.k); if(!el) return;
    if(f.t==='checkbox') o[f.k]=el.checked;
    else if(f.t==='members') o[f.k]=[...el.querySelectorAll('input:checked')].map(i=>i.value);
    else if(f.t==='number') o[f.k]= el.value===''?null:Number(el.value);
    else o[f.k]=el.value.trim();
  });
  return o;
}
function formModal({title, fields, vals, onSave, onDelete, saveLabel, wide, onMount}){
  vals=vals||{};
  openModal({title, wide, onMount,
    body:`<form id="mform" class="fgrid" novalidate>${fields.map(f=>fieldHTML(f, vals[f.k])).join('')}</form><p class="ferr" id="ferr" hidden></p>`,
    foot:`${onDelete?'<button type="button" class="btn ghost-danger" data-act="mDelete">'+I(P.trash,16)+' Eliminar</button>':''}<span class="sp"></span><button type="button" class="btn" data-act="mClose">Cancelar</button><button type="submit" form="mform" class="btn primary">${esc(saveLabel||'Guardar')}</button>`});
  MC={fields, onSave, onDelete};
  $('#mform').addEventListener('submit', e=>{
    e.preventDefault();
    const v=gather(fields);
    const miss=fields.filter(f=>f.req && (v[f.k]==null || v[f.k]==='' ));
    if(miss.length){ const er=$('#ferr'); er.hidden=false; er.textContent=`Completa: ${miss.map(f=>f.l).join(', ')}.`; const el=document.getElementById('f_'+miss[0].k); if(el) el.focus(); return; }
    if(READONLY){ toast('Estás en modo solo lectura.'); return; }
    const r=onSave(v); if(r===false) return; closeModal();
  });
}
function confirmBox(text, okLabel, fn){
  openModal({title:'Confirmar', body:`<p style="margin:0;font-size:14px;line-height:1.5">${esc(text)}</p>`, foot:`<span class="sp"></span><button type="button" class="btn" data-act="mClose">Cancelar</button><button type="button" class="btn danger" id="cfok">${esc(okLabel)}</button>`});
  $('#cfok').addEventListener('click', ()=>{ closeModal(); fn(); });
}

const memOpts = () => all('equipo').map(m=>[m.id, m.nombre]);
const cliOpts = () => all('clientes').sort((a,b)=>a.nombre.localeCompare(b.nombre)).map(c=>[c.id, c.nombre]);
const doOpts = () => all('operaciones').map(o=>[o.id, o.numero+' · '+cliName(o.cliente)]);
const servOpts = () => SERV.map(s=>[s.k, s.label]);

/* ----- Editores ----- */
function editTask(id, preset){
  const t=id?get('tareas',id):Object.assign({prioridad:'Media', area:'Operaciones', vence:isoD(NOW())+'T18:00'}, preset||{});
  if(!t) return;
  formModal({title:id?'Editar tarea':'Nueva tarea', vals:t,
    fields:[{k:'titulo',l:'Título',req:1,full:1,ph:'Ej.: Radicar Formulario 500 de la DO…'},{k:'area',l:'Área',t:'select',o:AREAS},{k:'prioridad',l:'Prioridad',t:'select',o:['Alta','Media','Baja']},{k:'vence',l:'Vence',t:'datetime-local'},{k:'responsable',l:'Responsable',t:'select',o:memOpts,empty:'Sin asignar'},{k:'ref',l:'Referencia',ph:'DO, cotización o cliente',full:1},{k:'nota',l:'Notas',t:'textarea',full:1,rows:3},{k:'hecha',l:'Completada',t:'checkbox',full:1}],
    onSave:v=>{ const o=Object.assign({}, t, v); if(v.hecha && !t.hecha) o.completada=isoDT(NOW()); if(!v.hecha) o.completada=null; if(!id) o.creada=isoDT(NOW()); const nid=put('tareas',o); if(!nid) return false; if(!id) logAct(`creó la tarea «${v.titulo}»`); toast(id?'Tarea guardada':'Tarea creada'); },
    onDelete:id?()=>{ del('tareas',id); toast('Tarea eliminada'); }:null});
}
function editReu(id, preset){
  const r=id?get('reuniones',id):Object.assign({duracion:30, area:'Operaciones', inicio:(preset&&preset.d?preset.d:isoD(NOW()))+'T'+(preset&&preset.d?'09:00':pad(Math.min(NOW().getHours()+1,18))+':00'), asistentes:[]}, preset||{});
  if(!r) return;
  const aiBtn = SAMPLE ? `<div class="ai-row"><button type="button" class="btn sm" data-act="aiResumen">${I(P.spark,15,2)} Resumir notas con IA</button><span class="small muted" id="aimsg">Escribe las notas y genera el resumen y los acuerdos.</span></div>` : '';
  formModal({title:id?'Reunión':'Agendar reunión', vals:Object.assign({}, r, {acuerdosTxt:(r.acuerdos||[]).join('\n')}), wide:true,
    fields:[{k:'titulo',l:'Título',req:1,full:1},{k:'inicio',l:'Fecha y hora',t:'datetime-local',req:1},{k:'duracion',l:'Duración (min)',t:'number',step:5},{k:'area',l:'Área',t:'select',o:AREAS},{k:'lugar',l:'Lugar o enlace',ph:'Sala, dirección o videollamada'},{k:'asistentes',l:'Asistentes del equipo',t:'members',full:1},{k:'externos',l:'Asistentes externos',t:'number'},{k:'_sp',t:'html',html:''},
      {k:'notas',l:'Notas de la reunión',t:'textarea',full:1,rows:4,ph:'Lo que se habló, en bruto.'},{k:'_ai',t:'html',full:1,html:aiBtn},{k:'resumen',l:'Resumen',t:'textarea',full:1,rows:3},{k:'acuerdosTxt',l:'Acuerdos (uno por línea)',t:'textarea',full:1,rows:4,hint:'Cada acuerdo se puede convertir en tarea desde Reuniones.'}],
    onSave:v=>{ const o=Object.assign({}, r, v); o.acuerdos=(v.acuerdosTxt||'').split('\n').map(s=>s.trim()).filter(Boolean); delete o.acuerdosTxt; delete o.d; o.tareasCreadas=(r.tareasCreadas||[]).filter(x=>o.acuerdos.includes(x)); if(!put('reuniones',o)) return false; if(!id) logAct(`agendó «${v.titulo}» para ${fWhen(v.inicio).toLowerCase()}`); toast(id?'Reunión guardada':'Reunión agendada'); },
    onDelete:id?()=>{ del('reuniones',id); toast('Reunión eliminada'); }:null});
}
function editCot(id, preset){
  const c=id?get('cotizaciones',id):Object.assign({numero:nextCotNum(), estado:'Borrador', servicio:'importacion'}, preset||{});
  if(!c) return;
  formModal({title:id?`Cotización ${c.numero}`:'Nueva cotización', vals:c,
    fields:[{k:'numero',l:'Número',req:1},{k:'estado',l:'Estado',t:'select',o:COT_EST},{k:'cliente',l:'Cliente',t:'select',o:cliOpts,empty:'Selecciona un cliente'},{k:'clienteNuevo',l:'…o escribe un cliente nuevo',ph:'Razón social'},{k:'servicio',l:'Servicio',t:'select',o:servOpts,full:1},{k:'detalle',l:'Alcance',t:'textarea',full:1,rows:3,ph:'Qué incluye: mercancía, ruta, modalidad…'},{k:'valor',l:'Valor de honorarios (COP)',t:'number',step:1000,req:1},{k:'vence',l:'Válida hasta',t:'date'},{k:'responsable',l:'Responsable',t:'select',o:memOpts,empty:'Sin asignar'}],
    onSave:v=>{
      if(!v.cliente && !v.clienteNuevo){ const er=$('#ferr'); er.hidden=false; er.textContent='Elige un cliente o escribe uno nuevo.'; return false; }
      let cid=v.cliente; if(v.clienteNuevo){ cid=put('clientes',{nombre:v.clienteNuevo}); if(!cid) return false; }
      const o=Object.assign({}, c, v, {cliente:cid}); delete o.clienteNuevo; if(!id) o.creada=isoDT(NOW());
      const prev=c.estado; if(!put('cotizaciones',o)) return false;
      if(!id) logAct(`creó la cotización ${o.numero} para ${cliName(cid)}`); else if(prev!==o.estado) logAct(`cambió ${o.numero} a ${o.estado.toLowerCase()}`);
      toast(id?'Cotización guardada':'Cotización creada');
    },
    onDelete:id?()=>{ del('cotizaciones',id); toast('Cotización eliminada'); }:null});
}
function editDO(id, preset){
  const o=id?get('operaciones',id):Object.assign({ciudad:'CUC', tipo:'IMP', estado:'Abierta', apertura:isoD(NOW()), servicio:'importacion'}, preset||{});
  if(!o) return;
  formModal({title:id?`DO ${o.numero}`:'Nueva DO', vals:o,
    fields:[{k:'numero',l:'Número de DO',ph:'Se asigna al guardar',hint:id?'':'Formato ciudad + año + tipo + consecutivo'},{k:'estado',l:'Estado',t:'select',o:DO_EST},{k:'ciudad',l:'Ciudad / aduana',t:'select',o:CIUDADES},{k:'tipo',l:'Tipo',t:'select',o:DO_TIPOS},{k:'cliente',l:'Cliente',t:'select',o:cliOpts,empty:'Selecciona un cliente',req:1},{k:'servicio',l:'Servicio',t:'select',o:servOpts},{k:'apertura',l:'Fecha de apertura',t:'date',req:1,hint:`El semáforo cuenta ${PLAZO_DO} días desde esta fecha`},{k:'responsable',l:'Responsable',t:'select',o:memOpts,empty:'Sin asignar'},{k:'cotizacion',l:'Cotización asociada',t:'select',o:()=>all('cotizaciones').map(c=>[c.id,c.numero+' · '+cliName(c.cliente)]),empty:'Ninguna'},{k:'notas',l:'Notas',t:'textarea',full:1,rows:3}],
    onSave:v=>{ const x=Object.assign({}, o, v); if(!x.numero) x.numero=nextDONum(x.ciudad, x.tipo, x.apertura); if(!put('operaciones',x)) return false; if(!id) logAct(`abrió la DO ${x.numero}`); else if(o.estado!==x.estado) logAct(`cambió ${x.numero} a ${x.estado.toLowerCase()}`); toast(id?'DO guardada':`DO ${x.numero} abierta`); },
    onDelete:id?()=>{ del('operaciones',id); toast('DO eliminada'); }:null});
}
function editCli(id){
  const c=id?get('clientes',id):{};
  if(!c) return;
  formModal({title:id?'Editar cliente':'Nuevo cliente', vals:c,
    fields:[{k:'nombre',l:'Razón social',req:1,full:1},{k:'nit',l:'NIT',ph:'900.000.000-0'},{k:'ciudad',l:'Ciudad'},{k:'contacto',l:'Contacto'},{k:'cargo',l:'Cargo'},{k:'email',l:'Correo',t:'email'},{k:'telefono',l:'Teléfono',t:'tel'},{k:'notas',l:'Notas',t:'textarea',full:1,rows:3}],
    onSave:v=>{ if(!put('clientes',Object.assign({}, c, v))) return false; if(!id) logAct(`registró el cliente ${v.nombre}`); toast(id?'Cliente guardado':'Cliente creado'); },
    onDelete:id?()=>{ del('clientes',id); toast('Cliente eliminado'); }:null});
}
function cliDetail(id){
  const c=get('clientes',id); if(!c) return;
  const cs=all('cotizaciones').filter(x=>x.cliente===id).sort((a,b)=>String(b.numero).localeCompare(String(a.numero)));
  const os=all('operaciones').filter(x=>x.cliente===id);
  const cp=(v)=> v?`<span class="copyable">${esc(v)}</span> <button type="button" class="ib sm" data-act="copy" data-v="${esc(v)}" aria-label="Copiar">${I(P.copy,14)}</button>`:'—';
  openModal({title:c.nombre, wide:true, body:`<div class="two"><div class="stack"><dl class="dl"><dt>NIT</dt><dd>${esc(c.nit||'—')}</dd><dt>Ciudad</dt><dd>${esc(c.ciudad||'—')}</dd><dt>Contacto</dt><dd>${esc(c.contacto||'—')}${c.cargo?' · '+esc(c.cargo):''}</dd><dt>Correo</dt><dd>${cp(c.email)}</dd><dt>Teléfono</dt><dd>${cp(c.telefono)}</dd>${c.notas?`<dt>Notas</dt><dd>${esc(c.notas)}</dd>`:''}</dl>
    <div class="chips"><button type="button" class="btn sm" data-act="newCotFor" data-id="${esc(id)}">Nueva cotización</button><button type="button" class="btn sm" data-act="newDOFor" data-id="${esc(id)}">Abrir DO</button><button type="button" class="btn sm" data-act="tplCartaFor" data-id="${esc(id)}">Escribir carta</button></div></div>
    <div class="stack"><div class="small" style="font-weight:700;color:var(--fg-3)">COTIZACIONES</div>${cs.length?cs.map(x=>`<div class="lr" style="padding:8px 0"><button type="button" class="plain grow" data-act="editCot" data-id="${esc(x.id)}"><span class="tt">${esc(x.numero)} · ${cop(x.valor)}</span><span class="ts">${esc(serv(x.servicio).corto)}</span></button>${pill(x.estado,COT_TONE[x.estado],true)}</div>`).join(''):'<div class="small muted">Sin cotizaciones.</div>'}
    <div class="small" style="font-weight:700;color:var(--fg-3);margin-top:8px">OPERACIONES</div>${os.length?os.map(x=>`<div class="lr" style="padding:8px 0"><button type="button" class="plain grow" data-act="editDO" data-id="${esc(x.id)}"><span class="tt">${esc(x.numero)}</span><span class="ts">Apertura ${fNum(parseD(x.apertura))}</span></button>${pill(x.estado,DO_TONE[x.estado])}</div>`).join(''):'<div class="small muted">Sin operaciones.</div>'}</div></div>`,
    foot:`<span class="sp"></span><button type="button" class="btn" data-act="mClose">Cerrar</button><button type="button" class="btn primary" data-act="editCli" data-id="${esc(id)}">Editar cliente</button>`});
}
function editPend(id){
  const p=id?get('pendientes',id):{esperando:'Cliente', desde:isoD(NOW())};
  if(!p) return;
  formModal({title:id?'Pendiente':'Nuevo pendiente', vals:p,
    fields:[{k:'titulo',l:'Qué se espera',req:1,full:1,ph:'Ej.: Formulario 500 · Textiles del Norte'},{k:'esperando',l:'Esperando a',t:'select',o:ESPERANDO},{k:'desde',l:'Desde',t:'date'},{k:'ref',l:'Referencia',ph:'DO o cotización'},{k:'responsable',l:'Responsable del seguimiento',t:'select',o:memOpts,empty:'Sin asignar'},{k:'nota',l:'Notas',t:'textarea',full:1,rows:3},{k:'resuelto',l:'Resuelto',t:'checkbox',full:1}],
    onSave:v=>{ if(!put('pendientes',Object.assign({}, p, v))) return false; if(!id) logAct(`registró el pendiente «${v.titulo}»`); toast('Pendiente guardado'); },
    onDelete:id?()=>{ del('pendientes',id); toast('Pendiente eliminado'); }:null});
}
function editDoc(id, preset){
  const d=id?get('documentos',id):Object.assign({estado:'Pendiente', tipo:'Factura comercial'}, preset||{});
  if(!d) return;
  formModal({title:id?'Documento':'Registrar documento', vals:d,
    fields:[{k:'tipo',l:'Tipo',t:'select',o:DOC_TIPOS},{k:'estado',l:'Estado',t:'select',o:DOC_EST},{k:'nombre',l:'Nombre o número',full:1,ph:'Ej.: Factura comercial FC-2231'},{k:'operacion',l:'DO',t:'select',o:doOpts,empty:'Sin DO'},{k:'fecha',l:'Fecha de recibido',t:'date'},{k:'responsable',l:'Responsable',t:'select',o:memOpts,empty:'Sin asignar'},{k:'nota',l:'Notas',t:'textarea',full:1,rows:2}],
    onSave:v=>{ const o=Object.assign({}, d, v); if(!o.nombre) o.nombre=o.tipo; if(!put('documentos',o)) return false; if(!id) logAct(`registró ${o.nombre}${o.operacion&&get('operaciones',o.operacion)?' en '+get('operaciones',o.operacion).numero:''}`); toast('Documento guardado'); },
    onDelete:id?()=>{ del('documentos',id); toast('Documento eliminado'); }:null});
}
function editSol(id){
  const s=id?get('solicitudes',id):{tipo:'Permiso', estado:'Pendiente'};
  if(!s) return;
  formModal({title:id?'Solicitud':'Nueva solicitud', vals:s,
    fields:[{k:'tipo',l:'Tipo',t:'select',o:SOL_TIPOS},{k:'solicitante',l:'Quién la pide',t:'select',o:memOpts,empty:'Selecciona',req:1},{k:'detalle',l:'Detalle',t:'textarea',full:1,rows:3,req:1},{k:'desde',l:'Desde',t:'date',hint:'Para permisos y vacaciones'},{k:'hasta',l:'Hasta',t:'date'},...(id?[{k:'estado',l:'Estado',t:'select',o:['Pendiente','Aprobada','Rechazada']},{k:'comentario',l:'Comentario de la respuesta'}]:[])],
    onSave:v=>{ const o=Object.assign({}, s, v); if(!id) o.creada=isoDT(NOW()); if(!put('solicitudes',o)) return false; if(id && s.estado!==o.estado) applySol(o); if(!id) logAct(`envió una solicitud de ${o.tipo.toLowerCase()}`); toast('Solicitud guardada'); },
    onDelete:id?()=>{ del('solicitudes',id); toast('Solicitud eliminada'); }:null});
}
function applySol(s){
  if(s.estado==='Aprobada' && ['Permiso','Vacaciones'].includes(s.tipo) && s.solicitante && s.desde){
    const m=mem(s.solicitante); if(m) put('equipo', Object.assign({}, m, {ausTipo:s.tipo, ausDesde:s.desde, ausHasta:s.hasta||s.desde, ausNota:''}));
  }
  logAct(`${s.estado==='Aprobada'?'aprobó':'rechazó'} la solicitud de ${String(s.tipo).toLowerCase()} de ${memName(s.solicitante)}`);
}
function editMember(id){
  const m=id?get('equipo',id):{area:'operaciones', color:'info'};
  if(!m) return;
  formModal({title:id?m.nombre:'Añadir persona', vals:m,
    fields:[{k:'nombre',l:'Nombre',req:1},{k:'iniciales',l:'Iniciales',ph:'Se calculan solas'},{k:'area',l:'Equipo',t:'select',o:EQUIPOS.map(e=>[e.k,e.n])},{k:'rol',l:'Cargo'},{k:'color',l:'Color del avatar',t:'select',o:COLORES},{k:'cumple',l:'Cumpleaños',t:'date'},{k:'ausTipo',l:'Ausencia',t:'select',o:AUS_TIPOS,empty:'Ninguna'},{k:'ausNota',l:'Nota de la ausencia',ph:'Ej.: medio día'},{k:'ausDesde',l:'Ausente desde',t:'date'},{k:'ausHasta',l:'Ausente hasta',t:'date'}],
    onSave:v=>{ const o=Object.assign({}, m, v); if(!o.iniciales) o.iniciales=initials(o.nombre); o.iniciales=o.iniciales.toUpperCase().slice(0,3); if(!o.ausTipo){ o.ausDesde=''; o.ausHasta=''; o.ausNota=''; } if(!put('equipo',o)) return false; if(!id) logAct(`añadió a ${o.nombre} al equipo`); toast('Persona guardada'); },
    onDelete:id?()=>{ del('equipo',id); toast('Persona eliminada'); }:null});
}
function editAviso(id){
  const a=id?get('avisos',id):{fijado:true};
  if(!a) return;
  formModal({title:id?'Comunicado':'Nuevo comunicado', vals:a,
    fields:[{k:'texto',l:'Texto',t:'textarea',req:1,full:1,rows:3},{k:'fijado',l:'Fijar en Inicio',t:'checkbox',full:1}],
    onSave:v=>{ const o=Object.assign({}, a, v); if(!id) o.fecha=isoDT(NOW()); if(!put('avisos',o)) return false; if(!id) logAct('publicó un comunicado'); toast('Comunicado guardado'); },
    onDelete:id?()=>{ del('avisos',id); toast('Comunicado eliminado'); }:null});
}

/* ----- Plantillas ----- */
function firma(){ return ME.name || 'Equipo Rubicam'; }
function txtCot(c){
  if(!c) return '';
  const cl=cli(c.cliente)||{}; const sv=serv(c.servicio); const v=parseD(c.vence);
  return `RUBICAM · Comercio exterior y aduanas
Cúcuta, ${fDM(NOW())} de ${NOW().getFullYear()}

Señores
${cl.nombre||'—'}${cl.nit?'\nNIT '+cl.nit:''}${cl.contacto?'\nAtención: '+cl.contacto:''}

Asunto: Cotización ${c.numero} · ${sv.corto}

Respetados señores:

De acuerdo con su solicitud, presentamos nuestra propuesta para el servicio de ${sv.label.replace(/\.$/,'').toLowerCase()}.

Alcance del servicio
${c.detalle||sv.desc}

Valor de honorarios: ${cop(c.valor)} más IVA.
Los tributos aduaneros, fletes y gastos de terceros se facturan aparte, según soportes.
${v?`Esta oferta es válida hasta el ${fDM(v)} de ${v.getFullYear()}.`:'Validez de la oferta: 15 días calendario.'}

Quedamos atentos a sus comentarios.

Cordialmente,

${c.responsable?memName(c.responsable):firma()}
Rubicam`;
}
function txtActa(r){
  if(!r) return '';
  const d=parseD(r.inicio); const as=(r.asistentes||[]).map(memName);
  return `ACTA DE REUNIÓN
${r.titulo}

Fecha: ${cap(fLarga(d))}, ${hora(d)}
Duración: ${r.duracion||30} minutos
Área: ${r.area||'—'}${r.lugar?'\nLugar: '+r.lugar:''}
Asistentes: ${as.join(', ')||'—'}${r.externos?` y ${plural(+r.externos,'invitado externo','invitados externos')}`:''}

Resumen
${r.resumen||'(Pendiente)'}

Acuerdos
${(r.acuerdos||[]).map((a,i)=>`${i+1}. ${a}`).join('\n')||'(Sin acuerdos registrados)'}

Elaboró: ${firma()}`;
}
const CARTAS = {
  docs:{t:'Solicitud de documentos', b:(c,o)=>`Para continuar con el trámite${o?` de la operación ${o.numero}`:''}, le solicitamos amablemente enviarnos los siguientes documentos:\n\n${o?all('documentos').filter(d=>d.operacion===o.id&&d.estado==='Pendiente').map(d=>'• '+(d.nombre||d.tipo)).join('\n')||'• (indique los documentos)':'• Factura comercial\n• Lista de empaque\n• Documento de transporte'}\n\nAgradecemos su pronta respuesta para evitar demoras en la operación.`},
  levante:{t:'Confirmación de levante', b:(c,o)=>`Nos complace informarle que${o?` la operación ${o.numero}`:' su operación'} obtuvo el levante autorizado por la DIAN. Coordinaremos con usted el retiro y la entrega de la mercancía.\n\nQuedamos atentos para acordar la fecha y el lugar de entrega.`},
  seguimiento:{t:'Seguimiento de cotización', b:(c)=>{ const q=all('cotizaciones').filter(x=>x.cliente===c.id&&['Enviada','En negociación'].includes(x.estado))[0]; return `Queremos saber si tuvo oportunidad de revisar nuestra cotización${q?` ${q.numero} por ${cop(q.valor)}`:''}. Con gusto resolvemos cualquier duda o ajustamos el alcance a sus necesidades.`; }}
};
function txtCarta(cid, motivo, oid){
  const c=cli(cid); if(!c) return ''; const o=get('operaciones',oid); const m=CARTAS[motivo]||CARTAS.docs;
  return `Cúcuta, ${fDM(NOW())} de ${NOW().getFullYear()}

Señores
${c.nombre}${c.contacto?'\nAtención: '+c.contacto:''}

Asunto: ${m.t}${o?' · '+o.numero:''}

Respetados señores:

${m.b(c,o)}

Cordialmente,

${firma()}
Rubicam`;
}
function openTpl(k, srcId){
  const tp=TPLS.find(t=>t.k===k); if(!tp) return;
  if(k==='check') return openChecklist(srcId);
  let ctl='', gen;
  if(k==='cot'){ const cs=all('cotizaciones').sort((a,b)=>String(b.numero).localeCompare(String(a.numero))); srcId=srcId||(cs[0]&&cs[0].id);
    ctl=`<label class="fl"><span class="lb">Cotización</span><select class="in" id="tplsrc">${opts(cs.map(c=>[c.id, c.numero+' · '+cliName(c.cliente)]), srcId)}</select></label>`; gen=()=>txtCot(get('cotizaciones',$('#tplsrc').value)); }
  if(k==='acta'){ const rs=all('reuniones').sort((a,b)=>b.inicio.localeCompare(a.inicio)); srcId=srcId||(rs.find(r=>r.resumen)||rs[0]||{}).id;
    ctl=`<label class="fl"><span class="lb">Reunión</span><select class="in" id="tplsrc">${opts(rs.map(r=>[r.id, r.titulo+' · '+fNum(parseD(r.inicio))]), srcId)}</select></label>`; gen=()=>txtActa(get('reuniones',$('#tplsrc').value)); }
  if(k==='carta'){ const cs=all('clientes').sort((a,b)=>a.nombre.localeCompare(b.nombre)); srcId=srcId||(cs[0]&&cs[0].id);
    ctl=`<label class="fl"><span class="lb">Cliente</span><select class="in" id="tplsrc">${opts(cs.map(c=>[c.id,c.nombre]), srcId)}</select></label>
      <label class="fl"><span class="lb">Motivo</span><select class="in" id="tplmot">${opts(Object.keys(CARTAS).map(k=>[k,CARTAS[k].t]),'docs')}</select></label>
      <label class="fl"><span class="lb">DO (opcional)</span><select class="in" id="tpldo"><option value="">Ninguna</option></select></label>`;
    gen=()=>txtCarta($('#tplsrc').value, $('#tplmot').value, $('#tpldo').value); }
  openModal({title:tp.t, wide:true, body:`<div class="two"><div class="stack" style="gap:14px">${ctl}<p class="small muted" style="margin:0;line-height:1.5">El texto se genera con los datos del workspace. Puedes editarlo antes de copiarlo a tu correo o documento.</p></div><label class="fl"><span class="lb">Texto</span><textarea class="in out" id="tplout"></textarea></label></div>`,
    foot:`<span class="sp"></span><button type="button" class="btn" data-act="mClose">Cerrar</button><button type="button" class="btn primary" data-act="copyOut">${I(P.copy,16)} Copiar texto</button>`,
    onMount:()=>{
      const upDO=()=>{ const s=$('#tpldo'); if(!s) return; const cid=$('#tplsrc').value; s.innerHTML='<option value="">Ninguna</option>'+opts(all('operaciones').filter(o=>o.cliente===cid).map(o=>[o.id,o.numero]),''); };
      const re=()=>{ $('#tplout').value = gen() || 'No hay registros para esta plantilla todavía.'; };
      ['#tplsrc','#tplmot','#tpldo'].forEach(s=>{ const el=$(s); if(el) el.addEventListener('change', ()=>{ if(s==='#tplsrc') upDO(); re(); }); });
      upDO(); re();
    }});
}
function openChecklist(oid){
  const ds=all('operaciones').filter(o=>o.tipo==='IMP');
  if(!ds.length){ openModal({title:'Checklist de importación', body:emptyBox('No hay DO de importación','Abre una DO de tipo Importación para usar el checklist.'), foot:`<span class="sp"></span><button type="button" class="btn" data-act="mClose">Cerrar</button><button type="button" class="btn primary" data-act="newDO">Nueva DO</button>`}); return; }
  oid=oid||(ds.find(o=>o.estado!=='Cerrada')||ds[0]).id;
  const draw=()=>{ const o=get('operaciones',oid); const ck=(o&&o.checklist)||{}; const n=CHECK_IMP.filter((_,i)=>ck[i]).length;
    return `<label class="fl"><span class="lb">DO de importación</span><select class="in" id="chksrc">${opts(ds.map(x=>[x.id, x.numero+' · '+cliName(x.cliente)]), oid)}</select></label>
    <div class="barrow">${bar(n/CHECK_IMP.length*100,'var(--ok-bar)')}<span class="pct">${n}/${CHECK_IMP.length}</span></div>
    <div class="steps">${CHECK_IMP.map((t,i)=>`<div class="step ${ck[i]?'done':''}" style="padding:10px 0"><label><input type="checkbox" class="ck" data-chk="${i}" ${ck[i]?'checked':''}><span class="st" style="font-weight:600">${esc(t)}</span></label></div>`).join('')}</div>`; };
  openModal({title:'Checklist de importación', body:`<div id="chkbody">${draw()}</div>`, foot:`<span class="small muted">Se guarda en la DO al marcar.</span><span class="sp"></span><button type="button" class="btn primary" data-act="mClose">Listo</button>`,
    onMount:()=>{ const body=$('#chkbody');
      body.addEventListener('change', e=>{
        if(e.target.id==='chksrc'){ oid=e.target.value; body.innerHTML=draw(); return; }
        const i=e.target.getAttribute('data-chk'); if(i==null) return;
        const o=get('operaciones',oid); if(!o) return; const ck=Object.assign({}, o.checklist||{}); if(e.target.checked) ck[i]=true; else delete ck[i];
        put('operaciones', Object.assign({}, o, {checklist:ck})); body.innerHTML=draw();
      }); }});
}
function openKB(k){
  let html='';
  if(k==='directorio'){
    html=`<h3 style="margin-top:0">Equipo</h3>${all('equipo').map(m=>`<div class="lr" style="flex-wrap:nowrap">${avatar(m.id,'lg')}<div class="grow"><span class="tt">${esc(m.nombre)}</span><span class="ts">${esc(m.rol||'')} · ${esc((EQUIPOS.find(e=>e.k===m.area)||{}).n||'')}</span></div></div>`).join('')||'<p class="muted">Aún no hay personas registradas.</p>'}
    <h3>Clientes</h3>${all('clientes').map(c=>`<div class="lr"><div class="grow"><span class="tt">${esc(c.nombre)}</span><span class="ts">${esc(c.contacto||'')}${c.email?' · '+esc(c.email):''}${c.telefono?' · '+esc(c.telefono):''}</span></div></div>`).join('')||'<p class="muted">Sin clientes.</p>'}
    <h3>Entidades</h3><ul><li>DIAN · <a href="https://www.dian.gov.co" target="_blank" rel="noopener">dian.gov.co</a></li><li>Ventanilla Única de Comercio Exterior (VUCE) · <a href="https://www.vuce.gov.co" target="_blank" rel="noopener">vuce.gov.co</a></li></ul>`;
  }
  if(k==='festivos'){
    const t=day0(NOW());
    html=`<p>Festivos en Colombia en ${NOW().getFullYear()}. Los que ya pasaron aparecen atenuados.</p><div class="stack" style="gap:0">${FESTIVOS.map(f=>{ const d=parseD(f[0]); const past=d<t; return `<div class="lr" style="${past?'opacity:.5':''}"><div class="grow"><span class="tt">${esc(f[1])}</span><span class="ts">${cap(fLarga(d))}</span></div>${!past&&diffDays(d,t)<=30?pill(`En ${diffDays(d,t)} días`,'ora'):''}</div>`; }).join('')}</div>
    <h3>Horario de oficina</h3><p>Administración debe confirmar el horario de atención y publicarlo como comunicado fijado.</p>`;
  }
  if(k==='normativa'){
    html=`<p>Resumen de consulta interna. Antes de aplicar una norma, verifica la versión vigente en <a href="https://www.dian.gov.co" target="_blank" rel="noopener">dian.gov.co</a>.</p>
    <h3>Normas principales</h3><ul><li><b>Decreto 1165 de 2019.</b> Régimen de aduanas: importación, exportación, tránsito y obligaciones de declarantes y agencias de aduanas.</li><li><b>Resolución DIAN 000046 de 2019.</b> Reglamenta el Decreto 1165.</li><li><b>Decreto 920 de 2023.</b> Régimen sancionatorio y de decomiso de mercancías.</li><li><b>Decreto 1881 de 2021.</b> Arancel de Aduanas, con la nomenclatura del Sistema Armonizado.</li></ul>
    <h3>Formularios frecuentes</h3><ul><li><b>Formulario 500.</b> Declaración de importación.</li><li><b>Formulario 560.</b> Declaración Andina del Valor.</li><li><b>Formulario 600.</b> Declaración de exportación (DEX).</li></ul>
    <h3>En el workspace</h3><ul><li>El semáforo de Operaciones DO cuenta ${PLAZO_DO} días desde la apertura de cada DO.</li><li>Usa el Checklist de importación para no omitir soportes.</li></ul>`;
  }
  if(k==='servicios'){
    html=`<p>Servicios que ofrece Rubicam, tal como aparecen en el formulario de contacto del sitio web. Son las mismas opciones que usan Cotizaciones y Operaciones DO.</p>${SERV.map(s=>{ const n=all('cotizaciones').filter(c=>c.servicio===s.k).length; return `<div class="lr"><div class="grow"><span class="tt">${esc(s.label)}</span><span class="ts" style="line-height:1.5">${esc(s.desc)}</span></div>${n?pill(plural(n,'cotización','cotizaciones'),'pur'):''}</div>`; }).join('')}`;
  }
  const kb=KB.find(x=>x.k===k)||{t:''};
  openModal({title:kb.t, wide:true, body:`<div class="prose">${html}</div>`, foot:`<span class="sp"></span><button type="button" class="btn primary" data-act="mClose">Cerrar</button>`});
}

/* =========================================================
   Búsqueda global y notificaciones
   ========================================================= */
function gsearch(q){
  q=q.trim().toLowerCase(); const box=$('#gres');
  if(!q){ box.hidden=true; return; }
  const R=[];
  const add=(k,t,s,act,id,hay)=>{ if(hay.toLowerCase().includes(q)) R.push({k,t,s,act,id}); };
  all('tareas').forEach(x=>add('TAREA',x.titulo,(x.area||'')+(x.ref?' · '+x.ref:''),'editTask',x.id,x.titulo+' '+(x.ref||'')));
  all('cotizaciones').forEach(x=>add('COTIZACIÓN',x.numero+' · '+cliName(x.cliente),serv(x.servicio).corto+' · '+x.estado,'editCot',x.id,x.numero+' '+cliName(x.cliente)+' '+serv(x.servicio).label));
  all('clientes').forEach(x=>add('CLIENTE',x.nombre,(x.nit||'')+' '+(x.ciudad||''),'cliDetail',x.id,x.nombre+' '+(x.nit||'')+' '+(x.contacto||'')));
  all('operaciones').forEach(x=>add('DO',x.numero,cliName(x.cliente)+' · '+x.estado,'editDO',x.id,x.numero+' '+cliName(x.cliente)));
  all('documentos').forEach(x=>add('DOCUMENTO',x.nombre||x.tipo,(get('operaciones',x.operacion)||{}).numero||'','editDoc',x.id,(x.nombre||'')+' '+x.tipo));
  all('reuniones').forEach(x=>add('REUNIÓN',x.titulo,fWhen(x.inicio),'editReu',x.id,x.titulo+' '+(x.resumen||'')));
  all('pendientes').forEach(x=>add('PENDIENTE',x.titulo,x.resuelto?'Resuelto':'Esperando '+waitLabel(x.esperando),'editPend',x.id,x.titulo+' '+(x.ref||'')));
  box.hidden=false;
  box.innerHTML = R.length ? R.slice(0,10).map(r=>`<button type="button" class="pop-i" data-act="${r.act}" data-id="${esc(r.id)}" data-closepop="1"><span class="k">${r.k}</span><span class="t"><b>${esc(r.t)}</b><small>${esc(r.s)}</small></span></button>`).join('') : `<div class="pop-empty">Sin resultados para «${esc(q)}».</div>`;
}
function fillBell(){
  const a=alerts();
  $('#bellpop').innerHTML = a.length ? a.map(x=>`<a href="#${x.go}" class="pop-i" style="text-decoration:none;color:inherit" data-closepop="1" ${x.f?`data-act="ui" data-k="tF" data-v="${x.f}"`:''}><span class="pill t-${x.tone}" style="width:10px;height:10px;padding:0;flex:none"></span><span class="t"><b>${esc(x.t)}</b><small>${esc(x.s)}</small></span></a>`).join('') : `<div class="pop-empty">Todo al día. No hay alertas.</div>`;
}

/* =========================================================
   Acciones
   ========================================================= */
let toastT=null;
function toast(msg){ const t=$('#toast'); t.textContent=msg; t.hidden=false; clearTimeout(toastT); toastT=setTimeout(()=>{ t.hidden=true; }, 3200); }

const ACT = {
  ui:(d)=>{ UI[d.k]=d.v; scheduleRender(); },
  openNav:()=>$('#app').classList.add('nav-open'),
  closeNav:()=>$('#app').classList.remove('nav-open'),
  refresh:(d,el)=>{ el.classList.remove('spin'); void el.offsetWidth; el.classList.add('spin'); LAST_SYNC=NOW(); render(); toast(MODE==='db'?'Los datos se actualizan en vivo. Todo al día.':'Vista actualizada'); },
  toggleBell:()=>{ const p=$('#bellpop'); p.hidden=!p.hidden; if(!p.hidden) fillBell(); },
  mClose:closeModal,
  ovClick:(d,el,e)=>{ if(e.target===el) closeModal(); },
  mDelete:()=>{ const f=$('#mfoot'); f.innerHTML=`<span class="confirm-q">¿Eliminar este registro? No se puede deshacer.</span><span class="sp"></span><button type="button" class="btn" data-act="mClose">Cancelar</button><button type="button" class="btn danger" data-act="mDeleteOk">Sí, eliminar</button>`; },
  mDeleteOk:()=>{ const fn=MC&&MC.onDelete; closeModal(); if(fn) fn(); },
  newTask:()=>editTask(null), editTask:d=>editTask(d.id),
  newReu:d=>editReu(null, d.d?{d:d.d}:null), editReu:d=>editReu(d.id),
  newCot:()=>editCot(null), editCot:d=>editCot(d.id), newCotFor:d=>editCot(null,{cliente:d.id}),
  newDO:()=>editDO(null), editDO:d=>editDO(d.id), newDOFor:d=>editDO(null,{cliente:d.id}),
  doFromCot:d=>{ const c=get('cotizaciones',d.id); if(!c) return; const tipo=c.servicio==='exportacion'?'EXP':c.servicio==='transito'?'TRA':'IMP'; const cc=cli(c.cliente); const city=(CIUDADES.find(x=>cc&&x[1]===cc.ciudad)||['CUC'])[0]; editDO(null,{cliente:c.cliente, servicio:c.servicio, cotizacion:c.id, tipo, ciudad:city, responsable:c.responsable}); },
  newCli:()=>editCli(null), editCli:d=>editCli(d.id), cliDetail:d=>cliDetail(d.id),
  newPend:()=>editPend(null), editPend:d=>editPend(d.id),
  pendDone:d=>{ const p=get('pendientes',d.id); if(p && patch('pendientes',d.id,{resuelto:true, resueltoEn:isoDT(NOW())})){ logAct(`resolvió «${p.titulo}»`); toast('Pendiente resuelto'); } },
  pendReopen:d=>{ patch('pendientes',d.id,{resuelto:false}); },
  pendRemind:d=>{ if(patch('pendientes',d.id,{recordatorio:isoDT(NOW())})) toast('Recordatorio registrado'); },
  newDoc:()=>editDoc(null, UI.docDO?{operacion:UI.docDO}:null), editDoc:d=>editDoc(d.id),
  docAsk:d=>{ const doc=get('documentos',d.id); if(!doc) return; const o=get('operaciones',doc.operacion); const t=`${doc.nombre||doc.tipo} · ${o?cliName(o.cliente):'cliente'}`; if(all('pendientes').some(p=>!p.resuelto&&p.titulo===t)){ toast('Ya hay un pendiente abierto para este documento'); return; } if(put('pendientes',{titulo:t, ref:o?o.numero:'', esperando:'Cliente', desde:isoD(NOW()), responsable:doc.responsable||'', resuelto:false})){ logAct(`pidió ${doc.nombre||doc.tipo} al cliente`); toast('Quedó registrado en Pendientes'); } },
  newSol:()=>editSol(null), editSol:d=>editSol(d.id),
  solDecide:d=>{ const s=get('solicitudes',d.id); if(!s) return; const o=Object.assign({}, s, {estado:d.v, decidida:isoDT(NOW())}); if(put('solicitudes',o)){ applySol(o); toast(d.v==='Aprobada'?'Solicitud aprobada':'Solicitud rechazada'); } },
  newMember:()=>editMember(null), editMember:d=>editMember(d.id),
  newAviso:()=>editAviso(null), editAviso:d=>editAviso(d.id),
  pinAviso:d=>{ const a=get('avisos',d.id); if(a) patch('avisos',d.id,{fijado:!a.fijado}); },
  openTpl:d=>openTpl(d.k), tplCot:d=>openTpl('cot',d.id), tplActa:d=>openTpl('acta',d.id), tplCartaFor:d=>openTpl('carta',d.id), chkDO:d=>openChecklist(d.id),
  openGuide:d=>{ UI.guia=d.g; if(VIEW!=='guias') location.hash='guias'; else scheduleRender(); },
  guideReset:d=>{ put('progreso',{id:d.g, hechos:[]}); },
  openKB:d=>openKB(d.k),
  wk:d=>{ UI.wk = d.v==='0'?0:UI.wk+Number(d.v); scheduleRender(); },
  acuerdoTask:d=>{ const r=get('reuniones',d.id); if(!r) return; const a=(r.acuerdos||[])[+d.i]; if(!a) return;
    const tid=put('tareas',{titulo:a, area:r.area||'Operaciones', ref:r.titulo, prioridad:'Media', vence:isoD(addDays(NOW(),2))+'T17:00', responsable:'', hecha:false, creada:isoDT(NOW())});
    if(tid){ patch('reuniones',r.id,{tareasCreadas:[...(r.tareasCreadas||[]), a]}); logAct(`creó una tarea desde «${r.titulo}»`); toast('Tarea creada. Asígnale responsable en Mis tareas.'); } },
  copy:d=>copyText(d.v),
  copyOut:()=>copyText($('#tplout').value, $('#tplout')),
  aiResumen:async(d,el)=>{
    const notas=($('#f_notas')||{}).value||''; const msg=$('#aimsg');
    if(notas.trim().length<15){ msg.textContent='Escribe primero algunas notas de la reunión.'; return; }
    el.disabled=true; msg.textContent='Generando resumen…';
    try{
      const titulo=($('#f_titulo')||{}).value||'';
      const res=await SAMPLE.json(`Eres asistente de Rubicam, empresa colombiana de comercio exterior y agenciamiento aduanero. A partir de las notas de una reunión, responde SOLO con un objeto JSON con esta forma: {"resumen": "2 o 3 frases en español, concretas", "acuerdos": ["acuerdo accionable, con responsable y fecha si aparecen en las notas"]}. No inventes datos que no estén en las notas.\n\nReunión: ${titulo}\nNotas:\n${notas}`, {modelTier:'quick'});
      if(res && typeof res.resumen==='string'){ $('#f_resumen').value=res.resumen; }
      if(res && Array.isArray(res.acuerdos)){ $('#f_acuerdosTxt').value=res.acuerdos.map(String).join('\n'); }
      msg.textContent='Listo. Revisa el resumen y los acuerdos antes de guardar.';
    }catch(e){
      const c=e&&e.code;
      msg.textContent = c==='not_granted'?'No se dio permiso para usar la IA en esta página.': c==='rate_limited'?'Se alcanzó el límite de uso. Intenta más tarde.':'No se pudo generar el resumen. Intenta de nuevo.';
    }finally{ el.disabled=false; }
  },
  clearExamples:()=>confirmBox('Se borrarán todos los datos de ejemplo del boceto (tareas, clientes, DO, reuniones, etc.). Lo que tú hayas creado se conserva.', 'Borrar datos de ejemplo', ()=>{
    let n=0; COLS.forEach(c=>all(c).forEach(d=>{ if(d.ejemplo){ del(c,d.id); n++; } })); toast(`Se borraron ${n} registros de ejemplo`);
  })
};
function copyText(v, el){
  const fallback=()=>{ if(el){ el.focus(); el.select(); } toast('Selecciona el texto y cópialo con Ctrl+C'); };
  try{ navigator.clipboard.writeText(v).then(()=>toast('Copiado'), fallback); }catch(e){ fallback(); }
}
const CHG = {
  toggleTask:(d,el)=>{ const t=get('tareas',d.id); if(!t) return; const o=Object.assign({}, t, {hecha:el.checked, completada:el.checked?isoDT(NOW()):null}); if(put('tareas',o) && el.checked) logAct(`completó «${t.titulo}»`); },
  ui:(d,el)=>{ UI[d.k]=el.value; scheduleRender(); },
  cotEstado:(d,el)=>{ const c=get('cotizaciones',d.id); if(!c) return; if(put('cotizaciones',Object.assign({}, c, {estado:el.value}))){ logAct(`cambió ${c.numero} a ${el.value.toLowerCase()}`); if(el.value==='Aprobada' && ['importacion','exportacion','transito'].includes(c.servicio)) toast('Cotización aprobada. Puedes abrir la DO desde la tabla.'); } },
  doEstado:(d,el)=>{ const o=get('operaciones',d.id); if(!o) return; if(put('operaciones',Object.assign({}, o, {estado:el.value}))) logAct(`cambió ${o.numero} a ${el.value.toLowerCase()}`); },
  docEstado:(d,el)=>{ const x=get('documentos',d.id); if(!x) return; const o=Object.assign({}, x, {estado:el.value}); if(el.value!=='Pendiente' && !x.fecha) o.fecha=isoD(NOW()); if(put('documentos',o)) logAct(`marcó ${x.nombre||x.tipo} como ${el.value.toLowerCase()}`); },
  guideStep:(d,el)=>{ const p=get('progreso',d.g); const h=new Set((p&&p.hechos)||[]); const i=+d.i; if(el.checked) h.add(i); else h.delete(i); put('progreso',{id:d.g, hechos:[...h].sort((a,b)=>a-b)}); }
};

/* =========================================================
   Chatbot (asistente IA vía n8n → ws-chat.php)
   ========================================================= */
const CB = {msgs:[], busy:false, draft:'', stick:true, scroll:0};
try{ const raw=sessionStorage.getItem('rubicam-chatbot'); if(raw) CB.msgs=JSON.parse(raw)||[]; }catch(e){}
function cbSave(){ try{ sessionStorage.setItem('rubicam-chatbot', JSON.stringify(CB.msgs.slice(-40))); }catch(e){} }
function cbFmt(t){ return esc(t).replace(/\*\*([^*\n]+)\*\*/g,'<b>$1</b>').replace(/^\s*[\*\-]\s+/gm,'• '); }
function cbHello(){
  const a=alerts(); const nm=ME.name?`, ${ME.name}`:'';
  if(!a.length) return `Hola${nm}. Soy tu asistente del workspace. Ahora mismo no hay alertas: todo al día. Pregúntame por tareas, cotizaciones, DO o pídeme crear algo.`;
  return `Hola${nm}. Esto necesita tu atención ahora:\n`+a.map(x=>`- **${x.t}**${x.s?': '+x.s:''}`).join('\n')+`\n\nDime qué hacemos: puedo detallarte cualquiera, crear tareas, agendar reuniones o cambiar estados.`;
}
const CB_SUG = ['¿Qué alertas tengo hoy?','¿Qué tareas están vencidas y de quién son?','Resume las DO críticas y qué falta','¿Qué cotizaciones vencen esta semana?','Crea una tarea para mañana a las 9: llamar al cliente'];
V.chatbot = () => {
  const cur=$('#cb_in'); if(cur) CB.draft=cur.value;
  const lg=$('#cb_log'); if(lg) CB.scroll=lg.scrollTop;
  const bubble=(m)=>`<div class="cb-m ${m.r==='u'?'u':'a'}${m.err?' err':''}"><div class="cb-b">${cbFmt(m.t)}</div>${(m.acts||[]).length?`<div class="cb-acts">${m.acts.map(x=>pill((x.ok?'✓ ':'✕ ')+x.texto, x.ok?'ok':'crit')).join('')}</div>`:''}</div>`;
  const a=alerts();
  return pageHead('Chatbot', 'Asistente con IA conectado a tareas, cotizaciones, pendientes, DO, agenda, clientes y documentos · vía n8n', `<button type="button" class="btn" data-act="cbNew">Nueva conversación</button>`) +
  `<div class="row" style="align-items:flex-start">
    <section class="card cb-card" style="flex:3 1 520px">
      <div class="cb-log" id="cb_log" aria-live="polite">
        ${bubble({r:'a', t:cbHello()})}
        ${CB.msgs.map(bubble).join('')}
        ${CB.busy?'<div class="cb-m a"><div class="cb-b cb-typing">Pensando…</div></div>':''}
      </div>
      <div class="cb-sug">${CB_SUG.map(q=>`<button type="button" class="hchip t-neu" data-act="cbAsk" data-q="${esc(q)}" ${CB.busy?'disabled':''}>${esc(q)}</button>`).join('')}</div>
      <div class="cb-form">
        <input type="text" id="cb_in" class="cb-in" data-key="cbSend" maxlength="2000" autocomplete="off" placeholder="Pregunta algo o da una orden: «agenda reunión con Laura el viernes 10 a. m.»" value="${esc(CB.draft)}">
        <button type="button" class="btn primary" data-act="cbSend" ${CB.busy?'disabled':''}>${I(P.arrow)}Enviar</button>
      </div>
    </section>
    <section class="card" style="flex:1 1 260px">
      <div class="ch"><h2>Alertas ahora</h2>${pill(String(a.length), a.length?'crit':'ok')}</div>
      ${a.length? a.map(x=>`<a href="#${x.go}" class="lr" style="text-decoration:none;color:inherit"><span class="pill t-${x.tone}" style="width:10px;height:10px;padding:0;flex:none"></span><span class="grow" style="min-width:0;flex:1"><span class="tt">${esc(x.t)}</span><span class="ts">${esc(x.s)}</span></span></a>`).join('') : emptyBox('Sin alertas','Todo está al día.')}
      <div class="sec"><div class="small muted">El asistente puede: crear, completar y reprogramar tareas · registrar y resolver pendientes · agendar reuniones · cambiar el estado de cotizaciones y DO · publicar avisos. No elimina registros.</div></div>
    </section>
  </div>`;
};
function cbAfter(){ const lg=$('#cb_log'); if(!lg) return; if(CB.stick){ lg.scrollTop=lg.scrollHeight; CB.stick=false; } else lg.scrollTop=CB.scroll; }
async function cbSend(text){
  const inp=$('#cb_in'); const q=String(text!=null?text:(inp?inp.value:'')).trim();
  if(!q || CB.busy) return;
  if(MODE!=='db'){ toast('El chatbot necesita la conexión con el servidor.'); return; }
  const history=CB.msgs.filter(m=>!m.err).slice(-10).map(m=>({role:m.r==='u'?'user':'assistant', text:m.t}));
  CB.msgs.push({r:'u', t:q}); CB.busy=true; CB.stick=true; CB.draft=''; if(inp) inp.value=''; cbSave(); render();
  let m;
  try{
    const res=await fetch((window.RUBICAM_CFG||{}).chat||'ws-chat.php', {method:'POST', credentials:'same-origin', cache:'no-store', headers:{'Content-Type':'application/json'}, body:JSON.stringify({message:q, history})});
    let data=null; try{ data=await res.json(); }catch(e){}
    if(res.status===401){ location.href=(window.RUBICAM_CFG||{}).loginUrl||'index.php'; return; }
    if(!data) m={r:'a', err:true, t:'ws-chat.php no respondió correctamente (HTTP '+res.status+'). Revisa que el archivo exista en la carpeta panel.'};
    else if(!res.ok || data.ok===false) m={r:'a', err:true, t:data.reply||data.error||'No se pudo obtener respuesta.'};
    else m={r:'a', t:data.reply||'Listo.', acts:data.acciones||[]};
    if(m.acts && m.acts.some(x=>x.ok) && window.claude && window.claude.refresh) window.claude.refresh();
  }catch(e){ m={r:'a', err:true, t:'Sin conexión con el servidor (¿Apache encendido?).'}; }
  CB.msgs.push(m); CB.busy=false; CB.stick=true; cbSave(); render();
  const i2=$('#cb_in'); if(i2 && VIEW==='chatbot') i2.focus();
}
Object.assign(ACT, {
  cbSend:()=>cbSend(),
  cbAsk:d=>cbSend(d.q),
  cbNew:()=>{ CB.msgs=[]; CB.draft=''; CB.stick=true; cbSave(); const i=$('#cb_in'); if(i) i.value=''; render(); }
});
(function(){ const st=document.createElement('style'); st.textContent=
  '.cb-card{gap:12px}.cb-log{height:min(56vh,560px);min-height:300px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:14px;background:var(--surface-2);border:1px solid var(--line-2);border-radius:12px}'+
  '.cb-m{display:flex;flex-direction:column;gap:6px;max-width:86%}.cb-m.u{align-self:flex-end;align-items:flex-end}.cb-m.a{align-self:flex-start}'+
  '.cb-b{padding:10px 14px;border-radius:14px;font-size:14px;line-height:1.5;white-space:pre-wrap;overflow-wrap:anywhere;background:var(--surface);border:1px solid var(--line);color:var(--fg)}'+
  '.cb-m.u .cb-b{background:var(--nav-active);border-color:transparent;color:#fff;border-bottom-right-radius:4px}.cb-m.a .cb-b{border-bottom-left-radius:4px}'+
  '.cb-m.err .cb-b{background:var(--crit-bg);border-color:transparent;color:var(--crit)}.cb-typing{color:var(--muted);font-style:italic}'+
  '.cb-acts{display:flex;flex-wrap:wrap;gap:6px}.cb-acts .pill{white-space:normal;text-align:left}'+
  '.cb-sug{display:flex;flex-wrap:wrap;gap:8px}.cb-sug .hchip{cursor:pointer}.cb-sug .hchip:disabled{opacity:.5;cursor:default}'+
  '.cb-form{display:flex;gap:10px}.cb-in{flex:1;min-width:0;height:44px;padding:0 14px;border-radius:12px;border:1px solid var(--line-3);background:var(--surface);color:var(--fg);font:inherit;font-size:14px}'+
  '.cb-in:focus{outline:2px solid var(--nav-active);outline-offset:1px}';
  document.head.appendChild(st); })();

document.addEventListener('click', e=>{
  const el=e.target.closest('[data-act]');
  if(!e.target.closest('#bellpop') && !e.target.closest('#bellbtn')) $('#bellpop').hidden=true;
  if(!e.target.closest('#gsearch')) $('#gres').hidden=true;
  if(e.target.closest('.nav a')) $('#app').classList.remove('nav-open');
  if(!el) return;
  if(el.dataset.act==='ovClick' && e.target!==el) return;
  const fn=ACT[el.dataset.act]; if(!fn) return;
  if(el.tagName!=='A') e.preventDefault();
  if(el.dataset.closepop){ $('#gres').hidden=true; $('#bellpop').hidden=true; $('#gq').value=''; }
  fn(el.dataset, el, e);
});
document.addEventListener('change', e=>{ const el=e.target.closest('[data-chg]'); if(!el) return; const fn=CHG[el.dataset.chg]; if(fn) fn(el.dataset, el, e); });
document.addEventListener('input', e=>{
  const el=e.target;
  if(el.id==='gq'){ gsearch(el.value); return; }
  if(el.dataset && el.dataset.inp){ UI[el.dataset.inp]=el.value; scheduleRender(); }
});
document.addEventListener('keydown', e=>{
  if((e.ctrlKey||e.metaKey) && (e.key==='k'||e.key==='K')){ e.preventDefault(); $('#gq').focus(); $('#gq').select(); return; }
  if(e.key==='Escape'){ if($('#mroot').innerHTML) closeModal(); $('#gres').hidden=true; $('#bellpop').hidden=true; $('#app').classList.remove('nav-open'); return; }
  const el=e.target;
  if(e.key==='Enter' && el.dataset && el.dataset.key==='quickTask'){
    e.preventDefault(); const v=el.value.trim(); if(!v) return;
    const id=put('tareas',{titulo:v, area:'Operaciones', prioridad:'Media', vence:isoD(NOW())+'T18:00', responsable:'', ref:'', hecha:false, creada:isoDT(NOW())});
    if(id){ el.value=''; logAct(`creó la tarea «${v}»`); toast('Tarea añadida para hoy'); }
  }
  if(e.key==='Enter' && el.dataset && el.dataset.key==='cbSend'){ e.preventDefault(); cbSend(); return; }
  if(e.key==='Enter' && el.id==='gq'){ const f=$('#gres .pop-i'); if(f) f.click(); }
});

/* =========================================================
   Arranque
   ========================================================= */
function route(){ const h=(location.hash||'').replace('#',''); let v = V[h] ? h : 'inicio'; if(!puedeVer(v)) v = primeraVista(); VIEW = v; }
window.addEventListener('hashchange', ()=>{ route(); $('#bellpop').hidden=true; $('#gres').hidden=true; $('#app').classList.remove('nav-open'); render(); window.scrollTo(0,0); });

const SEED_LOCAL = {};
function startLocal(){ MODE='local'; let had=false; try{ had=localStorage.getItem(LS_KEY)!=null; }catch(e){}
  if(had) lsLoad(); else { COLS.forEach(c=>{ S[c]=JSON.parse(JSON.stringify(SEED_LOCAL[c]||{})); }); lsSave(); }
  LAST_SYNC=NOW(); render(); }
function noServer(){ $('#view').innerHTML='<div class="ph"><div><h1>Sin conexión con el servidor</h1><p>No se pudo cargar ws-adapter.js. Revisa que esté en la misma carpeta que este archivo.</p></div></div>'; }
async function init(){
  route(); render();
  const c=window.claude;
  if(!c || typeof c.use!=='function'){ noServer(); return; }
  let db=null; try{ db=await c.use('db'); }catch(e){ db=null; }
  if(!db){ noServer(); return; }
  DB=db; MODE='db'; setTimeout(()=>{ LOAD_TIMEOUT=true; scheduleRender(); }, 8000);
  COLS.forEach(col=>{
    try{
      DB.collection(col).onSnapshot(snap=>{
        const m={}; snap.docs.forEach(d=>{ m[d.id]=Object.assign({id:d.id}, d.data()); });
        S[col]=m; LOADED.add(col); LAST_SYNC=NOW(); scheduleRender();
      }, err=>{ if(err && err.code==='revoked'){ READONLY=true; } toast('Se perdió la conexión con los datos compartidos. Recarga la página.'); scheduleRender(); });
    }catch(e){}
  });
  scheduleRender();
  c.use('user').then(u=>{
    if(!u) return; USER=u;
    Promise.resolve().then(()=>u.name()).then(n=>{ ME.name=n||''; scheduleRender(); }).catch(()=>{});
    Promise.resolve().then(()=>u.id()).then(id=>{ ME.id=id; }).catch(()=>{});
    Promise.resolve().then(()=>u.isOwner()).then(v=>{ IS_OWNER=!!v; scheduleRender(); }).catch(()=>{});
    Promise.resolve().then(()=>u.can('data.write')).then(v=>{ if(v===false){ READONLY=true; scheduleRender(); } }).catch(()=>{});
  }).catch(()=>{});
  c.use('sample').then(s=>{ SAMPLE=s||null; scheduleRender(); }).catch(()=>{});
}
setInterval(()=>{ if(!$('#mroot').innerHTML) scheduleRender(); }, 60000);
init();
})();
</script>
</body>
</html>
