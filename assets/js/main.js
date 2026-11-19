/* ============================================================
   main.js — Formulario, consentimiento, cookies y avisos (toast)
   ============================================================ */

/* Aviso flotante */
let toastTimer;
function toast(msg){
  const t = document.getElementById('toast');
  if(!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=>t.classList.remove('show'), 3200);
}

/* Noticia de ejemplo */
function newsSoon(){ toast(window.__T.news || 'Próximamente'); }

/* ---- Formulario de contacto + registro de consentimiento (legalidad #5) ---- */
function submitForm(e){
  e.preventDefault();
  const terms = document.getElementById('cTerms');
  const priv  = document.getElementById('cPriv');
  if(!terms.checked || !priv.checked){
    toast(window.__T.consent || 'Acepte ambas casillas.');
    return false;
  }
  // Constancia del consentimiento: qué, cuándo y cómo
  const record = {
    fecha: new Date().toISOString(),
    acepta_terminos: terms.checked,
    acepta_privacidad: priv.checked,
    version_politicas: '2026-01',
    idioma: document.documentElement.lang,
    userAgent: navigator.userAgent
  };
  console.log('[Rubicam] Registro de consentimiento:', record);
  // En producción: enviar 'record' + datos del formulario al backend.
  e.target.reset();
  toast(window.__T.sent || 'Enviado.');
  return false;
}

/* ---- Cookies (legalidad #3) ---- */
function openCookie(){
  const c = document.getElementById('cookieBanner');
  if(c) c.classList.add('show');
}
function acceptCookies(level){
  console.log('[Rubicam] Consentimiento de cookies:', {nivel:level, fecha:new Date().toISOString()});
  lsSet('rubicam_cookies', level);
  const c = document.getElementById('cookieBanner');
  if(c) c.classList.remove('show');
  toast(window.__T.cookieOk || 'Preferencias guardadas.');
}

/* Mostrar banner de cookies la primera visita (tras cargar el footer) */
document.addEventListener('components:ready', ()=>{
  if(!lsGet('rubicam_cookies')){
    setTimeout(openCookie, 800);
  }
});

/* Conectar el formulario si existe en esta página */
document.addEventListener('DOMContentLoaded', ()=>{
  const form = document.getElementById('contactForm');
  if(form) form.addEventListener('submit', submitForm);
});

/* Exportación solo para las pruebas automáticas (Node/Jest). En el navegador no hace nada. */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { toast, newsSoon, submitForm, openCookie, acceptCookies };
}
