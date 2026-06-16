<?php
/**
 * Content — Post List
 *
 * Body bersama untuk index.php (blog index/is_home()), archive.php
 * (category/tag/date/post-type), dan search.php — strukturnya SAMA
 * (header judul kontekstual → loop card overlay+list → pagination →
 * fallback kosong), bedanya hanya cara menentukan judul header. Dipakai
 * bersama supaya index & archive konsisten, bukan 3x logic loop yang
 * terduplikasi (lihat docs/02-architecture.md §10).
 *
 * Sidebar default (get_sidebar(), SAMA dengan single post) dipasang di
 * sini lewat wrapper .content-with-sidebar.
 *
 * Komponen loop-context — baca query/loop global langsung.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_home() && ! is_front_page() ) {
	$posts_page_id = get_option( 'page_for_posts' );
	$title         = $posts_page_id ? get_the_title( $posts_page_id ) : __( 'Artikel Terbaru', 'jalaversity' );
} elseif ( is_search() ) {
	$title = sprintf(
		/* translators: %s: kata kunci pencarian */
		__( 'Hasil pencarian: %s', 'jalaversity' ),
		'<span>' . esc_html( get_search_query() ) . '</span>'
	);
} else {
	$title = get_the_archive_title();
}
?>

<div class="container archive-header">
	<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

	<h1 class="archive-header__title text-hero-sub">
		<?php echo wp_kses_post( $title ); ?>
	</h1>

	<?php the_archive_description( '<div class="archive-header__desc">', '</div>' ); ?>

	<?php if ( is_search() ) : ?>
		<?php get_template_part( 'template-parts/components/search-form' ); ?>
	<?php endif; ?>
</div>

<div class="container content-with-sidebar">
	<div class="content-with-sidebar__main">

		<?php if ( have_posts() ) : ?>

			<div class="archive-list">
				<?php
				$post_index = 0;
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content-card', null, [
						'variant' => ( 0 === $post_index ) ? 'overlay' : 'list',
					] );
					$post_index++;
				endwhile;
				?>
			</div>

			<?php get_template_part( 'template-parts/components/pagination' ); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content/content-none' ); ?>

		<?php endif; ?>

	</div>

	<?php get_sidebar(); ?>
</div>
