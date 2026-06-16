<?php
/**
 * Site Footer
 *
 * 5-kolom footer: logo+social+alamat | Tentang | Akademik | Layanan | Kontak.
 * Menu dinamis dari WordPress nav menu locations (footer-about, footer-akademik, footer-layanan).
 * Social links dari Settings Page.
 * Bottom bar: copyright + footer menu (jika ada).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$address   = jalaversity_get_option( 'contact_address', '' );
$phone     = jalaversity_get_option( 'contact_phone', '' );
$email     = jalaversity_get_option( 'contact_email', '' );
$copyright = jalaversity_get_option( 'footer_copyright', '' );

if ( ! $copyright ) {
	/* translators: %d: current year, %s: site name */
	$copyright = sprintf(
		__( '&copy; %1$d %2$s. Hak cipta dilindungi.', 'jalaversity' ),
		(int) gmdate( 'Y' ),
		get_bloginfo( 'name' )
	);
}

// Footer column config: [ label, menu_location ]
$footer_cols = [
	[
		'label'    => __( 'Tentang', 'jalaversity' ),
		'location' => 'footer-about',
	],
	[
		'label'    => __( 'Akademik', 'jalaversity' ),
		'location' => 'footer-akademik',
	],
	[
		'label'    => __( 'Layanan', 'jalaversity' ),
		'location' => 'footer-layanan',
	],
];
?>
<footer class="site-footer" role="contentinfo">

	<!-- ── Girih overlay handled by .site-footer::before via @mixin in CSS ── -->

	<div class="container">
		<div class="site-footer__grid">

			<!-- Kolom 1: Logo + Deskripsi + Social + Alamat -->
			<div class="site-footer__brand">

				<?php $logo = get_custom_logo(); ?>
				<?php if ( $logo ) : ?>
					<div class="site-footer__logo">
						<?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-footer__site-name" rel="home">
						<?php bloginfo( 'name' ); ?>
					</a>
				<?php endif; ?>

				<?php $desc = get_bloginfo( 'description' ); ?>
				<?php if ( $desc ) : ?>
				<p class="site-footer__desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>

				<?php if ( $address ) : ?>
				<address class="site-footer__address">
					<?php jalaversity_icon_e( 'map-pin', 15 ); ?>
					<?php echo wp_kses_post( $address ); ?>
				</address>
				<?php endif; ?>

				<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="site-footer__contact-link">
					<?php jalaversity_icon_e( 'phone', 14 ); ?>
					<?php echo esc_html( $phone ); ?>
				</a>
				<?php endif; ?>

				<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="site-footer__contact-link">
					<?php jalaversity_icon_e( 'mail', 14 ); ?>
					<?php echo esc_html( antispambot( $email ) ); ?>
				</a>
				<?php endif; ?>

				<!-- Social links dari Settings Page -->
				<?php
				$social_html = '';
				ob_start();
				jalaversity_social_links( 'footer-social-link', 18 );
				$social_html = ob_get_clean();

				if ( $social_html ) :
				?>
				<div class="site-footer__social" role="list" aria-label="<?php esc_attr_e( 'Media sosial', 'jalaversity' ); ?>">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $social_html;
					?>
				</div>
				<?php endif; ?>

			</div><!-- /.site-footer__brand -->

			<!-- Kolom 2–4: Nav menu dinamis -->
			<?php foreach ( $footer_cols as $col ) : ?>
			<?php if ( ! has_nav_menu( $col['location'] ) ) : continue; endif; ?>
			<div class="site-footer__col">
				<h4 class="site-footer__col-title"><?php echo esc_html( $col['label'] ); ?></h4>
				<?php
				wp_nav_menu(
					[
						'theme_location' => $col['location'],
						'container'      => false,
						'menu_class'     => 'site-footer__nav-list',
						'depth'          => 1,
						'fallback_cb'    => false,
						'items_wrap'     => '<ul class="%2$s" role="list">%3$s</ul>',
						'link_before'    => '',
						'link_after'     => '',
					]
				);
				?>
			</div>
			<?php endforeach; ?>

			<!-- Kolom 5: Kontak cepat (opsional, dari Settings) -->
			<?php
			$social_fb = jalaversity_get_option( 'social_facebook', '' );
			$social_ig = jalaversity_get_option( 'social_instagram', '' );
			$social_wa = jalaversity_get_option( 'social_whatsapp', '' );
			if ( $social_wa ) :
			?>
			<div class="site-footer__col">
				<h4 class="site-footer__col-title"><?php esc_html_e( 'Hubungi Kami', 'jalaversity' ); ?></h4>
				<div class="site-footer__contact-col">
					<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="site-footer__link">
						<?php jalaversity_icon_e( 'phone', 15 ); ?>
						<?php echo esc_html( $phone ); ?>
					</a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="site-footer__link">
						<?php jalaversity_icon_e( 'mail', 15 ); ?>
						<?php echo esc_html( antispambot( $email ) ); ?>
					</a>
					<?php endif; ?>
					<?php if ( $social_wa ) : ?>
					<a href="<?php echo esc_url( $social_wa ); ?>" class="site-footer__link" target="_blank" rel="noopener noreferrer">
						<?php jalaversity_icon_e( 'chat', 15 ); ?>
						<?php esc_html_e( 'Chat WhatsApp', 'jalaversity' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

		</div><!-- /.site-footer__grid -->
	</div><!-- /.container -->

	<!-- ── Bottom Bar ─────────────────────────────────────────────────── -->
	<div class="site-footer__bottom">
		<div class="container">
			<div class="site-footer__bottom-inner">
				<p class="site-footer__copyright">
					<?php echo wp_kses_post( $copyright ); ?>
				</p>

				<?php if ( has_nav_menu( 'footer-about' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Tautan footer', 'jalaversity' ); ?>">
					<?php
					wp_nav_menu(
						[
							'theme_location' => 'footer-about',
							'container'      => false,
							'menu_class'     => 'site-footer__bottom-menu',
							'depth'          => 1,
							'fallback_cb'    => false,
							'items_wrap'     => '<ul class="%2$s" role="list">%3$s</ul>',
						]
					);
					?>
				</nav>
				<?php endif; ?>
			</div>
		</div>
	</div><!-- /.site-footer__bottom -->

</footer><!-- .site-footer -->
