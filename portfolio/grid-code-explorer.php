<?php
/**
 * Grid Code Explorer (detail page) — showcase demo.
 *
 * A built-for-portfolio React SPA representing the web/full-stack-JS line
 * of the CV. Demonstrates React, modern JS, and a small domain UI tuned to
 * Emerson’s voltage-quality work. Honest about its demo status.
 */

$page_title       = 'Grid Code Explorer — Emerson Plancarte';
$page_description = 'Grid Code Explorer: a small React SPA for looking up Mexico Grid-Code voltage limits by level and locality. Showcase demo from Emerson Plancarte’s portfolio.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--demo"><?= t('Showcase demo', 'Demostración') ?></span>
        <?= t('Web · React', 'Web · React') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('Grid Code Explorer', 'Explorador del Código de Red') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'A small single-page React app for looking up Grid-Code voltage limits by level and locality.',
          'Una SPA en React pequeña para consultar los límites de voltaje del Código de Red por nivel y localidad.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Type', 'Tipo') ?></dt><dd><?= t('Showcase demo', 'Demostración') ?></dd></div>
        <div><dt><?= t('Year', 'Año') ?></dt><dd>2025</dd></div>
        <div><dt><?= t('Category', 'Categoría') ?></dt><dd>Web</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">React</span></li>
        <li><span class="tag">JavaScript</span></li>
        <li><span class="tag">Vite</span></li>
        <li><span class="tag">CSS Modules</span></li>
      </ul>
    </header>

    <div class="project__callout project__callout--demo" data-reveal>
      <p>
        <strong><?= t('Showcase demo', 'Demostración') ?>.</strong>
        <?= t(
          'A small project built for this portfolio to demonstrate the React / modern-JS line of the CV. Not a prior client deliverable.',
          'Un proyecto pequeño construido para este portafolio para demostrar la línea de React / JS moderno del CV. No es un entregable previo para un cliente.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <div class="stack" data-reveal>
        <h2><?= t('The idea', 'La idea') ?></h2>
        <?= tb(
          '<p>During the ICV work I kept reaching for the same Grid-Code tables — looking up the right voltage band for a given level and locality. This demo turns that lookup into a tiny client-side app: type a locality or pick a voltage level, see the applicable limits immediately.</p>',
          '<p>Durante el trabajo del ICV acudía una y otra vez a las mismas tablas del Código de Red — buscando la banda de voltaje correcta para un nivel y localidad dados. Esta demo convierte esa consulta en una pequeña aplicación del lado del cliente: escribe una localidad o elige un nivel de tensión y verás los límites aplicables al instante.</p>'
        ) ?>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Approach', 'Enfoque') ?></h2>
        <ul class="project__highlights">
          <li><?= t('Static React SPA built with Vite; Grid-Code limits ship as a small JSON file so the app works fully offline.', 'SPA estática en React construida con Vite; los límites del Código de Red se distribuyen como un pequeño archivo JSON para que la app funcione completamente sin conexión.') ?></li>
          <li><?= t('Fast client-side search with memoised filtering keyed by locality and voltage level.', 'Búsqueda rápida en el cliente con filtrado memoizado por localidad y nivel de tensión.') ?></li>
          <li><?= t('CSS Modules keep the styling scoped; no UI framework, to keep the bundle tiny.', 'CSS Modules mantienen los estilos aislados; sin framework de UI, para mantener el bundle mínimo.') ?></li>
        </ul>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Component shape', 'Forma del componente') ?></h2>
        <figure class="project__code">
          <figcaption class="project__code__bar">LimitLookup.jsx</figcaption>
<pre><code>export function LimitLookup({ limits }) {
  const [q, setQ] = useState("");
  const [level, setLevel] = useState("all");

  const rows = useMemo(
    () => limits.filter(l =>
      (level === "all" || l.level === level) &&
      l.locality.toLowerCase().includes(q.toLowerCase())),
    [limits, q, level]
  );

  return (
    <>
      <input value={q} onChange={e => setQ(e.target.value)}
             placeholder="Search locality" />
      <select value={level} onChange={e => setLevel(e.target.value)}>
        <option value="all">All levels</option>
        {LEVELS.map(lv => <option key={lv}>{lv}</option>)}
      </select>
      <LimitTable rows={rows} />
    </>
  );
}</code></pre>
        </figure>
      </div>
    </div>

    <div class="project__links" data-reveal>
      <a class="btn btn--secondary" href="/#contact"><?= t('Ask about this project', 'Pregunta sobre este proyecto') ?></a>
      <a class="btn btn--secondary" href="/portfolio/"><?= t('Back to portfolio', 'Volver al portafolio') ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php';
