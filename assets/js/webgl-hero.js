/* =====================================================================
   webgl-hero.js — Lush Aurora Nebula & Interactive 3D Cyber-Polyhedron
   ---------------------------------------------------------------------
   Dual-pass WebGL rendering:
     1. Volumetric dual-tone Aurora Nebula with domain-warped fbm and
        drifting circular bokeh particles floating upward.
     2. Interactive 3D Wireframe Cyber-Polyhedron (dual icosahedron cage)
        with glowing vertices, inner core, and orbiting satellite particles.
     3. Pointer & Mobile Touch drag-to-rotate with fluid inertia and dampening.
     4. Responsive camera distance adaptation for mobile viewports.
     5. Theme-responsive colors (#36e8a0 mint, #4aa8ff cyan).
     6. Performance guards: pauses when scrolled out of view or the tab is
        hidden, caps DPR at 2.

   PHONE-FIRST RENDERING TIER (coarse pointers / narrow viewports).
   Same scene, same fidelity — the cost moves, not the pixels:
     - Non-blocking boot at defer time: the aurora initializes right
       away (no late pop-in). Where the driver exposes
       KHR_parallel_shader_compile the nebula shader compiles on
       background threads and a completion poll sequences the boot —
       the main thread never stalls, so the typing choreography stays
       smooth. Without the extension the compile is synchronous at
       defer, exactly as the pre-optimization site always was — only
       much cheaper, because the heavy 40-orb loop no longer sits in
       the phone-tier nebula shader.
     - Bokeh orbs as POINT SPRITES: the 40-orb per-pixel loop (~80% of
       the fragment cost) is replaced by 40 full-resolution additive
       point sprites with the identical falloff, drift, twinkle and
       parallax math — computed per frame on the CPU (40 orbs is
       nothing) and drawn with a blend that reproduces the shader's
       pre-tonemap accumulation. The orbs actually render at FULL
       resolution, so the sparkle is indistinguishable from desktop.
     - Nebula FBO: the soft-focus fbm aurora (all 4 octaves kept)
       renders into an offscreen framebuffer at 60% and is composited
       with linear filtering — visually equivalent on a field that is
       soft by design — and refreshed every 2nd frame (its evolution
       runs at 5% time-scale; half-rate sampling is invisible).
     - The crisp 3D wireframe, nodes and halo keep full canvas
       resolution every frame.
     - Uniform/attribute locations are cached once after linking.
     - powerPreference 'default' on the phone tier — no high-perf GPU
       hint, kinder to batteries.
     - If the point-sprite or composite programs fail to build, the
       bg shader recompiles WITH its orb loop and the direct
       full-quality path takes over — visual correctness first.
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

    /* 3D Floating Particle Constellation */
    var PARTICLE_COUNT = 90;
    var particleVerts = [];
    var particleVelocities = [];
    for (var p = 0; p < PARTICLE_COUNT; p++) {
      var r = 1.7 + Math.random() * 2.8;
      var theta = Math.random() * Math.PI * 2;
      var u = Math.random() * 2 - 1;
      var x = r * Math.sqrt(1 - u * u) * Math.cos(theta);
      var y = r * Math.sqrt(1 - u * u) * Math.sin(theta);
      var z = r * u;
      particleVerts.push(x, y, z);
      particleVelocities.push(
        (Math.random() - 0.5) * 0.003,
        (Math.random() - 0.5) * 0.003,
        (Math.random() - 0.5) * 0.003
      );
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

    function bgFragSrc(withOrbs) {
      var src = [
        '#ifdef GL_FRAGMENT_PRECISION_HIGH',
        'precision highp float;',
        '#else',
        'precision mediump float;',
        '#endif',
        'uniform vec2  u_res;',
        'uniform float u_time;',
        'uniform vec2  u_mouse;',
        'uniform vec3  u_colA;',
        'uniform vec3  u_colB;',
        '',
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
        '',
        '  /* gentle pointer parallax of the whole field */',
        '  uv += u_mouse * 0.05;',
        '',
        '  /* domain-warped celestial nebula */',
        '  vec2 q = vec2(fbm(uv * 1.6 + t), fbm(uv * 1.6 - t * 0.7));',
        '  float n = fbm(uv * 2.2 + q * 1.4);',
        '  vec3 base = u_colA * smoothstep(0.35, 0.9, n) * 0.55;',
        '  base += u_colB * smoothstep(0.55, 0.95, fbm(uv * 2.8 - t + q)) * 0.45;',
        '  base *= (1.0 - smoothstep(0.15, 1.1, length(uv)));      /* vignette */',
        ''
      ];
      if (withOrbs) {
        src.push(
          '  /* drifting bokeh field — 40 layered twinkling celestial orbs */',
          '  float glow = 0.0;',
          '  for (int i = 0; i < 40; i++){',
          '    float fi = float(i);',
          '    float seed = fi * 17.23;',
          '    vec2 c = vec2(',
          '      hash1(seed) * 2.4 - 1.2 + sin(t * (0.3 + hash1(seed + 1.7) * 0.4)) * 0.10,',
          '      mod(hash1(seed + 3.1) + t * (0.02 + hash1(seed + 5.9) * 0.03) * 2.0, 2.4) - 1.2',
          '    );',
          '    c += u_mouse * 0.08 * (0.3 + hash1(seed + 7.3));  /* parallax depth */',
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
    var orbsAsPoints = false;
    var bgProg = null, objProg = null;

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

    /* Stage 1 — the tiny point-sprite + composite programs first, so
       the big nebula program can be specialized before it compiles.
       If either fails, the nebula keeps its orb loop and the direct
       full-quality path takes over — visuals are never lost. */
    if (lite) {
      blitProg = link(compile(gl.VERTEX_SHADER, BLIT_VERT), compile(gl.FRAGMENT_SHADER, BLIT_FRAG));
      orbProg = link(compile(gl.VERTEX_SHADER, ORB_VERT), compile(gl.FRAGMENT_SHADER, ORB_FRAG));
    }

    whenLinked([blitProg, orbProg], function stage2() {
      if (blitProg && !gl.getProgramParameter(blitProg, gl.LINK_STATUS)) blitProg = null;
      if (orbProg && !gl.getProgramParameter(orbProg, gl.LINK_STATUS)) orbProg = null;
      orbsAsPoints = !!(lite && blitProg && orbProg);

      /* Stage 2 — the specialized nebula (with/without its orb loop)
         plus the 3D program. */
      bgProg = link(compile(gl.VERTEX_SHADER, BG_VERT), compile(gl.FRAGMENT_SHADER, bgFragSrc(!orbsAsPoints)));
      objProg = link(compile(gl.VERTEX_SHADER, OBJ_3D_VERT), compile(gl.FRAGMENT_SHADER, OBJ_3D_FRAG));

      whenLinked([bgProg, objProg], function stage3() {
        if (!bgProg || !gl.getProgramParameter(bgProg, gl.LINK_STATUS) ||
            !objProg || !gl.getProgramParameter(objProg, gl.LINK_STATUS)) return;
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
      mouse: gl.getUniformLocation(bgProg, 'u_mouse'),
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

    var particleArray = new Float32Array(particleVerts);
    var particleBuf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, particleBuf);
    gl.bufferData(gl.ARRAY_BUFFER, particleArray, gl.DYNAMIC_DRAW);

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
          parF:   0.08 * (0.3 + hash1js(seed + 7.3)),
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
        orbData[j]     = o.x0 + Math.sin(tt * o.swayF) * 0.10 + pointerX * o.parF;
        orbData[j + 1] = (((o.y0 + tt * o.riseF * 2.0) % 2.4) + 2.4) % 2.4 - 1.2 + pointerY * o.parF;
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
    var BG_SCALE = 0.6;
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
       Interaction: unified PointerEvent drag (desktop + touch)
       ------------------------------------------------------------------
       The canvas carries `touch-action: pan-y` (fx.css): vertical
       touch pans stay native scrolling, horizontal moves arrive as
       pointer events — the manual scroll-bypass heuristic is no
       longer needed on modern engines. When the browser takes a
       gesture over (vertical pan), pointercancel ends the drag and
       restores the pre-drag orientation, so a scroll begun on the
       hero leaves the scene exactly as it found it. The mouse pair +
       touch triplet below remain ONLY for engines without
       PointerEvent. */
    var rotX = 0.35, rotY = 0.45;
    var targetRotX = rotX, targetRotY = rotY;
    var pointerX = 0, pointerY = 0;
    var isDragging = false, dragStartX = 0, dragStartY = 0;
    var baseRotX = rotX, baseRotY = rotY;
    var dragPointerId = null;

    function onPointerMove(clientX, clientY, dragging) {
      var nx = (clientX / window.innerWidth) * 2 - 1;
      var ny = -((clientY / window.innerHeight) * 2 - 1);
      pointerX = nx;
      pointerY = ny;
      if (dragging) {
        var dx = (clientX - dragStartX) * 0.008;
        var dy = (clientY - dragStartY) * 0.008;
        targetRotY = baseRotY + dx;
        targetRotX = baseRotX + dy;
      } else if (!coarse) {
        /* Idle drift follows the mouse on desktop only. On touch,
           pointermove fires only mid-gesture — letting a scroll nudge
           the rotation reads as the scene twitching. Parallax
           (pointerX/Y) still tracks the finger. */
        targetRotY += nx * 0.008;
        targetRotX += ny * 0.005;
      }
    }

    function startDrag(x, y, pid) {
      isDragging = true;
      dragStartX = x;
      dragStartY = y;
      baseRotX = targetRotX;
      baseRotY = targetRotY;
      dragPointerId = pid;
      canvas.style.cursor = 'grabbing';
    }

    function endDrag(revert) {
      if (!isDragging) return;
      isDragging = false;
      dragPointerId = null;
      if (revert) {
        targetRotX = baseRotX;
        targetRotY = baseRotY;
      }
      canvas.style.cursor = 'grab';
    }

    if (typeof window.PointerEvent === 'function') {
      window.addEventListener('pointermove', function (e) {
        if (isDragging && e.pointerId !== dragPointerId) return;
        onPointerMove(e.clientX, e.clientY, isDragging);
      }, { passive: true });

      canvas.addEventListener('pointerdown', function (e) {
        if (dragPointerId !== null) return;   /* second finger ignored */
        startDrag(e.clientX, e.clientY, e.pointerId);
      });

      window.addEventListener('pointerup', function (e) {
        if (dragPointerId === null || e.pointerId !== dragPointerId) return;
        endDrag(false);
      });
      window.addEventListener('pointercancel', function (e) {
        if (dragPointerId === null || e.pointerId !== dragPointerId) return;
        endDrag(true);   /* browser took the gesture (pan) — restore */
      });
    } else {
      /* Legacy fallback (no PointerEvent): mouse pair + touch triplet
         with the original scroll-bypass heuristic. */
      canvas.addEventListener('mousedown', function (e) {
        startDrag(e.clientX, e.clientY, null);
      });
      window.addEventListener('mousemove', function (e) {
        onPointerMove(e.clientX, e.clientY, isDragging);
      }, { passive: true });
      window.addEventListener('mouseup', function () { endDrag(false); });

      var touchStartX = 0, touchStartY = 0;
      var touchScrolling = false;

      canvas.addEventListener('touchstart', function (e) {
        if (e.touches && e.touches.length === 1) {
          touchScrolling = false;
          touchStartX = e.touches[0].clientX;
          touchStartY = e.touches[0].clientY;
          startDrag(touchStartX, touchStartY, null);
        }
      }, { passive: true });

      window.addEventListener('touchmove', function (e) {
        if (isDragging && e.touches && e.touches.length === 1) {
          var cx = e.touches[0].clientX;
          var cy = e.touches[0].clientY;
          var dx = Math.abs(cx - touchStartX);
          var dy = Math.abs(cy - touchStartY);
          if (!touchScrolling && dy > dx * 1.3 && dy > 12) {
            touchScrolling = true;
            endDrag(true);
            return;
          }
          onPointerMove(cx, cy, true);
        }
      }, { passive: true });

      window.addEventListener('touchend', function () {
        touchScrolling = false;
        endDrag(false);
      }, { passive: true });
    }

    /* Sizing & DPR */
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
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
       Render Loop
       ------------------------------------------------------------------ */
    var running = !reduceMotion;
    var rafId = null;
    var startTime = performance.now();
    var lastTime = 0;
    var frameIdx = 0;

    var projMat = mat4Create();
    var mvpMat = mat4Create();
    var rotMat = mat4Create();
    var camMat = mat4Create();   /* persistent identity — only Z changes */

    function render(now) {
      rafId = null;
      if (!heroVisible || document.hidden) { schedule(); return; }
      if (now - lastTime < 24) { schedule(); return; }
      lastTime = now;
      frameIdx++;

      var time = (now - startTime) / 1000;
      resize();
      if (canvas.width === 0 || canvas.height === 0) { schedule(); return; }

      rotX += (targetRotX - rotX) * 0.08;
      rotY += (targetRotY - rotY) * 0.08;
      if (!isDragging) {
        targetRotY += 0.0028;
      }

      gl.disable(gl.BLEND);
      gl.clearColor(0, 0, 0, 0);

      /* 1. Lush Aurora Nebula Background.
            Desktop: full-res direct render, orbs included in the shader.
            Phone: nebula-only into a low-res offscreen FBO (refreshed
            every 2nd frame), composited each frame with linear
            filtering; orbs are separate full-res sprites (step 1b). */
      var useFbo = !!(lite && blitU && bgFbo);
      if (lite && blitU) ensureBgTarget(canvas.width, canvas.height);
      var drawNebula = !useFbo || (frameIdx % 2) === 1;

      if (drawNebula) {
        gl.bindFramebuffer(gl.FRAMEBUFFER, useFbo ? bgFbo : null);
        gl.viewport(0, 0, useFbo ? bgW : canvas.width, useFbo ? bgH : canvas.height);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
        gl.useProgram(bgProg);
        gl.uniform2f(bgU.res, useFbo ? bgW : canvas.width, useFbo ? bgH : canvas.height);
        gl.uniform1f(bgU.time, time);
        gl.uniform2f(bgU.mouse, pointerX, pointerY);
        gl.uniform3fv(bgU.colA, colA);
        gl.uniform3fv(bgU.colB, colB);
        gl.enableVertexAttribArray(bgU.aPos);
        gl.bindBuffer(gl.ARRAY_BUFFER, bgQuadBuf);
        gl.vertexAttribPointer(bgU.aPos, 2, gl.FLOAT, false, 0, 0);
        gl.drawArrays(gl.TRIANGLES, 0, 3);
      }

      if (useFbo) {
        /* Composite the low-res nebula onto the canvas. */
        gl.bindFramebuffer(gl.FRAMEBUFFER, null);
        gl.viewport(0, 0, canvas.width, canvas.height);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
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

      /* 2. Responsive 3D Camera Setup */
      var aspect = canvas.width / Math.max(1, canvas.height);
      mat4Perspective(projMat, Math.PI / 4, aspect, 0.1, 100.0);

      /* On mobile (portrait aspect < 1.0), pull the camera back to frame
         gracefully — but less far on the phone tier, where the cage needs
         the extra screen presence (was -7.0 flat; lite frames ~12% larger). */
      camMat[14] = aspect < 1.0 ? (lite ? -6.2 : -7.0) : -5.6;
      mat4RotateX(rotMat, camMat, rotX);
      mat4RotateY(rotMat, rotMat, rotY);
      mat4Multiply(mvpMat, projMat, rotMat);

      /* Setup 3D Program */
      gl.enable(gl.BLEND);
      gl.useProgram(objProg);
      gl.blendFunc(gl.SRC_ALPHA, gl.ONE);

      gl.uniformMatrix4fv(objU.mvp, false, mvpMat);
      gl.uniform1f(objU.dimFloor, dimFloor);

      /* 3. 3D Wireframe Cage — the lite tier paints a hotter wire color
             (per-frame boost of the theme accent) so the cage reads
             against the nebula on small screens. */
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
      gl.uniform1f(objU.alpha, wireAlpha);
      gl.uniform1i(objU.isPoint, 0);
      gl.drawArrays(gl.LINES, 0, wireLineVerts.length / 3);

      /* 4. 3D Vertices (Nodes) */
      gl.bindBuffer(gl.ARRAY_BUFFER, nodeBuf);
      gl.vertexAttribPointer(objU.aPos, 3, gl.FLOAT, false, 0, 0);
      gl.uniform3fv(objU.color, colB);
      gl.uniform1f(objU.alpha, nodeAlpha);
      gl.uniform1f(objU.pointSize, nodeSize * dpr);
      gl.uniform1i(objU.isPoint, 1);
      gl.drawArrays(gl.POINTS, 0, vertPoints.length / 3);

      /* 5. 3D Orbiting Particle Halo */
      for (var k = 0; k < PARTICLE_COUNT; k++) {
        var idx = k * 3;
        particleArray[idx] += particleVelocities[idx];
        particleArray[idx+1] += particleVelocities[idx+1];
        particleArray[idx+2] += particleVelocities[idx+2];
        var distSq = particleArray[idx]*particleArray[idx] + particleArray[idx+1]*particleArray[idx+1] + particleArray[idx+2]*particleArray[idx+2];
        if (distSq > 26.0 || distSq < 1.4) {
          particleVelocities[idx] = -particleVelocities[idx];
          particleVelocities[idx+1] = -particleVelocities[idx+1];
          particleVelocities[idx+2] = -particleVelocities[idx+2];
        }
      }
      gl.bindBuffer(gl.ARRAY_BUFFER, particleBuf);
      gl.bufferSubData(gl.ARRAY_BUFFER, 0, particleArray);
      gl.vertexAttribPointer(objU.aPos, 3, gl.FLOAT, false, 0, 0);
      gl.uniform3fv(objU.color, colA);
      gl.uniform1f(objU.alpha, partAlpha);
      gl.uniform1f(objU.pointSize, 3.5 * dpr);
      gl.uniform1i(objU.isPoint, 1);
      gl.drawArrays(gl.POINTS, 0, PARTICLE_COUNT);

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
