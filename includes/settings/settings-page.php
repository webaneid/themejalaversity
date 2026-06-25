<?php
/**
 * Settings Page Registration
 *
 * Registers the admin menu page and renders the tabbed Settings Page UI.
 * Field schema lives in settings-fields.php; sanitization in
 * settings-sanitize.php. See docs/04-settings-schema.md for the full schema.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function (): void {
	add_menu_page(
		__( 'Jalaversity Settings', 'jalaversity' ),
		__( 'Jalaversity', 'jalaversity' ),
		'manage_options',
		'jalaversity-settings',
		'jalaversity_render_settings_page',
		'dashicons-admin-customizer',
		61
	);
} );

/**
 * Render the Settings Page with tab navigation.
 */
function jalaversity_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'jalaversity' ) );
	}

	$schema      = jalaversity_settings_schema();
	$tab_keys    = array_keys( $schema );
	$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
	if ( ! in_array( $current_tab, $tab_keys, true ) ) {
		$current_tab = $tab_keys[0];
	}
	?>
	<div class="wrap jalaversity-settings">
		<h1><?php esc_html_e( 'Jalaversity Settings', 'jalaversity' ); ?></h1>

		<?php settings_errors(); ?>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $schema as $slug => $tab ) :
				$url = add_query_arg( [ 'page' => 'jalaversity-settings', 'tab' => $slug ], admin_url( 'admin.php' ) );
				$active = ( $slug === $current_tab ) ? ' nav-tab-active' : '';
			?>
				<a href="<?php echo esc_url( $url ); ?>" class="nav-tab<?php echo esc_attr( $active ); ?>">
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'jalaversity_options_group' );

			if ( isset( $schema[ $current_tab ]['render'] ) ) {
				call_user_func( $schema[ $current_tab ]['render'] );
			} else {
				do_settings_sections( 'jalaversity_settings_' . $current_tab );
			}

			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Generic field renderer — dipanggil oleh add_settings_field() untuk semua
 * field, switch berdasarkan `type`. Lihat jalaversity_settings_schema()
 * di settings-fields.php untuk daftar field.
 *
 * @param array $field Konfigurasi field (key/label/type/default/desc).
 */
function jalaversity_render_settings_field( array $field ): void {
	$key     = $field['key'];
	$type    = $field['type'] ?? 'text';
	$default = $field['default'] ?? '';
	$value   = jalaversity_get_option( $key, $default );
	$name    = "jalaversity_options[{$key}]";
	$id      = 'jalaversity-field-' . $key;

	switch ( $type ) {

		case 'textarea':
			printf(
				'<textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( $value )
			);
			break;

		case 'url':
			printf(
				'<input type="url" id="%s" name="%s" value="%s" class="regular-text" placeholder="https://">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
			break;

		case 'email':
			printf(
				'<input type="email" id="%s" name="%s" value="%s" class="regular-text">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
			break;

		case 'tel':
			printf(
				'<input type="tel" id="%s" name="%s" value="%s" class="regular-text">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
			break;

		case 'color':
			printf(
				'<input type="text" id="%s" name="%s" value="%s" class="jalaversity-color-field" data-default-color="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $default )
			);
			break;

		case 'image':
			$image_id  = (int) $value;
			$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
			?>
			<div class="jalaversity-image-field">
				<input
					type="hidden"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $image_id ); ?>"
					class="jalaversity-image-field__input"
				>
				<div class="jalaversity-image-field__preview" <?php echo $image_url ? '' : 'style="display:none;"'; ?>>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="">
				</div>
				<button type="button" class="button jalaversity-image-field__select"><?php esc_html_e( 'Pilih Gambar', 'jalaversity' ); ?></button>
				<button type="button" class="button jalaversity-image-field__remove" <?php echo $image_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Hapus', 'jalaversity' ); ?></button>
			</div>
			<?php
			break;

		case 'password':
			printf(
				'<input type="password" id="%s" name="%s" value="%s" class="regular-text" autocomplete="new-password">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
			break;

		default:
			printf(
				'<input type="text" id="%s" name="%s" value="%s" class="regular-text">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
	}

	if ( ! empty( $field['desc'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $field['desc'] ) );
	}
}
