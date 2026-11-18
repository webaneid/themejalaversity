<?php
/**
 * PHPStan bootstrap file for Jalaversity Theme.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'JALAVERSITY_VERSION' ) ) {
	define( 'JALAVERSITY_VERSION', '1.1.1' );
}

if ( ! defined( 'JALAVERSITY_DIR' ) ) {
	define( 'JALAVERSITY_DIR', __DIR__ );
}

if ( ! defined( 'JALAVERSITY_URI' ) ) {
	define( 'JALAVERSITY_URI', 'https://example.com/wp-content/themes/jalaversity' );
}

if ( ! defined( 'JALAVERSITY_UPDATER_REPO' ) ) {
	define( 'JALAVERSITY_UPDATER_REPO', 'webaneid/themejalaversity' );
}

if ( ! defined( 'JALAVERSITY_UPDATER_SLUG' ) ) {
	define( 'JALAVERSITY_UPDATER_SLUG', 'jalaversity' );
}

if ( ! function_exists( 'get_field' ) ) {
	/**
	 * @param string     $selector
	 * @param int|string $post_id
	 * @param bool       $format_value
	 * @return mixed
	 */
	function get_field( $selector, $post_id = false, $format_value = true ) {
		return null;
	}
}

if ( ! function_exists( 'the_field' ) ) {
	/**
	 * @param string     $selector
	 * @param int|string $post_id
	 * @param bool       $format_value
	 * @return void
	 */
	function the_field( $selector, $post_id = false, $format_value = true ) {}
}

if ( ! function_exists( 'have_rows' ) ) {
	/**
	 * @param string     $selector
	 * @param int|string $post_id
	 * @return bool
	 */
	function have_rows( $selector, $post_id = false ) {
		return (bool) random_int( 0, 1 );
	}
}

if ( ! function_exists( 'the_row' ) ) {
	/**
	 * @param bool $format
	 * @return array<string, mixed>|false
	 */
	function the_row( $format = false ) {
		return [];
	}
}

if ( ! function_exists( 'get_row_layout' ) ) {
	/**
	 * @return string|false
	 */
	function get_row_layout() {
		return '';
	}
}

if ( ! function_exists( 'get_sub_field' ) ) {
	/**
	 * @param string $selector
	 * @param bool   $format_value
	 * @return mixed
	 */
	function get_sub_field( $selector, $format_value = true ) {
		return null;
	}
}

if ( ! function_exists( 'the_sub_field' ) ) {
	/**
	 * @param string $selector
	 * @param bool   $format_value
	 * @return void
	 */
	function the_sub_field( $selector, $format_value = true ) {}
}

if ( ! function_exists( 'update_field' ) ) {
	/**
	 * @param string     $selector
	 * @param mixed      $value
	 * @param int|string $post_id
	 * @return int|bool
	 */
	function update_field( $selector, $value, $post_id = false ) {
		return true;
	}
}

if ( ! function_exists( 'acf_add_options_page' ) ) {
	/**
	 * @param array<string, mixed>|string $page
	 * @return array<string, mixed>|false
	 */
	function acf_add_options_page( $page = '' ) {
		return [];
	}
}

if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
	/**
	 * @param array<string, mixed>|string $page
	 * @return array<string, mixed>|false
	 */
	function acf_add_options_sub_page( $page = '' ) {
		return [];
	}
}

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	/**
	 * @param array<string, mixed> $field_group
	 * @return bool
	 */
	function acf_add_local_field_group( $field_group ) {
		return true;
	}
}
