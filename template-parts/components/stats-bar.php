<?php
/**
 * Stats Bar
 *
 * 4-kolom kartu statistik yang overlap hero via negative margin-top.
 *
 * Pure render component — pakai $args['items'] jika diisi (mis. dari ACF
 * flexible content), fallback ke jalaversity_get_stats() (Settings Page)
 * supaya pemanggilan existing di page-home.php tanpa $args tetap jalan.
 *
 * @param array $args['items'] List ['icon','value','label'] (opsional).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats = $args['items'] ?? jalaversity_get_stats();
?>
<div class="stats-bar" aria-label="<?php esc_attr_e( 'Statistik institusi', 'jalaversity' ); ?>">
	<?php foreach ( $stats as $stat ) : ?>
	<div class="stats-item">
		<div class="stats-item__icon" aria-hidden="true">
			<?php jalaversity_icon_e( $stat['icon'], 26 ); ?>
		</div>
		<div class="stats-item__content">
			<div class="stats-item__value"><?php echo esc_html( $stat['value'] ); ?></div>
			<div class="stats-item__label"><?php echo esc_html( $stat['label'] ); ?></div>
		</div>
	</div>
	<?php endforeach; ?>
</div>
