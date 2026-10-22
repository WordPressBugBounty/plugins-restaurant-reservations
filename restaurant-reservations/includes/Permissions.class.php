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
		if ( ! array_key_exists( $permission_type, $this->plugin_permissions ) ) {
			return false;
		}
		if ( $this->has_capability( $permission_type ) ) {
			return true;
		}

		// Preserve configured safety policies only. This does not permit editing
		// their settings or grant any other paid capability.
		if ( in_array( $permission_type, array( 'payments', 'premium_table_restrictions' ), true ) ) {
			if ( ! $this->permission_level ) { $this->get_permission_level(); }
			if ( $this->permission_level < 3 ) { return false; }
			global $rtb_controller;
			if ( empty( $rtb_controller->settings ) ) { return false; }
			return 'payments' === $permission_type
				? ! empty( $rtb_controller->settings->get_setting( 'require-deposit' ) )
				: ! empty( $rtb_controller->settings->get_setting( 'enable-tables' ) );
		}
		return false;
	}

	public function can_edit_permission( $permission_type = '' ) {
		return $this->has_capability( $permission_type );
	}

	public function has_active_ultimate_entitlement() {
		$contract = $this->get_contract();
		return ! empty( $contract['ultimate']['active'] );
	}

	public function has_compatible_helper() {
		return defined( 'FSPPH_VERSION' )
			&& version_compare( FSPPH_VERSION, '0.2.0', '>=' )
			&& function_exists( 'fspph_rtb_supported_entitlement_contracts' )
			&& in_array( 2, fspph_rtb_supported_entitlement_contracts(), true )
			&& function_exists( 'fspph_rtb_get_entitlement_contract' );
	}

	public function get_entitlement_state() {
		if ( ! $this->has_compatible_helper() ) {
			return array( 'status' => 'incompatible', 'active' => false );
		}
		$contract = $this->get_contract();
		$ultimate = isset( $contract['ultimate'] ) && is_array( $contract['ultimate'] ) ? $contract['ultimate'] : array();
		return array( 'status' => $ultimate['status'] ?? 'unverifiable', 'active' => ! empty( $ultimate['active'] ), 'expires_at' => $ultimate['runtime_expires_at'] ?? null );
	}

	public function get_contract() {
		if ( ! $this->has_compatible_helper() ) { return array(); }
		$contract = fspph_rtb_get_entitlement_contract( 2 );
		return is_array( $contract ) && 2 === ( $contract['contract_version'] ?? null ) && ! empty( $contract['compatible'] ) && isset( $contract['capabilities'] ) && is_array( $contract['capabilities'] ) ? $contract : array();
	}

	private function has_capability( $permission_type ) {
		if ( ! isset( $this->plugin_permissions[ $permission_type ] ) ) { return false; }
		$contract = $this->get_contract();
		return in_array( $permission_type, $contract['capabilities'] ?? array(), true );
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
