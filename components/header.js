/* ============================================================
   header.js — Barra de navegación reutilizable (componente)
   El menú se escribe UNA sola vez aquí y se inyecta en cada
   página mediante components.js. Al ser un .js (no fetch),
   funciona también abriendo el archivo con doble clic.
   Los enlaces son relativos y se resuelven con el <base> de cada página.
   ============================================================ */
const HEADER_HTML = `
<nav class="nav">
  <div class="nav-inner">
    <a class="logo" href="index.html">
      <img src="assets/img/Logo_Grupo_Empresarial_RUBICAM_transparente.png" alt="Rubicam" class="logo-img">
    </a>

    <div class="nav-center" id="navCenter">
      <div class="nav-item">
        <a class="nav-link" href="index.html" data-page="index.html" data-i18n="nav.home">Inicio</a>
      </div>

      <div class="nav-item">
        <button class="nav-link" data-page="nosotros.html" onclick="toggleDropdown(this)">
          <span data-i18n="nav.about">Sobre nosotros</span>
          <svg class="caret" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="dropdown">
          <a href="pages/nosotros.html#origen" data-i18n="about.origin">Nuestro origen</a>
          <a href="pages/nosotros.html#nivel" data-i18n="about.level">Nuestro nivel y certificaciones</a>
          <a href="pages/nosotros.html#equipo" data-i18n="about.team">Nuestro equipo</a>
          <a href="pages/nosotros.html#porque" data-i18n="about.why">¿Por qué elegirnos?</a>
          <a href="pages/nosotros.html#objetivos" data-i18n="about.goals">Objetivos empresariales</a>
        </div>
      </div>

      <div class="nav-item">
        <button class="nav-link" data-page="servicios.html" onclick="toggleDropdown(this)">
          <span data-i18n="nav.services">Servicios</span>
          <svg class="caret" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="dropdown">
          <a href="pages/servicios.html#s-import" data-i18n="svc.import.t">Importaciones</a>
          <a href="pages/servicios.html#s-export" data-i18n="svc.export.t">Exportaciones</a>
          <a href="pages/servicios.html#s-clas" data-i18n="svc.clas.t">Clasificación arancelaria</a>
          <a href="pages/servicios.html#s-asesoria" data-i18n="svc.advice.t">Asesoría en comercio exterior</a>
          <a href="pages/servicios.html#s-cambio" data-i18n="svc.fx.t">Cumplimiento cambiario</a>
          <a href="pages/servicios.html#s-log" data-i18n="svc.log.t">Logística y transporte</a>
        </div>
      </div>

      <div class="nav-item">
        <a class="nav-link" href="pages/noticias.html" data-page="noticias.html" data-i18n="nav.news">Noticias</a>
      </div>
      <div class="nav-item">
        <a class="nav-link" href="pages/contacto.html" data-page="contacto.html" data-i18n="nav.contact">Contáctanos</a>
      </div>
    </div>

    <div class="nav-right">
      <div class="lang" id="lang">
        <button class="lang-btn" onclick="toggleLang()">
          <svg class="globe" viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg>
          <span id="langLabel">ES</span>
          <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="lang-menu">
          <button data-lang="es" onclick="setLang('es')"><span class="flag">🇪🇸</span> Español <span class="chk">✓</span></button>
          <button data-lang="en" onclick="setLang('en')"><span class="flag">🇬🇧</span> English <span class="chk">✓</span></button>
          <button data-lang="de" onclick="setLang('de')"><span class="flag">🇩🇪</span> Deutsch <span class="chk">✓</span></button>
        </div>
      </div>
      <a class="btn btn-grad nav-cta" href="Panel/login.php" data-i18n="nav.cta"></a>
      <button class="burger" onclick="toggleBurger()"><span></span><span></span><span></span></button>
    </div>
  </div>
</nav>`;
