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
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts manages its own cache headers.
	);

	// Front-end CSS (compiled from scss/front/main.scss via PostCSS + Tailwind).
	// Tidak bergantung pada jalaversity-fonts agar front.css tidak terblokir request eksternal.
	wp_enqueue_style(
		'jalaversity-style',
		JALAVERSITY_URI . '/css/front.css',
		[],
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
			'postId'  => is_singular( 'post' ) ? (int) get_queried_object_id() : 0,
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
 * Daftarkan preconnect hints terpusat melalui wp_resource_hints (<= 4 koneksi).
 *
 * @param array<int, string|array<string, string>> $urls          Daftar URL resource hints.
 * @param string                                   $relation_type Tipe relasi (dns-prefetch, preconnect, dll).
 * @return array<int, string|array<string, string>>
 */
function jalaversity_resource_hints( array $urls, string $relation_type ): array {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = [
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		];
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'jalaversity_resource_hints', 10, 2 );

/**
 * Muat Google Fonts stylesheet secara non-blocking (media="print" onload="this.media='all'")
 * dengan fallback <noscript>, sesuai standar webane-pagespeed-cwv.
 *
 * @param string $html   Tag <link> penuh.
 * @param string $handle Handle stylesheet.
 * @param string $href   URL stylesheet.
 * @return string
 */
function jalaversity_async_google_fonts( string $html, string $handle, string $href ): string {
	if ( 'jalaversity-fonts' !== $handle || is_admin() ) {
		return $html;
	}

	return sprintf(
		"<link rel='stylesheet' id='%1\$s-css' href='%2\$s' media='print' onload=\"this.media='all'\" />\n<noscript><link rel='stylesheet' href='%2\$s' /></noscript>\n", // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		esc_attr( $handle ),
		esc_url( $href )
	);
}
add_filter( 'style_loader_tag', 'jalaversity_async_google_fonts', 10, 3 );

/**
 * Deteksi attachment ID, ukuran gambar, dan atribut `sizes` untuk kandidat LCP
 * pada halaman yang sedang dirender.
 *
 * @return array{id: int, size: string, sizes: string}|null
 */
function jalaversity_get_lcp_image_candidate(): ?array {
	if ( is_admin() ) {
		return null;
	}

	$hero_sizes   = '(max-width: 767px) 88vw, (max-width: 1023px) 480px, 560px';
	$single_sizes = '(max-width: 767px) 92vw, 800px';

	// 1. Halaman Beranda (template page-home.php atau front_page default).
	if ( is_page_template( 'page-templates/page-home.php' ) || ( is_front_page() && ! is_page_template( 'page-templates/page-dynamic.php' ) ) ) {
		$hero_id = (int) jalaversity_get_option( 'hero_image_id', 0 );
		if ( $hero_id > 0 && wp_attachment_is_image( $hero_id ) ) {
			return [
				'id'    => $hero_id,
				'size'  => 'large',
				'sizes' => $hero_sizes,
			];
		}
	}

	// 2. Halaman Dinamis (ACF Flexible Content page_sections — row pertama layout 'hero').
	if ( is_singular( 'page' ) && function_exists( 'get_field' ) ) {
		$page_id  = (int) get_queried_object_id();
		$sections = get_field( 'page_sections', $page_id );
		if ( is_array( $sections ) && ! empty( $sections[0] ) && is_array( $sections[0] ) ) {
			$first = $sections[0];
			if ( 'hero' === ( $first['acf_fc_layout'] ?? '' ) && ! empty( $first['image'] ) ) {
				$raw_img = $first['image'];
				$img_id  = is_array( $raw_img ) ? (int) ( $raw_img['ID'] ?? $raw_img['id'] ?? 0 ) : (int) $raw_img;
				if ( $img_id > 0 && wp_attachment_is_image( $img_id ) ) {
					return [
						'id'    => $img_id,
						'size'  => 'large',
						'sizes' => $hero_sizes,
					];
				}
			}
		}
	}

	// 3. Halaman Singular (post, page standar, agenda, pengumuman) dengan featured image.
	if ( is_singular( [ 'post', 'page', 'agenda', 'pengumuman' ] ) ) {
		$post_id  = (int) get_queried_object_id();
		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		if ( $thumb_id > 0 && wp_attachment_is_image( $thumb_id ) ) {
			return [
				'id'    => $thumb_id,
				'size'  => 'jalaversity-medium',
				'sizes' => $single_sizes,
			];
		}
	}

	return null;
}

/**
 * Preload font heading self-hosted dan gambar LCP di <head> prioritas 1.
 */
function jalaversity_preload_critical_assets(): void {
	// Preload self-hosted heading font — browser discovers it from CSS too late otherwise.
	echo '<link rel="preload" href="' . esc_url( JALAVERSITY_URI . '/fonts/Gontor-Bold.otf' ) . '" as="font" type="font/otf" crossorigin="anonymous">' . "\n";

	$lcp = jalaversity_get_lcp_image_candidate();
	if ( ! is_array( $lcp ) ) {
		return;
	}

	$src = wp_get_attachment_image_url( $lcp['id'], $lcp['size'] );
	if ( ! $src ) {
		return;
	}

	$srcset = wp_get_attachment_image_srcset( $lcp['id'], $lcp['size'] );

	if ( $srcset ) {
		printf(
			'<link rel="preload" as="image" href="%1$s" imagesrcset="%2$s" imagesizes="%3$s" fetchpriority="high">' . "\n",
			esc_url( $src ),
			esc_attr( $srcset ),
			esc_attr( $lcp['sizes'] )
		);
	} else {
		printf(
			'<link rel="preload" as="image" href="%1$s" fetchpriority="high">' . "\n",
			esc_url( $src )
		);
	}
}
add_action( 'wp_head', 'jalaversity_preload_critical_assets', 1 );

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
