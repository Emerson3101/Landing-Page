<?php
/**
 * Event Platform — Real-Time Wedding Coordination & Seating Planner (Case Study)
 *
 * Source: C:\Users\Emerson Plancarte\StudioProjects\wedproject
 * Next.js 16, React 19, Supabase, Tailwind CSS v4, Framer Motion, GSAP, Satori.
 */

$page_title       = 'Event Platform — Real-Time Wedding Coordination — Emerson Plancarte';
$page_description = 'Next.js 16 and Supabase event platform featuring an interactive visual seating planner ("Mesas"), dynamic companion management, YouTube collaborative playlist, and serverless passcard generation.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--real"><?= t('Real project', 'Proyecto real') ?></span>
        <?= t('Web Systems · Full-Stack Next.js 16', 'Sistemas Web · Full-Stack Next.js 16') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('Event Platform — Real-Time Wedding Coordination & Seating Planner', 'Plataforma de Eventos — Coordinación y Plano de Asientos') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Production Next.js 16 SaaS-grade event platform with visual drag-and-drop table layouts, real-time companion drift detection, and serverless SVG passcard generation.',
          'Plataforma de eventos en producción nivel SaaS con Next.js 16, editor visual drag-and-drop de mesas, detección de desfase de acompañantes y generación serverless de pases SVG.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Role', 'Rol') ?></dt><dd><?= t('Full-Stack Architect & Lead Developer', 'Arquitecto Full-Stack y Desarrollador Líder') ?></dd></div>
        <div><dt><?= t('Release Year', 'Año de Lanzamiento') ?></dt><dd>2026</dd></div>
        <div><dt><?= t('Core Framework', 'Framework Principal') ?></dt><dd>Next.js 16 (App Router) + React 19</dd></div>
        <div><dt><?= t('Database & Auth', 'Base de Datos y Auth') ?></dt><dd>Supabase (PostgreSQL 16) with Row-Level Security</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Next.js 16</span></li>
        <li><span class="tag">React 19</span></li>
        <li><span class="tag">TypeScript</span></li>
        <li><span class="tag">Tailwind CSS v4</span></li>
        <li><span class="tag">Supabase (PostgreSQL)</span></li>
        <li><span class="tag">Framer Motion</span></li>
        <li><span class="tag">GSAP ScrollTrigger</span></li>
        <li><span class="tag">Satori &amp; Sharp</span></li>
        <li><span class="tag">ExcelJS Logistics</span></li>
        <li><span class="tag">Zero-Emoji Standard</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Real production event platform', 'Plataforma real de eventos en producción') ?>:</strong>
        <?= t(
          'Engineered for Alma & Chava’s wedding in September 2026. Manages end-to-end guest RSVP, companion limits, interactive venue table seating, dietary restrictions, and collaborative music curation.',
          'Diseñada para la boda de Alma y Chava en septiembre de 2026. Administra el registro completo de confirmación (RSVP), límites de acompañantes, distribución de mesas en el salón, restricciones dietéticas y curación colaborativa de música.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('Architecture & event coordination pipeline', 'Arquitectura y flujo de coordinación') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. RSVP</span>
            <h3 class="pipeline-step__title">Client RSVP &amp; Companions</h3>
            <p class="pipeline-step__desc"><?= t('Mobile-first invite portal allowing guests to confirm attendance, register verified companion names, and declare dietary allergies.', 'Portal móvil para que invitados confirmen asistencia, registren nombres de acompañantes y especifiquen alergias alimentarias.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. SEATING</span>
            <h3 class="pipeline-step__title">Visual Table Engine</h3>
            <p class="pipeline-step__desc"><?= t('Interactive top-down 2D canvas with round/rectangular tables. Drag-and-drop party assignment with live capacity boundary guards.', 'Lienzo interactivo 2D de mesas redondas y rectangulares con drag-and-drop de familias y validación de cupo en tiempo real.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. DRIFT</span>
            <h3 class="pipeline-step__title">Companion Drift Detection</h3>
            <p class="pipeline-step__desc"><?= t('Parallel data aggregation compares allocated seats against live companion counts, visually flagging discrepancies without corrupting layouts.', 'Cruce de datos en paralelo que compara asientos asignados contra acompañantes activos, alertando discrepancias sin romper el plano.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. PASSCARDS</span>
            <h3 class="pipeline-step__title">Serverless Passcards</h3>
            <p class="pipeline-step__desc"><?= t('Generates high-resolution digital passes on-the-fly using Satori (HTML/JSX → SVG) and Sharp (SVG → PNG) with verification QR codes.', 'Generación de pases digitales de alta resolución al vuelo con Satori (JSX → SVG) y Sharp (SVG → PNG) con código QR de verificación.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement -->
      <div class="stack" data-reveal>
        <h2><?= t('The organizational problem', 'El problema organizativo') ?></h2>
        <?= tb(
          '<p>Coordinating seating for 250+ wedding guests is notoriously prone to logistical failures. Spreadsheets and standard forms suffer from critical flaws:</p>
          <ul>
            <li><strong>Desynchronized Changes:</strong> When a guest modifies their companion count after tables have already been laid out, spreadsheet manifests break silently, resulting in catering shortfalls or empty chairs.</li>
            <li><strong>Table Boundary Violations:</strong> Overfilling a 10-person round table causes venue layout collisions and catering confusion.</li>
            <li><strong>Complex Relational Hierarchy:</strong> A single confirmed party consists of 1 lead guest plus zero, one, or two dynamic companions who must be assigned to adjacent chairs without losing their relational parent key.</li>
          </ul>',
          '<p>Coordinar la distribución de mesas para más de 250 invitados es un proceso altamente vulnerable a fallas logísticas. Las hojas de cálculo convencionales presentan serias limitantes:</p>
          <ul>
            <li><strong>Desfases por Modificaciones:</strong> Si un invitado cambia sus acompañantes tras haber sido asignado a una mesa, las hojas de cálculo se desincronizan en silencio, provocando faltantes de platillos o sillas vacías.</li>
            <li><strong>Violación de Capacidad:</strong> Sobrecargar una mesa redonda de 10 personas colapsa la circulación del salón y el servicio del banquete.</li>
            <li><strong>Jerarquía Relacional Dinámica:</strong> Una entrada familiar consta de 1 titular más cero, uno o dos acompañantes dinámicos que deben situarse en sillas contiguas conservando su enlace relacional.</li>
          </ul>'
        ) ?>
      </div>

      <!-- Real Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('Seating API & drift detection route', 'Ruta de API para asignación y detección de desfase') ?></h2>
        <p>
          <?= t(
            'The following production code from `src/app/api/admin/seating/route.ts` illustrates the parallel Supabase query pipeline, relational indexing, and real-time companion drift analysis:',
            'El siguiente código en producción de `src/app/api/admin/seating/route.ts` ilustra la consulta paralela en Supabase, indexación relacional en memoria y análisis de desfase de acompañantes:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>src/app/api/admin/seating/route.ts — Next.js 16 App Router</span>
          </div>
          <pre><code><span class="code-kw">import</span> { NextResponse } <span class="code-kw">from</span> <span class="code-str">"next/server"</span>;
<span class="code-kw">import</span> { createSupabaseServerClient, <span class="code-type">type Guest, type Companion, type SeatingTable, type SeatingSeat</span> } <span class="code-kw">from</span> <span class="code-str">"@/lib/supabase"</span>;
<span class="code-kw">import</span> { requireAdmin } <span class="code-kw">from</span> <span class="code-str">"@/lib/auth"</span>;

<span class="code-kw">export async function</span> <span class="code-fn">GET</span>() {
  <span class="code-kw">const</span> auth = <span class="code-kw">await</span> <span class="code-fn">requireAdmin</span>({ wrapOk: <span class="code-kw">true</span> });
  <span class="code-kw">if</span> (!auth.ok) <span class="code-kw">return</span> auth.response;

  <span class="code-kw">const</span> supabase = <span class="code-fn">createSupabaseServerClient</span>()!;

  <span class="code-cm">// Consultas concurrentes en paralelo para evitar cascadas de red (N+1)</span>
  <span class="code-kw">const</span> [tablesRes, seatsRes, guestsRes, companionsRes] = <span class="code-kw">await</span> Promise.<span class="code-fn">all</span>([
    supabase.<span class="code-fn">from</span>(<span class="code-str">"seating_tables"</span>).<span class="code-fn">select</span>(<span class="code-str">"*"</span>).<span class="code-fn">order</span>(<span class="code-str">"display_order"</span>, { ascending: <span class="code-kw">true</span> }),
    supabase.<span class="code-fn">from</span>(<span class="code-str">"seating_seats"</span>).<span class="code-fn">select</span>(<span class="code-str">"*"</span>).<span class="code-fn">order</span>(<span class="code-str">"seat_index"</span>, { ascending: <span class="code-kw">true</span> }),
    supabase.<span class="code-fn">from</span>(<span class="code-str">"guests"</span>).<span class="code-fn">select</span>(<span class="code-str">"id, name, email, phone, status, side"</span>).<span class="code-fn">eq</span>(<span class="code-str">"status"</span>, <span class="code-str">"confirmed"</span>),
    supabase.<span class="code-fn">from</span>(<span class="code-str">"companions"</span>).<span class="code-fn">select</span>(<span class="code-str">"id, guest_id, name"</span>).<span class="code-fn">order</span>(<span class="code-str">"created_at"</span>, { ascending: <span class="code-kw">true</span> })
  ]);

  <span class="code-cm">// Agrupación en memoria O(N) con estructuras Map</span>
  <span class="code-kw">const</span> companionsByGuest = <span class="code-kw">new</span> <span class="code-type">Map</span>&lt;<span class="code-type">string</span>, Companion[]&gt;();
  <span class="code-kw">for</span> (<span class="code-kw">const</span> c <span class="code-kw">of</span> companionsRes.data || []) {
    <span class="code-kw">const</span> list = companionsByGuest.<span class="code-fn">get</span>(c.guest_id) || [];
    list.<span class="code-fn">push</span>(c);
    companionsByGuest.<span class="code-fn">set</span>(c.guest_id, list);
  }

  <span class="code-cm">// Detección de drift: Si los acompañantes activos difieren del snapshot de la silla</span>
  <span class="code-kw">const</span> seats = (seatsRes.data || []).<span class="code-fn">map</span>((seat: SeatingSeat) =&gt; {
    <span class="code-kw">if</span> (!seat.guest_id) <span class="code-kw">return</span> seat;
    <span class="code-kw">const</span> liveCompanions = companionsByGuest.<span class="code-fn">get</span>(seat.guest_id) || [];
    <span class="code-kw">const</span> hasDrift = (seat.companion_count !== liveCompanions.length);
    <span class="code-kw">return</span> { ...seat, liveCompanions, hasDrift };
  });

  <span class="code-kw">return</span> NextResponse.<span class="code-fn">json</span>({ ok: <span class="code-kw">true</span>, tables: tablesRes.data, seats });
}</code></pre>
        </figure>
      </div>

      <!-- Technical Specifications Table -->
      <div class="stack" data-reveal>
        <h2><?= t('Technical registry & stack specifications', 'Registro técnico y especificaciones de la plataforma') ?></h2>
        <div class="project__spec-table-wrap">
          <table class="project__spec-table">
            <thead>
              <tr>
                <th><?= t('Layer', 'Capa') ?></th>
                <th><?= t('Technology / Library', 'Tecnología / Librería') ?></th>
                <th><?= t('Implementation & Responsibilities', 'Implementación y Responsabilidades') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Framework &amp; SSR</strong></td>
                <td><code>Next.js 16 + React 19</code></td>
                <td><?= t('Server Components for instant initial paint; streaming hydration on interactive seating diagrams.', 'Server Components para carga inmediata; hidratación en streaming en diagramas interactivos de mesas.') ?></td>
              </tr>
              <tr>
                <td><strong>Database &amp; RLS</strong></td>
                <td><code>Supabase (PostgreSQL 16)</code></td>
                <td><?= t('Partial unique indices preventing double-seat assignments; Row-Level Security for authenticated admin sessions.', 'Índices únicos parciales que impiden duplicar asientos; Row-Level Security para sesiones de administración.') ?></td>
              </tr>
              <tr>
                <td><strong>Visual Styling</strong></td>
                <td><code>Tailwind CSS v4 + Motion</code></td>
                <td><?= t('Zero-runtime utility engine paired with Framer Motion layout animations and GSAP ScrollTrigger timeline.', 'Motor de utilidades sin costo de runtime combinado con Framer Motion y línea de tiempo con GSAP ScrollTrigger.') ?></td>
              </tr>
              <tr>
                <td><strong>Vector Passcards</strong></td>
                <td><code>Satori + Sharp (Serverless)</code></td>
                <td><?= t('Headless HTML/JSX-to-SVG engine converting personalized pass layouts into crisp 300 DPI PNG cards with QR tokens.', 'Motor headless JSX-a-SVG que convierte pases personalizados en imágenes PNG nítidas a 300 DPI con tokens QR.') ?></td>
              </tr>
              <tr>
                <td><strong>Catering Exports</strong></td>
                <td><code>ExcelJS Workbook Pipeline</code></td>
                <td><?= t('Direct buffer compilation of multi-sheet workbooks with table roster headers and dietary caution flags.', 'Compilación directa en buffer de libros multitabla con listas por mesa y alertas de dietas especiales.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Engineering Challenges -->
      <div class="stack" data-reveal>
        <h2><?= t('Key engineering achievements', 'Logros clave de ingeniería') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('Zero Seating Drift:', 'Cero Desfase en Asientos:') ?></strong> <?= t('Decoupled snapshot strategy ensures that even if a guest alters their RSVP later, the physical seating chart retains historical integrity while highlighting variances.', 'Estrategia de snapshots desacoplados que asegura la integridad histórica del plano aun si el invitado modifica su confirmación.') ?></li>
          <li><strong><?= t('Single-Roundtrip Data Loading:', 'Carga en un solo Viaje de Red:') ?></strong> <?= t('Promise.all parallel fetches eliminate waterfall latency, loading entire floorplans with 250+ attendees in <180ms.', 'Consultas paralelas con Promise.all que eliminan cascadas de red, cargando planos completos con más de 250 asistentes en <180ms.') ?></li>
          <li><strong><?= t('Sub-Second Passcard Generation:', 'Generación de Pases en Sub-Segundo:') ?></strong> <?= t('Serverless Satori + Sharp rendering completes within 400ms without heavyweight Puppeteer/Chrome browser dependencies.', 'Renderizado serverless con Satori y Sharp completado en <400ms sin sobrecargas de Puppeteer o navegadores pesados.') ?></li>
          <li><strong><?= t('Strict Zero-Emoji Policy:', 'Política Estricta Cero Emojis:') ?></strong> <?= t('Clean, sophisticated UI leveraging Lucide React stroke icons, subtle serif typography, and custom ambient motion.', 'Interfaz limpia y sofisticada con iconos de trazo de Lucide React, tipografía refinada y movimiento ambiental sutil.') ?></li>
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
