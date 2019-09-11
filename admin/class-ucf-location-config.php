<?php
/**
 * Defines the configuration settings
 * for the plugin
 */
if ( ! class_exists( 'UCF_Location_Config' ) ) {
	/**
	 * Class that handles the configuration
	 * of the Location custom post type
	 */
	class UCF_Location_Config {
		public static
			/**
			 * @var string The option prefix used
			 */
			$options_prefix = 'ucf_location_',
			/**
			 * @var array The option default values
			 */
			$option_defaults = array(
				'events_integration'      => true,
				'events_base_url'         => 'https://events.ucf.edu',
				'events_default_feed'     => 'this-week',
				'events_default_template' => ''
			);

		/**
		 * Creates options via the WP Options API that are utilized by the
		 * plugin. Intended to be run on plugin activation.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public static function add_options() {
			$defaults = self::$option_defaults;

			add_option( self::$options_prefix . 'events_integration', $defaults['events_integration'] );
			add_option( self::$options_prefix . 'events_base_url', $defaults['events_base_url'] );
			add_option( self::$options_prefix . 'events_default_feed', $defaults['events_default_feed'] );
			add_option( self::$options_prefix . 'events_default_template', $defaults['events_default_template'] );
		}

		/**
		 * Deletes options via the WP Options API that are utilized by the
		 * plugin. Intended to be run on plugin uninstallation.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public static function delete_options() {
			delete_option( self::$options_prefix . 'events_integration' );
			delete_option( self::$options_prefix . 'events_base_url' );
			delete_option( self::$options_prefix . 'events_default_feed' );
			delete_option( self::$options_prefix . 'events_default_template' );
		}

		/**
		 * Returns a list of default plugin options. Applied any overridden
		 * default values set within the options page.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return array
		 */
		public static function get_option_defaults() {
			$defaults = self::$option_defaults;

			$configurable_defaults = array(
				'events_integration'      => get_option( self::$options_prefix . 'events_integration', $defaults['events_integration'] ),
				'events_base_url'         => get_option( self::$options_prefix . 'events_base_url', $defaults['events_base_url'] ),
				'events_default_feed'     => get_option( self::$options_prefix . 'events_default_feed', $defaults['events_default_feed'] ),
				'events_default_template' => get_option( self::$options_prefix . 'events_default_template', $defaults['events_default_template'] )
			);

			$configurable_defaults = self::format_options( $configurable_defaults );
			$defaults = array_merge( $defaults, $configurable_defaults );

			return $defaults;
		}

		/**
		 * Returns an array with plugin defaults applied
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $list The list of options to apply defaults to
		 * @param boolean $list_keys_only Modifies results to only return array key
		 * 								  values present in $list.
		 * @return array
		 */
		public static function apply_option_defaults( $list, $list_keys_only=false ) {
			$defaults = self::get_option_defaults();
			$options = array();

			if ( $list_keys_only ) {
				foreach( $list as $key => $val ) {
					$options[$key] = ! empty( $val ) ? $val : $defaults[$key];
				}
			} else {
				$options = array_merge( $defaults, $list );
			}

			return $options;
		}

		/**
		 * Performs typecasting and sanitization on an array of plugin options.
		 * @author Jim Barnes
		 * @param array $list The list of options to format.
		 * @return array
		 */
		public static function format_options( $list ) {
			foreach( $list as $key => $val ) {
				switch( $key ) {
					case 'events_integration':
						$list[$key] = filter_var( $val, FILTER_VALIDATE_BOOLEAN );
						break;
					default:
						break;
				}
			}

			return $list;
		}

		/**
		 * Applies formatting to a single options. Intended to be passed to the
		 * option_{$option} hook.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param mixed $value The value to be formatted
		 * @param string $option_name The name of the option being formatted.
		 * @return mixed
		 */
		public static function format_option( $value, $option_name ) {
			$option_name_no_prefix = str_replace( self::$options_prefix, '', $option_name );
			$option_formatted = self::format_options( array( $option_name_no_prefix => $value ) );

			return $option_formatted[$option_name_no_prefix];
		}

		/**
		 * Adds filters for plugin options that apply
		 * our formatting rules to option values.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public static function add_option_formatting_filters() {
			$defaults = self::$option_defaults;

			foreach( $defaults as $option => $default ) {
				$option_name = self::$options_prefix . $option;
				add_filter( "option_{$option_name}", array( 'UCF_Location_Config', 'format_option' ), 10, 2 );
			}
		}

		/**
		 * Utility method for returning an option from the WP Options API
		 * or a plugin option default.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param string $option_name The name of the option to retrieve.
		 * @return mixed
		 */
		public static function get_option_or_default( $option_name ) {
			// Handle $option_name passed in with or without self::$options_prefix applied:
			$option_name_no_prefix = str_replace( self::$options_prefix, '', $option_name );
			$option_name           = self::$options_prefix . $option_name_no_prefix;
			$defaults              = self::get_option_defaults();

			$default = isset( $defaults[$option_name_no_prefix] ) ? $defaults[$option_name_no_prefix] : null;

			return get_option( $option_name, $default );
		}

		/**
		 * Initializes setting registration with the Settings API.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void
		 */
		public static function settings_init() {
			$settings_slug = 'ucf_location_settings';
			$defaults      = self::$option_defaults;
			$display_fn    = array( 'UCF_Location_Config', 'display_settings_field' );

			// Register all default settings.
			foreach( $defaults as $key => $val ) {
				register_setting(
					$settings_slug,
					self::$options_prefix . $key
				);
			}

			add_settings_section(
				'ucf_location_events',
				'Events Settings',
				null,
				$settings_slug
			);

			if ( UCF_Location_Utils::ucf_events_is_active() ) {
				add_settings_field(
					self::$options_prefix . 'events_integration',
					'Enable Events Integration',
					$display_fn,
					$settings_slug,
					'ucf_location_events',
					array(
						'label_for'   => self::$options_prefix . 'events_integration',
						'description' => 'When checked, the events integration will be active, adding additional fields and functionality for displaying events happening at locations.',
						'type'        => 'checkbox'
					)
				);

				add_settings_field(
					self::$options_prefix . 'events_base_url',
					'Events Base URL',
					$display_fn,
					$settings_slug,
					'ucf_location_events',
					array(
						'label_for'   => self::$options_prefix . 'events_base_url',
						'description' => 'The base URL of the UCF Events system.',
						'type'        => 'url'
					)
				);

				$feed_options = array(
					'today'      => 'Today',
					'this-week'  => 'This Week',
					'this-month' => 'This Month',
					'this-year'  => 'This Year',
					'upcoming'   => 'Upcoming'
				);

				add_settings_field(
					self::$options_prefix . 'events_default_feed',
					'Default Feed',
					$display_fn,
					$settings_slug,
					'ucf_location_events',
					array(
						'label_for'   => self::$options_prefix . 'events_default_feed',
						'description' => 'The default feed to use when fetching events.',
						'type'        => 'select',
						'options'     => $feed_options
					)
				);

				$layouts = UCF_Events_Config::get_layouts();

				add_settings_field(
					self::$options_prefix . 'events_default_template',
					'Default Layout',
					$display_fn,
					$settings_slug,
					'ucf_location_events',
					array(
						'label_for'   => self::$options_prefix . 'events_default_template',
						'description' => 'The default layout to use when generating the events list.',
						'type'        => 'select',
						'options'     => $layouts
					)
				);

			} else {
				add_settings_field(
					null,
					'Events Integration Requirements',
					$display_fn,
					$settings_slug,
					'ucf_location_events',
					array(
						'label_for'   => null,
						'description' => 'The events integration requires the <a href="" target="blank">UCF Events Plugin</a> to be installed and active.',
						'type'        => 'message'
					)
				);
			}
		}

		/**
		 * Displays an individual settings's field markup.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array The field's argument array
		 * @return string The formatted html of the field
		 */
		public static function display_settings_field( $args ) {
			$option_name = $args['label_for'];
			$description = $args['description'];
			$field_type  = $args['type'];
			$options     = isset( $args['options'] ) ? $args['options'] : null;
			$current_val = self::get_option_or_default( $option_name );
			$markup      = '';

			switch( $field_type ) {
				case 'checkbox':
					ob_start();
				?>
					<input type="checkbox" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>"<?php echo ( $current_val === true ) ? ' checked' : ''; ?>>
					<p class="description">
						<?php echo $description; ?>
					</p>
				<?php
					$markup = ob_get_clean();
					break;
				case 'checkbox-list':
					ob_start();
					if ( ! empty( $options ) ) :
				?>
					<p class="description"><strong><?php echo $description; ?></strong></p>
					<ul class="catagorychecklist">
				<?php
						foreach( $options as $val => $text ) :
				?>
					<li>
						<label for="<?php echo $option_name . '_' . $val; ?>">
						<input type="checkbox" id="<?php echo $option_name . '_' . $val; ?>" name="<?php echo $option_name; ?>[]"<?php echo ( in_array( $val, $current_val ) ) ? ' checked' : ''; ?> value="<?php echo $val; ?>">
							<?php echo $text; ?>
						</label>
					</li>
				<?php
						endforeach;
				?>
					</ul>
				<?php
					else:
				?>
					<p class="description" style="color: red;">
						There was an error loading the options.
					</p>
				<?php
					endif;
					$markup = ob_get_clean();
					break;
				case 'select':
					ob_start();
				?>
					<select id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>">
					<?php foreach( $options as $val => $text ) : ?>
						<option value="<?php echo $val; ?>"<?php echo ( $val === $current_val ) ? ' selected' : ''; ?>>
							<?php echo $text; ?>
						</option>
					<?php endforeach; ?>
					</select>
				<?php
					$markup = ob_get_clean();
					break;
				case 'number':
				case 'date':
				case 'email':
				case 'month':
				case 'tel':
				case 'text':
				case 'text':
				case 'time':
				case 'url':
					ob_start();
				?>
					<input type="<?php echo $field_type; ?>" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" value="<?php echo $current_val; ?>">
					<p class="description">
						<?php echo $description; ?>
					</p>
				<?php
					$markup = ob_get_clean();
					break;
				case 'message':
					ob_start();
				?>
					<div style="background-color: #fff; color: #000; padding: 1rem 2rem; border: 1px solid #acacac;">
						<p><?php echo $description; ?></p>
					</div>
				<?php
					$markup = ob_get_clean();
					break;
				default:
				?>
					<input type="text" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" value="<?php echo $current_val; ?>">
					<p class="description">
						<?php echo $description; ?>
					</p>
				<?php
					$markup = ob_get_clean();
					break;
			}

			echo $markup;
		}

		/**
		 * Registers the settings page to display in the WordPress admin.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return string The resulting page's hook suffix.
		 */
		public static function add_options_page() {
			$page_title = 'UCF Location Settings';
			$menu_title = 'UCF Location';
			$capability = 'manage_options';
			$menu_slug  = 'ucf_location_settings';
			$callback   = array( 'UCF_Location_Config', 'options_page_html' );

			return add_options_page(
				$page_title,
				$menu_title,
				$capability,
				$menu_slug,
				$callback
			);
		}

		/**
		 * Provides the HTML for the UCF Location
		 * settings page.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return void Output is echoed.
		 */
		public static function options_page_html() {
			ob_start();
		?>
			<div class="wrap">
				<h1><?php echo get_admin_page_title(); ?></h1>
				<form method="post" action="options.php">
					<?php
						settings_fields( 'ucf_location_settings' );
						do_settings_sections( 'ucf_location_settings' );
						submit_button();
					?>
				</form>
			</div>
		<?php
			echo ob_get_clean();
		}
	}
}
