<?php
/**
 * Floating Badge
 *
 * Badge absolut-positioned dengan animasi floaty. Dipanggil dari dalam container
 * bergaya `position: relative`. Args dikirim via get_template_part() $args.
 *
 * @param string $args['icon']  Nama icon (jalaversity_icon).
 * @param string $args['label'] Teks kecil di atas value (caption).
 * @param string $args['value'] Teks utama (nilai besar, mis. "UNGGUL").
 * @param string $args['class'] CSS class tambahan (opsional).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon  = isset( $args['icon'] ) ? $args['icon'] : 'trophy';
$label = isset( $args['label'] ) ? $args['label'] : '';
$value = isset( $args['value'] ) ? $args['value'] : '';
$class = isset( $args['class'] ) ? ' ' . $args['class'] : '';
?>
<div class="floating-badge<?php echo esc_attr( $class ); ?>" aria-hidden="true">
	<div class="floating-badge__icon">
		<?php jalaversity_icon_e( $icon, 24 ); ?>
	</div>
	<div class="floating-badge__text">
		<?php if ( $label ) : ?>
		<div class="floating-badge__label"><?php echo esc_html( $label ); ?></div>
		<?php endif; ?>
		<div class="floating-badge__value"><?php echo esc_html( $value ); ?></div>
	</div>
</div>
