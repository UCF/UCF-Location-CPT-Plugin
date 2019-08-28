<?php
/*
Plugin Name: UCF Location Custom Post Type
Description: Provides a custom post type and custom fields for describing locations.
Version: 1.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/UCF-Location-CPT-Plugin
*/

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'UCF_LOCATION__FILE', __FILE__ );

require_once 'includes/class-location-post-type.php';

if ( ! function_exists( 'ucf_location_activation' ) ) {
	/**
	 * Function that runs on plugin activation
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	function ucf_location_activation() {
		UCF_Location_Post_Type::register_post_type();
		flush_rewrite_rules();
	}

	register_activation_hook( UCF_LOCATION__FILE, 'ucf_location_activation' );
}

if ( ! function_exists( 'ucf_location_deactivation' ) ) {
	/**
	 * Function that runs on plugin deactivation
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	function ucf_location_deactivation() {
		flush_rewrite_rules();
	}

	register_deactivation_hook( UCF_LOCATION__FILE, 'ucf_location_deactivation' );
}

if( ! function_exists( 'ucf_location_init' ) ) {
	/**
	 * Function that runs when all plugins are loaded
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	function ucf_location_init() {
		add_action( 'init', array( 'UCF_Location_Post_Type', 'register_post_type' ), 10, 0 );
	}

	add_action( 'plugins_loaded', 'ucf_location_init', 10, 0 );
}
