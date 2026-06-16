# Project Brief — Jalaversity WordPress Theme

## Ringkasan
Jalaversity adalah WordPress theme custom yang dibangun dari nol. Dirancang untuk menjadi ringan, aman, modern, dan terasa seperti aplikasi mobile — bukan website biasa.

## Tujuan
- Membuat theme WordPress yang sepenuhnya custom (tidak bergantung parent theme)
- Performa tinggi: CSS minimal, JS minimal, load cepat
- Keamanan ketat: setiap input sanitasi, setiap output escape
- Mobile-first: tampilan dan feel seperti aplikasi di smartphone
- Mudah dikonfigurasi: settings page yang komprehensif tanpa plugin tambahan
- SEO-ready: schema markup, OG tags, canonical, breadcrumb built-in

## Identitas

| Atribut | Nilai |
|---------|-------|
| Nama theme | Jalaversity |
| Textdomain | `jalaversity` |
| Versi awal | 1.0.0 |
| Minimum WP | 6.0 |
| Minimum PHP | 8.1 |
| Lisensi | GPL-2.0+ |

## Target Pengguna
*[Isi: siapa yang akan menggunakan theme ini — blogger, media, portofolio, dll]*

## Referensi Desain
UI reference tersimpan di `docs/templates/`. File HTML dari hasil generate Claude design menjadi panduan visual yang wajib diikuti.

## Prinsip Non-Negotiable
1. **Aman** — tidak ada celah XSS, CSRF, atau direct file access
2. **Ringan** — CSS front-end < 30KB (gzip), tidak ada jQuery di front-end
3. **Modular** — satu file = satu tanggung jawab, tidak ada file monolitik
4. **Terdokumentasi** — setiap keputusan tercatat di `docs/`
5. **Mobile-first** — desain dimulai dari 375px

## Fitur Utama yang Direncanakan
- [ ] Settings page komprehensif (tanpa plugin)
- [ ] SEO built-in (meta, OG, schema, breadcrumb)
- [ ] Header sticky + responsive navigation
- [ ] Dark mode (opsional, diputuskan di Fase 2)
- [ ] Lazy load gambar
- [ ] Custom card component untuk listing konten
- [ ] Single post layout yang bersih
- [ ] Archive/category page
- [ ] Search overlay
- [ ] Footer multi-kolom

## Struktur Tim
- Owner/Developer: [nama Anda]
- AI Assistant: Claude Code (VS Code)
- Design Reference: Claude.ai generated UI

## Catatan Owner
*[Tambahkan catatan, preferensi, atau batasan khusus di sini]*

---
*Dibuat: [tanggal]*
*Status: Aktif — Fase 0*
