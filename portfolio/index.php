<?php
/**
 * Portfolio index — filterable grid of project cards.
 *
 * Cards are driven by assets/data/projects.json (single source of truth).
 * Each card links to a hand-written bilingual detail page at
 * portfolio/<slug>.php.
 */

$page_title       = 'Portfolio — Emerson Plancarte';
$page_description = 'Selected projects by Emerson Plancarte — backend developer specializing in .NET and modern web stacks. Industrial electrical grid telemetry, cloud-native RAG systems, Next.js web platforms, and Android applications.';
require __DIR__ . '/../includes/header.php';

$data     = json_decode(file_get_contents(__DIR__ . '/../assets/data/projects.json'), true);
$projects = $data['projects'] ?? [];
$cats     = $data['categories'] ?? [];

function bi_field(array $field) {
  $en = isset($field['en']) ? htmlspecialchars($field['en'], ENT_QUOTES, 'UTF-8') : '';
  $es = isset($field['es']) ? htmlspecialchars($field['es'], ENT_QUOTES, 'UTF-8') : '';
  return '<span data-lang="en">' . $en . '</span><span data-lang="es">' . $es . '</span>';
}
?>

<!-- =========================================================
     Portfolio hero — same WebGL aurora/polyhedron scene as home
     (webgl-hero.js boots wherever #hero-gl exists; phone tier,
     theme sync, reduced-motion static frame and the scroll-out
     pause all carry over unchanged).
     ======================================================= -->
<section class="hero hero--compact" aria-labelledby="portfolio-title">
  <canvas class="hero__canvas" id="hero-gl" aria-hidden="true"></canvas>
  <div class="hero__inner" data-reveal-group>
    <h1 class="hero__title" id="portfolio-title" data-reveal data-typetrick>
      <?= t('Engineering Portfolio', 'Portafolio de Ingeniería') ?>
    </h1>
    <p class="hero__subtitle" data-reveal data-typetrick>
      <?= t(
        'Selected work spanning industrial grid telemetry, cloud-native RAG systems, modern web platforms, and mobile engineering.',
        'Trabajo seleccionado que abarca telemetría industrial de red, sistemas RAG cloud-native, plataformas web modernas y desarrollo móvil.'
      ) ?>
    </p>
  </div>
</section>

<!-- =========================================================
     Filterable grid & Search
     ======================================================= -->
<section class="section portfolio" aria-labelledby="portfolio-grid-title">
  <div class="container container--wide">
    <h2 id="portfolio-grid-title" class="visually-hidden">
      <?= t('All projects', 'Todos los proyectos') ?>
    </h2>

    <!-- Layout lives in pages.css (.portfolio__controls) so the phone
         breakpoint can restack it — inline styles would win the cascade. -->
    <div class="portfolio__controls" data-reveal>
      <div class="search-bar" style="margin-bottom: 0;">
        <svg class="search-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <circle cx="11" cy="11" r="8"/>
          <path d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="search" class="search-bar__input" id="portfolio-search" autocomplete="off" spellcheck="false" <?= lang_attr('Search by keyword, tech stack, or feature…', 'Buscar por palabra clave, tecnología o característica…', 'placeholder') ?> <?= lang_attr('Search projects', 'Buscar proyectos', 'aria-label') ?>>
      </div>

      <div class="filters" role="group" <?= lang_attr('Filter projects by category', 'Filtrar proyectos por categoría', 'aria-label') ?>>
        <button type="button" class="filter is-active" data-filter="all" aria-pressed="true">
          <?= t('All', 'Todos') ?>
        </button>
        <?php foreach ($cats as $key => $label): ?>
          <button type="button" class="filter" data-filter="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false">
            <?= bi_field($label) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <ul class="portfolio__grid" data-reveal-group>
      <?php $leadSlotTaken = false; ?>
      <?php foreach ($projects as $p):
        $slug    = htmlspecialchars($p['slug'],    ENT_QUOTES, 'UTF-8');
        $cat     = htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8');
        $detail  = htmlspecialchars($p['links']['detail'], ENT_QUOTES, 'UTF-8');
        // Only the FIRST featured project takes the wide lead slot; the rest
        // render as regular cards so the grid never piles up double-widths.
        $isFeat  = !$leadSlotTaken && !empty($p['featured']);
        if ($isFeat) $leadSlotTaken = true;
        $catLabel = $cats[$p['category']] ?? ['en' => ucfirst($p['category']), 'es' => ucfirst($p['category'])];
        ?>
        <li class="portfolio__item<?= $isFeat ? ' portfolio__item--featured' : '' ?>"
            data-category="<?= $cat ?>" data-reveal>

          <a class="card card--interactive portfolio__card" href="<?= $detail ?>" data-tilt>
            <div class="portfolio__card-head">
              <span class="portfolio__cat"><?= bi_field($catLabel) ?></span>
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

    <p class="portfolio__empty" hidden data-reveal>
      <?= t('No projects match your current filter or search term.', 'Ningún proyecto coincide con el filtro o término de búsqueda actual.') ?>
    </p>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php';
