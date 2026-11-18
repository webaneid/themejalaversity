<?php
/**
 * Content — Single Pengumuman (CPT: pengumuman)
 *
 * Menampilkan pengumuman resmi kampus dengan badge "Pengumuman Resmi",
 * tanggal publikasi, waktu baca, featured image (opsional), isi pengumuman,
 * tombol bagikan, navigasi post, dan sidebar default.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'single-post single-pengumuman' ); ?>>

	<div class="container content-with-sidebar">
		<div class="content-with-sidebar__main single-post__container">

			<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

			<header class="single-post__header">
				<div class="post-meta-line">
					<a href="<?php echo esc_url( (string) get_post_type_archive_link( 'pengumuman' ) ); ?>" class="post-meta-line__category">
						<?php esc_html_e( 'Pengumuman Resmi', 'jalaversity' ); ?>
					</a>
					<span class="post-meta-line__sep" aria-hidden="true">-</span>
					<time class="post-meta-line__date" datetime="<?php echo esc_attr( (string) get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( (string) get_the_date( 'l, j F Y' ) ); ?>
					</time>
				</div>

				<h1 class="single-post__title text-hero-sub"><?php the_title(); ?></h1>

				<div class="post-meta">
					<span class="post-meta__item">
						<?php jalaversity_icon_e( 'clock', 15 ); ?>
						<?php
						printf(
							/* translators: %d: estimasi waktu baca dalam menit */
							esc_html__( '%d menit baca', 'jalaversity' ),
							(int) jalaversity_reading_time()
						);
						?>
					</span>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-post__image">
				<?php
				the_post_thumbnail(
					'jalaversity-medium',
					[
						'class'         => 'single-post__img',
						'loading'       => false,
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(max-width: 767px) 92vw, 800px',
					]
				);
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

</article>
