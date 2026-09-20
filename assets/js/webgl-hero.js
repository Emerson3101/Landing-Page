/* =====================================================================
   webgl-hero.js — Lush Aurora Nebula & Interactive 3D Cyber-Polyhedron
   ---------------------------------------------------------------------
   Dual-pass WebGL rendering:
     1. Volumetric dual-tone Aurora Nebula with domain-warped fbm and
        12 drifting circular bokeh particles floating upward.
     2. Interactive 3D Wireframe Cyber-Polyhedron (dual icosahedron cage)
        with glowing vertices, inner core, and orbiting satellite particles.
     3. Pointer & Mobile Touch drag-to-rotate with fluid inertia and dampening.
     4. Responsive camera distance adaptation for mobile viewports.
     5. Theme-responsive colors (#36e8a0 mint, #4aa8ff cyan).
     6. Performance guards: pauses when scrolled out of view or tab is hidden,
        caps DPR at 2.
   ===================================================================== */

'use strict';

(function hero3D() {
  var canvas = document.getElementById('hero-gl');
  if (!canvas) return;

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var gl = canvas.getContext('webgl', { antialias: true, alpha: true, powerPreference: 'high-performance' })
        || canvas.getContext('experimental-webgl');
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
  /* Volumetric Aurora Background Quad Shader */
  var BG_VERT = [
    'attribute vec2 a_pos;',
    'void main(){ gl_Position = vec4(a_pos, 0.999, 1.0); }'
  ].join('\n');

  var BG_FRAG = [
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
    '',
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
    '  vec3 col = base + sparks;',
    '  col = col / (1.0 + col);                          /* soft tonemap */',
    '  gl_FragColor = vec4(col, clamp(dot(col, vec3(1.0)), 0.0, 1.0));',
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
    '  float depthDim = mix(1.0, 0.35, clamp(v_depth, 0.0, 1.0));',
    '  gl_FragColor = vec4(u_color * depthDim, a * depthDim);',
    '}'
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

  var bgProg = link(compile(gl.VERTEX_SHADER, BG_VERT), compile(gl.FRAGMENT_SHADER, BG_FRAG));
  var objProg = link(compile(gl.VERTEX_SHADER, OBJ_3D_VERT), compile(gl.FRAGMENT_SHADER, OBJ_3D_FRAG));
  if (!bgProg || !objProg) return;

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
     Interaction: Pointer & Touch Controls (Desktop & Mobile)
     ------------------------------------------------------------------ */
  var rotX = 0.35, rotY = 0.45;
  var targetRotX = rotX, targetRotY = rotY;
  var pointerX = 0, pointerY = 0;
  var isDragging = false, dragStartX = 0, dragStartY = 0;
  var baseRotX = rotX, baseRotY = rotY;

  function onPointerMove(clientX, clientY) {
    var nx = (clientX / window.innerWidth) * 2 - 1;
    var ny = -((clientY / window.innerHeight) * 2 - 1);
    pointerX = nx;
    pointerY = ny;
    if (!isDragging) {
      targetRotY += nx * 0.008;
      targetRotX += ny * 0.005;
    } else {
      var dx = (clientX - dragStartX) * 0.008;
      var dy = (clientY - dragStartY) * 0.008;
      targetRotY = baseRotY + dx;
      targetRotX = baseRotX + dy;
    }
  }

  window.addEventListener('pointermove', function (e) {
    onPointerMove(e.clientX, e.clientY);
  }, { passive: true });

  canvas.addEventListener('pointerdown', function (e) {
    isDragging = true;
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    baseRotX = targetRotX;
    baseRotY = targetRotY;
    canvas.style.cursor = 'grabbing';
  });

  window.addEventListener('pointerup', function () {
    if (isDragging) {
      isDragging = false;
      canvas.style.cursor = 'grab';
    }
  });

  /* Touch Support for Mobile Drag with intelligent scroll bypass */
  var touchStartX = 0, touchStartY = 0;
  var touchScrolling = false;

  canvas.addEventListener('touchstart', function (e) {
    if (e.touches && e.touches.length === 1) {
      isDragging = true;
      touchScrolling = false;
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
      dragStartX = touchStartX;
      dragStartY = touchStartY;
      baseRotX = targetRotX;
      baseRotY = targetRotY;
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
        isDragging = false;
        return;
      }
      onPointerMove(cx, cy);
    }
  }, { passive: true });

  window.addEventListener('touchend', function () {
    isDragging = false;
    touchScrolling = false;
  }, { passive: true });

  /* Sizing & DPR */
  var dpr = Math.min(window.devicePixelRatio || 1, 2);
  function resize() {
    var w = Math.floor(canvas.clientWidth * dpr);
    var h = Math.floor(canvas.clientHeight * dpr);
    if (canvas.width !== w || canvas.height !== h) {
      canvas.width = w;
      canvas.height = h;
      gl.viewport(0, 0, w, h);
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

  var projMat = mat4Create();
  var mvpMat = mat4Create();
  var rotMat = mat4Create();

  function render(now) {
    rafId = null;
    if (!heroVisible || document.hidden) { schedule(); return; }
    if (now - lastTime < 24) { schedule(); return; }
    lastTime = now;

    var time = (now - startTime) / 1000;
    resize();

    rotX += (targetRotX - rotX) * 0.08;
    rotY += (targetRotY - rotY) * 0.08;
    if (!isDragging) {
      targetRotY += 0.0028;
    }

    gl.disable(gl.BLEND);
    gl.clearColor(0, 0, 0, 0);
    gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

    /* 1. Lush Aurora Nebula Background */
    gl.useProgram(bgProg);
    gl.uniform2f(gl.getUniformLocation(bgProg, 'u_res'), canvas.width, canvas.height);
    gl.uniform1f(gl.getUniformLocation(bgProg, 'u_time'), time);
    gl.uniform2f(gl.getUniformLocation(bgProg, 'u_mouse'), pointerX, pointerY);
    gl.uniform3fv(gl.getUniformLocation(bgProg, 'u_colA'), colA);
    gl.uniform3fv(gl.getUniformLocation(bgProg, 'u_colB'), colB);

    var aBgPos = gl.getAttribLocation(bgProg, 'a_pos');
    gl.enableVertexAttribArray(aBgPos);
    gl.bindBuffer(gl.ARRAY_BUFFER, bgQuadBuf);
    gl.vertexAttribPointer(aBgPos, 2, gl.FLOAT, false, 0, 0);
    gl.drawArrays(gl.TRIANGLES, 0, 3);

    /* 2. Responsive 3D Camera Setup */
    var aspect = canvas.width / Math.max(1, canvas.height);
    mat4Perspective(projMat, Math.PI / 4, aspect, 0.1, 100.0);

    var camMat = mat4Create();
    /* On mobile (portrait aspect < 1.0), move camera back slightly to frame gracefully */
    camMat[14] = aspect < 1.0 ? -7.0 : -5.6;
    mat4RotateX(rotMat, camMat, rotX);
    mat4RotateY(rotMat, rotMat, rotY);
    mat4Multiply(mvpMat, projMat, rotMat);

    /* Setup 3D Program */
    gl.enable(gl.BLEND);
    gl.useProgram(objProg);
    gl.blendFunc(gl.SRC_ALPHA, gl.ONE);
    var uMvp = gl.getUniformLocation(objProg, 'u_mvp');
    var uColor = gl.getUniformLocation(objProg, 'u_color');
    var uAlpha = gl.getUniformLocation(objProg, 'u_alpha');
    var uPtSize = gl.getUniformLocation(objProg, 'u_pointSize');
    var uIsPt = gl.getUniformLocation(objProg, 'u_isPoint');
    var aPos = gl.getAttribLocation(objProg, 'a_pos');
    gl.enableVertexAttribArray(aPos);

    gl.uniformMatrix4fv(uMvp, false, mvpMat);

    /* 3. 3D Wireframe Cage */
    gl.bindBuffer(gl.ARRAY_BUFFER, wireBuf);
    gl.vertexAttribPointer(aPos, 3, gl.FLOAT, false, 0, 0);
    gl.uniform3fv(uColor, colA);
    gl.uniform1f(uAlpha, 0.28);
    gl.uniform1i(uIsPt, 0);
    gl.drawArrays(gl.LINES, 0, wireLineVerts.length / 3);

    /* 4. 3D Vertices (Nodes) */
    gl.bindBuffer(gl.ARRAY_BUFFER, nodeBuf);
    gl.vertexAttribPointer(aPos, 3, gl.FLOAT, false, 0, 0);
    gl.uniform3fv(uColor, colB);
    gl.uniform1f(uAlpha, 0.75);
    gl.uniform1f(uPtSize, 5.0 * dpr);
    gl.uniform1i(uIsPt, 1);
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
    gl.vertexAttribPointer(aPos, 3, gl.FLOAT, false, 0, 0);
    gl.uniform3fv(uColor, colA);
    gl.uniform1f(uAlpha, 0.45);
    gl.uniform1f(uPtSize, 3.5 * dpr);
    gl.uniform1i(uIsPt, 1);
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
      if (!document.hidden) {
        startTime += performance.now() - lastTime;
        schedule();
      }
    });
  }
})();
