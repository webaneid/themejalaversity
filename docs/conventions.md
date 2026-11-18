# Conventions

## PHP
- Ikuti **WordPress Coding Standards** (dicek otomatis oleh PHPCS):
  tab untuk indentasi, `snake_case` untuk fungsi/variabel, spasi di dalam
  kurung `foo( $bar )`, Yoda condition tidak diwajibkan.
- Prefix wajib: fungsi/hook/option/transient `jalaversity_`, konstanta
  `JALAVERSITY_`, class `Jalaversity_`.
- Kompatibilitas: PHP **8.1+**, WordPress **6.0+**. Fitur PHP 8.1 (typed
  properties/params/returns, `match`, named args, `str_contains`,
  `str_starts_with`, `str_ends_with`, nullsafe, short array `[]`) dipakai secara
  konsisten.
- Satu file `includes/` = satu area tanggung jawab (`includes/setup.php`,
  `includes/security.php`, `includes/enqueue.php`, `includes/seo.php`,
  `includes/updater.php`, dst.). Hindari file monolitik.
- Komentar boleh Bahasa Indonesia; docblock fungsi publik minimal berisi
  satu kalimat tujuan + `@param`/`@return`.

## Template & Komponen
- Template hierarchy WordPress di root (`index.php`, `single.php`, `page.php`,
  `archive.php`, `search.php`, `sidebar.php`, `404.php`).
- Custom Page Templates di `page-templates/page-*.php` (hanya berisi komposisi
  pemanggilan komponen — zero business logic).
- Dua jenis komponen di `template-parts/`:
  1. **Pure-`$args` components (`template-parts/components/*.php`):** hanya
     merender data dari `$args`, tidak boleh memanggil `jalaversity_get_option()`
     atau membaca global `$post` langsung (kecuali fallback eksplisit untuk
     kompatibilitas mundur).
  2. **Loop-context components (`template-parts/content/*.php`):** beroperasi di
     dalam WordPress loop (`the_post()` sudah dipanggil), membaca `get_the_title()`,
     `the_content()`, dst. langsung; `$args` hanya untuk opsi tampilan (mis. `variant`).

## Aset (PostCSS + SCSS + Tailwind v3)
- Source front-end → `scss/front/main.scss` (beserta partial `scss/front/_*.scss`).
- Source wp-admin → `scss/admin/main.scss` (`scss/admin/_admin-base.scss`).
- Output produksi yang masuk rilis → `css/front.css` dan `css/admin.css`
  (di-compile via `npm run build` dan diverifikasi `composer css:check`).
- **Aturan Kritis Tailwind v3:** HANYA `scss/front/_components.scss` yang boleh
  membungkus rule di dalam `@layer components {}`. Partial lain (`_article.scss`,
  dll.) wajib menulis rule polos tanpa wrapper `@layer components`.
- Front-end JavaScript: `js/front/main.js` — murni Vanilla ES6+ (`defer`), tanpa
  jQuery.

## Branch
- `main` — production. Setiap merge berpotensi rilis ke semua pelanggan.
- `feat/{nama}`, `fix/{nama}`, `docs/{nama}`, `chore/{nama}` — dari `main`,
  di-merge kembali setelah seluruh pemeriksaan wajib lolos.

## Commit Message (Conventional Commits)
Format: `type(scope): deskripsi singkat` — deskripsi Bahasa Indonesia boleh.

| Type | Kapan | Memicu rilis? |
|---|---|---|
| `feat` | fitur baru yang terlihat pengguna | ✅ minor |
| `fix` | perbaikan bug | ✅ patch |
| `perf` | peningkatan performa | ✅ patch |
| `refactor` | ubah struktur tanpa ubah perilaku | ❌ |
| `style` | format kode (bukan CSS tampilan!) | ❌ |
| `docs` | dokumentasi | ❌ |
| `chore` | tooling, config, dependensi dev | ❌ |
| `test` | test | ❌ |

- Perubahan tampilan CSS yang terlihat pengguna = `fix` atau `feat`, bukan `style`.
- Scope contoh: `updater`, `news`, `header`, `security`, `enqueue`, `docs`, `admin`, `seo`, `cwv`.
- Breaking: tambahkan footer `BREAKING CHANGE: ...` (jarang — lihat SOP).

## Versi
- Sumber kebenaran: `Version:` di `style.css` dan konstanta `JALAVERSITY_VERSION`
  di `functions.php`. Dinaikkan otomatis oleh CI saat rilis.
