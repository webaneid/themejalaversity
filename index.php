<?php
/**
 * Main Index Template (Fallback)
 *
 * Fallback WordPress wajib. Juga berfungsi sebagai blog index (is_home(),
 * dipakai saat tidak ada front page statis dan tidak ada home.php) —
 * strukturnya SAMA dengan archive.php dan search.php, lewat
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
