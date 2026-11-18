# Architecture Overview — Jalaversity

> Dokumen ini mengonsolidasikan arsitektur inti, struktur komponen, skema
> database (`jalaversity_options` & ACF), dan kontrak kompatibilitas mundur
> theme **Jalaversity (`jalaversity`)**.

---

## 1. Stack Teknologi

| Lapisan | Teknologi | Versi Minimum |
|---|---|---|
| **CMS** | WordPress | `6.0+` (diuji `6.7+`) |
| **PHP** | PHP (Functional modular style) | `8.1+` |
| **CSS Source** | PostCSS (`postcss-import`, `postcss-mixins`, `postcss-nested`, `postcss-scss`) + Tailwind CSS v3 | Node.js `18+` |
| **CSS Output** | `css/front.css` (< 12 KB gzip) & `css/admin.css` | — |
| **JavaScript** | Front-end: Vanilla ES6+ (`defer`, tanpa jQuery) · Admin: `js/admin/admin.js` | — |
| **Icon** | Inline SVG Heroicons (`includes/helpers/icon-helpers.php`) & Brand SVG (`social-helpers.php`) | — |
| **Font** | Heading: Self-hosted `Gontor-Bold.otf` · Body: Plus Jakarta Sans (Google Fonts) | — |
| **Page Builder** | ACF Pro Flexible Content (`page_sections`) | ACF Pro `6.0+` |

---

## 2. Urutan Pemuatan Modul (`functions.php`)

`functions.php` hanya mendefinisikan konstanta (`JALAVERSITY_VERSION`, `JALAVERSITY_DIR`, `JALAVERSITY_URI`) dan memuat file modul secara eksplisit dalam urutan berikut:

```text
1. /includes/setup.php                  → Theme support, nav menus, image sizes, sidebars, CPT agenda & pengumuman
2. /includes/security.php               → Hardening WordPress, HTTP headers, sanitasi nama file, login rate-limit
3. /includes/helpers/options-helpers.php→ jalaversity_get_option() & jalaversity_update_option() (static cache)
4. /includes/enqueue.php                → Enqueue CSS/JS, output inline CSS variables (:root), preconnect/preload
5. /includes/seo.php                    → Mesin SEO hybrid, Open Graph, Schema JSON-LD kampus, llms.txt
6. /includes/helpers/image-helpers.php  → Thumbnail, SVG placeholder, responsive image, WebP auto-convert
7. /includes/helpers/icon-helpers.php   → 40+ Heroicons inline SVG (jalaversity_icon / jalaversity_icon_e)
8. /includes/helpers/social-helpers.php → Brand SVG icons & jalaversity_social_links()
9. /includes/helpers/template-helpers.php→ Data helper homepage & normalizer kontrak komponen
10. /includes/helpers/post-helpers.php  → Reading time, views, related posts, meta line, archive title filter
11. /includes/nav-walker.php            → Jalaversity_Nav_Walker (ARIA submenu & toggle)
12. /includes/settings/settings-page.php→ Registrasi menu admin & generic field renderer + repeater Tim Layanan
13. /includes/settings/settings-fields.php→ Skema tunggal jalaversity_settings_schema()
14. /includes/settings/settings-sanitize.php→ Sanitasi berbasis tipe & merge antar-tab
15. /includes/acf/acf-fields.php        → Registrasi kode ACF Flexible Content page_sections (11 layout)
16. /includes/acf/acf-render.php        → Render bridge layout ACF → komponen generik
17. /includes/acf/acf-post-fields.php   → Field ACF post meta (is_featured, editor) & CPT agenda
18. /includes/updater.php               → GitHub Release auto-updater (webaneid/themejalaversity)
```

---

## 3. Arsitektur Komponen Template (`template-parts/`)

Jalaversity memisahkan komponen ke dalam **dua kategori tegas** yang tidak boleh dicampur aturannya:

### 3.1 Pure-`$args` Components (`template-parts/components/*.php`)
Dipakai oleh `page-templates/page-home.php` dan ACF Page Builder (`page-templates/page-dynamic.php` via `includes/acf/acf-render.php`).
- **Aturan:** Hanya menerima data dari `$args` (argumen ke-3 `get_template_part()`). Tidak boleh memanggil `get_the_*()` atau membaca global `$post` secara langsung.
- **Taksonomi Komponen:**
  - `hero-page.php` — Hero hijau tua bermotif girih (`variant`: `'home'` dengan search + trust badges, atau `'subpage'` dengan breadcrumb + tombol CTA).
  - `stats-bar.php` — Bar statistik mengambang 4 kolom (overlap hero).
  - `section-header.php` — Label seksi + heading `<h2>` + lead paragraph (`dark` toggle).
  - `content-media.php` — Seksi 2 kolom gambar + konten (posisi gambar `left`/`right`, background `cream`/`surface`, corner badge, `icon-list`).
  - `card-grid.php` — Grid kartu serbaguna untuk Fakultas, Program Studi (mendukung `code`, `badge`, `badge_variant`, `meta`), Lokasi Kampus, Fasilitas, dan Keunggulan (`dark` glassmorphism).
  - `icon-list.php` — Daftar ikon + judul + deskripsi (`grid`, `rows`, atau `plain`).
  - `numbered-steps.php` — Kartu langkah bernomor (alur PMB, varian `light` atau `on-dark`).
  - `sub-nav.php` — Bar navigasi jangkar (*anchor jump-links*) untuk halaman Fakultas.
  - `profile-quote.php` — Sambutan Rektor/Dekan (foto profil, nama, jabatan, kutipan, isi).
  - `checklist-cards.php` — Komposisi 2 kolom (`icon-list.php` di kiri + `card-grid.php` di kanan) untuk Kompetensi & Prospek Karier.
  - `cta-banner.php` — Banner ajakan bertindak bermotif girih dengan 2 tombol.
  - `floating-badge.php` — Lencana melayang di atas foto hero (mis. Akreditasi Unggul).

### 3.2 Loop-Context & Shared Components (`template-parts/content/*.php`)
Beroperasi di dalam WordPress Loop (`the_post()` sudah berjalan) dan membaca `get_the_title()`, `the_permalink()`, `the_content()`, dll. secara langsung.
- `content-card.php` — **1 file dengan 4 varian** via `$args['variant']`:
  - `overlay`: gambar `jalaversity-thumbnail` (16:9) full-bleed dengan gradasi gelap + judul di atas gambar.
  - `list`: gambar `jalaversity-square` (1:1) di kiri (~30%) + meta & judul di kanan.
  - `klasik`: gambar `jalaversity-thumbnail` (16:9) di atas + meta, judul, & excerpt di bawah.
  - `title`: hanya meta & judul tanpa gambar (dipakai di widget *Artikel Terpopuler* sidebar).
- `content-single.php` — Body artikel lengkap (breadcrumb, meta line, H1, estimasi waktu baca & views, byline penulis + editor ACF, featured image `jalaversity-medium`, `.entry-content`, tombol share, post-nav, sidebar, dan related posts).
- `content-post-list.php` — Body bersama untuk `index.php`, `archive.php`, dan `search.php` dengan sidebar default.
- `content-page.php` & `content-none.php`.
- `news-section.php` & `pmb-section.php` — Seksi khusus beranda/dinamis.

---

## 4. Kontrak Database & Pengaturan (`jalaversity_options` & ACF)

> ⚠️ **PERINGATAN KOMPATIBILITAS MUNDUR:** Seluruh nama key di bawah ini sudah dipakai di situs pelanggan dan **DILARANG diubah namanya** tanpa skrip migrasi otomatis.

### 4.1 WordPress Options API (`jalaversity_options`)
Disimpan sebagai satu array ter-serialisasi di `wp_options` dengan key `jalaversity_options` (lihat `includes/settings/settings-fields.php`):
- **Tab Umum (`umum`):** `contact_address`, `contact_phone`, `contact_whatsapp`, `contact_email`, `contact_hours`, `contact_maps_url`, `contact_url`, `footer_copyright`, `pmb_wave_label`, `pmb_heading`, `pmb_body`, `pmb_url`, `pmb_label`, `pmb_brochure_url`.
- **Tab Sosial Media (`sosial`):** `social_facebook`, `social_instagram`, `social_youtube`, `social_twitter`, `social_linkedin`, `social_whatsapp`, `social_telegram`, `social_tiktok` (+ `social_threads` pada Fase 07).
- **Tab Warna (`warna`):** `color_primary` (`#08422e`), `color_primary_dark` (`#06301f`), `color_primary_medium` (`#0a4730`), `color_accent` (`#b68c2e`), `color_accent_dark` (`#a87e26`), `color_accent_light` (`#e9c970`).
- **Tab Tim Layanan (`tim_layanan`):** `wa_default_message`, `tim_layanan_contacts` (array repeater `nama`, `jabatan`, `whatsapp`, `photo`).
- **Tab Update (`update`):** `github_token`.
- **Opsi Beranda Fallback (`template-helpers.php`):** `hero_*`, `about_*`, `stats_{1..4}_*`, `faculty_*`, `research_*`, `locations_heading`, `campus_{1..3}_*`, `cta_*`.

### 4.2 Custom Post Types & Post Meta
- **CPT:** `pengumuman` (slug: `pengumuman`) dan `agenda` (slug: `agenda`).
- **Post Meta:** `_jalaversity_views` (integer jumlah pembaca artikel).
- **ACF Field Groups (Terdaftar via Kode):**
  1. `group_jalaversity_page_sections` (`page_sections` — 11 layout: `hero`, `stats_bar`, `content_media`, `card_grid`, `numbered_steps`, `cta_banner`, `pmb_section`, `news_section`, `sub_nav`, `profile_quote`, `checklist_cards`).
  2. `group_jalaversity_post_meta` (`is_featured`, `editor` pada `post_type == post`).
  3. `group_jalaversity_agenda` (`tanggal_mulai`, `tanggal_selesai`, `jam_mulai`, `jam_selesai`, `tipe_lokasi`, `lokasi`, `link_online`, `narasumber`, `penyelenggara`, `link_pendaftaran`, `kuota` pada `post_type == agenda`).

### 4.3 Image Sizes & Auto-Convert WebP
Empat ukuran gambar kustom (semua *hard-crop* tengah) di `includes/setup.php` & `JALAVERSITY_IMAGE_SIZES` di `includes/helpers/image-helpers.php`:
- `jalaversity-large`: `1120×630` (16:9)
- `jalaversity-medium`: `800×450` (16:9) — Single post & Page featured image
- `jalaversity-thumbnail`: `400×225` (16:9) — Kartu `overlay` & `klasik`
- `jalaversity-square`: `400×400` (1:1) — Kartu `list`
 seluruh ukuran turunan JPEG/PNG dikonversi otomatis ke `.webp` via filter `image_editor_output_format`.
