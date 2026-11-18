# Fase 09 — Dashboard Admin Kustom Kampus (`includes/dashboard.php` — Gaya Makhadane)

- **Status:** `Done`
- **Target Gap:** `GAP-16`
- **Branch:** `feat/phase-09-dashboard-admin`
- **Tanggal Selesai:** `2026-11-19`

---

## 1. Tujuan & Ruang Lingkup

Membangun halaman **Custom Admin Dashboard** (`jalaversity-dashboard`) yang mengadopsi desain eksekutif **Makhadane / Elemenane**:

1. **Halaman Dashboard Kustom (`includes/dashboard.php`):**
   - Menggantikan menu `index.php` bawaan WordPress dengan `jalaversity-dashboard` (`add_menu_page` posisi 2 + `remove_menu_page( 'index.php' )` + redirect otomatis di `load-index.php`).
   - **4 Kartu KPI Utama (`.ane-stats-grid`):**
     - **Total Visitors:** Akumulasi kunjungan dari `_jalaversity_views` beserta persentase pertumbuhan bulan ini dibanding bulan lalu.
     - **Total Articles:** Total artikel terbit beserta penambahan bulan ini (`+X this month`).
     - **Total Comments:** Total komentar disetujui beserta penambahan bulan ini.
     - **Active Authors:** Jumlah penulis aktif yang memublikasikan artikel.
   - **Content Analytics (12 Bulan Terakhir):**
     - Grafik kurva ganda (`Published Posts` & `Total Views`) selama 12 bulan terakhir menggunakan Chart.js (`wp_enqueue_script` pada halaman `toplevel_page_jalaversity-dashboard`).
   - **Grid 3 Kolom:**
     - **Popular Posts:** 5 artikel dengan views tertinggi (badge peringkat bulat, link edit, ikon mata + jumlah views, waktu relatif).
     - **Recent Posts:** 5 artikel terbaru lengkap dengan tombol **Create New Post** di header panel.
     - **Author Performance:** 5 penulis teratas dengan avatar, jumlah artikel, dan total views.
   - **Content Distribution & Goal:**
     - Statistik `Categories`, `Tags`, `Agenda Kampus`, `Pengumuman`, `Draft Posts`, `Scheduled`, serta progress bar `Content Goal This Month` (`X / 30`).
   - **Caching Transient (10 Menit):**
     - Seluruh agregasi SQL menggunakan `$wpdb->prepare()` dan disimpan di transient `jalaversity_dashboard_stats` serta `jalaversity_dashboard_chart` (otomatis di-flush pada `save_post`, `deleted_post`, `wp_insert_comment`, dan `edit_comment`).
2. **Styling Dashboard (`scss/admin/_admin-dashboard.scss` & `scss/admin/main.scss`):**
   - Mengadopsi token warna dan tata letak kartu Makhadane (`--ane-color-primary`, `--ane-color-secondary`, `--ane-color-accent`, `--ane-color-light`, `--ane-radius-lg`) tanpa mengubah desain sidebar menu admin bawaan WordPress.

---

## 2. File yang Diubah / Dibuat

- `includes/dashboard.php` — Implementasi penuh Custom Admin Dashboard bergaya Makhadane.
- `scss/admin/_admin-dashboard.scss` & `scss/admin/main.scss` — Styling `.ane-custom-dashboard`, `.ane-stats-grid`, `.ane-chart-container`, `.ane-post-list`, `.ane-author-list`, dan `.ane-breakdown-grid`.
- `css/admin.css` — Hasil kompilasi produksi via `npm run build`.
- `functions.php` & `bin/check-dist.sh` — Registrasi `includes/dashboard.php`.
- `docs/phases/phase-09-dashboard-admin.md`, `docs/PROGRESS.md`, `docs/RETROFIT-GAPS.md`.

---

## 3. Hasil Verifikasi & Security Review

- `composer lint` (`php -l` + `phpcs` `WordPress-Extra`): **0 Error, 0 Warning**
- `composer analyse` (PHPStan Level 5): **0 Error**
- `composer css:check`: **LULUS** (`css/admin.css` 9.871 B raw / **2.188 B gzip**)
- `composer dist:check`: **LULUS** (`75 file`, `684 KB`)
- `composer security:scan`: **0 Error** (seluruh query `$wpdb` dengan parameter menggunakan `$wpdb->prepare()`, seluruh output di-escape dengan `esc_html()`, `esc_attr()`, dan `esc_url()`).
