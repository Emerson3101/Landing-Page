<?php
/**
 * Shared site header.
 *
 * Pages set $page_title / $page_description BEFORE requiring this file.
 * Asset paths are root-absolute (e.g. /assets/...), which assumes the
 * site is served from the host root (e.g. `php -S localhost:8000` run
 * from the project root). A SITE_BASE indirection is deferred to a later
 * phase if a subfolder deploy is ever needed.
 *
 * i18n helpers (t()/tb()/lang_attr()) are loaded here so every page —
 * and nav.php below — can use them without each file re-requiring.
 */

require_once __DIR__ . '/i18n.php';

$page_title       = $page_title       ?? 'Emerson Plancarte — Software & Embedded Systems Engineer';
$page_description = $page_description ?? 'Personal landing page of Emerson Salvador Plancarte Cerecedo — software and embedded systems engineer.';
$page_lang        = $page_lang        ?? 'en'; // JS updates <html lang> when the language toggle flips

// Origin for canonical / Open Graph URLs. Precedence: per-page $site_url,
// then the SITE_URL environment variable (set by build.php/serve.php or the
// host), then the placeholder default. sitemap.xml must match this.
$site_url  = $site_url  ?? (getenv('SITE_URL') ?: 'https://emerson-plancarte.example');
$canonical = $canonical ?? ($site_url . ($_SERVER['REQUEST_URI'] ?? '/'));
$og_type   = $og_type   ?? 'website';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($page_lang, ENT_QUOTES, 'UTF-8') ?>"
      data-theme="dark"
      data-active-lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8') ?>">

  <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>

  <!-- SEO + social graph (Phase 8). Set $site_url to the real origin on
       deploy so canonical / Open Graph URLs resolve. -->
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="author" content="Emerson Plancarte">
  <meta name="robots" content="index,follow,archive">

  <!-- Open Graph -->
  <meta property="og:type" content="<?= htmlspecialchars($og_type, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:site_name" content="Emerson Plancarte">
  <meta property="og:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:locale" content="en_US">
  <meta property="og:locale:alternate" content="es_ES">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8') ?>">
  <!-- TODO Phase 10: og:image / twitter:image social card
       (/assets/img/social-card.png, 1200×630). -->

  <!-- Browser-chrome color follows the OS color scheme. The in-page
       light/dark toggle is data-theme-driven and persisted separately. -->
  <meta name="theme-color" content="#0b0f14" media="(prefers-color-scheme: dark)">
  <meta name="theme-color" content="#f7f8fa" media="(prefers-color-scheme: light)">

  <!-- Structured data (Phase 8): Person. Static, site-wide. -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "Emerson Salvador Plancarte Cerecedo",
    "givenName": "Emerson",
    "familyName": "Plancarte Cerecedo",
    "jobTitle": "Software & Embedded Systems Engineer",
    "url": "<?= htmlspecialchars($site_url . '/', ENT_QUOTES, 'UTF-8') ?>",
    "email": "mailto:emersonplancarte@gmail.com",
    "telephone": "+52 744 447 3905",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Colonia Electricistas",
      "addressLocality": "Acapulco de Juárez",
      "addressRegion": "Guerrero",
      "addressCountry": "MX"
    },
    "sameAs": [
      "https://linkedin.com/in/emerson-plancarte",
      "https://github.com/Emerson3101"
    ],
    "knowsLanguage": ["es", "en"],
    "knowsAbout": ["C#", "C", "C++", "Python", "Kotlin", "PHP", "Java", "JavaScript", "SQL", "Embedded Systems", "ESP32", "Laravel", "Android", "Web Development"]
  }
  </script>

  <!-- Fonts (Phase 9): self-hosted latin + latin-ext woff2 subsets of
       Space Grotesk (display), Inter (body), JetBrains Mono (code/data).
       Replaces the former third-party Google Fonts <link> — no cross-origin
       preconnect / CSS round-trip, and each family's variable woff2 is
       fetch-deduped across weights. See assets/css/fonts.css.
       Regenerate subsets with: node assets/fonts/_fetch-fonts.js -->
  <link rel="preload" href="/assets/fonts/space-grotesk-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="/assets/css/fonts.css">

  <!-- Styles. animations.css is the Phase 4 motion layer. -->
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/pages.css">
  <link rel="stylesheet" href="/assets/css/animations.css">
  <link rel="stylesheet" href="/assets/css/fx.css">

  <!-- Anti-FOUC: apply persisted theme AND language before first paint,
       and flag JS active so .js-gated CSS (the reveal hide in
       animations.css) takes effect before first paint — no flash of
       un-animated content. Defaults (dark, en) are set on <html> above. -->
  <script>
    (function () {
      document.documentElement.classList.add('js');
      try {
        var t = localStorage.getItem('theme');
        if (t === 'light' || t === 'dark') {
          document.documentElement.setAttribute('data-theme', t);
        }
        var l = localStorage.getItem('lang');
        if (l === 'en' || l === 'es') {
          document.documentElement.setAttribute('data-active-lang', l);
          document.documentElement.setAttribute('lang', l);
        }
      } catch (e) { /* storage unavailable — keep defaults */ }
    })();
  </script>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>

  <?php require __DIR__ . '/nav.php'; ?>

  <main id="main">
