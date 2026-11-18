# ADR-0006: Mesin SEO Hybrid Kampus (`CollegeOrUniversity`, `Event`) & Endpoint GEO `/llms.txt`

**Status:** Accepted
**Tanggal:** 2026-11-18

## Context
File `includes/seo.php` di `jalaversity` saat ini masih berupa stub kosong (`GAP-13`).
Sebagai theme untuk perguruan tinggi, situs pelanggan membutuhkan:
1. SEO mandiri (title, meta description, Open Graph, Twitter Card, canonical,
   robots noindex pada search/404) apabila kampus tidak memasang plugin SEO.
2. Kompatibilitas penuh (tanpa duplikasi tag meta/schema) apabila kampus
   memasang Yoast SEO atau RankMath.
3. Structured data Schema.org spesifik pendidikan tinggi (`CollegeOrUniversity`,
   `WebSite` + `SearchAction`, `Article`, `BreadcrumbList`, dan `Event` untuk
   CPT `agenda`).
4. Endpoint `/llms.txt` (Generative Engine Optimization / GEO) seperti di
   `sekolahane` dan `newsane` agar informasi kampus, fakultas, pengumuman, dan
   agenda mudah dikutip oleh mesin pencari AI.

## Decision
Mengimplementasikan `includes/seo.php` pada **Fase 06** sesuai spesifikasi
`docs/architecture/architecture-seo.md`:
1. Mode otomatis: mandiri saat tidak ada plugin SEO aktif, dan beralih ke mode
   *booster* (menonaktifkan meta/OG/canonical bawaan theme) saat Yoast SEO /
   RankMath / SEOPress terdeteksi aktif.
2. Menyediakan JSON-LD `CollegeOrUniversity` (identitas kampus + `sameAs`
   sosmed), `Event` pada `is_singular( 'agenda' )`, `Article` pada
   `is_singular( 'post' )`, serta harmonisasi microdata di `breadcrumb.php`.
3. Menyediakan endpoint `/llms.txt` ber-cache transient yang merangkum profil
   kampus, fakultas, pengumuman, agenda, dan artikel terbaru yang berstatus
   `publish` (dan tidak dilindungi kata sandi).

## Alternatif yang Dipertimbangkan
- Mewajibkan plugin SEO berbayar untuk Schema kampus & Event — ditolak karena
  tujuan utama `jalaversity` adalah menyediakan SEO & Schema kampus siap pakai
  sejak theme diaktifkan.

## Konsekuensi
- Situs kampus langsung memiliki struktur SEO, Open Graph, Schema.org
  (`CollegeOrUniversity` & `Event`), dan `/llms.txt` kelas produksi tanpa
  konflik dengan Yoast/RankMath.
