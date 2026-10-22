<?php
/** Durable, booking-scoped provider attempts. Never reuse a booking for a new charge while an attempt is uncertain. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'rtbPaymentAttempt' ) ) {
class rtbPaymentAttempt {
	public static function option_name( $id ) { return 'rtb-payment-attempt-' . (int) $id; }
	public static function ledger_name( $id, $provider, $receipt ) {
		return 'rtb-payment-ledger-' . (int) $id . '-' . substr( hash( 'sha256', $provider . ':' . $receipt ), 0, 32 );
	}
	public static function has_ledger( $id ) {
		global $wpdb;
		$prefix = 'rtb-payment-ledger-' . (int) $id . '-';
		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1", $wpdb->esc_like( $prefix ) . '%' ) );
	}
	private static function scope( $id ) { return 'payment|' . get_current_blog_id() . '|' . (int) $id; }
	public static function acquire( $id ) { return rtbBookingAdmission::acquire( array( self::scope( $id ) ) ); }
	public static function release( $locks ) { rtbBookingAdmission::release( $locks ); }
	public static function get( $id ) {
		$attempt = get_option( self::option_name( $id ), array() );
		return is_array( $attempt ) ? $attempt : array();
	}

	public static function start( $booking_id, $provider ) {
		$locks = self::acquire( $booking_id );
		if ( false === $locks ) { return new WP_Error( 'payment_busy', 'A payment is already being prepared. Please wait.' ); }
		try {
			$booking = new rtbBooking();
			if ( ! $booking->load_post( (int) $booking_id ) ) { return new WP_Error( 'booking_missing', 'Booking not found.' ); }
			if ( ! in_array( $booking->post_status, array( 'payment_pending', 'payment_failed' ), true ) || ! empty( $booking->receipt_id )
				|| get_option( 'rtb-payment-reconciliation-' . (int) $booking_id ) || self::has_ledger( $booking_id ) ) {
				return new WP_Error( 'payment_closed', 'This booking is already paid or needs payment review. Please do not pay again.' );
			}
			$existing = self::get( $booking_id );
			if ( $existing ) {
				if ( 'stripe_sca' === $provider && 'stripe_sca' === ( $existing['provider'] ?? '' )
					&& 'ready' === ( $existing['state'] ?? '' ) && ! empty( $existing['intent_id'] )
					&& (string) $booking->stripe_payment_intent_id === (string) $existing['intent_id'] ) {
					return array( 'attempt' => $existing, 'reused' => true );
				}
				return new WP_Error( 'payment_attempt_exists', 'This booking already has a payment attempt. Please do not start another payment; contact the site administrator if needed.' );
			}
			$uuid = wp_generate_uuid4();
			$attempt = array( 'id' => $uuid, 'provider' => $provider, 'state' => 'reserved',
				'idempotency_key' => 'rtb-' . get_current_blog_id() . '-' . (int) $booking_id . '-' . $uuid,
				'intent_id' => '', 'receipt_id' => '', 'created_at' => time() );
			if ( ! add_option( self::option_name( $booking_id ), $attempt, '', false ) || self::get( $booking_id ) !== $attempt ) {
				return new WP_Error( 'payment_attempt_not_saved', 'A safe payment attempt could not be recorded. Please do not pay again.' );
			}
			return array( 'attempt' => $attempt, 'reused' => false );
		} finally { self::release( $locks ); }
	}

	public static function update( $booking_id, $attempt_id, $changes ) {
		$locks = self::acquire( $booking_id );
		if ( false === $locks ) { return false; }
		try {
			$attempt = self::get( $booking_id );
			if ( ! $attempt || ! hash_equals( (string) ( $attempt['id'] ?? '' ), (string) $attempt_id ) ) { return false; }
			$updated = array_merge( $attempt, $changes );
			update_option( self::option_name( $booking_id ), $updated, false );
			return self::get( $booking_id ) === $updated;
		} finally { self::release( $locks ); }
	}

	public static function transition( $booking_id, $attempt_id, $from, $to ) {
		$locks = self::acquire( $booking_id );
		if ( false === $locks ) { return false; }
		try {
			$attempt = self::get( $booking_id );
			if ( ! $attempt || ! hash_equals( (string) ( $attempt['id'] ?? '' ), (string) $attempt_id )
				|| ! in_array( $attempt['state'] ?? '', (array) $from, true ) ) { return false; }
			$attempt['state'] = $to;
			update_option( self::option_name( $booking_id ), $attempt, false );
			return self::get( $booking_id ) === $attempt;
		} finally { self::release( $locks ); }
	}

	public static function clear( $booking_id, $attempt_id ) {
		$locks = self::acquire( $booking_id );
		if ( false === $locks ) { return false; }
		try {
			$attempt = self::get( $booking_id );
			return $attempt && hash_equals( (string) ( $attempt['id'] ?? '' ), (string) $attempt_id )
				&& delete_option( self::option_name( $booking_id ) );
		} finally { self::release( $locks ); }
	}

	/** A verified PayPal IPN is already money received; claim once or queue for review. */
	public static function claim_paypal( $booking_id, $transaction_id ) {
		$locks = self::acquire( $booking_id );
		if ( false === $locks ) { return 'review'; }
		try {
			$used = get_option( 'rtb_used_paypal_txn_ids', array() );
			$used = is_array( $used ) ? $used : array();
			if ( in_array( $transaction_id, $used, true ) ) { return 'duplicate'; }
			$used[] = $transaction_id;
			update_option( 'rtb_used_paypal_txn_ids', $used, false );
			if ( get_option( 'rtb_used_paypal_txn_ids' ) !== $used ) { return 'review'; }
			$booking = new rtbBooking();
			if ( ! $booking->load_post( (int) $booking_id ) || 'payment_pending' !== $booking->post_status
				|| ! empty( $booking->receipt_id ) || self::get( $booking_id )
				|| get_option( 'rtb-payment-reconciliation-' . (int) $booking_id ) || self::has_ledger( $booking_id ) ) { return 'review'; }
			$uuid = wp_generate_uuid4();
			$attempt = array( 'id' => $uuid, 'provider' => 'paypal', 'state' => 'claimed',
				'idempotency_key' => '', 'intent_id' => '', 'receipt_id' => $transaction_id, 'created_at' => time() );
			if ( ! add_option( self::option_name( $booking_id ), $attempt, '', false ) || self::get( $booking_id ) !== $attempt ) { return 'review'; }
			return 'process';
		} finally { self::release( $locks ); }
	}
}
}
