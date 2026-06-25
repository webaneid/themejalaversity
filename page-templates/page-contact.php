<?php
/**
 * Template Name: Laman Kontak
 *
 * Menampilkan informasi kontak (dari Settings → Kontak), Google Maps embed,
 * dan form kontak via the_content() — admin cukup tempel shortcode CF7
 * di editor Gutenberg.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$phone     = jalaversity_get_option( 'contact_phone' );
$whatsapp  = jalaversity_get_option( 'contact_whatsapp' );
$email     = jalaversity_get_option( 'contact_email' );
$address   = jalaversity_get_option( 'contact_address' );
$hours     = jalaversity_get_option( 'contact_hours', __( 'Sen–Jum: 08.00–16.00 WIB', 'jalaversity' ) );
$maps_url  = jalaversity_get_option( 'contact_maps_url' );

$contact_cards = [
	$phone     ? [ 'icon' => 'phone',          'label' => __( 'Telepon', 'jalaversity' ),          'value' => $phone,    'href' => 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ] : null,
	$whatsapp  ? [ 'icon' => 'message-circle', 'label' => __( 'WhatsApp', 'jalaversity' ),         'value' => $whatsapp, 'href' => 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp ), 'external' => true ] : null,
	$email     ? [ 'icon' => 'mail',           'label' => __( 'Email', 'jalaversity' ),            'value' => $email,    'href' => 'mailto:' . $email ] : null,
	$address   ? [ 'icon' => 'map-pin',        'label' => __( 'Alamat', 'jalaversity' ),           'value' => $address,  'href' => null ] : null,
	$hours     ? [ 'icon' => 'clock',          'label' => __( 'Jam Operasional', 'jalaversity' ),  'value' => $hours,    'href' => null ] : null,
];
$contact_cards = array_filter( $contact_cards );

get_header();
?>

<?php
get_template_part( 'template-parts/components/hero-page', null, [
	'variant' => 'subpage',
	'title'   => get_the_title(),
] );
?>

<main id="main" class="contact-page section-py">
	<div class="container">

		<?php if ( $contact_cards ) : ?>
		<div class="contact-cards">
			<?php foreach ( $contact_cards as $card ) : ?>
			<div class="contact-card">
				<div class="contact-card__icon" aria-hidden="true">
					<?php jalaversity_icon_e( $card['icon'], 22 ); ?>
				</div>
				<div class="contact-card__body">
					<div class="contact-card__label"><?php echo esc_html( $card['label'] ); ?></div>
					<?php if ( $card['href'] ) : ?>
					<a
						href="<?php echo esc_url( $card['href'] ); ?>"
						class="contact-card__value"
						<?php if ( ! empty( $card['external'] ) ) : ?>rel="noopener noreferrer" target="_blank"<?php endif; ?>
					><?php echo nl2br( esc_html( $card['value'] ) ); ?></a>
					<?php else : ?>
					<div class="contact-card__value"><?php echo nl2br( esc_html( $card['value'] ) ); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="contact-layout<?php echo $maps_url ? '' : ' contact-layout--no-map'; ?>">

			<?php if ( $maps_url ) : ?>
			<div class="contact-map">
				<iframe
					src="<?php echo esc_url( $maps_url ); ?>"
					width="100%"
					height="100%"
					style="border:0;"
					allowfullscreen=""
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					title="<?php esc_attr_e( 'Lokasi Kampus', 'jalaversity' ); ?>"
				></iframe>
			</div>
			<?php endif; ?>

			<div class="contact-form">
				<?php if ( have_posts() ) :
					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;
				endif; ?>
			</div>

		</div>

	</div>
</main>

<?php get_footer(); ?>
