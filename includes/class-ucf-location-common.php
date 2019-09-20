<?php
/**
 * Place for common functions
 */
if ( ! class_exists( 'UCF_Location_Common' ) ) {
	class UCF_Location_Common {
		/**
		 * Enqueues all front end assets.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public static function enqueue_frontend_assets() {
			wp_register_script(
				'ucf_location_script',
				UCF_LOCATION__JS_URL . '/script.min.js',
				array( 'jquery' ),
				null,
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
		 * @since 1.0.0
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
