/* Pruebas del formulario, consentimiento y cookies (assets/js/main.js) */
const i18n = require('../../assets/js/i18n.js');
global.lsGet = i18n.lsGet;
global.lsSet = i18n.lsSet;
const { toast, newsSoon, submitForm, openCookie, acceptCookies } = require('../../assets/js/main.js');

function formulario(terminos, privacidad) {
  document.body.innerHTML = `
    <div id="toast"></div>
    <div id="cookieBanner"></div>
    <form id="contactForm"><input id="nombre" value="Ana">
      <input type="checkbox" id="cTerms" ${terminos ? 'checked' : ''}>
      <input type="checkbox" id="cPriv" ${privacidad ? 'checked' : ''}></form>`;
  return document.getElementById('contactForm');
}
const evento = (form) => ({ preventDefault: jest.fn(), target: form });

beforeEach(() => {
  jest.useFakeTimers();
  localStorage.clear();
  window.__T = {};
  jest.spyOn(console, 'log').mockImplementation(() => {});
});
afterEach(() => { jest.useRealTimers(); jest.restoreAllMocks(); });

describe('formulario de contacto', () => {
  test.each([[false, false], [true, false], [false, true]])(
    'no se envía sin ambos consentimientos (términos=%s, privacidad=%s)', (t, p) => {
      const form = formulario(t, p);
      const e = evento(form);
      expect(submitForm(e)).toBe(false);
      expect(e.preventDefault).toHaveBeenCalled();
      expect(document.getElementById('toast').textContent).toBe('Acepte ambas casillas.');
      expect(console.log).not.toHaveBeenCalled();
    });

  test('con ambos consentimientos registra la constancia y limpia el formulario', () => {
    const form = formulario(true, true);
    document.documentElement.lang = 'es';
    window.__T = { sent: 'Enviado OK' };
    form.querySelector('#nombre').value = 'Cambiado';
    submitForm(evento(form));
    const [, registro] = console.log.mock.calls[0];
    expect(registro).toMatchObject({ acepta_terminos: true, acepta_privacidad: true, version_politicas: '2026-01', idioma: 'es' });
    expect(new Date(registro.fecha).toString()).not.toBe('Invalid Date');
    expect(form.querySelector('#nombre').value).toBe('Ana');
    expect(document.getElementById('toast').textContent).toBe('Enviado OK');
  });
});

describe('avisos (toast)', () => {
  test('se muestra y se oculta a los 3,2 s', () => {
    formulario(false, false);
    toast('Hola');
    const t = document.getElementById('toast');
    expect(t.classList.contains('show')).toBe(true);
    jest.advanceTimersByTime(3200);
    expect(t.classList.contains('show')).toBe(false);
  });

  test('no falla si la página no tiene contenedor de avisos', () => {
    document.body.innerHTML = '';
    expect(() => toast('x')).not.toThrow();
  });

  test('noticia de ejemplo usa el texto traducido o el de respaldo', () => {
    formulario(false, false);
    newsSoon();
    expect(document.getElementById('toast').textContent).toBe('Próximamente');
    window.__T = { news: 'Coming soon' };
    newsSoon();
    expect(document.getElementById('toast').textContent).toBe('Coming soon');
  });
});

describe('cookies', () => {
  test('aceptar guarda el nivel elegido y cierra el banner', () => {
    formulario(false, false);
    openCookie();
    const b = document.getElementById('cookieBanner');
    expect(b.classList.contains('show')).toBe(true);
    acceptCookies('necesarias');
    expect(localStorage.getItem('rubicam_cookies')).toBe('necesarias');
    expect(b.classList.contains('show')).toBe(false);
  });

  test('el banner aparece en la primera visita y no en las siguientes', () => {
    formulario(false, false);
    document.dispatchEvent(new Event('components:ready'));
    jest.advanceTimersByTime(800);
    expect(document.getElementById('cookieBanner').classList.contains('show')).toBe(true);

    formulario(false, false);
    localStorage.setItem('rubicam_cookies', 'todas');
    document.dispatchEvent(new Event('components:ready'));
    jest.advanceTimersByTime(800);
    expect(document.getElementById('cookieBanner').classList.contains('show')).toBe(false);
  });

  test('funciones de cookies no fallan sin banner', () => {
    document.body.innerHTML = '';
    expect(() => { openCookie(); acceptCookies('todas'); }).not.toThrow();
  });
});

test('al cargar la página se conecta el envío del formulario', () => {
  const form = formulario(false, false);
  document.dispatchEvent(new Event('DOMContentLoaded'));
  form.dispatchEvent(new Event('submit', { cancelable: true }));
  expect(document.getElementById('toast').textContent).toBe('Acepte ambas casillas.');
});
