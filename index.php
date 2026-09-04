<?php
/**
 * Home page entry point.
 *
 * Full bilingual (EN/ES) single-page site. Both languages are rendered and
 * tagged data-lang="en"/"es"; <html data-active-lang> (set in header.php
 * from localStorage, flipped by #lang-toggle) controls which is visible.
 *
 * Sections (ids match nav.php anchors):
 *   #about      — profile summary + headline figures
 *   #skills     — languages / databases / platforms / frameworks
 *   #experience — CFE ZOTGM timeline + ICV by-the-numbers band
 *   #education  — TecNM degree + TecAssist innovation award
 *   #contact    — CTA + direct contact links (form arrives in Phase 6/7)
 *
 * Content is sourced from "CV Emerson Plancarte.txt" (ICV voltage-quality
 * work at CFE, TecNM degree, innovation-contest award). See Development_Plan.md
 * for the content↔section mapping.
 */

$page_title       = 'Emerson Plancarte — Software & Embedded Systems Engineer';
$page_description = 'Emerson Salvador Plancarte Cerecedo — software and embedded systems engineer. ICV voltage-quality tooling at CFE, TecNM graduate, innovation award winner.';
require __DIR__ . '/includes/header.php';
?>

<!-- =========================================================
     Hero
     ======================================================= -->
<section class="hero" id="top" aria-labelledby="hero-title">
  <canvas class="hero__canvas" id="hero-gl" aria-hidden="true"></canvas>
  <div class="hero__inner" data-reveal-group>
    <p class="hero__eyebrow" data-reveal>
      <?= t('Available for new opportunities', 'Disponible para nuevas oportunidades') ?>
    </p>
    <h1 class="hero__title" id="hero-title" data-reveal data-scramble>Emerson Plancarte</h1>
    <p class="hero__subtitle" data-reveal data-scramble>
      <?= t('Software & Embedded Systems Engineer', 'Ingeniero de Software y Sistemas Embebidos') ?>
    </p>
    <div class="hero__actions" data-reveal>
      <a class="btn btn--primary btn--lg" href="/portfolio/" data-magnetic>
        <?= t('View my work', 'Ver mi trabajo') ?>
      </a>
      <a class="btn btn--secondary btn--lg" href="#contact" data-magnetic>
        <?= t('Get in touch', 'Contáctame') ?>
      </a>
    </div>
    <p class="hero__prompt" data-reveal>
      <?= t('Press', 'Presiona') ?> <span class="kbd">Ctrl</span>+<span class="kbd">K</span> <?= t('to explore', 'para explorar') ?>
    </p>
  </div>
  <div class="hero__cue" aria-hidden="true">
    <span data-lang="en">scroll</span><span data-lang="es">desliza</span>
    <span class="hero__cue-line"></span>
  </div>
</section>

<!-- =========================================================
     About
     ======================================================= -->
<section class="section about" id="about" aria-labelledby="about-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('About', 'Acerca de') ?></p>
      <h2 class="section__title" id="about-title">
        <?= t('A bit about me', 'Un poco sobre mí') ?>
      </h2>
    </header>

    <div class="about__intro stack" data-reveal>
      <?= tb(
        '<p>I’m an engineer with hands-on experience across electronic development and software. I design and build <strong>embedded systems</strong>, ship <strong>web, mobile, and desktop</strong> applications, and manage databases on-prem and in the cloud — focused on solving technical problems with pragmatic, innovative solutions.</p>',
        '<p>Soy ingeniero con experiencia práctica en desarrollo electrónico y de software. Diseño y construyo <strong>sistemas embebidos</strong>, desarrollo aplicaciones <strong>web, móviles y de escritorio</strong>, y gestiono bases de datos locales y en la nube — enfocado en resolver problemas técnicos con soluciones pragmáticas e innovadoras.</p>'
      ) ?>
    </div>

    <!-- Headline figures pulled from the CV: ICV analysis speed, scope, uptime, GPA -->
    <ul class="stats" data-reveal-group <?= lang_attr('Headline figures', 'Cifras destacadas', 'aria-label') ?>>
      <li class="stat" data-reveal>
        <span class="stat__value">><span data-count-to="80">80</span><em>%</em></span>
        <span class="stat__label"><?= t('Faster voltage-quality analysis', 'Análisis de calidad de voltaje más rápido') ?></span>
      </li>
      <li class="stat" data-reveal>
        <span class="stat__value"><span data-count-to="50">50</span><em>+</em></span>
        <span class="stat__label"><?= t('Localities evaluated by the ICV tool', 'Localidades evaluadas con la herramienta ICV') ?></span>
      </li>
      <li class="stat" data-reveal>
        <span class="stat__value">><span data-count-to="99">99</span><em>%</em></span>
        <span class="stat__label"><?= t('Critical-system availability', 'Disponibilidad de sistemas críticos') ?></span>
      </li>
      <li class="stat" data-reveal>
        <span class="stat__value">3.7</span>
        <span class="stat__label"><?= t('GPA, Computer Systems Engineering', 'Promedio, Ingeniería en Sistemas') ?></span>
      </li>
    </ul>
  </div>
</section>

<!-- =========================================================
     Skills
     ======================================================= -->
<section class="section skills" id="skills" aria-labelledby="skills-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('Skills', 'Habilidades') ?></p>
      <h2 class="section__title" id="skills-title">
        <?= t('Tools I reach for', 'Herramientas que utilizo') ?>
      </h2>
      <p class="section__lead">
        <?= t(
          'Grouped by role — from low-level embedded work up to full-stack web and cloud.',
          'Agrupadas por rol — desde trabajo embebido de bajo nivel hasta web full-stack y nube.'
        ) ?>
      </p>
    </header>

    <div class="skills__grid" data-reveal-group>
      <?php
      // Skill groups. The labels are bilingual; the tag text is proper-noun /
      // code names so it is NOT translated.
      $skill_groups = [
        [
          'en'  => 'Programming languages',
          'es'  => 'Lenguajes de programación',
          'tags' => ['C#', 'C/C++', 'Python', 'Kotlin', 'PHP', 'Java', 'JavaScript', 'SQL'],
        ],
        [
          'en'  => 'Databases',
          'es'  => 'Bases de datos',
          'tags' => ['SQL Server', 'PostgreSQL', 'MySQL', 'PI System'],
        ],
        [
          'en'  => 'Platforms & tools',
          'es'  => 'Plataformas y herramientas',
          'tags' => ['Visual Studio', 'VS Code', 'Git', 'PyCharm', 'IntelliJ IDEA', 'Azure', 'AWS', 'Arduino IDE', 'Raspberry Pi', 'Arduino', 'ESP32'],
        ],
        [
          'en'  => 'Frameworks & libraries',
          'es'  => 'Frameworks y librerías',
          'tags' => ['.NET Framework', '.NET Core', 'ASP.NET', 'Laravel', 'Node.js', 'React', 'jQuery', 'AJAX'],
        ],
      ];
      foreach ($skill_groups as $group): ?>
        <article class="card skills__card" data-reveal data-tilt>
          <h3 class="card__eyebrow"><?= t($group['en'], $group['es']) ?></h3>
          <ul class="tag-list">
            <?php foreach ($group['tags'] as $tag): ?>
              <li><span class="tag"><?= $tag ?></span></li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     Experience (timeline + ICV featured callout)
     ======================================================= -->
<section class="section experience" id="experience" aria-labelledby="experience-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('Experience', 'Experiencia') ?></p>
      <h2 class="section__title" id="experience-title">
        <?= t('Where I’ve worked', 'Dónde he trabajado') ?>
      </h2>
    </header>

    <ol class="timeline" data-reveal-group>
      <li class="timeline__item" data-reveal>
        <span class="timeline__node" aria-hidden="true"></span>
        <p class="timeline__meta">
          <?= t('03/2024 — 03/2026 · Acapulco, México', '03/2024 — 03/2026 · Acapulco, México') ?>
        </p>
        <h3 class="timeline__title">
          <?= t('Resident Engineer', 'Ingeniero Residente') ?>
        </h3>
        <p class="timeline__org">
          <?= t('CFE — Guerrero–Morelos Transmission Zone (ZOTGM)', 'CFE — Zona de Transmisión Guerrero–Morelos (ZOTGM)') ?>
        </p>
        <div class="timeline__body stack">
          <?= tb(
            '<p>Designed and built an advanced web tool for the automated evaluation of the <strong>Voltage Quality Index (ICV)</strong> under Mexico’s Grid Code, deployed across 50+ localities in the Guerrero and Morelos transmission zones. Integrated historical data from the OSIsoft PI System using .NET Framework 3.5, C#, PHP, JavaScript, jQuery, Bootstrap, and AJAX. The tool cut voltage-quality analysis time by more than 80% and adds manual validation, global and per-voltage-level ICV calculation, results export, and automatic report generation.</p>',
            '<p>Diseñé e implementé una herramienta web avanzada para la evaluación automatizada del <strong>Índice de Calidad de Voltaje (ICV)</strong> conforme al Código de Red, aplicada en más de 50 localidades de las zonas de transmisión Guerrero y Morelos. Integré datos históricos del sistema PI de OSIsoft usando .NET Framework 3.5, C#, PHP, JavaScript, jQuery, Bootstrap y AJAX. La herramienta redujo el tiempo de análisis de calidad de voltaje en más de un 80%, e incluye validación manual, cálculo de ICV global y por nivel de tensión, exportación de resultados y generación de reportes automáticos.</p>'
          ) ?>
          <?= tb(
            '<p>Delivered custom software solutions to support operational decision-making — surfacing critical events and compliance analysis. Improved operational efficiency and reduced human error through intuitive interfaces and automation.</p>',
            '<p>Entregué soluciones de software a la medida para apoyar la toma de decisiones operativas — facilitando la visualización de eventos críticos y el análisis de cumplimiento normativo. Mejoré la eficiencia operativa y reduje errores humanos mediante interfaces intuitivas y automatización.</p>'
          ) ?>
          <?= tb(
            '<p>Performed preventive and corrective maintenance on servers and workstations across the Transmission Zone, ensuring continuity of critical systems — diagnosing and resolving hardware and software faults, updating systems, and backing up key data — sustaining availability above 99% and documenting technical procedures for future interventions.</p>',
            '<p>Realicé mantenimiento preventivo y correctivo a servidores y estaciones de trabajo de la Zona de Transmisión, garantizando la continuidad operativa de los sistemas críticos — diagnóstico y resolución de fallas de hardware y software, actualización de sistemas y respaldo de información crítica — contribuyendo a una disponibilidad superior al 99% y documentando procedimientos técnicos para futuras intervenciones.</p>'
          ) ?>
        </div>
      </li>
    </ol>

    <!-- Featured callout: the ICV tool, lifted out so it read as a headline
         accomplishment and links onward to the portfolio (built in Phase 5). -->
    <aside class="feature" data-reveal-group aria-labelledby="feature-title">
      <div class="feature__main" data-reveal>
        <p class="card__eyebrow"><?= t('Featured work', 'Proyecto destacado') ?></p>
        <h3 class="feature__title" id="feature-title">
          <?= t('ICV — Voltage Quality Evaluation Tool', 'ICV — Herramienta de Evaluación de Calidad de Voltaje') ?>
        </h3>
        <p class="feature__body">
          <?= t(
            'A web platform that screens transmission-line voltage against Mexico’s Grid Code, flags infractions, validates them, and turns months of manual analysis into minutes — with audit-ready exports and reports.',
            'Una plataforma web que evalúa el voltaje de las líneas de transmisión contra el Código de Red de México, detecta infracciones, las valida y convierte meses de análisis manual en minutos — con exportaciones y reportes listos para auditoría.'
          ) ?>
        </p>
        <p class="feature__stack">
          <span class="tag">.NET 3.5</span>
          <span class="tag">C#</span>
          <span class="tag">PHP</span>
          <span class="tag">JavaScript</span>
          <span class="tag">PI System</span>
        </p>
        <a class="btn btn--secondary" href="/portfolio/">
          <?= t('Read the case study', 'Leer el caso de estudio') ?>
        </a>
      </div>
      <div class="feature__stat stat" data-reveal>
        <!-- Stat value keeps `>` and `%` static; the inner span is the only
             thing the count-up touches, so it animates 0 -> 80 while the
             decoration stays put. Static source is "80" so no-JS/reduced-
             motion users still see the final number. -->
        <span class="stat__value">><span data-count-to="80">80</span><em>%</em></span>
        <span class="stat__label">
          <?= t('Reduction in voltage-quality analysis time', 'Reducción en el tiempo de análisis de calidad de voltaje') ?>
        </span>
      </div>
    </aside>
  </div>
</section>

<!-- =========================================================
     Education + Award
     ======================================================= -->
<section class="section education" id="education" aria-labelledby="education-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('Education & Awards', 'Educación y Reconocimientos') ?></p>
      <h2 class="section__title" id="education-title">
        <?= t('School & recognition', 'Estudios y reconocimiento') ?>
      </h2>
    </header>

    <div class="education__grid" data-reveal-group>
      <article class="card" data-reveal data-tilt>
        <h3 class="card__eyebrow"><?= t('Education', 'Educación') ?></h3>
        <p class="card__title">
          <?= t('B.Sc. in Computer Systems Engineering', 'Ing. en Sistemas Computacionales') ?>
        </p>
        <p class="card__body">
          <?= t(
            'Instituto Tecnológico Nacional de México — Campus Acapulco (TecNM).',
            'Instituto Tecnológico Nacional de México — Campus Acapulco (TecNM).'
          ) ?>
        </p>
        <p class="card__footer education__marks">
          <span class="tag"><span class="tag__dot" aria-hidden="true"></span><?= t('GPA 3.7', 'Promedio 3.7') ?></span>
          <span class="tag"><?= t('Cumulative avg 92.33', 'Promedio acumulado 92.33') ?></span>
          <span class="tag"><?= t('TOEFL English certified', 'Certificación TOEFL de inglés') ?></span>
        </p>
      </article>

      <article class="card" data-reveal data-tilt>
        <h3 class="card__eyebrow"><?= t('Award', 'Reconocimiento') ?></h3>
        <p class="card__title">
          <?= t('1st place — Tech Innovation Contest', '1er lugar — Concurso de Innovación Tecnológica') ?>
        </p>
        <p class="card__body">
          <?= t('Smart Cities category, December 2025, for the project', 'Categoría Ciudades Inteligentes, diciembre de 2025, por el proyecto') ?>
          <strong><?= t('“TecAssist: Virtual Assistant”', '“TecAssist: Asistente Virtual”') ?></strong>
          <?= t(', recognized at the local level.', ', reconocido a nivel local.') ?>
        </p>
        <p class="card__footer">
          <span class="badge">
            <svg class="badge__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 2l2.39 4.84 5.34.78-3.87 3.77.91 5.32L12 14.99l-4.77 2.5.91-5.32L4.27 7.62l5.34-.78L12 2z"/>
              <path d="M8 15l-1.2 4.5L12 17l5.2 2.5L16 15" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            </svg>
            <?= t('Winner', 'Ganador') ?>
          </span>
        </p>
      </article>
    </div>
  </div>
</section>

<!-- =========================================================
     Contact CTA
     ======================================================= -->
<section class="section contact" id="contact" aria-labelledby="contact-title">
  <div class="container container--prose">
    <header class="section__header section__header--center" data-reveal>
      <p class="section__eyebrow"><?= t('Contact', 'Contacto') ?></p>
      <h2 class="section__title" id="contact-title">
        <?= t('Let’s build something', 'Construyamos algo juntos') ?>
      </h2>
      <p class="section__lead">
        <?= t(
          'Have a project in mind, or just want to say hello? I read every message — the fastest way to reach me is email.',
          '¿Tienes un proyecto en mente, o solo quieres saludar? Leo cada mensaje — la forma más rápida de contactarme es por correo.'
        ) ?>
      </p>
    </header>

    <ul class="contact-list" data-reveal-group>
      <li data-reveal>
        <a class="contact-link" href="mailto:emersonplancarte@gmail.com">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
            <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z"/>
            <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z"/>
          </svg>
          <span class="contact-link__label"><?= t('Email', 'Correo') ?></span>
          <span class="contact-link__value">emersonplancarte@gmail.com</span>
        </a>
      </li>
      <li data-reveal>
        <a class="contact-link" href="tel:+527444473905">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
            <path d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.615.599 1.836 1.428l1.553 5.875a1.875 1.875 0 0 1-.512 1.836L8.13 11.04a22.89 22.89 0 0 0 4.83 4.83l1.401-1.32a1.875 1.875 0 0 1 1.836-.512l5.875 1.553a1.875 1.875 0 0 1 1.428 1.836V19.5a3 3 0 0 1-3 3h-2.25C8.94 22.5 1.5 15.06 1.5 6.75v-2.25Z"/>
          </svg>
          <span class="contact-link__label"><?= t('Phone', 'Teléfono') ?></span>
          <span class="contact-link__value">+52 (744) 447 3905</span>
        </a>
      </li>
      <li data-reveal>
        <a class="contact-link" href="https://linkedin.com/in/emerson-plancarte" rel="noopener" target="_blank">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.063 2.063 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
          </svg>
          <span class="contact-link__label">LinkedIn</span>
          <span class="contact-link__value">/in/emerson-plancarte</span>
        </a>
      </li>
      <li data-reveal>
        <a class="contact-link" href="https://github.com/Emerson3101" rel="noopener" target="_blank">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
            <path d="M12 .5a11.5 11.5 0 0 0-3.635 22.41c.576.106.787-.25.787-.556 0-.275-.01-1.004-.016-1.972-3.198.696-3.873-1.542-3.873-1.542-.523-1.329-1.278-1.683-1.278-1.683-1.045-.714.08-.7.08-.7 1.156.082 1.764 1.188 1.764 1.188 1.027 1.761 2.695 1.252 3.352.957.103-.744.402-1.252.732-1.54-2.553-.29-5.238-1.278-5.238-5.687 0-1.257.449-2.283 1.187-3.09-.119-.291-.515-1.462.112-3.05 0 0 .967-.31 3.169 1.18a10.99 10.99 0 0 1 5.772 0c2.2-1.49 3.166-1.18 3.166-1.18.629 1.588.233 2.759.115 3.05.74.807 1.185 1.833 1.185 3.09 0 4.42-2.689 5.393-5.252 5.678.413.356.78 1.058.78 2.13 0 1.538-.014 2.778-.014 3.157 0 .309.208.668.793.555A11.5 11.5 0 0 0 12 .5z"/>
          </svg>
          <span class="contact-link__label">GitHub</span>
          <span class="contact-link__value">@Emerson3101</span>
        </a>
      </li>
    </ul>

    <form class="contact-form" id="contact-form" novalidate data-reveal>
      <div class="contact-form__row">
        <div class="field">
          <label class="field__label" for="cf-name">
            <?= t('Name', 'Nombre') ?> <span class="req" aria-hidden="true">*</span>
          </label>
          <input
            class="field__input" id="cf-name" name="name"
            type="text" required autocomplete="name"
            aria-describedby="cf-name-err"
            <?= lang_attr('Your name', 'Tu nombre', 'placeholder') ?>
          >
          <p class="field__error" id="cf-name-err"></p>
        </div>
        <div class="field">
          <label class="field__label" for="cf-email">
            <?= t('Email', 'Correo') ?> <span class="req" aria-hidden="true">*</span>
          </label>
          <input
            class="field__input" id="cf-email" name="email"
            type="email" required autocomplete="email"
            aria-describedby="cf-email-err"
            placeholder="you@example.com"
          >
          <p class="field__error" id="cf-email-err"></p>
        </div>
      </div>

      <div class="field">
        <label class="field__label" for="cf-message">
          <?= t('Message', 'Mensaje') ?> <span class="req" aria-hidden="true">*</span>
        </label>
        <textarea
          class="field__textarea" id="cf-message" name="message"
          required minlength="10"
          aria-describedby="cf-message-err"
          <?= lang_attr('Tell me about your project…', 'Cuéntame sobre tu proyecto…', 'placeholder') ?>
        ></textarea>
        <p class="field__error" id="cf-message-err"></p>
      </div>

      <!-- Honeypot: visually hidden, ignored by real users; a filled value
           means a bot, and the Phase 7 server drops it. The client also
           silently aborts submit if it is filled. -->
      <div class="field contact-form__hp" aria-hidden="true">
        <label class="field__label" for="cf-company">Company</label>
        <input class="field__input" id="cf-company" name="company" type="text" tabindex="-1" autocomplete="off">
      </div>

      <p class="contact-form__status" id="cf-status" role="status" aria-live="polite"></p>

      <button class="btn btn--primary btn--lg contact-form__submit" id="cf-submit" type="submit">
        <?= t('Send message', 'Enviar mensaje') ?>
      </button>
    </form>

    <div class="contact__location">
      <p>
        <?= t('Based in Acapulco de Juárez, Guerrero, México.', 'Desde Acapulco de Juárez, Guerrero, México.') ?>
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php';
