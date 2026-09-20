<?php
/**
 * Discord Music Streamer & Audio Pipeline (Case Study)
 *
 * Source: C:\Users\Emerson Plancarte\StudioProjects\Music Bot
 * Python, discord.py, Spotipy (Spotify API), yt-dlp, FFmpeg, AsyncIO.
 */

$page_title       = 'Discord Music Streamer & Audio Pipeline — Emerson Plancarte';
$page_description = 'High-fidelity asynchronous audio streaming Discord bot featuring dynamic queue management, Spotify Web API track resolution, and an FFmpeg audio transcoding pipeline.';
require __DIR__ . '/../includes/header.php';
?>

<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">

    <a class="project__back" href="/portfolio/"><?= t('All projects', 'Todos los proyectos') ?></a>

    <header class="project__header" data-reveal>
      <p class="project__cat">
        <span class="badge portfolio__tier portfolio__tier--real"><?= t('Real project', 'Proyecto real') ?></span>
        <?= t('Audio Streaming & Concurrency · Discord Voice Architecture', 'Streaming de Audio y Concurrencia · Arquitectura de Voz Discord') ?>
      </p>
      <h1 class="project__title" id="project-title">
        <?= t('Discord Music Streamer & Audio Pipeline', 'Streamer de Música para Discord y Pipeline de Audio') ?>
      </h1>
      <p class="project__subtitle">
        <?= t(
          'Asynchronous audio streaming engine for Discord servers with Spotify OAuth/headless resolution, resilient FFmpeg stream piping, and interactive UI views.',
          'Motor de streaming de audio asíncrono para Discord con resolución de pistas Spotify vía OAuth/headless, tuberías de transcodificación FFmpeg resilientes y controles UI interactivos.'
        ) ?>
      </p>

      <dl class="project__meta">
        <div><dt><?= t('Core Runtime', 'Entorno') ?></dt><dd>Python 3.11+ / AsyncIO</dd></div>
        <div><dt><?= t('VoIP Protocol', 'Protocolo VoIP') ?></dt><dd>Discord Voice Gateway (Opus 48kHz)</dd></div>
        <div><dt><?= t('Transcoder', 'Transcodificador') ?></dt><dd>FFmpeg with Reconnect Streaming Buffers</dd></div>
        <div><dt><?= t('Metadata Sources', 'Fuentes de Metadatos') ?></dt><dd>Spotify Web API &amp; yt-dlp</dd></div>
      </dl>

      <ul class="tag-list project__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
        <li><span class="tag">Python 3.11+</span></li>
        <li><span class="tag">discord.py (Cogs)</span></li>
        <li><span class="tag">Spotipy API</span></li>
        <li><span class="tag">yt-dlp Engine</span></li>
        <li><span class="tag">FFmpeg PCM</span></li>
        <li><span class="tag">AsyncIO Locks</span></li>
        <li><span class="tag">Interactive UI Views</span></li>
        <li><span class="tag">Auto-Disconnect GC</span></li>
        <li><span class="tag">Headless Audio Pipeline</span></li>
      </ul>
    </header>

    <div class="project__callout" data-reveal>
      <p>
        <strong><?= t('Real media streaming engineering', 'Ingeniería real de streaming de medios') ?>:</strong>
        <?= t(
          'Designed to overcome common Discord audio stream crashes caused by CDN token expirations, VoIP packet drop, and lingering ghost voice connections in idle channels.',
          'Diseñado para superar las caídas de audio habituales en Discord por expiración de tokens en CDN, pérdida de paquetes VoIP y conexiones fantasma en canales vacíos.'
        ) ?>
      </p>
    </div>

    <div class="project__body stack" data-reveal-group>
      <!-- Pipeline Diagram -->
      <div class="stack" data-reveal>
        <h2><?= t('Architecture & audio streaming pipeline', 'Arquitectura y pipeline de streaming de audio') ?></h2>
        <div class="project__pipeline">
          <div class="pipeline-step">
            <span class="pipeline-step__num">01. RESOLVE</span>
            <h3 class="pipeline-step__title">Metadata Extraction</h3>
            <p class="pipeline-step__desc"><?= t('Resolves raw links and keywords via Spotify Web API (with headless ClientCredentials fallback) and yt-dlp metadata extractors.', 'Resuelve enlaces y palabras clave con Spotify Web API (con fallback headless a ClientCredentials) y extractores yt-dlp.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">02. BUFFER</span>
            <h3 class="pipeline-step__title">FFmpeg PCM Pipe</h3>
            <p class="pipeline-step__desc"><?= t('Streams raw audio through FFmpeg with reconnect flags (-reconnect 1 -reconnect_delay_max 5) into discord.PCMVolumeTransformer.', 'Transcodifica audio en streaming con FFmpeg y parámetros de reconexión hacia discord.PCMVolumeTransformer.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">03. QUEUE</span>
            <h3 class="pipeline-step__title">GuildMusicState Machine</h3>
            <p class="pipeline-step__desc"><?= t('Thread-safe queue with asyncio.Lock preventing race conditions during rapid track skipping, volume stepping, and repeat loops.', 'Cola segura en concurrencia con asyncio.Lock que previene condiciones de carrera en saltos rápidos y control de volumen.') ?></p>
          </div>
          <div class="pipeline-step">
            <span class="pipeline-step__num">04. CONTROL</span>
            <h3 class="pipeline-step__title">Interactive Discord View</h3>
            <p class="pipeline-step__desc"><?= t('Persistent interactive buttons (Play/Pause, Skip, Loop, Volume) synced live with channel voice status and auto-disconnect GC.', 'Botones interactivos persistentes (Pausa, Saltar, Bucle, Volumen) sincronizados con voz y recolección de basura por inactividad.') ?></p>
          </div>
        </div>
      </div>

      <!-- Problem Statement -->
      <div class="stack" data-reveal>
        <h2><?= t('The media streaming challenge', 'El desafío de streaming de medios') ?></h2>
        <?= tb(
          '<p>Transmitting real-time audio over VoIP networks under Discord’s Opus voice protocol introduces distinct failure modes:</p>
          <ul>
            <li><strong>Audio Stutter &amp; Dropped Packets:</strong> Direct audio streams often drop when CDN servers throttle connections mid-song. Without low-level reconnect buffering in the FFmpeg subprocess, songs abruptly stop.</li>
            <li><strong>Headless Server Authentication:</strong> Running on a remote headless VPS prevents standard browser-based Spotify OAuth popups. The system must seamlessly negotiate between cached tokens and headless Client Credentials fallback.</li>
            <li><strong>Voice Connection Leaks:</strong> Forgotten bot instances left in empty channels consume substantial CPU and bandwidth. The bot requires an automated garbage-collector loop to cleanly terminate orphaned voice sessions.</li>
          </ul>',
          '<p>Transmitir audio en tiempo real sobre redes VoIP con el protocolo Opus de Discord presenta vulnerabilidades técnicas notables:</p>
          <ul>
            <li><strong>Cortes y Pérdida de Paquetes:</strong> Los streams de audio se interrumpen cuando las CDN limitan conexiones a mitad de canción. Sin parámetros de reconexión en el subproceso FFmpeg, la música se corta repentinamente.</li>
            <li><strong>Autenticación en Servidores Headless:</strong> Operar en un VPS remoto sin entorno gráfico impide ventanas emergentes de OAuth para Spotify. El sistema debe alternar entre tokens cacheados y Client Credentials de forma transparente.</li>
            <li><strong>Fugas de Conexiones de Voz:</strong> Bots olvidados en canales vacíos consumen ancho de banda y CPU innecesarios. Se requiere un recolector de basura automatizado para desconectar sesiones huérfanas con márgenes de gracia.'
        ) ?>
      </div>

      <!-- Real Code Snippet -->
      <div class="stack" data-reveal>
        <h2><?= t('State management & Spotify fallback engine', 'Gestor de estado y motor de respaldo Spotify') ?></h2>
        <p>
          <?= t(
            'The following production code from `src/cogs/music.py` demonstrates the per-guild music state machine, headless Spotify authentication fallback, and automated idle garbage-collection loop:',
            'El siguiente código en producción de `src/cogs/music.py` demuestra la máquina de estados por servidor, el respaldo headless para Spotify y el bucle de recolección de conexiones inactivas:'
          ) ?>
        </p>

        <figure class="project__code">
          <div class="project__code__bar">
            <span>src/cogs/music.py — Discord Music Voice Pipeline</span>
          </div>
          <pre><code><span class="code-kw">class</span> <span class="code-type">GuildMusicState</span>:
    <span class="code-str">"""Encapsulates isolated playback state, volume, and locks per Discord guild."""</span>
    <span class="code-kw">def</span> <span class="code-fn">__init__</span>(<span class="code-var">self</span>, bot: commands.Bot, guild_id: <span class="code-type">int</span>):
        <span class="code-var">self</span>.bot = bot
        <span class="code-var">self</span>.guild_id = guild_id
        <span class="code-var">self</span>.queue = []
        <span class="code-var">self</span>.current_song = <span class="code-kw">None</span>
        <span class="code-var">self</span>.loop = <span class="code-kw">False</span>
        <span class="code-var">self</span>.volume = <span class="code-num">1.0</span>  <span class="code-cm"># 1.0 = 100%</span>
        <span class="code-var">self</span>.play_lock = asyncio.<span class="code-fn">Lock</span>()  <span class="code-cm"># Prevents concurrent track collision</span>
        <span class="code-var">self</span>.idle_seconds = <span class="code-num">0</span>
        <span class="code-var">self</span>.alone_seconds = <span class="code-num">0</span>

<span class="code-kw">class</span> <span class="code-type">Music</span>(commands.Cog):
    <span class="code-kw">def</span> <span class="code-fn">_init_spotify</span>(<span class="code-var">self</span>):
        <span class="code-cm"># Attempt cached SpotifyOAuth first; gracefully fallback to headless Client Credentials</span>
        <span class="code-kw">try</span>:
            auth_manager = spotipy.<span class="code-type">SpotifyOAuth</span>(
                client_id=os.<span class="code-fn">getenv</span>(<span class="code-str">'SPOTIPY_CLIENT_ID'</span>),
                client_secret=os.<span class="code-fn">getenv</span>(<span class="code-str">'SPOTIPY_CLIENT_SECRET'</span>),
                cache_path=<span class="code-str">'.spotify_cache'</span>, open_browser=<span class="code-kw">False</span>
            )
            <span class="code-var">self</span>.spotify = spotipy.<span class="code-type">Spotify</span>(auth_manager=auth_manager)
            <span class="code-kw">if not</span> auth_manager.<span class="code-fn">get_cached_token</span>():
                <span class="code-kw">raise</span> <span class="code-type">ValueError</span>(<span class="code-str">"Headless server environment"</span>)
        <span class="code-kw">except</span> <span class="code-type">Exception</span>:
            auth_manager = <span class="code-type">SpotifyClientCredentials</span>(
                client_id=os.<span class="code-fn">getenv</span>(<span class="code-str">'SPOTIPY_CLIENT_ID'</span>),
                client_secret=os.<span class="code-fn">getenv</span>(<span class="code-str">'SPOTIPY_CLIENT_SECRET'</span>),
                cache_handler=<span class="code-type">MemoryCacheHandler</span>()
            )
            <span class="code-var">self</span>.spotify = spotipy.<span class="code-type">Spotify</span>(auth_manager=auth_manager)

    @tasks.<span class="code-fn">loop</span>(seconds=<span class="code-num">15</span>)
    <span class="code-kw">async def</span> <span class="code-fn">inactivity_check</span>(<span class="code-var">self</span>):
        <span class="code-str">"""Background GC loop disconnecting orphaned bot instances with a 60s grace margin."""</span>
        <span class="code-kw">for</span> guild_id, state <span class="code-kw">in</span> <span class="code-fn">list</span>(<span class="code-var">self</span>.states.<span class="code-fn">items</span>()):
            guild = <span class="code-var">self</span>.bot.<span class="code-fn">get_guild</span>(guild_id)
            vc = get(<span class="code-var">self</span>.bot.voice_clients, guild=guild) <span class="code-kw">if</span> guild <span class="code-kw">else</span> <span class="code-kw">None</span>
            <span class="code-kw">if not</span> vc <span class="code-kw">or not</span> vc.channel: <span class="code-kw">continue</span>

            members = [m <span class="code-kw">for</span> m <span class="code-kw">in</span> vc.channel.members <span class="code-kw">if not</span> m.bot]
            <span class="code-kw">if not</span> members:
                state.alone_seconds += <span class="code-num">15</span>
                <span class="code-kw">if</span> state.alone_seconds &gt;= <span class="code-num">60</span>:  <span class="code-cm"># 60-second grace margin before cleanup</span>
                    <span class="code-kw">await</span> <span class="code-var">self</span>.<span class="code-fn">cleanup_guild_state</span>(guild, vc, <span class="code-str">"Auto-disconnected: channel empty."</span>)</code></pre>
        </figure>
      </div>

      <!-- Technical Specifications Table -->
      <div class="stack" data-reveal>
        <h2><?= t('Technical registry & audio pipeline specifications', 'Registro técnico y especificaciones del pipeline') ?></h2>
        <div class="project__spec-table-wrap">
          <table class="project__spec-table">
            <thead>
              <tr>
                <th><?= t('Subsystem', 'Subsistema') ?></th>
                <th><?= t('Technology / Stack', 'Tecnología / Pila') ?></th>
                <th><?= t('Role & Engineering Implementation', 'Rol e Implementación de Ingeniería') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Audio Transcoder</strong></td>
                <td><code>FFmpeg Subprocess Pipe</code></td>
                <td><?= t('Pipes 48kHz stereo PCM with auto-reconnect arguments (-reconnect 1 -reconnect_streamed 1).', 'Transcodifica audio estéreo PCM a 48kHz con argumentos de reconexión automática.') ?></td>
              </tr>
              <tr>
                <td><strong>Voice Client</strong></td>
                <td><code>discord.py VoiceClient &amp; PyNaCl</code></td>
                <td><?= t('High-speed Opus voice packet encryption and direct UDP voice socket broadcasting.', 'Cifrado de paquetes de voz Opus a alta velocidad y transmisión directa por sockets UDP.') ?></td>
              </tr>
              <tr>
                <td><strong>Track Metadata</strong></td>
                <td><code>Spotipy (Spotify Web API)</code></td>
                <td><?= t('Dual-tier auth (OAuth with cache fallback to memory-based ClientCredentials) for resilient lookups.', 'Autenticación dual (OAuth con respaldo a ClientCredentials en memoria) para búsquedas confiables.') ?></td>
              </tr>
              <tr>
                <td><strong>Stream Extractor</strong></td>
                <td><code>yt-dlp Python Library</code></td>
                <td><?= t('Dynamic best-audio stream URL extraction with adaptive bitrate matching to avoid network congestion.', 'Extracción dinámica de URLs de audio con selección adaptable de bitrate para evitar saturación.') ?></td>
              </tr>
              <tr>
                <td><strong>Concurrency Guard</strong></td>
                <td><code>asyncio.Lock &amp; tasks.loop</code></td>
                <td><?= t('Atomic queue operations and periodic 15s daemon loop for automated voice channel garbage collection.', 'Operaciones atómicas en colas y bucle de 15 segundos para desconexión automática en canales vacíos.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Verified Outcomes -->
      <div class="stack" data-reveal>
        <h2><?= t('Impact and verified outcomes', 'Impacto y resultados verificados') ?></h2>
        <ul class="project__highlights">
          <li><strong><?= t('Zero Stutter Audio Streaming:', 'Streaming de Audio Continuo sin Cortes:') ?></strong> <?= t('FFmpeg reconnect flags eliminate stream termination caused by temporary CDN network timeouts.', 'Los parámetros de reconexión de FFmpeg eliminan cortes por latencia temporal en servidores CDN.') ?></li>
          <li><strong><?= t('Automated Server Resource Protection:', 'Protección Automatizada de Servidor:') ?></strong> <?= t('15-second background garbage-collector disconnects idle voice instances, saving CPU and bandwidth.', 'El recolector de basura en segundo plano desconecta sesiones inactivas, liberando CPU y ancho de banda.') ?></li>
          <li><strong><?= t('Headless Server Compatibility:', 'Compatibilidad con Servidores Headless:') ?></strong> <?= t('Graceful fallback to in-memory ClientCredentials allows unattended deployment on Linux servers without GUI.', 'El respaldo a ClientCredentials en memoria permite el despliegue desatendido en servidores Linux sin interfaz gráfica.') ?></li>
          <li><strong><?= t('Discord Embed UI Refinement:', 'Refinamiento de Interfaz en Discord:') ?></strong> <?= t('Implemented clean text indicators, custom color badges, and professional volume bars for sleek player state display.', 'Implementó indicadores de texto limpios, insignias de color y barras de volumen sobrias para un estado de reproducción impecable.') ?></li>
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
