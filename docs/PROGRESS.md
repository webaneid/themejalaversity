# Progress Tracker — Retrofit Jalaversity

Dokumen ini melacak status eksekusi setiap fase retrofit theme **Jalaversity (`jalaversity`)** sesuai [`docs/SOP.md`](SOP.md).

---

## Ringkasan Status

- **Versi Theme Saat Ini:** `1.2.1` (retrofit selesai dari baseline `1.1.1` commit `c2e3112`)
- **Fase Terakhir Selesai:** **Fase 09 — Dashboard Admin Kustom Kampus (`includes/dashboard.php` — Gaya Makhadane) (`Done` — 2026-11-19)**
- **Fase Berikutnya:** **Seluruh Fase Retrofit (00–09) Selesai (`100% Done`)**

---

## Tabel Pelacakan Fase

> Satu baris per fase. Status: `Planned` → `In Progress` → `Done`.
> Jangan mulai fase baru sebelum fase sebelumnya `Done` (lihat `SOP.md`).

| Fase | Judul Fase | Dokumen Spesifikasi | Status | Tanggal Selesai |
| :--- | :--- | :--- | :--- | :--- |
| **00** | Audit Awal & Pemasangan Fondasi Standar (`AGENTS.md`, `docs/*`, Composer, PHPCS, PHPStan, `bin/*`, `.distignore`) | [`docs/phases/phase-00-fondasi.md`](phases/phase-00-fondasi.md) | **Done** | 2026-11-18 |
| **01** | Hardening Updater (CSRF Nonce & Diagnostik Error), Pembersihan 15 File Font `.otf` Tak Terpakai, & Otomasi CI/CD Rilis (`ci.yml`, `release.yml`, `.releaserc.json`) | [`docs/phases/phase-01-updater-dan-ci.md`](phases/phase-01-updater-dan-ci.md) | **Done** | 2026-11-18 |
| **02** | Remediasi Keamanan (Validasi Iframe Maps & Sanitasi Token), Perbaikan Cache-Busting `?ver=` Aset Theme, & Endpoint AJAX Post Views | [`docs/phases/phase-02-security-dan-ajax-views.md`](phases/phase-02-security-dan-ajax-views.md) | **Done** | 2026-11-18 |
| **03** | Perbaikan Bug Komponen UI (Panel Tab Kategori `news-section.php`, Prop Hero & Tag `<main>` `page-contact.php`, Sinkronisasi Ikon SVG `wa-widget.php`, & Hapus Stub Mati) | [`docs/phases/phase-03-perbaikan-komponen-ui.md`](phases/phase-03-perbaikan-komponen-ui.md) | **Done** | 2026-11-18 |
| **04** | Template Konten Kampus (`single-agenda.php` dengan Detail ACF Agenda, `single-pengumuman.php`, & Halaman `404.php`) | [`docs/phases/phase-04-template-agenda-pengumuman-404.md`](phases/phase-04-template-agenda-pengumuman-404.md) | **Done** | 2026-11-18 |
| **05** | Standarisasi Kode WPCS (`WordPress-Extra` 0 Error) & Penyelesaian Seluruh 17 Error PHPStan Level 5 (Baseline Kosong) | [`docs/phases/phase-05-standarisasi-wpcs-dan-phpstan.md`](phases/phase-05-standarisasi-wpcs-dan-phpstan.md) | **Done** | 2026-11-18 |
| **06** | Mesin SEO Hybrid Kampus (`includes/seo.php`), Schema JSON-LD (`CollegeOrUniversity`, `WebSite`, `Article`, `Event`), & Endpoint GEO `/llms.txt` | [`docs/phases/phase-06-seo-hybrid-dan-llms-txt.md`](phases/phase-06-seo-hybrid-dan-llms-txt.md) | **Done** | 2026-11-19 |
| **07** | Integrasi Tracking (`GTM`, `GA4`, `Meta Pixel`, `TikTok Pixel`, Verifikasi Webmaster) & Dukungan Sosial Media `Threads` | [`docs/phases/phase-07-tracking-dan-sosmed-threads.md`](phases/phase-07-tracking-dan-sosmed-threads.md) | **Done** | 2026-11-19 |
| **08** | Penerapan Standar `webane-pagespeed-cwv` (WebP 78, Preload LCP `wp_head`, DPR-Aware `sizes`, Preconnect `<= 4`, Async Font CSS, `content-visibility: auto`, & Aksesibilitas 100) | [`docs/phases/phase-08-optimasi-pagespeed-cwv.md`](phases/phase-08-optimasi-pagespeed-cwv.md) | **Done** | 2026-11-19 |
| **09** | Dashboard Admin Kustom Kampus (`includes/dashboard.php`: KPI Berita/Agenda/Pengumuman/Halaman, Grafik 12 Bulan SVG, Status Kampus & Pintasan Cepat) | [`docs/phases/phase-09-dashboard-admin.md`](phases/phase-09-dashboard-admin.md) | **Done** | 2026-11-19 |
