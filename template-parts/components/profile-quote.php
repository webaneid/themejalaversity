<?php
/**
 * Profile + Quote — Generic
 *
 * Foto bulat + nama + jabatan di satu sisi, label+heading+quote box+body
 * paragraf di sisi lain. Dipakai untuk sambutan Dekan/Rektor, tapi generik
 * (tidak menyebut "Dekan" di markup) supaya bisa dipakai untuk profil
 * pimpinan lain.
 *
 * Pure render component — semua data via $args.
 *
 * @param int    $args['image_id']   Attachment ID foto profil.
 * @param string $args['image_alt']  Alt text foto.
 * @param string $args['name']       Nama (wajib).
 * @param string $args['title']      Jabatan.
 * @param string $args['label']      Section label (opsional).
 * @param string $args['heading']    Heading (wajib).
 * @param string $args['quote']      Kutipan singkat (ditampilkan dalam quote box).
 * @param string $args['body']       Paragraf body.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image_id  = (int) ( $args['image_id'] ?? 0 );
$image_alt = $args['image_alt'] ?? get_bloginfo( 'name' );
$name      = $args['name'] ?? '';
$title     = $args['title'] ?? '';
$label     = $args['label'] ?? '';
$heading   = $args['heading'] ?? '';
$quote     = $args['quote'] ?? '';
$body      = $args['body'] ?? '';

if ( ! $heading || ! $name ) {
	return;
}
?>
<section class="profile-quote-section section-py" aria-labelledby="profile-quote-heading">
	<div class="container">
	<div class="profile-quote">

		<div class="profile-quote__photo-col">
			<div class="profile-quote__photo-wrap">
				<?php if ( $image_id && wp_attachment_is_image( $image_id ) ) :
					echo wp_get_attachment_image(
						$image_id,
						'medium',
						false,
						[
							'class'   => 'profile-quote__photo',
							'alt'     => esc_attr( $image_alt ),
							'loading' => 'lazy',
						]
					);
				else : ?>
					<div class="profile-quote__photo-placeholder" aria-hidden="true">
						<?php jalaversity_icon_e( 'user', 48 ); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="profile-quote__name"><?php echo esc_html( $name ); ?></div>
			<?php if ( $title ) : ?>
			<div class="profile-quote__role"><?php echo esc_html( $title ); ?></div>
			<?php endif; ?>
		</div>

		<div class="profile-quote__content">
			<?php if ( $label ) : ?>
				<?php jalaversity_section_label( $label ); ?>
			<?php endif; ?>

			<h2 id="profile-quote-heading" class="profile-quote__heading text-section"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( $quote ) : ?>
			<div class="profile-quote__quote">
				<span class="profile-quote__quote-icon" aria-hidden="true">
					<?php jalaversity_icon_e( 'chat-quote', 30 ); ?>
				</span>
				<p><?php echo esc_html( $quote ); ?></p>
			</div>
			<?php endif; ?>

			<?php if ( $body ) : ?>
			<p class="profile-quote__body"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
		</div>

	</div>
	</div>
</section>
