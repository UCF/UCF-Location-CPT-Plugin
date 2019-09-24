<?php
/**
 * Provides a shortcode for a locations
 * typeahead
 */
if ( ! class_exists( 'UCF_Location_Typeahead_Shortcode' ) ) {
	class UCF_Location_Typeahead_Shortcode {
		/**
		 * Registers the locations-typeahead shortcode
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @return void
		 */
		public static function register_shortcode() {
			add_shortcode( 'locations-typeahead', array( 'UCF_Location_Typeahead_Shortcode', 'callback' ) );
		}

		public static function callback( $atts, $content='' ) {
			ob_start();
		?>
			<input type="text" class="location-search form-control form-control-lg" placeholder="Library" aria-label="Search UCF Locations">
		<?php
			return ob_get_clean();
		}
	}
}
