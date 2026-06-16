<?php
/**
 * Template Name: Halaman Dinamis
 *
 * Page builder berbasis ACF Pro Flexible Content. Admin menyusun halaman
 * dengan menambah, menghapus, dan men-drag-reorder section pada field
 * "Sections" di editor halaman ini — termasuk repeater untuk section yang
 * jumlah item-nya tidak tetap (mis. Prodi/Fakultas di layout Card Grid).
 *
 * Skema field didaftarkan di includes/acf/acf-fields.php. Setiap layout
 * dirender oleh includes/acf/acf-render.php, yang memanggil komponen
 * generik yang sama dengan page-templates/page-home.php
 * (lihat docs/02-architecture.md §9).
 *
 * Cara pakai: Buat halaman baru di WP Admin → pilih Template "Halaman
 * Dinamis" pada Page Attributes → isi field "Sections" yang muncul.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_rows( 'page_sections' ) ) {
	while ( have_rows( 'page_sections' ) ) {
		the_row();
		jalaversity_render_dynamic_section( get_row_layout() );
	}
}

get_footer();
