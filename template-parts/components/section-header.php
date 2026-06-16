<?php
/**
 * Section Header — Generic
 *
 * Label + heading + lead paragraph. Dipakai oleh card-grid dan section
 * apapun yang butuh header standar (centered atau left-aligned).
 *
 * Pure render component — semua data dikirim via $args, tidak ada
 * pemanggilan jalaversity_get_option() di sini agar bisa dipakai ulang
 * di halaman manapun (bukan hanya homepage).
 *
 * @param string $args['label']      Section label (opsional).
 * @param string $args['heading']    Judul (wajib, jika kosong tidak render apapun).
 * @param string $args['heading_id'] ID untuk aria-labelledby (opsional).
 * @param string $args['lead']       Paragraf lead (opsional).
 * @param bool   $args['center']     Centered layout (default false).
 * @param string $args['tag']        HTML tag untuk heading (default 'h2').
 * @param bool   $args['dark']       Varian on-dark — label/heading/lead warna terang (default false).
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
$center     = ! empty( $args['center'] );
$tag        = isset( $args['tag'] ) ? tag_escape( $args['tag'] ) : 'h2';
$dark       = ! empty( $args['dark'] );

if ( ! $heading ) {
	return;
}

$class = 'section-header' . ( $center ? ' section-header--center' : '' ) . ( $dark ? ' section-header--on-dark' : '' );
?>
<div class="<?php echo esc_attr( $class ); ?>">
	<?php if ( $label ) : ?>
		<?php jalaversity_section_label( $label, $center, $dark ); ?>
	<?php endif; ?>

	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — tag_escape() above ?> <?php if ( $heading_id ) : ?>id="<?php echo esc_attr( $heading_id ); ?>"<?php endif; ?> class="section-header__heading text-section">
		<?php echo esc_html( $heading ); ?>
	</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $lead ) : ?>
	<p class="section-header__lead"><?php echo esc_html( $lead ); ?></p>
	<?php endif; ?>
</div>
