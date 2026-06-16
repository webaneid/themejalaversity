<?php
/**
 * Icon List — Generic
 *
 * List item icon+title+desc. Tiga layout:
 * - 'grid'  : flex item polos, wrap multi-kolom (mis. nilai institusi).
 * - 'rows'  : kartu vertikal dengan background+border+hover (mis. item riset).
 * - 'plain' : satu kolom polos tanpa card chrome (mis. checklist kompetensi).
 *
 * Menggantikan rendering about-value dan research-item yang sebelumnya
 * inline di masing-masing template page-specific.
 *
 * Pure render component — semua data via $args.
 *
 * @param array  $args['items']  List ['icon','title','desc'] (wajib).
 * @param string $args['layout'] 'grid' | 'rows' | 'plain' (default 'grid').
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = $args['items'] ?? [];
$layout = in_array( $args['layout'] ?? 'grid', [ 'rows', 'plain' ], true ) ? $args['layout'] : 'grid';

if ( ! $items ) {
	return;
}

$icon_size = ( 'grid' === $layout ) ? 18 : 22;
?>
<div class="icon-list icon-list--<?php echo esc_attr( $layout ); ?>" role="list">
	<?php foreach ( $items as $item ) : ?>
	<div class="icon-list__item" role="listitem">
		<div class="icon-list__icon" aria-hidden="true">
			<?php jalaversity_icon_e( $item['icon'] ?? 'star', $icon_size ); ?>
		</div>
		<div class="icon-list__text">
			<div class="icon-list__title"><?php echo esc_html( $item['title'] ?? '' ); ?></div>
			<?php if ( ! empty( $item['desc'] ) ) : ?>
			<div class="icon-list__desc"><?php echo esc_html( $item['desc'] ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>
</div>
