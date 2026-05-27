<?php
/**
 * Commands for managing data
 */
if ( ! class_exists( 'UCF_Location_Commands' ) ) {
	class UCF_Location_Commands extends WP_CLI_Command {
		/**
		 * Imports location data from the search service API.
		 *
		 * ## OPTIONS
		 *
		 * <endpoint>
		 * : The URL of the search service locations endpoint.
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
		 * : Comma-separated list of object types to import. Leave empty to import all types.
		 * ---
		 * default:
		 * ---
		 *
		 * [--data-source=<data-source>]
		 * : Filters results to those matching this data_source value. Leave empty to import all data sources.
		 * ---
		 * default:
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 * 	wp locations import https://search.cm.ucf.edu/api/v1/locations/
		 *
		 * 	wp locations import https://search.cm.ucf.edu/api/v1/locations/ --use-progress=false
		 *
		 * 	wp locations import https://search.cm.ucf.edu/api/v1/locations/ --object-types=Building,Dining
		 *
		 * 	wp locations import https://search.cm.ucf.edu/api/v1/locations/ --data-source="FBO Buildings Report"
		 *
		 * @when after_wp_load
		 */
		public function import( $args, $assoc_args ) {
			list( $endpoint )     = $args;
			$use_progress         = isset( $assoc_args['use-progress'] )
										? filter_var( $assoc_args['use-progress'], FILTER_VALIDATE_BOOLEAN )
										: true;
			$desired_object_types = ( isset( $assoc_args['object-types'] ) && ! empty( $assoc_args['object-types'] ) )
										? explode( ',', $assoc_args['object-types'] )
										: array();
			$data_source          = ( isset( $assoc_args['data-source'] ) && ! empty( $assoc_args['data-source'] ) )
									? $assoc_args['data-source']
									: null;

			if ( empty( $endpoint ) ) {
				WP_CLI::error( 'A search service endpoint URL is required to run the location importer.' );
			}

			try {
				$importer = new UCF_Location_Importer( $endpoint, $use_progress, $desired_object_types, $data_source );
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
		 * default: location
		 * ---
		 *
		 * [--exclude-child-types=<exclude-child-types>]
		 * : Comma-separated list of location types to exclude from child association. All other types will be included as children.
		 * ---
		 * default: location
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
		 * wp locations associate --parent-types=location,campus
		 *
		 * wp locations associate --exclude-child-types=location,parking
		 *
		 * wp locations associate --multi-assoc
		 */
		public function associate( $args, $assoc_args ) {
			$field        = isset( $assoc_args['field'] ) ?
							$assoc_args['field'] :
							'ucf_location_campus';

			$parent_types = isset( $assoc_args['parent-types'] ) ?
							explode( ',', $assoc_args['parent-types'] ) :
							array( 'location' );

			$excluded_child_types = isset( $assoc_args['exclude-child-types'] ) ?
							array_map( 'sanitize_title', explode( ',', $assoc_args['exclude-child-types'] ) ) :
							array( 'location' );

			$all_child_types = get_terms( array(
				'taxonomy'   => 'location_type',
				'hide_empty' => false,
				'fields'     => 'slugs',
			) );

			if ( is_wp_error( $all_child_types ) ) {
				WP_CLI::error( 'Failed to retrieve location types.' );
			}

			$child_types = array_values( array_diff( $all_child_types, $excluded_child_types ) );

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
