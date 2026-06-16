# Rencana Pengembangan — Jalaversity

> Dokumen hidup. Update setiap sesi: centang yang selesai, tambah yang baru ditemukan.
> Posisi pengerjaan saat ini selalu tercermin di sini.

---

## Timeline (estimasi per fase)

| Fase | Nama | Estimasi Sesi | Status |
|------|------|--------------|--------|
| 0 | Fondasi & Dokumentasi | Sesi 1–2 | **SELESAI** ✓ |
| 1 | Setup WordPress Theme | Sesi 2–3 | **SELESAI** ✓ |
| 2 | Design System & CSS | Sesi 4 | **SELESAI** ✓ |
| 3 | Template Parts (UI) | Sesi 5–11 | **AKTIF** |
| 4 | Settings Page | Sesi 14–16 | Pending |
| 5 | SEO | Sesi 17–18 | Pending |
| 6 | Performance & Testing | Sesi 19–21 | Pending |

---

## Fase 0 — Fondasi & Dokumentasi (SEKARANG)

**Tujuan**: Semua keputusan teknis didokumentasikan sebelum satu baris kode ditulis.

### Checklist:
- [x] CLAUDE.md dibuat
- [x] `docs/00-project-brief.md` dibuat
- [x] Analisis UI reference (`docs/templates/`) selesai — **Sesi 01**
- [x] `docs/01-planning.md` (file ini) — **Sesi 01**
- [x] `docs/02-architecture.md` — **Sesi 01**
- [x] `docs/03-design-system.md` — **Sesi 01**
- [x] `docs/changelog.md` dimulai — **Sesi 01**
- [ ] `docs/00-project-brief.md` dilengkapi (Target Pengguna, Catatan Owner)
- [ ] `docs/04-settings-schema.md` — Skema lengkap Settings Page
- [ ] `docs/05-seo-strategy.md` — Strategi SEO detail
- [ ] `docs/06-security-checklist.md` — Security checklist implementasi

### Kriteria selesai Fase 0:
Design system terdokumentasi ✓, arsitektur diputuskan ✓, siap mulai Fase 1.

---

## Fase 1 — Setup WordPress Theme

**Tujuan**: Theme dapat diaktifkan di WordPress tanpa error. Asset ter-enqueue.

### File yang dibuat: ✓ SEMUA SELESAI (Sesi 02–03)

**Root:**
- [x] `style.css` — WordPress theme header (metadata saja, bukan styling)
- [x] `functions.php` — Entry point, hanya require files dari `includes/`
- [x] `index.php` — Fallback template kosong (required WP)

**includes/:**
- [x] `includes/setup.php` — `add_theme_support()`, nav menus, image sizes
- [x] `includes/security.php` — Hardening: 10 measures (version hiding, XML-RPC off, security headers, dll)
- [x] `includes/enqueue.php` — Enqueue CSS/JS + output CSS vars dari settings
- [x] `includes/helpers/options-helpers.php` — `jalaversity_get_option()` dengan static cache
- [x] `includes/helpers/image-helpers.php` — thumbnail, placeholder SVG, responsive image helpers
- [x] `includes/helpers/icon-helpers.php` — stub, full implementation Fase 3
- [x] `includes/helpers/social-helpers.php` — stub, full implementation Fase 3
- [x] `includes/seo.php` — stub, full implementation Fase 5
- [x] `includes/settings/settings-page.php` — stub, full implementation Fase 4
- [x] `includes/settings/settings-fields.php` — stub, full implementation Fase 4
- [x] `includes/settings/settings-sanitize.php` — stub, full implementation Fase 4

**JS stubs:**
- [x] `js/front/main.js` — sticky header, smooth scroll (aktif); mobile menu + news tabs (stub Fase 3)
- [x] `js/admin/admin.js` — stub, full implementation Fase 4

**npm/build setup:**
- [x] `package.json` — PostCSS build pipeline (bukan Tailwind CLI — postcss-scss + postcss-import + mixins + nested + tailwindcss + autoprefixer + cssnano)
- [x] `tailwind.config.js` — Config dengan CSS var references di semua warna
- [x] `postcss.config.js` — Custom SCSS partial resolver + semua plugins
- [x] `scss/front/main.scss` — Entry point: @import variables + base SEBELUM @tailwind directives
- [x] `scss/front/_variables.scss` — 35+ CSS custom properties dari design system
- [x] `scss/front/_base.scss` — Reset & base styles dibungkus `@layer base {}`
- [x] `scss/admin/main.scss` — Admin CSS entry point
- [x] `css/front.css` — Generated ✓ — 7,652 bytes raw / **2,407 bytes gzip** (target < 30KB ✓)
- [x] `css/admin.css` — Generated ✓
- [x] `.gitignore` — node_modules, OS files

### Kriteria selesai Fase 1: ✓ SEMUA TERPENUHI
- [x] Theme file structure lengkap, siap aktivasi di WP
- [x] `npm run build` berjalan clean, nol error / nol warning
- [x] `css/front.css` dihasilkan dengan semua 35+ CSS vars, Tailwind base + utilities, dan custom base layer
- [x] Tidak ada jQuery di front-end (Vanilla ES6+)

---

## Fase 2 — Design System & CSS

**Tujuan**: Semua token design system terimplementasi di CSS. Base styles ready.

### File yang dibuat/dimodifikasi: ✓ SEMUA SELESAI (Sesi 02–04)

- [x] `scss/front/_variables.scss` — 35+ CSS custom properties dari design system ✓ Sesi 02
- [x] `scss/front/_base.scss` — body, typography, selection, scroll-behavior, `@layer base` ✓ Sesi 02–03
- [x] `scss/front/_components.scss` — girih mixin, section-label, stats-bar, cards (4 tipe), badge, floating-badge, cta-banner, btn, icon-container, img-radius, link-arrow, footer-social — **Sesi 04**
- [x] `scss/front/_utilities.scss` — fluid typography (clamp), section-py, anim-fadeup/floaty, bg-gradient-primary — **Sesi 04**
- [x] `css/front.css` — Generated ✓ prod: 2,407 bytes gzip / dev (tanpa purge): 6,901 bytes gzip
- [x] `scss/admin/_admin-base.scss` — Settings Page layout, tabs, color picker, form rows, toggle switch — **Sesi 04**
- [x] `css/admin.css` — Generated ✓ 4,261 bytes

### Kriteria selesai Fase 2: ✓ SEMUA TERPENUHI
- [x] Semua CSS variable terdefinisi dan bisa dioverride via Settings Page
- [x] `npm run build` clean, nol error / nol warning
- [x] CSS < 30KB gzip — prod 2,407 bytes / dev 6,901 bytes (keduanya ✓)
- [x] Girih pattern mixin `@define-mixin girih-bg $opacity` berfungsi dengan postcss-mixins
- [ ] Typography scale terlihat benar di browser (test saat aktivasi WP — Fase 6)

---

## Fase 3 — Template Parts (UI)

**Tujuan**: Semua komponen UI dari `docs/templates/` terimplementasi sebagai PHP template parts.

### Urutan implementasi (dari atas ke bawah, shared dulu):

**Pre-requisites (gap closers — Sesi 05):**
- [x] `header.php` — WP root header, memanggil top-bar + site-header template parts — **Sesi 05**
- [x] `footer.php` — WP root footer, memanggil site-footer template part — **Sesi 05**
- [x] `includes/helpers/icon-helpers.php` — 40+ Heroicons inline SVG, jalaversity_icon() + jalaversity_icon_e() — **Sesi 05**
- [x] `includes/helpers/social-helpers.php` — 8 brand icons fill SVG, jalaversity_social_links() — **Sesi 05**
- [x] `includes/nav-walker.php` — Jalaversity_Nav_Walker extends Walker_Nav_Menu, submenu ARIA + toggle — **Sesi 05**
- [x] `includes/setup.php` — register_nav_menus 6 locations (primary, topbar, footer-about, footer-akademik, footer-layanan, social) — **Sesi 05**
- [x] `functions.php` — includes nav-walker.php — **Sesi 05**

**Shared components (semua halaman):**
- [x] `template-parts/header/top-bar.php` — dark bar: kontak kiri, topbar menu + language kanan — **Sesi 05**
- [x] `template-parts/header/site-header.php` — sticky header: logo + nav Walker + CTA + hamburger + mobile drawer — **Sesi 05**
- [ ] `template-parts/header/navigation.php`
- [x] `template-parts/footer/site-footer.php` — 5-kolom footer: brand+social+alamat, 3 nav menu dinamis, bottom bar — **Sesi 05**
- [x] CSS header/footer/menu — topbar, site-header, nav-menu, sub-menu, mobile-menu, site-footer (semua class) ditambahkan ke `_components.scss` — **Sesi 05**
- [x] `js/front/main.js` — mobile menu drawer ARIA, desktop submenu hover+keyboard, news tabs — **Sesi 05**
- [x] `template-parts/components/stats-bar.php` — 4-kolom data dari Settings, icons Heroicons — **Sesi 06**
- [x] `template-parts/components/section-label.php` — via `jalaversity_section_label()` helper function — **Sesi 06**
- [x] `template-parts/components/cta-banner.php` — dark green girih, 2 btn, args override — **Sesi 06**
- [x] `template-parts/components/floating-badge.php` — floaty animation, args-driven — **Sesi 06**
- [x] `template-parts/components/breadcrumb.php` — schema.org BreadcrumbList, semua context WP — **Sesi 06**
- [x] `template-parts/components/pagination.php` — wp paginate_links wrapper — **Sesi 06**

**Homepage (`page-templates/page-home.php`):**
- [x] `template-parts/content/hero-home.php` — dark green hero: badge, H1 highlight, search form, trust badges, img — **Sesi 06** → **direfactor jadi `template-parts/components/hero-page.php` (generic) — Sesi 07**
- [x] `template-parts/content/about.php` — 2-col: img custom-radius + corner badge, 4 nilai institusi — **Sesi 06** → **direfactor jadi `template-parts/components/content-media.php` (generic) — Sesi 07**
- [x] `template-parts/content/faculty-grid.php` — 6 kartu grid, icon overlay, link ke halaman fakultas — **Sesi 06** → **direfactor jadi `template-parts/components/card-grid.php` (generic) — Sesi 07**
- [x] `template-parts/content/pmb-section.php` — dark green girih, header+CTA baris, 4 step cards — **Sesi 06** (tetap bespoke, step cards kini pakai `numbered-steps.php` generic — Sesi 07)
- [x] `template-parts/content/news-section.php` — tab filter JS, featured + list, pengumuman + agenda WP_Query — **Sesi 06** (tetap bespoke, belum ada use case kedua)
- [x] `template-parts/content/research.php` — 2-col: 3 research items, img+badge jurnal — **Sesi 06** → **dihapus, kini `content-media.php` generic dengan `image_position='right'`, `bg='surface'` — Sesi 07**
- [x] `template-parts/content/locations.php` — 3-col grid kampus dengan alamat dari Settings — **Sesi 06** → **dihapus, kini `card-grid.php` generic — Sesi 07**
- [x] `page-templates/page-home.php` — template WP assembles semua sections — **Sesi 06**, direstruktur jadi pure-composition (panggil komponen generik + helper `$args`) — **Sesi 07**
- [x] `includes/helpers/template-helpers.php` — data helpers: stats, about values, faculties, pmb steps, research, campuses, `jalaversity_section_label()` — **Sesi 06**; ditambah `jalaversity_get_hero_home_args()`, `jalaversity_get_about_args()`, `jalaversity_get_research_args()`, normalisasi shape faculties/campuses ke kontrak card-grid — **Sesi 07**
- [x] CSS homepage components — **Sesi 06**, direstruktur total ke class generik (`.hero-page`, `.content-media`, `.icon-list`, `.card-grid`/`.card--grid`, `.numbered-steps`, `.breadcrumb--on-dark`) + dead CSS lama dihapus — **Sesi 07**
- [x] **Refactor total Sesi 07**: 5 file page-specific (`hero-home.php`, `about.php`, `faculty-grid.php`, `research.php`, `locations.php`) dihapus, diganti 6 komponen generik data-driven di `template-parts/components/`. Lihat `docs/02-architecture.md` §8 untuk taksonomi lengkap dan rasional.

**Page Builder Dinamis — ACF Pro Flexible Content (Sesi 08)**: owner minta admin bisa menyusun halaman dari section secara drag-reorder/tambah/hapus/edit, termasuk repeater untuk Prodi/Fakultas. Diimplementasi di atas komponen generik Sesi 07 (lihat `docs/02-architecture.md` §9):
- [x] `includes/acf/acf-fields.php` — field group "Page Sections" (flexible content, 8 layout) — **Sesi 08**
- [x] `includes/acf/acf-render.php` — render bridge per layout → komponen generik — **Sesi 08**
- [x] `page-templates/page-dynamic.php` — "Template Name: Halaman Dinamis" — **Sesi 08**
- [x] Fix pure-render: `stats-bar.php`, `pmb-section.php` (sebelumnya hardcode `jalaversity_get_option()`, lolos dari refactor Sesi 07) — **Sesi 08**
- [x] Enhancement: `numbered-steps.php` dukungan header opsional agar bisa berdiri sendiri — **Sesi 08**
- [ ] Verifikasi end-to-end di wp-admin (isi field, drag-reorder, preview) — **butuh user**, lihat `docs/changelog.md` Sesi 08

**Faculty page** — audit kecocokan terhadap `docs/templates/Fakultas Tarbiyah.dc.html` selesai dilakukan Sesi 09 (lihat `docs/02-architecture.md` §9). Halaman Fakultas akan dibangun sebagai halaman ber-template "Halaman Dinamis" (ACF flexible content), **bukan** `page-faculty.php` statis terpisah — semua section sudah punya layout ACF:
- [x] Hero (variant `subpage` + 2 tombol CTA) — enhancement `buttons` di `hero-page.php` — **Sesi 09**
- [x] Stats bar — reuse layout `stats_bar` (sudah ada Sesi 08)
- [x] `template-parts/components/sub-nav.php` + layout ACF `sub_nav` — **Sesi 09**
- [x] Sambutan Dekan — `template-parts/components/profile-quote.php` + layout ACF `profile_quote` — **Sesi 09**
- [x] Program Studi grid — reuse `card-grid.php` + field baru `code`/`badge`/`badge_variant`/`meta` — **Sesi 09**
- [x] Keunggulan — reuse `card-grid.php` varian `dark` (glass card di atas background gelap) — **Sesi 09**
- [x] Kompetensi & Karier — `template-parts/components/checklist-cards.php` + layout ACF `checklist_cards` (komposisi `icon-list.php` + `card-grid.php`) — **Sesi 09**
- [x] Fasilitas — reuse layout `card_grid` varian foto (sudah ada Sesi 08)
- [x] CTA penutup — reuse layout `cta_banner` (sudah ada Sesi 08)
- [ ] Verifikasi end-to-end di wp-admin: buat halaman test "Fakultas Tarbiyah" pakai template Halaman Dinamis, isi semua section di atas, cek render vs `docs/templates/Fakultas Tarbiyah.dc.html` — **butuh user**

**Content templates (WordPress standar) ✓ SELESAI (Sesi 11)** — desain artikel diadopsi dari theme referensi `jalawarta` (header/footer tetap default jalaversity), lihat `docs/02-architecture.md` §10 untuk detail lengkap + daftar fitur yang sengaja tidak diadopsi:
- [x] `single.php`, `archive.php`, `search.php`, `page.php` (root templates, sebelumnya tidak ada sama sekali)
- [x] `template-parts/content/content-single.php`
- [x] `template-parts/content/content-card.php` — **menggantikan rencana awal `content-archive.php`**: 1 file, **4 varian** (`overlay`/`list`/`klasik`/`title`, direvisi Sesi 13 sesuai spek persis user) via `$args['variant']`, dipakai archive/search/Halaman Blog/related-posts/sidebar. `klasik` masih belum diwire — `title` sudah dapat use case nyata di sidebar "Artikel Terpopuler" (Sesi 14)
- [x] **Sidebar default (Sesi 14)** — `sidebar.php` + `template-parts/components/sidebar.php` (search, Artikel Terpopuler, Kategori, widget area `sidebar-1`), SATU desain di index/archive/tag/category/author/search/single via `.content-with-sidebar` (2 kolom ≥992px). Membalik keputusan "full-width tanpa sidebar" Sesi 11 atas permintaan user
- [x] `template-parts/content/content-page.php`, `content-none.php`
- [x] `template-parts/components/search-form.php`, `share-buttons.php`, `post-nav.php`, `related-posts.php`
- [x] `page-templates/page-blog.php` ("Halaman Blog") — landing artikel: featured (`is_featured`) + latest posts paginated
- [x] `includes/helpers/post-helpers.php` (reading time, view counter, related-posts query, excerpt filter)
- [x] `includes/acf/acf-post-fields.php` — field `is_featured` (true_false) + `editor` (user), location `post_type == post`
- [x] `scss/front/_article.scss` — partial baru, typography `.entry-content`, card varian, share/nav/search/no-results
- [ ] Verifikasi end-to-end: buat post test (featured image, tandai Berita Utama, pilih Editor), cek single/archive/Halaman Blog di browser, bandingkan dengan desain jalawarta — **butuh user**

**JS (front-end interaktivitas):**
- [x] `js/front/main.js` — Sticky header + smooth scroll aktif; mobile menu + news tabs stub (Fase 3); ditambah `initArabicParagraphs()` + `initCopyLink()` (Sesi 11)

### Kriteria selesai Fase 3:
- Homepage tampil sesuai `docs/templates/Beranda Al-Ikhlash.dc.html`
- Faculty page tampil sesuai `docs/templates/Fakultas Tarbiyah.dc.html`
- Navigation hamburger berfungsi di mobile
- News tabs filter berfungsi
- Diuji di 375px, 768px, 1280px

---

## Fase 4 — Settings Page ✓ SELESAI (Sesi 10)

**Tujuan**: Settings page komprehensif tanpa plugin. Semua nilai dapat diubah admin.

### File:
- [x] `includes/settings/settings-page.php` — Register admin menu, render halaman ber-tab (WP Settings API murni, `do_settings_sections()`), generic field renderer
- [x] `includes/settings/settings-fields.php` — `jalaversity_settings_schema()` (1 sumber kebenaran) + registrasi `add_settings_section`/`add_settings_field`
- [x] `includes/settings/settings-sanitize.php` — 1 sanitize callback type-aware, merge-on-save (cegah tab lain ke-wipe)
- [x] `docs/04-settings-schema.md` — ditulis sebelum implementasi, 85 field di 4 tab

### Seksi settings yang **benar-benar diimplementasikan** (disesuaikan dari rencana awal berdasarkan audit konsumen nyata — lihat `docs/04-settings-schema.md`):
| Tab | Field |
|-------|-------|
| Umum | Kontak (alamat/telp/email), URL kontak, copyright footer, PMB (url/label/brosur) |
| Beranda | Hero, Tentang, Statistik (4), Fakultas (heading+12 field per-fakultas), Riset, Lokasi Kampus (heading+15 field), CTA |
| Sosial Media | URL 8 platform (facebook/instagram/youtube/twitter/linkedin/whatsapp/telegram/tiktok) |
| Warna | 6 warna brand (primary/primary-dark/primary-medium, accent/accent-dark/accent-light) — color picker, live override via CSS var tanpa rebuild |

**Sengaja TIDAK dibuat** (General logo/favicon/nama-site sudah ditangani WP core; Header/Footer toggle, Typography, SEO, Performance, Advanced belum punya konsumen kode sama sekali) — lihat alasan lengkap di `docs/04-settings-schema.md` § "Field yang sengaja tidak dibuat". Ditambah nanti saat fase yang mengkonsumsinya benar-benar dikerjakan (mis. SEO → Fase 5).

### Kriteria selesai Fase 4:
- [x] Semua input tersanitasi sesuai type (text/textarea/url/email/tel/color/image) — `jalaversity_sanitize_field_value()`
- [x] Ubah primary color → warna berubah di front-end tanpa rebuild CSS (sudah jalan sejak Sesi 02-04, `jalaversity_output_css_vars()`)
- [x] Nonce via `settings_fields()` (WP handle otomatis), capability check `manage_options` di render + `add_menu_page`
- [ ] Verifikasi end-to-end di wp-admin (isi semua tab, simpan, cek tab lain tidak ter-wipe, cek front-end berubah) — **butuh user**

---

## Fase 5 — SEO

**Tujuan**: SEO built-in tanpa Yoast atau plugin eksternal.

### File:
- [ ] `includes/seo.php` — Semua hook SEO
- [ ] `template-parts/components/breadcrumb.php` — dengan schema markup
- [ ] `docs/05-seo-strategy.md` — (ditulis sebelum implementasi)

### Fitur SEO:
- [ ] Title tag dinamis (single, archive, home, search, 404)
- [ ] Meta description (settings → excerpt → auto-generate)
- [ ] Open Graph (title, description, image, type, locale)
- [ ] Twitter Card (summary_large_image)
- [ ] Schema.org: Article, WebSite, BreadcrumbList, Organization
- [ ] Canonical URL semua halaman
- [ ] Robots meta (noindex untuk search result, 404, dll)

### Kriteria selesai Fase 5:
- Structured data valid (Google Rich Results Test)
- OG tags lengkap untuk setiap tipe halaman

---

## Fase 6 — Performance & Testing

**Tujuan**: Theme siap production, semua audit lulus.

### Checklist:
- [ ] CSS audit: `css/front.css` < 30KB gzip
- [ ] JS audit: `js/front/main.js` < 10KB gzip
- [ ] Lazy load gambar (native `loading="lazy"` + `includes/helpers/image-helpers.php`)
- [ ] Font: evaluasi self-hosting (woff2)
- [ ] Test mobile: 375px, 414px, 768px
- [ ] Test desktop: 1280px, 1440px
- [ ] Security checklist: `docs/06-security-checklist.md` semua ✓
- [ ] No PHP warnings/notices (`WP_DEBUG=true` clean)
- [ ] No console errors
- [ ] Lighthouse score target: Performance ≥ 90, Accessibility ≥ 90, SEO ≥ 95

---

## Backlog & Ide

Fitur yang muncul tapi belum dijadwalkan — evaluasi setelah Fase 4:

- **Dark mode** — opsional, via `prefers-color-scheme` + settings toggle
- **Search overlay** — fullscreen search dengan keyboard shortcut (⌘/Ctrl + K)
- **Sidebar** — collapsible, untuk halaman artikel/blog
- **Comment section** — custom styling untuk WordPress comments
- **Print stylesheet** — CSS print untuk halaman artikel
- **RTL support** — untuk konten Arab (relevan untuk institusi Islam)
- **Custom 404 page** — branded, bukan WP default
