# Fase 00: Audit Awal & Pemasangan Fondasi Standar

- **Status:** `Done`
- **Jenis:** `Normal`
- **Tanggal Mulai:** 2026-11-18
- **Tanggal Selesai:** 2026-11-18
- **Referensi Gap:** Penyelesaian `GAP-10` (konsolidasi dokumen lama) & `GAP-11` (tooling audit otomatis), serta persiapan roadmap untuk `GAP-01` s/d `GAP-16`.

---

## 1. Tujuan (Objectives)
1. Melakukan audit menyeluruh terhadap struktur kode, keamanan, komponen UI, performa, dan arsitektur theme **Jalaversity (`v1.1.1`)** tanpa mengubah perilaku runtime theme.
2. Memasang fondasi dokumentasi dan aturan kerja berstandar **Sekolahane**, **Newsane**, dan **Modernnews (Jalawarta)**: `AGENTS.md`, `CLAUDE.md`, `README.md`, `docs/SOP.md`, `docs/PROGRESS.md`, `docs/RETROFIT-GAPS.md`, `docs/conventions.md`, `docs/glossary.md`, `docs/lessons-learned.md`, `docs/architecture/*`, dan `docs/decisions/adr-0001..0006`.
3. Mengonsolidasikan dokumen lama (`docs/00-project-brief.md` s/d `docs/04-settings-schema.md` & `docs/changelog.md`) ke dalam struktur standar `docs/architecture/*` dan `docs/lessons-learned.md` agar tidak ada dua sistem dokumentasi yang saling tumpang-tindih.
4. Memasang tooling analisis statis, standar kode, dan pemindai keamanan otomatis (`composer.json`, `phpcs.xml.dist`, `phpstan.neon.dist`, `phpstan-bootstrap.php`, `phpstan-baseline.neon`, `bin/security-scan.sh`, `bin/css-audit.py`, `bin/check-dist.sh`, `bin/lint-changed.sh`, `.distignore`, `.gitattributes`, `.gitignore`).

---

## 2. Ruang Lingkup Perubahan (Scope)

### File yang Dibuat / Diubah
- `.gitignore`, `.gitattributes`, `.distignore`
- `AGENTS.md`, `CLAUDE.md`, `README.md`
- `composer.json`, `composer.lock`, `phpcs.xml.dist`, `phpstan.neon.dist`, `phpstan-bootstrap.php`, `phpstan-baseline.neon`
- `bin/security-scan.sh`, `bin/css-audit.py`, `bin/check-dist.sh`, `bin/lint-changed.sh`
- `docs/SOP.md`, `docs/PROGRESS.md`, `docs/RETROFIT-GAPS.md`, `docs/conventions.md`, `docs/glossary.md`, `docs/lessons-learned.md`
- `docs/phases/phase-template.md`, `docs/phases/phase-00-fondasi.md`
- `docs/architecture/architecture-overview.md`, `docs/architecture/architecture-css.md`, `docs/architecture/architecture-security.md`, `docs/architecture/architecture-deployment.md`, `docs/architecture/architecture-seo.md`
- `docs/decisions/adr-template.md`, `adr-0001-tool-agnostic.md`, `adr-0002-versioning-dan-rilis.md`, `adr-0003-auto-update-github-private.md`, `adr-0004-auto-convert-webp.md`, `adr-0005-hybrid-settings-dan-acf-builder.md`, `adr-0006-hybrid-seo-dan-schema-kampus.md`
- Menghapus file dokumentasi bernomor lama (`docs/00-project-brief.md`, `docs/01-planning.md`, `docs/02-architecture.md`, `docs/03-design-system.md`, `docs/04-settings-schema.md`, `docs/changelog.md`) setelah seluruh isinya dilebur ke `docs/architecture/*` dan `docs/lessons-learned.md` (mockup HTML di `docs/templates/` tetap dipertahankan).

### Di Luar Ruang Lingkup (Out of Scope)
- Modifikasi file PHP/JS/SCSS runtime theme (`functions.php`, `includes/*`, `page-templates/*`, `template-parts/*`, `js/*`, `scss/*`). Seluruh perbaikan runtime dijadwalkan mulai **Fase 01**.

---

## 3. Langkah Implementasi (Implementation Steps)
1. [x] Mengaudit seluruh 41 file PHP theme di root, `includes/`, `page-templates/`, dan `template-parts/`, serta file JS (`js/front/main.js`, `js/admin/admin.js`) dan SCSS (`scss/front/*`, `scss/admin/*`).
2. [x] Menyusun `AGENTS.md`, `CLAUDE.md`, `README.md`, dan seluruh dokumen di `docs/` sesuai cetak biru `sekolahane`, `newsane`, dan `jalawarta`.
3. [x] Memasang konfigurasi Composer, PHPCS (`WordPress-Extra`), PHPStan Level 5, `.distignore`, dan 4 skrip verifikasi di `bin/`.
4. [x] Menjalankan `composer install`, merekam baseline PHPStan (`17 errors`), menjalankan ringkasan PHPCS (`405 errors, 209 warnings`), pemindai keamanan (`0 errors, 1 warning`), audit CSS (`PASS`), dan verifikasi distribusi (`PASS`).
5. [x] Menyusun laporan kesenjangan lengkap dan peta jalan Fase 00–09 di `docs/RETROFIT-GAPS.md` dan `docs/PROGRESS.md`.

---

## 4. Kriteria Penerimaan & Verifikasi (Verification Checklist)
- [x] `composer lint:syntax` — Lulus (`0 syntax error` di seluruh 62 file PHP).
- [x] `composer security:scan` — Berjalan akurat (`0 Error`, `1 Peringatan` audit `$_GET`; 100% file PHP memiliki `ABSPATH` guard).
- [x] `composer analyse` — Lulus dengan `phpstan-baseline.neon` (`17 errors` tercatat untuk dibereskan di Fase 05).
- [x] `composer css:check` — Lulus (`css/front.css` 63.814 B raw / 11.561 B gzip, `css/admin.css` 1.637 B raw / 607 B gzip; kurung kurawal seimbang; aturan tunggal `@layer components` terpenuhi).
- [x] `composer dist:check` — Lulus (`84 file`, `1.3M`, bebas dari `docs/`, `scss/`, `bin/`, `vendor/`, `node_modules/`, dan file `.md`).

---

## 5. Security Review — Fase 00
- ✅ **Lolos:** Tidak ada file PHP/JS runtime yang diubah pada Fase 00. Skrip `bin/security-scan.sh` dan `bin/check-dist.sh` berjalan lokal/CI tanpa menyentuh runtime WordPress.
- ⚠️ **Temuan Audit Kode Eksisting (Dijadwalkan Fase 01–03):**
  - `GAP-01` (High): Handler `?jalaversity_force_check=1` di `includes/updater.php:290` belum dilindungi nonce CSRF → dijadwalkan **Fase 01**.
  - `GAP-03` (Medium): URL `<iframe src="...">` di `page-templates/page-contact.php:75` belum memvalidasi host Google Maps → dijadwalkan **Fase 02**.
  - `GAP-05` (Medium): Penulisan DB sinkron `jalaversity_track_post_view()` di `single.php:17` → dijadwalkan **Fase 02**.
