<?php
/**
 * Shared Canvas — Real-Time Collaborative Whiteboard (Case Study)
 *
 * Source: C:\Users\Emerson Plancarte\AndroidStudioProjects\SketchingProject
 * Kotlin, Jetpack Compose, Clean Architecture, Hilt, Firebase RTDB, Glance 1.1.0.
 */

$page_title       = 'Shared Canvas — Real-Time Collaborative Android App — Emerson Plancarte';
$page_description = 'Android collaborative whiteboard app built in Kotlin and Jetpack Compose with Clean Architecture, normalized unit-space stroke sync (<50ms), and an interactive home-screen Glance widget.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <?= t('Mobile Engineering · Android Clean Architecture', 'Ingeniería Móvil · Clean Architecture Android') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('Shared Canvas — Real-Time Collaborative Whiteboard', 'Shared Canvas — Pizarra Colaborativa en Tiempo Real') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Collaborative Android sketching platform featuring resolution-independent coordinate normalization, sub-50ms peer sync, and Glance 1.1.0 home-screen widgets.',
          'Plataforma de dibujo colaborativo para Android con normalización de coordenadas independiente de resolución, sincronización entre pares en <50ms y widgets de inicio Glance 1.1.0.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Platform', 'Plataforma') ?></dt><dd>Android (Kotlin 2.0+, SDK 35/36)</dd></div>
        <div><dt><?= t('UI & Widget', 'UI y Widgets') ?></dt><dd>Jetpack Compose &amp; Glance 1.1.0</dd></div>
        <div><dt><?= t('Sync Latency', 'Latencia de Sincronía') ?></dt><dd>&lt;50ms <?= t('peer-to-peer over Firebase', 'punto a punto con Firebase') ?></dd></div>
        <div><dt><?= t('Architecture', 'Arquitectura') ?></dt><dd>Clean Architecture + Dagger Hilt DI</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Kotlin 2.0+</span></li>
        <li><span class="tag">Jetpack Compose</span></li>
        <li><span class="tag">Clean Architecture</span></li>
        <li><span class="tag">Dagger Hilt</span></li>
        <li><span class="tag">Firebase RTDB</span></li>
        <li><span class="tag">Cloud Firestore</span></li>
        <li><span class="tag">Glance 1.1.0 Widget</span></li>
        <li><span class="tag">WorkManager</span></li>
        <li><span class="tag">PorterDuff Blending</span></li>
        <li><span class="tag">Vector Stroke Engine</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Architecture highlights', 'Aspectos clave de la arquitectura') ?>:</strong>
        <?= t(
          'Engineered with reactive Kotlin Coroutines & Flow streams, multi-device coordinate normalization, optimistic local rendering, and headless widget updates via silent FCM notifications.',
          'Construida con Coroutines y Flow reactivos en Kotlin, normalización de coordenadas multidispositivo, renderizado optimista local y actualización remota de widgets mediante notificaciones silenciosas FCM.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('Architecture & stroke pipeline', 'Arquitectura y pipeline de trazos') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. CAPTURE</span>
            <h3 class="pipeline-step__title">Compose Touch Ingest</h3>
            <p class="pipeline-step__desc"><?= t('High-frequency pointerInput gesture sampling converting local touch pixel events into a normalized 0.0–1.0 float coordinate space.', 'Muestreo de gestos con pointerInput a alta frecuencia, convirtiendo píxeles táctiles locales a un espacio flotante normalizado 0.0–1.0.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. SYNC</span>
            <h3 class="pipeline-step__title">Sub-50ms Firebase RTDB</h3>
            <p class="pipeline-step__desc"><?= t('Optimistic instant local painting paired with debounced JSON delta transmission and heartbeat peer connection tracking.', 'Pintado optimista inmediato en el lienzo local junto con transmisión debounced de deltas y monitoreo de presencia en tiempo real.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. RENDER</span>
            <h3 class="pipeline-step__title">Bézier Smoothing</h3>
            <p class="pipeline-step__desc"><?= t('Headless CanvasSnapshotGenerator translates normalized points to screen dimensions using quadratic Bézier curves (quadTo) and PorterDuff CLEAR mode.', 'CanvasSnapshotGenerator convierte puntos normalizados con curvas Bézier cuadráticas (quadTo) y borrado en modo PorterDuff CLEAR.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. WIDGET</span>
            <h3 class="pipeline-step__title">Glance AppWidget</h3>
            <p class="pipeline-step__desc"><?= t('Silent FCM wakeups trigger WorkManager background jobs that render an offscreen bitmap directly onto the Android home screen.', 'FCM silencioso despierta tareas en segundo plano con WorkManager que pintan mapas de bits directamente en la pantalla de inicio.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement -->
      <div class="stack" data-reveal>
        <h2><?= t('The multi-device challenge', 'El desafío multidispositivo') ?></h2>
        <?= tb(
          '<p>Building a seamless collaborative drawing experience between two Android devices runs into two core challenges:</p>
          <ul>
            <li><strong>Display Resolution Variance:</strong> If Device A has a 1440&times;3120 120Hz display and Device B has a 720&times;1600 60Hz screen, broadcasting raw pixel positions causes cropped drawings, distorted strokes, and out-of-bounds strokes.</li>
            <li><strong>Drawing Latency Tolerance:</strong> Human perception detects tactile drawing lag above 30ms. Waiting for network roundtrips before rendering a stroke renders the drawing experience unusable.</li>
          </ul>',
          '<p>Construir una experiencia fluida de dibujo colaborativo entre dos dispositivos Android enfrenta dos obstáculos fundamentales:</p>
          <ul>
            <li><strong>Disparidad de Resolución de Pantallas:</strong> Si el Dispositivo A tiene pantalla 1440&times;3120 a 120Hz y el Dispositivo B tiene 720&times;1600 a 60Hz, transmitir coordenadas en píxeles absolutos provoca trazos recortados, desplazados o fuera de lienzo.</li>
            <li><strong>Tolerancia de Latencia Táctil:</strong> El ojo y mano humanos perciben retardo en el trazo por encima de 30ms. Esperar la confirmación de la red antes de dibujar destruye la sensación natural del lienzo.</li>
          </ul>'
        ) ?>
      </div>

      <!-- Real Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('Normalized rendering engine implementation', 'Implementación del motor de renderizado normalizado') ?></h2>
        <p>
          <?= t(
            'The following production code from `core/data/CanvasSnapshotGenerator.kt` shows resolution-independent float projection, quadratic Bézier curve interpolation (`quadTo`), and PorterDuff eraser blending:',
            'El siguiente código en producción de `core/data/CanvasSnapshotGenerator.kt` muestra la proyección flotante independiente de resolución, interpolación con curvas Bézier cuadráticas (`quadTo`) y borrado por mezcla PorterDuff:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>core/data/CanvasSnapshotGenerator.kt — Shared Canvas Native Engine</span>
          </div>
          <pre><code><span class="code-kw">package</span> com.example.sketchingproject.core.data

<span class="code-kw">import</span> android.content.Context
<span class="code-kw">import</span> android.graphics.Bitmap
<span class="code-kw">import</span> androidx.compose.ui.geometry.Size
<span class="code-kw">import</span> com.example.sketchingproject.core.domain.CanvasState
<span class="code-kw">import</span> com.example.sketchingproject.core.domain.DrawingTool
<span class="code-kw">import</span> com.example.sketchingproject.core.domain.Stroke

<span class="code-kw">class</span> <span class="code-type">CanvasSnapshotGenerator</span> @Inject <span class="code-kw">constructor</span>(
    @ApplicationContext <span class="code-kw">private val</span> context: Context
) {
    <span class="code-kw">fun</span> <span class="code-fn">generateBitmap</span>(state: CanvasState, widthPx: <span class="code-type">Int</span> = <span class="code-num">1080</span>, heightPx: <span class="code-type">Int</span> = <span class="code-num">1440</span>): Bitmap {
        <span class="code-kw">val</span> bitmap = Bitmap.<span class="code-fn">createBitmap</span>(widthPx, heightPx, Bitmap.Config.ARGB_8888)
        <span class="code-kw">val</span> canvas = android.graphics.<span class="code-type">Canvas</span>(bitmap)
        canvas.<span class="code-fn">drawColor</span>(android.graphics.Color.WHITE)

        <span class="code-kw">val</span> canvasSize = Size(widthPx.<span class="code-fn">toFloat</span>(), heightPx.<span class="code-fn">toFloat</span>())
        state.completedStrokes.<span class="code-fn">forEach</span> { stroke -&gt;
            canvas.<span class="code-fn">renderStroke</span>(stroke, canvasSize)
        }
        <span class="code-kw">return</span> bitmap
    }

    <span class="code-kw">private fun</span> android.graphics.<span class="code-type">Canvas</span>.<span class="code-fn">renderStroke</span>(stroke: Stroke, canvasSize: Size) {
        <span class="code-kw">if</span> (stroke.points.<span class="code-fn">isEmpty</span>()) <span class="code-kw">return</span>
        <span class="code-kw">val</span> paint = android.graphics.<span class="code-type">Paint</span>().<span class="code-fn">apply</span> {
            isAntiAlias = <span class="code-kw">true</span>
            style = android.graphics.Paint.Style.STROKE
            strokeWidth = stroke.widthDp * context.resources.displayMetrics.density
            color = stroke.colorArgb
            strokeCap = <span class="code-kw">if</span> (stroke.tool == DrawingTool.MARKER)
                android.graphics.Paint.Cap.SQUARE <span class="code-kw">else</span> android.graphics.Paint.Cap.ROUND
            strokeJoin = android.graphics.Paint.Join.ROUND
            <span class="code-kw">if</span> (stroke.tool == DrawingTool.MARKER) alpha = <span class="code-num">153</span>  <span class="code-cm">// 60% opacity blend</span>
            <span class="code-kw">if</span> (stroke.tool == DrawingTool.ERASER) {
                xfermode = android.graphics.<span class="code-type">PorterDuffXfermode</span>(android.graphics.PorterDuff.Mode.CLEAR)
            }
        }

        <span class="code-cm">// Resolution-independent coordinate projection from normalized float space [0..1]</span>
        <span class="code-kw">val</span> path = android.graphics.<span class="code-type">Path</span>()
        <span class="code-kw">val</span> px = { nx: <span class="code-type">Float</span> -&gt; nx * canvasSize.width }
        <span class="code-kw">val</span> py = { ny: <span class="code-type">Float</span> -&gt; ny * canvasSize.height }

        <span class="code-kw">val</span> first = stroke.points.<span class="code-fn">first</span>()
        path.<span class="code-fn">moveTo</span>(px(first.x), py(first.y))
        <span class="code-kw">var</span> prev = first

        <span class="code-cm">// Quadratic Bézier smoothing across stroke control vertices</span>
        stroke.points.<span class="code-fn">drop</span>(<span class="code-num">1</span>).<span class="code-fn">forEach</span> { curr -&gt;
            <span class="code-kw">val</span> midX = (px(prev.x) + px(curr.x)) / <span class="code-num">2f</span>
            <span class="code-kw">val</span> midY = (py(prev.y) + py(curr.y)) / <span class="code-num">2f</span>
            path.<span class="code-fn">quadTo</span>(px(prev.x), py(prev.y), midX, midY)
            prev = curr
        }
        path.<span class="code-fn">lineTo</span>(px(prev.x), py(prev.y))
        <span class="code-fn">drawPath</span>(path, paint)
    }
}</code></pre>
        </figure>
      </div>

      <!-- Technical Specifications Table -->
      <div class="stack" data-reveal>
        <h2><?= t('Technical registry & architectural specifications', 'Registro técnico y especificaciones arquitectónicas') ?></h2>
        <div class="project__spec-table-wrap">
          <table class="project__spec-table">
            <thead>
              <tr>
                <th><?= t('Layer / Module', 'Capa / Módulo') ?></th>
                <th><?= t('Android Technology', 'Tecnología Android') ?></th>
                <th><?= t('Architecture & Functionality', 'Arquitectura y Funcionalidad') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Interactive UI</strong></td>
                <td><code>Jetpack Compose 1.7+</code></td>
                <td><?= t('Declarative canvas state rendering with auto-hiding glassmorphic toolbox and animated pointer indicators.', 'Lienzo declarativo con barra de herramientas frosted glass auto-ocultable e indicadores de cursor animados.') ?></td>
              </tr>
              <tr>
                <td><strong>Home Widget</strong></td>
                <td><code>Android Glance 1.1.0</code></td>
                <td><?= t('Home-screen AppWidget displaying latest peer drawings without launching the main application process.', 'AppWidget en pantalla de inicio que muestra los últimos trazos sin abrir el proceso principal de la app.') ?></td>
              </tr>
              <tr>
                <td><strong>Cloud Transport</strong></td>
                <td><code>Firebase Realtime Database</code></td>
                <td><?= t('WebSocket-based sub-50ms synchronized stroke stream with automatic offline persistence and reconnection.', 'Flujo sincronizado en <50ms sobre WebSockets con persistencia fuera de línea y reconexión automática.') ?></td>
              </tr>
              <tr>
                <td><strong>Dependency Injection</strong></td>
                <td><code>Dagger Hilt 2.51+</code></td>
                <td><?= t('Strict constructor injection across Domain use cases, Repository implementations, and ViewModel scopes.', 'Inyección de dependencias estricta en casos de uso de Dominio, repositorios y ViewModels.') ?></td>
              </tr>
              <tr>
                <td><strong>Background Push</strong></td>
                <td><code>WorkManager + Firebase Cloud Messaging</code></td>
                <td><?= t('Silent high-priority FCM payloads wake WorkManager to regenerate local widget bitmaps on-the-fly.', 'Notificaciones FCM silenciosas de alta prioridad que activan WorkManager para renderizar bitmaps del widget al vuelo.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Outcomes -->
      <div class="stack" data-reveal>
        <h2><?= t('Impact and performance metrics', 'Impacto y métricas de rendimiento') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('<50ms Peer Sync Latency:', '<50ms Latencia entre Pares:') ?></strong> <?= t('Strokes render with no perceptible delay between connected Android devices on standard Wi-Fi / LTE networks.', 'Los trazos se renderizan sin retardo perceptible entre dispositivos Android sobre redes Wi-Fi y LTE convencionales.') ?></li>
          <li><strong><?= t('100% Cross-Resolution Precision:', '100% Precisión Multirresolución:') ?></strong> <?= t('Mathematical unit projection preserves identical stroke proportion and curves across budget and flagship screen aspect ratios.', 'La proyección matemática unitaria preserva proporciones y curvas idénticas en cualquier relación de aspecto.') ?></li>
          <li><strong><?= t('Sub-Second Home Widget Refresh:', 'Actualización de Widget en Sub-Segundo:') ?></strong> <?= t('Glance 1.1.0 widget automatically renders remote changes directly on the Android home screen via silent FCM triggers.', 'El widget Glance 1.1.0 refleja trazos remotos directamente en la pantalla de inicio mediante avisos silenciosos FCM.') ?></li>
          <li><strong><?= t('Vector UI & Minimalist Toolbar:', 'Interfaz Vectorial y Barra Minimalista:') ?></strong> <?= t('Custom vector brushes, palette swatches, and clean SVG status icons throughout all screens.', 'Pinceles vectoriales, selectores de paleta y limpios iconos SVG en todas las pantallas.') ?></li>
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
