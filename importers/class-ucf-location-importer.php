<?php
/**
 * Provides an importer for the json feed
 * from map.ucf.edu.
 */
if ( ! class_exists( 'UCF_Location_Importer' ) ) {
	class UCF_Location_Importer {
		private
			/**
			 * @var string The URL of the map data being imported
			 */
			$endpoint,
			/**
			 * @var bool Whether a progress bar should be displayed
			 */
			$use_progress = true,
			/**
			 * @var array Stores the map data from the URL endpoint
			 */
			$map_data = false,
			/**
			 * @var array An array of existing locations
			 */
			$existing_locations = array(),
			/**
			 * @var array An array of desired object types to import
			 */
			$desired_object_types,
			/**
			 * @var int The number of locations processed
			 */
			$processed_locations = 0,
			/**
			 * @var int The number of posts created
			 */
			$created_locations = 0,
			/**
			 * @var int The number of posts updated
			 */
			$updated_locations = 0,
			/**
			 * @var int The number of posts removed
			 */
			$removed_locations = 0,
			/**
			 * @var array Array of posts that need to be published
			 */
			$posts_to_publish = array(),
			/**
			 * @var array An array of errors to display
			 */
			$errors = array();

		/**
		 * Constructs a new instance of the
		 * UCF_Location_Importer
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param string $endpoint The URL of the map data to be imported
		 */
		public function __construct( $endpoint, $use_progress = true, $desired_object_types = array() ) {
			$this->endpoint = $endpoint;
			$this->use_progress = $use_progress;
			$this->desired_object_types = ! empty( $desired_object_types )
											? $desired_object_types
											: array(
												'Building',
												'DiningLocation'
											);
		}

		/**
		 * Returns the stats string
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return string
		 */
		public function print_stats() {
			$processed = $this->processed_locations;
			$created   = $this->created_locations;
			$updated   = $this->updated_locations;
			$removed   = $this->removed_locations;
			$errors    = $this->errors;

			$retval = "

Processed: $processed
Created:   $created
Updated:   $updated
Removed:   $removed
			";
			if ( count( $errors ) > 0 ) {

				$retval .= "
Errors:
				";

				foreach( $errors as $error ) {
					$retval .= "
	$error->name: $error->message
					";
				}
			}

			return $retval;
		}

		/**
		 * Imports the map objects
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public function import() {
			$this->get_data();
			$this->get_existing();
			$this->save_data();
			$this->remove_stale_locations();
			$this->publish_posts();
		}

		/**
		 * Gets the map data and filters it
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		private function get_data() {
			$response      = wp_remote_get( $this->endpoint, array( 'timeout' => 10 ) );
			$response_code = wp_remote_retrieve_response_code( $response );
			$result        = false;

			if ( is_array( $response ) && is_int( $response_code ) && $response_code < 400 ) {
				$result = json_decode( wp_remote_retrieve_body( $response ) );
				// Filter the results before returning.
				$result = $this->filter_data( $result );
			}

			$this->map_data = $result;
		}

		/**
		 * Gets the existing locations in the system
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		private function get_existing() {
			$args = array(
				'post_type'      => 'location',
				'posts_per_page' => -1
			);

			$posts = get_posts( $args );

			foreach( $posts as $post ) {
				$location_id = get_post_meta( $post->ID, 'ucf_location_id', true );

				if ( $location_id ) {
					$this->existing_locations[$location_id] = $post;
				}
			}
		}

		/**
		 * Filters the returned data to include
		 * only the desired object types.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $results The results to filter
		 * @return array
		 */
		private function filter_data( $results ) {
			$retval = array();

			foreach( $results as $result ) {
				// If this isn't an object type we want, skip it!
				if ( ! in_array( $result->object_type, $this->desired_object_types ) ) continue;

				$retval[] = $result;
			}

			return $retval;
		}

		/**
		 * Creates or updates the location custom
		 * post types.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		private function save_data() {
			if ( $this->use_progress ) {
				$progress = \WP_CLI\Utils\make_progress_bar( 'Importing locations...', count ( $this->map_data ) );
			}

			foreach( $this->map_data as $data ) {
				$updated = false;
				$created = false;

				if ( array_key_exists( $data->id, $this->existing_locations ) ) {
					$existing = $this->existing_locations[$data->id]->ID;
					$updated = $this->update_existing( $existing, $data );

					if ( $updated === true ) {
						$this->updated_locations++;
					} else {
						$this->errors[] = array(
							'name'    => $data->name,
							'message' => $updated->get_error_message()
						);
					}

					unset( $this->existing_locations[$data->id] );
				} else {
					$created = $this->create_new( $data );

					if ( $created === true ) {
						$this->created_locations++;
					} else {
						$this->errors[] = array(
							'name'    => $data->name,
							'message' => $updated->get_error_message()
						);
					}
				}

				if ( $this->use_progress ) {
					$progress->tick();
				}
			}

			if ( $this->use_progress ) {
				$progress->finish();
			}
		}

		/**
		 * Updates an existing post with data from the import
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param int $_id The post ID to update
		 * @param string $data_id The map ID
		 * @return bool|WP_Error True if updated, the WP_Error if there was an error
		 */
		private function update_existing( $post_id, $data ) {
			$title = isset( $data->title ) ? trim( $data->title ) : trim( $data->name );

			$split = explode( '/', untrailingslashit( $data->profile_link ) );
			$post_name = end( $split );

			$post_data = array(
				'ID'           => $post_id,
				'post_name'    => $post_name,
				'post_status'  => 'draft',
				'post_title'   => $title,
				'post_content' => $data->description,
				'post_type'    => 'location'
			);

			$result = wp_update_post( $post_data );

			if ( is_wp_error( $result ) ) {
				// Could save the post, bail out
				return $result;
			}

			$this->update_meta( $result, $data );

			$this->posts_to_publish[] = $result;

			return true;
		}

		/**
		 * Creates a new post with data from the import
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param string $data_id The map ID
		 * @return bool|WP_Error True if created, a WP_Error if there was an error
		 */
		private function create_new( $data ) {
			$title = isset( $data->name ) ? trim( $data->name ) : trim( $data->title );
			$desc  = isset( $data->profile ) ? trim( $data->profile ) : $data->description;
			$split = explode( '/', untrailingslashit( $data->profile_link ) );
			$post_name = end( $split );

			$post_data = array(
				'post_name'    => $post_name,
				'post_status'  => 'draft',
				'post_title'   => $title,
				'post_content' => $desc,
				'post_type'    => 'location'
			);

			$result = wp_insert_post( $post_data );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$this->update_meta( $result, $data );

			$this->posts_to_publish[] = $result;

			return true;
		}
		/**
		 * Updates post meta for a location
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param int $_id The post ID to update
		 * @param string $data_id The map ID
		 * @return bool True if updated
		 */
		private function update_meta( $post_id, $data ) {
			update_field( 'ucf_location_id', $data->id, $post_id );

			if ( isset( $data->abbreviation ) ) {
				update_field( 'ucf_location_abbr', $data->abbreviation, $post_id );
			}

			update_field( 'ucf_location_lng_lat', array(
				'ucf_location_lng' => $data->googlemap_point[0],
				'ucf_location_lat' => $data->googlemap_point[1]
			), $post_id );

			update_field( 'ucf_location_address', $data->address, $post_id );

			return true;
		}

		/**
		 * Removes any existing locations not found
		 * in the imported data.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		private function remove_stale_locations() {
			foreach( $this->existing_locations as $location ) {
				wp_delete_post( $location->ID, true );
				$this->removed_locations++;
			}
		}

		/**
		 * Publishes all the posts updated or created.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		private function publish_posts() {
			foreach( $this->posts_to_publish as $post_id ) {
				wp_publish_post( $post_id );
			}
		}
	}
}
