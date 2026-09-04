#!/usr/bin/env node
/* =====================================================================
 * _fetch-fonts.js — one-shot Phase 9 helper (run with node, then delete).
 * -------------------------------------------------------------------
 * Downloads only the latin + latin-ext woff2 subsets for the three
 * families the site actually uses, and emits assets/css/fonts.css with
 * @font-face rules rewritten to root-absolute local paths (/assets/
 * fonts/<file>). Latin covers all Spanish characters used on the site
 * (ñ á é í ó ú ¿ ¡ live in Latin-1 Supplement; em-dash/curly quotes in
 * general punctuation) plus ASCII; latin-ext is fetched as insurance
 * and the browser only loads it if a glyph in that range is rendered.
 *
 * This replaces the third-party Google Fonts <link> (two preconnects +
 * one CSS request + a per-weight woff2) with a single first-party CSS
 * file + first-party woff2s — removing the cross-origin round-trips
 * that were the biggest LCP lever on this otherwise asset-light page.
 * ===================================================================*/

'use strict';

const https = require('https');
const fs = require('fs');
const path = require('path');

const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';
const CSS_URL = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Space+Grotesk:wght@500;600;700&display=swap';

const OUT_DIR = __dirname;                 // assets/fonts/
const FONTS_CSS = path.join(__dirname, '..', 'css', 'fonts.css');
const WANTED_SUBSETS = ['latin', 'latin-ext'];

function fetch(url, asText) {
  return new Promise((resolve, reject) => {
    https.get(url, { headers: { 'User-Agent': UA } }, (res) => {
      if (res.statusCode !== 200) {
        reject(new Error('GET ' + url + ' -> HTTP ' + res.statusCode));
        return;
      }
      const chunks = [];
      res.on('data', (c) => chunks.push(c));
      res.on('end', () => {
        const buf = Buffer.concat(chunks);
        resolve(asText ? buf.toString('utf8') : buf);
      });
      res.on('error', reject);
    }).on('error', reject);
  });
}

function familySlug(family) { return family.toLowerCase().replace(/\s+/g, '-'); }
// Google serves a single variable woff2 per (family, subset) and points
// every weight block at the SAME url. Dedupe by URL: one local file per
// (family, subset), named family-subset.woff2, so the browser fetches each
// binary exactly once across all weights rather than 4× under 4 names.
function subsetSlug(subset) { return subset === 'latin' ? 'latin' : 'latin-ext'; }

(async function main() {
  console.log('Fetching Google Fonts CSS…');
  const css = await fetch(CSS_URL, true);

  // Parse into @font-face blocks. Each block is the text from '@font-face {'
  // to the matching closing '}'. Google's output is one block per comment.
  const blocks = [];
  const re = /@font-face\s*\{([^}]*)\}/g;
  let m;
  while ((m = re.exec(css)) !== null) {
    const body = m[1];
    const subset = (body.match(/\/\*\s*([^*]+?)\s*\*\//) || [])[1]
      ? '' // comments sit OUTSIDE the braces in Google's output — handled below
      : '';
    blocks.push(body);
  }

  // Google prints the subset *comment* before each block, not inside it.
  // Re-parse the raw CSS to pair each block with its preceding /* subset */.
  const paired = [];
  const blockRe = /\/\*\s*([a-z\-]+)\s*\*\/\s*@font-face\s*\{([^}]*)\}/g;
  while ((m = blockRe.exec(css)) !== null) {
    paired.push({ subset: m[1], body: m[2] });
  }
  console.log('Found ' + paired.length + ' @font-face blocks total.');

  const wanted = paired.filter((p) => WANTED_SUBSETS.includes(p.subset));
  console.log('Keeping ' + wanted.length + ' blocks for subsets: ' + WANTED_SUBSETS.join(', '));

  const outRules = [];
  const urlToFile = new Map();   // srcUrl -> local filename (dedupe)
  let downloaded = 0, reused = 0, skipped = 0;

  for (const w of wanted) {
    // Extract fields from the block body.
    const family = (w.body.match(/font-family:\s*'([^']+)'/) || [])[1];
    const style  = (w.body.match(/font-style:\s*([^;]+);/) || [])[1] || 'normal';
    const weight = (w.body.match(/font-weight:\s*([^;]+);/) || [])[1];
    const display= (w.body.match(/font-display:\s*([^;]+);/) || [])[1] || 'swap';
    const srcUrl = (w.body.match(/url\(([^)]+)\)/) || [])[1];
    const fmt    = (w.body.match(/format\('([^']+)'\)/) || [])[1] || 'woff2';
    const urange = (w.body.match(/unicode-range:\s*([^;]+);/) || [])[1] || '';

    if (!family || !weight || !srcUrl) {
      console.warn('  SKIP (unparseable): ' + family + ' ' + weight + ' ' + w.subset);
      skipped++;
      continue;
    }

    // Dedupe by source URL: one file per (family, subset), shared across weights.
    let filename = urlToFile.get(srcUrl);
    if (!filename) {
      filename = familySlug(family) + '-' + subsetSlug(w.subset) + '.woff2';
      urlToFile.set(srcUrl, filename);
      const dest = path.join(OUT_DIR, filename);
      process.stdout.write('  ' + family + ' ' + w.subset + ' <- ' + srcUrl + ' … ');
      const buf = await fetch(srcUrl, false);
      fs.writeFileSync(dest, buf);
      console.log(buf.length + ' bytes -> ' + filename);
      downloaded++;
    } else {
      reused++;
    }

    outRules.push(
      '@font-face {\n' +
      '  font-family: \'' + family + '\';\n' +
      '  font-style: ' + style.trim() + ';\n' +
      '  font-weight: ' + weight.trim() + ';\n' +
      '  font-display: ' + display.trim() + ';\n' +
      '  src: url(/assets/fonts/' + filename + ') format(\'' + fmt + '\');\n' +
      (urange ? '  unicode-range: ' + urange.trim() + ';\n' : '') +
      '}'
    );
  }

  const cssOut =
    '/* =====================================================================\n' +
    ' * fonts.css — self-hosted webfont subsets (Phase 9).\n' +
    ' * -------------------------------------------------------------------\n' +
    ' * Only the latin + latin-ext woff2 subsets of Space Grotesk (display),\n' +
    ' * Inter (body), and JetBrains Mono (code/data) are served. Latin covers\n' +
    ' * every glyph used on the site (Spanish diacritics live in Latin-1\n' +
    ' * Supplement; em-dash/curly quotes in general punctuation); latin-ext\n' +
    ' * is bundled as insurance and fetched by the browser only if a glyph\n' +
    ' * in that range is actually rendered. Replaces the former third-party\n' +
    ' * Google Fonts <link> — no cross-origin preconnect/CSS round-trip.\n' +
    ' * Regenerate with: node assets/fonts/_fetch-fonts.js\n' +
    ' * ===================================================================*/\n' +
    '\n' +
    outRules.join('\n\n') + '\n';

  fs.writeFileSync(FONTS_CSS, cssOut);
  console.log('\nWrote ' + FONTS_CSS + ' (' + outRules.length + ' rules, ' +
    downloaded + ' unique woff2 files downloaded, ' + reused + ' weight blocks reused them, ' +
    skipped + ' skipped).');
})().catch((e) => { console.error('FATAL', e); process.exit(1); });
