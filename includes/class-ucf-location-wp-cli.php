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
		 * default: Building,DiningLocation,Location
		 * ---
		 *
		 * [--media-base=<media-base>]
		 * : The base URL of media objects on map.ucf.edu.
		 * ---
		 * default: https://map.ucf.edu/media/
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 * 	wp locations import https://someurl.com/locations.json
		 *
		 * 	wp locations import --use-progress=false
		 *
		 * 	wp locations import --media-base=https://someotherurl.com/media/
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
										: array( 'Building', 'DiningLocation', 'Location' );
			$media_base           = isset( $assoc_args['media-base'] )
										? $assoc_args['media-base']
										: 'https://map.ucf.edu/media/';

			if ( empty( $endpoint ) ) {
				WP_CLI::error( 'A JSON endpoint is required to run the location importer.' );
			}

			$importer = new UCF_Location_Importer( $endpoint, $use_progress, $desired_object_types, $media_base );

			try {
				$importer->import();
				WP_CLI::success( $importer->print_stats() );
			} catch ( Exception $e ) {
				WP_CLI::error( $e->getMessage(), $e->getCode() );
			}
		}

		/**
		 * Creates a relationship between map points
		 * based on their proximity to each other
		 *
		 * ## OPTIONS
		 *
		 * [--field=<field>]
		 * : The custom field to set with the association data.
		 * ---
		 * default: ucf_location_campus
		 * ---
		 *
		 * [--parent-types=<parent-types>]
		 * : The location type of the parent locations
		 * ---
		 * default: Location
		 * ---
		 *
		 * [--child-type=<child-types>]
		 * : The location type of the children locations
		 * ---
		 * default: Building,DiningLocation
		 * ---
		 *
		 * [--distance=<distance>]
		 * : Distance, in km, between two locations for them to be associated.
		 * ---
		 * default: 5
		 * ---
		 *
		 * [--multi-assoc[=<multi-assoc>]]
		 * : Determines if locations can have multiple parents
		 * ---
		 * default: false
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 * wp locations associate --distance=3
		 *
		 * wp locations associate --parent-types=Location,Campus
		 *
		 * wp locations associate --multi-assoc
		 */
		public function associate( $args, $assoc_args ) {
			$field        = isset( $assoc_args['field'] ) ?
							$assoc_args['field'] :
							'ucf_location_campus';

			$parent_types = isset( $assoc_args['parent-types'] ) ?
							explode( ',', $assoc_args['parent-types'] ) :
							array( 'Location' );

			$child_types  = isset( $assoc_args['child-types'] ) ?
							explode( ',', $assoc_args['child-types'] ) :
							array( 'Building', 'DiningLocation' );

			$distance     = isset( $assoc_args['distance'] ) ?
							$assoc_args['distance'] :
							5;

			$multi_assoc  = false;

			if ( isset( $assoc_args['multi-assoc'] ) ) {
				$multi_assoc = filter_var( $assoc_args['multi-assoc'], FILTER_VALIDATE_BOOLEAN );
			}

			$importer = new UCF_Location_Associate( $field, $distance, $parent_types, $child_types, $multi_assoc );

			try {
				$importer->import();
				WP_CLI::success( $importer->print_stats() );
			} catch ( Exception $e ) {
				WP_CLI::error( $e->getMessage(), $e->getCode() );
			}
		}
	}
}
