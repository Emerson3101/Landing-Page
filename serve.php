<?php
declare(strict_types=1);

/**
 * serve.php — local dev router for `php -S <host>:<port> serve.php`.
 *
 * Maps clean URLs to their source files so the dynamic tree behaves like
 * the static build (and like Apache with .htaccess):
 *
 *   /                    -> index.php
 *   /portfolio/          -> portfolio/index.php
 *   /portfolio/icv/      -> portfolio/icv.php
 *   /styleguide/         -> styleguide.php
 *   /api/contact.php     -> api/contact.php        (still POSTs)
 *
 * Everything else (assets, fonts, JSON) is served straight from disk.
 */

$root = __DIR__;
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path  = rawurldecode($uri);

// Clean URL -> source file candidates.
$candidates = [];
if ($path === '/' || $path === '') {
  $candidates[] = '/index.php';
} else {
  $trim = rtrim($path, '/');
  $candidates[] = $trim . '.php';          // /portfolio/icv  -> portfolio/icv.php
  $candidates[] = $trim . '/index.php';     // /portfolio/     -> portfolio/index.php
}

foreach ($candidates as $c) {
  $file = $root . $c;
  if (is_file($file)) {
    $GLOBALS['site_url'] = getenv('SITE_URL') ?: 'http://localhost:8000';
    require $file;
    return true;
  }
}

// Static asset straight off disk (php -S already does this, but being
// explicit keeps the 404 branch ours).
$file = $root . $path;
if ($path !== '/' && is_file($file) && strpos(realpath($file), $root) === 0) {
  return false;   // let the built-in server stream it
}

http_response_code(404);
echo "404 — not found: " . htmlspecialchars($path);
