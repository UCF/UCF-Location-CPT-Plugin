<?php
/**
 * Defines the Location custom post type
 */
if ( ! class_exists( 'UCF_Location_Post_Type' ) ) {
	class UCF_Location_Post_Type {
		/**
		 * Function that registers the custom post type
		 * @author Jim Barnes
		 * @since 1.0.0
		 */
		public static function register_post_type() {
			$labels = apply_filters(
				'ucf_location_labels',
				array(
					'singular'    => 'Location',
					'plural'      => 'Locations',
					'text_domain' => 'ucf_location'
				)
			);

			register_post_type( 'location', self::args( $labels ) );
		}

		/**
		 * Returns an array of labels for the custom post type
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $labels The labels array
		 * 						Defaults:
		 * 							( 'singular'    => 'Location' ),
		 * 							( 'plural'      => 'Locations' ),
		 * 							( 'text_domain' => 'ucf_location' )
		 * @return array
		 */
		public static function labels( $labels ) {
			$singular       = isset( $labels['singular'] ) ? $labels['singular'] : 'Location';
			$singular_lower = strtolower( $singular );
			$plural         = isset( $labels['plural'] ) ? $labels['plural'] : 'Locations';
			$plural_lower   = strtolower( $plural );
			$text_domain    = isset( $labels['text_domain'] ) ? $labels['text_domain'] : 'ucf_location';

			$retval = array(
				"name"                  => _x( $plural, "Post Type General Name", $text_domain ),
				"singular_name"         => _x( $singular, "Post Type Singular Name", $text_domain ),
				"menu_name"             => __( $plural, $text_domain ),
				"name_admin_bar"        => __( $singular, $text_domain ),
				"archives"              => __( "$singular Archives", $text_domain ),
				"parent_item_colon"     => __( "Parent $singular:", $text_domain ),
				"all_items"             => __( "All $plural", $text_domain ),
				"add_new_item"          => __( "Add New $singular", $text_domain ),
				"add_new"               => __( "Add New", $text_domain ),
				"new_item"              => __( "New $singular", $text_domain ),
				"edit_item"             => __( "Edit $singular", $text_domain ),
				"update_item"           => __( "Update $singular", $text_domain ),
				"view_item"             => __( "View $singular", $text_domain ),
				"search_items"          => __( "Search $plural", $text_domain ),
				"not_found"             => __( "Not found", $text_domain ),
				"not_found_in_trash"    => __( "Not found in Trash", $text_domain ),
				"featured_image"        => __( "Featured Image", $text_domain ),
				"set_featured_image"    => __( "Set featured image", $text_domain ),
				"remove_featured_image" => __( "Remove featured image", $text_domain ),
				"use_featured_image"    => __( "Use as featured image", $text_domain ),
				"insert_into_item"      => __( "Insert into $singular_lower", $text_domain ),
				"uploaded_to_this_item" => __( "Uploaded to this $singular_lower", $text_domain ),
				"items_list"            => __( "$plural list", $text_domain ),
				"items_list_navigation" => __( "$plural list navigation", $text_domain ),
				"filter_items_list"     => __( "Filter $plural_lower list", $text_domain ),
			);

			/**
			 * Hook for modifying labels
			 * @author Jim Barnes
			 * @since 1.0.0
			 * @param array $retval The default return value
			 * @param array $labels The labels array including the singular, plural and text_domain
			 * @return array
			 */
			$retval = apply_filters( 'ucf_location_labels', $retval, $labels );

			return $retval;
		}

		/**
		 * Returns the arguments for registering
		 * the custom post type
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $labels The labels array
		 * 						Defaults:
		 * 							( 'singular'    => 'Location' ),
		 * 							( 'plural'      => 'Locations' ),
		 * 							( 'text_domain' => 'ucf_location' )
		 * @return array
		 */
		public static function args( $labels ) {
			$taxonomies = apply_filters(
				'ucf_location_taxonomies',
				array(
					'category',
					'post_tag'
				)
			);

			$text_domain = isset( $labels['text_domain'] ) ? $labels['text_domain'] : 'ucf_location';

			$args = array(
				'label'               => __( 'Location', $text_domain ),
				'description'         => __( 'Locations', $text_domain ),
				'labels'              => self::labels( $labels ),
				'supports'            => array(),
				'taxonomies'          => $taxonomies,
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'rest_base'           => 'locations',
				'menu_position'       => 8,
				'menu_icon'           => 'dashicons-location',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post'
			);

			$args = apply_filters( 'ucf_location_args', $args );

			return $args;
		}

		/**
		 * Registers the ACF Fields for locations
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public static function register_acf_fields() {
			// Bail out if the function is missing for some reason.
			if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

			// Create the field array.
			// Will be filled one at a time
			$fields = array();

			/**
			 * Adds the ID field used by facilities
			 * @author Jim Barnes
			 * @since 1.0.0
			 */
			$fields[] = array(
				'key'          => 'ucf_location_id',
				'label'        => 'ID',
				'name'         => 'ucf_location_id',
				'type'         => 'text',
				'instructions' => 'The ID of the field. This should only be the unique ID as assigned by UCF Facilities.',
				'required'     => 1,
			);

			/**
			 * Adds the Abbreviation field
			 * @author Jim Barnes
			 * @since 1.0.0
			 */
			$fields[] = array(
				'key'          => 'ucf_location_abbr',
				'label'        => 'Abbreviation',
				'name'         => 'ucf_location_abbr',
				'type'         => 'text',
				'instructions' => 'The abbreviation of the building. This is commonly used when referring to building or room locations and should be provided.',
				'required'     => 0,
			);

			/**
			 * Adds the Google Map Point field
			 * which holds the lat and lng of the location
			 * @author Jim Barnes
			 * @since 1.0.0
			 */
			$fields[] = array(
				'key'          => 'ucf_location_lat_lng',
				'label'        => 'Google Map Point',
				'name'         => 'ucf_location_lat_lng',
				'type'         => 'group',
				'instructions' => 'The latitude and longitude of the location.',
				'required'     => 1,
				'layout'       => 'table',
				'sub_fields'   => array(
					array(
						'key' => 'ucf_location_lat',
						'label' => 'Latitude',
						'name' => 'ucf_location_lat',
						'type' => 'text',
						'instructions' => 'The latitude of the location.',
						'required' => 1
					),
					array(
						'key' => 'ucf_location_lng',
						'label' => 'Longitude',
						'name' => 'ucf_location_lng',
						'type' => 'text',
						'instructions' => 'The longitude of the location',
						'required' => 1
					)
				)
			);

			/**
			 * Adds the address field
			 * @author Jim Barnes
			 * @since 1.0.0
			 */
			$fields[] = array(
				'key' => 'ucf_location_address',
				'label' => 'Address',
				'name' => 'ucf_location_address',
				'type' => 'text',
				'instructions' => 'The full address of the location in the following format: 123 Name Dr., Orlando, FL 32816.',
				'required' => 0,
				'conditional_logic' => 0
			);

			/**
			 * Adds the fields to a field group
			 * @author Jim Barnes
			 * @since 1.0.0
			 */
			$field_group = array(
				'key'      => 'ucf_location_custom_fields',
				'title'    => 'Location Fields',
				'fields'   => $fields,
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'location'
						)
					)
				),
				'position' => 'normal',
				'style'    => 'default',
				'active'   => true
			);

			acf_add_local_field_group( $field_group );
		}

		/**
		 * Function that appends meta data onto the
		 * WP_Post object when it's queried
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param WP_Post $post The WP Post object
		 * @return WP_Post
		 */
		public static function location_append_meta( $post ) {
			/**
			 * We depend on ACF for gettings fields.
			 * If the function doesn't exist, return the post.
			 */
			if ( ! function_exists( 'get_fields' ) ) return $post;

			$meta = get_fields( $post->ID );
			$post->meta = self::reduce_post_meta( $meta );

			// See if we're integrating with the events plugin.
			if ( UCF_Location_Config::get_option_or_default( 'events_integration' ) ===  true
				&& UCF_Location_Utils::ucf_events_is_active()
				&& isset( $post->meta['ucf_location_id'] ) ) {

				$base_url         = UCF_Location_Config::get_option_or_default( 'events_base_url' );
				$default_feed     = UCF_Location_Config::get_option_or_default( 'events_default_feed' );
				$default_template = UCF_Location_Config::get_option_or_default( 'events_default_template' );
				$default_limit    = UCF_Location_Config::get_option_or_default( 'events_default_limit' );
				$params           = '?' . http_build_query( array(
					'location' => $post->meta['ucf_location_id']
				) );

				$request_url = trailingslashit( $base_url ) . trailingslashit( $default_feed ) . $params;

				$items = UCF_Events_Feed::get_events( array(
					'feed_url' => $request_url,
					'limit'    => $default_limit
				) );

				$args = array(
					'title' => 'Upcoming Events'
				);

				$markup = UCF_Events_Common::display_events( $items, $default_template, $args, 'shortcode', '' );

				$post->meta['events_markup'] = $markup;
			}

			return $post;
		}

		/**
		 * Adds meta data to all results returned for
		 * the location post type
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $posts The array of posts
		 * @param WP_Query $query The WP_Query object
		 * @return array
		 */
		public static function append_meta_to_results( $posts, $query ) {
			if ( $query->get( 'post_type' ) === 'location' ) {
				foreach( $posts as $post ) {
					$post = self::location_append_meta( $post );
				}
			}

			return $posts;
		}

		/**
		 * Reduces meta data to single values unless they are an array
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $meta_array The array of metadata to reduce
		 * @return array
		 */
		private static function reduce_post_meta( $meta_array ) {
			$retval = array();

			foreach( $meta_array as $key => $val ) {
				// Skip if the key starts with an underscore.
				if ( substr( $key, 0, 1 ) === '_' ) continue;

				if ( is_array( $val ) && count( $val ) === 1 ) {
					$retval[$key] = $val[0];
				} else {
					$retval[$key] = $val;
				}
			}

			return $retval;
		}
	}
}
