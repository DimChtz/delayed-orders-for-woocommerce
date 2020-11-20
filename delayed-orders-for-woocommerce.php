<?php

/**
 * @link 			  #
 * @since 			  1.0.0
 * @package 		  Delayed_Orders_For_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name: 	  Delayed Orders for WooCommerce
 * Plugin URI: 		  https://github.com/DimChtz/delayed-orders-for-woocommerce
 * Description: 	  Delayed orders management for WooCommerce
 * Version: 		  1.0.0
 * Author: 			  Dimitris Chatzis
 * Author URI: 		  https://github.com/DimChtz
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-delayed-orders
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( !defined('WPINC') ) {
	die;
}

define('WCDO_VERSION', '1.0.0');
define('WCDO_ENV', 'PRODUCTION');
define('WCDO_PLUGIN_DIR', dirname(__FILE__));
define('WCDO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCDO_PLUGIN_BASENAME', dirname(plugin_basename(__FILE__)));
define('WCDO_PLUGIN_INCLUDES', WCDO_PLUGIN_DIR . '/includes');
define('WCDO_PLUGIN_TEMPLATES', WCDO_PLUGIN_DIR . '/templates');
define('WCDO_PLUGIN_ASSETS', WCDO_PLUGIN_URL . '/assets');
define('WCDO_PLUGIN_ASSETS_PATH', WCDO_PLUGIN_DIR . '/assets');

// Call the bootloader
require_once __DIR__ . '/bootloader.php';
