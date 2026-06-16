<?php
/**
 * Single Post Template
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	jalaversity_track_post_view( get_the_ID() );

	get_template_part( 'template-parts/content/content-single' );

endwhile;

get_footer();
