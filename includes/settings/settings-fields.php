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
		'umum'        => [
			'label'    => __( 'Umum', 'jalaversity' ),
			'sections' => [
				'kontak' => [
					'label'  => __( 'Kontak & PMB', 'jalaversity' ),
					'fields' => [
						[
							'key'   => 'contact_address',
							'label' => __( 'Alamat', 'jalaversity' ),
							'type'  => 'textarea',
						],
						[
							'key'   => 'contact_phone',
							'label' => __( 'Telepon', 'jalaversity' ),
							'type'  => 'tel',
						],
						[
							'key'   => 'contact_whatsapp',
							'label' => __( 'WhatsApp', 'jalaversity' ),
							'type'  => 'text',
							'desc'  => __( 'Format angka saja tanpa tanda + atau spasi. Contoh: 6281234567890', 'jalaversity' ),
						],
						[
							'key'   => 'contact_email',
							'label' => __( 'Email', 'jalaversity' ),
							'type'  => 'email',
						],
						[
							'key'     => 'contact_hours',
							'label'   => __( 'Jam Operasional', 'jalaversity' ),
							'type'    => 'text',
							'default' => __( 'Sen–Jum: 08.00–16.00 WIB', 'jalaversity' ),
						],
						[
							'key'   => 'contact_maps_url',
							'label' => __( 'Google Maps Embed URL', 'jalaversity' ),
							'type'  => 'url',
							'desc'  => __( 'Google Maps → Share → Embed a map → salin URL dari src="..."', 'jalaversity' ),
						],
						[
							'key'     => 'contact_url',
							'label'   => __( 'URL Kontak (tombol "Hubungi Kami")', 'jalaversity' ),
							'type'    => 'url',
							'default' => '#',
						],
						[
							'key'   => 'footer_copyright',
							'label' => __( 'Teks Copyright Footer', 'jalaversity' ),
							'type'  => 'text',
						],
						[
							'key'     => 'pmb_wave_label',
							'label'   => __( 'PMB — Label Gelombang', 'jalaversity' ),
							'type'    => 'text',
							'default' => __( 'Penerimaan Mahasiswa Baru 2026/2027', 'jalaversity' ),
							'desc'    => __( 'Teks kecil di atas heading PMB, mis. "Gelombang II · 2026/2027".', 'jalaversity' ),
						],
						[
							'key'     => 'pmb_heading',
							'label'   => __( 'PMB — Heading', 'jalaversity' ),
							'type'    => 'text',
							'default' => __( 'Gelombang II Telah Dibuka', 'jalaversity' ),
						],
						[
							'key'     => 'pmb_body',
							'label'   => __( 'PMB — Body', 'jalaversity' ),
							'type'    => 'textarea',
							'default' => __( 'Pendaftaran berlangsung 24 Juni – 15 Agustus 2026. Tersedia beasiswa tahfizh, prestasi, dan bidikmisi bagi calon mahasiswa terpilih.', 'jalaversity' ),
						],
						[
							'key'     => 'pmb_url',
							'label'   => __( 'PMB — URL Pendaftaran', 'jalaversity' ),
							'type'    => 'url',
							'default' => '#',
						],
						[
							'key'     => 'pmb_label',
							'label'   => __( 'PMB — Teks Tombol', 'jalaversity' ),
							'type'    => 'text',
							'default' => __( 'Daftar PMB Sekarang', 'jalaversity' ),
						],
						[
							'key'   => 'pmb_brochure_url',
							'label' => __( 'PMB — URL Brosur (opsional)', 'jalaversity' ),
							'type'  => 'url',
						],
					],
				],
			],
		],

		'sosial'      => [
			'label'    => __( 'Sosial Media', 'jalaversity' ),
			'sections' => [
				'sosial' => [
					'label'  => __( 'Tautan Sosial Media', 'jalaversity' ),
					'fields' => [
						[
							'key'   => 'social_facebook',
							'label' => __( 'Facebook', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_instagram',
							'label' => __( 'Instagram', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_youtube',
							'label' => __( 'YouTube', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_twitter',
							'label' => __( 'X (Twitter)', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_linkedin',
							'label' => __( 'LinkedIn', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_whatsapp',
							'label' => __( 'WhatsApp', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_telegram',
							'label' => __( 'Telegram', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_tiktok',
							'label' => __( 'TikTok', 'jalaversity' ),
							'type'  => 'url',
						],
						[
							'key'   => 'social_threads',
							'label' => __( 'Threads', 'jalaversity' ),
							'type'  => 'url',
						],
					],
				],
			],
		],

		'warna'       => [
			'label'    => __( 'Warna', 'jalaversity' ),
			'sections' => [
				'warna' => [
					'label'  => __( 'Brand Colors', 'jalaversity' ),
					'fields' => [
						[
							'key'     => 'color_primary',
							'label'   => __( 'Primary', 'jalaversity' ),
							'type'    => 'color',
							'default' => '#08422e',
						],
						[
							'key'     => 'color_primary_dark',
							'label'   => __( 'Primary Dark', 'jalaversity' ),
							'type'    => 'color',
							'default' => '#06301f',
						],
						[
							'key'     => 'color_primary_medium',
							'label'   => __( 'Primary Medium', 'jalaversity' ),
							'type'    => 'color',
							'default' => '#0a4730',
						],
						[
							'key'     => 'color_accent',
							'label'   => __( 'Accent', 'jalaversity' ),
							'type'    => 'color',
							'default' => '#b68c2e',
						],
						[
							'key'     => 'color_accent_dark',
							'label'   => __( 'Accent Dark', 'jalaversity' ),
							'type'    => 'color',
							'default' => '#a87e26',
						],
						[
							'key'     => 'color_accent_light',
							'label'   => __( 'Accent Light', 'jalaversity' ),
							'type'    => 'color',
							'default' => '#e9c970',
						],
					],
				],
			],
		],

		'tim_layanan' => [
			'label'  => __( 'Tim Layanan', 'jalaversity' ),
			'render' => 'jalaversity_render_tim_layanan_tab',
		],

		'integrasi'   => [
			'label'    => __( 'Integrasi & Tracking', 'jalaversity' ),
			'sections' => [
				'google'      => [
					'label'  => __( 'Google (Search Console, Tag Manager, & Analytics 4)', 'jalaversity' ),
					'fields' => [
						[
							'key'         => 'gsc_verification',
							'label'       => __( 'Google Search Console Verification', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'Contoh: abc123XYZ_kode_verifikasi',
							'desc'        => __( 'Isi kode verifikasi "HTML tag" dari Google Search Console — boleh kodenya saja atau seluruh tag <meta>. Theme mencetaknya otomatis di <head>.', 'jalaversity' ),
						],
						[
							'key'         => 'gtm_id',
							'label'       => __( 'Google Tag Manager (Container ID)', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'GTM-XXXXXXX',
							'desc'        => __( 'Isi Container ID (diawali GTM-). Theme otomatis memasang skrip GTM di <head> dan <noscript> tepat setelah <body>. Jangan tempel snippet mentah.', 'jalaversity' ),
						],
						[
							'key'         => 'ga4_id',
							'label'       => __( 'Google Analytics 4 (Measurement ID)', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'G-XXXXXXXXXX',
							'desc'        => __( 'Isi Measurement ID (diawali G-). Kosongkan bila Analytics sudah dipasang lewat Google Tag Manager agar tidak terhitung dua kali.', 'jalaversity' ),
						],
					],
				],
				'yandex_bing' => [
					'label'  => __( 'Yandex & Bing (Webmaster & Metrica)', 'jalaversity' ),
					'fields' => [
						[
							'key'         => 'yandex_verification',
							'label'       => __( 'Yandex Webmaster Verification', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'Contoh: 1fa477a7ff934bf1',
							'desc'        => __( 'Isi kode verifikasi dari Yandex Webmaster — boleh kodenya saja atau seluruh tag <meta name="yandex-verification">.', 'jalaversity' ),
						],
						[
							'key'         => 'yandex_metrica_id',
							'label'       => __( 'Yandex Metrica (Counter ID)', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'Contoh: 12345678',
							'desc'        => __( 'Isi nomor counter Yandex Metrica (angka). Rekaman sesi (Webvisor) tidak diaktifkan demi privasi dan performa.', 'jalaversity' ),
						],
						[
							'key'         => 'bing_verification',
							'label'       => __( 'Bing Webmaster Verification', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'Contoh: 1234567890ABCDEF',
							'desc'        => __( 'Opsional. Kode verifikasi dari Bing Webmaster Tools (msvalidate.01) — boleh kodenya saja atau seluruh tag <meta>.', 'jalaversity' ),
						],
					],
				],
				'meta_tiktok' => [
					'label'  => __( 'Meta (Facebook Pixel & Domain) & TikTok Pixel', 'jalaversity' ),
					'fields' => [
						[
							'key'         => 'fb_pixel_id',
							'label'       => __( 'Meta / Facebook Pixel ID', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'Contoh: 123456789012345',
							'desc'        => __( 'Isi deretan angka Pixel ID untuk melacak konversi kampanye PMB / iklan Meta.', 'jalaversity' ),
						],
						[
							'key'         => 'fb_domain_verify',
							'label'       => __( 'Meta / Facebook Domain Verification', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'Contoh: abc123xyz...',
							'desc'        => __( 'Opsional. Kode verifikasi domain dari Meta Business Suite — boleh kodenya saja atau seluruh tag <meta name="facebook-domain-verification">.', 'jalaversity' ),
						],
						[
							'key'         => 'tiktok_pixel_id',
							'label'       => __( 'TikTok Pixel ID', 'jalaversity' ),
							'type'        => 'text',
							'placeholder' => 'Contoh: CXXXXXXXXXXXXXXXXXXX',
							'desc'        => __( 'Opsional. Isi TikTok Pixel ID bila kampus menjalankan iklan di TikTok Ads.', 'jalaversity' ),
						],
					],
				],
			],
		],

		'update'      => [
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
 * Registrasi setting + semua section/field ke WordPress Settings API.
 */
add_action(
	'admin_init',
	function (): void {

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
			if ( isset( $tab['render'] ) ) {
				continue; // custom-render tabs tidak pakai Settings API sections
			}

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
	}
);

/**
 * Render tab Tim Layanan — repeater kustom, disimpan di jalaversity_options.
 */
function jalaversity_render_tim_layanan_tab(): void {
	$message  = jalaversity_get_option( 'wa_default_message', __( 'Halo, saya ingin bertanya mengenai informasi penerimaan mahasiswa baru.', 'jalaversity' ) );
	$contacts = jalaversity_get_option( 'tim_layanan_contacts', [] );
	if ( ! is_array( $contacts ) ) {
		$contacts = [];
	}
	?>
	<h2><?php esc_html_e( 'WhatsApp Widget', 'jalaversity' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="jalaversity-field-wa_default_message"><?php esc_html_e( 'Pesan Default WhatsApp', 'jalaversity' ); ?></label>
			</th>
			<td>
				<textarea
					id="jalaversity-field-wa_default_message"
					name="jalaversity_options[wa_default_message]"
					rows="3"
					class="large-text"
				><?php echo esc_textarea( $message ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Pesan yang otomatis terisi saat pengunjung membuka chat WhatsApp.', 'jalaversity' ); ?></p>
			</td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Daftar Tim Layanan', 'jalaversity' ); ?></h2>
	<p class="description" style="margin-bottom:12px;"><?php esc_html_e( 'Tombol WhatsApp di footer hanya muncul jika ada petugas di sini.', 'jalaversity' ); ?></p>

	<div id="jalaversity-tim-layanan-repeater">
		<?php
		foreach ( $contacts as $i => $row ) :
			$photo_id  = absint( $row['photo'] ?? 0 );
			$photo_url = $photo_id ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';
			?>
		<div class="jalaversity-tim-row">
			<div class="jalaversity-tim-row__handle" title="<?php esc_attr_e( 'Drag to reorder', 'jalaversity' ); ?>">⠿</div>
			<div class="jalaversity-tim-row__fields">
				<label><?php esc_html_e( 'Nama', 'jalaversity' ); ?>
					<input type="text" name="jalaversity_options[tim_layanan_contacts][<?php echo (int) $i; ?>][nama]" value="<?php echo esc_attr( $row['nama'] ?? '' ); ?>" class="regular-text" required>
				</label>
				<label><?php esc_html_e( 'Jabatan', 'jalaversity' ); ?>
					<input type="text" name="jalaversity_options[tim_layanan_contacts][<?php echo (int) $i; ?>][jabatan]" value="<?php echo esc_attr( $row['jabatan'] ?? '' ); ?>" class="regular-text">
				</label>
				<label><?php esc_html_e( 'Nomor WhatsApp', 'jalaversity' ); ?>
					<input type="text" name="jalaversity_options[tim_layanan_contacts][<?php echo (int) $i; ?>][whatsapp]" value="<?php echo esc_attr( $row['whatsapp'] ?? '' ); ?>" class="regular-text" placeholder="6281234567890">
					<span class="description"><?php esc_html_e( 'Angka saja, tanpa + atau spasi.', 'jalaversity' ); ?></span>
				</label>
			</div>
			<div class="jalaversity-tim-row__photo jalaversity-image-field">
				<input type="hidden" name="jalaversity_options[tim_layanan_contacts][<?php echo (int) $i; ?>][photo]" value="<?php echo esc_attr( (string) $photo_id ); ?>" class="jalaversity-image-field__input">
				<div class="jalaversity-image-field__preview" <?php echo $photo_url ? '' : 'style="display:none;"'; ?>>
					<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="60" height="60" style="border-radius:50%;object-fit:cover;">
				</div>
				<button type="button" class="button jalaversity-image-field__select"><?php esc_html_e( 'Foto', 'jalaversity' ); ?></button>
				<button type="button" class="button jalaversity-image-field__remove" <?php echo $photo_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Hapus', 'jalaversity' ); ?></button>
			</div>
			<button type="button" class="button jalaversity-tim-row__remove" style="align-self:start;">✕ <?php esc_html_e( 'Hapus', 'jalaversity' ); ?></button>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" class="button button-secondary" id="jalaversity-tim-add-row" style="margin-top:12px;">
		+ <?php esc_html_e( 'Tambah Petugas', 'jalaversity' ); ?>
	</button>

	<?php
}
