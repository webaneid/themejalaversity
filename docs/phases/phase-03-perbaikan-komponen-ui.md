# Fase 03 — Perbaikan Bug Komponen UI (News Tabs, Hero Kontak, Ikon SVG, & Hapus Stub Mati)

**Status:** Done
**Jenis:** Normal
**Mulai:** 2026-11-18
**Selesai:** 2026-11-18

## Tujuan
1. Memperbaiki tab kategori berita di `template-parts/content/news-section.php` agar setiap tombol tab kategori memiliki panel `data-tab-panel="{slug}"` yang merender artikel kategori tersebut (`GAP-06`).
2. Memperbaiki `page-templates/page-contact.php` agar mengirim `'heading' => get_the_title()` + `'show_breadcrumb' => true` ke `hero-page.php` dan mengganti `<main id="main">` menjadi `<section>` sehingga tidak bersarang di dalam `<main id="main-content">` (`GAP-07`).
3. Menambahkan key ikon `'message-circle'` dan `'x'` di `includes/helpers/icon-helpers.php` agar ikon pada `template-parts/footer/wa-widget.php` dan `page-templates/page-contact.php` tampil utuh (`GAP-08`).
4. Menghapus file stub kosong `includes/acf/acf-options.php` beserta referensinya di `functions.php` (`GAP-10`).

## Scope
- [x] Perbaiki `template-parts/content/news-section.php` (loop panel per kategori + atribut `aria-labelledby` & `type="button"`).
- [x] Perbaiki `page-templates/page-contact.php` (`heading` prop & `<section>` wrapper).
- [x] Tambahkan `'message-circle'` dan `'x'` di `includes/helpers/icon-helpers.php` serta rapikan `template-parts/footer/wa-widget.php`.
- [x] Hapus `includes/acf/acf-options.php` dan perbarui `functions.php`.

## Checklist Penutupan (SOP)
- [x] `composer lint` — 0 error & 0 warning pada seluruh file yang diubah (`functions.php`, `icon-helpers.php`, `page-contact.php`, `news-section.php`, `wa-widget.php`).
- [x] `composer analyse` — 0 error.
- [x] `composer css:check` — valid.
- [x] `composer dist:check` — valid (`68 file`, `556 KB`).
- [x] Security Review dijalankan (`architecture-security.md`).
- [x] `PROGRESS.md` & `RETROFIT-GAPS.md` diupdate.

## Security Review — Fase 03
- ✅ **Lolos:** Seluruh output di `news-section.php`, `page-contact.php`, `wa-widget.php`, dan `icon-helpers.php` telah di-escape sesuai konteks (`esc_html`, `esc_attr`, `esc_url`).

## Ringkasan Hasil
- `GAP-06`, `GAP-07`, `GAP-08`, dan `GAP-10` selesai penuh.
