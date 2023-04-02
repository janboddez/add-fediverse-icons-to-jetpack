<?php
/**
 * Main plugin class.
 *
 * @package Fediverse_Icons_Jetpack
 */

/**
 * Main plugin class.
 */
class Fediverse_Icons_Jetpack {
	/**
	 * Supported platforms.
	 *
	 * @var array $social_icons Supported platforms.
	 */
	public static $social_icons = array(
		// Using `fedicons-` prefix to prevent duplicate IDs now that Jetpack
		// also has `icon-mastodon`.
		'Diaspora'   => 'fedicons-diaspora',
		'Friendica'  => 'fedicons-friendica',
		'GNU Social' => 'fedicons-gnu-social',
		'Mastodon'   => 'fedicons-mastodon',
		'PeerTube'   => 'fedicons-peertube',
		'Pixelfed'   => 'fedicons-pixelfed',
	);

	/**
	 * This plugin's single instance.
	 *
	 * @var Fediverse_Icons_Jetpack $instance Plugin instance.
	 */
	private static $instance;

	/**
	 * Returns the single instance of this class.
	 *
	 * @return Fediverse_Icons_Jetpack Single class instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers hook callbacks.
	 */
	public function register() {
		add_action( 'wp_footer', array( $this, 'print_icons' ), 10000 ); // This prio should have our icons land below Jetpack's.
		add_filter( 'walker_nav_menu_start_el', array( $this, 'apply_icon' ), 100, 4 ); // Important! This prio should make this callback run after Jetpack's.
	}

	/**
	 * Outputs our own social menu icons so they can be used anywhere on the page.
	 */
	public function print_icons() {
		$custom_icons = dirname( __DIR__ ) . '/assets/svg/custom-icons.svg';

		if ( is_readable( $custom_icons ) ) {
			require_once $custom_icons;
		}
	}

	/**
	 * Applies our additional SVG icons to social menu links.
	 *
	 * @link https://github.com/Automattic/jetpack/blob/3bf755fd92fd52a6296551aa7bdf1a95c9271752/modules/theme-tools/social-menu/icon-functions.php#L89 Jetpack's 'original' at the time of writing.
	 *
	 * @param string         $item_output Menu item output.
	 * @param WP_Post        $item        Menu item object.
	 * @param int            $depth       Menu depth.
	 * @param array|stdClass $args        `wp_nav_menu()` arguments.
	 *
	 * @return string Modified menu item output.
	 */
	public function apply_icon( $item_output, $item, $depth, $args ) {
		if ( ! function_exists( 'jetpack_social_menu_get_svg' ) ) {
			return $item_output;
		}

		// If the URL in `$item_output` matches any of the sites above, apply
		// the SVG icon. For this to work, the menu item must be named after the
		// platform. We can't deduce anything from a domain name, like Jetpack
		// does (an instance's domain could be just about anything)!
		if ( 'jetpack-social-menu' === $args->theme_location ) {
			foreach ( static::$social_icons as $attr => $value ) {
				if ( false !== stripos( $item_output, $attr ) ) {
					$item_output = str_ireplace(
						$args->link_after,
						'</span>' . jetpack_social_menu_get_svg( array( 'icon' => esc_attr( $value ) ) ),
						$item_output
					);
				}
			}
		}

		return $item_output;
	}
}
