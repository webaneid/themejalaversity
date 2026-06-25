<?php
/**
 * ACF Flexible Content — Render Bridge
 *
 * Satu fungsi per layout: baca sub-field ACF via get_sub_field(), susun
 * $args, lalu panggil komponen generik yang sama persis dengan yang
 * dipakai page-templates/page-home.php (template-parts/components/*).
 * Pola identik dengan helper jalaversity_get_*_args() di
 * includes/helpers/template-helpers.php — hanya sumber datanya ACF, bukan
 * jalaversity_get_option().
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dispatch satu row flexible content ke render function yang sesuai.
 *
 * @param string $layout Nama layout (get_row_layout()).
 */
function jalaversity_render_dynamic_section( string $layout ): void {
	switch ( $layout ) {
		case 'hero':
			jalaversity_render_acf_hero();
			break;
		case 'stats_bar':
			jalaversity_render_acf_stats_bar();
			break;
		case 'content_media':
			jalaversity_render_acf_content_media();
			break;
		case 'card_grid':
			jalaversity_render_acf_card_grid();
			break;
		case 'numbered_steps':
			jalaversity_render_acf_numbered_steps();
			break;
		case 'cta_banner':
			jalaversity_render_acf_cta_banner();
			break;
		case 'pmb_section':
			jalaversity_render_acf_pmb_section();
			break;
		case 'news_section':
			get_template_part( 'template-parts/content/news-section', null, [
				'label'   => get_sub_field( 'label' )   ?: __( 'Kabar Kampus', 'jalaversity' ),
				'heading' => get_sub_field( 'heading' ) ?: __( 'Berita & Pengumuman', 'jalaversity' ),
			] );
			break;
		case 'sub_nav':
			jalaversity_render_acf_sub_nav();
			break;
		case 'profile_quote':
			jalaversity_render_acf_profile_quote();
			break;
		case 'checklist_cards':
			jalaversity_render_acf_checklist_cards();
			break;
	}
}

/**
 * Konversi sub-field group "link" (label/url/external) jadi array kontrak
 * komponen, atau null kalau url-nya kosong (dianggap tidak diisi admin).
 *
 * @param array|null $link Hasil get_sub_field() untuk field type group link.
 * @return array|null
 */
function jalaversity_acf_link_value( ?array $link ): ?array {
	if ( empty( $link['url'] ) ) {
		return null;
	}

	return [
		'label'    => $link['label'] ?? '',
		'url'      => $link['url'],
		'external' => ! empty( $link['external'] ),
	];
}

/**
 * Layout: Hero
 */
function jalaversity_render_acf_hero(): void {
	$floating_badge = get_sub_field( 'floating_badge' );
	if ( empty( $floating_badge['value'] ) ) {
		$floating_badge = null;
	}

	$buttons = array_map(
		static fn( array $row ): array => [
			'label'    => $row['label'] ?? '',
			'url'      => $row['url'] ?? '',
			'style'    => $row['style'] ?? 'primary',
			'external' => ! empty( $row['external'] ),
		],
		get_sub_field( 'buttons' ) ?: []
	);

	get_template_part( 'template-parts/components/hero-page', null, [
		'variant'         => get_sub_field( 'variant' ),
		'badge'           => get_sub_field( 'badge' ),
		'heading'         => get_sub_field( 'heading' ),
		'highlight'       => get_sub_field( 'highlight' ),
		'lead'            => get_sub_field( 'lead' ),
		'image_id'        => (int) get_sub_field( 'image' ),
		'show_search'     => (bool) get_sub_field( 'show_search' ),
		'trust_items'     => wp_list_pluck( get_sub_field( 'trust_items' ) ?: [], 'text' ),
		'buttons'         => $buttons,
		'floating_badge'  => $floating_badge,
		'show_breadcrumb' => (bool) get_sub_field( 'show_breadcrumb' ),
	] );
}

/**
 * Layout: Stats Bar
 */
function jalaversity_render_acf_stats_bar(): void {
	$items = array_map(
		static fn( array $row ): array => [
			'icon'  => $row['icon'] ?? '',
			'value' => $row['value'] ?? '',
			'label' => $row['label'] ?? '',
		],
		get_sub_field( 'items' ) ?: []
	);
	?>
	<div class="stats-bar-wrap">
		<div class="container">
			<?php get_template_part( 'template-parts/components/stats-bar', null, [ 'items' => $items ] ); ?>
		</div>
	</div>
	<?php
}

/**
 * Layout: Content + Media
 */
function jalaversity_render_acf_content_media(): void {
	$corner_badge = get_sub_field( 'corner_badge' );
	if ( empty( $corner_badge['value'] ) ) {
		$corner_badge = null;
	}

	$items = array_map(
		static fn( array $row ): array => [
			'icon'  => $row['icon'] ?? '',
			'title' => $row['title'] ?? '',
			'desc'  => $row['desc'] ?? '',
		],
		get_sub_field( 'items' ) ?: []
	);

	get_template_part( 'template-parts/components/content-media', null, [
		'label'          => get_sub_field( 'label' ),
		'heading'        => get_sub_field( 'heading' ),
		'body'           => get_sub_field( 'body' ),
		'image_id'       => (int) get_sub_field( 'image' ),
		'image_position' => get_sub_field( 'image_position' ),
		'image_radius'   => get_sub_field( 'image_radius' ),
		'bg'              => get_sub_field( 'bg' ),
		'corner_badge'   => $corner_badge,
		'items'          => $items,
		'items_layout'   => get_sub_field( 'items_layout' ),
		'link'           => jalaversity_acf_link_value( get_sub_field( 'link' ) ),
	] );
}

/**
 * Layout: Card Grid
 *
 * Komponen card-grid.php sendiri tidak membungkus <section>/.container —
 * dipertahankan dari Sesi 07 karena page-home.php membungkusnya manual
 * dengan class background spesifik per section (faculty-section,
 * locations-section). Di Halaman Dinamis kita pakai wrapper generik
 * (section-py saja, tanpa tint background khusus) — kecuali varian `dark`
 * yang dapat tambahan class girih+gradient hijau tua full-bleed (lihat
 * .card-grid-dark-bg di scss/front/_components.scss).
 */
function jalaversity_render_acf_card_grid(): void {
	$dark = (bool) get_sub_field( 'dark' );

	$items = array_map(
		static fn( array $row ): array => [
			'icon'          => $row['icon'] ?? '',
			'image_id'      => (int) ( $row['image'] ?? 0 ),
			'title'         => $row['title'] ?? '',
			'desc'          => $row['desc'] ?? '',
			'address'       => $row['address'] ?? '',
			'code'          => $row['code'] ?? '',
			'badge'         => $row['badge'] ?? '',
			'badge_variant' => $row['badge_variant'] ?? 'green',
			'meta'          => $row['meta'] ?? [],
			'link'          => jalaversity_acf_link_value( $row['link'] ?? null ),
		],
		get_sub_field( 'items' ) ?: []
	);

	$section_class = 'section-py' . ( $dark ? ' card-grid-dark-bg' : '' );
	?>
	<section class="<?php echo esc_attr( $section_class ); ?>" aria-label="<?php echo esc_attr( get_sub_field( 'heading' ) ?: __( 'Kartu', 'jalaversity' ) ); ?>">
		<div class="container">
			<?php get_template_part( 'template-parts/components/card-grid', null, [
				'label'           => get_sub_field( 'label' ),
				'heading'         => get_sub_field( 'heading' ),
				'lead'            => get_sub_field( 'lead' ),
				'center'          => (bool) get_sub_field( 'center' ),
				'min_card_width'  => get_sub_field( 'min_card_width' ),
				'dark'            => $dark,
				'items'           => $items,
			] ); ?>
		</div>
	</section>
	<?php
}

/**
 * Layout: Numbered Steps (standalone — lihat juga pmb_section yang punya
 * pemanggilan numbered-steps.php sendiri tanpa heading).
 */
function jalaversity_render_acf_numbered_steps(): void {
	$items = array_map(
		static fn( array $row ): array => [
			'number' => $row['number'] ?? '',
			'title'  => $row['title'] ?? '',
			'desc'   => $row['desc'] ?? '',
		],
		get_sub_field( 'items' ) ?: []
	);
	?>
	<section class="section-py" aria-label="<?php echo esc_attr( get_sub_field( 'heading' ) ?: __( 'Langkah-langkah', 'jalaversity' ) ); ?>">
		<div class="container">
			<?php get_template_part( 'template-parts/components/numbered-steps', null, [
				'label'   => get_sub_field( 'label' ),
				'heading' => get_sub_field( 'heading' ),
				'lead'    => get_sub_field( 'lead' ),
				'variant' => get_sub_field( 'variant' ),
				'items'   => $items,
			] ); ?>
		</div>
	</section>
	<?php
}

/**
 * Layout: CTA Banner
 */
function jalaversity_render_acf_cta_banner(): void {
	$args = [];

	$heading = get_sub_field( 'heading' );
	if ( $heading ) {
		$args['heading'] = $heading;
	}

	$body = get_sub_field( 'body' );
	if ( $body ) {
		$args['body'] = $body;
	}

	$btn_primary = jalaversity_acf_link_value( get_sub_field( 'btn_primary' ) );
	if ( $btn_primary ) {
		$args['btn_primary'] = $btn_primary;
	}

	$btn_ghost = jalaversity_acf_link_value( get_sub_field( 'btn_ghost' ) );
	if ( $btn_ghost ) {
		$args['btn_ghost'] = $btn_ghost;
	}
	?>
	<section class="cta-section">
		<div class="container">
			<?php get_template_part( 'template-parts/components/cta-banner', null, $args ); ?>
		</div>
	</section>
	<?php
}

/**
 * Layout: PMB Section
 */
function jalaversity_render_acf_pmb_section(): void {
	$steps = array_map(
		static fn( array $row ): array => [
			'number' => $row['number'] ?? '',
			'title'  => $row['title'] ?? '',
			'desc'   => $row['desc'] ?? '',
		],
		get_sub_field( 'steps' ) ?: []
	);

	get_template_part( 'template-parts/content/pmb-section', null, [
		'wave_label'   => get_sub_field( 'wave_label' ),
		'heading'      => get_sub_field( 'heading' ),
		'body'         => get_sub_field( 'body' ),
		'cta_label'    => get_sub_field( 'cta_label' ),
		'cta_url'      => get_sub_field( 'cta_url' ),
		'brochure_url' => get_sub_field( 'brochure_url' ),
		'steps'        => $steps,
	] );
}

/**
 * Layout: Sub Nav
 */
function jalaversity_render_acf_sub_nav(): void {
	$items = array_map(
		static fn( array $row ): array => [
			'label' => $row['label'] ?? '',
			'href'  => $row['href'] ?? '',
		],
		get_sub_field( 'items' ) ?: []
	);

	get_template_part( 'template-parts/components/sub-nav', null, [
		'items' => $items,
	] );
}

/**
 * Layout: Profile + Quote
 */
function jalaversity_render_acf_profile_quote(): void {
	get_template_part( 'template-parts/components/profile-quote', null, [
		'image_id' => (int) get_sub_field( 'image' ),
		'name'     => get_sub_field( 'name' ),
		'title'    => get_sub_field( 'title' ),
		'label'    => get_sub_field( 'label' ),
		'heading'  => get_sub_field( 'heading' ),
		'quote'    => get_sub_field( 'quote' ),
		'body'     => get_sub_field( 'body' ),
	] );
}

/**
 * Layout: Checklist + Cards
 */
function jalaversity_render_acf_checklist_cards(): void {
	$checklist = array_map(
		static fn( array $row ): array => [
			'icon' => $row['icon'] ?? '',
			'text' => $row['text'] ?? '',
		],
		get_sub_field( 'checklist' ) ?: []
	);

	$cards = array_map(
		static fn( array $row ): array => [
			'icon'  => $row['icon'] ?? '',
			'title' => $row['title'] ?? '',
			'desc'  => $row['desc'] ?? '',
		],
		get_sub_field( 'cards' ) ?: []
	);

	get_template_part( 'template-parts/components/checklist-cards', null, [
		'label'         => get_sub_field( 'label' ),
		'heading'       => get_sub_field( 'heading' ),
		'checklist'     => $checklist,
		'cards_heading' => get_sub_field( 'cards_heading' ),
		'cards'         => $cards,
	] );
}
