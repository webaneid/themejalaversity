# Fase 01 — Hardening Updater, Pembersihan Aset Font, & Otomasi CI/CD Rilis

**Status:** Done
**Jenis:** Berisiko (menyentuh mekanisme auto-update & pipeline rilis)
**Mulai:** 2026-11-18
**Selesai:** 2026-11-18

## Tujuan
1. Menutup celah CSRF pada fitur **Cek Update Sekarang** di `includes/updater.php`, menambahkan dukungan konstanta `JALAVERSITY_UPDATER_TOKEN` di `wp-config.php`, menyimpan dan menampilkan diagnostik penyebab error HTTP GitHub (`401`/`403`/`404`/jaringan/tanpa aset ZIP), mengosongkan cache otomatis saat opsi disimpan, serta memicu `wp_update_themes()` secara instan (`GAP-01`).
2. Menghapus 15 file font `.otf` Gontor yang tidak terpakai di `fonts/` (hanya menyisakan `fonts/Gontor-Bold.otf` yang dipakai `@font-face`) agar ukuran paket distribusi turun drastis dari `1.3 MB` menjadi `556 KB` (`GAP-02`).
3. Memasang `.releaserc.json`, `.github/workflows/ci.yml` (Quality Gates otomatis), dan memperbarui `.github/workflows/release.yml` agar menggunakan `.distignore` dan menjalankan seluruh pemeriksaan kualitas sebelum mempublikasikan ZIP rilis (`GAP-02`).

## Scope
- [x] Hardening & modernisasi `includes/updater.php` (bebas error PHPStan & WPCS).
- [x] Hapus 15 file font `.otf` tidak terpakai di `fonts/`.
- [x] Buat `.releaserc.json` dan `.github/workflows/ci.yml`.
- [x] Perbarui `.github/workflows/release.yml` agar memakai `.distignore` dan menjalankan quality gates.
- [x] Perbarui `phpstan-baseline.neon` setelah error PHPStan di `includes/updater.php` teratasi (turun dari 17 menjadi 15 error).

## Di Luar Scope
- Perubahan pada template front-end, `includes/security.php`, atau `includes/seo.php` (dijadwalkan di Fase 02–06).

## Referensi
- [`docs/decisions/adr-0002-versioning-dan-rilis.md`](../decisions/adr-0002-versioning-dan-rilis.md)
- [`docs/decisions/adr-0003-auto-update-github-private.md`](../decisions/adr-0003-auto-update-github-private.md)
- [`docs/architecture/architecture-deployment.md`](../architecture/architecture-deployment.md)

## Keputusan Kecil Selama Eksekusi
- Menambahkan hook `update_option_jalaversity_options` di `includes/updater.php` untuk otomatis menghapus transient `jalaversity_github_release` dan `jalaversity_github_error` setiap kali pengaturan disimpan, sehingga admin yang baru memasukkan/memperbaiki token GitHub langsung mendapatkan hasil segar.
- Memperketat `jalaversity_updater_auth_header()` agar hanya menyisipkan header `Authorization: token ...` pada request ke path `api.github.com/repos/webaneid/themejalaversity/` dan `github.com/webaneid/themejalaversity/` (menghapus pencocokan longgar `github-releases.githubusercontent.com` yang berisiko mengirim header Authorization ke signed URL S3/Azure milik GitHub yang justru menolak header Authorization ganda).

## Checklist Penutupan (SOP)
- [x] `composer lint` — nol pelanggaran di `includes/updater.php` (`vendor/bin/phpcs includes/updater.php` lolos 100%)
- [x] `composer analyse` — nol error baru (dan 2 error lama di `includes/updater.php` hilang; baseline turun 17 → 15)
- [x] `composer css:check` — build PostCSS/SCSS + Tailwind v3 sinkron & valid
- [x] `composer dist:check` — paket distribusi bersih (turun dari 84 file / 1.3 MB menjadi **69 file / 556 KB**)
- [x] Security Review dijalankan (`architecture-security.md`)
- [x] `PROGRESS.md` & `RETROFIT-GAPS.md` diupdate
- [x] Commit sesuai Conventional Commits

## Security Review — Fase 01
- ✅ **Lolos (`includes/updater.php`):**
  - Seluruh output dinamis di-escape (`esc_html`, `esc_url`).
  - Aksi `?jalaversity_force_check=1` dilindungi `current_user_can( 'update_themes' )` + `check_admin_referer( 'jalaversity_updater_action' )`.
  - Notice hasil pengecekan (`?jalaversity_checked=1`) dilindungi `wp_verify_nonce()` pada `jalaversity_notice_nonce`.
  - Token GitHub tidak pernah dicetak di pesan error maupun log; header `Authorization` hanya dikirim ke endpoint repo `webaneid/themejalaversity`.
- ✅ **Lolos (`.github/workflows/ci.yml` & `release.yml`):**
  - Paket ZIP rilis wajib melewati `rsync -a --exclude-from=.distignore` dan diverifikasi `composer dist:check`.

## Known Limitations
- Belum ada perubahan pada validasi sanitasi field password di `includes/settings/settings-sanitize.php` (dijadwalkan di Fase 02 bersama `GAP-03`).

## Ringkasan Hasil
- `GAP-01` dan `GAP-02` selesai penuh.
- `includes/updater.php` kini memiliki proteksi CSRF nonce penuh, dukungan konstanta `JALAVERSITY_UPDATER_TOKEN`, diagnostik error HTTP GitHub yang jelas (`401`/`403`/`404`/jaringan/tanpa ZIP), auto-clear transient saat opsi disimpan, dan pemicuan langsung `wp_update_themes()`.
- 15 file font `.otf` Gontor yang tidak dipakai telah dihapus, memangkas ukuran distribusi sebesar ~750 KB.
