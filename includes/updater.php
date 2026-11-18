<?php
/**
 * GitHub Auto-Updater
 *
 * Cek release terbaru di GitHub repo webaneid/themejalaversity,
 * tampilkan notifikasi update di wp-admin, dan handle download + install
 * via WordPress Updater API.
 *
 * Token disimpan di Settings Page → Tab Update → field github_token
 * atau konstanta JALAVERSITY_UPDATER_TOKEN di wp-config.php.
 * Tidak membutuhkan ACF atau plugin eksternal.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'JALAVERSITY_UPDATER_REPO' ) ) {
	define( 'JALAVERSITY_UPDATER_REPO', 'webaneid/themejalaversity' );
}

if ( ! defined( 'JALAVERSITY_UPDATER_SLUG' ) ) {
	define( 'JALAVERSITY_UPDATER_SLUG', 'jalaversity' );
}

/**
 * Ambil GitHub Personal Access Token untuk autentikasi rilis privat.
 *
 * Prioritas:
 * 1. Konstanta JALAVERSITY_UPDATER_TOKEN di wp-config.php.
 * 2. Opsi `github_token` di jalaversity_options.
 *
 * @return string Token GitHub atau string kosong.
 */
function jalaversity_updater_get_token(): string {
	if ( defined( 'JALAVERSITY_UPDATER_TOKEN' ) && is_string( JALAVERSITY_UPDATER_TOKEN ) ) {
		$config_token = trim( JALAVERSITY_UPDATER_TOKEN );
		if ( '' !== $config_token ) {
			return $config_token;
		}
	}

	return trim( (string) jalaversity_get_option( 'github_token', '' ) );
}

/**
 * Ambil versi theme yang sedang terpasang dari header style.css.
 *
 * @return string Versi theme.
 */
function jalaversity_updater_get_version(): string {
	$version = (string) wp_get_theme( JALAVERSITY_UPDATER_SLUG )->get( 'Version' );

	return '' !== $version ? $version : '0.0.0';
}

/**
 * Simpan alasan gagal terakhir (kode HTTP / pesan jaringan) dan tahan permintaan ulang 15 menit.
 *
 * @param int    $status HTTP status (0 = gagal jaringan).
 * @param string $detail Detail singkat (pesan jaringan atau penanda internal).
 */
function jalaversity_updater_remember_error( int $status, string $detail = '' ): void {
	set_transient( 'jalaversity_github_release', 'error', 15 * MINUTE_IN_SECONDS );
	set_transient(
		'jalaversity_github_error',
		[
			'status' => $status,
			'detail' => wp_strip_all_tags( $detail ),
		],
		15 * MINUTE_IN_SECONDS
	);
}

/**
 * Petunjuk perbaikan berdasarkan alasan gagal terakhir. Tidak pernah memuat token.
 *
 * @return string Teks polos (belum di-escape).
 */
function jalaversity_updater_error_hint(): string {
	if ( '' === jalaversity_updater_get_token() ) {
		return __( 'GitHub Personal Access Token belum diisi (repo privat membutuhkannya).', 'jalaversity' );
	}

	$error  = get_transient( 'jalaversity_github_error' );
	$status = is_array( $error ) && isset( $error['status'] ) ? (int) $error['status'] : -1;
	$detail = is_array( $error ) && isset( $error['detail'] ) ? (string) $error['detail'] : '';

	if ( 401 === $status ) {
		return __( 'GitHub menolak token (401): token salah atau sudah kedaluwarsa. Buat token baru.', 'jalaversity' );
	}
	if ( 403 === $status ) {
		return __( 'GitHub menolak akses (403): token tidak punya izin Contents: Read-only, atau batas permintaan GitHub tercapai. Coba lagi nanti.', 'jalaversity' );
	}
	if ( 404 === $status ) {
		return __( 'Repo atau rilis tidak ditemukan (404). Pastikan Repository access mencakup webaneid/themejalaversity, permission Contents: Read-only aktif, dan minimal sudah ada 1 Release yang dipublikasikan.', 'jalaversity' );
	}
	if ( 0 === $status ) {
		/* translators: %s: pesan kesalahan jaringan */
		return sprintf( __( 'Tidak dapat terhubung ke GitHub: %s', 'jalaversity' ), $detail );
	}
	if ( 200 === $status && 'no_asset' === $detail ) {
		return __( 'Rilis terbaru di GitHub belum memiliki file lampiran ZIP paket theme.', 'jalaversity' );
	}
	if ( $status > 0 ) {
		/* translators: %d: kode HTTP dari GitHub */
		return sprintf( __( 'GitHub membalas dengan kode HTTP %d.', 'jalaversity' ), $status );
	}

	return '';
}

/**
 * Ambil data release terbaru dari GitHub API.
 * Di-cache di transient selama 12 jam (error: 15 menit).
 *
 * @return array<string, string>|string|false Array release, 'error', atau false (token kosong).
 */
function jalaversity_updater_get_release(): array|string|false {
	$cached = get_transient( 'jalaversity_github_release' );
	if ( false !== $cached ) {
		return is_array( $cached ) || 'error' === $cached ? $cached : false;
	}

	$token = jalaversity_updater_get_token();
	if ( '' === $token ) {
		return false;
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . JALAVERSITY_UPDATER_REPO . '/releases/latest',
		[
			'headers' => [
				'Authorization' => 'token ' . $token,
				'Accept'        => 'application/vnd.github.v3+json',
				'User-Agent'    => 'Jalaversity-Updater/' . JALAVERSITY_VERSION,
			],
			'timeout' => 15,
		]
	);

	if ( is_wp_error( $response ) ) {
		jalaversity_updater_remember_error( 0, $response->get_error_message() );
		return 'error';
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status ) {
		jalaversity_updater_remember_error( $status );
		return 'error';
	}

	$body = json_decode( wp_remote_retrieve_body( $response ) );
	if ( ! is_object( $body ) || empty( $body->tag_name ) ) {
		jalaversity_updater_remember_error( 200, 'invalid_release' );
		return 'error';
	}

	// Cari .zip asset (bukan zipball — folder name-nya tidak cocok dengan slug theme).
	$asset_url = '';
	if ( ! empty( $body->assets ) && is_array( $body->assets ) ) {
		foreach ( $body->assets as $asset ) {
			if ( is_object( $asset ) && ! empty( $asset->name ) && ! empty( $asset->url ) ) {
				if ( str_ends_with( (string) $asset->name, '.zip' ) ) {
					$asset_url = (string) $asset->url; // API URL agar private repo bisa di-auth.
					break;
				}
			}
		}
	}

	if ( '' === $asset_url ) {
		jalaversity_updater_remember_error( 200, 'no_asset' );
		return 'error';
	}

	$release = [
		'version'   => ltrim( (string) $body->tag_name, 'vV' ),
		'tag'       => (string) $body->tag_name,
		'url'       => isset( $body->html_url ) ? (string) $body->html_url : '',
		'package'   => $asset_url,
		'body'      => isset( $body->body ) ? (string) $body->body : '',
		'published' => isset( $body->published_at ) ? (string) $body->published_at : '',
	];

	delete_transient( 'jalaversity_github_error' );
	set_transient( 'jalaversity_github_release', $release, 12 * HOUR_IN_SECONDS );

	return $release;
}

/**
 * Suntikkan informasi update ke transient `update_themes` WordPress.
 *
 * @param mixed $transient Objek transient update_themes.
 * @return mixed
 */
function jalaversity_updater_check( mixed $transient ): mixed {
	if ( ! $transient instanceof stdClass || empty( $transient->checked ) ) {
		return $transient;
	}

	$release = jalaversity_updater_get_release();
	if ( ! is_array( $release ) ) {
		return $transient;
	}

	if ( version_compare( $release['version'], jalaversity_updater_get_version(), '>' ) ) {
		if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
			$transient->response = [];
		}

		$transient->response[ JALAVERSITY_UPDATER_SLUG ] = [
			'theme'       => JALAVERSITY_UPDATER_SLUG,
			'new_version' => $release['version'],
			'url'         => $release['url'],
			'package'     => $release['package'],
		];
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'jalaversity_updater_check' );

/**
 * Sediakan detail informasi theme & changelog pada popup wp-admin.
 *
 * @param mixed  $result Nilai hasil default.
 * @param string $action Nama aksi themes_api.
 * @param object $args   Argumen request.
 * @return mixed
 */
function jalaversity_updater_info( mixed $result, string $action, object $args ): mixed {
	if ( 'theme_information' !== $action || ! isset( $args->slug ) || JALAVERSITY_UPDATER_SLUG !== $args->slug ) {
		return $result;
	}

	$release = jalaversity_updater_get_release();
	if ( ! is_array( $release ) ) {
		return $result;
	}

	$theme = wp_get_theme( JALAVERSITY_UPDATER_SLUG );

	return (object) [
		'name'          => $theme->get( 'Name' ),
		'slug'          => JALAVERSITY_UPDATER_SLUG,
		'version'       => $release['version'],
		'author'        => $theme->get( 'Author' ),
		'homepage'      => $theme->get( 'ThemeURI' ),
		'requires'      => '6.0',
		'tested'        => '6.7',
		'requires_php'  => '8.1',
		'downloaded'    => 0,
		'last_updated'  => $release['published'],
		'sections'      => [
			'description' => (string) $theme->get( 'Description' ),
			'changelog'   => nl2br( esc_html( $release['body'] ) ),
		],
		'download_link' => $release['package'],
	];
}
add_filter( 'themes_api', 'jalaversity_updater_info', 20, 3 );

/**
 * Inject Authorization header ke setiap request WP yang menuju GitHub repo ini,
 * supaya download .zip dari private repo bisa ter-auth dengan benar.
 *
 * @param array<string, mixed> $args Argumen HTTP request.
 * @param string               $url  URL tujuan request.
 * @return array<string, mixed>
 */
function jalaversity_updater_auth_header( array $args, string $url ): array {
	$is_api    = str_contains( $url, 'api.github.com/repos/' . JALAVERSITY_UPDATER_REPO . '/' );
	$is_github = str_contains( $url, 'github.com/' . JALAVERSITY_UPDATER_REPO . '/' );

	if ( ! $is_api && ! $is_github ) {
		return $args;
	}

	$token = jalaversity_updater_get_token();
	if ( '' === $token ) {
		return $args;
	}

	if ( ! isset( $args['headers'] ) || ! is_array( $args['headers'] ) ) {
		$args['headers'] = [];
	}

	$args['headers']['Authorization'] = 'token ' . $token;

	// Asset download butuh Accept: application/octet-stream agar mendapat binary, bukan JSON.
	if ( $is_api && str_contains( $url, '/releases/assets/' ) ) {
		$args['headers']['Accept'] = 'application/octet-stream';
	}

	return $args;
}
add_filter( 'http_request_args', 'jalaversity_updater_auth_header', 10, 2 );

/**
 * Pastikan nama folder hasil ekstraksi ZIP sesuai dengan slug theme (`jalaversity/`).
 *
 * @param string               $source        Path folder sumber yang diekstrak.
 * @param string               $remote_source Path direktori sementara.
 * @param mixed                $upgrader      Instance upgrader WordPress.
 * @param array<string, mixed> $hook_extra    Konteks ekstra proses upgrade.
 * @return string|WP_Error
 */
function jalaversity_updater_rename_source( string $source, string $remote_source, mixed $upgrader, array $hook_extra ): string|WP_Error {
	global $wp_filesystem;

	if ( ! isset( $hook_extra['theme'] ) || JALAVERSITY_UPDATER_SLUG !== $hook_extra['theme'] ) {
		return $source;
	}

	$correct_dir = trailingslashit( $remote_source ) . JALAVERSITY_UPDATER_SLUG;

	if ( trailingslashit( $source ) === trailingslashit( $correct_dir ) ) {
		return $source;
	}

	if ( is_object( $wp_filesystem ) && method_exists( $wp_filesystem, 'move' ) ) {
		if ( $wp_filesystem->move( $source, $correct_dir, true ) ) {
			return trailingslashit( $correct_dir );
		}
	}

	return new WP_Error(
		'jalaversity_rename_failed',
		__( 'Gagal menyesuaikan nama folder theme saat update.', 'jalaversity' )
	);
}
add_filter( 'upgrader_source_selection', 'jalaversity_updater_rename_source', 10, 4 );

/**
 * Bersihkan transient rilis setelah proses update theme selesai.
 *
 * @param mixed                $upgrader Instance upgrader.
 * @param array<string, mixed> $options  Opsi proses upgrade.
 */
function jalaversity_updater_clear_cache( mixed $upgrader, array $options ): void {
	if ( isset( $options['action'], $options['type'] ) && 'update' === $options['action'] && 'theme' === $options['type'] ) {
		delete_transient( 'jalaversity_github_release' );
		delete_transient( 'jalaversity_github_error' );
	}
}
add_action( 'upgrader_process_complete', 'jalaversity_updater_clear_cache', 10, 2 );

/**
 * Bersihkan cache update saat admin menyimpan opsi jalaversity_options.
 */
function jalaversity_updater_on_save_options(): void {
	delete_transient( 'jalaversity_github_release' );
	delete_transient( 'jalaversity_github_error' );
	delete_site_transient( 'update_themes' );
}
add_action( 'update_option_jalaversity_options', 'jalaversity_updater_on_save_options' );

/**
 * Render status updater di Jalaversity Settings → Tab Update.
 */
function jalaversity_updater_section_callback(): void {
	$version   = jalaversity_updater_get_version();
	$release   = jalaversity_updater_get_release();
	$force_url = wp_nonce_url(
		add_query_arg(
			[
				'page'                    => 'jalaversity-settings',
				'tab'                     => 'update',
				'jalaversity_force_check' => '1',
			],
			admin_url( 'admin.php' )
		),
		'jalaversity_updater_action'
	);
	?>
	<div class="jalaversity-updater-status">
		<p>
			<strong><?php esc_html_e( 'Versi terpasang:', 'jalaversity' ); ?></strong>
			<?php echo esc_html( $version ); ?>
			&nbsp;|&nbsp;
			<strong><?php esc_html_e( 'Repo:', 'jalaversity' ); ?></strong>
			<code><?php echo esc_html( JALAVERSITY_UPDATER_REPO ); ?></code>
		</p>

		<?php if ( is_array( $release ) ) : ?>
			<?php if ( version_compare( $release['version'], $version, '>' ) ) : ?>
				<p class="jalaversity-updater-status__badge jalaversity-updater-status__badge--update">
					&#9650; <?php esc_html_e( 'Update tersedia:', 'jalaversity' ); ?>
					<strong>v<?php echo esc_html( $release['version'] ); ?></strong>
					&mdash;
					<a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>">
						<?php esc_html_e( 'Lihat di Appearance → Themes', 'jalaversity' ); ?>
					</a>
				</p>
			<?php else : ?>
				<p class="jalaversity-updater-status__badge jalaversity-updater-status__badge--ok">
					&#10003; <?php esc_html_e( 'Sudah versi terbaru.', 'jalaversity' ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $release['body'] ) ) : ?>
				<details class="jalaversity-updater-status__changelog">
					<summary>
						<?php
						printf(
							/* translators: %s: nomor versi rilis */
							esc_html__( 'Changelog v%s', 'jalaversity' ),
							esc_html( $release['version'] )
						);
						?>
					</summary>
					<pre><?php echo esc_html( $release['body'] ); ?></pre>
				</details>
			<?php endif; ?>

		<?php elseif ( 'error' === $release ) : ?>
			<p class="jalaversity-updater-status__badge jalaversity-updater-status__badge--error">
				<?php esc_html_e( 'Gagal menghubungi GitHub. Cek token dan koneksi server.', 'jalaversity' ); ?>
			</p>
			<?php $hint = jalaversity_updater_error_hint(); ?>
			<?php if ( '' !== $hint ) : ?>
				<p class="description">
					<strong><?php esc_html_e( 'Penyebab:', 'jalaversity' ); ?></strong>
					<?php echo esc_html( $hint ); ?>
				</p>
			<?php endif; ?>
		<?php else : ?>
			<p class="description">
				<?php esc_html_e( 'Belum ada data. Isi token lalu klik "Cek Update Sekarang".', 'jalaversity' ); ?>
			</p>
		<?php endif; ?>

		<p>
			<a href="<?php echo esc_url( $force_url ); ?>" class="button button-secondary">
				<?php esc_html_e( 'Cek Update Sekarang', 'jalaversity' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * Tangani permintaan "Cek Update Sekarang" (dengan verifikasi capability & nonce).
 */
function jalaversity_updater_force_check_handler(): void {
	if ( ! isset( $_GET['jalaversity_force_check'] ) ) {
		return;
	}

	if ( ! current_user_can( 'update_themes' ) ) {
		return;
	}

	check_admin_referer( 'jalaversity_updater_action' );

	delete_transient( 'jalaversity_github_release' );
	delete_transient( 'jalaversity_github_error' );
	delete_site_transient( 'update_themes' );
	wp_update_themes();

	$redirect_url = wp_nonce_url(
		add_query_arg(
			[
				'page'                => 'jalaversity-settings',
				'tab'                 => 'update',
				'jalaversity_checked' => '1',
			],
			admin_url( 'admin.php' )
		),
		'jalaversity_updater_checked',
		'jalaversity_notice_nonce'
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_init', 'jalaversity_updater_force_check_handler', 1 );

/**
 * Tampilkan notice hasil setelah redirect force check.
 */
function jalaversity_updater_force_check_notice(): void {
	if ( ! isset( $_GET['jalaversity_checked'], $_GET['jalaversity_notice_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_GET['jalaversity_notice_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'jalaversity_updater_checked' ) ) {
		return;
	}

	$release = jalaversity_updater_get_release();
	$current = jalaversity_updater_get_version();

	if ( ! is_array( $release ) ) {
		$hint = jalaversity_updater_error_hint();
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php esc_html_e( 'Gagal mengambil informasi rilis dari GitHub.', 'jalaversity' ); ?></p>
			<?php if ( '' !== $hint ) : ?>
				<p><strong><?php esc_html_e( 'Penyebab:', 'jalaversity' ); ?></strong> <?php echo esc_html( $hint ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return;
	}

	if ( version_compare( $release['version'], $current, '>' ) ) {
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php
				printf(
					/* translators: 1: versi terbaru, 2: versi terpasang */
					esc_html__( 'Pembaruan ditemukan! Versi terbaru v%1$s siap diinstal (versi saat ini: v%2$s). Silakan perbarui melalui Appearance → Themes.', 'jalaversity' ),
					esc_html( $release['version'] ),
					esc_html( $current )
				);
				?>
			</p>
		</div>
		<?php
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<?php
			printf(
				/* translators: %s: versi terpasang */
				esc_html__( 'Pengecekan update selesai. Tema Jalaversity Anda sudah menggunakan versi terbaru (v%s).', 'jalaversity' ),
				esc_html( $current )
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'jalaversity_updater_force_check_notice' );
