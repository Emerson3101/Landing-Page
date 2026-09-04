/* =====================================================================
    fx-particles.js — ambient "ember field" across the whole document
    ---------------------------------------------------------------------
    A fixed-viewport canvas whose particle field lives in DOCUMENT
    coordinates: scrolling moves the page through the field, so new
    embers come into view as you descend instead of one static band
    pinned to the viewport. Soft cursor repulsion (a parting, not a
    chase), slow upward drift, sine wander.

    Physics model (frame-rate independent):
      - base drift velocity  : permanent, upward-ish, never damped
      - wander               : sine offset, per-particle phase/amplitude
      - cursor push velocity : separate term; only THIS one decays away
      - scroll wake          : brief vertical impulse from scroll deltas

    Reduced-motion: one static constellation, repainted on scroll only.
    Style: low count, low alpha, no link lines — embers, not a network.
    ===================================================================== */

'use strict';

(function fxParticles() {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(pointer: fine)').matches;

  var canvas = document.createElement('canvas');
  canvas.className = 'fx-particles';
  canvas.setAttribute('aria-hidden', 'true');
  document.body.appendChild(canvas);

  var ctx = canvas.getContext('2d');
  if (!ctx) return;

  var particles = [];
  var mouse = { cx: -1e4, cy: -1e4 };      /* viewport coords */
  var docH = 0;                             /* document height (CSS px) */
  var lastScrollY = window.pageYOffset;
  var scrollImpulse = 0;

  var colA = [54, 232, 160];   /* --color-accent   (#36e8a0) */
  var colB = [74, 168, 255];   /* --color-accent-2 (#4aa8ff) */

  function parseHex(h) {
    var m = h.match(/^#([0-9a-f]{6})$/i);
    return m ? [parseInt(m[1].slice(1, 3), 16), parseInt(m[1].slice(3, 5), 16), parseInt(m[1].slice(5, 7), 16)] : null;
  }
  function readTheme() {
    var cs = getComputedStyle(document.documentElement);
    var a = parseHex(cs.getPropertyValue('--color-accent').trim());
    var b = parseHex(cs.getPropertyValue('--color-accent-2').trim());
    if (a) colA = a;
    if (b) colB = b;
  }

  function rnd(a, b) { return a + Math.random() * (b - a); }

  function measureDocHeight() {
    var h = Math.max(
      document.body ? document.body.scrollHeight : 0,
      document.documentElement ? document.documentElement.scrollHeight : 0
    );
    if (h > 0 && h !== docH) {
      var ratio = docH > 0 ? h / docH : 1;
      docH = h;
      /* preserve the distribution when the layout height reflows */
      if (ratio !== 1) {
        for (var i = 0; i < particles.length; i++) particles[i].y *= ratio;
      }
    }
  }

  function Particle(deep) {
    this.x = rnd(0, window.innerWidth);
    this.y = rnd(0, Math.max(1, docH));
    this.deep = deep;                       /* far layer: smaller, dimmer, slower */
    this.r = deep ? rnd(0.7, 1.3) : rnd(1.3, 2.3);
    this.alpha = deep ? rnd(0.10, 0.20) : rnd(0.20, 0.35);
    this.mix = Math.random();               /* 0 = accent … 1 = accent-2 */
    /* permanent base drift (px per 60fps frame), upward-ish */
    this.dirX = rnd(-0.06, 0.06) * (deep ? 0.6 : 1);
    this.dirY = rnd(-0.10, -0.03) * (deep ? 0.6 : 1);
    /* sine wander */
    this.phase = rnd(0, Math.PI * 2);
    this.wobble = rnd(0.008, 0.02);
    this.wobbleAmp = rnd(0.10, 0.35);
    /* cursor push velocity — the ONLY velocity term that decays */
    this.pushX = 0;
    this.pushY = 0;
    this.twinkle = rnd(0.6, 1.4);
  }

  Particle.prototype.update = function (dt, mdx, mdy) {
    /* --- cursor repulsion (soft radial push, in document space) --- */
    var R = this.deep ? 90 : 140;
    var dx = this.x - mdx;
    var dy = this.y - mdy;
    var d2 = dx * dx + dy * dy;
    if (d2 > 0.25 && d2 < R * R) {
      var d = Math.sqrt(d2);
      var f = 1 - d / R;
      f = f * f;                            /* quadratic ease-out at rim */
      this.pushX += (dx / d) * f * 0.85;
      this.pushY += (dy / d) * f * 0.85;
    }

    /* only the cursor-derived velocity decays (~0.88/frame time constant) */
    var damp = Math.pow(0.88, dt);
    this.pushX *= damp;
    this.pushY *= damp;
    var ps = Math.sqrt(this.pushX * this.pushX + this.pushY * this.pushY);
    var maxPush = 3.2;
    if (ps > maxPush) {
      this.pushX = this.pushX / ps * maxPush;
      this.pushY = this.pushY / ps * maxPush;
    }

    /* permanent motion: drift + wander + push + scroll wake */
    this.phase += this.wobble * dt;
    this.x += (this.dirX + Math.sin(this.phase) * this.wobbleAmp + this.pushX) * dt;
    this.y += (this.dirY + Math.cos(this.phase * 0.9) * this.wobbleAmp * 0.6
             + this.pushY + scrollImpulse * (this.deep ? 0.4 : 1)) * dt;

    /* wrap in document space (x viewport-wide, y document-tall) */
    var W = window.innerWidth;
    if (this.x < -24) this.x = W + 24;
    else if (this.x > W + 24) this.x = -24;
    if (this.y < -24) this.y = docH + 24;
    else if (this.y > docH + 24) this.y = -24;
  };

  function dotColor(p) {
    var m = p.mix;
    return Math.round(colA[0] + (colB[0] - colA[0]) * m) + ', ' +
           Math.round(colA[1] + (colB[1] - colA[1]) * m) + ', ' +
           Math.round(colA[2] + (colB[2] - colA[2]) * m);
  }

  function drawViewport(scrollY, t) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    var vh = canvas.height;
    for (var i = 0; i < particles.length; i++) {
      var p = particles[i];
      var sy = p.y - scrollY;
      if (sy < -28 || sy > vh + 28) continue;   /* cull off-screen */
      var a = p.alpha * (0.75 + 0.25 * Math.sin(t * 0.0009 * p.twinkle + p.phase));
      var col = dotColor(p);
      ctx.fillStyle = 'rgba(' + col + ',' + (a * 0.4) + ')';
      ctx.beginPath();
      ctx.arc(p.x, sy, p.r * 2.4, 0, Math.PI * 2);
      ctx.fill();
      ctx.fillStyle = 'rgba(' + col + ',' + a + ')';
      ctx.beginPath();
      ctx.arc(p.x, sy, p.r, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    measureDocHeight();
  }

  function spawn() {
    measureDocHeight();
    particles.length = 0;
    /* Density is per-VIEWPORT (~60 dots on screen at once), then scaled by
       how many viewport-heights the document spans — the field lives in
       document space, so the count must cover the whole scroll length.
       Off-screen dots are culled in drawViewport, so the per-frame cost
       stays bound to what's visible. */
    var vh = window.innerHeight || 1;
    var perViewport = Math.round((window.innerWidth * vh) / 28000);
    perViewport = Math.max(24, Math.min(72, perViewport));
    var screens = Math.max(1, Math.ceil(docH / vh));
    var count = Math.max(perViewport, Math.min(500, perViewport * screens));
    for (var i = 0; i < count; i++) {
      particles.push(new Particle(i % 3 === 0));  /* ~⅓ on the far layer */
    }
  }

  /* --- animation loop (skipped entirely off-tab) --------------------- */
  var lastT = 0;
  function frame(now) {
    if (!document.hidden) {
      var dt = (now - lastT) / 16.666;          /* 60fps-normalized step */
      dt = dt > 3 ? 3 : (dt < 0.2 ? 0.2 : dt);
      lastT = now;

      var scrollY = window.pageYOffset;
      scrollImpulse = Math.max(-1.4, Math.min(1.4, (scrollY - lastScrollY) * 0.02));
      lastScrollY = scrollY;

      var mdy = mouse.cy + scrollY;              /* cursor in doc space */
      for (var i = 0; i < particles.length; i++) particles[i].update(dt, mouse.cx, mdy);
      drawViewport(scrollY, now);
      scrollImpulse *= 0.85;                     /* wake decays fast */
    }
    requestAnimationFrame(frame);
  }

  /* ------------------------------------------------------------------ */
  function boot() {
    readTheme();
    resize();
    spawn();

    new MutationObserver(readTheme)
      .observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    var rt = null;
    window.addEventListener('resize', function () {
      clearTimeout(rt);
      rt = setTimeout(function () { resize(); spawn(); }, 160);
    }, { passive: true });
    /* font/late layout shifts change doc height — re-measure once settled */
    setTimeout(measureDocHeight, 1500);

    if (reduceMotion) {
      /* static constellation that repaints per scroll position */
      drawViewport(lastScrollY, 0);
      var drawing = false;
      window.addEventListener('scroll', function () {
        if (drawing) return;
        drawing = true;
        var sy = window.pageYOffset;
        requestAnimationFrame(function () {
          drawViewport(sy, 0);
          drawing = false;
        });
      }, { passive: true });
      return;
    }

    if (finePointer) {
      window.addEventListener('pointermove', function (e) {
        mouse.cx = e.clientX;
        mouse.cy = e.clientY;
      }, { passive: true });
      document.documentElement.addEventListener('pointerleave', function () {
        mouse.cx = -1e4;
        mouse.cy = -1e4;
      });
    }

    requestAnimationFrame(function (t0) {
      lastT = t0;
      requestAnimationFrame(frame);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
