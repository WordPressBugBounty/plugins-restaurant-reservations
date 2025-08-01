<?php

/**
 * Register, display and save a text field setting in the admin menu
 *
 * @since 2.5.5
 * @package Simple Admin Pages
 */

class sapAdminPageSettingTime_2_7_0_rtb extends sapAdminPageSetting_2_7_0_rtb {

	public $sanitize_callback = 'sanitize_text_field';

	/**
	 * Placeholder string for the input field
	 * @since 2.5.5
	 */
	public $placeholder = '';

	/**
	 * Display this setting
	 * @since 2.5.5
	 */
	public function display_setting() {
		?>

		<input name="<?php echo esc_attr( $this->get_input_name() ); ?>" type="time" id="<?php echo esc_attr( $this->get_input_name() ); ?>" value="<?php echo esc_attr( $this->value ); ?>"<?php echo !empty( $this->placeholder ) ? ' placeholder="' . esc_attr( $this->placeholder ) . '"' : ''; ?> class="regular-text" <?php echo ( $this->disabled ? 'disabled' : ''); ?> />

		<?php $this->display_disabled(); ?>	
		
		<?php

		$this->display_description();

	}

}
