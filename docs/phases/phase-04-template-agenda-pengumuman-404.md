# Fase 04 — Template Konten Kampus (`single-agenda.php`, `single-pengumuman.php`, & `404.php`)

**Status:** Done
**Jenis:** Normal
**Mulai:** 2026-11-18
**Selesai:** 2026-11-18

## Tujuan
1. Menyediakan template khusus `single-agenda.php` dan `template-parts/content/content-agenda.php` untuk menampilkan seluruh field ACF dari `group_jalaversity_agenda` (`tanggal_mulai`, `tanggal_selesai`, `jam_mulai`, `jam_selesai`, `tipe_lokasi`, `lokasi`, `link_online`, `narasumber`, `penyelenggara`, `link_pendaftaran`, `kuota`) beserta tombol CTA pendaftaran (`GAP-09`).
2. Menyediakan template khusus `single-pengumuman.php` dan `template-parts/content/content-pengumuman.php` untuk menampilkan pengumuman resmi kampus (`GAP-09`).
3. Menyediakan halaman `404.php` berdesain tema dengan hero subpage, tombol kembali ke beranda, dan kotak pencarian (`GAP-09`).

## Scope
- [x] Buat `single-agenda.php` & `template-parts/content/content-agenda.php`.
- [x] Buat `single-pengumuman.php` & `template-parts/content/content-pengumuman.php`.
- [x] Buat `404.php`.
- [x] Tambahkan styling `.agenda-detail-card` & `.agenda-speakers` di `scss/front/_article.scss` dan jalankan `npm run build`.
- [x] Daftarkan file baru di `phpstan.neon.dist` dan `bin/check-dist.sh`.

## Checklist Penutupan (SOP)
- [x] `composer lint` — 0 error & 0 warning pada seluruh 5 file PHP baru.
- [x] `composer analyse` — 0 error.
- [x] `composer css:check` — valid.
- [x] `composer dist:check` — valid.
- [x] Security Review dijalankan (`architecture-security.md`).
- [x] `PROGRESS.md` & `RETROFIT-GAPS.md` diupdate.

## Security Review — Fase 04
- ✅ **Lolos:** Seluruh nilai field ACF (`tanggal_mulai`, `lokasi`, `link_online`, `penyelenggara`, `link_pendaftaran`, `kuota`, `narasumber`) di-escape sesuai konteks (`esc_html`, `esc_attr`, `esc_url`, `absint`).

## Ringkasan Hasil
- `GAP-09` selesai penuh.
