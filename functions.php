<?php
/**
 * Jalaversity Theme Functions
 *
 * Entry point. Loads all modular includes in the correct order.
 * Do not add business logic here — use the includes/ directory.
 *
 * @package Jalaversity
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Minimum PHP version check — graceful admin notice, not a fatal error.
if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
	add_action(
		'admin_notices',
		function (): void {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__(
				'Jalaversity requires PHP 8.1 or higher. Please upgrade your PHP version.',
				'jalaversity'
			);
			echo '</p></div>';
		}
	);
	return;
}

// Theme constants.
define( 'JALAVERSITY_VERSION', '1.1.0' );
define( 'JALAVERSITY_DIR', get_template_directory() );
define( 'JALAVERSITY_URI', get_template_directory_uri() );

// Load order matters: helpers before the modules that use them.
$jalaversity_includes = [
	'/includes/setup.php',
	'/includes/security.php',
	'/includes/helpers/options-helpers.php', // must load before enqueue.php
	'/includes/enqueue.php',
	'/includes/seo.php',
	'/includes/helpers/image-helpers.php',
	'/includes/helpers/icon-helpers.php',
	'/includes/helpers/social-helpers.php',
	'/includes/helpers/template-helpers.php',
	'/includes/helpers/post-helpers.php',
	'/includes/nav-walker.php',              // Walker used by template parts
	'/includes/settings/settings-page.php',
	'/includes/settings/settings-fields.php',
	'/includes/settings/settings-sanitize.php',
	'/includes/acf/acf-fields.php',          // Requires icon-helpers.php (icon choices)
	'/includes/acf/acf-render.php',
	'/includes/acf/acf-post-fields.php',
	'/includes/acf/acf-options.php',         // ACF Options Pages (Tim Layanan, dll)
	'/includes/updater.php',               // GitHub auto-updater (load last — needs options-helpers.php)
];

foreach ( $jalaversity_includes as $file ) {
	$filepath = JALAVERSITY_DIR . $file;
	if ( file_exists( $filepath ) ) {
		require_once $filepath;
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'Jalaversity: Missing include — ' . $filepath );
	}
}
