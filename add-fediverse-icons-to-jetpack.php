<?php
/**
 * Plugin Name: Add Fediverse Icons to Jetpack
 * Description: Add Fediverse SVG icons to Jetpack's Social Menu module.
 * Plugin URI:  https://jan.boddez.net/wordpress/add-fediverse-icons-to-jetpack
 * Author:      Jan Boddez
 * Author URI:  https://jan.boddez.net/
 * License:     GNU General Public License v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Version:     0.5.0
 *
 * @package Fediverse_Icons_Jetpack
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/includes/class-fediverse-icons-jetpack.php';

$fediverse_icons_jetpack = Fediverse_Icons_Jetpack::get_instance();
$fediverse_icons_jetpack->register();
