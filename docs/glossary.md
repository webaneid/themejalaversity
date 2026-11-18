# Glossary — Istilah Domain Jalaversity

| Istilah | Arti |
|---|---|
| **Jalaversity Settings (`jalaversity_options`)** | Satu-satunya key di tabel `wp_options` tempat seluruh pengaturan global theme disimpan sebagai array ter-serialisasi (Kontak & PMB, Sosial Media, Warna Brand, Tim Layanan WA, dan GitHub Updater Token). Dibaca melalui helper ber-cache statis `jalaversity_get_option()`. |
| **Halaman Dinamis (`page-templates/page-dynamic.php`)** | Custom Page Template berbasis ACF Pro Flexible Content (`page_sections`) dengan 11 layout yang memungkinkan admin kampus menyusun halaman (Beranda, Fakultas, Prodi, Profil) secara drag-and-drop tanpa membuat file template baru. |
| **Pure-`$args` Component** | Komponen di `template-parts/components/*.php` yang hanya menerima data lewat parameter `$args` dari `get_template_part()`, tidak bergantung pada state global WordPress atau Options API secara langsung. |
| **Loop-Context Component** | Komponen di `template-parts/content/*.php` yang berjalan di dalam WordPress Loop (`the_post()`) dan membaca fungsi template standar (`the_title()`, `the_permalink()`, `get_the_date()`). |
| **Render Bridge (`includes/acf/acf-render.php`)** | Lapisan jembatan yang membaca `get_sub_field()` dari tiap layout ACF Flexible Content, menormalisasi datanya ke kontrak `$args`, lalu memanggil komponen generik di `template-parts/components/`. |
| **PMB (Penerimaan Mahasiswa Baru)** | Seksi dan alur promosi pendaftaran mahasiswa baru kampus (mencakup gelombang pendaftaran, brosur PDF, langkah pendaftaran `numbered-steps`, dan tombol CTA). |
| **CPT `agenda`** | Custom Post Type untuk kegiatan/acara kampus (seminar, wisuda, ujian, kuliah umum) lengkap dengan field ACF `group_jalaversity_agenda` (tanggal/jam mulai-selesai, tipe lokasi offline/online/hybrid, tempat/link meeting, narasumber repeater, penyelenggara, kuota, link pendaftaran). |
| **CPT `pengumuman`** | Custom Post Type untuk pengumuman resmi akademik/kampus yang ditampilkan di kotak Pengumuman beranda dan arsip pengumuman. |
| **Berita Utama (`is_featured`)** | Field ACF `true_false` pada post standar untuk menandai artikel sorotan yang tampil di bagian atas `page-templates/page-blog.php`. |
| **Tim Layanan (`tim_layanan_contacts`)** | Daftar petugas admisi/layanan kampus (nama, jabatan, nomor WhatsApp, foto) yang disimpan di `jalaversity_options` dan dirender sebagai floating WhatsApp widget (`template-parts/footer/wa-widget.php`). |
| **Girih Pattern** | Ornamen geometris Islam (`@define-mixin girih-bg` / `images/bg-pattern.svg`) yang menjadi ciri visual pada section berlatar hijau gelap (`--color-primary` / `--color-primary-dark`). |
| **Fase** | Satu unit kerja terukur menurut `docs/SOP.md`. |
| **ADR** | Architecture Decision Record — catatan keputusan arsitektur penting di `docs/decisions/`. |
