<?php
/**
 * Home page entry point.
 *
 * Full bilingual (EN/ES) single-page site with interactive 3D WebGL hero,
 * featured engineering projects showcase, interactive telemetry & architecture
 * explorer, skills matrix, experience timeline, education & contact form.
 */

$page_title       = 'Emerson Plancarte — Backend Developer | .NET & Web Stacks';
$page_description = 'Emerson Salvador Plancarte Cerecedo — Backend developer specializing in .NET (C#, ASP.NET) and web stacks (PHP, Node.js, Next.js/TypeScript) with SQL Server and PostgreSQL experience. Electrical grid telemetry at CFE, cloud-native RAG systems, and production web & mobile platforms.';
$body_class       = 'page-home';
require __DIR__ . '/includes/header.php';

// Load single source of truth for projects
$projects_json = json_decode(file_get_contents(__DIR__ . '/assets/data/projects.json'), true);
$all_projects  = $projects_json['projects'] ?? [];
$categories    = $projects_json['categories'] ?? [];
$featured_projects = array_filter($all_projects, function ($p) {
  return !empty($p['featured']);
});
?>

<!-- =========================================================
     Hero (with Interactive 3D WebGL Hologram)
     ======================================================= -->
<section class="hero" id="top" aria-labelledby="hero-title">
  <canvas class="hero__canvas" id="hero-gl" aria-hidden="true"></canvas>
  <div class="hero__inner" data-reveal-group>
    <h1 class="hero__title" id="hero-title" data-reveal data-scramble data-typetrick>Emerson Plancarte</h1>
    <p class="hero__subtitle" data-reveal data-scramble data-typetrick>
      <?= t('Backend Developer | .NET & Web Stacks', 'Desarrollador Backend | .NET y Stacks Web') ?>
    </p>

    <div class="hero__actions" data-reveal>
      <a class="btn btn--primary btn--lg" href="#projects" data-magnetic>
        <?= t('Featured Work', 'Proyectos Destacados') ?>
      </a>
      <a class="btn btn--secondary btn--lg" href="/portfolio/" data-magnetic>
        <?= t('Full Portfolio', 'Portafolio completo') ?>
      </a>
      <a class="btn btn--ghost btn--lg" <?= lang_attr('/Emerson_Plancarte_Resume_EN.pdf', '/Emerson_Plancarte_CV_ES.pdf', 'href') ?> target="_blank" rel="noopener" data-magnetic>
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin-right: 0.35rem; width: 1.1em; height: 1.1em; vertical-align: -0.15em;">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <?= t('View CV / Resume', 'Ver CV') ?>
      </a>
      <a class="btn btn--ghost btn--lg" href="#contact" data-magnetic>
        <?= t('Contact', 'Contacto') ?>
      </a>
    </div>

    <p class="hero__prompt" data-reveal>
      <?= t('Press', 'Presiona') ?> <span class="kbd" data-key="ctrl">Ctrl</span>+<span class="kbd">K</span> <?= t('for quick commands', 'para comandos rápidos') ?>
    </p>
  </div>
  <div class="hero__cue" aria-hidden="true">
    <span data-lang="en">scroll</span><span data-lang="es">desliza</span>
    <span class="hero__cue-line"></span>
  </div>
</section>

<!-- =========================================================
     About & Metrics
     ======================================================= -->
<section class="section about" id="about" aria-labelledby="about-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('About', 'Acerca de') ?></p>
      <h2 class="section__title" id="about-title" data-typetrick>
        <?= t('Backend engineering & data-integrated systems', 'Ingeniería backend y sistemas integrados con datos') ?>
      </h2>
    </header>

    <div class="about__intro stack" data-reveal>
      <?= tb(
        '<p>Backend developer specializing in <strong>.NET (C#, ASP.NET)</strong> and <strong>web stacks (PHP, Node.js, Next.js/TypeScript)</strong>, with <strong>SQL Server</strong> and <strong>PostgreSQL</strong> experience. Built and shipped data-integrated systems for a national electric utility (CFE), including a voltage-quality tool applied across 50+ localities. Additional experience in <strong>Android (Kotlin)</strong> and embedded systems (Raspberry Pi, Arduino, ESP32).</p>',
        '<p>Desarrollador backend especializado en <strong>.NET (C#, ASP.NET)</strong> y <strong>stacks web (PHP, Node.js, Next.js/TypeScript)</strong>, con experiencia en <strong>SQL Server</strong> y <strong>PostgreSQL</strong>. Desarrollé y puse en producción sistemas con integración de datos para una empresa eléctrica nacional (CFE), incluyendo una herramienta de calidad de voltaje aplicada en más de 50 localidades. Experiencia adicional en <strong>Android (Kotlin)</strong> y sistemas embebidos (Raspberry Pi, Arduino, ESP32).</p>'
      ) ?>
    </div>

    <!-- Figures from Emerson's professional career & projects -->
    <ul class="stats" data-reveal-group <?= lang_attr('Headline figures', 'Cifras destacadas', 'aria-label') ?>>
      <li class="stat" data-reveal>
        <span class="stat__value">><span data-count-to="80">80</span><em>%</em></span>
        <span class="stat__label"><?= t('Faster voltage quality analysis at CFE', 'Análisis de calidad de voltaje más rápido en CFE') ?></span>
      </li>
      <li class="stat" data-reveal>
        <span class="stat__value"><span data-count-to="50">50</span><em>+</em></span>
        <span class="stat__label"><?= t('Localities & substations monitored in real time', 'Localidades y subestaciones monitoreadas en tiempo real') ?></span>
      </li>
      <li class="stat" data-reveal>
        <span class="stat__value"><span data-count-to="42">42</span></span>
        <span class="stat__label"><?= t('Automated tests in TecAssist.NET (xUnit & Testcontainers)', 'Pruebas automatizadas en TecAssist.NET (xUnit y Testcontainers)') ?></span>
      </li>
      <li class="stat" data-reveal>
        <span class="stat__value"><span data-lang="en">1<em>st</em> Place</span><span data-lang="es">1<em>er</em> Lugar</span></span>
        <span class="stat__label"><?= t('Tech Innovation Contest (Smart Cities), 2024', 'Concurso de Innovación Tecnológica (Ciudades Inteligentes), 2024') ?></span>
      </li>
    </ul>
  </div>
</section>

<!-- =========================================================
     Featured Projects Showcase
     ======================================================= -->
<section class="section projects" id="projects" aria-labelledby="projects-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('Featured Work', 'Proyectos Destacados') ?></p>
      <h2 class="section__title" id="projects-title" data-typetrick>
        <?= t('Selected engineering projects', 'Proyectos de ingeniería seleccionados') ?>
      </h2>
      <p class="section__lead">
        <?= t(
          'A selection of projects spanning industrial energy monitoring, local AI assistants, and collaborative mobile apps.',
          'Una selección de proyectos que abarca monitoreo de energía industrial, asistentes locales con IA y apps móviles colaborativas.'
        ) ?>
      </p>
    </header>

    <ul class="portfolio__grid" data-reveal-group>
      <?php foreach ($featured_projects as $p):
        $catKey = $p['category'];
        $catLabel = $categories[$catKey] ?? ['en' => ucfirst($catKey), 'es' => ucfirst($catKey)];
        ?>
        <li class="portfolio__item" data-reveal>
          <a class="card card--interactive portfolio__card" href="<?= htmlspecialchars($p['links']['detail'], ENT_QUOTES, 'UTF-8') ?>" data-tilt>
            <div class="portfolio__card-head">
              <span class="portfolio__cat">
                <span data-lang="en"><?= htmlspecialchars($catLabel['en'], ENT_QUOTES, 'UTF-8') ?></span>
                <span data-lang="es"><?= htmlspecialchars($catLabel['es'], ENT_QUOTES, 'UTF-8') ?></span>
              </span>
            </div>

            <h3 class="portfolio__title">
              <span data-lang="en"><?= htmlspecialchars($p['title']['en'], ENT_QUOTES, 'UTF-8') ?></span>
              <span data-lang="es"><?= htmlspecialchars($p['title']['es'], ENT_QUOTES, 'UTF-8') ?></span>
            </h3>

            <p class="portfolio__subtitle">
              <span data-lang="en"><?= htmlspecialchars($p['subtitle']['en'], ENT_QUOTES, 'UTF-8') ?></span>
              <span data-lang="es"><?= htmlspecialchars($p['subtitle']['es'], ENT_QUOTES, 'UTF-8') ?></span>
            </p>

            <p class="portfolio__summary">
              <span data-lang="en"><?= htmlspecialchars($p['summary']['en'], ENT_QUOTES, 'UTF-8') ?></span>
              <span data-lang="es"><?= htmlspecialchars($p['summary']['es'], ENT_QUOTES, 'UTF-8') ?></span>
            </p>

            <ul class="tag-list portfolio__stack" <?= lang_attr('Stack', 'Pila tecnológica', 'aria-label') ?>>
              <?php foreach (array_slice($p['stack'], 0, 6) as $tag): ?>
                <li><span class="tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span></li>
              <?php endforeach; ?>
            </ul>

            <span class="portfolio__cta" aria-hidden="true">
              <?= t('Read the case study', 'Leer el caso de estudio') ?>
              <span class="portfolio__cta-arrow" aria-hidden="true">&rarr;</span>
            </span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="projects__more" data-reveal style="margin-top: var(--space-5); text-align: center;">
      <a class="btn btn--secondary btn--lg" href="/portfolio/" data-magnetic>
        <?= t('Explore all 7 projects', 'Explorar los 7 proyectos') ?>
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" style="margin-left: 0.5rem;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- =========================================================
     Skills Matrix
     ======================================================= -->
<section class="section skills" id="skills" aria-labelledby="skills-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('Skills & Stack', 'Habilidades y Tecnologías') ?></p>
      <h2 class="section__title" id="skills-title" data-typetrick>
        <?= t('Core technologies', 'Tecnologías principales') ?>
      </h2>
      <p class="section__lead">
        <?= t(
          'Technologies proven across industrial utility telemetry, cloud-native RAG systems, full-stack web platforms, and mobile engineering.',
          'Tecnologías probadas en telemetría industrial de red, sistemas RAG cloud-native, plataformas web full-stack y desarrollo móvil.'
        ) ?>
      </p>
    </header>

    <div class="skills__grid" data-reveal-group>
      <?php
      $skill_groups = [
        [
          'en'   => 'Backend',
          'es'   => 'Backend',
          'tags' => ['C#', '.NET Core / Framework', 'ASP.NET', 'PHP', 'Node.js', 'Laravel', 'Python', 'Java', 'REST APIs', 'LLM / RAG integration'],
        ],
        [
          'en'   => 'Databases & Cloud',
          'es'   => 'Datos y Nube',
          'tags' => ['SQL Server', 'PostgreSQL (Supabase)', 'MySQL', 'SQLite', 'Firebase', 'PI System (OSIsoft)', 'Microsoft Azure', 'Vercel'],
        ],
        [
          'en'   => 'Frontend',
          'es'   => 'Frontend',
          'tags' => ['JavaScript', 'TypeScript', 'React', 'Next.js 16', 'jQuery', 'AJAX', 'Bootstrap', 'Tailwind CSS'],
        ],
        [
          'en'   => 'Testing & DevOps',
          'es'   => 'Pruebas y DevOps',
          'tags' => ['Automated Testing', 'CI/CD (GitHub Actions)', 'Vercel Deployments', 'Git', 'xUnit', 'Testcontainers', 'Docker'],
        ],
        [
          'en'   => 'Mobile & Embedded',
          'es'   => 'Móvil y Embebidos',
          'tags' => ['Kotlin', 'Android (Jetpack Compose)', 'Room Database', 'Hilt', 'C/C++', 'Raspberry Pi', 'Arduino', 'ESP32'],
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
     Experience Timeline
     ======================================================= -->
<section class="section experience" id="experience" aria-labelledby="experience-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('Experience', 'Experiencia') ?></p>
      <h2 class="section__title" id="experience-title" data-typetrick>
        <?= t('Professional track record', 'Trayectoria profesional') ?>
      </h2>
    </header>

    <ol class="timeline" data-reveal-group>
      <li class="timeline__item" data-reveal>
        <span class="timeline__node" aria-hidden="true"></span>
        <p class="timeline__meta">
          <?= t('11/2025 — 08/2026 · Acapulco de Juárez, Guerrero, México', '11/2025 — 08/2026 · Acapulco de Juárez, Guerrero, México') ?>
        </p>
        <h3 class="timeline__title">
          <?= t('Contract Software Engineer', 'Ingeniero Contratista') ?>
        </h3>
        <p class="timeline__org">
          <?= t('CFE – Guerrero-Morelos Transmission Zone (ZOTGM)', 'CFE – Zona de Transmisión Guerrero-Morelos (ZOTGM)') ?>
        </p>
        <div class="timeline__body stack">
          <?= tb(
            '<p>Designed and delivered custom software for a 20+ person team, including a <strong>real-time grid load-capacity dashboard</strong> with PI System data integration (PHP/JavaScript), automated status alerts, and Excel/CSV export.</p>
            <p>Improved operational efficiency by 80% and reduced human error through intuitive, responsive interfaces.</p>
            <p>Delivered real-time visibility into voltage behavior via a local-first (on-premises) architecture, enabling immediate corrective action across the regional transmission network.</p>',
            '<p>Diseñé y entregué software a la medida para un equipo de más de 20 integrantes, incluyendo un <strong>dashboard de cargabilidad de red en tiempo real</strong> con integración de datos del sistema PI (PHP/JavaScript), alertas automáticas y exportación a Excel/CSV.</p>
            <p>Mejoré la eficiencia operativa en un 80% y reduje errores humanos mediante interfaces intuitivas.</p>
            <p>Aporté visibilidad en tiempo real del comportamiento del voltaje mediante una arquitectura local (on-premises), permitiendo acciones correctivas inmediatas en la red de transmisión.</p>'
          ) ?>
        </div>
      </li>

      <li class="timeline__item" data-reveal>
        <span class="timeline__node" aria-hidden="true"></span>
        <p class="timeline__meta">
          <?= t('04/2024 — 04/2025 · Acapulco de Juárez, Guerrero, México', '04/2024 — 04/2025 · Acapulco de Juárez, Guerrero, México') ?>
        </p>
        <h3 class="timeline__title">
          <?= t('Software Development Intern', 'Residente de Ingeniería') ?>
        </h3>
        <p class="timeline__org">
          <?= t('CFE – Guerrero-Morelos Transmission Zone (ZOTGM)', 'CFE – Zona de Transmisión Guerrero-Morelos (ZOTGM)') ?>
        </p>
        <div class="timeline__body stack">
          <?= tb(
            '<p>Designed and built a web application (<strong>.NET Framework 3.5, C#, PHP, JavaScript, jQuery, Bootstrap, AJAX</strong>) that integrates historical OSIsoft PI System data to automate <strong>Voltage Quality Index (ICV)</strong> evaluation under Mexico\'s Grid Code across 50+ localities, cutting analysis time by 80%+.</p>
            <p>Maintained 4 servers and 10+ workstations, resolved hardware/software failures, and ran updates and backups, sustaining 99%+ uptime; documented 100% of technical procedures.</p>',
            '<p>Diseñé y desarrollé una aplicación web (<strong>.NET Framework 3.5, C#, PHP, JavaScript, jQuery, Bootstrap, AJAX</strong>) que integra datos históricos del sistema PI (OSIsoft) para automatizar la evaluación del <strong>Índice de Calidad de Voltaje (ICV)</strong> conforme al Código de Red en más de 50 localidades, reduciendo el tiempo de análisis en más del 80%.</p>
            <p>Di mantenimiento a 4 servidores y más de 10 estaciones de trabajo, resolví fallas de hardware/software y realicé actualizaciones y respaldos, manteniendo una disponibilidad superior al 99%; documenté el 100% de los procedimientos técnicos.</p>'
          ) ?>
        </div>
      </li>
    </ol>
  </div>
</section>

<!-- =========================================================
     Education & Recognition
     ======================================================= -->
<section class="section education" id="education" aria-labelledby="education-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow"><?= t('Education & Awards', 'Educación y Reconocimientos') ?></p>
      <h2 class="section__title" id="education-title" data-typetrick>
        <?= t('Academic background & awards', 'Formación académica y reconocimientos') ?>
      </h2>
    </header>

    <div class="education__grid" data-reveal-group>
      <article class="card" data-reveal data-tilt>
        <h3 class="card__eyebrow"><?= t('Degree', 'Licenciatura') ?></h3>
        <p class="card__title">
          <?= t('B.S. in Computer Systems Engineering', 'Ing. en Sistemas Computacionales') ?>
        </p>
        <p class="card__body">
          <?= t(
            'Instituto Tecnológico Nacional de México, Campus Acapulco (TecNM Acapulco). Jun 2020 – Dec 2025. Focused on backend engineering, data architectures, and embedded telemetry.',
            'Instituto Tecnológico Nacional de México, Campus Acapulco (TecNM Acapulco). Jun 2020 – Dic 2025. Especializado en ingeniería backend, arquitecturas de datos y telemetría embebida.'
          ) ?>
        </p>
        <p class="card__footer education__marks">
          <span class="tag"><span class="tag__dot" aria-hidden="true"></span><?= t('GPA 92.33 / 100', 'Promedio 92.33 / 100') ?></span>
          <span class="tag"><?= t('TOEFL English Certified', 'Certificación TOEFL de Inglés') ?></span>
          <span class="tag"><?= t('Contest Winner (2024)', 'Ganador de Concurso (2024)') ?></span>
        </p>
      </article>

      <article class="card" data-reveal data-tilt>
        <h3 class="card__eyebrow"><?= t('Award', 'Reconocimiento') ?></h3>
        <p class="card__title">
          <?= t('Winner — Local Technology Innovation Contest', 'Ganador — Concurso de Innovación Tecnológica') ?>
        </p>
        <p class="card__body">
          <?= t('Smart Cities category, 2024, for the project', 'Categoría Ciudades Inteligentes, 2024, por el proyecto') ?>
          <strong><?= t('“TecAssist: Virtual Assistant”', '“TecAssist: Asistente Virtual”') ?></strong>
          <?= t('— university AI assistant concept, now rebuilt as the cloud-native system TecAssist.NET.', '— concepto de asistente universitario con IA, ahora reconstruido como el sistema cloud-native TecAssist.NET.') ?>
        </p>
        <p class="card__footer">
          <span class="badge">
            <svg class="badge__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 2l2.39 4.84 5.34.78-3.87 3.77.91 5.32L12 14.99l-4.77 2.5.91-5.32L4.27 7.62l5.34-.78L12 2z"/>
            </svg>
            <?= t('1st Place Winner (2024)', 'Ganador 1er Lugar (2024)') ?>
          </span>
        </p>
      </article>
    </div>
  </div>
</section>

<!-- =========================================================
     Contact Section
     ======================================================= -->
<section class="section contact" id="contact" aria-labelledby="contact-title">
  <div class="container container--prose">
    <header class="section__header section__header--center" data-reveal>
      <p class="section__eyebrow"><?= t('Contact', 'Contacto') ?></p>
      <h2 class="section__title" id="contact-title" data-typetrick>
        <?= t('Let’s build something together', 'Construyamos algo juntos') ?>
      </h2>
      <p class="section__lead">
        <?= t(
          'Looking for a backend engineer specializing in .NET & modern web stacks, RAG systems, or industrial telemetry? I read every message.',
          '¿Buscas un desarrollador backend especializado en .NET y stacks web modernos, sistemas RAG o telemetría industrial? Leo cada mensaje.'
        ) ?>
      </p>
    </header>

    <ul class="contact-list" data-reveal-group>
      <li data-reveal>
        <a class="contact-link" <?= lang_attr('/Emerson_Plancarte_Resume_EN.pdf', '/Emerson_Plancarte_CV_ES.pdf', 'href') ?> target="_blank" rel="noopener">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          <span class="contact-link__label"><?= t('Curriculum Vitae', 'Currículum Vitae') ?></span>
          <span class="contact-link__value"><?= t('Download Resume (PDF)', 'Descargar CV (PDF)') ?></span>
        </a>
      </li>
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
          <span class="contact-link__value">+52 744 447 3905</span>
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

    <!-- action/method = no-JS path: a native submit posts urlencoded to
         /api/contact (api/contact.php on dynamic hosts, the Netlify
         function on static). With JS on, preventDefault owns the submit. -->
    <form class="contact-form" id="contact-form" action="/api/contact" method="POST" novalidate data-reveal>
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
            <?= lang_attr('you@example.com', 'tu@correo.com', 'placeholder') ?>
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
