<?php
/**
 * Sidebar — Default
 *
 * SATU desain dipakai identik di index/archive/tag/category/author
 * (via content-post-list.php) dan single post (via content-single.php) —
 * sesuai permintaan user, bukan sidebar berbeda per context.
 *
 * 3 blok bawaan: search, artikel terpopuler (urut views via
 * jalaversity_get_views()), kategori. Ditutup dengan widget area WP native
 * (`sidebar-1`, sudah diregister di includes/setup.php) untuk fleksibilitas
 * admin — kosong/tidak render apa pun kalau tidak ada widget di-drop.
 *
 * PENTING: dynamic_sidebar() TIDAK dibungkus div .sidebar__widget sendiri
 * — setiap widget yang di-drop admin SUDAH otomatis dibungkus <section
 * class="widget ..."> oleh WordPress (before_widget di includes/setup.php),
 * dan .sidebar .widget sudah diberi box style yang sama di _article.scss.
 * Membungkusnya lagi di sini akan jadi box-di-dalam-box untuk SETIAP
 * widget (dobel border/padding) — terlihat "numpuk2", bukan rapi.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'jalaversity' ); ?>">

	<div class="sidebar__widget">
		<?php get_template_part( 'template-parts/components/search-form' ); ?>
	</div>

	<?php
	$popular_query = new WP_Query( [
		'post_type'           => 'post',
		'posts_per_page'      => 5,
		'meta_key'            => '_jalaversity_views',
		'orderby'             => 'meta_value_num',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
	] );

	if ( $popular_query->have_posts() ) :
	?>
	<div class="sidebar__widget">
		<h3 class="sidebar__widget-title"><?php esc_html_e( 'Artikel Terpopuler', 'jalaversity' ); ?></h3>
		<div class="sidebar__popular-list">
			<?php
			while ( $popular_query->have_posts() ) :
				$popular_query->the_post();
				get_template_part( 'template-parts/content/content-card', null, [ 'variant' => 'title' ] );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php endif; ?>

	<?php
	$categories = get_categories( [ 'hide_empty' => true ] );
	if ( $categories ) :
	?>
	<div class="sidebar__widget">
		<h3 class="sidebar__widget-title"><?php esc_html_e( 'Kategori', 'jalaversity' ); ?></h3>
		<ul class="sidebar__category-list">
			<?php foreach ( $categories as $category ) : ?>
			<li>
				<a href="<?php echo esc_url( get_category_link( $category ) ); ?>">
					<span><?php echo esc_html( $category->name ); ?></span>
					<span class="sidebar__category-count"><?php echo esc_html( $category->count ); ?></span>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php endif; ?>

</aside>
