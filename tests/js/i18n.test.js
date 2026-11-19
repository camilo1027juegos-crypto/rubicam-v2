/* Pruebas del motor de idiomas (assets/js/i18n.js) */
const fs = require('fs');
const path = require('path');
const { I18N, applyLang, getLang, lsGet, lsSet } = require('../../assets/js/i18n.js');

const RAIZ = path.join(__dirname, '..', '..');
const IDIOMAS = ['es', 'en', 'de'];

/* Todas las claves data-i18n usadas en las páginas y en los componentes */
function clavesUsadas() {
  const archivos = [];
  const recorrer = (dir) => fs.readdirSync(dir, { withFileTypes: true }).forEach((e) => {
    const p = path.join(dir, e.name);
    if (e.isDirectory()) recorrer(p);
    else if (/\.(html|js)$/.test(e.name)) archivos.push(p);
  });
  archivos.push(path.join(RAIZ, 'index.html'));
  recorrer(path.join(RAIZ, 'pages'));
  recorrer(path.join(RAIZ, 'components'));
  const claves = new Set();
  archivos.forEach((f) => {
    for (const m of fs.readFileSync(f, 'utf8').matchAll(/data-i18n="([^"]+)"/g)) claves.add(m[1]);
  });
  return [...claves];
}

beforeEach(() => {
  localStorage.clear();
  document.body.innerHTML = `
    <h1 data-i18n="nav.home"></h1>
    <p data-i18n="clave.que.no.existe">original</p>
    <span id="langLabel"></span>
    <div class="lang-menu"><button data-lang="es"></button><button data-lang="en"></button></div>`;
});

describe('diccionario', () => {
  test('los tres idiomas tienen exactamente las mismas claves', () => {
    const base = Object.keys(I18N.es).sort();
    IDIOMAS.forEach((l) => expect(Object.keys(I18N[l]).sort()).toEqual(base));
  });

  test('ningún texto está vacío', () => {
    IDIOMAS.forEach((l) => Object.entries(I18N[l]).forEach(([k, v]) => {
      expect({ idioma: l, clave: k, vacio: typeof v !== 'string' || v.trim() === '' })
        .toEqual({ idioma: l, clave: k, vacio: false });
    }));
  });

  test('toda clave data-i18n de las páginas existe en el diccionario', () => {
    const faltan = clavesUsadas().filter((k) => !(k in I18N.es));
    expect(faltan).toEqual([]);
  });

  test('las traducciones conservan el mismo HTML permitido (<b>)', () => {
    Object.keys(I18N.es).forEach((k) => {
      const tieneB = (l) => I18N[l][k].includes('<b>');
      expect({ k, en: tieneB('en'), de: tieneB('de') }).toEqual({ k, en: tieneB('es'), de: tieneB('es') });
    });
  });
});

describe('applyLang', () => {
  test('traduce los elementos y actualiza idioma, etiqueta y botón activo', () => {
    applyLang('en');
    expect(document.querySelector('[data-i18n="nav.home"]').textContent).toBe(I18N.en['nav.home']);
    expect(document.documentElement.lang).toBe('en');
    expect(document.getElementById('langLabel').textContent).toBe('EN');
    expect(document.querySelector('[data-lang="en"]').classList.contains('sel')).toBe(true);
    expect(document.querySelector('[data-lang="es"]').classList.contains('sel')).toBe(false);
  });

  test('deja intacto un elemento cuya clave no existe', () => {
    applyLang('de');
    expect(document.querySelector('[data-i18n="clave.que.no.existe"]').textContent).toBe('original');
  });

  test('un idioma desconocido usa español', () => {
    applyLang('fr');
    expect(document.querySelector('[data-i18n="nav.home"]').textContent).toBe(I18N.es['nav.home']);
  });

  test('textos con <b> se insertan como HTML y el resto como texto (evita inyección)', () => {
    const conB = Object.keys(I18N.es).find((k) => I18N.es[k].includes('<b>'));
    document.body.innerHTML = `<p data-i18n="${conB}"></p><p data-i18n="nav.home"></p>`;
    applyLang('es');
    expect(document.querySelector(`[data-i18n="${conB}"] b`)).not.toBeNull();
    expect(document.querySelector('[data-i18n="nav.home"]').children.length).toBe(0);
  });

  test('recuerda el idioma elegido y expone los avisos', () => {
    applyLang('de');
    expect(getLang()).toBe('de');
    expect(window.__T.sent).toBe(I18N.de['_toast.sent']);
  });

  test('funciona sin etiqueta de idioma en la página', () => {
    document.body.innerHTML = '<h1 data-i18n="nav.home"></h1>';
    expect(() => applyLang('en')).not.toThrow();
  });
});

describe('almacenamiento local', () => {
  test('idioma por defecto es español', () => expect(getLang()).toBe('es'));

  test('lsSet/lsGet guardan y leen', () => {
    lsSet('x', '1');
    expect(lsGet('x')).toBe('1');
  });

  test('no falla si el navegador bloquea localStorage', () => {
    const espia = jest.spyOn(Storage.prototype, 'getItem').mockImplementation(() => { throw new Error('bloqueado'); });
    const espia2 = jest.spyOn(Storage.prototype, 'setItem').mockImplementation(() => { throw new Error('bloqueado'); });
    expect(lsGet('x')).toBeNull();
    expect(() => lsSet('x', '1')).not.toThrow();
    expect(getLang()).toBe('es');
    espia.mockRestore(); espia2.mockRestore();
  });
});
