<?php
/**
 * Search Results Template
 *
 * Struktur SAMA dengan index.php dan archive.php, lewat
 * template-parts/content/content-post-list.php — yang juga menampilkan
 * search form di header saat is_search() (lihat docs/02-architecture.md §10).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/content/content-post-list' );

get_footer();
