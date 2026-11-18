# AGENTS.md — Jalaversity (WordPress Theme)

> Sumber aturan TUNGGAL untuk semua AI agent (Antigravity, Claude Code, Codex,
> dst). `CLAUDE.md` hanya mengimpor file ini — JANGAN tulis aturan di dua tempat.
> Standar ini diadopsi dari cetak biru `sekolahane`, `newsane`, dan `jalawarta`
> (`master-wordpress`) untuk semua theme Webane. Bagian bertanda **[theme]**
> spesifik `jalaversity`; sisanya standar umum.

## Ringkasan Project [theme]
Theme WordPress klasik (non-block) generasi baru untuk website kampus, universitas,
dan perguruan tinggi Islam, milik Webane Indonesia — **proprietary, hanya untuk
customer Webane**. Menggunakan kombinasi **WordPress Settings API** (`jalaversity_options`)
untuk pengaturan situs global dan **ACF Pro Flexible Content** (`page_sections`)
untuk penyusunan halaman dinamis (Fakultas, Prodi, Beranda). Sudah terpasang di
situs pelanggan, jadi **setiap rilis berdampak ke semua situs itu** (lihat Auto-Update).

## Sebelum Mengerjakan Apa Pun
1. Cek `docs/PROGRESS.md` — fase mana yang aktif.
2. Ikuti alur `docs/SOP.md` (rencana → eksekusi → cek → security review → tutup).
3. Baca HANYA dokumen di Peta Dokumen yang relevan dengan task.

## Rules Non-Negotiable
- **Minimal & sesuai scope.** Jangan tambah abstraksi/opsi/fallback "buat
  jaga-jaga". Temuan di luar scope → catat di `docs/RETROFIT-GAPS.md` atau
  fase baru, jangan diperbaiki diam-diam.
- **Kompatibilitas mundur.** Theme ini hidup di situs pelanggan. JANGAN ubah
  nama/slug yang tersimpan di database tanpa migrasi: post type (`agenda`,
  `pengumuman`), nama field ACF (`page_sections`, `is_featured`, `editor`,
  `tanggal_mulai`, dst.), option name (`jalaversity_options` beserta key-nya),
  menu location (`primary`, `topbar`, `footer-about`, `footer-akademik`,
  `footer-layanan`, `social`), sidebar ID (`sidebar-1`, `footer-1`, `footer-2`),
  image size (`jalaversity-large`, `jalaversity-medium`, `jalaversity-thumbnail`,
  `jalaversity-square`), maupun nama file page template (`page-templates/page-*.php`).
- **Prefix.** Fungsi/hook/option/transient WAJIB berprefix `jalaversity_`
  (konstanta: `JALAVERSITY_`, class: `Jalaversity_`).
- **Text domain** hanya `jalaversity`.
- **Aset** wajib lewat `wp_enqueue_style/script` dengan versi = versi theme
  (`JALAVERSITY_VERSION`). Tidak ada `<script>`/`<link>` stylesheet mentah
  di template.
- **Tidak ada jQuery di front-end.** JavaScript front-end (`js/front/main.js`)
  wajib Vanilla ES6+ dengan `defer`. jQuery hanya boleh dipakai di wp-admin (`js/admin/admin.js`).
- **Tidak ada secret di kode** (API key, token, password). Ambil dari konstanta
  `wp-config.php` atau `jalaversity_get_option()`.
- **Tidak ada debug tertinggal**: `var_dump`, `print_r`, `error_log` sementara,
  `die()` untuk debug.
- **Tidak ada tulis ke DB di setiap request** (mis. `update_option()` di
  `init`/`after_setup_theme`, atau `update_post_meta()` langsung saat render GET `single.php`).
- **Seluruh file PHP theme** diawali `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- Keputusan besar → ADR di `docs/decisions/`. Bug penting yang sudah di-fix →
  `docs/lessons-learned.md`.
- Commit WAJIB Conventional Commits (`docs/conventions.md`) — menentukan
  versi rilis otomatis.

## Security — Non-Negotiable (detail: `docs/architecture/architecture-security.md`)
- **Escape saat output** (late escaping): `esc_html`, `esc_attr`, `esc_url`,
  `wp_kses_post`. Termasuk data dari `jalaversity_get_option()`, ACF, dan post meta —
  data DB TIDAK dianggap aman.
- **Sanitize saat input**: `wp_unslash()` + `sanitize_*()`/`absint()` untuk
  setiap `$_GET/$_POST/$_REQUEST/$_COOKIE/$_SERVER`.
- **Aksi yang mengubah data**: nonce (`check_admin_referer`/`check_ajax_referer`)
  + `current_user_can()`.
- **SQL**: utamakan `WP_Query`/API WP; `$wpdb` WAJIB `$wpdb->prepare()` kalau
  ada variabel.
- Selesai mengubah kode → jalankan checklist Security Review.

## Cek Wajib Sebelum Menyatakan Selesai
```bash
composer lint        # php -l + PHPCS (WordPress Coding Standards)
composer analyse     # PHPStan
composer css:check   # Build PostCSS/SCSS + Tailwind v3 + verifikasi output CSS
```
- Tidak ada test suite otomatis; verifikasi = tiga perintah di atas + cek manual.
- Cek satu file saja: `vendor/bin/phpcs path/to/file.php`. Untuk semua file
  yang berubah: `composer lint:changed` (ganti basis dengan
  `BASE=origin/main composer lint:changed`).
- Tambahan: `composer lint:fix` (phpcbf), `composer security:scan`,
  `composer dist:check` (ZIP rilis bersih dari .md/docs/scss/tooling dev).
- Target runtime **PHP 8.1+** (`style.css` & `functions.php` mewajibkan PHP 8.1+).
- File yang DIUBAH/DIBUAT di task ini wajib lolos ketiganya.
- Setiap perubahan tampilan/style: edit di `scss/front/` atau `scss/admin/`,
  lalu wajib jalankan `npm run build` / `composer css:check` agar `css/front.css`
  dan `css/admin.css` ter-update dan tervalidasi. **Jangan pernah mengedit `css/*.css` secara manual** dan **hanya `_components.scss` yang boleh membungkus rule dengan `@layer components {}`** (lihat `docs/architecture/architecture-css.md`).
- Kode lama yang belum disentuh boleh masih melanggar (dicatat di baseline),
  tapi jangan menambah pelanggaran baru.
- Perubahan tampilan → cek manual di situs lokal (desktop + mobile).

## Struktur Theme [theme]
```
functions.php         → entry point, require_once eksplisit file includes/
includes/             → logika PHP modular:
  setup.php           → add_theme_support, nav menus, image sizes, widgets, CPT agenda & pengumuman
  security.php        → hardening WordPress, headers, sanitasi nama file, login rate-limit
  enqueue.php         → enqueue CSS/JS, inline CSS variables (:root), preconnect/preload
  seo.php             → mesin SEO hybrid, Open Graph, Schema JSON-LD kampus, llms.txt
  nav-walker.php      → Jalaversity_Nav_Walker (ARIA submenu & toggle)
  updater.php         → GitHub Release auto-updater (webaneid/themejalaversity)
  settings/           → WordPress Settings API (settings-page, settings-fields, settings-sanitize)
  helpers/            → options-helpers, image-helpers (WebP), icon-helpers (Heroicons SVG),
                        social-helpers, post-helpers, template-helpers
  acf/                → acf-fields (Flexible Content 11 layout), acf-render (bridge ke komponen),
                        acf-post-fields (is_featured, editor, detail agenda)
page-templates/       → custom page templates (page-home, page-dynamic, page-blog, page-contact)
template-parts/       → komponen template:
  header/             → top-bar.php, site-header.php
  footer/             → site-footer.php, wa-widget.php
  content/            → loop-context & bespoke sections (content-single, content-card,
                        content-page, content-post-list, content-none, news-section, pmb-section)
  components/         → pure-$args components untuk ACF page builder & UI bersama
*.php (root)          → template hierarchy WordPress (index, single, page, archive, search, sidebar, 404)
scss/                 → source SCSS + Tailwind v3 (scss/front/*, scss/admin/*; tidak ikut ZIP rilis)
css/                  → css/front.css & css/admin.css (hasil build untuk rilis; JANGAN edit manual)
js/                   → js/front/main.js (Vanilla ES6+) & js/admin/admin.js (WP media & repeater)
fonts/                → fonts/Gontor-Bold.otf (self-hosted heading font)
images/               → aset SVG/gambar statis theme (bg-pattern.svg)
docs/                 → dokumentasi SOP, arsitektur, ADR, & fase (tidak ikut ZIP rilis)
```

`phpstan-baseline.neon` hanya direkam ulang secara sadar (`composer analyse:baseline`),
bukan untuk menyembunyikan error baru.

## Auto-Update & Rilis (detail: ADR-0002, ADR-0003)
- Merge ke `main` dengan commit `feat:`/`fix:`/`perf:` = **rilis ke SEMUA situs
  pelanggan**. Perlakukan `main` sebagai production.
- Versi di `style.css` dan `functions.php` (`JALAVERSITY_VERSION`) dinaikkan otomatis oleh CI.
- File yang tidak ikut ZIP rilis didaftar di `.distignore`.

## Peta Dokumen
| Kalau task menyangkut...                    | Baca                                              |
|---------------------------------------------|---------------------------------------------------|
| Alur kerja per fase                         | `docs/SOP.md`                                     |
| Fase aktif & statusnya                      | `docs/PROGRESS.md`                                |
| Naming, struktur file, commit, branch       | `docs/conventions.md`                             |
| Arsitektur PostCSS/SCSS + Tailwind v3       | `docs/architecture/architecture-css.md`           |
| Escape/sanitize/nonce, checklist review     | `docs/architecture/architecture-security.md`      |
| CI, versioning, auto-update GitHub release  | `docs/architecture/architecture-deployment.md`    |
| Gambaran theme, komponen, & kontrak DB      | `docs/architecture/architecture-overview.md`      |
| Mesin SEO hybrid, Schema kampus, & llms.txt | `docs/architecture/architecture-seo.md`           |
| Gap kode vs standar                         | `docs/RETROFIT-GAPS.md`                           |
| Kenapa suatu keputusan diambil              | `docs/decisions/` (ADR)                           |
| Bug yang pernah terjadi & pencegahannya     | `docs/lessons-learned.md`                         |
| Istilah domain kampus & akademik            | `docs/glossary.md`                                |
