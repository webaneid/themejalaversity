<?php
/**
 * Hero Page — Generic
 *
 * Hero dark green dengan girih pattern. Dua varian:
 * - 'home'    : badge institusi, search form, trust badges, glow.
 * - 'subpage' : breadcrumb (dark), padding lebih kecil, tanpa search/trust.
 *
 * Pure render component — semua data via $args, tidak ada panggilan
 * jalaversity_get_option() di sini agar bisa dipakai ulang di halaman manapun
 * (homepage maupun Fakultas/sub-page lain).
 *
 * @param string $args['variant']        'home' | 'subpage' (default 'home').
 * @param string $args['badge']          Teks badge institusi (varian home).
 * @param string $args['heading']        H1 (wajib).
 * @param string $args['highlight']      Frase dalam heading yang diberi warna gold.
 * @param string $args['lead']           Lead paragraph.
 * @param bool   $args['show_search']    Tampilkan search form (default: true jika home).
 * @param array  $args['trust_items']    List string trust badges (varian home).
 * @param array  $args['buttons']        Maks 2 tombol CTA ['label','url','external'=>bool,'style'=>'primary'|'ghost'].
 * @param int    $args['image_id']       Attachment ID gambar.
 * @param string $args['image_alt']      Alt text gambar.
 * @param array  $args['floating_badge'] Args untuk floating-badge component (icon/label/value).
 * @param bool   $args['show_breadcrumb'] Tampilkan breadcrumb (default: true jika subpage).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant   = ( 'subpage' === ( $args['variant'] ?? '' ) ) ? 'subpage' : 'home';
$is_home   = 'home' === $variant;
$heading   = $args['heading'] ?? '';
$highlight = $args['highlight'] ?? '';
$lead      = $args['lead'] ?? '';
$badge     = $args['badge'] ?? '';

$show_search = array_key_exists( 'show_search', $args ) ? (bool) $args['show_search'] : $is_home;
$trust_items = $args['trust_items'] ?? [];
$buttons     = array_slice( $args['buttons'] ?? [], 0, 2 );

$image_id  = (int) ( $args['image_id'] ?? 0 );
$image_alt = $args['image_alt'] ?? get_bloginfo( 'name' );

$floating_badge = $args['floating_badge'] ?? null;

$show_breadcrumb = array_key_exists( 'show_breadcrumb', $args ) ? (bool) $args['show_breadcrumb'] : ! $is_home;

if ( ! $heading ) {
	return;
}

// Highlight satu frase dalam heading dengan warna gold.
if ( $highlight && false !== strpos( $heading, $highlight ) ) {
	$heading_html = str_replace(
		esc_html( $highlight ),
		'<span class="hero-page__highlight">' . esc_html( $highlight ) . '</span>',
		esc_html( $heading )
	);
} else {
	$heading_html = esc_html( $heading );
}

$section_class = 'hero-page' . ( $is_home ? '' : ' hero-page--subpage' );
?>
<section class="<?php echo esc_attr( $section_class ); ?>" aria-labelledby="hero-heading">
	<div class="hero-page__glow" aria-hidden="true"></div>

	<div class="container">

		<?php if ( $show_breadcrumb ) : ?>
		<div class="hero-page__breadcrumb">
			<?php get_template_part( 'template-parts/components/breadcrumb', null, [ 'dark' => true ] ); ?>
		</div>
		<?php endif; ?>

		<div class="hero-page__inner">

			<div class="hero-page__content anim-fadeup">

				<?php if ( $badge ) : ?>
				<div class="hero-page__badge" role="note">
					<?php jalaversity_icon_e( 'sparkles', 16 ); ?>
					<?php echo esc_html( $badge ); ?>
				</div>
				<?php endif; ?>

				<h1 id="hero-heading" class="hero-page__heading <?php echo $is_home ? 'text-hero' : 'text-hero-sub'; ?>">
					<?php echo $heading_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — sanitized above ?>
				</h1>

				<?php if ( $lead ) : ?>
				<p class="hero-page__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>

				<?php if ( $buttons ) : ?>
				<div class="hero-page__buttons">
					<?php foreach ( $buttons as $button ) :
						if ( empty( $button['url'] ) ) {
							continue;
						}
						$btn_class = ( 'ghost' === ( $button['style'] ?? 'primary' ) ) ? 'btn--ghost-white' : 'btn--accent-solid';
					?>
					<a
						href="<?php echo esc_url( $button['url'] ); ?>"
						class="btn <?php echo esc_attr( $btn_class ); ?>"
						<?php if ( ! empty( $button['external'] ) ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
					>
						<?php echo esc_html( $button['label'] ?? '' ); ?>
					</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php if ( $show_search ) : ?>
				<form
					action="<?php echo esc_url( home_url( '/' ) ); ?>"
					method="get"
					class="hero-page__search"
					role="search"
					aria-label="<?php esc_attr_e( 'Cari konten', 'jalaversity' ); ?>"
				>
					<span class="hero-page__search-icon" aria-hidden="true">
						<?php jalaversity_icon_e( 'search', 21 ); ?>
					</span>
					<input
						type="search"
						name="s"
						value="<?php echo esc_attr( get_search_query() ); ?>"
						class="hero-page__search-input"
						placeholder="<?php esc_attr_e( 'Cari program studi, berita, atau layanan...', 'jalaversity' ); ?>"
						aria-label="<?php esc_attr_e( 'Kata kunci pencarian', 'jalaversity' ); ?>"
					>
					<button type="submit" class="hero-page__search-btn">
						<?php esc_html_e( 'Cari', 'jalaversity' ); ?>
					</button>
				</form>
				<?php endif; ?>

				<?php if ( $trust_items ) : ?>
				<ul class="hero-page__trust" aria-label="<?php esc_attr_e( 'Keunggulan institusi', 'jalaversity' ); ?>">
					<?php foreach ( $trust_items as $trust ) :
						if ( ! $trust ) {
							continue;
						}
					?>
					<li class="hero-page__trust-item">
						<?php jalaversity_icon_e( 'check-circle', 16 ); ?>
						<?php echo esc_html( $trust ); ?>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

			</div><!-- /.hero-page__content -->

			<?php if ( $image_id || $floating_badge ) : ?>
			<div class="hero-page__visual anim-fadeup">
				<div class="hero-page__img-wrap img-hero-radius">
					<?php if ( $image_id && wp_attachment_is_image( $image_id ) ) :
						echo wp_get_attachment_image(
							$image_id,
							'large',
							false,
							[
								'class'   => 'hero-page__img',
								'alt'     => esc_attr( $image_alt ),
								'loading' => 'eager',
							]
						);
					else : ?>
						<div class="hero-page__img-placeholder" aria-hidden="true">
							<?php jalaversity_icon_e( 'photo', 48 ); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $floating_badge ) : ?>
					<?php get_template_part( 'template-parts/components/floating-badge', null, $floating_badge ); ?>
				<?php endif; ?>
			</div><!-- /.hero-page__visual -->
			<?php endif; ?>

		</div><!-- /.hero-page__inner -->
	</div><!-- /.container -->
</section>
