<?php
/**
 * Portfolio index — filterable grid of project cards.
 *
 * Cards are driven by assets/data/projects.json (single source of truth).
 * Each card links to a hand-written bilingual detail page at
 * portfolio/<slug>.php. Two tiers are surfaced honestly: real CV projects
 * ('real') and built-for-portfolio showcase demos ('demo'), distinguished
 * by the badge text.
 *
 * Bilingual JSON fields are rendered as dual data-lang spans via the local
 * bi() helper, so the existing EN/ES toggle (which flips <html
 * data-active-lang> and hides the other language via CSS) works on the grid
 * without rebuilding it. Filtering is enhanced with JS (assets/js/portfolio.js)
 * and degrades to a static grid when JS is unavailable.
 */

$page_title       = 'Portfolio — Emerson Plancarte';
$page_description = 'Selected work by Emerson Plancarte — software and embedded systems engineer. Real projects (ICV voltage-quality tool, TecAssist) plus showcase demos across web, backend, embedded, and mobile.';
require __DIR__ . '/../includes/header.php';

$data     = json_decode(file_get_contents(__DIR__ . '/../assets/data/projects.json'), true);
$projects = $data['projects'] ?? [];
$cats     = $data['categories'] ?? [];
$tiers    = $data['tiers'] ?? [];

// Emit a bilingual {en, es} field from the JSON as two escaped data-lang
// spans, matching the t() contract so the language toggle hides the right one.
function bi_field(array $field) {
  $en = isset($field['en']) ? htmlspecialchars($field['en'], ENT_QUOTES, 'UTF-8') : '';
  $es = isset($field['es']) ? htmlspecialchars($field['es'], ENT_QUOTES, 'UTF-8') : '';
  return '<span data-lang="en">' . $en . '</span><span data-lang="es">' . $es . '</span>';
}
?>

<!-- =========================================================
     Portfolio hero (compact)
     ======================================================= -->
<section class="hero hero--compact" aria-labelledby="portfolio-title">
  <div class="hero__inner" data-reveal-group>
    <p class="hero__eyebrow" data-reveal><?= t('Portfolio', 'Portafolio') ?></p>
    <h1 class="hero__title" id="portfolio-title" data-reveal>
      <?= t('Selected work', 'Trabajo seleccionado') ?>
    </h1>
    <p class="hero__subtitle" data-reveal>
      <?= t(
        'Real projects from the CV alongside built-for-portfolio showcase demos — spanning web, backend, embedded, and mobile.',
        'Proyectos reales del CV junto con demostraciones creadas para el portafolio — abarcando web, backend, embebido y móvil.'
      ) ?>
    </p>
  </div>
</section>

<!-- =========================================================
     Filterable grid
     ======================================================= -->
<section class="section portfolio" aria-labelledby="portfolio-grid-title">
  <div class="container">
    <h2 id="portfolio-grid-title" class="visually-hidden">
      <?= t('All projects', 'Todos los proyectos') ?>
    </h2>

    <!-- Filter bar. aria-pressed reflects active state (set by portfolio.js
         and pre-set on the All button as the default). -->
    <div class="filters" role="group" <?= lang_attr('Filter projects by category', 'Filtrar proyectos por categoría', 'aria-label') ?> data-reveal>
      <button type="button" class="filter is-active" data-filter="all" aria-pressed="true">
        <?= t('All', 'Todos') ?>
      </button>
      <?php foreach ($cats as $key => $label): ?>
        <button type="button" class="filter" data-filter="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false">
          <?= bi_field($label) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <ul class="portfolio__grid" data-reveal-group>
      <?php foreach ($projects as $p):
        $slug    = htmlspecialchars($p['slug'],    ENT_QUOTES, 'UTF-8');
        $cat     = htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8');
        $tier    = htmlspecialchars($p['tier'],    ENT_QUOTES, 'UTF-8');
        $detail  = htmlspecialchars($p['links']['detail'], ENT_QUOTES, 'UTF-8');
        $isFeat  = !empty($p['featured']);
        $catLabel = $cats[$p['category']] ?? ['en' => ucfirst($p['category']), 'es' => ucfirst($p['category'])];
        $tierLabel = $tiers[$p['tier']] ?? ['en' => '', 'es' => ''];
        ?>
        <li class="portfolio__item<?= $isFeat ? ' portfolio__item--featured' : '' ?>"
            data-category="<?= $cat ?>" data-reveal>

          <a class="card card--interactive portfolio__card" href="<?= $detail ?>" data-tilt>
            <div class="portfolio__card-head">
              <span class="portfolio__cat"><?= bi_field($catLabel) ?></span>
              <span class="badge portfolio__tier portfolio__tier--<?= $tier ?>"><?= bi_field($tierLabel) ?></span>
            </div>

            <h3 class="portfolio__title"><?= bi_field($p['title']) ?></h3>
            <p class="portfolio__subtitle"><?= bi_field($p['subtitle']) ?></p>
            <p class="portfolio__summary"><?= bi_field($p['summary']) ?></p>

            <?php if (!empty($p['stack'])): ?>
              <ul class="tag-list portfolio__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
                <?php foreach ($p['stack'] as $tag): ?>
                  <li><span class="tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <span class="portfolio__cta" aria-hidden="true">
              <?= t('Read the case study', 'Leer el caso de estudio') ?>
              <span class="portfolio__cta-arrow" aria-hidden="true">&rarr;</span>
            </span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Empty-state message shown by portfolio.js when a filter has no
         matches (none here, but kept for a11y / future categories). -->
    <p class="portfolio__empty" hidden data-reveal>
      <?= t('No projects in this category yet.', 'Aún no hay proyectos en esta categoría.') ?>
    </p>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php';
