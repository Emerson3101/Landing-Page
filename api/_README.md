# api

Server-side PHP endpoints.

## `contact.php` (Phase 7)

POST-only JSON endpoint for the home-page contact form. Reads JSON (or
form-encoded) and answers JSON:

| Status | Body | Meaning |
| ------ | ---- | ------- |
| 200 | `{ok:true}` | accepted — mailed (best-effort) + logged |
| 400 | `{ok:false, error:"bad-request", message}` | body unreadable / > 16 KB |
| 405 | `{ok:false, error:"method", message}` | non-POST |
| 422 | `{ok:false, errors:{name,email,message}}` | validation failed |
| 429 | `{ok:false, error:"rate-limited", message}` | over per-IP throttle |

Pipeline: method gate → parse → rate-limit → honeypot (silent OK) →
validate → deliver (mail best-effort + CSV log) → respond.

Error strings are bilingual EN/ES, keyed off the payload `lang` field the
front-end sets from `<html data-active-lang>`. Field-level validation
errors come back on 422 and are applied to the form by `main.js`; a 429
surfaces the rate-limit message; a 404/500/network failure falls back to a
`mailto:` launch so the message still reaches the inbox.

Rate limiting is file-based, per IP (`storage/rl/<hash>.json`), 5 per hour.
Submissions append to `storage/messages.csv`; `mail()` is also attempted so
a configured MTA still delivers. Header injection is blocked by stripping
CRLF from name/email; the honeypot (`company` field) silently succeeds.

### `storage/` must not be web-served

`storage/.htaccess` denies Apache. On nginx or hosts with `.htaccess`
disabled, move `storage/` above the web root and point the `STORAGE`
constant in `contact.php` at the absolute path.
