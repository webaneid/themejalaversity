# ADR-0004: Auto-Convert Sub-Sizes Gambar ke WebP & Standar Kualitas 78

**Status:** Accepted
**Tanggal:** 2026-11-18

## Context
`includes/helpers/image-helpers.php` di `jalaversity` menggunakan filter core
WordPress 5.8+ `image_editor_output_format` untuk mengonversi ukuran turunan
(`jalaversity-large`, `jalaversity-medium`, `jalaversity-thumbnail`,
`jalaversity-square`) dari JPEG/PNG ke WebP dengan tetap mempertahankan file
asli yang diunggah sebagai cadangan. Saat ini kualitas WebP disetel ke `80`.
Sementara itu, standar `webane-pagespeed-cwv` menetapkan kualitas WebP `78`
agar lolos audit `[image-delivery-insight]` Lighthouse Mobile tanpa penurunan
visual yang terlihat.

## Decision
1. Mempertahankan mekanisme `image_editor_output_format` di
   `includes/helpers/image-helpers.php` (dengan guard
   `wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] )`).
2. Menurunkan target kualitas WebP (`jalaversity_webp_quality`) dari `80` ke
   `78` (melalui konstanta `JALAVERSITY_WEBP_QUALITY = 78`) pada **Fase 08**
   sesuai standar `webane-pagespeed-cwv`.

## Alternatif yang Dipertimbangkan
- Mengganti file asli yang diunggah dengan WebP — ditolak untuk `jalaversity`
  karena pemilik proyek sejak awal menetapkan bahwa file asli kampus (mis.
  foto dokumentasi resolusi penuh) tetap disimpan utuh sementara seluruh
  ukuran turunan yang disajikan di front-end berformat WebP.

## Konsekuensi
- Seluruh ukuran gambar yang dipanggil template front-end (`large`, `medium`,
  `thumbnail`, `square`) disajikan sebagai WebP berkualitas `78` yang ringan
  dan lolos audit PageSpeed Insights.
