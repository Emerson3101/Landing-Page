/* =====================================================================
 * main.js — site behavior
 * -------------------------------------------------------------------
 * Loaded with `defer`, so the DOM is parsed before this runs. Modules
 * are deliberately small and self-contained so later phases can split
 * them into sibling files without surgery.
 *
 * Wired:
 *   - theme toggle (light/dark)         — persisted via localStorage
 *   - language toggle (EN/ES)           — persisted via localStorage
 *   - mobile nav drawer (Phase 6)       — toggle / Esc / click-outside
 *   - back-to-top (Phase 6)             — appears after scroll
 *   - contact form (Phase 6)            — client validation + async
 *                                         submit, mailto fallback
 *
 * Static text is bilingualized in PHP (both languages rendered; CSS
 * shows the active one via <html data-active-lang>). Only JS-generated
 * strings need a dictionary — see I18N/t() below — and they read the
 * active language each call so a mid-session toggle picks up at once.
 * ===================================================================*/

'use strict';

(function bootstrap() {

  function init() {
    initThemeToggle();
    initLangToggle();
    initNavDrawer();
    initBackToTop();
    initContactForm();
  }

  /* --- Tiny i18n for JS-generated strings ---------------------------
   * Static text is bilingual via PHP's dual-render; this only covers the
   * strings JS produces at runtime (nav labels, form validation, status).
   * Reads <html data-active-lang> each time so a mid-session language
   * flip picks up immediately.
   * ----------------------------------------------------------------- */
  var I18N = {
    en: {
      openMenu: 'Open menu', closeMenu: 'Close menu',
      switchToEn: 'Switch to English', switchToEs: 'Switch to Spanish',
      switchToDark: 'Switch to dark theme', switchToLight: 'Switch to light theme',
      sending: 'Sending…',
      success: 'Thanks — your message is on its way. I’ll reply within a day or two.',
      error: 'Something went wrong sending the form. You can email me directly at emersonplancarte@gmail.com.',
      nameRequired: 'Please enter your name.',
      emailRequired: 'Please enter your email address.',
      emailInvalid: 'Please enter a valid email address.',
      messageRequired: 'Please enter a message.',
      messageShort: 'Message must be at least 10 characters.'
    },
    es: {
      openMenu: 'Abrir menú', closeMenu: 'Cerrar menú',
      switchToEn: 'Cambiar a inglés', switchToEs: 'Cambiar a español',
      switchToDark: 'Cambiar a tema oscuro', switchToLight: 'Cambiar a tema claro',
      sending: 'Enviando…',
      success: 'Gracias — tu mensaje va en camino. Responderé en uno o dos días.',
      error: 'Algo salió mal al enviar el formulario. Puedes escribirme directamente a emersonplancarte@gmail.com.',
      nameRequired: 'Por favor escribe tu nombre.',
      emailRequired: 'Por favor escribe tu correo.',
      emailInvalid: 'Por favor escribe un correo válido.',
      messageRequired: 'Por favor escribe un mensaje.',
      messageShort: 'El mensaje debe tener al menos 10 caracteres.'
    }
  };
  function t(key) {
    var lang = document.documentElement.getAttribute('data-active-lang') === 'es' ? 'es' : 'en';
    return (I18N[lang] && I18N[lang][key]) || I18N.en[key] || '';
  }

  /* Bilingual aria-labels for the JS-managed toggle buttons (language +
     theme + the nav drawer). Each label names the TARGET state ("Switch to
     Spanish", "Switch to dark theme", "Close menu"). Re-run on init and
     whenever the language flips so screen-reader labels follow the active
     language — and on theme/nav state change so the target wording matches. */
  function applyToggleLabels() {
    var lang = document.documentElement.getAttribute('data-active-lang') === 'es' ? 'es' : 'en';
    var themeBtn = document.getElementById('theme-toggle');
    if (themeBtn) {
      var isLight = document.documentElement.getAttribute('data-theme') === 'light';
      themeBtn.setAttribute('aria-label', isLight ? t('switchToDark') : t('switchToLight'));
    }
    var langBtn = document.getElementById('lang-toggle');
    if (langBtn) {
      langBtn.setAttribute('aria-label', lang === 'es' ? t('switchToEn') : t('switchToEs'));
    }
    var navBtn = document.getElementById('nav-toggle');
    if (navBtn) {
      var open = document.body.getAttribute('data-nav-open') === 'true';
      navBtn.setAttribute('aria-label', open ? t('closeMenu') : t('openMenu'));
    }
  }

  /* Bilingual PHP-rendered attributes (aria-label, placeholder). The
     lang_attr() helper emits the English default + data-en/data-es + a
     data-i18n-attr marker; swap the real attribute to the active language
     so stats/stack lists, the portfolio filter group, and form placeholders
     localize when the language toggles (and on load for returning users). */
  function syncAttrI18n() {
    var lang = document.documentElement.getAttribute('data-active-lang') === 'es' ? 'es' : 'en';
    Array.prototype.forEach.call(
      document.querySelectorAll('[data-i18n-attr][data-en][data-es]'),
      function (el) {
        var attr = el.getAttribute('data-i18n-attr');
        if (!attr) return;
        var v = lang === 'es' ? el.getAttribute('data-es') : el.getAttribute('data-en');
        if (v !== null) el.setAttribute(attr, v);
      }
    );
  }

  /* --- Theme toggle ------------------------------------------------- */
  function initThemeToggle() {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    syncToggleState(btn);
    applyToggleLabels();
    btn.addEventListener('click', function () {
      var root = document.documentElement;
      var next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('theme', next); } catch (e) {}
      syncToggleState(btn);
      applyToggleLabels();
    });
    function syncToggleState(el) {
      el.setAttribute('aria-pressed',
        String(document.documentElement.getAttribute('data-theme') === 'light'));
    }
  }

  /* --- Language toggle --------------------------------------------- */
  function initLangToggle() {
    var btn = document.getElementById('lang-toggle');
    if (!btn) return;
    syncLangState(btn);
    applyToggleLabels();
    syncAttrI18n();
    btn.addEventListener('click', function () {
      var root = document.documentElement;
      var next = root.getAttribute('data-active-lang') === 'es' ? 'en' : 'es';
      root.setAttribute('data-active-lang', next);
      root.setAttribute('lang', next);
      try { localStorage.setItem('lang', next); } catch (e) {}
      syncLangState(btn);
      applyToggleLabels();
      syncAttrI18n();
    });
    function syncLangState(el) {
      el.setAttribute('aria-pressed',
        String(document.documentElement.getAttribute('data-active-lang') === 'es'));
    }
  }

  /* --- Mobile nav drawer (Phase 6) --------------------------------- */
  function initNavDrawer() {
    var toggle = document.getElementById('nav-toggle');
    var drawer = document.getElementById('nav-drawer');
    if (!toggle || !drawer) return;
    var links = drawer.querySelectorAll('.nav-drawer__link');

    function isOpen() {
      return document.body.getAttribute('data-nav-open') === 'true';
    }
    function open() {
      document.body.setAttribute('data-nav-open', 'true');
      toggle.setAttribute('aria-expanded', 'true');
      toggle.setAttribute('aria-label', t('closeMenu'));
      if (links.length) links[0].focus();
    }
    function close() {
      document.body.removeAttribute('data-nav-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', t('openMenu'));
      toggle.focus();
    }

    toggle.addEventListener('click', function () { isOpen() ? close() : open(); });

    // A link click navigates (hash anchor / portfolio) — dismiss so the
    // drawer is closed when the user comes back.
    Array.prototype.forEach.call(links, function (a) {
      a.addEventListener('click', close);
    });

    // Click on the dimmed page (outside the drawer + toggle) closes.
    document.addEventListener('click', function (e) {
      if (!isOpen()) return;
      if (drawer.contains(e.target) || toggle.contains(e.target)) return;
      close();
    });

    // Escape closes and returns focus to the toggle.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOpen()) close();
    });

    // Basic focus wrap inside the drawer.
    drawer.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab' || !isOpen() || links.length < 2) return;
      var first = links[0], last = links[links.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });

    // Close if the viewport grows past the breakpoint while open. Some
    // older engines still use addListener; feature-detect.
    var mq = window.matchMedia('(min-width: 48em)');
    function onBreak(e) { if (e.matches && isOpen()) close(); }
    if (mq.addEventListener) mq.addEventListener('change', onBreak);
    else if (mq.addListener) mq.addListener(onBreak);
  }

  /* --- Back-to-top (Phase 6) -------------------------------------- */
  function initBackToTop() {
    var btn = document.getElementById('to-top');
    if (!btn) return;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var shown = false;
    function onScroll() {
      var y = window.pageYOffset || document.documentElement.scrollTop || 0;
      var should = y > 600;
      if (should === shown) return;
      shown = should;
      btn.classList.toggle('is-visible', should);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }

  /* --- Contact form (Phase 6) ------------------------------------- */
  function initContactForm() {
    var form = document.getElementById('contact-form');
    if (!form) return;
    var nameEl   = document.getElementById('cf-name');
    var emailEl  = document.getElementById('cf-email');
    var msgEl    = document.getElementById('cf-message');
    var hpEl     = document.getElementById('cf-company');
    var statusEl = document.getElementById('cf-status');
    var submitBtn = document.getElementById('cf-submit');

    function setErr(field, msg) {
      var wrap = field.closest('.field');
      var err = wrap ? wrap.querySelector('.field__error') : null;
      if (err) err.textContent = msg;
      if (wrap) wrap.classList.toggle('field--error', !!msg);
      field.setAttribute('aria-invalid', msg ? 'true' : 'false');
    }
    function clearErr(field) { setErr(field, ''); }

    function validate() {
      var ok = true;
      if (!nameEl.value.trim()) { setErr(nameEl, t('nameRequired')); ok = false; } else clearErr(nameEl);

      var email = emailEl.value.trim();
      if (!email) { setErr(emailEl, t('emailRequired')); ok = false; }
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setErr(emailEl, t('emailInvalid')); ok = false; }
      else clearErr(emailEl);

      var msg = msgEl.value.trim();
      if (!msg) { setErr(msgEl, t('messageRequired')); ok = false; }
      else if (msg.length < 10) { setErr(msgEl, t('messageShort')); ok = false; }
      else clearErr(msgEl);

      return ok;
    }

    // Clear an error as soon as the user starts fixing that field.
    [nameEl, emailEl, msgEl].forEach(function (el) {
      el.addEventListener('input', function () {
        var wrap = el.closest('.field');
        if (wrap && wrap.classList.contains('field--error')) clearErr(el);
      });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (hpEl && hpEl.value) return;          // honeypot tripped — silent drop
      if (!validate()) {
        var firstErr = form.querySelector('.field--error .field__input, .field--error .field__textarea');
        if (firstErr) firstErr.focus();
        return;
      }

      setStatus('sending', t('sending'));
      submitBtn.disabled = true;

      var payload = {
        name: nameEl.value.trim(),
        email: emailEl.value.trim(),
        message: msgEl.value.trim(),
        company: hpEl ? hpEl.value : '',
        lang: document.documentElement.getAttribute('data-active-lang') === 'es' ? 'es' : 'en'
      };

      // Phase 7 PHP endpoint. 200 -> done. 422 -> surface the server's
      // bilingual field errors. 429 -> show its message. 404/500/network
      // (endpoint down) -> fall back to the visitor's own mail client so
      // the message still reaches me.
      submitViaApi(payload).then(function (res) {
        if (res.ok) { done(null); return; }
        if (res.status === 422 && res.data && res.data.errors) {
          if (res.data.errors.name)    setErr(nameEl,  res.data.errors.name);
          if (res.data.errors.email)   setErr(emailEl, res.data.errors.email);
          if (res.data.errors.message) setErr(msgEl,    res.data.errors.message);
          submitBtn.disabled = false;
          setStatus('', '');        // clear "Sending…" — fields now carry the feedback
          var firstErr = form.querySelector('.field--error .field__input, .field--error .field__textarea');
          if (firstErr) firstErr.focus();
          return;
        }
        if (res.status === 429 && res.data && res.data.message) {
          submitBtn.disabled = false;
          setStatus('error', res.data.message);
          return;
        }
        return Promise.reject(new Error('endpoint unavailable'));
      }).catch(function () {
        return submitViaMailto(payload).then(function () { done(null); },
                                        function (err) { done(err); });
      });

      function done(err) {
        submitBtn.disabled = false;
        if (err) setStatus('error', t('error'));
        else { setStatus('success', t('success')); form.reset(); }
      }
    });

    function submitViaApi(payload) {
      return fetch('/api/contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      }).then(function (r) {
        // Parse JSON defensively — a 5xx HTML error page or empty body
        // yields null, which the caller treats as "unavailable".
        return r.json().catch(function () { return null; }).then(function (data) {
          return { ok: r.ok, status: r.status, data: data };
        });
      });
    }
    function submitViaMailto(payload) {
      return new Promise(function (resolve, reject) {
        try {
          var subject = encodeURIComponent('Portfolio contact — ' + payload.name);
          var body = encodeURIComponent(payload.message + '\n\n— ' + payload.name + ' (' + payload.email + ')');
          window.location.href = 'mailto:emersonplancarte@gmail.com?subject=' + subject + '&body=' + body;
          resolve();
        } catch (e) { reject(e); }
      });
    }
    function setStatus(state, msg) {
      statusEl.setAttribute('data-state', state);
      statusEl.textContent = msg;
    }
  }

  /* --- Boot ----------------------------------------------------------
   * Dispatch sits LAST in the IIFE on purpose. `defer` runs us after the
   * parse (readyState is 'interactive', not 'loading'), so this branch
   * calls init() synchronously — i.e. during this IIFE's own execution.
   * The closure var `I18N` (and every hoisted function above) must be
   * initialized before that call. If the dispatch sat at the top of the
   * IIFE, init() would run before the `var I18N = {...}` assignment and
   * t() would read I18N as undefined (the "reading 'en'" TypeError). */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
