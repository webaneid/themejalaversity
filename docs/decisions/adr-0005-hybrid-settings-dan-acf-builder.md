# ADR-0005: Arsitektur Hybrid — WordPress Settings API (`jalaversity_options`) + ACF Pro Flexible Content

**Status:** Accepted
**Tanggal:** 2026-11-18

## Context
Berbeda dari theme portal berita lama yang menyimpan seluruh pengaturan situs di
ACF Options Page, `jalaversity` menggunakan pendekatan hibrida:
1. **Pengaturan global situs** (Kontak, PMB, Sosial Media, Warna Brand `:root`,
   Tim Layanan WhatsApp, dan GitHub Updater Token) disimpan dalam **1 row
   `wp_options`** (`jalaversity_options`) menggunakan **WordPress Settings API
   murni** (`includes/settings/*`).
2. **Penyusunan halaman dinamis** (`page-templates/page-dynamic.php`) serta
   **metadata spesifik CPT `agenda` & `post`** menggunakan **ACF Pro** yang
   didaftarkan via kode PHP (`includes/acf/*`).

## Decision
Mempertahankan dan membakukan arsitektur hibrida ini:
1. Seluruh pengaturan global situs (termasuk penambahan Tab Tracking/Integrasi
   pada Fase 07) wajib masuk ke skema tunggal `jalaversity_settings_schema()` di
   `includes/settings/settings-fields.php` dan dibaca melalui
   `jalaversity_get_option()`.
2. Seluruh field group ACF (`group_jalaversity_page_sections`,
   `group_jalaversity_post_meta`, `group_jalaversity_agenda`) wajib didaftarkan
   secara terprogram di `includes/acf/` dengan guard
   `if ( ! function_exists( 'acf_add_local_field_group' ) ) { return; }` sehingga
   theme tidak pernah mengalami fatal error meskipun plugin ACF Pro sedang
   dinonaktifkan sementara.

## Alternatif yang Dipertimbangkan
- Memindahkan pengaturan global dari `jalaversity_options` ke ACF Options Page —
  ditolak karena melanggar kompatibilitas mundur situs pelanggan yang sudah
  berjalan dan WordPress Settings API sudah bekerja mandiri tanpa ketergantungan
  plugin.

## Konsekuensi
- Pengaturan global, warna, header, footer, dan auto-updater tetap berfungsi 100%
  bahkan tanpa plugin eksternal, sementara fleksibilitas drag-and-drop seksi
  halaman Fakultas/Prodi tetap terpenuhi lewat ACF Pro.
