# Fase 06 — Mesin SEO Hybrid Kampus, Schema JSON-LD, & Endpoint GEO `/llms.txt`

- **Status:** `Done`
- **Target Gap:** `GAP-13`
- **Branch:** `feat/phase-06-seo-hybrid-dan-llms-txt`
- **Tanggal Selesai:** `2026-11-19`

---

## 1. Tujuan & Ruang Lingkup

Menggantikan stub 13 baris `includes/seo.php` dengan mesin **Hybrid SEO & GEO Kampus** sesuai spesifikasi [`docs/architecture/architecture-seo.md`](../architecture/architecture-seo.md):

1. **Deteksi Hybrid SEO (`jalaversity_has_seo_plugin()`):**
   - Mendeteksi plugin SEO populer (Yoast SEO, Rank Math, AIOSEO, SEOPress, The SEO Framework).
   - **Mode Mandiri** (tanpa plugin SEO): merender `<meta name="description">`, `<link rel="canonical">`, Open Graph (`og:*`), Twitter Card (`twitter:*`), dan robots directives (`wp_robots`).
   - **Mode Booster** (dengan plugin SEO): menyerahkan meta dasar ke plugin, menyuntikkan fallback cerdas ke filter Yoast (`wpseo_metadesc`, `wpseo_opengraph_desc`, `wpseo_opengraph_image`), dan tetap merender Schema JSON-LD spesifik kampus (`CollegeOrUniversity`, `EducationEvent`, `SpecialAnnouncement`, `SiteNavigationElement`, `BreadcrumbList`).
2. **Deskripsi Kontekstual (`jalaversity_get_seo_description()`):**
   - Menangani halaman beranda, singular (`post`, `page`, `agenda` dengan tanggal/lokasi ACF, `pengumuman`, halaman fleksibel dengan ekstraksi blok `hero`/`content_media`), arsip CPT, dan taksonomi.
3. **Schema JSON-LD Kampus (`jalaversity_output_json_ld()`):**
   - `@graph` terpadu mencakup `CollegeOrUniversity` (identitas, alamat, telepon, email, logo, `sameAs` sosial media), `WebSite` (`SearchAction`), `SiteNavigationElement` (menu utama), `BreadcrumbList`, `NewsArticle`/`BlogPosting` (berita), `SpecialAnnouncement` (pengumuman), dan `EducationEvent` (agenda lengkap dengan `startDate`/`endDate` ISO-8601, `eventAttendanceMode` Offline/Online/Mixed, `Place`/`VirtualLocation`, `performer` narasumber, `organizer`, dan `offers` pendaftaran).
   - Menghapus microdata HTML duplikat (`itemscope`, `itemtype`, `itemprop`) dari `template-parts/components/breadcrumb.php`.
4. **Endpoint GEO `/llms.txt` (`jalaversity_serve_llms_txt()`):**
   - Menyajikan ringkasan Markdown terstruktur di `/llms.txt` (dan `?llms_txt=1`) berisi identitas kampus, kontak, halaman utama, pengumuman terbaru, agenda kegiatan mendatang/terbaru, dan berita kampus terbaru.
   - Di-cache menggunakan transient `jalaversity_llms_txt_cache` (TTL 6 jam) dengan invalidasi otomatis pada `save_post`, `deleted_post`, dan `update_option_jalaversity_options`.
5. **Fallback Alt Gambar (`jalaversity_auto_image_alt()`):**
   - Mengisi atribut `alt` gambar attachment yang kosong dengan judul post induk atau judul attachment.

---

## 2. File yang Diubah / Dibuat

- `includes/seo.php` — Implementasi penuh mesin SEO hybrid, Schema JSON-LD `@graph`, fallback alt gambar, dan endpoint `/llms.txt`.
- `template-parts/components/breadcrumb.php` — Pembersihan microdata HTML `BreadcrumbList` agar terpusat di JSON-LD `@graph`.
- `docs/phases/phase-06-seo-hybrid-dan-llms-txt.md` — Dokumentasi fase ini.
- `docs/PROGRESS.md` & `docs/RETROFIT-GAPS.md` — Pembaruan status Fase 06 & `GAP-13`.

---

## 3. Hasil Verifikasi & Security Review

- `composer lint` (`php -l` + `phpcs` `WordPress-Extra`): **0 Error, 0 Warning**
- `composer analyse` (PHPStan Level 5): **0 Error**
- `composer css:check`: **LULUS**
- `composer dist:check`: **LULUS** (`73 file`, `616 KB`)
- `composer security:scan`: **0 Error** (output JSON-LD menggunakan `wp_json_encode( ..., JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG )`, seluruh atribut meta di-escape dengan `esc_attr()` / `esc_url()`, input `$_GET['llms_txt']` di-`wp_unslash()` + `sanitize_text_field()`).
