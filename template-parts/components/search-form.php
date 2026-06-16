<?php
/**
 * Search Form — Generic
 *
 * Form pencarian standar WordPress. Dipakai di search.php dan content-none.php
 * (saat hasil pencarian kosong) — bisa dipakai ulang di tempat lain yang
 * butuh search input.
 *
 * Pure render — tidak ada $args, hanya baca get_search_query().
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form
	action="<?php echo esc_url( home_url( '/' ) ); ?>"
	method="get"
	class="search-form"
	role="search"
	aria-label="<?php esc_attr_e( 'Cari konten', 'jalaversity' ); ?>"
>
	<span class="search-form__icon" aria-hidden="true">
		<?php jalaversity_icon_e( 'search', 20 ); ?>
	</span>
	<input
		type="search"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		class="search-form__input"
		placeholder="<?php esc_attr_e( 'Cari artikel...', 'jalaversity' ); ?>"
		aria-label="<?php esc_attr_e( 'Kata kunci pencarian', 'jalaversity' ); ?>"
	>
	<button type="submit" class="search-form__btn">
		<?php esc_html_e( 'Cari', 'jalaversity' ); ?>
	</button>
</form>
