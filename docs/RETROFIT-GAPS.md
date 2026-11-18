# Laporan Audit Awal & Peta Kesenjangan Retrofit (`RETROFIT-GAPS.md`)

Dokumen ini merangkum temuan audit menyeluruh terhadap theme **Jalaversity (`v1.1.1`, commit awal `c2e3112`)** dibandingkan dengan standar ketat proyek referensi **Sekolahane**, **Newsane**, dan **Modernnews (Jalawarta)**, beserta roadmap eksekusi fase demi fase.

---

## 1. Ringkasan Metrik Baseline (Fase 00)

| Alat Audit | Hasil Baseline (`v1.1.1`) | Target Akhir Retrofit |
| :--- | :--- | :--- |
| **PHP Syntax (`composer lint:syntax`)** | **LULUS** (`0 Syntax Error` di seluruh file PHP pada PHP 8.x) | **0 Syntax Error** |
| **Security Scanner (`composer security:scan`)** | **0 Error, 1 Kategori Peringatan** (4 titik `$_GET` untuk diaudit; 100% file PHP sudah memiliki `ABSPATH` guard) | **0 Error, Seluruh Titik `$_GET` Terlindungi Nonce/Sanitasi** |
| **PHPStan (`composer analyse`, Level 5)** | **17 Error** (dibekukan di `phpstan-baseline.neon`) | **0 Error** (baseline kosong `ignoreErrors: []`) |
| **PHPCS (`composer lint:phpcs`, `WordPress-Extra`)** | **405 Error & 209 Warning** di 43 file PHP (574 dapat diperbaiki otomatis dengan `phpcbf`) | **0 Error, 0 Warning** |
| **CSS Pipeline Check (`composer css:check`)** | **LULUS** (`css/front.css` 63.814 B raw / **11.561 B gzip**, `css/admin.css` 1.637 B raw / **607 B gzip**; kurung kurawal seimbang; aturan tunggal `@layer components` lolos) | **LULUS** & memenuhi standar CWV |
| **Dist Package Check (`composer dist:check`)** | **LULUS** (`84 file`, `1.3 MB`; bebas `docs/`, `scss/`, `bin/`, `vendor/`, `node_modules/`, dan `.md`) | **LULUS** & ukuran turun ke `~350 KB` setelah pembersihan 15 file font `.otf` tak terpakai di Fase 01 |

---

## 2. Daftar Kesenjangan (Gaps) & Temuan Audit

### A. Keamanan, Updater & Distribusi Rilis (Prioritas Tinggi)

| ID | Keparahan | Lokasi File | Deskripsi Masalah | Target Fase |
| :--- | :--- | :--- | :--- | :--- |
| **GAP-01** | **HIGH** | `includes/updater.php:230-307` | **CSRF pada Force-Check Updater & Error HTTP Ditelan:** Handler `$_GET['jalaversity_force_check']` di `admin_init` belum dilindungi `wp_nonce_url()` / `check_admin_referer()`. Selain itu, kegagalan API GitHub (`401`/`403`/`404`/tanpa aset ZIP) hanya disimpan sebagai `'error'` tanpa rincian diagnostik seperti di Newsane/Sekolahane, serta belum mendukung konstanta `JALAVERSITY_UPDATER_TOKEN` di `wp-config.php`. | **Done (Fase 01)** |
| **GAP-02** | **HIGH** | `.github/workflows/release.yml:35-47`, `fonts/*` | **Workflow Rilis Belum Memakai `.distignore` & 15 Font `.otf` Tak Terpakai (~1 MB):** `release.yml` lama belum mengecualikan `docs/` (termasuk mockup HTML besar di `docs/templates/`), `package.json`, `postcss.config.js`, `tailwind.config.js`, dan di `fonts/` masih terdapat 15 file `.otf` Gontor yang tidak dipakai oleh `@font-face` (hanya `Gontor-Bold.otf` yang dipakai). Belum ada `.github/workflows/ci.yml` & `.releaserc.json`. | **Done (Fase 01)** |
| **GAP-03** | **MEDIUM** | `page-templates/page-contact.php:73-83`, `includes/settings/settings-sanitize.php:92-108` | **Validasi URL Embed Google Maps (`<iframe>`) & Sanitasi Field Password:** `contact_maps_url` langsung dimasukkan ke `<iframe src="...">` tanpa validasi skema `https://` / host Google Maps (dan gagal bila admin menempelkan tag `<iframe ...>` utuh). Selain itu `jalaversity_sanitize_field_value()` belum memiliki case khusus `'password'`. | **Done (Fase 02)** |
| **GAP-04** | **MEDIUM** | `includes/security.php:19-27,107-110` | **Penghapusan `?ver=` Merusak Cache-Busting Update Theme & Sanitasi IP:** `jalaversity_remove_version_query()` menghapus `?ver=` pada *semua* file CSS/JS termasuk `front.css` dan `main.js`, sehingga saat theme auto-update di situs pelanggan, browser/CDN tetap menyajikan CSS/JS versi lama. Hanya `?ver=` bawaan WP core yang boleh disembunyikan, sedangkan aset theme wajib memakai `JALAVERSITY_VERSION`. Selain itu `$_SERVER['REMOTE_ADDR']` di `jalaversity_login_transient_key()` belum di-`wp_unslash()` + `sanitize_text_field()`. | **Done (Fase 02)** |
| **GAP-05** | **MEDIUM** | `single.php:17`, `includes/helpers/post-helpers.php:49-51` | **Penulisan DB di Setiap Request GET (`jalaversity_track_post_view()`):** Memanggil `update_post_meta()` langsung saat render `single.php` melanggar aturan *Non-Negotiable* `AGENTS.md` ("Tidak ada tulis ke DB di setiap request"), tidak bekerja saat page cache aktif, dan membebani DB. Harus dipindah ke AJAX non-blocking dengan validasi `post_status === 'publish'`. | **Done (Fase 02)** |

### B. Bug Fungsional Template & Komponen (Prioritas Tinggi)

| ID | Keparahan | Lokasi File | Deskripsi Masalah | Target Fase |
| :--- | :--- | :--- | :--- | :--- |
| **GAP-06** | **HIGH** | `template-parts/content/news-section.php:95-209` | **Tab Kategori Berita di Beranda Kosong Saat Diklik:** `news-section.php` merender tombol tab untuk 6 kategori (`data-tab-target="{slug}"`), tetapi hanya merender **1** panel (`data-tab-panel="semua"`). Saat pengunjung mengklik tab kategori apa pun, `initNewsTabs()` di `js/front/main.js` menyembunyikan panel "Semua" dan area berita menjadi kosong. | **Done (Fase 03)** |
| **GAP-07** | **HIGH** | `page-templates/page-contact.php:36-42`, `header.php:22` | **Hero Laman Kontak Tidak Tampil & Duplikasi Tag `<main>`:** `page-contact.php` mengirim `'title' => get_the_title()` ke `hero-page.php`, padahal `hero-page.php` membaca `$args['heading']` dan melakukan `return;` jika kosong — akibatnya Hero di halaman Kontak hilang. Selain itu `page-contact.php` membuka `<main id="main">` di dalam `<main id="main-content">` dari `header.php`. | **Done (Fase 03)** |
| **GAP-08** | **MEDIUM** | `template-parts/footer/wa-widget.php:40,53,89`, `page-templates/page-contact.php:25`, `includes/helpers/icon-helpers.php` | **Ikon `message-circle` & `x` Hilang (Render Kosong):** `wa-widget.php` dan `page-contact.php` memanggil `jalaversity_icon_e('message-circle')` dan `jalaversity_icon_e('x')`, sedangkan di `icon-helpers.php` namanya adalah `'chat'` dan `'x-mark'`. Akibatnya tombol close popup WA dan ikon kartu WA tidak memiliki ikon SVG. | **Done (Fase 03)** |
| **GAP-09** | **MEDIUM** | `includes/setup.php:173-224`, `includes/acf/acf-post-fields.php:68-228` | **Belum Ada Template `single-agenda.php`, `single-pengumuman.php`, & `404.php`:** CPT `agenda` memiliki 10 field ACF (`tanggal_mulai`, `tanggal_selesai`, `jam_mulai`, `jam_selesai`, `tipe_lokasi`, `lokasi`, `link_online`, `narasumber`, `penyelenggara`, `link_pendaftaran`, `kuota`), namun saat dibuka di front-end hanya jatuh ke `single.php` biasa sehingga seluruh informasi detail agenda tidak tampil. Halaman `404.php` juga belum ada. | **Fase 04** |
| **GAP-10** | **LOW** | `includes/acf/acf-options.php`, `functions.php:56`, `docs/00-*.md..04-*.md` | **File Stub Kosong & Konsolidasi Dokumen Lama:** Konsolidasi dokumen lama `docs/00-*.md..04-*.md` selesai di **Fase 00**; pelepasan stub kosong `includes/acf/acf-options.php` dari `functions.php` selesai di **Fase 03**. | **Done (Fase 00 & 03)** |

### C. Kualitas Kode, SEO, Tracking, Dashboard, & PageSpeed CWV

| ID | Keparahan | Lokasi File | Deskripsi Masalah | Target Fase |
| :--- | :--- | :--- | :--- | :--- |
| **GAP-11** | **HIGH** | Root repo (`composer.json`, `phpcs.xml.dist`, `phpstan.neon.dist`, `bin/*`, `.distignore`) | **Pemasangan Tooling Audit Otomatis Standar Webane:** Composer, PHPCS (`WordPress-Extra`), PHPStan Level 5, `bin/security-scan.sh`, `bin/css-audit.py`, `bin/check-dist.sh`, `bin/lint-changed.sh`, dan `.distignore`. | **Done (Fase 00)** |
| **GAP-12** | **MEDIUM** | Seluruh 43 file PHP (`phpstan-baseline.neon`) | **17 Error PHPStan Level 5 & 405 Error / 209 Warning WPCS:** Termasuk `$item->title` (seharusnya `$item->post_title` atau properti menu item di `includes/nav-walker.php:127`), tipe argumen `esc_attr()`/`esc_html()` (`int` → `string`), tipe `container => ''` pada `wp_nav_menu()`, definisi default `$args = $args ?? []` di komponen template, serta perapian standar kode WPCS. | **Fase 05** |
| **GAP-13** | **HIGH** | `includes/seo.php` | **`includes/seo.php` Masih Kosong (Stub 13 Baris):** Belum ada mesin SEO & Schema hybrid (mode mandiri + Yoast/RankMath Booster, meta description, Open Graph, Twitter Card, Canonical, robots noindex 404/search, Schema JSON-LD `CollegeOrUniversity`, `WebSite`, `Article`, `Event` untuk CPT `agenda`, serta endpoint GEO `/llms.txt`). | **Fase 06** |
| **GAP-14** | **MEDIUM** | `includes/settings/settings-fields.php`, `includes/helpers/social-helpers.php` | **Belum Ada Modul Tracking (`GTM`, `GA4`, `Meta Pixel`, `TikTok Pixel`, Verifikasi Webmaster) & Sosmed `Threads`:** Belum tersedia tab Integrasi & Tracking di Jalaversity Settings serta dukungan sosial media Threads (`social_threads`). | **Fase 07** |
| **GAP-15** | **MEDIUM** | `includes/enqueue.php`, `includes/helpers/image-helpers.php`, `template-parts/**`, `scss/**` | **Optimasi Standar `webane-pagespeed-cwv` & Aksesibilitas 100:** Penyesuaian `WEBP_QUALITY` ke `78`, preload gambar LCP di `wp_head` prioritas 1, `fetchpriority="high"` + DPR-aware `sizes`, preconnect terpusat di `wp_resource_hints` (`<= 4`), async Google Fonts stylesheet, `content-visibility: auto` pada seksi bawah fold, dan perbaikan urutan heading aksesibilitas (`<h4>` di footer). | **Fase 08** |
| **GAP-16** | **LOW** | `includes/dashboard.php` (baru) | **Belum Ada Custom Admin Dashboard Kampus (`includes/dashboard.php`):** Ringkasan eksekutif kampus di wp-admin (KPI Berita, Agenda, Pengumuman, Halaman, grafik publikasi 12 bulan SVG, status sistem, dan pintasan cepat). | **Fase 09** |

---

## 3. Roadmap Fase Retrofit Jalaversity

| Fase | Nama Fase | Fokus Utama | Status |
| :--- | :--- | :--- | :--- |
| **Fase 00** | **Audit Awal & Pemasangan Fondasi Standar** | Pemasangan `AGENTS.md`, `CLAUDE.md`, `README.md`, `docs/*`, ADRs, Composer (PHPCS + PHPStan), `bin/*` scanners, `.distignore`, konsolidasi dokumen lama (`GAP-10`, `GAP-11`), dan pembuatan baseline. | **Done** |
| **Fase 01** | **Hardening Updater, Pembersihan Aset Font, & Otomasi CI/CD Rilis** | Perbaikan **GAP-01** & **GAP-02**: nonce CSRF + diagnostik HTTP error + instant check di `includes/updater.php`, hapus 15 file font `.otf` tak terpakai di `fonts/`, pasang `.releaserc.json`, `.github/workflows/ci.yml`, & perbarui `.github/workflows/release.yml` dengan `.distignore`. | **Done** |
| **Fase 02** | **Remediasi Keamanan, Cache-Busting Aset, & AJAX Post Views** | Perbaikan **GAP-03**, **GAP-04**, & **GAP-05**: validasi URL iframe Google Maps di `page-contact.php` & sanitasi password di `settings-sanitize.php`, perbaiki `?ver=` di `includes/security.php` agar aset theme tetap punya `JALAVERSITY_VERSION` + sanitasi IP login, dan pindahkan pencatatan view artikel ke AJAX non-blocking. | **Done** |
| **Fase 03** | **Perbaikan Bug Komponen UI (News Tabs, Hero Kontak, & Ikon SVG)** | Perbaikan **GAP-06**, **GAP-07**, **GAP-08**, & **GAP-10**: render panel per-kategori di `news-section.php`, perbaiki prop `'heading'` & hapus `<main>` ganda di `page-contact.php`, tambahkan alias/key ikon `message-circle` & `x` di `icon-helpers.php`, serta lepas stub kosong `includes/acf/acf-options.php`. | **Done** |
| **Fase 04** | **Template Konten Kampus (`single-agenda.php`, `single-pengumuman.php`, & `404.php`)** | Perbaikan **GAP-09**: buat `single-agenda.php` (menampilkan seluruh detail ACF agenda & tombol daftar), `single-pengumuman.php`, dan halaman `404.php` berdesain tema + rebuild CSS. | **Planned** |
| **Fase 05** | **Standarisasi Kode WPCS (`WordPress-Extra`) & PHPStan 0 Error** | Perbaikan **GAP-12**: selesaikan seluruh 17 error di `phpstan-baseline.neon` hingga 0 error dan rapikan seluruh file PHP hingga `composer lint` lolos dengan 0 error & 0 warning. | **Planned** |
| **Fase 06** | **Mesin SEO Hybrid Kampus, Schema JSON-LD, & Endpoint GEO `/llms.txt`** | Perbaikan **GAP-13**: implementasi penuh `includes/seo.php` (mode mandiri + Yoast/RankMath Booster, Open Graph, Schema `CollegeOrUniversity`, `WebSite`, `Article`, `Event` agenda, dan `/llms.txt`). | **Planned** |
| **Fase 07** | **Integrasi Tracking (`GTM`, `GA4`, `Pixel`, Verifikasi) & Sosial Media `Threads`** | Perbaikan **GAP-14**: modul `includes/tracking.php` + tab Integrasi di Settings Page, serta dukungan sosial media `Threads` di Settings, `social-helpers.php`, footer, dan Schema. | **Planned** |
| **Fase 08** | **Optimasi Standar `webane-pagespeed-cwv` & Aksesibilitas 100** | Perbaikan **GAP-15**: `WEBP_QUALITY` 78, preload LCP `wp_head`, `fetchpriority="high"` + DPR-aware `sizes`, preconnect `wp_resource_hints`, async font CSS, `content-visibility: auto`, & urutan heading aksesibilitas. | **Planned** |
| **Fase 09** | **Dashboard Admin Kustom Kampus (`includes/dashboard.php`)** | Perbaikan **GAP-16**: widget/panel Dashboard Admin Webane dengan KPI konten kampus, grafik 12 bulan SVG, status pengaturan, dan aksi cepat. | **Planned** |
