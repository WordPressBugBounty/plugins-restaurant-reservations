<?php

/**
 * Add a setting to Simple Admin Pages to select a list and define
 * merge fields for that list.
 *
 * This class is modelled on AdminPageSetting.class.php in the Simple
 * Admin Pages library. It should work just like an extended class, but
 * due to the way the library embeds the version into the class name,
 * that could cause problems if the library is updated in the parent
 * plugin.
 *
 * See: https://github.com/NateWr/simple-admin-pages
 *
 */

class mcfrtbAdminPageSettingMcListMerge_2_7_0_rtb {

	// Page defaults
	public $id; // used in form fields and database to track and store setting
	public $page; // id of the menu/submenu page this setting is attached to
	public $tab; //  id of the tab (if any) for this setting
	public $title; // setting label
	public $description; // optional description of the setting
	public $value; // value of the setting, if a value exists
	public $disabled = false; // whether a setting should be disabled
	public $placeholder; // optional placeholder for the API key field 
	public $string_loading; // string to display while loading the list options
	public $conditional_on; // optional setting that this one is dependent on to diplay (ex. payment enabled for payment settings)
	public $conditional_on_value; // the required value of the dependent setting, if enabled
	public $conditional_display = true; // whether this setting should be displayed based on its conditional settings
	public $setting_type; // optional setting type, only displays for selected type
	public $setting_type_value; // the value for the type of setting that should be displayed

	// For saving any args used internally
	public $args = array();
	
	/**
	 * Scripts and styles to load for this component
	 * (not used but required as part of the library)
	 */
	public $scripts = array();
	public $styles = array();

	/**
	 * List of fields available for merging
	 */
	public $fields = array();

	/**
	 * Initialize the setting
	 */
	public function __construct( $args ) {

		// Parse the values passed
		$this->parse_args( $args );

		// Get any existing value
		$this->set_value();

		// Set an error if the object is missing necessary data
		if ( $this->missing_data() ) {
			$this->set_error();
		}
	}
	
	/**
	 * Parse the arguments passed in the construction and assign them to
	 * internal variables. This function will be overwritten for most subclasses
	 */
	private function parse_args( $args ) {
		foreach ( $args as $key => $val ) {
			switch ( $key ) {

				case 'id' :
					$this->{$key} = esc_attr( $val );

				case 'title' :
					$this->{$key} = esc_attr( $val );

				case 'fields' :
					$this->{$key} = is_array( $val ) ? $val : array();

				default :
					$this->{$key} = $val;

			}
		}
	}

	/**
	 * Check for missing data when setup.
	 */
	private function missing_data() {

		// Required fields
		if ( empty( $this->id ) ) {
			$this->set_error(
				array(
					'type'		=> 'missing_data',
					'data'		=> 'id'
				)
			);
		}
		if ( empty( $this->title ) ) {
			$this->set_error(
				array(
					'type'		=> 'missing_data',
					'data'		=> 'title'
				)
			);
		}
		if ( empty( $this->fields ) ) {
			$this->set_error(
				array(
					'type'		=> 'missing_data',
					'data'		=> 'fields'
				)
			);
		}
		if ( empty( $this->string_loading ) ) {
			$this->set_error(
				array(
					'type'		=> 'missing_data',
					'data'		=> 'string_loading'
				)
			);
		}
	}

	/**
	 * Set a value
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
	 */
	public function esc_value( $val ) {

		$value = array(
			'list'		=> '',
			'fields'	=> array(),
		);

		if ( empty( $val ) || empty( $val['list'] ) ) {
			return $value;
		}

		$value['list'] = esc_attr( $val['list'] );

		// Escape the id/title of each merge field
		foreach( $val['fields'] as $id => $val ) {
			$value['fields'][$id] = esc_html( $val );
		}

		return $value;
	}

	/**
	 * Display this setting
	 */
	public function display_setting() {

		?>

		<fieldset <?php $this->print_conditional_data(); ?> <?php $this->print_setting_type_data(); ?>>

			<span class="mcf-list-select"></span>

			<span class="mcf-sap_loading">
				<span class="spinner"></span>
				<span><?php echo esc_html( $this->string_loading ); ?></span>
			</span>

			<?php $this->display_description(); ?>

			<div id="mcfrtb-merge-controls" data-input-name="<?php echo esc_attr( $this->get_input_name() ); ?>"></div>

		</fieldset>

		<?php
	}

	/**
	 * Display a description for this setting
	 */
	public function display_description() {

		if ( !empty( $this->description ) ) : ?>
		
			<p class="description"><?php echo wp_kses_post( $this->description ); ?></p>
			
		<?php endif;
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

		$option_group_value[ $this->conditional_on ] = isset( $option_group_value[ $this->conditional_on ] ) ? $option_group_value[ $this->conditional_on ] : false;

		if ( is_array( $option_group_value[ $this->conditional_on ] ) ) {

			$this->conditional_display = in_array( $this->conditional_on_value, $option_group_value[ $this->conditional_on ] );
		}

		$this->conditional_display = $this->conditional_on_value == $option_group_value[ $this->conditional_on ] ? true : false;

		if ( $this->conditional_display ) { return; }

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

		echo 'data-conditional_on="' . esc_attr( $this->conditional_on ) . '"';
		echo 'data-conditional_on_value="' . esc_attr( $this->conditional_on_value ) . '"';
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
	 * Generate an option input field name, using the grouped schema.
	 */
	public function get_input_name() {
		return esc_attr( $this->page ) . '[' . esc_attr( $this->id ) . ']';
	}


	/**
	 * Sanitize the array of text inputs for this setting
	 */
	public function sanitize_callback_wrapper( $values ) {

		$output = array(
			'list'		=> '',
			'fields'	=> array(),
		);

		// Return an empty value if we're missing anything important
		if ( !is_array( $values ) || empty( $values ) || empty( $values['list'] ) ) {
			return $output;
		}

		// Sanitize the list
		$output['list'] = sanitize_text_field( $values['list'] );

		// Sanitize each merge field
		$val_log = array();
		foreach( $values['fields'] as $id => $val ) {

			// Make sure that a merge field isn't assigned to multiple data
			if ( !in_array( $val, $val_log ) ) {
				$output['fields'][$id] = sanitize_text_field( $val );
			}

			$val_log[] = $val;
		}

		return $output;
	}

	/**
	 * Add and register this setting
	 *
	 * @since 1.0
	 */
	public function add_settings_field( $section_id ) {

		add_settings_field(
			$this->id,
			$this->title,
			array( $this, 'display_setting' ),
			$this->tab,
			$section_id
		);

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

}
