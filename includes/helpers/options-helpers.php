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
 * @param string $key     Option key within the jalaversity_options array.
 * @param mixed  $default Fallback value if the key does not exist.
 * @return mixed
 */
function jalaversity_get_option( string $key, mixed $default = '' ): mixed {
	static $options = null;

	if ( null === $options ) {
		$options = get_option( 'jalaversity_options', [] );
		if ( ! is_array( $options ) ) {
			$options = [];
		}
	}

	return $options[ $key ] ?? $default;
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
