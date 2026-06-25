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

add_action( 'acf/init', function (): void {

	acf_add_local_field_group( [
		'key'      => 'group_jalaversity_agenda',
		'title'    => __( 'Detail Agenda', 'jalaversity' ),
		'fields'   => [

			// ── Tanggal & Waktu ───────────────────────────────────────────
			[
				'key'            => 'field_agenda_tanggal_mulai',
				'name'           => 'tanggal_mulai',
				'label'          => __( 'Tanggal Mulai', 'jalaversity' ),
				'type'           => 'date_picker',
				'required'       => 1,
				'return_format'  => 'Y-m-d',
				'display_format' => 'd/m/Y',
				'first_day'      => 1,
			],
			[
				'key'            => 'field_agenda_tanggal_selesai',
				'name'           => 'tanggal_selesai',
				'label'          => __( 'Tanggal Selesai', 'jalaversity' ),
				'instructions'   => __( 'Isi jika event berlangsung lebih dari satu hari.', 'jalaversity' ),
				'type'           => 'date_picker',
				'required'       => 0,
				'return_format'  => 'Y-m-d',
				'display_format' => 'd/m/Y',
				'first_day'      => 1,
			],
			[
				'key'            => 'field_agenda_jam_mulai',
				'name'           => 'jam_mulai',
				'label'          => __( 'Jam Mulai', 'jalaversity' ),
				'type'           => 'time_picker',
				'required'       => 1,
				'return_format'  => 'H:i',
				'display_format' => 'H:i',
			],
			[
				'key'            => 'field_agenda_jam_selesai',
				'name'           => 'jam_selesai',
				'label'          => __( 'Jam Selesai', 'jalaversity' ),
				'type'           => 'time_picker',
				'required'       => 0,
				'return_format'  => 'H:i',
				'display_format' => 'H:i',
			],

			// ── Lokasi ────────────────────────────────────────────────────
			[
				'key'           => 'field_agenda_tipe_lokasi',
				'name'          => 'tipe_lokasi',
				'label'         => __( 'Tipe Lokasi', 'jalaversity' ),
				'type'          => 'select',
				'required'      => 1,
				'choices'       => [
					'offline' => __( 'Offline', 'jalaversity' ),
					'online'  => __( 'Online', 'jalaversity' ),
					'hybrid'  => __( 'Hybrid (Offline + Online)', 'jalaversity' ),
				],
				'default_value' => 'offline',
			],
			[
				'key'          => 'field_agenda_lokasi',
				'name'         => 'lokasi',
				'label'        => __( 'Tempat / Gedung', 'jalaversity' ),
				'instructions' => __( 'Misal: Aula Utama Gedung A, Kampus Al-Ikhlash.', 'jalaversity' ),
				'type'         => 'text',
				'required'     => 0,
				'conditional_logic' => [
					[
						[
							'field'    => 'field_agenda_tipe_lokasi',
							'operator' => '!=',
							'value'    => 'online',
						],
					],
				],
			],
			[
				'key'          => 'field_agenda_link_online',
				'name'         => 'link_online',
				'label'        => __( 'Link Meeting / Live Stream', 'jalaversity' ),
				'type'         => 'url',
				'required'     => 0,
				'conditional_logic' => [
					[
						[
							'field'    => 'field_agenda_tipe_lokasi',
							'operator' => '!=',
							'value'    => 'offline',
						],
					],
				],
			],

			// ── Narasumber ────────────────────────────────────────────────
			[
				'key'          => 'field_agenda_narasumber',
				'name'         => 'narasumber',
				'label'        => __( 'Narasumber / Pembicara', 'jalaversity' ),
				'instructions' => __( 'Kosongkan jika tidak ada pembicara khusus (misal: upacara wisuda).', 'jalaversity' ),
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => __( 'Tambah Narasumber', 'jalaversity' ),
				'sub_fields'   => [
					[
						'key'      => 'field_agenda_narasumber_nama',
						'name'     => 'nama',
						'label'    => __( 'Nama', 'jalaversity' ),
						'type'     => 'text',
						'required' => 1,
					],
					[
						'key'   => 'field_agenda_narasumber_jabatan',
						'name'  => 'jabatan',
						'label' => __( 'Jabatan / Institusi', 'jalaversity' ),
						'type'  => 'text',
					],
				],
			],

			// ── Info tambahan ─────────────────────────────────────────────
			[
				'key'          => 'field_agenda_penyelenggara',
				'name'         => 'penyelenggara',
				'label'        => __( 'Penyelenggara', 'jalaversity' ),
				'instructions' => __( 'Unit atau prodi yang menyelenggarakan. Misal: Prodi Manajemen Haji & Umrah.', 'jalaversity' ),
				'type'         => 'text',
			],
			[
				'key'   => 'field_agenda_link_pendaftaran',
				'name'  => 'link_pendaftaran',
				'label' => __( 'Link Pendaftaran', 'jalaversity' ),
				'type'  => 'url',
			],
			[
				'key'          => 'field_agenda_kuota',
				'name'         => 'kuota',
				'label'        => __( 'Kuota Peserta', 'jalaversity' ),
				'instructions' => __( 'Kosongkan jika tidak ada batasan kuota.', 'jalaversity' ),
				'type'         => 'number',
				'min'          => 1,
			],
		],
		'location'   => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'agenda',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'normal',
		'style'      => 'default',
		'active'     => true,
	] );

} );
