<?php
/**
 * Place for common functions
 */
if ( ! class_exists( 'UCF_Location_Common' ) ) {
	class UCF_Location_Common {
		/**
		 * Enqueues all front end assets.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @return void
		 */
		public static function enqueue_frontend_assets() {
			$ds_typeahead_enqueued = wp_script_is( 'ucf-degree-typeahead-js' );

			$deps_array = array(
				'jquery'
			);

			if ( UCF_Location_Config::get_option_or_default( 'typeahead_js_enqueue' ) ) {
				if ( ! $ds_typeahead_enqueued ) {
					wp_enqueue_script(
						'typeahead-js',
						UCF_LOCATION__TYPEAHEAD,
						null,
						null,
						true
					);

					$deps_array[] = 'typeahead-js';
				}
			}

			if ( UCF_Location_Config::get_option_or_default( 'handlebars_js_enqueue' ) ) {
				if ( ! $ds_typeahead_enqueued ) {
					wp_enqueue_script(
						'handlebars-js',
						UCF_LOCATION__HANDLEBARS,
						null,
						null,
						true
					);

					$deps_array[] = 'handlebars-js';
				}
			}

			if ( $ds_typeahead_enqueued ) {
				/**
				 * Make sure the dependency array has the degree
				 * search handles for typeahead and halendars.
				 * TODO: Update these values after this issue
				 * has been solved:
				 * https://github.com/UCF/UCF-Degree-Search-Plugin/issues/83
				 */
				$deps_array[] = 'ucf-degree-typeahead-js';
				$deps_array[] = 'ucf-degree-handlebars-js';
			}

			wp_register_script(
				'ucf_location_script',
				UCF_LOCATION__JS_URL . '/script.min.js',
				$deps_array,
				UCF_LOCATION__VERSION,
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

			wp_enqueue_script( 'ucf_location_script' );
		}

		/**
		 * Gets a sinple of array of locations
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
