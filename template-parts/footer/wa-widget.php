<?php
/**
 * WhatsApp Widget — Tim Layanan
 *
 * Floating button kanan bawah. Klik → popup daftar tim layanan.
 * Data dari ACF Options Page "Tim Layanan" (Appearance → Tim Layanan).
 * Tidak render apa-apa jika tidak ada data.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contacts   = jalaversity_get_option( 'tim_layanan_contacts', [] );
$wa_message = jalaversity_get_option( 'wa_default_message', '' );

if ( ! is_array( $contacts ) ) {
	$contacts = [];
}

if ( ! $contacts ) {
	return;
}
?>
<div class="wa-widget" id="wa-widget" aria-label="<?php esc_attr_e( 'Tim Layanan WhatsApp', 'jalaversity' ); ?>">

	<div
		class="wa-popup"
		id="wa-popup"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Pilih Tim Layanan', 'jalaversity' ); ?>"
		hidden
	>
		<div class="wa-popup__header">
			<div class="wa-popup__header-info">
				<div class="wa-popup__avatar" aria-hidden="true">
					<?php jalaversity_icon_e( 'message-circle', 20 ); ?>
				</div>
				<div>
					<div class="wa-popup__title"><?php esc_html_e( 'Tim Layanan', 'jalaversity' ); ?></div>
					<div class="wa-popup__subtitle"><?php esc_html_e( 'Pilih petugas yang ingin dihubungi', 'jalaversity' ); ?></div>
				</div>
			</div>
			<button
				class="wa-popup__close"
				id="wa-popup-close"
				aria-label="<?php esc_attr_e( 'Tutup', 'jalaversity' ); ?>"
				type="button"
			>
				<?php jalaversity_icon_e( 'x', 18 ); ?>
			</button>
		</div>

		<div class="wa-popup__body">
			<?php foreach ( $contacts as $contact ) :
				$nama     = $contact['nama'] ?? '';
				$jabatan  = $contact['jabatan'] ?? '';
				$nomor     = $contact['whatsapp'] ?? '';
				$photo_id  = absint( $contact['photo'] ?? 0 );
				$photo_url = $photo_id ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';
				$wa_url   = 'https://wa.me/' . $nomor . ( $wa_message ? '?text=' . rawurlencode( $wa_message ) : '' );

				if ( ! $nomor ) continue;
			?>
			<a
				href="<?php echo esc_url( $wa_url ); ?>"
				class="wa-contact"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php echo esc_attr( sprintf( __( 'Chat dengan %s', 'jalaversity' ), $nama ) ); ?>"
			>
				<div class="wa-contact__photo" aria-hidden="true">
					<?php if ( $photo_url ) : ?>
					<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="44" height="44" loading="lazy">
					<?php else : ?>
					<span class="wa-contact__initials"><?php echo esc_html( mb_strtoupper( mb_substr( $nama, 0, 1 ) ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wa-contact__info">
					<div class="wa-contact__name"><?php echo esc_html( $nama ); ?></div>
					<?php if ( $jabatan ) : ?>
					<div class="wa-contact__jabatan"><?php echo esc_html( $jabatan ); ?></div>
					<?php endif; ?>
				</div>
				<div class="wa-contact__cta" aria-hidden="true">
					<?php jalaversity_icon_e( 'message-circle', 18 ); ?>
					<span><?php esc_html_e( 'Chat', 'jalaversity' ); ?></span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
	</div>

	<button
		class="wa-fab"
		id="wa-fab"
		type="button"
		aria-expanded="false"
		aria-controls="wa-popup"
		aria-label="<?php esc_attr_e( 'Hubungi Tim Layanan', 'jalaversity' ); ?>"
	>
		<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true">
			<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
		</svg>
		<span class="wa-fab__badge" id="wa-fab-badge" aria-hidden="true"><?php echo count( $contacts ); ?></span>
	</button>

</div>
