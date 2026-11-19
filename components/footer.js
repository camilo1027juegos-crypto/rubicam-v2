/* ============================================================
   footer.js — Pie de página + banner de cookies (componente)
   Se escribe una sola vez aquí y se inyecta en cada página.
   ============================================================ */
const FOOTER_HTML = `
<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="brand">Rubicam<span class="star">*</span></div>
        <p class="footer-about" data-i18n="footer.about">Agencia de aduanas autorizada. Movemos su comercio exterior con rigor técnico y trazabilidad total.</p>
      </div>
      <div class="footer-col">
        <h4 data-i18n="footer.nav">Navegación</h4>
        <a href="index.html" data-i18n="nav.home">Inicio</a>
        <a href="pages/nosotros.html" data-i18n="nav.about">Sobre nosotros</a>
        <a href="pages/servicios.html" data-i18n="nav.services">Servicios</a>
        <a href="pages/noticias.html" data-i18n="nav.news">Noticias</a>
        <a href="pages/contacto.html" data-i18n="nav.contact">Contáctanos</a>
      </div>
      <div class="footer-col">
        <h4 data-i18n="footer.services">Servicios</h4>
        <a href="pages/servicios.html#s-import" data-i18n="svc.import.t">Importaciones</a>
        <a href="pages/servicios.html#s-export" data-i18n="svc.export.t">Exportaciones</a>
        <a href="pages/servicios.html#s-clas" data-i18n="svc.clas.t">Clasificación arancelaria</a>
        <a href="pages/servicios.html#s-cambio" data-i18n="svc.fx.t">Cumplimiento cambiario</a>
      </div>
      <div class="footer-col">
        <h4 data-i18n="footer.legal">Legal</h4>
        <a href="pages/legal/terminos.html" data-i18n="legal.terms">Términos y condiciones</a>
        <a href="pages/legal/privacidad.html" data-i18n="legal.privacy">Política de privacidad</a>
        <a href="pages/legal/cookies.html" data-i18n="legal.cookies">Política de cookies</a>
        <a href="pages/legal/reembolso.html" data-i18n="legal.refund">Política de reembolso</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 Rubicam. <span data-i18n="footer.rights">Todos los derechos reservados.</span></p>
      <div class="footer-legal">
        <a href="pages/legal/terminos.html" data-i18n="legal.terms">Términos y condiciones</a>
        <a href="pages/legal/privacidad.html" data-i18n="legal.privacy">Política de privacidad</a>
        <a href="#" onclick="event.preventDefault();openCookie()" data-i18n="footer.cookieSettings">Configurar cookies</a>
      </div>
    </div>
  </div>
</footer>

<div class="cookie" id="cookieBanner">
  <h4 data-i18n="cookie.title">Usamos cookies</h4>
  <p data-i18n="cookie.text">Usamos cookies necesarias para que el sitio funcione y, con su permiso, cookies analíticas para mejorarlo.</p>
  <div class="cookie-actions">
    <button class="btn btn-primary" onclick="acceptCookies('all')" data-i18n="cookie.accept">Aceptar todas</button>
    <button class="btn btn-ghost" onclick="acceptCookies('necessary')" data-i18n="cookie.reject">Solo necesarias</button>
  </div>
</div>`;
