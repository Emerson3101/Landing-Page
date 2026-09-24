<?php
/**
 * TecAssist Revisited — AI Consultation Assistant & RAG Engine (Case Study)
 *
 * Source: C:\Users\Emerson Plancarte\StudioProjects\TecAssistLogic
 * 1st Place Winner, Technological Innovation Contest (Smart Cities, Dec 2025).
 * Ground-up modern rebuild as a PyQt6 desktop AI consultation assistant
 * powered by NVIDIA NIM streaming models and local RAG knowledge grounding.
 */

$page_title       = 'TecAssist Revisited — AI Desktop Assistant — Emerson Plancarte';
$page_description = '1st Place Innovation Contest winner: modern desktop AI consultation assistant powered by NVIDIA NIM token streaming, multi-format RAG indexing (.pdf/.docx/.txt/.md), vector caching, and speech I/O.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge"><?= t('1st Place Award', '1er Lugar') ?></span>
        <?= t('AI & Desktop Engineering · Smart Cities Innovation', 'Ingeniería de IA y Escritorio · Innovación Ciudades Inteligentes') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('TecAssist Revisited — AI Consultation Assistant & RAG Engine', 'TecAssist Revisited — Asistente de Consulta IA y Motor RAG') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Award-winning smart assistant rebuilt from the ground up as a desktop consultation system with streaming NVIDIA NIM inference and disk-cached vector search.',
          'Asistente galardonado reconstruido desde cero como un sistema de consulta de escritorio con inferencia streaming de NVIDIA NIM y búsqueda vectorial en caché de disco.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Award', 'Reconocimiento') ?></dt><dd><?= t('1st Place, Tech Innovation Contest (Smart Cities)', '1er Lugar, Concurso de Innovación Tecnológica (Ciudades Inteligentes)') ?></dd></div>
        <div><dt><?= t('Timeline', 'Cronología') ?></dt><dd><?= t('Dec 2025 Prototype → 2026 Complete Rebuild', 'Prototipo Dic 2025 → Reconstrucción Total 2026') ?></dd></div>
        <div><dt><?= t('Architect & Creator', 'Arquitecto y Creador') ?></dt><dd>Emerson Salvador Plancarte Cerecedo</dd></div>
        <div><dt><?= t('Inference Engine', 'Motor de Inferencia') ?></dt><dd>NVIDIA NIM (meta/llama-3.1-70b-instruct / nv-embedqa)</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Python 3.12+</span></li>
        <li><span class="tag">PyQt6 GUI</span></li>
        <li><span class="tag">NVIDIA NIM API</span></li>
        <li><span class="tag">NumPy Vector Math</span></li>
        <li><span class="tag">RAG Architecture</span></li>
        <li><span class="tag">SQLite / NPZ Cache</span></li>
        <li><span class="tag">SpeechRecognition</span></li>
        <li><span class="tag">pyttsx3 Audio Thread</span></li>
        <li><span class="tag">Qt Custom Animations</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('From university contest winner to desktop application', 'De ganador de concurso universitario a aplicación de escritorio') ?>:</strong>
        <?= t(
          'Originally took 1st place in Smart Cities as a voice-driven campus assistant. Re-architected as TecAssist Revisited with a modular Python architecture, cosine-similarity vector retrieval, and token-by-token streaming inference.',
          'Ganó el 1er lugar en Ciudades Inteligentes como asistente de voz para campus. Reestructurado como TecAssist Revisited con una arquitectura modular en Python, recuperación vectorial por similitud de coseno e inferencia en streaming token a token.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('RAG & Inference Pipeline', 'Pipeline de RAG e Inferencia') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. INGEST</span>
            <h3 class="pipeline-step__title">Multi-Format Extraction</h3>
            <p class="pipeline-step__desc"><?= t('Parses .pdf, .docx, .txt, and .md institutional documents, stripping formatting noise and chunking into semantic paragraphs.', 'Procesa documentos institucionales en .pdf, .docx, .txt y .md, eliminando ruido y fragmentando en párrafos semánticos.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. EMBED</span>
            <h3 class="pipeline-step__title">NIM Vector Indexing</h3>
            <p class="pipeline-step__desc"><?= t('Generates dense vector embeddings via NVIDIA NIM. Serializes normalized matrices into a compressed .npz disk cache with signature invalidation.', 'Genera embeddings densos con NVIDIA NIM. Serializa matrices normalizadas en un caché .npz en disco con firma de invalidación.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. RETRIEVE</span>
            <h3 class="pipeline-step__title">Cosine Top-K Match</h3>
            <p class="pipeline-step__desc"><?= t('Executes dot product similarity searches in <15ms via NumPy. Fallbacks to NFKD accent-normalized keyword search if offline.', 'Ejecuta búsquedas de similitud por producto punto en <15ms con NumPy. Degrada a búsqueda por palabras clave normalizadas NFKD sin red.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. STREAM</span>
            <h3 class="pipeline-step__title">PyQt6 Aurora UI</h3>
            <p class="pipeline-step__desc"><?= t('Emits streaming tokens over Qt signals to smooth typewriter chat bubbles. Dispatches synthesized audio in background worker threads.', 'Emite tokens en streaming mediante señales Qt a burbujas animadas. Sintetiza audio en hilos de fondo sin congelar la interfaz.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement -->
      <div class="stack" data-reveal>
        <h2><?= t('The engineering challenge', 'El desafío de ingeniería') ?></h2>
        <?= tb(
          '<p>Large language models frequently suffer from two critical pitfalls when deployed in institutional contexts: <strong>hallucination</strong> (inventing regulations, office numbers, or procedures) and <strong>latency</strong> (waiting several seconds for a full paragraph to generate before showing any visual output to the user).</p>
          <p>Furthermore, desktop applications frequently freeze during network calls if UI threads and API loops are tightly coupled. TecAssist Revisited was built to guarantee:</p>
          <ul>
            <li><strong>Strict Factual Grounding:</strong> The LLM is restricted to the provided institutional knowledge base. When information is absent, it transparently declines rather than fabricating answers.</li>
            <li><strong>Zero Perceived Latency:</strong> Token streaming begins within 300ms, displaying words as they are generated.</li>
            <li><strong>Thread-Safe Speech Pipeline:</strong> Users can interrupt spoken responses at any time while the background speech synthesizer (pyttsx3) immediately halts without locking the GUI.</li>
          </ul>',
          '<p>Los modelos de lenguaje masivo padecen frecuentemente de dos fallas críticas en contextos institucionales: <strong>alucinaciones</strong> (inventar normativas, trámites o calendarios) y <strong>latencia excesiva</strong> (esperar varios segundos a que se genere todo el texto antes de mostrar respuesta).</p>
          <p>Adicionalmente, las aplicaciones de escritorio se congelan durante llamadas de red si el hilo de interfaz y el cliente HTTP están acoplados. TecAssist Revisited se diseñó para garantizar:</p>
          <ul>
            <li><strong>Fundamentación Factual Estricta:</strong> El modelo se restringe estrictamente a la base de conocimiento provista. Si la información no existe, declina con transparencia en lugar de inventar.</li>
            <li><strong>Cero Latencia Percibida:</strong> El streaming de tokens inicia en menos de 300ms, desplegando palabras conforme emergen.</li>
            <li><strong>Pipeline de Voz Seguro en Hilos:</strong> El usuario puede interrumpir la lectura en voz alta en cualquier momento deteniendo la síntesis (pyttsx3) sin bloquear los fotogramas de la GUI.</li>
          </ul>'
        ) ?>
      </div>

      <!-- Real Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('Core retrieval implementation', 'Implementación de recuperación central') ?></h2>
        <p>
          <?= t(
            'The following production code from `app/core/retriever.py` highlights the dual semantic vector search with NumPy matrix math, signature-based disk caching, and accent-insensitive keyword fallback:',
            'El siguiente código en producción de `app/core/retriever.py` resalta la búsqueda vectorial semántica dual con álgebra matricial en NumPy, caché en disco por firma y respaldo por palabras clave insensible a acentos:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>app/core/retriever.py — TecAssist Revisited Semantic Engine</span>
          </div>
          <pre><code><span class="code-cm">"""Retrieval over knowledge-base chunks.
Primary: semantic search with NIM embeddings (cached on disk per KB signature).
Fallback: lightweight keyword scoring with NFKD accent normalization.
"""</span>
<span class="code-kw">import</span> numpy <span class="code-kw">as</span> np
<span class="code-kw">import</span> unicodedata
<span class="code-kw">from</span> dataclasses <span class="code-kw">import</span> dataclass
<span class="code-kw">from</span> ..config <span class="code-kw">import</span> EMBED_CACHE
<span class="code-kw">from</span> .nim_client <span class="code-kw">import</span> NIMClient

<span class="code-kw">class</span> <span class="code-type">Retriever</span>:
    <span class="code-str">"""Semantic retriever with keyword fallback and compressed disk cache."""</span>

    <span class="code-kw">def</span> <span class="code-fn">build_index</span>(<span class="code-var">self</span>, chunks, signature: <span class="code-type">str</span>, progress=<span class="code-kw">None</span>):
        <span class="code-var">self</span>.chunks = chunks
        <span class="code-var">self</span>.signature = signature

        <span class="code-cm"># Load cached matrix if KB signature hasn't changed</span>
        cached = <span class="code-var">self</span>._load_cache(signature)
        <span class="code-kw">if</span> cached <span class="code-kw">is not</span> <span class="code-kw">None</span>:
            <span class="code-var">self</span>._matrix, <span class="code-var">self</span>.use_embeddings = cached, <span class="code-kw">True</span>
            <span class="code-kw">return</span>

        <span class="code-cm"># Batch embed new chunks via NVIDIA NIM</span>
        vectors = []
        batch = <span class="code-num">32</span>
        <span class="code-kw">for</span> i <span class="code-kw">in</span> <span class="code-fn">range</span>(<span class="code-num">0</span>, <span class="code-fn">len</span>(chunks), batch):
            group = chunks[i : i + batch]
            vectors.<span class="code-fn">extend</span>(<span class="code-var">self</span>.client.<span class="code-fn">embed_texts</span>([c.text <span class="code-kw">for</span> c <span class="code-kw">in</span> group], <span class="code-str">"passage"</span>))

        <span class="code-cm"># Normalize embedding matrix for instant unit dot-product cosine similarity</span>
        <span class="code-var">self</span>._matrix = np.<span class="code-fn">array</span>(vectors, dtype=np.float32)
        norms = np.linalg.<span class="code-fn">norm</span>(<span class="code-var">self</span>._matrix, axis=<span class="code-num">1</span>, keepdims=<span class="code-kw">True</span>)
        norms[norms == <span class="code-num">0</span>] = <span class="code-num">1.0</span>
        <span class="code-var">self</span>._matrix = <span class="code-var">self</span>._matrix / norms
        <span class="code-var">self</span>.use_embeddings = <span class="code-kw">True</span>
        <span class="code-var">self</span>._save_cache(signature, <span class="code-var">self</span>._matrix)

    <span class="code-kw">def</span> <span class="code-fn">_semantic_search</span>(<span class="code-var">self</span>, query: <span class="code-type">str</span>, top_k: <span class="code-type">int</span>, min_score: <span class="code-type">float</span>):
        q = np.<span class="code-fn">array</span>(<span class="code-var">self</span>.client.<span class="code-fn">embed_texts</span>([query], <span class="code-str">"query"</span>)[<span class="code-num">0</span>], dtype=np.float32)
        n = np.linalg.<span class="code-fn">norm</span>(q) <span class="code-kw">or</span> <span class="code-num">1.0</span>
        sims = (<span class="code-var">self</span>._matrix @ q) / n  <span class="code-cm"># Vectorized cosine projection in &lt;15ms</span>
        top_idx = np.<span class="code-fn">argsort</span>(-sims)[:top_k]
        <span class="code-kw">return</span> [
            ScoredChunk(<span class="code-var">self</span>.chunks[i], <span class="code-type">float</span>(sims[i]))
            <span class="code-kw">for</span> i <span class="code-kw">in</span> top_idx <span class="code-kw">if</span> sims[i] >= min_score
        ]</code></pre>
        </figure>
      </div>

      <!-- Technical Specifications Table -->
      <div class="stack" data-reveal>
        <h2><?= t('Technical registry & subsystem specifications', 'Registro técnico y especificaciones de subsistemas') ?></h2>
        <div class="project__spec-table-wrap">
          <table class="project__spec-table">
            <thead>
              <tr>
                <th><?= t('Subsystem', 'Subsistema') ?></th>
                <th><?= t('Technology / Stack', 'Tecnología / Pila') ?></th>
                <th><?= t('Key Capability & Architecture', 'Capacidad Clave y Arquitectura') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Inference Provider</strong></td>
                <td><code>NVIDIA NIM Cloud / Local Endpoint</code></td>
                <td><?= t('Server-sent events (SSE) streaming token generator with customizable temperature and top-p sampling.', 'Generador en streaming vía Server-Sent Events (SSE) con muestreo configurable de temperatura y top-p.') ?></td>
              </tr>
              <tr>
                <td><strong>Vector Engine</strong></td>
                <td><code>NumPy Matrix Multiplication + .npz</code></td>
                <td><?= t('Normalized vector matrix computation achieving sub-15ms semantic matching over hundreds of chunks.', 'Cálculo matricial normalizado que logra coincidencias semánticas en <15ms sobre cientos de fragmentos.') ?></td>
              </tr>
              <tr>
                <td><strong>Desktop Interface</strong></td>
                <td><code>PyQt6 + Qt Custom Property Animations</code></td>
                <td><?= t('Fluid dark-themed "Aurora" aesthetic with spring-damped sidebar toggles and custom SVG icon glyphs.', 'Estética oscura fluida "Aurora" con menú lateral animado con amortiguación y glifos SVG vectoriales.') ?></td>
              </tr>
              <tr>
                <td><strong>Speech Pipeline</strong></td>
                <td><code>Google Speech-to-Text + pyttsx3</code></td>
                <td><?= t('Decoupled QThread audio worker with instant cancellation when new queries arrive or mute is toggled.', 'Worker de audio desacoplado en QThread con cancelación inmediata al recibir nuevas preguntas o silenciar.') ?></td>
              </tr>
              <tr>
                <td><strong>Document Parsing</strong></td>
                <td><code>PyPDF2, python-docx, markdown</code></td>
                <td><?= t('Automatic character normalization and sliding-window semantic paragraph chunking with overlap buffers.', 'Normalización de caracteres y fragmentación en párrafos semánticos con solapamiento controlado.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Impact & Results -->
      <div class="stack" data-reveal>
        <h2><?= t('Achievements & competition results', 'Logros y resultados de la competencia') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('1st Place Innovation Contest (Dec 2025):', '1er Lugar Concurso de Innovación (Dic 2025):') ?></strong> <?= t('Evaluated by an academic jury against 20+ competing teams, winning top honors in the Smart Cities division.', 'Evaluado por un jurado académico frente a más de 20 equipos, obteniendo el máximo galardón en Ciudades Inteligentes.') ?></li>
          <li><strong><?= t('<300ms Time-to-First-Token:', '<300ms Tiempo al primer token:') ?></strong> <?= t('Near-zero perceived latency via NVIDIA NIM streaming endpoints compared to multi-second delays in monolithic API calls.', 'Latencia percibida casi nula con streaming de NVIDIA NIM frente a esperas de varios segundos en llamadas monolíticas.') ?></li>
          <li><strong><?= t('Clear institutional UI:', 'Interfaz institucional clara:') ?></strong> <?= t('High-contrast vector icons and clear typography suited to institutional use.', 'Iconos vectoriales de alto contraste y tipografía clara adaptadas al uso institucional.') ?></li>
          <li><strong><?= t('100% Offline Keyword Fallback:', 'Respaldo 100% fuera de línea:') ?></strong> <?= t('Guaranteed functionality even when internet access or API credentials are unavailable.', 'Operatividad garantizada incluso ante caídas de internet o falta de credenciales de API.') ?></li>
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
