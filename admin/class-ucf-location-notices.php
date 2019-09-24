<?php
/**
 * Stores banner alerts for the plugin
 */
if ( ! class_exists( 'UCF_Location_Admin_Notices' ) ) {
	class UCF_Location_Admin_Notices {
		/**
		 * A notice that informs users that ACF is not activated
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @return void The output is echoed
		 */
		public static function acf_not_active_notice() {
			$message = "The UCF Location plugin requires Advanced Custom Fields Pro version 5.0.0
			 or higher, or the free Advance Custom Fields 5.0.0 or higher. The fields within the Location
			 edit screen will not appear without the plugin installed.
			";

		?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e( $message, 'ucf_location' ); ?></p>
			</div>
		<?php
		}
	}
}
