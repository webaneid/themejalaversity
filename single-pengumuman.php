<?php
/**
 * Single Pengumuman Template (CPT: pengumuman)
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/content/content-pengumuman' );

endwhile;

get_footer();
