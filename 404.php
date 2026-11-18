<?php
/**
 * 404 Error Page Template
 *
 * Halaman tidak ditemukan dengan hero subpage, form pencarian, dan tombol
 * kembali ke beranda.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part(
	'template-parts/components/hero-page',
	null,
	[
		'variant'         => 'subpage',
		'heading'         => __( '404 — Halaman Tidak Ditemukan', 'jalaversity' ),
		'lead'            => __( 'Maaf, halaman yang Anda tuju mungkin telah dipindahkan, dihapus, atau alamat URL tidak tepat.', 'jalaversity' ),
		'show_breadcrumb' => true,
		'buttons'         => [
			[
				'label' => __( 'Kembali ke Beranda', 'jalaversity' ),
				'url'   => home_url( '/' ),
				'style' => 'primary',
			],
		],
	]
);
?>

<section class="section-py" aria-label="<?php esc_attr_e( 'Pencarian Halaman', 'jalaversity' ); ?>">
	<div class="container">
		<div class="no-results">
			<h2 class="no-results__heading text-section">
				<?php esc_html_e( 'Cari Informasi Kampus', 'jalaversity' ); ?>
			</h2>
			<p class="no-results__desc">
				<?php esc_html_e( 'Silakan gunakan kotak pencarian di bawah ini untuk menemukan program studi, berita, pengumuman, atau agenda kampus.', 'jalaversity' ); ?>
			</p>
			<?php get_template_part( 'template-parts/components/search-form' ); ?>
		</div>
	</div>
</section>

<?php
get_footer();
