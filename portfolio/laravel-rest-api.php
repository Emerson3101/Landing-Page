<?php
/**
 * ICV Records API (detail page) — showcase demo.
 *
 * A built-for-portfolio Laravel resource API representing the backend /
 * database-design line of the CV. Demonstrates Laravel, PHP, migrations,
 * request validation, and token auth. Honest about its demo status.
 */

$page_title       = 'ICV Records API — Emerson Plancarte';
$page_description = 'ICV Records API: a small Laravel/PHP REST resource API with migrations, request validation, and token auth for storing ICV voltage-quality evaluation records. Showcase demo.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--demo"><?= t('Showcase demo', 'Demostración') ?></span>
        <?= t('Backend · Laravel', 'Backend · Laravel') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('ICV Records API', 'API de Registros ICV') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'A tiny REST resource API for storing and validating ICV evaluation records.',
          'Una API REST pequeña para almacenar y validar registros de evaluación ICV.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Type', 'Tipo') ?></dt><dd><?= t('Showcase demo', 'Demostración') ?></dd></div>
        <div><dt><?= t('Year', 'Año') ?></dt><dd>2025</dd></div>
        <div><dt><?= t('Category', 'Categoría') ?></dt><dd>Backend</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Laravel</span></li>
        <li><span class="tag">PHP</span></li>
        <li><span class="tag">MySQL</span></li>
        <li><span class="tag">Eloquent</span></li>
        <li><span class="tag">Sanctum</span></li>
      </ul>
    </header>

    <div class="project__callout project__callout--demo" data-reveal>
      <p>
        <strong><?= t('Showcase demo', 'Demostración') ?>.</strong>
        <?= t(
          'A small project built for this portfolio to demonstrate the backend / database-design line of the CV. Not a prior client deliverable.',
          'Un proyecto pequeño construido para este portafolio para demostrar la línea de backend / diseño de bases de datos del CV. No es un entregable previo para un cliente.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <div class="stack" data-reveal>
        <h2><?= t('The idea', 'La idea') ?></h2>
        <?= tb(
          '<p>In the ICV tool the evaluation results lived as files and exports. This demo imagines the next step: a small resource API that stores ICV evaluation records so other systems (and the SPA) can query them. It is scoped deliberately small — a single resource, done properly.</p>',
          '<p>En la herramienta ICV los resultados de la evaluación vivían como archivos y exportaciones. Esta demo imagina el siguiente paso: una pequeña API de recursos que almacena registros de evaluación ICV para que otros sistemas (y la SPA) puedan consultarlos. Está acotada deliberadamente a pequeño — un único recurso, hecho bien.</p>'
        ) ?>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Approach', 'Enfoque') ?></h2>
        <ul class="project__highlights">
          <li><?= t('REST resource controller for ICV evaluation records with index / store / show endpoints.', 'Controlador de recurso REST para registros de evaluación ICV con endpoints index / store / show.') ?></li>
          <li><?= t('Form requests validate input — locality, voltage level, computed index, and a timestamp are required and ranged.', 'Las form requests validan la entrada — localidad, nivel de tensión, índice calculado y marca de tiempo son obligatorios y acotados.') ?></li>
          <li><?= t('Eloquent model + migration with sensible indexes on locality and level for the filtered queries.', 'Modelo Eloquent + migración con índices razonables en localidad y nivel para las consultas filtradas.') ?></li>
          <li><?= t('Laravel Sanctum token auth so only authorized clients can write.', 'Autenticación por token con Laravel Sanctum para que solo clientes autorizados puedan escribir.') ?></li>
        </ul>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Migration and validation', 'Migración y validación') ?></h2>
        <figure class="project__code">
          <figcaption class="project__code__bar">2025_01_01_create_icv_records_table.php</figcaption>
<pre><code>Schema::create('icv_records', function (Blueprint $t) {
    $t->id();
    $t->string('locality');
    $t->string('voltage_level');        // e.g. "230 kV", "400 kV"
    $t->decimal('index_value', 5, 2);   // 0.00 – 100.00
    $t->timestamp('evaluated_at');
    $t->timestamps();

    $t->index(['locality', 'voltage_level']);
});</code></pre>
        </figure>
        <figure class="project__code">
          <figcaption class="project__code__bar">StoreIcvRecordRequest.php</figcaption>
<pre><code>public function rules(): array
{
    return [
        'locality'      => ['required', 'string', 'max:120'],
        'voltage_level' => ['required', 'string', 'max:16'],
        'index_value'   => ['required', 'numeric', 'between:0,100'],
        'evaluated_at'  => ['required', 'date'],
    ];
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
