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
              <?php if (!empty($p['links']['github'])): ?>
                <span class="portfolio__gh-badge">
                  <svg class="icon" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true" style="width: 1em; height: 1em; vertical-align: -0.15em;">
                    <path d="M12 .5a11.5 11.5 0 0 0-3.635 22.41c.576.106.787-.25.787-.556 0-.275-.01-1.004-.016-1.972-3.198.696-3.873-1.542-3.873-1.542-.523-1.329-1.278-1.683-1.278-1.683-1.045-.714.08-.7.08-.7 1.156.082 1.764 1.188 1.764 1.188 1.027 1.761 2.695 1.252 3.352.957.103-.744.402-1.252.732-1.54-2.553-.29-5.238-1.278-5.238-5.687 0-1.257.449-2.283 1.187-3.09-.119-.291-.515-1.462.112-3.05 0 0 .967-.31 3.169 1.18a10.99 10.99 0 0 1 5.772 0c2.2-1.49 3.166-1.18 3.166-1.18.629 1.588.233 2.759.115 3.05.74.807 1.185 1.833 1.185 3.09 0 4.42-2.689 5.393-5.252 5.678.413.356.78 1.058.78 2.13 0 1.538-.014 2.778-.014 3.157 0 .309.208.668.793.555A11.5 11.5 0 0 0 12 .5z"/>
                  </svg>
                  GitHub
                </span>
              <?php endif; ?>
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
