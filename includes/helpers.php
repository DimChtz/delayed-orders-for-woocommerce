<?php

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Collection of various helper functions
 */

function wcdo_load_template($template) {

	include WCDO_PLUGIN_TEMPLATES . "/{$template}.php";

}

function wcdo_count_delay_orders($statuses) {

	if ( is_null($statuses) ) {
		$statuses = ['wp-processing'];
	}

	if ( is_string($statuses) ) {
		$statuses = [$statuses];
	}

	$query = new \WP_Query(array(
		'post_type' 		=> 'shop_order',
		'posts_per_page' 	=> -1,
		'post_status' 		=> $statuses,
		'meta_query'		=> array(
			array(
				'key' 		=> '_wc_delay_status',
				'value' 	=> '1',
				'compare' 	=> '='
			)
		)
	));

	return $query->post_count;

}

function wcdo_count_unmarked_orders($statuses) {

	if ( is_null($statuses) ) {
		$statuses = ['wp-processing'];
	}

	if ( is_string($statuses) ) {
		$statuses = [$statuses];
	}

	$query = new \WP_Query(array(
		'post_type' 		=> 'shop_order',
		'posts_per_page' 	=> -1,
		'post_status' 		=> $statuses,
		'meta_query'		=> array(
			array(
				'key' 		=> '_wc_delay_status',
				'value' 	=> '',
				'compare' 	=> 'NOT EXISTS'
			)
		)
	));

	return $query->post_count;

}