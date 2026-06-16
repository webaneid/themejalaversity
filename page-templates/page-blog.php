<?php
/**
 * Template Name: Halaman Blog
 *
 * Landing page artikel — section "Berita Utama" (post yang ditandai field
 * ACF `is_featured`) + daftar artikel terbaru (paginated, exclude post yang
 * sudah tampil di Berita Utama). Header/footer tetap default jalaversity.
 *
 * Untuk dipakai: buat halaman baru → pilih Template "Halaman Blog" → bisa
 * juga di-assign sebagai "Posts page" di Settings → Reading kalau mau jadi
 * landing artikel utama situs.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Set up $post untuk halaman ini sendiri (single Page di main query).
if ( have_posts() ) :
	the_post();
endif;

get_template_part( 'template-parts/components/hero-page', null, [
	'variant'         => 'subpage',
	'heading'         => get_the_title(),
	'show_breadcrumb' => true,
] );

// ── Berita Utama (post dengan field ACF is_featured) ──────────────────
$featured_query = new WP_Query( [
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'meta_key'            => 'is_featured',
	'meta_value'          => '1',
	'ignore_sticky_posts' => true,
] );

$featured_ids = [];

if ( $featured_query->have_posts() ) :
	$featured_ids = wp_list_pluck( $featured_query->posts, 'ID' );
	?>
	<section class="section-py" aria-labelledby="featured-posts-heading">
		<div class="container">
			<?php get_template_part( 'template-parts/components/section-header', null, [
				'label'      => __( 'Berita Utama', 'jalaversity' ),
				'heading'    => __( 'Sorotan Terkini', 'jalaversity' ),
				'heading_id' => 'featured-posts-heading',
				'center'     => false,
			] ); ?>
			<div class="post-cards-grid">
				<?php
				while ( $featured_query->have_posts() ) :
					$featured_query->the_post();
					get_template_part( 'template-parts/content/content-card', null, [ 'variant' => 'overlay' ] );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
// ── Artikel Terbaru (exclude yang sudah tampil di Berita Utama) ───────
$paged = max( 1, (int) get_query_var( 'paged' ) );

$latest_query = new WP_Query( [
	'post_type'           => 'post',
	'posts_per_page'      => 9,
	'paged'               => $paged,
	'post__not_in'        => $featured_ids,
	'ignore_sticky_posts' => true,
] );
?>

<section class="section-py" aria-labelledby="latest-posts-heading">
	<div class="container">
		<?php get_template_part( 'template-parts/components/section-header', null, [
			'heading'    => __( 'Artikel Terbaru', 'jalaversity' ),
			'heading_id' => 'latest-posts-heading',
			'center'     => false,
		] ); ?>

		<?php if ( $latest_query->have_posts() ) : ?>
			<div class="archive-list">
				<?php
				while ( $latest_query->have_posts() ) :
					$latest_query->the_post();
					get_template_part( 'template-parts/content/content-card', null, [ 'variant' => 'list' ] );
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php get_template_part( 'template-parts/components/pagination', null, [ 'query' => $latest_query ] ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content-none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
