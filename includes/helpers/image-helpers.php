<?php
/**
 * Image Helper Functions
 *
 * Utility functions for handling images: thumbnails, lazy loading,
 * srcset, inline SVG placeholders, dan auto-convert ukuran turunan ke
 * WebP kualitas 80% (lihat docs/02-architecture.md §10). Ukuran custom
 * sendiri diregister di includes/setup.php — JALAVERSITY_IMAGE_SIZES di
 * sini cuma cermin dimensinya untuk keperluan placeholder.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registered custom image size dimensions — harus sinkron dengan
 * add_image_size() di includes/setup.php.
 */
const JALAVERSITY_IMAGE_SIZES = [
	'jalaversity-large'     => [ 1120, 630 ], // 16:9
	'jalaversity-medium'    => [ 800, 450 ],  // 16:9
	'jalaversity-thumbnail' => [ 400, 225 ],  // 16:9
	'jalaversity-square'    => [ 400, 400 ],  // 1:1
];

/**
 * Get a post's featured image HTML with lazy loading and a fallback.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Registered image size slug.
 * @param array  $attr    Additional HTML attributes for the <img> tag.
 * @return string         HTML <img> tag or inline SVG placeholder.
 */
function jalaversity_get_thumbnail( int $post_id, string $size = 'jalaversity-medium', array $attr = [] ): string {

	$default_attr = [
		'loading' => 'lazy',
		'class'   => 'w-full h-full object-cover',
	];

	$attr = array_merge( $default_attr, $attr );

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attr );
	}

	return jalaversity_get_placeholder_image( $size );
}

/**
 * Return an inline SVG placeholder when no featured image exists.
 *
 * Using an inline SVG data-URI means zero additional HTTP requests.
 *
 * @param string $size Registered image size slug.
 * @return string      HTML <img> tag with data-URI src.
 */
function jalaversity_get_placeholder_image( string $size = 'jalaversity-medium' ): string {

	$dimensions = JALAVERSITY_IMAGE_SIZES[ $size ] ?? [ 800, 450 ];
	[ $width, $height ] = $dimensions;

	$svg = sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d">'
		. '<rect width="100%%" height="100%%" fill="#f0ebe0"/>'
		. '<text x="50%%" y="50%%" font-family="sans-serif" font-size="14" fill="#b68c2e" '
		. 'text-anchor="middle" dominant-baseline="middle">No Image</text>'
		. '</svg>',
		$width,
		$height
	);

	return sprintf(
		'<img src="%s" width="%d" height="%d" alt="%s" class="w-full h-full object-cover" loading="lazy">',
		esc_attr( 'data:image/svg+xml;base64,' . base64_encode( $svg ) ),
		esc_attr( (string) $width ),
		esc_attr( (string) $height ),
		esc_attr__( 'Placeholder image', 'jalaversity' )
	);
}

/**
 * Get the URL of a post's featured image.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Registered image size slug.
 * @return string         Image URL, or empty string if no thumbnail.
 */
function jalaversity_get_thumbnail_url( int $post_id, string $size = 'jalaversity-medium' ): string {

	if ( ! has_post_thumbnail( $post_id ) ) {
		return '';
	}

	$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );

	return $src ? esc_url( $src[0] ) : '';
}

/**
 * Output a responsive <img> with srcset for a given attachment ID.
 *
 * @param int    $attachment_id Attachment post ID.
 * @param string $size          Registered image size slug.
 * @param string $alt           Alt text.
 * @param array  $attr          Extra attributes.
 * @return string               Full <img> HTML.
 */
function jalaversity_responsive_image( int $attachment_id, string $size = 'jalaversity-medium', string $alt = '', array $attr = [] ): string {

	if ( ! $attachment_id ) {
		return '';
	}

	$default_attr = [
		'loading' => 'lazy',
		'class'   => 'w-full h-full object-cover',
		'alt'     => $alt ?: get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	];

	return wp_get_attachment_image( $attachment_id, $size, false, array_merge( $default_attr, $attr ) );
}

/**
 * Konversi ukuran turunan (large/medium/thumbnail/square, dst — bukan
 * file asli yang diupload) ke WebP — format yang direkomendasikan Google,
 * ukuran file lebih kecil tanpa kehilangan kualitas signifikan.
 *
 * Hanya aktif kalau image editor server (GD/Imagick) benar-benar bisa
 * output WebP — kalau tidak didukung, format asli dipertahankan (graceful
 * degradation, bukan fatal error). GIF sengaja TIDAK dikonversi supaya
 * animasi tidak hilang.
 *
 * @param array $formats Mime type asal → mime type output.
 * @return array
 */
function jalaversity_webp_output_format( array $formats ): array {
	if ( ! wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ) {
		return $formats;
	}

	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';

	return $formats;
}
add_filter( 'image_editor_output_format', 'jalaversity_webp_output_format' );

/**
 * Kualitas WebP 80% untuk ukuran turunan — titik seimbang ukuran file vs
 * tampilan, sesuai permintaan eksplisit (bukan default WP 82%).
 *
 * @param int    $quality   Kualitas default WP untuk mime type ini.
 * @param string $mime_type Mime type output yang sedang digenerate.
 * @return int
 */
function jalaversity_webp_quality( int $quality, string $mime_type ): int {
	return ( 'image/webp' === $mime_type ) ? 80 : $quality;
}
add_filter( 'wp_image_editor_default_quality', 'jalaversity_webp_quality', 10, 2 );
