# PROJECT_GUIDE — Emerson Plancarte Landing Page (Maintainer Handoff)

> Internal maintainer reference for this repository. Written 2026-07-06 after
> Phases 1–10 landed and the site was **run-verified for the first time** on a
> local PHP dev server. Feed this whole file back into context before adding
> or changing anything; it captures the contracts that aren't obvious from any
> single file and the footguns that have already bitten once.
>
> The user-facing deploy guide is `README.md` (six-step deploy, browser
> support, performance notes). This document is the deeper "how the machine
> fits together" companion. `CLAUDE.md` is unrelated — it's an archived
> system prompt, ignore it for maintenance work.

---

## 0. The two non-negotiable constraints (read first)

1. **Work only within the home directory.** Do not stray outside `html, php,
   css, js` and web-based stacks — *unless* you are working inside
   `portfolio/` (there, specific demo detail pages may reach into other
   stacks where justified: React, C#, Kotlin, C/C++ etc.). The landing-page
   shell itself (header, nav, footer, home, portfolio index, the contact
   endpoint, all shared CSS/JS) stays vanilla: no build step, no bundler, no
   npm, no framework.

2. **Every piece of user-visible content is bilingual EN + ES, authored in
   both languages.** English is primary; Spanish is a full peer, not a
   partial translation. Do not leave a Spanish string empty or as English
   fallback. Proper nouns and code names (C#, PostgreSQL, ESP32, Laravel,
   "CFE ZOTGM") are **not** translated. See §4 for the exact helpers.

Everything else flows from these two.

---

## 1. Stack, in one paragraph

Semantic HTML5. PHP 7.4+ **only** for server logic that can't be avoided:
shared includes (`includes/header.php`, `footer.php`, `nav.php`,
`i18n.php`) and the contact endpoint (`api/contact.php`). CSS3 with custom
properties, `clamp()` fluid type, `color-mix()`, and a paired
`-webkit-backdrop-filter` where needed. Vanilla JavaScript (no TypeScript,
no modules — IIFEs with `'use strict'`). Fonts are self-hosted woff2
subsets (no Google Fonts `<link>`). Zero raster images anywhere — the site
is type + inline SVG only. No `npm install`, no bundler, no build step. The
site is served from the host root, so all asset paths are root-absolute
(`/assets/...`, `/portfolio/...`).

---

## 2. How to run it locally (the dev server)

PHP is **portable**, not system-installed. It lives outside the project at

```
C:\Users\Emerson Plancarte\php-portable\php.exe
```

(PHP 8.5.8 ZTS, OPcache on, downloaded from windows.php.net.) Start the
built-in dev server from the project root **with `serve.php` as the router
script** — the router is what maps clean URLs (`/portfolio/icv/`) to their
source files (`portfolio/icv.php`). Without it, PHP's built-in server falls
back to the nearest ancestor `index.php`, so every `/portfolio/<slug>/`
silently renders the portfolio grid instead of the detail page (and bogus
URLs 200 as the home page instead of 404ing):

```
& "C:\Users\Emerson Plancarte\php-portable\php.exe" -S localhost:8000 serve.php
```

Then open `http://localhost:8000`. The process runs detached and survives the
shell call; kill it with `Get-Process php | Stop-Process -Force` when done.

Two local-only caveats: PHP's built-in server **ignores `.htaccess`**, so the
`storage/` web-denial rule and the gzip/cache headers are inert at `:8000`
(nothing secret in there — there's a blank `storage/index.html`). And
`mail()` finds no MTA locally, so the contact form's `mail()` silently fails
but **still logs to `storage/messages.csv`** — that's the durable record and
the front end reports success either way. This is by design, not a bug.

**Before changes** that touch JS or PHP: static-check fast with
`node --check <file.js>` (JS) and a brace/tag-balance scan for PHP (see
§18). **After changes**: probe with `Invoke-WebRequest` against
`http://localhost:8000/` so a runtime regression (the class of bug static
review can't see — see §17) is caught before handoff.

---

## 3. File tree (every file, with its job)

```
index.php                       Home page (hero, about, skills, experience, education, contact)
portfolio/
  index.php                     Portfolio index — filterable grid, driven by projects.json
  icv.php                       ICV detail page (canonical, the most detailed — see §13)
  grid-code-explorer.php        Grid Code Explorer detail (React SPA demo)
  laravel-rest-api.php          ICV Records API detail (Laravel demo)
  esp32-voltage-dashboard.php   ESP32 Voltage Monitor detail (embedded demo)
  kotlin-demo.php               Voltage Scout detail (Kotlin/Android demo)
  teassist.php                  PLACEHOLDER — honest stub; expand once stack/scope confirmed
  _README.md                    Notes for this folder
includes/
  header.php                    <head>, fonts, SEO/OG/JSON-LD, anti-FOUC script, opens <main>
  footer.php                    Closes <main>, footer, back-to-top button, loads the 6 JS files
  nav.php                       Site header + nav links + theme/lang/mobile-toggle buttons + drawer
  i18n.php                      t(), tb(), lang_attr() — the bilingual helpers (see §4)
api/
  contact.php                   POST-only JSON contact endpoint (see §14)
  _README.md
assets/
  css/
    tokens.css                  DESIGN TOKENS — single source of truth (see §5)
    base.css                    Modern reset + raw element defaults + bilingual CSS + reduced-motion guard
    components.css              Component primitives (buttons, cards, nav, hero, fields, tags, stats, timeline…)
    pages.css                   Page-level LAYOUT using the components (home, portfolio, project, contact form)
    animations.css              Motion layer (reveal, hover, theme cross-fade, ctaPulse, countGlow, reduced-motion safety)
    fx.css                      "Terminal Noir" effects layer — living aura, seamless cross-fading section tints, footer hairline, grain, cursor glow, palette, page wipe, tilt/magnetic bases, footer row
    fonts.css                  GENERATED — @font-face for the self-hosted subsets (see §6)
  js/
    main.js                     Theme + lang toggles, mobile nav, back-to-top, contact form (see §11)
    animations.js               Reveal (blur+rise), countUp (+count-done glow), scrollSpy (see §10)
    fx.js                       Scroll progress, cursor glow (mouse + touch twin), magnetic buttons, tilt cards (hover tilt + touch sheen), text scramble, timeline rail draw, Ctrl+K command palette (nav trigger visible on phones too), page wipe, footer clock, platform-aware ⌘/Ctrl labels
    fx-particles.js             Ambient ember field across the whole document; pointer repulsion (mouse) + touch repulsion (finger)
    webgl-hero.js               Vanilla-WebGL fluid-coupled aurora (stable-fluids wake: "hand through tinted water") + physics-driven wireframe polyhedron (arcball, momentum, pinch-zoom, tap pulse) behind the HOME and PORTFOLIO heroes; graceful fallbacks at every tier; window.__heroDebug test hook
    portfolio.js                Portfolio grid filtering (no-ops off the portfolio page)
  fonts/
    space-grotesk-latin.woff2, space-grotesk-latin-ext.woff2
    inter-latin.woff2, inter-latin-ext.woff2
    jetbrains-mono-latin.woff2, jetbrains-mono-latin-ext.woff2
    _fetch-fonts.js             DEV-ONLY (underscore prefix) — regenerates the 6 woff2 + fonts.css
  data/
    projects.json              Portfolio data — single source of truth for the grid (see §12)
    _README.md
  img/
    _README.md                  (social-card.png goes here when added — §19)
storage/
  messages.csv                  Contact submissions (CSV log — never web-served)
  .htaccess                     Apache denial (Deny from all)
  rl/                           Per-IP rate-limit json files (md5(ip).json), auto-created
  index.html                    Blank — index blocking only
robots.txt                      SEO — allow all, disallow /api/ /storage/ /styleguide.php, Sitemap line
sitemap.xml                     8 URLs, lastmod dates — origin must match header.php $site_url
styleguide.php                  INTERNAL design-system review page (not nav-linked)
.htaccess                       Optional Apache polish (gzip, cache headers, contact no-store)
README.md                       User-facing deploy guide (6 steps)
Development_Plan.md             The 10-phase plan this was built against
CV Emerson Plancarte.txt        Content source for the home page + portfolio
Instructions.txt                The user's original build instructions
PROJECT_GUIDE.md                THIS FILE
```

Anything not listed (e.g. `.claude/`) is tooling, not site source.

---

## 4. The bilingual system (THE most important section)

**Architecture:** both languages are *rendered into the HTML* tagged
`data-lang="en"` / `data-lang="es"`. CSS in `base.css` hides the inactive
one based on `<html data-active-lang>`:

```css
[data-active-lang="en"] [data-lang="es"] { display: none; }
[data-active-lang="es"] [data-lang="en"] { display: none; }
```

The toggle (in `main.js`) flips `data-active-lang` on `<html>`; CSS does the
rest instantly, no re-render. This was chosen over a JS dictionary so both
languages are crawlable, the toggle is JS-light, and the page degrades to
English if JS fails. The default is English (set on `<html>` in
`header.php`), and `header.php` runs an inline pre-paint script that
overrides `data-theme` + `data-active-lang` from `localStorage` before first
paint — anti-FOUC.

**Three helpers in `includes/i18n.php`.** Pick the right one; the wrong one
is a recurring bug source:

### `t($en, $es)` — inline text (the common case)
Emits two `<span data-lang="en">…</span><span data-lang="es">…</span>`
side by side. **Escapes** its input via `htmlspecialchars`. Use inside
headings, `<p>`, `<li>`, `<button>`, etc. Pass **plain text**: real Unicode
characters (`—`, `’`, `“ ”`, `·`, `ñ`, `¿`, `¡`), **NOT** HTML entities
(`&mdash;` would be double-escaped to literal `&mdash;` text). Never put
HTML tags inside `t()` — wrap the tag *around* the call instead:

```php
// GOOD — real Unicode, tag wraps the call
<h1><?= t('ICV — Voltage Quality', 'ICV — Calidad de Voltaje') ?></h1>

// BAD — entities double-escape; tags inside break the span
<h1><?= t('ICV &mdash; Voltage <em>Quality</em>', '…') ?></h1>
```

### `tb($en_html, $es_html)` — block HTML
Emits two `<div data-lang="en">…</div><div data-lang="es">…</div>`. **Not
escaped** — pass raw HTML; entities and tags render as written. Use where
the content has block elements (lists, multiple paragraphs, `<em>` inside
prose):

```php
<?= tb(
  '<p>Evaluating voltage meant pulling <em>PI</em> records…</p>',
  '<p>Evaluar el voltaje significaba extraer registros de <em>PI</em>…</p>'
) ?>
```

### `lang_attr($en, $es, $attr)` — attribute values (placeholder, aria-label)
Emits the **real attribute** (English default, so no-JS stays correct) plus
`data-en`, `data-es`, and a `data-i18n-attr` marker. `main.js`
(`syncAttrI18n()`) reads the marker and swaps the attribute to the active
language when the toggle flips. `$attr` is the real attribute name
(`'aria-label'` or `'placeholder'`):

```php
<input ... <?= lang_attr('Your name', 'Tu nombre', 'placeholder') ?>>
<!-- renders: placeholder="Your name" data-en="Your name" data-es="Tu nombre"
            data-i18n-attr="placeholder" -->
```

### The NEVERs
- **NEVER use `t()` inside an attribute value.** It renders `<span>s` whose
  double quotes terminate the attribute early and corrupt the markup.
  Attribute values must use `lang_attr()` (or a literal, if not translated).
- **NEVER feed HTML entities (`&mdash;`, `&rsquo;`, `&nbsp;`) into `t()`.**
  Use real Unicode.
- **NEVER leave the Spanish string empty or as English.** Both languages are
  first-class; if you can't write the Spanish yet, mark it `TODO(ES)` in the
  code and fix before any deploy, don't ship `t('English', '')`.
- **NEVER translate proper nouns / code names.** "C#", "PostgreSQL",
  "ESP32", "Laravel", "CFE ZOTGM", "Android", "Jetpack Compose" stay
  verbatim in both `t()` halves.

### JS-generated strings have their OWN dictionary (`assets/js/main.js`)
JS-produced strings (nav aria-labels, form validation, status messages) are
not in the HTML, so they can't use `t()`. They live in the `I18N` object at
the top of `main.js`'s IIFE, and the `t(key)` function there reads
`<html data-active-lang>` each call. When you add a JS-generated string,
add it to both the `en` and `es` blocks of that `I18N` object. (See §17 for
why the IIFE dispatch sits at the *bottom* — don't move it.)

---

## 5. The design-token system (`assets/css/tokens.css`)

`tokens.css` is the single source of truth for visual decisions. Components
consume tokens, **never raw hex/rem values**, so the whole system re-themes
by editing this one file. The categories:

- **Type families:** `--font-sans` (Inter), `--font-display` (Space
  Grotesk → Inter fallback), `--font-mono` (JetBrains Mono). Each with
  a system fallback chain.
- **Fluid type scale** `--step--2` … `--step-5` via `clamp(min, preferred,
  max)`, anchored at 1rem body. Grows with viewport, no media-query jumps.
  Label sizes: `--step--2` labels, `--step--1` small text, `--step-0` body,
  `--step-1` h4/lead, `--step-2` h3, `--step-3` h2, `--step-4` h1 section,
  `--step-5` hero.
- **Spacing** `--space-0` … `--space-8` on an 8px base
  (0, 0.5, 1, 1.5, 2, 3, 4, 6, 8 rem). `--space-7` is section gutters,
  `--space-8` large section gaps.
- **Radius** `--radius-sm` 8px / `--radius-md` 14px / `--radius-lg` 20px /
  `--radius-pill` 999px.
- **Motion** `--ease-out` / `--ease-in-out` / `--ease-spring` (cubic
  beziers) and `--dur-fast` 150ms / `--dur-base` 250ms / `--dur-slow` 450ms.
- **Layout** `--maxw-content` 72rem / `--maxw-wide` 80rem / `--maxw-prose`
  68rem.
- **Z-index** `--z-base` 1 / `--z-sticky` 50 / `--z-overlay` 100 /
  `--z-modal` 1000 — one place, avoids ad-hoc clouds.

### Color & theme
**Dark is the default** (`:root, [data-theme="dark"]`). Light is applied by
`[data-theme="light"]` on `<html>` (the nav toggle does this; `main.js`
persists the choice to `localStorage`; `header.php`'s pre-paint script
restores it). Color tokens: `--color-bg`, `--color-surface`,
`--color-surface-2`, `--color-surface-3`, `--color-overlay`, `--color-text`,
`--color-text-muted`, `--color-text-subtle`,
`--color-accent` / `-hover` / `-2` (secondary blue hue) / `-soft` /
`-contrast`, `--color-link` / `-hover`, `--color-danger` / `-hover` /
`-soft` (AA-checked in both themes — never use raw hex for errors),
`--color-border` / `-strong`, `-shadow-sm/md/lg/glow`, `--glass-bg`,
`--glass-border`, `--grad-hero`.

**Ambient tint tokens** (`--tint-green`, `--tint-blue`, `--tint-contact`)
feed the per-section washes painted by `.section::before` in `fx.css` —
literal gradients defined in both theme blocks, ~4–8% alpha, consumed
only via those tokens.

**Accent is voltage-green.** Dark `--color-accent: #3ddc97-family
(#36e8a0)`; light `#0a7549` (deliberately darkened from `#0b8052` so
accent-on-softBadge text clears WCAG AA 4.5:1; the `--color-text-subtle`
values were likewise adjusted to clear AA on every surface in both
themes). **Verified WCAG 2.2 AA** for body text ≥4.5:1 and large/non-text
≥3:1 in both themes.

### When adding color or a new component
- Define a new token in `tokens.css` if it's a *system-wide* decision; use an
  existing token if it's not. Never hardcode hex in a component.
- If the new thing must work in both themes, set its token in *both*
  `:root,[data-theme="dark"]` and `[data-theme="light"]` blocks.
- If you use `color-mix()`, always declare a **solid-color fallback line
  before** the `color-mix()` line (older engines drop the whole property
  when they see `color-mix`, so the fallback must come first). See §7.

---

## 6. The self-hosted font system

The two LCP fonts (Space Grotesk latin → h1; Inter latin → body) are
**preloaded with `crossorigin`** in `header.php`, before
`/assets/css/fonts.css`. `latin-ext` only fetches if a glyph in its range
actually renders — this site's content never triggers it, so realistic
page-load font cost is ~100 KB of woff2, same-origin, no third-party
preconnect or CSS round-trip.

`assets/css/fonts.css` is **generated** (18 `@font-face` rules, 6 unique
woff2 files: one deduped variable woff2 per family × subset, shared across
weights). To regenerate after changing weights/families:

```
node assets/fonts/_fetch-fonts.js
```

The `_` prefix marks it dev-only. It fetches the Google Fonts CSS2 API,
keeps only latin + latin-ext subsets, dedupes by source URL, and rewrites
`@font-face` `src` to `/assets/fonts/<file>`. **Do not hand-edit
`fonts.css`** — regenerate it. Latin covers all Spanish characters (ñ á é í
ó ú ¿ ¡ in Latin-1 Supplement; em-dash and curly quotes in general
punctuation).

---

## 7. The CSS architecture & feature-degradation rules

Six stylesheets, loaded in this order in `header.php`:
`tokens → base → components → pages → animations` (and `fonts` right before
them). The load order matters: tokens first so custom properties resolve;
base before components so element defaults exist; components before pages so
page layouts can compose them; animations last so motion sits on top.

**Three deliberate feature-degradation patterns to preserve:**

1. **`color-mix()` needs a solid fallback *before* the modern declaration.**
   Older engines that don't understand `color-mix` drop the *entire
   property*, so the fallback must precede it on its own line. There are 3
   such sites today (sticky header bg, link underline color, active filter
   border); keep that pattern when you add a fourth:
   ```css
   border-color: var(--color-accent);   /* fallback before color-mix */
   border-color: color-mix(in srgb, var(--color-accent) 32%, transparent);
   ```

2. **`backdrop-filter` needs the `-webkit-` prefix pair.** The sticky
   header already has it (`-webkit-backdrop-filter` then
   `backdrop-filter`). Safari is the reason; keep both.

3. **`clamp()` is universally supported since 2020** — no fallback needed
   for fluid type.

`pages.css` is page-level *layout*; `components.css` is component
*primitives*. When you add something, ask: is this a reusable primitive
(button, card, field) → `components.css`; is this how-the-page-arranges-it
(grid, section spacing) → `pages.css`. Keep raw rems/hex out of both — go
through tokens.

---

## 8. The motion & animation layer

Two files: `assets/css/animations.css` (CSS) and `assets/js/animations.js`
(JS). `base.css` has a global `@media (prefers-reduced-motion: reduce)`
guard that collapses every `animation-duration`/`transition-duration` to
~0ms and sets `scroll-behavior: auto` — so motion added *anywhere* is
auto-gated, **except** that an `opacity:0` hide would leave content
invisible under reduced motion, so `animations.css` also forces `.js
[data-reveal]` to `opacity:1 !important` there. Preserve this net.

The leading `.js` gate (the class is added pre-paint by `header.php`) means
**no-JS users see content immediately** — the reveal hide only applies once
JS is active. Don't drop the `.js` prefix on the reveal CSS.

### The reveal system (use this for any new section)
- `data-reveal` on an element → fades + lifts 16px into view when observed.
- `.is-visible` is added by the IntersectionObserver in `animations.js`
  (`initReveal`), once, then unobserved.
- `data-reveal-group` on a parent → its `[data-reveal]` children **stagger**
  by a per-item index set to `--reveal-i` by JS (capped at 8, ~60ms steps).
  Hero children stagger slower (capped 5, ~90ms).
- Root margin `0px 0px -10% 0px`, threshold `0.12` — reveals slightly
  before fully in view.

Add `data-reveal` to any new section/element you want to animate in. Wrap a
set of staggered items in a `data-reveal-group` parent. That's it — no
further wiring.

### `data-count-to="<int>"` (stat count-up)
An element with `data-count-to="80"` is parked at `0` at defer time
(pre-paint, no flash), then animates `0 → 80` when scrolled into view
(easeOutCubic, 1100ms, threshold 0.5). Under reduced motion, no IO, or no
rAF, it **snaps** to the final value — and the static source must show the
final value as the no-JS fallback (see the ICV stats: the markup literally
contains `>80</span><em>%</em>` so it reads correctly without JS):
```html
<span class="stat__value">><span data-count-to="80">80</span><em>%</em></span>
```
The inner `80` is the no-JS/reduced-motion value; JS overwrites it to `0`
then animates back to `80`. **Always put the final value in the source.**

### scrollSpy
`initScrollSpy` highlights the nav link whose `/#id` target is in view by
setting `aria-current="true"` (styled in `animations.css`/`components.css`).
Only home-page `/#id` links are watched; `/portfolio/` and off-page links
are skipped. Needs IntersectionObserver; no-ops otherwise.

### Theme cross-fade & ctaPulse
`animations.css` cross-fades token-bearing surfaces when `data-theme` flips
(targeted, not global — don't add `transition: all` anywhere). The first
contact-link (email) gets a one-shot soft ring (`ctaPulse`) shortly after
load; collapsed under reduced motion.

---

## 9. Theme & language toggle wiring

`<html>` carries two attributes the toggles flip: `data-theme` (`dark`|
`light`, default `dark`) and `data-active-lang` (`en`|`es`, default `en`).
`header.php`'s inline pre-paint script restores both from `localStorage`
before first paint (anti-FOUC). `main.js`:
- `initThemeToggle()` — flips `data-theme`, persists, calls
  `syncToggleState` (sets `aria-pressed`) and `applyToggleLabels` (sets
  the bilingual aria-label to name the *target* state — "Switch to dark
  theme" when light, etc.).
- `initLangToggle()` — flips `data-active-lang` AND `<html lang>`, persists,
  calls `syncLangState`, `applyToggleLabels`, and `syncAttrI18n` (swaps all
  `[data-i18n-attr]` attributes to the active language).
- The mobile nav drawer (`initNavDrawer`) — `body[data-nav-open]` drives a
  CSS dim+slide; JS handles toggle, Escape, click-outside, focus wrap,
  breakpoint close.

---

## 10. JS module map (6 files, all `defer`-loaded at end of `<body>`)

All are IIFEs with `'use strict'`, loaded in `footer.php` in this
order: `animations.js`, `webgl-hero.js`, `fx.js`, `fx-particles.js`,
`portfolio.js`, `main.js`. They're independent. All use the
**bottom-of-IIFE dispatch** pattern (see §17 — this is load-bearing,
don't "tidy" it to the top):

```js
(function name() {
  …all declarations + closure vars…
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
```

- **`main.js`** — `I18N` dictionary + `t()`, `applyToggleLabels`,
  `syncAttrI18n`, `initThemeToggle`, `initLangToggle`, `initNavDrawer`,
  `initBackToTop`, `initContactForm`. The contact form does live client
  validation (regexes in `validate()`), async submit to `/api/contact.php`
  with `submitViaApi()`, surfaces 422/429 server errors, and **falls back
  to `mailto:`** (`submitViaMailto`) if the endpoint is unreachable. Honeypot
  is the hidden `#cf-company` field — a non-empty value silently drops.
- **`animations.js`** — `initReveal`, `initCountUp`, `initScrollSpy` (§8).
- **`webgl-hero.js`** — self-boots wherever a `#hero-gl` canvas exists
  (home + portfolio index). Two coupled systems:
  *AURORA FLUID* — a stable-fluids layer (velocity + dye ping-pong
  half-float FBOs, splat → curl/vorticity → Jacobi pressure →
  gradient subtract → semi-Lagrangian advection). Cursor movement over
  the hero band and touch drags page-wide (window touchmove keeps
  firing during native scrolls) splat momentum into the field; the
  nebula shader warps its fbm domain around the velocity — the
  aurora structures already on screen bend and swirl where stirred.
  The dye field is only a disturbance mask that lifts the existing
  nebula light; it never injects new color, so the wake stays inside
  the page palette (subtle by design: band luminance moves a few
  percent, the warp does the talking). Requires the three
  half-float extensions — any missing piece (or reduced motion)
  skips the subsystem and the nebula keeps plain parallax.
  Desktop 128/256 grids at a 60 Hz fixed-dt sim; phones 96/192 at
  30 Hz; near-idle (two decay passes) once no splat arrives ~2.5 s.
  *POLYHEDRON PHYSICS* — persistent arcball orientation matrix
  (incremental view-space spins, Gram-Schmidt re-orthonormalized; no
  euler tumble). Drag tracks angular velocity → flick-to-spin
  momentum with dt-normalized damping; auto-rotation blends back in
  as it decays — one clean steady turn around the screen vertical,
  and NOTHING else may push the cage (no cursor-following torque:
  it rests centered and predictable, only a drag changes its motion).
  Pinch-zoom (two pointers, clamped, eased); tap =
  cage pulse + light drop; double-tap = quaternion-slerp ease home
  (matrix lerp stalls through degenerate space — see the file
  header). `touch-action: pan-y` keeps vertical pans native;
  pointercancel ends input with zero momentum. Satellite halo orbits
  are computed in the vertex shader (static buffer + u_time). All
  rates are dt-normalized; the ~24 ms frame gate is phone-tier
  only. `window.__heroDebug` (non-enumerable) exposes spin/camZ/
  pulse/reset/fluid/simSteps state for runtime assertions.
- **`fx.js`** — "Terminal Noir" interaction layer. Desktop (fine
  pointer): cursor spotlight, magnetic buttons, card tilt+sheen.
  Touch twins (coarse pointer): the spotlight follows the finger while
  touching; the card sheen follows the finger while pressed (no 3D
  rotation — scrolling is never fought). Palette is tappable on phones
  via `#palette-open` (visible at all widths, hidden without `.js`),
  with a pointer-aware bilingual hint line. Cosmetic `:hover` rules live
  behind `@media (hover: hover)` in the CSS; `@media (hover: none)`
  blocks carry the `:active` press feedback — no sticky tap-hover.
- **`fx-particles.js`** — ambient ember field in document space; the
  repulsion follows the mouse (fine) or the finger while touching
  (coarse). Reduced-motion renders a static constellation.
- **`portfolio.js`** — `filters()` IIFE. Bails if no `.filters` or
  `.portfolio__grid` on the page (so it's safe site-wide). Click delegation
  on the bar; `applyFilter(filter)` toggles `is-active` + `aria-pressed`
  on buttons and the native `hidden` attribute on `.portfolio__item` by
  `data-category`; shows/hides `.portfolio__empty`. Filtering by `hidden`
  means filtered-out cards leave layout entirely and returning cards are
  re-revealed by the observer when they scroll back into view.

---

## 11. Page anatomy — how a page is assembled

```php
<?php
$page_title       = ' Portfolio — Emerson Plancarte';      // <title> + OG
$page_description = 'Selected work by…';                   // <meta description> + OG
$body_class       = 'page-home';                            // optional: page-scoped CSS hook
// optional: $canonical, $og_type, $site_url (override per-page)
require __DIR__ . '/../includes/header.php';                // <head>, fonts, SEO, opens <main>
?>
  …page content, using t()/tb()/lang_attr() and the component classes…
<?php require __DIR__ . '/../includes/footer.php';          // closes <main>, footer, JS
```

`header.php` `require`s `i18n.php` itself (so every page + `nav.php` can
use the helpers without each re-requiring). It also `require`s `nav.php`
inside itself. The pattern is: page sets vars → requires header → emits
content → requires footer. `header.php` opens `<main id="main">` (with a
`skip-link` to it); `footer.php` closes `</main>`. **Always pair them.**

Container widths: `.container` (default ~`--maxw-content`),
`.container--prose` (narrower, `--maxw-prose`) for long-form case-study
text, `.container--wide` for the portfolio grid. Section rhythm:
`.section` + `.subsection` give standard block spacing; `.stack` is a
vertical-rhythm utility used inside blocks.

---

## 12. The portfolio data model (`assets/data/projects.json`)

Single source of truth for the **grid cards only** (title, subtitle,
category, year, stack, summary, links). Each project *also* has a
hand-written bilingual detail page at `portfolio/<slug>.php` (the JSON does
NOT contain the case-study body — that's authorial and lives in the PHP).

Schema:
```jsonc
{
  "categories": { "web": {"en":"Web","es":"Web"}, "backend":{…}, "embedded":{…}, "mobile":{…} },
  "projects": [
    {
      "slug": "icv",                     // matches portfolio/<slug>.php
      "title":      { "en": "…", "es": "…" },
      "subtitle":   { "en": "…", "es": "…" },
      "category":   "web",               // a key in `categories`
      "year":       "2024–2026",
      "featured":   true,                 // optional; true → spans 2 cols on wide grids
      "stack":      ["…","…"],            // proper nouns, NOT translated; [] is valid
      "summary":    { "en": "…", "es": "…" },
      "links":      { "detail": "/portfolio/icv.php" }
    }
  ]
}
```

`portfolio/index.php` reads this JSON, then renders a filter bar (one button
per category + an "All" default) and a grid of cards. **Bilingual JSON
fields are emitted as dual `data-lang` spans via the local `bi_field()`
helper** in `portfolio/index.php` (it mirrors the `t()` contract so the
EN/ES toggle hides the right language without rebuilding the grid). Stack
tags are rendered as `.tag` chips. `featured: true` marks the strongest
projects (they populate the home-page showcase); on the portfolio grid only
   the FIRST featured card takes the double-width lead slot (`grid-column:
   span 2`, released under 42em) — the grid uses fixed column counts
   (1/2/3 by breakpoint) with `grid-auto-flow: dense` so rows always pack
   evenly. The home page renders its featured cards as a uniform 2×2 mosaic
   on wide viewports (no spans).

---

## 13. How to add a new portfolio project (the common task)

1. **Add an entry to `assets/data/projects.json` → `projects[]`** with a
   unique `slug`, both-language `title`/`subtitle`/`summary`, the right
   `category` key, `year`, `stack` (proper
   nouns), and `links.detail` pointing at the PHP page you'll create. Set
   `featured: true` only for the strongest one (currently ICV).
2. **Create `portfolio/<slug>.php`** by copying `portfolio/icv.php` as the
   canonical template (it's the most complete; §13.5 below). Update
   `$page_title`, `$page_description`, and the body. Keep the structure:
   `<section class="section project">` → `.container--prose` →
    `.project__back` link → `.project__header` (`h1.project__title` +
    `.project__meta` dl + `.project__stack` tag-list) →
    `.project__callout` → `.project__body.stack`
   with `data-reveal-group` and `data-reveal` children → `.project__links`.
3. **Translate every prose string** via `t()` or `tb()`. Keep stack tags
   untranslated. If you include a code figure, use the
   `.project__code` / `.project__code__bar` / `<pre><code>` pattern.
4. **If you add a new category** (not `web`/`backend`/`embedded`/`mobile`),
   you must add it to the `categories` object in `projects.json` **and**
   the `data-category` on the card will then match a new filter button
   automatically (the filter bar is generated from `categories`).
   `portfolio.js` needs no change.
5. **Update `sitemap.xml`** — add a `<url><loc>` for the new detail page
   with the deploy origin (see §15). The grid card links there.
6. **Probe it**: run the dev server, hit `/portfolio/` (card appears, filter
   works), click through to `/portfolio/<slug>.php` (200, renders).

### 13.5 The canonical detail-page skeleton (from `icv.php`)
```php
<?php
$page_title = '<Title> — Emerson Plancarte';
$page_description = '<1–2 sentences for meta/OG>';
require __DIR__ . '/../includes/header.php';
?>
<section class="section project" aria-labelledby="project-title">
  <div class="container container--prose">
    <a class="project__back" href="/portfolio/"><?= t('All projects','Todos los proyectos') ?></a>
    <header class="project__header" data-reveal>
      <p class="project__cat">
        <?= t('Web · CFE ZOTGM','Web · CFE ZOTGM') ?>
      </p>
      <h1 class="project__title" id="project-title"><?= t($en,$es) ?></h1>
      <p class="project__subtitle"><?= t($en,$es) ?></p>
      <dl class="project__meta">
        <div><dt><?= t('Role','Rol') ?></dt><dd><?= t('…','…') ?></dd></div>
        <div><dt><?= t('Period','Periodo') ?></dt><dd>2024 — 2026</dd></div>
        …
      </dl>
      <ul class="tag-list project__stack" <?= lang_attr('Stack','Pila tecnológica','aria-label') ?>>
        <li><span class="tag">C#</span></li> …
      </ul>
    </header>
    <div class="project__callout" data-reveal>…</div>
    <div class="project__body stack" data-reveal-group>
      <div class="stack" data-reveal><h2><?= t('The problem','El problema') ?></h2><?= tb($enHtml,$esHtml) ?></div>
      …
      <div class="stack" data-reveal>
        <h2><?= t('Outcome','Resultado') ?></h2>
        <ul class="stats" <?= lang_attr('Headline outcomes','Resultados destacados','aria-label') ?>>
          <li class="stat"><span class="stat__value">><span data-count-to="80">80</span><em>%</em></span>
            <span class="stat__label"><?= t('Faster analysis','Análisis más rápido') ?></span></li>
          …
        </ul>
      </div>
    </div>
    <div class="project__links" data-reveal>
      <a class="btn btn--primary" href="/#contact"><?= t('Ask about this project','Pregunta sobre este proyecto') ?></a>
      <a class="btn btn--secondary" href="/portfolio/"><?= t('Back to portfolio','Volver al portafolio') ?></a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php';
```

---

## 14. The contact endpoint (`api/contact.php`)

POST-only, JSON-in/JSON-out. Reads JSON (preferred) or
`application/x-www-form-urlencoded` (no-JS fallback). Returns:
- `200 {ok:true}` — accepted (mailed best-effort + CSV-logged)
- `400 {ok:false,error:"bad-request",message}` — body unreadable / > 16 KB
- `405 {ok:false,error:"method",message}` — non-POST (sets `Allow: POST`)
- `422 {ok:false,errors:{name,email,message}}` — validation failed
- `429 {ok:false,error:"rate-limited",message}` — over per-IP throttle

Pipeline: method gate → parse → **rate-limit (5/IP/hr, file-based at
`storage/rl/<md5(ip)>.json`)** → **honeypot `company` field (silent
`200 {ok:true}` if filled — bot bait)** → validate → deliver (`mail()`
best-effort + always CSV-log) → respond.

**Config constants** (top of file): `MAIL_TO` = `emersonplancarte@gmail.com`
(public, from CV — don't change), `MAIL_FROM` = `no-reply@emerson-plancarte.local`
(**placeholder — set to your deploy domain on deploy**), `RL_WINDOW=3600`,
`RL_MAX=5`, `MSG_MIN=10`, `MSG_MAX=4000`, `NAME_MAX=120`, `MAX_BODY=16384`,
`STORAGE=__DIR__ . '/../storage'`.

Bilingual error messages keyed off the payload's `lang` field (which
`main.js` sets from `<html data-active-lang>`). Hardening: `display_errors`
off (never leak internals), `X-Content-Type-Options: nosniff`,
`Referrer-Policy: no-referrer`, `Cache-Control: no-store`. CRLF stripped
from name/email (`clean()`) — no header/log injection. `client_ip()` uses
`REMOTE_ADDR` only (does NOT trust `X-Forwarded-For` without a known proxy
— a spoofable header would let a client dodge the throttle).

CSV columns: `timestamp,ip,name,email,message,sent` (`sent` is `1`/`0` —
`0` means `mail()` didn't deliver, but the row is the record). Storage/
must NOT be web-served — `storage/.htaccess` denies Apache; on nginx or
.htaccess-disabled hosts, move `storage/` above the web root and point
`STORAGE` at the absolute path.

### Front-end contract (`main.js` `initContactForm`)
The form is `#contact-form` with `#cf-name`, `#cf-email`, `#cf-message`,
honeypot `#cf-company`, status region `#cf-status` (paired with
`aria-describedby` live regions per field: `cf-name-err`, `cf-email-err`,
`cf-message-err`), submit `#cf-submit`. On submit: preventDefault, bail if
honeypot filled, `validate()` (live regex email check), POST JSON, then:
200 → success status + reset; 422 → surface server field errors onto the
fields; 429 → show server message; any other failure (404/500/network) →
**mailto: fallback** so the message still reaches Emerson.

---

## 15. SEO: the origin must agree in THREE places

There is a placeholder origin `https://emerson-plancarte.example` set in
**three files that must all match on deploy**:

1. `includes/header.php` → `$site_url` (drives canonical, OG tags, and the
   JSON-LD `Person` `url`).
2. `robots.txt` → the `Sitemap:` line.
3. `sitemap.xml` → every `<loc>` (and the `lastmod` dates should be
   refreshed at deploy).

When you change the domain, update all three together. The JSON-LD
`Person` schema in `header.php` is static and site-wide (name, jobTitle,
email, phone, Acapulco address, LinkedIn, GitHub `Emerson3101`,
`knowsLanguage [es,en]`, `knowsAbout` the skill list) — it was hand-written,
not generated; update it if Emerson's details change.

`robots.txt` allows all, disallows `/api/`, `/storage/`, `/styleguide.php`.
`sitemap.xml` lists 8 URLs: home, `/portfolio/`, and the 6 detail pages
(teassist is included). `styleguide.php` is intentionally not nav-linked
(internal review only).

---

## 16. How to add a new section to the home page

`index.php` is the home page. It's one `<main>` of `<section>`s, each with
an `id` matched by a nav link (`/#about`, `/#skills`, `/#experience`,
`/#education`, `/#contact`). To add a section:

1. Add a `<section class="section" id="newid" aria-labelledby="newid-title">`
   with a `<h2 id="newid-title">` heading using `t()`.
2. Put `data-reveal` on it (or `data-reveal-group` if its children stagger).
3. Add a nav link in `includes/nav.php`'s `$nav_links` array
   (`['href' => '/#newid', 'en' => 'New', 'es' => 'Nuevo']`) — it's the one
   source for both the inline links and the mobile drawer.
4. Layout via `pages.css` (grid/spacing); components via `components.css`.
5. `scrollSpy` will pick it up automatically (it watches any `/#id` link
   with a matching element). Smooth-scroll + `scroll-margin-top`
   (sticky-header clearance) are already handled in `base.css` +
   `animations.css`.

---

## 17. THE big lesson — runtime bugs that static review can't see

This site had a latent bug from the day `main.js` was written until the
first local run on 2026-07-05: `Cannot read properties of undefined
(reading 'en')` at `main.js:75`. It crashed the entire JS layer on every
page load — every toggle, the nav drawer, back-to-top, contact-form JS all
silently dead. The contact form's POST still "worked" only because it was
tested by hitting the endpoint directly, bypassing the JS.

**Root cause:** the IIFE's dispatch sat at the *top* of `main.js`:
```js
(function bootstrap() {
  if (document.readyState === 'loading') { … } else { init(); }  // ← called here
  function init() { initThemeToggle(); … }                          // hoisted
  …
  var I18N = { en:{…}, es:{…} };                                    // NOT yet initialized
})();
```
Function declarations hoist, but `var` initializers don't — they only have
their *declaration* hoisted (value `undefined`). Because `main.js` is
`defer`-loaded, `document.readyState` is `'interactive'` (not `'loading'`)
when it runs, so the `else { init(); }` branch fired **synchronously
mid-IIFE**, before the `var I18N = {...}` assignment a few lines down had
executed. So `t('switchToLight')` → `I18N.en` threw. Brace-balance was
fine, `node --check` was fine — it was a *runtime ordering* bug.

**The fix** (now in place, and the convention for all 3 JS files): the
dispatch sits at the **bottom** of the IIFE, after every closure var is
initialized. There is a comment block explaining why — **don't "tidy" it
back to the top.**

**Lesson for future-you:** static review (brace-balance, `node --check`,
JSON validity) is necessary but not sufficient. Before declaring a JS/PHP
change done, **run the dev server and probe with `Invoke-WebRequest`**, and
for JS changes **open the browser console and hard-reload** (`Ctrl+F5` —
PHP's built-in server sets no cache headers, but the browser cached the
first load). A bug hiding behind a throw is invisible until the throw is
fixed, so after fixing one runtime error, re-check the console — there may
be a second that the first was masking.

---

## 18. Verification conventions (cheap, before/after changes)

- **JS syntax:** `node --check assets/js/<file>.js` → echoes nothing on
  pass. (Doesn't catch runtime ordering — see §17.)
- **PHP tag balance:** a quick scanner counts `<?php` + `<?=` opens vs `?>`
  closes. header.php uses *both* `<?php` (×1) and `<?=` short-echo (×many)
  in its HTML head — count both, or it false-fails. (A prior session's
  scanner only counted `<?php` and reported a spurious FAIL. The file was
  fine; the scanner was wrong.)
- **JSON validity:** `node -e "JSON.parse(require('fs').readFileSync('assets/data/projects.json','utf8')); console.log('OK')"`
  — quick check after editing `projects.json`.
- **Runtime probe (the strong check):** with the dev server running,
```
Invoke-WebRequest http://localhost:8000/ -UseBasicParsing | Select-Object StatusCode, @{n='Len';e={$_.Content.Length}}
```
  and do the same for `/portfolio/`, a detail page, `/assets/css/tokens.css`,
  `/assets/css/fonts.css`, a woff2, and `POST /api/contact.php` with a
  small JSON body. Expect 200 across the board; check `storage/messages.csv`
  for the new row after the contact POST.
- **Contrast:** if you change a color token, re-check WCAG 2.2 AA (4.5:1
  body / 3:1 large) **in both themes, on every surface that uses it**
  (bg, surface, surface-2). The `--color-text-subtle` and light-accent
  values were specifically tuned to clear AA everywhere — don't loosen
  them without re-checking.

---

## 19. Known pending items (flagged in code, blocked on external input)

One item is openly pending and explicitly marked. Don't silently "finish"
it — it's blocked on Emerson.

1. **The social card image is a TODO in `includes/header.php`.** Add
   `assets/img/social-card.png` at 1200×630 and uncomment the two meta tags
   (`og:image` / `twitter:image`) at the marked spot in `header.php`. The
   asset can't be generated here.

---

## 20. Deploy checklist (summary — full version in `README.md`)

1. Upload the entire project to a PHP 7.4+-enabled host, served from the
   web root (root-absolute paths require it).
2. Set `$site_url` in `includes/header.php` to the real origin.
3. Update the origin in `robots.txt` (`Sitemap:` line) **and** every
   `<loc>` in `sitemap.xml` to match. All three (header.php, robots.txt,
   sitemap.xml) must agree.
4. Set `MAIL_FROM` in `api/contact.php` to an address on the deploy domain
   (currently the placeholder `no-reply@emerson-plancarte.local`). `MAIL_TO`
   is Emerson's public inbox — don't change.
5. Ensure `storage/` exists, is writable by the PHP process (mode 0775),
   and is **NOT web-served** (`storage/.htaccess` denies Apache; on nginx
   or .htaccess-disabled hosts, move `storage/` above the web root and
   point the `STORAGE` constant at the absolute path).
6. Optional but recommended: enable an MTA so `mail()` actually delivers.
   Without a mailer, the CSV log is the durable record and the front end
   reports success either way.
7. After deploy, smoke-test: load the home page in both themes and both
   languages, submit the contact form once (confirm the row in
   `storage/messages.csv`), click through the portfolio to each detail
   page.

---

## 21. Quick "I want to…" index

- **…add a portfolio project** → §13.
- **…add a home-page section** → §16.
- **…add a bilingual string** → §4 (`t` for inline, `tb` for HTML, `lang_attr`
  for attributes; JS strings go in `main.js`'s `I18N`).
- **…add a color / re-theme** → §5 (`tokens.css`; both theme blocks).
- **…add motion to a new section** → §8 (`data-reveal` / `data-reveal-group`).
- **…add a stat counter** → §8 (`data-count-to`, final value in source).
- **…change the contact config/throttle** → §14 (`api/contact.php` constants).
- **…regenerate fonts after weight/family changes** → §6
  (`node assets/fonts/_fetch-fonts.js`; don't hand-edit `fonts.css`).
- **…change the deploy domain** → §15 (three files together).
- **…verify a change before handoff** → §18 (and §17 — always run it).
- **…run locally** → §2.
