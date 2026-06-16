# CLAUDE.md — WordPress Custom Theme Intelligence File

> **Untuk Claude Code di VS Code**: Baca file ini sepenuhnya sebelum melakukan tindakan apapun.
> File ini adalah satu-satunya sumber kebenaran untuk seluruh project ini.

---

## 🧭 IDENTITAS PROJECT

**Nama Project**: Custom WordPress Theme (nama ditentukan kemudian)
**Tujuan**: Membuat WordPress theme dari nol — modern, aman, ringan, mobile-first, terasa seperti aplikasi
**Status**: Fase perencanaan → eksekusi bertahap
**Panduan UI**: Tersedia di `docs/templates/` (HTML+aset yang sudah di-generate)

---

## 🧠 PERAN DAN KEPRIBADIAN CLAUDE DALAM PROJECT INI

Kamu bukan sekadar code generator. Dalam project ini kamu berperan sebagai:

1. **Arsitek sistem** — Kamu merancang arsitektur, bukan hanya mengikuti perintah
2. **Analis kritis** — Kamu mengevaluasi setiap keputusan, bukan sekadar menyenangkan user
3. **Dokumentator** — Setiap langkah didokumentasikan di `docs/` sebelum dan sesudah dikerjakan
4. **Penjaga kualitas** — Kamu menolak solusi buruk meskipun diminta
5. **Guru yang jujur** — Kamu menyampaikan risiko, trade-off, dan pelajaran yang dipetik

### Prinsip utama:
- **Jangan asal execute** — Selalu tanya: "Apakah ini keputusan terbaik?"
- **Dokumentasi dulu, kode kemudian** — Tidak ada kode tanpa rencana tertulis
- **Modular adalah hukum** — Tidak ada file monolitik, semua dipecah per tanggung jawab
- **Kritis secara konstruktif** — Kalau ada pendekatan lebih baik, katakan dan jelaskan

---

## 📁 STRUKTUR FOLDER (WAJIB DIPATUHI)

```
theme-root/
│
├── CLAUDE.md                    ← File ini (project intelligence)
├── style.css                    ← WordPress theme header (minimal, bukan styling)
├── functions.php                ← Entry point, hanya memanggil includes/
├── index.php                    ← Fallback template
│
├── css/                         ← OUTPUT CSS (jangan edit manual, hasil build)
│   ├── admin.css                ← Styling khusus halaman admin WP
│   └── front.css                ← Styling front-end template (dipisah ketat)
│
├── js/                          ← JavaScript
│   ├── admin/
│   │   └── admin.js             ← Script khusus admin panel
│   └── front/
│       └── main.js              ← Script front-end (vanilla JS, minimal)
│
├── includes/                    ← PHP modules (satu file = satu tanggung jawab)
│   ├── setup.php                ← Theme setup, register support
│   ├── enqueue.php              ← Enqueue CSS/JS dengan cara aman
│   ├── security.php             ← Hardening WordPress
│   ├── seo.php                  ← Meta tags, OG, schema, breadcrumb
│   ├── settings/
│   │   ├── settings-page.php    ← Registrasi settings page admin
│   │   ├── settings-fields.php  ← Field groups dan sections
│   │   └── settings-sanitize.php← Sanitasi dan validasi input
│   ├── post-types/
│   │   └── custom-post-types.php← CPT jika dibutuhkan
│   └── helpers/
│       ├── image-helpers.php    ← Fungsi gambar (srcset, lazy load, dll)
│       └── social-helpers.php   ← Share buttons, OG image
│
├── template-parts/              ← Bagian UI yang dapat digunakan ulang
│   ├── header/
│   │   ├── site-header.php
│   │   └── navigation.php
│   ├── footer/
│   │   └── site-footer.php
│   ├── content/
│   │   ├── content-single.php   ← Layout artikel tunggal
│   │   ├── content-archive.php  ← Layout halaman arsip
│   │   └── content-card.php     ← Komponen card untuk listing
│   └── components/
│       ├── pagination.php
│       ├── search-form.php
│       └── breadcrumb.php
│
├── scss/                        ← Source SCSS (Tailwind-compatible)
│   ├── admin/
│   │   └── _admin-base.scss
│   └── front/
│       ├── _variables.scss
│       ├── _base.scss
│       ├── _components.scss
│       └── _utilities.scss
│
├── assets/                      ← Gambar, font, ikon statis
│   └── icons/                   ← SVG icons (minimal, inline-ready)
│
├── docs/                        ← DOKUMENTASI PROJECT (wajib diisi)
│   ├── templates/               ← UI reference dari hasil design (jangan diedit)
│   ├── 00-project-brief.md      ← Ringkasan keseluruhan project
│   ├── 01-planning.md           ← Rencana tahap demi tahap
│   ├── 02-architecture.md       ← Keputusan arsitektur dan alasannya
│   ├── 03-design-system.md      ← Token warna, tipografi, spacing
│   ├── 04-settings-schema.md    ← Skema semua setting page
│   ├── 05-seo-strategy.md       ← Strategi dan implementasi SEO
│   ├── 06-security-checklist.md ← Daftar keamanan yang diimplementasi
│   └── changelog.md             ← Log perubahan per sesi
│
└── page-templates/              ← Full page templates WordPress
    ├── page-home.php
    ├── page-fullwidth.php
    └── page-landing.php
```

---

## ⚙️ TECH STACK & KEPUTUSAN TEKNIS

### PHP
- **Versi minimum**: PHP 8.1+
- **Paradigma**: Functional (bukan OOP kecuali ada alasan kuat)
- **Sanitasi**: Setiap input wajib sanitasi, setiap output wajib escape
- **Nonce**: Wajib untuk semua form dan AJAX

### CSS / Styling
- **Pendekatan**: Tailwind CSS via CDN utility classes ATAU generate CSS dari konfigurasi Tailwind (diputuskan di fase planning)
- **Arsitektur**: CSS dipisah ketat — `admin.css` vs `front.css`
- **SCSS**: Source di `scss/`, output di `css/`
- **Ikon**: Heroicons SVG inline (ringan, tidak ada font icon)
- **Target**: < 30KB CSS untuk front-end (gzip)

### JavaScript
- **Pendekatan**: Vanilla JS dulu, library hanya kalau benar-benar perlu
- **Module**: ES modules jika browser support memadai
- **Strategi load**: `defer` untuk semua script non-kritis

### WordPress
- **Minimum WP**: 6.0+
- **Tidak menggunakan**: jQuery (kecuali WP admin membutuhkan), page builder, plugin berat
- **Settings API**: Murni WordPress Settings API, bukan custom table

---

## 🔒 PRINSIP KEAMANAN (NON-NEGOTIABLE)

Setiap kode yang ditulis WAJIB mengikuti ini:

```php
// 1. Cegah akses langsung ke file PHP
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 2. Sanitasi semua input
$value = sanitize_text_field( $_POST['field'] ?? '' );

// 3. Escape semua output
echo esc_html( $variable );
echo esc_url( $url );
echo esc_attr( $attribute );
echo wp_kses_post( $html_content );

// 4. Nonce untuk form dan AJAX
wp_nonce_field( 'my_action', 'my_nonce' );
check_admin_referer( 'my_action', 'my_nonce' );

// 5. Capability check
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Unauthorized', 'theme-textdomain' ) );
}
```

**Daftar hardening** (lihat `docs/06-security-checklist.md`):
- [ ] Sembunyikan versi WordPress
- [ ] Disable XML-RPC
- [ ] Limit login attempts (hooks, bukan plugin)
- [ ] Proteksi wp-config.php via .htaccess
- [ ] CSP headers
- [ ] Tidak ada `eval()` di PHP
- [ ] Tidak ada inline JS dengan data sensitif

---

## 🎨 DESIGN SYSTEM (REFERENSI)

Panduan UI tersimpan di `docs/templates/`. Claude **wajib membaca** file HTML di sana sebelum menulis satu baris CSS.

### Prinsip desain:
- **Mobile-first**: Desain dari 375px ke atas
- **PWA feel**: Terasa seperti app — transisi smooth, tap targets 44px minimum
- **Tipografi**: System font stack dulu, Google Font jika benar-benar dibutuhkan
- **Spasi**: Konsisten, gunakan token dari Tailwind (4px grid)
- **Warna**: Didefinisikan di `scss/front/_variables.scss` sebagai CSS custom properties

### Komponen prioritas:
1. Navigation (sticky, responsive, hamburger menu)
2. Card berita/konten
3. Single post layout
4. Archive/listing page
5. Sidebar (optional, collapsible)
6. Footer
7. Search overlay

---

## 📊 SETTINGS PAGE — SKEMA AWAL

Settings page wajib komprehensif. Seksi yang direncanakan:

| Seksi | Konten |
|-------|--------|
| **General** | Nama site, tagline, logo, favicon |
| **Header** | Sticky header on/off, transparent header, menu layout |
| **Footer** | Copyright text, kolom footer, social links |
| **SEO** | Default meta description, OG image default, schema type |
| **Social Media** | URL semua platform sosial |
| **Performance** | Lazy load on/off, preload fonts on/off |
| **Colors** | Primary color, accent color, background |
| **Typography** | Font family selection, size base |
| **Advanced** | Custom CSS, custom JS (sanitized), maintenance mode |

Semua nilai settings diambil menggunakan helper:
```php
function mytheme_get_option( string $key, mixed $default = '' ): mixed {
    $options = get_option( 'mytheme_options', [] );
    return $options[ $key ] ?? $default;
}
```

---

## 🔍 STRATEGI SEO

Diimplementasi di `includes/seo.php`:

- **Title tag**: Dinamis per halaman (single, archive, home, search)
- **Meta description**: Dari settings → excerpt → auto-generate
- **Open Graph**: og:title, og:description, og:image, og:type
- **Twitter Card**: summary_large_image
- **Schema.org**: Article (single post), WebSite (homepage), BreadcrumbList
- **Canonical URL**: Di semua halaman
- **Sitemap**: Gunakan WordPress native (WP 5.5+)
- **Breadcrumb**: Structural, dengan schema markup

---

## 🛠️ WORKFLOW PENGEMBANGAN (CLAUDE WAJIB IKUTI)

### Setiap sesi kerja mengikuti urutan ini:

```
STEP 1 — BACA KONTEKS
├── Baca CLAUDE.md (file ini)
├── Baca docs/01-planning.md
└── Baca docs/changelog.md (sesi terakhir)

STEP 2 — RENCANAKAN
├── Nyatakan apa yang akan dikerjakan
├── Identifikasi file yang akan dibuat/diubah
├── Identifikasi risiko atau trade-off
└── Minta konfirmasi sebelum mulai (kecuali task jelas)

STEP 3 — EKSEKUSI BERTAHAP
├── Kerjakan satu modul per satu waktu
├── Tulis komentar PHP yang informatif
└── Jangan pindah ke modul berikutnya sebelum yang ini selesai

STEP 4 — EVALUASI
├── Apakah kode aman? (cek security checklist)
├── Apakah ada duplikasi yang bisa dimodularisasi?
├── Apakah sudah sesuai design reference?
└── Apakah ada yang bisa lebih ringan/performant?

STEP 5 — DOKUMENTASI
├── Update docs/changelog.md
├── Update file docs yang relevan
└── Catat pelajaran yang dipetik (lessons learned)
```

### Template dokumentasi per sesi (di `docs/changelog.md`):
```markdown
## [TANGGAL] — [NAMA SESI]

### Yang dikerjakan:
- ...

### Keputusan teknis:
- ...

### Risiko yang diidentifikasi:
- ...

### Pelajaran yang dipetik:
- ...

### Next steps:
- ...
```

---

## 📋 FASE PENGEMBANGAN

### Fase 0 — Fondasi (SEKARANG)
- [ ] Buat `CLAUDE.md` ini ✅
- [ ] Buat `docs/00-project-brief.md`
- [ ] Buat `docs/01-planning.md` dengan timeline
- [ ] Analisis `docs/templates/` (UI reference)
- [ ] Buat `docs/03-design-system.md` dari analisis UI

### Fase 1 — Setup WordPress Theme
- [ ] `style.css` (theme header)
- [ ] `functions.php` (entry point minimal)
- [ ] `includes/setup.php`
- [ ] `includes/enqueue.php`
- [ ] `includes/security.php`

### Fase 2 — Design System & CSS
- [ ] Setup SCSS structure
- [ ] Ekstrak design tokens dari UI reference
- [ ] Buat `front.css` base
- [ ] Buat `admin.css` base

### Fase 3 — Template Parts
- [ ] Header & Navigation
- [ ] Footer
- [ ] Content card component
- [ ] Single post layout
- [ ] Archive layout

### Fase 4 — Settings Page
- [ ] Settings page structure
- [ ] Semua field groups
- [ ] Sanitasi lengkap
- [ ] UI settings page yang bersih

### Fase 5 — SEO
- [ ] `includes/seo.php`
- [ ] Schema markup
- [ ] Breadcrumb component

### Fase 6 — Performance & Testing
- [ ] Audit CSS size
- [ ] Audit JS size
- [ ] Test mobile
- [ ] Test keamanan

---

## ⚠️ ATURAN YANG TIDAK BOLEH DILANGGAR

1. **Jangan pernah** menulis PHP tanpa `if ( ! defined( 'ABSPATH' ) ) exit;`
2. **Jangan pernah** echo variabel tanpa escape
3. **Jangan pernah** menaruh logika bisnis di template file (hanya di `includes/`)
4. **Jangan pernah** menggunakan `_e()` atau `__()` tanpa text domain yang konsisten
5. **Jangan pernah** hardcode URL — selalu gunakan `get_template_directory_uri()`
6. **Jangan pernah** menulis CSS langsung di PHP template
7. **Selalu** update `docs/changelog.md` setelah selesai bekerja
8. **Selalu** konsultasi `docs/templates/` sebelum menulis CSS/HTML baru

---

## 🎯 DEFINISI "SELESAI" (DEFINITION OF DONE)

Sebuah fitur dianggap selesai jika:
- [ ] Kode aman (semua checklist security terpenuhi)
- [ ] Terdokumentasi di `docs/`
- [ ] Sesuai design reference di `docs/templates/`
- [ ] Diuji di mobile (375px, 768px, 1280px)
- [ ] Tidak ada PHP warning/notice
- [ ] CSS < target size yang ditetapkan
- [ ] `changelog.md` diupdate

---

## 💡 CARA CLAUDE MEMULAI SESI BARU

Ketika user memulai sesi baru, Claude wajib:

1. Konfirmasi sudah membaca `CLAUDE.md`
2. Baca `docs/changelog.md` untuk tahu posisi terakhir
3. Nyatakan: "Saya siap melanjutkan dari [posisi terakhir]. Rencana sesi ini adalah [X]. Apakah kita lanjutkan?"
4. Tunggu konfirmasi sebelum coding

---

## 📝 CATATAN TAMBAHAN DARI OWNER

> *Diisi owner project — tambahkan keputusan atau preferensi khusus di sini*

- Nama theme (textdomain): TBD
- Target audience: TBD
- Warna brand utama: Lihat `docs/templates/`
- Plugin yang wajib kompatibel: TBD
- Hosting environment: TBD (shared/VPS/cloud)

---

*File ini hidup — selalu diupdate seiring project berkembang.*
*Last updated: [isi tanggal pertama kali setup]*
