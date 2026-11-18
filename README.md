# Jalaversity — WordPress Theme for Universities & Islamic Higher Education

Theme WordPress klasik generasi baru untuk website universitas, institut, dan perguruan tinggi Islam milik **Webane Indonesia** (proprietary). Dirancang ringan, aman, mobile-first, dan mendukung penyusunan halaman dinamis.

## Persyaratan Sistem
- **WordPress:** `6.0+` (diuji hingga `6.7+`)
- **PHP:** `8.1+`
- **Plugin Pendukung:** **Advanced Custom Fields (ACF) Pro** (untuk *Halaman Dinamis* dan field CPT *Agenda* / *Post Meta*)
- **Node.js:** `18+` (hanya untuk kompilasi aset SCSS + Tailwind CSS saat pengembangan)

## Perintah Build & Quality Assurance (QA)

```bash
# 1. Instalasi dependensi dev (pertama kali)
npm install
composer install

# 2. Build CSS produksi (scss/front/main.scss -> css/front.css & scss/admin/main.scss -> css/admin.css)
npm run build

# 3. Pemeriksaan kualitas kode & keamanan wajib sebelum commit/merge
composer lint          # Cek sintaks PHP (php -l) + WordPress Coding Standards (PHPCS)
composer analyse       # Analisis statis PHPStan (Level 5)
composer css:check     # Build & verifikasi integritas CSS produksi (< 30 KB gzip)
composer security:scan # Pemindaian keamanan statis (secret, fungsi berbahaya, ABSPATH)
composer dist:check    # Verifikasi paket distribusi bersih sesuai .distignore
```

## Dokumentasi Lengkap
Seluruh aturan kerja dan arsitektur terdokumentasi di dalam repositori:
- [`AGENTS.md`](AGENTS.md) — Aturan utama proyek (Single Source of Truth)
- [`docs/SOP.md`](docs/SOP.md) — Standar Operasional Prosedur (6 langkah per fase)
- [`docs/PROGRESS.md`](docs/PROGRESS.md) — Pelacakan status fase retrofit
- [`docs/RETROFIT-GAPS.md`](docs/RETROFIT-GAPS.md) — Peta temuan audit & kesenjangan
- [`docs/architecture/`](docs/architecture/) — Arsitektur sistem, CSS, keamanan, SEO, dan deployment
- [`docs/decisions/`](docs/decisions/) — Architecture Decision Records (ADR)
