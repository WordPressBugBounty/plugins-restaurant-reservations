<?php
/** Shared single-address validation, independent of Helper and commercial rights. */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class rtbSenderAddress {
	/** Null means invalid; an empty string means use the established default. */
	public static function validate( $value ) {
		if ( ! is_string( $value ) || preg_match( '/[\x00-\x1f\x7f]/', $value ) ) {
			return null; }
		$value = trim( $value );
		if ( '' === $value ) {
			return ''; }
		return is_email( $value ) && sanitize_email( $value ) === $value ? $value : null;
	}

	/** WordPress options.php has already unslashed this settings value once. */
	public static function sanitize_setting( $value ) {
		$address = self::validate( $value );
		if ( null !== $address ) {
			return $address; }
		add_settings_error( 'rtb-settings', 'rtb_sender_invalid', __( 'Enter one valid FROM email address without header characters. The previous address has been retained.', 'restaurant-reservations' ) );
		$previous = get_option( 'rtb-settings', array() );
		return isset( $previous['from-email-address'] ) && is_string( $previous['from-email-address'] ) ? $previous['from-email-address'] : '';
	}

	public static function safe_effective( $value ) {
		$address = self::validate( $value );
		if ( null !== $address && '' !== $address ) {
			return $address; }
		$default = self::validate( get_option( 'admin_email', '' ) );
		return null !== $default ? $default : '';
	}

	public static function header_name( $value ) {
		return is_string( $value ) ? preg_replace( '/[\x00-\x1f\x7f]/', '', $value ) : '';
	}
}
