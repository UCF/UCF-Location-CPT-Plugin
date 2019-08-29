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

// Must be first as other classes use these utility functions
require_once 'includes/class-ucf-location-utilities.php';
require_once 'admin/class-ucf-location-notices.php';

require_once 'admin/class-ucf-location-config.php';
require_once 'includes/class-ucf-location-post-type.php';

if ( ! function_exists( 'ucf_location_activation' ) ) {
	/**
	 * Function that runs on plugin activation
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	function ucf_location_activation() {
		if ( ! UCF_Location_Utils::acf_is_active() ) {
			die( "Advanced Custom Fields Pro or the free Advanced Custom Fields versions 5.0.0 or higher are required to activate the UCF Location Plugin." );
		}

		UCF_Location_Config::add_options();
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
		UCF_Location_Config::delete_options();
		flush_rewrite_rules();
	}

	register_deactivation_hook( UCF_LOCATION__FILE, 'ucf_location_deactivation' );
}

if ( ! function_exists( 'ucf_location_init' ) ) {
	/**
	 * Function that runs when all plugins are loaded
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	function ucf_location_init() {
		// Add admin menu item
		add_action( 'admin_init', array( 'UCF_Location_Config', 'settings_init' ), 10, 0 );
		add_action( 'admin_menu', array( 'UCF_Location_Config', 'add_options_page' ), 10, 0 );

		// Init actions here
		add_action( 'init', array( 'UCF_Location_Post_Type', 'register_post_type' ), 10, 0 );
		add_action( 'init', array( 'UCF_Location_Config', 'add_option_formatting_filters' ), 10, 0 );

		if ( UCF_Location_Utils::acf_is_active() ) {
			add_action( 'acf/init', array( 'UCF_Location_Post_Type', 'register_acf_fields' ), 10, 0 );
		} else {
			add_action( 'admin_notices', array( 'UCF_Location_Admin_Notices', 'acf_not_active_notice' ), 10, 0 );
		}
	}

	add_action( 'plugins_loaded', 'ucf_location_init', 10, 0 );
}
