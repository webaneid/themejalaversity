<?php
/**
 * Content — Single Post
 *
 * Body lengkap single post: meta line seragam (kategori - tanggal, via
 * jalaversity_post_meta_line()), H1, reading-time/views, author+editor,
 * featured image, isi konten (.entry-content), share buttons, related
 * posts, navigasi prev/next. Tidak ada comment form (dimatikan sesuai
 * keputusan — lihat docs/02-architecture.md §10).
 *
 * Komponen loop-context — beroperasi di dalam single post loop, baca
 * get_the_*()/the_content() langsung, bukan via $args.
 *
 * Sidebar default (get_sidebar(), SAMA dengan archive/index) dipasang
 * lewat wrapper .content-with-sidebar — related-posts tetap full-width
 * di luar wrapper itu (butuh lebar penuh untuk grid 3 kartu).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$editor = function_exists( 'get_field' ) ? get_field( 'editor' ) : null;
?>
<article <?php post_class( 'single-post' ); ?>>

	<div class="container content-with-sidebar">
	<div class="content-with-sidebar__main single-post__container">

		<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

		<header class="single-post__header">
			<?php jalaversity_post_meta_line(); ?>

			<h1 class="single-post__title text-hero-sub"><?php the_title(); ?></h1>

			<div class="post-meta">
				<span class="post-meta__item">
					<?php jalaversity_icon_e( 'clock', 15 ); ?>
					<?php
					printf(
						/* translators: %d: estimasi waktu baca dalam menit */
						esc_html__( '%d menit baca', 'jalaversity' ),
						jalaversity_reading_time()
					);
					?>
				</span>
				<span class="post-meta__item">
					<?php jalaversity_icon_e( 'eye', 15 ); ?>
					<?php
					printf(
						/* translators: %s: jumlah views terformat */
						esc_html__( '%s views', 'jalaversity' ),
						esc_html( number_format_i18n( jalaversity_get_views( get_the_ID() ) ) )
					);
					?>
				</span>
			</div>

			<div class="post-byline">
				<span class="post-byline__avatar">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
				<span class="post-byline__text">
					<strong><?php the_author(); ?></strong>
					<?php if ( $editor && ! empty( $editor['ID'] ) ) : ?>
					<span class="post-byline__editor">
						<?php
						printf(
							/* translators: %s: nama editor */
							esc_html__( 'Diedit oleh %s', 'jalaversity' ),
							esc_html( $editor['display_name'] ?? '' )
						);
						?>
					</span>
					<?php endif; ?>
				</span>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
		<div class="single-post__image">
			<?php the_post_thumbnail( 'jalaversity-medium', [ 'class' => 'single-post__img', 'loading' => 'eager' ] ); ?>
			<?php
			$caption = get_the_post_thumbnail_caption();
			if ( $caption ) :
			?>
			<figcaption class="single-post__caption"><?php echo esc_html( $caption ); ?></figcaption>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php the_content(); ?>
		</div>

		<?php get_template_part( 'template-parts/components/share-buttons' ); ?>

		<?php get_template_part( 'template-parts/components/post-nav' ); ?>

	</div>
	<?php get_sidebar(); ?>
	</div>

	<?php get_template_part( 'template-parts/components/related-posts' ); ?>

</article>
