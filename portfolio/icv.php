<?php
/**
 * ICV & Cargabilidad — Electrical Grid Telemetry Suite (Case Study)
 *
 * Source: C:\xampp\htdocs\ICVCargaUpdate & CFE ZOTGM Engineering Residency.
 * Automated Índice de Calidad de Voltaje (ICV) per Mexico's Código de Red
 * and transmission line load capacity (Cargabilidad) monitoring suite.
 */

$page_title       = 'ICV & Cargabilidad — Electrical Grid Telemetry Suite — Emerson Plancarte';
$page_description = 'Enterprise telemetry and compliance platform at CFE evaluating the Voltage Quality Index (ICV) and line load capacity across 50+ localities, cutting analysis time by over 80%.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--real"><?= t('Real project', 'Proyecto real') ?></span>
        <?= t('Industrial Telemetry & Grid Analytics · CFE ZOTGM', 'Telemetría Industrial y Analítica de Red · CFE ZOTGM') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('ICV & Cargabilidad — Electrical Grid Telemetry Suite', 'ICV y Cargabilidad — Suite de Telemetría de Red Eléctrica') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Automated Grid-Code voltage quality indexing and transmission line load capacity monitoring across 50+ localities in Guerrero and Morelos.',
          'Indexación automatizada de calidad de voltaje conforme al Código de Red y monitoreo de cargabilidad de líneas en más de 50 localidades de Guerrero y Morelos.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Author & Lead Developer', 'Autor y Desarrollador Líder') ?></dt><dd>Emerson Salvador Plancarte Cerecedo</dd></div>
        <div><dt><?= t('Period', 'Periodo') ?></dt><dd>2024 — 2026</dd></div>
        <div><dt><?= t('Organization', 'Organización') ?></dt><dd>Comisión Federal de Electricidad (CFE) — ZOTGM</dd></div>
        <div><dt><?= t('Operational Scope', 'Alcance Operativo') ?></dt><dd>50+ <?= t('transmission substations & lines', 'subestaciones y líneas de transmisión') ?></dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">.NET Framework 3.5</span></li>
        <li><span class="tag">C# Core Engine</span></li>
        <li><span class="tag">PHP 8</span></li>
        <li><span class="tag">JavaScript (ES6+)</span></li>
        <li><span class="tag">Chart.js</span></li>
        <li><span class="tag">OSIsoft PI System</span></li>
        <li><span class="tag">MySQL</span></li>
        <li><span class="tag">JSON Batching</span></li>
        <li><span class="tag">Código de Red Compliance</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Real enterprise industrial deployment', 'Despliegue industrial empresarial real') ?>:</strong>
        <?= t(
          'Built, tested, and actively operated within the Guerrero–Morelos Transmission Zone (ZOTGM) of CFE. Designed to replace error-prone manual spreadsheets with an audit-ready, high-throughput computational pipeline.',
          'Construido, probado y operado activamente en la Zona de Transmisión Guerrero–Morelos (ZOTGM) de CFE. Diseñado para reemplazar hojas de cálculo manuales propensas a error con un pipeline de cálculo auditado y de alto rendimiento.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('Architecture & data flow pipeline', 'Arquitectura y flujo de datos') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. INGEST</span>
            <h3 class="pipeline-step__title">OSIsoft PI Ingestion</h3>
            <p class="pipeline-step__desc"><?= t('Automated PI DataLink extraction polling continuous minute-by-minute voltage telemetry across 50+ transmission substations.', 'Extracción automatizada con PI DataLink consultando telemetría de voltaje minuto a minuto en más de 50 subestaciones.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. COMPUTE</span>
            <h3 class="pipeline-step__title">C# &amp; .NET Math Core</h3>
            <p class="pipeline-step__desc"><?= t('Batch processing engine evaluating 400 kV, 230 kV, and 115 kV limits, computing duration intervals and deviation magnitude.', 'Motor de procesamiento por lotes evaluando límites de 400 kV, 230 kV y 115 kV, calculando intervalos de duración y magnitud de desviación.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. AUDIT</span>
            <h3 class="pipeline-step__title">PHP &amp; Hash Index Cache</h3>
            <p class="pipeline-step__desc"><?= t('In-memory hash index filtering and audit verification. Cross-references events with analyst annotations and regulatory exemption flags.', 'Filtrado con índice hash en memoria y validación de auditoría. Cruza eventos con notas de analistas y banderas de exención regulatoria.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. PRESENT</span>
            <h3 class="pipeline-step__title">Operations Dashboard</h3>
            <p class="pipeline-step__desc"><?= t('Interactive executive view with semaphoric alarms, historical trend curves, and instant Excel / CSV regulatory deliverables.', 'Vista ejecutiva interactiva con semáforos de alarma, curvas de tendencia histórica y entregables regulatorios instantáneos en Excel / CSV.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement -->
      <div class="stack" data-reveal>
        <h2><?= t('The operational challenge', 'El desafío operativo') ?></h2>
        <?= tb(
          '<p>In national electrical transmission networks, compliance with Mexico’s statutory <em>Código de Red</em> (Grid Code) is legally enforced to safeguard grid integrity and prevent cascading blackout events. The regulatory body mandates precise accounting of all voltage excursions outside nominal tolerances:</p>
          <ul>
            <li><strong>400 kV Transmission Lines:</strong> Strict nominal boundaries with tight statutory variance tolerances.</li>
            <li><strong>230 kV &amp; 115 kV Regional Links:</strong> Dynamic limit tracking depending on local bus topology and seasonal load.</li>
            <li><strong>The Legacy Bottleneck:</strong> Transmission engineers previously executed manual Excel DataLink pulls for dozens of nodes. The sheer volume of samples caused frequent spreadsheet crashes, took weeks of repetitive copy-pasting, and made official audits painful due to the lack of tamper-evident calculation history.</li>
          </ul>',
          '<p>En las redes nacionales de transmisión eléctrica, el cumplimiento del <em>Código de Red</em> es de carácter legal y obligatorio para salvaguardar la estabilidad del sistema y prevenir apagones en cascada. La normativa exige un registro exacto de cualquier excursión de voltaje fuera de tolerancia:</p>
          <ul>
            <li><strong>Líneas de Transmisión de 400 kV:</strong> Estrictos límites nominales con reducidas tolerancias de varianza.</li>
            <li><strong>Enlaces Regionales de 230 kV y 115 kV:</strong> Monitoreo dinámico de límites según topología de bus y carga estacional.</li>
            <li><strong>El Cuello de Botella Previo:</strong> Los ingenieros realizaban extracciones manuales en hojas de cálculo para decenas de nodos. El enorme volumen de muestras provocaba bloqueos del software, semanas de copiado y pegado repetitivo, y dificultaba auditorías oficiales por falta de trazabilidad estandarizada.</li>
          </ul>'
        ) ?>
      </div>

      <!-- Real Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('Core algorithm implementation', 'Implementación del algoritmo central') ?></h2>
        <p>
          <?= t(
            'The following production snippet from `icv/procesar.php` demonstrates the high-throughput memory scaling, hash indexing of statutory limits, and contiguous excursion interval detection authored for CFE ZOTGM:',
            'El siguiente fragmento en producción de `icv/procesar.php` demuestra la escala de memoria intensiva, indexación hash de límites normativos y detección de intervalos continuos de infracción desarrollado para CFE ZOTGM:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>icv/procesar.php — CFE ZOTGM Production Backend</span>
          </div>
          <pre><code><span class="code-cm">/**
 * Procesamiento de Datos ICV - ZOTGM
 * @author Emerson Salvador Plancarte Cerecedo
 * @description Backend para procesamiento de datos de infracciones y cálculos ICV
 */</span>
<span class="code-fn">date_default_timezone_set</span>(<span class="code-str">'America/Mexico_City'</span>);

<span class="code-kw">if</span> ($_SERVER[<span class="code-str">"REQUEST_METHOD"</span>] === <span class="code-str">"POST"</span>) {
    <span class="code-cm">// Asignación de recursos para analítica intensiva sobre series de tiempo masivas</span>
    <span class="code-fn">set_time_limit</span>(<span class="code-num">0</span>);
    <span class="code-fn">ini_set</span>(<span class="code-str">'memory_limit'</span>, <span class="code-str">'10G'</span>);

    <span class="code-var">$resultados</span>   = <span class="code-fn">json_decode</span>(<span class="code-fn">file_get_contents</span>(<span class="code-str">"resultados.json"</span>), <span class="code-kw">true</span>);
    <span class="code-var">$tags</span>         = <span class="code-fn">json_decode</span>(<span class="code-fn">file_get_contents</span>(<span class="code-str">"tags.json"</span>), <span class="code-kw">true</span>);
    <span class="code-var">$evaluaciones</span> = <span class="code-fn">file_exists</span>(<span class="code-str">"eval.json"</span>) ? <span class="code-fn">json_decode</span>(<span class="code-fn">file_get_contents</span>(<span class="code-str">"eval.json"</span>), <span class="code-kw">true</span>) : [];

    <span class="code-cm">// Optimización de acceso: Índice hash O(1) para límites de Código de Red</span>
    <span class="code-var">$limites</span> = [];
    <span class="code-kw">foreach</span> (<span class="code-var">$tags</span> <span class="code-kw">as</span> <span class="code-var">$tag</span>) {
        <span class="code-kw">if</span> (<span class="code-fn">isset</span>(<span class="code-var">$tag</span>[<span class="code-str">"tag"</span>], <span class="code-var">$tag</span>[<span class="code-str">"limiteInferior"</span>], <span class="code-var">$tag</span>[<span class="code-str">"limiteSuperior"</span>])) {
            <span class="code-var">$limites</span>[<span class="code-var">$tag</span>[<span class="code-str">"tag"</span>]] = [
                <span class="code-str">"limiteInferior"</span> => (<span class="code-type">float</span>)<span class="code-var">$tag</span>[<span class="code-str">"limiteInferior"</span>],
                <span class="code-str">"limiteSuperior"</span> => (<span class="code-type">float</span>)<span class="code-var">$tag</span>[<span class="code-str">"limiteSuperior"</span>],
                <span class="code-str">"nivel_tension"</span>  => (<span class="code-type">int</span>)<span class="code-var">$tag</span>[<span class="code-str">"nivel_tension"</span>]
            ];
        }
    }

    <span class="code-cm">// Detección de intervalos contiguos de infracción</span>
    <span class="code-var">$filtrados</span> = [];
    <span class="code-var">$vistos</span>    = [];
    <span class="code-var">$total</span>     = <span class="code-fn">count</span>(<span class="code-var">$resultados</span>);

    <span class="code-kw">for</span> (<span class="code-var">$i</span> = <span class="code-num">0</span>; <span class="code-var">$i</span> &lt; <span class="code-var">$total</span>; <span class="code-var">$i</span>++) {
        <span class="code-var">$item</span>      = <span class="code-var">$resultados</span>[<span class="code-var">$i</span>];
        <span class="code-var">$tag</span>       = <span class="code-var">$item</span>[<span class="code-str">"tag"</span>];
        <span class="code-var">$valor</span>     = (<span class="code-type">float</span>)<span class="code-var">$item</span>[<span class="code-str">"value"</span>];
        <span class="code-var">$timestamp</span> = <span class="code-var">$item</span>[<span class="code-str">"timestamp"</span>];

        <span class="code-kw">if</span> (!<span class="code-fn">isset</span>(<span class="code-var">$limites</span>[<span class="code-var">$tag</span>])) <span class="code-kw">continue</span>;

        <span class="code-var">$limInf</span> = <span class="code-var">$limites</span>[<span class="code-var">$tag</span>][<span class="code-str">"limiteInferior"</span>];
        <span class="code-var">$limSup</span> = <span class="code-var">$limites</span>[<span class="code-var">$tag</span>][<span class="code-str">"limiteSuperior"</span>];

        <span class="code-kw">if</span> (<span class="code-var">$valor</span> &lt; <span class="code-var">$limInf</span> || <span class="code-var">$valor</span> &gt; <span class="code-var">$limSup</span>) {
            <span class="code-cm">// Agrupación del evento con banderas de auditoría (cuenta / no cuenta)</span>
            <span class="code-var">$cuenta</span>      = <span class="code-var">$evaluaciones</span>[<span class="code-var">$tag</span>][<span class="code-var">$timestamp</span>][<span class="code-str">"cuenta"</span>] ?? <span class="code-kw">false</span>;
            <span class="code-var">$descripcion</span> = <span class="code-var">$evaluaciones</span>[<span class="code-var">$tag</span>][<span class="code-var">$timestamp</span>][<span class="code-str">"descripcion"</span>] ?? <span class="code-str">""</span>;
            <span class="code-cm">// Localiza timestamp de normalización subsecuente</span>
            <span class="code-comment">/* ... calculo de delta en segundos y clasificación según Código de Red ... */</span>
        }
    }
}</code></pre>
        </figure>
      </div>

      <!-- Technical Specifications Table -->
      <div class="stack" data-reveal>
        <h2><?= t('Technical registry & deployment specifications', 'Registro técnico y especificaciones de despliegue') ?></h2>
        <div class="project__spec-table-wrap">
          <table class="project__spec-table">
            <thead>
              <tr>
                <th><?= t('Component', 'Componente') ?></th>
                <th><?= t('Technology / Version', 'Tecnología / Versión') ?></th>
                <th><?= t('Responsibility / Operational Role', 'Responsabilidad / Rol Operativo') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>PI Bridge Core</strong></td>
                <td><code>.NET 3.5 / C# (ICVDatalink.exe)</code></td>
                <td><?= t('Enterprise binary interfacing with OSIsoft PI SDK; performs headless temporal series extraction.', 'Binario empresarial enlazado con OSIsoft PI SDK; realiza extracción headless de series temporales.') ?></td>
              </tr>
              <tr>
                <td><strong>Batch Analytics Engine</strong></td>
                <td><code>PHP 8.2 with 10GB In-Memory Ceiling</code></td>
                <td><?= t('Vector parsing, contiguous infraction consolidation, and regulatory scoring without database write contention.', 'Parsing vectorial, consolidación contigua de infracciones y cálculo normativo sin contención de base de datos.') ?></td>
              </tr>
              <tr>
                <td><strong>Audit Persistence</strong></td>
                <td><code>JSON Flat-file Store + MySQL Audit Logs</code></td>
                <td><?= t('Dual-tier caching preserving analyst overrides, incident justification notes, and immutable evaluation logs.', 'Almacenamiento de doble nivel con anotaciones de analistas, justificaciones operativas y logs inmutables.') ?></td>
              </tr>
              <tr>
                <td><strong>Executive UI</strong></td>
                <td><code>JavaScript, Bootstrap, Chart.js, HTML5</code></td>
                <td><?= t('Client-side time-window filtering, multi-bus voltage comparison charts, and asynchronous Excel export.', 'Filtros en cliente por ventana de tiempo, gráficas comparativas de voltaje y exportación asíncrona a Excel.') ?></td>
              </tr>
              <tr>
                <td><strong>Grid Standards</strong></td>
                <td><code>Código de Red (CRE / CENACE)</code></td>
                <td><?= t('Automated validation rules for 400 kV (±5%), 230 kV (±5%), and 115 kV (±5% nominal tolerance limits).', 'Reglas automáticas de validación para 400 kV (±5%), 230 kV (±5%) y 115 kV (±5% de tolerancia nominal).') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Key Challenges & Engineering Innovations -->
      <div class="stack" data-reveal>
        <h2><?= t('Engineering challenges & solutions', 'Desafíos de ingeniería y soluciones') ?></h2>
        <?= tb(
          '<div class="grid grid--2">
            <div class="card">
              <h3>Memory Saturation with Multi-Month Datasets</h3>
              <p>When extracting 1-minute interval data for 50+ nodes over months, the raw JSON payload easily exceeded 4 GB. Standard PHP scripts terminated with fatal memory exhaustion. I restructured the engine into a streaming batch evaluator that maintains pre-allocated array sizes, relies on integer key mappings, and performs lookups via hash indices with direct memory garbage sweeps.</p>
            </div>
            <div class="card">
              <h3>Transient Fluctuations vs. Sustained Infractions</h3>
              <p>Spurious voltage spikes lasting milliseconds must be segregated from sustained sub-nominal grid depressions. The algorithm was engineered to aggregate contiguous minute-stamps into discrete infraction periods with start timestamps, recovery timestamps, total excursion minutes, and severity area under the curve.</p>
            </div>
            <div class="card">
              <h3>Human-in-the-Loop Audit Accountability</h3>
              <p>Under regulatory guidelines, certain grid excursions (such as planned transmission maintenance or severe weather contingencies) are eligible for official exemption. The UI allows certified grid analysts to flag specific events with justification descriptions while maintaining an unalterable digital log.</p>
            </div>
            <div class="card">
              <h3>Zero External Dependency Frontend</h3>
              <p>CFE control room workstations operate on locked-down intranet networks without internet access. The entire web client was built with strictly local, zero-CDN assets, ensuring 100% operational autonomy and zero external telemetry leaks.</p>
            </div>
          </div>',
          '<div class="grid grid--2">
            <div class="card">
              <h3>Saturación de Memoria con Datos Multimensuales</h3>
              <p>Al extraer datos con intervalo de 1 minuto para más de 50 nodos durante varios meses, el JSON bruto superaba fácilmente 4 GB. Los scripts estándar de PHP colapsaban por agotamiento de memoria. Reestructuré el motor con un evaluador por lotes continuos que mantiene tamaños prealocados, usa índices enteros e implementa barridos de memoria optimizados.</p>
            </div>
            <div class="card">
              <h3>Fluctuaciones Transitorias vs. Infracciones Sostenidas</h3>
              <p>Picos espurios de milisegundos deben distinguirse de depresiones sostenidas de voltaje en la red. El algoritmo se programó para agrupar marcas de tiempo consecutivas en periodos discretos de infracción, calculando inicio, normalización, minutos totales y severidad del área bajo la curva.</p>
            </div>
            <div class="card">
              <h3>Trazabilidad de Auditoría con Intervención Humana</h3>
              <p>Bajo la normativa regulatoria, ciertas excursiones (mantenimiento programado o contingencias climáticas) califican para exención oficial. La interfaz permite a analistas certificados marcar eventos con descripciones justificativas preservando un registro digital inalterable.</p>
            </div>
            <div class="card">
              <h3>Frontend sin Dependencias Externas</h3>
              <p>Las computadoras en salas de control de CFE operan en intranets cerradas sin salida a internet. Toda la interfaz web se construyó exclusivamente con librerías locales empaquetadas sin CDN, garantizando 100% de autonomía operativa y cero filtraciones.</p>
            </div>
          </div>'
        ) ?>
      </div>

      <!-- Verified Outcomes -->
      <div class="stack" data-reveal>
        <h2><?= t('Impact and verified outcomes', 'Impacto y resultados verificados') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('>80% Reduction in Analysis Time:', 'Reducción >80% en tiempo de análisis:') ?></strong> <?= t('Turned multi-week manual spreadsheet calculations into automated, push-button evaluations completed in minutes.', 'Transformó cálculos manuales de varias semanas en hojas de cálculo en evaluaciones automáticas ejecutadas en minutos.') ?></li>
          <li><strong><?= t('50+ Transmission Localities Covered:', 'Más de 50 localidades de transmisión cubiertas:') ?></strong> <?= t('Full regional monitoring across Guerrero and Morelos substations.', 'Monitoreo regional integral en subestaciones de Guerrero y Morelos.') ?></li>
          <li><strong><?= t('Audit-Ready Transparency:', 'Transparencia lista para auditorías:') ?></strong> <?= t('Standardized calculation algorithms replaced ad-hoc spreadsheet macros, creating verifiable records for regulatory oversight.', 'Estandarizó algoritmos de cálculo reemplazando macros dispersas, generando registros auditables ante reguladores.') ?></li>
          <li><strong><?= t('>99% System Availability:', 'Disponibilidad de sistemas >99%:') ?></strong> <?= t('Sustained continuous operational availability on transmission control center workstations and servers.', 'Mantuvo disponibilidad operativa continua en estaciones de trabajo y servidores de centros de control.') ?></li>
        </ul>
      </div>

      <div class="project__links" data-reveal>
        <a class="btn btn--primary" href="/#contact"><?= t('Ask about this project', 'Pregunta sobre este proyecto') ?></a>
        <a class="btn btn--secondary" href="/portfolio/"><?= t('Back to portfolio', 'Volver al portafolio') ?></a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php';
