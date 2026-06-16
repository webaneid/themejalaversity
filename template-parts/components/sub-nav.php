<?php
/**
 * Sub Navigation — Generic
 *
 * Bar anchor-link sticky di bawah hero/stats bar, untuk halaman panjang
 * (mis. Fakultas) yang punya beberapa sub-section (Tentang, Program Studi,
 * Keunggulan, dst).
 *
 * Pure render component — semua data via $args.
 *
 * @param array $args['items'] List ['label','href'] (wajib). `href` berupa
 *                              fragment anchor (mis. '#prodi') atau URL biasa.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = $args['items'] ?? [];

if ( ! $items ) {
	return;
}
?>
<nav class="sub-nav" aria-label="<?php esc_attr_e( 'Navigasi halaman', 'jalaversity' ); ?>">
	<div class="container sub-nav__inner">
		<?php foreach ( $items as $item ) :
			if ( empty( $item['label'] ) || empty( $item['href'] ) ) {
				continue;
			}
		?>
		<a href="<?php echo esc_url( $item['href'] ); ?>" class="sub-nav__link">
			<?php echo esc_html( $item['label'] ); ?>
		</a>
		<?php endforeach; ?>
	</div>
</nav>
