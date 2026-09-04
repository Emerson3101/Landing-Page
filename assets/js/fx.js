/* =====================================================================
   fx.js — interaction & flair layer ("Terminal Noir")
   ---------------------------------------------------------------------
   Small independent modules, each gated and degradable:

     1. scrollProgress  top hairline fill (--sp) on scroll
     2. cursorGlow      soft accent spotlight trailing the pointer
     3. magnetic        [data-magnetic] elements gently follow the cursor
     4. tilt            [data-tilt] 3D card tilt + sheen (-gx/-gy)
     5. scramble        [data-scramble] decode-in text effect
     6. timelineRail    draws the experience rail with scroll (--rail)
     7. palette         Ctrl+K command palette (nav, toggles, easter egg)
     8. pageWipe        accent wipe on same-origin navigation
     9. clock           live mono clock in the footer status bar

   Nothing here is load-bearing: fine-pointer checks, reduced-motion,
   and try/catch fences keep the base experience intact everywhere.
   ===================================================================== */

'use strict';

(function fx() {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(pointer: fine)').matches;
  var isEs = function () {
    return document.documentElement.getAttribute('data-active-lang') === 'es';
  };

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }
  function rafThrottle(cb) {
    var queued = false;
    return function () {
      if (queued) return;
      queued = true;
      requestAnimationFrame(function () { queued = false; cb(); });
    };
  }

  /* --- 1. Scroll progress ----------------------------------------------- */
  function initScrollProgress() {
    var fill = document.querySelector('.scroll-progress__fill');
    if (!fill) return;
    var update = rafThrottle(function () {
      var doc = document.documentElement;
      var max = doc.scrollHeight - window.innerHeight;
      var p = max > 0 ? Math.min(1, (window.pageYOffset || doc.scrollTop) / max) : 0;
      fill.style.setProperty('--sp', p.toFixed(4));
    });
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
  }

  /* --- 2. Cursor glow ---------------------------------------------------- */
  function initCursorGlow() {
    if (!finePointer || reduceMotion) return;
    var glow = document.createElement('div');
    glow.className = 'cursor-glow';
    glow.setAttribute('aria-hidden', 'true');
    document.body.appendChild(glow);

    var x = -1e3, y = -1e3, tx = x, ty = y, shown = false;
    window.addEventListener('pointermove', function (e) {
      tx = e.clientX; ty = e.clientY;
      if (!shown) { shown = true; glow.classList.add('is-on'); x = tx; y = ty; }
    }, { passive: true });
    document.documentElement.addEventListener('pointerleave', function () {
      shown = false; glow.classList.remove('is-on');
    });

    (function tick() {
      x += (tx - x) * 0.12;
      y += (ty - y) * 0.12;
      glow.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)';
      requestAnimationFrame(tick);
    })();
  }

  /* --- 3. Magnetic elements ---------------------------------------------- */
  function initMagnetic() {
    if (!finePointer || reduceMotion) return;
    var STRENGTH = 0.22;   /* fraction of the pointer offset to pull */
    var RADIUS_BOOST = 40; /* px of extra tolerance outside the box */

    Array.prototype.forEach.call(document.querySelectorAll('[data-magnetic]'), function (el) {
      var ox = 0, oy = 0, tx = 0, ty = 0, active = false;

      function loop() {
        ox += (tx - ox) * 0.18;
        oy += (ty - oy) * 0.18;
        el.style.transform = 'translate3d(' + ox.toFixed(2) + 'px,' + oy.toFixed(2) + 'px,0)';
        if (Math.abs(tx - ox) > 0.1 || Math.abs(ty - oy) > 0.1 || active) {
          requestAnimationFrame(loop);
        } else {
          el.style.transform = '';
        }
      }

      el.addEventListener('pointerenter', function () {
        active = true;
        requestAnimationFrame(loop);
      });
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect();
        tx = (e.clientX - (r.left + r.width / 2)) * STRENGTH;
        ty = (e.clientY - (r.top + r.height / 2)) * STRENGTH;
        var max = Math.max(r.width, r.height) / 2 + RADIUS_BOOST;
        tx = Math.max(-max, Math.min(max, tx));
        ty = Math.max(-max, Math.min(max, ty));
      });
      el.addEventListener('pointerleave', function () { tx = 0; ty = 0; active = false; });
    });
  }

  /* --- 4. Tilt cards ------------------------------------------------------ */
  function initTilt() {
    if (!finePointer || reduceMotion) return;
    var MAX = 6; /* degrees */

    Array.prototype.forEach.call(document.querySelectorAll('[data-tilt]'), function (el) {
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width;
        var py = (e.clientY - r.top) / r.height;
        el.style.transform =
          'perspective(800px) rotateX(' + ((0.5 - py) * MAX).toFixed(2) + 'deg)' +
          ' rotateY(' + ((px - 0.5) * MAX).toFixed(2) + 'deg) translateY(-3px)';
        el.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
        el.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
      });
      el.addEventListener('pointerleave', function () {
        el.style.transform = '';
      });
    });
  }

  /* --- 5. Scramble / decode-in ------------------------------------------- */
  function initScramble() {
    if (reduceMotion) return;
    var GLYPHS = '!<>-_\\/[]{}=+*^?#________';

    function scramble(el) {
      var finalText = el.textContent;
      var len = finalText.length;
      var frame = 0;
      var totalFrames = Math.min(26, 12 + len);
      (function step() {
        var out = '';
        var solved = Math.floor((frame / totalFrames) * len);
        for (var i = 0; i < len; i++) {
          var c = finalText.charAt(i);
          if (c === ' ' || i < solved) out += c;
          else out += GLYPHS.charAt(Math.floor(Math.random() * GLYPHS.length));
        }
        el.textContent = out;
        frame++;
        if (frame <= totalFrames) requestAnimationFrame(step);
        else el.textContent = finalText;
      })();
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-scramble]'), function (el) {
      /* bilingual wrapper → scramble the SPAN that is currently visible */
      var spans = el.querySelectorAll('[data-lang]');
      var target = el;
      if (spans.length) {
        for (var i = 0; i < spans.length; i++) {
          if (spans[i].offsetParent !== null) { target = spans[i]; break; }
        }
      }
      /* hero items are above the fold: run shortly after their reveal */
      setTimeout(scramble.bind(null, target), 350 + Math.random() * 250);
    });
  }

  /* --- 6. Timeline rail + node lighting ---------------------------------- */
  function initTimelineRail() {
    var tl = document.querySelector('.timeline');
    if (!tl) return;

    var update = rafThrottle(function () {
      var r = tl.getBoundingClientRect();
      var vh = window.innerHeight;
      /* 0 when the rail top hits 80% viewport, 1 when its bottom reaches 35% */
      var start = vh * 0.8, end = vh * 0.35;
      var p = (start - r.top) / Math.max(1, (r.height + start - end * 0.5));
      tl.style.setProperty('--rail', Math.max(0, Math.min(1, p)).toFixed(3));
    });
    window.addEventListener('scroll', update, { passive: true });
    update();

    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          en.target.classList.toggle('is-lit', en.isIntersecting);
        });
      }, { rootMargin: '-35% 0px -35% 0px' });
      Array.prototype.forEach.call(tl.querySelectorAll('.timeline__item'), function (it) {
        io.observe(it);
      });
    }
  }

  /* --- 7. Command palette ------------------------------------------------- */
  function initPalette() {
    var openBtn = document.getElementById('palette-open');
    var root = document.createElement('div');
    root.className = 'palette';
    root.id = 'palette';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.innerHTML =
      '<div class="palette__box">' +
        '<input class="palette__input" id="palette-input" type="text" autocomplete="off" spellcheck="false">' +
        '<ul class="palette__list" id="palette-list" role="listbox"></ul>' +
        '<p class="palette__hint">↑↓ navigate · ⏎ select · esc close</p>' +
      '</div>';
    document.body.appendChild(root);

    var input = root.querySelector('#palette-input');
    var list = root.querySelector('#palette-list');
    var active = 0, visible = [];

    function commands() {
      var es = isEs();
      var items = [
        { kind: es ? 'sección' : 'section', label: es ? 'Ir a Inicio' : 'Go to Top', run: function () { location.hash = ''; location.href = '/#top'; } },
        { kind: es ? 'sección' : 'section', label: es ? 'Acerca de' : 'About', run: function () { location.href = '/#about'; } },
        { kind: es ? 'sección' : 'section', label: es ? 'Habilidades' : 'Skills', run: function () { location.href = '/#skills'; } },
        { kind: es ? 'sección' : 'section', label: es ? 'Experiencia' : 'Experience', run: function () { location.href = '/#experience'; } },
        { kind: es ? 'sección' : 'section', label: es ? 'Educación' : 'Education', run: function () { location.href = '/#education'; } },
        { kind: es ? 'sección' : 'section', label: es ? 'Contacto' : 'Contact', run: function () { location.href = '/#contact'; } },
        { kind: es ? 'página' : 'page', label: es ? 'Portafolio' : 'Portfolio', run: function () { location.href = '/portfolio/'; } },
        { kind: es ? 'acción' : 'action', label: es ? 'Cambiar tema' : 'Toggle theme', run: function () { var b = document.getElementById('theme-toggle'); if (b) b.click(); } },
        { kind: es ? 'acción' : 'action', label: es ? 'Cambiar idioma (EN/ES)' : 'Switch language (EN/ES)', run: function () { var b = document.getElementById('lang-toggle'); if (b) b.click(); } },
        { kind: es ? 'correo' : 'email', label: 'emersonplancarte@gmail.com', run: function () { location.href = 'mailto:emersonplancarte@gmail.com'; } },
        { kind: '…', label: 'sudo hire me', run: function () {
            /* easter egg: brief full-page accent heartbeat */
            document.body.animate(
              [{ filter: 'none' },
               { filter: 'drop-shadow(0 0 2rem var(--color-accent, #36e8a0))' },
               { filter: 'none' }],
              { duration: 1400, easing: 'ease-out' });
          }
        }
      ];
      return items;
    }

    function render(q) {
      var query = (q || '').toLowerCase();
      visible = commands().filter(function (c) {
        return !query || c.label.toLowerCase().indexOf(query) !== -1 || c.kind.indexOf(query) !== -1;
      });
      active = 0;
      list.innerHTML = visible.map(function (c, i) {
        return '<li><button type="button" class="palette__item" role="option" data-i="' + i + '"' +
               ' aria-selected="' + (i === active) + '">' +
               '<span class="palette__item__kind">' + c.kind + '</span>' +
               '<span>' + c.label.replace(/</g, '&lt;') + '</span></button></li>';
      }).join('') || '<li class="palette__item" aria-selected="false"><span class="palette__item__kind">—</span><span>' +
                     (isEs() ? 'Sin resultados' : 'No results') + '</span></li>';
    }

    function open() {
      render('');
      input.value = '';
      input.placeholder = isEs() ? 'Escribe un comando o busca…' : 'Type a command or search…';
      root.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      setTimeout(function () { input.focus(); }, 50);
    }
    function close() {
      root.classList.remove('is-open');
      document.body.style.overflow = '';
    }
    function isOpen() { return root.classList.contains('is-open'); }

    function pick(i) {
      var cmd = visible[i];
      close();
      if (cmd) setTimeout(cmd.run, 60);
    }

    input.addEventListener('input', function () { render(input.value); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        if (!visible.length) return;
        active = (active + (e.key === 'ArrowDown' ? 1 : -1) + visible.length) % visible.length;
        Array.prototype.forEach.call(list.querySelectorAll('.palette__item'), function (b, i) {
          b.setAttribute('aria-selected', String(i === active));
        });
      } else if (e.key === 'Enter') {
        e.preventDefault();
        pick(active);
      }
    });
    list.addEventListener('click', function (e) {
      var b = e.target.closest('.palette__item[data-i]');
      if (b) pick(parseInt(b.getAttribute('data-i'), 10));
    });
    root.addEventListener('click', function (e) { if (e.target === root) close(); });

    document.addEventListener('keydown', function (e) {
      var k = (e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K');
      if (k) { e.preventDefault(); isOpen() ? close() : open(); return; }
      if (e.key === '/' && !isOpen() && !/(INPUT|TEXTAREA|SELECT)/.test(document.activeElement.tagName)) {
        e.preventDefault(); open(); return;
      }
      if (e.key === 'Escape' && isOpen()) close();
    });
    if (openBtn) openBtn.addEventListener('click', open);
  }

  /* --- 8. Page wipe on same-origin navigation ------------------------------ */
  function initPageWipe() {
    if (reduceMotion) return;
    var wipe = document.querySelector('.page-wipe');
    if (!wipe) return;
    var label = wipe.querySelector('.page-wipe__label');

    document.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href]') : null;
      if (!a || e.defaultPrevented) return;
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
      if (a.target === '_blank' || a.hasAttribute('download')) return;
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
      /* same-origin only */
      var url;
      try { url = new URL(href, location.href); } catch (err) { return; }
      if (url.origin !== location.origin) return;
      /* hash-only change on the same page → let the browser handle it */
      if (url.pathname === location.pathname && url.hash) return;

      e.preventDefault();
      label.textContent = url.pathname === '/' ? 'home' : url.pathname.replace(/\//g, ' ').trim();
      wipe.classList.add('is-running');
      setTimeout(function () { location.href = href; }, 300);
    });

    /* returning via bfcache: clear a leftover wipe */
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) wipe.classList.remove('is-running');
    });
  }

  /* --- 9. Footer clock ----------------------------------------------------- */
  function initClock() {
    var el = document.getElementById('footer-clock');
    if (!el) return;
    function tick() {
      var d = new Date();
      el.textContent =
        String(d.getHours()).padStart(2, '0') + ':' +
        String(d.getMinutes()).padStart(2, '0') + ':' +
        String(d.getSeconds()).padStart(2, '0');
    }
    tick();
    setInterval(tick, 1000);
  }

  /* --- Console easter egg -------------------------------------------------- */
  (function consoleHello() {
    try {
      console.log(
        '%c◈ Emerson Plancarte\n%cSoftware & Embedded Systems Engineer\ntry `sudo hire me` in the palette (Ctrl+K)',
        'color:#36e8a0;font-family:monospace;font-size:14px;font-weight:bold',
        'color:#a8b2c0;font-family:monospace'
      );
    } catch (e) {}
  })();

  /* --- boot ---------------------------------------------------------------- */
  ready(function () {
    initScrollProgress();
    initCursorGlow();
    initMagnetic();
    initTilt();
    initScramble();
    initTimelineRail();
    initPalette();
    initPageWipe();
    initClock();
  });
})();
