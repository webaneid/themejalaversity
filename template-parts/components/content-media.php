<?php
/**
 * Content + Media — Generic
 *
 * Section 2-kolom: gambar di satu sisi, konten (label/heading/body/items/link)
 * di sisi lain. Posisi gambar diatur via `image_position`. Menggantikan
 * about.php dan research.php yang sebelumnya hampir identik strukturnya.
 *
 * Pure render component — semua data via $args.
 *
 * @param string $args['label']          Section label (opsional).
 * @param string $args['heading']        H2 (wajib).
 * @param string $args['heading_id']     ID heading untuk aria-labelledby.
 * @param string $args['body']           Paragraf body.
 * @param int    $args['image_id']       Attachment ID.
 * @param string $args['image_alt']      Alt text gambar.
 * @param string $args['image_position'] 'left' | 'right' — sisi gambar (default 'left').
 * @param string $args['image_radius']   Modifier radius: 'about' | 'default' (default 'default').
 * @param string $args['bg']             Section background: 'cream' | 'surface' (default 'cream').
 * @param array  $args['corner_badge']   ['value','label','position'=>'top-right'|'bottom-left','variant'=>'dark'|'gold'].
 * @param array  $args['items']          List item untuk icon-list (icon/title/desc).
 * @param string $args['items_layout']   Layout icon-list: 'grid' | 'rows' (default 'grid').
 * @param array  $args['link']           ['label','url'] — link-arrow di bawah konten.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label          = $args['label'] ?? '';
$heading        = $args['heading'] ?? '';
$heading_id     = $args['heading_id'] ?? '';
$body           = $args['body'] ?? '';
$image_id       = (int) ( $args['image_id'] ?? 0 );
$image_alt      = $args['image_alt'] ?? get_bloginfo( 'name' );
$image_position = ( 'right' === ( $args['image_position'] ?? 'left' ) ) ? 'right' : 'left';
$image_radius   = $args['image_radius'] ?? 'default';
$corner_badge   = $args['corner_badge'] ?? null;
$items          = $args['items'] ?? [];
$items_layout   = $args['items_layout'] ?? 'grid';
$link           = $args['link'] ?? null;
$bg             = ( 'surface' === ( $args['bg'] ?? 'cream' ) ) ? 'surface' : 'cream';

if ( ! $heading ) {
	return;
}

$section_class    = 'content-media content-media--bg-' . $bg . ( 'right' === $image_position ? ' content-media--reverse' : '' );
$img_radius_class = 'content-media__img-wrap--' . sanitize_html_class( $image_radius );
?>
<section class="<?php echo esc_attr( $section_class ); ?> section-py" aria-labelledby="<?php echo esc_attr( $heading_id ?: 'content-media-heading' ); ?>">
	<div class="container">
		<div class="content-media__inner">

			<div class="content-media__visual">
				<div class="content-media__img-wrap <?php echo esc_attr( $img_radius_class ); ?>">
					<?php if ( $image_id && wp_attachment_is_image( $image_id ) ) :
						echo wp_get_attachment_image(
							$image_id,
							'large',
							false,
							[
								'class'   => 'content-media__img',
								'alt'     => esc_attr( $image_alt ),
								'loading' => 'lazy',
							]
						);
					else : ?>
						<div class="content-media__img-placeholder" aria-hidden="true">
							<?php jalaversity_icon_e( 'photo', 48 ); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $corner_badge ) :
					$badge_pos     = ( 'bottom-left' === ( $corner_badge['position'] ?? '' ) ) ? 'bottom-left' : 'top-right';
					$badge_variant = ( 'gold' === ( $corner_badge['variant'] ?? '' ) ) ? 'gold' : 'dark';
				?>
				<div
					class="content-media__badge content-media__badge--<?php echo esc_attr( $badge_pos ); ?> content-media__badge--<?php echo esc_attr( $badge_variant ); ?>"
					aria-label="<?php echo esc_attr( ( $corner_badge['value'] ?? '' ) . ' ' . ( $corner_badge['label'] ?? '' ) ); ?>"
				>
					<div class="content-media__badge-value"><?php echo esc_html( $corner_badge['value'] ?? '' ); ?></div>
					<div class="content-media__badge-label"><?php echo esc_html( $corner_badge['label'] ?? '' ); ?></div>
				</div>
				<?php endif; ?>
			</div><!-- /.content-media__visual -->

			<div class="content-media__content">

				<?php if ( $label ) : ?>
					<?php jalaversity_section_label( $label ); ?>
				<?php endif; ?>

				<h2
					<?php if ( $heading_id ) : ?>id="<?php echo esc_attr( $heading_id ); ?>"<?php endif; ?>
					class="content-media__heading text-section"
				>
					<?php echo esc_html( $heading ); ?>
				</h2>

				<?php if ( $body ) : ?>
				<p class="content-media__body"><?php echo esc_html( $body ); ?></p>
				<?php endif; ?>

				<?php if ( $items ) : ?>
				<div class="content-media__items">
					<?php get_template_part( 'template-parts/components/icon-list', null, [ 'items' => $items, 'layout' => $items_layout ] ); ?>
				</div>
				<?php endif; ?>

				<?php if ( $link ) : ?>
				<a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>" class="link-arrow">
					<?php echo esc_html( $link['label'] ?? '' ); ?>
					<?php jalaversity_icon_e( 'arrow-right', 17 ); ?>
				</a>
				<?php endif; ?>

			</div><!-- /.content-media__content -->

		</div><!-- /.content-media__inner -->
	</div><!-- /.container -->
</section>
