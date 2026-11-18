# Fase 08 — Penerapan Standar `webane-pagespeed-cwv` & Aksesibilitas 100

- **Status:** `Done`
- **Target Gap:** `GAP-15`
- **Branch:** `feat/phase-08-optimasi-pagespeed-cwv`
- **Tanggal Selesai:** `2026-11-19`

---

## 1. Tujuan & Ruang Lingkup

Menerapkan standar **PageSpeed Insights & Core Web Vitals (`webane-pagespeed-cwv`)** serta hierarki heading Aksesibilitas 100 pada theme Jalaversity:

1. **Optimasi Kompresi WebP (`includes/helpers/image-helpers.php`):**
   - Menyesuaikan kualitas WebP ukuran turunan dari `80` ke `78` (`jalaversity_webp_quality()`).
   - Menambahkan atribut default `decoding="async"` pada `jalaversity_get_thumbnail()`, `jalaversity_get_placeholder_image()`, dan `jalaversity_responsive_image()`.
2. **Preconnect Terpusat (`<= 4`) & Non-Blocking Google Fonts (`includes/enqueue.php`):**
   - Memindahkan `<link rel="preconnect">` Google Fonts (`https://fonts.googleapis.com` dan `https://fonts.gstatic.com` dengan `crossorigin="anonymous"`) ke filter `wp_resource_hints` (`jalaversity_resource_hints()`).
   - Melepas dependensi `jalaversity-fonts` dari `jalaversity-style` agar `css/front.css` tidak terblokir oleh request Google Fonts.
   - Memuat stylesheet `jalaversity-fonts` secara asinkron (`media="print" onload="this.media='all'"` + fallback `<noscript>`) melalui `style_loader_tag` (`jalaversity_async_google_fonts()`).
3. **Preload Gambar LCP di `wp_head` Prioritas 1 + `fetchpriority="high"` & DPR-Aware `sizes`:**
   - Menambahkan `jalaversity_get_lcp_image_candidate()` & `jalaversity_preload_critical_assets()` di `includes/enqueue.php` untuk mencetak `<link rel="preload" as="image" href="..." imagesrcset="..." imagesizes="..." fetchpriority="high">` pada halaman beranda (`hero_image_id`), halaman dinamis (row pertama layout `hero`), dan halaman singular (`post`, `page`, `agenda`, `pengumuman`).
   - Memperbarui gambar LCP (`hero-page.php`, `content-single.php`, `content-page.php`, `content-agenda.php`, `content-pengumuman.php`) agar menggunakan `loading => false`, `fetchpriority="high"`, `decoding="async"`, dan atribut `sizes` yang sadar DPR mobile (`(max-width: 767px) 88vw, (max-width: 1023px) 480px, 560px` untuk Hero; `(max-width: 767px) 92vw, 800px` untuk Singular).
   - Menambahkan `decoding="async"` dan `sizes` yang sesuai pada gambar below-the-fold (`post-helpers.php`, `news-section.php`, `card-grid.php`, `content-media.php`, `profile-quote.php`).
4. **Render-Tree Deferral (`content-visibility: auto`) & Hierarki Heading Aksesibilitas 100:**
   - Menambahkan `content-visibility: auto` beserta `contain-intrinsic-size` pada seksi bawah fold (`.pmb-section`, `.news-section`, `.locations-section`, `.cta-section`, `.site-footer`) di `scss/front/_components.scss` dan mengompilasi ulang `css/front.css`.
   - Memperbaiki urutan heading di `template-parts/footer/site-footer.php` (menambahkan `<h2 class="screen-reader-text">` dan mengubah judul kolom footer dari `<h4>` ke `<h3>`) serta di `template-parts/content/news-section.php` (judul kartu list dari `<h4>` ke `<h3>`) agar urutan heading menurun secara sekuensial tanpa melompati level.

---

## 2. File yang Diubah / Dibuat

- `includes/helpers/image-helpers.php` — WebP quality `78` & default `decoding="async"`.
- `includes/helpers/post-helpers.php` — `decoding="async"` & DPR-aware `sizes` pada `jalaversity_card_thumbnail()`.
- `includes/enqueue.php` — `wp_resource_hints` preconnect, async Google Fonts, dan preload gambar LCP di `wp_head` prioritas 1.
- `template-parts/components/hero-page.php` — `fetchpriority="high"`, `loading => false`, `decoding="async"`, DPR-aware `sizes`.
- `template-parts/content/content-single.php`, `content-page.php`, `content-agenda.php`, `content-pengumuman.php` — `fetchpriority="high"`, `loading => false`, `decoding="async"`, DPR-aware `sizes`.
- `template-parts/content/news-section.php`, `template-parts/components/card-grid.php`, `content-media.php`, `profile-quote.php` — `decoding="async"`, `sizes`, dan perbaikan heading `<h3>` kartu berita list.
- `template-parts/footer/site-footer.php` — Heading `<h2 class="screen-reader-text">` & `<h3>` judul kolom footer.
- `scss/front/_components.scss` & `css/front.css` — `content-visibility: auto` & `contain-intrinsic-size`.
- `docs/phases/phase-08-optimasi-pagespeed-cwv.md`, `docs/PROGRESS.md`, `docs/RETROFIT-GAPS.md`.

---

## 3. Hasil Verifikasi & Security Review

- `composer lint` (`php -l` + `phpcs` `WordPress-Extra`): **0 Error, 0 Warning**
- `composer analyse` (PHPStan Level 5): **0 Error**
- `composer css:check`: **LULUS**
- `composer dist:check`: **LULUS**
- `composer security:scan`: **0 Error**
