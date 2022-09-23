<?php
/**
 * Plugin Name:       Add Fediverse Icons to Jetpack
 * Description:       Add Fediverse SVG icons to Jetpack's Social Menu module.
 * Plugin URI:        https://janboddez.tech/wordpress/add-fediverse-icons-to-jetpack
 * Author:            Jan Boddez
 * Author URI:        https://janboddez.tech/
 * License:           GNU General Public License v3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Version:           0.3.3
 *
 * @package Fediverse_Icons_Jetpack
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class Fediverse_Icons_Jetpack {
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
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'wp_footer', array( $this, 'print_icons' ), 10000 ); // This prio should have our icons land below Jetpack's.
		add_filter( 'walker_nav_menu_start_el', array( $this, 'apply_icon' ), 100, 4 ); // Important! This prio should make this callback run after Jetpack's.
		add_action( 'init', array( $this, 'handle_block_themes' ), 9 );
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
	 * @param string  $item_output Menu item output.
	 * @param WP_Post $item        Menu item object.
	 * @param int     $depth       Menu depth.
	 * @param array   $args        wp_nav_menu() arguments.
	 *
	 * @return string Modified menu item output.
	 */
	public function apply_icon( $item_output, $item, $depth, $args ) {
		if ( ! function_exists( 'jetpack_social_menu_get_svg' ) ) {
			if ( ! is_file( WP_CONTENT_DIR . '/plugins/jetpack/modules/theme-tools/social-menu/icon-functions.php' ) ) {
				return $item_output;
			}

			include WP_CONTENT_DIR . '/plugins/jetpack/modules/theme-tools/social-menu/icon-functions.php';

			if ( ! function_exists( 'jetpack_social_menu_get_svg' ) ) {
				return $item_output;
			}
		}

		// Supported platforms.
		$social_icons = array(
			'Diaspora'   => 'diaspora',
			'Friendica'  => 'friendica',
			'GNU Social' => 'gnu-social',
			'Mastodon'   => 'mastodon',
			'PeerTube'   => 'peertube',
			'Pixelfed'   => 'pixelfed',
		);

		// If the URL in `$item_output` matches any of the sites above, apply
		// the SVG icon. For this to work, the menu item must be named after the
		// platform. We can't deduce anything from a domain name, like Jetpack
		// does (an instance's domain could be just about anything)!
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

		return $item_output;
	}


	/**
	 * Overrides WordPress' `core/social-link` render callback.
	 */
	public function handle_block_themes() {
		remove_action( 'init', 'register_block_core_social_link' );

		add_action( 'init', function() {
			remove_action( 'init', 'gutenberg_register_block_core_social_link', 20 );
		} );

		add_action( 'init', function() {
			register_block_type_from_metadata(
				ABSPATH . WPINC . '/blocks/social-link',
				array(
					'render_callback' => array( $this, 'render_block_core_social_link' ),
				)
			);
		} );
	}

	/**
	 * Pretty much lifted from WP core. It'd be nice if we had a simple PHP
	 * filter for this type of thing.
	 *
	 * @todo: Look into block variations?
	 */
	public function render_block_core_social_link( $attributes, $content, $block ) {
		$open_in_new_tab = isset( $block->context['openInNewTab'] ) ? $block->context['openInNewTab'] : false;

		$service     = ( isset( $attributes['service'] ) ) ? $attributes['service'] : 'Icon';
		$url         = ( isset( $attributes['url'] ) ) ? $attributes['url'] : false;
		$label       = ( isset( $attributes['label'] ) ) ? $attributes['label'] : block_core_social_link_get_name( $service );
		$show_labels = array_key_exists( 'showLabels', $block->context ) ? $block->context['showLabels'] : false;

		// Don't render a link if there is no URL set.
		if ( ! $url ) {
			return '';
		}

		/**
		 * Prepend emails with `mailto:` if not set.
		 * The `is_email` returns false for emails with schema.
		 */
		if ( is_email( $url ) ) {
			$url = 'mailto:' . $url;
		}

		/**
		 * Prepend URL with https:// if it doesn't appear to contain a scheme
		 * and it's not a relative link starting with //.
		 */
		if ( ! parse_url( $url, PHP_URL_SCHEME ) && ! str_starts_with( $url, '//' ) ) {
			$url = 'https://' . $url;
		}

		$rel_target_attributes = '';
		if ( $open_in_new_tab ) {
			$rel_target_attributes = 'rel="noopener nofollow" target="_blank"';
		}

		$icon = block_core_social_link_get_icon( $service );

		// Hardcoded, for now.
		// @todo: Add the other platforms. Run this only when a block theme's active?
		if ( 'Mastodon' === $label ) {
			$icon = '<svg class="icon icon-mastodon" aria-hidden="true" role="img"> <use href="#icon-mastodon" xlink:href="#icon-mastodon"></use> </svg>';
		} elseif ( 'Pixelfed' === $label ) {
			$icon = '<svg class="icon icon-pixelfed" aria-hidden="true" role="img"> <use href="#icon-pixelfed" xlink:href="#icon-pixelfed"></use> </svg>';
		}

		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'wp-social-link wp-social-link-' . $service,
				'style' => block_core_social_link_get_color_styles( $block->context ),
			)
		);

		$link  = '<li ' . $wrapper_attributes . '>';
		$link .= '<a href="' . esc_url( $url ) . '" ' . $rel_target_attributes . ' class="wp-block-social-link-anchor">';
		$link .= $icon;
		$link .= '<span class="wp-block-social-link-label' . ( $show_labels ? '' : ' screen-reader-text' ) . '">';
		$link .= esc_html( $label );
		$link .= '</span></a></li>';

		return $link;
	}
}

Fediverse_Icons_Jetpack::get_instance();
