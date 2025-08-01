<?php

/**
 * Register, display and save a settings page in the WordPress admin menu.
 *
 * @since 1.0
 * @package Simple Admin Pages
 */

class sapAdminPage_2_7_0_rtb {

	public $title;
	public $menu_title;
	public $description; // optional description for this page
	public $capability; // user permissions needed to edit this panel
	public $id; // id of this page
	public $sections = array(); // array of sections to display on this page
	public $show_button = true; // whether or not to show the Save Changes button

	public $setup_function = 'add_options_page'; // WP function to register the page


	/**
	 * Initialize the page
	 * @since 1.0
	 */
	public function __construct( $args ) {

		// Parse the values passed
		$this->parse_args( $args );
	}

	/**
	 * Implement magic __set to avoid PHP 8.2 dynamic property deprecation
	 */
	public function __set( $name, $value ) {

		$this->$name = $value;
	}

	/**
	 * Implement magic __set to avoid PHP 8.2 dynamic property deprecation
	 */
	public function __get( $name ) {

		return $this->$name;
	}

	/**
	 * Parse the arguments passed in the construction and assign them to
	 * internal variables.
	 * @since 1.1
	 */
	private function parse_args( $args ) {
		foreach ( $args as $key => $val ) {
			switch ( $key ) {

				case 'id' :
					$this->{$key} = esc_attr( $val );

				default :
					$this->{$key} = $val;

			}
		}
	}

	/**
	 * Modify the capability required to save settings on this page
	 * @since 2.0
	 */
	public function modify_required_capability( $cap ) {
		return $this->capability;
	}

	/**
	 * Add the page to the appropriate menu slot.
	 * @note The default will be to post to the options page, but other classes
	 *			should override this function.
	 * @since 1.0
	 */
	public function add_admin_menu() {
		call_user_func( $this->setup_function, $this->title, $this->menu_title, $this->capability, $this->id, array( $this, 'display_admin_menu' ) );
	}

	/**
	 * Add a section to the page
	 * @since 1.0
	 */
	public function add_section( $section ) {

		if ( !$section ) {
			return;
		}

		$this->sections[ $section->id ] = $section;

	}

	/**
	 * Register the settings and sanitization callbacks for each setting
	 * @since 1.0
	 */
	public function register_admin_menu() {

		foreach ( $this->sections as $section ) {
			$section->add_settings_section();

			foreach ( $section->settings as $setting ) {
				$setting->add_settings_field( $section->id );
			}
		}

		register_setting( $this->id, $this->id, array( $this, 'sanitize_callback' ) );

		// Modify capability required to save the settings if it's not
		// the default `manage_options`
		if ( !empty( $this->capability ) && $this->capability !== 'manage_options') {
			add_filter( 'option_page_capability_' . $this->id, array( $this, 'modify_required_capability' ) );
		}
	}

	/**
	 * Loop through the settings and sanitize the data
	 * @since 2.0
	 */
	public function sanitize_callback( $value ) {

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $value;
		}

		// Get the current page/tab so we only update those settings
		parse_str( sanitize_url( $_POST['_wp_http_referer'] ), $referrer );
		$current_page = $this->get_current_page( $referrer );

		// Use a new empty value so only values for settings that were added are
		// passed to the db.
		$new_value = array();

		foreach ( $this->sections as $section ) {
			foreach ( $section->settings as $setting ) {
				if ( $setting->tab == $current_page ) {
					$setting_value = isset( $value[$setting->id] ) ? $value[$setting->id] : '';
					$new_value[$setting->id] = $setting->sanitize_callback_wrapper( $setting_value );
				}
			}
		}

		// Pull in the existing values so we never overwrite values that were
		// on a different tab
		$old_value = get_option( $this->id );

		if ( is_array( $old_value ) ) {
			return array_merge( $old_value, $new_value );
		} else {
			return $new_value;
		}

	}

	/**
	 * Get the current page/tab being viewed
	 * @since 2.0
	 */
	public function get_current_page( $request ) {

		if ( !empty( $request['tab'] ) ) {
			return sanitize_text_field( $request['tab'] );
		} elseif ( !empty( $this->default_tab ) ) {
			return $this->default_tab;
		} else {
			return $this->id;
		}

	}

	/**
	 * Output the settings passed to this page
	 * @since 1.0
	 */
	public function display_admin_menu() {

		if ( !$this->title && !count( $this->settings ) ) {
			return;
		}

		if ( !current_user_can( $this->capability ) ) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		$current_page = $this->get_current_page( $_GET );

		// Fetch and Order sections/tabs
		$tab_list = []; $non_tab_list = []; $bottom = 1000;
		foreach( $this->sections as $section ) {
			if ( isset( $section->is_tab ) && $section->is_tab === true ) {
				if( property_exists( $section, 'rank' ) ) {
					// array start from 0, rank start from 1
					$tab_list[ $section->rank - 1 ][] = $section;
				}
				else {
					$tab_list[$bottom][] = $section;
				}
			} else {
				$non_tab_list[] = $section;
			}
		}

		$this->sections = [];
		ksort($tab_list);
		foreach ($tab_list as $rank => $sub_list) {
			$this->sections = array_merge( $this->sections, $sub_list );
		}
		$this->sections = array_merge( $this->sections, $non_tab_list );

		?>

			<div class="wrap sap-settings-page">

				<?php $this->display_page_title(); ?>

				<?php if ( isset( $this->default_tab ) ) : ?>
				<div class="sap-settings-menu-and-video-button">
					<h2 class="nav-tab-wrapper">
					<?php
					foreach( $this->sections as $section ) {

						if ( isset( $section->is_tab ) && $section->is_tab === true ) {

							$tab_url = add_query_arg(
								array(
									'settings-updated' => false,
									'setting_type'	=> false,
									'setting_type_value'	=> false,
									'tab' => $section->id
								)
							);

							if( isset( $section->show_submit_button ) && $current_page == $section->id ) {
								$this->show_button = $section->show_submit_button;
							}

							$active = $current_page == $section->id ? ' nav-tab-active' : '';
							echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $section->title ) . '" class="nav-tab' . esc_attr( $active ) . '">';
								echo '<span class="dashicons dashicons-' . esc_attr( $section->icon ) . '"></span>';
								echo esc_html( $section->title );
							echo '</a>';
						}
					}
					?>
					</h2>
				</div>	
				<?php endif; ?>

				<form method="post" action="options.php" class="sap-parent-form">
					<?php settings_fields( $this->id ); ?>
					<?php $this->maybe_print_settings_type_toggle( $current_page ); ?>
					<?php $this->maybe_print_tutorial_div( $current_page ); ?>
					<?php do_settings_sections( $current_page ); ?>
					<?php if ( $this->show_button ) { submit_button(); } ?>
				 </form>
			</div>

		<?php
	}

	/**
	 * Output the title of the page
	 * @since 1.0
	 */
	public function display_page_title() {

		if ( empty( $this->title ) ) {
			return;
		}
		?>
			<h1><?php echo esc_html( $this->title ); ?></h1>
		<?php
	}

	/**
	 * Output the YouTube settings tutorial video
	 * @since 2.6.9
	 */
	public function maybe_print_tutorial_div( $current_page ) {

		foreach ( $this->sections as $section ) {

			if ( $current_page == $section->id and ! empty( $section->tutorial_yt_id ) ) { ?>

				<div class='sap-tutorial-div sap-hidden'>

					<iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo esc_attr( $section->tutorial_yt_id ); ?>" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; gyroscope;" allowfullscreen></iframe>

				</div>

				<?php

				return true;
			}
		}

		return false;
	}

	/**
	 * Output the settings toggle, used to switch the type of settings displayed
	 * @since 2.6.21.rtb
	 */
	public function maybe_print_settings_type_toggle( $current_page ) {

		$setting_type = isset( $_GET['setting_type'] ) ? $_GET['setting_type'] : '';
		$setting_type_value = isset( $_GET['setting_type_value'] ) ? $_GET['setting_type_value'] : '';

		foreach ( $this->sections as $section ) {

			if ( $current_page == $section->id and ! empty( $section->settings_type_toggle_options ) ) { ?>

				<div class='sap-settings-type-toggle-div <?php echo ( ! isset( $_GET['setting_type'] ) ? 'sap-hidden' : '' ); ?>'>

					<?php foreach ( $section->settings_type_toggle_options as $option_title => $option_values ) { ?>

						<div class='sap-settings-type-toggle-option'>

							<span class='sap-settings-type-toggle-option-title'>
								<?php echo esc_html( ucwords( str_replace( '_', ' ', $option_title ) ) ); ?>
							</span>

							<select name='<?php echo esc_attr( $option_title ); ?>' <?php echo ( ( $setting_type and $setting_type != $option_title ) ? 'disabled' : '' ); ?>>

								<?php 

									$option_group = '';

									foreach ( $option_values as $option ) { 

										if ( ! empty( $option['group'] ) and $option['group'] != $option_group ) {

											if ( ! empty( $option_group ) ) { echo '</optgroup>'; }

											$option_group = $option['group'];

											echo '<optgroup label=\'' . esc_attr( $option_group ) . '\'>';
										}

										$selected = ( $setting_type and $setting_type == $option_title and $setting_type_value and $setting_type_value == $option['value'] ) ? 'selected' : '';
									?>
									
										<option value='<?php echo esc_attr( $option['value'] ); ?>' <?php echo $selected; ?>>
											<?php echo esc_html( $option['label'] ); ?>
										</option>

								<?php } ?>

								<?php if ( ! empty( $option_group ) ) { echo '</optgroup>'; } ?>

							</select>

						</div>

					<?php } ?>

				</div>

				<?php

				return true;
			}
		}

		return false;
	}
}
