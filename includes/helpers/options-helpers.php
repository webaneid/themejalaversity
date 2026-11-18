<?php
/**
 * Options Helper Functions
 *
 * Wrapper for WordPress Options API with static cache to avoid
 * repeated get_option() database calls per page load.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a single theme option value with a fallback default.
 *
 * All theme options are stored as a single serialised array under the key
 * 'jalaversity_options'. The static cache means get_option() is called once
 * per request regardless of how many options are read.
 *
 * @param string $key           Option key within the jalaversity_options array.
 * @param mixed  $default_value Fallback value if the key does not exist.
 * @return mixed
 */
function jalaversity_get_option( string $key, mixed $default_value = '' ): mixed {
	static $options = null;

	if ( null === $options ) {
		$options = get_option( 'jalaversity_options', [] );
		if ( ! is_array( $options ) ) {
			$options = [];
		}
	}

	return $options[ $key ] ?? $default_value;
}

/**
 * Update a single theme option value.
 *
 * Always use this helper to update options so the static cache stays
 * consistent within the same request.
 *
 * @param string $key   Option key.
 * @param mixed  $value New value (must already be sanitised before calling).
 * @return bool         True on update, false if unchanged.
 */
function jalaversity_update_option( string $key, mixed $value ): bool {
	static $options = null;

	if ( null === $options ) {
		$options = get_option( 'jalaversity_options', [] );
		if ( ! is_array( $options ) ) {
			$options = [];
		}
	}

	$options[ $key ] = $value;

	return update_option( 'jalaversity_options', $options );
}

/**
 * Normalisasi & validasi URL embed Google Maps.
 *
 * Mendukung ekstraksi atribut `src="..."` secara otomatis apabila admin
 * menempelkan kode HTML `<iframe ...>` utuh dari Google Maps, serta
 * membatasi hanya skema `https://` pada domain resmi Google Maps.
 *
 * @param string $raw Input mentah URL atau potongan HTML iframe.
 * @return string URL embed yang valid atau string kosong.
 */
function jalaversity_clean_maps_embed_url( string $raw ): string {
	$raw = trim( $raw );
	if ( '' === $raw ) {
		return '';
	}

	if ( str_contains( $raw, '<iframe' ) && preg_match( '/\bsrc=["\']([^"\']+)["\']/i', $raw, $matches ) ) {
		$raw = html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' );
	}

	$url = esc_url_raw( $raw, [ 'https' ] );
	if ( '' === $url ) {
		return '';
	}

	$parsed = wp_parse_url( $url );
	$host   = isset( $parsed['host'] ) ? strtolower( $parsed['host'] ) : '';
	$path   = isset( $parsed['path'] ) ? $parsed['path'] : '';

	$allowed_hosts = [
		'www.google.com',
		'google.com',
		'maps.google.com',
		'www.google.co.id',
		'google.co.id',
	];

	if ( ! in_array( $host, $allowed_hosts, true ) ) {
		return '';
	}

	if ( 'maps.google.com' !== $host && ! str_starts_with( $path, '/maps' ) ) {
		return '';
	}

	return $url;
}
