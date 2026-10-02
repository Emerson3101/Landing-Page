<?php
/**
 * TecAssist.NET — Cloud-Native RAG Technical Assistant (Case Study)
 *
 * Source: https://github.com/Emerson3101/TecAssistNet
 * 1st Place Winner, Technological Innovation Contest (Smart Cities, 2024).
 * Ground-up modern rebuild as a cloud-native RAG system: ASP.NET Core 10 Web API
 * (Clean Architecture), EF Core, Supabase (PostgreSQL + pgvector), streaming cited
 * answers via NVIDIA NIM, Supabase Auth (ES256 JWKS), and a Next.js 16 chat UI.
 */

$page_title       = 'TecAssist.NET — Cloud-Native RAG Assistant — Emerson Plancarte';
$page_description = '1st Place Innovation Contest winner rebuilt as an enterprise cloud-native RAG system: ASP.NET Core 10 Web API (Clean Architecture), Supabase pgvector, NVIDIA NIM streaming citations, Next.js 16 chat UI, and 42 automated tests.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge"><?= t('1st Place Award', '1er Lugar') ?></span>
        <?= t('Backend Engineering & Cloud-Native RAG · Smart Cities Innovation', 'Ingeniería Backend y RAG Cloud-Native · Innovación Ciudades Inteligentes') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('TecAssist.NET — Cloud-Native RAG Technical Assistant', 'TecAssist.NET — Asistente Técnico Cloud-Native con RAG') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Award-winning AI assistant concept rebuilt as an enterprise cloud-native RAG platform with ASP.NET Core 10, Supabase pgvector, NVIDIA NIM streaming citations, and a Next.js 16 chat UI.',
          'Concepto de asistente con IA galardonado reconstruido como plataforma RAG cloud-native con ASP.NET Core 10, Supabase pgvector, respuestas en streaming con citas vía NVIDIA NIM y chat en Next.js 16.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Award', 'Reconocimiento') ?></dt><dd><?= t('1st Place, Tech Innovation Contest (Smart Cities), 2024', '1er Lugar, Concurso de Innovación Tecnológica (Ciudades Inteligentes), 2024') ?></dd></div>
        <div><dt><?= t('Timeline', 'Cronología') ?></dt><dd><?= t('2024 Prototype → 2025–2026 Cloud-Native Rebuild', 'Prototipo 2024 → Reconstrucción Cloud-Native 2025–2026') ?></dd></div>
        <div><dt><?= t('Architect & Creator', 'Arquitecto y Creador') ?></dt><dd>Emerson Salvador Plancarte Cerecedo</dd></div>
        <div><dt><?= t('Backend Framework', 'Framework Backend') ?></dt><dd>ASP.NET Core 10 Web API (Clean Architecture)</dd></div>
        <div><dt><?= t('Database & Vectors', 'Base de Datos y Vectores') ?></dt><dd>Supabase (PostgreSQL 16 + pgvector)</dd></div>
        <div><dt><?= t('Repository', 'Repositorio') ?></dt><dd><a href="https://github.com/Emerson3101/TecAssistNet" target="_blank" rel="noopener">github.com/Emerson3101/TecAssistNet</a></dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">ASP.NET Core 10</span></li>
        <li><span class="tag">C# 13</span></li>
        <li><span class="tag">Clean Architecture</span></li>
        <li><span class="tag">EF Core 10</span></li>
        <li><span class="tag">PostgreSQL 16</span></li>
        <li><span class="tag">pgvector</span></li>
        <li><span class="tag">NVIDIA NIM</span></li>
        <li><span class="tag">Next.js 16</span></li>
        <li><span class="tag">TypeScript</span></li>
        <li><span class="tag">Supabase Auth (ES256)</span></li>
        <li><span class="tag">Postgres RLS</span></li>
        <li><span class="tag">xUnit & Testcontainers</span></li>
        <li><span class="tag">Azure App Service</span></li>
        <li><span class="tag">Docker</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('From university contest winner to cloud-native enterprise system', 'De ganador de concurso universitario a sistema cloud-native empresarial') ?>:</strong>
        <?= t(
          'Originally won 1st Place in Smart Cities as a university AI assistant. Completely redesigned from the ground up as TecAssist.NET into an enterprise cloud-native system: an ASP.NET Core 10 Web API built on Clean Architecture, EF Core with pgvector similarity indexing, streaming Server-Sent Events (SSE) via NVIDIA NIM, ES256 JWT validation with Row-Level Security, and 42 automated tests deployed to Azure App Service.',
          'Ganó el 1er Lugar en Ciudades Inteligentes como asistente universitario con IA. Rediseñado completamente desde cero como TecAssist.NET en un sistema cloud-native empresarial: una API Web en ASP.NET Core 10 construida bajo Clean Architecture, EF Core con índices de similitud pgvector, streaming vía Server-Sent Events (SSE) con NVIDIA NIM, validación JWT ES256 con Row-Level Security y 42 pruebas automatizadas desplegadas en Azure App Service.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('RAG & Streaming Architecture Pipeline', 'Pipeline de Arquitectura RAG y Streaming') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. INGEST</span>
            <h3 class="pipeline-step__title"><?= t('Async Channel Worker', 'Worker Asíncrono en Channel') ?></h3>
            <p class="pipeline-step__desc"><?= t('Accepts .pdf, .md, and .txt files. Dispatches through a bounded Channel queue to an asynchronous background worker for semantic chunking and clean metadata extraction.', 'Recibe archivos .pdf, .md y .txt. Despacha mediante colas Channel acotadas a un worker en segundo plano para fragmentación semántica y extracción de metadatos.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. EMBED</span>
            <h3 class="pipeline-step__title"><?= t('NVIDIA NIM & pgvector', 'NVIDIA NIM y pgvector') ?></h3>
            <p class="pipeline-step__desc"><?= t('Batches embeddings via NVIDIA NIM (nvidia/nemotron-3-embed-1b) in passage mode. Persists vector matrices into Supabase PostgreSQL 16 with HNSW cosine distance indexes.', 'Genera embeddings en lote con NVIDIA NIM (nvidia/nemotron-3-embed-1b) en modo pasaje. Persiste matrices vectoriales en Supabase PostgreSQL 16 con índices HNSW de distancia de coseno.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. RETRIEVE</span>
            <h3 class="pipeline-step__title"><?= t('Grounding & Citations', 'Fundamentación y Citas') ?></h3>
            <p class="pipeline-step__desc"><?= t('Projects query embeddings into vector space with top-K similarity matching. Enforces strict zero-hallucination grounding and generates persisted citation indices with confidence scores.', 'Proyecta el embedding de la pregunta en el espacio vectorial con coincidencia top-K. Garantiza cero alucinaciones con fundamentación estricta y genera índices de citas persistidas con puntuaciones de confianza.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. STREAM</span>
            <h3 class="pipeline-step__title"><?= t('SSE Stream to Next.js 16', 'Streaming SSE a Next.js 16') ?></h3>
            <p class="pipeline-step__desc"><?= t('Streams completions over HTTP Server-Sent Events (token → citation → done frames) to the Next.js 16 chat interface, rendering live typography with real-time token dispatch.', 'Transmite respuestas vía Server-Sent Events en HTTP (marcos token → citation → done) a la interfaz de chat en Next.js 16, desplegando texto en tiempo real con despacho de tokens interactivo.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement & Engineering Highlights -->
      <div class="stack" data-reveal>
        <h2><?= t('The engineering challenge & system design', 'El desafío de ingeniería y diseño del sistema') ?></h2>
        <?= tb(
          '<p>Deploying AI assistants in institutional and educational environments presents stringent engineering demands: <strong>absolute factual grounding</strong> (regulations, admission procedures, and schedules must never be hallucinated), <strong>near-instant response initiation</strong>, and <strong>robust data isolation</strong> across users.</p>
          <p>TecAssist.NET replaces monolith desktop prototypes with an enterprise cloud-native architecture engineered to address these challenges directly:</p>
          <ul>
            <li><strong>Clean Architecture Separation:</strong> Decoupled into four distinct layers (<code>Domain</code>, <code>Application</code>, <code>Infrastructure</code>, and <code>Api</code>). Core business workflows and RAG orchestration have zero dependency on external AI SDKs, database drivers, or web frameworks.</li>
            <li><strong>Streaming SSE Protocol with Cited Answers:</strong> Instead of blocking HTTP calls, completions stream token-by-token over Server-Sent Events. Every assertion cites its exact source chunk (<code>[1]</code>, <code>[2]</code>) with confidence scores, allowing students and staff to audit answers directly against institutional source documents.</li>
            <li><strong>Defense-in-Depth Security:</strong> Supabase Auth issues ES256 JWT tokens verified against the project’s JWKS endpoint. Multi-tenant isolation is enforced both in the Application query layer and through native PostgreSQL <strong>Row-Level Security (RLS)</strong> policies on every table.</li>
            <li><strong>Rigorous Testing Suite (42 Tests):</strong> Tested end-to-end with 30 unit tests covering chunking, citation parsing, prompt synthesis, and JWKS token validation, plus 12 integration tests using <code>WebApplicationFactory</code> and <strong>Testcontainers</strong> spinning up real PostgreSQL 16 + pgvector containers in Docker with full SSE wire-format contract verification.</li>
          </ul>',
          '<p>El despliegue de asistentes con IA en entornos institucionales y educativos impone exigencias técnicas rigurosas: <strong>fundamentación factual estricta</strong> (normativas, trámites de admisión y calendarios jamás deben alucinarse), <strong>inicio casi instantáneo de respuestas</strong> y <strong>aislamiento estricto de datos</strong> entre usuarios.</p>
          <p>TecAssist.NET reemplaza prototipos monolíticos con una arquitectura cloud-native de grado empresarial diseñada para resolver estos desafíos:</p>
          <ul>
            <li><strong>Separación con Clean Architecture:</strong> Desacoplado en cuatro capas (<code>Domain</code>, <code>Application</code>, <code>Infrastructure</code> y <code>Api</code>). Las reglas de negocio centrales y la orquestación RAG no dependen de SDKs externos, drivers de base de datos ni frameworks web.</li>
            <li><strong>Protocolo SSE en Streaming con Respuestas Citadas:</strong> En lugar de llamadas bloqueantes, las respuestas se transmiten token a token mediante Server-Sent Events. Cada afirmación cita su fragmento de origen exacto (<code>[1]</code>, <code>[2]</code>) con puntuaciones de confianza, permitiendo auditar la información contra los documentos fuente oficiales.</li>
            <li><strong>Seguridad en Profundidad (Defense-in-Depth):</strong> Supabase Auth emite tokens JWT ES256 validados contra el endpoint JWKS del proyecto. El aislamiento multitenant se garantiza tanto en la capa de aplicación como mediante políticas nativas de <strong>Row-Level Security (RLS)</strong> en cada tabla de PostgreSQL.</li>
            <li><strong>Suite Rigurosa de Pruebas (42 Pruebas):</strong> Probado integralmente con 30 pruebas unitarias (fragmentación, análisis de citas, síntesis de prompts y validación de tokens) más 12 pruebas de integración con <code>WebApplicationFactory</code> y <strong>Testcontainers</strong> levantando contenedores reales de PostgreSQL 16 + pgvector en Docker con verificación de contratos del formato SSE.</li>
          </ul>'
        ) ?>
      </div>

      <!-- Real Production C# Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('Core streaming SSE endpoint implementation', 'Implementación del endpoint de streaming SSE') ?></h2>
        <p>
          <?= t(
            'The following production code from `src/TecAssist.Api/Controllers/ConversationsController.cs` illustrates the asynchronous Server-Sent Events pipeline, streaming tokens, citations, and completion metadata directly to the Next.js client:',
            'El siguiente código en producción de `src/TecAssist.Api/Controllers/ConversationsController.cs` ilustra el pipeline de Server-Sent Events asíncrono, transmitiendo tokens, citas y metadatos de finalización directamente al cliente en Next.js:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>src/TecAssist.Api/Controllers/ConversationsController.cs — ASP.NET Core 10 Web API</span>
          </div>
          <pre><code><span class="code-cm">/// &lt;summary&gt;
/// Streams RAG completion tokens, citations, and metadata over Server-Sent Events.
/// Enforces user identity extraction from validated ES256 JWT claims.
/// &lt;/summary&gt;</span>
[<span class="code-type">HttpPost</span>(<span class="code-str">"{id:guid}/messages"</span>)]
[<span class="code-type">Produces</span>(<span class="code-str">"text/event-stream"</span>)]
<span class="code-kw">public async</span> <span class="code-type">Task</span> <span class="code-fn">SendMessageStreamAsync</span>(
    <span class="code-type">Guid</span> id,
    [<span class="code-type">FromBody</span>] <span class="code-type">SendMessageRequest</span> request,
    <span class="code-type">CancellationToken</span> cancellationToken)
{
    <span class="code-type">string</span> userId = <span class="code-var">User</span>.<span class="code-fn">GetRequiredUserId</span>();

    <span class="code-var">Response</span>.<span class="code-var">ContentType</span> = <span class="code-str">"text/event-stream"</span>;
    <span class="code-var">Response</span>.<span class="code-var">Headers</span>[<span class="code-str">"Cache-Control"</span>] = <span class="code-str">"no-cache"</span>;
    <span class="code-var">Response</span>.<span class="code-var">Headers</span>[<span class="code-str">"X-Accel-Buffering"</span>] = <span class="code-str">"no"</span>;

    <span class="code-kw">await foreach</span> (<span class="code-kw">var</span> frame <span class="code-kw">in</span> <span class="code-var">_chatService</span>.<span class="code-fn">StreamMessageAsync</span>(
        userId, id, request.<span class="code-var">Content</span>, cancellationToken))
    {
        <span class="code-type">string</span> payload = frame <span class="code-kw">switch</span>
        {
            <span class="code-type">TokenFrame</span> t    =&gt; <span class="code-str">$"event: token\ndata: {JsonSerializer.Serialize(t.Text)}\n\n"</span>,
            <span class="code-type">CitationFrame</span> c =&gt; <span class="code-str">$"event: citation\ndata: {JsonSerializer.Serialize(c.Citation)}\n\n"</span>,
            <span class="code-type">DoneFrame</span> d     =&gt; <span class="code-str">$"event: done\ndata: {JsonSerializer.Serialize(d.Metadata)}\n\n"</span>,
            <span class="code-type">ErrorFrame</span> e    =&gt; <span class="code-str">$"event: error\ndata: {JsonSerializer.Serialize(e.Message)}\n\n"</span>,
            _               =&gt; <span class="code-kw">throw new</span> <span class="code-type">InvalidOperationException</span>(<span class="code-str">"Unknown SSE frame."</span>)
        };

        <span class="code-kw">await</span> <span class="code-var">Response</span>.<span class="code-fn">WriteAsync</span>(payload, cancellationToken);
        <span class="code-kw">await</span> <span class="code-var">Response</span>.<span class="code-var">Body</span>.<span class="code-fn">FlushAsync</span>(cancellationToken);
    }
}</code></pre>
        </figure>
      </div>

      <!-- Technical Specifications Table -->
      <div class="stack" data-reveal>
        <h2><?= t('Technical registry & system specifications', 'Registro técnico y especificaciones del sistema') ?></h2>
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
                <td><strong><?= t('Backend Web API', 'API Web Backend') ?></strong></td>
                <td><code>ASP.NET Core 10 · C# 13 · Clean Architecture</code></td>
                <td><?= t('Modular domain-driven design (Domain, Application, Infrastructure, Api) with EF Core 10 and Npgsql.', 'Diseño modular (Domain, Application, Infrastructure, Api) con EF Core 10 y Npgsql.') ?></td>
              </tr>
              <tr>
                <td><strong><?= t('Vector Store & DB', 'Base de Datos y Vectores') ?></strong></td>
                <td><code>Supabase PostgreSQL 16 + pgvector</code></td>
                <td><?= t('HNSW vector indexing, cosine similarity projection, and automated idempotent schema migrations.', 'Indexación vectorial HNSW, proyección por similitud de coseno y migraciones de esquema idempotentes.') ?></td>
              </tr>
              <tr>
                <td><strong><?= t('AI Embeddings & Chat', 'Embeddings e Inferencia') ?></strong></td>
                <td><code>NVIDIA NIM (nemotron-3-embed-1b + LLaMA 3.1)</code></td>
                <td><?= t('Dual passage/query dense embeddings and SSE streaming completions with strict factual grounding.', 'Embeddings densos duales pasaje/consulta y completions en streaming SSE con fundamentación factual estricta.') ?></td>
              </tr>
              <tr>
                <td><strong><?= t('Frontend Client', 'Cliente Frontend') ?></strong></td>
                <td><code>Next.js 16 · React 19 · TypeScript · Tailwind CSS</code></td>
                <td><?= t('Server-Sent Events consumer with real-time typing carets, inline citation cards, and markdown rendering.', 'Consumidor de Server-Sent Events con cursor interactivo, tarjetas de citas inline y renderizado markdown.') ?></td>
              </tr>
              <tr>
                <td><strong><?= t('Auth & Multi-Tenancy', 'Autenticación y Seguridad') ?></strong></td>
                <td><code>Supabase Auth (ES256 JWKS) + Postgres RLS</code></td>
                <td><?= t('Stateless token validation against JWKS and defense-in-depth Postgres Row-Level Security on all user tables.', 'Validación de tokens sin estado contra JWKS y Row-Level Security en Postgres en todas las tablas de usuario.') ?></td>
              </tr>
              <tr>
                <td><strong><?= t('Automated Testing', 'Pruebas Automatizadas') ?></strong></td>
                <td><code>xUnit · Testcontainers (Postgres 16 + pgvector)</code></td>
                <td><?= t('42 automated tests (30 unit + 12 integration) validating real database migrations and SSE wire protocols.', '42 pruebas automatizadas (30 unitarias + 12 de integración) validando migraciones reales y protocolos SSE.') ?></td>
              </tr>
              <tr>
                <td><strong><?= t('DevOps & Cloud', 'DevOps y Nube') ?></strong></td>
                <td><code>GitHub Actions CI/CD · Azure App Service · Docker</code></td>
                <td><?= t('Automated build and test pipeline deploying containerized API to Azure App Service and UI to Vercel.', 'Pipeline automatizado de compilación y pruebas con despliegue en contenedor a Azure App Service y UI en Vercel.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Impact & Results -->
      <div class="stack" data-reveal>
        <h2><?= t('Engineering milestones & results', 'Hitos de ingeniería y resultados') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('1st Place Innovation Contest Winner (2024):', 'Ganador del 1er Lugar en Concurso de Innovación (2024):') ?></strong> <?= t('Evaluated by academic and industry judges, winning top honors in the Smart Cities category.', 'Evaluado por jueces académicos e industriales, obteniendo el máximo galardón en la categoría Ciudades Inteligentes.') ?></li>
          <li><strong><?= t('42 Automated Tests with 100% CI Passing:', '42 Pruebas Automatizadas con 100% de Aprobación en CI:') ?></strong> <?= t('Comprehensive coverage across chunking algorithms, prompt assembly, and live ephemeral Testcontainers database tests.', 'Cobertura completa en algoritmos de fragmentación, armado de prompts y pruebas reales en contenedores efímeros con Testcontainers.') ?></li>
          <li><strong><?= t('Real-Time Streaming Latency (<300ms):', 'Latencia de Streaming en Tiempo Real (<300ms):') ?></strong> <?= t('First token emitted over Server-Sent Events in under 300ms, eliminating wait times of legacy monolithic requests.', 'Primer token emitido vía Server-Sent Events en menos de 300ms, eliminando los tiempos de espera de peticiones monolíticas tradicionales.') ?></li>
          <li><strong><?= t('Defense-in-Depth Tenant Isolation:', 'Aislamiento de Usuarios en Profundidad:') ?></strong> <?= t('Strict application filtering coupled with PostgreSQL Row-Level Security guarantees zero cross-tenant data leakage.', 'Filtrado estricto en la aplicación combinado con Row-Level Security en PostgreSQL garantiza cero filtración de datos entre usuarios.') ?></li>
        </ul>
      </div>

      <div class="project__links" data-reveal>
        <a class="btn btn--primary" href="https://github.com/Emerson3101/TecAssistNet" target="_blank" rel="noopener">
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
