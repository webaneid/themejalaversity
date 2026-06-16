# Skema Settings Page — Jalaversity

> Fase 4. Satu sumber kebenaran untuk semua field di Settings Page admin.
> Disusun dari audit langsung seluruh pemanggilan `jalaversity_get_option()`
> di codebase (bukan dari tebakan) — setiap field di bawah ini punya
> konsumen nyata yang sudah ada.

---

## Arsitektur

- **Storage**: 1 row di `wp_options` — key `jalaversity_options`, isi serialized array. Dibaca/ditulis lewat `jalaversity_get_option()` / `jalaversity_update_option()` (`includes/helpers/options-helpers.php`, sudah ada).
- **Registrasi**: WordPress Settings API murni (`register_setting`, `add_settings_section`, `add_settings_field`) — bukan custom table, sesuai CLAUDE.md.
- **UI**: 1 halaman admin dengan tab (`?page=jalaversity-settings&tab=X`). Tiap tab adalah "page" Settings-API terpisah, tapi semua menulis ke `option_name` yang sama (`jalaversity_options`) lewat `option_group` yang sama (`jalaversity_options_group`).
- **Field generik**: Tidak ada 1 callback per field (akan jadi >80 fungsi nyaris identik). Field didefinisikan sebagai array konfigurasi di `jalaversity_settings_schema()`, di-loop untuk register, dan dirender oleh **satu** fungsi generik `jalaversity_render_settings_field( $field )` yang switch berdasarkan `type`.
- **Sanitasi**: **Satu** callback `jalaversity_sanitize_options( $input )` yang melakukan iterasi field di schema, sanitasi sesuai tipe (`sanitize_text_field`, `sanitize_textarea_field`, `sanitize_email`, `sanitize_hex_color`, `absint`, `esc_url_raw`), lalu **merge dengan option lama** (`array_merge`) sebelum disimpan — wajib, karena tiap tab cuma submit field miliknya sendiri; tanpa merge, submit Tab A akan menghapus seluruh data Tab B/C/D.
- **Image field**: simpan attachment ID (int), pakai `wp.media` uploader (JS di `js/admin/admin.js`), preview thumbnail di admin.
- **Color field**: `<input type="text" class="jalaversity-color-field">` + `wp-color-picker` (WP core, sudah built-in, tidak perlu library tambahan).

## Field yang SENGAJA TIDAK dibuat di fase ini

CLAUDE.md mencantumkan section SEO/Performance/Typography/Advanced di rencana awal, tapi field-field itu **belum punya konsumen** di kode (grep `jalaversity_get_option` tidak menemukan pemanggilan apa pun untuk `meta_description`, `og_image`, `lazy_load`, `preload_fonts`, `font_family`, `custom_css`, `maintenance_mode`, dst). Menambah field tanpa kode yang membacanya melanggar aturan anti-premature-abstraction yang sudah dipegang sejak Sesi 07. Field-field ini ditambahkan **saat fase yang mengkonsumsinya benar-benar dikerjakan**:
- **SEO** → Fase 5 (`includes/seo.php`, masih placeholder)
- **Performance** (lazy load/preload fonts toggle) → saat `includes/helpers/image-helpers.php`/`enqueue.php` benar-benar mengimplementasikan toggle-nya
- **Typography** (font family/size pilihan) → saat ada kebutuhan ganti font selain Google Fonts hardcode di `enqueue.php`
- **Advanced** (custom CSS/JS, maintenance mode) → saat dibutuhkan nyata

Logo/favicon juga **tidak** didobel di sini — sudah ditangani WordPress core via `custom-logo` theme support (`includes/setup.php`) dan Site Icon di Customizer.

---

## Tab 1 — Umum

| Key | Label | Type | Default |
|---|---|---|---|
| `contact_address` | Alamat | textarea | — |
| `contact_phone` | Telepon | tel | — |
| `contact_email` | Email | email | — |
| `footer_copyright` | Teks Copyright Footer | text | — |
| `contact_url` | URL Kontak (tombol "Hubungi Kami") | url | `#` |
| `pmb_url` | URL Pendaftaran PMB | url | `#` |
| `pmb_label` | Teks Tombol PMB | text | `Daftar PMB Sekarang` |
| `pmb_brochure_url` | URL Brosur PMB (opsional) | url | — |

Dipakai di: `top-bar.php`, `site-header.php`, `site-footer.php`, `cta-banner.php`, `pmb-section.php`.

## Tab 2 — Beranda

### Hero
| Key | Label | Type | Default |
|---|---|---|---|
| `hero_tagline` | Badge/Tagline | text | nama situs |
| `hero_heading` | Heading (H1) | text | "Menuntut Ilmu, Menebar Manfaat untuk Peradaban" |
| `hero_highlight` | Frase Highlight | text | "Menebar Manfaat" |
| `hero_lead` | Lead Paragraph | textarea | — |
| `hero_image_id` | Gambar Hero | image | — |
| `hero_image_alt` | Alt Text Gambar | text | nama situs |
| `hero_trust_1` / `_2` / `_3` | Trust Badge 1/2/3 | text | — |
| `accreditation_label` | Label Floating Badge | text | "Akreditasi Institusi" |
| `accreditation_value` | Value Floating Badge | text | "UNGGUL" |

### Tentang
| Key | Label | Type | Default |
|---|---|---|---|
| `about_heading` | Heading | text | — |
| `about_body` | Body | textarea | — |
| `about_image_id` | Gambar | image | — |
| `about_years` | Angka Tahun (badge) | text | "28+" |
| `about_years_label` | Label Tahun | text | — |
| `about_link_label` | Teks Link | text | — |
| `about_link_url` | URL Link | url | `#` |

### Statistik (4 kartu)
| Key | Label | Type |
|---|---|---|
| `stats_1_value`, `stats_1_label` | Statistik 1 | text |
| `stats_2_value`, `stats_2_label` | Statistik 2 | text |
| `stats_3_value`, `stats_3_label` | Statistik 3 | text |
| `stats_4_value`, `stats_4_label` | Statistik 4 | text |

### Fakultas & Program Studi
| Key | Label | Type |
|---|---|---|
| `faculty_heading` | Heading Section | text |
| `faculty_subhead` | Lead Paragraph | textarea |
| `faculty_{1..6}_image_id` | Gambar Fakultas 1–6 | image |
| `faculty_{1..6}_url` | URL Fakultas 1–6 | url |

Catatan: nama/icon/deskripsi 6 fakultas **hardcode** di `jalaversity_get_faculties()` (bukan settings) — hanya gambar dan URL per-fakultas yang bisa diatur di sini. Mengubah daftar fakultas perlu edit kode (di luar scope settings page).

### Riset & Inovasi
| Key | Label | Type |
|---|---|---|
| `research_heading` | Heading | text |
| `research_body` | Body | textarea |
| `research_image_id` | Gambar | image |
| `research_badge_value` | Value Badge | text |
| `research_badge_label` | Label Badge | text |

### Lokasi Kampus (3 kampus)
| Key | Label | Type |
|---|---|---|
| `locations_heading` | Heading Section | text |
| `campus_{1..3}_name` | Nama Kampus | text |
| `campus_{1..3}_desc` | Deskripsi | textarea |
| `campus_{1..3}_addr` | Alamat | text |
| `campus_{1..3}_map` | URL Google Maps | url |
| `campus_{1..3}_image_id` | Gambar | image |

### CTA Penutup
| Key | Label | Type |
|---|---|---|
| `cta_heading` | Heading | text |
| `cta_body` | Body | textarea |

## Tab 3 — Sosial Media

| Key | Label | Type |
|---|---|---|
| `social_facebook` | Facebook | url |
| `social_instagram` | Instagram | url |
| `social_youtube` | YouTube | url |
| `social_twitter` | X (Twitter) | url |
| `social_linkedin` | LinkedIn | url |
| `social_whatsapp` | WhatsApp | url |
| `social_telegram` | Telegram | url |
| `social_tiktok` | TikTok | url |

Kosongkan field yang tidak dipakai — `jalaversity_social_links()` otomatis skip platform tanpa URL.

## Tab 4 — Warna

| Key | Label | Type | Default |
|---|---|---|---|
| `color_primary` | Warna Primary | color | `#08422e` |
| `color_primary_dark` | Warna Primary Dark | color | `#06301f` |
| `color_primary_medium` | Warna Primary Medium | color | `#0a4730` |
| `color_accent` | Warna Accent | color | `#b68c2e` |
| `color_accent_dark` | Warna Accent Dark | color | `#a87e26` |
| `color_accent_light` | Warna Accent Light | color | `#e9c970` |

Dibaca `includes/enqueue.php` (`jalaversity_output_css_vars()`), di-output sebagai CSS custom property override di `<head>` — override CSS var default di `scss/front/_variables.scss` tanpa perlu rebuild CSS.

---

**Total**: ~83 field di 4 tab. Field bertipe `image` butuh `wp.media` uploader; field bertipe `color` butuh `wp-color-picker` — keduanya di-enqueue khusus di halaman settings ini saja (`admin_enqueue_scripts` sudah punya guard `str_contains($hook,'jalaversity')`).
