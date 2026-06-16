<?php
/**
 * Template Name: Halaman Beranda
 *
 * Homepage template. Urutan section mengikuti design reference:
 * Hero → Stats Bar → Tentang → Fakultas → PMB → Berita → Riset → Lokasi → CTA.
 *
 * Murni komposisi: setiap section adalah komponen generik dari
 * template-parts/components/ dipanggil dengan $args dari template-helpers.php
 * (lihat docs/02-architecture.md — "Generic Component Architecture").
 * pmb-section.php dan news-section.php tetap bespoke karena layout-nya
 * unik (belum ada halaman lain yang butuh pola serupa).
 *
 * Untuk menggunakan: Buat halaman baru di WP Admin, pilih template "Halaman Beranda",
 * lalu set halaman tersebut sebagai "Front page" di Settings → Reading.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php /* Hero — dark green, girih, search (varian 'home') */ ?>
<?php get_template_part( 'template-parts/components/hero-page', null, jalaversity_get_hero_home_args() ); ?>

<?php /* Stats bar — overlap hero via negative margin-top */ ?>
<div class="stats-bar-wrap">
	<div class="container">
		<?php get_template_part( 'template-parts/components/stats-bar' ); ?>
	</div>
</div>

<?php /* Tentang (Selayang Pandang) — cream bg, image kiri */ ?>
<?php get_template_part( 'template-parts/components/content-media', null, jalaversity_get_about_args() ); ?>

<?php /* Fakultas & Program Studi — white bg, card grid icon overlay */ ?>
<section class="faculty-section section-py" aria-labelledby="faculty-heading">
	<div class="container">
		<?php get_template_part( 'template-parts/components/card-grid', null, [
			'label'      => __( 'Program Akademik', 'jalaversity' ),
			'heading'    => jalaversity_get_option( 'faculty_heading', __( 'Fakultas & Program Studi', 'jalaversity' ) ),
			'heading_id' => 'faculty-heading',
			'lead'       => jalaversity_get_option( 'faculty_subhead', __( 'Lima fakultas dan program pascasarjana dengan 18 program studi terakreditasi, dirancang untuk membentuk ahli yang kompeten dan berintegritas.', 'jalaversity' ) ),
			'items'      => jalaversity_get_faculties(),
		] ); ?>
	</div>
</section>

<?php /* PMB — dark green, girih (bespoke composition, pakai numbered-steps di dalamnya) */ ?>
<?php get_template_part( 'template-parts/content/pmb-section' ); ?>

<?php /* Berita + Pengumuman + Agenda — cream bg (bespoke, lihat catatan di changelog) */ ?>
<?php get_template_part( 'template-parts/content/news-section' ); ?>

<?php /* Riset & Inovasi — white bg, image kanan */ ?>
<?php get_template_part( 'template-parts/components/content-media', null, jalaversity_get_research_args() ); ?>

<?php /* Lokasi Kampus — cream bg, card grid foto */ ?>
<section class="locations-section section-py" aria-labelledby="locations-heading">
	<div class="container">
		<?php get_template_part( 'template-parts/components/card-grid', null, [
			'heading'        => jalaversity_get_option( 'locations_heading', __( 'Tiga Kampus, Satu Visi Keilmuan', 'jalaversity' ) ),
			'heading_id'     => 'locations-heading',
			'label'          => __( 'Lokasi Kampus', 'jalaversity' ),
			'items'          => jalaversity_get_campuses(),
			'min_card_width' => '300px',
		] ); ?>
	</div>
</section>

<?php /* CTA Banner — cream bg, inner card dark green */ ?>
<section class="cta-section">
	<div class="container">
		<?php get_template_part( 'template-parts/components/cta-banner' ); ?>
	</div>
</section>

<?php get_footer(); ?>
