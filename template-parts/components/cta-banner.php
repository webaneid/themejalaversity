<?php
/**
 * CTA Banner
 *
 * Dark green banner dengan girih pattern. Dua tombol: utama (gold) + ghost (white).
 * Data dari Settings Page dengan fallback.
 * Dapat di-override via $args:
 *   $args['heading']     - Judul banner
 *   $args['body']        - Paragraf deskripsi
 *   $args['btn_primary'] - [ 'label' => ..., 'url' => ... ]
 *   $args['btn_ghost']   - [ 'label' => ..., 'url' => ... ]
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = $args['heading'] ?? __( 'Wujudkan Cita-citamu Bersama Kami', 'jalaversity' );
$body    = $args['body']    ?? __( 'Bergabunglah dengan komunitas akademik yang menumbuhkan ilmu, iman, dan amal. Masa depanmu dimulai di sini.', 'jalaversity' );

$pmb_url   = jalaversity_get_option( 'pmb_url', '#' );
$pmb_label = jalaversity_get_option( 'pmb_label', __( 'Daftar PMB Sekarang', 'jalaversity' ) );

$btn_primary = isset( $args['btn_primary'] ) ? $args['btn_primary'] : [
	'label' => $pmb_label,
	'url'   => $pmb_url,
];

$btn_ghost = isset( $args['btn_ghost'] ) ? $args['btn_ghost'] : [
	'label' => __( 'Hubungi Kami', 'jalaversity' ),
	'url'   => jalaversity_get_option( 'contact_url', '#' ),
];
?>
<div class="cta-banner">
	<div class="cta-banner__inner">
		<h2 class="cta-banner__title text-cta">
			<?php echo esc_html( $heading ); ?>
		</h2>
		<p class="cta-banner__body">
			<?php echo esc_html( $body ); ?>
		</p>
		<div class="cta-banner__actions">
			<a
				href="<?php echo esc_url( $btn_primary['url'] ); ?>"
				class="btn btn--accent-solid"
			>
				<?php echo esc_html( $btn_primary['label'] ); ?>
				<?php jalaversity_icon_e( 'arrow-right', 18 ); ?>
			</a>
			<a
				href="<?php echo esc_url( $btn_ghost['url'] ); ?>"
				class="btn btn--ghost-white"
			>
				<?php echo esc_html( $btn_ghost['label'] ); ?>
			</a>
		</div>
	</div>
</div>
