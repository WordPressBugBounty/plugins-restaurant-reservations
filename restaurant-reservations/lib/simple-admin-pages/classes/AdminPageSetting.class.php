<?php

/**
 * Register, display and save a setting on a custom admin menu
 *
 * All settings accept the following arguments in their constructor functions.
 *
 * $args = array(
 *		'id'			=> 'setting_id', 	// Unique id
 *		'title'			=> 'My Setting', 	// Title or label for the setting
 *		'description'	=> 'Description' 	// Help text description
 * 		'args'			=> array();			// Arguments to pass to WordPress's add_settings_field() function
 * );
 *
 * @since 1.0
 * @package Simple Admin Pages
 */

abstract class sapAdminPageSetting_2_7_0_rtb {

	// Page defaults
	public $id; // used in form fields and database to track and store setting
	public $page; // id of the menu/submenu page this setting is attached to
	public $tab; //  id of the tab (if any) for this setting
	public $title; // setting label
	public $description; // optional description of the setting
	public $value; // value of the setting, if a value exists
	public $disabled = false; // whether a setting should be disabled
	public $small = false; // whether a text input should use the small styling
	public $columns; // to be used for the number of columns for settings, like radio and checkbox, with lots of options/values
	public $conditional_on; // optional setting that this one is dependent on to diplay (ex. payment enabled for payment settings)
	public $conditional_on_value; // the required value of the dependent setting, if enabled
	public $conditional_display = true; // whether this setting should be displayed based on its conditional settings
	public $setting_type; // optional setting type, only displays for selected type
	public $setting_type_value; // the value for the type of setting that should be displayed
	

	/**
	 * An array of arguments accepted by add_settings_field.
	 * See: https://codex.wordpress.org/Function_Reference/add_settings_field
	 */
	public $args = array();

	// Array to store errors
	public $errors = array();

	/**
	 * Position in section
	 *
	 * An array with two elements describing where this setting should
	 * be placed in its section. The first element describes a position
	 * and the second (optional) element identifies the id of an
	 * existing setting. Examples:
	 *
	 * array( 'bottom' ) // Default. bottom of section
	 * array( 'top' ) // top of section
	 * array( 'before', 'my-setting' ) // before a specific setting
	 * array( 'after', 'my-setting' ) // after a specific setting
	 *
	 * This setting is intended for use when you have to hook in after
	 * the settings page has been defined, such as adding a new setting
	 * from a third-party plugin.
	 */
	public $position;

	/**
	 * Function to use when sanitizing the data
	 *
	 * We set this to a strict sanitization function as a default, but a
	 * setting should override this in an extended class when needed.
	 *
	 * @since 1.0
	 */
	public $sanitize_callback = 'sanitize_text_field';

	/**
	 * Scripts that must be loaded for this component
	 * @since 2.0.a.4
	 */
	public $scripts = array(
		/**
		 * Example
		 * See: http://codex.wordpress.org/Function_Reference/wp_enqueue_script
		 *
		'handle' => array(
			'path'			=> 'path/from/simple-admin-pages/file.js',
			'dependencies'	=> array( 'jquery' ),
			'version'		=> '3.5.0',
			'footer'		=> true,
		),
		 */
	);

	/**
	 * Styles that must be loaded for this component
	 * @since 2.0.a.4
	 */
	public $styles = array(
		/**
		 * Example
		 * See: http://codex.wordpress.org/Function_Reference/wp_enqueue_style
		 *
		'handle' => array(
			'path'			=> 'path/from/simple-admin-pages/file.css',
			'dependencies'	=> 'array( 'another-handle')', // or empty string
			'version'		=> '3.5.0',
			'media'			=> null,
		),
		 */
	);

	/**
	 * Translateable strings required for this component
	 *
	 * Settings classes which require translateable strings should be
	 * defined with string id's pointing to null values. The actual
	 * strings should be passed with the $sap->add_setting() call.
	 *
	 * @since 2.0.a.8
	 */
	public $strings = array(
		/**
		 * Example
		 *
		 'string_id' => null
		 */
	);

	// Acceptable option values, for defined-choice options
	public $options = array();

	// The default option value that should be selected for defined-choice options
	public $default;
	
	/**
	 * Initialize the setting
	 *
	 * By default, every setting takes an id, title and description in the $args
	 * array.
	 *
	 * @since 1.0
	 */
	public function __construct( $args ) {

		// Parse the values passed
		$this->parse_args( $args );

		// Get any existing value
		$this->set_value();

		// Set display conditional on other options
		$this->set_conditional_display();

		// Set display conditional on setting type
		$this->set_setting_type_display();

		// Check for missing data
		$this->missing_data();
	}

	/**
	 * Parse the arguments passed in the construction and assign them to
	 * internal variables. This function will be overwritten for most subclasses
	 * @since 1.0
	 */
	private function parse_args( $args ) {
		foreach ( $args as $key => $val ) {
			switch ( $key ) {

				case 'id' :
					$this->{$key} = esc_attr( $val );

				default :
					if( property_exists( $this, $key ) && is_array( $this->{$key} ) ) {
						$this->{$key} = array_replace( $this->{$key}, $val );
					}
					else {
						$this->{$key} = $val;
					}

			}
		}
	}

	/**
	 * Check for missing data when setup.
	 * @since 1.0
	 */
	private function missing_data() {

		$error_type = 'missing_data';

		// Required fields
		if ( empty( $this->id ) ) {
			$this->set_error(
				array(
					'type'		=> $error_type,
					'data'		=> 'id'
				)
			);
		}
		if ( empty( $this->title ) ) {
			$this->set_error(
				array(
					'type'		=> $error_type,
					'data'		=> 'title'
				)
			);
		}

		// Check for strings
		foreach ( $this->strings as $id => $string ) {

			if ( $string === null ) {
				$this->set_error(
					array(
						'type'		=> $error_type,
						'data'		=> 'string: ' . $id,
					)
				);
			}
		}
	}

	/**
	 * Set a value
	 * @since 2.0
	 */
	public function set_value( $val = null ) {

		if ( $val === null ) {
			$option_group_value = get_option( $this->page );
			$val = isset( $option_group_value[ $this->id ] ) ? $option_group_value[ $this->id ] : '';
		}

		$this->value = $this->esc_value( $val );
	}

	/**
	 * Escape the value to display it in text fields and other input fields
	 *
	 * We use esc_attr() here so that the default is quite strict, but other
	 * setting types should override this function with the appropriate escape
	 * function. See: http://codex.wordpress.org/Data_Validation
	 *
	 * @since 1.0
	 */
	public function esc_value( $val ) {

		if ( is_array( $val ) ) { return array_map( 'esc_attr', $val );} 
		
		return esc_attr( $val );
	}

	/**
	 * Determines whether this setting should be displayed, based on its
	 * conditional conditions, if any.
	 *
	 * @since 2.6
	 */
	public function set_conditional_display() {

		if ( empty( $this->conditional_on ) ) { return; }

		$option_group_value = get_option( $this->page );

		if ( empty( $option_group_value ) ) { return; }

		// Turn conditional_on and conditional_on_value into arrays.
		// If conditional_on_value is an array and conditional_on isn't, then turn it into a multidimensional array
		$this->conditional_on_value = is_array( $this->conditional_on ) ? (array) $this->conditional_on_value : array( $this->conditional_on_value );
		$this->conditional_on = is_array( $this->conditional_on ) ? $this->conditional_on : (array) $this->conditional_on;

		foreach ( $this->conditional_on as $key => $conditional_on ) {

			// If one condition fails, stop
			if ( empty( $this->conditional_display ) ) { break; }

			$option_group_value[ $conditional_on ] = isset( $option_group_value[ $conditional_on ] ) ? $option_group_value[ $conditional_on ] : false;

			if ( is_array( $option_group_value[ $conditional_on ] ) ) {

				$this->conditional_display = is_array( $this->conditional_on_value[ $key ] ) ? ! empty( array_intersect( $this->conditional_on_value[ $key ], $option_group_value[ $conditional_on ] ) ) : in_array( $this->conditional_on_value[ $key ], $option_group_value[ $conditional_on ] );
			}
			else {

				$this->conditional_display = is_array( $this->conditional_on_value[ $key ] ) ? in_array( $option_group_value[ $conditional_on ], $this->conditional_on_value[ $key ] )  : ( $this->conditional_on_value[ $key ] == $option_group_value[ $conditional_on ] ? true : false );
			}
		}

		if ( ! empty( $this->conditional_display ) ) { return; }

		if ( ! empty( $this->args['class'] ) ) {

			$this->args['class'] .= ' sap-hidden';
		}
		else {

			$this->args['class'] = 'sap-hidden';
		}
	}

	/**
	 * Prints conditional data tags within the input element if necessary
	 *
	 * @since 2.6
	 */
	public function print_conditional_data() {

		if ( empty( $this->conditional_on ) ) { return; }

		echo 'data-conditional_on="' . esc_attr( json_encode( $this->conditional_on ) ) . '"';
		echo 'data-conditional_on_value="' . esc_attr( json_encode( $this->conditional_on_value ) ) . '"';
	}

	/**
	 * Determines whether this setting should be displayed, based on its
	 * setting type, if any.
	 *
	 * @since 2.6.21.rtb
	 */
	public function set_setting_type_display() {

		$selected_setting_type = isset( $_GET['setting_type'] ) ? $_GET['setting_type'] : '';
		$selected_setting_type_value = isset( $_GET['setting_type_value'] ) ? $_GET['setting_type_value'] : '';

		$setting_types = is_array( $this->setting_type ) ? $this->setting_type : ( isset( $this->setting_type ) ? explode( ',', $this->setting_type ) : array() );
		$setting_type_values = is_array( $this->setting_type_value ) ? $this->setting_type_value : ( isset( $this->setting_type_value ) ? explode( ',', $this->setting_type_value ) : array() );

		if ( empty( $selected_setting_type ) and empty( $this->setting_type ) ) { return; }
		elseif ( empty( $selected_setting_type ) ) {

			foreach ( $setting_type_values as $value ) {

				if ( ! empty( $value ) ) {
	
					$this->args['class'] = ! empty( $this->args['class'] ) ? $this->args['class'] . ' sap-hidden' : 'sap-hidden';
				}
			}
		}
		elseif ( empty( $this->setting_type ) ) {

			$this->args['class'] = ! empty( $this->args['class'] ) ? $this->args['class'] . ' sap-hidden' : 'sap-hidden';
		}
		else {

			foreach ( $setting_types as $index => $setting_type ) {

				if ( $selected_setting_type != $setting_type or empty( $setting_type_values[ $index ] ) or $selected_setting_type_value != $setting_type_values[ $index ] ) {
	
					$this->args['class'] = ! empty( $this->args['class'] ) ? $this->args['class'] . ' sap-hidden' : 'sap-hidden';
				}
			}
		}
	}

	/**
	 * Prints setting type data tags within the input element if necessary
	 *
	 * @since 2.6.21.rtb
	 */
	public function print_setting_type_data() {

		if ( empty( $this->setting_type ) ) { return; }

		echo 'data-setting_type="' . esc_attr( is_array( $this->setting_type ) ? implode( ',', $this->setting_type ) : $this->setting_type ) . '"';
		echo 'data-setting_type_value="' . esc_attr( is_array( $this->setting_type_value ) ? implode( ',', $this->setting_type_value ) : $this->setting_type_value ) . '"';
	}

	/**
	 * Wrapper for the sanitization callback function.
	 *
	 * This just reduces code duplication for child classes that need a custom
	 * callback function.
	 * @since 1.0
	 */
	public function sanitize_callback_wrapper( $value ) {
		return call_user_func( $this->sanitize_callback, $value );
	}

	/**
	 * Display this setting
	 * @since 1.0
	 */
	abstract public function display_setting();

	/**
	 * Display a description for this setting
	 * @since 1.0
	 */
	public function display_description() {

		if ( empty( $this->description ) ) { return; }

		?>

		<p class="description<?php echo ( $this->disabled ? ' disabled' : ''); ?>"><?php echo wp_kses_post( $this->description ); ?></p>

		<?php
	}

	/**
	 * Display a disabled image for this section and possibly a link to upgrade
	 * @since 2.0
	 */
	public function display_disabled() {

		if ( $this->disabled and isset($this->disabled_image) ) {

		?>

			<?php echo ( isset($this->purchase_link ) ? "<a href='" . esc_url( $this->purchase_link ) . "'>" : '' ); ?>
			<div class="disabled"><img src='<?php echo esc_url( $this->disabled_image ); ?>;' /></div>
			<?php echo ( isset($this->purchase_link ) ? "</a>" : '' ); ?>

		<?php

		}
	}

	/**
	 * Generate an option input field name, using the grouped schema:
	 * "page[option_name]"
	 * @since 1.2
	 */
	public function get_input_name() {
		return esc_attr( $this->page ) . '[' . esc_attr( $this->id ) . ']';
	}

	/**
	 * Get the default value for a setting if value is currently empty
	 *
	 * @since 2.4.1
	 */
	public function get_default_setting( $default_override = null ) {

		if ( ! empty( $this->default ) ) { return $this->default; }

		return $default_override !== null ? $default_override : $this->value;
	}

	/**
	 * Add and register this setting
	 *
	 * @since 1.0
	 */
	public function add_settings_field( $section_id ) {

		// If no sanitization callback exists, don't register the setting.
		if ( !$this->has_sanitize_callback() ) {
			return;
		}

		add_settings_field(
			$this->id,
			$this->title,
			array( $this, 'display_setting' ),
			$this->tab,
			$section_id,
			$this->args
		);

	}

	/**
	 * Check if this field has a sanitization callback set
	 * @since 1.2
	 */
	public function has_sanitize_callback() {
		if ( isset( $this->sanitize_callback ) && trim( $this->sanitize_callback ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Set an error
	 * @since 1.0
	 */
	public function set_error( $error ) {
		$this->errors[] = array_merge(
			$error,
			array(
				'class'		=> get_class( $this ),
				'id'		=> $this->id,
				'backtrace'	=> debug_backtrace()
			)
		);
	}

	/**
	 * Check if a setting has a position
	 * @since 2.0.a.9
	 */
	public function has_position() {
		return !empty( $this->position ) && is_array( $this->position ) && !empty( $this->position[0] );
	}
}
