<?php
/**
 * Plugin Name:       Shipping plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Nikita Polikarpov
 * Author URI:        https://github.com/hereWasKitus
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       shipping-plugin
 * Domain Path:       /languages
 */
require_once __DIR__ . '/includes/Shipping_Plugin.php';
require_once __DIR__ . '/woocommerce/Woocommerce_Settings.php';
require_once __DIR__ . '/includes/Ajax_Handler.php';

define( 'PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_SLUG', 'shipping-plugin' );

$instance = new Shipping_Plugin();

if ( in_array( trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php', wp_get_active_and_valid_plugins() ) ) {
  $wcsettings = new SP\Woocommerce\Woocommerce_Settings();
}