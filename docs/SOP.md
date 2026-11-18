# SOP — Alur Kerja Per Fase

> Aturan PROSES (aturan teknis ada di `AGENTS.md` dan `docs/architecture/`).
> Berlaku untuk manusia maupun AI. Tiap langkah wajib selesai sebelum lanjut.

## Kenapa per fase
Satu fase = satu unit kerja yang bisa didokumentasikan, di-review, dan
di-rollback sendiri. Karena theme ini auto-update ke banyak situs pelanggan,
fase yang kecil dan jelas = risiko rilis yang kecil dan jelas.

## Klasifikasi Task (tentukan di awal)
| Jenis | Contoh | Perlakuan |
|---|---|---|
| **Kecil** | typo, CSS minor, fix 1 file | Boleh tanpa phase doc; tetap Langkah 3–4 |
| **Normal** | fitur/section baru, refactor 1 area | SOP penuh |
| **Berisiko** | ubah struktur data (CPT/field ACF/option `jalaversity_options`), updater, keamanan, apa pun yang menyentuh data pelanggan | SOP penuh + rencana ditulis & disetujui dulu + uji di situs staging/lokal dengan data mirip production |

## Alur Wajib (6 Langkah)

### 1. Rencana
- Buat `docs/phases/phase-XX-{nama}.md` dari `phase-template.md`, status `Planned`.
- Tambah baris di `docs/PROGRESS.md`.
- Keputusan teknis besar (library baru, pola baru, ubah mekanisme update) → ADR
  baru di `docs/decisions/` (nomor berikutnya).
- Area baru yang cukup besar → `docs/architecture/architecture-{area}.md`.

### 2. Eksekusi
- Branch: `feat/{nama}`, `fix/{nama}`, atau `chore/{nama}` dari `main`.
- Kerjakan HANYA scope di phase doc. Temuan baru di luar scope → catat
  (phase doc / `RETROFIT-GAPS.md`), jangan diperluas diam-diam.
- Catat keputusan kecil di bagian "Keputusan Kecil" phase doc.
- `PROGRESS.md` → `In Progress`.

### 3. Cek Otomatis + Manual
```bash
composer lint
composer analyse
composer css:check
```
- Nol pelanggaran BARU pada file yang diubah.
- **Khusus perubahan CSS / SCSS / Tailwind (`docs/architecture/architecture-css.md`):**
  1. Edit style komponen di partial `scss/front/_*.scss` atau `scss/admin/_*.scss` yang sesuai, atau gunakan utility Tailwind v3 di template PHP.
  2. Jangan mengedit `css/front.css` atau `css/admin.css` secara manual.
  3. Jalankan `npm run build` dan `composer css:check` (memverifikasi integritas hasil compile `css/front.css` dan `css/admin.css`, keseimbangan kurung kurawal, budget `< 30 KB` gzip, dan aturan tunggal `@layer components`).
- Cek manual di situs lokal: halaman yang terdampak, desktop + mobile,
  dengan `WP_DEBUG` dan `WP_DEBUG_LOG` aktif → tidak ada notice/warning baru
  di `wp-content/debug.log`.
- Yang sengaja belum ditangani → tulis di "Known Limitations" phase doc.

### 4. Security Review
- Jalankan checklist di `docs/architecture/architecture-security.md` untuk
  semua file yang diubah.
- Temuan **Critical/High** WAJIB diperbaiki sebelum lanjut.
- **Medium/Low** boleh ditunda, tapi WAJIB tercatat di `RETROFIT-GAPS.md`.

### 5. Tutup Fase & Merge
- Phase doc → `Done` + ringkasan hasil. `PROGRESS.md` → `Done`.
- Insight/bug penting → `docs/lessons-learned.md`.
- Commit pakai Conventional Commits, merge ke `main`.
- ⚠️ Merge ke `main` = rilis ke semua situs pelanggan (lihat di bawah).

### 6. Verifikasi Rilis
- Setelah CI membuat rilis: di satu situs uji, klik **Cek Update Sekarang** →
  update → pastikan theme aktif normal dan versi baru tampil.
- Baru setelah itu fase berikutnya dimulai.

## Rilis & Auto-Update (otomatis, detail di ADR-0002/0003)
```
merge ke main → CI: lint/analyse/css:check/dist:check → semantic-release baca commit
→ naikkan Version di style.css & functions.php + CHANGELOG.md + tag vX.Y.Z
→ build CSS + build ZIP bersih (pakai .distignore) → GitHub Release + asset ZIP
→ situs pelanggan cek update (tiap 12 jam) → notifikasi di wp-admin
```
- Commit `docs:`/`chore:`/`refactor:`/`style:`/`test:` TIDAK memicu rilis.
- `fix:`/`perf:` → patch (1.1.1 → 1.1.2), `feat:` → minor (1.1.1 → 1.2.0),
  `BREAKING CHANGE:` → major — gunakan hanya kalau situs pelanggan perlu
  tindakan manual setelah update.

## Pengecualian
- **Hotfix production** (situs pelanggan rusak): boleh skip Langkah 1, tapi
  Langkah 3, 4, 6 tetap wajib dan dicatat di `lessons-learned.md`.
- **Eksperimen**: boleh di branch terpisah tanpa SOP, tapi sebelum di-merge
  harus masuk ulang lewat Langkah 1.
