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
				'layout'         => 'location',
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

		$parts = array();

		foreach ( $atts as $key => $val  ) {
			$parts[] = $key . "=\"" . $val . "\"";
		}

		$att_str = implode( ' ', $parts );

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
		<?php echo do_shortcode('[ucf-post-list ' . $att_str . ']'); ?>
	<?php
		return ob_get_clean();
	}
}

endif;
