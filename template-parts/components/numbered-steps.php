<?php
/**
 * Numbered Steps — Generic
 *
 * Grid kartu langkah bernomor (mis. tahapan PMB, alur pendaftaran).
 * Menggantikan rendering pmb-steps/pmb-step yang sebelumnya inline
 * di pmb-section.php.
 *
 * Header (label/heading/lead) opsional — di-render via section-header
 * component jika `heading` diisi, supaya komponen ini bisa dipakai berdiri
 * sendiri (mis. sebagai layout ACF) tanpa wrapper bespoke seperti
 * pmb-section.php yang sudah punya heading sendiri.
 *
 * Pure render component — semua data via $args.
 *
 * @param array  $args['items']      List ['number','title','desc'] (wajib).
 * @param string $args['variant']    'light' | 'on-dark' (default 'light').
 * @param string $args['label']      Section label (opsional).
 * @param string $args['heading']    Judul section (opsional — render section-header jika diisi).
 * @param string $args['heading_id'] ID heading untuk aria-labelledby (opsional).
 * @param string $args['lead']       Lead paragraph (opsional).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items      = $args['items'] ?? [];
$variant    = ( 'on-dark' === ( $args['variant'] ?? 'light' ) ) ? 'on-dark' : 'light';
$label      = $args['label'] ?? '';
$heading    = $args['heading'] ?? '';
$heading_id = $args['heading_id'] ?? '';
$lead       = $args['lead'] ?? '';

if ( ! $items ) {
	return;
}

if ( $heading ) :
	get_template_part( 'template-parts/components/section-header', null, [
		'label'      => $label,
		'heading'    => $heading,
		'heading_id' => $heading_id,
		'lead'       => $lead,
		'center'     => true,
	] );
endif;
?>
<div class="numbered-steps numbered-steps--<?php echo esc_attr( $variant ); ?>" role="list" aria-label="<?php esc_attr_e( 'Langkah-langkah', 'jalaversity' ); ?>">
	<?php foreach ( $items as $step ) : ?>
	<div class="numbered-step" role="listitem">
		<div class="numbered-step__number" aria-hidden="true"><?php echo esc_html( $step['number'] ?? '' ); ?></div>
		<div class="numbered-step__title"><?php echo esc_html( $step['title'] ?? '' ); ?></div>
		<div class="numbered-step__desc"><?php echo esc_html( $step['desc'] ?? '' ); ?></div>
	</div>
	<?php endforeach; ?>
</div>
