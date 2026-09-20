<?php
/**
 * Site navigation.
 *
 * Anchor links target home sections (#about, #skills, #experience,
 * #education, #contact) plus the portfolio index. Labels are bilingual
 * via t(); the active language is <html data-active-lang>.
 *
 * Controls in .site-nav__actions (left→right):
 *   - Language toggle (EN/ES)     — behavior in main.js (wire-on in this phase)
 *   - Theme toggle (light/dark)   — behavior in main.js, persisted via
 *                                    localStorage, applied pre-paint by header.php
 *   - Mobile drawer toggle        — wired in Phase 6 (CSS shows it on
 *                                    narrow screens; JS drives open/close)
 */
$nav_links = [
  ['href' => '/#about',        'en' => 'About',        'es' => 'Acerca de'],
  ['href' => '/#projects',     'en' => 'Projects',     'es' => 'Proyectos'],
  ['href' => '/#skills',       'en' => 'Skills',       'es' => 'Habilidades'],
  ['href' => '/#experience',   'en' => 'Experience',   'es' => 'Experiencia'],
  ['href' => '/portfolio/',    'en' => 'Portfolio',    'es' => 'Portafolio'],
  ['href' => '/#contact',      'en' => 'Contact',      'es' => 'Contacto'],
];
?>
<header class="site-header">
  <nav class="site-nav" aria-label="Primary">
    <a class="site-nav__brand" href="/">Emerson<span>.</span></a>

    <ul class="site-nav__links">
      <?php foreach ($nav_links as $link): ?>
        <li>
          <a class="site-nav__link" href="<?= $link['href'] ?>">
            <?= t($link['en'], $link['es']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="site-nav__actions">
      <!-- Command palette trigger (fx.js owns the palette itself). -->
      <button
        type="button"
        class="nav-kbd-hint"
        id="palette-open"
        <?= lang_attr('Open command palette', 'Abrir paleta de comandos', 'aria-label') ?>
      >Ctrl K</button>

      <!-- Language toggle: shows the language you'd switch TO.
           aria-pressed reflects whether ES is active. -->
      <button
        type="button"
        class="icon-btn lang-toggle"
        id="lang-toggle"
        aria-label="Switch to Spanish"
        aria-pressed="false"
        title="EN / ES"
      >
        <span data-lang="en">ES</span>
        <span data-lang="es">EN</span>
      </button>

      <!-- Theme toggle: aria-pressed reflects active state (light = pressed). -->
      <button
        type="button"
        class="icon-btn"
        id="theme-toggle"
        aria-label="Switch to light theme"
        aria-pressed="false"
        title="Toggle theme"
      >
        <!-- Sun shown in dark mode (action: go light); hidden in light mode. -->
        <svg class="icon icon--sun" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="4.5"></circle>
          <path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"></path>
        </svg>
        <!-- Moon shown in light mode (action: go dark); hidden in dark mode. -->
        <svg class="icon icon--moon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"></path>
        </svg>
      </button>

      <!-- Mobile nav toggle (Phase 6). icon-btn that swaps hamburger/close
           via aria-expanded; the drawer is rendered after </nav>. Hidden on
           wide screens via CSS, and hidden without .js on narrow screens
           (so the inline links remain the no-JS fallback there). -->
      <button
        type="button"
        class="icon-btn site-nav__toggle"
        id="nav-toggle"
        aria-label="Open menu"
        aria-expanded="false"
        aria-controls="nav-drawer"
        title="Menu"
      >
        <svg class="icon icon--menu" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M3 6h18M3 12h18M3 18h18"></path>
        </svg>
        <svg class="icon icon--close" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 5l14 14M19 5L5 19"></path>
        </svg>
      </button>
    </div>
  </nav>

  <!-- Mobile nav drawer (Phase 6). Duplicate of the inline links, shown only
       on narrow screens below the header bar. JS-driven: toggle, Escape,
       click-outside, focus wrap. body[data-nav-open] drives the dim overlay
       + slide via CSS, so no extra element is needed. -->
  <div class="nav-drawer" id="nav-drawer">
    <ul class="nav-drawer__links">
      <?php foreach ($nav_links as $link): ?>
        <li>
          <a class="nav-drawer__link" href="<?= $link['href'] ?>">
            <?= t($link['en'], $link['es']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</header>
