<?php
/**
 * Place for common functions
 */
if ( ! class_exists( 'UCF_Location_Common' ) ) {
	class UCF_Location_Common {
		/**
		 * Registers frontend assets for the location typeahead shortcode.
		 * @author Jo Dickson
		 * @since 0.3.1
		 * @return void
		 */
		public static function register_frontend_assets() {
			$plugin_data = get_plugin_data( UCF_LOCATION__PLUGIN_FILE, false, false );
			$version     = $plugin_data['Version'];
			$deps_array  = array(
				'jquery'
			);

			if ( UCF_Location_Config::get_option_or_default( 'typeahead_js_enqueue' ) ) {
				wp_register_script(
					'typeahead-js',
					UCF_LOCATION__TYPEAHEAD,
					null,
					null,
					true
				);

				$deps_array[] = 'typeahead-js';
			}

			if ( UCF_Location_Config::get_option_or_default( 'handlebars_js_enqueue' ) ) {
				wp_register_script(
					'handlebars-js',
					UCF_LOCATION__HANDLEBARS,
					null,
					null,
					true
				);

				$deps_array[] = 'handlebars-js';
			}

			wp_register_script(
				'ucf_location_script',
				UCF_LOCATION__JS_URL . '/script.min.js',
				$deps_array,
				$version,
				true
			);

			$localization_array = array(
				'local_data' => UCF_Location_Common::get_locations()
			);

			wp_localize_script(
				'ucf_location_script',
				'UCF_LOCATIONS_SEARCH',
				$localization_array
			);
		}

		/**
		 * Enqueues frontend assets for the location typeahead shortcode.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @return void
		 */
		public static function enqueue_frontend_assets() {
			wp_enqueue_script( 'ucf_location_script' );
		}

		/**
		 * Gets a simple of array of locations
		 * for the typeahead localization array.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @return array
		 */
		public static function get_locations() {
			$retval = array();

			$args = array(
				'post_type'      => 'location',
				'posts_per_page' => -1,
			);

			$posts = get_posts( $args );

			foreach( $posts as $post ) {
				$retval[] = array(
					'title' => $post->post_title,
					'link'  => get_permalink( $post->ID )
				);
			}

			return $retval;
		}
	}
}
