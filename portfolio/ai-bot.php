<?php
/**
 * LM Studio Discord AI Bot & Control Hub (Case Study)
 *
 * Source: C:\Users\Emerson Plancarte\StudioProjects\AI Bot
 * Python, discord.py, FastAPI, LM Studio, httpx, Uvicorn, AsyncIO.
 */

$page_title       = 'LM Studio Discord AI Bot & Control Hub — Emerson Plancarte';
$page_description = 'Dual-interface AI assistant pairing an asynchronous Discord bot with a FastAPI companion web hub, integrating local LLM inference engines via LM Studio with sliding context memory.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <?= t('AI & Backend Systems · Discord API & Local LLMs', 'Sistemas de IA y Backend · API de Discord y LLMs Locales') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('LM Studio Discord AI Bot & Control Hub', 'Bot de Discord con IA LM Studio y Centro de Control') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Asynchronous Discord bot and companion FastAPI web dashboard integrating locally hosted LLMs with sliding-window conversation memory and Markdown boundary healing.',
          'Bot asíncrono de Discord y panel web en FastAPI que integran LLMs ejecutados localmente con memoria de ventana deslizante y reparación de bloques Markdown.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Core Runtime', 'Entorno') ?></dt><dd>Python 3.11+ / AsyncIO</dd></div>
        <div><dt><?= t('Inference Backend', 'Backend de Inferencia') ?></dt><dd>LM Studio (Local OpenAI-Compatible Server)</dd></div>
        <div><dt><?= t('Gateway Framework', 'Framework Gateway') ?></dt><dd>discord.py 2.3+ (Slash Commands &amp; Threads)</dd></div>
        <div><dt><?= t('Web Hub Framework', 'Panel Web') ?></dt><dd>FastAPI + Uvicorn + Pillow QR</dd></div>
        <div><dt><?= t('Repository', 'Repositorio') ?></dt><dd><a href="https://github.com/Emerson3101/LLMDiscordbot-Playground" target="_blank" rel="noopener">github.com/Emerson3101/LLMDiscordbot-Playground</a></dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Python 3.11+</span></li>
        <li><span class="tag">FastAPI</span></li>
        <li><span class="tag">discord.py 2.3+</span></li>
        <li><span class="tag">LM Studio API</span></li>
        <li><span class="tag">httpx AsyncClient</span></li>
        <li><span class="tag">Uvicorn Server</span></li>
        <li><span class="tag">Sliding Window Memory</span></li>
        <li><span class="tag">Markdown Block Healing</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Local-first, privacy-focused AI', 'IA local con enfoque en privacidad') ?>:</strong>
        <?= t(
          'Lets private Discord communities and local workstations converse with open-weight models (e.g. LLaMA 3, Mistral, DeepSeek) running entirely on local hardware — no cloud subscriptions, and no data leaving the network.',
          'Permite a comunidades privadas de Discord y estaciones de trabajo locales conversar con modelos abiertos (como LLaMA 3, Mistral o DeepSeek) ejecutados por completo en hardware local — sin suscripciones en la nube y sin que los datos salgan de la red.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('Architecture & query dispatch pipeline', 'Arquitectura y flujo de despacho de consultas') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. INGEST</span>
            <h3 class="pipeline-step__title">Discord Gateway Event</h3>
            <p class="pipeline-step__desc"><?= t('Captures slash commands (/chat, /model, /system) and mentions. Dispatches async typing indicator to signal generation.', 'Captura comandos slash (/chat, /model, /system) y menciones. Envía indicador asíncrono de escritura.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. BUFFER</span>
            <h3 class="pipeline-step__title">Sliding-Window Memory</h3>
            <p class="pipeline-step__desc"><?= t('Maintains isolated per-channel context state. Enforces a 16-turn maximum history window while perpetually preserving root system instructions.', 'Mantiene el historial por canal. Limita la memoria a una ventana de 16 turnos preservando el prompt de sistema base.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. INFERENCE</span>
            <h3 class="pipeline-step__title">Async HTTPX Bridge</h3>
            <p class="pipeline-step__desc"><?= t('Non-blocking HTTP client calls LM Studio local OpenAI-compatible endpoint without stalling the Discord heartbeat loop.', 'Cliente HTTP no bloqueante consulta el servidor local de LM Studio sin comprometer el pulso heartbeat de Discord.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. DISPATCH</span>
            <h3 class="pipeline-step__title">Markdown Healing</h3>
            <p class="pipeline-step__desc"><?= t('Chunks outputs below Discord’s 2000-char limit while dynamically closing and reopening markdown syntax code blocks across message fragments.', 'Divide respuestas bajo el límite de 2000 caracteres cerrando y reabriendo bloques de código Markdown automáticamente.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement -->
      <div class="stack" data-reveal>
        <h2><?= t('The technical bottleneck', 'El cuello de botella técnico') ?></h2>
        <?= tb(
          '<p>Integrating high-parameter neural models into a chat platform like Discord involves tricky concurrency challenges:</p>
          <ul>
            <li><strong>Gateway Heartbeat Timeouts:</strong> If a local LLM takes 20–40 seconds to process a large query on consumer GPU hardware, synchronous HTTP calls would freeze the event loop, causing Discord to drop the bot connection with <code>Gateway disconnected</code> errors.</li>
            <li><strong>Context Window Saturation:</strong> Unrestricted multi-turn history on 8GB consumer VRAM quickly exceeds the model’s context limit, triggering out-of-memory crashes.</li>
            <li><strong>Discord 2000-Character Boundary:</strong> Lengthy code explanations exceed Discord’s message cap. Naive string slicing splits formatted code blocks in half, producing ugly, unclosed markdown.</li>
          </ul>',
          '<p>Integrar modelos neuronales de gran tamaño en una plataforma como Discord plantea retos severos de concurrencia:</p>
          <ul>
            <li><strong>Caídas de Heartbeat del Gateway:</strong> Si un modelo local toma 20 a 40 segundos para generar una respuesta compleja en GPUs comerciales, llamadas HTTP síncronas congelarían el event loop, provocando desconexiones del bot.</li>
            <li><strong>Saturación de Memoria VRAM:</strong> Un historial conversacional ilimitado en tarjetas de 8GB desborda rápidamente la ventana de contexto, provocando fallas por falta de memoria.</li>
            <li><strong>Límite de 2000 Caracteres en Discord:</strong> Respuestas extensas con código exceden el límite de Discord. Un corte ingenuo de cadenas fragmenta los bloques de código, arruinando el formateo.'
        ) ?>
      </div>

      <!-- Real Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('Markdown-aware chunking implementation', 'Implementación de partición de mensajes con Markdown') ?></h2>
        <p>
          <?= t(
            'The following production code from `run_bot.py` demonstrates how the bot chunks large responses below Discord’s 2000-character ceiling while preserving and repairing open code block syntax across splits:',
            'El siguiente código en producción de `run_bot.py` demuestra cómo el bot divide respuestas extensas bajo el tope de 2000 caracteres de Discord preservando y reparando bloques de código abiertos:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>run_bot.py — LM Studio Discord Engine</span>
          </div>
          <pre><code><span class="code-kw">def</span> <span class="code-fn">split_message</span>(text: <span class="code-type">str</span>, limit: <span class="code-type">int</span> = <span class="code-num">1900</span>) -&gt; <span class="code-type">list</span>[<span class="code-type">str</span>]:
    <span class="code-str">"""
    Safely splits a message into chunks below Discord's 2000 character limit.
    Splits on newlines first, but also handles single lines longer than the limit.
    Closes and reopens markdown code blocks across chunks to avoid broken formatting.
    """</span>
    <span class="code-kw">if</span> <span class="code-fn">len</span>(text) &lt;= limit:
        <span class="code-kw">return</span> [text]

    chunks = []
    lines = text.<span class="code-fn">split</span>(<span class="code-str">'\n'</span>)
    current_chunk = <span class="code-str">""</span>
    in_code_block = <span class="code-kw">False</span>
    code_block_lang = <span class="code-str">""</span>

    <span class="code-kw">for</span> line <span class="code-kw">in</span> lines:
        <span class="code-cm"># Detect code block markers and track active syntax language</span>
        <span class="code-kw">if</span> line.<span class="code-fn">strip</span>().<span class="code-fn">startswith</span>(<span class="code-str">"```"</span>):
            in_code_block = <span class="code-kw">not</span> in_code_block
            <span class="code-kw">if</span> in_code_block:
                code_block_lang = line.<span class="code-fn">strip</span>()[<span class="code-num">3</span>:]

        <span class="code-cm"># Check if adding this line exceeds our chunk ceiling</span>
        <span class="code-kw">if</span> <span class="code-fn">len</span>(current_chunk) + <span class="code-fn">len</span>(line) + <span class="code-num">1</span> &gt; limit:
            <span class="code-kw">if</span> current_chunk:
                <span class="code-kw">if</span> in_code_block:
                    current_chunk += <span class="code-str">"\n```"</span>  <span class="code-cm"># Close block in current chunk</span>
                chunks.<span class="code-fn">append</span>(current_chunk)
                current_chunk = <span class="code-str">""</span>
                <span class="code-kw">if</span> in_code_block:
                    current_chunk = <span class="code-str">f"```{code_block_lang}\n"</span>  <span class="code-cm"># Reopen in next chunk</span>

        current_chunk += (<span class="code-str">"\n"</span> <span class="code-kw">if</span> current_chunk <span class="code-kw">and</span> <span class="code-kw">not</span> current_chunk.<span class="code-fn">endswith</span>(<span class="code-str">"\n"</span>) <span class="code-kw">else</span> <span class="code-str">""</span>) + line

    <span class="code-kw">if</span> current_chunk:
        chunks.<span class="code-fn">append</span>(current_chunk)
    <span class="code-kw">return</span> chunks</code></pre>
        </figure>
      </div>

      <!-- Technical Specifications Table -->
      <div class="stack" data-reveal>
        <h2><?= t('Technical registry & system specifications', 'Registro técnico y especificaciones del sistema') ?></h2>
        <div class="project__spec-table-wrap">
          <table class="project__spec-table">
            <thead>
              <tr>
                <th><?= t('Module', 'Módulo') ?></th>
                <th><?= t('Technology / Stack', 'Tecnología / Pila') ?></th>
                <th><?= t('Function & Operational Design', 'Función y Diseño Operativo') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Gateway Client</strong></td>
                <td><code>discord.py 2.3+ (AsyncIO)</code></td>
                <td><?= t('Slash command tree synchronization, typing feedback loops, and multi-guild thread routing.', 'Sincronización de comandos slash, simulación de escritura y enrutamiento por hilos de servidor.') ?></td>
              </tr>
              <tr>
                <td><strong>HTTP Multiplexer</strong></td>
                <td><code>httpx.AsyncClient</code></td>
                <td><?= t('Persistent connection pooling with configurable timeouts to prevent blocking during intensive generation.', 'Pool de conexiones persistentes con timeouts configurables para evitar bloqueos durante la inferencia.') ?></td>
              </tr>
              <tr>
                <td><strong>Web Dashboard</strong></td>
                <td><code>FastAPI + Uvicorn</code></td>
                <td><?= t('REST endpoints for active model querying, system prompt hot-reloading, and server health diagnostics.', 'Endpoints REST para consulta de modelo activo, recarga de prompt de sistema y diagnósticos de salud.') ?></td>
              </tr>
              <tr>
                <td><strong>Local Model Engine</strong></td>
                <td><code>LM Studio Core (OpenAI-compatible)</code></td>
                <td><?= t('Local execution of quantized GGUF neural weights on Apple Metal / CUDA GPU compute.', 'Ejecución local de modelos cuantizados GGUF en hardware con aceleración Apple Metal o CUDA.') ?></td>
              </tr>
              <tr>
                <td><strong>Mobile Quick-Pair</strong></td>
                <td><code>Pillow + qrcode library</code></td>
                <td><?= t('Generates dynamic visual QR tokens allowing LAN mobile devices to quickly access the FastAPI dashboard.', 'Genera códigos QR dinámicos para emparejar dispositivos móviles de la red LAN al panel FastAPI.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Outcomes -->
      <div class="stack" data-reveal>
        <h2><?= t('Impact and engineering outcomes', 'Impacto y resultados de ingeniería') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('$0 in cloud costs:', '$0 en costos de nube:') ?></strong> <?= t('Full conversational AI capabilities without recurring token billing or external API dependencies.', 'Capacidades completas de IA conversacional sin facturación por token ni dependencias de APIs externas.') ?></li>
          <li><strong><?= t('100% Privacy & Data Sovereignty:', '100% Privacidad y Soberanía de Datos:') ?></strong> <?= t('Zero conversation telemetry or message history leaves the host workstation intranet.', 'Ningún mensaje ni dato de telemetría abandona la red local de la estación de trabajo.') ?></li>
          <li><strong><?= t('No broken formatting:', 'Formato siempre intacto:') ?></strong> <?= t('Markdown boundary repair prevents broken code blocks and truncated monospace text across Discord message splits.', 'La reparación de límites Markdown impide fragmentación de bloques de código en mensajes divididos.') ?></li>
          <li><strong><?= t('Clean embed design:', 'Diseño de embeds limpio:') ?></strong> <?= t('Monochrome embeds, technical status badges, and clear typography for consistent readability.', 'Embeds monocromáticos, insignias técnicas y tipografía clara para una lectura consistente.') ?></li>
        </ul>
      </div>

      <div class="project__links" data-reveal>
        <a class="btn btn--primary" href="https://github.com/Emerson3101/LLMDiscordbot-Playground" target="_blank" rel="noopener">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true" style="margin-right: 0.35rem; width: 1.1em; height: 1.1em; vertical-align: -0.15em;">
            <path d="M12 .5a11.5 11.5 0 0 0-3.635 22.41c.576.106.787-.25.787-.556 0-.275-.01-1.004-.016-1.972-3.198.696-3.873-1.542-3.873-1.542-.523-1.329-1.278-1.683-1.278-1.683-1.045-.714.08-.7.08-.7 1.156.082 1.764 1.188 1.764 1.188 1.027 1.761 2.695 1.252 3.352.957.103-.744.402-1.252.732-1.54-2.553-.29-5.238-1.278-5.238-5.687 0-1.257.449-2.283 1.187-3.09-.119-.291-.515-1.462.112-3.05 0 0 .967-.31 3.169 1.18a10.99 10.99 0 0 1 5.772 0c2.2-1.49 3.166-1.18 3.166-1.18.629 1.588.233 2.759.115 3.05.74.807 1.185 1.833 1.185 3.09 0 4.42-2.689 5.393-5.252 5.678.413.356.78 1.058.78 2.13 0 1.538-.014 2.778-.014 3.157 0 .309.208.668.793.555A11.5 11.5 0 0 0 12 .5z"/>
          </svg>
          <?= t('View on GitHub', 'Ver en GitHub') ?>
        </a>
        <a class="btn btn--secondary" href="/#contact"><?= t('Ask about this project', 'Pregunta sobre este proyecto') ?></a>
        <a class="btn btn--ghost" href="/portfolio/"><?= t('Back to portfolio', 'Volver al portafolio') ?></a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php';
