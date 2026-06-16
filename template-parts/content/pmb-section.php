<?php
/**
 * PMB Section — Penerimaan Mahasiswa Baru
 *
 * Dark green section dengan girih, header kiri + 2 CTA button kanan,
 * lalu step cards di bawah.
 *
 * Pure render component — semua data via $args, fallback ke
 * jalaversity_get_option()/jalaversity_get_pmb_steps() (Settings Page)
 * supaya pemanggilan existing di page-home.php tanpa $args tetap jalan.
 *
 * @param string $args['wave_label']   Teks wave label.
 * @param string $args['heading']      Heading.
 * @param string $args['body']         Body (wp_kses_post).
 * @param string $args['cta_label']    Teks tombol CTA utama.
 * @param string $args['cta_url']      URL tombol CTA utama.
 * @param string $args['brochure_url'] URL brosur (opsional).
 * @param array  $args['steps']        List ['number','title','desc'].
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pmb_wave    = $args['wave_label'] ?? jalaversity_get_option( 'pmb_wave_label', __( 'Penerimaan Mahasiswa Baru 2026/2027', 'jalaversity' ) );
$pmb_heading = $args['heading'] ?? jalaversity_get_option( 'pmb_heading', __( 'Gelombang II Telah Dibuka', 'jalaversity' ) );
$pmb_body    = $args['body'] ?? jalaversity_get_option( 'pmb_body', __( 'Pendaftaran berlangsung 24 Juni – 15 Agustus 2026. Tersedia beasiswa tahfizh, prestasi, dan bidikmisi bagi calon mahasiswa terpilih.', 'jalaversity' ) );
$pmb_url     = $args['cta_url'] ?? jalaversity_get_option( 'pmb_url', '#' );
$pmb_label   = $args['cta_label'] ?? jalaversity_get_option( 'pmb_label', __( 'Daftar Sekarang', 'jalaversity' ) );
$pmb_brochure_url = $args['brochure_url'] ?? jalaversity_get_option( 'pmb_brochure_url', '' );

$steps = $args['steps'] ?? jalaversity_get_pmb_steps();
?>
<section id="pmb" class="pmb-section" aria-labelledby="pmb-heading">

	<div class="container">

		<?php /* Header baris: judul kiri, tombol kanan */ ?>
		<div class="pmb-section__header">
			<div class="pmb-section__intro">
				<div class="pmb-section__wave">
					<?php jalaversity_icon_e( 'sparkles', 16 ); ?>
					<?php echo esc_html( $pmb_wave ); ?>
				</div>
				<h2 id="pmb-heading" class="pmb-section__heading text-section">
					<?php echo esc_html( $pmb_heading ); ?>
				</h2>
				<p class="pmb-section__body">
					<?php echo wp_kses_post( $pmb_body ); ?>
				</p>
			</div>

			<div class="pmb-section__cta">
				<a href="<?php echo esc_url( $pmb_url ); ?>" class="btn btn--accent-solid">
					<?php echo esc_html( $pmb_label ); ?>
					<?php jalaversity_icon_e( 'arrow-right', 17 ); ?>
				</a>
				<?php if ( $pmb_brochure_url ) : ?>
				<a
					href="<?php echo esc_url( $pmb_brochure_url ); ?>"
					class="btn btn--ghost-white"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'Unduh Brosur', 'jalaversity' ); ?>
				</a>
				<?php endif; ?>
			</div>
		</div>

		<?php /* 4 Step Cards — generic numbered-steps component */ ?>
		<?php get_template_part( 'template-parts/components/numbered-steps', null, [
			'items'   => $steps,
			'variant' => 'on-dark',
		] ); ?>

	</div>
</section>
