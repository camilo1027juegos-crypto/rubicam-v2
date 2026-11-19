/* ============================================================
   ws-adapter.js — Conecta el dashboard con el servidor (ws-api.php)
   Define window.claude, que es lo que el dashboard usa para
   guardar y leer datos, saber quién eres y tus permisos.
   Sin IA (el "sample" devuelve null).
   ============================================================ */
(function () {
  'use strict';
  var CFG = window.RUBICAM_CFG || {};
  var API = CFG.api || 'ws-api.php';
  var POLL_MS = 6000;

  function post(body) {
    return fetch(API, {
      method: 'POST', credentials: 'same-origin', cache: 'no-store',
      headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body)
    }).then(function (res) {
      if (res.status === 401) { var e = new Error('sin sesión'); e.code = 'unauthenticated'; throw e; }
      return res.json().catch(function () { var e = new Error('respuesta no válida'); e.code = 'unavailable'; throw e; })
        .then(function (j) {
          if (!j || j.ok === false) { var e = new Error((j && j.error) || 'error'); e.code = (j && j.code) || 'unavailable'; throw e; }
          return j;
        });
    });
  }

  var cache = {};     // colección -> [{id, data}]
  var subs = {};      // colección -> [callback]
  var errSubs = {};   // colección -> [errback]
  var started = false, wasOk = true;

  function notify(col) {
    var snap = { docs: (cache[col] || []).map(function (d) { return { id: d.id, data: function () { return d.data; } }; }) };
    (subs[col] || []).forEach(function (fn) { try { fn(snap); } catch (e) {} });
  }
  function loadAll() {
    return post({ op: 'listall' }).then(function (j) {
      var data = j.data || {};
      wasOk = true;
      Object.keys(subs).forEach(function (col) { cache[col] = data[col] || []; notify(col); });
    }).catch(function (e) {
      if (wasOk) {   // avisar solo al pasar de "bien" a "mal" (no spamear)
        wasOk = false;
        Object.keys(errSubs).forEach(function (col) { (errSubs[col] || []).forEach(function (fn) { try { fn(e); } catch (_) {} }); });
      }
    });
  }
  function ensurePoll() { if (started) return; started = true; loadAll(); setInterval(loadAll, POLL_MS); }

  var nudgeT = null;
  function nudge() { clearTimeout(nudgeT); nudgeT = setTimeout(loadAll, 400); }

  function collection(col) {
    return {
      onSnapshot: function (cb, errb) {
        (subs[col] = subs[col] || []).push(cb);
        if (errb) (errSubs[col] = errSubs[col] || []).push(errb);
        if (cache[col]) notify(col);
        ensurePoll();
        return function () {};
      },
      doc: function (id) {
        return {
          set: function (body) { return post({ op: 'set', col: col, id: id, data: body }).then(nudge); },
          delete: function () { return post({ op: 'delete', col: col, id: id }).then(nudge); }
        };
      }
    };
  }

  var DB = { collection: collection };
  var USER = {
    name: function () { return Promise.resolve((CFG.user && CFG.user.name) || 'Equipo Rubicam'); },
    id: function () { return Promise.resolve((CFG.user && CFG.user.id) || 'me'); },
    isOwner: function () { return Promise.resolve(!!CFG.esAdmin); },
    can: function () { return Promise.resolve(true); },   // el permiso real lo aplica el servidor por sección
    profiles: function () { return Promise.resolve({}); }
  };

  window.claude = {
    use: function (name) {
      if (name === 'db') return Promise.resolve(DB);
      if (name === 'user') return Promise.resolve(USER);
      if (name === 'sample') return Promise.resolve(null);  // sin IA
      return Promise.resolve(null);
    },
    refresh: function () { loadAll(); }
  };
})();
