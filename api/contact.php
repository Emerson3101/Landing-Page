<?php
declare(strict_types=1);

/* =====================================================================
 * api/contact.php — contact form endpoint (Phase 7)
 * -------------------------------------------------------------------
 * POST only. Reads JSON (preferred) or application/x-www-form-urlencoded
 * and always answers JSON:
 *
 *   200 {ok:true}                                   message accepted (mailed + logged)
 *   400 {ok:false, error:"bad-request", message}    body unreadable / > MAX_BODY
 *   405 {ok:false, error:"method", message}         non-POST
 *   422 {ok:false, errors:{name,email,message}}      validation failed
 *   429 {ok:false, error:"rate-limited", message}    over the per-IP throttle
 *
 * Pipeline: method gate -> parse -> rate-limit -> honeypot (silent OK)
 * -> validate -> deliver (mail best-effort + CSV log) -> respond.
 *
 * The CSV log is the durable record on hosts with no mailer; mail() is
 * still attempted so a configured MTA delivers. Error strings are
 * bilingual (EN/ES) keyed off the payload's `lang` field, which the
 * front-end sets from <html data-active-lang>.
 *
 * The storage/ directory (rate-limit counters + messages.csv) must NOT
 * be web-served — storage/.htaccess denies Apache. On nginx or hosts
 * with .htaccess disabled, move storage/ above the web root and point
 * STORAGE at the absolute path.
 * ===================================================================*/

/* --- Config --------------------------------------------------------- */
const MAIL_TO   = 'emersonplancarte@gmail.com';
const MAIL_FROM = 'no-reply@emerson-plancarte.local';   // set to your domain on deploy
const RL_WINDOW = 3600;        // per-IP throttle window (seconds)
const RL_MAX    = 5;           // max submissions per IP per window
const MSG_MIN   = 10;          // min message length (chars)
const MSG_MAX   = 4000;        // max message length (caps logging)
const NAME_MAX  = 120;
const MAX_BODY  = 16384;       // reject payloads larger than 16 KB
const STORAGE   = __DIR__ . '/../storage';

/* --- Hardening ------------------------------------------------------ */
error_reporting(E_ALL);
ini_set('display_errors', '0');    // never leak internals to the client
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

/* --- Bilingual messages -------------------------------------------- */
$MSGS = [
  'en' => [
    'name_required'    => 'Please enter your name.',
    'name_long'        => 'Name is too long.',
    'email_required'   => 'Please enter your email address.',
    'email_invalid'    => 'Please enter a valid email address.',
    'message_required' => 'Please enter a message.',
    'message_short'    => 'Message must be at least ' . MSG_MIN . ' characters.',
    'message_long'     => 'Message is too long.',
    'rate_limited'     => 'Too many attempts — please try again later.',
    'method'           => 'Method not allowed.',
    'bad_request'      => 'Could not read the request.',
  ],
  'es' => [
    'name_required'    => 'Por favor escribe tu nombre.',
    'name_long'        => 'El nombre es demasiado largo.',
    'email_required'   => 'Por favor escribe tu correo.',
    'email_invalid'    => 'Por favor escribe un correo válido.',
    'message_required' => 'Por favor escribe un mensaje.',
    'message_short'    => 'El mensaje debe tener al menos ' . MSG_MIN . ' caracteres.',
    'message_long'     => 'El mensaje es demasiado largo.',
    'rate_limited'     => 'Demasiados intentos — por favor inténtalo más tarde.',
    'method'           => 'Método no permitido.',
    'bad_request'      => 'No se pudo leer la solicitud.',
  ],
];

/* --- Helpers -------------------------------------------------------- */
function respond(int $code, array $body): void {
  http_response_code($code);
  echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}
function clean(string $s): string {
  $s = trim($s);
  $s = str_replace(["\r", "\n", "\0"], ' ', $s);     // no CRLF -> header/log injection
  return $s;
}
function utf8_len(string $s): int {
  return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
}
function utf8_substr(string $s, int $start, int $len): string {
  return function_exists('mb_substr') ? mb_substr($s, $start, $len) : substr($s, $start, $len);
}
function client_ip(): string {
  // REMOTE_ADDR only. We don't trust X-Forwarded-For without a known proxy
  // in front; a misconfigured or spoofable header would let a client pick
  // its own IP and dodge the throttle.
  return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/* --- Method gate ---------------------------------------------------- */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Allow: POST');
  respond(405, ['ok' => false, 'error' => 'method', 'message' => $MSGS['en']['method']]);
}

/* --- Parse body ----------------------------------------------------- */
$raw = file_get_contents('php://input') ?: '';
if (strlen($raw) > MAX_BODY) {
  respond(400, ['ok' => false, 'error' => 'bad-request', 'message' => $MSGS['en']['bad_request']]);
}
$payload = json_decode($raw, true);
if (!is_array($payload)) {
  $payload = $_POST;     // form-encoded fallback (no-JS / proxy)
}

// Active language for response strings (payload wins; default EN for edge cases).
$lang = (($payload['lang'] ?? 'en') === 'es') ? 'es' : 'en';
$m    = $MSGS[$lang];

/* --- Rate limiting (file-based, per IP) ---------------------------- */
$ip     = client_ip();
$rlDir  = STORAGE . '/rl';
$rlFile = $rlDir . '/' . md5($ip) . '.json';
$now    = time();
$hits   = [];
if (is_file($rlFile)) {
  $decoded = json_decode(file_get_contents($rlFile) ?: '[]', true);
  if (is_array($decoded)) {
    foreach ($decoded as $t) {
      if (is_int($t) && ($now - $t) < RL_WINDOW) $hits[] = $t;
    }
  }
}
if (count($hits) >= RL_MAX) {
  respond(429, ['ok' => false, 'error' => 'rate-limited', 'message' => $m['rate_limited']]);
}
$hits[] = $now;
if (!is_dir($rlDir)) @mkdir($rlDir, 0775, true);
@file_put_contents($rlFile, json_encode($hits), LOCK_EX);

/* --- Pull + clean fields ------------------------------------------- */
$name    = clean((string)($payload['name']    ?? ''));
$email   = clean((string)($payload['email']   ?? ''));
$message = trim((string)($payload['message'] ?? ''));    // keep internal newlines
$company = trim((string)($payload['company']  ?? ''));    // honeypot

/* --- Honeypot: silent success -------------------------------------- */
// Filling the hidden "Company" field means a bot did. Reply with OK so it
// looks like a win, but send nothing and log nothing. Real users never see
// the field. (The rate limit above still shields the endpoint either way.)
if ($company !== '') {
  respond(200, ['ok' => true]);
}

/* --- Validate ------------------------------------------------------ */
$errors = [];
if ($name === '')                     $errors['name']    = $m['name_required'];
elseif (utf8_len($name) > NAME_MAX)   $errors['name']    = $m['name_long'];
if ($email === '')                    $errors['email']   = $m['email_required'];
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = $m['email_invalid'];
if ($message === '')                  $errors['message'] = $m['message_required'];
elseif (utf8_len($message) < MSG_MIN) $errors['message'] = $m['message_short'];
elseif (utf8_len($message) > MSG_MAX) $errors['message'] = $m['message_long'];

if ($errors) {
  respond(422, ['ok' => false, 'errors' => $errors]);
}

/* --- Deliver: mail (best-effort) + CSV log ------------------------- */
$subject = 'Portfolio contact — ' . $name;
$body    = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}\n\n— submitted "
         . date('c') . " from {$ip}";
$header  = "From: " . MAIL_FROM . "\r\n"
         . "Reply-To: " . $email . "\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/plain; charset=utf-8\r\n";

$sent = @mail(MAIL_TO, $subject, $body, $header);

// CSV log — always. The durable record whether or not mail() succeeded.
if (!is_dir(STORAGE)) @mkdir(STORAGE, 0775, true);
$csv     = STORAGE . '/messages.csv';
$newHdr  = !is_file($csv);
$fp      = @fopen($csv, 'a');
if ($fp) {
  if ($newHdr) fputcsv($fp, ['timestamp', 'ip', 'name', 'email', 'message', 'sent']);
  fputcsv($fp, [date('c'), $ip, $name, $email, utf8_substr($message, 0, MSG_MAX), $sent ? '1' : '0']);
  fclose($fp);
}

/* --- Respond -------------------------------------------------------- */
respond(200, ['ok' => true]);
