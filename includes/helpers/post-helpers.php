<?php
/**
 * Post Helper Functions
 *
 * Reading time, view counter (AJAX non-blocking), related-posts query, dan
 * excerpt filter — pendukung desain artikel (single/archive/Halaman Blog)
 * yang diadaptasi dari theme referensi (jalawarta), tanpa fitur
 * monetisasi/SDK eksternal.
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
	$post_id = $post_id ?? (int) get_the_ID();
	$content = (string) get_post_field( 'post_content', $post_id );
	$words   = preg_split( '/\s+/', trim( wp_strip_all_tags( $content ) ) );
	$count   = is_array( $words ) ? count( $words ) : 0;

	return max( 1, (int) ceil( $count / 200 ) );
}

/**
 * Ambil jumlah views post.
 *
 * @param int $post_id ID post.
 * @return int
 */
function jalaversity_get_views( int $post_id ): int {
	return (int) get_post_meta( $post_id, '_jalaversity_views', true );
}

/**
 * Tambah 1 ke jumlah views post — hanya untuk post berstatus 'publish' dan
 * tidak dilindungi kata sandi. Dipanggil dari endpoint AJAX non-blocking
 * agar tidak ada penulisan DB saat render GET single.php.
 *
 * @param int $post_id ID post.
 */
function jalaversity_track_post_view( int $post_id ): void {
	if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		return;
	}

	if ( post_password_required( $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_jalaversity_views', jalaversity_get_views( $post_id ) + 1 );
}

/**
 * Endpoint AJAX untuk mencatat kunjungan artikel secara asinkron (kompatibel
 * dengan page caching & mematuhi aturan non-negotiable tanpa tulis DB saat GET).
 */
function jalaversity_ajax_track_post_view(): void {
	check_ajax_referer( 'jalaversity_ajax', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
	if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		wp_send_json_error( [ 'message' => 'Invalid post.' ], 400 );
	}

	jalaversity_track_post_view( $post_id );

	wp_send_json_success(
		[
			'views'     => jalaversity_get_views( $post_id ),
			'formatted' => sprintf(
				/* translators: %s: jumlah views terformat */
				__( '%s views', 'jalaversity' ),
				number_format_i18n( jalaversity_get_views( $post_id ) )
			),
		]
	);
}
add_action( 'wp_ajax_jalaversity_track_view', 'jalaversity_ajax_track_post_view' );
add_action( 'wp_ajax_nopriv_jalaversity_track_view', 'jalaversity_ajax_track_post_view' );

/**
 * Ambil post terkait: coba tags dulu, fallback ke categories, fallback ke
 * post terbaru acak. Dipakai oleh template-parts/components/related-posts.php
 * untuk menentukan heading yang sesuai ("Konten Terkait" vs "Konten Lain").
 *
 * @param int $post_id ID post.
 * @param int $count   Jumlah post terkait.
 * @return array{query: WP_Query, source: string} source: 'tags'|'categories'|'random'.
 */
function jalaversity_get_related_posts( int $post_id, int $count = 3 ): array {
	$base_args = [
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post__not_in'        => [ $post_id ],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	];

	$tag_ids = wp_get_post_tags( $post_id, [ 'fields' => 'ids' ] );

	if ( is_array( $tag_ids ) && $tag_ids ) {
		$query = new WP_Query( array_merge( $base_args, [ 'tag__in' => $tag_ids ] ) );

		if ( $query->have_posts() ) {
			return [
				'query'  => $query,
				'source' => 'tags',
			];
		}
	}

	$cat_ids = wp_get_post_categories( $post_id );

	if ( $cat_ids ) {
		$query = new WP_Query( array_merge( $base_args, [ 'category__in' => $cat_ids ] ) );

		if ( $query->have_posts() ) {
			return [
				'query'  => $query,
				'source' => 'categories',
			];
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
		<time class="post-meta-line__date" datetime="<?php echo esc_attr( (string) get_the_date( 'c' ) ); ?>">
			<?php echo esc_html( (string) get_the_date( 'l, j F Y' ) ); ?>
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
 * @param string $title Judul archive awal.
 * @return string
 */
function jalaversity_clean_archive_title( string $title ): string {
	if ( is_category() ) {
		$title = (string) single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$title = (string) single_tag_title( '', false );
	} elseif ( is_tax() ) {
		$title = (string) single_term_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	} elseif ( is_post_type_archive() ) {
		$title = (string) post_type_archive_title( '', false );
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
		'<%1$s class="card__title"><a href="%2$s">%3$s</a></%1$s>',
		$tag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $tag di-tag_escape()
		esc_url( (string) get_permalink() ),
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
	echo '<a href="' . esc_url( (string) get_permalink() ) . '" class="card__media-link">';
	echo '<div class="card__media">';

	if ( has_post_thumbnail() ) {
		the_post_thumbnail(
			$size,
			[
				'class'   => 'card__media-img',
				'loading' => 'lazy',
			]
		);
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
