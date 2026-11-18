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
 * @return array<string, mixed>
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
		// Tab dengan custom render (misal tim_layanan) tidak punya sections.
		if ( isset( $tab['render'] ) ) {
			continue;
		}

		foreach ( $tab['sections'] as $section ) {
			foreach ( $section['fields'] as $field ) {
				$key = $field['key'];

				if ( ! array_key_exists( $key, $input ) ) {
					continue;
				}

				if ( 'contact_maps_url' === $key ) {
					$sanitized[ $key ] = jalaversity_clean_maps_embed_url( (string) $input[ $key ] );
					continue;
				}

				if ( in_array( $key, [ 'gsc_verification', 'yandex_verification', 'bing_verification', 'fb_domain_verify', 'gtm_id', 'ga4_id', 'yandex_metrica_id', 'fb_pixel_id', 'tiktok_pixel_id' ], true ) ) {
					$sanitized[ $key ] = jalaversity_clean_tracking_field( $key, (string) $input[ $key ] );
					continue;
				}

				$sanitized[ $key ] = jalaversity_sanitize_field_value( $input[ $key ], $field['type'] ?? 'text' );
			}
		}
	}

	// Sanitasi tab Tim Layanan.
	if ( array_key_exists( 'wa_default_message', $input ) ) {
		$sanitized['wa_default_message'] = sanitize_textarea_field( (string) $input['wa_default_message'] );
	}

	if ( array_key_exists( 'tim_layanan_contacts', $input ) && is_array( $input['tim_layanan_contacts'] ) ) {
		$clean_contacts = [];
		foreach ( $input['tim_layanan_contacts'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$nomor = preg_replace( '/[^0-9]/', '', (string) ( $row['whatsapp'] ?? '' ) );
			if ( ! $nomor ) {
				continue; // Baris tanpa nomor WA dibuang.
			}
			$clean_contacts[] = [
				'nama'     => sanitize_text_field( (string) ( $row['nama'] ?? '' ) ),
				'jabatan'  => sanitize_text_field( (string) ( $row['jabatan'] ?? '' ) ),
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
 * @param string $type  Field type (text|textarea|url|email|tel|color|image|password).
 * @return mixed
 */
function jalaversity_sanitize_field_value( mixed $value, string $type ): mixed {
	switch ( $type ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'url':
			return esc_url_raw( (string) $value );
		case 'email':
			return sanitize_email( (string) $value );
		case 'color':
			return sanitize_hex_color( (string) $value ) ?: '';
		case 'image':
			return absint( $value );
		case 'password':
			return trim( (string) preg_replace( '/[\r\n\t\0\x0B]/', '', (string) $value ) );
		case 'tel':
		default:
			return sanitize_text_field( (string) $value );
	}
}
