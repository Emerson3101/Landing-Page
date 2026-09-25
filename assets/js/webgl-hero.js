/* =====================================================================
   webgl-hero.js — Fluid Aurora & Physics-Driven Cyber-Polyhedron
   ---------------------------------------------------------------------
    Dual-pass WebGL rendering:
      1. Volumetric dual-tone Aurora Nebula with domain-warped fbm and
         drifting bokeh particles, COUPLED to a stable-fluids wake:
         pointer / finger movement splats momentum into a velocity
         field (curl + vorticity confinement, Jacobi pressure solve,
         semi-Lagrangian advection); the nebula domain bends around
         the wake — passing a hand through tinted water. The wake never
         introduces new color: it only lifts the light of the nebula
         already on screen, so the aurora stays in the page palette.
     2. Interactive 3D Wireframe Cyber-Polyhedron (dual icosahedron
        cage) with glowing vertices, inner core and orbiting satellite
        particles (per-particle orbits computed in the vertex shader).
     3. Physics-based manipulation, identical for mouse and touch:
        persistent arcball orientation (no euler tumble), drag with
        live angular-velocity tracking, flick-to-spin momentum with
        dt-normalized damping, auto-rotation that blends back in as
        momentum decays, pinch-zoom, tap pulse (+ a dye drop in the
        water), double-tap reset.
     4. Theme-responsive colors (#36e8a0 mint, #4aa8ff cyan).
     5. Performance guards: pauses when scrolled out of view or the
        tab is hidden, caps DPR at 2 (1.5 on the phone tier), paces
        rendered frames on the phone tier (~16.5 ms gate).

    PHYSICS CORE. Every rate (momentum damping, auto-spin, pulse decay,
    camera easing) is dt-normalized, so the feel is identical at 30, 60
    or 120 Hz. Desktop renders at the full rAF cadence; the frame gate
    is phone-tier only. Fluid sim steps at a fixed cadence (60 Hz
    desktop / 30 Hz phone) accumulated from real frame time, idles
    down to the two decay passes once no splat has arrived for ~2.5 s,
    and sleeps completely (zero sim draws) after ~5.5 s of stillness —
    the next touch of cursor or finger wakes it instantly.

    FLUID AURORA ("hand through tinted water"). Requires
    OES_texture_half_float, EXT_color_buffer_half_float AND
    OES_texture_half_float_linear — any missing piece quietly skips the
     subsystem and the nebula keeps its calm drifting look (a 1×1
     zero texture backs the uniforms, so the specialized shader reads
     no wake). Resolutions: velocity 192 / dye 512 on desktop, 96 / 192
    on phones, aspect-shaped, reallocated on orientation change.
    The nebula couples to the fields through 4-tap box-smoothed
    samples (no reliance on half-float LINEAR quality, which some
    drivers implement badly) and a soft-saturating displacement cap:
    stirring bends the fbm domain by up to ~4.5% of the screen, so
    every octave keeps its detail and the velocity grid's texel seams
    stay invisible — no smearing, no printed grid, no dithering.
    Cursor movement stirs anywhere over the hero band; on touch,
    window-level touchmove stirs page-wide — scrolling drags the water
    with it. Reduced motion skips the fluid entirely.

   PHONE-FIRST RENDERING TIER (coarse pointers / narrow viewports).
   Same scene, same fidelity — the cost moves, not the pixels:
     - Non-blocking boot at defer time: the aurora initializes right
       away (no late pop-in). Where the driver exposes
       KHR_parallel_shader_compile the shaders compile on background
       threads and a completion poll sequences the boot — the main
       thread never stalls, so the typing choreography stays smooth.
      - Bokeh orbs as POINT SPRITES: the 40-orb per-pixel loop (~80% of
        the fragment cost) is replaced by 40 full-resolution additive
        point sprites with the identical falloff, drift and twinkle —
        computed per frame on the CPU (40 orbs is
        nothing) and drawn with a blend that reproduces the shader's
       pre-tonemap accumulation. The orbs actually render at FULL
       resolution, so the sparkle is indistinguishable from desktop.
      - Nebula FBO: the soft-focus fbm aurora (all 4 octaves kept)
        renders into an offscreen framebuffer at 50% and is composited
        with linear filtering — visually equivalent on a field that is
        soft by design. While the wake is alive it refreshes every
        rendered frame (the water must answer the finger at once, and
        at the halved scale that costs less than the old every-2nd-
        frame 60% refresh); once calm it drops to every 2nd frame
        with the slowly drifting sky.
      - Device pixel ratio caps at 1.5: the hero is a soft aurora
        plus additive glow lines, every full-canvas pass (composite,
        wireframe, sprites, and the browser's own CSS-mask
        compositing per frame) costs bandwidth per device pixel, and
        a 300+ PPI panel at 1.5 still renders them Retina-crisp.
      - Full-canvas clears are gone — every pass writes every pixel
        anyway (fullscreen triangle, blending off), so the extra
        clear was pure bandwidth.
     - The crisp 3D wireframe, nodes and halo keep full canvas
       resolution every frame.
     - Uniform/attribute locations are cached once after linking.
     - powerPreference 'default' on the phone tier — no high-perf GPU
       hint, kinder to batteries.
     - If the point-sprite or composite programs fail to build, the
       bg shader recompiles WITH its orb loop and the direct
       full-quality path takes over — visual correctness first.

   TEST HOOK: window.__heroDebug (non-enumerable) exposes live spin /
   camera / fluid state so runtime physics can be asserted in tests.
   ===================================================================== */

'use strict';

(function hero3D() {
  var canvas = document.getElementById('hero-gl');
  if (!canvas) return;

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- Device tier -----------------------------------------------------
     lite = touch-primary (phones/tablets) or narrow viewports. Cores and
     RAM are deliberately NOT part of the signal: a 4-core desktop with
     a fine pointer and a wide screen must keep the full-quality path —
     nobody wants their desktop aurora silently downgraded. */
  var coarse = window.matchMedia('(pointer: coarse)').matches;
  var smallScreen = window.matchMedia('(max-width: 48rem)').matches;
  var lite = coarse || smallScreen;

  function boot() {
    var gl = canvas.getContext('webgl', {
      antialias: true,
      alpha: true,
      powerPreference: lite ? 'default' : 'high-performance'
    }) || canvas.getContext('experimental-webgl');
    if (!gl) return;

    /* ------------------------------------------------------------------
       Matrix 4x4 Math Helpers
       ------------------------------------------------------------------ */
    function mat4Create() {
      var out = new Float32Array(16);
      out[0] = 1; out[5] = 1; out[10] = 1; out[15] = 1;
      return out;
    }

    function mat4Perspective(out, fovy, aspect, near, far) {
      var f = 1.0 / Math.tan(fovy / 2);
      var nf = 1 / (near - far);
      out[0] = f / aspect; out[1] = 0; out[2] = 0; out[3] = 0;
      out[4] = 0; out[5] = f; out[6] = 0; out[7] = 0;
      out[8] = 0; out[9] = 0; out[10] = (far + near) * nf; out[11] = -1;
      out[12] = 0; out[13] = 0; out[14] = (2 * far * near) * nf; out[15] = 0;
      return out;
    }

    function mat4Multiply(out, a, b) {
      var a00 = a[0], a01 = a[1], a02 = a[2], a03 = a[3];
      var a10 = a[4], a11 = a[5], a12 = a[6], a13 = a[7];
      var a20 = a[8], a21 = a[9], a22 = a[10], a23 = a[11];
      var a30 = a[12], a31 = a[13], a32 = a[14], a33 = a[15];

      var b0 = b[0], b1 = b[1], b2 = b[2], b3 = b[3];
      out[0] = b0*a00 + b1*a10 + b2*a20 + b3*a30;
      out[1] = b0*a01 + b1*a11 + b2*a21 + b3*a31;
      out[2] = b0*a02 + b1*a12 + b2*a22 + b3*a32;
      out[3] = b0*a03 + b1*a13 + b2*a23 + b3*a33;

      b0 = b[4]; b1 = b[5]; b2 = b[6]; b3 = b[7];
      out[4] = b0*a00 + b1*a10 + b2*a20 + b3*a30;
      out[5] = b0*a01 + b1*a11 + b2*a21 + b3*a31;
      out[6] = b0*a02 + b1*a12 + b2*a22 + b3*a32;
      out[7] = b0*a03 + b1*a13 + b2*a23 + b3*a33;

      b0 = b[8]; b1 = b[9]; b2 = b[10]; b3 = b[11];
      out[8] = b0*a00 + b1*a10 + b2*a20 + b3*a30;
      out[9] = b0*a01 + b1*a11 + b2*a21 + b3*a31;
      out[10] = b0*a02 + b1*a12 + b2*a22 + b3*a32;
      out[11] = b0*a03 + b1*a13 + b2*a23 + b3*a33;

      b0 = b[12]; b1 = b[13]; b2 = b[14]; b3 = b[15];
      out[12] = b0*a00 + b1*a10 + b2*a20 + b3*a30;
      out[13] = b0*a01 + b1*a11 + b2*a21 + b3*a31;
      out[14] = b0*a02 + b1*a12 + b2*a22 + b3*a32;
      out[15] = b0*a03 + b1*a13 + b2*a23 + b3*a33;
      return out;
    }

    function mat4RotateX(out, a, rad) {
      var s = Math.sin(rad), c = Math.cos(rad);
      var a10 = a[4], a11 = a[5], a12 = a[6], a13 = a[7];
      var a20 = a[8], a21 = a[9], a22 = a[10], a23 = a[11];
      if (a !== out) {
        out[0] = a[0]; out[1] = a[1]; out[2] = a[2]; out[3] = a[3];
        out[12] = a[12]; out[13] = a[13]; out[14] = a[14]; out[15] = a[15];
      }
      out[4] = a10 * c + a20 * s;
      out[5] = a11 * c + a21 * s;
      out[6] = a12 * c + a22 * s;
      out[7] = a13 * c + a23 * s;
      out[8] = a20 * c - a10 * s;
      out[9] = a21 * c - a11 * s;
      out[10] = a22 * c - a12 * s;
      out[11] = a23 * c - a13 * s;
      return out;
    }

    function mat4RotateY(out, a, rad) {
      var s = Math.sin(rad), c = Math.cos(rad);
      var a00 = a[0], a01 = a[1], a02 = a[2], a03 = a[3];
      var a20 = a[8], a21 = a[9], a22 = a[10], a23 = a[11];
      if (a !== out) {
        out[4] = a[4]; out[5] = a[5]; out[6] = a[6]; out[7] = a[7];
        out[12] = a[12]; out[13] = a[13]; out[14] = a[14]; out[15] = a[15];
      }
      out[0] = a00 * c - a20 * s;
      out[1] = a01 * c - a21 * s;
      out[2] = a02 * c - a22 * s;
      out[3] = a03 * c - a23 * s;
      out[8] = a00 * s + a20 * c;
      out[9] = a01 * s + a21 * c;
      out[10] = a02 * s + a22 * c;
      out[11] = a03 * s + a23 * c;
      return out;
    }

    /* ------------------------------------------------------------------
       3D Geometry: Icosahedron Wireframe & Orbital Satellites
       ------------------------------------------------------------------ */
    var phi = (1.0 + Math.sqrt(5.0)) / 2.0;
    var rawIcoVerts = [
      -1,  phi,  0,    1,  phi,  0,   -1, -phi,  0,    1, -phi,  0,
       0, -1,  phi,    0,  1,  phi,    0, -1, -phi,    0,  1, -phi,
       phi,  0, -1,    phi,  0,  1,   -phi,  0, -1,   -phi,  0,  1
    ];
    var icoVerts = [];
    for (var i = 0; i < rawIcoVerts.length; i += 3) {
      var vx = rawIcoVerts[i], vy = rawIcoVerts[i+1], vz = rawIcoVerts[i+2];
      var len = Math.sqrt(vx*vx + vy*vy + vz*vz);
      icoVerts.push(vx / len, vy / len, vz / len);
    }

    var icoIndices = [
      0, 11, 5,   0, 5, 1,    0, 1, 7,    0, 7, 10,   0, 10, 11,
      1, 5, 9,    5, 11, 4,   11, 10, 2,  10, 7, 6,   7, 1, 8,
      3, 9, 4,    3, 4, 2,    3, 2, 6,    3, 6, 8,    3, 8, 9,
      4, 9, 5,    2, 4, 11,   6, 2, 10,   8, 6, 7,    9, 8, 1
    ];

    var edgeSet = {};
    var wireLineVerts = [];
    function addEdge(i1, i2, scale) {
      var key = i1 < i2 ? i1 + '_' + i2 : i2 + '_' + i1;
      if (edgeSet[key + '_' + scale]) return;
      edgeSet[key + '_' + scale] = true;
      wireLineVerts.push(
        icoVerts[i1*3] * scale, icoVerts[i1*3+1] * scale, icoVerts[i1*3+2] * scale,
        icoVerts[i2*3] * scale, icoVerts[i2*3+1] * scale, icoVerts[i2*3+2] * scale
      );
    }
    for (var t = 0; t < icoIndices.length; t += 3) {
      addEdge(icoIndices[t], icoIndices[t+1], 1.6);
      addEdge(icoIndices[t+1], icoIndices[t+2], 1.6);
      addEdge(icoIndices[t+2], icoIndices[t], 1.6);
      /* Inner dual core */
      addEdge(icoIndices[t], icoIndices[t+1], 0.95);
      addEdge(icoIndices[t+1], icoIndices[t+2], 0.95);
      addEdge(icoIndices[t+2], icoIndices[t], 0.95);
    }

    /* 3D Orbital Satellite Constellation — per-particle orbit parameters
       consumed by ORBIT_VERT below: positions are computed on the GPU
       each frame from u_time, so there is zero per-frame CPU work and
       the buffer never updates. Keplerian touch: outer satellites
       sweep slower than inner ones.
         a_orbit  = (phase0, radius, inclination, angular speed)
         a_wobble = (node phase, wobble amp, wobble freq, unused)     */
    var PARTICLE_COUNT = 90;
    var orbitData = new Float32Array(PARTICLE_COUNT * 8);
    for (var p = 0; p < PARTICLE_COUNT; p++) {
      var j = p * 8;
      var orbR = 1.9 + Math.random() * 2.4;
      orbitData[j]     = Math.random() * Math.PI * 2;                        /* phase0 */
      orbitData[j + 1] = orbR;                                               /* radius */
      orbitData[j + 2] = (Math.random() - 0.5) * 1.2;                        /* inclination */
      orbitData[j + 3] = (0.22 + Math.random() * 0.45) / orbR
                       * (Math.random() < 0.5 ? 1 : -1);                     /* omega */
      orbitData[j + 4] = Math.random() * Math.PI * 2;                        /* node */
      orbitData[j + 5] = 0.10 + Math.random() * 0.28;                        /* wobble amp */
      orbitData[j + 6] = 0.4 + Math.random() * 1.2;                          /* wobble freq */
      orbitData[j + 7] = 0;
    }

    /* ------------------------------------------------------------------
       Shaders
       ------------------------------------------------------------------ */
    /* Volumetric Aurora Background Quad Shader.
       withOrbs=true → the original desktop source, byte for byte: the
       40-orb loop lives in the fragment shader.
       withOrbs=false → phones: the nebula only; the orbs are drawn as
       full-resolution point sprites by ORB_VERT/ORB_FRAG below (same
       math, ~1/300th of the fragment cost). */
    var BG_VERT = [
      'attribute vec2 a_pos;',
      'void main(){ gl_Position = vec4(a_pos, 0.999, 1.0); }'
    ].join('\n');

    /* withFluid=true → the nebula samples the fluid velocity field to
        warp its fbm domain (the aurora bends around the wake — this is
        the visible interaction) and uses the advected dye purely as a
        disturbance mask that lifts the light already in the scene. No
        new color is ever added; the wake stays inside the palette.
        withFluid=false → byte-identical to the pre-fluid source. */
    function bgFragSrc(withOrbs, withFluid) {
      var src = [
        '#ifdef GL_FRAGMENT_PRECISION_HIGH',
        'precision highp float;',
        '#else',
        'precision mediump float;',
        '#endif',
        'uniform vec2  u_res;',
        'uniform float u_time;',
        'uniform vec3  u_colA;',
        'uniform vec3  u_colB;',
        ''
      ];
      if (withFluid) {
        src.push(
          '/* FLUID — velocity field (domain warp) + dye field (wake) */',
          'uniform sampler2D u_vel;',
          'uniform sampler2D u_dye;',
          'uniform vec2  u_velTexel;',
          'uniform vec2  u_dyeTexel;',
          'uniform float u_warp;',
          'uniform float u_warpCap;',
          'uniform float u_dyeGain;',
          ''
        );
      }
      src.push(
        '/* Robust multiply/fract hashes stable across mobile and desktop GPUs */',
        'float hash(vec2 p){',
        '  p = fract(p * vec2(234.34, 435.345));',
        '  p += dot(p, p + 34.23);',
        '  return fract(p.x * p.y);',
        '}',
        'float hash1(float n){',
        '  float p = fract(n * 0.1031);',
        '  p *= p + 33.33;',
        '  p *= p + p;',
        '  return fract(p);',
        '}',
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
        '/* Soft glowing bokeh particle */',
        'float orb(vec2 uv, vec2 c, float r){',
        '  float d = length(uv - c);',
        '  return smoothstep(r, r * 0.25, d);',
        '}',
        '',
        'void main(){',
        '  vec2 uv = (gl_FragCoord.xy - 0.5 * u_res) / min(u_res.x, u_res.y);',
        '  float t = u_time * 0.05;',
        ''
      );
      if (withFluid) {
        src.push(
          '  /* FLUID WARP — the nebula domain bends around the wake. The',
          '     minus sign makes the pattern trail the pointer, the way',
          '     water follows a moving hand. The field is read through a',
          '     4-tap box (half-float LINEAR quality is not trusted across',
          '     drivers) and the displacement is soft-capped: it grows',
          '     linearly for a gentle stir and saturates toward u_warpCap,',
          '     so the hardest flick bends the sky by a few percent of the',
          '     screen instead of smearing the fbm across it — every',
          '     octave keeps its detail and the velocity texel seams stay',
          '     below visibility. */',
          '  vec2 fuv = gl_FragCoord.xy / u_res;',
          '  vec2 vo = u_velTexel * 0.6;',
          '  vec2 fvel = texture2D(u_vel, fuv).xy;',
          '  fvel += texture2D(u_vel, fuv + vec2( vo.x,  vo.y)).xy;',
          '  fvel += texture2D(u_vel, fuv + vec2(-vo.x,  vo.y)).xy;',
          '  fvel += texture2D(u_vel, fuv + vec2( vo.x, -vo.y)).xy;',
          '  fvel = clamp(fvel * 0.25, vec2(-4000.0), vec2(4000.0));',
          '  vec2 disp = fvel * u_warp;',
          '  disp *= u_warpCap / (u_warpCap + length(disp));',
          '  uv -= disp;',
          ''
        );
      }
      src.push(
        '  /* domain-warped celestial nebula */',
        '  vec2 q = vec2(fbm(uv * 1.6 + t), fbm(uv * 1.6 - t * 0.7));',
        '  float n = fbm(uv * 2.2 + q * 1.4);',
        '  vec3 base = u_colA * smoothstep(0.35, 0.9, n) * 0.55;',
        '  base += u_colB * smoothstep(0.55, 0.95, fbm(uv * 2.8 - t + q)) * 0.45;',
        '  base *= (1.0 - smoothstep(0.15, 1.1, length(uv)));      /* vignette */',
        ''
      );
      if (withOrbs) {
        src.push(
          '  /* drifting bokeh field — 40 layered twinkling celestial orbs */',
          '  float glow = 0.0;',
          '  for (int i = 0; i < 40; i++){',
          '    float fi = float(i);',
          '    float seed = fi * 17.23;',
          '    vec2 c = vec2(',
          '      hash1(seed) * 2.4 - 1.2 + sin(t * (0.3 + hash1(seed + 1.7) * 0.4)) * 0.10,',
          '    mod(hash1(seed + 3.1) + t * (0.02 + hash1(seed + 5.9) * 0.03) * 2.0, 2.4) - 1.2',
          '  );',
          '    float r = 0.012 + hash1(seed + 9.1) * 0.05;',
          '    float tw = 0.55 + 0.45 * sin(t * (1.5 + hash1(seed + 11.7) * 2.0) + seed);',
          '    glow += orb(uv, c, r) * tw;',
          '  }',
          '  vec3 sparks = mix(u_colA, u_colB, 0.5 + 0.5 * sin(t)) * glow * 0.5;',
          '',
          '  vec3 col = base + sparks;'
        );
      } else {
        src.push(
          '  /* bokeh orbs are drawn as a separate full-resolution additive */',
          '  /* point pass — see ORB_VERT / ORB_FRAG below.               */',
          '  vec3 col = base;'
        );
      }
      if (withFluid) {
        src.push(
          '  /* FLUID WAKE — the dye field is a map of how disturbed the',
          '     water is. It never adds new color: it lifts the light of',
          '     the nebula already sitting there, the way stirred water',
          '     catches whatever glow is passing through it. The same',
          '     4-tap box keeps any driver from printing its texel grid',
          '     into the glow. */',
          '  vec2 dn = u_dyeTexel * 0.6;',
          '  vec3 dye = texture2D(u_dye, fuv).rgb;',
          '  dye += texture2D(u_dye, fuv + vec2( dn.x,  dn.y)).rgb;',
          '  dye += texture2D(u_dye, fuv + vec2(-dn.x,  dn.y)).rgb;',
          '  dye += texture2D(u_dye, fuv + vec2( dn.x, -dn.y)).rgb;',
          '  float wake = dot(dye, vec3(0.0833));',
          '  col *= 1.0 + wake * u_dyeGain;'
        );
      }
      src.push(
        '  col = col / (1.0 + col);                          /* soft tonemap */',
        '  gl_FragColor = vec4(col, clamp(dot(col, vec3(1.0)), 0.0, 1.0));',
        '}'
      );
      return src.join('\n');
    }

    /* Bokeh orb point sprites (phone tier). The vertex stage maps the
       orb's uv-space center to device pixels (same uv convention as the
       bg shader: centered, normalized by min(res.x, res.y)); the
       fragment reproduces orb()'s smoothstep(r, r*0.25, d) falloff at
       FULL resolution, so the sparkles match desktop 1:1.
       Blend (ONE, ONE_MINUS_SRC_COLOR) then accumulates each orb into
       the tonemapped nebula with dst + s·(1-dst) — the tonemap
       algebra's exact single-orb form, so overlaps still sum
       gracefully, matching the in-shader glow loop. */
    var ORB_VERT = [
      'attribute vec2 a_pos;',      /* uv-space center        */
      'attribute vec2 a_data;',     /* x: radius (uv), y: twinkle */
      'uniform vec2 u_res;',
      'uniform float u_minDim;',
      'varying float v_tw;',
      'varying float v_rpx;',
      'void main(){',
      '  float rpx = a_data.x * u_minDim;',
      '  v_rpx = rpx;',
      '  v_tw = a_data.y;',
      '  vec2 px = vec2(0.5 * u_res.x + a_pos.x * u_minDim,',
      '                 0.5 * u_res.y + a_pos.y * u_minDim);',
      '  gl_Position = vec4(px / u_res * 2.0 - 1.0, 0.0, 1.0);',
      '  gl_PointSize = max(2.0, rpx * 2.0);',
      '}'
    ].join('\n');

    var ORB_FRAG = [
      'precision mediump float;',
      'uniform vec3 u_spark;',      /* mix(colA,colB,t) * 0.5, per frame */
      'varying float v_tw;',
      'varying float v_rpx;',
      'void main(){',
      '  float d = length(gl_PointCoord - vec2(0.5)) * (v_rpx * 2.0);',
      '  float fall = smoothstep(v_rpx, v_rpx * 0.25, d);',
      '  vec3 s = u_spark * (v_tw * fall);',
      '  gl_FragColor = vec4(s, s.r + s.g + s.b);',   /* alpha follows the rgb sum, like the shader */
      '}'
    ].join('\n');

    /* 3D Object Shaders */
    var OBJ_3D_VERT = [
      'attribute vec3 a_pos;',
      'uniform mat4 u_mvp;',
      'uniform float u_pointSize;',
      'varying float v_depth;',
      'void main(){',
      '  vec4 pos = u_mvp * vec4(a_pos, 1.0);',
      '  gl_Position = pos;',
      '  v_depth = (pos.z / pos.w) * 0.5 + 0.5;',
      '  gl_PointSize = u_pointSize * clamp(1.6 / (pos.z * 0.2 + 1.2), 0.6, 3.2);',
      '}'
    ].join('\n');

    var OBJ_3D_FRAG = [
      'precision mediump float;',
      'uniform vec3 u_color;',
      'uniform float u_alpha;',
      'uniform float u_dimFloor;',   /* depth-dim floor: lite raises it so the cage's far side stays legible */
      'uniform int u_isPoint;',
      'varying float v_depth;',
      'void main(){',
      '  float a = u_alpha;',
      '  if (u_isPoint == 1){',
      '    vec2 coord = gl_PointCoord - vec2(0.5);',
      '    float d = length(coord);',
      '    if (d > 0.5) discard;',
      '    a *= (1.0 - smoothstep(0.08, 0.5, d));',
      '  }',
      '  float depthDim = mix(1.0, u_dimFloor, clamp(v_depth, 0.0, 1.0));',
      '  gl_FragColor = vec4(u_color * depthDim, a * depthDim);',
      '}'
    ].join('\n');

    /* Orbital satellite vertex shader — per-particle orbit parameters
       (static buffer) + u_time produce the position on the GPU:
       a circle in the XZ plane, inclined around X, precessed around Y
       by a per-orbit node phase, with a gentle vertical wobble. Zero
       per-frame CPU work, zero buffer uploads. Depth/point-size math
       mirrors OBJ_3D_VERT exactly. */
    var ORBIT_VERT = [
      'attribute vec4 a_orbit;',    /* phase0, radius, inclination, omega  */
      'attribute vec4 a_wobble;',   /* node, wobbleAmp, wobbleFreq, unused  */
      'uniform mat4 u_mvp;',
      'uniform float u_pointSize;',
      'uniform float u_time;',
      'varying float v_depth;',
      'void main(){',
      '  float th = a_orbit.x + u_time * a_orbit.w;',
      '  vec3 p = vec3(cos(th), 0.0, sin(th)) * a_orbit.y;',
      '  float ci = cos(a_orbit.z), si = sin(a_orbit.z);',
      '  p = vec3(p.x, p.y * ci - p.z * si, p.y * si + p.z * ci);',
      '  float cn = cos(a_wobble.x), sn = sin(a_wobble.x);',
      '  p = vec3(p.x * cn + p.z * sn, p.y, -p.x * sn + p.z * cn);',
      '  p.y += sin(u_time * a_wobble.z + a_orbit.x * 7.0) * a_wobble.y;',
      '  vec4 pos = u_mvp * vec4(p, 1.0);',
      '  gl_Position = pos;',
      '  v_depth = (pos.z / pos.w) * 0.5 + 0.5;',
      '  gl_PointSize = u_pointSize * clamp(1.6 / (pos.z * 0.2 + 1.2), 0.6, 3.2);',
      '}'
    ].join('\n');

    /* ------------------------------------------------------------------
       FLUID AURORA — stable-fluids simulation shaders (WebGL1)
       ------------------------------------------------------------------
       Classic ping-pong Navier-Stokes passes at tiny resolutions (see
       the runtime pipeline in finish() for the step order). SIM_VERT
       drives every pass from the shared fullscreen triangle and hands
       each fragment its texel-space neighbors for the finite-difference
       stencils. Velocity passes run at velocity-grid resolution, dye
       at dye resolution; both grids are aspect-shaped to the canvas. */
    var SIM_VERT = [
      'attribute vec2 a_pos;',
      'uniform vec2 u_texel;',
      'varying vec2 vUv;',
      'varying vec2 vL;',
      'varying vec2 vR;',
      'varying vec2 vT;',
      'varying vec2 vB;',
      'void main(){',
      '  vUv = a_pos * 0.5 + 0.5;',
      '  vL = vUv - vec2(u_texel.x, 0.0);',
      '  vR = vUv + vec2(u_texel.x, 0.0);',
      '  vT = vUv + vec2(0.0, u_texel.y);',
      '  vB = vUv - vec2(0.0, u_texel.y);',
      '  gl_Position = vec4(a_pos, 0.0, 1.0);',
      '}'
    ].join('\n');

    /* highp where available — velocity magnitudes and texel-precision
       coordinates need more than mediump's ~10-bit mantissa */
    var SIM_PRECISION = [
      '#ifdef GL_FRAGMENT_PRECISION_HIGH',
      'precision highp float;',
      '#else',
      'precision mediump float;',
      '#endif'
    ].join('\n');

    var SPLAT_FRAG = [
      SIM_PRECISION,
      'varying vec2 vUv;',
      'uniform sampler2D u_target;',
      'uniform float u_aspect;',
      'uniform vec2 u_point;',
      'uniform vec3 u_value;',
      'uniform float u_radius;',
      'void main(){',
      '  vec2 p = vUv - u_point;',
      '  p.x *= u_aspect;',
      '  vec3 splat = exp(-dot(p, p) / u_radius) * u_value;',
      '  vec3 base = texture2D(u_target, vUv).xyz;',
      '  gl_FragColor = vec4(base + splat, 1.0);',
      '}'
    ].join('\n');

    /* Semi-Lagrangian advection: trace each cell back along the
       velocity field and sample there. u_decay fades the field so the
       water calms once the hand has passed. */
    var ADVECT_FRAG = [
      SIM_PRECISION,
      'varying vec2 vUv;',
      'uniform sampler2D u_velocity;',
      'uniform sampler2D u_src;',
      'uniform vec2 u_texel;',   /* velocity grid texel */
      'uniform float u_dt;',
      'uniform float u_decay;',
      'void main(){',
      '  vec2 coord = vUv - u_dt * texture2D(u_velocity, vUv).xy * u_texel;',
      '  gl_FragColor = texture2D(u_src, coord) * u_decay;',
      '}'
    ].join('\n');

    var CLEAR_FRAG = [
      'precision mediump float;',
      'varying vec2 vUv;',
      'uniform sampler2D u_texture;',
      'uniform float u_value;',
      'void main(){ gl_FragColor = u_value * texture2D(u_texture, vUv); }'
    ].join('\n');

    var DIV_FRAG = [
      'precision mediump float;',
      'varying vec2 vUv;',
      'varying vec2 vL;',
      'varying vec2 vR;',
      'varying vec2 vT;',
      'varying vec2 vB;',
      'uniform sampler2D u_velocity;',
      'void main(){',
      '  float L = texture2D(u_velocity, vL).x;',
      '  float R = texture2D(u_velocity, vR).x;',
      '  float T = texture2D(u_velocity, vT).y;',
      '  float B = texture2D(u_velocity, vB).y;',
      '  vec2 C = texture2D(u_velocity, vUv).xy;',
      '  if (vL.x < 0.0) { L = -C.x; }',
      '  if (vR.x > 1.0) { R = -C.x; }',
      '  if (vT.y > 1.0) { T = -C.y; }',
      '  if (vB.y < 0.0) { B = -C.y; }',
      '  gl_FragColor = vec4(0.5 * (R - L + T - B), 0.0, 0.0, 1.0);',
      '}'
    ].join('\n');

    var CURL_FRAG = [
      'precision mediump float;',
      'varying vec2 vUv;',
      'varying vec2 vL;',
      'varying vec2 vR;',
      'varying vec2 vT;',
      'varying vec2 vB;',
      'uniform sampler2D u_velocity;',
      'void main(){',
      '  float L = texture2D(u_velocity, vL).y;',
      '  float R = texture2D(u_velocity, vR).y;',
      '  float T = texture2D(u_velocity, vT).x;',
      '  float B = texture2D(u_velocity, vB).x;',
      '  gl_FragColor = vec4(0.5 * (R - L - T + B), 0.0, 0.0, 1.0);',
      '}'
    ].join('\n');

    var VORT_FRAG = [
      SIM_PRECISION,
      'varying vec2 vUv;',
      'varying vec2 vL;',
      'varying vec2 vR;',
      'varying vec2 vT;',
      'varying vec2 vB;',
      'uniform sampler2D u_velocity;',
      'uniform sampler2D u_curl;',
      'uniform float u_curlStrength;',
      'uniform float u_dt;',
      'void main(){',
      '  float L = texture2D(u_curl, vL).x;',
      '  float R = texture2D(u_curl, vR).x;',
      '  float T = texture2D(u_curl, vT).x;',
      '  float B = texture2D(u_curl, vB).x;',
      '  float C = texture2D(u_curl, vUv).x;',
      '  vec2 force = 0.5 * vec2(abs(T) - abs(B), abs(R) - abs(L));',
      '  force /= length(force) + 0.0001;',
      '  force *= u_curlStrength * C;',
      '  force.y *= -1.0;',
      '  vec2 velocity = texture2D(u_velocity, vUv).xy + force * u_dt;',
      '  gl_FragColor = vec4(clamp(velocity, -1000.0, 1000.0), 0.0, 1.0);',
      '}'
    ].join('\n');

    var PRESSURE_FRAG = [
      'precision mediump float;',
      'varying vec2 vUv;',
      'varying vec2 vL;',
      'varying vec2 vR;',
      'varying vec2 vT;',
      'varying vec2 vB;',
      'uniform sampler2D u_pressure;',
      'uniform sampler2D u_divergence;',
      'void main(){',
      '  float L = texture2D(u_pressure, vL).x;',
      '  float R = texture2D(u_pressure, vR).x;',
      '  float T = texture2D(u_pressure, vT).x;',
      '  float B = texture2D(u_pressure, vB).x;',
      '  float divergence = texture2D(u_divergence, vUv).x;',
      '  gl_FragColor = vec4((L + R + B + T - divergence) * 0.25, 0.0, 0.0, 1.0);',
      '}'
    ].join('\n');

    var GRADIENT_FRAG = [
      'precision mediump float;',
      'varying vec2 vUv;',
      'varying vec2 vL;',
      'varying vec2 vR;',
      'varying vec2 vT;',
      'varying vec2 vB;',
      'uniform sampler2D u_pressure;',
      'uniform sampler2D u_velocity;',
      'void main(){',
      '  float L = texture2D(u_pressure, vL).x;',
      '  float R = texture2D(u_pressure, vR).x;',
      '  float T = texture2D(u_pressure, vT).x;',
      '  float B = texture2D(u_pressure, vB).x;',
      '  vec2 velocity = texture2D(u_velocity, vUv).xy;',
      '  velocity -= 0.5 * vec2(R - L, T - B);',
      '  gl_FragColor = vec4(velocity, 0.0, 1.0);',
      '}'
    ].join('\n');

    /* Blit shader — composites the low-res nebula texture onto the
       canvas (phone tier only). Reuses the fullscreen-triangle buffer. */
    var BLIT_VERT = [
      'attribute vec2 a_pos;',
      'varying vec2 v_uv;',
      'void main(){',
      '  v_uv = a_pos * 0.5 + 0.5;',
      '  gl_Position = vec4(a_pos, 0.0, 1.0);',
      '}'
    ].join('\n');

    var BLIT_FRAG = [
      'precision mediump float;',
      'varying vec2 v_uv;',
      'uniform sampler2D u_tex;',
      'void main(){ gl_FragColor = texture2D(u_tex, v_uv); }'
    ].join('\n');

    function compile(type, src) {
      var sh = gl.createShader(type);
      gl.shaderSource(sh, src);
      gl.compileShader(sh);
      if (!gl.getShaderParameter(sh, gl.COMPILE_STATUS)) {
        console.warn(gl.getShaderInfoLog(sh));
        gl.deleteShader(sh);
        return null;
      }
      return sh;
    }

    function link(vs, fs) {
      var prog = gl.createProgram();
      gl.attachShader(prog, vs);
      gl.attachShader(prog, fs);
      gl.linkProgram(prog);
      if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
        console.warn(gl.getProgramInfoLog(prog));
        gl.deleteProgram(prog);
        return null;
      }
      return prog;
    }

    var blitProg = null, blitU = null;
    var orbProg = null, orbU = null;
    var orbitProg = null, orbitU = null;
    var orbsAsPoints = false;
    var bgProg = null, objProg = null;

    /* FLUID plumbing — all three half-float extensions must exist or
       the whole subsystem stays dark and the nebula keeps its calm
       drifting look (a zero texture backs the uniforms of a specialized
       shader, so a late runtime failure also degrades safely). */
    var halfType = 0;
    var fluidPossible = false;
    var fluidReady = false;
    if (!reduceMotion) {
      try {
        var extHalf = gl.getExtension('OES_texture_half_float');
        var extCB = gl.getExtension('EXT_color_buffer_half_float');
        var extLinear = gl.getExtension('OES_texture_half_float_linear');
        fluidPossible = !!(extHalf && extCB && extLinear);
        if (extHalf) halfType = extHalf.HALF_FLOAT_OES;
      } catch (e) {}
    }

    var splatProg = null, advectProg = null, clearProg = null;
    var divProg = null, curlProg = null, vortProg = null;
    var pressureProg = null, gradientProg = null;
    if (fluidPossible) {
      splatProg    = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, SPLAT_FRAG));
      advectProg   = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, ADVECT_FRAG));
      clearProg    = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, CLEAR_FRAG));
      divProg      = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, DIV_FRAG));
      curlProg     = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, CURL_FRAG));
      vortProg     = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, VORT_FRAG));
      pressureProg = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, PRESSURE_FRAG));
      gradientProg = link(compile(gl.VERTEX_SHADER, SIM_VERT), compile(gl.FRAGMENT_SHADER, GRADIENT_FRAG));
    }
    orbitProg = link(compile(gl.VERTEX_SHADER, ORBIT_VERT), compile(gl.FRAGMENT_SHADER, OBJ_3D_FRAG));

    /* KHR_parallel_shader_compile: where the driver supports it,
       compile/link run on background threads; boot is sequenced by
       polling COMPLETION_STATUS_KHR, so the main thread never stalls
       and the typing choreography stays smooth. Without it, the same
       steps run synchronously at defer time — the pre-optimization
       behavior, only cheaper (phones no longer compile the 40-orb
       shader). */
    var extParallel = null;
    try { extParallel = gl.getExtension('KHR_parallel_shader_compile'); } catch (e) {}

    function whenLinked(progs, next) {
      if (!extParallel) { next(); return; }
      var waited = 0;
      (function poll() {
        for (var i = 0; i < progs.length; i++) {
          if (!progs[i]) continue;
          if (!gl.getProgramParameter(progs[i], extParallel.COMPLETION_STATUS_KHR)) {
            if (waited > 3000) { next(); return; }   /* stalled driver → accept the blocking query */
            waited += 40;
            setTimeout(poll, 40);
            return;
          }
        }
        next();
      })();
    }

    function linkedOk(p) {
      return !!(p && gl.getProgramParameter(p, gl.LINK_STATUS));
    }

    /* Stage 1 — every TINY program first (point sprites, composite,
       fluid passes, orbits), so the big nebula program can be
       specialized before it compiles. */
    if (lite) {
      blitProg = link(compile(gl.VERTEX_SHADER, BLIT_VERT), compile(gl.FRAGMENT_SHADER, BLIT_FRAG));
      orbProg = link(compile(gl.VERTEX_SHADER, ORB_VERT), compile(gl.FRAGMENT_SHADER, ORB_FRAG));
    }

    whenLinked([blitProg, orbProg, orbitProg,
                splatProg, advectProg, clearProg, divProg, curlProg,
                vortProg, pressureProg, gradientProg], function stage2() {
      if (!linkedOk(blitProg)) blitProg = null;
      if (!linkedOk(orbProg)) orbProg = null;
      if (!linkedOk(orbitProg)) orbitProg = null;
      orbsAsPoints = !!(lite && blitProg && orbProg);
      fluidReady = fluidPossible && linkedOk(splatProg) && linkedOk(advectProg) &&
                   linkedOk(clearProg) && linkedOk(divProg) && linkedOk(curlProg) &&
                   linkedOk(vortProg) && linkedOk(pressureProg) && linkedOk(gradientProg);

      /* Stage 2 — the specialized nebula (with/without orbs, with/without
         fluid coupling) plus the 3D program. */
      bgProg = link(compile(gl.VERTEX_SHADER, BG_VERT), compile(gl.FRAGMENT_SHADER, bgFragSrc(!orbsAsPoints, fluidReady)));
      objProg = link(compile(gl.VERTEX_SHADER, OBJ_3D_VERT), compile(gl.FRAGMENT_SHADER, OBJ_3D_FRAG));

      whenLinked([bgProg, objProg], function stage3() {
        if (!linkedOk(bgProg) || !linkedOk(objProg)) return;
        finish();
      });
    });

    /* Everything below needs LINKED programs: location caching,
       buffers, orb-sprite constants, the render loop. Runs as soon as
       the stages above resolve — synchronously without the extension,
       via the completion poll with it. */
    function finish() {

    /* Cached uniform/attribute handles — no per-frame lookups. */
    if (blitProg) blitU = {
      aPos: gl.getAttribLocation(blitProg, 'a_pos'),
      tex: gl.getUniformLocation(blitProg, 'u_tex')
    };
    if (orbProg) orbU = {
      aPos: gl.getAttribLocation(orbProg, 'a_pos'),
      aData: gl.getAttribLocation(orbProg, 'a_data'),
      res: gl.getUniformLocation(orbProg, 'u_res'),
      minDim: gl.getUniformLocation(orbProg, 'u_minDim'),
      spark: gl.getUniformLocation(orbProg, 'u_spark')
    };
    var bgU = {
      aPos:  gl.getAttribLocation(bgProg, 'a_pos'),
      res:   gl.getUniformLocation(bgProg, 'u_res'),
      time:  gl.getUniformLocation(bgProg, 'u_time'),
      colA:  gl.getUniformLocation(bgProg, 'u_colA'),
      colB:  gl.getUniformLocation(bgProg, 'u_colB')
    };
    var objU = {
      aPos:  gl.getAttribLocation(objProg, 'a_pos'),
      mvp:   gl.getUniformLocation(objProg, 'u_mvp'),
      color: gl.getUniformLocation(objProg, 'u_color'),
      alpha: gl.getUniformLocation(objProg, 'u_alpha'),
      dimFloor: gl.getUniformLocation(objProg, 'u_dimFloor'),
      pointSize: gl.getUniformLocation(objProg, 'u_pointSize'),
      isPoint:   gl.getUniformLocation(objProg, 'u_isPoint')
    };
    /* Fluid samplers exist only in the fluid-specialized nebula —
       getUniformLocation returns null otherwise, which makes every
       fluid binding below naturally null-safe. */
    bgU.velTex  = gl.getUniformLocation(bgProg, 'u_vel');
    bgU.dyeTex  = gl.getUniformLocation(bgProg, 'u_dye');
    bgU.velTexel = gl.getUniformLocation(bgProg, 'u_velTexel');
    bgU.dyeTexel = gl.getUniformLocation(bgProg, 'u_dyeTexel');
    bgU.warp    = gl.getUniformLocation(bgProg, 'u_warp');
    bgU.warpCap = gl.getUniformLocation(bgProg, 'u_warpCap');
    bgU.dyeGain = gl.getUniformLocation(bgProg, 'u_dyeGain');

    if (orbitProg) orbitU = {
      aOrbit:  gl.getAttribLocation(orbitProg, 'a_orbit'),
      aWobble: gl.getAttribLocation(orbitProg, 'a_wobble'),
      mvp:     gl.getUniformLocation(orbitProg, 'u_mvp'),
      color:   gl.getUniformLocation(orbitProg, 'u_color'),
      alpha:   gl.getUniformLocation(orbitProg, 'u_alpha'),
      dimFloor: gl.getUniformLocation(orbitProg, 'u_dimFloor'),
      pointSize: gl.getUniformLocation(orbitProg, 'u_pointSize'),
      time:    gl.getUniformLocation(orbitProg, 'u_time')
    };

    var fluidU = null;
    if (fluidReady) {
      fluidU = {
        splat: {
          aPos: gl.getAttribLocation(splatProg, 'a_pos'),
          target: gl.getUniformLocation(splatProg, 'u_target'),
          aspect: gl.getUniformLocation(splatProg, 'u_aspect'),
          point: gl.getUniformLocation(splatProg, 'u_point'),
          value: gl.getUniformLocation(splatProg, 'u_value'),
          radius: gl.getUniformLocation(splatProg, 'u_radius')
        },
        advect: {
          aPos: gl.getAttribLocation(advectProg, 'a_pos'),
          velocity: gl.getUniformLocation(advectProg, 'u_velocity'),
          src: gl.getUniformLocation(advectProg, 'u_src'),
          texel: gl.getUniformLocation(advectProg, 'u_texel'),
          dt: gl.getUniformLocation(advectProg, 'u_dt'),
          decay: gl.getUniformLocation(advectProg, 'u_decay')
        },
        clear: {
          aPos: gl.getAttribLocation(clearProg, 'a_pos'),
          texture: gl.getUniformLocation(clearProg, 'u_texture'),
          value: gl.getUniformLocation(clearProg, 'u_value')
        },
        div: {
          aPos: gl.getAttribLocation(divProg, 'a_pos'),
          velocity: gl.getUniformLocation(divProg, 'u_velocity'),
          texel: gl.getUniformLocation(divProg, 'u_texel')
        },
        curl: {
          aPos: gl.getAttribLocation(curlProg, 'a_pos'),
          velocity: gl.getUniformLocation(curlProg, 'u_velocity'),
          texel: gl.getUniformLocation(curlProg, 'u_texel')
        },
        vort: {
          aPos: gl.getAttribLocation(vortProg, 'a_pos'),
          velocity: gl.getUniformLocation(vortProg, 'u_velocity'),
          curl: gl.getUniformLocation(vortProg, 'u_curl'),
          texel: gl.getUniformLocation(vortProg, 'u_texel'),
          curlStrength: gl.getUniformLocation(vortProg, 'u_curlStrength'),
          dt: gl.getUniformLocation(vortProg, 'u_dt')
        },
        pressure: {
          aPos: gl.getAttribLocation(pressureProg, 'a_pos'),
          pressure: gl.getUniformLocation(pressureProg, 'u_pressure'),
          divergence: gl.getUniformLocation(pressureProg, 'u_divergence'),
          texel: gl.getUniformLocation(pressureProg, 'u_texel')
        },
        gradient: {
          aPos: gl.getAttribLocation(gradientProg, 'a_pos'),
          pressure: gl.getUniformLocation(gradientProg, 'u_pressure'),
          velocity: gl.getUniformLocation(gradientProg, 'u_velocity'),
          texel: gl.getUniformLocation(gradientProg, 'u_texel')
        }
      };
    }

    /* Tier visibility tuning. On the phone tier the polyhedron renders
       into a narrower frame against a brighter-composited nebula, and
       the desktop alphas read as noise there — users reported the
       cage as "indistinguishable" on phones. Lite runs hotter: higher
       line/node alphas (the additive SRC_ALPHA,ONE blend turns the
       extra alpha into a natural glow, no bloom pass needed), a
       brighter wire color computed per frame from the theme accent,
       larger vertex nodes, and a shallower depth-dim floor so the far
       side of the cage keeps its detail. Desktop values are the
       originals — wide screens keep the delicate look. */
    var wireAlpha = lite ? 0.50 : 0.28;
    var nodeAlpha = lite ? 0.95 : 0.75;
    var nodeSize  = lite ? 7.0 : 5.0;
    var partAlpha = lite ? 0.60 : 0.45;
    var dimFloor  = lite ? 0.55 : 0.35;
    var wireCol   = [0, 0, 0];   /* scratch: lite wire color, refilled per frame */

    var bgQuadBuf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, bgQuadBuf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);

    var wireBuf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, wireBuf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(wireLineVerts), gl.STATIC_DRAW);

    var vertPoints = [];
    for (var vp = 0; vp < icoVerts.length; vp += 3) {
      vertPoints.push(icoVerts[vp] * 1.6, icoVerts[vp+1] * 1.6, icoVerts[vp+2] * 1.6);
      vertPoints.push(icoVerts[vp] * 0.95, icoVerts[vp+1] * 0.95, icoVerts[vp+2] * 0.95);
    }
    var nodeBuf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, nodeBuf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertPoints), gl.STATIC_DRAW);

    /* Static orbit-parameter buffer — the GPU computes positions from
       u_time every frame; nothing is ever re-uploaded. */
    var orbitBuf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, orbitBuf);
    gl.bufferData(gl.ARRAY_BUFFER, orbitData, gl.STATIC_DRAW);

    /* ------------------------------------------------------------------
       Bokeh orb sprites (phone tier). Every constant the shader derived
       from hash1(seed) is precomputed once; per frame only the cheap
       time terms update. Position math mirrors the GLSL exactly.
       ------------------------------------------------------------------ */
    var ORB_COUNT = 40;
    var orbData = null, orbBuf = null, orbConsts = null;
    var sparkColor = new Float32Array(3);
    var maxPointPx = 255;
    if (orbsAsPoints) {
      try {
        var prange = gl.getParameter(gl.ALIASED_POINT_SIZE_RANGE);
        if (prange && prange.length > 1) maxPointPx = prange[1];
      } catch (e) {}

      function fract(v) { return v - Math.floor(v); }
      function hash1js(n) {
        var p = fract(n * 0.1031);
        p *= p + 33.33;
        p *= p + p;
        return fract(p);
      }

      orbConsts = [];
      for (var oi = 0; oi < ORB_COUNT; oi++) {
        var seed = oi * 17.23;
        orbConsts.push({
          seed:   seed,
          x0:     hash1js(seed) * 2.4 - 1.2,
          swayF:  0.3 + hash1js(seed + 1.7) * 0.4,
          y0:     hash1js(seed + 3.1),
          riseF:  0.02 + hash1js(seed + 5.9) * 0.03,
          radius: 0.012 + hash1js(seed + 9.1) * 0.05,
          twF:    1.5 + hash1js(seed + 11.7) * 2.0
        });
      }

      orbData = new Float32Array(ORB_COUNT * 4);
      orbBuf = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, orbBuf);
      gl.bufferData(gl.ARRAY_BUFFER, orbData, gl.DYNAMIC_DRAW);
    }

    function updateOrbData(time) {
      var tt = time * 0.05;
      var mixv = 0.5 + 0.5 * Math.sin(tt);
      for (var c = 0; c < 3; c++) {
        sparkColor[c] = (colA[c] + (colB[c] - colA[c]) * mixv) * 0.5;
      }
      var maxRuv = maxPointPx / (2.0 * Math.min(canvas.width, canvas.height));
      for (var k = 0; k < ORB_COUNT; k++) {
        var o = orbConsts[k];
        var j = k * 4;
        orbData[j]     = o.x0 + Math.sin(tt * o.swayF) * 0.10;
        orbData[j + 1] = (((o.y0 + tt * o.riseF * 2.0) % 2.4) + 2.4) % 2.4 - 1.2;
        orbData[j + 2] = Math.min(o.radius, maxRuv);
        orbData[j + 3] = 0.55 + 0.45 * Math.sin(tt * o.twF + o.seed);
      }
    }

    /* ------------------------------------------------------------------
       Offscreen nebula target (phone tier). Reallocated on resize; if
       the framebuffer is incomplete the renderer falls back to the
       direct full-res path. Realloc work is skipped when the size is
       unchanged (two integer compares per frame).
       ------------------------------------------------------------------ */
    var BG_SCALE = 0.5;
    var bgFbo = null, bgTex = null, bgW = 0, bgH = 0;

    function ensureBgTarget(w, h) {
      var tw = Math.max(2, Math.floor(w * BG_SCALE));
      var th = Math.max(2, Math.floor(h * BG_SCALE));
      if (tw === bgW && th === bgH) return;
      bgW = tw; bgH = th;
      if (!bgTex) {
        bgTex = gl.createTexture();
        gl.bindTexture(gl.TEXTURE_2D, bgTex);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
      } else {
        gl.bindTexture(gl.TEXTURE_2D, bgTex);
      }
      gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, tw, th, 0, gl.RGBA, gl.UNSIGNED_BYTE, null);
      if (!bgFbo) bgFbo = gl.createFramebuffer();
      gl.bindFramebuffer(gl.FRAMEBUFFER, bgFbo);
      gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, bgTex, 0);
      if (gl.checkFramebufferStatus(gl.FRAMEBUFFER) !== gl.FRAMEBUFFER_COMPLETE) {
        bgFbo = null;   /* unsupported → direct path */
      }
      gl.bindFramebuffer(gl.FRAMEBUFFER, null);
    }

    /* ------------------------------------------------------------------
       Theme & Colors
       ------------------------------------------------------------------ */
    var colA = [0.21, 0.91, 0.63]; /* mint #36e8a0 */
    var colB = [0.29, 0.66, 1.00]; /* cyan #4aa8ff */

    function hexToRGB(hex) {
      var m = hex.match(/^#([0-9a-f]{6})$/i);
      if (!m) return null;
      var n = parseInt(m[1], 16);
      return [(n >> 16 & 255) / 255, (n >> 8 & 255) / 255, (n & 255) / 255];
    }

    function readTheme() {
      var cs = getComputedStyle(document.documentElement);
      var a = hexToRGB(cs.getPropertyValue('--color-accent').trim());
      var b = hexToRGB(cs.getPropertyValue('--color-accent-2').trim());
      if (a) colA = a;
      if (b) colB = b;
    }
    readTheme();
    new MutationObserver(readTheme).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    /* ------------------------------------------------------------------
       FLUID AURORA — runtime pipeline ("hand through tinted water")
       ------------------------------------------------------------------
        Fields (aspect-shaped half-float ping-pong pairs):
          vel      192 / 96   (desktop / phone)   velocity xy
          dye      512 / 192                      wake mask rgb
          pressure 192 / 96                      Jacobi solve scratch
        Step (fixed dt — 60 Hz desktop, 30 Hz phone, accumulated from
        real frame time so any refresh rate behaves the same):
          splats → curl → vorticity confinement → divergence →
          pressure (Jacobi ×N) → gradient subtract → advect velocity →
          advect dye. When no splat has arrived for ~2.5 s only the two
        advection/decay passes run — the water finishes calming at
        near-zero cost — and after ~5.5 s of stillness the sim sleeps
        outright (no draws at all) until the next splat wakes it.
        Targets are built lazily on the first sized
        frame and rebuilt on orientation change.
       ------------------------------------------------------------------ */
    var SIM_DT = lite ? 0.033 : 0.016;
    var PRESSURE_ITERS = lite ? 12 : 20;
    var SPLAT_FORCE = 6000;
    var SPLAT_RADIUS = 0.005;
    var VEL_DECAY = lite ? 0.982 : 0.987;
    var DYE_DECAY = lite ? 0.985 : 0.988;
    var CURL_STRENGTH = 24;
    /* The interaction the visitor sees is the DOMAIN WARP — stirring
       drags and bends the nebula structures that already exist on
       screen. FLUID_WARP is the small-signal gain (sim velocity → uv
       displacement); the nebula shader soft-saturates the displacement
       toward FLUID_WARP_CAP, so a gentle stir bends the sky a little
       and the hardest flick never smears it past ~4.5% of the screen —
       the fbm keeps every octave of detail and the low-res velocity
       grid stays invisible. The dye field only lifts the existing
       light a touch in the wake (never a new hue), so the water stays
       inside the page's palette no matter how it is stirred. */
    var FLUID_WARP = 0.0015;
    var FLUID_WARP_CAP = 0.09;
    var FLUID_DYE_GAIN = 0.35;
    var splats = [];
    var splatBudget = 16;
    var simAccum = 0;
    var simSteps = 0;
    var lastSplatT = -10;
    var simAsleep = false;

    var vel = null, dye = null, pressure = null;
    var curlT = null, divT = null;
    var fluidOK = false, fluidBuildFailed = false;
    var fluidAspect = 0;
    var zeroTex = null;

    /* Cached hero rect. getBoundingClientRect per pointer/touch event
       (touchmove fires at input rate during every scroll) is a forced
       layout read — measurable jank on phones. The cache is dirtied by
       scroll/resize, the only things that move or resize the canvas,
       and refreshed lazily by the next splat. */
    var heroRect = null, heroRectDirty = true;
    window.addEventListener('scroll', function () { heroRectDirty = true; }, { passive: true });
    window.addEventListener('resize', function () { heroRectDirty = true; }, { passive: true });
    function heroRectNow() {
      if (heroRectDirty || !heroRect) {
        heroRect = canvas.getBoundingClientRect();
        heroRectDirty = false;
      }
      return heroRect;
    }

    /* Map a pointer/finger delta into the fluid queue. Touch splats
       come from the window-level touchmove twin in the interaction
       section (page-wide stirring — scrolling drags the water); the
       cursor stirs anywhere over the hero band. Far-off inputs are
       skipped: a splat deep below the band could never reach the
       visible field. */
    function queuePointerSplat(x, y, px, py) {
      if (!fluidOK || !heroVisible) return;
      var rect = heroRectNow();
      var u = (x - rect.left) / Math.max(1, rect.width);
      var v = 1 - (y - rect.top) / Math.max(1, rect.height);
      if (u < -0.08 || u > 1.08 || v < -0.06 || v > 1.06) return;
      var dx = (x - px) / Math.max(1, rect.width);
      var dy = -(y - py) / Math.max(1, rect.height);
      if (Math.abs(dx) + Math.abs(dy) < 0.0004) return;
      if (splats.length >= splatBudget) return;
      splats.push({ u: u, v: v, vx: dx * SPLAT_FORCE, vy: dy * SPLAT_FORCE, drop: false });
      simAsleep = false;
    }

    /* A tap injects a small dye drop (with a random nudge) — a fingertip
       touching the water. */
    function queueDrop(x, y) {
      if (!fluidOK || !heroVisible) return;
      var rect = heroRectNow();
      var u = (x - rect.left) / Math.max(1, rect.width);
      var v = 1 - (y - rect.top) / Math.max(1, rect.height);
      if (u < -0.08 || u > 1.08 || v < -0.06 || v > 1.06) return;
      if (splats.length >= splatBudget) return;
      var ang = Math.random() * Math.PI * 2;
      splats.push({ u: u, v: v, vx: Math.cos(ang) * 60, vy: Math.sin(ang) * 60, drop: true });
      simAsleep = false;
    }

    function makeTarget(w, h) {
      var tex = gl.createTexture();
      gl.activeTexture(gl.TEXTURE0);
      gl.bindTexture(gl.TEXTURE_2D, tex);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
      gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, w, h, 0, gl.RGBA, halfType, null);
      var fbo = gl.createFramebuffer();
      gl.bindFramebuffer(gl.FRAMEBUFFER, fbo);
      gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, tex, 0);
      var ok = gl.checkFramebufferStatus(gl.FRAMEBUFFER) === gl.FRAMEBUFFER_COMPLETE;
      gl.bindFramebuffer(gl.FRAMEBUFFER, null);
      return { tex: tex, fbo: fbo, w: w, h: h, ok: ok };
    }
    function makePair(w, h) {
      var a = makeTarget(w, h);
      var b = makeTarget(w, h);
      return {
        read: a, write: b, ok: a.ok && b.ok,
        swap: function () { var t = this.read; this.read = this.write; this.write = t; }
      };
    }
    function destroyFluidTargets() {
      function kill(t) { if (!t) return; gl.deleteTexture(t.tex); gl.deleteFramebuffer(t.fbo); }
      if (vel) { kill(vel.read); kill(vel.write); vel = null; }
      if (dye) { kill(dye.read); kill(dye.write); dye = null; }
      if (pressure) { kill(pressure.read); kill(pressure.write); pressure = null; }
      kill(curlT); kill(divT); curlT = divT = null;
    }
    function buildFluidTargets() {
      try {
        destroyFluidTargets();
        var aspect = canvas.width / Math.max(1, canvas.height);
        /* Desktop grids were 128/256 — the wake then read as a grainy,
           mottled halftone: dye texels upscaled ~4x against the smooth
           fbm, velocity-driven warp bending in ~8px patches. 192/512
           puts the dye at ~2 canvas pixels and the warp at ~5 — the
           wake blends into the image. Phones keep 96/192: their
           canvas is small and the nebula renders at 60% anyway. */
        var vRes = lite ? 96 : 192, dRes = lite ? 192 : 512;
        var vw = aspect >= 1 ? Math.round(vRes * aspect) : vRes;
        var vh = aspect >= 1 ? vRes : Math.round(vRes / aspect);
        var dw = aspect >= 1 ? Math.round(dRes * aspect) : dRes;
        var dh = aspect >= 1 ? dRes : Math.round(dRes / aspect);
        vel = makePair(vw, vh);
        dye = makePair(dw, dh);
        pressure = makePair(vw, vh);
        var curlTgt = makeTarget(vw, vh);
        var divTgt = makeTarget(vw, vh);
        curlT = curlTgt;
        divT = divTgt;
        if (!(vel.ok && dye.ok && pressure.ok && curlTgt.ok && divTgt.ok)) {
          destroyFluidTargets();
          fluidBuildFailed = true;
          return;
        }
        fluidAspect = aspect;
        fluidOK = true;
      } catch (e) {
        destroyFluidTargets();
        fluidBuildFailed = true;
      }
    }

    /* Draw one sim pass into `target` with the current program. Every
       pass is a fullscreen triangle; the neighbor varyings come from
       SIM_VERT via u_texel (velocity-grid texel for stencil passes). */
    function simDraw(u, target) {
      gl.bindFramebuffer(gl.FRAMEBUFFER, target ? target.fbo : null);
      gl.viewport(0, 0, target ? target.w : canvas.width, target ? target.h : canvas.height);
      gl.bindBuffer(gl.ARRAY_BUFFER, bgQuadBuf);
      gl.enableVertexAttribArray(u.aPos);
      gl.vertexAttribPointer(u.aPos, 2, gl.FLOAT, false, 0, 0);
      gl.drawArrays(gl.TRIANGLES, 0, 3);
    }

    function stepFluid(time) {
      if (!fluidOK || !fluidU) return;
      gl.disable(gl.BLEND);
      simSteps++;
      var active = (time - lastSplatT) < 2.5;
      var vt = [1 / vel.read.w, 1 / vel.read.h];

      /* 0. drain the pointer splat queue into both fields */
      while (splats.length) {
        var s = splats.shift();
        lastSplatT = time;
        var mixv = Math.random();
        var dyeScale = s.drop ? 0.55 : 0.42;

        gl.useProgram(splatProg);
        gl.uniform1f(fluidU.splat.aspect, fluidAspect);
        gl.uniform1f(fluidU.splat.radius, SPLAT_RADIUS);
        gl.uniform2f(fluidU.splat.point, s.u, s.v);

        /* velocity splat */
        gl.uniform3f(fluidU.splat.value, s.vx, s.vy, 0);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
        gl.uniform1i(fluidU.splat.target, 0);
        simDraw(fluidU.splat, vel.write);
        vel.swap();

        /* dye splat — a random blend of the two theme accents, so the
           wake drifts between mint and cyan like the nebula itself */
        gl.uniform3f(fluidU.splat.value,
          (colA[0] + (colB[0] - colA[0]) * mixv) * dyeScale,
          (colA[1] + (colB[1] - colA[1]) * mixv) * dyeScale,
          (colA[2] + (colB[2] - colA[2]) * mixv) * dyeScale);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, dye.read.tex);
        gl.uniform1i(fluidU.splat.target, 0);
        simDraw(fluidU.splat, dye.write);
        dye.swap();
      }

      if (active) {
        /* 1. curl of the velocity field */
        gl.useProgram(curlProg);
        gl.uniform2f(fluidU.curl.texel, vt[0], vt[1]);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
        gl.uniform1i(fluidU.curl.velocity, 0);
        simDraw(fluidU.curl, curlT);

        /* 2. vorticity confinement — the swirls that read as water */
        gl.useProgram(vortProg);
        gl.uniform2f(fluidU.vort.texel, vt[0], vt[1]);
        gl.uniform1f(fluidU.vort.curlStrength, CURL_STRENGTH);
        gl.uniform1f(fluidU.vort.dt, SIM_DT);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
        gl.uniform1i(fluidU.vort.velocity, 0);
        gl.activeTexture(gl.TEXTURE1);
        gl.bindTexture(gl.TEXTURE_2D, curlT.tex);
        gl.uniform1i(fluidU.vort.curl, 1);
        simDraw(fluidU.vort, vel.write);
        vel.swap();

        /* 3. divergence */
        gl.useProgram(divProg);
        gl.uniform2f(fluidU.div.texel, vt[0], vt[1]);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
        gl.uniform1i(fluidU.div.velocity, 0);
        simDraw(fluidU.div, divT);

        /* 4. pressure warm-start decay */
        gl.useProgram(clearProg);
        gl.uniform1f(fluidU.clear.value, 0.8);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, pressure.read.tex);
        gl.uniform1i(fluidU.clear.texture, 0);
        simDraw(fluidU.clear, pressure.write);
        pressure.swap();

        /* 5. Jacobi pressure solve */
        gl.useProgram(pressureProg);
        gl.uniform2f(fluidU.pressure.texel, vt[0], vt[1]);
        gl.activeTexture(gl.TEXTURE1);
        gl.bindTexture(gl.TEXTURE_2D, divT.tex);
        gl.uniform1i(fluidU.pressure.divergence, 1);
        for (var i = 0; i < PRESSURE_ITERS; i++) {
          gl.activeTexture(gl.TEXTURE0);
          gl.bindTexture(gl.TEXTURE_2D, pressure.read.tex);
          gl.uniform1i(fluidU.pressure.pressure, 0);
          simDraw(fluidU.pressure, pressure.write);
          pressure.swap();
        }

        /* 6. gradient subtract → divergence-free velocity */
        gl.useProgram(gradientProg);
        gl.uniform2f(fluidU.gradient.texel, vt[0], vt[1]);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, pressure.read.tex);
        gl.uniform1i(fluidU.gradient.pressure, 0);
        gl.activeTexture(gl.TEXTURE1);
        gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
        gl.uniform1i(fluidU.gradient.velocity, 1);
        simDraw(fluidU.gradient, vel.write);
        vel.swap();
      }

      /* 7. velocity self-advection + decay (always — this is what
             calms the water once the hand has passed) */
      gl.useProgram(advectProg);
      gl.uniform2f(fluidU.advect.texel, vt[0], vt[1]);
      gl.uniform1f(fluidU.advect.dt, SIM_DT);
      gl.uniform1f(fluidU.advect.decay, VEL_DECAY);
      gl.activeTexture(gl.TEXTURE0);
      gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
      gl.uniform1i(fluidU.advect.velocity, 0);
      gl.activeTexture(gl.TEXTURE1);
      gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
      gl.uniform1i(fluidU.advect.src, 1);
      simDraw(fluidU.advect, vel.write);
      vel.swap();

      /* 8. dye advection + fade (always) */
      gl.uniform1f(fluidU.advect.dt, SIM_DT);
      gl.uniform1f(fluidU.advect.decay, DYE_DECAY);
      gl.activeTexture(gl.TEXTURE0);
      gl.bindTexture(gl.TEXTURE_2D, vel.read.tex);
      gl.uniform1i(fluidU.advect.velocity, 0);
      gl.activeTexture(gl.TEXTURE1);
      gl.bindTexture(gl.TEXTURE_2D, dye.read.tex);
      gl.uniform1i(fluidU.advect.src, 1);
      simDraw(fluidU.advect, dye.write);
      dye.swap();
    }

    /* 1×1 zero texture — backs the nebula's fluid samplers until the
       targets exist (or forever, if they never can): a zero field is
       no warp and no wake, exactly the pre-fluid look. */
    if (fluidReady) {
      zeroTex = gl.createTexture();
      gl.bindTexture(gl.TEXTURE_2D, zeroTex);
      gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, 1, 1, 0, gl.RGBA, gl.UNSIGNED_BYTE, new Uint8Array(4));
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    }

    /* ------------------------------------------------------------------
       Interaction — physics-based manipulation (desktop + touch)
       ------------------------------------------------------------------
       The canvas carries `touch-action: pan-y` (fx.css): vertical
       touch pans stay native scrolling; horizontal drags and pinches
       arrive as pointer events. Rotation is a PERSISTENT ORIENTATION
       MATRIX driven by incremental arcball rotations (pre-multiplied
       view-space spins) — no euler accumulation, no gimbal tumble, and
       it composes with any prior orientation. Angular velocity is
       tracked while dragging and becomes momentum on release; the slow
       auto-rotation blends back in as momentum decays. Tap = cage pulse
       + a dye drop in the water; double-tap eases the cage back to its
       resting pose and resets the zoom. pointercancel (the browser
       taking a scroll) ends input cleanly with zero momentum — the
        physics eases, nothing snaps. */
     var orient = mat4Create(), scratchA = mat4Create(), scratchB = mat4Create();
    var IDENT = mat4Create();
    var REST_ORIENT = mat4Create();
    mat4RotateX(scratchA, IDENT, 0.35);
    mat4RotateY(REST_ORIENT, scratchA, 0.45);
    orient.set(REST_ORIENT);

    var angVelX = 0, angVelY = 0;       /* momentum: rad/s around view X / Y */
    var dragVelX = 0, dragVelY = 0;     /* EMA of the same, tracked while dragging */
    var ROT_PER_PX = 0.006;            /* rad per pixel of drag */
    var MAX_SPIN = 4.0;                 /* rad/s clamp on release momentum */
    var AUTO_SPIN = 0.17;               /* rad/s resting rotation */
    var pulse = 0;                      /* tap pulse; decays exponentially */
    var resetting = false;              /* easing back to REST_ORIENT */
    var camZ = 0, camZTarget = 0;       /* smoothed camera distance */
    var lastPortrait = null;
    function baseCamZ(aspect) { return aspect < 1.0 ? (lite ? -6.2 : -7.0) : -5.6; }

    var isDragging = false, dragId = null;
    var isPinching = false;
    var pointers = {};
    var downT = 0, downX = 0, downY = 0, dragMoved = false;
    var lastMoveT = 0, lastMoveX = 0, lastMoveY = 0;
    var lastTapT = 0, lastTapX = 0, lastTapY = 0;
    var lastTouchX = null, lastTouchY = null;
    var hoverX = null, hoverY = null;
    var pinchBaseDist = 1, pinchBaseZ = 0;

    /* Incremental view-space rotation — pre-multiplying means the spin
       acts around the CURRENT screen axes: grab the front face, turn
       it. Small per-event increments make the composite exact enough. */
    function applySpin(rx, ry) {
      if (rx === 0 && ry === 0) return;
      mat4RotateY(scratchA, IDENT, ry);
      mat4RotateX(scratchB, scratchA, rx);
      mat4Multiply(orient, scratchB, orient);
    }

    /* Repeated small-angle multiplies drift the basis; Gram-Schmidt
       the three columns every frame so `orient` stays a rotation. */
    function orthonormalize(m) {
      var c0x = m[0], c0y = m[1], c0z = m[2];
      var c1x = m[4], c1y = m[5], c1z = m[6];
      var l = Math.sqrt(c0x * c0x + c0y * c0y + c0z * c0z) || 1;
      c0x /= l; c0y /= l; c0z /= l;
      var d = c0x * c1x + c0y * c1y + c0z * c1z;
      c1x -= d * c0x; c1y -= d * c0y; c1z -= d * c0z;
      l = Math.sqrt(c1x * c1x + c1y * c1y + c1z * c1z) || 1;
      c1x /= l; c1y /= l; c1z /= l;
      var c2x = c0y * c1z - c0z * c1y;
      var c2y = c0z * c1x - c0x * c1z;
      var c2z = c0x * c1y - c0y * c1x;
      m[0] = c0x; m[1] = c0y; m[2] = c0z;
      m[4] = c1x; m[5] = c1y; m[6] = c1z;
      m[8] = c2x; m[9] = c2y; m[10] = c2z;
    }

    /* Quaternion helpers for the double-tap reset. An element-wise
       matrix lerp toward the rest pose travels through degenerate,
       non-orthogonal space (and can stall against orthonormalization
       when the cage was flipped ~180°); slerp takes the shortest arc
       on the rotation group from ANY orientation. Only used during a
       reset — a couple dozen microseconds for ~1.5 s, once in a
       while. Quats are [x, y, z, w], matrices column-major. */
    function mat4ToQuat(m) {
      /* Column-major storage: m[col*4+row], so mij (row i, col j) lives
         at m[j*4+i]. The trace-method signs below are derived against
         that layout — they are easy to flip by accident and a flipped
         axis yields the conjugate pose, so don't "simplify" them. */
      var tr = m[0] + m[5] + m[10];
      var q = [0, 0, 0, 1];
      var s;
      if (tr > 0) {
        s = Math.sqrt(tr + 1) * 2;
        q[3] = 0.25 * s;
        q[0] = (m[6] - m[9]) / s;
        q[1] = (m[8] - m[2]) / s;
        q[2] = (m[4] - m[1]) / s;
      } else if (m[0] > m[5] && m[0] > m[10]) {
        s = Math.sqrt(1 + m[0] - m[5] - m[10]) * 2;
        q[3] = (m[6] - m[9]) / s;
        q[0] = 0.25 * s;
        q[1] = (m[4] + m[1]) / s;
        q[2] = (m[8] + m[2]) / s;
      } else if (m[5] > m[10]) {
        s = Math.sqrt(1 + m[5] - m[0] - m[10]) * 2;
        q[3] = (m[8] - m[2]) / s;
        q[0] = (m[4] + m[1]) / s;
        q[1] = 0.25 * s;
        q[2] = (m[9] + m[6]) / s;
      } else {
        s = Math.sqrt(1 + m[10] - m[0] - m[5]) * 2;
        q[3] = (m[4] - m[1]) / s;
        q[0] = (m[8] + m[2]) / s;
        q[1] = (m[9] + m[6]) / s;
        q[2] = 0.25 * s;
      }
      return q;
    }
    function quatSlerp(a, b, t) {
      var bx = b[0], by = b[1], bz = b[2], bw = b[3];
      var dot = a[0] * bx + a[1] * by + a[2] * bz + a[3] * bw;
      if (dot < 0) { bx = -bx; by = -by; bz = -bz; bw = -bw; dot = -dot; }
      if (dot > 0.9995) {
        var lx = a[0] + (bx - a[0]) * t, ly = a[1] + (by - a[1]) * t,
            lz = a[2] + (bz - a[2]) * t, lw = a[3] + (bw - a[3]) * t;
        var il = 1 / Math.sqrt(lx * lx + ly * ly + lz * lz + lw * lw);
        return [lx * il, ly * il, lz * il, lw * il];
      }
      var th = Math.acos(Math.min(1, dot));
      var sTh = Math.sin(th);
      var wa = Math.sin((1 - t) * th) / sTh;
      var wb = Math.sin(t * th) / sTh;
      return [a[0] * wa + bx * wb, a[1] * wa + by * wb, a[2] * wa + bz * wb, a[3] * wa + bw * wb];
    }
    function quatToMat4(m, q) {
      var x = q[0], y = q[1], z = q[2], w = q[3];
      var x2 = x + x, y2 = y + y, z2 = z + z;
      var xx = x * x2, xy = x * y2, xz = x * z2;
      var yy = y * y2, yz = y * z2, zz = z * z2;
      var wx = w * x2, wy = w * y2, wz = w * z2;
      m[0] = 1 - (yy + zz); m[1] = xy + wz;        m[2] = xz - wy;
      m[4] = xy - wz;        m[5] = 1 - (xx + zz); m[6] = yz + wx;
      m[8] = xz + wy;        m[9] = yz - wx;       m[10] = 1 - (xx + yy);
      m[3] = 0; m[7] = 0; m[11] = 0;
      m[12] = 0; m[13] = 0; m[14] = 0; m[15] = 1;
      return m;
    }
    var REST_QUAT = mat4ToQuat(REST_ORIENT);
    var resetQ = null, resetT = 0;

    function spinFromDrag(dx, dy, dtm) {
      applySpin(dy * ROT_PER_PX, dx * ROT_PER_PX);
      if (dtm > 0) {
        var ivx = (dy * ROT_PER_PX) / dtm;
        var ivy = (dx * ROT_PER_PX) / dtm;
        dragVelX += (ivx - dragVelX) * 0.4;
        dragVelY += (ivy - dragVelY) * 0.4;
      }
    }

    function onTap(x, y) {
      pulse = 1;
      queueDrop(x, y);
      var nowT = performance.now();
      if (nowT - lastTapT < 320 && Math.abs(x - lastTapX) + Math.abs(y - lastTapY) < 48) {
        /* double-tap: shortest-arc ease home (slerp — see the quat
           helpers above for why not a matrix lerp) */
        resetting = true;
        resetQ = mat4ToQuat(orient);
        resetT = 0;
        angVelX = 0; angVelY = 0;
        camZTarget = baseCamZ(canvas.width / Math.max(1, canvas.height));
        lastTapT = 0;
      } else {
        lastTapT = nowT; lastTapX = x; lastTapY = y;
      }
    }

    function countPointers() {
      var n = 0;
      for (var k in pointers) n++;
      return n;
    }
    function beginDrag(x, y, pid) {
      isDragging = true; dragId = pid;
      dragMoved = false;
      downT = performance.now(); downX = x; downY = y;
      angVelX = 0; angVelY = 0; dragVelX = 0; dragVelY = 0;
      lastMoveT = downT; lastMoveX = x; lastMoveY = y;
      resetting = false;
      canvas.style.cursor = 'grabbing';
    }
    function endDragGesture(withMomentum) {
      if (!isDragging) return;
      isDragging = false; dragId = null;
      canvas.style.cursor = 'grab';
      if (withMomentum) {
        angVelX = Math.max(-MAX_SPIN, Math.min(MAX_SPIN, dragVelX));
        angVelY = Math.max(-MAX_SPIN, Math.min(MAX_SPIN, dragVelY));
      } else {
        angVelX = 0; angVelY = 0;
      }
    }
    function beginPinch() {
      var ids = Object.keys(pointers);
      if (ids.length < 2) return;
      var a = pointers[ids[0]], b = pointers[ids[1]];
      isPinching = true;
      pinchBaseDist = Math.max(1, Math.sqrt((a.x - b.x) * (a.x - b.x) + (a.y - b.y) * (a.y - b.y)));
      pinchBaseZ = camZTarget;
    }
    function updatePinch() {
      var ids = Object.keys(pointers);
      if (ids.length < 2) return;
      var a = pointers[ids[0]], b = pointers[ids[1]];
      var d = Math.max(1, Math.sqrt((a.x - b.x) * (a.x - b.x) + (a.y - b.y) * (a.y - b.y)));
      /* fingers apart → closer camera; capped to the framing band */
      camZTarget = Math.max(-9.5, Math.min(-4.0, pinchBaseZ * pinchBaseDist / d));
    }

    if (typeof window.PointerEvent === 'function') {
      canvas.addEventListener('pointerdown', function (e) {
        pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
        var n = countPointers();
        if (n === 1) {
          beginDrag(e.clientX, e.clientY, e.pointerId);
        } else if (n === 2) {
          /* second finger → pinch; end the drag without flinging */
          endDragGesture(false);
          beginPinch();
        }
      });

      window.addEventListener('pointermove', function (e) {
        var prev = pointers[e.pointerId];
        if (prev) {
          /* an active pointer (drag/pinch) — deltas from its own trail */
          if (!coarse) queuePointerSplat(e.clientX, e.clientY, prev.x, prev.y);
          prev.x = e.clientX; prev.y = e.clientY;
        } else if (!coarse) {
          /* hovering cursor — the hand through the water. Tracked on
             its own trail so no button is needed to stir. */
          if (hoverX !== null) queuePointerSplat(e.clientX, e.clientY, hoverX, hoverY);
          hoverX = e.clientX; hoverY = e.clientY;
        }

        if (isDragging && e.pointerId === dragId) {
          var nowT = performance.now();
          var dtm = Math.max(0.004, (nowT - lastMoveT) / 1000);
          if (!dragMoved && Math.abs(e.clientX - downX) + Math.abs(e.clientY - downY) > 6) dragMoved = true;
          spinFromDrag(e.clientX - lastMoveX, e.clientY - lastMoveY, dtm);
          lastMoveT = nowT; lastMoveX = e.clientX; lastMoveY = e.clientY;
        } else if (isPinching) {
          updatePinch();
        }
      }, { passive: true });

      window.addEventListener('pointerup', function (e) {
        if (!(e.pointerId in pointers)) return;
        delete pointers[e.pointerId];
        if (isPinching && countPointers() < 2) isPinching = false;
        if (isDragging && e.pointerId === dragId) {
          var dur = performance.now() - downT;
          if (!dragMoved && dur < 260) {
            endDragGesture(false);
            onTap(e.clientX, e.clientY);
          } else {
            endDragGesture(true);
          }
        }
      });

      window.addEventListener('pointercancel', function (e) {
        if (!(e.pointerId in pointers)) return;
        delete pointers[e.pointerId];
        if (isPinching && countPointers() < 2) isPinching = false;
        if (isDragging && e.pointerId === dragId) {
          /* browser took the gesture (pan) — clean end, no momentum,
             physics eases; nothing snaps */
          endDragGesture(false);
        }
      });

      /* Touch twin for the fluid: touchmove keeps firing during
         native scrolls (pointer events get canceled instead), so this
         is what makes scrolling drag the water page-wide. */
      window.addEventListener('touchmove', function (e) {
        if (!coarse || !e.touches || !e.touches.length) return;
        var t = e.touches[0];
        if (lastTouchX !== null) queuePointerSplat(t.clientX, t.clientY, lastTouchX, lastTouchY);
        lastTouchX = t.clientX; lastTouchY = t.clientY;
      }, { passive: true });
      window.addEventListener('touchend', function () {
        lastTouchX = null; lastTouchY = null;
      }, { passive: true });
      window.addEventListener('touchcancel', function () {
        lastTouchX = null; lastTouchY = null;
      }, { passive: true });
    } else {
      /* Legacy fallback (no PointerEvent): mouse pair + touch triplet
         with the original scroll-bypass heuristic, driving the same
         physics core; two-finger touches pinch. */
      canvas.addEventListener('mousedown', function (e) {
        beginDrag(e.clientX, e.clientY, null);
      });
      window.addEventListener('mousemove', function (e) {
        if (isDragging) {
          var nowT = performance.now();
          var dtm = Math.max(0.004, (nowT - lastMoveT) / 1000);
          if (!dragMoved && Math.abs(e.clientX - downX) + Math.abs(e.clientY - downY) > 6) dragMoved = true;
          spinFromDrag(e.clientX - lastMoveX, e.clientY - lastMoveY, dtm);
          lastMoveT = nowT; lastMoveX = e.clientX; lastMoveY = e.clientY;
        }
      }, { passive: true });
      window.addEventListener('mouseup', function (e) {
        if (!isDragging) return;
        var dur = performance.now() - downT;
        if (!dragMoved && dur < 260) {
          endDragGesture(false);
          onTap(e.clientX, e.clientY);
        } else {
          endDragGesture(true);
        }
      });

      var touchScrolling = false;
      canvas.addEventListener('touchstart', function (e) {
        if (!e.touches) return;
        if (e.touches.length === 1 && !isDragging && !isPinching) {
          touchScrolling = false;
          beginDrag(e.touches[0].clientX, e.touches[0].clientY, null);
        } else if (e.touches.length === 2) {
          endDragGesture(false);
          pointers.t0 = { x: e.touches[0].clientX, y: e.touches[0].clientY };
          pointers.t1 = { x: e.touches[1].clientX, y: e.touches[1].clientY };
          beginPinch();
        }
      }, { passive: true });
      window.addEventListener('touchmove', function (e) {
        if (!e.touches) return;
        if (isPinching && e.touches.length === 2) {
          pointers.t0.x = e.touches[0].clientX; pointers.t0.y = e.touches[0].clientY;
          pointers.t1.x = e.touches[1].clientX; pointers.t1.y = e.touches[1].clientY;
          updatePinch();
          return;
        }
        if (lastTouchX !== null) queuePointerSplat(e.touches[0].clientX, e.touches[0].clientY, lastTouchX, lastTouchY);
        lastTouchX = e.touches[0].clientX; lastTouchY = e.touches[0].clientY;
        if (isDragging && e.touches.length === 1) {
          var cx = e.touches[0].clientX, cy = e.touches[0].clientY;
          var adx = Math.abs(cx - downX), ady = Math.abs(cy - downY);
          if (!touchScrolling && ady > adx * 1.3 && ady > 12) {
            touchScrolling = true;
            endDragGesture(false);
            return;
          }
          var nowT = performance.now();
          var dtm = Math.max(0.004, (nowT - lastMoveT) / 1000);
          if (!dragMoved && adx + ady > 6) dragMoved = true;
          spinFromDrag(cx - lastMoveX, cy - lastMoveY, dtm);
          lastMoveT = nowT; lastMoveX = cx; lastMoveY = cy;
        }
      }, { passive: true });
      window.addEventListener('touchend', function (e) {
        lastTouchX = null; lastTouchY = null;
        if (isPinching && (!e.touches || e.touches.length < 2)) {
          isPinching = false;
          delete pointers.t0; delete pointers.t1;
        }
        if (isDragging && (!e.touches || e.touches.length === 0)) {
          touchScrolling = false;
          var dur = performance.now() - downT;
          if (!dragMoved && dur < 260) {
            endDragGesture(false);
            onTap(lastMoveX, lastMoveY);
          } else {
            endDragGesture(true);
          }
        }
      }, { passive: true });
      window.addEventListener('touchcancel', function () {
        lastTouchX = null; lastTouchY = null;
        touchScrolling = false;
        if (isPinching) { isPinching = false; delete pointers.t0; delete pointers.t1; }
        endDragGesture(false);
      }, { passive: true });
    }

    /* Sizing & DPR. Phones cap at 1.5: the hero is a soft aurora plus
       additive glow lines, every full-canvas pass (composite, cage,
       sprites, and the browser's own CSS-mask compositing each frame)
       costs bandwidth per device pixel, and a 300+ PPI panel at 1.5
       still renders them Retina-crisp. Desktop keeps the cap of 2. */
    var dpr = Math.min(window.devicePixelRatio || 1, lite ? 1.5 : 2);
    function resize() {
      var w = Math.floor(canvas.clientWidth * dpr);
      var h = Math.floor(canvas.clientHeight * dpr);
      if (canvas.width !== w || canvas.height !== h) {
        canvas.width = w;
        canvas.height = h;
      }
    }

    var heroVisible = true;
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        heroVisible = entries[0].isIntersecting;
      }, { threshold: 0.05 }).observe(canvas);
    }

     /* ------------------------------------------------------------------
        Physics driver — momentum, auto-spin blend, pulse decay, reset
        easing, camera easing. Every rate is dt-normalized
        so 30 / 60 / 120 Hz displays all feel identical.
        ------------------------------------------------------------------ */
    function stepOrientation(dt) {
      /* momentum from released drags */
      if (!isDragging && (angVelX !== 0 || angVelY !== 0)) {
        applySpin(angVelX * dt, angVelY * dt);
        var damp = Math.exp(-1.9 * dt);
        angVelX *= damp; angVelY *= damp;
        if (angVelX * angVelX + angVelY * angVelY < 6e-7) { angVelX = 0; angVelY = 0; }
      }
      /* Resting rotation blends in as momentum fades — one clean,
         steady turn around the screen vertical. Nothing else may
         push the cage: no cursor-following torque, no ambient drift.
         It stays centered and predictable, and only a pointer drag
         changes its motion. Suppressed while resetting so the
         double-tap slerp converges monotonically and snaps home
         without fighting the spin. */
      if (!isDragging && !isPinching && !resetting) {
        var sp2 = angVelX * angVelX + angVelY * angVelY;
        applySpin(0, (AUTO_SPIN / (1 + sp2 * 14.0)) * dt);
      }
      /* double-tap reset: shortest-arc slerp home with a deterministic
         exponential ease; slerp output is orthonormal by construction,
         and the snap lands at a fixed duration from ANY start pose */
      if (resetting) {
        resetT += dt;
        var p = 1 - Math.exp(-4.0 * resetT);
        if (p > 0.995) {
          orient.set(REST_ORIENT);
          resetting = false;
          resetQ = null;
        } else {
          quatToMat4(orient, quatSlerp(resetQ, REST_QUAT, p));
        }
      } else {
        orthonormalize(orient);
      }
      pulse *= Math.exp(-4.5 * dt);
      /* camera: an aspect flip re-inits the target (orientation
         change), pinches scale it, easing follows at ~6/s */
      var portrait = canvas.width < canvas.height;
      if (portrait !== lastPortrait) {
        lastPortrait = portrait;
        camZTarget = baseCamZ(canvas.width / Math.max(1, canvas.height));
      }
      if (camZ === 0) camZ = camZTarget;
      camZ += (camZTarget - camZ) * (1 - Math.exp(-6 * dt));
      camMat[14] = camZ;
    }

    /* ------------------------------------------------------------------
       Render Loop
       ------------------------------------------------------------------ */
    var running = !reduceMotion;
    var rafId = null;
    var startTime = performance.now();
    var lastTime = 0;
    var frameIdx = 0;

    var projMat = mat4Create();
    var mvpMat = mat4Create();
    var worldMat = mat4Create();
    var camMat = mat4Create();   /* persistent identity — only Z changes */

    function render(now) {
      rafId = null;
      if (!heroVisible || document.hidden) { schedule(); return; }
      /* phone tier paces frames at ~16.5 ms — a 60 Hz screen renders
          every rAF when the frame fits (the old 24 ms gate capped every
          phone at 30 fps with a 15 fps nebula), weak devices fall back
          naturally behind the compositor, and 90–120 Hz panels still
          skip the in-between rAFs. Desktop runs at the full rAF
          cadence. The first frame (lastTime still 0) always passes:
          dtRaw 0.016 would otherwise trip the gate and starve the
          loop forever. */
      var dtRaw = lastTime ? (now - lastTime) / 1000 : 1;
      if (lite && dtRaw < 0.0165) { schedule(); return; }
      var dt = lastTime ? Math.min(0.05, Math.max(0.001, dtRaw)) : 0.016;
      lastTime = now;
      frameIdx++;

      var time = (now - startTime) / 1000;
      resize();
      if (canvas.width === 0 || canvas.height === 0) { schedule(); return; }

      stepOrientation(dt);

      /* fluid: lazy target build on the first sized frame, rebuild on
         aspect drift (orientation change), fixed-step sim accumulated
         from real frame time */
      if (fluidReady && !fluidOK && !fluidBuildFailed) buildFluidTargets();
      if (fluidOK) {
        var fasp = canvas.width / Math.max(1, canvas.height);
        if (Math.abs(fasp - fluidAspect) > Math.max(0.12, fluidAspect * 0.12)) buildFluidTargets();
        /* The sim sleeps once the water has been still for ~5.5 s: the
           decay passes have long faded both fields below visibility, so
           the sleeping textures read as zero warp and zero wake — the
           calm pre-fluid look, at zero cost. Any splat wakes it, and it
           never sleeps with splats still queued: the fresh queue keeps
           the accumulator building until the step that drains it
           refreshes lastSplatT. */
        if (!simAsleep) {
          simAccum += dt;
          var steps = 0;
          while (simAccum >= SIM_DT && steps < 2) { stepFluid(time); simAccum -= SIM_DT; steps++; }
          if (steps === 2) simAccum = 0;
          if (!splats.length && time - lastSplatT > 5.5) { simAsleep = true; simAccum = 0; }
        }
      }

      gl.disable(gl.BLEND);

      /* 1. Lush Aurora Nebula Background — fluid-coupled.
             Desktop: full-res direct render, orbs included in the shader.
             Phone: nebula-only into a low-res offscreen FBO, composited
             each frame with linear filtering; orbs are separate full-res
             sprites (step 1b). While the wake is alive the FBO refreshes
             every rendered frame — the water answers the finger at the
             full frame cadence, and at the halved scale that still costs
             less than the old every-2nd-frame refresh — and it drops
             back to every 2nd frame once calm, in step with the slow
             sky drift. No clears anywhere: every pass writes every
             pixel through a fullscreen triangle with blending off, so
             the old clear passes were pure bandwidth. */
      var useFbo = !!(lite && blitU && bgFbo);
      if (lite && blitU) ensureBgTarget(canvas.width, canvas.height);
      var fluidAwake = fluidOK && (time - lastSplatT) < 2.5;
      var drawNebula = !useFbo || fluidAwake || (frameIdx % 2) === 1;

      if (drawNebula) {
        gl.bindFramebuffer(gl.FRAMEBUFFER, useFbo ? bgFbo : null);
        gl.viewport(0, 0, useFbo ? bgW : canvas.width, useFbo ? bgH : canvas.height);
        gl.useProgram(bgProg);
        gl.uniform2f(bgU.res, useFbo ? bgW : canvas.width, useFbo ? bgH : canvas.height);
        gl.uniform1f(bgU.time, time);
        gl.uniform3fv(bgU.colA, colA);
        gl.uniform3fv(bgU.colB, colB);
        if (bgU.velTex) {
          /* zero texture until the targets exist (or forever if they
             can't) — a zero field is exactly the pre-fluid look */
          gl.activeTexture(gl.TEXTURE0);
          gl.bindTexture(gl.TEXTURE_2D, fluidOK ? vel.read.tex : zeroTex);
          gl.uniform1i(bgU.velTex, 0);
          gl.activeTexture(gl.TEXTURE1);
          gl.bindTexture(gl.TEXTURE_2D, fluidOK ? dye.read.tex : zeroTex);
          gl.uniform1i(bgU.dyeTex, 1);
          gl.uniform1f(bgU.warp, FLUID_WARP);
          gl.uniform1f(bgU.warpCap, FLUID_WARP_CAP);
          gl.uniform1f(bgU.dyeGain, FLUID_DYE_GAIN);
          /* texel sizes drive the 4-tap smoothing offsets; harmless
             unit values back the 1×1 zero texture before the fluid
             targets exist */
          gl.uniform2f(bgU.velTexel,
            fluidOK ? 1 / vel.read.w : 1, fluidOK ? 1 / vel.read.h : 1);
          gl.uniform2f(bgU.dyeTexel,
            fluidOK ? 1 / dye.read.w : 1, fluidOK ? 1 / dye.read.h : 1);
        }
        gl.enableVertexAttribArray(bgU.aPos);
        gl.bindBuffer(gl.ARRAY_BUFFER, bgQuadBuf);
        gl.vertexAttribPointer(bgU.aPos, 2, gl.FLOAT, false, 0, 0);
        gl.drawArrays(gl.TRIANGLES, 0, 3);
      }

      if (useFbo) {
        /* Composite the low-res nebula onto the canvas — the blit
           writes every pixel with blending off, so no clear is needed. */
        gl.bindFramebuffer(gl.FRAMEBUFFER, null);
        gl.viewport(0, 0, canvas.width, canvas.height);
        gl.useProgram(blitProg);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, bgTex);
        gl.uniform1i(blitU.tex, 0);
        gl.enableVertexAttribArray(blitU.aPos);
        gl.bindBuffer(gl.ARRAY_BUFFER, bgQuadBuf);
        gl.vertexAttribPointer(blitU.aPos, 2, gl.FLOAT, false, 0, 0);
        gl.drawArrays(gl.TRIANGLES, 0, 3);
      }

      /* 1b. Bokeh orb sprites (phone tier) — full resolution, every
              frame, blended tonemap-correct into the nebula. */
      if (orbsAsPoints) {
        updateOrbData(time);
        gl.enable(gl.BLEND);
        gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_COLOR);
        gl.useProgram(orbProg);
        gl.uniform2f(orbU.res, canvas.width, canvas.height);
        gl.uniform1f(orbU.minDim, Math.min(canvas.width, canvas.height));
        gl.uniform3fv(orbU.spark, sparkColor);
        gl.bindBuffer(gl.ARRAY_BUFFER, orbBuf);
        gl.bufferSubData(gl.ARRAY_BUFFER, 0, orbData);
        gl.enableVertexAttribArray(orbU.aPos);
        gl.enableVertexAttribArray(orbU.aData);
        gl.vertexAttribPointer(orbU.aPos, 2, gl.FLOAT, false, 16, 0);
        gl.vertexAttribPointer(orbU.aData, 2, gl.FLOAT, false, 16, 8);
        gl.drawArrays(gl.POINTS, 0, ORB_COUNT);
      }

      /* 2. Camera + persistent arcball orientation (pinch-eased Z).
             ORDER MATTERS — camMat * orient rotates the cage around its
             own center first, then the camera pushes it to depth, so
             it stays centered forever. orient * camMat instead rotates
             the already-translated eye space around the origin, sending
             the cage on a wide orbit: offscreen, behind the camera,
             then back in from the opposite side. (The pre-fluid build
             composed proj * camMat * rotX * rotY — same order as
             this.) Don't "simplify" this multiply. */
      var aspect = canvas.width / Math.max(1, canvas.height);
      mat4Perspective(projMat, Math.PI / 4, aspect, 0.1, 100.0);
      mat4Multiply(worldMat, camMat, orient);
      mat4Multiply(mvpMat, projMat, worldMat);

      /* Setup 3D Program */
      gl.enable(gl.BLEND);
      gl.useProgram(objProg);
      gl.blendFunc(gl.SRC_ALPHA, gl.ONE);

      gl.uniformMatrix4fv(objU.mvp, false, mvpMat);
      gl.uniform1f(objU.dimFloor, dimFloor);

      /* 3. 3D Wireframe Cage — the lite tier paints a hotter wire color
              (per-frame boost of the theme accent) so the cage reads
              against the nebula on small screens. A tap pulse breathes
              extra glow through the additive blend. */
      var wcol = colA;
      if (lite) {
        wcol = wireCol;
        wcol[0] = Math.min(1, colA[0] * 1.25 + 0.08);
        wcol[1] = Math.min(1, colA[1] * 1.12 + 0.05);
        wcol[2] = Math.min(1, colA[2] * 1.20 + 0.10);
      }
      gl.bindBuffer(gl.ARRAY_BUFFER, wireBuf);
      gl.vertexAttribPointer(objU.aPos, 3, gl.FLOAT, false, 0, 0);
      gl.uniform3fv(objU.color, wcol);
      gl.uniform1f(objU.alpha, Math.min(1, wireAlpha * (1 + 0.6 * pulse)));
      gl.uniform1i(objU.isPoint, 0);
      gl.drawArrays(gl.LINES, 0, wireLineVerts.length / 3);

      /* 4. 3D Vertices (Nodes) — the tap pulse swells them */
      gl.bindBuffer(gl.ARRAY_BUFFER, nodeBuf);
      gl.vertexAttribPointer(objU.aPos, 3, gl.FLOAT, false, 0, 0);
      gl.uniform3fv(objU.color, colB);
      gl.uniform1f(objU.alpha, Math.min(1, nodeAlpha * (1 + 0.3 * pulse)));
      gl.uniform1f(objU.pointSize, nodeSize * dpr * (1 + 0.55 * pulse));
      gl.uniform1i(objU.isPoint, 1);
      gl.drawArrays(gl.POINTS, 0, vertPoints.length / 3);

      /* 5. Orbital satellite halo — positions computed on the GPU from
              u_time; the buffer never updates */
      if (orbitProg) {
        gl.useProgram(orbitProg);
        gl.uniformMatrix4fv(orbitU.mvp, false, mvpMat);
        gl.uniform1f(orbitU.dimFloor, dimFloor);
        gl.uniform1f(orbitU.time, time);
        gl.uniform3fv(orbitU.color, colA);
        gl.uniform1f(orbitU.alpha, Math.min(1, partAlpha * (1 + 0.25 * pulse)));
        gl.uniform1f(orbitU.pointSize, 3.5 * dpr);
        gl.bindBuffer(gl.ARRAY_BUFFER, orbitBuf);
        gl.enableVertexAttribArray(orbitU.aOrbit);
        gl.enableVertexAttribArray(orbitU.aWobble);
        gl.vertexAttribPointer(orbitU.aOrbit, 4, gl.FLOAT, false, 32, 0);
        gl.vertexAttribPointer(orbitU.aWobble, 4, gl.FLOAT, false, 32, 16);
        gl.drawArrays(gl.POINTS, 0, PARTICLE_COUNT);
      }

      schedule();
    }

    function schedule() {
      if (running && rafId === null) rafId = requestAnimationFrame(render);
    }

    if (reduceMotion) {
      resize();
      render(startTime + 1000);
      running = false;
    } else {
      canvas.style.cursor = 'grab';
      schedule();
      document.addEventListener('visibilitychange', function () {
        if (document.hidden) return;
        /* Freeze the clock across the hidden gap so the nebula doesn't
           fast-forward (only once at least one frame has rendered). */
        if (lastTime > 0) startTime += performance.now() - lastTime;
        schedule();
      });
    }

    /* Test hook — non-enumerable, exposes live physics state so the
       runtime behavior can be asserted from the console or tests. */
    try {
      Object.defineProperty(window, '__heroDebug', {
        enumerable: false,
        configurable: true,
        get: function () {
          return {
            spin: Math.sqrt(angVelX * angVelX + angVelY * angVelY),
            dragging: isDragging,
            pinching: isPinching,
            camZ: camZ,
            pulse: pulse,
            resetting: resetting,
            resetP: resetting ? 1 - Math.exp(-4 * resetT) : 0,
            fluid: fluidOK,
            simAsleep: simAsleep,
            queuedSplats: splats.length,
            simSteps: simSteps,
            orient: [orient[0], orient[1], orient[2], orient[4], orient[5], orient[6], orient[8], orient[9], orient[10]]
          };
        }
      });
    } catch (e) {}
    }

    /* boot() returns here on the parallel-compile path — the poll
       timers above sequence finish() asynchronously once the driver
       reports the programs linked. */
  }

  /* Boot runs immediately at defer time — the aurora belongs to the
     first impression, so there is no late pop-in. Main-thread safety
     comes from the staging inside boot() (KHR_parallel_shader_compile
     above): where the driver cooperates, compilation happens on
     background threads and the main thread never stalls; where it
     doesn't, this is exactly the pre-optimization boot order — only
     lighter, because phone-tier compilation no longer includes the
     heavy 40-orb fragment loop. */
  boot();
})();
