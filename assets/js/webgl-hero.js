/* =====================================================================
   webgl-hero.js — abstract "aurora particle field" shader for the hero
   ---------------------------------------------------------------------
   Vanilla WebGL1, fullscreen quad, single fragment shader compositing:

     1. a slowly-drifting fbm nebula in two theme hues, and
     2. a field of soft circular bokeh particles floating upward,
        gently displaced by the pointer.

   Deliberately abstract and calm — no grids, no waveforms, nothing that
   reads as electrical. Fallbacks (in order): reduced-motion → skip
   animation loop & render one still frame; WebGL unavailable → leave the
   CSS gradient fallback on the canvas element; page hidden / hero
   scrolled past → loop paused.

   Colors are re-read from CSS variables so the theme toggle re-tints the
   shader without rebuilding the program.
   ===================================================================== */

'use strict';

(function heroGL() {
  var canvas = document.getElementById('hero-gl');
  if (!canvas) return;

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var gl = canvas.getContext('webgl', { antialias: false, alpha: true, powerPreference: 'low-power' })
        || canvas.getContext('experimental-webgl');
  if (!gl) return; /* CSS gradient fallback stays visible */

  /* --- Shaders -------------------------------------------------------- */
  var VERT = [
    'attribute vec2 p;',
    'void main(){ gl_Position = vec4(p, 0.0, 1.0); }'
  ].join('\n');

  var FRAG = [
    'precision mediump float;',
    'uniform vec2  u_res;',
    'uniform float u_time;',
    'uniform vec2  u_mouse;',   /* normalized -1..1, lerped in JS */
    'uniform vec3  u_colA;',    /* accent            */
    'uniform vec3  u_colB;',    /* secondary accent  */
    '',
    '/* hash / value-noise / fbm, cheap 4-octave version */',
    'float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }',
    'float noise(vec2 p){',
    '  vec2 i = floor(p), f = fract(p);',
    '  vec2 u = f * f * (3.0 - 2.0 * f);',
    '  return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), u.x),',
    '             mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y);',
    '}',
    'float fbm(vec2 p){',
    '  float v = 0.0, a = 0.5;',
    '  for (int i = 0; i < 4; i++){ v += a * noise(p); p *= 2.03; a *= 0.55; }',
    '  return v;',
    '}',
    '',
    '/* soft bokeh particle */',
    'float orb(vec2 uv, vec2 c, float r){',
    '  float d = length(uv - c);',
    '  return smoothstep(r, r * 0.25, d);',
    '}',
    '',
    'void main(){',
    '  vec2 uv = (gl_FragCoord.xy - 0.5 * u_res) / min(u_res.x, u_res.y);',
    '  float t = u_time * 0.05;',
    '',
    '  /* gentle pointer parallax of the whole field */',
    '  uv += u_mouse * 0.05;',
    '',
    '  /* domain-warped nebula */',
    '  vec2 q = vec2(fbm(uv * 1.6 + t), fbm(uv * 1.6 - t * 0.7));',
    '  float n = fbm(uv * 2.2 + q * 1.4);',
    '  vec3 base = u_colA * smoothstep(0.35, 0.9, n) * 0.55;',
    '  base += u_colB * smoothstep(0.55, 0.95, fbm(uv * 2.8 - t + q)) * 0.45;',
    '  base *= smoothstep(1.1, 0.15, length(uv));      /* vignette */',
    '',
    '  /* drifting bokeh field — one loop of layered orbs */',
    '  float glow = 0.0;',
    '  for (int i = 0; i < 14; i++){',
    '    float fi = float(i);',
    '    float seed = fi * 17.23;',
    '    vec2 c = vec2(',
    '      fract(sin(seed) * 43758.5) * 2.4 - 1.2 + sin(t * (0.3 + fract(seed) * 0.4)) * 0.10,',
    '      mod(fract(cos(seed) * 24634.6) + t * (0.02 + fract(seed * 0.7) * 0.03) * 2.0, 2.4) - 1.2',
    '    );',
    '    c += u_mouse * 0.08 * (0.3 + fract(seed * 1.3));  /* parallax depth */',
    '    float r = 0.012 + fract(seed * 0.37) * 0.05;',
    '    float tw = 0.55 + 0.45 * sin(t * (1.5 + fract(seed) * 2.0) + seed);',
    '    glow += orb(uv, c, r) * tw;',
    '  }',
    '  vec3 sparks = mix(u_colA, u_colB, 0.5 + 0.5 * sin(t)) * glow * 0.5;',
    '',
    '  vec3 col = base + sparks;',
    '  col = col / (1.0 + col);                          /* soft tonemap */',
    '  gl_FragColor = vec4(col, clamp(dot(col, vec3(1.0)), 0.0, 1.0));',
    '}'
  ].join('\n');

  function compile(type, src) {
    var s = gl.createShader(type);
    gl.shaderSource(s, src);
    gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
      /* leave CSS fallback in place */
      return null;
    }
    return s;
  }

  var vs = compile(gl.VERTEX_SHADER, VERT);
  var fs = compile(gl.FRAGMENT_SHADER, FRAG);
  if (!vs || !fs) return;

  var prog = gl.createProgram();
  gl.attachShader(prog, vs);
  gl.attachShader(prog, fs);
  gl.linkProgram(prog);
  if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return;
  gl.useProgram(prog);

  var buf = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buf);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
  var loc = gl.getAttribLocation(prog, 'p');
  gl.enableVertexAttribArray(loc);
  gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

  var uRes   = gl.getUniformLocation(prog, 'u_res');
  var uTime  = gl.getUniformLocation(prog, 'u_time');
  var uMouse = gl.getUniformLocation(prog, 'u_mouse');
  var uColA  = gl.getUniformLocation(prog, 'u_colA');
  var uColB  = gl.getUniformLocation(prog, 'u_colB');

  /* --- Theme colors from CSS custom properties ------------------------ */
  function cssColor(name, fallback) {
    var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    var m = v.match(/^#([0-9a-f]{6})$/i);
    if (!m) return fallback;
    var n = parseInt(m[1], 16);
    return [(n >> 16 & 255) / 255, (n >> 8 & 255) / 255, (n & 255) / 255];
  }
  function syncColors() {
    gl.uniform3fv(uColA, cssColor('--color-accent', [0.21, 0.91, 0.63]));
    gl.uniform3fv(uColB, cssColor('--color-accent-2', [0.29, 0.66, 1.0]));
  }
  syncColors();
  new MutationObserver(syncColors)
    .observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

  /* --- Sizing --------------------------------------------------------- */
  function resize() {
    var dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    var w = Math.floor(canvas.clientWidth * dpr);
    var h = Math.floor(canvas.clientHeight * dpr);
    if (canvas.width !== w || canvas.height !== h) {
      canvas.width = w; canvas.height = h;
      gl.viewport(0, 0, w, h);
    }
  }
  window.addEventListener('resize', resize, { passive: true });
  resize();

  /* --- Pointer (lerped toward target so motion stays buttery) -------- */
  var mx = 0, my = 0, tmx = 0, tmy = 0;
  window.addEventListener('pointermove', function (e) {
    tmx = (e.clientX / window.innerWidth) * 2 - 1;
    tmy = -((e.clientY / window.innerHeight) * 2 - 1);
  }, { passive: true });

  /* --- Loop control: pause off-screen / hidden tab / scrolled past ---- */
  var heroVisible = true;
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      heroVisible = entries[0].isIntersecting;
    }, { threshold: 0.05 }).observe(canvas);
  }

  var running = !reduceMotion;
  var rafId = null;
  var start = performance.now();
  var last = 0;

  function frame(now) {
    rafId = null;
    if (!heroVisible || document.hidden) { schedule(); return; }
    /* ~30fps is plenty for a calm field and halves GPU/CPU load */
    if (now - last < 33) { schedule(); return; }
    last = now;

    mx += (tmx - mx) * 0.06;
    my += (tmy - my) * 0.06;

    resize();
    gl.uniform2f(uRes, canvas.width, canvas.height);
    gl.uniform1f(uTime, (now - start) / 1000);
    gl.uniform2f(uMouse, mx, my);
    gl.drawArrays(gl.TRIANGLES, 0, 3);
    schedule();
  }
  function schedule() { if (running && rafId === null) rafId = requestAnimationFrame(frame); }

  if (reduceMotion) {
    /* one static frame so the hero still has the aurora tint */
    gl.uniform2f(uRes, canvas.width, canvas.height);
    gl.uniform1f(uTime, 3.0);
    gl.uniform2f(uMouse, 0, 0);
    gl.drawArrays(gl.TRIANGLES, 0, 3);
  } else {
    schedule();
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) { start += performance.now() - last; schedule(); }
    });
  }
})();
