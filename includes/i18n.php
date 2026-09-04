<?php
/**
 * i18n — bilingual content helpers (EN primary, ES secondary).
 *
 * Architecture: both languages are RENDERED into the HTML and tagged
 * with data-lang="en" / data-lang="es". CSS hides whichever is not
 * active, where "active" is the data-active-lang attribute on <html>
 * (default "en", set in header.php and overridden pre-paint from
 * localStorage). The nav toggle flips that attribute.
 *
 * Why dual-render over a JS dictionary:
 *   - Content stays in the HTML → crawlable by search engines in both
 *     languages (an SEO concern addressed in Phase 8; per-language URLs
 *     are a possible later enhancement).
 *   - Toggle is instant and JS-light (one attribute flip + CSS).
 *   - Degrades gracefully: if JS fails, the PHP default language shows.
 *
 * Helpers:
 *   t($en, $es)        — inline strings. Emits two <span>s side by side;
 *                       use inside headings, <p>, <li>, etc. ESCAPES its
 *                       input via htmlspecialchars, so pass PLAIN TEXT:
 *                       use real Unicode (— ’ “ ” ·), NOT HTML entities
 *                       (&mdash; etc. — those would be double-escaped to
 *                       literal text), and never put HTML tags inside t()
 *                       (wrap the tag around the t() call instead).
 *   tb($en_html, $es_html) — block strings. Emits two <div>s; use where
 *                       the content itself contains block elements
 *                       (lists, multiple paragraphs). NOT escaped — pass
 *                       raw HTML; entities and tags render as written.
 *   lang_attr($en, $es, $attr) — for attribute values that need translating
 *                       (placeholders, aria-labels). Emits the real attribute
 *                       (English default, so no-JS stays correct) plus
 *                       data-en/data-es + a data-i18n-attr marker; main.js
 *                       reads the marker and swaps the attribute to the
 *                       active language when the toggle flips. $attr is the
 *                       real attribute name ('aria-label' or 'placeholder').
 *                       NEVER use t() inside an attribute — it renders <span>s
 *                       whose double quotes terminate the attribute early.
 *
 * NOTE: only output user-visible prose through these. Proper nouns and
 * code (skill names like "C#", "PostgreSQL") are not translated.
 */

if (!function_exists('t')) {
  function t($en, $es) {
    return '<span data-lang="en">' . htmlspecialchars($en, ENT_QUOTES, 'UTF-8') . '</span>'
         . '<span data-lang="es">' . htmlspecialchars($es, ENT_QUOTES, 'UTF-8') . '</span>';
  }
}

if (!function_exists('tb')) {
  function tb($en_html, $es_html) {
    return '<div data-lang="en">' . $en_html . '</div>'
         . '<div data-lang="es">' . $es_html . '</div>';
  }
}

if (!function_exists('lang_attr')) {
  /** Emits a real attribute (English default) plus data-en/data-es and a
   *  data-i18n-attr marker, so main.js can swap the value to the active
   *  language when the toggle flips. $attr is the real attribute name —
   *  'aria-label' or 'placeholder'. The English default keeps no-JS correct.
   *  Use: <input ... <?= lang_attr('Your name', 'Tu nombre', 'placeholder') ?>>
   *  NEVER use t() inside an attribute value — it renders <span>s whose
   *  double quotes terminate the attribute early and corrupt the value. */
  function lang_attr($en, $es, $attr = 'aria-label') {
    $h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
    return $attr . '="' . $h($en) . '" '
         . 'data-en="' . $h($en) . '" '
         . 'data-es="' . $h($es) . '" '
         . 'data-i18n-attr="' . $h($attr) . '"';
  }
}
