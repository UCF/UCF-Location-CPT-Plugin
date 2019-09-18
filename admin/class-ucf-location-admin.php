<?php
/**
 * Adds the admin javascript
 */
if ( ! class_exists( 'UCF_Location_Admin' ) ) {
	class UCF_Location_Admin {
		/**
		 * Enqueues the admin scripts
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param string $hook The page that is being loaded.
		 * @return void
		 */
		public static function admin_enqueue_scripts( $hook ) {
			if ( in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
				$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : null;

				$plugin_data = get_plugin_data( UCF_LOCATION__PLUGIN_FILE, false, false );
				$version = $plugin_data['Version'];

				wp_enqueue_script(
					'ucf_location_admin_js',
					UCF_LOCATION__JS_URL . '/admin.min.js',
					array( 'jquery' ),
					$version,
					true
				);
			}
		}
	}
}
