<?php
/**
 * Generic Page Template
 *
 * Fallback untuk Page yang tidak memakai template "Halaman Beranda"
 * (page-templates/page-home.php), "Halaman Dinamis" (page-dynamic.php),
 * atau "Halaman Blog" (page-blog.php).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/content/content-page' );

endwhile;

get_footer();
