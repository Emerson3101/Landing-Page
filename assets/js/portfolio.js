/* =====================================================================
   portfolio.js — Portfolio filtering & live search
   ---------------------------------------------------------------------
   Progressive enhancement for the portfolio index:
     - Category filter buttons (All, Industrial, AI, Web, Mobile, Backend)
     - Live search filter matching titles, descriptions, and tech stacks
     - Live empty state handling
   ===================================================================== */

'use strict';

(function filters() {
  var bar = document.querySelector('.filters');
  var grid = document.querySelector('.portfolio__grid');
  var searchInput = document.getElementById('portfolio-search');
  if (!grid) return;

  var buttons = bar ? Array.prototype.slice.call(bar.querySelectorAll('.filter')) : [];
  var items = Array.prototype.slice.call(grid.querySelectorAll('.portfolio__item'));
  var emptyMsg = document.querySelector('.portfolio__empty');
  var currentCategory = 'all';
  var currentQuery = '';

  function evaluateFilters() {
    var query = currentQuery.trim().toLowerCase();
    var anyVisible = false;

    items.forEach(function (item) {
      var catMatch = currentCategory === 'all' || item.getAttribute('data-category') === currentCategory;
      var textMatch = true;

      if (query) {
        var cardText = (item.textContent || '').toLowerCase();
        textMatch = cardText.indexOf(query) !== -1;
      }

      var visible = catMatch && textMatch;
      item.hidden = !visible;
      if (visible) anyVisible = true;
    });

    if (emptyMsg) emptyMsg.hidden = anyVisible;
  }

  if (bar) {
    bar.addEventListener('click', function (e) {
      var btn = e.target.closest('.filter');
      if (!btn) return;
      var filter = btn.getAttribute('data-filter');
      currentCategory = filter;

      buttons.forEach(function (b) {
        var active = b.getAttribute('data-filter') === filter;
        b.classList.toggle('is-active', active);
        b.setAttribute('aria-pressed', String(active));
      });

      evaluateFilters();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentQuery = searchInput.value;
      evaluateFilters();
    });
  }
})();
