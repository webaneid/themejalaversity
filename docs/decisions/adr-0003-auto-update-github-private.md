# ADR-0003: Hardening Auto-Updater GitHub Release (`webaneid/themejalaversity`)

**Status:** Accepted
**Tanggal:** 2026-11-18

## Context
`jalaversity` sudah memiliki modul `includes/updater.php` yang terhubung ke
repositori privat `webaneid/themejalaversity` dan menyimpan token di
`jalaversity_options['github_token']` (halaman Settings dengan capability
`manage_options`). Namun audit Fase 00 menemukan beberapa kesenjangan (`GAP-01`):
1. Handler `?jalaversity_force_check=1` di `admin_init` belum dilindungi CSRF
   nonce (`wp_nonce_url()` + `check_admin_referer()`).
2. Saat `jalaversity_updater_get_release()` gagal, semua jenis error (`401`,
   `403`, `404`, gagal jaringan, atau rilis tanpa aset `.zip`) hanya disimpan
   sebagai string `'error'` tanpa rincian penyebab, sehingga menyulitkan
   diagnosis di wp-admin.
3. Belum mendukung konstanta `JALAVERSITY_UPDATER_TOKEN` di `wp-config.php`.

## Decision
Menyelaraskan `includes/updater.php` pada **Fase 01** dengan standar `sekolahane`
(Fase 38) dan `newsane` (Fase 44):
1. Prioritaskan konstanta `JALAVERSITY_UPDATER_TOKEN` di `wp-config.php` sebelum
   fallback ke `jalaversity_get_option( 'github_token' )`.
2. Lindungi tombol dan handler **Cek Update Sekarang** (`?jalaversity_force_check=1`)
   dengan `wp_nonce_url( ..., 'jalaversity_force_check' )` dan
   `check_admin_referer( 'jalaversity_force_check' )`, serta panggil
   `wp_update_themes()` secara langsung agar status update segera diperbarui.
3. Simpan alasan kegagalan spesifik di transient `jalaversity_github_error`
   (tanpa pernah mencetak nilai token) dan tampilkan di panel status Tab Update.

## Alternatif yang Dipertimbangkan
- Membiarkan handler force-check tanpa nonce — ditolak karena melanggar aturan
  non-negotiable `architecture-security.md`.

## Konsekuensi
- Tetap 100% kompatibel mundur dengan situs yang sudah menyimpan `github_token`
  di `jalaversity_options`, sekaligus bebas CSRF dan mudah didiagnosis.
