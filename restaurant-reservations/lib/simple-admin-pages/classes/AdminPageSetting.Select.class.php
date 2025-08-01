<?php

/**
 * Register, display and save a selection option with a drop-down menu.
 *
 * This setting accepts the following arguments in its constructor function.
 *
 * $args = array(
 *		'id'			=> 'setting_id', 	// Unique id
 *		'title'			=> 'My Setting', 	// Title or label for the setting
 *		'description'	=> 'Description', 	// Help text description
 *		'blank_option'	=> true, 			// Whether or not to show a blank option
 *		'options'		=> array(			// An array of key/value pairs which
 *			'option1'	=> 'Option 1',		//	define the options.
 *			'option2'	=> 'Option 2',
 *			...
 *		);
 * );
 *
 * @since 1.0
 * @package Simple Admin Pages
 */

class sapAdminPageSettingSelect_2_7_0_rtb extends sapAdminPageSetting_2_7_0_rtb {

	public $sanitize_callback = 'sanitize_text_field';

	// Whether or not to display a blank option
	public $blank_option = true;

	// An array of options for this select field, accepted as a key/value pair.
	public $options = array();

	/**
	 * Display this setting
	 * @since 1.0
	 */
	public function display_setting() {

		?>

		<fieldset <?php $this->print_conditional_data(); ?>>

			<select name="<?php echo esc_attr( $this->get_input_name() ); ?>" id="<?php echo esc_attr( $this->id ); ?>" <?php echo ( $this->disabled ? 'disabled' : ''); ?>>

				<?php if ( $this->blank_option === true ) : ?>
					<option></option>
				<?php endif; ?>

				<?php $this->print_options( $this->options ); ?>

			</select>
			<?php $this->display_disabled(); ?>	

		</fieldset>

		<?php

		$this->display_description();

	}

	/**
	 * Recursively print out select options
	 * @since 2.6.18
	 */
	public function print_options( $options ) {

		foreach ( $options as $option_value => $option_name ) {

			if ( is_array( $option_name ) ) { ?>

				<optgroup label='<?php echo esc_attr( $option_value ); ?>'>
					<?php $this->print_options( $option_name ); ?>
				</optgroup>

				<?php

				continue;
			}

			?>

			<option value='<?php echo esc_attr( $option_value ); ?>' <?php echo ( $this->value == $option_value ? 'selected' : '' ); ?>>
				<?php echo esc_html( $option_name ); ?>
			</option>
		
			<?php 
		}
	}

}
