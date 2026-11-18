<?php
/**
 * Breadcrumb
 *
 * Trail navigasi struktural dengan schema.org BreadcrumbList markup.
 * Digunakan di sub-pages, single post, dan archive.
 * Tidak ditampilkan di homepage.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Tidak tampil di homepage.
if ( is_front_page() ) {
	return;
}

$items    = [];
$home_url = home_url( '/' );

// Beranda selalu item pertama.
$items[] = [
	'label' => __( 'Beranda', 'jalaversity' ),
	'url'   => $home_url,
];

if ( is_single() ) {
	$current_post_type = (string) get_post_type();

	// Archive link (jika ada).
	if ( 'post' === $current_post_type ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );
		if ( $posts_page_id ) {
			$items[] = [
				'label' => get_the_title( $posts_page_id ),
				'url'   => (string) get_permalink( $posts_page_id ),
			];
		}
	} elseif ( get_post_type_archive_link( $current_post_type ) ) {
		$pt_obj = get_post_type_object( $current_post_type );
		if ( $pt_obj ) {
			$items[] = [
				'label' => esc_html( $pt_obj->labels->name ),
				'url'   => (string) get_post_type_archive_link( $current_post_type ),
			];
		}
	}

	// Post sendiri (tidak ada URL — ini item aktif).
	$items[] = [
		'label' => get_the_title(),
		'url'   => '',
	];

} elseif ( is_page() ) {
	// Ancestor pages.
	$ancestors = array_reverse( get_post_ancestors( (int) get_the_ID() ) );
	foreach ( $ancestors as $ancestor_id ) {
		$items[] = [
			'label' => get_the_title( $ancestor_id ),
			'url'   => (string) get_permalink( $ancestor_id ),
		];
	}
	$items[] = [
		'label' => get_the_title(),
		'url'   => '',
	];

} elseif ( is_category() || is_tag() || is_tax() ) {
	$queried_term = get_queried_object();
	if ( $queried_term instanceof WP_Term ) {
		$items[] = [
			'label' => $queried_term->name,
			'url'   => '',
		];
	}
} elseif ( is_archive() ) {
	$items[] = [
		'label' => get_the_archive_title(),
		'url'   => '',
	];

} elseif ( is_search() ) {
	$items[] = [
		'label' => sprintf(
			/* translators: %s: search query */
			__( 'Hasil pencarian: %s', 'jalaversity' ),
			get_search_query()
		),
		'url'   => '',
	];

} elseif ( is_404() ) {
	$items[] = [
		'label' => __( 'Halaman Tidak Ditemukan', 'jalaversity' ),
		'url'   => '',
	];
}

if ( count( $items ) <= 1 ) {
	return;
}

$dark      = ! empty( $args['dark'] );
$nav_class = 'breadcrumb' . ( $dark ? ' breadcrumb--on-dark' : '' );
?>
<nav class="<?php echo esc_attr( $nav_class ); ?>" aria-label="<?php esc_attr_e( 'Breadcrumb', 'jalaversity' ); ?>">
	<ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
		<?php
		foreach ( $items as $index => $item ) :
			$position = $index + 1;
			$is_last  = $index === count( $items ) - 1;
			?>
		<li
			class="breadcrumb__item<?php echo $is_last ? ' breadcrumb__item--active' : ''; ?>"
			itemprop="itemListElement"
			itemscope
			itemtype="https://schema.org/ListItem"
		>
			<?php if ( $item['url'] && ! $is_last ) : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>" class="breadcrumb__link" itemprop="item">
					<span itemprop="name"><?php echo esc_html( $item['label'] ); ?></span>
				</a>
			<?php else : ?>
				<span class="breadcrumb__current" itemprop="name" aria-current="page">
					<?php echo esc_html( $item['label'] ); ?>
				</span>
			<?php endif; ?>
			<meta itemprop="position" content="<?php echo esc_attr( (string) $position ); ?>">
			<?php if ( ! $is_last ) : ?>
			<span class="breadcrumb__sep" aria-hidden="true">
				<?php jalaversity_icon_e( 'chevron-right', 14 ); ?>
			</span>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ol>
</nav>
