<?php
/**
 * Card Grid — Generic
 *
 * Grid kartu (icon overlay dan/atau foto) + judul + deskripsi + alamat
 * opsional + link. Menggantikan faculty-grid.php dan locations.php yang
 * sebelumnya struktur kartunya identik, hanya datanya berbeda.
 *
 * Header (label/heading/lead) di-render via section-header component jika
 * `heading` diisi.
 *
 * Pure render component — semua data via $args. Lookup gambar per-item
 * (mis. `faculty_1_image_id`) dilakukan di helper data (template-helpers.php),
 * bukan di sini — komponen ini hanya menerima `image_id` yang sudah jadi.
 *
 * @param string $args['label']          Section label (opsional).
 * @param string $args['heading']        Judul section (opsional — render section-header jika diisi).
 * @param string $args['heading_id']     ID heading untuk aria-labelledby (opsional).
 * @param string $args['lead']           Lead paragraph (opsional).
 * @param bool   $args['center']         Section header centered (default true).
 * @param array  $args['items']          List kartu (wajib). Setiap item:
 *        - icon          (string|null) Nama icon untuk overlay/standalone.
 *        - image_id      (int|null)    Attachment ID foto.
 *        - title         (string)      Judul kartu.
 *        - desc          (string)      Deskripsi singkat.
 *        - address       (string|null) Alamat (render dengan icon map-pin).
 *        - link          (array|null)  ['label','url','external'=>bool].
 *        - code          (string|null) Kode singkat (mis. kode prodi "PAI") — badge kotak.
 *        - badge         (string|null) Teks badge (mis. akreditasi "Unggul").
 *        - badge_variant (string)      'gold' | 'green' (default 'green').
 *        - meta          (array|null)  List ['label','value'] (mis. Jenjang/Gelar).
 * @param string $args['min_card_width'] CSS minmax() min value (default '330px').
 * @param bool   $args['dark']           Varian glass/dark di atas background gelap (default false).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label      = $args['label'] ?? '';
$heading    = $args['heading'] ?? '';
$heading_id = $args['heading_id'] ?? '';
$lead       = $args['lead'] ?? '';
$center     = array_key_exists( 'center', $args ) ? (bool) $args['center'] : true;
$items      = $args['items'] ?? [];
$min_card   = $args['min_card_width'] ?? '330px';
$dark       = ! empty( $args['dark'] );

if ( ! $items ) {
	return;
}
?>
<div class="card-grid-section">

	<?php if ( $heading ) :
		get_template_part( 'template-parts/components/section-header', null, [
			'label'      => $label,
			'heading'    => $heading,
			'heading_id' => $heading_id,
			'lead'       => $lead,
			'center'     => $center,
			'dark'       => $dark,
		] );
	endif; ?>

	<div class="card-grid" role="list" style="--card-grid-min: <?php echo esc_attr( $min_card ); ?>;">
		<?php foreach ( $items as $item ) :
			$has_icon  = ! empty( $item['icon'] );
			$has_image = ! empty( $item['image_id'] ) && wp_attachment_is_image( (int) $item['image_id'] );
			$title     = $item['title'] ?? '';
			$has_code  = ! empty( $item['code'] );
			$has_badge = ! empty( $item['badge'] );
			$meta      = $item['meta'] ?? [];
			$card_class = 'card card--grid' . ( $has_image ? ' card--grid-photo' : '' ) . ( $dark ? ' card--grid-dark' : '' );
		?>
		<article class="<?php echo esc_attr( $card_class ); ?>" role="listitem">

			<?php if ( $has_image || $has_icon ) : ?>
			<div class="card__media">
				<?php if ( $has_image ) :
					echo wp_get_attachment_image(
						(int) $item['image_id'],
						'medium',
						false,
						[
							'class'   => 'card__media-img',
							'alt'     => esc_attr( $title ),
							'loading' => 'lazy',
						]
					);
				elseif ( $has_icon ) : ?>
					<div class="card__media-placeholder" aria-hidden="true"></div>
				<?php endif; ?>

				<?php if ( $has_icon && $has_image ) : ?>
				<div class="card__media-icon" aria-hidden="true">
					<?php jalaversity_icon_e( $item['icon'], 24 ); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="card__body">
				<?php if ( $has_code || $has_badge ) : ?>
				<div class="card__top-row">
					<?php if ( $has_code ) : ?>
					<div class="card__code"><?php echo esc_html( $item['code'] ); ?></div>
					<?php endif; ?>
					<?php if ( $has_badge ) :
						$badge_variant = ( 'gold' === ( $item['badge_variant'] ?? 'green' ) ) ? 'gold' : 'green';
					?>
					<span class="card__badge card__badge--<?php echo esc_attr( $badge_variant ); ?>"><?php echo esc_html( $item['badge'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ( $has_icon && ! $has_image && ! $has_code ) : ?>
				<div class="card__icon" aria-hidden="true">
					<?php jalaversity_icon_e( $item['icon'], 24 ); ?>
				</div>
				<?php endif; ?>

				<h3 class="card__title"><?php echo esc_html( $title ); ?></h3>

				<?php if ( ! empty( $item['desc'] ) ) : ?>
				<p class="card__desc"><?php echo esc_html( $item['desc'] ); ?></p>
				<?php endif; ?>

				<?php if ( $meta ) : ?>
				<div class="card__meta">
					<?php foreach ( $meta as $meta_item ) : ?>
					<div class="card__meta-item">
						<div class="card__meta-label"><?php echo esc_html( $meta_item['label'] ?? '' ); ?></div>
						<div class="card__meta-value"><?php echo esc_html( $meta_item['value'] ?? '' ); ?></div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $item['address'] ) ) : ?>
				<address class="card__address">
					<span class="card__address-icon" aria-hidden="true">
						<?php jalaversity_icon_e( 'map-pin', 15 ); ?>
					</span>
					<span><?php echo esc_html( $item['address'] ); ?></span>
				</address>
				<?php endif; ?>

				<?php if ( ! empty( $item['link']['url'] ) ) : ?>
				<a
					href="<?php echo esc_url( $item['link']['url'] ); ?>"
					class="link-arrow link-arrow--gold"
					<?php if ( ! empty( $item['link']['external'] ) ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
				>
					<?php echo esc_html( $item['link']['label'] ?? '' ); ?>
					<?php jalaversity_icon_e( 'chevron-right', 16 ); ?>
				</a>
				<?php endif; ?>
			</div>

		</article>
		<?php endforeach; ?>
	</div>

</div>
