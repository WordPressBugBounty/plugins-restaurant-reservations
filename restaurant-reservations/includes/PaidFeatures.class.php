<?php
/** Local component availability, separate from entitlement authority. */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class rtbPaidFeatures {
	private static $booted = false;

	public static function available( $capability = '' ) {
		if ( ! function_exists( 'fspph_rtb_get_runtime_manifest' ) ) {
			return false; }
		$manifest = fspph_rtb_get_runtime_manifest();
		if ( ! is_array( $manifest ) || 1 !== ( $manifest['api'] ?? null )
			|| version_compare( RTB_VERSION, $manifest['minimum_free'] ?? '999', '<' )
			|| version_compare( RTB_VERSION, $manifest['maximum_free_exclusive'] ?? '0', '>=' ) ) {
			return false; }
		return '' === $capability || in_array( $capability, $manifest['capabilities'] ?? array(), true );
	}

	public static function can( $capability ) {
		if ( ! self::available( $capability ) || ! function_exists( 'fspph_rtb_get_entitlement_contract' ) ) {
			return false; }
		$contract = fspph_rtb_get_entitlement_contract( 2 );
		return is_array( $contract ) && 2 === ( $contract['contract_version'] ?? null ) && ! empty( $contract['compatible'] )
			&& in_array( $capability, $contract['capabilities'] ?? array(), true );
	}

	public static function boot() {
		if ( self::$booted || ! self::available() || ! is_callable( array( 'fspphRTBRuntime', 'boot' ) ) ) {
			return; }
		self::$booted = true;
		fspphRTBRuntime::boot();
	}
}

function rtb_get_paid_runtime_api() {
	return RTB_PAID_RUNTIME_API; }
