<?php
/**
 * Archive Template
 *
 * Category/tag/date/post-type archive (WP template hierarchy fallback —
 * tidak ada category.php/tag.php terpisah). Struktur SAMA dengan index.php
 * (blog index) dan search.php, lewat
 * template-parts/content/content-post-list.php (lihat docs/02-architecture.md §10).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/content/content-post-list' );

get_footer();
