/* =====================================================================
   portfolio.js — grid filtering (Phase 5)
   -------------------------------------------------------------------
   Progressive enhancement for the portfolio index. Toggles the native
   `hidden` attribute on .portfolio__item elements by data-category, so
   cards that are filtered out leave the layout entirely and cards that
   return are re-evaluated by the reveal observer (animations.js) when
   they scroll back into view. No-ops cleanly when the filter bar or grid
   is absent, so it is safe to load on every page. Loaded with `defer`.
 * ===================================================================*/

'use strict';

(function filters() {
  var bar = document.querySelector('.filters');
  var grid = document.querySelector('.portfolio__grid');
  if (!bar || !grid) return; // not the portfolio page — bail

  var buttons = Array.prototype.slice.call(bar.querySelectorAll('.filter'));
  var items = Array.prototype.slice.call(grid.querySelectorAll('.portfolio__item'));
  var emptyMsg = document.querySelector('.portfolio__empty');
  if (!buttons.length) return;

  // Click delegation: any .filter click drives the whole bar.
  bar.addEventListener('click', function (e) {
    var btn = e.target.closest('.filter');
    if (!btn) return;
    applyFilter(btn.getAttribute('data-filter'));
  });

  // Keyboard: filters are real <button>s, so Enter/Space already fire click.
  function applyFilter(filter) {
    var anyVisible = false;
    buttons.forEach(function (b) {
      var active = b.getAttribute('data-filter') === filter;
      b.classList.toggle('is-active', active);
      b.setAttribute('aria-pressed', String(active));
    });
    items.forEach(function (item) {
      var match = filter === 'all' || item.getAttribute('data-category') === filter;
      item.hidden = !match;          // native hidden — out of layout + a11y tree
      if (match) anyVisible = true;
    });
    if (emptyMsg) emptyMsg.hidden = anyVisible;
  }
})();
