<?php
/**
 * Content — Generic Page
 *
 * Entry header (judul + featured image opsional) + isi konten
 * (.entry-content, sharing style typography yang sama dengan single post).
 * Dipakai oleh page.php — fallback untuk Page yang TIDAK memakai template
 * "Halaman Beranda" atau "Halaman Dinamis".
 *
 * Komponen loop-context — baca get_the_*()/the_content() langsung.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'single-page' ); ?>>

	<div class="container single-page__container">

		<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

		<header class="single-page__header">
			<h1 class="single-page__title text-hero-sub"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
		<div class="single-page__image">
			<?php the_post_thumbnail( 'jalaversity-medium', [ 'class' => 'single-page__img', 'loading' => 'eager' ] ); ?>
		</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php the_content(); ?>
		</div>

	</div>

</article>
