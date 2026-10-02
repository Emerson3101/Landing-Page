/* =====================================================================
 * netlify/functions/contact.js — contact form endpoint (static deploys)
 * -------------------------------------------------------------------
 * Serves POST /api/contact on the Netlify build. Mirrors the dynamic
 * tree's api/contact.php contract exactly — same status codes, same
 * bilingual (EN/ES) error strings, same honeypot + size caps — so
 * assets/js/main.js submits to ONE URL on every host:
 *
 *   Apache/.htaccess  -> /api/contact rewrites to api/contact.php
 *   php -S serve.php  -> /api/contact maps to api/contact.php
 *   Netlify (static)  -> this function (config.path at the bottom)
 *
 *   200 {ok:true}                                  message mailed via Resend
 *   400 {ok:false, error:"bad-request", message}   payload > MAX_BODY
 *   405 {ok:false, error:"method", message}         non-POST (sets Allow)
 *   422 {ok:false, errors:{name,email,message}}    validation failed
 *   429 {ok:false, error:"rate-limited", message}  over the per-IP throttle
 *   500 {ok:false, error:"send-failed", message}   key missing / Resend
 *                                                  rejected / send timed out
 *
 * Pipeline: method gate -> parse -> rate-limit -> honeypot (silent OK)
 * -> validate -> deliver via Resend -> respond.
 *
 * Deliberate differences from the PHP endpoint (see PROJECT_GUIDE §14):
 *   - No CSV log: serverless has no writable filesystem. The email IS
 *     the record.
 *   - A failed send returns 500, not 200. With no CSV fallback, an OK
 *     would silently lose the message; 500 makes the front-end fall
 *     back to the visitor's own mail client (submitViaMailto).
 *   - The throttle is an in-memory Map — per warm instance, not global.
 *     A cold start or instance recycle resets it. The honeypot plus
 *     Resend's own abuse controls carry the rest of the anti-spam load.
 *
 * Delivery: Resend REST API, key from the RESEND_API_KEY environment
 * variable (set in the Netlify UI — never in the repo). Without a
 * verified domain Resend only delivers to the account owner's own
 * address, which is why the account must be signed up with MAIL_TO;
 * reply_to is the visitor, so a reply in Gmail goes straight to them.
 *
 * Zero dependencies, no bundler, no npm: one ESM file using only
 * Node 18+ globals (fetch, AbortController, URLSearchParams).
 * ===================================================================*/

/* --- Config (mirrors api/contact.php constants) --------------------- */
const MAIL_TO        = 'emersonplancarte@gmail.com';
const MAIL_FROM      = 'Portfolio Contact <onboarding@resend.dev>';
const RL_WINDOW      = 3600;    // per-IP throttle window (seconds)
const RL_MAX         = 5;       // max submissions per IP per window
const MSG_MIN        = 10;      // min message length (characters)
const MSG_MAX        = 4000;    // max message length (characters)
const NAME_MAX       = 120;     // max name length (characters)
const MAX_BODY       = 16384;   // reject payloads larger than 16 KB
const SEND_TIMEOUT_MS = 8000;   // give up on Resend -> 500 -> mailto fallback

/* --- Bilingual messages (identical strings to api/contact.php) ------ */
const MSGS = {
  en: {
    name_required:    'Please enter your name.',
    name_long:        'Name is too long.',
    email_required:   'Please enter your email address.',
    email_invalid:    'Please enter a valid email address.',
    message_required: 'Please enter a message.',
    message_short:    'Message must be at least ' + MSG_MIN + ' characters.',
    message_long:     'Message is too long.',
    rate_limited:     'Too many attempts — please try again later.',
    method:           'Method not allowed.',
    bad_request:      'Could not read the request.',
    send_failed:      'Something went wrong sending the form. Please email me directly at ' + MAIL_TO + '.'
  },
  es: {
    name_required:    'Por favor escribe tu nombre.',
    name_long:        'El nombre es demasiado largo.',
    email_required:   'Por favor escribe tu correo.',
    email_invalid:    'Por favor escribe un correo válido.',
    message_required: 'Por favor escribe un mensaje.',
    message_short:    'El mensaje debe tener al menos ' + MSG_MIN + ' caracteres.',
    message_long:     'El mensaje es demasiado largo.',
    rate_limited:     'Demasiados intentos — por favor inténtalo más tarde.',
    method:           'Método no permitido.',
    bad_request:      'No se pudo leer la solicitud.',
    send_failed:      'Algo salió mal al enviar el formulario. Puedes escribirme directamente a ' + MAIL_TO + '.'
  }
};

/* --- Helpers --------------------------------------------------------- */
function respond(code, body, extraHeaders) {
  const headers = {
    'Content-Type': 'application/json; charset=utf-8',
    'X-Content-Type-Options': 'nosniff',
    'Referrer-Policy': 'no-referrer',
    'Cache-Control': 'no-store'
  };
  if (extraHeaders) {
    for (const k in extraHeaders) headers[k] = extraHeaders[k];
  }
  return new Response(JSON.stringify(body), { status: code, headers });
}
function clean(s) {
  // Same contract as the PHP endpoint: trim + collapse CRLF/NUL so the
  // name and email stay single-line in the mail payload.
  return String(s == null ? '' : s).trim().replace(/[\r\n\0]/g, ' ');
}
function utf8Len(s) {
  return [...s].length;         // code points, matching PHP's mb_strlen
}
function clientIp(req, context) {
  // x-nf-client-connection-ip is set by Netlify's edge and cannot be
  // spoofed by the client (unlike a bare X-Forwarded-For).
  const h = (req.headers.get('x-nf-client-connection-ip') || '').trim();
  return h || (context && context.ip) || '0.0.0.0';
}

/* --- Rate limiting: in-memory, per warm instance --------------------- */
const hits = new Map();         // ip -> recent submission timestamps (s)
function overLimit(ip, nowSec) {
  const recent = (hits.get(ip) || []).filter(function (t) {
    return nowSec - t < RL_WINDOW;
  });
  if (hits.size > 5000) hits.clear();   // safety valve: never grow unbounded
  if (recent.length >= RL_MAX) {
    hits.set(ip, recent);               // keep the window, consume nothing
    return true;
  }
  recent.push(nowSec);                  // every POST consumes a slot — even
  hits.set(ip, recent);                 // one that later fails 422 (PHP parity)
  return false;
}

/* --- Handler --------------------------------------------------------- */
export default async (req, context) => {

  /* Method gate */
  if (req.method !== 'POST') {
    return respond(405, { ok: false, error: 'method', message: MSGS.en.method }, { Allow: 'POST' });
  }

  /* Parse body: JSON preferred, form-encoded fallback (no-JS submit) */
  const raw = await req.text();
  if (raw.length > MAX_BODY) {
    return respond(400, { ok: false, error: 'bad-request', message: MSGS.en.bad_request });
  }
  let payload = null;
  try {
    const parsed = JSON.parse(raw);
    if (parsed && typeof parsed === 'object') payload = parsed;
  } catch (e) { /* fall through to form-encoded */ }
  if (!payload) {
    // URLSearchParams never throws; a garbage body parses into junk keys,
    // an empty body into {} — both then 422 on validation, exactly like
    // the PHP endpoint's $_POST fallback.
    payload = Object.fromEntries(new URLSearchParams(raw));
  }

  // Active language for response strings (payload wins; default EN).
  const m = MSGS[payload.lang === 'es' ? 'es' : 'en'];

  /* Rate limit */
  const ip = clientIp(req, context);
  if (overLimit(ip, Math.floor(Date.now() / 1000))) {
    return respond(429, { ok: false, error: 'rate-limited', message: m.rate_limited });
  }

  /* Pull + clean fields */
  const name    = clean(payload.name);
  const email   = clean(payload.email);
  const message = String(payload.message == null ? '' : payload.message).trim(); // keep newlines
  const company = String(payload.company == null ? '' : payload.company).trim(); // honeypot

  /* Honeypot: silent success — send nothing */
  if (company !== '') {
    return respond(200, { ok: true });
  }

  /* Validate */
  const errors = {};
  if (name === '')                        errors.name    = m.name_required;
  else if (utf8Len(name) > NAME_MAX)      errors.name    = m.name_long;
  if (email === '')                       errors.email   = m.email_required;
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.email = m.email_invalid;
  if (message === '')                     errors.message = m.message_required;
  else if (utf8Len(message) < MSG_MIN)    errors.message = m.message_short;
  else if (utf8Len(message) > MSG_MAX)    errors.message = m.message_long;
  if (Object.keys(errors).length) {
    return respond(422, { ok: false, errors: errors });
  }

  /* Deliver via Resend. A missing key or a failed call MUST be a 500 —
   * with no CSV fallback a 200 would silently lose the message. */
  const key = process.env.RESEND_API_KEY;
  if (!key) {
    console.error('contact: RESEND_API_KEY is not set');
    return respond(500, { ok: false, error: 'send-failed', message: m.send_failed });
  }

  const controller = new AbortController();
  const timer = setTimeout(function () { controller.abort(); }, SEND_TIMEOUT_MS);
  let delivered = false;
  try {
    const res = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + key,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        from: MAIL_FROM,
        to: [MAIL_TO],
        reply_to: email,
        subject: 'Portfolio contact — ' + name,
        text: 'Name: ' + name + '\nEmail: ' + email + '\n\nMessage:\n' + message
            + '\n\n— submitted ' + new Date().toISOString() + ' from ' + ip
      }),
      signal: controller.signal
    });
    delivered = res.ok;
    if (!delivered) {
      const detail = await res.text().catch(function () { return ''; });
      console.error('contact: Resend returned ' + res.status + ' ' + detail);
    }
  } catch (err) {
    console.error('contact: Resend call failed', err);
  } finally {
    clearTimeout(timer);
  }

  if (!delivered) {
    return respond(500, { ok: false, error: 'send-failed', message: m.send_failed });
  }
  return respond(200, { ok: true });
};

/* --- Route ------------------------------------------------------------
 * Serve at /api/contact (NOT /.netlify/functions/contact) so the
 * front-end uses one URL on every host. */
export const config = {
  path: '/api/contact'
};
