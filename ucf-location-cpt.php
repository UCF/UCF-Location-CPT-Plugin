<?php
/*
Plugin Name: UCF Location Custom Post Type
Description: Provides a custom post type and custom fields for describing locations.
Version: 0.2.2
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/UCF-Location-CPT-Plugin
*/

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'UCF_LOCATION__PLUGIN_FILE', __FILE__ );
define( 'UCF_LOCATION__PLUGIN_URL', plugins_url( basename( dirname( __FILE__ ) ) ) );
define( 'UCF_LOCATION__STATIC_URL', UCF_LOCATION__PLUGIN_URL . '/static' );
define( 'UCF_LOCATION__JS_URL', UCF_LOCATION__STATIC_URL . '/js' );

define( 'UCF_LOCATION__VERSION', '0.1.3' );

define( 'UCF_LOCATION__TYPEAHEAD', 'https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.0.1/typeahead.bundle.min.js' );
define( 'UCF_LOCATION__HANDLEBARS', 'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.6/handlebars.min.js' );


// Must be first as other classes use these utility functions
require_once 'includes/class-ucf-location-utilities.php';
require_once 'admin/class-ucf-location-notices.php';

require_once 'admin/class-ucf-location-config.php';
require_once 'admin/class-ucf-location-admin.php';
require_once 'includes/class-ucf-location-post-type.php';
require_once 'includes/class-ucf-location-type-tax.php';
require_once 'shortcodes/class-ucf-location-typeahead-sc.php';
require_once 'includes/class-ucf-location-common.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once 'importers/class-ucf-location-importer.php';
	require_once 'importers/class-ucf-location-associate.php';
	require_once 'includes/class-ucf-location-wp-cli.php';

	WP_CLI::add_command( 'locations', 'UCF_Location_Commands' );
}

if ( ! function_exists( 'ucf_location_activation' ) ) {
	/**
	 * Function that runs on plugin activation
	 * @author Jim Barnes
	 * @since 0.1.0
	 */
	function ucf_location_activation() {
		if ( ! UCF_Location_Utils::acf_is_active() ) {
			die( "Advanced Custom Fields Pro or the free Advanced Custom Fields versions 5.0.0 or higher are required to activate the UCF Location Plugin." );
		}

		UCF_Location_Config::add_options();
		UCF_Location_Post_Type::register_post_type();
		UCF_Location_Type_Taxonomy::register_taxonomy();
		flush_rewrite_rules();
	}

	register_activation_hook( UCF_LOCATION__PLUGIN_FILE, 'ucf_location_activation' );
}

if ( ! function_exists( 'ucf_location_deactivation' ) ) {
	/**
	 * Function that runs on plugin deactivation
	 * @author Jim Barnes
	 * @since 0.1.0
	 */
	function ucf_location_deactivation() {
		UCF_Location_Config::delete_options();
		flush_rewrite_rules();
	}

	register_deactivation_hook( UCF_LOCATION__PLUGIN_FILE, 'ucf_location_deactivation' );
}

if ( ! function_exists( 'ucf_location_init' ) ) {
	/**
	 * Function that runs when all plugins are loaded
	 * @author Jim Barnes
	 * @since 0.1.0
	 */
	function ucf_location_init() {
		// Add admin menu item
		add_action( 'admin_init', array( 'UCF_Location_Config', 'settings_init' ), 10, 0 );
		add_action( 'admin_menu', array( 'UCF_Location_Config', 'add_options_page' ), 10, 0 );

		// Init actions here
		add_action( 'init', array( 'UCF_Location_Type_Taxonomy', 'register_taxonomy' ), 10, 0 );
		add_action( 'init', array( 'UCF_Location_Post_Type', 'register_post_type' ), 10, 0 );
		add_action( 'init', array( 'UCF_Location_Config', 'add_option_formatting_filters' ), 10, 0 );
		add_action( 'init', array( 'UCF_Location_Typeahead_Shortcode', 'register_shortcode' ), 10, 0 );

		add_action( 'wp_enqueue_scripts', array( 'UCF_Location_Common', 'enqueue_frontend_assets' ), 10, 0 );

		if ( UCF_Location_Utils::acf_is_active() ) {
			add_action( 'acf/init', array( 'UCF_Location_Post_Type', 'register_acf_fields' ), 10, 0 );
		} else {
			add_action( 'admin_notices', array( 'UCF_Location_Admin_Notices', 'acf_not_active_notice' ), 10, 0 );
		}

		// Only append metadata on the front end
		if ( ! is_admin() ) {
			add_filter( 'posts_results', array( 'UCF_Location_Post_Type', 'append_meta_to_results' ), 10, 2 );
		}

		add_action( 'admin_enqueue_scripts', array( 'UCF_Location_Admin', 'admin_enqueue_scripts' ), 10, 1 );
	}

	add_action( 'plugins_loaded', 'ucf_location_init', 10, 0 );
}
