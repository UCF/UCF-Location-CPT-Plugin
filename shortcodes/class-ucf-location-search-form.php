<?php
/**
 * Provides for a traditional form for searching
 * for locations.
 */
if ( ! class_exists( 'UCF_Location_Search_Shortcode' ) ) :

class UCF_Location_Search_Shortcode {
	/**
	 * Registers the `location-search` shortcode
	 * @author Jim Barnes
	 * @since 0.3.0
	 */
	public static function register_shortcode() {
		add_shortcode( 'location-search', array( 'UCF_Location_Search_Shortcode', 'callback' ) );
	}

	/**
	 * The callback function when the `location-search`
	 * shortcode is used.
	 * @author Jim Barnes
	 * @since 0.3.0
	 * @param array $atts The array of attributes passed into the shortcode
	 * @param string $content The content contained within the shortcode
	 * @return string The HTML output as a string
	 */
	public static function callback( $atts, $content = '' ) {
		$q = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : null;

		$atts = shortcode_atts(
			array(
				'post_type'      => 'location',
				'order'          => 'ASC',
				'order_by'       => 'post_title',
				'posts_per_page' => -1,
				'posts_per_row'  => 2
			),
			$atts
		);

		// Add the query
		if ( ! empty( $q ) ) $atts['s'] = $q;

		$posts = get_posts( $atts );

		ob_start();
	?>
		<form action="." type="get">
			<div class="input-group mb-4">
				<label class="sr-only" for="location-search">Search for UCF locations</label>
				<input type="text" class="search-query form-control" id="location-search" name="q" placeholder="Library" aria-label="Search UCF Locations"<?php echo ( ! empty( $q ) ) ? ' value="' . $q . '"' : '';?>>
				<span class="input-group-btn">
					<button class="btn btn-primary" type="submit">
						<span class="fa fa-search" aria-labelledby="search-btn-text"></span>
						<span id="search-btn-text" class="hidden-sm-down">Search</span>
					</button>
				</span>
			</div>
		</form>
		<div class="location-list">
			<?php foreach( $posts as $idx => $post ) :
				$address   = get_field( 'ucf_location_address', $post->ID );
				$campus    = get_field( 'ucf_location_campus', $post->ID );
				$thumbnail = get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'img-fluid' ) );
			?>
			<div class="card<?php echo ( $idx === 0 ) ? '' : ' border-top-0'; ?>">
				<div class="card-block">
					<div class="row">
						<div class="col-8 col-md-9">
							<a class="d-block h5 text-complementary mb-4" href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $post->post_title; ?></a>
							<div class="row">
								<div class="col-md-7 col-md-push-5">
									<?php if ( $address ) : ?>
									<span class="d-block text-uppercase text-muted font-weight-light small">Address</span>
									<p><?php echo $address; ?></p>
									<?php endif; ?>
								</div> <!-- End address block -->
								<div class="col-md-5 col-md-pull-7">
									<?php if ( $campus ) : ?>
									<span class="d-block text-uppercase text-mited font-weight-light small">Campus</span>
									<p><?php echo $campus->post_title; ?>
									<?php endif; ?>
								</div> <!-- End campus block -->
							</div> <!-- End meta block (row) -->
						</div> <!-- End meta column -->
						<?php if ( $thumbnail ) : ?>
						<div class="col-4 col-md-3">
							<?php echo $thumbnail; ?>
						</div>
						<?php endif; ?>
					</div> <!-- End layout row -->
				</div> <!-- End card block -->
			</div> <!-- End card -->
			<?php endforeach; ?>
		</div>
	<?php
		return ob_get_clean();
	}
}

endif;
