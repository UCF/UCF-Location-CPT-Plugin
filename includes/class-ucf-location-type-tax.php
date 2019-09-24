<?php
/**
 * Taxonomy for storing taxonomy types
 */
if ( ! class_exists( 'UCF_Location_Type_Taxonomy' ) ) {
	class UCF_Location_Type_Taxonomy {
		/**
		 * Registers the location_type custom
		 * taxonomy.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @return void
		 */
		public static function register_taxonomy() {
			$labels = array(
				'singular'    => 'Location Type',
				'plural'      => 'Location Types',
				'text_domain' => 'ucf_location'
			);

			$labels = apply_filters( 'ucf_location_type_label_parts', $labels );

			register_taxonomy( 'location_type', array(), self::args( $labels ) );
		}

		/**
		 * Returns the default labels
		 * for the location_type taxonomy.
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param array $labels The label array
		 * @return array The label array
		 */
		private static function labels( $labels ) {
			$singular       = $labels['singular'];
			$singular_lower = strtolower( $singular );
			$plural         = $labels['plural'];
			$plural_lower   = strtolower( $plural );
			$text_domain    = $labels['text_domain'];

			$retval = array(
				'name'                       => _x( $plural, 'Taxonomy General Name', $text_domain ),
				'singular_name'              => _x( $singular, 'Taxonomy Singular Name', $text_domain ),
				'menu_name'                  => __( $plural, $text_domain ),
				'all_items'                  => __( "All $plural", $text_domain ),
				'parent_item'                => __( "Parent $singular", $text_domain ),
				'parent_item_colon'          => __( "Parent $singular:", $text_domain ),
				'new_item_name'              => __( "New $singular Name", $text_domain ),
				'add_new_item'               => __( "Add New $singular", $text_domain ),
				'edit_item'                  => __( "Edit $singular", $text_domain ),
				'update_item'                => __( "Update $singular", $text_domain ),
				'view_item'                  => __( "View $singular", $text_domain ),
				'separate_items_with_commas' => __( "Separate $plural_lower with commas", $text_domain ),
				'add_or_remove_items'        => __( "Add or remove $plural_lower", $text_domain ),
				'choose_from_most_used'      => __( "Choose from the most used", $text_domain ),
				'popular_items'              => __( "Popular $plural", $text_domain ),
				'search_items'               => __( "Search $plural", $text_domain ),
				'not_found'                  => __( "Not Found", $text_domain ),
				'no_terms'                   => __( "No $plural_lower", $text_domain ),
				'items_list'                 => __( "$plural list", $text_domain ),
				'items_list_navigation'      => __( "$plural list navigation", $text_domain )
			);

			$retval = apply_filters( 'ucf_location_type_labels', $retval, $labels );

			return $retval;
		}

		/**
		 * Returns the argument array
		 * @author Jim Barnes
		 * @since 0.1.0
		 * @param array $labels The label array
		 * @return array The argument array
		 */
		private static function args( $labels ) {
			$retval = array(
				'labels'                => self::labels( $labels ),
				'hierarchical'          => true,
				'public'                => true,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'show_in_nav_menus'     => true,
				'show_tagcloud'         => true,
				'show_in_rest'          => true,
				'rest_base'             => 'location_types',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
				'rewrite'               => array( 'slug' => 'location-type' )
			);

			$retval = apply_filters( 'ucf_location_type_args', $retval, $labels );

			return $retval;
		}
	}
}
