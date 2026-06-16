<?php
/**
 * Post Navigation — Prev/Next
 *
 * 2 kartu: post sebelumnya dan berikutnya (kronologis, get_previous_post()/
 * get_next_post() native WP). Komponen loop-context — baca post saat ini
 * langsung, bukan via $args (lihat docs/02-architecture.md §10).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prev = get_previous_post();
$next = get_next_post();

if ( ! $prev && ! $next ) {
	return;
}
?>
<nav class="post-nav" aria-label="<?php esc_attr_e( 'Navigasi artikel', 'jalaversity' ); ?>">
	<?php if ( $prev ) : ?>
	<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="post-nav__item post-nav__item--prev">
		<span class="post-nav__direction">
			<?php jalaversity_icon_e( 'arrow-left', 16 ); ?>
			<?php esc_html_e( 'Sebelumnya', 'jalaversity' ); ?>
		</span>
		<span class="post-nav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
	</a>
	<?php else : ?>
	<span class="post-nav__item post-nav__item--empty" aria-hidden="true"></span>
	<?php endif; ?>

	<?php if ( $next ) : ?>
	<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="post-nav__item post-nav__item--next">
		<span class="post-nav__direction">
			<?php esc_html_e( 'Berikutnya', 'jalaversity' ); ?>
			<?php jalaversity_icon_e( 'arrow-right', 16 ); ?>
		</span>
		<span class="post-nav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
	</a>
	<?php endif; ?>
</nav>
