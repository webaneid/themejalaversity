<?php
/**
 * Share Buttons
 *
 * Tombol share untuk post saat ini (Facebook, X/Twitter, WhatsApp,
 * LinkedIn, copy-link). Baca permalink/title dari loop post saat ini —
 * komponen loop-context, bukan pure-$args (lihat docs/02-architecture.md §10).
 *
 * Tidak ada bottom-sheet mobile seperti jalawarta — disederhanakan jadi
 * row biasa yang tetap tampil responsive di semua ukuran layar.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url = get_permalink();

if ( ! $url ) {
	return;
}

$title         = get_the_title();
$encoded_url   = rawurlencode( $url );
$encoded_title = rawurlencode( $title );
?>
<div class="share-buttons">
	<span class="share-buttons__label"><?php esc_html_e( 'Bagikan', 'jalaversity' ); ?></span>
	<ul class="share-buttons__list">
		<li>
			<a
				href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $encoded_url ); ?>"
				class="share-buttons__link share-buttons__link--facebook"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'Bagikan ke Facebook', 'jalaversity' ); ?>"
			>
				<?php jalaversity_social_icon_e( 'facebook', 18 ); ?>
			</a>
		</li>
		<li>
			<a
				href="https://twitter.com/intent/tweet?url=<?php echo esc_attr( $encoded_url ); ?>&amp;text=<?php echo esc_attr( $encoded_title ); ?>"
				class="share-buttons__link share-buttons__link--twitter"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'Bagikan ke X (Twitter)', 'jalaversity' ); ?>"
			>
				<?php jalaversity_social_icon_e( 'twitter', 18 ); ?>
			</a>
		</li>
		<li>
			<a
				href="https://wa.me/?text=<?php echo esc_attr( $encoded_title . ' ' . $encoded_url ); ?>"
				class="share-buttons__link share-buttons__link--whatsapp"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'Bagikan ke WhatsApp', 'jalaversity' ); ?>"
			>
				<?php jalaversity_social_icon_e( 'whatsapp', 18 ); ?>
			</a>
		</li>
		<li>
			<a
				href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $encoded_url ); ?>"
				class="share-buttons__link share-buttons__link--linkedin"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'Bagikan ke LinkedIn', 'jalaversity' ); ?>"
			>
				<?php jalaversity_social_icon_e( 'linkedin', 18 ); ?>
			</a>
		</li>
		<li>
			<button
				type="button"
				class="share-buttons__link share-buttons__link--copy"
				data-jalaversity-copy-url="<?php echo esc_url( $url ); ?>"
				aria-label="<?php esc_attr_e( 'Salin link', 'jalaversity' ); ?>"
			>
				<?php jalaversity_icon_e( 'link', 18 ); ?>
			</button>
		</li>
	</ul>
</div>
