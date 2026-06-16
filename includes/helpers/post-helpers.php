<?php
/**
 * Post Helper Functions
 *
 * Reading time, view counter, related-posts query, dan excerpt filter —
 * pendukung desain artikel (single/archive/Halaman Blog) yang diadaptasi
 * dari theme referensi (jalawarta), tanpa fitur monetisasi/SDK eksternal.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Estimasi waktu baca dalam menit (jumlah kata ÷ 200 kata/menit).
 *
 * @param int|null $post_id Default: post saat ini (get_the_ID()).
 * @return int Minimal 1.
 */
function jalaversity_reading_time( ?int $post_id = null ): int {
	$post_id = $post_id ?? get_the_ID();
	$content = (string) get_post_field( 'post_content', $post_id );
	$words   = count( preg_split( '/\s+/', trim( wp_strip_all_tags( $content ) ) ) );

	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Ambil jumlah views post.
 *
 * @param int $post_id
 * @return int
 */
function jalaversity_get_views( int $post_id ): int {
	return (int) get_post_meta( $post_id, '_jalaversity_views', true );
}

/**
 * Tambah 1 ke jumlah views post — plain post meta, BUKAN field ACF
 * (ini counter terprogram, bukan konten yang diedit admin lewat editor).
 * Dipanggil sekali per page-load single.php. Implementasi sederhana
 * (tidak ada dedup per session/IP) — cukup untuk kebutuhan saat ini,
 * bisa diperketat nanti tanpa mengubah signature fungsi ini.
 *
 * @param int $post_id
 */
function jalaversity_track_post_view( int $post_id ): void {
	update_post_meta( $post_id, '_jalaversity_views', jalaversity_get_views( $post_id ) + 1 );
}

/**
 * Ambil post terkait: coba tags dulu, fallback ke categories, fallback ke
 * post terbaru acak. Dipakai oleh template-parts/components/related-posts.php
 * untuk menentukan heading yang sesuai ("Konten Terkait" vs "Konten Lain").
 *
 * @param int $post_id
 * @param int $count
 * @return array{query: WP_Query, source: string} source: 'tags'|'categories'|'random'.
 */
function jalaversity_get_related_posts( int $post_id, int $count = 3 ): array {
	$base_args = [
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post__not_in'        => [ $post_id ],
		'ignore_sticky_posts' => true,
	];

	$tag_ids = wp_get_post_tags( $post_id, [ 'fields' => 'ids' ] );

	if ( $tag_ids ) {
		$query = new WP_Query( array_merge( $base_args, [ 'tag__in' => $tag_ids ] ) );

		if ( $query->have_posts() ) {
			return [ 'query' => $query, 'source' => 'tags' ];
		}
	}

	$cat_ids = wp_get_post_categories( $post_id );

	if ( $cat_ids ) {
		$query = new WP_Query( array_merge( $base_args, [ 'category__in' => $cat_ids ] ) );

		if ( $query->have_posts() ) {
			return [ 'query' => $query, 'source' => 'categories' ];
		}
	}

	return [
		'query'  => new WP_Query( array_merge( $base_args, [ 'orderby' => 'rand' ] ) ),
		'source' => 'random',
	];
}

/**
 * Render meta line seragam: "Kategori - Hari, Tanggal Bulan Tahun" (mis.
 * "Akademik - Selasa, 10 Januari 2026"). SATU fungsi, SATU class CSS
 * (`.post-meta-line`), dipakai identik di semua varian content-card.php
 * DAN content-single.php — supaya tampilan meta konsisten di seluruh situs,
 * bukan markup berbeda-beda per tempat.
 *
 * Hanya kategori PERTAMA yang ditampilkan (bukan semua badge) — sesuai
 * format yang diminta, satu kategori satu tanggal, bukan daftar badge.
 */
function jalaversity_post_meta_line(): void {
	$categories = get_the_category();
	$category   = $categories ? $categories[0] : null;
	?>
	<div class="post-meta-line">
		<?php if ( $category ) : ?>
		<a href="<?php echo esc_url( get_category_link( $category ) ); ?>" class="post-meta-line__category">
			<?php echo esc_html( $category->name ); ?>
		</a>
		<span class="post-meta-line__sep" aria-hidden="true">-</span>
		<?php endif; ?>
		<time class="post-meta-line__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
			<?php echo esc_html( get_the_date( 'l, j F Y' ) ); ?>
		</time>
	</div>
	<?php
}

/**
 * Bersihkan archive title dari prefix default WP core ("Category: X",
 * "Tag: Y", dst) supaya konsisten dengan desain custom — dibangun ulang
 * dari fungsi single_*_title() per context, bukan strip string (locale-proof,
 * tidak rapuh terhadap terjemahan WP core).
 *
 * @param string $title
 * @return string
 */
function jalaversity_clean_archive_title( string $title ): string {
	if ( is_category() ) {
		$title = single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', false );
	} elseif ( is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'jalaversity_clean_archive_title' );

/**
 * Render judul kartu post (loop-context, link ke permalink). Dipakai
 * bersama oleh semua varian content-card.php — bukan di-copy per varian.
 *
 * @param string $tag HTML tag pembungkus (default 'h3').
 */
function jalaversity_card_title( string $tag = 'h3' ): void {
	$tag = tag_escape( $tag );
	printf(
		'<%1$s class="card__title"><a href="%2$s">%3$s</a></%1$s>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $tag di-tag_escape()
		$tag,
		esc_url( get_permalink() ),
		esc_html( get_the_title() )
	);
}

/**
 * Render featured image kartu post (dibungkus link ke permalink), dengan
 * fallback placeholder icon kalau post tidak punya thumbnail. Dipakai
 * bersama oleh varian content-card.php yang menampilkan gambar
 * ('overlay'/'list'/'klasik' — bukan 'title').
 *
 * @param string $size      Nama image size WP (default 'jalaversity-medium', 16:9 800x450).
 * @param int    $icon_size Ukuran icon placeholder (default 32).
 */
function jalaversity_card_thumbnail( string $size = 'jalaversity-medium', int $icon_size = 32 ): void {
	echo '<a href="' . esc_url( get_permalink() ) . '" class="card__media-link">';
	echo '<div class="card__media">';

	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $size, [ 'class' => 'card__media-img', 'loading' => 'lazy' ] );
	} else {
		echo '<div class="card__media-placeholder" aria-hidden="true">';
		jalaversity_icon_e( 'photo', $icon_size );
		echo '</div>';
	}

	echo '</div></a>';
}

/**
 * Custom panjang excerpt (kata), menggantikan default WP (55 kata).
 *
 * @return int
 */
function jalaversity_excerpt_length(): int {
	return 20;
}
add_filter( 'excerpt_length', 'jalaversity_excerpt_length' );

/**
 * Custom suffix excerpt ("…" bukan "[&hellip;]" default WP).
 *
 * @return string
 */
function jalaversity_excerpt_more(): string {
	return '…';
}
add_filter( 'excerpt_more', 'jalaversity_excerpt_more' );
