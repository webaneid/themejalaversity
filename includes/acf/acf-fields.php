<?php
/**
 * ACF Field Group — Page Sections (Flexible Content)
 *
 * Mendaftarkan field "page_sections" untuk page-templates/page-dynamic.php
 * ("Template Name: Halaman Dinamis"). Tiap layout di sini punya field yang
 * 1:1 mapping ke kontrak $args komponen generik di template-parts/components/
 * (lihat docs/02-architecture.md §9). Didaftarkan via kode supaya skema
 * version-controlled, bukan via UI admin ACF / export JSON.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

/**
 * Daftar nama icon yang tersedia (lihat includes/helpers/icon-helpers.php).
 * Dipakai berulang di semua field select bertipe icon.
 *
 * @return array<string,string>
 */
function jalaversity_acf_icon_choices(): array {
	$icons = [
		'academic-cap', 'arrow-left', 'arrow-right', 'beaker', 'bell', 'book-open',
		'building-library', 'building-office', 'calendar', 'chat', 'chat-quote',
		'check', 'check-circle', 'chevron-down', 'chevron-left', 'chevron-right',
		'chevron-up', 'clock', 'document-text', 'dots-vertical', 'external-link',
		'globe', 'hand-raised', 'heart', 'info', 'language', 'link', 'mail',
		'map-pin', 'menu', 'minus', 'newspaper', 'phone', 'photo', 'play', 'plus',
		'search', 'shield-check', 'sparkles', 'star', 'trophy', 'user', 'users',
		'x-mark',
	];

	return array_combine( $icons, $icons );
}

/**
 * Sub-field "link" generik (label + url + buka tab baru). Dipakai di
 * beberapa layout (content_media, card_grid items, cta_banner) — komponen
 * tujuan menerima ['label','url','external'=>bool], bukan native ACF link
 * field (shape-nya beda: title/url/target).
 *
 * @param string $key   Key unik (prefix dari field pemanggil).
 * @param string $name  Nama sub_field.
 * @param string $label Label di admin.
 * @return array
 */
function jalaversity_acf_link_field( string $key, string $name, string $label ): array {
	return [
		'key'        => $key,
		'name'       => $name,
		'label'      => $label,
		'type'       => 'group',
		'sub_fields' => [
			[
				'key'   => $key . '_label',
				'name'  => 'label',
				'label' => __( 'Teks Tombol/Link', 'jalaversity' ),
				'type'  => 'text',
			],
			[
				'key'   => $key . '_url',
				'name'  => 'url',
				'label' => __( 'URL', 'jalaversity' ),
				'type'  => 'url',
			],
			[
				'key'   => $key . '_external',
				'name'  => 'external',
				'label' => __( 'Buka di Tab Baru', 'jalaversity' ),
				'type'  => 'true_false',
				'ui'    => 1,
			],
		],
	];
}

add_action( 'acf/init', function (): void {

	$icon_choices = jalaversity_acf_icon_choices();

	acf_add_local_field_group( [
		'key'          => 'group_jalaversity_page_sections',
		'title'        => __( 'Page Sections', 'jalaversity' ),
		'fields'       => [
			[
				'key'          => 'field_jalaversity_page_sections',
				'name'         => 'page_sections',
				'label'        => __( 'Sections', 'jalaversity' ),
				'type'         => 'flexible_content',
				'button_label' => __( 'Tambah Section', 'jalaversity' ),
				'layouts'      => [

					// ── Hero ──────────────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_hero',
						'name'       => 'hero',
						'label'      => __( 'Hero', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'     => 'field_hero_variant',
								'name'    => 'variant',
								'label'   => __( 'Varian', 'jalaversity' ),
								'type'    => 'button_group',
								'choices' => [
									'home'    => __( 'Home (badge + search + trust)', 'jalaversity' ),
									'subpage' => __( 'Subpage (breadcrumb)', 'jalaversity' ),
								],
								'default_value' => 'subpage',
							],
							[
								'key'   => 'field_hero_badge',
								'name'  => 'badge',
								'label' => __( 'Teks Badge', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'      => 'field_hero_heading',
								'name'     => 'heading',
								'label'    => __( 'Heading (H1)', 'jalaversity' ),
								'type'     => 'text',
								'required' => 1,
							],
							[
								'key'         => 'field_hero_highlight',
								'name'        => 'highlight',
								'label'       => __( 'Frase Highlight', 'jalaversity' ),
								'instructions' => __( 'Frase dalam heading di atas yang diberi warna gold. Harus sama persis (case-sensitive).', 'jalaversity' ),
								'type'        => 'text',
							],
							[
								'key'   => 'field_hero_lead',
								'name'  => 'lead',
								'label' => __( 'Lead Paragraph', 'jalaversity' ),
								'type'  => 'textarea',
								'rows'  => 2,
							],
							[
								'key'           => 'field_hero_image',
								'name'          => 'image',
								'label'         => __( 'Gambar', 'jalaversity' ),
								'type'          => 'image',
								'return_format' => 'id',
							],
							[
								'key'           => 'field_hero_show_search',
								'name'          => 'show_search',
								'label'         => __( 'Tampilkan Form Pencarian', 'jalaversity' ),
								'type'          => 'true_false',
								'ui'            => 1,
								'default_value' => 0,
							],
							[
								'key'          => 'field_hero_trust_items',
								'name'         => 'trust_items',
								'label'        => __( 'Trust Badges', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Tambah Item', 'jalaversity' ),
								'sub_fields'   => [
									[
										'key'   => 'field_hero_trust_text',
										'name'  => 'text',
										'label' => __( 'Teks', 'jalaversity' ),
										'type'  => 'text',
									],
								],
							],
							[
								'key'        => 'field_hero_floating_badge',
								'name'       => 'floating_badge',
								'label'      => __( 'Floating Badge', 'jalaversity' ),
								'type'       => 'group',
								'instructions' => __( 'Kosongkan "Value" jika tidak ingin menampilkan floating badge.', 'jalaversity' ),
								'sub_fields' => [
									[
										'key'     => 'field_hero_fb_icon',
										'name'    => 'icon',
										'label'   => __( 'Icon', 'jalaversity' ),
										'type'    => 'select',
										'choices' => $icon_choices,
										'default_value' => 'trophy',
									],
									[
										'key'   => 'field_hero_fb_label',
										'name'  => 'label',
										'label' => __( 'Label', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_hero_fb_value',
										'name'  => 'value',
										'label' => __( 'Value', 'jalaversity' ),
										'type'  => 'text',
									],
								],
							],
							[
								'key'          => 'field_hero_buttons',
								'name'         => 'buttons',
								'label'        => __( 'Tombol CTA', 'jalaversity' ),
								'instructions' => __( 'Maks 2 tombol. Kosongkan jika hero ini pakai search form, bukan tombol.', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'Tambah Tombol', 'jalaversity' ),
								'max'          => 2,
								'sub_fields'   => [
									[
										'key'   => 'field_hero_btn_label',
										'name'  => 'label',
										'label' => __( 'Teks Tombol', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_hero_btn_url',
										'name'  => 'url',
										'label' => __( 'URL', 'jalaversity' ),
										'type'  => 'url',
									],
									[
										'key'     => 'field_hero_btn_style',
										'name'    => 'style',
										'label'   => __( 'Gaya', 'jalaversity' ),
										'type'    => 'select',
										'choices' => [
											'primary' => __( 'Primary (gold solid)', 'jalaversity' ),
											'ghost'   => __( 'Ghost (outline putih)', 'jalaversity' ),
										],
										'default_value' => 'primary',
									],
									[
										'key'   => 'field_hero_btn_external',
										'name'  => 'external',
										'label' => __( 'Buka di Tab Baru', 'jalaversity' ),
										'type'  => 'true_false',
										'ui'    => 1,
									],
								],
							],
							[
								'key'           => 'field_hero_show_breadcrumb',
								'name'          => 'show_breadcrumb',
								'label'         => __( 'Tampilkan Breadcrumb', 'jalaversity' ),
								'type'          => 'true_false',
								'ui'            => 1,
								'default_value' => 1,
							],
						],
					],

					// ── Stats Bar ─────────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_stats_bar',
						'name'       => 'stats_bar',
						'label'      => __( 'Stats Bar', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'          => 'field_stats_items',
								'name'         => 'items',
								'label'        => __( 'Statistik', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Tambah Statistik', 'jalaversity' ),
								'sub_fields'   => [
									[
										'key'     => 'field_stats_icon',
										'name'    => 'icon',
										'label'   => __( 'Icon', 'jalaversity' ),
										'type'    => 'select',
										'choices' => $icon_choices,
									],
									[
										'key'   => 'field_stats_value',
										'name'  => 'value',
										'label' => __( 'Value', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_stats_label',
										'name'  => 'label',
										'label' => __( 'Label', 'jalaversity' ),
										'type'  => 'text',
									],
								],
							],
						],
					],

					// ── Content + Media ───────────────────────────────────
					[
						'key'        => 'layout_jalaversity_content_media',
						'name'       => 'content_media',
						'label'      => __( 'Content + Media', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'   => 'field_cm_label',
								'name'  => 'label',
								'label' => __( 'Section Label', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'      => 'field_cm_heading',
								'name'     => 'heading',
								'label'    => __( 'Heading', 'jalaversity' ),
								'type'     => 'text',
								'required' => 1,
							],
							[
								'key'   => 'field_cm_body',
								'name'  => 'body',
								'label' => __( 'Body', 'jalaversity' ),
								'type'  => 'textarea',
								'rows'  => 3,
							],
							[
								'key'           => 'field_cm_image',
								'name'          => 'image',
								'label'         => __( 'Gambar', 'jalaversity' ),
								'type'          => 'image',
								'return_format' => 'id',
							],
							[
								'key'           => 'field_cm_image_position',
								'name'          => 'image_position',
								'label'         => __( 'Posisi Gambar', 'jalaversity' ),
								'type'          => 'button_group',
								'choices'       => [
									'left'  => __( 'Kiri', 'jalaversity' ),
									'right' => __( 'Kanan', 'jalaversity' ),
								],
								'default_value' => 'left',
							],
							[
								'key'           => 'field_cm_image_radius',
								'name'          => 'image_radius',
								'label'         => __( 'Radius Gambar', 'jalaversity' ),
								'type'          => 'select',
								'choices'       => [
									'default' => __( 'Default', 'jalaversity' ),
									'about'   => __( 'About (radius khusus)', 'jalaversity' ),
								],
								'default_value' => 'default',
							],
							[
								'key'           => 'field_cm_bg',
								'name'          => 'bg',
								'label'         => __( 'Background', 'jalaversity' ),
								'type'          => 'select',
								'choices'       => [
									'cream'   => __( 'Cream', 'jalaversity' ),
									'surface' => __( 'Surface', 'jalaversity' ),
								],
								'default_value' => 'cream',
							],
							[
								'key'        => 'field_cm_corner_badge',
								'name'       => 'corner_badge',
								'label'      => __( 'Corner Badge', 'jalaversity' ),
								'type'       => 'group',
								'instructions' => __( 'Kosongkan "Value" jika tidak ingin menampilkan badge ini.', 'jalaversity' ),
								'sub_fields' => [
									[
										'key'   => 'field_cm_cb_value',
										'name'  => 'value',
										'label' => __( 'Value', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_cm_cb_label',
										'name'  => 'label',
										'label' => __( 'Label', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'     => 'field_cm_cb_position',
										'name'    => 'position',
										'label'   => __( 'Posisi', 'jalaversity' ),
										'type'    => 'select',
										'choices' => [
											'top-right'   => __( 'Kanan Atas', 'jalaversity' ),
											'bottom-left' => __( 'Kiri Bawah', 'jalaversity' ),
										],
										'default_value' => 'top-right',
									],
									[
										'key'     => 'field_cm_cb_variant',
										'name'    => 'variant',
										'label'   => __( 'Warna', 'jalaversity' ),
										'type'    => 'select',
										'choices' => [
											'dark' => __( 'Dark', 'jalaversity' ),
											'gold' => __( 'Gold', 'jalaversity' ),
										],
										'default_value' => 'dark',
									],
								],
							],
							[
								'key'          => 'field_cm_items',
								'name'         => 'items',
								'label'        => __( 'Icon List Items', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Tambah Item', 'jalaversity' ),
								'sub_fields'   => [
									[
										'key'     => 'field_cm_item_icon',
										'name'    => 'icon',
										'label'   => __( 'Icon', 'jalaversity' ),
										'type'    => 'select',
										'choices' => $icon_choices,
									],
									[
										'key'   => 'field_cm_item_title',
										'name'  => 'title',
										'label' => __( 'Judul', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_cm_item_desc',
										'name'  => 'desc',
										'label' => __( 'Deskripsi', 'jalaversity' ),
										'type'  => 'textarea',
										'rows'  => 2,
									],
								],
							],
							[
								'key'           => 'field_cm_items_layout',
								'name'          => 'items_layout',
								'label'         => __( 'Layout Icon List', 'jalaversity' ),
								'type'          => 'select',
								'choices'       => [
									'grid' => __( 'Grid', 'jalaversity' ),
									'rows' => __( 'Rows', 'jalaversity' ),
								],
								'default_value' => 'grid',
							],
							jalaversity_acf_link_field( 'field_cm_link', 'link', __( 'Link Bawah Konten', 'jalaversity' ) ),
						],
					],

					// ── Card Grid ─────────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_card_grid',
						'name'       => 'card_grid',
						'label'      => __( 'Card Grid', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'   => 'field_cg_label',
								'name'  => 'label',
								'label' => __( 'Section Label', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_cg_heading',
								'name'  => 'heading',
								'label' => __( 'Heading', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_cg_lead',
								'name'  => 'lead',
								'label' => __( 'Lead Paragraph', 'jalaversity' ),
								'type'  => 'textarea',
								'rows'  => 2,
							],
							[
								'key'           => 'field_cg_center',
								'name'          => 'center',
								'label'         => __( 'Header Center', 'jalaversity' ),
								'type'          => 'true_false',
								'ui'            => 1,
								'default_value' => 1,
							],
							[
								'key'           => 'field_cg_min_card_width',
								'name'          => 'min_card_width',
								'label'         => __( 'Lebar Minimum Kartu (px)', 'jalaversity' ),
								'type'          => 'text',
								'default_value' => '330px',
							],
							[
								'key'           => 'field_cg_dark',
								'name'          => 'dark',
								'label'         => __( 'Varian Dark/Glass', 'jalaversity' ),
								'instructions'  => __( 'Aktifkan untuk section di atas background hijau tua dengan girih pattern (mis. "Keunggulan").', 'jalaversity' ),
								'type'          => 'true_false',
								'ui'            => 1,
								'default_value' => 0,
							],
							[
								'key'          => 'field_cg_items',
								'name'         => 'items',
								'label'        => __( 'Kartu', 'jalaversity' ),
								'instructions' => __( 'Tambah/hapus kartu sesuai kebutuhan — cocok untuk daftar Prodi/Fakultas/Kampus yang jumlahnya tidak tetap.', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'Tambah Kartu', 'jalaversity' ),
								'min'          => 1,
								'sub_fields'   => [
									[
										'key'     => 'field_cg_item_icon',
										'name'    => 'icon',
										'label'   => __( 'Icon', 'jalaversity' ),
										'type'    => 'select',
										'choices' => $icon_choices,
										'allow_null' => 1,
									],
									[
										'key'           => 'field_cg_item_image',
										'name'          => 'image',
										'label'         => __( 'Foto', 'jalaversity' ),
										'type'          => 'image',
										'return_format' => 'id',
									],
									[
										'key'      => 'field_cg_item_title',
										'name'     => 'title',
										'label'    => __( 'Judul', 'jalaversity' ),
										'type'     => 'text',
										'required' => 1,
									],
									[
										'key'   => 'field_cg_item_desc',
										'name'  => 'desc',
										'label' => __( 'Deskripsi', 'jalaversity' ),
										'type'  => 'textarea',
										'rows'  => 2,
									],
									[
										'key'   => 'field_cg_item_address',
										'name'  => 'address',
										'label' => __( 'Alamat', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_cg_item_code',
										'name'  => 'code',
										'label' => __( 'Kode (mis. kode Prodi)', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_cg_item_badge',
										'name'  => 'badge',
										'label' => __( 'Teks Badge (mis. Akreditasi)', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'     => 'field_cg_item_badge_variant',
										'name'    => 'badge_variant',
										'label'   => __( 'Warna Badge', 'jalaversity' ),
										'type'    => 'select',
										'choices' => [
											'green' => __( 'Green', 'jalaversity' ),
											'gold'  => __( 'Gold', 'jalaversity' ),
										],
										'default_value' => 'green',
									],
									[
										'key'          => 'field_cg_item_meta',
										'name'         => 'meta',
										'label'        => __( 'Meta (mis. Jenjang/Gelar)', 'jalaversity' ),
										'type'         => 'repeater',
										'layout'       => 'table',
										'button_label' => __( 'Tambah Meta', 'jalaversity' ),
										'sub_fields'   => [
											[
												'key'   => 'field_cg_item_meta_label',
												'name'  => 'label',
												'label' => __( 'Label', 'jalaversity' ),
												'type'  => 'text',
											],
											[
												'key'   => 'field_cg_item_meta_value',
												'name'  => 'value',
												'label' => __( 'Value', 'jalaversity' ),
												'type'  => 'text',
											],
										],
									],
									jalaversity_acf_link_field( 'field_cg_item_link', 'link', __( 'Link', 'jalaversity' ) ),
								],
							],
						],
					],

					// ── Numbered Steps ────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_numbered_steps',
						'name'       => 'numbered_steps',
						'label'      => __( 'Numbered Steps', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'   => 'field_ns_label',
								'name'  => 'label',
								'label' => __( 'Section Label', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_ns_heading',
								'name'  => 'heading',
								'label' => __( 'Heading', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_ns_lead',
								'name'  => 'lead',
								'label' => __( 'Lead Paragraph', 'jalaversity' ),
								'type'  => 'textarea',
								'rows'  => 2,
							],
							[
								'key'           => 'field_ns_variant',
								'name'          => 'variant',
								'label'         => __( 'Varian', 'jalaversity' ),
								'type'          => 'select',
								'choices'       => [
									'light'   => __( 'Light', 'jalaversity' ),
									'on-dark' => __( 'On Dark', 'jalaversity' ),
								],
								'default_value' => 'light',
							],
							[
								'key'          => 'field_ns_items',
								'name'         => 'items',
								'label'        => __( 'Langkah', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'Tambah Langkah', 'jalaversity' ),
								'min'          => 1,
								'sub_fields'   => [
									[
										'key'   => 'field_ns_item_number',
										'name'  => 'number',
										'label' => __( 'Nomor', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_ns_item_title',
										'name'  => 'title',
										'label' => __( 'Judul', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_ns_item_desc',
										'name'  => 'desc',
										'label' => __( 'Deskripsi', 'jalaversity' ),
										'type'  => 'textarea',
										'rows'  => 2,
									],
								],
							],
						],
					],

					// ── CTA Banner ────────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_cta_banner',
						'name'       => 'cta_banner',
						'label'      => __( 'CTA Banner', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'   => 'field_cta_heading',
								'name'  => 'heading',
								'label' => __( 'Heading', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_cta_body',
								'name'  => 'body',
								'label' => __( 'Body', 'jalaversity' ),
								'type'  => 'textarea',
								'rows'  => 2,
							],
							jalaversity_acf_link_field( 'field_cta_btn_primary', 'btn_primary', __( 'Tombol Utama', 'jalaversity' ) ),
							jalaversity_acf_link_field( 'field_cta_btn_ghost', 'btn_ghost', __( 'Tombol Ghost', 'jalaversity' ) ),
						],
					],

					// ── PMB Section ───────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_pmb_section',
						'name'       => 'pmb_section',
						'label'      => __( 'PMB Section', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'   => 'field_pmb_wave_label',
								'name'  => 'wave_label',
								'label' => __( 'Wave Label', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_pmb_heading',
								'name'  => 'heading',
								'label' => __( 'Heading', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'         => 'field_pmb_body',
								'name'        => 'body',
								'label'       => __( 'Body', 'jalaversity' ),
								'type'        => 'wysiwyg',
								'tabs'        => 'visual',
								'toolbar'     => 'basic',
								'media_upload' => 0,
							],
							[
								'key'   => 'field_pmb_cta_label',
								'name'  => 'cta_label',
								'label' => __( 'Teks Tombol CTA', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_pmb_cta_url',
								'name'  => 'cta_url',
								'label' => __( 'URL Tombol CTA', 'jalaversity' ),
								'type'  => 'url',
							],
							[
								'key'   => 'field_pmb_brochure_url',
								'name'  => 'brochure_url',
								'label' => __( 'URL Brosur (opsional)', 'jalaversity' ),
								'type'  => 'url',
							],
							[
								'key'          => 'field_pmb_steps',
								'name'         => 'steps',
								'label'        => __( 'Langkah', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'Tambah Langkah', 'jalaversity' ),
								'sub_fields'   => [
									[
										'key'   => 'field_pmb_step_number',
										'name'  => 'number',
										'label' => __( 'Nomor', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_pmb_step_title',
										'name'  => 'title',
										'label' => __( 'Judul', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_pmb_step_desc',
										'name'  => 'desc',
										'label' => __( 'Deskripsi', 'jalaversity' ),
										'type'  => 'textarea',
										'rows'  => 2,
									],
								],
							],
						],
					],

					// ── News Section ──────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_news_section',
						'name'       => 'news_section',
						'label'      => __( 'News & Pengumuman', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'     => 'field_news_info',
								'name'    => 'info',
								'label'   => '',
								'type'    => 'message',
								'message' => __( 'Section ini otomatis menampilkan 4 berita terbaru, pengumuman, dan agenda dari WP_Query — tidak ada konten yang perlu diisi manual.', 'jalaversity' ),
							],
						],
					],

					// ── Sub Nav ───────────────────────────────────────────
					[
						'key'        => 'layout_jalaversity_sub_nav',
						'name'       => 'sub_nav',
						'label'      => __( 'Sub Nav', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'          => 'field_sn_items',
								'name'         => 'items',
								'label'        => __( 'Item Navigasi', 'jalaversity' ),
								'instructions' => __( 'href berupa anchor halaman ini (mis. #prodi) yang mengarah ke id section terkait.', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Tambah Item', 'jalaversity' ),
								'min'          => 1,
								'sub_fields'   => [
									[
										'key'   => 'field_sn_item_label',
										'name'  => 'label',
										'label' => __( 'Label', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_sn_item_href',
										'name'  => 'href',
										'label' => __( 'Href (anchor, mis. #prodi)', 'jalaversity' ),
										'type'  => 'text',
									],
								],
							],
						],
					],

					// ── Profile + Quote ───────────────────────────────────
					[
						'key'        => 'layout_jalaversity_profile_quote',
						'name'       => 'profile_quote',
						'label'      => __( 'Profile + Quote (Sambutan)', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'           => 'field_pq_image',
								'name'          => 'image',
								'label'         => __( 'Foto Profil', 'jalaversity' ),
								'type'          => 'image',
								'return_format' => 'id',
							],
							[
								'key'      => 'field_pq_name',
								'name'     => 'name',
								'label'    => __( 'Nama', 'jalaversity' ),
								'type'     => 'text',
								'required' => 1,
							],
							[
								'key'   => 'field_pq_title',
								'name'  => 'title',
								'label' => __( 'Jabatan', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'   => 'field_pq_label',
								'name'  => 'label',
								'label' => __( 'Section Label', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'      => 'field_pq_heading',
								'name'     => 'heading',
								'label'    => __( 'Heading', 'jalaversity' ),
								'type'     => 'text',
								'required' => 1,
							],
							[
								'key'   => 'field_pq_quote',
								'name'  => 'quote',
								'label' => __( 'Kutipan', 'jalaversity' ),
								'type'  => 'textarea',
								'rows'  => 2,
							],
							[
								'key'   => 'field_pq_body',
								'name'  => 'body',
								'label' => __( 'Body', 'jalaversity' ),
								'type'  => 'textarea',
								'rows'  => 3,
							],
						],
					],

					// ── Checklist + Cards ─────────────────────────────────
					[
						'key'        => 'layout_jalaversity_checklist_cards',
						'name'       => 'checklist_cards',
						'label'      => __( 'Checklist + Cards', 'jalaversity' ),
						'display'    => 'block',
						'sub_fields' => [
							[
								'key'   => 'field_cc_label',
								'name'  => 'label',
								'label' => __( 'Section Label (kolom kiri)', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'      => 'field_cc_heading',
								'name'     => 'heading',
								'label'    => __( 'Heading (kolom kiri)', 'jalaversity' ),
								'type'     => 'text',
								'required' => 1,
							],
							[
								'key'          => 'field_cc_checklist',
								'name'         => 'checklist',
								'label'        => __( 'Checklist (kolom kiri)', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Tambah Item', 'jalaversity' ),
								'min'          => 1,
								'sub_fields'   => [
									[
										'key'     => 'field_cc_check_icon',
										'name'    => 'icon',
										'label'   => __( 'Icon', 'jalaversity' ),
										'type'    => 'select',
										'choices' => $icon_choices,
										'default_value' => 'check-circle',
									],
									[
										'key'   => 'field_cc_check_text',
										'name'  => 'text',
										'label' => __( 'Teks', 'jalaversity' ),
										'type'  => 'text',
									],
								],
							],
							[
								'key'   => 'field_cc_cards_heading',
								'name'  => 'cards_heading',
								'label' => __( 'Sub-heading (kolom kanan)', 'jalaversity' ),
								'type'  => 'text',
							],
							[
								'key'          => 'field_cc_cards',
								'name'         => 'cards',
								'label'        => __( 'Kartu (kolom kanan)', 'jalaversity' ),
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'Tambah Kartu', 'jalaversity' ),
								'sub_fields'   => [
									[
										'key'     => 'field_cc_card_icon',
										'name'    => 'icon',
										'label'   => __( 'Icon', 'jalaversity' ),
										'type'    => 'select',
										'choices' => $icon_choices,
									],
									[
										'key'   => 'field_cc_card_title',
										'name'  => 'title',
										'label' => __( 'Judul', 'jalaversity' ),
										'type'  => 'text',
									],
									[
										'key'   => 'field_cc_card_desc',
										'name'  => 'desc',
										'label' => __( 'Deskripsi', 'jalaversity' ),
										'type'  => 'textarea',
										'rows'  => 2,
									],
								],
							],
						],
					],

				],
			],
		],
		'location'     => [
			[
				[
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'page-templates/page-dynamic.php',
				],
			],
		],
		'menu_order'   => 0,
		'position'     => 'normal',
		'style'        => 'default',
		'active'       => true,
	] );

} );
