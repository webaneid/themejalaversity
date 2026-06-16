<?php
/**
 * Security Hardening
 *
 * WordPress security hardening. All hardening is intentional — do not remove.
 * See docs/06-security-checklist.md for the full checklist.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Remove WordPress version from all HTML outputs.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// 2. Strip version query strings from enqueued asset URLs.
function jalaversity_remove_version_query( string $src ): string {
	if ( str_contains( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'jalaversity_remove_version_query' );
add_filter( 'script_loader_src', 'jalaversity_remove_version_query' );

// 3. Disable XML-RPC completely.
add_filter( 'xmlrpc_enabled', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

// 4. Remove noisy, unnecessary <head> tags.
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

// 5. Hide user endpoints from REST API for unauthenticated requests.
add_filter(
	'rest_endpoints',
	function ( array $endpoints ): array {
		if ( ! is_user_logged_in() ) {
			unset(
				$endpoints['/wp/v2/users'],
				$endpoints['/wp/v2/users/(?P<id>[\d]+)']
			);
		}
		return $endpoints;
	}
);

// 6. Block author enumeration via ?author=N URL parameter.
function jalaversity_block_author_enumeration(): void {
	if ( ! is_admin() && isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'init', 'jalaversity_block_author_enumeration' );

// 7. Add security response headers on all front-end requests.
function jalaversity_security_headers(): void {
	if ( is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'X-XSS-Protection: 1; mode=block' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
}
add_action( 'send_headers', 'jalaversity_security_headers' );

// 8. Prevent file editing from within the WordPress admin panel.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// 9. Hide the admin bar on the front-end for non-administrators.
add_action(
	'after_setup_theme',
	function (): void {
		if ( ! current_user_can( 'administrator' ) ) {
			show_admin_bar( false );
		}
	}
);

// 10. Sanitize uploaded file names: lowercase, ASCII-only, no spaces.
function jalaversity_sanitize_filename( string $filename ): string {
	$filename = remove_accents( $filename );
	$filename = strtolower( $filename );
	$filename = preg_replace( '/[^a-z0-9\-\_\.]/', '-', $filename );
	$filename = preg_replace( '/-+/', '-', $filename );
	return trim( $filename, '-' );
}
add_filter( 'sanitize_file_name', 'jalaversity_sanitize_filename' );

// 11. Limit login attempts to prevent brute-force attacks.
//     Max 5 attempts per 15-minute window; 30-minute lockout after that.
//     Uses WP transients — no extra table, no plugin required.

function jalaversity_login_transient_key(): string {
	$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	return 'jalaversity_login_' . md5( $ip );   // MD5 so raw IP is never stored.
}

// Block login early if lockout is active (priority 1 = before WP authenticates).
function jalaversity_maybe_block_login( mixed $user, string $username ): mixed {
	if ( empty( $username ) ) {
		return $user;
	}
	$data = get_transient( jalaversity_login_transient_key() );
	if ( $data && $data['count'] >= 5 ) {
		$minutes = (int) ceil( max( 0, $data['expires'] - time() ) / 60 );
		return new WP_Error(
			'too_many_attempts',
			sprintf(
				/* translators: %d: minutes remaining */
				esc_html__( 'Terlalu banyak percobaan login. Coba lagi dalam %d menit.', 'jalaversity' ),
				$minutes
			)
		);
	}
	return $user;
}
add_filter( 'authenticate', 'jalaversity_maybe_block_login', 1, 2 );

// Record each failed attempt.
function jalaversity_record_login_failure(): void {
	$key  = jalaversity_login_transient_key();
	$data = get_transient( $key ) ?: [ 'count' => 0, 'expires' => 0 ];
	$data['count']++;
	// First 4 failures: reset window to 15 min. On 5th+: 30-min lockout.
	$ttl            = $data['count'] >= 5 ? 30 * MINUTE_IN_SECONDS : 15 * MINUTE_IN_SECONDS;
	$data['expires'] = time() + $ttl;
	set_transient( $key, $data, $ttl );
}
add_action( 'wp_login_failed', 'jalaversity_record_login_failure' );

// Clear counter on successful login.
function jalaversity_clear_login_attempts(): void {
	delete_transient( jalaversity_login_transient_key() );
}
add_action( 'wp_login', 'jalaversity_clear_login_attempts' );
