<?php
/**
 * Plugin Name: Fediverse Icons for Jetpack
 * Description: Add Fediverse SVG icons to Jetpack's Social Menu module.
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
	 *
	 * @link https://github.com/Automattic/jetpack/blob/3bf755fd92fd52a6296551aa7bdf1a95c9271752/modules/theme-tools/social-menu/icon-functions.php#L89 Jetpack's 'original' at the time of writing.
	 *
	 * @param string $item_output Menu item output.
	 * @param WP_Post $item Menu item object.
	 * @param int $depth Menu depth.
	 * @param array $args wp_nav_menu() arguments.
	 * @return string Modified menu item output.
	 */
	public function apply_icon( $item_output, $item, $depth, $args ) {
		// Supported icons/domains. We'll eventually set these up so they work
		// for other instances, too.
		$social_icons = array(
			'diaspora' => 'diaspora',
			'friendica' => 'friendica',
			'gnu social' => 'gnu-social',
			'mastodon' => 'mastodon',
			'peertube' => 'peertube',
			'pixelfed' => 'pixelfed',
		);

		// If the URL in `$item_output` matches any of the sites above, apply
		// the SVG icon. For this to work, the menu item must actually be named
		// after the platform, as we can't deduce anything from a domain name
		// anymore (an instance's domain could be just about anything)!
		if ( 'jetpack-social-menu' === $args->theme_location ) {
			foreach ( $social_icons as $attr => $value ) {
				if ( false !== stripos( $item_output, $attr ) ) {
					$item_output = str_ireplace(
						$args->link_after,
						'</span>' . jetpack_social_menu_get_svg( array( 'icon' => esc_attr( $value ) ) ),
						$item_output
					);
				}
			}
		}

		// Always return `$item_output`!
		return $item_output;
	}
}

new Fediverse_Icons_Jetpack();
