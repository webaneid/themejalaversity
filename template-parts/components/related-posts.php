<?php
/**
 * Related Posts
 *
 * Post terkait untuk single post saat ini, via jalaversity_get_related_posts()
 * (strategi tags → categories → random fallback). Heading menyesuaikan
 * sumber match, sama seperti perilaku jalawarta.
 *
 * Komponen loop-context — baca post saat ini langsung (lihat
 * docs/02-architecture.md §10), bukan pure-$args.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_single() ) {
	return;
}

$result = jalaversity_get_related_posts( get_the_ID(), 3 );
$query  = $result['query'];

if ( ! $query->have_posts() ) {
	return;
}

$heading = ( 'random' === $result['source'] )
	? __( 'Konten Lain', 'jalaversity' )
	: __( 'Konten Terkait', 'jalaversity' );
?>
<section class="related-posts section-py" aria-labelledby="related-posts-heading">
	<div class="container">
		<?php get_template_part( 'template-parts/components/section-header', null, [
			'heading'    => $heading,
			'heading_id' => 'related-posts-heading',
			'center'     => false,
		] ); ?>

		<div class="post-cards-grid">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php get_template_part( 'template-parts/content/content-card', null, [ 'variant' => 'overlay' ] ); ?>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
