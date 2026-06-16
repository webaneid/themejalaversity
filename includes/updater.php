<?php
/**
 * GitHub Auto-Updater
 *
 * Cek release terbaru di GitHub repo webaneid/themejalaversity,
 * tampilkan notifikasi update di wp-admin, dan handle download + install
 * via WordPress Updater API.
 *
 * Token disimpan di Settings Page → Tab Update → field github_token.
 * Tidak membutuhkan ACF atau plugin eksternal.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JALAVERSITY_UPDATER_REPO', 'webaneid/themejalaversity' );
define( 'JALAVERSITY_UPDATER_SLUG', 'jalaversity' );

// ── Helpers ───────────────────────────────────────────────────────────────────

function jalaversity_updater_get_token(): string {
	return trim( (string) jalaversity_get_option( 'github_token', '' ) );
}

function jalaversity_updater_get_version(): string {
	return wp_get_theme( JALAVERSITY_UPDATER_SLUG )->get( 'Version' );
}

/**
 * Ambil data release terbaru dari GitHub API.
 * Di-cache di transient selama 12 jam (error: 1 jam).
 *
 * @return array|string|false Array release, 'error', atau false (token kosong).
 */
function jalaversity_updater_get_release(): array|string|false {
	$cached = get_transient( 'jalaversity_github_release' );
	if ( $cached !== false ) {
		return $cached;
	}

	$token = jalaversity_updater_get_token();
	if ( empty( $token ) ) {
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

	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
		set_transient( 'jalaversity_github_release', 'error', HOUR_IN_SECONDS );
		return false;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ) );
	if ( empty( $body->tag_name ) ) {
		return false;
	}

	// Cari .zip asset (bukan zipball — folder name-nya tidak cocok dengan slug theme).
	$asset_url = '';
	if ( ! empty( $body->assets ) ) {
		foreach ( $body->assets as $asset ) {
			if ( str_ends_with( $asset->name, '.zip' ) ) {
				$asset_url = $asset->url; // API URL, bukan browser_download_url (agar private repo bisa diauth).
				break;
			}
		}
	}

	if ( empty( $asset_url ) ) {
		return false;
	}

	$release = [
		'version'   => ltrim( $body->tag_name, 'vV' ),
		'tag'       => $body->tag_name,
		'url'       => $body->html_url,
		'package'   => $asset_url,
		'body'      => $body->body ?? '',
		'published' => $body->published_at ?? '',
	];

	set_transient( 'jalaversity_github_release', $release, 12 * HOUR_IN_SECONDS );

	return $release;
}

// ── Check for Updates ─────────────────────────────────────────────────────────

add_filter( 'pre_set_site_transient_update_themes', 'jalaversity_updater_check' );

function jalaversity_updater_check( mixed $transient ): mixed {
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$release = jalaversity_updater_get_release();
	if ( ! $release || $release === 'error' ) {
		return $transient;
	}

	if ( version_compare( $release['version'], jalaversity_updater_get_version(), '>' ) ) {
		$transient->response[ JALAVERSITY_UPDATER_SLUG ] = [
			'theme'       => JALAVERSITY_UPDATER_SLUG,
			'new_version' => $release['version'],
			'url'         => $release['url'],
			'package'     => $release['package'],
		];
	}

	return $transient;
}

// ── Theme Info Popup ──────────────────────────────────────────────────────────

add_filter( 'themes_api', 'jalaversity_updater_info', 20, 3 );

function jalaversity_updater_info( mixed $result, string $action, object $args ): mixed {
	if ( $action !== 'theme_information' || ! isset( $args->slug ) || $args->slug !== JALAVERSITY_UPDATER_SLUG ) {
		return $result;
	}

	$release = jalaversity_updater_get_release();
	if ( ! $release || $release === 'error' ) {
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
			'description' => $theme->get( 'Description' ),
			'changelog'   => nl2br( esc_html( $release['body'] ) ),
		],
		'download_link' => $release['package'],
	];
}

// ── Auth Header for Private Repo Download ─────────────────────────────────────

// Inject Authorization header ke setiap request WP yang menuju GitHub repo ini,
// supaya download .zip dari private repo bisa ter-auth dengan benar.
add_filter( 'http_request_args', 'jalaversity_updater_auth_header', 10, 2 );

function jalaversity_updater_auth_header( array $args, string $url ): array {
	$is_api    = str_contains( $url, 'api.github.com' ) && str_contains( $url, JALAVERSITY_UPDATER_REPO );
	$is_github = str_contains( $url, 'github.com' ) && str_contains( $url, JALAVERSITY_UPDATER_REPO );
	$is_cdn    = str_contains( $url, 'github-releases.githubusercontent.com' );

	if ( ! $is_api && ! $is_github && ! $is_cdn ) {
		return $args;
	}

	$token = jalaversity_updater_get_token();
	if ( empty( $token ) ) {
		return $args;
	}

	$args['headers']['Authorization'] = 'token ' . $token;

	// Asset download butuh Accept: application/octet-stream agar mendapat binary, bukan JSON.
	if ( $is_api && str_contains( $url, '/releases/assets/' ) ) {
		$args['headers']['Accept'] = 'application/octet-stream';
	}

	return $args;
}

// ── Rename Extracted Folder ───────────────────────────────────────────────────

// GitHub ekstrak zip dengan nama folder `themejalaversity-1.x.x/` — harus
// di-rename ke `jalaversity/` agar WP bisa menemukan theme setelah update.
add_filter( 'upgrader_source_selection', 'jalaversity_updater_rename_source', 10, 4 );

function jalaversity_updater_rename_source( string $source, string $remote_source, mixed $upgrader, array $hook_extra ): string|WP_Error {
	if ( ! isset( $hook_extra['theme'] ) || $hook_extra['theme'] !== JALAVERSITY_UPDATER_SLUG ) {
		return $source;
	}

	$correct_dir = trailingslashit( $remote_source ) . JALAVERSITY_UPDATER_SLUG;

	if ( $source === $correct_dir . '/' ) {
		return $source;
	}

	if ( ! rename( $source, $correct_dir ) ) {
		return new WP_Error( 'rename_failed', __( 'Gagal rename folder theme saat update.', 'jalaversity' ) );
	}

	return trailingslashit( $correct_dir );
}

// ── Clear Cache After Update ──────────────────────────────────────────────────

add_action( 'upgrader_process_complete', 'jalaversity_updater_clear_cache', 10, 2 );

function jalaversity_updater_clear_cache( mixed $upgrader, array $options ): void {
	if ( $options['action'] === 'update' && $options['type'] === 'theme' ) {
		delete_transient( 'jalaversity_github_release' );
	}
}

// ── Section Callback (status di Settings Page) ────────────────────────────────

function jalaversity_updater_section_callback(): void {
	$version = jalaversity_updater_get_version();
	$release = get_transient( 'jalaversity_github_release' );
	$force_url = add_query_arg(
		[ 'page' => 'jalaversity-settings', 'tab' => 'update', 'jalaversity_force_check' => '1' ],
		admin_url( 'admin.php' )
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

		<?php if ( $release && $release !== 'error' ) : ?>
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
					<summary><?php printf( esc_html__( 'Changelog v%s', 'jalaversity' ), esc_html( $release['version'] ) ); ?></summary>
					<pre><?php echo esc_html( $release['body'] ); ?></pre>
				</details>
			<?php endif; ?>

		<?php elseif ( $release === 'error' ) : ?>
			<p class="jalaversity-updater-status__badge jalaversity-updater-status__badge--error">
				<?php esc_html_e( 'Gagal menghubungi GitHub. Cek token dan koneksi server.', 'jalaversity' ); ?>
			</p>
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

// ── Force Check Handler ───────────────────────────────────────────────────────

add_action( 'admin_init', 'jalaversity_updater_force_check_handler' );

function jalaversity_updater_force_check_handler(): void {
	if ( ! current_user_can( 'update_themes' ) ) {
		return;
	}

	if ( ! isset( $_GET['jalaversity_force_check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	delete_transient( 'jalaversity_github_release' );
	delete_site_transient( 'update_themes' );

	// Redirect tanpa parameter force_check agar tidak loop saat refresh.
	wp_safe_redirect(
		add_query_arg( [ 'page' => 'jalaversity-settings', 'tab' => 'update', 'jalaversity_checked' => '1' ], admin_url( 'admin.php' ) )
	);
	exit;
}

// Tampilkan notice sukses setelah redirect force check.
add_action( 'admin_notices', 'jalaversity_updater_force_check_notice' );

function jalaversity_updater_force_check_notice(): void {
	if ( ! isset( $_GET['jalaversity_checked'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Cache update di-clear. WordPress akan mengecek GitHub saat halaman dimuat.', 'jalaversity' ); ?></p>
	</div>
	<?php
}
