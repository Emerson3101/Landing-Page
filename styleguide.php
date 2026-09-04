<?php
/**
 * Style inventory — internal review page (NOT linked in the nav).
 *
 * Phase 2 deliverable: every primitive shown in both themes and at all
 * sizes, so the design system can be reviewed before any real content is
 * built onto the home/Portfolio pages. Toggle the theme with the sun/moon
 * button in the nav (the choice persists). The page uses the shared
 * header/footer so it stays consistent with the live site.
 */

$page_title       = 'Style Inventory — Emerson Plancarte';
$page_description = 'Design system inventory for the Emerson Plancarte landing page.';
require __DIR__ . '/includes/header.php';
?>

<div class="container container--wide styleguide">

  <div class="styleguide__bar">
    <h1>Style Inventory</h1>
    <p class="hint">Toggle theme via the sun/moon button in the nav · Phase 2 preview</p>
  </div>

  <!-- Colors ---------------------------------------------------------- -->
  <section class="sg-block">
    <h2 class="sg-block__title">Color tokens</h2>
    <div class="sg-swatches">
      <?php
      $swatches = [
        '--color-bg', '--color-surface', '--color-surface-2', '--color-surface-3',
        '--color-text', '--color-text-muted', '--color-text-subtle',
        '--color-accent', '--color-accent-hover', '--color-accent-2', '--color-link',
        '--color-danger', '--color-danger-hover',
        '--color-border', '--color-border-strong',
        '--glass-bg', '--glass-border',
      ];
      foreach ($swatches as $tok):
      ?>
        <div class="sg-swatch">
          <div class="sg-swatch__chip<?= (strpos($tok, 'glass') !== false) ? ' sg-swatch__chip--glass' : '' ?>" style="background: var(<?= $tok ?>);"></div>
          <div class="sg-swatch__meta">
            <div class="sg-swatch__name"><?= $tok ?></div>
            <div class="sg-swatch__val">current theme</div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Type scale ------------------------------------------------------ -->
  <section class="sg-block">
    <h2 class="sg-block__title">Type scale</h2>
    <div class="sg-type">
      <?php foreach (['--step-5','--step-4','--step-3','--step-2','--step-1','--step-0','--step--1','--step--2'] as $step): ?>
        <div class="sg-row">
          <span class="sg-row__label"><?= $step ?></span>
          <span style="font-size: var(<?= $step ?>);">
            <?php if ($step === '--step-5'): ?>Emerson Plancarte<?php else: ?>The quick brown fox jumps — 0123456789<?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
      <div class="sg-row">
        <span class="sg-row__label">mono</span>
        <span style="font-family: var(--font-mono);">--font-mono · JetBrains Mono · 80% reduction</span>
      </div>
    </div>
  </section>

  <!-- Buttons --------------------------------------------------------- -->
  <section class="sg-block">
    <h2 class="sg-block__title">Buttons</h2>
    <div class="sg-cluster">
      <button class="btn btn--primary" type="button">Get in touch</button>
      <button class="btn btn--secondary" type="button">View portfolio</button>
      <button class="btn btn--ghost" type="button">Read more</button>
    </div>
    <div class="sg-cluster" style="margin-top: 1rem;">
      <button class="btn btn--primary btn--lg" type="button">Large primary</button>
      <button class="btn btn--secondary btn--sm" type="button">Small</button>
      <button class="btn btn--primary" type="button" disabled>Disabled</button>
      <button class="btn btn--primary" type="button">
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
        With icon
      </button>
    </div>
  </section>

  <!-- Links + icons --------------------------------------------------- -->
  <section class="sg-block">
    <h2 class="sg-block__title">Links & icons</h2>
    <p>Read the <a class="link" href="#main">project plan</a> or visit <a class="link" href="https://github.com/Emerson3101" rel="noopener">GitHub</a>.</p>
    <div class="sg-cluster" style="margin-top: 1rem; color: var(--color-text-muted);">
      <span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.5"></circle><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"></path></svg> sun</span>
      <span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"></path></svg> moon</span>
      <span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg> arrow</span>
    </div>
  </section>

  <!-- Tags ------------------------------------------------------------ -->
  <section class="sg-block">
    <h2 class="sg-block__title">Tags / chips</h2>
    <ul class="tag-list">
      <li><span class="tag"><span class="tag__dot"></span> C#</span></li>
      <li><span class="tag"><span class="tag__dot"></span> Python</span></li>
      <li><span class="tag"><span class="tag__dot"></span> PHP</span></li>
      <li><span class="tag"><span class="tag__dot"></span> JavaScript</span></li>
      <li><span class="tag"><span class="tag__dot"></span> .NET Core</span></li>
      <li><span class="tag"><span class="tag__dot"></span> PostgreSQL</span></li>
      <li><span class="tag"><span class="tag__dot"></span> ESP32</span></li>
    </ul>
  </section>

  <!-- Card ------------------------------------------------------------ -->
  <section class="sg-block">
    <h2 class="sg-block__title">Card</h2>
    <div class="sg-cluster sg-cluster--stack">
      <article class="card card--interactive">
        <p class="card__eyebrow">Web · Embedded</p>
        <h3 class="card__title">ICV — Voltage Quality Index</h3>
        <p class="card__body">Automated web tool computing the Índice de Calidad de Voltaje per Mexico's Código de Red across 50+ transmission localities.</p>
        <div class="card__footer">
          <span class="tag">.NET 3.5</span>
          <span class="tag">C#</span>
          <span class="tag">PHP</span>
          <span class="tag">AJAX</span>
        </div>
      </article>
    </div>
  </section>

  <!-- Stat + badge --------------------------------------------------- -->
  <section class="sg-block">
    <h2 class="sg-block__title">Stat & badge</h2>
    <div class="sg-cluster" style="gap: 3rem; align-items: flex-end;">
      <div class="stat">
        <span class="stat__value"><em>>80</em>%</span>
        <span class="stat__label">Reduction in analysis time</span>
      </div>
      <span class="badge">
        <svg class="badge__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v6a5 5 0 0 1-10 0V4zM7 4H4v2a3 3 0 0 0 3 3M17 4h3v2a3 3 0 0 1-3 3"></path></svg>
        Innovation Award · Smart Cities 2025
      </span>
    </div>
  </section>

  <!-- Timeline ------------------------------------------------------- -->
  <section class="sg-block">
    <h2 class="sg-block__title">Timeline</h2>
    <div class="timeline">
      <div class="timeline__item">
        <span class="timeline__node"></span>
        <p class="timeline__meta">03/2024 — 03/2026</p>
        <h3 class="timeline__title">Resident Engineer</h3>
        <p class="timeline__org">CFE Zona de Transmisión Guerrero–Morelos</p>
        <div class="timeline__body">
          <p>Led the ICV automated voltage-quality tool and sustained >99% availability on critical transmission systems.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Form fields ---------------------------------------------------- -->
  <section class="sg-block">
    <h2 class="sg-block__title">Form fields</h2>
    <div class="sg-cluster sg-cluster--stack">
      <div class="field">
        <label class="field__label" for="sg-name">Name <span class="req" aria-hidden="true">*</span></label>
        <input class="field__input" id="sg-name" type="text" placeholder="Your name">
        <span class="field__help">How should I address you?</span>
      </div>
      <div class="field field--error">
        <label class="field__label" for="sg-email">Email <span class="req" aria-hidden="true">*</span></label>
        <input class="field__input" id="sg-email" type="email" value="not-an-email" aria-invalid="true">
        <span class="field__error">Please enter a valid email address.</span>
      </div>
      <div class="field">
        <label class="field__label" for="sg-msg">Message</label>
        <textarea class="field__textarea" id="sg-msg" placeholder="Tell me about the project…"></textarea>
      </div>
    </div>
  </section>

  <p class="hint" style="margin-top: var(--space-6);">
    <a class="link" href="/">← Back to home</a> · This page is for design review only and is not linked from the site nav.
  </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
