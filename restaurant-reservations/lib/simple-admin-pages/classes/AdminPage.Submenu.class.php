<?php

/**
 * Register, display and save an settings page as a submenu item in the
 * WordPress admin menu.
 *
 * @since 1.1
 * @package Simple Admin Pages
 */

class sapAdminPageSubmenu_2_7_0_rtb extends sapAdminPage_2_7_0_rtb {

	public $setup_function = 'add_submenu_page'; // WP function to register the page
	public $parent_menu = null; // Which menu to attach this submenu page to
	public $default_tab; // which menu tab to option on page load, if none selected

	/**
	 * Add the page to the appropriate menu slot.
	 * @since 1.0
	 */
	public function add_admin_menu() {

		// Don't register if no parent menu is specified
		if ( !$this->parent_menu ) {
			return;
		}

		call_user_func(
			$this->setup_function,
			$this->parent_menu,
			$this->title,
			$this->menu_title,
			$this->capability,
			$this->id,
			array( $this, 'display_admin_menu' )
		);
	}
}
