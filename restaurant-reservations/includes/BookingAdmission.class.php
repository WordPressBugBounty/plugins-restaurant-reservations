<?php
/** Coordinated writes for booking inventory on a site/location/service day. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'rtbBookingAdmission' ) ) {
class rtbBookingAdmission {
	public static function consuming_statuses() {
		return array( 'pending', 'payment_pending', 'confirmed', 'arrived' );
	}

	public static function consumes( $status ) {
		return in_array( $status, self::consuming_statuses(), true );
	}

	public static function scope( $date, $location ) {
		return get_current_blog_id() . '|' . (int) $location . '|' . substr( (string) $date, 0, 10 );
	}

	/** Acquire all affected scopes in a stable order; release partial acquisition. */
	public static function acquire( $scopes ) {
		global $wpdb;
		$scopes = array_unique( array_filter( (array) $scopes ) );
		sort( $scopes, SORT_STRING );
		$locks = array();
		foreach ( $scopes as $scope ) {
			$name = 'rtb-booking-admission-lock-' . substr( hash( 'sha256', $scope ), 0, 32 );
			$value = array( 'owner' => wp_generate_uuid4(), 'expires' => time() + 15 );
			$acquired = add_option( $name, $value, '', 'no' );
			if ( ! $acquired ) {
				$existing = get_option( $name );
				if ( is_array( $existing ) && ! empty( $existing['expires'] ) && (int) $existing['expires'] < time() ) {
					$updated = $wpdb->query( $wpdb->prepare(
						"UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s",
						maybe_serialize( $value ), $name, maybe_serialize( $existing )
					) );
					wp_cache_delete( $name, 'options' );
					$acquired = 1 === $updated;
				}
			}
			if ( ! $acquired ) { self::release( $locks ); return false; }
			$locks[] = array( 'name' => $name, 'value' => $value );
		}
		return $locks;
	}

	public static function refresh( &$locks ) {
		global $wpdb;
		foreach ( $locks as &$lock ) {
			$refreshed = $lock['value'];
			$refreshed['expires'] = max( time() + 15, (int) $refreshed['expires'] + 15 );
			$updated = $wpdb->query( $wpdb->prepare(
				"UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s",
				maybe_serialize( $refreshed ), $lock['name'], maybe_serialize( $lock['value'] )
			) );
			wp_cache_delete( $lock['name'], 'options' );
			if ( 1 !== $updated ) { return false; }
			$lock['value'] = $refreshed;
		}
		unset( $lock );
		return true;
	}

	public static function release( $locks ) {
		global $wpdb;
		foreach ( array_reverse( (array) $locks ) as $lock ) {
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
				$lock['name'], maybe_serialize( $lock['value'] )
			) );
			wp_cache_delete( $lock['name'], 'options' );
		}
	}

	public static function revision( $booking ) {
		$post = $booking->post;
		return hash( 'sha256', implode( '|', array(
			$post->ID, $post->post_modified_gmt, $booking->post_status,
			$booking->date, $booking->location, $booking->party,
			wp_json_encode( $booking->table ),
		) ) );
	}

	public static function delete( $id, $permanent = false ) {
		$booking = new rtbBooking();
		if ( ! $booking->load_post( (int) $id ) ) { return false; }
		$scope = self::scope( $booking->date, $booking->location );
		$lock = self::acquire( array( $scope ) );
		if ( false === $lock ) { return false; }
		try {
			$reloaded = new rtbBooking();
			if ( ! $reloaded->load_post( (int) $id ) || self::scope( $reloaded->date, $reloaded->location ) !== $scope || ! self::refresh( $lock ) ) { return false; }
			return $permanent ? wp_delete_post( (int) $id, true ) : wp_trash_post( (int) $id );
		} finally {
			self::release( $lock );
		}
	}

	/** Shared guarded entry for public cancellation, staff and Helper API status. */
	public static function mutate( $id, $changes, $revision = '', $actor = 0, $override = false ) {
		global $rtb_controller;
		$booking = new rtbBooking();
		if ( ! $booking->load_post( (int) $id ) ) { return new WP_Error( 'rtb_booking_missing', __( 'Booking not found.', 'restaurant-reservations' ) ); }
		$scope = self::scope( $booking->date, $booking->location );
		$lock = self::acquire( array( $scope ) );
		if ( false === $lock ) { return new WP_Error( 'rtb_booking_busy', __( 'Another booking is being changed. Please retry.', 'restaurant-reservations' ) ); }
		try {
			// Reload after taking the lock: the pre-lock booking may already be stale.
			$booking = new rtbBooking();
			if ( ! $booking->load_post( (int) $id ) ) { return new WP_Error( 'rtb_booking_missing', __( 'Booking not found.', 'restaurant-reservations' ) ); }
			if ( self::scope( $booking->date, $booking->location ) !== $scope ) {
				return new WP_Error( 'rtb_booking_stale', __( 'This booking changed. Reload it and try again.', 'restaurant-reservations' ) );
			}
			if ( '' !== $revision && ! hash_equals( self::revision( $booking ), $revision ) ) {
				return new WP_Error( 'rtb_booking_stale', __( 'This booking changed. Reload it and try again.', 'restaurant-reservations' ) );
			}
			$before = $booking->post_status;
			$original_table = array_map( 'strval', (array) $booking->table );
			$status = isset( $changes['status'] ) ? sanitize_key( $changes['status'] ) : $before;
			if ( ! isset( $rtb_controller->cpts->booking_statuses[ $status ] ) ) {
				return new WP_Error( 'rtb_booking_status_invalid', __( 'Invalid booking status.', 'restaurant-reservations' ) );
			}
			if ( array_key_exists( 'table', $changes ) ) {
				$table = $changes['table'];
				if ( ! is_array( $table ) ) { return new WP_Error( 'rtb_booking_table_invalid', __( 'Invalid table selection.', 'restaurant-reservations' ) ); }
				$booking->table = array_values( array_unique( array_map( 'sanitize_text_field', $table ) ) );
				$proposed_table = array_map( 'strval', $booking->table );
				sort( $original_table, SORT_STRING );
				sort( $proposed_table, SORT_STRING );
				if ( $proposed_table !== $original_table ) {
					$available_ids = array_map( 'strval', array_keys( (array) $rtb_controller->settings->get_sorted_tables( $booking->date, $booking->location ) ) );
					foreach ( $booking->table as $number ) {
						if ( ! in_array( (string) $number, $available_ids, true ) ) { return new WP_Error( 'rtb_booking_table_invalid', __( 'Invalid table selection.', 'restaurant-reservations' ) ); }
					}
				}
			}
			$booking->post_status = $status;
			$claims_inventory = self::consumes( $status ) && ( ! self::consumes( $before ) || ( isset( $proposed_table ) && $proposed_table !== $original_table ) );
			if ( $claims_inventory && ! $override ) {
				if ( ! $booking->is_under_max_reservations() || ! $booking->is_under_max_seats()
					|| ( ! empty( $booking->table ) && ! $booking->is_valid_table() ) ) {
					return new WP_Error( 'rtb_booking_capacity', __( 'This booking no longer fits the available capacity or table. Reload and choose another option.', 'restaurant-reservations' ) );
				}
			}
			if ( ! self::refresh( $lock ) ) { return new WP_Error( 'rtb_booking_busy', __( 'Availability changed. Please retry.', 'restaurant-reservations' ) ); }
			$prior_meta = get_post_meta( $id, 'rtb', true );
			if ( array_key_exists( 'table', $changes ) ) {
				$meta = is_array( $prior_meta ) ? $prior_meta : array();
				$meta['table'] = $booking->table;
				update_post_meta( $id, 'rtb', $meta );
			}
			if ( $status !== $before ) {
				// Staff quick actions default to no customer notification, matching
				// the existing admin booking form. Public/API paths keep prior hooks.
				$notifications = isset( $rtb_controller->notifications ) ? $rtb_controller->notifications : null;
				$prior_disabled = $notifications ? $notifications->notifications_disabled : null;
				if ( $actor && $notifications ) { $notifications->notifications_disabled = true; }
				try {
					$result = wp_update_post( array( 'ID' => (int) $id, 'post_status' => $status ), true );
				} finally {
					if ( $notifications ) { $notifications->notifications_disabled = $prior_disabled; }
				}
				if ( is_wp_error( $result ) || ! $result ) {
					if ( array_key_exists( 'table', $changes ) ) { update_post_meta( $id, 'rtb', $prior_meta ); }
					return new WP_Error( 'rtb_booking_save_failed', __( 'The booking could not be saved.', 'restaurant-reservations' ) );
				}
			}
			if ( $actor ) {
				$meta = get_post_meta( $id, 'rtb', true );
				$meta = is_array( $meta ) ? $meta : array();
				$logs = isset( $meta['logs'] ) && is_array( $meta['logs'] ) ? $meta['logs'] : array();
				$logs[] = array( 'staff', 'Booking updated', sprintf( 'User %d changed status %s to %s; override: %s', (int) $actor, $before, $status, $override ? 'yes' : 'no' ), current_time( 'mysql' ) );
				$meta['logs'] = $logs;
				update_post_meta( $id, 'rtb', $meta );
			}
			$updated = new rtbBooking();
			$updated->load_post( (int) $id );
			return array( 'status' => $status, 'table' => $updated->table, 'revision' => self::revision( $updated ) );
		} finally {
			self::release( $lock );
		}
	}
}
}
