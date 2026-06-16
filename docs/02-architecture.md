# Arsitektur — Jalaversity

> Dokumen ini mencatat semua keputusan arsitektur dan alasannya.
> Setiap keputusan bersifat final untuk fase yang bersangkutan —
> ubah hanya jika ada alasan teknis yang kuat, dan catat di changelog.

---

## Stack

| Lapisan | Teknologi | Versi Minimum |
|---------|-----------|---------------|
| CMS | WordPress | 6.0+ |
| PHP | PHP (Functional style) | 8.1+ |
| CSS Source | SCSS + Tailwind CSS CLI | Tailwind v3 |
| CSS Output | `css/front.css` + `css/admin.css` | — |
| JavaScript | Vanilla ES6+ (defer) | — |
| Icon | Heroicons SVG inline | — |
| Font | Heading: self-hosted "Gontor" (`fonts/Gontor-Bold.otf`) — Body: Google Fonts CDN (`display=swap`) | — |
| Build Tool | Tailwind CLI via npm script | Node.js 18+ |

---

## Keputusan Arsitektur

### 1. CSS Strategy — SCSS Custom + Tailwind Build

**Keputusan**: SCSS custom dikombinasi dengan Tailwind CSS CLI build.

**Cara kerja**:
```
scss/front/main.scss
  ├── @tailwind base;
  ├── @tailwind components;
  ├── @tailwind utilities;
  ├── @import 'variables';    ← CSS custom properties
  ├── @import 'base';         ← Reset & base styles
  ├── @import 'components';   ← Component classes
  └── @import 'utilities';    ← Custom utility helpers
```
Tailwind CLI membaca `main.scss`, menghasilkan `css/front.css` setelah purge.

**Konfigurasi Tailwind** (`tailwind.config.js`):
```js
module.exports = {
  content: [
    './**/*.php',
    './js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        primary:        'var(--color-primary)',
        'primary-dark': 'var(--color-primary-dark)',
        'primary-med':  'var(--color-primary-medium)',
        'primary-light':'var(--color-primary-light)',
        accent:         'var(--color-accent)',
        'accent-dark':  'var(--color-accent-dark)',
        'accent-light': 'var(--color-accent-light)',
        bg:             'var(--color-bg)',
        surface:        'var(--color-surface)',
        border:         'var(--color-border)',
        'text-primary': 'var(--color-text-primary)',
        'text-secondary':'var(--color-text-secondary)',
        'text-muted':   'var(--color-text-muted)',
      },
      fontFamily: {
        heading: ['Playfair Display', 'Georgia', 'serif'],
        body:    ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
```

**Alasan kombinasi SCSS + Tailwind**:
- Tailwind utilities untuk layout cepat (`flex`, `grid`, `gap-*`, dll)
- SCSS custom untuk komponen yang terlalu spesifik untuk utility-first
- CSS custom properties sebagai bridge antara Tailwind config dan Settings Page WP
- Output di-purge berdasarkan PHP/JS files → target < 30KB tercapai

**Build command** (via npm scripts):
```bash
# Development
npm run dev   # → tailwindcss -i scss/front/main.scss -o css/front.css --watch

# Production
npm run build # → tailwindcss -i scss/front/main.scss -o css/front.css --minify
```

> ⚠️ **PERINGATAN PENTING — JANGAN bungkus 2 partial berbeda dengan
> `@layer components { }` masing-masing.** Ditemukan & diverifikasi Sesi 13:
> kalau ada DUA at-rule `@layer components { ... }` terpisah di hasil akhir
> (mis. satu di `_components.scss`, satu lagi di partial baru), Tailwind v3
> **diam-diam menghapus sebagian rule dari blok kedua** tanpa error/warning
> apa pun — reproducible, bukan flaky. Hanya `_components.scss` yang boleh
> punya wrapper `@layer components { }`. Partial CSS baru manapun (`_article.scss`
> dan sejenisnya nanti) **harus** berisi rule polos tanpa wrapper `@layer`
> sendiri — posisi `@import`-nya di `main.scss` sudah menentukan urutan
> cascade yang benar (sebelum `@tailwind utilities`). Kalau menulis partial
> SCSS baru, baca catatan di kepala `_article.scss` dulu.

---

### 2. CSS Theming — CSS Custom Properties via Settings Page

**Keputusan**: Semua warna brand disimpan sebagai CSS custom properties yang
dapat dioverride oleh WordPress Settings Page tanpa menyentuh SCSS.

**Cara kerja**:
1. `scss/front/_variables.scss` mendefinisikan default values di `:root`
2. Settings Page menyimpan pilihan warna ke `wp_options` via `jalaversity_options`
3. `includes/enqueue.php` membaca opsi lalu output inline CSS ke `<head>`:

```php
// Di includes/enqueue.php
function jalaversity_output_css_vars(): void {
    $primary      = jalaversity_get_option( 'color_primary', '#08422e' );
    $primary_dark = jalaversity_get_option( 'color_primary_dark', '#06301f' );
    $accent       = jalaversity_get_option( 'color_accent', '#b68c2e' );
    $accent_light = jalaversity_get_option( 'color_accent_light', '#e9c970' );

    // Sanitasi wajib
    $primary      = sanitize_hex_color( $primary ) ?? '#08422e';
    $primary_dark = sanitize_hex_color( $primary_dark ) ?? '#06301f';
    $accent       = sanitize_hex_color( $accent ) ?? '#b68c2e';
    $accent_light = sanitize_hex_color( $accent_light ) ?? '#e9c970';

    $css = ":root {
        --color-primary:      {$primary};
        --color-primary-dark: {$primary_dark};
        --color-accent:       {$accent};
        --color-accent-light: {$accent_light};
    }";

    wp_add_inline_style( 'jalaversity-front', $css );
}
add_action( 'wp_enqueue_scripts', 'jalaversity_output_css_vars', 20 );
```

**Warna yang dapat diubah via Settings Page** (Fase 4):
- Primary color (green utama)
- Primary dark (untuk topbar/footer)
- Accent color (gold utama)
- Accent light (bright gold)

**Warna yang TIDAK diekspos ke Settings** (terlalu teknikal / jarang berubah):
- Neutral backgrounds (#f8f5ec, #ffffff)
- Text colors (semua varian)
- Semantic colors (success/warning/error/info)
- Shadow colors

---

### 3. JavaScript Strategy — Vanilla ES6+

**Keputusan**: Vanilla JavaScript, tanpa library (termasuk jQuery tidak diload di front-end).

**Alasan**:
- Interaktivitas yang dibutuhkan terbatas: sticky header shadow, news tab filter,
  mobile hamburger menu, smooth scroll anchor
- Semuanya dapat dicapai dengan < 100 baris vanilla JS
- Menghindari ketergantungan eksternal dan overhead parsing

**Pola implementasi**:
```javascript
// js/front/main.js — struktur dasar
document.addEventListener('DOMContentLoaded', () => {
  initStickyHeader();
  initMobileMenu();
  initNewsTabs();
  initSmoothScroll();
});
```

**Kapan boleh tambah library**:
- Jika ada animasi kompleks yang tidak bisa diselesaikan dengan CSS → pertimbangkan
  [micro-library < 5KB] saja, bukan framework penuh
- Jika ada komponen form yang sangat kompleks → tunda keputusan sampai ada use case nyata

---

### 4. Icon Strategy — Heroicons SVG Inline

**Keputusan**: SVG inline, bukan font icon atau sprite.

**Alasan**:
- Tidak ada HTTP request tambahan per icon
- Dapat di-style via CSS (`color`, `stroke`, `fill`)
- Dapat diakses (aria-hidden untuk dekoratif)
- Ukuran per icon kecil (< 200 bytes)

**Implementasi di PHP**:
```php
// includes/helpers/icon-helpers.php
function jalaversity_icon( string $name, int $size = 24, string $class = '' ): string {
    $icons = [
        'arrow-right' => '<path d="M4 12h15"/><path d="M13 5l7 7-7 7"/>',
        'chevron-right' => '<path d="M9 5l7 7-7 7"/>',
        // ...
    ];
    $d     = $icons[ $name ] ?? '';
    $cls   = $class ? ' class="' . esc_attr( $class ) . '"' : '';
    return sprintf(
        '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"%2$s>%3$s</svg>',
        $size, $cls, $d
    );
}
```

---

### 5. Font Loading Strategy — Heading Self-Hosted, Body Google Fonts CDN

**Keputusan (direvisi Sesi 17)**: Font heading ("Gontor") **self-hosted**
dari `fonts/Gontor-Bold.otf` via `@font-face` — bukan lagi Google Fonts.
Font body (Plus Jakarta Sans) tetap Google Fonts CDN, `display=swap`.

> Ini langsung memenuhi catatan "Fase 6: Evaluasi self-hosting" yang sudah
> ditulis sejak rencana awal proyek — dipicu user minta cek folder
> `fonts/` yang sudah berisi 16 file Gontor (8 weight × normal/italic, OTF).

**Implementasi**:
```scss
/* scss/front/_base.scss, di dalam @layer base */
@font-face {
  font-family: 'Gontor';
  src: url('../fonts/Gontor-Bold.otf') format('opentype');
  font-weight: 700;
  font-style: normal;
  font-display: swap;
}
```
```scss
/* scss/front/_variables.scss */
--font-heading: 'Gontor', Georgia, serif;
```
```php
// includes/enqueue.php — Google Fonts URL tinggal Plus Jakarta Sans saja
wp_enqueue_style( 'jalaversity-fonts',
  'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
  [], null );
```

**Alasan hanya `Gontor-Bold.otf` (1 dari 16 file) yang dipakai**: grep
`font-weight` di sekitar setiap `var(--font-heading)` di seluruh SCSS —
**semua** heading di theme ini pakai weight 700 (Bold), tidak ada yang
butuh 800/ExtraBold atau weight lain. Load 1 file kecil (~52KB) yang
benar-benar dipakai, bukan 16 file "siapa tahu nanti perlu".

**Format OTF, bukan WOFF2**: file yang ada di `fonts/` adalah OpenType
(`.otf`) — valid untuk `@font-face` di semua browser modern, TAPI bukan
format paling optimal (WOFF2 ~30% lebih kecil karena terkompresi). Tidak
dikonversi sekarang karena tidak ada tool konversi font terpasang di
environment ini (`fonttools`/`woff2_compress` tidak ada) — instal tool
baru cuma untuk satu file ~52KB dianggap tidak sepadan. Kalau mau
optimasi lebih jauh nanti, convert manual ke WOFF2 (mis. via
fontsquirrel.com/CloudConvert) lalu ganti path `src:` di `_base.scss` —
satu baris.

**Trade-off yang diterima**: menghapus Playfair Display dari request
Google Fonts MENGHILANGKAN 1 koneksi network (lebih cepat, lebih privat),
tapi body font (Plus Jakarta Sans) masih dari Google Fonts CDN — DNS
lookup ke fonts.googleapis.com/fonts.gstatic.com belum 100% hilang, cuma
berkurang. Self-host body font juga bisa dievaluasi nanti kalau mau, sama
prinsipnya dengan heading.

---

### 6. Settings Storage — WordPress Options API

**Keputusan**: WordPress Options API (`get_option` / `update_option`), bukan custom table.

**Alasan**:
- Standar WordPress, tidak perlu migrasi DB
- Kompatibel dengan semua backup/migration plugin
- Cukup untuk volume data settings (< 50 key-value)
- Helper function untuk akses bersih:

```php
// includes/helpers/options-helpers.php
function jalaversity_get_option( string $key, mixed $default = '' ): mixed {
    static $options = null;
    if ( null === $options ) {
        $options = get_option( 'jalaversity_options', [] );
    }
    return $options[ $key ] ?? $default;
}
```

**Catatan**: Gunakan static cache untuk menghindari multiple `get_option()` call per page load.

---

### 7. Template Architecture — Template Parts + Page Templates

**Keputusan**: Template parts di `template-parts/` dipanggil via `get_template_part()`.
Page templates (home, fakultas, dll) di `page-templates/`.

**Aturan**:
- File `page-*.php` hanya berisi pemanggilan `get_template_part()` — zero logic
- Semua logic ada di `includes/`
- Data disiapkan sebagai PHP array/variable sebelum template dirender
- Tidak ada query database di dalam template file

**Pola pemanggilan**:
```php
// page-templates/page-home.php
get_header();
get_template_part( 'template-parts/components/hero-page', null, jalaversity_get_hero_home_args() );
get_template_part( 'template-parts/components/stats-bar' );
get_template_part( 'template-parts/components/content-media', null, jalaversity_get_about_args() );
// ...
get_footer();
```

Lihat **§8 — Generic Component Architecture** untuk aturan lengkap tentang
component generik vs. file komposisi.

---

### 8. Generic Component Architecture — Pure Render Components vs. Composition Files

**Konteks keputusan**: Sesi 06 (awal) membangun section homepage sebagai file
per-section dengan markup spesifik (`hero-home.php`, `about.php`, `faculty-grid.php`,
`research.php`, `locations.php`). Ditemukan masalah skalabilitas: About dan Research
punya struktur 2-kolom (gambar+konten) yang identik, hanya beda data dan posisi
gambar — begitu juga Faculty Grid dan Locations (grid kartu identik, beda data).
Kalau pola ini diteruskan, setiap halaman baru (Fakultas, Prodi, dll) akan butuh
section file baru meski UI pattern-nya sama → duplikasi tak terbatas. **Sesi 07**
melakukan refactor total: 5 file page-specific dihapus, diganti 6 komponen generik
data-driven di `template-parts/components/`.

**Keputusan**: Komponen di `template-parts/components/` WAJIB **pure render** —
hanya menerima `$args` (via `get_template_part( $slug, null, $args )`), tidak pernah
memanggil `jalaversity_get_option()` atau melakukan business-logic lookup sendiri.
Pengambilan data dan penyusunan `$args` terjadi di:
- File komposisi halaman (`page-templates/page-*.php`), atau
- Helper khusus di `includes/helpers/template-helpers.php` yang mengembalikan
  array `$args` siap pakai (mis. `jalaversity_get_hero_home_args()`).

**Aturan turunan**:
- Komponen generik tidak boleh tahu halaman mana yang memanggilnya.
- Jika dua section terlihat beda tapi strukturnya sama, beda itu harus jadi
  parameter (`image_position`, `bg`, `variant`), bukan file baru.
- Lookup data per-item yang butuh business logic (mis. `faculty_{id}_image_id`
  dari Options API) dilakukan di helper, bukan di template — sesuai aturan
  CLAUDE.md "jangan taruh logika bisnis di template file".
- Komponen baru hanya dibuat kalau pola UI benar-benar baru. Jangan bikin
  komponen "untuk jaga-jaga" sebelum ada use case nyata (lihat Fakultas page,
  masih ditunda — `profile-card.php` untuk Dekan/Rektor baru dibuat saat
  benar-benar dipakai).

**Taksonomi komponen generik** (hasil refactor Sesi 07):

| Komponen | Menggantikan | Dipakai untuk |
|----------|---------------|----------------|
| `section-header.php` | (inline di tiap section) | Label + heading + lead, dipakai oleh `card-grid.php` dan section lain |
| `hero-page.php` | `hero-home.php` | Hero homepage (`variant='home'`) dan hero subpage (`variant='subpage'`, dengan breadcrumb dark) |
| `content-media.php` | `about.php`, `research.php` | Section 2-kolom gambar+konten, posisi gambar (`image_position`) dan background (`bg`) sebagai parameter |
| `card-grid.php` | `faculty-grid.php`, `locations.php` | Grid kartu (icon dan/atau foto) + judul + deskripsi + link |
| `icon-list.php` | (inline di about/research) | List icon+title+desc, layout `grid` (nilai institusi) atau `rows` (item riset) |
| `numbered-steps.php` | (inline di pmb-section) | Step bernomor (alur PMB), variant `on-dark` |

**File yang TETAP bespoke** (sengaja tidak digenericize): `pmb-section.php` dan
`news-section.php` — layout-nya unik, belum ada halaman lain yang butuh pola
serupa. Generic-kan baru kalau ada use case kedua (anti-premature-abstraction).

**Trade-off yang diterima**: beberapa nilai visual digeneralisasi (mis. tinggi
gambar About 460px vs Research 480px → disatukan jadi 470px; lebar kartu faculty
172px vs locations 190px → disatukan jadi 180px). Selisih ±10-20px tidak terlihat
signifikan di browser, dan ini harga yang wajar untuk satu komponen reusable
dibanding mempertahankan presisi piksel di balik duplikasi file.

---

### 9. Page Builder — ACF Pro Flexible Content

**Konteks keputusan**: Sesi 07 menyelesaikan masalah duplikasi *komponen* (1
section, banyak halaman). Sesi 08 menjawab masalah berikutnya yang diangkat
owner: admin WP butuh menyusun *halaman* dari section-section itu secara
dinamis — tambah, hapus, reorder, edit copywriting, dan repeater untuk
section dengan jumlah item tidak tetap (Prodi/Fakultas) — tanpa developer
membuat page template statis baru setiap kali kombinasi section berubah.

**Keputusan**: Pakai **ACF Pro Flexible Content** sebagai mekanisme builder,
bukan native Gutenberg custom blocks atau plugin repeater gratis. Ini adalah
**pengecualian eksplisit** dari aturan CLAUDE.md "tidak menggunakan plugin
berat" — disetujui owner karena (a) ACF Pro sudah dimiliki/dilisensikan
owner, dan (b) effort membangun custom block editor UI setara dari nol
(drag-reorder, repeater, conditional fields) jauh lebih besar daripada
memakai builder yang sudah matang. Field group didaftarkan **via kode**
(`acf_add_local_field_group()` di `includes/acf/acf-fields.php`), bukan
lewat UI admin ACF + export JSON, supaya skema tetap version-controlled.

**Cara kerja**:
1. `includes/acf/acf-fields.php` — registrasi field group "Page Sections"
   (1 field `flexible_content` bernama `page_sections`), berisi 8 layout.
   Tiap layout field-nya 1:1 mapping ke kontrak `$args` komponen generik
   yang sudah ada (lihat §8) — **investasi pure-render Sesi 07 langsung
   terpakai**, bukan dibangun ulang.
2. `includes/acf/acf-render.php` — dispatcher `jalaversity_render_dynamic_section( $layout )`
   + satu fungsi `jalaversity_render_acf_*()` per layout yang baca
   `get_sub_field()`, susun `$args`, panggil `get_template_part()` ke
   komponen yang sama dengan yang dipakai `page-home.php`. Pola ini identik
   dengan helper `jalaversity_get_*_args()` di `template-helpers.php` —
   hanya sumber datanya ACF sub-field, bukan `jalaversity_get_option()`.
3. `page-templates/page-dynamic.php` ("Template Name: Halaman Dinamis") —
   isinya hanya loop `have_rows('page_sections')` + dispatch, nol business
   logic, sesuai §7.

**Taksonomi 11 layout** (8 awal Sesi 08 + 3 baru Sesi 09 untuk halaman Fakultas):

| Layout | Field penting | Komponen tujuan |
|--------|----------------|------------------|
| `hero` | variant, heading, image, trust_items (repeater), floating_badge (group), **buttons** (repeater maks 2, Sesi 09) | `hero-page.php` |
| `stats_bar` | items (repeater icon/value/label) | `stats-bar.php` |
| `content_media` | heading, body, image, items (repeater), corner_badge (group), link (group) | `content-media.php` |
| `card_grid` | items (**repeater** icon/image/title/desc/address/link/**code/badge/badge_variant/meta**), **dark** (toggle, Sesi 09) — dipakai untuk Prodi/Fakultas/Kampus/Keunggulan | `card-grid.php` |
| `numbered_steps` | heading (opsional), items (repeater number/title/desc) | `numbered-steps.php` |
| `cta_banner` | heading, body, btn_primary/btn_ghost (group) | `cta-banner.php` |
| `pmb_section` | wave_label, heading, body (wysiwyg), steps (repeater) | `pmb-section.php` |
| `news_section` | — (full WP_Query, tidak ada field) | `news-section.php` |
| `sub_nav` *(Sesi 09)* | items (repeater label/href anchor) | `sub-nav.php` *(baru)* |
| `profile_quote` *(Sesi 09)* | image, name, title, label, heading, quote, body | `profile-quote.php` *(baru, sambutan Dekan/Rektor)* |
| `checklist_cards` *(Sesi 09)* | heading, checklist (repeater icon/text), cards_heading, cards (repeater icon/title/desc) | `checklist-cards.php` *(baru — komposisi `icon-list.php` + `card-grid.php`, bukan styling baru)* |

**Penambahan Sesi 09** (audit kecocokan terhadap `docs/templates/Fakultas Tarbiyah.dc.html`):
- `hero-page.php`: tambah `$args['buttons']` (maks 2, di-`array_slice`) untuk varian subpage yang pakai 2 tombol CTA bukan search form — `show_search` tetap dinamis seperti sebelumnya (toggle, tidak terkait buttons).
- `card-grid.php`: tambah field opsional per-item `code` (badge kotak, mis. kode prodi "PAI"), `badge`/`badge_variant` (pill akreditasi), `meta` (repeater key-value, mis. Jenjang/Gelar) — dipakai untuk kartu Program Studi. Tambah `$args['dark']` untuk varian glass di atas background gelap (mis. "Keunggulan Fakultas") — kelas `.card--grid-dark` di card, wrapper `<section>` dark+girih (`.card-grid-dark-bg`) dipasang oleh render bridge, BUKAN oleh card-grid.php sendiri (supaya gradient full-bleed, bukan inset card di dalam container).
- `section-header.php`: tambah `$args['dark']` (meneruskan ke `jalaversity_section_label()` yang sudah lama punya param `$dark` tapi belum pernah dipakai — gap lama yang baru ketahuan saat audit ini).
- `icon-list.php`: tambah varian layout ketiga `'plain'` (satu kolom, tanpa card chrome) untuk checklist sederhana — `'grid'` dan `'rows'` yang sudah ada tidak cocok (grid = multi-kolom, rows = ada card chrome).
- 3 komponen baru: `sub-nav.php` (anchor jump-link), `profile-quote.php` (sambutan Dekan/Rektor — foto bulat + nama + quote box), `checklist-cards.php` (2-kolom: checklist polos kiri + card-grid kanan, murni komposisi ulang komponen existing).
- **Trade-off yang diterima**: kartu "Prospek Karier" di `checklist_cards` mereuse `card-grid.php` apa adanya (ukuran ikon/font card-grid lebih besar dari mockup yang aslinya kartu kecil) — diterima demi reuse, sama prinsipnya dengan trade-off Sesi 07.

**Perbaikan pure-render yang ditemukan saat integrasi**: `stats-bar.php` dan
`pmb-section.php` lolos dari refactor Sesi 07 (dibuat Sesi 06, tidak pernah
disentuh ulang) dan ternyata masih hardcode panggil `jalaversity_get_option()`/
`jalaversity_get_stats()`/`jalaversity_get_pmb_steps()` langsung — melanggar
aturan §8. Keduanya diperbaiki jadi `$args['x'] ?? jalaversity_get_option(...)`
(pola yang sama dengan `cta-banner.php`), supaya bisa jadi layout ACF tanpa
merusak pemanggilan existing tanpa `$args` di `page-home.php`. `numbered-steps.php`
ditambahkan dukungan header opsional (`label`/`heading`/`lead` via
`section-header.php`) agar bisa dipakai berdiri sendiri di Halaman Dinamis,
bukan hanya sebagai bagian dari `pmb-section.php`.

**Field "link" pakai ACF `group`, bukan native link field** — native ACF
link field mengembalikan shape `title`/`url`/`target` yang tidak match
kontrak existing `['label','url','external'=>bool]`. Group dengan 3
sub-field eksplisit lebih predictable untuk di-mapping 1:1 ke komponen.

**`page-templates/page-home.php` tetap hidup berdampingan** dengan
`page-dynamic.php` — tidak dihapus atau dimigrasikan otomatis. Owner yang
menentukan kapan (jika pernah) homepage dipindah ke sistem ACF, supaya tidak
ada risiko kehilangan homepage yang sudah jalan kalau isian ACF belum lengkap.

**Resep menambah layout baru di masa depan**: 1 komponen baru di
`template-parts/components/` (kalau belum ada) + 1 definisi layout baru di
`acf-fields.php` + 1 fungsi render baru di `acf-render.php`. Layout khusus
halaman Fakultas (profile-card Dekan, Keunggulan, dll) sengaja ditunda
sampai halaman itu benar-benar dibangun (anti-premature-abstraction, sama
prinsipnya dengan §8).

---

### 10. Content Templates — Post/Archive/Page (Desain Artikel)

**Konteks keputusan**: User punya desain artikel (single post, archive,
halaman) yang sudah jadi di theme referensi lain (`jalawarta`, theme berita
di `/Users/webane/sites/modernnews/...`). Diminta porting **desain
artikelnya saja** — header/footer tetap default jalaversity. Sebelum ini,
jalaversity belum punya `single.php`/`archive.php`/`page.php` sama sekali
(fallback ke `index.php`).

**Dua jenis komponen — jangan disamakan aturannya**:
1. **Pure-$args components** (§8, dipakai ACF flexible-content page builder
   §9) — data arbitrary dari admin, BUKAN dari WP loop. Tidak boleh baca
   `get_the_*()`/global `$post`.
2. **Loop-context components** (BARU, sesi ini) — `content-card.php`,
   `content-single.php`, `content-page.php`, `share-buttons.php`,
   `post-nav.php`, `related-posts.php`. Beroperasi DI DALAM WP loop
   (`the_post()` sudah jalan) — baca `get_the_title()`/`the_content()`/
   `get_the_date()` dst langsung, ini idiom WordPress standar untuk konten
   loop asli (bukan pelanggaran §8 — beda use-case, bukan beda kualitas).
   `$args` di komponen ini HANYA untuk opsi tampilan (mis. `variant`),
   tidak pernah untuk data post itu sendiri.

**File baru**:

| File | Peran |
|---|---|
| `single.php`, `page.php` | Root template: header → loop → footer |
| `index.php`, `archive.php`, `search.php` | **Identik** (header → `content-post-list.php` → footer) — index dipakai juga sebagai blog index (`is_home()`) saat tidak ada front page statis. Bukan 3x logic loop terduplikasi (lihat poin di bawah) |
| `template-parts/content/content-post-list.php` | Body bersama index/archive/search — judul header menyesuaikan context (`is_home`/`is_search`/`get_the_archive_title()`), loop `content-card.php`, pagination, fallback kosong |
| `template-parts/content/content-single.php` | Body single post lengkap (meta, featured image, `.entry-content`, share, related, prev/next) |
| `template-parts/content/content-card.php` | **1 file, 4 varian** via `$args['variant']`: `overlay` (gambar background+gradient, judul overlay), `list` (gambar ±30% kiri), `klasik` (gambar stack atas+excerpt), `title` (tanpa gambar) — bukan 4 file terpisah, konsisten §8. **Direvisi Sesi 13** dari spek user persis (lihat jalawarta `content-list.php`/`content-klasik.php`/`content-overlay.php`) — varian lama `classic`/`grid` diganti `overlay`/`klasik`. Tiap varian cuma menyusun ulang 3 helper bersama (`jalaversity_card_title()`/`jalaversity_post_meta_line()`/`jalaversity_card_thumbnail()` di `post-helpers.php`) — bukan markup/CSS baru per varian |
| `template-parts/content/content-page.php` | Entry-header + `.entry-content` untuk Page generik |
| `template-parts/content/content-none.php` | Fallback archive/search kosong (class CSS `.no-results` — BUKAN `.content-none`, itu nama utility Tailwind) |
| `template-parts/components/{search-form,share-buttons,post-nav,related-posts}.php` | Reusable, loop-context |
| `sidebar.php` (root) + `template-parts/components/sidebar.php` | **Sidebar default Sesi 14, styling widget WP Sesi 16** — SATU desain dipakai identik di index/archive/tag/category/author/search (lewat `content-post-list.php`) DAN single post (lewat `content-single.php`). Isi: search form, "Artikel Terpopuler" (query `_jalaversity_views` DESC, render `content-card.php` varian `title`), daftar Kategori (hand-built), lalu `dynamic_sidebar('sidebar-1')` — **TIDAK dibungkus div sendiri** (WP sudah bungkus tiap widget dengan `<section class="widget">` via `before_widget`, dobel wrapper = box-di-dalam-box). Layout 2-kolom via `.content-with-sidebar` (1 kolom di mobile, `1fr 360px` mulai 992px). **CSS generik** `.sidebar .widget`/`.widget .wp-block-heading`/`.widget_block ul/li/a` (`_article.scss`) bikin APAPUN widget block yang admin drop via Appearance > Widgets otomatis rapi (box+judul+list seragam), tanpa CSS baru per jenis widget — widget Search disembunyikan (`display:none`) karena redundan dengan search form kustom yang sudah ada |
| `page-templates/page-blog.php` ("Halaman Blog") | Landing artikel: featured (`is_featured`) + latest posts paginated |
| `includes/helpers/post-helpers.php` | `jalaversity_reading_time()`, `jalaversity_get_views()`/`jalaversity_track_post_view()`, `jalaversity_get_related_posts()` (tags→categories→random), `jalaversity_post_meta_line()` (meta seragam "Kategori - Tanggal", lihat di bawah), `jalaversity_card_title()`/`jalaversity_card_thumbnail()` (helper kartu lainnya), filter `excerpt_length`/`excerpt_more`, filter `get_the_archive_title` (`jalaversity_clean_archive_title()` — buang prefix "Category:"/"Tag:" default WP core, dibangun ulang dari `single_*_title()` per context, bukan strip string supaya locale-proof) |
| `includes/acf/acf-post-fields.php` | Field group **terpisah** dari `acf-fields.php` — lokasi `post_type == post` (bukan `page_template`). 2 field: `is_featured` (true_false), `editor` (user). Dibaca langsung via `get_field()` — **tidak butuh render-bridge** (pola itu khusus flexible_content multi-layout, field fixed tidak perlu) |
| `scss/front/_article.scss` | Partial CSS terpisah dari `_components.scss` (yang isinya komponen ACF builder) |

**Kenapa view-count BUKAN field ACF**: `is_featured`/`editor` adalah konten
yang diedit admin lewat editor (cocok ACF). View-count adalah counter
terprogram yang nambah otomatis tiap page-load (`jalaversity_track_post_view()`)
— bukan sesuatu yang diedit admin lewat form, jadi plain `update_post_meta()`/
`get_post_meta()` adalah tool yang tepat, bukan ACF. "Pakai ACF untuk semua
custom meta" tidak berarti pakai ACF untuk data yang sifatnya programatik.

**Reuse komponen existing**: `breadcrumb.php` sudah auto-detect semua
context (`is_single`, `is_page`, `is_category/tag/tax`, `is_search`,
`is_404`) sejak awal — tidak perlu diubah sama sekali. `pagination.php`
sudah menerima `$args['query']` — dipakai langsung untuk custom `WP_Query`
di `page-blog.php`. Varian `grid` di `content-card.php` reuse class
`.card`/`.card--grid-photo` dari `card-grid.php` (chrome kartu: rounded,
shadow, hover-lift) — hanya nambah modifier `.card--post` untuk meta row.

**Fitur jalawarta yang SENGAJA TIDAK diadopsi** (bukan "desain", melainkan
fitur monetisasi/UX spesifik news-site, atau di luar keputusan user):
Facebook Comments SDK (user pilih: comments dimatikan total — tidak ada
`comment_form()` sama sekali), ad injection di paragraf-N, gallery/video
reorder via `the_content` filter, lightbox/Magnific Popup, mobile sticky
toolbar + bottom-sheet "more menu" (disederhanakan jadi share-row biasa
yang tetap tampil di semua ukuran layar), custom icon font FontAne (diganti
bullet css polos pakai `var(--color-accent)`), widget post custom OOP
jalawarta (`Webane_Posts_Widget` — kompleks, tidak ada sanitasi/escaping
input, melanggar prinsip keamanan CLAUDE.md; fungsinya sudah cukup ditutupi
blok "Artikel Terpopuler" hand-built di sidebar).

> **Catatan revisi Sesi 14**: keputusan "full-width tanpa sidebar" di Sesi 11
> (poin di atas, dulu tertulis di sini) **DIBALIK** atas permintaan user —
> sidebar default sekarang ada, sama persis dengan struktur jalawarta
> (`sidebar.php` → `dynamic_sidebar()`), hanya isinya di-custom (lihat tabel
> file di atas). `content_width = 780` yang sudah diset sejak awal proyek
> (`includes/setup.php`) ternyata memang dirancang untuk skenario sidebar
> (1200px container − 360px sidebar − 48px gap ≈ 792px, dekat 780) — jadi
> penambahan sidebar ini sebenarnya MEMENUHI niat desain awal, bukan
> penyimpangan baru.

**Fitur tambahan yang diadopsi**: deteksi paragraf Arab (relevan untuk
konten institusi Islam) — class `.arabic-paragraph` ditambahkan via JS
(`initArabicParagraphs()` di `js/front/main.js`, regex Unicode range Arab),
BUKAN via PHP regex pada HTML (lebih rapuh) — styling pakai `font-family:
serif` generik, sengaja tidak menambah Google Font baru untuk Arab (browser
otomatis fallback ke font sistem yang punya glyph Arab).

---

### 11. Image Sizes — 16:9 "Golden Ratio" + Auto-Convert WebP

**Keputusan (Sesi 15)**: 3 ukuran custom lama (`jalaversity-card` 600×400,
`jalaversity-hero` 1200×600, `jalaversity-thumb` 400×300 — masing-masing
rasio berbeda-beda, tidak konsisten) **diganti total** dengan 4 ukuran baru,
semua hard-crop (`add_image_size(..., true)` — WP crop tengah otomatis ke
rasio pas, bukan cuma scale):

| Nama | Dimensi | Rasio | Dipakai untuk |
|---|---|---|---|
| `jalaversity-large` | 1120×630 | 16:9 | **Belum diwire ke tempat manapun** — diregister sesuai spek, dipakai nanti kalau ada kebutuhan (mis. hero image) |
| `jalaversity-medium` | 800×450 | 16:9 | Featured image single post (`content-single.php`) dan Page (`content-page.php`) — diarahkan ulang user dari `large` |
| `jalaversity-thumbnail` | 400×225 | 16:9 | Varian `overlay` DAN `klasik` di `content-card.php` — diarahkan ulang user dari `medium`/`large` |
| `jalaversity-square` | 400×400 | 1:1 | Varian `list` di `content-card.php` (cocok dengan CSS `aspect-ratio:1` yang sudah ada di `.card--post-list .card__media`) |

**Mapping final** (setelah arahan eksplisit user, beda dari mapping awal
yang sempat dibuat berdasarkan asumsi "lebih prominent = lebih besar"):
`content-list` → `square`, `content-klasik` → `thumbnail`, `content-overlay`
→ `thumbnail`, single post & Page → `medium`. Default param
`jalaversity_card_thumbnail()` tetap `jalaversity-medium` tapi sudah tidak
ada call site yang benar-benar mengandalkan default ini (semua 3 varian
yang pakai gambar passing `$size` eksplisit) — dipertahankan apa adanya
sebagai fallback aman kalau ada pemanggilan baru tanpa `$size` di masa depan.

Konstanta `JALAVERSITY_IMAGE_SIZES` di `includes/helpers/image-helpers.php`
WAJIB tetap sinkron manual dengan `add_image_size()` di `includes/setup.php`
— dipakai untuk hitung dimensi placeholder SVG.

**Auto-convert ke WebP** (`includes/helpers/image-helpers.php`):
- `jalaversity_webp_output_format()` di filter `image_editor_output_format`
  (WP core 5.8+, bukan plugin) — JPEG dan PNG yang digenerate jadi 4 ukuran
  di atas otomatis disimpan sebagai WebP. GIF sengaja TIDAK dikonversi
  (animasi akan hilang kalau dikonversi statis).
- Dijaga dengan `wp_image_editor_supports(['mime_type'=>'image/webp'])` —
  kalau server (GD/Imagick) tidak support WebP, filter ini no-op, format
  asli dipertahankan. Tidak akan fatal di hosting manapun.
- `jalaversity_webp_quality()` di filter `wp_image_editor_default_quality`
  — kualitas WebP dikunci ke 80 (bukan default WP 82) sesuai permintaan,
  hanya untuk output WebP (mime type lain tidak terpengaruh).
- **File asli yang diupload TIDAK dikonversi** — keputusan eksplisit (lihat
  klarifikasi user) supaya selalu ada backup kualitas/format penuh. Hanya
  4 ukuran turunan di atas yang jadi WebP.

**Limitasi yang diketahui (bukan bug)**: ukuran/konversi baru ini cuma
berlaku untuk upload BARU sejak perubahan ini diterapkan — WordPress tidak
otomatis meng-crop ulang gambar yang sudah ada di media library. Kalau
perlu backfill ke gambar lama: `wp media regenerate --yes` via WP-CLI.
Sengaja tidak dibuat tooling custom untuk ini (scope ditunda, lihat
keputusan user) — WP-CLI sudah cukup kalau dibutuhkan nanti.

**Mengganti, bukan menambah**: 3 ukuran lama dihapus total (bukan
dipertahankan berdampingan) supaya tidak ada 7 ukuran custom yang
membingungkan dan boros storage per upload — semua call site lama
(`content-single.php`, `content-page.php`, `content-card.php` 3 varian,
default param `jalaversity_card_thumbnail()`) sudah dipetakan ulang ke
ukuran baru yang paling sesuai konteksnya.

---

## File Loading Order (functions.php)

```php
// functions.php — hanya require, nol logic
$includes = [
    'includes/setup.php',           // Theme setup & register support
    'includes/security.php',        // WordPress hardening
    'includes/enqueue.php',         // CSS/JS enqueue + CSS vars output
    'includes/seo.php',             // Meta, OG, Schema, canonical
    'includes/settings/settings-page.php',      // Admin menu registration
    'includes/settings/settings-fields.php',    // Fields & sections
    'includes/settings/settings-sanitize.php',  // Input sanitasi
    'includes/helpers/options-helpers.php',     // get_option wrapper
    'includes/helpers/image-helpers.php',       // srcset, lazy load
    'includes/helpers/icon-helpers.php',        // SVG icon renderer
    'includes/helpers/social-helpers.php',      // Share buttons, OG image
    'includes/helpers/post-helpers.php',        // Reading time, views, related posts (§10)
    'includes/acf/acf-fields.php',              // ACF flexible content schema (§9)
    'includes/acf/acf-render.php',              // ACF layout → component render bridge
    'includes/acf/acf-post-fields.php',         // ACF post meta: is_featured, editor (§10)
];

foreach ( $includes as $file ) {
    require_once get_template_directory() . '/' . $file;
}
```

---

## npm Scripts & Build Setup

**`package.json`** (root theme):
```json
{
  "name": "jalaversity",
  "version": "1.0.0",
  "scripts": {
    "dev":   "tailwindcss -i ./scss/front/main.scss -o ./css/front.css --watch",
    "build": "tailwindcss -i ./scss/front/main.scss -o ./css/front.css --minify",
    "dev:admin":   "tailwindcss -i ./scss/admin/main.scss -o ./css/admin.css --watch",
    "build:admin": "tailwindcss -i ./scss/admin/main.scss -o ./css/admin.css --minify"
  },
  "devDependencies": {
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.0",
    "postcss": "^8.4.0"
  }
}
```

> **Catatan**: File `node_modules/` dan output CSS (`css/*.css`) **TIDAK** di-commit ke git.
> CSS di-generate di environment development, di-commit hanya file SCSS source.
> Untuk production deploy, jalankan `npm run build` sebelum upload.

---

## Risiko & Mitigasi

| Risiko | Kemungkinan | Dampak | Mitigasi |
|--------|-------------|--------|----------|
| CSS terlalu besar (> 30KB) | Sedang | Tinggi | Audit size tiap fase, Tailwind purge ketat via content paths |
| CSS custom properties tidak didukung browser lama | Rendah | Sedang | IE11 bukan target; caniuse: 97%+ support |
| Build step gagal di production | Rendah | Tinggi | Commit file `css/*.css` ke git setelah build, bukan depend runtime |
| XSS via color settings | Rendah | Tinggi | `sanitize_hex_color()` wajib, hanya allow format `#rrggbb` |
| Google Fonts diblokir (China, dll) | Rendah | Sedang | System font fallback sudah tersedia di stack |
| Query berlebihan di News Section | Sedang | Sedang | Batasi `posts_per_page`, gunakan transient cache jika perlu |
| Negative margin stats bar collapse di mobile | Sedang | Sedang | Test ketat di 375px, fallback ke `margin-top: 0` dengan media query |
| Font weight tidak tersedia → FOUT | Rendah | Rendah | `display=swap` sudah meminimasi FOUT |

---

## Catatan Arsitek

- **CSS custom properties adalah kunci fleksibilitas**. Karena theme ini bisa dipakai
  untuk berbagai institusi (bukan hanya Al-Ikhlash), brand color harus mudah ganti
  tanpa rebuild CSS. Pola ini memungkinkan itu.

- **Jangan pernah hardcode warna brand di SCSS**. Selalu gunakan `var(--color-*)`.
  Warna yang bukan brand (putih, teks, semantic) boleh hardcoded.

- **Tailwind content path harus ketat**. Tambah path baru ke `tailwind.config.js`
  setiap kali ada folder PHP baru — jika tidak, class di file itu tidak akan di-include.

- **PHP 8.1+ enforced**: Gunakan named arguments, `match` expression, `readonly` property,
  dan typed properties. Jangan tulis kode yang kompatibel dengan PHP 7.x.

- **jQuery tidak diload di front-end**. Jika ada plugin yang membutuhkan jQuery
  hanya di front-end, evaluasi ulang pilihan plugin tersebut.
