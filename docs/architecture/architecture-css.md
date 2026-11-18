# Architecture — CSS, Design System & Build Pipeline (`PostCSS` + `SCSS` + `Tailwind v3`)

> Dokumen referensi wajib setiap kali mengubah tampilan, menambah komponen UI,
> atau memodifikasi style admin maupun front-end di **Jalaversity**.

---

## 1. Pipeline Build CSS (`package.json` & `postcss.config.js`)

Jalaversity menggunakan pipeline **PostCSS** (`postcss-scss` parser + `postcss-import` + `postcss-mixins` + `postcss-nested` + `tailwindcss` v3 + `autoprefixer` + `cssnano` saat `NODE_ENV=production`):

| Perintah | Fungsi |
|---|---|
| `npm run build` | Menjalankan `build:front` dan `build:admin` (minified & purged untuk produksi) |
| `npm run build:front` | Mengompilasi `scss/front/main.scss` → `css/front.css` |
| `npm run build:admin` | Mengompilasi `scss/admin/main.scss` → `css/admin.css` |
| `npm run dev` | Watch mode `scss/front/main.scss` → `css/front.css` |
| `composer css:check` | Menjalankan `python3 bin/css-audit.py --check` (verifikasi eksistensi, keseimbangan `{` dan `}`, budget gzip `< 30 KB`, dan aturan tunggal `@layer components`) |

---

## 2. Struktur File `scss/` dan Urutan Import

```text
scss/
├── front/
│   ├── main.scss          ← Entry point front-end
│   ├── _variables.scss    ← 35+ CSS custom properties (:root)
│   ├── _base.scss         ← Reset, @font-face (Gontor-Bold.otf), base typography (@layer base)
│   ├── _components.scss   ← Komponen UI & ACF Page Builder (SATU-SATUNYA @layer components)
│   ├── _article.scss      ← Style artikel, arsip, kartu post, & sidebar (TANPA @layer)
│   └── _utilities.scss    ← Fluid typography (clamp), animasi, girih helper (@layer utilities)
└── admin/
    ├── main.scss          ← Entry point wp-admin
    └── _admin-base.scss   ← Layout Jalaversity Settings, tabs, & repeater Tim Layanan
```

### ⚠️ Dua Aturan Kritis Pipeline PostCSS + Tailwind v3
1. **Urutan `@import` Wajib di Atas `@tailwind`:**
   Di `scss/front/main.scss`, seluruh `@import` (`variables`, `base`, `components`, `article`, `utilities`) **wajib** ditulis sebelum `@tailwind base; @tailwind components; @tailwind utilities;` agar `postcss-import` meresolusi seluruh partial terlebih dahulu.
2. **Hanya `_components.scss` yang Boleh Memakai `@layer components {}`:**
   Jika ada dua blok `@layer components { ... }` terpisah di hasil akhir, Tailwind CSS v3 secara diam-diam menghapus sebagian rule dari blok kedua tanpa warning. Semua partial tambahan (seperti `_article.scss`) **wajib** menulis rule CSS polos tanpa wrapper `@layer components`. Aturan ini diaudit otomatis oleh `composer css:check`.

---

## 3. Sistem Token Desain & Dinamisasi Warna (`:root`)

Semua warna brand didefinisikan di `scss/front/_variables.scss` dan direferensikan di `tailwind.config.js`. Nilai dari **Jalaversity Settings → Tab Warna** disuntikkan ke `<head>` melalui `jalaversity_output_css_vars()` di `includes/enqueue.php`:

| Token | CSS Variable | Default | Penggunaan |
|---|---|---|---|
| Primary | `--color-primary` | `#08422e` | Heading, nav aktif, gradien hero |
| Primary Dark | `--color-primary-dark` | `#06301f` | Background top-bar & footer |
| Primary Medium | `--color-primary-medium` | `#0a4730` | Gradien sekunder, hover state |
| Primary Light | `--color-primary-light` | `#f1f6f1` | Background ikon pada seksi terang |
| Primary Surface | `--color-primary-surface` | `#e8f3ec` | Badge akreditasi Unggul/A |
| Accent | `--color-accent` | `#b68c2e` | Link aktif, tombol PMB header, garis label |
| Accent Dark | `--color-accent-dark` | `#a87e26` | Hover tombol aksen |
| Accent Light | `--color-accent-light` | `#e9c970` | Teks highlight hero, tombol CTA emas |
| Background | `--color-bg` | `#f8f5ec` | Background dasar halaman (warm cream) |
| Surface | `--color-surface` | `#ffffff` | Kartu, header, seksi putih |
| Border | `--color-border` | `#ece6d6` | Garis tepi kartu |
| Text Primary | `--color-text-primary` | `#1c2b24` | Teks utama |
| Text Secondary | `--color-text-secondary` | `#4b5a51` | Paragraf isi |
| Text Muted | `--color-text-muted` | `#6e7d73` | Deskripsi kartu & teks pendukung |

> **Aturan:** Jangan pernah meng-hardcode hex warna brand (`#08422e`, `#b68c2e`, dst.) di SCSS — selalu gunakan `var(--color-*)` agar kustomisasi warna per-kampus berfungsi penuh tanpa rebuild CSS.

---

## 4. Tipografi & Self-Hosted Font

- **Font Heading (`--font-heading`):** `'Gontor', Georgia, serif` — dimuat lokal dari `fonts/Gontor-Bold.otf` (`font-weight: 700; font-display: swap`) di `scss/front/_base.scss` dan di-preload di `<head>`. Seluruh heading memakai weight `700`.
- **Font Body (`--font-body`):** `'Plus Jakarta Sans', system-ui, sans-serif` — dimuat dari Google Fonts CDN (`wght@400;500;600;700`).
- **Teks Arab (`.arabic-paragraph`):** Dideteksi otomatis di `.entry-content` oleh `initArabicParagraphs()` di `js/front/main.js` dan diberi `direction: rtl; text-align: right; font-family: serif;`.
