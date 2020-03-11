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
			 * @var int Number of profile images added.
			 */
			$media_locations = 0,
			/**
			 * @var int Number of profile images already existing
			 */
			$media_exists = 0,
			/**
			 * @var int The number of location types created
			 */
			$location_types_created = 0,
			/**
			 * @var array Array of posts that need to be published
			 */
			$posts_to_publish = array(),
			/**
			 * @var array An array of errors to display
			 */
			$errors = array(),
			/**
			 * @var string The upload directory for the site
			 */
			$upload_dir = '',
			/**
			 * @var string The media base of uploaded files on map.ucf.edu
			 */
			$media_base = '';

		/**
		 * Constructs a new instance of the
		 * UCF_Location_Importer
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param string $endpoint The URL of the map data to be imported
		 */
		public function __construct( $endpoint, $use_progress = true, $desired_object_types = array(), $media_base=null ) {
			$this->endpoint = $endpoint;
			$this->use_progress = $use_progress;
			$this->desired_object_types = ! empty( $desired_object_types )
											? $desired_object_types
											: array(
												'Building',
												'DiningLocation',
												'Location'
											);
			$this->upload_dir = wp_upload_dir();
			$this->media_base = trailingslashit( $media_base );
		}

		/**
		 * Returns the stats string
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @return string
		 */
		public function print_stats() {
			$processed = $this->processed_locations;
			$created   = $this->created_locations;
			$updated   = $this->updated_locations;
			$removed   = $this->removed_locations;
			$errors    = $this->errors;
			$media     = $this->media_locations;
			$m_exists  = $this->media_exists;
			$terms     = $this->location_types_created;

			$retval = "

Processed: $processed
Created:   $created
Updated:   $updated
Removed:   $removed

Images Uploaded: $media
Existing Images: $m_exists

Location Types Created: $terms
			";
			if ( count( $errors ) > 0 ) {

				$retval .= "
Errors:
				";

				foreach( $errors as $error ) {
					$retval .= "
	" . $error['name'] . " : " . $error['message'];
				}
			}

			return $retval;
		}

		/**
		 * Imports the map objects
		 * @author Jim Barnes
		 * @since 0.1.0
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
		 * @since 0.1.0
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
		 * @since 0.1.0
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
		 * @since 0.1.0
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
		 * @since 0.1.0
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
						unset( $this->existing_locations[$data->id] );
					} else {
						$this->errors[] = array(
							'name'    => 'Map Location ID ' . $data->id . ' (' . ( $data->name ?? 'name n/a' ) . ')',
							'message' => $updated->get_error_message()
						);
					}
				} else {
					$created = $this->create_new( $data );

					if ( $created === true ) {
						$this->created_locations++;
					} else {
						$this->errors[] = array(
							'name'    => 'Map Location ID ' . $data->id . ' (' . ( $data->name ?? 'name n/a' ) . ')',
							'message' => $created->get_error_message()
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
		 * @since 0.1.0
		 * @param int $_id The post ID to update
		 * @param string $data_id The map ID
		 * @return bool|WP_Error True if updated, the WP_Error if there was an error
		 */
		private function update_existing( $post_id, $data ) {
			// Require a name/title for the location.
			// Bail out early if one isn't available for some reason.
			$title = isset( $data->name ) ? trim( $data->name ) : '';
			if ( ! $title && isset( $data->title ) ) {
				$title = trim( $data->title );
			}
			if ( ! $title ) {
				return new WP_Error( 'ucflocation_map_location_nameless', 'Map location has no name.' );
			}

			$desc  = isset( $data->profile ) ? trim( $data->profile ) : $data->description;

			$split = explode( '/', untrailingslashit( $data->profile_link ) );
			$post_name = $this->clean_post_name( end( $split ), $data );

			$post_data = array(
				'ID'           => $post_id,
				'post_name'    => $post_name,
				'post_status'  => 'draft',
				'post_title'   => $title,
				'post_content' => $desc,
				'post_type'    => 'location'
			);

			$result = wp_update_post( $post_data );

			if ( is_wp_error( $result ) ) {
				// Could save the post, bail out
				return $result;
			}

			$this->update_meta( $result, $data );
			$this->update_terms( $result, $data );

			$this->posts_to_publish[] = $result;

			return true;
		}

		/**
		 * Creates a new post with data from the import
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param string $data_id The map ID
		 * @return bool|WP_Error True if created, a WP_Error if there was an error
		 */
		private function create_new( $data ) {
			// Require a name/title for the location.
			// Bail out early if one isn't available for some reason.
			$title = isset( $data->name ) ? trim( $data->name ) : '';
			if ( ! $title && isset( $data->title ) ) {
				$title = trim( $data->title );
			}
			if ( ! $title ) {
				return new WP_Error( 'ucflocation_map_location_nameless', 'Map location has no name.' );
			}

			$desc  = isset( $data->profile ) ? trim( $data->profile ) : $data->description;
			$split = explode( '/', untrailingslashit( $data->profile_link ) );
			$post_name = $this->clean_post_name( end( $split ), $data );

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
			$this->update_terms( $result, $data );

			$this->posts_to_publish[] = $result;

			return true;
		}

		/**
		 * Removes the location abbreviation from the
		 * post_name if it is there.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param string $post_name The post name
		 * @param object $data The location object
		 */
		private function clean_post_name( $post_name, $data ) {
			if ( ! isset( $data->abbreviation ) ) return $post_name;

			$abbr = strtolower( $data->abbreviation );
			$pattern = "/\-$abbr$/";
			$post_name = preg_replace( $pattern, '', $post_name );

			return $post_name;
		}

		/**
		 * Updates post meta for a location
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param int $_id The post ID to update
		 * @param string $data_id The map ID
		 * @return bool True if updated
		 */
		private function update_meta( $post_id, $data ) {
			update_field( 'ucf_location_id', $data->id, $post_id );

			if ( isset( $data->abbreviation ) ) {
				update_field( 'ucf_location_abbr', $data->abbreviation, $post_id );
			}

			update_field( 'ucf_location_lat_lng', array(
				'ucf_location_lat' => $data->googlemap_point[0],
				'ucf_location_lng' => $data->googlemap_point[1]
			), $post_id );

			if ( isset( $data->address ) ) {
				update_field( 'ucf_location_address', $data->address, $post_id );
			}

			if ( isset( $data->image ) && ! empty( $data->image ) ) {
				$result = $this->upload_media(
					$this->media_base . $data->image,
					$post_id
				);

				if ( $result ) {
					$this->media_locations++;
				}
			}

			if ( isset( $data->orgs ) ) {
				if ( count( $data->orgs->results ) > 0 ) {
					$this->update_orgs( $post_id, $data->orgs->results );
				}
			}

			return true;
		}

		/**
		 * Updates taxonomy data
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param int $post_id The post ID
		 * @param object $data The data object from map
		 * @return void
		 */
		private function update_terms( $post_id, $data ) {
			$object_type = $data->object_type;

			$term = null;

			if ( term_exists( $object_type, 'location_type' ) ) {
				$term = get_term_by( 'name', $object_type, 'location_type' );
				$term = $term->term_id;
			} else {
				$term = wp_insert_term( $object_type, 'location_type' );
				$this->location_types_created++;
			}

			wp_set_post_terms(
				$post_id,
				array( $term ),
				'location_type',
				false
			);
		}

		/**
		 * Adds orgs data to the location
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param int $post_id The post ID
		 * @param array $orgs The array of org data
		 * @return void
		 */
		private function update_orgs( $post_id, $orgs ) {
			// Start fresh with every import
			$data = array();

			foreach( $orgs as $org ) {
				$org_name = $org->name;
				$org_phone = isset( $org->phone ) ? $org->phone : null;
				$org_room = isset( $org->room ) ? $org->room : null;

				$org_data = array(
					'org_name'        => $org_name,
					'org_phone'       => $org_phone,
					'org_room'        => $org_room,
					'org_departments' => array()
				);

				if ( isset( $org->departments ) && count( $org->departments ) > 0 ) {
					foreach( $org->departments as $dept ) {
						$dept_name  = $dept->name;
						$dept_phone = isset( $dept->phone ) ? $dept->phone : null;
						$dept_build = isset( $dept->bldg ) ? $dept->bldg->name : null;
						$dept_room  = isset( $dept->room ) ? $dept->room : null;

						$dept_data = array(
							'dept_name'     => $dept_name,
							'dept_phone'    => $dept_phone,
							'dept_building' => $dept_build,
							'dept_room'     => $dept_room
						);

						$org_data['org_departments'][] = $dept_data;
					}
				}

				$data[] = $org_data;
			}

			update_field( 'ucf_location_orgs', $data, $post_id );
		}

		/**
		 * Removes any existing locations not found
		 * in the imported data.
		 * @author Jim Barnes
		 * @since 0.1.0
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
		 * @since 0.1.0
		 * @return void
		 */
		private function publish_posts() {
			foreach( $this->posts_to_publish as $post_id ) {
				wp_publish_post( $post_id );
			}
		}

		/**
		 * Retrieves an external image and uploads it
		 * to the post as the featured image.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param string $image_url The URL of the image to upload
		 * @param int $post_id The ID of the post to set as a featured image
		 * @return bool True if file is successfully uploaded and attached
		 */
		private function upload_media( $image_url, $post_id ) {
			$response = wp_remote_get( $image_url, array( 'timeout' => 15 ) );
			$filename   = basename( $image_url );

			// There was a problem getting the file, return
			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) > 400 ) {
				return false;
			}

			$image_data = wp_remote_retrieve_body( $response );

			// There was a problem reading the data, return
			if ( is_wp_error( $image_data ) ) {
				return false;
			}

			if ( wp_mkdir_p( $this->upload_dir['path'] ) ) {
				$file = $this->upload_dir['path'] . '/' . $filename;
			} else {
				$file = $this->upload_dir['basedir'] . '/' . $filename;
			}

			// File already exists, return
			if ( file_exists( $file ) ) {
				$this->media_exists++;
				return false;
			}

			$result = file_put_contents( $file, $image_data );

			// If result is false, the file put failed.
			if ( $result === false ) return false;

			$wp_filetype = wp_check_filetype( $filename, null );

			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => sanitize_file_name( $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attachment_id   = wp_insert_attachment( $attachment, $file, $post_id );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			set_post_thumbnail( $post_id, $attachment_id );

			return true;
		}
	}
}
