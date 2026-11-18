# Lessons Learned — Jalaversity

Dokumen ini merekam temuan teknis, bug unik, dan pembelajaran penting selama pengembangan dan retrofit theme **Jalaversity** agar kesalahan serupa tidak terulang.

---

## 1. Tailwind CSS v3 Diam-Diam Menghapus Rule jika Ada Dua Blok `@layer components` (2026-06)
- **Gejala:** Sebagian rule CSS di partial baru (`_article.scss`) hilang tanpa pesan error atau warning saat `npm run build` dijalankan.
- **Penyebab:** Pada pipeline `postcss-import` + Tailwind CSS v3, apabila terdapat lebih dari satu blok `@layer components { ... }` yang terpisah setelah semua `@import` digabung, Tailwind v3 secara diam-diam mengabaikan/menghapus sebagian rule pada blok kedua, terutama rule dengan selector bersarang (`postcss-nested`).
- **Solusi & Pencegahan:**
  1. **Hanya `scss/front/_components.scss` yang boleh menggunakan wrapper `@layer components {}`.**
  2. Partial tambahan seperti `scss/front/_article.scss` wajib menulis rule CSS polos di luar `@layer` (posisinya sebelum `@tailwind utilities` di `scss/front/main.scss` sudah menjamin urutan cascade yang benar).
  3. Diuji otomatis oleh `bin/css-audit.py` (`composer css:check`) yang akan menggagalkan build jika ada file selain `_components.scss` yang mendeklarasikan `@layer components`.

## 2. Urutan `@import` Wajib di Atas `@tailwind` pada PostCSS (2026-06)
- **Gejala:** Error spesifikasi CSS atau variabel/mixin tidak dikenali saat build.
- **Penyebab:** `postcss-import` mewajibkan seluruh statement `@import` berada di baris paling atas file entry point (`scss/front/main.scss`) sebelum direktif `@tailwind base; @tailwind components; @tailwind utilities;`.
- **Solusi:** Di `scss/front/main.scss`, seluruh `@import` (`variables`, `base`, `components`, `article`, `utilities`) diletakkan di atas `@tailwind`.

## 3. Penyimpanan Form Settings Multi-Tab Wajib Merge dengan Opsi Lama (2026-06)
- **Gejala:** Menyimpan perubahan di Tab "Warna" menghapus seluruh data di Tab "Umum", "Sosial Media", dan "Tim Layanan".
- **Penyebab:** Semua tab di `includes/settings/settings-page.php` menyimpan ke satu key `wp_options` yang sama (`jalaversity_options`), sementara setiap form tab hanya mengirimkan input milik tab yang sedang aktif.
- **Solusi:** Di `jalaversity_sanitize_options()` (`includes/settings/settings-sanitize.php`), hanya key yang hadir di `$input` yang disanitasi lalu digabungkan (`array_merge( $existing, $sanitized )`) di atas nilai `get_option( 'jalaversity_options', [] )` yang sudah tersimpan.

## 4. Hindari Membungkus `dynamic_sidebar()` dengan Card Wrapper Tambahan (2026-06)
- **Gejala:** Widget bawaan WordPress di sidebar terlihat memiliki border dan padding ganda ("kotak di dalam kotak").
- **Penyebab:** `register_sidebar()` di `includes/setup.php` sudah membungkus setiap widget dengan `<section class="widget ...">`, dan `.sidebar .widget` sudah diberi gaya kartu di `_article.scss`.
- **Solusi:** Panggil `dynamic_sidebar( 'sidebar-1' )` langsung di dalam `<aside class="sidebar">` tanpa membungkusnya lagi dengan `<div class="sidebar__widget">`.

## 5. Kontrak Prop Komponen Pure-Render & Nama Ikon SVG Harus Konsisten (Audit Fase 00 — 2026-11-18)
- **Gejala:**
  1. Hero di halaman Kontak (`page-templates/page-contact.php`) tidak muncul sama sekali.
  2. Ikon tombol tutup dan ikon WhatsApp di `template-parts/footer/wa-widget.php` serta `page-contact.php` tidak ter-render.
  3. Mengklik tab kategori berita di beranda (`template-parts/content/news-section.php`) membuat daftar berita hilang.
- **Penyebab:**
  1. `page-contact.php` mengirim `'title' => get_the_title()` ke `hero-page.php`, sedangkan `hero-page.php` mengecek `$args['heading']` dan keluar (`return;`) bila kosong (`GAP-07`).
  2. Pemanggilan `jalaversity_icon_e('message-circle')` dan `'x'` tidak cocok dengan daftar key di `icon-helpers.php` (`'chat'` dan `'x-mark'`) (`GAP-08`).
  3. `news-section.php` merender tombol tab untuk setiap kategori tetapi hanya merender satu `data-tab-panel` untuk tab "Semua" (`GAP-06`).
- **Solusi:** Dijadwalkan untuk diperbaiki secara tuntas di **Fase 03**.
