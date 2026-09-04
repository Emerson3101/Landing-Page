<?php
/**
 * TecAssist — Virtual Assistant (detail page, pending detail).
 *
 * TecAssist is a real innovation-contest winner (1st place, Smart Cities,
 * Dec 2025), but per Development_Plan.md §11 the stack and scope are to be
 * confirmed with Emerson before the page is written out, so that page stays
 * accurate rather than invented. This page exists so the portfolio card
 * links somewhere honest about that status today.
 */

$page_title       = 'TecAssist — Virtual Assistant — Emerson Plancarte';
$page_description = 'TecAssist, a virtual assistant that won first place in the Smart Cities category of the local Technological Innovation Contest (Dec 2025). Full write-up pending confirmation of stack and scope.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--real"><?= t('Real project', 'Proyecto real') ?></span>
        <?= t('Mobile · Innovation contest', 'Móvil · Concurso de innovación') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('TecAssist — Virtual Assistant', 'TecAssist — Asistente Virtual') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'First place, Technological Innovation Contest — Smart Cities category, December 2025.',
          'Primer lugar, Concurso de Innovación Tecnológica — categoría Ciudades Inteligentes, diciembre de 2025.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Award', 'Reconocimiento') ?></dt><dd><?= t('1st place', '1er lugar') ?></dd></div>
        <div><dt><?= t('Category', 'Categoría') ?></dt><dd><?= t('Smart Cities', 'Ciudades Inteligentes') ?></dd></div>
        <div><dt><?= t('Date', 'Fecha') ?></dt><dd>2025-12</dd></div>
        <div><dt><?= t('Level', 'Nivel') ?></dt><dd><?= t('Local', 'Local') ?></dd></div>
      </dl>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Full write-up pending', 'Descripción completa pendiente') ?>.</strong>
        <?= t(
          'TecAssist is a real award-winning project. Its stack and detailed scope will be added here once confirmed, so the page stays accurate rather than invented.',
          'TecAssist es un proyecto real premiado. Su pila tecnológica y alcance detallado se añadirán aquí una vez confirmados, para que la página sea precisa en lugar de inventada.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal>
      <?= tb(
        '<p>TecAssist earned first place in the Smart Cities category of the local Technological Innovation Contest in December 2025, recognized at the local level. The concept centred on a virtual assistant aimed at a civic context. Once the project’s concrete stack and scope are confirmed, this page will be expanded to match the depth of the ICV case study: problem, what was built, key features, and outcome.</p>',
        '<p>TecAssist obtuvo el primer lugar en la categoría Ciudades Inteligentes del Concurso de Innovación Tecnológica local en diciembre de 2025, reconocido a nivel local. El concepto se centró en un asistente virtual orientado a un contexto cívico. Una vez confirmada la pila tecnológica concreta y el alcance del proyecto, esta página se ampliará para igualar la profundidad del caso de estudio de ICV: problema, lo que se construyó, características clave y resultado.</p>'
      ) ?>
    </div>

    <div class="project__links" data-reveal>
      <a class="btn btn--primary" href="/#contact"><?= t('Ask about this project', 'Pregunta sobre este proyecto') ?></a>
      <a class="btn btn--secondary" href="/portfolio/"><?= t('Back to portfolio', 'Volver al portafolio') ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php';
