<?php

namespace Dimchtz\WCDO;

if ( !defined('ABSPATH') ) {
	exit;
}

// Prepare autoloader and helper functions
require_once WCDO_PLUGIN_INCLUDES . '/autoloader.php';
require_once WCDO_PLUGIN_INCLUDES . '/helpers.php';

// Initialize global settings object
$wcdo_settings = new Modules\Settings();

// Plugin Activator/Deactivator
register_activation_hook(WCDO_PLUGIN_BASENAME, 'Activator::activate');
register_deactivation_hook(WCDO_PLUGIN_BASENAME, 'Activator::deactivate');

// Fire plugin's core
Plugin::instance();