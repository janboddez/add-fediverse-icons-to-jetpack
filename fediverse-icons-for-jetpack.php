<?php
/**
 * Plugin Name: Fediverse Icons for Jetpack
 * Description: Add fediverse SVG icons to Jetpack's Social Menu module.
 * GitHub Plugin URI: https://github.com/janboddez/fediverse-icons-for-jetpack
 * Author: Jan Boddez
 * Author URI: https://janboddez.tech/
 * License: GNU General Public License v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Version: 0.1
 */

// Prevent this script from being loaded directly.
defined( 'ABSPATH' ) or exit;

/**
 * Main plugin class.
 */
class Fediverse_Icons_Jetpack {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_footer', array( $this, 'print_icons' ), 10000 ); // This prio should have our icons land below Jetpack's.
		add_filter( 'walker_nav_menu_start_el', array( $this, 'apply_icon' ), 100, 4 ); // Important! This prio should make this callback run after Jetpack's.
	}

	/**
	 * Outputs our own social menu icons so they can be used anywhere on the page.
	 */
	public function print_icons() {
		$custom_icons = dirname( __FILE__ ) . '/assets/svg/custom-icons.svg';

		if ( file_exists( $custom_icons ) ) {
			require_once $custom_icons;
		}
	}

	/**
	 * Applies our additional SVG icons to social menu links.
	 */
	public function apply_icon( $item_output, $item, $depth, $args ) {
		// Supported icons/domains. We'll eventually set these up so they work
		// for other instances, too.
		$social_icons = array(
			'mastodon.social' => 'mastodon',
			'pixelfed.social' => 'pixelfed',
		);

		// If the URL in `$item_output` matches any of the sites above, apply
		// the SVG icon.
		if ( 'jetpack-social-menu' === $args->theme_location ) {
			foreach ( $social_icons as $attr => $value ) {
				if ( false !== strpos( $item_output, $attr ) ) {
					$item_output = str_replace(
						$args->link_after,
						'</span>' . jetpack_social_menu_get_svg( array( 'icon' => esc_attr( $value ) ) ),
						$item_output
					);
				}
			}
		}

		// Always return `$item_output`.
		return $item_output;
	}
}

new Fediverse_Icons_Jetpack();
