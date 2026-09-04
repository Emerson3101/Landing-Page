<?php
/**
 * ESP32 Voltage Dashboard (detail page) — showcase demo.
 *
 * A built-for-portfolio embedded + web demo representing the
 * microcontroller / IoT line of the CV. Demonstrates C/C++ on the
 * ESP32, a tiny WebSocket stream, and a live browser chart with
 * Chart.js. Honest about its demo status.
 */

$page_title       = 'ESP32 Voltage Dashboard — Emerson Plancarte';
$page_description = 'ESP32 Voltage Dashboard: a small embedded demo that streams ADC voltage samples from an ESP32 to a live browser chart over WebSockets. Showcase demo from Emerson Plancarte’s portfolio.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--demo"><?= t('Showcase demo', 'Demostración') ?></span>
        <?= t('Embedded · ESP32', 'Embebido · ESP32') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('ESP32 Voltage Dashboard', 'Tablero de Voltaje ESP32') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'A tiny embedded app that streams live voltage samples from an ESP32 to a browser dashboard.',
          'Una pequeña aplicación embebida que transmite muestras de voltaje en vivo desde un ESP32 a un tablero en el navegador.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Type', 'Tipo') ?></dt><dd><?= t('Showcase demo', 'Demostración') ?></dd></div>
        <div><dt><?= t('Year', 'Año') ?></dt><dd>2025</dd></div>
        <div><dt><?= t('Category', 'Categoría') ?></dt><dd><?= t('Embedded', 'Embebido') ?></dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">C</span></li>
        <li><span class="tag">C++</span></li>
        <li><span class="tag">ESP32</span></li>
        <li><span class="tag">Arduino IDE</span></li>
        <li><span class="tag">WebSockets</span></li>
        <li><span class="tag">Chart.js</span></li>
      </ul>
    </header>

    <div class="project__callout project__callout--demo" data-reveal>
      <p>
        <strong><?= t('Showcase demo', 'Demostración') ?>.</strong>
        <?= t(
          'A small project built for this portfolio to demonstrate the embedded / IoT line of the CV. Not a prior client deliverable.',
          'Un proyecto pequeño construido para este portafolio para demostrar la línea embebida / IoT del CV. No es un entregable previo para un cliente.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <div class="stack" data-reveal>
        <h2><?= t('The idea', 'La idea') ?></h2>
        <?= tb(
          '<p>The ICV work was all historical data, evaluated after the fact. This demo is the other direction: watch a voltage live. An ESP32 samples its ADC, streams the values over a WebSocket, and a browser plots a rolling waveform — a bench-top cousin of the transmission-zone dashboard, small enough to fit on a desk.</p>',
          '<p>El trabajo del ICV era todo con datos históricos, evaluados a posteriori. Esta demo va en el otro sentido: observar un voltaje en vivo. Un ESP32 muestrea su ADC, transmite los valores por un WebSocket y un navegador dibuja una forma de onda continua — un primo de mesa del tablero de la zona de transmisión, lo bastante pequeño para caber en un escritorio.</p>'
        ) ?>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Approach', 'Enfoque') ?></h2>
        <ul class="project__highlights">
          <li><?= t('ESP32 reads an ADC pin on a fixed sampling cadence and pushes each sample as a small JSON frame over a WebSocket server.', 'El ESP32 lee un pin ADC con una cadencia fija de muestreo y envía cada muestra como un pequeño frame JSON sobre un servidor WebSocket.') ?></li>
          <li><?= t('A static browser page keeps a rolling buffer of recent samples and charts them with Chart.js — no build step, no framework.', 'Una página estática del navegador mantiene un buffer rotatorio de muestras recientes y las grafica con Chart.js — sin paso de build, sin framework.') ?></li>
          <li><?= t('Wi-Fi credentials and sampling rate are read from a config header so the firmware stays portable across boards.', 'Las credenciales Wi-Fi y la tasa de muestreo se leen de un header de configuración para que el firmware sea portable entre placas.') ?></li>
          <li><?= t('Sampling cadence and the WebSocket payload are both deliberately small so the heap and send buffer stay stable over long runs.', 'La cadencia de muestreo y el payload del WebSocket son ambos deliberadamente pequeños para que el heap y el buffer de envío se mantengan estables en corridas largas.') ?></li>
        </ul>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Firmware shape', 'Forma del firmware') ?></h2>
        <?= tb(
          '<p>The firmware is one loop: sample, frame, send. The sketch below shows the shape of that loop — illustrative, trimmed to the essential path.</p>',
          '<p>El firmware es un solo ciclo: muestrear, empaquetar, enviar. El sketch de abajo muestra la forma de ese ciclo — ilustrativo, recortado a la ruta esencial.</p>'
        ) ?>
        <figure class="project__code">
          <figcaption class="project__code__bar">voltage_dashboard.ino — <?= t('illustrative', 'ilustrativo') ?></figcaption>
<pre><code>#include <WiFi.h>
#include <WebSocketsServer.h>

WebSocketsServer ws = WebSocketsServer(81);

void loop() {
    ws.cleanupClients();

    // Sample the ADC and frame it as a tiny JSON line.
    uint32_t now = micros();
    int raw = analogRead(ADC_PIN);
    float volts = (raw / 4095.0f) * 3.3f;

    char frame[48];
    snprintf(frame, sizeof(frame),
             "{\"t\":%lu,\"v\":%.3f}", now, volts);

    ws.broadcastTXT(frame);
    delay(SAMPLE_MS);   // SAMPLE_MS is set in config.h
}</code></pre>
        </figure>
      </div>

      <div class="stack" data-reveal>
        <h2><?= t('Browser side', 'Lado del navegador') ?></h2>
        <figure class="project__code">
          <figcaption class="project__code__bar">dashboard.js</figcaption>
<pre><code>const ws = new WebSocket(`ws://${location.hostname}:81`);
const buf = [];

ws.onmessage = (e) => {
  const { t, v } = JSON.parse(e.data);
  buf.push(v);
  if (buf.length > 120) buf.shift();   // rolling window
  chart.data.labels = buf.map((_, i) => i);
  chart.data.datasets[0].data = buf;
  chart.update("none");
};</code></pre>
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
