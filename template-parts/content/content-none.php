<?php
/**
 * Content — No Results
 *
 * Fallback ketika query tidak menghasilkan post (archive/search kosong).
 * Sudah direferensikan index.php sejak awal proyek, baru dibuat sekarang.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="no-results">
	<h2 class="no-results__heading text-section">
		<?php
		if ( is_search() ) {
			esc_html_e( 'Tidak ada hasil ditemukan', 'jalaversity' );
		} else {
			esc_html_e( 'Belum ada konten', 'jalaversity' );
		}
		?>
	</h2>
	<p class="no-results__desc">
		<?php
		if ( is_search() ) {
			printf(
				/* translators: %s: search query */
				esc_html__( 'Tidak ada artikel yang cocok dengan kata kunci "%s". Coba kata kunci lain.', 'jalaversity' ),
				esc_html( get_search_query() )
			);
		} else {
			esc_html_e( 'Belum ada artikel yang dipublikasikan di sini.', 'jalaversity' );
		}
		?>
	</p>
	<?php if ( is_search() ) : ?>
		<?php get_template_part( 'template-parts/components/search-form' ); ?>
	<?php endif; ?>
</div>
