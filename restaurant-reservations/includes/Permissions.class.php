<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbPermissions' ) ) {
/**
 * Class to handle plugin permissions for Restaurant Reservations
 *
 * @since 2.0.0
 */
class rtbPermissions {

	private $plugin_permissions;
	private $permission_level;

	public function __construct() {
		$this->plugin_permissions = array(
			"premium" => 2,
			"advanced" => 2,
			"labelling" => 2,
			"styling" => 2,
			"import" => 2,
			"export" => 2,
			"custom_fields" => 2,
			"mailchimp" => 2,
			"templates" => 2,
			"designer" => 2,
			"premium_view_bookings" => 2,
			"premium_seat_restrictions" => 2,
			"payments" => 3,
			"reminders" => 3,
			"premium_table_restrictions" => 3,
			"api_usage"	=> 3
		);
	}

	public function set_permissions() {
		global $rtb_controller;

		if ( is_array( get_option( 'rtb-permission-level' ) ) ) { return; }

		if ( ! empty( get_option( 'rtb-permission-level' ) ) ) {

			update_option( 'rtb-permission-level', array( get_option( 'rtb-permission-level' ) ) );

			return;
		}

		$cffrtb = $rtb_controller->settings->get_setting( 'license-cffrtb' );
		$ebfrtb = $rtb_controller->settings->get_setting( 'license-ebfrtb' );
		$etfrtb = $rtb_controller->settings->get_setting( 'license-etfrtb' );

		$bookings_objects = get_posts( array( 'post_type' => array( RTB_BOOKING_POST_TYPE ) ) );

		$this->permission_level = ( ( ( is_array($cffrtb) and array_key_exists('status', $cffrtb) ) or ( is_array($ebfrtb) and array_key_exists('status', $ebfrtb) ) or ( is_array($etfrtb) and array_key_exists('status', $etfrtb) ) or get_option("mcfrtb_license_key") ) ? 2 : ( ! empty($bookings_objects) ? 1 : 0 ) );

		update_option( "rtb-permission-level", array( $this->permission_level ) );
	}

	public function get_permission_level() {

		if ( ! is_array( get_option( 'rtb-permission-level' ) ) ) { $this->set_permissions(); }

		$permissions_array = get_option( 'rtb-permission-level' );

		$this->permission_level = is_array( $permissions_array ) ? reset( $permissions_array ) : $permissions_array;
	}

	public function check_permission( $permission_type = '' ) {
		if ( ! $this->permission_level ) { $this->get_permission_level(); }

		if ( ! array_key_exists( $permission_type, $this->plugin_permissions ) || $this->permission_level < $this->plugin_permissions[ $permission_type ] ) {
			return false;
		}

		if ( 3 !== $this->plugin_permissions[ $permission_type ] ) {
			return true;
		}

		// Existing payment and table policies remain enforced while entitlement
		// is degraded, but their settings are made read-only by can_edit_permission().
		if ( in_array( $permission_type, array( 'payments', 'premium_table_restrictions' ), true ) ) {
			return true;
		}

		return $this->has_active_ultimate_entitlement();
	}

	public function can_edit_permission( $permission_type = '' ) {
		if ( ! $this->permission_level ) { $this->get_permission_level(); }

		if ( ! array_key_exists( $permission_type, $this->plugin_permissions ) || $this->permission_level < $this->plugin_permissions[ $permission_type ] ) {
			return false;
		}

		return 3 !== $this->plugin_permissions[ $permission_type ] || $this->has_active_ultimate_entitlement();
	}

	public function has_active_ultimate_entitlement() {
		return $this->has_compatible_helper() && fspph_rtb_is_ultimate_active();
	}

	public function has_compatible_helper() {
		return defined( 'FSPPH_VERSION' )
			&& version_compare( FSPPH_VERSION, '0.1.0', '>=' )
			&& function_exists( 'fspph_rtb_entitlement_contract_version' )
			&& 1 === (int) fspph_rtb_entitlement_contract_version()
			&& function_exists( 'fspph_rtb_is_ultimate_active' );
	}

	public function get_entitlement_state() {
		if ( ! $this->has_compatible_helper() || ! function_exists( 'fspph_rtb_get_entitlement_state' ) ) {
			return array( 'status' => 'incompatible', 'active' => false );
		}

		return fspph_rtb_get_entitlement_state();
	}

	public function get_stored_permission_level() {
		if ( ! $this->permission_level ) { $this->get_permission_level(); }

		return (int) $this->permission_level;
	}

	public function update_permissions() {
		$permissions = get_option( 'rtb-permission-level' );
		$this->permission_level = is_array( $permissions ) ? (int) reset( $permissions ) : (int) $permissions;
	}
}
}
