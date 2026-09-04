  </main>

<?php
/**
 * Shared site footer. Closes <main> (opened in header.php) and the
 * document. Loads the JS bundle with `defer` so each runs after the DOM
 * parses: main.js (theme + language toggles, mobile nav
 * drawer, back-to-top, contact-form), animations.js (motion pass),
 * portfolio.js (grid filtering — no-ops off the portfolio page).
 * Real footer content (links, contact CTA) is added in a later phase.
 */
?>
  <footer class="site-footer">
    <div class="site-footer__row">
      <p>&copy; <?= date('Y') ?> Emerson Plancarte</p>
      <time class="site-footer__clock" id="footer-clock" aria-hidden="true"></time>
    </div>
  </footer>

  <!-- Scroll progress hairline (fx.js sets --sp). -->
  <div class="scroll-progress" aria-hidden="true"><span class="scroll-progress__fill"></span></div>

  <!-- Page-transition wipe (fx.js drives .is-running / .is-leaving). -->
  <div class="page-wipe" aria-hidden="true"><span class="page-wipe__label"></span></div>

  <!-- Back-to-top: appears after scrolling down (Phase 6). Fixed bottom-right;
       main.js toggles .is-visible and smooth-scrolls to top. -->
  <button class="to-top" id="to-top" type="button" <?= lang_attr('Back to top', 'Volver arriba', 'aria-label') ?>>
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M12 19V5M5 12l7-7 7 7"></path>
    </svg>
  </button>

  <!-- main.js = theme + language toggles, mobile nav drawer, back-to-top,
       contact-form (Phases 2/3/6); animations.js = motion pass (Phase 4);
       portfolio.js = grid filtering (Phase 5) — no-ops off the portfolio
       page. All deferred so the DOM parses first; independent. -->
  <script src="/assets/js/animations.js" defer></script>
  <script src="/assets/js/webgl-hero.js" defer></script>
  <script src="/assets/js/fx.js" defer></script>
  <script src="/assets/js/fx-particles.js" defer></script>
  <script src="/assets/js/portfolio.js" defer></script>
  <script src="/assets/js/main.js" defer></script>
</body>
</html>
