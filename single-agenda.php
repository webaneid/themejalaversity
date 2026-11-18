<?php
/**
 * Single Agenda Template (CPT: agenda)
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/content/content-agenda' );

endwhile;

get_footer();
