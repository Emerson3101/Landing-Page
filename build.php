<?php
declare(strict_types=1);

/**
 * build.php — static site generator (host-agnostic).
 *
 * Renders every page PHP file to plain HTML in _site/ with clean URLs:
 *
 *   index.php                 -> _site/index.html               (URL: /)
 *   styleguide.php            -> _site/styleguide/index.html    (URL: /styleguide/)
 *   portfolio/index.php       -> _site/portfolio/index.html     (URL: /portfolio/)
 *   portfolio/icv.php         -> _site/portfolio/icv/index.html (URL: /portfolio/icv/)
 *
 * Any page-agnostic host with a build step can deploy the output:
 * Netlify/Cloudflare Pages publish _site, GitHub Pages publishes _site/**,
 * or a classic host can upload _site's contents directly. Switching hosts
 * means changing one publish/build command — no source changes.
 *
 * The same tree still runs dynamically on Apache + mod_php thanks to
 * .htaccess rewrites (and on `php -S` via serve.php), so the static build
 * is an output format, not a migration.
 *
 * Usage:
 *   php build.php [origin]
 *
 *   origin  absolute site origin, default https://emerson-plancarte.netlify.app
 *           (or from the SITE_URL environment variable). Used for canonical /
 *           Open Graph URLs and substituted into sitemap.xml + robots.txt.
 */

/* --- Config ---------------------------------------------------------- */
$ROOT   = __DIR__;
$OUT    = $ROOT . '/_site';
$origin = rtrim($argv[1] ?? (getenv('SITE_URL') ?: 'https://emerson-plancarte.netlify.app'), '/');

/* Pages to render: url-path => source file (relative to $ROOT). Adding a
 * page = adding one line here (or a portfolio/<slug>.php, auto-included). */
$pages = [
  '/'               => 'index.php',
  '/portfolio/'     => 'portfolio/index.php',
];

// Auto-discover portfolio detail pages.
foreach (glob($ROOT . '/portfolio/*.php') as $file) {
  $base = basename($file, '.php');
  if ($base === 'index') continue;
  $pages["/portfolio/$base/"] = "portfolio/$base.php";
}

/* Optional pages rendered only when present (not every host should expose
 * the styleguide; keep it internal by deleting the file or removing the line). */
if (is_file($ROOT . '/styleguide.php')) {
  $pages['/styleguide/'] = 'styleguide.php';
}

/* --- Helpers --------------------------------------------------------- */
function ensure_dir(string $dir): void {
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
}

function rrmdir(string $dir): void {
  if (!is_dir($dir)) return;
  foreach (scandir($dir) ?: [] as $f) {
    if ($f === '.' || $f === '..') continue;
    $p = $dir . '/' . $f;
    is_dir($p) ? rrmdir($p) : @unlink($p);
  }
  @rmdir($dir);
}

/** Copy $src tree into $dst, skipping PHP files and build/dev-only entries. */
function copy_static(string $src, string $dst, string $root): void {
  foreach (scandir($src) ?: [] as $f) {
    if ($f === '.' || $f === '..') continue;
    $s = $src . '/' . $f;
    $d = $dst . '/' . $f;

    // Never ship sources, the API endpoint, storage, the serverless
    // function, or dev/build-only files (assets/fonts/_fetch-fonts.js is
    // a dev regeneration script; netlify/functions/ runs server-side —
    // Netlify reads it from the repo root, not the publish).
    $rel = str_replace('\\', '/', substr($s, strlen($root) + 1));
    // (a|b)(/|$) — match the tree contents AND the bare top-level dir,
    // otherwise copy_static creates the excluded dir as an empty husk.
    if (preg_match('#^(api|storage|includes|netlify|\.playwright-mcp|\.claude|\.git|\.github|node_modules|_site)(/|$)#', $rel)
        || (preg_match('#\.(php|md|txt)$#', $rel) && basename($rel) !== 'robots.txt')
        || $rel === '.gitignore' || $rel === 'netlify.toml' || $rel === 'build.php'
        || $rel === 'serve.php' || $rel === '.htaccess' || $rel === 'assets/fonts/_fetch-fonts.js') {
      continue;
    }

    if (is_dir($s)) { ensure_dir($d); copy_static($s, $d, $root); }
    else @copy($s, $d);
  }
}

/** Render one page and return its HTML, faking the request URI so
 *  header.php's canonical/OG URLs point at the clean URL. */
function render_page(string $root, string $file, string $url_path, string $origin): string {
  $_SERVER['REQUEST_URI'] = $url_path;
  $GLOBALS['site_url']    = $origin;
  ob_start();
  include $root . '/' . $file;
  $html = (string)ob_get_clean();
  return $html;
}

/* --- Build ------------------------------------------------------------ */
$t0 = microtime(true);
echo "Building site → $OUT\n";

rrmdir($OUT);
ensure_dir($OUT);

$sitemap = ['<?xml version="1.0" encoding="UTF-8"?>',
  '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

foreach ($pages as $url => $file) {
  $html = render_page($ROOT, $file, $url, $origin);
  $out  = $OUT . ($url === '/' ? '/index.html' : rtrim($url, '/') . '/index.html');
  ensure_dir(dirname($out));
  file_put_contents($out, $html);
  $sitemap[] = '  <url><loc>' . htmlspecialchars($origin . $url, ENT_XML1) . '</loc>'
    . '<lastmod>' . date('Y-m-d') . '</lastmod></url>';
  echo "  $url  ← $file (" . strlen($html) . " bytes)\n";
}

$sitemap[] = '</urlset>';
file_put_contents($OUT . '/sitemap.xml', implode("\n", $sitemap) . "\n");

// robots.txt with the real origin substituted.
$robots = "# robots.txt — generated by build.php\n\nUser-agent: *\n"
  . "Allow: /\n"
  . "Disallow: /styleguide/\n\n"
  . "Sitemap: $origin/sitemap.xml\n";
file_put_contents($OUT . '/robots.txt', $robots);

// Static assets + public data files.
copy_static($ROOT, $OUT, $ROOT);

echo 'Done in ' . round((microtime(true) - $t0) * 1000) . " ms — "
  . count($pages) . " pages, origin: $origin\n";
