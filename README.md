# Emerson Plancarte — Personal Landing Page

Bilingual (English + Spanish) personal landing page and portfolio for Emerson
Salvador Plancarte Cerecedo — software and embedded-systems engineer. One home
page and a filterable portfolio of seven project detail pages, built vanilla
on a no-build-step web stack: semantic HTML5, hand-written CSS3 with custom
properties, and vanilla JavaScript, with PHP only for the shared
header/footer includes and the contact-form endpoint.

Content is sourced from `CV Emerson Plancarte.txt`; the build was phased per
`Development_Plan.md` across ten phases. Both themes (dark default, light
toggle), both languages, and a reduced-motion path are all first-class.

## Stack and constraints

The main site is intentionally framework-free and has no build step, no
bundler, and no `npm` install — it edits and deploys as written. PHP is used
only where server logic is unavoidable: shared includes
(`includes/header.php`, `footer.php`, `nav.php`, `i18n.php`) and the contact
endpoint (`api/contact.php`). The portfolio detail pages may reach into other
stacks where a specific demo justifies it, but the landing-page shell never
depends on them. Everything stays within this directory.

## Local preview

The site is served from the host root, so asset paths are root-absolute
(`/assets/...`, `/portfolio/...`). Run PHP's built-in server from the project
root, **using `serve.php` as the router** (it maps clean URLs like
`/portfolio/icv/` to `portfolio/icv.php`), and visit `http://localhost:8000`:

```
php -S localhost:8000 serve.php
```

That single command serves the pages, the PHP includes assemble, and the
contact endpoint works (it will log to `storage/messages.csv` and attempt
`mail()` if an MTA is present). The contact form's `mailto:` fallback covers
the no-PHP case, so the static HTML/CSS/JS surface alone is still presentable.

## Project layout

```
index.php                 home page
portfolio/                portfolio index + 7 project detail pages
includes/                 header, footer, nav, i18n helpers
api/contact.php           contact form endpoint (JSON in/out)
assets/css/               tokens, base, components, pages, animations, fonts
assets/js/                main, animations, portfolio (all deferred)
assets/fonts/             self-hosted woff2 subsets + the _fetch-fonts.js helper
assets/data/projects.json portfolio data (single source of truth for the grid)
storage/                 contact CSV log + rate-limit files (NOT web-served)
robots.txt, sitemap.xml   SEO (Phase 8)
styleguide.php            internal design-system review page (not nav-linked)
```

## Deploy

1. Upload the entire project to a PHP-enabled host, preserving the directory
   structure, so the site is served from the web root. (A static host is not
   enough — the contact endpoint and the shared includes require PHP 7.4+.)
2. Set the real origin in `includes/header.php`. The `$site_url` fallback is
   currently the placeholder `https://emerson-plancarte.example`; it feeds the
   canonical URL, Open Graph tags, and JSON-LD `Person` schema, so it must be
   correct for the live domain.
3. Update the origin in `robots.txt` (the `Sitemap:` line) and in
   `sitemap.xml` (every `<loc>`) to match that same domain. All three —
   `header.php`, `robots.txt`, `sitemap.xml` — must agree.
4. Set `MAIL_FROM` in `api/contact.php` to an address on your deploy domain
   (it is currently the placeholder `no-reply@emerson-plancarte.local`). It is
   used as the `From:` header on outbound contact mail. `MAIL_TO` already
   points at Emerson's public inbox and should not normally change.
5. Make sure the `storage/` directory exists and is writable by the PHP
   process (mode `0775` is what the endpoint requests). It holds
   `messages.csv` and the per-IP rate-limit files. It must NOT be
   web-served: `storage/.htaccess` denies Apache — on nginx or any host with
   `.htaccess` disabled, move `storage/` above the web root and point the
   `STORAGE` constant in `api/contact.php` at its absolute path instead.
6. Optional but recommended: enable an MTA (or a configured `sendmail`/SMTP
   wrapper) so `mail()` actually delivers. On a host without a mailer, the CSV
   log in `storage/` is the durable record of submissions; the front end
   reports success either way because the message is captured.

After deploy, smoke-test: load the home page in both themes and both
languages, submit the contact form once and confirm the row appears in
`storage/messages.csv`, and click through the portfolio to each detail page.

## Browser support

Targets the modern evergreen set (current Chromium, Firefox, Safari, Edge).
A few modern CSS features are used deliberately and each degrades gracefully:
the sticky header, the active portfolio filter border, and the link underline
tint all declare a solid-color fallback before their `color-mix()` override,
and the header backdrop has a paired `-webkit-` prefix plus the solid
fallback. Fluid type uses `clamp()` (universally supported since 2020).
`prefers-reduced-motion` disables the animation layer for users who request
it. No raster images are used — the site is type + inline SVG — so there is
nothing to lazy-load or re-encode.

## Performance

Self-hosted font subsets are the load-bearing optimization: only the `latin`
and `latin-ext` woff2 subsets of Space Grotesk (display), Inter (body), and
JetBrains Mono (code) are served from `assets/fonts/`, one deduped variable
file per family and subset (six files total). The two LCP fonts (Space
Grotesk latin for the hero heading, Inter latin for body) are preloaded with
`crossorigin` in `header.php`; `latin-ext` fetches only if a glyph in its
range actually renders, which this site's content does not trigger. Realistic
page-load font cost is roughly 100 KB of woff2, same-origin, with no
third-party preconnect or CSS round-trip. Total CSS is about 60 KB across
six files; the three JS files are small and `defer`-loaded at the end of
`<body>`. To regenerate the subsets, run
`node assets/fonts/_fetch-fonts.js` (the underscore prefix marks it a
dev-only helper).

## Accessibility and i18n

Bilingualism is implemented by rendering both languages into the HTML tagged
`data-lang="en"`/`"es"` and showing the active one via CSS on
`<html data-active-lang>` — the toggle is instant and JS-light, both
languages are crawlable, and it degrades to the English default without JS.
Translated attribute values (aria-labels, placeholders) go through the
`lang_attr()` helper, which emits an English default plus `data-en`/`data-es`
markers that `main.js` swaps to the active language on toggle. Color contrast
was verified to WCAG 2.2 AA (4.5:1) across both themes, the contact form
pairs every input with a label and live error region, the mobile nav traps
focus and closes on Escape, and all interactive surfaces have a visible
`focus-visible` indicator.

## Still to populate

Two items remain openly pending and are flagged in the code:

- `portfolio/teassist.php` is an honest placeholder. TecAssist is a real
  first-place innovation-contest winner (Smart Cities, December 2025), but
  its stack and scope are to be confirmed so the page stays accurate rather
  than invented. Once Emerson provides the specifics, expand that detail page
  to match the depth of the ICV case study.
- The social card image (`og:image` / `twitter:image`) is a TODO in
  `includes/header.php` — add `assets/img/social-card.png` at 1200×630 and
  uncomment the two meta tags.
