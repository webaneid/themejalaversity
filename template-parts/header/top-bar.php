<?php
/**
 * Top Bar
 *
 * Dark green baris di atas header: kontak kiri, quick links + language kanan.
 * Data dari Settings Page (kontak) dan menu 'topbar' (quick links).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$phone   = jalaversity_get_option( 'contact_phone', '' );
$email   = jalaversity_get_option( 'contact_email', '' );
$has_top = has_nav_menu( 'topbar' );
?>
<div class="topbar" role="complementary" aria-label="<?php esc_attr_e( 'Informasi kontak dan tautan cepat', 'jalaversity' ); ?>">
	<div class="container">
		<div class="topbar__inner">

			<?php if ( $phone || $email ) : ?>
			<div class="topbar__contact">
				<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="topbar__contact-item">
					<?php jalaversity_icon_e( 'phone', 14 ); ?>
					<span><?php echo esc_html( $phone ); ?></span>
				</a>
				<?php endif; ?>

				<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="topbar__contact-item">
					<?php jalaversity_icon_e( 'mail', 14 ); ?>
					<span><?php echo esc_html( antispambot( $email ) ); ?></span>
				</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="topbar__actions">
				<?php if ( $has_top ) : ?>
				<nav class="topbar__nav" aria-label="<?php esc_attr_e( 'Tautan Cepat', 'jalaversity' ); ?>">
					<?php
					wp_nav_menu(
						[
							'theme_location' => 'topbar',
							'container'      => false,
							'menu_class'     => 'topbar-menu',
							'depth'          => 1,
							'fallback_cb'    => false,
							'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
							'walker'         => null,
						]
					);
					?>
				</nav>

				<span class="topbar__divider" aria-hidden="true"></span>
				<?php endif; ?>

				<div class="topbar__lang" aria-label="<?php esc_attr_e( 'Pilihan bahasa', 'jalaversity' ); ?>">
					<button class="topbar__lang-btn is-active" aria-pressed="true" lang="id">ID</button>
					<button class="topbar__lang-btn" aria-pressed="false" lang="en">EN</button>
				</div>
			</div>

		</div>
	</div>
</div>
