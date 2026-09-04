/* =====================================================================
   animations.js — motion pass (Phase 4)
   -------------------------------------------------------------------
   Three small, independent modules, all gated on `prefers-reduced-motion`
   where motion is involved, and written to degrade cleanly:

     1. reveal      IntersectionObserver fades [data-reveal] into view;
                    children of [data-reveal-group] stagger by --reveal-i.
     2. countUp     [data-count-to] numbers count from 0 when scrolled in
                    (snaps instantly when reduced-motion is requested, or
                    when IntersectionObserver / rAF is unavailable).
     3. scrollSpy   highlights the nav link whose target section is in view
                    by setting aria-current="true" (styled in components.css).

   Smooth-scroll itself needs no JS — base.css sets scroll-behavior:smooth
   (and scroll-margin-top on sections clears the sticky header). Loaded
   with `defer` alongside main.js; the two are independent.
 * ===================================================================*/

'use strict';

(function motion() {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  function init() {
    initReveal();
    initCountUp();
    initScrollSpy();
  }

  /* --- Reveal + stagger ---------------------------------------------- */
  function initReveal() {
    var items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    // Assign stagger indices to children of any [data-reveal-group].
    var caps = { cap: 8 }; // keep staggers snappy (max ~480ms at 60ms steps)
    document.querySelectorAll('[data-reveal-group]').forEach(function (group) {
      group.querySelectorAll('[data-reveal]').forEach(function (child, i) {
        child.style.setProperty('--reveal-i', String(Math.min(i, caps.cap)));
      });
    });

    if (!('IntersectionObserver' in window)) {
      // No observer → show everything immediately.
      items.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          obs.unobserve(entry.target); // reveal once, then stop watching
        }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });

    items.forEach(function (el) { obs.observe(el); });
  }

  /* --- Stat count-up ------------------------------------------------- */
  function initCountUp() {
    var nums = document.querySelectorAll('[data-count-to]');
    if (!nums.length) return;

    if (reduce || !('IntersectionObserver' in window) || !('requestAnimationFrame' in window)) {
      // Snap straight to the final number — no animation.
      nums.forEach(function (el) { el.textContent = el.getAttribute('data-count-to'); });
      return;
    }

    var obs = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        runCount(entry.target);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.5 });

    // Park each counter at 0 before observing, so the count-up reads
    // 0 -> target when it scrolls in. The static source already shows the
    // final value as a no-JS / reduced-motion fallback; we only zero it
    // here, on the path that will actually animate it. Runs at defer
    // time (pre-paint) so there's no flash from the source value.
    nums.forEach(function (el) { el.textContent = '0'; });
    nums.forEach(function (el) { obs.observe(el); });

    function runCount(el) {
      var target = parseInt(el.getAttribute('data-count-to'), 10);
      if (isNaN(target)) return;
      var duration = 1100;
      var start = null;

      function step(ts) {
        if (start === null) start = ts;
        var p = Math.min((ts - start) / duration, 1);
        // easeOutCubic — fast at first, slowing into the landing.
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = String(Math.round(target * eased));
        if (p < 1) requestAnimationFrame(step);
        else {
          el.textContent = String(target);
          var wrap = el.closest('.stat');
          if (wrap) wrap.classList.add('count-done');
        }
      }
      requestAnimationFrame(step);
    }
  }

  /* --- Scroll-spy (active nav link) ---------------------------------- */
  function initScrollSpy() {
    var links = document.querySelectorAll('.site-nav__link');
    if (!links.length) return;

    // Map home-page nav links (href="/#id") to their target sections,
    // ignoring off-page links like /portfolio/ and links whose section
    // isn't on this page.
    var pairs = [];
    links.forEach(function (link) {
      var href = link.getAttribute('href') || '';
      if (href.charAt(0) !== '/' || href.charAt(1) !== '#') return; // only "/#id"
      var id = href.slice(2);
      var target = document.getElementById(id);
      if (target) pairs.push({ link: link, target: target });
    });
    if (!pairs.length) return;

    if (!('IntersectionObserver' in window)) return; // no spy without IO

    function setActive(activeLink) {
      links.forEach(function (l) {
        if (l === activeLink) l.setAttribute('aria-current', 'true');
        else l.removeAttribute('aria-current');
      });
    }

    // The strip near the top of the viewport (below the sticky header)
    // selects the active section: 45% down from top to 50% up from bottom.
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var pair = pairs.find(function (p) { return p.target === entry.target; });
          if (pair) setActive(pair.link);
        }
      });
    }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });

    pairs.forEach(function (pair) { obs.observe(pair.target); });
  }
})();
