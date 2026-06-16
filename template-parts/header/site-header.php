<?php
/**
 * Site Header
 *
 * Sticky header: logo kiri, primary navigation tengah, CTA button kanan.
 * Mobile: hamburger toggle → fullscreen drawer dengan submenu accordion.
 *
 * Scroll behavior (JS): box-shadow muncul saat scroll > 20px.
 * Submenu: toggle button per item + CSS untuk hover desktop / JS untuk mobile.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pmb_url   = jalaversity_get_option( 'pmb_url', '#' );
$pmb_label = jalaversity_get_option( 'pmb_label', __( 'Daftar PMB', 'jalaversity' ) );

// Custom logo or fallback to site name.
$logo_html = get_custom_logo();
$site_name = get_bloginfo( 'name' );
?>
<header id="site-header" class="site-header" role="banner">
	<div class="container">
		<div class="site-header__inner">

			<!-- ── Logo ─────────────────────────────────────────── -->
			<div class="site-header__logo">
				<?php if ( $logo_html ) : ?>
					<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-header__site-name" rel="home">
						<?php echo esc_html( $site_name ); ?>
					</a>
				<?php endif; ?>
			</div>

			<!-- ── Primary Navigation (desktop) ─────────────────── -->
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Navigasi Utama', 'jalaversity' ); ?>">
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'primary',
						'container'      => false,
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'nav-menu',
						'depth'          => 2,
						'fallback_cb'    => false,
						'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
						'walker'         => new Jalaversity_Nav_Walker(),
					]
				);
				?>
			</nav>
			<?php endif; ?>

			<!-- ── CTA + Hamburger ───────────────────────────────── -->
			<div class="site-header__actions">
				<a href="<?php echo esc_url( $pmb_url ); ?>" class="btn btn--pmb" aria-label="<?php echo esc_attr( $pmb_label ); ?>">
					<?php echo esc_html( $pmb_label ); ?>
					<?php jalaversity_icon_e( 'arrow-right', 17 ); ?>
				</a>

				<button
					class="hamburger"
					id="mobile-menu-toggle"
					aria-controls="mobile-menu"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Buka menu navigasi', 'jalaversity' ); ?>"
					type="button"
				>
					<span class="hamburger__icon hamburger__icon--open" aria-hidden="true">
						<?php jalaversity_icon_e( 'menu', 24 ); ?>
					</span>
					<span class="hamburger__icon hamburger__icon--close" aria-hidden="true">
						<?php jalaversity_icon_e( 'x-mark', 24 ); ?>
					</span>
				</button>
			</div>

		</div><!-- /.site-header__inner -->
	</div><!-- /.container -->
</header><!-- #site-header -->

<!-- ── Mobile Drawer ──────────────────────────────────────────────────── -->
<div
	id="mobile-menu"
	class="mobile-menu"
	aria-hidden="true"
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'Menu Navigasi', 'jalaversity' ); ?>"
>
	<div class="mobile-menu__backdrop" id="mobile-menu-backdrop" aria-hidden="true"></div>

	<div class="mobile-menu__panel" role="document">

		<div class="mobile-menu__header">
			<span class="mobile-menu__title">
				<?php esc_html_e( 'Menu', 'jalaversity' ); ?>
			</span>
			<button
				class="mobile-menu__close"
				id="mobile-menu-close"
				aria-label="<?php esc_attr_e( 'Tutup menu', 'jalaversity' ); ?>"
				type="button"
			>
				<?php jalaversity_icon_e( 'x-mark', 22 ); ?>
			</button>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<nav class="mobile-menu__nav" aria-label="<?php esc_attr_e( 'Navigasi Mobile', 'jalaversity' ); ?>">
			<?php
			wp_nav_menu(
				[
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'mobile-primary-menu',
					'menu_class'     => 'mobile-nav-menu',
					'depth'          => 2,
					'fallback_cb'    => false,
					'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
					'walker'         => new Jalaversity_Nav_Walker(),
				]
			);
			?>
		</nav>
		<?php endif; ?>

		<?php
		$phone = jalaversity_get_option( 'contact_phone', '' );
		$email = jalaversity_get_option( 'contact_email', '' );
		if ( $phone || $email ) :
		?>
		<div class="mobile-menu__footer">
			<?php if ( $phone ) : ?>
			<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="mobile-menu__contact-item">
				<?php jalaversity_icon_e( 'phone', 16 ); ?>
				<?php echo esc_html( $phone ); ?>
			</a>
			<?php endif; ?>
			<?php if ( $email ) : ?>
			<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="mobile-menu__contact-item">
				<?php jalaversity_icon_e( 'mail', 16 ); ?>
				<?php echo esc_html( antispambot( $email ) ); ?>
			</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

	</div><!-- /.mobile-menu__panel -->
</div><!-- #mobile-menu -->
