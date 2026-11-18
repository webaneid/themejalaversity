# Fase 02 — Remediasi Keamanan, Cache-Busting Aset, & AJAX Post Views

**Status:** Done
**Jenis:** Berisiko (menyentuh keamanan input/output, cache-busting aset, & tracking views)
**Mulai:** 2026-11-18
**Selesai:** 2026-11-18

## Tujuan
1. Memvalidasi dan mengekstrak URL embed Google Maps (`contact_maps_url`) secara aman baik saat penyimpanan di `includes/settings/settings-sanitize.php` maupun saat render `<iframe>` di `page-templates/page-contact.php` (hanya mengizinkan skema `https://` pada host Google Maps resmi), serta menambahkan sanitasi khusus tipe `'password'` (`GAP-03`).
2. Memperbaiki `jalaversity_remove_version_query()` di `includes/security.php` agar hanya menghapus parameter `?ver=` yang sama dengan versi WordPress core (`get_bloginfo( 'version' )`), sehingga aset theme (`css/front.css` dan `js/front/main.js`) tetap memiliki `?ver=JALAVERSITY_VERSION` untuk cache-busting otomatis saat rilis baru, sekaligus men-sanitasi `$_SERVER['REMOTE_ADDR']` pada pembatas login (`GAP-04`).
3. Memindahkan pencatatan jumlah pembaca (`_jalaversity_views`) dari pemanggilan langsung `jalaversity_track_post_view()` saat render GET di `single.php` menjadi endpoint AJAX non-blocking (`wp_ajax_jalaversity_track_view` / `wp_ajax_nopriv_jalaversity_track_view`) dengan verifikasi nonce, `absint()`, dan validasi `post_status === 'publish'` (`GAP-05`).

## Scope
- [x] Perbarui `includes/security.php` (selective `?ver=` stripping + sanitasi `REMOTE_ADDR` + `current_user_can('manage_options')` + lolos WPCS/PHPStan).
- [x] Perbarui `includes/helpers/options-helpers.php` & `includes/settings/settings-sanitize.php` (`jalaversity_clean_maps_embed_url()` + case `'password'` + lolos WPCS/PHPStan).
- [x] Perbarui `page-templates/page-contact.php` (gunakan `jalaversity_clean_maps_embed_url()` sebelum merender `<iframe>`).
- [x] Perbarui `single.php`, `includes/helpers/post-helpers.php`, `includes/enqueue.php`, `template-parts/content/content-single.php`, dan `js/front/main.js` untuk AJAX Post Views non-blocking.

## Di Luar Scope
- Perbaikan prop `'heading'` dan tag `<main>` di `page-contact.php` serta ikon `message-circle` (dijadwalkan di **Fase 03** agar scope tetap terpisah bersih).

## Referensi
- [`docs/architecture/architecture-security.md`](../architecture/architecture-security.md)
- [`AGENTS.md`](../../AGENTS.md) — Rules Non-Negotiable ("Tidak ada tulis ke DB di setiap request", "Aset wajib lewat wp_enqueue_style/script dengan versi = JALAVERSITY_VERSION")

## Keputusan Kecil Selama Eksekusi
- Menyediakan fungsi helper `jalaversity_clean_maps_embed_url( string $raw ): string` di `includes/helpers/options-helpers.php` yang dipakai bersama oleh `settings-sanitize.php` (saat simpan) dan `page-contact.php` (saat output) sehingga URL lama di DB yang belum disimpan ulang pun tetap tervalidasi aman.
- Menambahkan atribut `[data-jalaversity-views]` pada `template-parts/content/content-single.php` sehingga respons JSON dari AJAX view tracker dapat langsung memperbarui angka views yang ditampilkan di layar meskipun halaman disajikan dari page cache.

## Checklist Penutupan (SOP)
- [x] `composer lint` — nol pelanggaran pada seluruh 7 file PHP inti yang diubah (`vendor/bin/phpcs` 7/7 lolos 100%)
- [x] `composer analyse` — nol error baru
- [x] `composer css:check` — valid (`css/front.css` 12,0 KB gzip, `css/admin.css` 607 B gzip)
- [x] `composer dist:check` — valid (`69 file`, `560 KB`)
- [x] Security Review dijalankan (`architecture-security.md`)
- [x] `PROGRESS.md` & `RETROFIT-GAPS.md` diupdate
- [x] Commit sesuai Conventional Commits

## Security Review — Fase 02
- ✅ **Lolos (`includes/helpers/options-helpers.php`, `includes/settings/settings-sanitize.php`, `page-templates/page-contact.php`):**
  - `jalaversity_clean_maps_embed_url()` hanya mengizinkan skema `https` dan whitelist domain `google.com`, `www.google.com`, `maps.google.com`, `google.co.id`, `www.google.co.id` dengan path `/maps*`, menutup risiko penyisipan iframe domain arbitrer/phishing.
- ✅ **Lolos (`includes/security.php`):**
  - `$_SERVER['REMOTE_ADDR']` di-unslash dan di-sanitize dengan `sanitize_text_field( wp_unslash( ... ) )`.
  - Pengecekan admin bar diubah dari role `'administrator'` menjadi capability `'manage_options'`.
  - Versi WordPress core tetap disembunyikan dari URL aset tanpa merusak `?ver=JALAVERSITY_VERSION` pada aset theme.
- ✅ **Lolos (`single.php`, `includes/helpers/post-helpers.php`, `js/front/main.js`):**
  - Tidak ada lagi penulisan DB saat request GET `single.php`.
  - Endpoint `jalaversity_ajax_track_post_view()` memverifikasi nonce (`check_ajax_referer( 'jalaversity_ajax', 'nonce' )`), men-sanitasi `post_id` dengan `absint( wp_unslash( ... ) )`, dan memvalidasi `'post' === get_post_type()` + `'publish' === get_post_status()` + `! post_password_required()`.

## Known Limitations
- Perbaikan penuh WPCS untuk `page-templates/page-contact.php` (bersama perbaikan prop `'heading'` dan penghapusan `<main>` ganda) diselesaikan pada **Fase 03**.

## Ringkasan Hasil
- `GAP-03`, `GAP-04`, dan `GAP-05` selesai penuh.
