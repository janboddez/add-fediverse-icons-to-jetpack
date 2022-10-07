<?php
/**
 * Handles block themes.
 *
 * @package Fediverse_Icons_Jetpack
 */

namespace Fediverse_Icons_Jetpack;

/**
 * Handles block themes by overriding WordPress' `core/social-link` render
 * callback.
 */
class Block_Themes {
	/**
	 * Overrides WordPress' `core/social-link` render callback.
	 */
	public static function register() {
		// Deregister the `core/social-link` block.
		remove_action( 'init', 'register_block_core_social_link' );

		// Deregister its Gutenberg variant.
		add_action( 'init', array( __CLASS__, 'gutenberg_deregister_block_core_social_link' ) );

		// Reregister with our custom render callback.
		add_action( 'init', array( __CLASS__, 'register_block_core_social_link' ) );
	}

	/**
	 * Add a custom render callback to  WordPress' `core/social-link` block.
	 */
	public static function register_block_core_social_link() {
		register_block_type_from_metadata(
			ABSPATH . WPINC . '/blocks/social-link',
			array(
				'render_callback' => array( __CLASS__, 'render_block_core_social_link' ),
			)
		);
	}

	/**
	 * Stop Gutenberg from registering  WordPress' `core/social-link` block.
	 */
	public static function gutenberg_deregister_block_core_social_link() {
		remove_action( 'init', 'gutenberg_register_block_core_social_link', 20 );
	}

	/**
	 * Pretty much lifted from WP core. It'd be nice if we had a simple PHP
	 * filter for this type of thing.
	 *
	 * @todo: Look into block variations?
	 *
	 * @param array    $attributes The block attributes.
	 * @param string   $content    InnerBlocks content of the Block.
	 * @param WP_Block $block      Block object.
	 *
	 * @return string Rendered block HTML.
	 */
	public static function render_block_core_social_link( $attributes, $content, $block ) {
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
		if ( ! wp_parse_url( $url, PHP_URL_SCHEME ) && ! str_starts_with( $url, '//' ) ) {
			$url = 'https://' . $url;
		}

		$rel_target_attributes = '';
		if ( $open_in_new_tab ) {
			$rel_target_attributes = 'rel="noopener nofollow" target="_blank"';
		}

		$icon = block_core_social_link_get_icon( $service );

		foreach ( \Fediverse_Icons_Jetpack::$social_icons as $attr => $value ) {
			if ( $attr === $label ) {
				// Override the default SVG icon.
				$icon = '<svg class="icon icon-' . $value . '" aria-hidden="true" role="img"> <use href="#icon-' . $value . '" xlink:href="#icon-' . $value . '"></use> </svg>';
			}
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
