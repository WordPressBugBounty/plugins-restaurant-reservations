<?php
/** Shared, non-sensitive transport evidence. No sending or paid entitlement is required to read it. */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class rtbNotificationDiagnostics {
	const META_KEY  = '_rtb_notification_diagnostic';
	const PAGE_SIZE = 20;

	/** Capture before preparation and extension hooks can mutate the notification. */
	public static function context( $notification ) {
		return array(
			'id'              => wp_generate_uuid4(),
			'booking_id'      => ! empty( $notification->booking->ID ) ? absint( $notification->booking->ID ) : 0,
			'timestamp'       => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'event'           => self::key( $notification->event ?? '' ),
			'trigger_event'   => self::key( $notification->trigger_event ?? '' ),
			'type'            => is_a( $notification, 'rtbNotificationSMS' ) ? 'sms' : 'email',
			'target'          => self::key( $notification->target ?? '' ),
			'notification_id' => absint( $notification->notification_id ?? 0 ),
		);
	}

	private static function key( $value ) {
		return is_string( $value ) ? substr( sanitize_key( $value ), 0, 80 ) : ''; }

	/** Short database lock covers persistence only, never preparation/transport. */
	public static function append( $context, $status, $reason = '' ) {
		global $wpdb;
		$id = absint( $context['booking_id'] ?? 0 );
		if ( ! $id || RTB_BOOKING_POST_TYPE !== get_post_type( $id ) ) {
			return false; }
		$name = 'rtb-diag-' . substr( hash( 'sha256', get_current_blog_id() . ':' . $id ), 0, 40 );
		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 1)', $name ) ) ) {
			error_log( 'RTB notification diagnostic lock unavailable; transport was not retried.' );
			return false;
		}
		try {
			unset( $context['booking_id'] );
			$context['status'] = self::key( $status );
			$context['reason'] = self::key( $reason );
			if ( ! add_post_meta( $id, self::META_KEY, $context, false ) ) {
				error_log( 'RTB notification diagnostic write failed; transport was not retried.' );
				return false;
			}
			$excess = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC LIMIT 18446744073709551615 OFFSET 50",
					$id,
					self::META_KEY
				)
			);
			foreach ( $excess as $meta_id ) {
				delete_metadata_by_mid( 'post', (int) $meta_id ); }
			return true;
		} finally {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $name ) ); }
	}

	/** Booking managers have a distinct role, not WordPress edit_posts. */
	public static function can_read( $id ) {
		$id = absint( $id );
		if ( ! $id ) { return false; }
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_bookings', $id ) || RTB_BOOKING_POST_TYPE !== get_post_type( $id ) ) {
			return false; }
		// Hosts with object/location restrictions can narrow the shared staff permission.
		return (bool) apply_filters( 'rtb_notification_history_can_read', true, $id, get_current_user_id() );
	}

	private static function encode_cursor( $state ) {
		$body = base64_encode( wp_json_encode( $state ) );
		return $body . '.' . hash_hmac( 'sha256', $body, wp_salt( 'nonce' ) );
	}

	private static function decode_cursor( $cursor, $id ) {
		if ( ! is_string( $cursor ) || strlen( $cursor ) > 2048 ) {
			return false; }
		$parts = explode( '.', $cursor );
		if ( 2 !== count( $parts ) || ! hash_equals( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), $parts[1] ) ) {
			return false; }
		$state = json_decode( base64_decode( $parts[0], true ), true );
		return is_array( $state ) && ( $state['booking'] ?? 0 ) === $id && ( $state['user'] ?? 0 ) === get_current_user_id()
			&& ( $state['blog'] ?? 0 ) === get_current_blog_id() && ( $state['expires'] ?? 0 ) >= time()
			&& isset( $state['upper'], $state['legacy_count'], $state['last'] ) && is_array( $state['last'] ) ? $state : false;
	}

	/** Stable newest-first key: UTC timestamp, then separate meta ID / legacy index. */
	private static function sort_key( $entry, $origin, $index ) {
		$timestamp = is_string( $entry['timestamp'] ?? null ) && preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/D', $entry['timestamp'] ) ? $entry['timestamp'] : '';
		return array( $timestamp, $origin, (int) $index );
	}

	/** Merge without rewriting legacy evidence or guessing its trigger from current settings. */
	public static function page( $id, $cursor = '' ) {
		global $wpdb;
		$id = absint( $id );
		if ( ! self::can_read( $id ) ) {
			return new WP_Error( 'history_forbidden', __( 'Notification history is not available.', 'restaurant-reservations' ) ); }
		$meta   = get_post_meta( $id, 'rtb', true );
		$legacy = is_array( $meta['notification_diagnostics'] ?? null ) ? array_values( $meta['notification_diagnostics'] ) : array();
		$state  = '' === $cursor ? array(
			'booking'      => $id,
			'user'         => get_current_user_id(),
			'blog'         => get_current_blog_id(),
			'expires'      => time() + HOUR_IN_SECONDS,
			'upper'        => (int) $wpdb->get_var( $wpdb->prepare( "SELECT MAX(meta_id) FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $id, self::META_KEY ) ),
			'legacy_count' => count( $legacy ),
			'last'         => array(),
		) : self::decode_cursor( $cursor, $id );
		if ( ! $state ) {
			return new WP_Error( 'history_cursor_invalid', __( 'History changed or the request expired. Refresh history.', 'restaurant-reservations' ) ); }
		$separate = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s AND meta_id <= %d ORDER BY meta_id ASC",
				$id,
				self::META_KEY,
				$state['upper']
			),
			ARRAY_A
		);
		$rows     = array();
		$seen     = array();
		// Prefer an existing embedded copy for a proven UUID duplicate. Its
		// source-index key cannot shift when the capped separate copy is pruned.
		foreach ( array_slice( $legacy, 0, $state['legacy_count'] ) as $index => $entry ) {
			if ( ! is_array( $entry ) ) {
				continue; }
			$uuid = $entry['id'] ?? '';
			if ( is_string( $uuid ) && wp_is_uuid( $uuid ) ) {
				if ( isset( $seen[ strtolower( $uuid ) ] ) ) {
					continue; }
				$seen[ strtolower( $uuid ) ] = true;
			}
			$rows[] = array(
				'entry' => $entry,
				'key'   => self::sort_key( $entry, 0, $index ),
			);
		}
		foreach ( $separate as $record ) {
			$entry = maybe_unserialize( $record['meta_value'] );
			if ( ! is_array( $entry ) ) {
				continue; }
			$uuid = $entry['id'] ?? '';
			if ( is_string( $uuid ) && wp_is_uuid( $uuid ) ) {
				if ( isset( $seen[ strtolower( $uuid ) ] ) ) {
					continue; }
				$seen[ strtolower( $uuid ) ] = true;
			}
			$rows[] = array(
				'entry' => $entry,
				'key'   => self::sort_key( $entry, 1, $record['meta_id'] ),
			);
		}
		usort(
			$rows,
			static function ( $a, $b ) {
				return $b['key'] <=> $a['key'];
			}
		);
		$total = count( $rows );
		if ( $state['last'] ) {
			$rows = array_values(
				array_filter(
					$rows,
					static function ( $row ) use ( $state ) {
						return $row['key'] < $state['last'];
					}
				)
			); }
		$has_more = count( $rows ) > self::PAGE_SIZE;
		$rows     = array_slice( $rows, 0, self::PAGE_SIZE );
		$next     = '';
		$current  = self::encode_cursor( $state );
		if ( $has_more ) {
			$state['last'] = end( $rows )['key'];
			$next          = self::encode_cursor( $state ); }
		return array(
			'rows'    => array_map(
				static function ( $row ) {
					return array_merge( self::display_entry( $row['entry'] ), array( 'reader_id' => ( $row['key'][1] ? 'meta:' : 'legacy:' ) . $row['key'][2] ) );
				},
				$rows
			),
			'current' => $current,
			'next'    => $next,
			'total'   => $total,
		);
	}

	public static function display_entry( $entry ) {
		$unknown = __( 'Unknown', 'restaurant-reservations' );
		$events  = array(
			'new_submission'                  => __( 'New booking', 'restaurant-reservations' ),
			'new_booking'                     => __( 'New booking', 'restaurant-reservations' ),
			'auto_confirmed_booking'          => __( 'Booking automatically confirmed', 'restaurant-reservations' ),
			'rtb_confirmed_booking'           => __( 'Booking automatically confirmed', 'restaurant-reservations' ),
			'pending_to_confirmed'            => __( 'Booking confirmed', 'restaurant-reservations' ),
			'booking_confirmed'               => __( 'Booking confirmed', 'restaurant-reservations' ),
			'pending_to_closed'               => __( 'Booking closed', 'restaurant-reservations' ),
			'confirmed_to_closed'             => __( 'Booking closed', 'restaurant-reservations' ),
			'booking_closed'                  => __( 'Booking closed', 'restaurant-reservations' ),
			'booking_cancelled'               => __( 'Booking cancelled', 'restaurant-reservations' ),
			'booking_payment_pending'         => __( 'Payment pending', 'restaurant-reservations' ),
			'reminder'                        => __( 'Booking reminder', 'restaurant-reservations' ),
			'booking_reminder'                => __( 'Booking reminder', 'restaurant-reservations' ),
			'late_user'                       => __( 'Late arrival', 'restaurant-reservations' ),
			'late_for_booking'                => __( 'Late arrival', 'restaurant-reservations' ),
			'post_reservation_follow_up_user' => __( 'Post-booking follow-up', 'restaurant-reservations' ),
			'post_booking_follow_up'          => __( 'Post-booking follow-up', 'restaurant-reservations' ),
			'manual'                          => __( 'Staff message', 'restaurant-reservations' ),
			'admin_email_notice'              => __( 'Staff message', 'restaurant-reservations' ),
			'daily_summary'                   => __( 'Daily summary', 'restaurant-reservations' ),
		);
		// A legacy standard event is evidence. table_event is only a rendering sentinel.
		$event = self::key( $entry['trigger_event'] ?? '' );
		if ( '' === $event && 'table_event' !== ( $entry['event'] ?? '' ) ) {
			$event = self::key( $entry['event'] ?? '' ); }
		$date = __( 'Unknown date', 'restaurant-reservations' );
		if ( is_string( $entry['timestamp'] ?? null ) && preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/D', $entry['timestamp'] ) ) {
			$parsed = strtotime( $entry['timestamp'] );
			if ( false !== $parsed ) {
				$date = wp_date( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $parsed, wp_timezone() ); }
		}
		$statuses = array(
			'send_accepted' => __( 'Accepted by sending service', 'restaurant-reservations' ),
			'send_failed'   => __( 'Send failed', 'restaurant-reservations' ),
			'not_generated' => __( 'Not sent (could not be prepared)', 'restaurant-reservations' ),
		);
		$reasons  = array(
			''                        => __( 'No additional reason recorded.', 'restaurant-reservations' ),
			'preparation_failed'       => __( 'Notification could not be prepared.', 'restaurant-reservations' ),
			'transport_rejected'       => __( 'Sending service did not accept the notification.', 'restaurant-reservations' ),
			'paid_runtime_unavailable' => __( 'Required paid capability was unavailable.', 'restaurant-reservations' ),
		);
		return array(
			'date'               => $date,
			'trigger'            => $events[ $event ] ?? ( '' === $event || 'table_event' === $event ? __( 'Trigger unavailable', 'restaurant-reservations' ) : __( 'Unknown trigger', 'restaurant-reservations' ) ),
			'trigger_identifier' => isset( $events[ $event ] ) || 'table_event' === $event ? '' : $event,
			'type'               => array(
				'email' => __( 'Email', 'restaurant-reservations' ),
				'sms'   => __( 'SMS', 'restaurant-reservations' ),
			)[ self::key( $entry['type'] ?? '' ) ] ?? $unknown,
			'target'             => array(
				'user'  => __( 'User', 'restaurant-reservations' ),
				'admin' => __( 'Administrator', 'restaurant-reservations' ),
			)[ self::key( $entry['target'] ?? '' ) ] ?? $unknown,
			'outcome'            => $statuses[ self::key( $entry['status'] ?? '' ) ] ?? $unknown,
			'reason'             => $reasons[ self::key( $entry['reason'] ?? '' ) ] ?? $unknown,
			'id'                 => is_string( $entry['id'] ?? null ) && wp_is_uuid( $entry['id'] ) ? $entry['id'] : $unknown,
			'notification_id'    => is_scalar( $entry['notification_id'] ?? null ) ? absint( $entry['notification_id'] ) : 0,
		);
	}
}
