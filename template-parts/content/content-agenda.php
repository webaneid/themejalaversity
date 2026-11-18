<?php
/**
 * Content — Single Agenda (CPT: agenda)
 *
 * Menampilkan detail lengkap kegiatan kampus dari field ACF
 * `group_jalaversity_agenda` (tanggal_mulai, tanggal_selesai, jam_mulai,
 * jam_selesai, tipe_lokasi, lokasi, link_online, narasumber, penyelenggara,
 * link_pendaftaran, kuota) beserta isi artikel (.entry-content) dan sidebar.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tgl_mulai        = (string) get_field( 'tanggal_mulai' );
$tgl_selesai      = (string) get_field( 'tanggal_selesai' );
$jam_mulai        = (string) get_field( 'jam_mulai' );
$jam_selesai      = (string) get_field( 'jam_selesai' );
$tipe_lokasi      = (string) ( get_field( 'tipe_lokasi' ) ?: 'offline' );
$lokasi           = (string) get_field( 'lokasi' );
$link_online      = (string) get_field( 'link_online' );
$penyelenggara    = (string) get_field( 'penyelenggara' );
$link_pendaftaran = (string) get_field( 'link_pendaftaran' );
$kuota            = absint( get_field( 'kuota' ) );
$narasumber_raw   = get_field( 'narasumber' );
$narasumber       = is_array( $narasumber_raw ) ? $narasumber_raw : [];

$format_date = static function ( string $ymd ): string {
	if ( '' === $ymd ) {
		return '';
	}
	$ts = strtotime( $ymd );
	return $ts ? wp_date( 'l, j F Y', $ts ) : $ymd;
};

$tanggal_label = $format_date( $tgl_mulai );
if ( $tgl_selesai && $tgl_selesai !== $tgl_mulai ) {
	$tanggal_label .= ' – ' . $format_date( $tgl_selesai );
}

$waktu_label = $jam_mulai;
if ( $jam_selesai ) {
	$waktu_label .= ' – ' . $jam_selesai;
}
if ( $waktu_label ) {
	$waktu_label .= ' WIB';
}

$tipe_labels = [
	'offline' => __( 'Offline (Tatap Muka)', 'jalaversity' ),
	'online'  => __( 'Online (Daring)', 'jalaversity' ),
	'hybrid'  => __( 'Hybrid (Offline + Online)', 'jalaversity' ),
];
$tipe_label  = $tipe_labels[ $tipe_lokasi ] ?? $tipe_labels['offline'];
?>
<article <?php post_class( 'single-post single-agenda' ); ?>>

	<div class="container content-with-sidebar">
		<div class="content-with-sidebar__main single-post__container">

			<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

			<header class="single-post__header">
				<div class="post-meta-line">
					<a href="<?php echo esc_url( (string) get_post_type_archive_link( 'agenda' ) ); ?>" class="post-meta-line__category">
						<?php esc_html_e( 'Agenda Kampus', 'jalaversity' ); ?>
					</a>
					<?php if ( $tanggal_label ) : ?>
						<span class="post-meta-line__sep" aria-hidden="true">-</span>
						<span class="post-meta-line__date"><?php echo esc_html( $tanggal_label ); ?></span>
					<?php endif; ?>
				</div>

				<h1 class="single-post__title text-hero-sub"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-post__image">
				<?php
				the_post_thumbnail(
					'jalaversity-medium',
					[
						'class'         => 'single-post__img',
						'loading'       => false,
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(max-width: 767px) 92vw, 800px',
					]
				);
				?>
			</div>
			<?php endif; ?>

			<!-- ── Kartu Detail Agenda ────────────────────────────────────── -->
			<section class="agenda-detail-card" aria-label="<?php esc_attr_e( 'Informasi Pelaksanaan Agenda', 'jalaversity' ); ?>">
				<div class="agenda-detail-card__header">
					<span class="badge"><?php echo esc_html( $tipe_label ); ?></span>
					<?php if ( $kuota > 0 ) : ?>
						<span class="agenda-detail-card__quota">
							<?php jalaversity_icon_e( 'users', 16 ); ?>
							<?php
							printf(
								/* translators: %d: kuota peserta */
								esc_html__( 'Kuota: %d peserta', 'jalaversity' ),
								(int) $kuota
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<dl class="agenda-detail-card__grid">
					<?php if ( $tanggal_label ) : ?>
					<div class="agenda-detail-card__item">
						<dt>
							<?php jalaversity_icon_e( 'calendar', 18 ); ?>
							<?php esc_html_e( 'Tanggal', 'jalaversity' ); ?>
						</dt>
						<dd><?php echo esc_html( $tanggal_label ); ?></dd>
					</div>
					<?php endif; ?>

					<?php if ( $waktu_label ) : ?>
					<div class="agenda-detail-card__item">
						<dt>
							<?php jalaversity_icon_e( 'clock', 18 ); ?>
							<?php esc_html_e( 'Waktu', 'jalaversity' ); ?>
						</dt>
						<dd><?php echo esc_html( $waktu_label ); ?></dd>
					</div>
					<?php endif; ?>

					<?php if ( $lokasi && 'online' !== $tipe_lokasi ) : ?>
					<div class="agenda-detail-card__item">
						<dt>
							<?php jalaversity_icon_e( 'map-pin', 18 ); ?>
							<?php esc_html_e( 'Tempat / Gedung', 'jalaversity' ); ?>
						</dt>
						<dd><?php echo esc_html( $lokasi ); ?></dd>
					</div>
					<?php endif; ?>

					<?php if ( $link_online && 'offline' !== $tipe_lokasi ) : ?>
					<div class="agenda-detail-card__item">
						<dt>
							<?php jalaversity_icon_e( 'globe', 18 ); ?>
							<?php esc_html_e( 'Tautan Daring', 'jalaversity' ); ?>
						</dt>
						<dd>
							<a href="<?php echo esc_url( $link_online ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Buka Ruang Meeting / Live Stream', 'jalaversity' ); ?>
								<?php jalaversity_icon_e( 'external-link', 14 ); ?>
							</a>
						</dd>
					</div>
					<?php endif; ?>

					<?php if ( $penyelenggara ) : ?>
					<div class="agenda-detail-card__item">
						<dt>
							<?php jalaversity_icon_e( 'building-office', 18 ); ?>
							<?php esc_html_e( 'Penyelenggara', 'jalaversity' ); ?>
						</dt>
						<dd><?php echo esc_html( $penyelenggara ); ?></dd>
					</div>
					<?php endif; ?>
				</dl>

				<?php if ( $link_pendaftaran ) : ?>
				<div class="agenda-detail-card__cta">
					<a href="<?php echo esc_url( $link_pendaftaran ); ?>" class="btn btn--pmb" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Daftar Agenda Sekarang', 'jalaversity' ); ?>
						<?php jalaversity_icon_e( 'arrow-right', 17 ); ?>
					</a>
				</div>
				<?php endif; ?>
			</section>

			<?php if ( $narasumber ) : ?>
			<section class="agenda-speakers" aria-label="<?php esc_attr_e( 'Narasumber', 'jalaversity' ); ?>">
				<h2 class="agenda-speakers__title"><?php esc_html_e( 'Narasumber & Pembicara', 'jalaversity' ); ?></h2>
				<div class="agenda-speakers__list">
					<?php
					foreach ( $narasumber as $speaker ) :
						if ( ! is_array( $speaker ) || empty( $speaker['nama'] ) ) {
							continue;
						}
						$spk_nama    = (string) $speaker['nama'];
						$spk_jabatan = (string) ( $speaker['jabatan'] ?? '' );
						?>
						<div class="agenda-speaker-item">
							<div class="agenda-speaker-item__icon" aria-hidden="true">
								<?php jalaversity_icon_e( 'user', 20 ); ?>
							</div>
							<div class="agenda-speaker-item__body">
								<strong class="agenda-speaker-item__name"><?php echo esc_html( $spk_nama ); ?></strong>
								<?php if ( $spk_jabatan ) : ?>
									<span class="agenda-speaker-item__role"><?php echo esc_html( $spk_jabatan ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>

			<div class="entry-content">
				<?php the_content(); ?>
			</div>

			<?php get_template_part( 'template-parts/components/share-buttons' ); ?>

			<?php get_template_part( 'template-parts/components/post-nav' ); ?>

		</div>
		<?php get_sidebar(); ?>
	</div>

</article>
