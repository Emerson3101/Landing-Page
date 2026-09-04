<?php
/**
 * ICV — Voltage Quality Evaluation Tool (detail page).
 *
 * The lead real project from the CV: automated Índice de Calidad de Voltaje
 * evaluation against Mexico's Código de Red at CFE ZOTGM. The most detailed
 * of the portfolio pages. Stack tags are proper nouns / code names and are
 * not translated; all prose is bilingual via t()/tb().
 *
 * No proprietary CFE data is reproduced here — the code excerpt is an
 * illustrative sketch of the algorithm shape, not the production source.
 */

$page_title       = 'ICV — Voltage Quality Evaluation Tool — Emerson Plancarte';
$page_description = 'The ICV voltage-quality evaluation tool: a .NET/C#/PHP web app that automated Mexico Grid-Code voltage-quality indexing across 50+ CFE transmission localities, cutting analysis time by over 80%.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--real"><?= t('Real project', 'Proyecto real') ?></span>
        <?= t('Web · CFE ZOTGM', 'Web · CFE ZOTGM') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('ICV — Voltage Quality Evaluation Tool', 'ICV — Herramienta de Evaluación de Calidad de Voltaje') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Automated evaluation of the Voltage Quality Index (ICV) under Mexico’s Grid Code across 50+ transmission localities.',
          'Evaluación automatizada del Índice de Calidad de Voltaje (ICV) conforme al Código de Red en más de 50 localidades de transmisión.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Role', 'Rol') ?></dt><dd><?= t('Designer & lead developer', 'Diseñador y desarrollador líder') ?></dd></div>
        <div><dt><?= t('Period', 'Periodo') ?></dt><dd>2024 — 2026</dd></div>
        <div><dt><?= t('Organization', 'Organización') ?></dt><dd>CFE — ZOTGM</dd></div>
        <div><dt><?= t('Scope', 'Alcance') ?></dt><dd>50+ <?= t('localities', 'localidades') ?></dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">.NET Framework 3.5</span></li>
        <li><span class="tag">C#</span></li>
        <li><span class="tag">PHP</span></li>
        <li><span class="tag">JavaScript</span></li>
        <li><span class="tag">jQuery</span></li>
        <li><span class="tag">Bootstrap</span></li>
        <li><span class="tag">AJAX</span></li>
        <li><span class="tag">PI System (OSIsoft)</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Real professional project', 'Proyecto profesional real') ?>.</strong>
        <?= t(
          'Built and deployed during my residency at CFE’s Guerrero–Morelos Transmission Zone (ZOTGM).',
          'Construido y desplegado durante mi residencia en la Zona de Transmisión Guerrero–Morelos (ZOTGM) de CFE.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <div class="stack" data-reveal>
        <h2><?= t('The problem', 'El problema') ?></h2>
        <?= tb(
          '<p>Evaluating voltage quality across a transmission zone meant analysts pulled historical records from the OSIsoft PI System, lined them up against the limits in Mexico’s <em>Código de Red</em>, and worked through each locality and voltage level by hand. Done across 50+ localities, that was weeks of repetitive, error-prone work every reporting cycle — and the conclusions were hard to audit because the calculation steps lived mostly in spreadsheets.</p>',
          '<p>Evaluar la calidad de voltaje en una zona de transmisión implicaba que los analistas extrajeran registros históricos del sistema PI de OSIsoft, los compararan contra los límites del <em>Código de Red</em> de México y recorrieran cada localidad y nivel de tensión a mano. En más de 50 localidades, eso eran semanas de trabajo repetitivo y propenso a errores en cada ciclo de reporte — y las conclusiones eran difíciles de auditar porque los pasos de cálculo vivían principalmente en hojas de cálculo.</p>'
        ) ?>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('What I built', 'Lo que construí') ?></h2>
        <?= tb(
          '<p>A web tool that pulls historical voltage data from PI, evaluates it against the Grid Code automatically, and exposes the results for manual validation and reporting. The architecture was a pragmatic mix of what the zone already ran: a .NET Framework 3.5 / C# backend doing the heavy computation and PI integration, a PHP layer for the web surface, and JavaScript + jQuery + Bootstrap + AJAX on the front for a responsive, no-reload experience.</p>',
          '<p>Una herramienta web que obtiene los datos históricos de voltaje desde PI, los evalúa contra el Código de Red de forma automatizada y expone los resultados para validación manual y reporte. La arquitectura fue una mezcla pragmática de lo que la zona ya operaba: un backend en .NET Framework 3.5 / C# hacía el cómputo pesado y la integración con PI, una capa en PHP para la superficie web, y JavaScript + jQuery + Bootstrap + AJAX en el frente para una experiencia responsiva y sin recargas.</p>'
        ) ?>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Key features', 'Características clave') ?></h2>
        <ul class="project__highlights">
          <li><?= t('Automatic ICV calculation, both global and per voltage level, against Grid-Code limits.', 'Cálculo automático del ICV, global y por nivel de tensión, contra los límites del Código de Red.') ?></li>
          <li><?= t('Manual validation workflow for flagged infractions, so an analyst confirms or rejects each event before it enters the index.', 'Flujo de validación manual para las infracciones detectadas, de modo que un analista confirma o rechaza cada evento antes de que entre al índice.') ?></li>
          <li><?= t('Results export and automatic report generation for audit-ready deliverables.', 'Exportación de resultados y generación automática de reportes para entregables listos para auditoría.') ?></li>
          <li><?= t('Historical data integration from the OSIsoft PI System instead of manual data entry.', 'Integración de datos históricos desde el sistema PI de OSIsoft en lugar de captura manual.') ?></li>
        </ul>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('How the index is computed', 'Cómo se calcula el índice') ?></h2>
        <?= tb(
          '<p>The ICV aggregates, per locality and voltage level, the proportion of time the recorded voltage stays within the Grid-Code band. Infractions are classified by severity and weighted, so the index reflects not just whether limits were breached but how often and how far. The sketch below shows the shape of the per-band accounting — illustrative, not the production source.</p>',
          '<p>El ICV agrega, por localidad y nivel de tensión, la proporción de tiempo en que el voltaje registrado se mantiene dentro de la banda del Código de Red. Las infracciones se clasifican por severidad y se ponderan, de modo que el índice refleja no solo si se rebasaron los límites, sino con qué frecuencia y en qué magnitud. El bosquejo de abajo muestra la forma del cómputo por banda — ilustrativo, no el código de producción.</p>'
        ) ?>

        <figure class="project__code">
          <figcaption class="project__code__bar">ICVCalculator.cs — <?= t('illustrative', 'ilustrativo') ?></figcaption>
<pre><code>// Per-band tally of in-band vs. out-of-band samples, then
// a severity-weighted roll-up into a 0–100 index.
double IndexForBand(IEnumerable<Sample> samples, Band band)
{
    int total = 0, inBand = 0;
    double weightedFault = 0;

    foreach (var s in samples)
    {
        total++;
        if (s.Voltage < band.Min || s.Voltage > band.Max)
            weightedFault += SeverityWeight(s, band);
        else
            inBand++;
    }
    if (total == 0) return 100;          // no data → treat as compliant
    double compliance = (double)inBand / total;
    double penalty     = weightedFault / total;
    return Math.Max(0, 100 * compliance - 100 * penalty);
}</code></pre>
        </figure>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Outcome', 'Resultado') ?></h2>
        <?= tb(
          '<p>The tool cut voltage-quality analysis time by more than 80%, replacing weeks of manual spreadsheet work with minutes of automated evaluation plus targeted human validation. It standardised how the zone reports ICV across localities, made the calculation auditable, and became the regular tool for Grid-Code compliance reporting during my residency.</p>',
          '<p>La herramienta redujo el tiempo de análisis de calidad de voltaje en más de un 80%, reemplazando semanas de trabajo manual en hojas de cálculo con minutos de evaluación automatizada más validación humana enfocada. Estandarizó la forma en que la zona reporta el ICV entre localidades, hizo el cálculo auditable y se convirtió en la herramienta habitual para el reporte de cumplimiento del Código de Red durante mi residencia.</p>'
        ) ?>

        <ul class="stats" <?= lang_attr('Headline outcomes', 'Resultados destacados', 'aria-label') ?>>
          <li class="stat">
            <span class="stat__value">><span data-count-to="80">80</span><em>%</em></span>
            <span class="stat__label"><?= t('Faster analysis', 'Análisis más rápido') ?></span>
          </li>
          <li class="stat">
            <span class="stat__value">50<em>+</em></span>
            <span class="stat__label"><?= t('Localities covered', 'Localidades cubiertas') ?></span>
          </li>
          <li class="stat">
            <span class="stat__value">><span data-count-to="99">99</span><em>%</em></span>
            <span class="stat__label"><?= t('System availability', 'Disponibilidad del sistema') ?></span>
          </li>
        </ul>
      </div>
    </div>

    <div class="project__links" data-reveal>
      <a class="btn btn--primary" href="/#contact"><?= t('Ask about this project', 'Pregunta sobre este proyecto') ?></a>
      <a class="btn btn--secondary" href="/portfolio/"><?= t('Back to portfolio', 'Volver al portafolio') ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php';
