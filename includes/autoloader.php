<?php

if ( !defined('ABSPATH') ) {
	exit;
}

spl_autoload_register(function($class) {

	// Filter the class name (remove base namespace)
	$class = implode('\\', array_filter(explode('\\', $class), function($token) {
		return !in_array($token, ['Dimchtz', 'WCDO']);
	}));

	// Replace separators and convert to lcase
	$class = strtolower(str_replace('\\', DIRECTORY_SEPARATOR , $class));

	// Get the filename
	$file = __DIR__ . DIRECTORY_SEPARATOR . "{$class}.php";

	is_readable($file) && require_once $file;

});