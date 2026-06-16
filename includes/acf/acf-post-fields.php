<?php
/**
 * ACF Field Group — Post Meta
 *
 * Field tambahan untuk post_type "post": toggle "Berita Utama" (dibaca oleh
 * page-templates/page-blog.php untuk section featured) dan "Editor" (kredit
 * tambahan terpisah dari Author/penulis WP native).
 *
 * Berbeda dari includes/acf/acf-fields.php (flexible_content dengan banyak
 * layout saling-tukar untuk page builder) — field di sini FIXED pada
 * post_type, jadi tidak butuh pola render-bridge seperti acf-render.php.
 * Dibaca langsung via get_field() di template-parts/content/content-*.php.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

add_action( 'acf/init', function (): void {

	acf_add_local_field_group( [
		'key'        => 'group_jalaversity_post_meta',
		'title'      => __( 'Post Meta', 'jalaversity' ),
		'fields'     => [
			[
				'key'           => 'field_jalaversity_post_is_featured',
				'name'          => 'is_featured',
				'label'         => __( 'Tandai sebagai Berita Utama', 'jalaversity' ),
				'instructions'  => __( 'Post ini akan ditampilkan di section "Berita Utama" pada Halaman Blog.', 'jalaversity' ),
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
			],
			[
				'key'           => 'field_jalaversity_post_editor',
				'name'          => 'editor',
				'label'         => __( 'Editor', 'jalaversity' ),
				'instructions'  => __( 'Kredit editor (terpisah dari Penulis/Author WordPress). Kosongkan jika tidak ada.', 'jalaversity' ),
				'type'          => 'user',
				'role'          => [],
				'allow_null'    => 1,
				'return_format' => 'array',
			],
		],
		'location'   => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'post',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'side',
		'style'      => 'default',
		'active'     => true,
	] );

} );
