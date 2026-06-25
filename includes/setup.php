<?php
/**
 * Theme Setup
 *
 * Registers theme supports, navigation menus, image sizes, and widget areas.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup — runs after theme is loaded.
 */
function jalaversity_setup(): void {

	// Enable translations.
	load_theme_textdomain( 'jalaversity', JALAVERSITY_DIR . '/languages' );

	// WordPress manages <title> tag automatically.
	add_theme_support( 'title-tag' );

	// Enable featured images on posts and pages.
	add_theme_support( 'post-thumbnails' );

	// RSS feed links in <head>.
	add_theme_support( 'automatic-feed-links' );

	// Valid HTML5 markup for core output.
	add_theme_support(
		'html5',
		[
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		]
	);

	// Custom logo — flexible dimensions, set min in CSS.
	add_theme_support(
		'custom-logo',
		[
			'height'      => 80,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
			'header-text' => [ 'site-title', 'site-description' ],
		]
	);

	// Block editor color palette — mirrors design system tokens.
	add_theme_support(
		'editor-color-palette',
		[
			[
				'name'  => __( 'Primary Green', 'jalaversity' ),
				'slug'  => 'primary-green',
				'color' => '#08422e',
			],
			[
				'name'  => __( 'Primary Dark', 'jalaversity' ),
				'slug'  => 'primary-dark',
				'color' => '#06301f',
			],
			[
				'name'  => __( 'Gold', 'jalaversity' ),
				'slug'  => 'gold',
				'color' => '#b68c2e',
			],
			[
				'name'  => __( 'Gold Light', 'jalaversity' ),
				'slug'  => 'gold-light',
				'color' => '#e9c970',
			],
			[
				'name'  => __( 'Background Cream', 'jalaversity' ),
				'slug'  => 'bg-cream',
				'color' => '#f8f5ec',
			],
			[
				'name'  => __( 'Text Primary', 'jalaversity' ),
				'slug'  => 'text-primary',
				'color' => '#1c2b24',
			],
		]
	);

	// Disable core block patterns (we define our own).
	remove_theme_support( 'core-block-patterns' );

	// Custom image sizes — 16:9 "golden ratio" untuk large/medium/thumbnail,
	// + square 1:1 untuk crop kotak. Param ke-4 `true` = hard-crop tengah
	// ke rasio pas (bukan cuma scale) — lihat docs/02-architecture.md §10.
	add_image_size( 'jalaversity-large',     1120, 630, true ); // 16:9
	add_image_size( 'jalaversity-medium',     800, 450, true ); // 16:9
	add_image_size( 'jalaversity-thumbnail',  400, 225, true ); // 16:9
	add_image_size( 'jalaversity-square',     400, 400, true ); // 1:1

	// Navigation menu locations.
	register_nav_menus(
		[
			'primary'        => __( 'Primary Navigation', 'jalaversity' ),
			'topbar'         => __( 'Top Bar Menu', 'jalaversity' ),
			'footer-about'   => __( 'Footer: Tentang', 'jalaversity' ),
			'footer-akademik'=> __( 'Footer: Akademik', 'jalaversity' ),
			'footer-layanan' => __( 'Footer: Layanan', 'jalaversity' ),
			'social'         => __( 'Social Links', 'jalaversity' ),
		]
	);
}
add_action( 'after_setup_theme', 'jalaversity_setup' );

/**
 * Set the maximum content width in pixels.
 */
function jalaversity_content_width(): void {
	$GLOBALS['content_width'] = apply_filters( 'jalaversity_content_width', 780 );
}
add_action( 'after_setup_theme', 'jalaversity_content_width', 0 );

/**
 * Register widget areas.
 */
function jalaversity_widgets_init(): void {

	register_sidebar(
		[
			'name'          => __( 'Sidebar', 'jalaversity' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in the sidebar.', 'jalaversity' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		]
	);

	register_sidebar(
		[
			'name'          => __( 'Footer Area 1', 'jalaversity' ),
			'id'            => 'footer-1',
			'description'   => __( 'Footer column 1.', 'jalaversity' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		]
	);

	register_sidebar(
		[
			'name'          => __( 'Footer Area 2', 'jalaversity' ),
			'id'            => 'footer-2',
			'description'   => __( 'Footer column 2.', 'jalaversity' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		]
	);
}
add_action( 'widgets_init', 'jalaversity_widgets_init' );

/**
 * Register custom post types.
 */
function jalaversity_register_post_types(): void {

	register_post_type(
		'pengumuman',
		[
			'labels'        => [
				'name'               => __( 'Pengumuman', 'jalaversity' ),
				'singular_name'      => __( 'Pengumuman', 'jalaversity' ),
				'add_new_item'       => __( 'Tambah Pengumuman', 'jalaversity' ),
				'edit_item'          => __( 'Edit Pengumuman', 'jalaversity' ),
				'new_item'           => __( 'Pengumuman Baru', 'jalaversity' ),
				'view_item'          => __( 'Lihat Pengumuman', 'jalaversity' ),
				'search_items'       => __( 'Cari Pengumuman', 'jalaversity' ),
				'not_found'          => __( 'Pengumuman tidak ditemukan.', 'jalaversity' ),
				'not_found_in_trash' => __( 'Pengumuman tidak ditemukan di sampah.', 'jalaversity' ),
				'menu_name'          => __( 'Pengumuman', 'jalaversity' ),
			],
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => [ 'slug' => 'pengumuman' ],
			'show_in_rest'  => false,
			'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
			'menu_icon'     => 'dashicons-megaphone',
			'menu_position' => 6,
		]
	);

	register_post_type(
		'agenda',
		[
			'labels'        => [
				'name'               => __( 'Agenda', 'jalaversity' ),
				'singular_name'      => __( 'Agenda', 'jalaversity' ),
				'add_new_item'       => __( 'Tambah Agenda', 'jalaversity' ),
				'edit_item'          => __( 'Edit Agenda', 'jalaversity' ),
				'new_item'           => __( 'Agenda Baru', 'jalaversity' ),
				'view_item'          => __( 'Lihat Agenda', 'jalaversity' ),
				'search_items'       => __( 'Cari Agenda', 'jalaversity' ),
				'not_found'          => __( 'Agenda tidak ditemukan.', 'jalaversity' ),
				'not_found_in_trash' => __( 'Agenda tidak ditemukan di sampah.', 'jalaversity' ),
				'menu_name'          => __( 'Agenda', 'jalaversity' ),
			],
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => [ 'slug' => 'agenda' ],
			'show_in_rest'  => false,
			'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 5,
		]
	);
}
add_action( 'init', 'jalaversity_register_post_types' );
