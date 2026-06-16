<?php
/**
 * Content Card — Post (Generic, 4 varian)
 *
 * Satu file, 4 varian via $args['variant'] — bukan file terpisah, konsisten
 * dengan aturan "beda jadi parameter, bukan file baru" (docs/02-architecture.md §8).
 * Bagian yang berulang (judul, meta, gambar) dipecah jadi helper kecil di
 * includes/helpers/post-helpers.php (jalaversity_card_title/_meta/_thumbnail)
 * — dipakai ulang oleh semua varian, bukan ditulis ulang per varian. Tiap
 * varian = susunan berbeda dari helper yang sama + CSS modifier tipis,
 * bukan markup/CSS baru dari nol.
 *
 * - 'overlay' : gambar full-bleed jadi background, judul+meta overlay di
 *               bawah gambar dengan gradient gelap.
 * - 'list'    : gambar kotak ±30% lebar di kiri, judul+meta di kanan.
 * - 'klasik'  : gambar penuh di atas (stack vertikal), judul+meta+excerpt
 *               di bawahnya.
 * - 'title'   : tanpa gambar sama sekali, hanya judul+meta (paling ringan).
 *
 * Komponen loop-context — beroperasi di dalam WP loop (the_post() sudah
 * jalan), baca get_the_*() langsung. $args HANYA untuk opsi tampilan
 * (variant), bukan data post itu sendiri (lihat docs/02-architecture.md §10).
 *
 * @param string $args['variant'] 'overlay' | 'list' | 'klasik' | 'title' (default 'list').
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$valid_variants = [ 'overlay', 'list', 'klasik', 'title' ];
$variant         = in_array( $args['variant'] ?? 'list', $valid_variants, true ) ? $args['variant'] : 'list';
?>
<article <?php post_class( 'card card--post card--post-' . $variant ); ?>>

	<?php if ( 'overlay' === $variant ) : ?>

		<?php jalaversity_card_thumbnail( 'jalaversity-thumbnail', 32 ); ?>
		<div class="card__overlay-body">
			<?php jalaversity_post_meta_line(); ?>
			<?php jalaversity_card_title( 'h3' ); ?>
		</div>

	<?php elseif ( 'klasik' === $variant ) : ?>

		<?php jalaversity_card_thumbnail( 'jalaversity-thumbnail', 40 ); ?>
		<div class="card__body">
			<?php jalaversity_post_meta_line(); ?>
			<?php jalaversity_card_title( 'h2' ); ?>
			<p class="card__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
		</div>

	<?php elseif ( 'title' === $variant ) : ?>

		<div class="card__body">
			<?php jalaversity_post_meta_line(); ?>
			<?php jalaversity_card_title( 'h3' ); ?>
		</div>

	<?php else : // 'list' (default) ?>

		<?php jalaversity_card_thumbnail( 'jalaversity-square', 24 ); ?>
		<div class="card__body">
			<?php jalaversity_post_meta_line(); ?>
			<?php jalaversity_card_title( 'h3' ); ?>
		</div>

	<?php endif; ?>

</article>
