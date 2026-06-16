<?php
/**
 * Custom Navigation Walker
 *
 * Generates accessible dropdown navigation HTML for primary menu.
 * Supports:
 *   - Submenu detection via has-submenu class + ARIA
 *   - Toggle button for each submenu (keyboard + touch accessible)
 *   - Current/active page highlighting
 *   - Two depth levels (menu → submenu)
 *
 * Usage via wp_nav_menu():
 *   wp_nav_menu([
 *     'theme_location' => 'primary',
 *     'walker'         => new Jalaversity_Nav_Walker(),
 *     'container'      => false,
 *     'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
 *   ]);
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Jalaversity_Nav_Walker
 */
class Jalaversity_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Open a level — outputs <ul> for submenu.
	 *
	 * @param string   $output Passed by reference.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n{$indent}<ul class=\"sub-menu\" role=\"list\" aria-hidden=\"true\">\n";
	}

	/**
	 * Close a level.
	 *
	 * @param string   $output Passed by reference.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Menu args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$indent  = str_repeat( "\t", $depth );
		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Open a menu item — outputs <li> with appropriate classes.
	 *
	 * @param string   $output Passed by reference.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu args.
	 * @param int      $id     Menu item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		$indent = $depth ? str_repeat( "\t", $depth ) : '';

		/* ── CSS classes ──────────────────────────────────────────── */
		$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[] = 'nav-item';
		$classes[] = 'menu-item-' . $item->ID;

		$has_children = in_array( 'menu-item-has-children', $classes, true );

		if ( $has_children ) {
			$classes[] = 'has-submenu';
		}

		$is_current = in_array( 'current-menu-item', $classes, true )
			|| in_array( 'current-menu-ancestor', $classes, true )
			|| in_array( 'current-menu-parent', $classes, true );

		if ( $is_current ) {
			$classes[] = 'is-active';
		}

		// Remove default WP classes that we replace with our own.
		$classes = array_filter( $classes, static fn( string $c ): bool =>
			! in_array( $c, [ 'menu-item-has-children', 'page_item', 'page-item-has-children' ], true )
		);

		$class_str = implode( ' ', array_unique( array_filter( $classes ) ) );

		$output .= "{$indent}<li class=\"" . esc_attr( $class_str ) . "\"";
		$output .= " id=\"menu-item-{$item->ID}\">\n";

		/* ── Link ─────────────────────────────────────────────────── */
		$atts           = [];
		$atts['href']   = ! empty( $item->url ) ? $item->url : '#';
		$atts['class']  = $depth > 0 ? 'nav-link nav-link--sub' : 'nav-link';

		if ( $is_current ) {
			$atts['aria-current'] = 'page';
		}

		if ( ! empty( $item->target ) ) {
			$atts['target'] = esc_attr( $item->target );
		}

		if ( '_blank' === ( $item->target ?? '' ) ) {
			$atts['rel'] = 'noopener noreferrer';
		}

		if ( ! empty( $item->xfn ) ) {
			$atts['rel'] = esc_attr( $item->xfn );
		}

		if ( ! empty( $item->attr_title ) ) {
			$atts['title'] = esc_attr( $item->attr_title );
		}

		$atts_str = '';
		foreach ( $atts as $attr => $val ) {
			$atts_str .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $val ) . '"';
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$output .= "{$indent}\t<a{$atts_str}>" . esc_html( $title ) . '</a>' . "\n";

		/* ── Submenu toggle button (depth 0 only, when has children) ── */
		if ( $has_children && 0 === $depth ) {
			$chevron = jalaversity_icon( 'chevron-down', 16, 'submenu-chevron' );
			$label   = sprintf(
				/* translators: %s: menu item title */
				esc_attr__( 'Open submenu of %s', 'jalaversity' ),
				esc_attr( $title )
			);

			$expanded = $is_current ? 'true' : 'false';

			$output .= "{$indent}\t<button ";
			$output .= 'class="submenu-toggle" ';
			$output .= 'aria-expanded="' . $expanded . '" ';
			$output .= 'aria-label="' . $label . '" ';
			$output .= 'type="button">';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$output .= $chevron;
			$output .= "</button>\n";
		}
	}

	/**
	 * Close a menu item.
	 *
	 * @param string   $output Passed by reference.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Menu args.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ): void {
		$indent  = $depth ? str_repeat( "\t", $depth ) : '';
		$output .= "{$indent}</li>\n";
	}
}
