<?php
/**
 * Settings Fields & Sections
 *
 * Satu sumber kebenaran (`jalaversity_settings_schema()`) untuk semua field
 * Settings Page — dipakai oleh settings-page.php (render tab) dan
 * settings-sanitize.php (sanitasi). Lihat docs/04-settings-schema.md untuk
 * skema lengkap dan alasan tiap field ada.
 *
 * Field tidak didaftarkan 1 callback per field (akan jadi >80 fungsi nyaris
 * identik) — semua field di-render oleh satu fungsi generik
 * `jalaversity_render_settings_field()` berdasarkan `type`.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skema lengkap: tab → section → field.
 *
 * Setiap field: ['key','label','type'=>'text|textarea|url|email|tel|color|image','default','desc'(opsional)].
 *
 * @return array
 */
function jalaversity_settings_schema(): array {
	return [
		'umum' => [
			'label'    => __( 'Umum', 'jalaversity' ),
			'sections' => [
				'kontak' => [
					'label'  => __( 'Kontak & PMB', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'contact_address', 'label' => __( 'Alamat', 'jalaversity' ), 'type' => 'textarea' ],
						[ 'key' => 'contact_phone', 'label' => __( 'Telepon', 'jalaversity' ), 'type' => 'tel' ],
						[ 'key' => 'contact_email', 'label' => __( 'Email', 'jalaversity' ), 'type' => 'email' ],
						[ 'key' => 'contact_url', 'label' => __( 'URL Kontak (tombol "Hubungi Kami")', 'jalaversity' ), 'type' => 'url', 'default' => '#' ],
						[ 'key' => 'footer_copyright', 'label' => __( 'Teks Copyright Footer', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'pmb_wave_label', 'label' => __( 'PMB — Label Gelombang', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Penerimaan Mahasiswa Baru 2026/2027', 'jalaversity' ), 'desc' => __( 'Teks kecil di atas heading PMB, mis. "Gelombang II · 2026/2027".', 'jalaversity' ) ],
						[ 'key' => 'pmb_heading', 'label' => __( 'PMB — Heading', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Gelombang II Telah Dibuka', 'jalaversity' ) ],
						[ 'key' => 'pmb_body', 'label' => __( 'PMB — Body', 'jalaversity' ), 'type' => 'textarea', 'default' => __( 'Pendaftaran berlangsung 24 Juni – 15 Agustus 2026. Tersedia beasiswa tahfizh, prestasi, dan bidikmisi bagi calon mahasiswa terpilih.', 'jalaversity' ) ],
						[ 'key' => 'pmb_url', 'label' => __( 'PMB — URL Pendaftaran', 'jalaversity' ), 'type' => 'url', 'default' => '#' ],
						[ 'key' => 'pmb_label', 'label' => __( 'PMB — Teks Tombol', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Daftar PMB Sekarang', 'jalaversity' ) ],
						[ 'key' => 'pmb_brochure_url', 'label' => __( 'PMB — URL Brosur (opsional)', 'jalaversity' ), 'type' => 'url' ],
					],
				],
			],
		],

		'beranda' => [
			'label'    => __( 'Beranda', 'jalaversity' ),
			'sections' => [
				'hero' => [
					'label'  => __( 'Hero', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'hero_tagline', 'label' => __( 'Badge/Tagline', 'jalaversity' ), 'type' => 'text', 'default' => get_bloginfo( 'name' ) ],
						[ 'key' => 'hero_heading', 'label' => __( 'Heading (H1)', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Menuntut Ilmu, Menebar Manfaat untuk Peradaban', 'jalaversity' ) ],
						[ 'key' => 'hero_highlight', 'label' => __( 'Frase Highlight', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Menebar Manfaat', 'jalaversity' ), 'desc' => __( 'Frase dalam heading di atas yang diberi warna gold.', 'jalaversity' ) ],
						[ 'key' => 'hero_lead', 'label' => __( 'Lead Paragraph', 'jalaversity' ), 'type' => 'textarea' ],
						[ 'key' => 'hero_image_id', 'label' => __( 'Gambar Hero', 'jalaversity' ), 'type' => 'image' ],
						[ 'key' => 'hero_image_alt', 'label' => __( 'Alt Text Gambar', 'jalaversity' ), 'type' => 'text', 'default' => get_bloginfo( 'name' ) ],
						[ 'key' => 'hero_trust_1', 'label' => __( 'Trust Badge 1', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Terakreditasi Unggul', 'jalaversity' ) ],
						[ 'key' => 'hero_trust_2', 'label' => __( 'Trust Badge 2', 'jalaversity' ), 'type' => 'text', 'default' => __( '18 Program Studi', 'jalaversity' ) ],
						[ 'key' => 'hero_trust_3', 'label' => __( 'Trust Badge 3', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Beasiswa Tahfizh', 'jalaversity' ) ],
						[ 'key' => 'accreditation_label', 'label' => __( 'Label Floating Badge', 'jalaversity' ), 'type' => 'text', 'default' => __( 'Akreditasi Institusi', 'jalaversity' ) ],
						[ 'key' => 'accreditation_value', 'label' => __( 'Value Floating Badge', 'jalaversity' ), 'type' => 'text', 'default' => __( 'UNGGUL', 'jalaversity' ) ],
					],
				],
				'tentang' => [
					'label'  => __( 'Tentang', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'about_heading', 'label' => __( 'Heading', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'about_body', 'label' => __( 'Body', 'jalaversity' ), 'type' => 'textarea' ],
						[ 'key' => 'about_image_id', 'label' => __( 'Gambar', 'jalaversity' ), 'type' => 'image' ],
						[ 'key' => 'about_years', 'label' => __( 'Angka Tahun (badge)', 'jalaversity' ), 'type' => 'text', 'default' => '28+' ],
						[ 'key' => 'about_years_label', 'label' => __( 'Label Tahun', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'about_link_label', 'label' => __( 'Teks Link', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'about_link_url', 'label' => __( 'URL Link', 'jalaversity' ), 'type' => 'url', 'default' => '#' ],
					],
				],
				'statistik' => [
					'label'  => __( 'Statistik', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'stats_1_value', 'label' => __( 'Statistik 1 — Value', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'stats_1_label', 'label' => __( 'Statistik 1 — Label', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'stats_2_value', 'label' => __( 'Statistik 2 — Value', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'stats_2_label', 'label' => __( 'Statistik 2 — Label', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'stats_3_value', 'label' => __( 'Statistik 3 — Value', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'stats_3_label', 'label' => __( 'Statistik 3 — Label', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'stats_4_value', 'label' => __( 'Statistik 4 — Value', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'stats_4_label', 'label' => __( 'Statistik 4 — Label', 'jalaversity' ), 'type' => 'text' ],
					],
				],
				'fakultas' => [
					'label'  => __( 'Fakultas & Program Studi', 'jalaversity' ),
					'fields' => array_merge(
						[
							[ 'key' => 'faculty_heading', 'label' => __( 'Heading Section', 'jalaversity' ), 'type' => 'text' ],
							[ 'key' => 'faculty_subhead', 'label' => __( 'Lead Paragraph', 'jalaversity' ), 'type' => 'textarea' ],
						],
						jalaversity_settings_faculty_fields()
					),
				],
				'riset' => [
					'label'  => __( 'Riset & Inovasi', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'research_heading', 'label' => __( 'Heading', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'research_body', 'label' => __( 'Body', 'jalaversity' ), 'type' => 'textarea' ],
						[ 'key' => 'research_image_id', 'label' => __( 'Gambar', 'jalaversity' ), 'type' => 'image' ],
						[ 'key' => 'research_badge_value', 'label' => __( 'Value Badge', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'research_badge_label', 'label' => __( 'Label Badge', 'jalaversity' ), 'type' => 'text' ],
					],
				],
				'lokasi' => [
					'label'  => __( 'Lokasi Kampus', 'jalaversity' ),
					'fields' => array_merge(
						[ [ 'key' => 'locations_heading', 'label' => __( 'Heading Section', 'jalaversity' ), 'type' => 'text' ] ],
						jalaversity_settings_campus_fields()
					),
				],
				'cta' => [
					'label'  => __( 'CTA Penutup', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'cta_heading', 'label' => __( 'Heading', 'jalaversity' ), 'type' => 'text' ],
						[ 'key' => 'cta_body', 'label' => __( 'Body', 'jalaversity' ), 'type' => 'textarea' ],
					],
				],
			],
		],

		'sosial' => [
			'label'    => __( 'Sosial Media', 'jalaversity' ),
			'sections' => [
				'sosial' => [
					'label'  => __( 'Tautan Sosial Media', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'social_facebook', 'label' => __( 'Facebook', 'jalaversity' ), 'type' => 'url' ],
						[ 'key' => 'social_instagram', 'label' => __( 'Instagram', 'jalaversity' ), 'type' => 'url' ],
						[ 'key' => 'social_youtube', 'label' => __( 'YouTube', 'jalaversity' ), 'type' => 'url' ],
						[ 'key' => 'social_twitter', 'label' => __( 'X (Twitter)', 'jalaversity' ), 'type' => 'url' ],
						[ 'key' => 'social_linkedin', 'label' => __( 'LinkedIn', 'jalaversity' ), 'type' => 'url' ],
						[ 'key' => 'social_whatsapp', 'label' => __( 'WhatsApp', 'jalaversity' ), 'type' => 'url' ],
						[ 'key' => 'social_telegram', 'label' => __( 'Telegram', 'jalaversity' ), 'type' => 'url' ],
						[ 'key' => 'social_tiktok', 'label' => __( 'TikTok', 'jalaversity' ), 'type' => 'url' ],
					],
				],
			],
		],

		'warna' => [
			'label'    => __( 'Warna', 'jalaversity' ),
			'sections' => [
				'warna' => [
					'label'  => __( 'Brand Colors', 'jalaversity' ),
					'fields' => [
						[ 'key' => 'color_primary', 'label' => __( 'Primary', 'jalaversity' ), 'type' => 'color', 'default' => '#08422e' ],
						[ 'key' => 'color_primary_dark', 'label' => __( 'Primary Dark', 'jalaversity' ), 'type' => 'color', 'default' => '#06301f' ],
						[ 'key' => 'color_primary_medium', 'label' => __( 'Primary Medium', 'jalaversity' ), 'type' => 'color', 'default' => '#0a4730' ],
						[ 'key' => 'color_accent', 'label' => __( 'Accent', 'jalaversity' ), 'type' => 'color', 'default' => '#b68c2e' ],
						[ 'key' => 'color_accent_dark', 'label' => __( 'Accent Dark', 'jalaversity' ), 'type' => 'color', 'default' => '#a87e26' ],
						[ 'key' => 'color_accent_light', 'label' => __( 'Accent Light', 'jalaversity' ), 'type' => 'color', 'default' => '#e9c970' ],
					],
				],
			],
		],

		'update' => [
			'label'    => __( 'Update', 'jalaversity' ),
			'sections' => [
				'github' => [
					'label'    => __( 'GitHub Auto-Update', 'jalaversity' ),
					'callback' => 'jalaversity_updater_section_callback',
					'fields'   => [
						[
							'key'   => 'github_token',
							'label' => __( 'GitHub Personal Access Token', 'jalaversity' ),
							'type'  => 'password',
							'desc'  => __( 'Buat token di GitHub → Settings → Developer settings → Fine-grained tokens. Scope yang dibutuhkan: Contents → Read-only pada repo webaneid/themejalaversity.', 'jalaversity' ),
						],
					],
				],
			],
		],
	];
}

/**
 * 2 field (image, url) untuk masing-masing 6 fakultas. Nama/icon/desc tiap
 * fakultas hardcode di jalaversity_get_faculties() — disertakan di label
 * field di sini supaya admin tahu field mana untuk fakultas mana.
 *
 * @return array
 */
function jalaversity_settings_faculty_fields(): array {
	$names  = [
		1 => __( 'Tarbiyah & Keguruan', 'jalaversity' ),
		2 => __( 'Syariah & Hukum', 'jalaversity' ),
		3 => __( 'Ushuluddin & Dakwah', 'jalaversity' ),
		4 => __( 'Ekonomi & Bisnis Islam', 'jalaversity' ),
		5 => __( 'Adab & Humaniora', 'jalaversity' ),
		6 => __( 'Pascasarjana', 'jalaversity' ),
	];
	$fields = [];

	foreach ( $names as $id => $name ) {
		$fields[] = [ 'key' => "faculty_{$id}_image_id", 'label' => sprintf( __( 'Gambar — %s', 'jalaversity' ), $name ), 'type' => 'image' ];
		$fields[] = [ 'key' => "faculty_{$id}_url", 'label' => sprintf( __( 'URL — %s', 'jalaversity' ), $name ), 'type' => 'url', 'default' => '#' ];
	}

	return $fields;
}

/**
 * 5 field (name/desc/addr/map/image) untuk masing-masing 3 kampus.
 *
 * @return array
 */
function jalaversity_settings_campus_fields(): array {
	$defaults = [
		1 => [ 'name' => __( 'Kampus Pusat', 'jalaversity' ), 'desc' => __( 'Rektorat, Fakultas Tarbiyah & Syariah, Perpustakaan Pusat.', 'jalaversity' ), 'addr' => __( 'Jl. Pendidikan No. 1, Subang', 'jalaversity' ) ],
		2 => [ 'name' => __( 'Kampus 2 Terpadu', 'jalaversity' ), 'desc' => __( 'Fakultas Ekonomi & Bisnis Islam, Pusat Inkubator.', 'jalaversity' ), 'addr' => __( 'Jl. Pesantren No. 25, Subang', 'jalaversity' ) ],
		3 => [ 'name' => __( 'Kampus Pascasarjana', 'jalaversity' ), 'desc' => __( 'Program Magister & Doktor, Pusat Riset Lanjut.', 'jalaversity' ), 'addr' => __( 'Jl. Cendekia No. 7, Subang', 'jalaversity' ) ],
	];
	$fields = [];

	foreach ( $defaults as $id => $d ) {
		$fields[] = [ 'key' => "campus_{$id}_name", 'label' => sprintf( __( 'Nama — Kampus %d', 'jalaversity' ), $id ), 'type' => 'text', 'default' => $d['name'] ];
		$fields[] = [ 'key' => "campus_{$id}_desc", 'label' => sprintf( __( 'Deskripsi — Kampus %d', 'jalaversity' ), $id ), 'type' => 'textarea', 'default' => $d['desc'] ];
		$fields[] = [ 'key' => "campus_{$id}_addr", 'label' => sprintf( __( 'Alamat — Kampus %d', 'jalaversity' ), $id ), 'type' => 'text', 'default' => $d['addr'] ];
		$fields[] = [ 'key' => "campus_{$id}_map", 'label' => sprintf( __( 'URL Google Maps — Kampus %d', 'jalaversity' ), $id ), 'type' => 'url' ];
		$fields[] = [ 'key' => "campus_{$id}_image_id", 'label' => sprintf( __( 'Gambar — Kampus %d', 'jalaversity' ), $id ), 'type' => 'image' ];
	}

	return $fields;
}

/**
 * Registrasi setting + semua section/field ke WordPress Settings API.
 */
add_action( 'admin_init', function (): void {

	register_setting(
		'jalaversity_options_group',
		'jalaversity_options',
		[
			'type'              => 'array',
			'sanitize_callback' => 'jalaversity_sanitize_options',
			'default'           => [],
		]
	);

	foreach ( jalaversity_settings_schema() as $tab_slug => $tab ) {
		$page = 'jalaversity_settings_' . $tab_slug;

		foreach ( $tab['sections'] as $section_slug => $section ) {
			$section_id = 'jalaversity_section_' . $tab_slug . '_' . $section_slug;
			$callback   = $section['callback'] ?? '__return_false';

			add_settings_section( $section_id, $section['label'], $callback, $page );

			foreach ( $section['fields'] as $field ) {
				add_settings_field(
					$field['key'],
					$field['label'],
					'jalaversity_render_settings_field',
					$page,
					$section_id,
					$field
				);
			}
		}
	}
} );
