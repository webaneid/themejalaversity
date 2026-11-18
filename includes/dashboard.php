<?php
/**
 * Custom Admin Dashboard — Jalaversity (`includes/dashboard.php`).
 *
 * Mengadopsi desain custom dashboard Makhadane / Elemenane:
 * - Menggantikan halaman Dashboard bawaan WordPress (`index.php`) dengan
 *   halaman `jalaversity-dashboard`
 * - 4 Kartu KPI utama: Total Visitors (`_jalaversity_views`), Total Articles,
 *   Total Comments, dan Active Authors
 * - Content Analytics: Grafik kurva Chart.js 12 bulan terakhir (Published Posts & Total Views)
 * - 3 Kolom: Popular Posts, Recent Posts (+ tombol Create New Post), Author Performance
 * - Content Distribution: Categories, Tags, Agenda, Pengumuman, Draft Posts, Scheduled,
 *   serta progress bar Content Goal This Month
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Daftarkan halaman custom dashboard di menu admin dan sembunyikan `index.php` bawaan.
 */
function jalaversity_add_dashboard_page(): void {
	add_menu_page(
		__( 'Dashboard', 'jalaversity' ),
		__( 'Dashboard', 'jalaversity' ),
		'read',
		'jalaversity-dashboard',
		'jalaversity_render_dashboard_page',
		'dashicons-dashboard',
		2
	);

	remove_menu_page( 'index.php' );
}
add_action( 'admin_menu', 'jalaversity_add_dashboard_page' );

/**
 * Redirect `wp-admin/index.php` ke halaman custom dashboard `jalaversity-dashboard`.
 */
function jalaversity_redirect_dashboard(): void {
	wp_safe_redirect( admin_url( 'admin.php?page=jalaversity-dashboard' ) );
	exit;
}
add_action( 'load-index.php', 'jalaversity_redirect_dashboard' );

/**
 * Enqueue Chart.js dan konfigurasi grafik pada halaman custom dashboard.
 *
 * @param string $hook Hook halaman admin aktif.
 */
function jalaversity_dashboard_assets( string $hook ): void {
	if ( 'toplevel_page_jalaversity-dashboard' !== $hook ) {
		return;
	}

	wp_enqueue_script(
		'jalaversity-chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
		[],
		JALAVERSITY_VERSION,
		true
	);

	$chart_data = jalaversity_get_posts_per_month_data();

	wp_localize_script(
		'jalaversity-chartjs',
		'jalaversityDashboard',
		[
			'postsData' => $chart_data,
			'colors'    => [
				'primary'   => '#dc3545',
				'secondary' => '#ff6b35',
				'accent'    => '#e0ddcf',
				'body'      => '#3c434a',
			],
		]
	);

	wp_add_inline_script(
		'jalaversity-chartjs',
		"(function(){'use strict';document.addEventListener('DOMContentLoaded',function(){if(typeof Chart==='undefined'||typeof jalaversityDashboard==='undefined'){return;}var chartCanvas=document.getElementById('ane-posts-chart');if(!chartCanvas){return;}var data=jalaversityDashboard.postsData;var colors=jalaversityDashboard.colors;var ctx=chartCanvas.getContext('2d');var postsGradient=ctx.createLinearGradient(0,0,0,400);postsGradient.addColorStop(0,colors.primary+'40');postsGradient.addColorStop(1,colors.primary+'00');var viewsGradient=ctx.createLinearGradient(0,0,0,400);viewsGradient.addColorStop(0,colors.secondary+'40');viewsGradient.addColorStop(1,colors.secondary+'00');new Chart(ctx,{type:'line',data:{labels:data.labels,datasets:[{label:'Published Posts',data:data.posts,borderColor:colors.primary,backgroundColor:postsGradient,borderWidth:2,fill:true,tension:0.4,pointRadius:4,pointHoverRadius:6,pointBackgroundColor:'#ffffff',pointBorderColor:colors.primary,pointBorderWidth:2,pointHoverBackgroundColor:colors.primary,pointHoverBorderColor:'#ffffff'},{label:'Total Views',data:data.views,borderColor:colors.secondary,backgroundColor:viewsGradient,borderWidth:2,fill:true,tension:0.4,pointRadius:4,pointHoverRadius:6,pointBackgroundColor:'#ffffff',pointBorderColor:colors.secondary,pointBorderWidth:2,pointHoverBackgroundColor:colors.secondary,pointHoverBorderColor:'#ffffff'}]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{display:false},tooltip:{backgroundColor:'rgba(255, 255, 255, 0.95)',titleColor:colors.body,bodyColor:colors.body,borderColor:colors.accent,borderWidth:1,padding:12,displayColors:true,boxWidth:12,boxHeight:12,usePointStyle:true,callbacks:{label:function(context){var label=context.dataset.label?context.dataset.label+': ':'';return label+new Intl.NumberFormat().format(context.parsed.y);}}}},scales:{x:{grid:{display:false,drawBorder:false},ticks:{color:colors.body,font:{size:11}}},y:{beginAtZero:true,grid:{color:colors.accent,drawBorder:false},ticks:{color:colors.body,font:{size:11},callback:function(value){return new Intl.NumberFormat().format(value);}}}}}});});})();"
	);
}
add_action( 'admin_enqueue_scripts', 'jalaversity_dashboard_assets' );

/**
 * Hapus cache statistik dashboard saat post atau komentar berubah.
 */
function jalaversity_flush_dashboard_cache(): void {
	delete_transient( 'jalaversity_dashboard_stats' );
	delete_transient( 'jalaversity_dashboard_chart' );
}
add_action( 'save_post', 'jalaversity_flush_dashboard_cache' );
add_action( 'deleted_post', 'jalaversity_flush_dashboard_cache' );
add_action( 'wp_insert_comment', 'jalaversity_flush_dashboard_cache' );
add_action( 'edit_comment', 'jalaversity_flush_dashboard_cache' );

/**
 * Render halaman utama Custom Dashboard.
 */
function jalaversity_render_dashboard_page(): void {
	?>
	<div class="wrap ane-custom-dashboard">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Dashboard', 'jalaversity' ); ?></h1>

		<div class="ane-dashboard-grid">
			<!-- Stats Cards -->
			<div class="ane-dashboard-section ane-dashboard-section--full">
				<?php jalaversity_dashboard_stats_cards(); ?>
			</div>

			<!-- Chart -->
			<div class="ane-dashboard-section ane-dashboard-section--full">
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Content Analytics', 'jalaversity' ); ?></h2>
					<div class="inside">
						<?php jalaversity_dashboard_posts_chart(); ?>
					</div>
				</div>
			</div>

			<!-- 3 Column Grid -->
			<div class="ane-dashboard-section">
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Popular Posts', 'jalaversity' ); ?></h2>
					<div class="inside">
						<?php jalaversity_dashboard_popular_posts(); ?>
					</div>
				</div>
			</div>

			<div class="ane-dashboard-section">
				<div class="postbox">
					<div class="ane-section-header">
						<h2 class="hndle"><?php esc_html_e( 'Recent Posts', 'jalaversity' ); ?></h2>
						<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'Create New Post', 'jalaversity' ); ?>
						</a>
					</div>
					<div class="inside">
						<?php jalaversity_dashboard_recent_posts(); ?>
					</div>
				</div>
			</div>

			<div class="ane-dashboard-section">
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Author Performance', 'jalaversity' ); ?></h2>
					<div class="inside">
						<?php jalaversity_dashboard_top_authors(); ?>
					</div>
				</div>
			</div>

			<!-- Content Distribution -->
			<div class="ane-dashboard-section ane-dashboard-section--full">
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Content Distribution', 'jalaversity' ); ?></h2>
					<div class="inside">
						<?php jalaversity_dashboard_content_breakdown(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render 4 kartu KPI utama (Total Visitors, Total Articles, Total Comments, Active Authors).
 */
function jalaversity_dashboard_stats_cards(): void {
	$stats = jalaversity_get_dashboard_stats();
	?>
	<div class="ane-dashboard">
		<div class="ane-stats-grid">
			<!-- Total Visitors -->
			<div class="ane-stat-card ane-stat-card--primary">
				<div class="ane-stat-card__icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
						<circle cx="9" cy="7" r="4"></circle>
						<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
						<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
					</svg>
				</div>
				<div class="ane-stat-card__content">
					<div class="ane-stat-card__label"><?php esc_html_e( 'Total Visitors', 'jalaversity' ); ?></div>
					<div class="ane-stat-card__value"><?php echo esc_html( number_format_i18n( $stats['total_views'] ) ); ?></div>
					<?php
					$growth_value = (float) $stats['views_growth'];
					$growth_class = 'ane-stat-card__change';
					$growth_sign  = '';

					if ( $growth_value > 0 ) {
						$growth_class .= ' ane-stat-card__change--up';
						$growth_sign   = '+';
					} elseif ( $growth_value < 0 ) {
						$growth_class .= ' ane-stat-card__change--down';
						$growth_sign   = '-';
					} else {
						$growth_class .= ' ane-stat-card__change--neutral';
						$growth_sign   = '+';
					}
					?>
					<div class="<?php echo esc_attr( $growth_class ); ?>">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: tanda +/-, 2: persentase perubahan */
								__( '%1$s%2$s%% this month', 'jalaversity' ),
								$growth_sign,
								number_format_i18n( abs( $growth_value ), 1 )
							)
						);
						?>
					</div>
				</div>
			</div>

			<!-- Total Articles -->
			<div class="ane-stat-card ane-stat-card--secondary">
				<div class="ane-stat-card__icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
						<polyline points="14 2 14 8 20 8"></polyline>
						<line x1="16" y1="13" x2="8" y2="13"></line>
						<line x1="16" y1="17" x2="8" y2="17"></line>
						<polyline points="10 9 9 9 8 9"></polyline>
					</svg>
				</div>
				<div class="ane-stat-card__content">
					<div class="ane-stat-card__label"><?php esc_html_e( 'Total Articles', 'jalaversity' ); ?></div>
					<div class="ane-stat-card__value"><?php echo esc_html( number_format_i18n( $stats['total_posts'] ) ); ?></div>
					<div class="ane-stat-card__change ane-stat-card__change--up">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: jumlah artikel bulan ini */
								__( '+%s this month', 'jalaversity' ),
								number_format_i18n( $stats['posts_this_month'] )
							)
						);
						?>
					</div>
				</div>
			</div>

			<!-- Total Comments -->
			<div class="ane-stat-card ane-stat-card--accent">
				<div class="ane-stat-card__icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
					</svg>
				</div>
				<div class="ane-stat-card__content">
					<div class="ane-stat-card__label"><?php esc_html_e( 'Total Comments', 'jalaversity' ); ?></div>
					<div class="ane-stat-card__value"><?php echo esc_html( number_format_i18n( $stats['total_comments'] ) ); ?></div>
					<div class="ane-stat-card__change ane-stat-card__change--up">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: jumlah komentar bulan ini */
								__( '+%s this month', 'jalaversity' ),
								number_format_i18n( $stats['comments_this_month'] )
							)
						);
						?>
					</div>
				</div>
			</div>

			<!-- Active Authors -->
			<div class="ane-stat-card ane-stat-card--light">
				<div class="ane-stat-card__icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
						<circle cx="9" cy="7" r="4"></circle>
						<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
						<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
					</svg>
				</div>
				<div class="ane-stat-card__content">
					<div class="ane-stat-card__label"><?php esc_html_e( 'Active Authors', 'jalaversity' ); ?></div>
					<div class="ane-stat-card__value"><?php echo esc_html( number_format_i18n( $stats['active_authors'] ) ); ?></div>
					<div class="ane-stat-card__change">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: jumlah penulis aktif bulan ini */
								__( '%s authors this month', 'jalaversity' ),
								number_format_i18n( $stats['authors_this_month'] )
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render kanvas grafik Chart.js 12 bulan terakhir beserta legenda.
 */
function jalaversity_dashboard_posts_chart(): void {
	?>
	<div class="ane-dashboard">
		<div class="ane-chart-container">
			<canvas id="ane-posts-chart"></canvas>
		</div>
		<div class="ane-chart-legend">
			<div class="ane-chart-legend__item">
				<span class="ane-chart-legend__dot ane-chart-legend__dot--posts"></span>
				<span><?php esc_html_e( 'Published Posts', 'jalaversity' ); ?></span>
			</div>
			<div class="ane-chart-legend__item">
				<span class="ane-chart-legend__dot ane-chart-legend__dot--views"></span>
				<span><?php esc_html_e( 'Total Views', 'jalaversity' ); ?></span>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render widget Popular Posts (5 artikel dengan views tertinggi).
 */
function jalaversity_dashboard_popular_posts(): void {
	$popular = jalaversity_get_popular_posts_dashboard( 5 );

	if ( empty( $popular ) ) {
		echo '<p class="ane-empty-state">' . esc_html__( 'No posts found.', 'jalaversity' ) . '</p>';
		return;
	}
	?>
	<div class="ane-dashboard">
		<ul class="ane-post-list">
			<?php foreach ( $popular as $item ) : ?>
				<li class="ane-post-list__item">
					<div class="ane-post-list__rank"><?php echo esc_html( (string) $item['rank'] ); ?></div>
					<div class="ane-post-list__content">
						<a href="<?php echo esc_url( (string) get_edit_post_link( $item['id'] ) ); ?>" class="ane-post-list__title">
							<?php echo esc_html( $item['title'] ); ?>
						</a>
						<div class="ane-post-list__meta">
							<span class="ane-post-list__views">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
									<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
									<circle cx="12" cy="12" r="3"></circle>
								</svg>
								<?php echo esc_html( number_format_i18n( $item['views'] ) ); ?>
							</span>
							<span class="ane-post-list__date">
								<?php echo esc_html( $item['date'] ); ?>
							</span>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Render widget Recent Posts (5 artikel terbaru).
 */
function jalaversity_dashboard_recent_posts(): void {
	$recent_posts = get_posts(
		[
			'numberposts' => 5,
			'post_type'   => 'post',
			'post_status' => 'publish',
			'orderby'     => 'date',
			'order'       => 'DESC',
		]
	);

	if ( empty( $recent_posts ) ) {
		echo '<p class="ane-empty-state">' . esc_html__( 'No posts found.', 'jalaversity' ) . '</p>';
		return;
	}
	?>
	<div class="ane-dashboard">
		<ul class="ane-post-list">
			<?php
			$rank = 1;
			$now  = time();
			foreach ( $recent_posts as $recent_post ) :
				$views     = jalaversity_get_views( (int) $recent_post->ID );
				$post_time = (int) get_post_time( 'U', true, $recent_post );
				?>
				<li class="ane-post-list__item">
					<div class="ane-post-list__rank"><?php echo esc_html( (string) $rank++ ); ?></div>
					<div class="ane-post-list__content">
						<a href="<?php echo esc_url( (string) get_edit_post_link( $recent_post->ID ) ); ?>" class="ane-post-list__title">
							<?php echo esc_html( $recent_post->post_title ); ?>
						</a>
						<div class="ane-post-list__meta">
							<span class="ane-post-list__views">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
									<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
									<circle cx="12" cy="12" r="3"></circle>
								</svg>
								<?php echo esc_html( number_format_i18n( $views ) ); ?>
							</span>
							<span class="ane-post-list__date">
								<?php echo esc_html( human_time_diff( $post_time > 0 ? $post_time : $now, $now ) . ' ' . __( 'ago', 'jalaversity' ) ); ?>
							</span>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Render widget Author Performance (5 penulis teratas).
 */
function jalaversity_dashboard_top_authors(): void {
	$authors = jalaversity_get_top_authors_dashboard( 5 );

	if ( empty( $authors ) ) {
		echo '<p class="ane-empty-state">' . esc_html__( 'No authors found.', 'jalaversity' ) . '</p>';
		return;
	}
	?>
	<div class="ane-dashboard">
		<ul class="ane-author-list">
			<?php foreach ( $authors as $author ) : ?>
				<li class="ane-author-list__item">
					<div class="ane-author-list__avatar">
						<?php echo get_avatar( $author['id'], 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="ane-author-list__content">
						<a href="<?php echo esc_url( (string) get_edit_user_link( $author['id'] ) ); ?>" class="ane-author-list__name">
							<?php echo esc_html( $author['name'] ); ?>
						</a>
						<div class="ane-author-list__stats">
							<span><?php echo esc_html( number_format_i18n( $author['posts'] ) ); ?> <?php esc_html_e( 'posts', 'jalaversity' ); ?></span>
							<span>•</span>
							<span><?php echo esc_html( number_format_i18n( $author['views'] ) ); ?> <?php esc_html_e( 'views', 'jalaversity' ); ?></span>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Render widget Content Distribution & Content Goal This Month.
 */
function jalaversity_dashboard_content_breakdown(): void {
	$breakdown = jalaversity_get_content_breakdown();
	?>
	<div class="ane-dashboard">
		<div class="ane-breakdown-grid">
			<div class="ane-breakdown-item">
				<div class="ane-breakdown-item__label"><?php esc_html_e( 'Categories', 'jalaversity' ); ?></div>
				<div class="ane-breakdown-item__value"><?php echo esc_html( number_format_i18n( $breakdown['categories'] ) ); ?></div>
			</div>
			<div class="ane-breakdown-item">
				<div class="ane-breakdown-item__label"><?php esc_html_e( 'Tags', 'jalaversity' ); ?></div>
				<div class="ane-breakdown-item__value"><?php echo esc_html( number_format_i18n( $breakdown['tags'] ) ); ?></div>
			</div>
			<div class="ane-breakdown-item">
				<div class="ane-breakdown-item__label"><?php esc_html_e( 'Agenda Kampus', 'jalaversity' ); ?></div>
				<div class="ane-breakdown-item__value"><?php echo esc_html( number_format_i18n( $breakdown['agenda'] ) ); ?></div>
			</div>
			<div class="ane-breakdown-item">
				<div class="ane-breakdown-item__label"><?php esc_html_e( 'Pengumuman', 'jalaversity' ); ?></div>
				<div class="ane-breakdown-item__value"><?php echo esc_html( number_format_i18n( $breakdown['pengumuman'] ) ); ?></div>
			</div>
			<div class="ane-breakdown-item">
				<div class="ane-breakdown-item__label"><?php esc_html_e( 'Draft Posts', 'jalaversity' ); ?></div>
				<div class="ane-breakdown-item__value"><?php echo esc_html( number_format_i18n( $breakdown['drafts'] ) ); ?></div>
			</div>
			<div class="ane-breakdown-item">
				<div class="ane-breakdown-item__label"><?php esc_html_e( 'Scheduled', 'jalaversity' ); ?></div>
				<div class="ane-breakdown-item__value"><?php echo esc_html( number_format_i18n( $breakdown['scheduled'] ) ); ?></div>
			</div>
		</div>

		<div class="ane-breakdown-progress">
			<div class="ane-breakdown-progress__label">
				<?php esc_html_e( 'Content Goal This Month', 'jalaversity' ); ?>
				<span><?php echo esc_html( (string) $breakdown['posts_this_month'] ); ?> / <?php echo esc_html( (string) $breakdown['goal'] ); ?></span>
			</div>
			<div class="ane-breakdown-progress__bar">
				<div class="ane-breakdown-progress__fill" style="width: <?php echo esc_attr( (string) $breakdown['goal_percentage'] ); ?>%;"></div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Ambil statistik utama dashboard (di-cache transient 10 menit).
 *
 * @return array{
 *     total_views: int,
 *     views_growth: float,
 *     total_posts: int,
 *     posts_this_month: int,
 *     total_comments: int,
 *     comments_this_month: int,
 *     active_authors: int,
 *     authors_this_month: int
 * }
 */
function jalaversity_get_dashboard_stats(): array {
	$cached = get_transient( 'jalaversity_dashboard_stats' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	$now_month  = (int) gmdate( 'n' );
	$now_year   = (int) gmdate( 'Y' );
	$prev_ts    = (int) strtotime( '-1 month' );
	$prev_month = (int) gmdate( 'n', $prev_ts );
	$prev_year  = (int) gmdate( 'Y', $prev_ts );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Di-cache via transient jalaversity_dashboard_stats (10 menit).
	$total_views = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)), 0)
			FROM {$wpdb->postmeta}
			WHERE meta_key = %s",
			'_jalaversity_views'
		)
	);

	$views_this_month = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0)
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = %s
			AND p.post_type = 'post'
			AND p.post_status = 'publish'
			AND MONTH(p.post_date) = %d
			AND YEAR(p.post_date) = %d",
			'_jalaversity_views',
			$now_month,
			$now_year
		)
	);

	$views_last_month = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0)
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = %s
			AND p.post_type = 'post'
			AND p.post_status = 'publish'
			AND MONTH(p.post_date) = %d
			AND YEAR(p.post_date) = %d",
			'_jalaversity_views',
			$prev_month,
			$prev_year
		)
	);

	$views_growth = 0.0;
	if ( $views_last_month > 0 ) {
		$views_growth = ( ( $views_this_month - $views_last_month ) / $views_last_month ) * 100.0;
	}

	$post_counts = wp_count_posts( 'post' );
	$total_posts = isset( $post_counts->publish ) ? (int) $post_counts->publish : 0;

	$posts_this_month = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'post'
			AND post_status = 'publish'
			AND MONTH(post_date) = %d
			AND YEAR(post_date) = %d",
			$now_month,
			$now_year
		)
	);

	$comment_counts = wp_count_comments();
	$total_comments = isset( $comment_counts->approved ) ? (int) $comment_counts->approved : 0;

	$comments_this_month = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$wpdb->comments}
			WHERE comment_approved = '1'
			AND MONTH(comment_date) = %d
			AND YEAR(comment_date) = %d",
			$now_month,
			$now_year
		)
	);

	$active_authors = (int) $wpdb->get_var(
		"SELECT COUNT(DISTINCT post_author)
		FROM {$wpdb->posts}
		WHERE post_type = 'post'
		AND post_status = 'publish'"
	);

	$authors_this_month = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT post_author)
			FROM {$wpdb->posts}
			WHERE post_type = 'post'
			AND post_status = 'publish'
			AND MONTH(post_date) = %d
			AND YEAR(post_date) = %d",
			$now_month,
			$now_year
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	$stats = [
		'total_views'         => $total_views,
		'views_growth'        => $views_growth,
		'total_posts'         => $total_posts,
		'posts_this_month'    => $posts_this_month,
		'total_comments'      => $total_comments,
		'comments_this_month' => $comments_this_month,
		'active_authors'      => $active_authors,
		'authors_this_month'  => $authors_this_month,
	];

	set_transient( 'jalaversity_dashboard_stats', $stats, 10 * MINUTE_IN_SECONDS );

	return $stats;
}

/**
 * Ambil data artikel & views per bulan selama 12 bulan terakhir (di-cache transient 10 menit).
 *
 * @return array{labels: string[], posts: int[], views: int[]}
 */
function jalaversity_get_posts_per_month_data(): array {
	$cached = get_transient( 'jalaversity_dashboard_chart' );
	if ( is_array( $cached ) && isset( $cached['labels'], $cached['posts'], $cached['views'] ) ) {
		return $cached;
	}

	global $wpdb;

	$data = [
		'labels' => [],
		'posts'  => [],
		'views'  => [],
	];

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Di-cache via transient jalaversity_dashboard_chart (10 menit).
	for ( $i = 11; $i >= 0; $i-- ) {
		$ts    = (int) strtotime( "-{$i} months" );
		$month = (int) gmdate( 'n', $ts );
		$year  = (int) gmdate( 'Y', $ts );

		$data['labels'][] = wp_date( 'M Y', $ts );

		$posts_count     = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND MONTH(post_date) = %d
				AND YEAR(post_date) = %d",
				$month,
				$year
			)
		);
		$data['posts'][] = $posts_count;

		$views_count     = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0)
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				WHERE pm.meta_key = %s
				AND p.post_type = 'post'
				AND p.post_status = 'publish'
				AND MONTH(p.post_date) = %d
				AND YEAR(p.post_date) = %d",
				'_jalaversity_views',
				$month,
				$year
			)
		);
		$data['views'][] = $views_count;
	}
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	set_transient( 'jalaversity_dashboard_chart', $data, 10 * MINUTE_IN_SECONDS );

	return $data;
}

/**
 * Ambil daftar artikel populer untuk dashboard.
 *
 * @param int $limit Jumlah artikel.
 * @return array<int, array{id: int, rank: int, title: string, views: int, date: string}>
 */
function jalaversity_get_popular_posts_dashboard( int $limit = 5 ): array {
	$query = new WP_Query(
		[
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'meta_key'            => '_jalaversity_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		]
	);

	// Fallback ke artikel terbaru jika belum ada artikel yang memiliki meta _jalaversity_views.
	if ( ! $query->have_posts() ) {
		$query = new WP_Query(
			[
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $limit,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			]
		);
	}

	$items = [];
	$rank  = 1;
	$now   = time();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id   = (int) get_the_ID();
			$post_time = (int) get_post_time( 'U', true, $post_id );
			$items[]   = [
				'id'    => $post_id,
				'rank'  => $rank++,
				'title' => get_the_title(),
				'views' => jalaversity_get_views( $post_id ),
				'date'  => human_time_diff( $post_time > 0 ? $post_time : $now, $now ) . ' ' . __( 'ago', 'jalaversity' ),
			];
		}
		wp_reset_postdata();
	}

	return $items;
}

/**
 * Ambil daftar penulis teratas berdasarkan total views & jumlah artikel.
 *
 * @param int $limit Jumlah penulis.
 * @return array<int, array{id: int, name: string, posts: int, views: int}>
 */
function jalaversity_get_top_authors_dashboard( int $limit = 5 ): array {
	global $wpdb;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT
				p.post_author,
				COUNT(DISTINCT p.ID) AS post_count,
				COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0) AS total_views
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			WHERE p.post_type = 'post'
			AND p.post_status = 'publish'
			GROUP BY p.post_author
			ORDER BY total_views DESC, post_count DESC
			LIMIT %d",
			'_jalaversity_views',
			$limit
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	$authors = [];

	if ( is_array( $results ) ) {
		foreach ( $results as $result ) {
			$author_id = (int) $result->post_author;
			$user      = get_userdata( $author_id );
			if ( $user ) {
				$authors[] = [
					'id'    => $author_id,
					'name'  => (string) $user->display_name,
					'posts' => (int) $result->post_count,
					'views' => (int) $result->total_views,
				];
			}
		}
	}

	return $authors;
}

/**
 * Ambil rincian distribusi konten kampus & progres target bulanan.
 *
 * @return array{
 *     categories: int,
 *     tags: int,
 *     agenda: int,
 *     pengumuman: int,
 *     drafts: int,
 *     scheduled: int,
 *     posts_this_month: int,
 *     goal: int,
 *     goal_percentage: float
 * }
 */
function jalaversity_get_content_breakdown(): array {
	$cat_count = wp_count_terms( [ 'taxonomy' => 'category' ] );
	$tag_count = wp_count_terms( [ 'taxonomy' => 'post_tag' ] );

	$post_counts       = wp_count_posts( 'post' );
	$agenda_counts     = wp_count_posts( 'agenda' );
	$pengumuman_counts = wp_count_posts( 'pengumuman' );

	$stats            = jalaversity_get_dashboard_stats();
	$posts_this_month = (int) $stats['posts_this_month'];
	$goal             = 30;
	$goal_percentage  = min( 100.0, ( $posts_this_month / $goal ) * 100.0 );

	return [
		'categories'       => is_wp_error( $cat_count ) ? 0 : (int) $cat_count,
		'tags'             => is_wp_error( $tag_count ) ? 0 : (int) $tag_count,
		'agenda'           => isset( $agenda_counts->publish ) ? (int) $agenda_counts->publish : 0,
		'pengumuman'       => isset( $pengumuman_counts->publish ) ? (int) $pengumuman_counts->publish : 0,
		'drafts'           => isset( $post_counts->draft ) ? (int) $post_counts->draft : 0,
		'scheduled'        => isset( $post_counts->future ) ? (int) $post_counts->future : 0,
		'posts_this_month' => $posts_this_month,
		'goal'             => $goal,
		'goal_percentage'  => $goal_percentage,
	];
}

/**
 * Kustomisasi teks kredit footer di wp-admin.
 *
 * @return string
 */
function jalaversity_admin_footer_text(): string {
	return sprintf(
		/* translators: %s: tautan ke Webane Indonesia */
		esc_html__( 'Developed by %s — Jalaversity Campus Theme.', 'jalaversity' ),
		'<a href="https://webane.com" target="_blank" rel="noopener noreferrer">Webane Indonesia</a>'
	);
}
add_filter( 'admin_footer_text', 'jalaversity_admin_footer_text' );
