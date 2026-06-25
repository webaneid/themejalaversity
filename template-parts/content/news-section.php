<?php
/**
 * News Section — Berita & Pengumuman
 *
 * Komponen paling kompleks di homepage:
 * - Header kiri (label + H2) + tab filter kanan (Semua, Akademik, Prestasi, dll)
 * - Featured article besar + 3 artikel list (dinamis dari WP_Query)
 * - Kotak Pengumuman + Kotak Agenda (dari custom post type atau category khusus)
 *
 * JS (js/front/main.js → initNewsTabs()) mengelola tab state via data attributes.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section_label   = $args['label']   ?? __( 'Kabar Kampus', 'jalaversity' );
$section_heading = $args['heading'] ?? __( 'Berita & Pengumuman', 'jalaversity' );

// Tab kategori — hanya tampilkan yang punya post.
$news_cats_raw = get_categories(
	[
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'number'     => 6,
	]
);

$news_tabs = [ __( 'Semua', 'jalaversity' ) ];
foreach ( $news_cats_raw as $cat ) {
	$news_tabs[] = $cat->name;
}

// Query utama berita terbaru (Semua).
$news_query = new WP_Query(
	[
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 4,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	]
);

// Pengumuman — CPT 'pengumuman'. Jika kosong, block disembunyikan.
$announcement_query = new WP_Query(
	[
		'post_type'           => 'pengumuman',
		'post_status'         => 'publish',
		'posts_per_page'      => 4,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	]
);

// Agenda — CPT 'agenda', hanya yang tanggal_mulai >= hari ini, urut ASC.
$agenda_query = new WP_Query(
	[
		'post_type'           => 'agenda',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'meta_key'            => 'tanggal_mulai',
		'orderby'             => 'meta_value',
		'order'               => 'ASC',
		'meta_query'          => [
			[
				'key'     => 'tanggal_mulai',
				'value'   => gmdate( 'Ymd' ),
				'compare' => '>=',
				'type'    => 'NUMERIC',
			],
		],
	]
);

$archive_url = get_post_type_archive_link( 'post' ) ?: home_url( '/berita/' );
?>
<section class="news-section section-py" aria-labelledby="news-heading">
	<div class="container">

		<!-- ── Header + Tabs ──────────────────────────────────────────── -->
		<div class="news-section__top">
			<div class="news-section__heading-wrap">
				<?php jalaversity_section_label( $section_label ); ?>
				<h2 id="news-heading" class="text-section">
					<?php echo esc_html( $section_heading ); ?>
				</h2>
			</div>

			<?php /* Tab filter — dihandle JS via data-tab-target / data-tab-panel */ ?>
			<div class="news-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter berita', 'jalaversity' ); ?>">
				<?php foreach ( $news_tabs as $i => $tab_name ) :
					$slug = sanitize_title( $tab_name );
				?>
				<button
					class="news-tab<?php echo 0 === $i ? ' is-active' : ''; ?>"
					role="tab"
					data-tab-target="<?php echo esc_attr( $slug ); ?>"
					aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
				>
					<?php echo esc_html( $tab_name ); ?>
				</button>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- ── Articles grid (tab panel: "Semua") ─────────────────────── -->
		<div
			class="news-grid"
			data-tab-panel="<?php echo esc_attr( sanitize_title( $news_tabs[0] ) ); ?>"
			role="tabpanel"
			aria-labelledby=""
		>
			<?php if ( $news_query->have_posts() ) :
				$is_first = true;
				while ( $news_query->have_posts() ) :
					$news_query->the_post();
					$post_id    = get_the_ID();
					$categories = get_the_category();
					$cat_name   = $categories ? $categories[0]->name : '';

					if ( $is_first ) :
						// Featured (besar).
						?>
						<article class="card card--news-featured" aria-label="<?php esc_attr_e( 'Artikel utama', 'jalaversity' ); ?>">
							<div class="card__media card__media--news">
								<?php if ( has_post_thumbnail() ) :
									the_post_thumbnail(
										'medium_large',
										[
											'class'   => 'card__media-img',
											'loading' => 'lazy',
											'alt'     => esc_attr( get_the_title() ),
										]
									);
								else : ?>
									<div class="card__media-placeholder" aria-hidden="true"></div>
								<?php endif; ?>
								<?php if ( $cat_name ) : ?>
								<span class="badge"><?php echo esc_html( $cat_name ); ?></span>
								<?php endif; ?>
							</div>
							<div class="card__body">
								<div class="card__meta">
									<?php jalaversity_icon_e( 'calendar', 15 ); ?>
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
										<?php echo esc_html( get_the_date() ); ?>
									</time>
								</div>
								<h3 class="card__title card__title--featured">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<p class="card__desc"><?php the_excerpt(); ?></p>
								<a href="<?php the_permalink(); ?>" class="link-arrow link-arrow--gold">
									<?php esc_html_e( 'Baca selengkapnya', 'jalaversity' ); ?>
									<?php jalaversity_icon_e( 'chevron-right', 16 ); ?>
								</a>
							</div>
						</article>
						<?php
						$is_first = false;
						echo '<div class="news-list-stack">';
					else :
						// List item (kecil).
						?>
						<article class="card card--news-list">
							<div class="card__media card__media--list">
								<?php if ( has_post_thumbnail() ) :
									the_post_thumbnail(
										'thumbnail',
										[
											'class'   => 'card__media-img',
											'loading' => 'lazy',
											'alt'     => esc_attr( get_the_title() ),
										]
									);
								else : ?>
									<div class="card__media-placeholder card__media-placeholder--sm" aria-hidden="true"></div>
								<?php endif; ?>
							</div>
							<div class="card__body card__body--list">
								<div class="card__tags">
									<?php if ( $cat_name ) : ?>
									<span class="badge-cat"><?php echo esc_html( $cat_name ); ?></span>
									<?php endif; ?>
									<span class="card__date">
										<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
											<?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
										</time>
									</span>
								</div>
								<h4 class="card__title card__title--list">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h4>
							</div>
						</article>
						<?php
					endif;
				endwhile;
				echo '</div>'; // .news-list-stack
				wp_reset_postdata();
			else : ?>
				<p class="news-section__empty"><?php esc_html_e( 'Belum ada berita yang dipublikasikan.', 'jalaversity' ); ?></p>
			<?php endif; ?>
		</div><!-- /.news-grid -->

		<!-- ── Pengumuman + Agenda ─────────────────────────────────────── -->
		<?php
		$has_pengumuman = $announcement_query->have_posts();
		$has_agenda     = $agenda_query->have_posts();
		if ( $has_pengumuman || $has_agenda ) :
		?>
		<div class="news-section__bottom">

			<?php if ( $has_pengumuman ) : ?>
			<div class="news-box">
				<div class="news-box__header">
					<span class="news-box__icon" aria-hidden="true">
						<?php jalaversity_icon_e( 'bell', 20 ); ?>
					</span>
					<h3 class="news-box__title"><?php esc_html_e( 'Pengumuman', 'jalaversity' ); ?></h3>
				</div>

				<?php
				while ( $announcement_query->have_posts() ) :
					$announcement_query->the_post();
				?>
				<a href="<?php the_permalink(); ?>" class="news-box__item">
					<span class="news-box__date">
						<?php echo esc_html( get_the_date( 'd M' ) ); ?>
					</span>
					<span class="news-box__item-title"><?php the_title(); ?></span>
				</a>
				<?php endwhile;
				wp_reset_postdata(); ?>

				<a href="<?php echo esc_url( get_post_type_archive_link( 'pengumuman' ) ?: $archive_url ); ?>" class="news-box__link">
					<?php esc_html_e( 'Semua pengumuman', 'jalaversity' ); ?>
					<?php jalaversity_icon_e( 'chevron-right', 15 ); ?>
				</a>
			</div>
			<?php endif; ?>

			<?php if ( $has_agenda ) : ?>
			<div class="news-box">
				<div class="news-box__header">
					<span class="news-box__icon" aria-hidden="true">
						<?php jalaversity_icon_e( 'calendar', 20 ); ?>
					</span>
					<h3 class="news-box__title"><?php esc_html_e( 'Agenda', 'jalaversity' ); ?></h3>
				</div>

				<?php
				while ( $agenda_query->have_posts() ) :
					$agenda_query->the_post();
					$tgl_mulai  = get_field( 'tanggal_mulai' );
					$jam_mulai  = get_field( 'jam_mulai' );
					$jam_selesai = get_field( 'jam_selesai' );
					$lokasi     = get_field( 'lokasi' );
					$dt         = $tgl_mulai ? new DateTime( $tgl_mulai ) : null;
				?>
				<div class="agenda-item">
					<div class="agenda-item__date" aria-label="<?php echo $dt ? esc_attr( $dt->format( 'd M' ) ) : ''; ?>">
						<span class="agenda-item__day"><?php echo $dt ? esc_html( $dt->format( 'd' ) ) : '—'; ?></span>
						<span class="agenda-item__month"><?php echo $dt ? esc_html( strtoupper( $dt->format( 'M' ) ) ) : ''; ?></span>
					</div>
					<div class="agenda-item__info">
						<div class="agenda-item__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</div>
						<?php if ( $jam_mulai ) : ?>
						<div class="agenda-item__meta">
							<?php jalaversity_icon_e( 'clock', 13 ); ?>
							<?php
							echo esc_html( $jam_mulai );
							if ( $jam_selesai ) {
								echo ' – ' . esc_html( $jam_selesai );
							}
							?>
						</div>
						<?php endif; ?>
						<?php if ( $lokasi ) : ?>
						<div class="agenda-item__meta">
							<?php jalaversity_icon_e( 'map-pin', 13 ); ?>
							<?php echo esc_html( $lokasi ); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endwhile;
				wp_reset_postdata(); ?>

				<a href="<?php echo esc_url( get_post_type_archive_link( 'agenda' ) ?: $archive_url ); ?>" class="news-box__link">
					<?php esc_html_e( 'Lihat semua agenda', 'jalaversity' ); ?>
					<?php jalaversity_icon_e( 'chevron-right', 15 ); ?>
				</a>
			</div>
			<?php endif; ?>

		</div><!-- /.news-section__bottom -->
		<?php endif; // has_pengumuman || has_agenda ?>

	</div>
</section>
