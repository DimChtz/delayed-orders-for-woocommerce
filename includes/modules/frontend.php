<?php

namespace Dimchtz\WCDO\Modules;

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Plugin's frontend class
 * 
 * @since 1.0.0
 */

class Frontend {

	public $instance;

	/**
	 * Frontend constructor.
	 *
	 * Initializing the frontend interface.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct($instance) {

		$this->instance = $instance;

		$this->register_hooks();
		$this->register_assets();

	}

	/**
     * Registers all frontend actions and filters.
     * 
     * @since 1.0.0
     * @access public
     */
	public function register_hooks() {

		

	}

	/**
     * Registers all available frontend assets.
     * 
     * @since 1.0.0
     * @access public
     */
	public function register_assets() {

		if ( $this->instance->is_active ) {
			add_action('wp_enqueue_scripts', [$this, 'load_scripts']);
			add_action('wp_enqueue_scripts', [$this, 'load_styles']);
		}

	}

	/**
     * Loads all scripts for frontend.
     * 
     * @since 1.0.0
     * @access public
     */
	public function load_scripts() {

		$file_version = $this->instance->version;
		if ( defined(WCDO_ENV) && WCDO_ENV == 'DEVELOPMENT' ) {
			$file_version = time();
		}

		is_readable(WCDO_PLUGIN_ASSETS_PATH . '/js/main.frontend.js') &&
		wp_enqueue_script(
			'wysiwyg-main', 
			WCDO_PLUGIN_ASSETS . '/js/main.frontend.js', 
			array('jquery'), 
			$file_version, 
			true
		);

	}

	/**
     * Loads all styles for frontend.
     * 
     * @since 1.0.0
     * @access public
     */
	public function load_styles() {

		$file_version = $this->instance->version;
		if ( defined(WCDO_ENV) && WCDO_ENV == 'DEVELOPMENT' ) {
			$file_version = time();
		}

		is_readable(WCDO_PLUGIN_ASSETS_PATH . '/css/main.frontend.css') &&
		wp_enqueue_style(
			'wcdo-main', 
			WCDO_PLUGIN_ASSETS . '/css/main.frontend.css', 
			array(), 
			$file_version, 
		);

	}

}