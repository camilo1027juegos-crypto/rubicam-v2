/* ============================================================
   components.js — Inserta header y footer en cada página.
   Toma el HTML desde header.js y footer.js (variables globales
   HEADER_HTML y FOOTER_HTML). Al NO usar fetch, funciona también
   abriendo el archivo con doble clic (file://), sin servidor.
   ============================================================ */
function loadComponents(){
  const h = document.getElementById('header');
  const f = document.getElementById('footer');
  if(h && typeof HEADER_HTML !== 'undefined') h.innerHTML = HEADER_HTML;
  if(f && typeof FOOTER_HTML !== 'undefined') f.innerHTML = FOOTER_HTML;
  // Avisar al resto de scripts que el header/footer ya están en el DOM
  document.dispatchEvent(new Event('components:ready'));
}
document.addEventListener('DOMContentLoaded', loadComponents);
