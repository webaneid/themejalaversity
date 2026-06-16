<?php
/**
 * Pagination
 *
 * Wrapper untuk WordPress paginate_links(). Digunakan di archive dan search.
 * Menerima WP_Query via $args['query'] (optional — default: global $wp_query).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$query = isset( $args['query'] ) && $args['query'] instanceof WP_Query
	? $args['query']
	: $wp_query;

$total_pages = isset( $query->max_num_pages ) ? (int) $query->max_num_pages : 1;

if ( $total_pages <= 1 ) {
	return;
}

$links = paginate_links(
	[
		'base'      => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
		'format'    => '?paged=%#%',
		'current'   => max( 1, (int) get_query_var( 'paged' ) ),
		'total'     => $total_pages,
		'type'      => 'array',
		'prev_text' => jalaversity_icon( 'chevron-right', 16 ) . '<span class="screen-reader-text">' . __( 'Sebelumnya', 'jalaversity' ) . '</span>',
		'next_text' => '<span class="screen-reader-text">' . __( 'Berikutnya', 'jalaversity' ) . '</span>' . jalaversity_icon( 'chevron-right', 16 ),
	]
);

if ( empty( $links ) ) {
	return;
}
?>
<nav class="pagination" aria-label="<?php esc_attr_e( 'Navigasi halaman', 'jalaversity' ); ?>">
	<ul class="pagination__list">
		<?php foreach ( $links as $link ) : ?>
		<li class="pagination__item">
			<?php
			// paginate_links returns escaped HTML — safe to output directly.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo str_replace( 'page-numbers', 'pagination__link', $link );
			?>
		</li>
		<?php endforeach; ?>
	</ul>
</nav>
