<?php

namespace Dimchtz\WCDO;

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Plugin's core class
 * 
 * @since 1.0.0
 */

class Plugin {

    public $version;

    public $admin;

    public $frontend;

    public $is_active;

    public static $instance = null;

    /**
	 * Instance.
	 *
	 * Only one instance of the plugin class can be loaded.
	 *
	 * @since 1.0.0
	 * @static
     * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
    public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

        return self::$instance;
        
	}

    /**
	 * Plugin constructor.
	 *
	 * Initializing the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
    private function __construct() {

        $this->version = defined(WCDO_VERSION) ? WCDO_VERSION : '1.0.0';

        add_action('plugins_loaded', [$this, 'plugins_loaded']);
        add_action('init', [$this, 'init']);

    }

    /**
     * Prevent cloning, otherwise single instance class is useless
     *
     * @since 1.0.0
     * @access public
     */

    public function __clone() {

        trigger_error('Oops! Something went wrong.', E_USER_ERROR);

    }

    /**
     * Fire the 'wcdo_loaded' hook after WP has loaded all the plugins
     * 
     * @since 1.0.0
     * @access public
     */
    public function plugins_loaded() {

        do_action('wcdo_loaded');

    }

    /**
     * Initialize our plugin when WordPress is initialized.
     * 
     * @since 1.0.0
     * @access public
     */
    public function init() {

        do_action('wcdo_before_init');

        // Load the translations
        load_plugin_textdomain('wc-delayed-orders', false, WCDO_PLUGIN_BASENAME . '/languages/');

        $this->check_requirements();
        $this->init_modules();

        do_action('wcdo_after_init');

    }

    /**
     * Checks for the plugin's requirements
     *
     * @since 1.0.0
     * @access private
     */
    private function check_requirements() {

        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        // WooCommerce plugin is required
        // $this->is_active = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
        $this->is_active = \is_plugin_active('woocommerce/woocommerce.php');

    }

    /**
     * Initializes everything we need
     * 
     * @since 1.0.0
     * @access private
     */

    private function init_modules() {

        // Initialize modules
        $this->frontend = new Modules\Frontend($this->instance());
        is_admin() && $this->admin = new Modules\Admin($this->instance());

    }

}
