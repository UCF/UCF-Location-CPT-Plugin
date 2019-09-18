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
				'remote_path' => get_rest_url( null, '/wp/v2/locations/?s=%q' )
			);

			wp_localize_script(
				'ucf_location_script',
				'UCF_LOCATIONS_SEARCH',
				$localization_array
			);

			wp_enqueue_script( 'ucf_location_script' );
		}
	}
}
