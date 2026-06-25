<?php
/**
 * Settings Sanitization & Validation
 *
 * Single sanitize callback for the whole `jalaversity_options` array,
 * driven by the field schema in settings-fields.php.
 *
 * Each tab's form only submits its own fields (see settings-page.php —
 * one <form> per tab, same option_group). Without merging onto the
 * existing stored value, saving Tab A would wipe out every other tab's
 * data. So this callback only touches keys present in $input and merges
 * the result onto whatever is already saved.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize submitted settings, merged onto the existing stored option.
 *
 * @param mixed $input Raw submitted value for 'jalaversity_options'.
 * @return array
 */
function jalaversity_sanitize_options( mixed $input ): array {
	$existing = get_option( 'jalaversity_options', [] );
	if ( ! is_array( $existing ) ) {
		$existing = [];
	}
	if ( ! is_array( $input ) ) {
		$input = [];
	}

	$sanitized = [];

	foreach ( jalaversity_settings_schema() as $tab ) {
		// Tab dengan custom render (misal tim_layanan) tidak punya sections
		if ( isset( $tab['render'] ) ) {
			continue;
		}

		foreach ( $tab['sections'] as $section ) {
			foreach ( $section['fields'] as $field ) {
				$key = $field['key'];

				if ( ! array_key_exists( $key, $input ) ) {
					continue;
				}

				$sanitized[ $key ] = jalaversity_sanitize_field_value( $input[ $key ], $field['type'] ?? 'text' );
			}
		}
	}

	// Sanitasi tab Tim Layanan
	if ( array_key_exists( 'wa_default_message', $input ) ) {
		$sanitized['wa_default_message'] = sanitize_textarea_field( $input['wa_default_message'] );
	}

	if ( array_key_exists( 'tim_layanan_contacts', $input ) && is_array( $input['tim_layanan_contacts'] ) ) {
		$clean_contacts = [];
		foreach ( $input['tim_layanan_contacts'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$nomor = preg_replace( '/[^0-9]/', '', $row['whatsapp'] ?? '' );
			if ( ! $nomor ) {
				continue; // baris tanpa nomor WA dibuang
			}
			$clean_contacts[] = [
				'nama'     => sanitize_text_field( $row['nama'] ?? '' ),
				'jabatan'  => sanitize_text_field( $row['jabatan'] ?? '' ),
				'whatsapp' => $nomor,
				'photo'    => absint( $row['photo'] ?? 0 ),
			];
		}
		$sanitized['tim_layanan_contacts'] = $clean_contacts;
	}

	return array_merge( $existing, $sanitized );
}

/**
 * Sanitize a single field value according to its declared type.
 *
 * @param mixed  $value Raw value.
 * @param string $type  Field type (text|textarea|url|email|tel|color|image).
 * @return mixed
 */
function jalaversity_sanitize_field_value( mixed $value, string $type ): mixed {
	switch ( $type ) {
		case 'textarea':
			return sanitize_textarea_field( $value );
		case 'url':
			return esc_url_raw( $value );
		case 'email':
			return sanitize_email( $value );
		case 'color':
			return sanitize_hex_color( $value ) ?: '';
		case 'image':
			return absint( $value );
		case 'tel':
			return sanitize_text_field( $value );
		default:
			return sanitize_text_field( $value );
	}
}
