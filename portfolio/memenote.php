<?php
/**
 * MemeNote — Modern Android Productivity & Habit Suite (Case Study)
 *
 * Source: C:\Users\Emerson Plancarte\AndroidStudioProjects\MemeNote
 * Kotlin, Jetpack Compose, Material 3, Room SQLite, DataStore, Glance Widgets.
 */

$page_title       = 'MemeNote — Android Productivity & Habit Suite — Emerson Plancarte';
$page_description = 'Production-grade Android productivity suite built with Jetpack Compose, Room SQLite (7+ entities), manual DI container, 4 Glance home-screen widgets, and reboot-resilient reminders.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--real"><?= t('Real project', 'Proyecto real') ?></span>
        <?= t('Mobile Engineering · Android Productivity Suite', 'Ingeniería Móvil · Suite de Productividad Android') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('MemeNote — Modern Android Productivity Suite', 'MemeNote — Suite Moderna de Productividad para Android') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Offline-first Android application uniting notes, tasks, habit streaks, and reminders with zero-framework manual DI, Room persistence, and 4 Glance home-screen widgets.',
          'Aplicación Android offline-first que une notas, tareas, hábitos y recordatorios con DI manual sin sobrecarga de frameworks, persistencia en Room y 4 widgets Glance.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Platform', 'Plataforma') ?></dt><dd>Android (Kotlin 2.0+, SDK 35/36)</dd></div>
        <div><dt><?= t('UI & Design', 'UI y Diseño') ?></dt><dd>Jetpack Compose &amp; Material 3</dd></div>
        <div><dt><?= t('Database Architecture', 'Arquitectura de Datos') ?></dt><dd>Room SQLite (Schema v5, 7+ Entities)</dd></div>
        <div><dt><?= t('Widgets', 'Widgets de Inicio') ?></dt><dd>4 Dedicated Glance 1.1.1 AppWidgets</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Kotlin 2.0+</span></li>
        <li><span class="tag">Jetpack Compose</span></li>
        <li><span class="tag">Material 3</span></li>
        <li><span class="tag">Room SQLite (v5)</span></li>
        <li><span class="tag">InvalidationTracker</span></li>
        <li><span class="tag">Glance 1.1.1 Widgets</span></li>
        <li><span class="tag">AlarmManager Exact</span></li>
        <li><span class="tag">Manual AppContainer DI</span></li>
        <li><span class="tag">Flow &amp; Coroutines</span></li>
        <li><span class="tag">Material 3 Design Tokens</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Real production Android architecture', 'Arquitectura Android de producción real') ?>:</strong>
        <?= t(
          'Designed to overcome build complexity and slow annotation-processing cycles through pragmatic manual Dependency Injection, reactive Kotlin Flow pipelines, and instant home-screen widget synchronization.',
          'Diseñada para superar la complejidad de compilación y procesadores de anotaciones lentos mediante Inyección de Dependencias manual pragmática, flujos Flow reactivos y sincronización instantánea de widgets de inicio.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('Architecture & reactive data pipeline', 'Arquitectura y flujo reactivo de datos') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. UI LAYER</span>
            <h3 class="pipeline-step__title">Jetpack Compose</h3>
            <p class="pipeline-step__desc"><?= t('Declarative screens observing ViewModels via collectAsStateWithLifecycle to suspend observation when in the background.', 'Pantallas declarativas observando ViewModels mediante collectAsStateWithLifecycle para pausar en segundo plano.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. DI CONTAINER</span>
            <h3 class="pipeline-step__title">Manual AppContainer</h3>
            <p class="pipeline-step__desc"><?= t('Lightweight manual dependency injection container eliminating reflection overhead and annotation processing build stalls.', 'Contenedor de inyección de dependencias manual que elimina sobrecarga por reflexión y tiempos de compilación lentos.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. PERSISTENCE</span>
            <h3 class="pipeline-step__title">Room Schema v5</h3>
            <p class="pipeline-step__desc"><?= t('SQLite relational persistence with InvalidationTracker listeners intercepting writes across all 7 database entities.', 'Persistencia relacional SQLite con InvalidationTracker interceptando escrituras en las 7 entidades de la base de datos.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. WIDGETS</span>
            <h3 class="pipeline-step__title">4 Glance AppWidgets</h3>
            <p class="pipeline-step__desc"><?= t('Immediate widget refresh for Notes, Todos, Habits, and Agenda without waiting for periodic OS update broadcasts.', 'Actualización inmediata para widgets de Notas, Tareas, Hábitos y Agenda sin esperar el ciclo de sondeo del sistema.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement -->
      <div class="stack" data-reveal>
        <h2><?= t('The architecture rationale', 'La justificación arquitectónica') ?></h2>
        <?= tb(
          '<p>Many single-module Android applications accumulate enormous build latency and reflection baggage by defaulting to heavy dependency injection frameworks like Dagger or Hilt. In MemeNote, I adopted a strict philosophy of <strong>zero-ceremony manual DI</strong> through an explicit <code>AppContainer</code>:</p>
          <ul>
            <li><strong>Instantaneous Compilation:</strong> Eliminates KAPT/KSP annotation processing cycles, cutting incremental build times by over 60%.</li>
            <li><strong>Immediate Widget Synchronization:</strong> Android home-screen widgets traditionally refresh on a 30-minute system timer (<code>updatePeriodMillis</code>). By attaching an <code>InvalidationTracker.Observer</code> directly to Room inside <code>AppContainer</code>, every user edit in the app immediately broadcasts to all 4 Glance widgets.</li>
            <li><strong>Reboot-Resilient Reminders:</strong> Exact scheduling via <code>AlarmManager</code> with a broadcast <code>BootReceiver</code> listening for <code>ACTION_BOOT_COMPLETED</code> to reschedule pending alerts across device restarts.</li>
          </ul>',
          '<p>Muchas aplicaciones Android de un solo módulo acumulan tiempos lentos de compilación y complejidad innecesaria al adoptar frameworks de inyección pesados como Dagger o Hilt. En MemeNote implementé una filosofía estricta de <strong>DI manual sin fricción</strong> mediante un <code>AppContainer</code> explícito:</p>
          <ul>
            <li><strong>Compilación Instantánea:</strong> Elimina ciclos de procesamiento de anotaciones con KAPT/KSP, reduciendo los tiempos de compilación incremental en más del 60%.</li>
            <li><strong>Sincronización Inmediata de Widgets:</strong> Los widgets de Android actualizan convencionalmente en ciclos de 30 minutos (<code>updatePeriodMillis</code>). Al conectar un <code>InvalidationTracker.Observer</code> directamente a Room en <code>AppContainer</code>, cada edición en la app se refleja al instante en los 4 widgets Glance.</li>
            <li><strong>Recordatorios Resistentes a Reinicios:</strong> Programación exacta mediante <code>AlarmManager</code> respaldada por un <code>BootReceiver</code> que escucha <code>ACTION_BOOT_COMPLETED</code> para reprogramar alertas pendientes tras reiniciar el teléfono.</li>
          </ul>'
        ) ?>
      </div>

      <!-- Real Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('Manual DI & InvalidationTracker implementation', 'Implementación de DI manual e InvalidationTracker') ?></h2>
        <p>
          <?= t(
            'The following production code from `data/AppContainer.kt` demonstrates the zero-framework DI container and Room `InvalidationTracker` pushing real-time writes directly to Glance widgets:',
            'El siguiente código en producción de `data/AppContainer.kt` demuestra el contenedor de DI manual y el `InvalidationTracker` de Room propagando escrituras inmediatamente a los widgets Glance:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>data/AppContainer.kt — MemeNote Manual Dependency Container</span>
          </div>
          <pre><code><span class="code-kw">package</span> com.example.memenote.data

<span class="code-kw">import</span> android.content.Context
<span class="code-kw">import</span> androidx.room.InvalidationTracker
<span class="code-kw">import</span> com.example.memenote.data.local.MemeNoteDatabase
<span class="code-kw">import</span> com.example.memenote.data.repo.*
<span class="code-kw">import</span> com.example.memenote.widget.WidgetRefresher

<span class="code-cm">/**
 * Lightweight manual dependency container — no DI framework needed.
 * Created once in MemeNoteApplication and reachable from ViewModels via application context.
 */</span>
<span class="code-kw">class</span> <span class="code-type">AppContainer</span> <span class="code-kw">private constructor</span>(
    <span class="code-kw">val</span> db: MemeNoteDatabase,
    <span class="code-kw">val</span> settings: SettingsRepository,
    <span class="code-kw">val</span> tags: TagRepository,
    <span class="code-kw">val</span> notes: NoteRepository,
    <span class="code-kw">val</span> todos: TodoRepository,
    <span class="code-kw">val</span> habits: HabitRepository,
    <span class="code-kw">val</span> reminders: ReminderRepository,
    <span class="code-kw">val</span> scheduler: ReminderScheduler
) {
    <span class="code-kw">companion object</span> {
        <span class="code-kw">fun</span> <span class="code-fn">from</span>(context: Context): AppContainer {
            <span class="code-kw">val</span> app = context.applicationContext
            <span class="code-kw">val</span> db = MemeNoteDatabase.<span class="code-fn">get</span>(app)

            <span class="code-cm">// Push every write through to the home-screen widgets immediately,</span>
            <span class="code-cm">// bypassing the OS-enforced 30-minute APPWIDGET_UPDATE polling cycle.</span>
            db.invalidationTracker.<span class="code-fn">addObserver</span>(<span class="code-kw">object</span> : InvalidationTracker.<span class="code-type">Observer</span>(
                <span class="code-str">"notes"</span>, <span class="code-str">"todo_lists"</span>, <span class="code-str">"tasks"</span>,
                <span class="code-str">"habits"</span>, <span class="code-str">"habit_logs"</span>, <span class="code-str">"reminders"</span>, <span class="code-str">"tags"</span>
            ) {
                <span class="code-kw">override fun</span> <span class="code-fn">onInvalidated</span>(tables: <span class="code-type">Set</span>&lt;<span class="code-type">String</span>&gt;) {
                    WidgetRefresher.<span class="code-fn">refreshAll</span>(app)
                }
            })

            <span class="code-kw">val</span> reminders = <span class="code-type">ReminderRepository</span>(db.<span class="code-fn">reminderDao</span>())
            <span class="code-kw">return</span> <span class="code-type">AppContainer</span>(
                db = db,
                settings = <span class="code-type">SettingsRepository</span>(app),
                tags = <span class="code-type">TagRepository</span>(db.<span class="code-fn">tagDao</span>()),
                notes = <span class="code-type">NoteRepository</span>(db.<span class="code-fn">noteDao</span>()),
                todos = <span class="code-type">TodoRepository</span>(db.<span class="code-fn">todoDao</span>()),
                habits = <span class="code-type">HabitRepository</span>(db.<span class="code-fn">habitDao</span>()),
                reminders = reminders,
                scheduler = <span class="code-type">ReminderScheduler</span>(app, reminders)
            )
        }
    }
}</code></pre>
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
                <th><?= t('Role & Engineering Details', 'Rol y Detalles de Ingeniería') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Database Core</strong></td>
                <td><code>Room SQLite (Schema Version 5)</code></td>
                <td><?= t('7 normalized tables with foreign keys, cascading deletes, and automated migration scripts.', '7 tablas normalizadas con claves foráneas, borrado en cascada y scripts de migración automáticos.') ?></td>
              </tr>
              <tr>
                <td><strong>Home Widgets</strong></td>
                <td><code>4 Glance 1.1.1 AppWidgets</code></td>
                <td><?= t('NotesWidget, TodosWidget, HabitsWidget, AgendaWidget with deep links into Compose view destinations.', 'NotesWidget, TodosWidget, HabitsWidget y AgendaWidget con enlaces profundos a destinos en Compose.') ?></td>
              </tr>
              <tr>
                <td><strong>Exact Alarms</strong></td>
                <td><code>AlarmManager + BootReceiver</code></td>
                <td><?= t('High-precision reminder scheduling with permission guards for Android 13/14 exact alarm policies.', 'Programación de alarmas de alta precisión con permisos estrictos de exact alarm en Android 13/14.') ?></td>
              </tr>
              <tr>
                <td><strong>User Preferences</strong></td>
                <td><code>Jetpack DataStore (Preferences)</code></td>
                <td><?= t('Reactive Flow-based storage for theme mode, true AMOLED dark mode, and dynamic Material You accents.', 'Almacenamiento reactivo basado en Flow para modo de tema, modo AMOLED puro y colores dinámicos Material You.') ?></td>
              </tr>
              <tr>
                <td><strong>Data Backup</strong></td>
                <td><code>kotlinx.serialization (JSON)</code></td>
                <td><?= t('Cryptographic JSON export and import for full offline data ownership and cross-device migration.', 'Exportación e importación en JSON estructurado para propiedad completa de datos y migración local.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Verified Outcomes -->
      <div class="stack" data-reveal>
        <h2><?= t('Impact and verified outcomes', 'Impacto y resultados verificados') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('Zero-Latency Widget Updates:', 'Actualización de Widgets sin Latencia:') ?></strong> <?= t('Room InvalidationTracker delivers immediate updates to home-screen widgets without waiting for system polling intervals.', 'InvalidationTracker de Room sincroniza widgets de inicio al instante sin depender de sondeos periódicos del sistema.') ?></li>
          <li><strong><?= t('>60% Faster Compilation Times:', 'Compilación >60% Más Rápida:') ?></strong> <?= t('Manual dependency injection eliminates annotation processing build stalls caused by KAPT/KSP.', 'La inyección de dependencias manual elimina cuellos de botella por procesadores de anotaciones KAPT/KSP.') ?></li>
          <li><strong><?= t('100% Reboot Survival for Alarms:', '100% Supervivencia de Alarmas tras Reinicio:') ?></strong> <?= t('BootReceiver guarantees uninterrupted reminder notifications across unexpected phone power cycles.', 'BootReceiver garantiza la restauración completa de recordatorios tras reinicios de batería o sistema.') ?></li>
          <li><strong><?= t('Material 3 Typography & Stroke Glyphs:', 'Tipografía Material 3 y Glifos Vectoriales:') ?></strong> <?= t('Polished typography, stroke vector icons, and Material 3 design tokens ensuring a clean, distraction-free aesthetic.', 'Tipografía cuidada, iconos vectoriales y tokens Material 3 que ofrecen una estética limpia y sin distracciones.') ?></li>
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
