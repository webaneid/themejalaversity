# Changelog — Jalaversity

> Satu entri per sesi kerja. Terbaru di atas.

---

## Sesi 17 — 2026-06-17

### Yang dikerjakan:
- **Self-host font heading**, dipicu user minta cek folder `fonts/` untuk font default judul yang sudah disiapkan, sekaligus verifikasi format & cara panggilnya.
- Ketemu 16 file font "Gontor" (8 weight: Thin/ExtraLight/Light/Regular/Medium/SemiBold/Bold/ExtraBold × normal/italic) di `fonts/`, semua OpenType (`.otf`) — dicek via `file` command, format valid.
- Audit pemakaian weight: grep `font-weight` di sekitar semua `var(--font-heading)` di seluruh SCSS — **semua heading di theme pakai weight 700 (Bold)**, tidak ada yang pakai 800/lainnya. Jadi cuma `Gontor-Bold.otf` yang benar-benar perlu di-load, bukan ke-16 file.
- Cek tool konversi WOFF2 (`fonttools`/`woff2_compress`) — tidak ada di environment ini. Diputuskan pakai `.otf` langsung (valid untuk `@font-face` di semua browser modern, file-nya kecil ~52KB) daripada instal tool baru cuma untuk 1 file kecil — didokumentasikan sebagai trade-off, bisa dioptimasi ke WOFF2 nanti kalau perlu.
- Implementasi: `@font-face` baru di `scss/front/_base.scss` (`@layer base`), `--font-heading` di `_variables.scss` diganti dari `'Playfair Display'` ke `'Gontor'`, `includes/enqueue.php` Google Fonts URL dikurangi (Playfair Display dihapus, Plus Jakarta Sans tetap), `tailwind.config.js` `fontFamily.heading` disamakan (dicek dulu — utility class `font-heading` Tailwind ternyata tidak dipakai di PHP manapun, tapi tetap disamakan biar config tidak menyesatkan).
- Verifikasi end-to-end via `curl` ke server live (bukan cuma cek lokal): font file ter-serve HTTP 200 ukuran tepat 53224 bytes, `@font-face` muncul di compiled CSS yang benar-benar di-serve, link Google Fonts di halaman live sudah tidak minta Playfair Display lagi.
- `php -l` + `npm run build` — 0 error.

### Keputusan teknis:
- Path `@font-face` pakai relative URL (`../fonts/Gontor-Bold.otf`), BUKAN `get_template_directory_uri()` — file SCSS ini di-compile murni lewat PostCSS/Node, tidak ada PHP yang ikut campur saat itu, jadi cuma path relatif yang valid di sini.
- Hanya 1 dari 16 file font di-load — prinsip "pakai yang dibutuhkan, bukan yang tersedia" (sama dengan filosofi image-size/CSS-cleanup sesi-sesi sebelumnya).
- Tidak instal tool konversi WOFF2 baru untuk 1 file ~52KB — bukan berarti WOFF2 tidak penting, tapi cost (dependency baru) belum sepadan dengan benefit (selisih ukuran kecil di 1 file). Threshold ini bisa beda kalau suatu saat ada banyak font/file besar yang perlu dikonversi.

### Risiko yang diidentifikasi:
- Belum diverifikasi visual di browser (font Gontor benar-benar tampil, bukan fallback ke Georgia) — perlu hard-refresh karena cache-busting memang sengaja dimatikan (`includes/security.php`, sudah dibahas sebelumnya).
- Format OTF (bukan WOFF2) sedikit lebih besar dari optimal, tapi tidak signifikan untuk 1 file ~52KB.

### Pelajaran yang dipetik:
- Selalu cek port HTTP nyata (`curl` ke live server), bukan cuma cek file lokal/filesystem — permission file yang restriktif (`-rwx------`, owner-only) sempat jadi kekhawatiran, tapi terverifikasi tetap ter-serve normal oleh web server di environment ini.
- Audit pemakaian aktual SEBELUM memutuskan apa yang di-load (di sini: grep font-weight) konsisten jadi kebiasaan yang berulang kali terbayar sepanjang proyek ini (image sizes, CSS cleanup, dan sekarang font weight) — jangan asumsikan "load semua yang ada", cek dulu yang benar-benar dipakai.

### Next steps:
- User hard-refresh dan cek visual: judul-judul (H1-H6, card title, dst) sekarang pakai font Gontor, bukan Playfair Display lagi.
- Kalau mau optimasi lebih jauh, convert `Gontor-Bold.otf` ke WOFF2 (online converter atau install `fonttools`), ganti 1 baris `src:` di `_base.scss`.

---

## Sesi 16 — 2026-06-17

### Yang dikerjakan:
- **Styling widget sidebar bawaan WordPress**, dipicu user minta perbaikan Recent Posts/Recent Comments/Archives/Categories yang "numpuk2" (tidak rapi). User menyebut "seperti terlampir" tapi gambar tidak terkirim — dicek langsung ke database (`wp_options.widget_block` + `sidebars_widgets`) untuk tau persis apa yang sudah dipasang: user sudah men-drop 5 **block widget** Gutenberg (Search, Recent Posts, Recent Comments, Archives, Categories) ke widget area `sidebar-1` via Appearance > Widgets — bukan minta dibuatkan komponen baru.
- Fetch HTML live (`curl`) untuk lihat markup ASLI yang dirender WordPress (`wp-block-latest-posts`, `wp-block-archives`, `wp-block-categories`, `wp-block-latest-comments`, `wp-block-search`), supaya CSS ditulis berdasarkan struktur nyata, bukan tebakan.
- **Temuan struktural sebelum eksekusi CSS**: `template-parts/components/sidebar.php` membungkus `dynamic_sidebar('sidebar-1')` dengan `<div class="sidebar__widget">` SENDIRI — sementara setiap widget yang di-drop admin SUDAH otomatis dibungkus `<section class="widget">` oleh WordPress sendiri (`before_widget` di `includes/setup.php`). Kalau dibiarkan + ditambah CSS box untuk `.widget`, hasilnya box-di-dalam-box untuk SETIAP widget (border dobel) — persis "numpuk2" yang dikomplain. **Dihapus** div pembungkus itu di `sidebar.php` — tiap widget WP jadi sibling sejajar dengan blok "Artikel Terpopuler"/"Kategori" kita, bukan anak dari satu wrapper bersama.
- CSS baru di `_article.scss`:
  - `.sidebar__widget` digabung jadi satu rule dengan `.sidebar .widget` — box putih+border+radius+padding yang SAMA otomatis berlaku untuk APAPUN widget yang admin drop nanti, tidak perlu CSS baru per jenis widget.
  - `.sidebar__widget-title` digabung dengan `.sidebar .widget .wp-block-heading` — judul widget WP (mis. `<h2>Recent Posts</h2>` dari Group block) otomatis pakai gaya "card design title" (font-heading, weight 700, warna primary) + border-bottom pemisah, SAMA dengan judul "Artikel Terpopuler"/"Kategori".
  - **Satu rule generik** `.sidebar .widget_block { ul/li/a/.no-comments }` untuk SEMUA daftar widget (Recent Posts, Archives, Categories ketiganya render `<ul><li><a>` yang sama; Recent Comments juga `<ul><li>` walau ada nesting tambahan) — border-bottom antar item, bukan 4 rule terpisah per jenis widget.
  - `.sidebar .widget_search { display:none }` — widget Search yang di-drop admin disembunyikan karena REDUNDAN dengan search form kustom kita yang sudah ada di atas sidebar (dua form pencarian dalam satu sidebar = numpuk juga).
- `php -l` — 0 error. `npm run build` — 0 error. Verifikasi via `curl` ke live server: struktur HTML benar (tidak ada lagi div pembungkus ganda), semua rule CSS baru terkonfirmasi ada di compiled output.

### Keputusan teknis:
- Pakai pendekatan GENERIK (style `.widget`/`.widget_block` sekali, bukan per-jenis-widget) — kalau admin nanti drop widget LAIN (Tag Cloud, Custom HTML, dst), otomatis ikut rapi tanpa perlu sesi CSS baru. Konsisten dengan filosofi "satu css, satu desain" yang sudah dipegang sejak request sebelumnya.
- Widget Search disembunyikan via CSS (bukan minta user hapus manual) — supaya langsung rapi tanpa perlu user bolak-balik ke wp-admin; tetap dijelaskan di komentar kode bahwa lebih bersih kalau dihapus langsung dari Widgets kalau user mau.
- Recent Comments saat ini kosong ("No comments to show") karena comment form sudah dimatikan total sejak Sesi 11 — bukan bug, CSS tetap disiapkan (`.no-comments` + list kalau suatu saat ada data) untuk kedua kondisi.

### Risiko yang diidentifikasi:
- Belum diverifikasi visual asli di browser (curl cuma konfirmasi markup+CSS benar, bukan tampilan akhir).
- Kalau admin drop widget block LAIN yang markup listnya beda jauh dari pola `<ul><li><a>` (mis. Tag Cloud yang render `<a>` inline tanpa `<ul>`), rule generik ini tidak otomatis menata — perlu rule tambahan kalau itu terjadi nanti.

### Pelajaran yang dipetik:
- Saat user bilang "seperti terlampir" tapi tidak ada lampiran yang benar-benar terkirim, jangan asumsikan dari teks saja kalau ada cara untuk verifikasi langsung (di sini: cek database widget config) — lebih akurat daripada menebak dari deskripsi.
- Sebelum menambah CSS untuk elemen yang "sudah dibungkus sesuatu", cek dulu PHP-nya — komponen WP (`before_widget`) bisa sudah menyediakan wrapper yang kita tidak sadari, dan menambah wrapper kita sendiri di atasnya bikin nested box yang justru menambah masalah "numpuk2" yang ingin diperbaiki.

### Next steps:
- User cek tampilan di browser (perlu hard-refresh — `?ver=` cache-busting memang sengaja dihapus untuk keamanan, lihat `includes/security.php`, sudah pernah dibahas sebelumnya).
- Kalau mau lebih bersih, hapus widget "Search" dari Appearance > Widgets (opsional, sudah disembunyikan via CSS jadi tidak wajib).

### Tambahan sesi ini — font-size judul varian `overlay` belum ikut diubah
- Sesi sebelumnya cuma ubah `.card--post-list .card__title` ke 27px — `.card--post-overlay` tidak punya font-size sendiri sama sekali (selalu ikut default browser), jadi belum konsisten. Ditambahkan `.card--post-overlay .card__title { font-size: 27px; }` agar sama dengan varian `list`. `npm run build` — 0 error, terverifikasi keduanya sama-sama 27px di compiled CSS.

---

## Sesi 15 — 2026-06-17

### Yang dikerjakan:
- **Standardisasi ukuran gambar + auto-convert WebP**, atas permintaan user: 16:9 sebagai "golden ratio" untuk 3 ukuran (large/medium/thumbnail) + 1 ukuran square (1:1) terpisah, semua auto-crop, lalu otomatis dikonversi ke WebP kualitas 80%.
- Klarifikasi 2 keputusan via `AskUserQuestion` sebelum eksekusi: (1) file asli yang diupload **tidak** ikut dikonversi WebP (tetap JPG/PNG sebagai backup kualitas penuh, cuma 4 ukuran turunan yang jadi WebP); (2) gambar lama yang sudah ada **tidak** diregenerate sekarang (limitasi native WordPress — ukuran/konversi baru cuma berlaku untuk upload baru; kalau perlu backfill nanti, pakai `wp media regenerate --yes` via WP-CLI, bukan tooling custom).
- **3 ukuran lama dihapus total** (`jalaversity-card` 600×400, `jalaversity-hero` 1200×600, `jalaversity-thumb` 400×300 — rasio beda-beda, tidak konsisten), diganti **4 ukuran baru** di `includes/setup.php`, semua `add_image_size(..., true)` (hard-crop tengah otomatis):
  - `jalaversity-large` 1120×630 (16:9)
  - `jalaversity-medium` 800×450 (16:9)
  - `jalaversity-thumbnail` 400×225 (16:9)
  - `jalaversity-square` 400×400 (1:1)
- Konstanta `JALAVERSITY_IMAGE_SIZES` di `includes/helpers/image-helpers.php` disinkronkan, default param 4 fungsi helper diganti ke `jalaversity-medium`.
- **Auto-convert WebP** — 2 filter baru di `image-helpers.php`, keduanya WP core native (5.8+, BUKAN plugin): `image_editor_output_format` (JPEG/PNG ukuran turunan → WebP, dijaga `wp_image_editor_supports()` supaya graceful degradation di server tanpa dukungan WebP, GIF sengaja dikecualikan biar animasi tidak hilang) + `wp_image_editor_default_quality` (kualitas WebP dikunci 80, sesuai permintaan persis).
- Update semua call site lama ke ukuran baru, dipetakan sesuai konteks visual: `content-single.php`/`content-page.php` (featured image) → `jalaversity-large`; `content-card.php` varian `klasik` → `jalaversity-large`, varian `overlay` → `jalaversity-medium`, varian `list` → `jalaversity-square` (selaras dengan CSS `aspect-ratio:1` yang sudah ada di varian itu, bukan 4:3 seperti ukuran lama).
- `php -l` semua file — 0 error. Verifikasi matematika rasio via `php -r`: ketiganya persis 1.7778 (16/9), square persis 1.0. Grep akhir: 0 sisa referensi nama ukuran lama di seluruh codebase.

### Keputusan teknis:
- Pakai filter native WP core (`image_editor_output_format`/`wp_image_editor_default_quality`, WP 5.8+) — bukan plugin/library eksternal, sesuai CLAUDE.md "tidak menggunakan plugin berat". Ini SATU-SATUNYA cara resmi WP untuk auto-convert format ukuran turunan, tidak ada cara native yang lebih ringan.
- Varian `list` di `content-card.php` sengaja dipetakan ke `jalaversity-square` (bukan `jalaversity-thumbnail` yang juga 400px lebar) — karena CSS-nya SUDAH memaksa `aspect-ratio:1` (square) sejak Sesi 13; pakai source 16:9 di sana cuma akan double-crop sia-sia di browser. Pilih ukuran source yang sesuai tampilan akhir, bukan asal pakai yang "kelihatan cocok".
- `jalaversity-thumbnail` (400×225) diregister sesuai permintaan eksplisit tapi belum diwire ke tempat spesifik manapun — sama seperti varian `title`/`klasik` di Sesi 13, infrastruktur disiapkan duluan, pemakaian menyusul kalau ada kebutuhan nyata.

### Risiko yang diidentifikasi:
- Gambar yang SUDAH ada di media library tidak otomatis ikut crop/WebP baru — perlu `wp media regenerate` manual kalau user mau backfill (sudah dikonfirmasi user: tidak perlu sekarang).
- Tergantung dukungan WebP di server (GD/Imagick) — sudah dijaga `wp_image_editor_supports()`, tapi user perlu tau bahwa di hosting yang library image-nya sangat lama/terbatas, konversi WebP bisa saja tidak aktif (fallback otomatis ke JPEG/PNG asli, tidak fatal, tapi user mungkin heran kalau filenya ternyata bukan .webp).

### Pelajaran yang dipetik:
- Saat memetakan ukuran gambar baru ke variant/komponen lama, cek dulu CSS-nya (aspect-ratio yang sudah di-set) sebelum asal pilih ukuran berdasarkan nama yang "kedengarannya cocok" — source image aspect ratio idealnya match CSS display aspect ratio, supaya tidak ada double-cropping yang sia-sia.
- Registrasi resource (ukuran gambar, varian komponen) boleh dibuat lengkap sesuai spek user walau belum semua langsung dipakai — selama jelas didokumentasikan sebagai "infrastruktur siap pakai", bukan dead code yang lupa kenapa ada.

### Next steps:
- User upload beberapa gambar baru, cek hasil crop 16:9/square dan format WebP di Media Library (lihat file extension/cek Network tab browser).
- Kalau hosting tidak mendukung WebP (jarang tapi mungkin), kabari supaya bisa investigasi alternatif (jarang perlu, GD/Imagick modern hampir semua sudah support).

### Tambahan sesi ini — koreksi mapping ukuran per varian
- User mengarahkan ulang mapping eksplisit (beda dari asumsi awal "lebih prominent = ukuran lebih besar"): `content-list` → `square` (sudah benar dari awal), `content-klasik` → `thumbnail` (sebelumnya `large`), `content-overlay` → `thumbnail` (sebelumnya `medium`), single post & Page → `medium` (sebelumnya `large`).
- Update `content-card.php` (varian `overlay`/`klasik`) dan `content-single.php`/`content-page.php`. `php -l` — 0 error.
- Akibatnya `jalaversity-large` (1120×630) jadi **belum dipakai di mana pun** — tetap diregister (sesuai spek awal user), didokumentasikan jelas sebagai "belum diwire" supaya tidak dikira dead code yang terlupa.

### Tambahan sesi ini — hapus background+border kartu post (bukan dari .card)
- User: hapus `background: var(--color-surface)` dan `border: 1px solid var(--color-border)` dari `.card` karena "bikin jelek".
- **Dicek dulu blast radius** sebelum eksekusi: `.card` adalah base class BERSAMA, dipakai juga oleh `card-grid.php` (card Program Studi/Fakultas/Kampus/Fasilitas di homepage) dan `news-section.php` (card berita homepage) — bukan cuma kartu artikel. Tanya user via `AskUserQuestion` apakah scope-nya global atau cuma kartu post — user pilih **cuma kartu post**.
- Implementasi: override `background:none; border:none;` ditambahkan di `.card--post` (bukan di `.card` itu sendiri) — `.card` tetap utuh, masih dipakai card-grid.php/news-section.php apa adanya. Varian `klasik` (`.card--post-klasik`) tetap punya `background`/`border` sendiri di blok terpisah (override balik, urutan cascade sudah benar) karena masih perlu look boxed untuk kartu featured besar.
- `npm run build` — 0 error. Verifikasi compiled CSS: `.card--post{background:none;border:none}`, `.card--post-klasik` tetap punya background+border sendiri, `.card` (base) sama sekali tidak berubah.

**Pelajaran**: instruksi "hapus X dari .card" bisa berarti "dari class .card itu sendiri" ATAU "dari area yang sedang dibahas (kartu artikel)" — kalau class yang disebut adalah base class bersama yang dipakai banyak komponen lain, cek dulu siapa lagi yang pakai sebelum eksekusi, jangan asumsikan scope dari konteks obrolan terakhir saja.

### Tambahan sesi ini — `.card--post` ternyata masih kalah dari `.card`
- User lapor override-nya tidak jalan, `.card` masih menang. Dicek byte-position rule di compiled CSS (`python3` cari posisi `.card{` vs `.card--post{`) — **terbukti** `.card--post` ada LEBIH DULU di file akhir, bukan lebih belakang seperti yang diasumsikan dari urutan `@import` di `main.scss`.
- **Akar masalah**: `.card` dibungkus `@layer components` (di `_components.scss`); Tailwind memindahkan SEMUA konten `@layer components` ke titik directive `@tailwind components;` saat build (baru ditemukan sekarang, melengkapi pemahaman dari bug `@layer` ganda Sesi 13) — jadi posisi akhirnya lebih BELAKANG dari `_article.scss` yang unlayered (dari fix Sesi 13), walau `_article.scss` di-`@import` lebih dulu di source. Specificity `.card`/`.card--post` sama-sama 1 class — yang posisinya lebih belakang menang, dan itu `.card`.
- **Fix**: ganti selector override jadi `.card.card--post` (compound, 2 class → specificity lebih tinggi) — menang terlepas dari urutan posisi, tidak lagi bergantung pada cara Tailwind memindah-mindah konten `@layer`. `npm run build` — 0 error, terverifikasi `.card.card--post{background:none;border:none}` di compiled CSS, `.card`/`.card--post-klasik` tidak berubah.

**Pelajaran**: kalau ada 2 selector dengan specificity SAMA dan berasal dari source berbeda yang salah satunya pakai `@layer`, jangan asumsikan urutan akhir di compiled CSS sama dengan urutan `@import` — Tailwind me-relokasi isi `@layer` ke titik `@tailwind <nama>;`. Solusi paling robust untuk override semacam ini: naikkan specificity (compound selector), bukan mengandalkan urutan source.

### Tambahan sesi ini — evaluasi card lanjutan
- Varian `list`: `.card__title` font-size 16px → **27px**.
- Varian `overlay`: `min-height: 320px` (tinggi fix, rasio berubah-ubah tergantung lebar) diganti `aspect-ratio: 16 / 9` — sekarang rasio KONSISTEN 16:9 di lebar berapa pun (full-width di archive/index maupun kecil di grid Halaman Blog/related-posts), selaras dengan source image-nya yang juga sudah 16:9 (`jalaversity-thumbnail`, lihat §11).
- `npm run build` — 0 error. Terverifikasi di compiled CSS: `.card--post-overlay{aspect-ratio:16/9;...}`, `.card--post-list .card__title{font-size:27px}`, varian `title` (beda dari `list`) tetap 16px tidak ikut berubah.

---

## Sesi 14 — 2026-06-17

### Yang dikerjakan:
- **Sidebar default** — user minta dicek dulu sistem sidebar jalawarta sebagai referensi, lalu dibalik keputusan Sesi 11 ("full-width tanpa sidebar"): sekarang ada SATU sidebar yang sama di index/archive/tag/category/author/search dan single post.
- Cek `jalawarta/sidebar.php` — ternyata sangat sederhana (cuma `dynamic_sidebar('default-sidebar')` dibungkus `is_active_sidebar()`, semua konten diatur admin via Widgets). Cek juga `jalawarta/inc/widget.php` (`Webane_Posts_Widget`, custom `WP_Widget` OOP) — **tidak diadopsi**: tidak ada sanitasi input (`strip_tags` saja, bukan `sanitize_text_field`) dan tidak ada escaping output sama sekali, melanggar prinsip keamanan CLAUDE.md.
- **`sidebar.php`** (root, baru) — thin wrapper standar WP, panggil `template-parts/components/sidebar.php`. Ini yang dipanggil `get_sidebar()` dari `content-post-list.php` dan `content-single.php`.
- **`template-parts/components/sidebar.php`** (baru) — 3 blok hand-built (bukan widget OOP) + 1 widget area native:
  1. Search form (reuse `search-form.php`, sudah ada).
  2. "Artikel Terpopuler" — `WP_Query` urut `_jalaversity_views` DESC (meta yang sudah ada dari Sesi 11), render tiap item pakai `content-card.php` varian `title` — varian ini sebelumnya dibuat Sesi 13 tapi belum dipakai di mana pun, sekarang dapat use case nyata.
  3. "Kategori" — daftar kategori + jumlah post, hand-built (bukan `wp_list_categories()`) supaya markup/class konsisten gaya theme.
  4. `dynamic_sidebar('sidebar-1')` — widget area native yang SUDAH diregister sejak awal proyek (`includes/setup.php`) tapi belum pernah benar-benar dipakai di template manapun; sekarang punya tempat.
- **Layout 2-kolom** `.content-with-sidebar` (1 kolom di mobile, `1fr 360px` mulai breakpoint 992px) dipasang di `content-post-list.php` (bungkus loop+pagination+no-results) dan `content-single.php` (bungkus header+image+content+share+post-nav — related-posts SENGAJA di luar wrapper, butuh lebar penuh untuk grid 3 kartu).
- `npm run build` + `php -l` — 0 error. Semua class CSS baru (`.content-with-sidebar`, `.sidebar`, `.sidebar__widget`, dst) terverifikasi ada di compiled output.

### Keputusan teknis:
- Sidebar **hand-built per blok**, bukan murni `dynamic_sidebar()` bebas isi widget apa saja (beda dari jalawarta) — konsisten dengan arsitektur jalaversity yang sudah mapan: komponen theme-native untuk konten yang predictable, widget area native cuma untuk fleksibilitas TAMBAHAN di admin (bukan satu-satunya sumber konten sidebar).
- Ditemukan: `content_width = 780` yang sudah diset sejak Sesi 02-04 (`includes/setup.php`) ternyata pas dengan matematika sidebar yang baru dipasang (1200px container − 360px sidebar − 48px gap ≈ 792px, dekat 780px) — sidebar ini sebenarnya MEMENUHI niat desain awal proyek yang sempat terlewat, bukan fitur baru di luar rencana.
- Halaman Blog (`page-blog.php`), Halaman Beranda, dan Halaman Dinamis **TIDAK** dapat sidebar — scope tetap sesuai permintaan user (index/archive/tag/author/category/single saja), halaman landing/institusi tetap full-width.

### Risiko yang diidentifikasi:
- Belum diverifikasi visual di browser (breakpoint 992px, proporsi sidebar 360px, kerapatan widget Artikel Terpopuler).
- Sidebar "Artikel Terpopuler" akan kosong di situs yang baru (belum ada post dengan views tercatat) — ini behavior yang benar (`WP_Query` dengan 0 hasil tidak render section-nya), bukan bug.

### Pelajaran yang dipetik:
- Keputusan arsitektur (full-width vs sidebar) yang sudah didokumentasikan TIDAK permanen — kalau user balik pikiran, edit dokumentasinya juga (bukan cuma kode), termasuk menjelaskan KENAPA dibalik supaya sesi berikutnya tidak bingung melihat kontradiksi antar catatan.
- Saat mengevaluasi kode referensi dari theme lain (jalawarta), jangan asumsikan semua bagiannya layak diadopsi — widget OOP-nya secara teknis berfungsi tapi melanggar prinsip keamanan dasar proyek ini; bagian yang diadopsi harus dipilih, bukan diport mentah-mentah.

### Next steps:
- User cek visual di browser: breakpoint sidebar, isi "Artikel Terpopuler" (butuh beberapa post dengan views > 0 dulu), kategori list.

---

## Sesi 13 — 2026-06-17

### Yang dikerjakan:
- **Redesign card system** sesuai spek persis dari user (lampiran `content-list.php`/`content-klasik.php`/`content-overlay.php` dari jalawarta + 1 varian baru `content-judul`). User minta: "index dan archive itu sama" (sudah dikerjakan sesi sebelumnya) ditingkatkan jadi sistem card 4 varian yang bisa dipasang bebas per kebutuhan (analogi "component/helper Laravel") — prinsip hemat CSS: 1 anatomi dasar dipakai ulang, bukan style besar per varian.
- Konfirmasi mapping sebelum eksekusi via `AskUserQuestion`: varian `klasik` dan `title` SENGAJA tidak diwire ke template manapun dulu — user akan arahkan penempatannya nanti. Hanya `overlay` (post pertama) + `list` (sisanya) yang dipakai sekarang di archive/index/Halaman Blog/related-posts, sesuai contoh eksplisit dari user.
- **4 varian final** (`template-parts/content/content-card.php`, `$args['variant']`): `overlay` (gambar background full-bleed + gradient gelap, judul+meta overlay di bawah), `list` (gambar kotak ±30% lebar kiri), `klasik` (gambar stack di atas + judul+meta+excerpt di bawah — vertikal, BUKAN side-by-side seperti `classic` versi lama), `title` (tanpa gambar sama sekali, paling ringan).
- **Ekstraksi 3 helper bersama** di `post-helpers.php` (`jalaversity_card_title()`, `jalaversity_card_meta()`, `jalaversity_card_thumbnail()`) — dipakai ulang oleh semua 4 varian, bukan markup di-copy 4x. Tiap varian sekarang cuma "menyusun" helper yang sama dengan urutan/wrapper beda.
- Update call site: `content-post-list.php` (archive/index: post pertama `overlay`, sisanya `list`), `page-blog.php` (semua featured posts jadi `overlay` dalam grid), `related-posts.php` (varian lama `grid` → `overlay`).

### 🐛 Bug tooling kritis ditemukan & diperbaiki:
- Setelah redesign, 4 rule varian baru (`.card--post-overlay`, `.card--post-list`, `.card--post-klasik`, `.card--post-title`) **hilang total** dari `css/front.css` — tidak ada error build sama sekali, silent.
- Investigasi sistematis (isolasi minimal repro, test tanpa cssnano, test tanpa import file lain, test rule trivial sekalipun) membuktikan: **kalau ada DUA `@layer components { }` terpisah** di dokumen akhir (`_components.scss` punya satu, `_article.scss` ikut bungkus satu lagi), Tailwind v3 diam-diam membuang sebagian rule dari blok KEDUA — reproducible, bukan flaky, tidak terkait isi rule-nya sama sekali (rule trivial `color:red` pun ikut hilang di posisi yang sama).
- **Fix**: hapus wrapper `@layer components { }` dari `_article.scss` — biarkan jadi rule polos (urutan `@import` di `main.scss` sudah menjamin cascade yang benar: sesudah components, sebelum utilities). Setelah fix, SEMUA rule (lama + 4 varian baru) terverifikasi muncul di `css/front.css`.
- Ditambahkan peringatan eksplisit di kepala `_article.scss`, di `main.scss`, dan di `docs/02-architecture.md` §1 (CSS Strategy) — supaya partial SCSS baru di masa depan tidak mengulang kesalahan yang sama.

### Keputusan teknis:
- Helper kecil (`jalaversity_card_*()`) ditaruh di `post-helpers.php`, bukan jadi method/class — konsisten gaya functional PHP yang sudah dipakai di seluruh theme (CLAUDE.md: "Paradigma: Functional, bukan OOP").
- Varian `klasik`/`title` didefinisikan lengkap (markup+CSS) tapi SENGAJA tidak diwire ke template manapun — user yang akan arahkan. Ini beda dari "anti-premature-abstraction" biasa (yang melarang BUAT komponen tanpa use case) karena di sini use case-nya jelas ada (user yang minta), hanya PENEMPATANNYA yang belum diputuskan.

### Pelajaran yang dipetik:
- **Jangan asumsikan "tidak ada error build" = "semua rule masuk"** — postcss/Tailwind bisa silent-drop content tanpa warning. Kalau menambah CSS baru dan curiga ada yang hilang, verifikasi dengan `grep` langsung ke file compiled, jangan cuma percaya exit code build sukses.
- Saat debug masalah tooling yang aneh, isolasi sistematis (hilangkan variabel satu-satu: cssnano? file lain? konten rule? posisi dalam file?) jauh lebih cepat sampai ke akar masalah dibanding menebak-nebak dari syntax.
- Memecah markup berulang jadi helper kecil (bukan template part terpisah) cocok untuk potongan yang SELALU dipakai bersama komponen induknya (judul/meta/gambar kartu) — beda dengan komponen `get_template_part()` yang cocok untuk blok yang BISA berdiri sendiri.

### Next steps:
- User akan arahkan pemakaian varian `klasik` dan `title` di template mana.
- Verifikasi visual di browser: cek varian `overlay` (archive/index post pertama, Halaman Blog featured, related-posts) dan `list` (sisanya) — pastikan gradient overlay/proporsi gambar 30% sesuai harapan.

### Tambahan sesi ini — unified meta line (kategori - tanggal)
- User: "saya ingin kita punya function atau komponen untuk meta... untuk content-* kita akan seragamkan semua... output metanya: Kategori - Selasa, 10 Januari 2026 ... satu css, satu function, satu design."
- Dihapus 2 fungsi lama (`jalaversity_render_post_categories()` — badge multi-kategori, `jalaversity_card_meta()` — gabungan badge+tanggal terpisah), diganti **1 fungsi tunggal** `jalaversity_post_meta_line()` di `post-helpers.php`: render kategori PERTAMA saja (bukan semua badge) + tanggal format Indonesia lengkap (`get_the_date('l, j F Y')` → "Selasa, 10 Januari 2026", otomatis ikut locale situs via `date_i18n()` WP core, tidak di-hardcode) digabung jadi satu baris "Kategori - Tanggal".
- 1 class CSS baru `.post-meta-line` (+ `__category`/`__sep`/`__date`) menggantikan `.post-categories`+`.card__meta-row` yang lama — dipakai **identik** di semua 4 varian `content-card.php` DAN `content-single.php` (sebelumnya single post punya markup kategori+tanggal sendiri yang beda dari card).
- Di `content-single.php`: tanggal yang sebelumnya dobel (sekali di meta-line baru, sekali lagi di icon-row `post-meta`) dihapus dari icon-row — sisa reading-time + views saja di sana, tidak ada duplikasi info.
- Override warna untuk varian `overlay` (teks putih di atas gambar gelap) disesuaikan ke class baru (`.card--post-overlay .post-meta-line__category`, dst).
- `npm run build` + `php -l` — 0 error. Verifikasi: class lama 0 occurrence di compiled CSS, fungsi lama 0 occurrence di PHP, fungsi+class baru terkonfirmasi ada.

---

## Sesi 12 — 2026-06-16

### Yang dikerjakan:
- **Audit CSS efficiency**, dipicu pertanyaan user: "apakah kita sudah cukup menghemat css?"
- Konfirmasi build pipeline sudah efisien: `cssnano` minify aktif di production build (`NODE_ENV=production`), Tailwind JIT hanya generate utility class yang benar-benar dipakai (content-scan `./**/*.php`, `./js/**/*.js`). `front.css` 56KB minified (~10KB gzip) — wajar untuk theme dengan homepage + ACF page builder + sistem artikel lengkap.
- **Temuan penting**: TIDAK ada tool purge untuk CSS custom yang kita tulis sendiri (`@layer components`) — beda dengan utility Tailwind yang di-JIT, class custom kita SELALU ikut ke bundle final selama ada di source SCSS, terlepas dipakai atau tidak. Jadi kalau ada refactor yang menghapus PHP tapi CSS-nya lupa dihapus, itu menumpuk selamanya tanpa terdeteksi otomatis.
- Audit manual: extract semua class selector dari `_components.scss`/`_article.scss`/`_utilities.scss`/`_base.scss`, cross-check tiap satu ke seluruh file PHP+JS (termasuk cek class yang dibangun dinamis via concatenation, supaya tidak false-positive). Ketemu **16 selector benar-benar mati** — sisa scaffolding awal (Sesi 02-04) yang sudah digantikan class lebih spesifik di sesi-sesi berikutnya tapi tidak pernah dibersihkan: `.badge-tag`/`.badge-akr-a`/`.badge-akr-b` (digantikan `.card__badge--*` Sesi 09), `.btn--primary`/`.btn--sm` (digantikan `.btn--accent-solid`), `.icon-container` + 3 modifier (digantikan icon class per-komponen), `.img-about-radius` (digantikan `.content-media__img-wrap--about` Sesi 07), `.section-pb`/`.section-pt`/`.text-section-sm`/`.container--sm` (utility yang tidak pernah diadopsi), `.anim-floaty`/`.bg-gradient-primary` (komponen yang memakainya inline animation/gradient-nya sendiri, tidak lewat utility class ini).
- Hapus semua 16 selector dari `_components.scss`, `_utilities.scss`, `_base.scss`. Verifikasi: `npm run build` 0 error, semua class yang masih dipakai (`.btn--accent-solid`, `.badge`, `.badge-cat`, `.anim-fadeup`, `.card__badge`, `.img-hero-radius`, dst) tetap ada di output, semua yang dihapus benar-benar hilang.

### Keputusan teknis:
- Dead-code removal langsung dieksekusi (bukan cuma dilaporkan) karena risikonya sangat rendah — sudah diverifikasi zero-usage via grep menyeluruh (termasuk JS, termasuk pengecekan class yang dibangun dinamis), dan ada build+lint untuk memastikan tidak ada yang patah.
- Byte savings minified cuma ~51 bytes (cssnano sudah mengompres rule-rule kecil ini secara efisien) — manfaat utama bukan ukuran file, melainkan kebersihan source code (menghindari kebingungan developer mendatang yang menemukan class yang ternyata tidak terpakai).

### Pelajaran yang dipetik:
- Tailwind v3 JIT/content-scan **hanya** berlaku untuk utility class yang DIA generate sendiri — tidak memvalidasi/membersihkan CSS custom yang kita tulis manual di `@layer components`/`@layer utilities`. Asumsi sebelumnya (Sesi 09, soal `.card-grid-dark-bg` yang sempat "hilang" dari build) kemungkinan besar bukan soal purging, melainkan sequencing kerja (CSS ditulis sebelum PHP-nya, lalu re-build setelah PHP ada) — bukan bukti adanya purge tool.
- Audit dead-CSS perlu cek dinamis juga (class yang dibangun via string concatenation di PHP, mis. `'icon-list--' . $layout`), bukan cuma grep literal — kalau tidak, false-positive "dead" untuk class yang sebenarnya dipakai.
- Worth diulang periodik setelah refactor besar (sama seperti yang dilakukan eksplisit di Sesi 07 untuk file PHP yang dihapus) — kali ini CSS-nya yang luput dicek saat itu.

### Next steps:
- Tidak ada — murni cleanup, tidak mengubah perilaku/tampilan apa pun.

### Tambahan sesi ini — konsolidasi index.php/archive.php/search.php
- User: "kita mulai dari index, harusnya index dan archive itu sama." Ditemukan `index.php` masih memanggil `get_template_part('template-parts/content/content', 'archive')` (mencari file `content-archive.php` yang TIDAK PERNAH dibuat — Sesi 11 membuat `content-card.php` sebagai gantinya tapi `index.php` tidak ikut diupdate) dan masih memanggil `get_sidebar()` (tidak relevan, desain jalaversity full-width tanpa sidebar).
- Dibuat `template-parts/content/content-post-list.php` — body bersama untuk index/archive/search (judul header menyesuaikan context: `is_home()` pakai judul "Posts page"/fallback, `is_search()` pakai "Hasil pencarian: ...", selain itu `get_the_archive_title()`; loop card classic+list; pagination; fallback kosong). `index.php`, `archive.php`, `search.php` sekarang **identik** isinya (header → `content-post-list.php` → footer) — hanya beda di docblock komentar.
- Tambah filter `jalaversity_clean_archive_title()` di `post-helpers.php` — buang prefix default WP core ("Category: X" → "X"), dibangun ulang dari `single_cat_title()`/`single_tag_title()`/dst per context (locale-proof, bukan strip string yang rapuh terhadap terjemahan).
- `docs/02-architecture.md` §10 diupdate (tabel file + entri helper baru). `npm run build` + `php -l` semua file terkait — 0 error.

### Yang dikerjakan:
- **Desain artikel (post/archive/page) diadopsi dari theme referensi `jalawarta`** (`/Users/webane/sites/modernnews/wp-content/themes/jalawarta`), atas permintaan user — header/footer tetap default jalaversity, hanya desain artikelnya yang diadopsi.
- Dijalankan via plan mode penuh: 2 Explore agent paralel (analisis jalawarta: single/archive/page/ACF/CSS; audit state jalaversity: template root, komponen existing, pola ACF) → 3 pertanyaan klarifikasi ke user → tulis plan lengkap → eksekusi 6 fase.
- **Keputusan dari user** (via AskUserQuestion): (1) landing page artikel jadi template baru `page-templates/page-blog.php` ("Halaman Blog"), bukan menimpa "Halaman Beranda"; (2) comments dimatikan total, tidak ada `comment_form()` atau Facebook Comments SDK; (3) field ACF "Editor" (kredit tambahan terpisah dari Author) diport.
- **Fase A** — `includes/helpers/post-helpers.php` (reading time, view counter via plain post meta, related-posts query tags→categories→random, excerpt filter), `includes/acf/acf-post-fields.php` (field `is_featured` + `editor`, location `post_type == post` — field group terpisah dari `acf-fields.php`/`page_template`).
- **Fase B** — 4 komponen baru (`search-form.php`, `share-buttons.php`, `post-nav.php`, `related-posts.php`) + `content-card.php` (1 file, 3 varian: classic/list/grid) + `content-none.php` (lama direferensikan index.php, baru sekarang dibuat).
- **Fase C** — `single.php`, `archive.php`, `search.php`, `page.php` (root, sebelumnya tidak ada sama sekali) + `content-single.php`, `content-page.php`.
- **Fase D** — `page-templates/page-blog.php` ("Halaman Blog"): featured posts (`is_featured`) + latest posts paginated, reuse `hero-page.php` + `pagination.php` (`$args['query']`) yang sudah ada.
- **Fase E** — `scss/front/_article.scss` (partial baru, terpisah dari `_components.scss`): typography `.entry-content`, post-meta/byline/categories, 3 varian card, share-buttons, post-nav, search-form, no-results. Ditambah `initArabicParagraphs()` + `initCopyLink()` di `js/front/main.js`. Icon `eye` baru di `icon-helpers.php` (untuk view-count).
- **Fase F** — `docs/02-architecture.md` §10 baru, `docs/01-planning.md` checklist Fase 3 selesai, entri ini.
- `php -l` semua file baru/diubah — 0 error. `npm run build` — 0 error. Sanity-check ACF: 131 key unik di 2 field group, 0 duplikat.

### Keputusan teknis:
- **2 jenis komponen, beda aturan**: komponen ACF page-builder (§8) = pure-`$args`, tidak boleh baca WP loop. Komponen post/archive/page (§10, BARU) = loop-context, baca `get_the_*()` langsung — idiom WordPress standar, bukan pelanggaran §8 (use-case beda: data arbitrary admin vs. WP loop asli).
- `content-card.php` 1 file 3 varian (bukan 3 file seperti jalawarta) — konsisten aturan "beda jadi parameter, bukan file baru" (§8), termasuk untuk komponen loop-context.
- View-count **bukan** field ACF — counter terprogram (auto-increment tiap page-load), bukan konten yang diedit admin lewat form, jadi plain `update_post_meta()` adalah tool yang tepat. "Pakai ACF untuk custom meta" ≠ "pakai ACF untuk semua meta tanpa terkecuali".
- Background dark+girih untuk `card_grid` varian `dark` (Sesi 09) jadi referensi pola yang DIULANG di sini: `.card--post-classic { grid-column: 1/-1 }` di dalam `.post-cards-grid` agar kartu featured besar tidak ikut grid-sizing kartu kecil di sebelahnya.
- Class CSS `.content-none` di-rename jadi `.no-results` — collide dengan utility Tailwind bawaan `content-none` (CSS `content` property). Tidak ada bug visual (properti `content` inert di non-pseudo-element) tapi tetap di-rename untuk hindari kebingungan saat inspect devtools.
- Arabic-paragraph detection via **JS** (regex Unicode range Arab di `initArabicParagraphs()`), bukan PHP regex pada HTML — manipulasi tag HTML via regex PHP rapuh; deteksi di DOM (sudah ter-parse browser) lebih aman. Tidak menambah Google Font baru untuk Arab — pakai `font-family: serif` generik, browser fallback otomatis ke font sistem yang punya glyph Arab.

### Risiko yang diidentifikasi:
- Belum diverifikasi end-to-end di browser (user perlu buat post test + halaman Halaman Blog, bandingkan visual dengan jalawarta untuk bagian yang diadopsi).
- View-count tidak ada dedup per session/IP — bot/crawler bisa menggelembungkan angka. Diterima sebagai simplifikasi sengaja, didokumentasikan di §10, bisa diperketat nanti tanpa ubah signature fungsi.

### Pelajaran yang dipetik:
- Saat porting desain dari codebase lain, audit dulu MANA yang benar-benar "desain" vs MANA yang "fitur fungsional/monetisasi spesifik" sebelum mulai — banyak hal di jalawarta (FB Comments, ad injection, content reorder, lightbox) ternyata bukan bagian dari "desain artikel" yang diminta, melainkan fitur news-site yang tidak relevan. Memisahkan ini di awal (sebelum coding) mencegah over-porting.
- Class CSS custom bisa collide dengan utility class bawaan Tailwind tanpa disadari (`.content-none`) — perlu dicek terutama untuk nama-nama umum/generik; nama yang lebih spesifik (`.no-results`) lebih aman.
- Reuse `breadcrumb.php`/`pagination.php` yang sudah dibangun jauh sebelumnya (Sesi 05-06) langsung jalan tanpa modifikasi sama sekali untuk kebutuhan baru ini — bukti bahwa investasi pure-render/auto-context-aware component di awal proyek terus terbayar di sesi-sesi berikutnya.

### Next steps:
- User membuat 1 post test (featured image, beberapa paragraf — termasuk yang mengandung teks Arab untuk uji `arabic-paragraph` — tandai "Berita Utama", pilih Editor), cek single post, cek archive kategori, buat halaman ber-template "Halaman Blog", bandingkan dengan jalawarta untuk hal-hal yang diadopsi.
- Pertimbangkan assign halaman "Halaman Blog" sebagai "Posts page" di Settings → Reading kalau memang mau jadi landing artikel utama situs.

---

## Sesi 10 — 2026-06-16

### Yang dikerjakan:
- **Fase 4 — Settings Page**, dipicu user: "Lanjut Fase 4 — Settings Page".
- Audit menyeluruh: grep semua pemanggilan `jalaversity_get_option()` di seluruh codebase untuk menemukan field yang BENAR-BENAR punya konsumen (bukan menebak dari rencana awal CLAUDE.md) — ketemu 85 key unik di 4 kelompok: Umum (kontak+PMB), Beranda (hero/tentang/statistik/fakultas/riset/lokasi/cta), Sosial Media (8 platform), Warna (6 brand color).
- `docs/04-settings-schema.md` ditulis dulu (sebelum kode), mendaftar semua 85 field + alasan field-field dari rencana awal (SEO/Performance/Typography/Advanced/General logo-favicon) **sengaja tidak dibuat** karena belum ada kode yang membacanya (logo/favicon sudah ditangani WP core via `custom-logo` + Site Icon).
- `includes/settings/settings-fields.php` — `jalaversity_settings_schema()` sebagai satu sumber kebenaran (tab → section → field), di-loop untuk registrasi `add_settings_section`/`add_settings_field` — bukan 85 fungsi callback individual.
- `includes/settings/settings-page.php` — admin menu + render halaman ber-tab pakai WP Settings API murni (`do_settings_sections()` → native `<table class="form-table">`), 1 fungsi render generik `jalaversity_render_settings_field()` yang switch berdasarkan `type` (text/textarea/url/email/tel/color/image).
- `includes/settings/settings-sanitize.php` — 1 callback `jalaversity_sanitize_options()` yang sanitasi per-type lalu **merge ke option lama** — wajib karena tiap tab cuma submit field miliknya sendiri, tanpa merge submit Tab A akan menghapus data Tab B/C/D.
- `js/admin/admin.js` — init `wpColorPicker()` untuk field color, `wp.media()` uploader untuk field image (pilih/ganti/hapus gambar, preview thumbnail).
- `includes/enqueue.php` — `jalaversity_admin_enqueue_scripts()` ditambah `wp_enqueue_media()`, `wp-color-picker` style+script dependency, string `mediaTitle`/`mediaButton` di-localize.
- `scss/admin/_admin-base.scss` — **ditulis ulang total**: CSS lama (Sesi 02-04) berisi class speculative (`.jalaversity-settings__header`, `__nav`, `__tab`, `__section`, `.jalaversity-color-row`, `.jalaversity-form-row`, `.jalaversity-toggle`) untuk markup custom yang TIDAK PERNAH dipakai — diganti CSS polish ringan di atas markup native WP (`.nav-tab-wrapper`, `.form-table`) + style untuk 2 custom field type (color, image).
- Sanity check via `php -r` (stub fungsi WP): schema menghasilkan 85 field, 0 duplikat key.
- `php -l` semua file baru/diubah — 0 error. `npm run build` — 0 error.

### Keputusan teknis:
- WP Settings API native (bukan markup custom) — sesuai CLAUDE.md "Settings API: Murni WordPress Settings API, bukan custom table". Ini berarti CSS lama yang sudah ditulis sejak Sesi 02-04 (sebelum settings-page.php diimplementasi) ternyata salah asumsi struktur HTML-nya — pelajaran: jangan tulis CSS untuk markup yang belum ada PHP-nya.
- Skema field generik (array config + 1 render function) dipilih daripada 85 fungsi callback individual — DRY, dan satu-satunya cara realistis menangani field sebanyak ini tanpa duplikasi masif.
- Field "General" (nama site/tagline/logo/favicon) dari rencana awal CLAUDE.md **tidak didobel** — WordPress core sudah punya UI native untuk semua itu (Settings → General, Customize → Site Identity/custom-logo, Site Icon).

### Risiko yang diidentifikasi:
- Belum diverifikasi end-to-end di wp-admin (isi semua tab, simpan per-tab, pastikan tab lain tidak ter-wipe, cek perubahan tampil di front-end).
- SEO/Performance/Typography/Advanced tab tidak ada — kalau user ekspektasi semua section CLAUDE.md awal langsung ada, perlu diluruskan bahwa itu menyusul saat fase terkait dikerjakan.

### Pelajaran yang dipetik:
- CSS yang ditulis jauh sebelum PHP-nya (speculative scaffolding) berisiko salah total begitu struktur HTML asli ditentukan — lebih aman menulis CSS setelah markup nyata ada, bukan sebelumnya, kalau strukturnya belum pasti.
- Audit "siapa konsumen field ini" (grep `jalaversity_get_option`) jauh lebih akurat sebagai sumber skema settings dibanding mengikuti tabel rencana generik di dokumen awal proyek — rencana awal bisa berubah seiring implementasi (mis. ACF page builder mengambil alih sebagian peran "settings" untuk konten halaman).

### Next steps:
- User login wp-admin → menu "Jalaversity" → isi field di tiap tab, simpan satu-satu, verifikasi: (a) tab lain tidak hilang datanya, (b) color picker & image uploader berfungsi, (c) perubahan warna langsung tampil di front-end tanpa rebuild.
- Field SEO/Performance/Typography/Advanced ditambah belakangan saat fase masing-masing benar-benar dikerjakan (bukan sekarang).

---

## Sesi 09 — 2026-06-16

### Yang dikerjakan:
- **Audit kecocokan halaman Fakultas**: bandingkan 8 layout ACF Sesi 08 dengan `docs/templates/Fakultas Tarbiyah.dc.html` section-by-section. Hasil: Stats Bar, Fasilitas (card_grid foto), dan CTA sudah cocok 100%; Hero butuh tombol CTA (bukan search); Sub Nav, Sambutan Dekan, Program Studi (field tambahan), Keunggulan (varian dark), dan Kompetensi & Karier (komposit 2 kolom) belum ada.
- User memutuskan: Hero cukup di-enhance jadi setting (buttons repeater maks 2 + show_search tetap toggle dinamis seperti sekarang), sisanya ditambah sebagai section/layout baru.
- **Enhancement komponen existing**:
  - `hero-page.php` — `$args['buttons']` (maks 2, `array_slice`), render `.hero-page__buttons` setelah lead, sebelum search/trust.
  - `card-grid.php` — item fields baru `code`/`badge`/`badge_variant`/`meta` (untuk kartu Prodi: kode, pill akreditasi, baris Jenjang/Gelar); `$args['dark']` untuk varian glass-card di atas background gelap (untuk Keunggulan).
  - `section-header.php` — `$args['dark']`, meneruskan ke `jalaversity_section_label()` (param `$dark` sudah ada sejak awal tapi belum pernah dipakai — gap lama).
  - `icon-list.php` — varian layout baru `'plain'` (satu kolom tanpa card chrome, untuk checklist sederhana).
- **3 komponen baru** di `template-parts/components/`: `sub-nav.php` (anchor jump-link), `profile-quote.php` (foto bulat + nama + quote box, untuk sambutan Dekan/Rektor — generik, tidak menyebut "Dekan" di markup), `checklist-cards.php` (2-kolom: checklist `icon-list` kiri + `card-grid` kanan — murni komposisi ulang, nol CSS baru untuk kartu kanan).
- **3 layout ACF baru** + 2 layout existing diperluas di `includes/acf/acf-fields.php`; render bridge diperluas di `includes/acf/acf-render.php` (3 fungsi render baru + mapping field baru di hero & card_grid).
- CSS baru di `_components.scss`: `.hero-page__buttons`, `.sub-nav`, `.profile-quote`, `.card__code`/`.card__badge`/`.card__meta`, `.card--grid-dark`, `.card-grid-dark-bg`, `.icon-list--plain`, `.checklist-cards`. `npm run build` — 0 error.
- `php -l` semua file baru/diubah — 0 syntax error.

### Keputusan teknis:
- Background dark+girih untuk varian `card_grid` dipasang di **wrapping `<section>` oleh render bridge**, bukan di dalam `card-grid.php` sendiri — supaya gradient full-bleed (edge-to-edge) sesuai mockup, bukan inset rounded-card di dalam `.container`. Sempat salah desain (taruh di div dalam) sebelum dikoreksi.
- `checklist-cards.php` murni komposisi `get_template_part()` ke `icon-list.php` dan `card-grid.php` yang sudah ada, bukan menulis ulang styling kartu — konsisten dengan aturan §8 "jangan duplikasi komponen yang sudah ada".
- Trade-off diterima: kartu "Prospek Karier" di `checklist_cards` pakai ukuran/styling `card-grid.php` apa adanya (lebih besar dari mockup aslinya) — demi reuse, bukan fork compact-card baru.
- Ditemukan saat verifikasi CSS: build tool (Tailwind/PostCSS content-scan) men-purge class yang belum direferensikan di PHP manapun — `.card-grid-dark-bg` baru muncul di `front.css` setelah ditulis ke `acf-render.php`. Bukan bug, hanya soal urutan kerja (tulis CSS dulu lalu PHP, build kedua kali baru lengkap).

### Risiko yang diidentifikasi:
- Belum diverifikasi end-to-end di browser (perlu user isi halaman test Fakultas via wp-admin dan bandingkan visual dengan mockup).
- Kartu "Prospek Karier" kemungkinan terlihat agak besar dibanding mockup karena reuse `card-grid.php` tanpa varian compact.

### Pelajaran yang dipetik:
- Sebelum menambah CSS baru, cek dulu apakah sudah ada (mis. `.section-label--on-dark` ternyata sudah ada dari sesi sebelumnya, hanya belum pernah dipakai — `jalaversity_section_label()` punya param `$dark` yang tidak terhubung ke manapun). Audit kecocokan desain sebaiknya juga mengecek "kode yang sudah ada tapi belum terpakai", bukan cuma "apa yang belum ada".
- Komposisi komponen pure-render di dalam komponen pure-render lain (checklist-cards.php memanggil icon-list.php + card-grid.php) bekerja baik dan tidak melanggar aturan §8 — pure-render tidak berarti "tidak boleh memanggil get_template_part()", hanya "tidak boleh memanggil jalaversity_get_option()/business-logic langsung".

### Next steps:
- User membuat halaman test "Fakultas Tarbiyah" dengan template Halaman Dinamis, isi 11 layout (termasuk Program Studi dengan field code/badge/meta, dan Keunggulan dengan toggle dark), bandingkan render dengan mockup, laporkan hasilnya.
- Halaman Fakultas lain (kalau ada multi-fakultas) tinggal duplikasi halaman + isi ulang field — tidak perlu kode baru.

---

## Sesi 08 — 2026-06-16

### Yang dikerjakan:
- **Page builder dinamis berbasis ACF Pro Flexible Content** — dipicu kritik user bahwa membuat 1 page template statis per jenis halaman tidak scalable; user mau admin WP bisa menyusun halaman dari section yang bisa di-drag-reorder, ditambah, dihapus, diedit copywriting-nya, termasuk repeater untuk section dengan jumlah item tidak tetap (Prodi/Fakultas).
- Mekanisme builder dipilih via `AskUserQuestion` (3 opsi: native Gutenberg block, ACF Pro Flexible Content, plugin repeater gratis) — user pilih **ACF Pro Flexible Content**, plugin sudah dimiliki & diinstall sendiri oleh owner.
- File baru:
  - `includes/acf/acf-fields.php` — field group "Page Sections" (`acf_add_local_field_group()`, di-hook ke `acf/init`), 8 layout: Hero, Stats Bar, Content + Media, Card Grid, Numbered Steps, CTA Banner, PMB Section, News & Pengumuman.
  - `includes/acf/acf-render.php` — dispatcher `jalaversity_render_dynamic_section()` + 1 fungsi render per layout, baca `get_sub_field()` → susun `$args` → panggil komponen generik Sesi 07 yang sama persis dengan `page-home.php`.
  - `page-templates/page-dynamic.php` — "Template Name: Halaman Dinamis", isinya cuma loop `have_rows('page_sections')`.
- Fix pure-render (ditemukan saat integrasi, lolos dari refactor Sesi 07): `stats-bar.php` dan `pmb-section.php` masih hardcode panggil `jalaversity_get_option()`/`jalaversity_get_stats()`/`jalaversity_get_pmb_steps()` — diperbaiki jadi `$args['x'] ?? jalaversity_get_option(...)` (pola sama dengan `cta-banner.php`), fallback dipertahankan supaya `page-home.php` tidak rusak.
- Enhancement `numbered-steps.php` — dukungan header opsional (`label`/`heading`/`lead` via `section-header.php`) agar bisa dipakai berdiri sendiri sebagai layout ACF, tidak hanya sebagai bagian dari `pmb-section.php`.
- `functions.php` — daftarkan 2 include baru di `$jalaversity_includes`.
- `php -l` di semua file baru/diubah — 0 syntax error.

### Keputusan teknis:
- Field group didaftarkan via kode (bukan UI admin ACF + export JSON) supaya skema tetap version-controlled di git.
- Field "link" (label/url/external) pakai ACF `group`, bukan native ACF link field — shape native (`title`/`url`/`target`) tidak match kontrak komponen existing.
- `card_grid` dan `numbered_steps` dibungkus `<section class="section-py"><div class="container">` di render bridge (komponennya sendiri tidak include wrapper itu, beda dengan `content-media.php`/`hero-page.php` yang sudah self-contained) — konsisten dengan cara `page-home.php` membungkusnya manual.
- `page-templates/page-home.php` **tidak dihapus/diubah** — `page-dynamic.php` berdiri sendiri sebagai template baru; migrasi homepage ke ACF (jika pernah) keputusan owner, bukan otomatis dari sesi ini.

### Risiko yang diidentifikasi:
- Eksplisit melanggar aturan CLAUDE.md "tidak menggunakan plugin berat" — diterima sebagai pengecualian owner-authorized, didokumentasikan di `docs/02-architecture.md` §9.
- Belum diverifikasi end-to-end di wp-admin (field group muncul, drag-reorder, repeater Prodi) — butuh akses browser yang tidak tersedia di sesi ini.
- Layout `card_grid`/`numbered_steps` standalone tidak punya tint background khusus per section (beda dengan `faculty-section`/`locations-section` di homepage) — scope cut yang disengaja, bisa ditambah via conditional logic ACF nanti kalau dibutuhkan.

### Pelajaran yang dipetik:
- Investasi "pure render component" Sesi 07 langsung terbayar — 6 komponen generik bisa langsung jadi layout ACF tanpa ditulis ulang, hanya butuh bridge function baca `get_sub_field()`.
- Komponen yang dibuat sebelum aturan pure-render ditetapkan (Sesi 06: `stats-bar.php`, `pmb-section.php`) bisa diam-diam melanggar aturan baru kalau tidak ada audit ulang — perlu dicek manual saat ada refactor besar, bukan diasumsikan otomatis konsisten.
- ACF `get_sub_field()` pada field repeater/group di dalam flexible content row mengembalikan array data langsung (tidak perlu nested `have_rows()`) — menyederhanakan bridge function secara signifikan.

### Next steps:
- User mengisi 2-3 layout test (termasuk Card Grid dengan beberapa item Prodi) di halaman baru bertemplate "Halaman Dinamis", verifikasi render + drag-reorder di wp-admin, lapor hasilnya.
- Layout khusus halaman Fakultas (profile-card Dekan, Keunggulan, Kompetensi Karier, Fasilitas) ditunda sampai halaman Fakultas benar-benar mulai dibangun.

---

## Sesi 07 — 2026-06-15

### Yang dikerjakan:
- **Refactor total homepage sections** dari page-specific menjadi generic, data-driven components — dipicu kritik user: "kalau bikin 1 section spesifik untuk setiap halaman, dampaknya akan membuat ribuan section untuk macam-macam halaman, 1 jenis section yang sama tidak masuk akal dibuat berkali-kali."
- **6 komponen generik baru** di `template-parts/components/`:
  - `section-header.php` — label + heading + lead, dipakai `card-grid.php` dan section lain
  - `hero-page.php` — ganti `hero-home.php`; mendukung `variant='home'|'subpage'` (subpage pakai breadcrumb dark + heading lebih kecil)
  - `content-media.php` — ganti `about.php` + `research.php`; 2-kolom gambar+konten dengan `image_position`, `bg`, `image_radius`, `corner_badge` sebagai parameter
  - `card-grid.php` — ganti `faculty-grid.php` + `locations.php`; grid kartu icon/foto + judul/desc/alamat/link
  - `icon-list.php` — list icon+title+desc, layout `grid` (nilai institusi) atau `rows` (item riset)
  - `numbered-steps.php` — step bernomor untuk PMB, variant `on-dark`
- **5 file lama dihapus**: `template-parts/content/{hero-home,about,faculty-grid,research,locations}.php`
- `includes/helpers/template-helpers.php` — `jalaversity_get_faculties()` dan `jalaversity_get_campuses()` dinormalisasi ke kontrak generik card-grid (`title`, `desc`, `image_id`, `link`); lookup `faculty_{id}_image_id`/`campus_{id}_image_id` dipindah dari template ke helper; ditambah `jalaversity_get_hero_home_args()`, `jalaversity_get_about_args()`, `jalaversity_get_research_args()` yang menyusun `$args` lengkap untuk dipanggil komponen generik
- `template-parts/content/pmb-section.php` — step cards diganti pakai `numbered-steps.php` (tetap bespoke untuk bagian lain, belum ada use case kedua)
- `page-templates/page-home.php` — ditulis ulang jadi pure-composition: hanya memanggil komponen generik dengan `$args` dari helper, zero markup section spesifik
- `scss/front/_components.scss` — direstruktur total: `.hero-home` → `.hero-page`/`.hero-page--subpage`, `.about-section`+`.research-section` → `.content-media`+`.content-media--reverse`+`.content-media--bg-{cream|surface}`, `.about-value`+`.research-item` → `.icon-list--{grid|rows}`, `.faculty-grid`+`.locations-grid`+kedua `.card--faculty`/`.card--location` → `.card-grid`+`.card--grid`/`.card--grid-photo`, `.pmb-steps`+`.pmb-step` → `.numbered-steps`+`.numbered-step`; ditambah `.breadcrumb--on-dark`
- **Dead CSS dihapus** sebagai bonus cleanup: ditemukan definisi BEM-nested lama (`.card--faculty`, `.card--location`, `.card--step` dengan `&__image`/`&__icon-badge`) yang tidak pernah dipakai template manapun (template pakai class flat `.card__media`/`.card__title`), juga duplikat `.floating-badge` dan `.about-badge` — semua dihapus setelah diverifikasi via grep zero reference
- `npm run build` — 0 error setelah restrukturisasi CSS selesai
- `docs/02-architecture.md` — ditambah §8 "Generic Component Architecture" mendokumentasikan aturan pure-render-component vs. composition-file, taksonomi 6 komponen, dan file yang sengaja tetap bespoke
- `docs/01-planning.md` — checklist Sesi 06 diupdate menandai item mana yang direfactor/dihapus di Sesi 07; checklist Faculty page diupdate untuk reuse komponen generik

### Keputusan teknis:
- **Pure render component**: komponen di `template-parts/components/` tidak boleh memanggil `jalaversity_get_option()` sendiri — semua data masuk via `$args`. Data-fetching jadi tanggung jawab file komposisi atau helper, bukan komponen. Ini menegakkan aturan CLAUDE.md "jangan taruh logika bisnis di template file" yang sebelumnya dilanggar (lookup image ID per-fakultas ada di dalam `faculty-grid.php`).
- **CSS `order`, bukan `flex-direction: row-reverse`**, untuk swap posisi gambar di `content-media.php` — `order` mempertahankan urutan stacking mobile yang benar (About: gambar dulu; Research: konten dulu) tanpa reorder DOM literal, karena urutan DOM `content-media.php` selalu visual-lalu-content.
- Beberapa nilai visual digeneralisasi demi reusability: tinggi gambar About(460px)/Research(480px) → 470px; lebar minimum kartu faculty(172px height)/locations(190px) → 180px. Selisih kecil, dianggap trade-off wajar.

### Risiko yang diidentifikasi:
- Belum ada uji visual browser langsung (theme belum di-deploy ke WP aktif) — verifikasi visual masih bergantung pada kesesuaian CSS lama→baru secara manual per-property, bukan screenshot diff.
- Helper `jalaversity_get_hero_home_args()`/`get_about_args()`/`get_research_args()` baru dipakai homepage; saat halaman Fakultas dibangun, perlu helper serupa per-halaman — pola ini harus konsisten diikuti, jangan kembali ke args inline di page-template kalau args-nya kompleks.

### Pelajaran yang dipetik:
- Begitu dua section punya markup yang strukturnya identik dan hanya beda data, itu sinyal kuat untuk generic component — menunggu sampai section ketiga/keempat sebelum refactor cuma menambah hutang teknis. Kritik user di sesi ini valid: pola "1 section per halaman" tidak scale.
- Refactor CSS itu sendiri menyingkap dead code yang sudah ada sejak Sesi 06 (definisi BEM-nested yang tidak pernah dipakai) — tanda bahwa duplikasi yang dikritik user sudah mulai terjadi bahkan dalam waktu singkat, bukan cuma risiko di masa depan.

### Next steps:
- Baca `docs/templates/Fakultas Tarbiyah.dc.html` sebelum mulai membangun halaman Fakultas, untuk pastikan 6 komponen generik ini cukup atau perlu tambahan (mis. `profile-card.php` untuk Dekan/Rektor) — jangan bikin komponen baru "untuk jaga-jaga" sebelum use case-nya nyata.
- Lanjut Fase 4 — Settings Page, atau lanjut Fase 3 — halaman Fakultas (tergantung prioritas user).

---

## Sesi 06 — 2026-06-15

### Yang dikerjakan:
- `includes/helpers/template-helpers.php` — **Baru**: helper functions untuk data homepage:
  - `jalaversity_section_label(string $text, bool $center, bool $dark)` — echo label HTML
  - `jalaversity_get_stats()` — 4 item statistik dari Settings dengan fallback
  - `jalaversity_get_about_values()` — 4 nilai institusi
  - `jalaversity_get_faculties()` — 6 fakultas dengan ikon dan program studi
  - `jalaversity_get_pmb_steps()` — 4 langkah pendaftaran
  - `jalaversity_get_research_items()` — 3 item riset
  - `jalaversity_get_campuses()` — 3 kampus dari Settings
- `functions.php` — ditambahkan `template-helpers.php` ke array includes
- **6 komponen reusable dibuat:**
  - `template-parts/components/stats-bar.php` — 4-kolom floating stats, data dari jalaversity_get_stats()
  - `template-parts/components/floating-badge.php` — badge animasi floaty, args-driven (icon, label, value, class)
  - `template-parts/components/cta-banner.php` — dark green girih banner, 2 tombol, override via $args
  - `template-parts/components/breadcrumb.php` — schema.org BreadcrumbList, handle: single, page, category, archive, search, 404
  - `template-parts/components/pagination.php` — wp paginate_links() wrapper dengan ARIA
- **7 homepage section dibuat:**
  - `template-parts/content/hero-home.php` — hero: badge institusi, H1 dengan highlight gold, lead text, search form (WP native), trust badges, gambar + floating-badge
  - `template-parts/content/about.php` — 2-col: gambar custom-radius, corner badge tahun, 4 nilai institusi dengan icon-container, link-arrow
  - `template-parts/content/faculty-grid.php` — 6 card--faculty: gambar + icon overlay, nama, program studi, link ke halaman
  - `template-parts/content/pmb-section.php` — dark green girih, intro kiri + CTA kanan, 4 step cards (number, title, desc)
  - `template-parts/content/news-section.php` — tab filter JS (data-tab-target), featured card + 3 list cards (WP_Query), pengumuman + agenda (query category + meta _event_time)
  - `template-parts/content/research.php` — konten kiri (3 research-item), visual kanan (img + research-badge sudut)
  - `template-parts/content/locations.php` — 3 card--location: img, nama, deskripsi, alamat, link peta
- `page-templates/page-home.php` — **Baru**: WordPress page template "Halaman Beranda", assembles semua sections dalam urutan: Hero → Stats → About → Faculty → PMB → News → Research → Locations → CTA
- `page-templates/` directory — dibuat
- `scss/front/_components.scss` — ~700 baris CSS baru: seluruh komponen homepage ditambahkan dalam `@layer components {}` block

### Keputusan teknis:
- **Data strategi**: `jalaversity_get_option()` dengan fallback realistis — theme langsung tampil tanpa perlu mengisi Settings Page (Fase 4). Saat Fase 4 selesai, semua otomatis ter-update.
- **Hero H1 highlight**: PHP `str_replace()` pada esc_html() output — aman, hanya satu kata yang di-highlight
- **News query**: WP_Query aktual, bukan data statis. Fallback ke latest posts jika kategori "pengumuman"/"agenda" kosong
- **Search form**: WP native (action = home_url('/'), name="s") — kompatibel dengan WP Search
- **Floating badge**: Template part dengan $args agar bisa dipakai ulang di konteks lain (bukan hanya hero)
- **Stats bar**: margin-top: -78px untuk overlap hero — `.stats-bar-wrap` sebagai intermediate layer dengan background cream
- **`section-label`**: Dijadikan PHP function (bukan template part) karena dipakai inline di banyak konteks

### Statistik build:
- `css/front.css` — 44,989 bytes raw / **8,456 bytes gzip** (target < 30KB ✓)
- `css/admin.css` — tidak berubah
- Build: 0 error, 0 warning ✓

### Risiko yang diidentifikasi:
- News tabs JS (initNewsTabs) saat ini hanya hide/show panel "Semua" — tab per-kategori belum terhubung ke query PHP (perlu AJAX atau per-tab query jika ingin filter live)
- Faculty grid menggunakan hardcoded array — idealnya dari CPT di Fase masa depan

### Pelajaran yang dipetik:
- Template helper functions lebih ergonomis dari template parts untuk pattern yang muncul inline (section-label, icon calls)
- `$args` pada `get_template_part()` (WP 5.5+) membuat komponen floating-badge dan cta-banner reusable tanpa globals

### Next steps:
- Fase 3 lanjut: halaman Fakultas (hero-subpage, dean-profile, prodi-grid, keunggulan, dll)
- Content templates standar WP: single post, archive, search
- Settings Page (Fase 4) — agar semua `jalaversity_get_option()` bisa diisi via admin

---

## Sesi 05 — 2026-06-15

### Yang dikerjakan:
- **3 gap pre-Fase 3 ditutup semua** sebelum melanjutkan ke komponen
- `header.php` — WP root template header: `language_attributes()`, `wp_head()`, skip-to-content link, memanggil top-bar dan site-header template parts, membuka `<main id="main-content">`
- `footer.php` — WP root template footer: menutup `</main>`, memanggil site-footer, `wp_footer()`
- `includes/helpers/icon-helpers.php` — Implementasi penuh (stub → full): `jalaversity_icon_paths()` dengan 40+ Heroicons SVG stroke paths (phone, mail, bell, search, menu, x-mark, arrows, chevrons, check, shield, trophy, academic-cap, book, building, users, map-pin, globe, calendar, clock, star, language, dll), `jalaversity_icon()` return string, `jalaversity_icon_e()` echo
- `includes/helpers/social-helpers.php` — Implementasi penuh: `jalaversity_social_icon_paths()` (8 platform: fb, ig, yt, twitter, linkedin, wa, telegram, tiktok), `jalaversity_social_icon()`, `jalaversity_social_label()`, `jalaversity_social_links()` — membaca dari Settings Page option, skip empty URLs, full ARIA attributes
- `includes/nav-walker.php` — **Baru**: `class Jalaversity_Nav_Walker extends Walker_Nav_Menu` — override `start_lvl/end_lvl/start_el/end_el` — sub-menu dengan `aria-hidden="true"`, nav-item dengan `has-submenu/is-active` classes, submenu toggle button dengan `aria-expanded` + chevron SVG inline
- `includes/setup.php` — `register_nav_menus()` diupdate: menambah 4 lokasi baru (topbar, footer-about, footer-akademik, footer-layanan) dengan nama lokasi yang deskriptif dalam bahasa Indonesia
- `functions.php` — `nav-walker.php` ditambahkan ke array includes
- `template-parts/header/top-bar.php` — **Baru**: dark green topbar, kontak (phone+email) kiri, topbar menu WP dinamis depth-1 + language switcher (ID/EN) kanan, full ARIA roles
- `template-parts/header/site-header.php` — **Baru**: sticky header dengan logo/site-name, primary nav dengan `Jalaversity_Nav_Walker` depth-2, CTA button PMB dari settings, hamburger button + mobile drawer ARIA dialog dengan submenu accordion
- `template-parts/footer/site-footer.php` — **Baru**: 5-kolom footer: brand (logo+desc+alamat+kontak+social), 3 kolom nav menu dinamis (footer-about, footer-akademik, footer-layanan), 1 kolom kontak opsional jika social_whatsapp diset, bottom bar (copyright+menu)
- `js/front/main.js` — Ditulis ulang penuh: `initMobileMenu()` dengan drawer ARIA, body scroll lock, accordion submenu, focus trap; `initDesktopSubmenu()` dengan hover timer 120ms + keyboard toggle + click-outside; `initNewsTabs()` data-driven, `initSmoothScroll()`, `initStickyHeader()`
- `scss/front/_components.scss` — 350+ baris CSS baru ditambahkan di dalam `@layer components {}` untuk: `.topbar` (semua modifiers), `.site-header`, `.site-header__inner/logo/nav/actions`, `.btn--pmb`, `.hamburger`, `.nav-menu`, `.nav-item`, `.nav-link`, `.sub-menu`, `.submenu-toggle`, `.mobile-menu` (full drawer), `.site-footer` (semua child elements)

### Keputusan teknis:
- **6 nav menu locations** dengan nama yang jelas dalam bahasa Indonesia: "Primary Navigation", "Top Bar Menu", "Footer: Tentang", "Footer: Akademik", "Footer: Layanan", "Social Links"
- **Custom Walker** dipilih daripada default WP walker untuk kontrol penuh atas submenu HTML + ARIA attributes. Depth 2 untuk primary/mobile nav, depth 1 untuk topbar dan footer
- **Mobile drawer**: position fixed, z-index 200, slide dari kanan, backdrop klik-to-close, Escape key close, focus management
- **Desktop submenu**: CSS-first (opacity/visibility transition), JS hanya untuk ARIA state management + hover timer
- CSS mobile menu accordion menggunakan `display: none/block` bukan CSS transition (karena height animasi butuh JS measurement)

### Statistik build:
- `css/front.css` — 19,669 bytes raw / **4,847 bytes gzip** (target < 30KB ✓)
- `css/admin.css` — 4,261 bytes raw / 1,195 bytes gzip
- Build: 0 error, 0 warning ✓

### Risiko yang diidentifikasi:
- Tailwind production purge masih akan menghapus banyak kelas karena PHP templates belum digunakan (sama seperti Sesi 04 — normal, akan beres begitu Fase 3 berjalan)
- Mobile menu perlu ditest langsung di device: focus trap dan scroll lock bisa berperilaku berbeda di iOS Safari

### Pelajaran yang dipetik:
- Semua gap pre-Fase (header.php, footer.php, icon helpers, nav walker) harus diselesaikan sebelum memulai template parts — urutan ini terbukti benar
- CSS `@layer components` terus berkembang — struktur modular per komponen di dalam satu `@layer` block bekerja dengan baik

### Next steps:
- Fase 3 lanjut: `template-parts/components/` (stats-bar, section-label, cta-banner, breadcrumb, pagination)
- Lalu homepage template parts (hero-home, about, faculty-grid, pmb-section, news-section, research, locations)
- Lalu `page-templates/page-home.php`

---

## Sesi 04 — 2026-06-15

### Yang dikerjakan:
- `scss/front/_components.scss` — **Dibuat dari nol**, berisi:
  - Girih pattern mixin: `@define-mixin girih-bg $opacity` (postcss-mixins syntax)
  - `.section-label` + `&__line` + `&--on-dark` modifier
  - `.stats-bar` + `.stats-item` (dengan negative margin `-78px` overlap hero)
  - `.card` base + 4 tipe: `--faculty`, `--news-featured`, `--news-list`, `--location`, `--step`
  - `.badge`, `.badge-cat`, `.badge-tag`, `.badge-akr-a`, `.badge-akr-b`
  - `.floating-badge` (animasi `floaty` 5s)
  - `.about-badge` (hijau corner badge)
  - `.cta-banner` + `@mixin girih-bg 0.1` + `&__inner/title/body/actions`
  - `.btn` base + `.btn--primary`, `.btn--ghost-white`, `.btn--sm`
  - `.icon-container` + `--primary`, `--gold`, `--white-glass`
  - `.img-hero-radius` (`210px 210px 28px 28px`), `.img-about-radius` (`24px 24px 24px 120px`)
  - `.link-arrow` + `&--gold`
  - `.footer-social-link` (44px tap target, hover gold)
- `scss/front/_utilities.scss` — **Dibuat dari nol**, berisi:
  - Fluid typography: `.text-hero` (`clamp(38px, 5vw, 60px)`), `.text-hero-sub`, `.text-section`, `.text-section-sm`, `.text-cta`
  - Section spacing: `.section-py` (92px), `.section-pt`, `.section-pb`
  - Animation: `.anim-fadeup`, `.anim-floaty`
  - Gradient: `.bg-gradient-primary` (linear-gradient primary → medium → dark)
- `scss/admin/_admin-base.scss` — **Dibuat dari nol**, berisi layout Settings Page: tabs, section box, color picker row, form row, toggle switch
- `scss/front/main.scss` — @import 'components' dan @import 'utilities' diaktifkan (dipindah ke atas @tailwind)
- `scss/admin/main.scss` — @import 'admin-base' diaktifkan (dipindah ke atas :root block)
- `npm run build` — **clean, nol error, nol warning**

### Error yang diperbaiki:

**Error 1 — `@import must precede all other statements` (lagi)**
- Gejala: `@import 'components'` dan `@import 'utilities'` diletakkan setelah `@tailwind base/components/utilities`
- Ini adalah PENGULANGAN error Sesi 03 — pola yang sama harus selalu diikuti
- Fix: Pindahkan SEMUA @import ke atas file, sebelum `@tailwind` directives. Ini harus diingat sebagai aturan tetap: **@import selalu di atas, @tailwind selalu di bawah**.

**Error 2 — `@layer components is used but no matching @tailwind components directive`**
- Gejala: `_admin-base.scss` menggunakan `@layer components { }` tapi `scss/admin/main.scss` tidak punya `@tailwind components`
- Root cause: Admin CSS TIDAK menggunakan Tailwind — tidak ada `@tailwind` directives, tidak ada layer system
- Fix: Hapus `@layer components { }` wrapper dari `_admin-base.scss`. Admin CSS adalah plain CSS saja.

### Keputusan teknis:
- **`@define-mixin` / `@mixin` (postcss-mixins syntax)**: Bukan SCSS `@mixin`/`@include`. postcss-mixins memerlukan syntax sendiri karena pipeline kita menggunakan PostCSS, bukan Sass compiler. Girih mixin terbukti berfungsi dengan variabel `$(opacity)`.
- **Production purge adalah expected behavior**: Komponen classes di-strip Tailwind JIT karena PHP templates belum ada. Dev build (27KB / 6.9KB gzip) menampilkan semua komponen. Prod build (7.6KB / 2.4KB gzip) purge classes yang belum dipakai — ini BENAR, tidak perlu diworkaround.
- **Admin CSS tanpa Tailwind**: `scss/admin/` adalah plain CSS pipeline (postcss-nested + autoprefixer + cssnano). Tidak ada Tailwind, tidak ada @layer. Komponen ditulis sebagai flat CSS rules.
- **BEM dengan postcss-nested**: `&__element` dan `&--modifier` syntax berfungsi karena postcss-nested melakukan string concatenation pada `&`.
- **Aturan @import tetap**: Selalu di atas. Tidak ada pengecualian. Ini adalah CSS spec, bukan pilihan.

### Build stats (Fase 2 final):
| File | Raw | Prod gzip | Dev gzip |
|------|-----|-----------|---------|
| `css/front.css` | 7,652 bytes | **2,407 bytes** ✓ | 6,901 bytes ✓ |
| `css/admin.css` | 4,261 bytes | — | — |

Target: < 30KB gzip → ✓ (dev pun hanya 6.9KB, 23% dari limit)

### Pelajaran yang dipetik:
- Aturan `@import sebelum @tailwind` HARUS selalu diingat — ini bukan edge case, ini adalah CSS spec fundamental. Tambahkan komentar eksplisit di `main.scss` sebagai pengingat.
- `@layer` hanya valid jika ada matching `@tailwind <layer-name>` directive di file yang sama. Admin CSS tidak punya Tailwind, jadi tidak pakai @layer sama sekali.
- Tailwind production purge adalah fitur, bukan bug. Component classes akan muncul di prod setelah Fase 3 PHP templates menggunakan class-class tersebut.

### Next steps (Sesi 05 — Fase 3 dimulai):
- **Urutan implementasi Fase 3**: shared components dulu, kemudian page-specific
- Buat `template-parts/header/top-bar.php`
- Buat `template-parts/header/site-header.php`
- Buat `template-parts/header/navigation.php`
- Buat `template-parts/footer/site-footer.php`
- Buat `template-parts/components/stats-bar.php`
- Buat `template-parts/components/section-label.php`
- Setelah shared components selesai: homepage template parts
- Test: `npm run build` produksi → verifikasi komponen classes MUNCUL di `css/front.css`

---

## Sesi 03 — 2026-06-15

### Yang dikerjakan:
- `js/front/main.js` — Sticky header (aktif: adds box-shadow at scroll > 20px), smooth scroll untuk anchor links; stub functions untuk mobile menu + news tabs (Fase 3)
- `js/admin/admin.js` — jQuery stub untuk admin settings page (Fase 4)
- `npm install` — install semua devDependencies dari `package.json`
- Iteratif fix build pipeline (3 error cycle — lihat bagian "Error yang diperbaiki" di bawah)
- `scss/front/main.scss` — restrukturisasi: `@import` dipindah SEBELUM `@tailwind` directives
- `scss/front/_base.scss` — ditulis ulang: semua konten dibungkus `@layer base {}` agar Tailwind Preflight dan custom base styles merge dengan urutan cascade yang benar
- `postcss.config.js` — ditambahkan plugin `postcss-import` (posisi pertama) + custom `resolve()` function untuk SCSS partial convention (`_filename.scss`)
- `npm run build` — **build bersih, nol error, nol warning**
- Verifikasi output `css/front.css`: semua 35+ CSS custom properties hadir, `@layer base` styles tercompile benar, Tailwind Preflight + utilities aktif
- **Fase 1 dinyatakan SELESAI**

### Error yang diperbaiki:

**Error 1 — `@import` muncul literal di output CSS**
- Gejala: `@import "variables";@import "base";` muncul di akhir `css/front.css` alih-alih di-inline
- Root cause: `postcss-import` tidak terpasang — tidak ada plugin yang meresolve `@import` SCSS
- Fix: `npm install postcss-import --save-dev` + tambahkan sebagai plugin **pertama** di `postcss.config.js`

**Error 2 — `⚠ @import must precede all other statements`**
- Gejala: warning dari `postcss-import` karena `@import` ada di bawah `@tailwind base`
- Root cause: spesifikasi CSS mengharuskan `@import` selalu di posisi paling atas sebelum rule lainnya
- Fix: pindahkan `@import 'variables'` dan `@import 'base'` ke atas, sebelum semua `@tailwind` directives

**Error 3 — `CssSyntaxError: Failed to find 'variables'`**
- Gejala: `postcss-import` tidak menemukan file meskipun `_variables.scss` ada
- Root cause: `postcss-import` menggunakan CSS file resolution (cari `variables.css`), bukan SCSS partial convention (cari `_variables.scss` dengan underscore prefix dan `.scss` extension)
- Fix: tambahkan custom `resolve(id, basedir)` function di config `postcss-import` yang mencoba kandidat: `_id.scss` → `id.scss` → `_id.css` → `id` menggunakan `fs.existsSync()`

### Keputusan teknis:
- **`@layer base` wrapper**: `_base.scss` dibungkus sehingga Tailwind Preflight berjalan dulu (posisi di `@tailwind base`), kemudian custom base styles di-merge — ini adalah pola yang benar untuk postcss + tailwind + custom SCSS
- **`postcss-import` harus plugin pertama**: harus resolve semua `@import` sebelum plugin lain (termasuk Tailwind) memprosesnya
- **JS stubs dengan TODOs eksplisit**: `initMobileMenu()` dan `initNewsTabs()` sudah ada sebagai fungsi stub dengan `// TODO: Fase 3` agar Fase 3 tinggal fill-in tanpa restructure

### Build stats (Fase 1 final):
| File | Raw | Gzip |
|------|-----|------|
| `css/front.css` | 7,652 bytes | **2,407 bytes** ✓ |
| `css/admin.css` | ~600 bytes | — |

Target: `front.css` < 30KB gzip → **2,407 bytes = jauh di bawah target (8% dari limit)**

### Next steps (Sesi 04 — Fase 2):
- Buat `scss/front/_components.scss`: girih SVG mixin, `.section-label`, `.cta-banner`, `.floating-badge`
- Buat `scss/front/_utilities.scss`: custom utilities yang tidak ada di Tailwind
- Uncomment `@import 'components'` dan `@import 'utilities'` di `main.scss`
- `npm run build` ulang, verifikasi < 30KB gzip masih terpenuhi
- Test aktivasi theme di WordPress dengan `WP_DEBUG=true`

---

## Sesi 02 — 2026-06-15

### Yang dikerjakan:
- `style.css` — WordPress theme header
- `functions.php` — Entry point dengan PHP 8.1 check, konstanta tema, load order aman
- `includes/setup.php` — theme supports, editor palette, image sizes, nav menus, widget areas
- `includes/security.php` — 10 hardening: version hiding, XML-RPC off, header security, author enum block, dll
- `includes/helpers/options-helpers.php` — `jalaversity_get_option()` + `jalaversity_update_option()` dengan static cache
- `includes/enqueue.php` — Google Fonts preconnect, front CSS/JS, admin CSS/JS, CSS vars inline override
- `includes/helpers/image-helpers.php` — thumbnail, placeholder SVG, responsive image helpers
- `index.php` — fallback template
- `package.json` — PostCSS build pipeline
- `postcss.config.js` — postcss-scss parser + mixins + nested + tailwind + autoprefixer + cssnano
- `tailwind.config.js` — extends colors/fonts dengan CSS custom property references
- `scss/front/main.scss` — entry point dengan @tailwind directives
- `scss/front/_variables.scss` — semua 35+ CSS custom properties dari design system
- `scss/front/_base.scss` — reset, typography base, animations, container helper
- `scss/admin/main.scss` — admin CSS entry point
- Stub files: `seo.php`, `icon-helpers.php`, `social-helpers.php`, `settings/*.php`
- CSS placeholders: `css/front.css`, `css/admin.css`
- `.gitignore` — node_modules, OS files

### Keputusan teknis:
- **Build pipeline**: PostCSS + postcss-scss (bukan sass-only yang ada di prompt) — satu pass untuk SCSS syntax + @tailwind directives + autoprefixer + minification
- **options-helpers.php ditambahkan** ke load order di functions.php (sebelum enqueue.php) karena enqueue.php bergantung pada `jalaversity_get_option()`
- **icon-helpers.php** ditambahkan ke includes list (ada di arsitektur, terlewat di prompt)
- **Google Fonts weights**: Playfair Display 700/800 + Plus Jakarta Sans 400/500/600/700 (sesuai architecture doc, lebih hemat dari prompt)
- **CSS vars**: `jalaversity_output_css_vars()` menggunakan `sanitize_hex_color()` sebelum output ke inline style
- **Tap target minimum**: `a, button { min-height: 44px }` di `_base.scss`
- **`jalaversity_update_option()`** ditambahkan ke options-helpers (needed by Fase 4 settings save)

### Risiko yang ditemukan:
- `postcss-scss` sebagai parser tidak mendukung Sass `$variables` atau `@use` — fine untuk project ini karena kita pakai CSS custom properties
- `min-height: 44px` pada `a` di base.scss dapat mempengaruhi inline links di konten — perlu `reset` untuk `.wp-block-paragraph a` di Fase 2

### Next steps (Sesi 03):
- Fase 2: Jalankan `npm install` dan `npm run build` — verifikasi output CSS
- Buat `scss/front/_components.scss` (girih mixin, section-label, CTA banner, floating badge)
- Buat `scss/front/_utilities.scss`
- Buat JS stubs: `js/front/main.js`, `js/admin/admin.js`
- Test: aktivasi theme di WordPress, cek tidak ada PHP error/warning

---

## Sesi 01 — 2026-06-15

### Yang dikerjakan:
- Baca dan konfirmasi CLAUDE.md
- Analisis menyeluruh UI reference di `docs/templates/` (2 file HTML)
- Buat `docs/03-design-system.md`
- Buat `docs/02-architecture.md`
- Buat `docs/01-planning.md`
- Buat `docs/changelog.md` (file ini)

### Keputusan teknis:
- **CSS Strategy**: SCSS custom dikombinasi dengan Tailwind CSS CLI build (bukan CDN play, bukan pure SCSS)
- **Theming**: Warna brand disimpan sebagai CSS custom properties → dapat dioverride per-situs via Settings Page tanpa rebuild CSS
- **Google Fonts**: CDN dengan `display=swap`, hanya weight yang dipakai (Playfair 700/800, Jakarta Sans 400/600/700)
- **JavaScript**: Vanilla ES6+ tanpa library, jQuery tidak diload di front-end
- **Icon**: Heroicons SVG inline via PHP helper function
- **Settings**: WordPress Options API dengan static cache helper
- **Semantic colors**: Tailwind canonical defaults (green-600, amber-600, red-600, blue-600)
- **Warna hijau**: Dikonsolidasi dari 5+ shade asli menjadi 4 canonical token (primary, primary-dark, primary-medium, primary-light)

### Komponen yang ditemukan di UI reference:
- **8 shared components**: Top Bar, Site Header, Navigation, Stats Bar, Section Label, CTA Banner, Footer, Floating Badge
- **7 homepage components**: Hero, About, Faculty Grid, PMB Section, News Section, Research, Locations
- **8 faculty page components**: Breadcrumb, Hero Sub-page, Sub Navigation, Dean Profile, Prodi Grid, Keunggulan, Kompetensi+Karier, Facilities
- Total: **23 template parts** yang teridentifikasi

### Inkonsistensi atau catatan dari UI:
- Ada 5+ shade hijau yang sangat mirip → dikonsolidasi ke 4 canonical (keputusan disetujui)
- Ada 6+ shade gold/amber → dikonsolidasi ke 4 canonical (primary, dark, light, surface)
- Social media icon di footer: 40px × 40px, di bawah minimum tap target 44px → akan difix saat implementasi
- News Section adalah komponen paling kompleks: ada state tab filter yang butuh JS, 5 sub-area berbeda
- Negative margin stats bar (`-62px` s/d `-78px`) memerlukan z-index management hati-hati

### Pelajaran yang dipetik:
- UI reference menggunakan inline styles sepenuhnya (bukan class-based) — ini memudahkan ekstraksi token tapi membutuhkan terjemahan ke sistem CSS variable + SCSS
- Motif girih (Islamic geometric) adalah elemen identitas visual yang konsisten — wajib jadi mixin reusable
- "Section label" (garis — TEKS UPPERCASE — garis) adalah pattern UI yang muncul di hampir setiap section — kandidat kuat untuk komponen PHP tersendiri
- Dua halaman yang ada memiliki header dan footer identik → shared components sangat penting untuk maintainability

### Next steps (Sesi 02):
- Lengkapi `docs/00-project-brief.md` (isi Target Pengguna & Catatan Owner)
- Buat `docs/04-settings-schema.md` sebelum Fase 4
- Mulai **Fase 1**: `style.css`, `functions.php`, `includes/setup.php`, `includes/security.php`, `includes/enqueue.php`
- Setup npm: `package.json`, `tailwind.config.js`, `scss/front/main.scss`, `scss/front/_variables.scss`
