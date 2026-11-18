# Architecture — Mesin SEO Hybrid Kampus, Schema JSON-LD & GEO (`llms.txt`)

> Spesifikasi arsitektur untuk `includes/seo.php` (diimplementasikan penuh pada
> **Fase 06** sesuai **ADR-0006**), mengadaptasi pola terbaik `sekolahane` dan
> `newsane` untuk domain perguruan tinggi.

---

## 1. Prinsip Mode Hybrid (Mandiri vs Yoast/RankMath Booster)

Situs kampus pelanggan bisa berjalan **tanpa plugin SEO** (mode mandiri) atau **dengan Yoast SEO / RankMath / SEOPress aktif** (mode booster).
Deteksi dilakukan melalui helper `jalaversity_has_seo_plugin()`:
- **Jika plugin SEO pihak ketiga TIDAK aktif (Mode Mandiri):**
  - Theme mencetak `<meta name="description">`, `<link rel="canonical">`, `<meta name="robots">`, Open Graph (`og:*`), Twitter Card (`twitter:*`), dan blok `<script type="application/ld+json">` lengkap di `wp_head`.
- **Jika plugin SEO pihak ketiga AKTIF (Mode Booster):**
  - Theme **tidak** mencetak meta description/OG/canonical/BreadcrumbList JSON-LD ganda agar tidak terjadi duplikasi tag.
  - Theme tetap menyuntikkan pengayaan Schema spesifik kampus (`CollegeOrUniversity` & `Event` pada single `agenda`) jika belum disediakan oleh plugin SEO gratis.

---

## 2. Peta Schema.org JSON-LD Spesifik Perguruan Tinggi

| Konteks Halaman | Tipe `@type` Schema | Sumber Data |
|---|---|---|
| **Seluruh Halaman (Identitas Institusi)** | `CollegeOrUniversity` (subtipe `EducationalOrganization`) | Nama situs, URL, logo kustom, `contact_address`, `contact_phone`, `contact_email`, dan array `sameAs` dari seluruh URL sosial media di `jalaversity_options` |
| **Beranda (`is_front_page()`)** | `WebSite` + `SearchAction` (`Sitelinks Searchbox`) | `home_url('/')` + query `?s={search_term_string}` |
| **Single Post (`is_singular('post')`)** | `NewsArticle` / `Article` | Judul, ringkasan, `datePublished`, `dateModified`, penulis (`author`), editor ACF (`editor`), featured image (`jalaversity-large`), publisher `CollegeOrUniversity` |
| **Single Pengumuman (`is_singular('pengumuman')`)** | `SpecialAnnouncement` / `Article` | Judul, tanggal terbit, isi pengumuman, organisasi kampus |
| **Single Agenda (`is_singular('agenda')`)** | `Event` (`EducationEvent`) | `name`, `startDate` (`tanggal_mulai` + `jam_mulai`), `endDate` (`tanggal_selesai` + `jam_selesai`), `eventAttendanceMode` (`offline` → `OfflineEventAttendanceMode`, `online` → `OnlineEventAttendanceMode`, `hybrid` → `MixedEventAttendanceMode`), `location` (`Place` `lokasi` / `VirtualLocation` `link_online`), `performer` (repeater `narasumber`), `organizer` (`penyelenggara`) |
| **Sub-Halaman & Arsip** | `BreadcrumbList` | Sinkron dengan `template-parts/components/breadcrumb.php` |

---

## 3. Generative Engine Optimization (GEO) — Endpoint `/llms.txt`

Mengadopsi standar `sekolahane` dan `newsane`, `includes/seo.php` menyediakan rewrite endpoint `/llms.txt` (serta fallback `?llms=1`) yang menyajikan ringkasan terstruktur berformat Markdown untuk mesin pencari AI (ChatGPT, Gemini, Perplexity, Claude):
1. **Identitas & Profil Kampus** (Nama, deskripsi, alamat, telepon, email, jam operasional, link PMB).
2. **Daftar Fakultas & Program Studi** serta Lokasi Kampus.
3. **Pengumuman Resmi Terbaru** (CPT `pengumuman` berstatus `publish`, tanpa password).
4. **Agenda Kampus Mendatang** (CPT `agenda` berstatus `publish`, lengkap dengan tanggal & lokasi).
5. **Artikel & Berita Akademik Terbaru** (Post `publish`, tanpa password).
Output di-cache menggunakan transient (`jalaversity_llms_txt_cache`) dan otomatis di-flush saat ada perubahan post (`save_post`) atau pengaturan (`update_option_jalaversity_options`).
