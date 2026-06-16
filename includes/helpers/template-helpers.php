<?php
/**
 * Template Helpers
 *
 * Helper functions untuk section-label, data homepage (stats, faculty, PMB, riset, lokasi).
 * Semua data dibaca dari Settings Page (jalaversity_get_option) dengan fallback realistis
 * sehingga tema langsung bisa ditampilkan sebelum Settings Page (Fase 4) diisi.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Echo section label HTML.
 * Pattern: [line] TEKS [line] (center) atau [line] TEKS (left).
 *
 * @param string $text   Label teks (plain text, akan di-escape).
 * @param bool   $center Tambah garis kanan (untuk layout centered).
 * @param bool   $dark   Gunakan varian teks terang (di atas section gelap).
 */
function jalaversity_section_label( string $text, bool $center = false, bool $dark = false ): void {
	$class = 'section-label';
	if ( $dark ) {
		$class .= ' section-label--on-dark';
	}
	echo '<div class="' . esc_attr( $class ) . '">';
	echo '<span class="section-label__line" aria-hidden="true"></span>';
	echo esc_html( $text );
	if ( $center ) {
		echo '<span class="section-label__line" aria-hidden="true"></span>';
	}
	echo '</div>';
}

/**
 * Data statistik institusi (4 angka di stats bar).
 * Override via Settings Page option keys: stats_item_1_value, stats_item_1_label, dst.
 */
function jalaversity_get_stats(): array {
	return [
		[
			'icon'  => 'users',
			'value' => jalaversity_get_option( 'stats_1_value', '8.500+' ),
			'label' => jalaversity_get_option( 'stats_1_label', __( 'Mahasiswa Aktif', 'jalaversity' ) ),
		],
		[
			'icon'  => 'building-office',
			'value' => jalaversity_get_option( 'stats_2_value', '5' ),
			'label' => jalaversity_get_option( 'stats_2_label', __( 'Fakultas', 'jalaversity' ) ),
		],
		[
			'icon'  => 'book-open',
			'value' => jalaversity_get_option( 'stats_3_value', '18' ),
			'label' => jalaversity_get_option( 'stats_3_label', __( 'Program Studi', 'jalaversity' ) ),
		],
		[
			'icon'  => 'academic-cap',
			'value' => jalaversity_get_option( 'stats_4_value', '320+' ),
			'label' => jalaversity_get_option( 'stats_4_label', __( 'Dosen & Tenaga Ahli', 'jalaversity' ) ),
		],
	];
}

/**
 * Nilai institusi untuk section About (4 poin).
 */
function jalaversity_get_about_values(): array {
	return [
		[
			'icon'  => 'book-open',
			'title' => __( 'Integrasi Ilmu', 'jalaversity' ),
			'desc'  => __( 'Memadukan ilmu agama dan sains modern.', 'jalaversity' ),
		],
		[
			'icon'  => 'heart',
			'title' => __( 'Berbasis Akhlak', 'jalaversity' ),
			'desc'  => __( "Pembinaan karakter Qur'ani sejak awal.", 'jalaversity' ),
		],
		[
			'icon'  => 'globe',
			'title' => __( 'Wawasan Global', 'jalaversity' ),
			'desc'  => __( 'Jejaring dan program internasional.', 'jalaversity' ),
		],
		[
			'icon'  => 'beaker',
			'title' => __( 'Riset Terapan', 'jalaversity' ),
			'desc'  => __( 'Karya ilmiah yang berdampak nyata.', 'jalaversity' ),
		],
	];
}

/**
 * Data fakultas (6 item) untuk Card Grid (Faculty section di homepage,
 * juga dipakai untuk Prodi Grid di halaman Fakultas dengan parameter berbeda).
 *
 * Mengembalikan kontrak generik card-grid: icon, image_id, title, desc, link.
 * Lookup gambar per-item (`faculty_{id}_image_id`) dilakukan di sini, bukan
 * di template — agar template card-grid.php tetap pure render.
 *
 * Idealnya dari CPT; untuk saat ini dari array dengan Settings fallback.
 */
function jalaversity_get_faculties(): array {
	$faculties = [
		[
			'id'    => 1,
			'icon'  => 'academic-cap',
			'title' => __( 'Tarbiyah & Keguruan', 'jalaversity' ),
			'desc'  => __( 'PAI · PGMI · Manajemen Pendidikan Islam · PBA', 'jalaversity' ),
		],
		[
			'id'    => 2,
			'icon'  => 'building-library',
			'title' => __( 'Syariah & Hukum', 'jalaversity' ),
			'desc'  => __( 'Hukum Ekonomi Syariah · Hukum Keluarga Islam', 'jalaversity' ),
		],
		[
			'id'    => 3,
			'icon'  => 'book-open',
			'title' => __( "Ushuluddin & Dakwah", 'jalaversity' ),
			'desc'  => __( "Ilmu Al-Qur'an & Tafsir · KPI · BKI", 'jalaversity' ),
		],
		[
			'id'    => 4,
			'icon'  => 'building-office',
			'title' => __( 'Ekonomi & Bisnis Islam', 'jalaversity' ),
			'desc'  => __( 'Perbankan Syariah · Manajemen Bisnis Syariah · Akuntansi Syariah', 'jalaversity' ),
		],
		[
			'id'    => 5,
			'icon'  => 'newspaper',
			'title' => __( 'Adab & Humaniora', 'jalaversity' ),
			'desc'  => __( 'Bahasa & Sastra Arab · Sejarah Peradaban Islam', 'jalaversity' ),
		],
		[
			'id'    => 6,
			'icon'  => 'star',
			'title' => __( 'Pascasarjana', 'jalaversity' ),
			'desc'  => __( 'Magister PAI · Magister Hukum Islam · Doktor Studi Islam', 'jalaversity' ),
		],
	];

	foreach ( $faculties as &$faculty ) {
		$faculty['image_id'] = (int) jalaversity_get_option( 'faculty_' . $faculty['id'] . '_image_id', 0 );
		$faculty['link']     = [
			'label' => __( 'Lihat program studi', 'jalaversity' ),
			'url'   => jalaversity_get_option( 'faculty_' . $faculty['id'] . '_url', '#' ),
		];
	}

	return $faculties;
}

/**
 * Langkah-langkah PMB (4 step) untuk PMB section.
 */
function jalaversity_get_pmb_steps(): array {
	return [
		[
			'number' => '01',
			'title'  => __( 'Daftar Online', 'jalaversity' ),
			'desc'   => __( 'Buat akun dan isi formulir di portal PMB resmi.', 'jalaversity' ),
		],
		[
			'number' => '02',
			'title'  => __( 'Unggah Berkas', 'jalaversity' ),
			'desc'   => __( 'Lengkapi dokumen dan bukti pembayaran pendaftaran.', 'jalaversity' ),
		],
		[
			'number' => '03',
			'title'  => __( 'Seleksi & Ujian', 'jalaversity' ),
			'desc'   => __( "Tes potensi akademik, baca Al-Qur'an, dan wawancara.", 'jalaversity' ),
		],
		[
			'number' => '04',
			'title'  => __( 'Pengumuman', 'jalaversity' ),
			'desc'   => __( 'Hasil seleksi diumumkan dan registrasi ulang.', 'jalaversity' ),
		],
	];
}

/**
 * Item riset untuk Research section (3 item).
 */
function jalaversity_get_research_items(): array {
	return [
		[
			'icon'  => 'beaker',
			'title' => __( 'Pusat Studi & Laboratorium', 'jalaversity' ),
			'desc'  => __( '9 pusat studi lintas disiplin keislaman dan sains.', 'jalaversity' ),
		],
		[
			'icon'  => 'document-text',
			'title' => __( 'Jurnal Ilmiah Terakreditasi', 'jalaversity' ),
			'desc'  => __( '14 jurnal terindeks SINTA dan Scopus.', 'jalaversity' ),
		],
		[
			'icon'  => 'heart',
			'title' => __( 'Pengabdian Masyarakat', 'jalaversity' ),
			'desc'  => __( 'Program dakwah dan pemberdayaan umat berkelanjutan.', 'jalaversity' ),
		],
	];
}

/**
 * Data lokasi kampus (3 kampus) untuk Card Grid (Locations section).
 *
 * Mengembalikan kontrak generik card-grid: image_id, title, desc, address, link.
 * Lookup gambar per-item (`campus_{id}_image_id`) dilakukan di sini, bukan
 * di template — agar template card-grid.php tetap pure render.
 */
function jalaversity_get_campuses(): array {
	$campuses = [
		[
			'id'      => 1,
			'title'   => jalaversity_get_option( 'campus_1_name', __( 'Kampus Pusat', 'jalaversity' ) ),
			'desc'    => jalaversity_get_option( 'campus_1_desc', __( 'Rektorat, Fakultas Tarbiyah & Syariah, Perpustakaan Pusat.', 'jalaversity' ) ),
			'address' => jalaversity_get_option( 'campus_1_addr', __( 'Jl. Pendidikan No. 1, Subang', 'jalaversity' ) ),
			'map'     => jalaversity_get_option( 'campus_1_map', '' ),
		],
		[
			'id'      => 2,
			'title'   => jalaversity_get_option( 'campus_2_name', __( 'Kampus 2 Terpadu', 'jalaversity' ) ),
			'desc'    => jalaversity_get_option( 'campus_2_desc', __( 'Fakultas Ekonomi & Bisnis Islam, Pusat Inkubator.', 'jalaversity' ) ),
			'address' => jalaversity_get_option( 'campus_2_addr', __( 'Jl. Pesantren No. 25, Subang', 'jalaversity' ) ),
			'map'     => jalaversity_get_option( 'campus_2_map', '' ),
		],
		[
			'id'      => 3,
			'title'   => jalaversity_get_option( 'campus_3_name', __( 'Kampus Pascasarjana', 'jalaversity' ) ),
			'desc'    => jalaversity_get_option( 'campus_3_desc', __( 'Program Magister & Doktor, Pusat Riset Lanjut.', 'jalaversity' ) ),
			'address' => jalaversity_get_option( 'campus_3_addr', __( 'Jl. Cendekia No. 7, Subang', 'jalaversity' ) ),
			'map'     => jalaversity_get_option( 'campus_3_map', '' ),
		],
	];

	foreach ( $campuses as &$campus ) {
		$campus['image_id'] = (int) jalaversity_get_option( 'campus_' . $campus['id'] . '_image_id', 0 );
		$campus['link']     = $campus['map'] ? [
			'label'    => __( 'Lihat di peta', 'jalaversity' ),
			'url'      => $campus['map'],
			'external' => true,
		] : null;
	}

	return $campuses;
}

/**
 * Susun $args lengkap untuk hero-page.php varian 'home' (homepage).
 * Mengumpulkan semua option read + highlight word + floating badge
 * menjadi satu array siap pakai, agar page-home.php tetap murni komposisi.
 */
function jalaversity_get_hero_home_args(): array {
	return [
		'variant'     => 'home',
		'badge'       => jalaversity_get_option( 'hero_tagline', get_bloginfo( 'name' ) ),
		'heading'     => jalaversity_get_option( 'hero_heading', __( 'Menuntut Ilmu, Menebar Manfaat untuk Peradaban', 'jalaversity' ) ),
		'highlight'   => jalaversity_get_option( 'hero_highlight', __( 'Menebar Manfaat', 'jalaversity' ) ),
		'lead'        => jalaversity_get_option( 'hero_lead', __( 'Membentuk cendekiawan muslim yang berakhlak mulia, unggul dalam keilmuan, dan berdaya saing global di tengah dinamika zaman.', 'jalaversity' ) ),
		'image_id'    => (int) jalaversity_get_option( 'hero_image_id', 0 ),
		'image_alt'   => jalaversity_get_option( 'hero_image_alt', get_bloginfo( 'name' ) ),
		'trust_items' => [
			jalaversity_get_option( 'hero_trust_1', __( 'Terakreditasi Unggul', 'jalaversity' ) ),
			jalaversity_get_option( 'hero_trust_2', __( '18 Program Studi', 'jalaversity' ) ),
			jalaversity_get_option( 'hero_trust_3', __( 'Beasiswa Tahfizh', 'jalaversity' ) ),
		],
		'floating_badge' => [
			'icon'  => 'trophy',
			'label' => jalaversity_get_option( 'accreditation_label', __( 'Akreditasi Institusi', 'jalaversity' ) ),
			'value' => jalaversity_get_option( 'accreditation_value', __( 'UNGGUL', 'jalaversity' ) ),
		],
	];
}

/**
 * Susun $args lengkap untuk content-media.php — section About (Selayang Pandang).
 */
function jalaversity_get_about_args(): array {
	return [
		'label'          => __( 'Selayang Pandang', 'jalaversity' ),
		'heading'        => jalaversity_get_option( 'about_heading', __( 'Memadukan Tradisi Keilmuan Islam dengan Wawasan Modern', 'jalaversity' ) ),
		'heading_id'     => 'about-heading',
		'body'           => jalaversity_get_option( 'about_body', __( 'Kami hadir sebagai perguruan tinggi keagamaan yang menyeimbangkan kedalaman ilmu agama, penguasaan sains, dan pembentukan akhlak. Kami menyiapkan lulusan yang siap mengabdi untuk umat, bangsa, dan kemanusiaan.', 'jalaversity' ) ),
		'image_id'       => (int) jalaversity_get_option( 'about_image_id', 0 ),
		'image_position' => 'left',
		'image_radius'   => 'about',
		'corner_badge'   => [
			'value'    => jalaversity_get_option( 'about_years', '28+' ),
			'label'    => jalaversity_get_option( 'about_years_label', __( 'Tahun mendidik generasi sejak 1998', 'jalaversity' ) ),
			'position' => 'top-right',
			'variant'  => 'dark',
		],
		'items'        => jalaversity_get_about_values(),
		'items_layout' => 'grid',
		'link'         => [
			'label' => jalaversity_get_option( 'about_link_label', __( 'Pelajari profil institusi', 'jalaversity' ) ),
			'url'   => jalaversity_get_option( 'about_link_url', '#' ),
		],
	];
}

/**
 * Susun $args lengkap untuk content-media.php — section Riset & Inovasi.
 */
function jalaversity_get_research_args(): array {
	$heading = jalaversity_get_option( 'research_heading', __( 'Ilmu yang Memberi Solusi bagi Umat', 'jalaversity' ) );

	return [
		'label'          => __( 'Riset & Inovasi', 'jalaversity' ),
		'heading'        => $heading,
		'heading_id'     => 'research-heading',
		'bg'             => 'surface',
		'body'           => jalaversity_get_option( 'research_body', __( 'Melalui pusat studi, jurnal ilmiah, dan program pengabdian masyarakat, sivitas akademika menghadirkan karya yang relevan dan berdampak.', 'jalaversity' ) ),
		'image_id'       => (int) jalaversity_get_option( 'research_image_id', 0 ),
		'image_alt'      => $heading,
		'image_position' => 'right',
		'corner_badge'   => [
			'value'    => jalaversity_get_option( 'research_badge_value', __( '14 Jurnal', 'jalaversity' ) ),
			'label'    => jalaversity_get_option( 'research_badge_label', __( 'Terakreditasi SINTA & Scopus', 'jalaversity' ) ),
			'position' => 'bottom-left',
			'variant'  => 'gold',
		],
		'items'        => jalaversity_get_research_items(),
		'items_layout' => 'rows',
	];
}
