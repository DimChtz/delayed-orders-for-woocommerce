<?php

namespace Dimchtz\WCDO\Modules;

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Plugin's settings class
 * 
 * @since 1.0.0
 */

class Settings implements \ArrayAccess {

	public $options;

	/**
	 * Settings constructor.
	 *
	 * Initializing the settings interface.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		// Initialize and load existing settings
		$this->init_options();
		$this->load_options();

		$this->register_hooks();

	}

	/**
     * Initializes all plugin options
     * 
     * @since 1.0.0
     * @access public
     */
	public function init_options() {

		$this->options = array(
			'delay_days' 			=> 3,
			'delay_days_range' 		=> '0',
			'delay_order_statuses' 	=> ['wp-processing']
		);

		if ( !get_option('wcdo_settings') ) {
			add_option('wcdo_settings', json_encode($this->options));
		}

	}

	/**
     * Loads all plugin options
     * 
     * @since 1.0.0
     * @access public
     */
	public function load_options() {

		if ( get_option('wcdo_settings') ) {
			$this->options = json_decode(get_option('wcdo_settings'), true);
		}

	}

	/**
     * Updates all plugin options
     * 
     * @since 1.0.0
     * @access public
     */
	public function update_options() {

		update_option('wcdo_settings', json_encode($this->options));

	}

	/**
     * Registers all admin actions and filters.
     * 
     * @since 1.0.0
     * @access public
     */
	public function register_hooks() {

		// Add a custom tab on WooCommerce settings page
		add_action('woocommerce_settings_tabs', [$this, 'wcdo_settings_tab']);
		add_action('woocommerce_settings_delayed_orders', [$this, 'wcdo_settings_tab_content']);
		add_action('woocommerce_update_options_delayed_orders', [$this, 'wcdo_settings_tab_save']);

	}


	/** ==========================================================
	 *    Main hooks for the settings tab
	 =========================================================== */

	/**
     * Adds the settings tab
     * 
     * @since 1.0.0
     * @access public
     */
	public function wcdo_settings_tab() {

		$current_tab = ( isset($_GET['tab']) && $_GET['tab'] === 'delayed_orders' ) ? 'nav-tab-active' : '';
    	echo '<a href="'.admin_url('admin.php?page=wc-settings&tab=delayed_orders').'" class="nav-tab '.$current_tab.'">'.__( "Delayed Orders", "wc-delayed-orders" ).'</a>';

	}

	/**
     * Adds the settings tab content
     * 
     * @since 1.0.0
     * @access public
     */
	public function wcdo_settings_tab_content() {

		wcdo_load_template('settings-tab');

	}

	/**
     * Handles the settings tab save process
     * 
     * @since 1.0.0
     * @access public
     */
	public function wcdo_settings_tab_save() {

		if ( isset($_POST['delay_days']) ) {
			$this['delay_days'] = intval(sanitize_text_field($_POST['delay_days']));
		}

		if ( isset($_POST['delay_days_range']) ) {
			$range = intval(sanitize_text_field($_POST['delay_days_range']));
			if ( $range >= 0 && $range < 4 ) {
				$this['delay_days_range'] = $range;
			}
		}

		if ( isset($_POST['delay_order_statuses']) && is_array($_POST['delay_order_statuses']) ) {
			$wc_statuses 	= array_keys(wc_get_order_statuses());
			$statuses 		= array_map('sanitize_text_field', $_POST['delay_order_statuses']);
			$statuses 		= array_filter($statuses, function($s) use($wc_statuses) {
				return in_array($s, $wc_statuses);
			});

			// If empty, force processing status
			if ( count($statuses) == 0 ) {
				$statuses[] = 'wc-processing';
			}

			$this['delay_order_statuses'] = $statuses;
		}

		$this->update_options();

		\Dimchtz\WCDO\Plugin::instance()->admin->update_delay_status_all();

	}


	/** ==========================================================
	 *    Overloads for the ArrayAccess Interface
	 =========================================================== */

	public function offsetSet($offset, $value) {

		if ( !is_null($offset) ) {
			$this->options[$offset] = $value;
		}

	}

	public function offsetGet($offset) {

		return isset($this->options[$offset]) ? $this->options[$offset] : null;

	}

	public function offsetExists($offset) {

		return isset($this->options[$offset]);

	}

	public function offsetUnset($offset) {

		unset($this->options[$offset]);

	}

}