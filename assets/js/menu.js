/* ============================================================
   menu.js — Desplegables, menú móvil y selector de idioma
   Se activa cuando el header ya está cargado (components:ready).
   ============================================================ */

/* Selector de idioma */
function setLang(lang){
  applyLang(lang);                 // definido en i18n.js
  const l = document.getElementById('lang');
  if(l) l.classList.remove('open');
}
function toggleLang(){
  const l = document.getElementById('lang');
  if(l) l.classList.toggle('open');
}

/* Desplegables del menú (clic en móvil / respaldo en escritorio) */
function toggleDropdown(btn){
  const dd = btn.parentElement.querySelector('.dropdown');
  if(!dd) return;
  const wasOpen = dd.classList.contains('open');
  document.querySelectorAll('.dropdown.open').forEach(d=>d.classList.remove('open'));
  if(!wasOpen) dd.classList.add('open');
}

/* Menú hamburguesa (móvil) */
function toggleBurger(){
  const nc = document.getElementById('navCenter');
  if(nc) nc.classList.toggle('open');
}

/* Marca como activo el enlace de la página actual */
function markActiveNav(){
  const path = location.pathname;
  const file = path.substring(path.lastIndexOf('/')+1) || 'index.html';
  document.querySelectorAll('.nav-link[data-page]').forEach(l=>{
    const target = l.getAttribute('data-page');
    if( (file==='' || file==='index.html') && target==='index.html' ) l.classList.add('active');
    else if( target!=='index.html' && path.indexOf(target)!==-1 ) l.classList.add('active');
  });
}

/* Cerrar menús al hacer clic fuera */
document.addEventListener('click', e=>{
  if(!e.target.closest('.lang')){
    const l=document.getElementById('lang'); if(l) l.classList.remove('open');
  }
  if(!e.target.closest('.nav-item')){
    document.querySelectorAll('.dropdown.open').forEach(d=>d.classList.remove('open'));
  }
});

/* Al cargar el header: aplicar idioma guardado y marcar navegación */
document.addEventListener('components:ready', ()=>{
  applyLang(getLang());
  markActiveNav();
});
