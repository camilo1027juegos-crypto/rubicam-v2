/* ============================================================
   Rubicam · Asistente del sitio web (widget flotante)
   Uso: <script src="chat/rubicam-chat.js" defer></script>   (antes de </body>)
   Opcional en la etiqueta: data-endpoint="ruta/chat-publico.php"
   Idioma: toma window.RUBICAM_CHAT_LANG, <html lang>, localStorage('lang'|'idioma') o el del navegador (es/en/de).
   ============================================================ */
(function () {
  'use strict';
  if (window.__rubicamChat) return;
  window.__rubicamChat = true;

  var script = document.currentScript || (function () { var s = document.getElementsByTagName('script'); return s[s.length - 1]; })();
  var base = script && script.src ? script.src.replace(/[^\/]*$/, '') : '';
  var ENDPOINT = (script && script.getAttribute('data-endpoint')) || (base + 'chat-publico.php');
  var STORE = 'rubicam_chat_v1';
  var MAX_LEN = 600;

  /* ---------------------------- textos ---------------------------- */
  var T = {
    es: {
      titulo: 'Rubi · Asistente Rubicam', sub: 'Resolvemos tus dudas de comercio exterior',
      bienvenida: '¡Hola! Soy Rubi, el asistente de Rubicam. Puedo contarte qué hacemos, cómo funciona una agencia de aduanas y cómo empezar a trabajar con nosotros. ¿En qué te ayudo?',
      chips: ['¿Qué servicios ofrecen?', '¿Qué es una agencia de aduanas?', '¿Cómo solicito una cotización?', '¿Qué necesito para importar?'],
      ph: 'Escribe tu pregunta…', enviar: 'Enviar', abrir: 'Abrir asistente', cerrar: 'Cerrar', ayuda: '¿Necesitas ayuda?',
      asesor: 'Hablar con un asesor', pensando: 'Rubi está escribiendo', aviso: 'Asistente con IA: puede equivocarse. Confirma los datos importantes con un asesor.',
      f_titulo: 'Déjanos tus datos y un asesor te contactará', f_nombre: 'Nombre completo', f_email: 'Correo electrónico', f_tel: 'Teléfono (opcional)',
      f_emp: 'Empresa (opcional)', f_msg: '¿Qué necesitas? (opcional)', f_acepto: 'Autorizo el tratamiento de mis datos para que Rubicam me contacte.',
      f_enviar: 'Enviar', f_volver: 'Volver al chat', f_enviando: 'Enviando…',
      f_err_nombre: 'Escribe tu nombre.', f_err_email: 'Revisa el correo electrónico.', f_err_acepto: 'Debes autorizar el tratamiento de datos para continuar.',
      f_ok: '¡Gracias! Un asesor te contactará pronto.', err: 'No pude responder en este momento. Intenta de nuevo.'
    },
    en: {
      titulo: 'Rubi · Rubicam Assistant', sub: 'We answer your foreign trade questions',
      bienvenida: 'Hi! I am Rubi, the Rubicam assistant. I can tell you what we do, how a customs agency works and how to start working with us. How can I help?',
      chips: ['What services do you offer?', 'What is a customs agency?', 'How do I request a quote?', 'What do I need to import?'],
      ph: 'Type your question…', enviar: 'Send', abrir: 'Open assistant', cerrar: 'Close', ayuda: 'Need help?',
      asesor: 'Talk to an advisor', pensando: 'Rubi is typing', aviso: 'AI assistant: it can make mistakes. Confirm important details with an advisor.',
      f_titulo: 'Leave your details and an advisor will contact you', f_nombre: 'Full name', f_email: 'Email', f_tel: 'Phone (optional)',
      f_emp: 'Company (optional)', f_msg: 'What do you need? (optional)', f_acepto: 'I authorize the processing of my data so Rubicam can contact me.',
      f_enviar: 'Send', f_volver: 'Back to chat', f_enviando: 'Sending…',
      f_err_nombre: 'Enter your name.', f_err_email: 'Check your email address.', f_err_acepto: 'You must authorize data processing to continue.',
      f_ok: 'Thank you! An advisor will contact you soon.', err: 'I could not answer right now. Please try again.'
    },
    de: {
      titulo: 'Rubi · Rubicam-Assistent', sub: 'Wir beantworten Ihre Fragen zum Außenhandel',
      bienvenida: 'Hallo! Ich bin Rubi, der Assistent von Rubicam. Ich erkläre, was wir tun, wie eine Zollagentur arbeitet und wie Sie mit uns starten. Wie kann ich helfen?',
      chips: ['Welche Leistungen bieten Sie an?', 'Was ist eine Zollagentur?', 'Wie fordere ich ein Angebot an?', 'Was brauche ich für den Import?'],
      ph: 'Ihre Frage…', enviar: 'Senden', abrir: 'Assistent öffnen', cerrar: 'Schließen', ayuda: 'Brauchen Sie Hilfe?',
      asesor: 'Mit einem Berater sprechen', pensando: 'Rubi schreibt', aviso: 'KI-Assistent: Er kann Fehler machen. Wichtige Angaben bitte mit einem Berater bestätigen.',
      f_titulo: 'Hinterlassen Sie Ihre Daten, ein Berater meldet sich', f_nombre: 'Vollständiger Name', f_email: 'E-Mail', f_tel: 'Telefon (optional)',
      f_emp: 'Unternehmen (optional)', f_msg: 'Was benötigen Sie? (optional)', f_acepto: 'Ich willige in die Verarbeitung meiner Daten ein, damit Rubicam mich kontaktieren kann.',
      f_enviar: 'Senden', f_volver: 'Zurück zum Chat', f_enviando: 'Wird gesendet…',
      f_err_nombre: 'Bitte Namen eingeben.', f_err_email: 'Bitte E-Mail prüfen.', f_err_acepto: 'Sie müssen der Datenverarbeitung zustimmen.',
      f_ok: 'Vielen Dank! Ein Berater meldet sich bald.', err: 'Ich konnte gerade nicht antworten. Bitte erneut versuchen.'
    }
  };

  function detectLang() {
    var c = window.RUBICAM_CHAT_LANG || document.documentElement.getAttribute('lang') || '';
    try { c = c || localStorage.getItem('lang') || localStorage.getItem('idioma') || ''; } catch (e) {}
    c = String(c || navigator.language || 'es').slice(0, 2).toLowerCase();
    return T[c] ? c : 'es';
  }
  var lang = detectLang();
  function tr(k) { return T[lang][k]; }

  /* ---------------------------- utilidades ---------------------------- */
  function esc(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function inline(s) {
    s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    s = s.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, function (_, t, u) {
      return '<a href="' + u + '" target="_blank" rel="noopener noreferrer">' + t + '</a>';
    });
    s = s.replace(/(^|[\s(])(https?:\/\/[^\s<)]+)/g, function (_, p, u) {
      return p + '<a href="' + u + '" target="_blank" rel="noopener noreferrer">' + u + '</a>';
    });
    return s;
  }
  // Markdown mínimo y seguro: primero se escapa TODO el HTML y luego se añaden solo negrita, listas y enlaces http(s).
  function fmt(text) {
    var out = [], list = false;
    esc(text).split(/\n/).forEach(function (l) {
      var m = l.match(/^\s*(?:[-*•]|\d+[.)])\s+(.*)$/);
      if (m) { if (!list) { out.push('<ul>'); list = true; } out.push('<li>' + inline(m[1]) + '</li>'); }
      else { if (list) { out.push('</ul>'); list = false; } if (l.trim() !== '') out.push('<p>' + inline(l) + '</p>'); }
    });
    if (list) out.push('</ul>');
    return out.join('');
  }
  function el(tag, cls, attrs) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (attrs) for (var k in attrs) if (Object.prototype.hasOwnProperty.call(attrs, k)) e.setAttribute(k, attrs[k]);
    return e;
  }

  /* ---------------------------- estado ---------------------------- */
  var msgs = [];           // {r:'u'|'b', t:'texto', c:bool(ofrecer contacto)}
  var busy = false, started = false;
  try { var saved = JSON.parse(sessionStorage.getItem(STORE) || 'null'); if (saved && Array.isArray(saved.msgs)) { msgs = saved.msgs.slice(-30); started = msgs.length > 0; } } catch (e) {}
  function save() { try { sessionStorage.setItem(STORE, JSON.stringify({ msgs: msgs.slice(-30) })); } catch (e) {} }

  /* ---------------------------- estilos ---------------------------- */
  var css = '' +
    '.rbc{--or:#ff6a13;--rd:#e5320f;--n1:#0f1a2b;--n2:#17263d;--n3:#22344f;--tx:#eef3fa;--mu:#a9b8cc;font:15px/1.45 Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:var(--tx)}' +
    '.rbc *{box-sizing:border-box}' +
    '.rbc-launch{position:fixed;right:20px;bottom:20px;z-index:2147483000;width:60px;height:60px;border-radius:50%;border:0;cursor:pointer;color:#fff;background:linear-gradient(135deg,var(--or),var(--rd));box-shadow:0 8px 24px rgba(229,50,15,.4);display:flex;align-items:center;justify-content:center;transition:transform .15s}' +
    '.rbc-launch:hover{transform:scale(1.06)}.rbc-launch:focus-visible,.rbc button:focus-visible,.rbc input:focus-visible,.rbc textarea:focus-visible{outline:3px solid #ffd2a8;outline-offset:2px}' +
    '.rbc-launch svg{width:28px;height:28px}' +
    '.rbc-tip{position:fixed;right:90px;bottom:32px;z-index:2147483000;background:#fff;color:#0f1a2b;padding:9px 14px;border-radius:12px;font-weight:600;box-shadow:0 6px 20px rgba(0,0,0,.25);cursor:pointer;max-width:220px}' +
    '.rbc-tip::after{content:"";position:absolute;right:-6px;top:50%;margin-top:-6px;border:6px solid transparent;border-left-color:#fff;border-right:0}' +
    '.rbc-panel{position:fixed;right:20px;bottom:92px;z-index:2147483000;width:380px;max-width:calc(100vw - 24px);height:min(620px,calc(100vh - 112px));background:var(--n1);border:1px solid var(--n3);border-radius:18px;box-shadow:0 18px 50px rgba(0,0,0,.45);display:none;flex-direction:column;overflow:hidden}' +
    '.rbc-panel.open{display:flex}' +
    '.rbc-head{padding:14px 16px;background:linear-gradient(135deg,var(--or),var(--rd));display:flex;align-items:center;gap:10px;color:#fff}' +
    '.rbc-head b{display:block;font-size:15px}.rbc-head small{opacity:.92;font-size:12px}.rbc-head>div{flex:1;min-width:0}' +
    '.rbc-x{background:rgba(255,255,255,.18);border:0;color:#fff;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px;line-height:1}' +
    '.rbc-log{flex:1;overflow:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:var(--n1)}' +
    '.rbc-m{max-width:88%;padding:10px 13px;border-radius:14px;word-wrap:break-word;overflow-wrap:anywhere}' +
    '.rbc-m p{margin:0 0 6px}.rbc-m p:last-child{margin:0}.rbc-m ul{margin:4px 0 6px;padding-left:20px}.rbc-m a{color:#ffb27a;text-decoration:underline}' +
    '.rbc-b{align-self:flex-start;background:var(--n2);border:1px solid var(--n3);border-bottom-left-radius:4px}' +
    '.rbc-u{align-self:flex-end;background:linear-gradient(135deg,var(--or),var(--rd));color:#fff;border-bottom-right-radius:4px}' +
    '.rbc-err{align-self:flex-start;background:#3a1a1a;border:1px solid #6b2a2a;color:#ffd0d0}' +
    '.rbc-chips{display:flex;flex-wrap:wrap;gap:7px}' +
    '.rbc-chip,.rbc-cta{border:1px solid var(--n3);background:var(--n2);color:var(--tx);padding:7px 11px;border-radius:999px;font:inherit;font-size:13px;cursor:pointer}' +
    '.rbc-chip:hover{border-color:var(--or)}' +
    '.rbc-cta{background:linear-gradient(135deg,var(--or),var(--rd));border:0;font-weight:600;color:#fff;align-self:flex-start}' +
    '.rbc-dots{display:inline-flex;gap:4px;align-items:center;height:18px}.rbc-dots i{width:7px;height:7px;border-radius:50%;background:var(--mu);animation:rbcb 1.1s infinite ease-in-out}' +
    '.rbc-dots i:nth-child(2){animation-delay:.15s}.rbc-dots i:nth-child(3){animation-delay:.3s}' +
    '@keyframes rbcb{0%,80%,100%{opacity:.3;transform:translateY(0)}40%{opacity:1;transform:translateY(-4px)}}' +
    '@media (prefers-reduced-motion:reduce){.rbc-dots i{animation:none;opacity:.7}.rbc-launch{transition:none}}' +
    '.rbc-foot{border-top:1px solid var(--n3);padding:10px 12px 8px;background:var(--n1)}' +
    '.rbc-row{display:flex;gap:8px}' +
    '.rbc-in{flex:1;background:var(--n2);border:1px solid var(--n3);color:var(--tx);border-radius:12px;padding:10px 12px;font:inherit;resize:none;max-height:90px}' +
    '.rbc-in::placeholder{color:var(--mu)}' +
    '.rbc-send{background:linear-gradient(135deg,var(--or),var(--rd));border:0;color:#fff;border-radius:12px;padding:0 14px;font-weight:600;cursor:pointer}' +
    '.rbc-send:disabled,.rbc-in:disabled{opacity:.55;cursor:not-allowed}' +
    '.rbc-note{display:block;color:var(--mu);font-size:11px;margin-top:6px;text-align:center}' +
    '.rbc-link{background:none;border:0;color:#ffb27a;cursor:pointer;font:inherit;font-size:12px;text-decoration:underline;padding:4px 0}' +
    '.rbc-form{display:none;flex:1;overflow:auto;padding:14px;flex-direction:column;gap:10px;background:var(--n1)}.rbc-form.open{display:flex}' +
    '.rbc-form label{font-size:12px;color:var(--mu);display:block;margin-bottom:3px}' +
    '.rbc-form input[type=text],.rbc-form input[type=email],.rbc-form input[type=tel],.rbc-form textarea{width:100%;background:var(--n2);border:1px solid var(--n3);color:var(--tx);border-radius:10px;padding:9px 11px;font:inherit}' +
    '.rbc-form textarea{min-height:70px;resize:vertical}' +
    '.rbc-chk{display:flex;gap:8px;align-items:flex-start;font-size:12px;color:var(--mu)}.rbc-chk input{margin-top:2px}' +
    '.rbc-fe{color:#ff9d9d;font-size:12px;min-height:14px}.rbc-fo{color:#9de3b3;font-weight:600}' +
    '.rbc-hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;opacity:0}' +
    '@media (max-width:480px){.rbc-panel{right:0;bottom:0;width:100vw;max-width:100vw;height:100vh;height:100dvh;border-radius:0}.rbc-tip{display:none}}';
  var st = document.createElement('style'); st.textContent = css; document.head.appendChild(st);

  /* ---------------------------- interfaz ---------------------------- */
  var root = el('div', 'rbc');
  var launch = el('button', 'rbc-launch', { type: 'button', 'aria-haspopup': 'dialog', 'aria-expanded': 'false' });
  launch.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><circle cx="9" cy="12" r=".8" fill="currentColor"/><circle cx="13" cy="12" r=".8" fill="currentColor"/><circle cx="17" cy="12" r=".8" fill="currentColor"/></svg>';
  var tip = el('div', 'rbc-tip', { role: 'status' });
  var panel = el('div', 'rbc-panel', { role: 'dialog', 'aria-modal': 'false', 'aria-labelledby': 'rbc-title' });

  var head = el('div', 'rbc-head');
  var hd = el('div'); var title = el('b', '', { id: 'rbc-title' }); var sub = el('small');
  hd.appendChild(title); hd.appendChild(sub);
  var closeBtn = el('button', 'rbc-x', { type: 'button' }); closeBtn.textContent = '×';
  head.appendChild(hd); head.appendChild(closeBtn);

  var log = el('div', 'rbc-log', { role: 'log', 'aria-live': 'polite' });
  var form = el('form', 'rbc-form', { novalidate: 'novalidate' });
  var foot = el('div', 'rbc-foot');
  var row = el('div', 'rbc-row');
  var input = el('textarea', 'rbc-in', { rows: '1', maxlength: String(MAX_LEN) });
  var sendBtn = el('button', 'rbc-send', { type: 'button' });
  row.appendChild(input); row.appendChild(sendBtn);
  var advisorLink = el('button', 'rbc-link', { type: 'button' });
  var note = el('span', 'rbc-note');
  foot.appendChild(row); foot.appendChild(advisorLink); foot.appendChild(note);
  panel.appendChild(head); panel.appendChild(log); panel.appendChild(form); panel.appendChild(foot);
  root.appendChild(tip); root.appendChild(panel); root.appendChild(launch);

  function applyTexts() {
    title.textContent = tr('titulo'); sub.textContent = tr('sub');
    closeBtn.setAttribute('aria-label', tr('cerrar')); launch.setAttribute('aria-label', tr('abrir'));
    input.setAttribute('placeholder', tr('ph')); input.setAttribute('aria-label', tr('ph'));
    sendBtn.textContent = tr('enviar'); advisorLink.textContent = tr('asesor'); note.textContent = tr('aviso');
    tip.textContent = tr('ayuda');
  }

  function bubble(cls, text, html) {
    var d = el('div', 'rbc-m ' + cls);
    if (html) d.innerHTML = fmt(text); else d.textContent = text;
    log.appendChild(d); log.scrollTop = log.scrollHeight; return d;
  }
  function ctaButton() {
    var b = el('button', 'rbc-cta', { type: 'button' }); b.textContent = tr('asesor');
    b.addEventListener('click', showForm); log.appendChild(b); log.scrollTop = log.scrollHeight;
  }
  function chips() {
    var w = el('div', 'rbc-chips');
    tr('chips').forEach(function (c) {
      var b = el('button', 'rbc-chip', { type: 'button' }); b.textContent = c;
      b.addEventListener('click', function () { w.remove(); send(c); }); w.appendChild(b);
    });
    log.appendChild(w);
  }
  function renderAll() {
    log.textContent = '';
    bubble('rbc-b', tr('bienvenida'), true);
    msgs.forEach(function (m) {
      if (m.r === 'u') bubble('rbc-u', m.t, false);
      else { bubble(m.e ? 'rbc-err' : 'rbc-b', m.t, !m.e); if (m.c) ctaButton(); }
    });
    if (!msgs.length) chips();
  }

  function typing() {
    var d = el('div', 'rbc-m rbc-b', { 'aria-label': tr('pensando') });
    d.innerHTML = '<span class="rbc-dots"><i></i><i></i><i></i></span>';
    log.appendChild(d); log.scrollTop = log.scrollHeight; return d;
  }
  function setBusy(v) { busy = v; input.disabled = v; sendBtn.disabled = v; }

  function send(text) {
    text = String(text || '').trim().slice(0, MAX_LEN);
    if (!text || busy) return;
    var ch = log.querySelector('.rbc-chips'); if (ch) ch.remove();
    var hist = msgs.filter(function (m) { return !m.e; }).slice(-6).map(function (m) { return { rol: m.r === 'u' ? 'user' : 'assistant', texto: m.t }; });
    msgs.push({ r: 'u', t: text }); bubble('rbc-u', text, false); input.value = ''; autoGrow(); setBusy(true);
    var tp = typing();
    var ctl = ('AbortController' in window) ? new AbortController() : null;
    var to = setTimeout(function () { if (ctl) ctl.abort(); }, 70000);
    fetch(ENDPOINT, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, signal: ctl ? ctl.signal : undefined,
      body: JSON.stringify({ mensaje: text, historial: hist, idioma: lang, pagina: location.pathname })
    }).then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (d) {
        tp.remove();
        if (d && d.ok && d.respuesta) { var c = !!d.ofrecer_contacto; msgs.push({ r: 'b', t: d.respuesta, c: c }); bubble('rbc-b', d.respuesta, true); if (c) ctaButton(); }
        else { var e = (d && (d.respuesta || d.error)) || tr('err'); msgs.push({ r: 'b', t: e, e: 1 }); bubble('rbc-err', e, false); }
      })
      .catch(function () { tp.remove(); msgs.push({ r: 'b', t: tr('err'), e: 1 }); bubble('rbc-err', tr('err'), false); })
      .then(function () { clearTimeout(to); setBusy(false); save(); input.focus(); });
  }

  /* ---------------------------- formulario de asesor ---------------------------- */
  function buildForm() {
    form.textContent = '';
    var h = el('b'); h.textContent = tr('f_titulo'); form.appendChild(h);
    function field(name, type, label, req) {
      var w = el('div'); var l = el('label', '', { for: 'rbc-f-' + name }); l.textContent = label;
      var i = type === 'textarea' ? el('textarea', '', { id: 'rbc-f-' + name, name: name, maxlength: '1000' })
                                  : el('input', '', { id: 'rbc-f-' + name, name: name, type: type, maxlength: '150', autocomplete: name === 'email' ? 'email' : name === 'telefono' ? 'tel' : 'off' });
      if (req) i.setAttribute('required', 'required');
      w.appendChild(l); w.appendChild(i); form.appendChild(w); return i;
    }
    field('nombre', 'text', tr('f_nombre'), true); field('email', 'email', tr('f_email'), true);
    field('telefono', 'tel', tr('f_tel')); field('empresa', 'text', tr('f_emp')); field('mensaje', 'textarea', tr('f_msg'));
    var hp = el('input', 'rbc-hp', { type: 'text', name: 'web', tabindex: '-1', autocomplete: 'off', 'aria-hidden': 'true' }); form.appendChild(hp);
    var chk = el('label', 'rbc-chk'); var cb = el('input', '', { type: 'checkbox', name: 'acepto' }); var sp = el('span'); sp.textContent = tr('f_acepto');
    chk.appendChild(cb); chk.appendChild(sp); form.appendChild(chk);
    var fe = el('div', 'rbc-fe', { role: 'alert' }); form.appendChild(fe);
    var ok = el('button', 'rbc-send', { type: 'submit' }); ok.style.padding = '11px'; ok.textContent = tr('f_enviar'); form.appendChild(ok);
    var back = el('button', 'rbc-link', { type: 'button' }); back.textContent = tr('f_volver'); back.addEventListener('click', hideForm); form.appendChild(back);
    form.onsubmit = function (ev) {
      ev.preventDefault();
      var f = form.elements, d = { nombre: f.nombre.value.trim(), email: f.email.value.trim(), telefono: f.telefono.value.trim(),
        empresa: f.empresa.value.trim(), mensaje: f.mensaje.value.trim(), web: f.web.value, acepto: f.acepto.checked, idioma: lang, pagina: location.pathname };
      if (d.nombre.length < 2) { fe.textContent = tr('f_err_nombre'); return; }
      if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(d.email)) { fe.textContent = tr('f_err_email'); return; }
      if (!d.acepto) { fe.textContent = tr('f_err_acepto'); return; }
      fe.textContent = ''; ok.disabled = true; ok.textContent = tr('f_enviando');
      fetch(ENDPOINT + '?accion=contacto', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(d) })
        .then(function (r) { return r.json().catch(function () { return {}; }); })
        .then(function (r) {
          if (r && r.ok) { form.textContent = ''; var o = el('p', 'rbc-fo'); o.textContent = tr('f_ok'); form.appendChild(o);
            var bk = el('button', 'rbc-link', { type: 'button' }); bk.textContent = tr('f_volver'); bk.addEventListener('click', hideForm); form.appendChild(bk);
            msgs.push({ r: 'b', t: tr('f_ok') }); save(); }
          else { ok.disabled = false; ok.textContent = tr('f_enviar'); fe.textContent = (r && r.respuesta && r.error && r.error.length > 12) ? r.respuesta : tr('err'); }
        })
        .catch(function () { ok.disabled = false; ok.textContent = tr('f_enviar'); fe.textContent = tr('err'); });
    };
  }
  function showForm() { buildForm(); log.style.display = 'none'; foot.style.display = 'none'; form.classList.add('open'); var n = form.querySelector('input'); if (n) n.focus(); }
  function hideForm() { form.classList.remove('open'); log.style.display = ''; foot.style.display = ''; renderAll(); input.focus(); }

  /* ---------------------------- abrir / cerrar ---------------------------- */
  function openPanel() {
    var nl = detectLang(); if (nl !== lang && !msgs.length) { lang = nl; }
    applyTexts(); if (!log.childNodes.length) renderAll();
    panel.classList.add('open'); tip.style.display = 'none'; launch.setAttribute('aria-expanded', 'true');
    try { sessionStorage.setItem(STORE + '_tip', '1'); } catch (e) {}
    setTimeout(function () { input.focus(); log.scrollTop = log.scrollHeight; }, 30);
  }
  function closePanel() { panel.classList.remove('open'); launch.setAttribute('aria-expanded', 'false'); launch.focus(); }
  function autoGrow() { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 90) + 'px'; }

  launch.addEventListener('click', function () { panel.classList.contains('open') ? closePanel() : openPanel(); });
  tip.addEventListener('click', openPanel);
  closeBtn.addEventListener('click', closePanel);
  advisorLink.addEventListener('click', showForm);
  sendBtn.addEventListener('click', function () { send(input.value); });
  input.addEventListener('input', autoGrow);
  input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(input.value); } });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && panel.classList.contains('open')) closePanel(); });

  function init() {
    applyTexts(); document.body.appendChild(root); tip.style.display = 'none';
    var seen = false; try { seen = !!sessionStorage.getItem(STORE + '_tip'); } catch (e) {}
    if (!seen && !started) setTimeout(function () { if (!panel.classList.contains('open')) tip.style.display = 'block'; }, 6000);
    if (typeof window.RubicamChatReady === 'function') window.RubicamChatReady({ open: openPanel, close: closePanel });
  }
  if (document.body) init(); else document.addEventListener('DOMContentLoaded', init);
})();
