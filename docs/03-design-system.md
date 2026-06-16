# Design System — Jalaversity

> Sumber kebenaran untuk semua token desain. Diekstrak dari `docs/templates/`.
> Warna brand disimpan sebagai CSS custom properties sehingga dapat diubah
> per-situs melalui Settings Page tanpa menyentuh kode.

---

## Arsitektur CSS Variable

Semua token warna brand didefinisikan sebagai CSS custom properties di `:root`
dalam `scss/front/_variables.scss`. Tailwind config mereference var yang sama.
WordPress Settings Page mengoverride var ini via `wp_add_inline_style()`.

```css
/* Contoh override dari Settings Page */
:root {
  --color-primary:      #08422e; /* diambil dari get_option('jalaversity_color_primary') */
  --color-accent:       #b68c2e;
}
```

---

## Warna

### Brand — Primary Green (dapat diubah via Settings)

| Token | CSS Variable | Default Value | Penggunaan |
|-------|-------------|---------------|------------|
| Primary | `--color-primary` | `#08422e` | Heading, nav active, hero gradient |
| Primary Dark | `--color-primary-dark` | `#06301f` | Top bar background, footer background |
| Primary Medium | `--color-primary-medium` | `#0a4730` | Secondary gradient stop, hover states |
| Primary Light | `--color-primary-light` | `#f1f6f1` | Icon background on white sections |
| Primary Surface | `--color-primary-surface` | `#e8f3ec` | Badge background "Akreditasi Unggul" |

### Brand — Accent Gold (dapat diubah via Settings)

| Token | CSS Variable | Default Value | Penggunaan |
|-------|-------------|---------------|------------|
| Accent | `--color-accent` | `#b68c2e` | Link aktif, tombol PMB header, underline |
| Accent Dark | `--color-accent-dark` | `#a87e26` | Button hover state |
| Accent Light | `--color-accent-light` | `#e9c970` | Hero highlight text, CTA button background |
| Accent Surface | `--color-accent-surface` | `#fbf3dd` | Icon background pada konteks gold |

### Neutral — Background & Surface

| Token | CSS Variable | Default Value | Penggunaan |
|-------|-------------|---------------|------------|
| Background | `--color-bg` | `#f8f5ec` | Body default — warm cream |
| Surface | `--color-surface` | `#ffffff` | Card, header, white sections |
| Border | `--color-border` | `#ece6d6` | Border kartu utama |
| Divider | `--color-divider` | `#f0ebdd` | Garis pembagi dalam kartu |

### Text — Pada Background Terang

| Token | CSS Variable | Default Value | Penggunaan |
|-------|-------------|---------------|------------|
| Text Primary | `--color-text-primary` | `#1c2b24` | Teks utama terdungu |
| Text Secondary | `--color-text-secondary` | `#4b5a51` | Body text paragraf |
| Text Muted | `--color-text-muted` | `#6e7d73` | Teks pendukung, deskripsi card |
| Text Caption | `--color-text-caption` | `#8a9690` | Metadata, tanggal, label kecil |
| Text Nav | `--color-text-nav` | `#28392f` | Navigation link |

### Text — Pada Background Gelap (hardcoded, tidak di Settings)

| Nilai | Penggunaan |
|-------|------------|
| `#cfe0d6` | Body text di atas hero/section hijau |
| `#9fc0ad` | Teks muted di atas hero (breadcrumb, badge) |
| `#9bbaa6` | Footer body text |
| `#6e9079` | Footer copyright (paling muted) |
| `#cfe3d6` | Top bar text |

### Semantic (Hardcoded — Tailwind canonical defaults)

| Token | CSS Variable | Nilai | Standar |
|-------|-------------|-------|---------|
| Success | `--color-success` | `#16a34a` | Tailwind green-600 |
| Warning | `--color-warning` | `#d97706` | Tailwind amber-600 |
| Error | `--color-error` | `#dc2626` | Tailwind red-600 |
| Info | `--color-info` | `#2563eb` | Tailwind blue-600 |

### Badge Akreditasi (Kontekstual)

```scss
// Akreditasi Unggul / A
.badge-akr-a {
  background: var(--color-primary-surface); // #e8f3ec
  color: var(--color-primary-medium);       // #0a4730
}
// Akreditasi B
.badge-akr-b {
  background: var(--color-accent-surface);  // #fbf3dd
  color: var(--color-accent-dark);          // #a87e26
}
```

### Selection Color
```css
::selection { background: var(--color-accent); color: #fff; }
```

---

## Tipografi

### Font Family

```css
--font-heading: 'Playfair Display', Georgia, serif;
--font-body:    'Plus Jakarta Sans', system-ui, sans-serif;
```

- **Heading**: Playfair Display — weight 500, 600, 700, 800 — semua `<h1>`–`<h4>` dan card title
- **Body**: Plus Jakarta Sans — weight 400, 500, 600, 700, 800 — semua teks paragraf, UI, nav

> Font di-load dari Google Fonts CDN dengan `display=swap`. Self-host di Fase 6 (Performance).

### Skala Ukuran

| Token | Nilai | Weight | Line-height | Font | Penggunaan |
|-------|-------|--------|-------------|------|------------|
| H1 Homepage | `clamp(38px, 5vw, 60px)` | 700 | 1.07 | Playfair | Hero utama |
| H1 Halaman | `clamp(34px, 4.6vw, 54px)` | 700 | 1.08 | Playfair | Hero sub-page |
| H2 Section | `clamp(28px, 3.6vw, 42px)` | 700 | 1.13 | Playfair | Judul section |
| H2 CTA | `clamp(28px, 4vw, 46px)` | 700 | 1.12 | Playfair | Banner CTA |
| H3 Card | `20–24px` | 700 | 1.22 | Playfair | Judul kartu besar |
| H3 Sidebar | `20px` | 700 | — | Playfair | Sidebar heading |
| H4 Footer | `14px` | 700 | — | Jakarta Sans | Footer col heading (uppercase) |
| Body XL | `17px` | 400 | 1.65 | Jakarta Sans | Lead paragraph hero |
| Body | `16px` | 400 | 1.65–1.72 | Jakarta Sans | Teks umum |
| Body SM | `15–15.5px` | 400/600 | 1.5 | Jakarta Sans | Konten kartu |
| Small | `14–14.5px` | 400/600 | 1.5 | Jakarta Sans | Link, label |
| Caption | `12–13.5px` | 500/600 | — | Jakarta Sans | Tanggal, meta, badge |
| Section Label | `13px` | 700 | — | Jakarta Sans | Label atas section (uppercase, ls 0.16em) |
| Top Bar | `12.5px` | 500 | — | Jakarta Sans | Kontak & quick links |

### Section Label Pattern
Digunakan di hampir semua section sebagai label pembuka di atas H2:
```html
<!-- Pattern: garis — teks — garis (centered) atau garis — teks (left-aligned) -->
<div class="section-label">
  <span class="section-label__line"></span>
  Nama Section
  <span class="section-label__line"></span>
</div>
```
```scss
.section-label {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: var(--color-accent);
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  &__line {
    width: 26px;
    height: 2px;
    background: var(--color-accent);
    display: inline-block;
  }
}
```

---

## Spacing (4px grid)

| Token | Nilai | CSS Variable |
|-------|-------|-------------|
| xs | 4px | `--space-xs` |
| sm | 8px | `--space-sm` |
| md | 16px | `--space-md` |
| lg | 24px | `--space-lg` |
| xl | 32px | `--space-xl` |
| 2xl | 48px | `--space-2xl` |
| 3xl | 64px | `--space-3xl` |
| 4xl | 96px | `--space-4xl` |

### Spacing Kontekstual

```
Container horizontal:   padding: 0 24px
Container max-width:    1200px (umum), 1120px (stats bar)
Section vertical:       90–96px atas-bawah
Grid gap (kartu):       24px
Grid gap (kecil):       16–18px
Flex gap (dua kolom):   56–60px
Hero padding (home):    84px 24px 150px
Hero padding (sub):     34px 24px 120px
Footer top padding:     64px
```

---

## Border Radius

| Token | Nilai | CSS Variable | Penggunaan |
|-------|-------|-------------|------------|
| sm | 8px | `--radius-sm` | Nav link hover, small elements |
| md | 12–14px | `--radius-md` | Icon containers, small cards |
| lg | 18px | `--radius-lg` | Kartu utama (card default) |
| xl | 20–24px | `--radius-xl` | Stats card, about image |
| 2xl | 28px | `--radius-2xl` | CTA banner |
| button-sm | 10px | `--radius-btn-sm` | Button kecil (PMB header) |
| button | 11–12px | `--radius-btn` | Button CTA utama |
| hero-img | `210px 210px 28px 28px` | — | Image hero (rounded top) |
| full | 9999px | `--radius-full` | Pills, badge akreditasi |

---

## Shadow

| Token | Nilai | CSS Variable | Penggunaan |
|-------|-------|-------------|------------|
| sm | `0 1px 2px rgba(0,0,0,.03)` | `--shadow-sm` | Card default (diam) |
| md | `0 6px 16px rgba(182,140,46,.30)` | `--shadow-md` | Button PMB |
| lg | `0 14px 30px rgba(8,66,46,.12)` | `--shadow-lg` | News card hover kecil |
| xl | `0 22px 44px rgba(8,66,46,.14)` | `--shadow-xl` | Card hover utama |
| 2xl | `0 24px 50px rgba(8,66,46,.13)` | `--shadow-2xl` | Stats card, about image |
| 3xl | `0 30px 60px rgba(0,0,0,.32)` | `--shadow-3xl` | Hero image |
| header | `0 10px 30px rgba(8,66,46,.10)` | `--shadow-header` | Header on-scroll |
| float | `0 18px 40px rgba(0,0,0,.28)` | `--shadow-float` | Floating badge pada hero |
| cta-gold | `0 12px 30px rgba(233,201,112,.30)` | `--shadow-cta` | CTA button gold |

---

## Breakpoints

| Nama | Nilai | Keterangan |
|------|-------|-----------|
| mobile | `< 640px` | Default (mobile-first) |
| tablet | `640px` | `@media (min-width: 640px)` |
| desktop | `1024px` | `@media (min-width: 1024px)` |
| wide | `1280px` | `@media (min-width: 1280px)` |

> Layout menggunakan `grid-template-columns: repeat(auto-fit, minmax(...))` — responsif
> tanpa explicit breakpoint di banyak komponen. Tambahkan breakpoint eksplisit hanya
> untuk kasus yang tidak dapat diselesaikan dengan auto-fit.

---

## Dekorasi — Girih Pattern

Islamic geometric pattern (motif girih) muncul di 5+ section sebagai background overlay.

```scss
// Gunakan sebagai mixin reusable
@mixin girih-bg($opacity: 0.1) {
  &::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64'%3E%3Cg fill='none' stroke='%23e9c970' stroke-width='1'%3E%3Crect x='16' y='16' width='32' height='32'/%3E%3Crect x='16' y='16' width='32' height='32' transform='rotate(45 32 32)'/%3E%3C/g%3E%3C/svg%3E");
    opacity: $opacity;
    pointer-events: none;
    z-index: 0;
  }
}
```

Penggunaan per section:
- Hero: `opacity: 0.10`
- PMB section: `opacity: 0.09`
- CTA banner: `opacity: 0.10`
- Footer: `opacity: 0.06`

---

## Animasi & Transisi

| Nama | Keyframe | Penggunaan |
|------|----------|-----------|
| `fadeup` | `opacity:0, translateY(18px) → opacity:1, translateY(0)` | Hero content on-load |
| `floaty` | `translateY(0) → translateY(-9px) → translateY(0)`, 5s | Floating badge pada hero |

```scss
// Transisi standar
--transition-fast:   0.18s ease;
--transition-normal: 0.22s ease;
--transition-slow:   0.30s ease;
```

---

## Komponen yang Teridentifikasi

### Reusable Components (Shared — ada di kedua halaman)

| # | Komponen | Deskripsi | File Template Part |
|---|----------|-----------|-------------------|
| 1 | **Top Bar** | Baris tipis kontak + quick links + language toggle | `template-parts/header/top-bar.php` |
| 2 | **Site Header** | Sticky header: logo + nav + CTA button | `template-parts/header/site-header.php` |
| 3 | **Navigation** | 7-item horizontal nav, hover rounded | `template-parts/header/navigation.php` |
| 4 | **Stats Bar** | Floating card 4 kolom, overlap hero via negative margin | `template-parts/components/stats-bar.php` |
| 5 | **Section Label** | Teks uppercase + garis emas atas section | `template-parts/components/section-label.php` |
| 6 | **CTA Banner** | Dark green card + girih + 2 tombol | `template-parts/components/cta-banner.php` |
| 7 | **Footer** | 5 kolom: logo+social + 4 tautan + bottom bar | `template-parts/footer/site-footer.php` |
| 8 | **Floating Badge** | Badge absolut di atas gambar ("UNGGUL"), animasi floaty | `template-parts/components/floating-badge.php` |

### Page-Specific Components

| # | Komponen | Halaman | File Template Part |
|---|----------|---------|-------------------|
| 9 | **Hero Homepage** | Beranda | `template-parts/content/hero-home.php` |
| 10 | **About Section** | Beranda | `template-parts/content/about.php` |
| 11 | **Faculty Cards Grid** | Beranda | `template-parts/content/faculty-grid.php` |
| 12 | **PMB Section** | Beranda | `template-parts/content/pmb-section.php` |
| 13 | **News Section** | Beranda | `template-parts/content/news-section.php` |
| 14 | **Research Section** | Beranda | `template-parts/content/research.php` |
| 15 | **Locations Grid** | Beranda | `template-parts/content/locations.php` |
| 16 | **Breadcrumb** | Sub-pages | `template-parts/components/breadcrumb.php` |
| 17 | **Hero Sub-page** | Semua sub-pages | `template-parts/content/hero-subpage.php` |
| 18 | **Sub Navigation** | Halaman Fakultas | `template-parts/components/sub-nav.php` |
| 19 | **Dean Profile** | Halaman Fakultas | `template-parts/content/dean-profile.php` |
| 20 | **Program Studi Grid** | Halaman Fakultas | `template-parts/content/prodi-grid.php` |
| 21 | **Keunggulan Grid** | Halaman Fakultas | `template-parts/content/keunggulan.php` |
| 22 | **Kompetensi & Karier** | Halaman Fakultas | `template-parts/content/kompetensi-karier.php` |
| 23 | **Facilities Grid** | Halaman Fakultas | `template-parts/content/fasilitas.php` |

### Komponen Kompleks (butuh perhatian khusus)

| # | Komponen | Kenapa Kompleks | Pendekatan |
|---|----------|----------------|------------|
| 1 | **News Section** | Terdiri dari: tab filter (JS state) + featured article + list 3 artikel + kotak pengumuman + kotak agenda — semua dalam satu section | Pisah menjadi sub-component, tab filter dengan vanilla JS data-attribute, `WP_Query` terpisah per panel |
| 2 | **Stats Bar** | Negative margin `-62px` sampai `-78px` harus overlap hero, z-index harus di atas hero gradient | Wrapper dengan `position: relative; z-index: 5` |
| 3 | **Sticky Header + Scroll Shadow** | Box shadow muncul saat scroll > 20px, membutuhkan JS event listener | Small vanilla JS snippet, passive event listener |
| 4 | **Hero Image Custom Radius** | `border-radius: 210px 210px 28px 28px` — top fully rounded, bottom squared. Tidak standard, overflow:hidden wajib | Tes ketat di mobile, pastikan tidak collapse |
| 5 | **Girih SVG Pattern** | Muncul di 5 section berbeda, jangan copy-paste | SCSS mixin `@include girih-bg(0.1)` dengan inline SVG data-URI |
| 6 | **Faculty Sub Navigation** | Anchor-based tabs, active state berdasarkan scroll position | Vanilla JS IntersectionObserver atau `scrollspy` sederhana |

---

## Pola Section Background

Section bergantian antara dua warna background:

```
Cream (#f8f5ec): Hero → About → PMB → Berita → Lokasi → body default
White (#ffffff): Stats bar card → Fakultas grid → Riset → [kartu di atas cream]
Dark (#08422e):  Hero → PMB → Keunggulan → CTA Banner → Footer
```

---

## Catatan & Keputusan Desain

- **CSS Variables + Tailwind**: Semua warna brand diekspos sebagai CSS custom property.
  Tailwind config `extend.colors` mereference var ini. Override per-situs dilakukan
  WordPress via inline style di `<head>`.

- **Google Fonts CDN**: `display=swap` untuk mencegah FOIT. Load hanya weight yang
  dipakai: Playfair Display 700, 800; Plus Jakarta Sans 400, 600, 700.
  Review untuk self-host di Fase 6.

- **Consolidasi warna hijau**: 5+ shade asli disederhanakan menjadi 4 canonical token
  (`primary`, `primary-dark`, `primary-medium`, `primary-light`). Shade yang sangat mirip
  (`#063a27`, `#2c5a45`, `#1d4d36`, `#133f2a`) diabsorb ke token terdekat atau dipakai
  hardcoded hanya untuk konteks spesifik (separator, footer divider).

- **Girih pattern sebagai mixin**: Jangan implementasi sebagai inline SVG per-element.
  Gunakan `::before` pseudo-element via SCSS mixin untuk konsistensi dan kemudahan ubah.

- **Semantic colors tidak ada di UI reference**: Ditetapkan menggunakan Tailwind canonical
  defaults (green-600, amber-600, red-600, blue-600). Digunakan untuk form validation,
  notice WordPress, alert message jika muncul di masa mendatang.

- **Section label**: Pola `garis — TEKS — garis` adalah motif desain yang konsisten
  di seluruh UI. Perlu dijadikan komponen PHP reusable.

- **Minimum tap target**: 44px × 44px untuk semua elemen interaktif (CLAUDE.md requirement).
  Icon social footer saat ini 40px — perlu disesuaikan ke 44px saat implementasi.
