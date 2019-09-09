<?php
/**
 * Commands for managing data
 */
if ( ! class_exists( 'UCF_Location_Commands' ) ) {
	class UCF_Location_Commands extends WP_CLI_Command {
		/**
		 * Imports map data from the map.ucf.edu JSON feed.
		 *
		 * ## OPTIONS
		 *
		 * <endpoint>
		 * : The URL of the map.ucf.edu JSON feed.
		 *
		 * [--use-progress[=<use_progress>]]
		 * : Determines if a progress bar is shown while the import runs.
		 * ---
		 * default: true
		 * options:
		 * 	- true
		 * 	- false
		 *
		 * [--object-types=<object-types>]
		 * : The type of map objects to import.
		 * ---
		 * default: Building,DiningLocation
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 * 	wp locations import https://someurl.com/locations.json
		 *
		 * 	wp locations import --use-progress=false
		 *
		 * @when after_wp_load
		 */
		public function import( $args, $assoc_args ) {
			list( $endpoint )     = $args;
			$use_progress         = isset( $assoc_args['use-progress'] )
										? filter_var( $assoc_args['use-progress'], FILTER_VALIDATE_BOOLEAN )
										: true;
			$desired_object_types = isset( $assoc_args['object-types'] )
										? explode( ',', $assoc_args['object-types'] )
										: array( 'Building', 'DiningLocation' );

			if ( empty( $endpoint ) ) {
				WP_CLI::error( 'A JSON endpoint is required to run the location importer.' );
			}

			$importer = new UCF_Location_Importer( $endpoint, $use_progress, $desired_object_types );

			try {
				$importer->import();
				WP_CLI::success( $importer->print_stats() );
			} catch ( Exception $e ) {
				WP_CLI::error( $e->getMessage(), $e->getCode() );
			}
		}
	}
}
