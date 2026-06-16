<?php
/**
 * Sidebar Template
 *
 * Dipanggil via get_sidebar() dari content-post-list.php (index/archive/
 * tag/category/author/search) dan content-single.php — sidebar default
 * yang SAMA di semua context (lihat docs/02-architecture.md §10).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/components/sidebar' );
