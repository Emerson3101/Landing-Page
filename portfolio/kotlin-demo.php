<?php
/**
 * Grid Code Pocket (detail page) — showcase demo.
 *
 * A built-for-portfolio Android demo representing the mobile /
 * Kotlin line of the CV. Demonstrates Jetpack Compose, MVVM, and a
 * small piece of domain logic tuned to Emerson’s voltage-quality
 * work. Honest about its demo status.
 */

$page_title       = 'Grid Code Pocket — Emerson Plancarte';
$page_description = 'Grid Code Pocket: a small Android demo in Kotlin with Jetpack Compose and MVVM that looks up Grid-Code voltage limits and computes a tiny ICV score. Showcase demo from Emerson Plancarte’s portfolio.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--demo"><?= t('Showcase demo', 'Demostración') ?></span>
        <?= t('Mobile · Kotlin', 'Móvil · Kotlin') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('Grid Code Pocket', 'Código de Red de Bolsillo') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'A small Android app that looks up Grid-Code voltage limits and computes a tiny ICV score.',
          'Una pequeña app de Android que consulta los límites de voltaje del Código de Red y calcula un pequeño ICV.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Type', 'Tipo') ?></dt><dd><?= t('Showcase demo', 'Demostración') ?></dd></div>
        <div><dt><?= t('Year', 'Año') ?></dt><dd>2025</dd></div>
        <div><dt><?= t('Category', 'Categoría') ?></dt><dd>Mobile</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Kotlin</span></li>
        <li><span class="tag">Android</span></li>
        <li><span class="tag">Jetpack Compose</span></li>
        <li><span class="tag">MVVM</span></li>
        <li><span class="tag">Coroutines</span></li>
      </ul>
    </header>

    <div class="project__callout project__callout--demo" data-reveal>
      <p>
        <strong><?= t('Showcase demo', 'Demostración') ?>.</strong>
        <?= t(
          'A small project built for this portfolio to demonstrate the mobile / Kotlin line of the CV. Not a prior client deliverable.',
          'Un proyecto pequeño construido para este portafolio para demostrar la línea móvil / Kotlin del CV. No es un entregable previo para un cliente.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <div class="stack" data-reveal>
        <h2><?= t('The idea', 'La idea') ?></h2>
        <?= tb(
          '<p>The ICV and Grid-Code Explorer demos live on the desktop. This one is the pocket version: a small Android app that puts the same voltage-limit lookup and a tiny ICV score in your hand, so a field walk-down doesn’t need a laptop.</p>',
          '<p>Las demos de ICV y Grid Code Explorer viven en el escritorio. Esta es la versión de bolsillo: una pequeña app de Android que pone la misma consulta de límites de voltaje y un pequeño ICV en la mano, para que un recorrido de campo no necesite un portátil.</p>'
        ) ?>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Approach', 'Enfoque') ?></h2>
        <ul class="project__highlights">
          <li><?= t('UI in Jetpack Compose with a single screen: a locality field, a voltage-level picker, and a result card.', 'UI en Jetpack Compose con una sola pantalla: un campo de localidad, un selector de nivel de tensión y una tarjeta de resultado.') ?></li>
          <li><?= t('MVVM with a StateFlow-holding ViewModel — domain logic is pure Kotlin and unit-testable without the UI.', 'MVVM con un ViewModel que mantiene un StateFlow — la lógica de dominio es Kotlin puro y unit-testeable sin la UI.') ?></li>
          <li><?= t('Grid-Code limits ship as a small JSON asset parsed once at start-up, so lookups are instant and offline.', 'Los límites del Código de Red se distribuyen como un pequeño asset JSON parseado una vez al arranque, para que las consultas sean instantáneas y sin conexión.') ?></li>
          <li><?= t('Coroutines on Dispatchers.Default for any non-trivial maths, keeping the Compose thread free.', 'Coroutines en Dispatchers.Default para cualquier cálculo no trivial, manteniendo libre el hilo de Compose.') ?></li>
        </ul>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('ViewModel shape', 'Forma del ViewModel') ?></h2>
        <?= tb(
          '<p>The ViewModel owns a tiny UI state and exposes it as a StateFlow. The ICV maths below is a faithful miniature of the per-band accounting used in the real tool — illustrative, not the production code.</p>',
          '<p>El ViewModel posee un pequeño estado de UI y lo expone como un StateFlow. El cálculo del ICV de abajo es una miniatura fiel del cómputo por banda usado en la herramienta real — ilustrativo, no el código de producción.</p>'
        ) ?>
        <figure class="project__code">
          <figcaption class="project__code__bar">IcvViewModel.kt</figcaption>
<pre><code>data class UiState(
    val locality: String = "",
    val level: String = "",
    val index: Double = 0.0,
)

class IcvViewModel : ViewModel() {
    private val _state = MutableStateFlow(UiState())
    val state: StateFlow<UiState> = _state

    fun evaluate(locality: String, level: String) = viewModelScope.launch {
        val band = repo.bandFor(locality, level) ?: return@launch
        val idx = withContext(Dispatchers.Default) { icv(band) }
        _state.update { it.copy(locality = locality, level = level, index = idx) }
    }
}</code></pre>
        </figure>
        <figure class="project__code">
          <figcaption class="project__code__bar">Icv.kt</figcaption>
<pre><code>// Severity-weighted roll-up of in-band vs. out-of-band samples.
fun icv(samples: List<Float>, band: Band): Double {
    if (samples.isEmpty()) return 100.0
    val inBand = samples.count { it in band.min..band.max }
    val compliance = inBand.toDouble() / samples.size
    val penalty = samples
        .filter { it !in band.min..band.max }
        .sumOf { band.severityOf(it) } / samples.size
    return (100 * compliance - 100 * penalty).coerceAtLeast(0.0)
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
