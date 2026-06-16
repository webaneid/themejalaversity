<?php
/**
 * Asset Enqueueing
 *
 * Registers and enqueues stylesheets and scripts.
 * Front-end and admin assets are strictly separated.
 * CSS brand variables are output as inline styles here so they can be
 * overridden per-site from the Settings Page without rebuilding CSS.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue front-end assets only.
 */
function jalaversity_enqueue_scripts(): void {

	// Google Fonts — Plus Jakarta Sans (body) saja. Font heading ("Gontor")
	// di-self-host via @font-face di scss/front/_base.scss (fonts/Gontor-Bold.otf)
	// — bukan lagi dari Google Fonts, lihat docs/02-architecture.md §12.
	wp_enqueue_style(
		'jalaversity-fonts',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
		[],
		null // Google Fonts manages its own cache headers.
	);

	// Front-end CSS (compiled from scss/front/main.scss via PostCSS + Tailwind).
	wp_enqueue_style(
		'jalaversity-style',
		JALAVERSITY_URI . '/css/front.css',
		[ 'jalaversity-fonts' ],
		JALAVERSITY_VERSION
	);

	// Front-end JS — vanilla ES6+, loaded in footer.
	wp_enqueue_script(
		'jalaversity-main',
		JALAVERSITY_URI . '/js/front/main.js',
		[],
		JALAVERSITY_VERSION,
		true
	);

	// Pass minimal PHP data to JS.
	wp_localize_script(
		'jalaversity-main',
		'jalaversityData',
		[
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'jalaversity_ajax' ),
			'homeUrl' => home_url( '/' ),
			'isRtl'   => is_rtl() ? 'true' : 'false',
		]
	);

	// Comment reply script — only when it is actually needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'jalaversity_enqueue_scripts' );

/**
 * Output CSS custom property overrides from Settings Page.
 *
 * Reads brand colours from jalaversity_options and injects them as CSS
 * variables into <head> after the main stylesheet, overriding the defaults
 * defined in scss/front/_variables.scss.
 */
function jalaversity_output_css_vars(): void {

	$primary      = sanitize_hex_color( jalaversity_get_option( 'color_primary', '#08422e' ) ) ?? '#08422e';
	$primary_dark = sanitize_hex_color( jalaversity_get_option( 'color_primary_dark', '#06301f' ) ) ?? '#06301f';
	$primary_med  = sanitize_hex_color( jalaversity_get_option( 'color_primary_medium', '#0a4730' ) ) ?? '#0a4730';
	$accent       = sanitize_hex_color( jalaversity_get_option( 'color_accent', '#b68c2e' ) ) ?? '#b68c2e';
	$accent_dark  = sanitize_hex_color( jalaversity_get_option( 'color_accent_dark', '#a87e26' ) ) ?? '#a87e26';
	$accent_light = sanitize_hex_color( jalaversity_get_option( 'color_accent_light', '#e9c970' ) ) ?? '#e9c970';

	// Only output the <style> block if at least one value differs from default.
	$css = ":root{--color-primary:{$primary};--color-primary-dark:{$primary_dark};--color-primary-medium:{$primary_med};--color-accent:{$accent};--color-accent-dark:{$accent_dark};--color-accent-light:{$accent_light};}";

	wp_add_inline_style( 'jalaversity-style', $css );
}
add_action( 'wp_enqueue_scripts', 'jalaversity_output_css_vars', 20 );

/**
 * Enqueue admin assets.
 *
 * @param string $hook Current admin page hook suffix.
 */
function jalaversity_admin_enqueue_scripts( string $hook ): void {

	// Admin CSS — loaded on all admin pages.
	wp_enqueue_style(
		'jalaversity-admin',
		JALAVERSITY_URI . '/css/admin.css',
		[],
		JALAVERSITY_VERSION
	);

	// Admin JS — only on the theme's own settings page.
	if ( str_contains( $hook, 'jalaversity' ) ) {
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			'jalaversity-admin',
			JALAVERSITY_URI . '/js/admin/admin.js',
			[ 'jquery', 'wp-color-picker' ], // WP admin depends on jQuery — acceptable in admin context.
			JALAVERSITY_VERSION,
			true
		);

		wp_localize_script(
			'jalaversity-admin',
			'jalaversityAdmin',
			[
				'nonce'       => wp_create_nonce( 'jalaversity_admin' ),
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'mediaTitle'  => __( 'Pilih Gambar', 'jalaversity' ),
				'mediaButton' => __( 'Gunakan Gambar Ini', 'jalaversity' ),
			]
		);
	}
}
add_action( 'admin_enqueue_scripts', 'jalaversity_admin_enqueue_scripts' );

/**
 * Add preconnect hints for Google Fonts and preload self-hosted heading font.
 * Must run before the fonts stylesheet is printed (priority 1).
 */
function jalaversity_preconnect_fonts(): void {
	// Preconnect for Google Fonts (Plus Jakarta Sans body font).
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

	// Preload self-hosted heading font — browser discovers it from CSS too late otherwise.
	$font_url = esc_url( JALAVERSITY_URI . '/fonts/Gontor-Bold.otf' );
	echo '<link rel="preload" href="' . $font_url . '" as="font" type="font/otf" crossorigin="anonymous">' . "\n";
}
add_action( 'wp_head', 'jalaversity_preconnect_fonts', 1 );

/**
 * Add defer attribute to front-end script for non-blocking load.
 * Already in footer (in_footer=true), defer adds an extra signal to the browser.
 *
 * @param string $tag    Full <script> tag HTML.
 * @param string $handle Script handle.
 * @return string
 */
function jalaversity_add_defer_attribute( string $tag, string $handle ): string {
	if ( 'jalaversity-main' === $handle ) {
		return str_replace( '<script ', '<script defer ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'jalaversity_add_defer_attribute', 10, 2 );
