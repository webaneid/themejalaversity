# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Project

**Jalaversity** — custom WordPress theme built from scratch for a university (institution perkuliahan Islam). Standalone theme (no parent), PHP 8.1+, WordPress 6.0+. Textdomain: `jalaversity`. Options key: `jalaversity_options`.

---

## Build Commands

```bash
# Install dependencies (first time)
npm install

# Front-end CSS — watch mode (development)
npm run dev

# Admin CSS — watch mode
npm run dev:admin

# Production build (minified, purged)
npm run build         # → css/front.css
npm run build:admin   # → css/admin.css
```

CSS source is in `scss/`, compiled output lands in `css/`. **Never edit `css/*.css` directly** — they are build artifacts. After any SCSS change, run `npm run build` before checking behavior.

**Verify PHP syntax** before committing: `php -l <file>` on any changed `.php` file.

---

## Architecture

### File Loading Order

`functions.php` only requires files — zero logic. Load order in `includes/`:

```
setup.php → security.php → enqueue.php → seo.php
→ settings/{settings-page, settings-fields, settings-sanitize}.php
→ helpers/{options-helpers, image-helpers, icon-helpers, social-helpers, post-helpers, template-helpers}.php
→ acf/{acf-fields, acf-render, acf-post-fields}.php
→ nav-walker.php → updater.php
```

### Settings & Options

All settings stored under a single `wp_options` key via WordPress Options API. Always read through the static-cached helper — never call `get_option()` directly:

```php
jalaversity_get_option( 'key', $default )   // in includes/helpers/options-helpers.php
```

### CSS Architecture

`scss/front/main.scss` imports in this exact order:
1. `@tailwind base / components / utilities`
2. `_variables.scss` — CSS custom properties (`:root`)
3. `_base.scss` — reset, `@font-face` (Gontor self-hosted), typography
4. `_components.scss` — **only file allowed to have `@layer components {}`** (ACF builder components)
5. `_article.scss` — post/archive/sidebar styles (plain rules, NO `@layer` wrapper)
6. `_utilities.scss` — custom helpers

**Critical**: Only `_components.scss` may wrap rules in `@layer components {}`. Any new partial **must** use plain rules — Tailwind v3 silently drops rules from a second `@layer` block (verified, reproducible).

### CSS Theming via Custom Properties

Brand colors are CSS custom properties in `_variables.scss`. `includes/enqueue.php` reads Settings Page options and outputs inline CSS to `<head>` to override them — this is how colors change without a CSS rebuild. Never hardcode brand colors in SCSS; always use `var(--color-*)`.

Tailwind extends those properties as named colors (`primary`, `accent`, `bg`, `surface`, etc.) in `tailwind.config.js`. When adding new PHP template folders, add the path to `tailwind.config.js` `content[]` or those Tailwind classes won't be purged-in.

### Template Architecture — Two Distinct Component Types

**1. Pure-$args components** (`template-parts/components/`) — used by ACF page builder. They only render data from `$args`, never call `jalaversity_get_option()` or read global `$post` directly. Data is prepared in page templates or `includes/helpers/template-helpers.php` before being passed in.

**2. Loop-context components** (`template-parts/content/`) — operate inside the WP loop (`the_post()` already called). They call `get_the_title()`, `the_content()`, etc. directly. `$args` here is only for display options (e.g. `variant`), not post data.

**Rule**: if two sections look different but share the same structure, make it one component with parameters — not two files. New components are only created when a genuinely new UI pattern exists, not speculatively.

### ACF Page Builder (requires ACF Pro)

`page-templates/page-dynamic.php` ("Template Name: Halaman Dinamis") loops over a `flexible_content` field (`page_sections`) and dispatches to components.

- **Schema** registered in code (not via ACF UI export): `includes/acf/acf-fields.php`
- **Render bridge**: `includes/acf/acf-render.php` — one `jalaversity_render_acf_*()` function per layout; reads `get_sub_field()`, builds `$args`, calls `get_template_part()`
- **Post meta fields** (is_featured, editor): `includes/acf/acf-post-fields.php` — read directly via `get_field()`, no render bridge needed

Available layouts: `hero`, `stats_bar`, `content_media`, `card_grid`, `numbered_steps`, `cta_banner`, `pmb_section`, `news_section`, `sub_nav`, `profile_quote`, `checklist_cards`.

To add a new layout: 1 component file in `template-parts/components/` + 1 layout definition in `acf-fields.php` + 1 render function in `acf-render.php`.

### Image Sizes

4 registered sizes, all hard-crop, declared in `includes/setup.php`:

| Name | Dimensions | Ratio | Used for |
|---|---|---|---|
| `jalaversity-large` | 1120×630 | 16:9 | Reserved (not wired yet) |
| `jalaversity-medium` | 800×450 | 16:9 | Single post & Page featured image |
| `jalaversity-thumbnail` | 400×225 | 16:9 | `content-card.php` overlay & klasik variants |
| `jalaversity-square` | 400×400 | 1:1 | `content-card.php` list variant |

New uploads auto-convert to WebP at quality 80 (turunan only — originals kept). The constant `JALAVERSITY_IMAGE_SIZES` in `includes/helpers/image-helpers.php` must stay in sync with `add_image_size()` in `setup.php` (used for placeholder SVG dimensions).

### Fonts

- **Heading**: `Gontor-Bold.otf` self-hosted via `@font-face` in `_base.scss` (path: `../fonts/Gontor-Bold.otf`). Path is relative to the compiled CSS output, not PHP — no `get_template_directory_uri()` here.
- **Body**: Plus Jakarta Sans, Google Fonts CDN.
- Only `Gontor-Bold.otf` (weight 700) is loaded; all headings in this theme use weight 700 only.

### content-card.php Variants

Single file, 4 variants via `$args['variant']`:
- `overlay` — background image + gradient, title overlay
- `list` — image ~30% left column
- `klasik` — image stacked top + excerpt
- `title` — no image

---

## Security Rules (Non-Negotiable)

Every PHP file must start with:
```php
if ( ! defined( 'ABSPATH' ) ) { exit; }
```

All input must be sanitized (`sanitize_text_field`, `sanitize_hex_color`, etc.), all output must be escaped (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`). All forms need nonces. All admin callbacks need capability checks.

No `eval()`, no inline JS with sensitive data, no hardcoded URLs — always `get_template_directory_uri()`.

---

## Known Constraints

- **No jQuery on front-end** — vanilla ES6+ only (`js/front/main.js`). Admin JS lives in `js/admin/`.
- **GitHub auto-updater** (`includes/updater.php`) — pulls releases from `webaneid/themejalaversity`. GitHub token stored in Settings Page → Tab Update → `github_token` field. No plugin dependency.
- **WordPress Settings API only** — no custom DB tables.
- **Comments disabled** — `comment_form()` is not used anywhere.
- **`?ver=` cache-busting intentionally removed** in `includes/security.php` — hard-refresh required after CSS changes in development.
- For existing media library images: new image sizes/WebP conversion only applies to new uploads. Use `wp media regenerate --yes` via WP-CLI to backfill old images.
- Docs in `docs/` are the authoritative record of architectural decisions — check `docs/02-architecture.md` before changing any structural pattern.
