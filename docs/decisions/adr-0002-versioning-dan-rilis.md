# ADR-0002: Versioning & Rilis Otomatis Berbasis `.distignore`

**Status:** Accepted
**Tanggal:** 2026-11-18

## Context
Sebelumnya `.github/workflows/release.yml` di `jalaversity` hanya berjalan saat
GitHub Release dipublikasikan manual, dan menggunakan perintah `rsync` dengan
daftar `--exclude` yang tidak lengkap (belum mengecualikan `docs/`, `package.json`,
`postcss.config.js`, `tailwind.config.js`, serta masih menyertakan 15 file font
`.otf` Gontor yang tidak dipakai di `fonts/`). Standar Webane (`sekolahane`,
`newsane`, `jalawarta`) memakai `ci.yml` + `release.yml` + `.distignore` +
`bin/check-dist.sh`.

`jalaversity` saat ini berada di versi `1.1.1`.

## Decision
1. **SemVer**, melanjutkan dari `1.1.1`. Tag berformat `vX.Y.Z`.
2. **Conventional Commits wajib** (`docs/conventions.md`).
3. **CI Quality Gates (`.github/workflows/ci.yml`)** berjalan otomatis di setiap
   push/PR ke `main` untuk memeriksa sintaks PHP, `security:scan`, PHPStan,
   `css:check`, dan `dist:check`.
4. **Release Workflow (`.github/workflows/release.yml`)** memvalidasi quality
   gates, menjalankan `npm ci && npm run build`, menyinkronkan versi di
   `style.css` dan `functions.php` (`JALAVERSITY_VERSION`), serta membangun ZIP
   bersih (`jalaversity.zip` dan `jalaversity-{version}.zip`) menggunakan
   `.distignore`.
5. Hanya `fonts/Gontor-Bold.otf` yang dipertahankan di `fonts/`; 15 file `.otf`
   weight lain yang tidak direferensikan oleh `@font-face` dihapus pada Fase 01
   untuk menghemat ~1 MB ukuran paket rilis.

## Alternatif yang Dipertimbangkan
- Tetap memakai daftar `--exclude` manual di `release.yml` — ditolak karena
  bukti audit menunjukkan `docs/templates/` dan file konfigurasi dev bocor ke ZIP.

## Konsekuensi
- Paket distribusi bersih, ringan (~350 KB), dan terverifikasi otomatis oleh
  `composer dist:check`.
