# Changelog — Jalaversity Theme

## [1.1.0] — 2026-06-26

### Added
- **CPT Agenda & Pengumuman** — Custom post type untuk event kampus dan pengumuman dengan ACF field group lengkap (tanggal, jam, lokasi, narasumber, kuota, link pendaftaran)
- **Section Berita & Pengumuman dinamis** — Layout featured card + 3 list card; judul/label konfigurasi via ACF page builder; conditional block (disembunyikan jika CPT kosong)
- **Laman Kontak** (`Template Name: Laman Kontak`) — Hero, kartu info kontak, Google Maps embed, form CF7 via `the_content()`
- **WhatsApp Widget** — Floating FAB kanan bawah dengan popup daftar Tim Layanan; data dikelola via Jalaversity Settings → Tim Layanan (repeater: nama, jabatan, WA, foto)
- **Tab Tim Layanan** di Jalaversity Settings — Repeater native (tanpa ACF Options Page) dengan pesan default WA
- Field baru di Settings → Umum: `contact_whatsapp`, `contact_hours`, `contact_maps_url`
- **SVG background pattern baru** (`images/bg-pattern.svg`) menggantikan pattern lama
- **Glass morphism** pada card Numbered Steps (varian light & on-dark)
- **Hero text-only mode** (`hero-page--text-only`) — konten terpusat lebar 65% ketika tidak ada gambar dan floating badge

### Changed
- Icon-list `rows` variant: CSS diratakan (flat rules) untuk menghindari silent-drop Tailwind v3 pada nested `@layer components`
- News section: grid `1fr 1fr` dengan `<div class="news-list-stack">` untuk 3 list card di kolom kanan
- CTA Banner: heading/body tidak lagi dari Settings, sepenuhnya via ACF page builder
- Tab Beranda di Settings dihapus (digantikan oleh page builder Laman Dinamis)
- Settings → Umum section Kontak & PMB dikonsolidasi (hapus tab Kontak duplikat)
- ACF Options Page Tim Layanan dihapus; data dipindah ke `jalaversity_options`

### Fixed
- SVG background path `url("../images/bg-pattern.svg")` — sebelumnya salah dua level ke atas
- Nested CSS dalam `@layer components` untuk icon-list rows dan news cards (Tailwind v3 silent-drop)
- `card__desc` margin `auto` sebagai nilai ke-4 tidak valid, diperbaiki
- News grid auto-fit membuat 4 kolom alih-alih 2; diganti `grid-template-columns: 1fr 1fr`

## [1.0.0] — 2026-06-01

- Initial release
