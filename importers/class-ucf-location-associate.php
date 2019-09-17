<?php
/**
 * Provides a command that associates
 * one map location with another based
 * on distance
 */
if ( ! class_exists( 'UCF_Location_Associate' ) ) {
	/**
	 * Class that provides the logic
	 * for associating one location
	 * with another based on distance
	 */
	class UCF_Location_Associate {
		private
			/**
			 * @var string The custom meta field
			 * to set the association in.
			 */
			$field = 'ucf_location_campus',
			/**
			 * @var int Proximity, in kilometers, two points
			 * must be to be associated.
			 */
			$distance = 5,
			/**
			 * @var array The location types that will
			 * be used as parent locations
			 */
			$parent_location_types = array( 'Location' ),
			/**
			 * @var array The location types that will
			 * be checked for association
			 */
			$children_location_types = array( 'Building', 'DiningLocation' ),
			/**
			 * @var bool When true, children locations
			 * can be assigned to multiple parents
			 */
			$multi_assoc = false,
			/**
			 * @var array The parent locations
			 */
			$parents = array(),
			/**
			 * @var array The children locations
			 */
			$children = array(),
			/**
			 * @var int The count of locations mapped
			 */
			$mapped_locations = 0;

		/**
		 * Constructs a new instance of UCF_Location_Associate
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param int $distance The distance two locations must be within to be associated.
		 * @param array $parents The location types that will be used as parent locations
		 * @param array $children The location types to be checked
		 * @param bool $multi_assoc When true, children locations can belong to multiple parent locations.
		 */
		public function __construct( $field = 'ucf_location_campus', $distance = 5, $parents = array( 'Location' ), $children = array( 'Building', 'DiningLocation' ), $multi_assoc = false ) {
			$this->field                   = $field;
			$this->distance                = $distance;
			$this->parent_location_types   = $parents;
			$this->children_location_types = $children;
		}

		/**
		 * Associate the locations
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public function import() {
			$this->parents  = $this->get_locations_by_terms( $this->parent_location_types );
			$this->children = $this->get_locations_by_terms( $this->children_location_types );

			foreach( $this->parents as $parent ) {
				$this->make_associations( $parent );
			}
		}

		/**
		 * Prints the stats of the job
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return string
		 */
		public function print_stats() {
			return "
Locations mapped: $this->mapped_locations
			";
		}

		/**
		 * Fills the parent locations array
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return array|false Returns an array of posts, or false if a wp error
		 */
		private function get_locations_by_terms( $terms ) {
			$args = array(
				'post_type'      => 'location',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'taxonomy' => 'location_type',
						'terms'    => $terms
					)
				)
			);

			$retval = get_posts( $args );

			if ( is_wp_error( $retval ) ) {
				return false;
			}

			return $retval;
		}

		/**
		 * Finds all the associations between
		 * parent posts and children.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param WP_Post $parent The parent post to test against
		 * @return void
		 */
		private function make_associations( $parent ) {
			$parent_lat = get_field( 'ucf_location_lat' );
			$parent_lng = get_field( 'ucf_location_lng' );

			if ( ! $parent_lat || ! $parent_lng ) {
				return;
			}

			// Loop through each child and associate if need be
			foreach( $this->children as $i => $child ) {
				$child_lat = get_field( 'ucf_location_lat' );
				$child_lng = get_field( 'ucf_location_lng' );

				$prox = $this->meets_threshold(
					array( $parent_lat, $parent_lng ),
					array( $child_lat, $child_lng ),
					$this->distance
				);

				if ( $prox ) {
					/**
					 * Update the specified field of the child post
					 * with the parent's post id
					 */
					update_field( $this->field, $parent->ID, $child->ID );

					/**
					 * There can't be multiple associations
					 * so unset this post from the child array
					 */
					if ( ! $this->multi_assoc ) {
						unset( $this->children[$i] );
					}
				}
			}
		}

		/**
		 * Determines if the two locations are within
		 * the specified proximity to each other
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $parent_loc The parent's location
		 * @param array $child_loc The child's location
		 * @param int $distance The distance to check
		 * @return bool
		 */
		private function meets_threshold( $parent_loc, $child_loc, $distance ) {
			$p = $parent_loc[0] - $child_loc[0];
			$c = $parent_loc[1] - $child_loc[1];
			$d = sqrt( $p * $p + $c * $c );

			if ( $c < $distance ) {
				return true;
			}

			return false;
		}
	}
}
