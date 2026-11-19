# Rubicam — Sitio web (agencia de aduanas)

[![CI](https://github.com/camilo1027juegos-crypto/rubicam-v2/actions/workflows/ci.yml/badge.svg)](https://github.com/camilo1027juegos-crypto/rubicam-v2/actions/workflows/ci.yml)

Prototipo de sitio web multipágina para **Rubicam**, construido con **HTML, CSS y JavaScript puro** (sin frameworks). Diseño basado en el sistema visual "Orderful" (monocromático con un único acento vermellón `#e42b0c`).

Idiomas: **Español (por defecto), Inglés y Alemán**, con selector funcional que recuerda la elección.

---

## Cómo ejecutarlo

**Con doble clic:** abre `index.html` en tu navegador. Funciona tal cual, sin servidor, porque el header y el footer se cargan con JavaScript (no con `fetch`), y todas las rutas son relativas.

**Con XAMPP u otro servidor (recomendado para producción):** coloca la carpeta `rubicam-sitio` dentro de `htdocs`, enciende Apache y entra por `http://localhost/.../rubicam-sitio/`. También funciona.

> Las rutas son **relativas** y cada página tiene una etiqueta `<base href>` que apunta a la raíz del proyecto. Por eso el sitio funciona desde cualquier carpeta, por doble clic o por servidor.
>
> Nota: con conexión a internet la tipografía Inter se carga desde Google Fonts. Sin conexión, el navegador usa una fuente de sistema parecida.

---

## 🔁 Integración continua (GitHub Actions)

Cada *push* a `main` o `develop` y cada *pull request* ejecuta `.github/workflows/ci.yml`:

| Etapa | Qué hace | Falla si… |
|---|---|---|
| 1 · Instalación y análisis estático | Composer + npm (con caché), `php -l`, ESLint, PHPStan | hay errores de sintaxis o de análisis |
| 2 · Pruebas y cobertura | PHPUnit + Jest, umbral de cobertura, reportes como artefacto | una prueba falla o la cobertura baja del umbral (PHP 80 %, JS 80 %) |
| 3 · Pruebas de humo | MySQL 8.4 en contenedor, importa el esquema y prueba login, permisos y API | una verificación de humo falla |
| 4 · Notificar | Comenta el resultado en el *pull request* | — |

**Comando único de pruebas (local):** `npm run pruebas`  (requiere `composer install` y `npm ci`).

**Claves:** nunca van en el código. Copie `.env.example` como `.env` (local) o defina los secretos
`DB_CLAVE_CI` y `ADMIN_CLAVE_STAGING` en *Settings → Secrets and variables → Actions*.

Más detalle en [`docs/PLAN_DE_PRUEBAS.md`](docs/PLAN_DE_PRUEBAS.md).

---

## 📁 Estructura de archivos

```
rubicam-sitio/
├── index.html                  ← Página de inicio
│
├── components/                 ← Piezas reutilizables (se inyectan con JS)
│   ├── header.js               ← Barra de navegación + selector de idioma
│   └── footer.js               ← Pie de página + banner de cookies
│
├── pages/                      ← Una página por sección del menú
│   ├── nosotros.html           ← Con subsecciones: origen, nivel, equipo, por qué, objetivos
│   ├── servicios.html          ← Con anclas por servicio
│   ├── noticias.html
│   ├── contacto.html           ← Formulario con doble casilla de consentimiento
│   └── legal/
│       ├── terminos.html
│       ├── privacidad.html
│       ├── cookies.html
│       └── reembolso.html
│
├── assets/
│   ├── css/
│   │   ├── reset.css           ← Normaliza el navegador
│   │   ├── variables.css       ← Colores, tipografía, medidas (cambia aquí los tokens)
│   │   ├── header.css          ← Estilos del menú
│   │   ├── footer.css          ← Estilos del pie, cookies y avisos
│   │   └── main.css            ← Estilos de todo el contenido
│   ├── js/
│   │   ├── i18n.js             ← Diccionario de idiomas + motor de traducción
│   │   ├── components.js       ← Inyecta header.js y footer.js en cada página
│   │   ├── menu.js             ← Desplegables, menú móvil, selector de idioma
│   │   └── main.js             ← Formulario, consentimiento, cookies, avisos
│   ├── img/                    ← (vacío) imágenes y banners reales
│   ├── icons/
│   │   └── chevron-down.svg
│   └── fonts/                  ← (vacío) si usas una fuente propia en lugar de Inter
│
├── data/
│   └── menu.json               ← Estructura del menú como datos (opcional)
│
└── README.md
```

---

## 🧩 Cómo funciona por dentro

- **Componentes**: cada página tiene `<div id="header"></div>` y `<div id="footer"></div>` vacíos. `components.js` los rellena con el contenido de `components/header.js` y `components/footer.js`. Así el menú se escribe **una sola vez** y, al no usar `fetch`, funciona también con doble clic.
- **Idiomas**: cada texto traducible lleva `data-i18n="clave"`. El diccionario está en `i18n.js`. Al cambiar de idioma, `applyLang()` reemplaza todos los textos y guarda la elección en el navegador.
- **Rutas**: todas absolutas desde la raíz, para que los enlaces del header funcionen igual desde cualquier carpeta.

### Añadir o cambiar un texto
1. En el HTML, pon `data-i18n="mi.clave"` en el elemento.
2. En `assets/js/i18n.js`, agrega `"mi.clave"` en los tres idiomas (`es`, `en`, `de`).

### Añadir una página nueva
1. Copia una página de `pages/` como plantilla (mantén los mismos `<link>` y `<script>`).
2. Agrégala al menú en `components/header.js` (y si quieres, en `data/menu.json`).

### Cambiar colores o tipografía
Edita **solo** `assets/css/variables.css`. Por ejemplo, `--color-vermillion` cambia el acento en todo el sitio.

---

## ✅ Estado (construcción por fases)

Esto es el **diseño/frontend**. Falta la parte de servidor:

- [x] Diseño de las 5 páginas + 4 páginas legales
- [x] Menús desplegables y menú móvil
- [x] Selector de idioma ES / EN / DE funcional
- [x] Banner de cookies con aceptar / rechazar
- [x] Formulario con doble casilla de consentimiento (términos + privacidad)
- [ ] **Backend**: enviar el formulario y **guardar el registro de consentimiento** (hoy solo se imprime en la consola del navegador)
- [ ] Contenido real de Rubicam (textos, datos de contacto, equipo)
- [ ] Imágenes reales con derechos verificados
- [ ] Revisión legal de los textos modelo por un abogado

---

© 2026 Rubicam. Prototipo de diseño.

> Historial de ejecuciones y reportes de cobertura: pestaña **Actions** del repositorio.
