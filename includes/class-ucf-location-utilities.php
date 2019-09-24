<?php
/**
 * Utility functions
 */
if ( ! class_exists( 'UCF_Location_Utils' ) ) {
	/**
	 * Class for holding useful, common utility
	 * static functions.
	 * @author Jim Barnes
	 * @since 0.1.0
	 */
	class UCF_Location_Utils {
		private static
			$acf_pro_file_location    = 'advanced-custom-fields-pro/acf.php',
			$acf_free_file_location   = 'advanced-custom-fields/acf.php',
			$ucf_events_file_location = 'UCF-Events-Plugin/ucf-events.php',
			$plugin_path              = ABSPATH . 'wp-content/plugins/' ;

		/**
		 * Determines if the Advanced Custom Fields
		 * plugin is installed and active.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param string $required_version The version the plugin must be
		 */
		public static function acf_is_active( $required_version='5.0.0' ) {
			// See if the pro version is installed
			if ( is_plugin_active( self::$acf_pro_file_location ) ) {
				$plugin_data = get_plugin_data( self::$plugin_path . self::$acf_pro_file_location );
				if ( self::is_above_version( $plugin_data['Version'], $required_version ) ) {
					return true;
				}
			}

			if ( is_plugin_active( self::$acf_free_file_location ) ) {
				$plugin_data = get_plugin_data( self::$plugin_path . self::$acf_free_file_location );
				if ( self::is_above_version( $plugin_data['Version'], $required_version ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		* Determines if the UCF Events
		* plugin is installed and active.
		* @author Jim Barnes
		* @since 0.1.0
		* @param string $required_version The version the plugin must be
		*/
		public static function ucf_events_is_active( $required_version='2.0.0' ) {
			if ( function_exists( 'is_plugin_active' ) ) {
				if ( is_plugin_active( self::$ucf_events_file_location ) ) {
					$plugin_data = get_plugin_data( self::$plugin_path . self::$ucf_events_file_location );
					if ( self::is_above_version( $plugin_data['Version'], $required_version ) ) {
						return true;
					}
				}
			} else {
				if ( class_exists( 'UCF_Events_Feed' ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Determines if a provided version is higher
		 * than the provided required version.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param string $version The version to be compared
		 * @param string $required_version The requirement that must be met
		 * @return bool
		 */
		private static function is_above_version( $version, $required_version ) {
			if ( version_compare( $version, $required_version ) >= 0 ) {
				return true;
			}

			return false;
		}
	}
}
