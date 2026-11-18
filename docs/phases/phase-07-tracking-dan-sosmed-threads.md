# Fase 07 — Integrasi Tracking (`GTM`, `GA4`, `Yandex`, `Bing`, `Meta Pixel`, `TikTok Pixel`) & Sosial Media `Threads`

- **Status:** `Done`
- **Target Gap:** `GAP-14`
- **Branch:** `feat/phase-07-tracking-dan-sosmed-threads`
- **Tanggal Selesai:** `2026-11-19`

---

## 1. Tujuan & Ruang Lingkup

1. **Tab "Integrasi & Tracking" di Jalaversity Settings (`includes/settings/settings-fields.php`):**
   - Menambahkan tab `integrasi` dengan 3 bagian terstruktur:
     - **Google:** `gsc_verification` (Google Search Console), `gtm_id` (Google Tag Manager `GTM-XXXX`), `ga4_id` (Google Analytics 4 `G-XXXX`).
     - **Yandex & Bing:** `yandex_verification` (Yandex Webmaster), `yandex_metrica_id` (Yandex Metrica Counter ID), `bing_verification` (Bing Webmaster `msvalidate.01`).
     - **Meta & TikTok:** `fb_pixel_id` (Meta/Facebook Pixel ID), `fb_domain_verify` (Meta Domain Verification), `tiktok_pixel_id` (TikTok Pixel ID).
   - Menambahkan dukungan atribut `placeholder` pada renderer field `text` di `includes/settings/settings-page.php`.
2. **Mesin Ekstraksi, Validasi & Render Skrip Pelacakan (`includes/tracking.php`):**
   - `jalaversity_extract_verification_token()`: mengekstrak kode verifikasi murni walaupun admin menempelkan tag `<meta ... content="KODE" />` secara utuh.
   - `jalaversity_clean_tracking_field()`: memvalidasi format ID secara ketat dengan regex (`GTM-[A-Z0-9]+`, `(?:G|UA|AW|DC)-[A-Z0-9\-]+`, `[0-9]{5,12}`, `[0-9]{8,25}`, `[A-Z0-9]{10,30}`) baik saat disimpan di `includes/settings/settings-sanitize.php` maupun saat dirender di front-end.
   - `jalaversity_render_tracking_head()` (`wp_head` prioritas 2) & `jalaversity_render_tracking_body_open()` (`wp_body_open` prioritas 2): mencetak tag `<meta>` verifikasi dan skrip resmi GTM, GA4, Yandex Metrica, Meta Pixel, serta TikTok Pixel hanya pada halaman publik (bukan admin, feed, robots, atau Customizer preview).
3. **Dukungan Sosial Media `Threads`:**
   - Menambahkan field `social_threads` di tab **Sosial Media** (`includes/settings/settings-fields.php`).
   - Menambahkan SVG path ikon `threads`, label `Threads`, dan platform `threads` di `includes/helpers/social-helpers.php` (serta otomatis masuk ke `sameAs` JSON-LD `CollegeOrUniversity` di `includes/seo.php`).

---

## 2. File yang Diubah / Dibuat

- `includes/tracking.php` — Modul ekstraksi, validasi, dan render meta verifikasi & skrip tracking.
- `includes/settings/settings-fields.php` — Tab `integrasi` (9 field tracking/verifikasi) & field `social_threads` di tab `sosial`.
- `includes/settings/settings-page.php` — Dukungan atribut `placeholder` pada field `text`.
- `includes/settings/settings-sanitize.php` — Sanitasi & ekstraksi token/ID tracking saat form disimpan.
- `includes/helpers/social-helpers.php` — Ikon SVG, label, dan daftar platform untuk `threads`.
- `functions.php` & `bin/check-dist.sh` — Registrasi `includes/tracking.php`.
- `docs/phases/phase-07-tracking-dan-sosmed-threads.md`, `docs/PROGRESS.md`, `docs/RETROFIT-GAPS.md`.

---

## 3. Hasil Verifikasi & Security Review

- `composer lint` (`php -l` + `phpcs` `WordPress-Extra`): **0 Error, 0 Warning**
- `composer analyse` (PHPStan Level 5): **0 Error**
- `composer css:check`: **LULUS**
- `composer dist:check`: **LULUS** (`74 file`, `636 KB`)
- `composer security:scan`: **0 Error** (tidak ada injeksi skrip mentah dari input admin; seluruh ID divalidasi dengan regex whitelist ketat dan di-escape dengan `esc_attr()`, `esc_url()`, atau `esc_js()`).
