<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbAJAX' ) ) {
	/**
	 * Class to handle AJAX date interactions for Restaurant Reservations
	 *
	 * @since 2.0.0
	 */
	class rtbAJAX {

		/**
		 * The location we're getting timeslots for, if specified
		 * @since 2.3.6
		 */
		public $location;

		/**
		 * The year of the booking date we're getting timeslots for
		 * @since 2.0.0
		 */
		public $year;

		/**
		 * The month of the booking date we're getting timeslots for
		 * @since 2.0.0
		 */
		public $month;

		/**
		 * The day of the booking date we're getting timeslots for
		 * @since 2.0.0
		 */
		public $day;

		/**
		 * The time of the booking we're getting timeslots for
		 * @since 2.1.5
		 */
		public $time;

		/**
		 * The party size we're looking to find valid tables for
		 * @since 2.1.7
		 */
		public $party;

		/**
		 * The ID of the booking being accessed by the AJAX request
		 * @since 2.6.22
		 */
		public $booking_id;

		/**
		 * The ID of the location being accessed by the AJAX request
		 * @since 2.6.22
		 */
		public $location_id;

		public function __construct() {

			add_action( 'wp_ajax_rtb_get_available_time_slots', array( $this, 'get_time_slots' ) );
			add_action( 'wp_ajax_nopriv_rtb_get_available_time_slots', array( $this, 'get_time_slots' ) );

			add_action( 'wp_ajax_rtb_find_reservations', array( $this, 'get_reservations' ) );
			add_action( 'wp_ajax_nopriv_rtb_find_reservations', array( $this, 'get_reservations' ) );

			add_action( 'wp_ajax_rtb_cancel_reservations', array( $this, 'cancel_reservation' ), 10, 0 );
			add_action( 'wp_ajax_nopriv_rtb_cancel_reservations', array( $this, 'cancel_reservation' ), 10, 0 );

			add_action( 'wp_ajax_rtb_get_available_party_size', array( $this, 'get_available_party_size' ) );
			add_action( 'wp_ajax_nopriv_rtb_get_available_party_size', array( $this, 'get_available_party_size' ) );

			add_action( 'wp_ajax_rtb_get_available_tables', array( $this, 'get_available_tables' ) );
			add_action( 'wp_ajax_nopriv_rtb_get_available_tables', array( $this, 'get_available_tables' ) );

			add_action( 'wp_ajax_rtb_reset_notifications', array( $this, 'reset_notifications' ) );
		}

		/**
		 * Get reservations that are associated with the email address that was sent
		 * @since 2.1.0
		 */
		public function get_reservations() {
			global $wpdb, $rtb_controller;

			if ( !check_ajax_referer( 'rtb-booking-form', 'nonce' ) ) {
				rtbHelper::bad_nonce_ajax();
			}

			$email = isset($_POST['booking_email']) ? sanitize_email( $_POST['booking_email'] ) : '';

			if ( ! $email ) {
				wp_send_json_error(
					array(
						'error' => 'noemail',
						'msg' => __( 'The email you entered is not valid.', 'restaurant-reservations' ),
					)
				);
			}

			$booking_status_lbls = $rtb_controller->cpts->booking_statuses;

			$bookings = array();
			$booking_ids = $wpdb->get_results(
				$wpdb->prepare("
					SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'rtb' AND `meta_value` LIKE %s", 
					'%' . $email . '%'
				)
			);

			foreach ( $booking_ids as $booking_id ) {
				$booking = new rtbBooking();
				if ( $booking->load_post( $booking_id->post_id ) ) {
					$booking_date = (new DateTime($booking->date, wp_timezone()))->format('U');
					if ( in_array($booking->post_status, ['pending', 'payment_pending', 'payment_failed', 'confirmed'] ) and time() < $booking_date ) {
						$bookings[] = array(
							'ID'         => $booking->ID,
							'email'      => $booking->email,
							'datetime'   => $booking->format_date( $booking->date ),
							'datetime_u' => $booking_date,
							'party'      => $booking->party,
							'status'     => $booking->post_status,
							'status_lbl' => $booking_status_lbls[$booking->post_status]['label']
						);
					}
				}
			}

			if ( ! empty( $bookings ) ) {
				wp_send_json_success(
					array(
						'bookings' => $bookings
					)
				);
			}
			else {
				wp_send_json_error(
					array(
						'error' => 'nobookings',
						'msg' => esc_html( $rtb_controller->settings->get_setting( 'label-modify-no-bookings-found'  ) ),
					)
				);
			}

			die();
		}

		/**
		 * Cancel a reservation based on its ID, with the email address used for confirmation
		 * @since 2.1.0
		 */
		public function cancel_reservation( $ajax = true ) {
			global $rtb_controller;

			if ( $ajax && !check_ajax_referer( 'rtb-booking-form', 'nonce' ) ) {
				rtbHelper::bad_nonce_ajax();
			}

			$cancelled_redirect = $rtb_controller->settings->get_setting( 'cancelled-redirect-page' );

			$booking_id = isset($_REQUEST['booking_id']) ? absint( $_REQUEST['booking_id'] ) : '';
			$booking_email = isset($_REQUEST['booking_email']) ? sanitize_email( $_REQUEST['booking_email'] ) : '';

			$success = false;
			$error = array(
				'error' => 'unknown',
				'msg' => __( 'Unkown error. Please try again', 'restaurant-reservations' )
			);

			$booking = new rtbBooking();
			if ( $booking->load_post( $booking_id ) ) {
				if ( $booking_email == $booking->email ) {
					wp_update_post( array( 'ID' => $booking->ID, 'post_status' => 'cancelled' ) );

					$success = true;
				}
				else {
					$error = array(
						'error' => 'invalidemail',
						'msg' => __( 'No booking matches the information that was sent.', 'restaurant-reservations' ),
					);
				}
			}
			else {
				$error = array(
					'error' => 'invalidid',
					'msg' => __( 'No booking matches the information that was sent.', 'restaurant-reservations' ),
				);
			}

			if ( $ajax ) { 
				if ( $success ) {

					$response = array( 'booking_id' => $booking_id );

					if( '' != $cancelled_redirect ) {
						$response['cancelled_redirect'] = $cancelled_redirect;
					}

					wp_send_json_success( $response );
				}
				else {
					wp_send_json_error( $error );
				}
			}
			else {
				$redirect_url = '';

				if( '' != $cancelled_redirect && $success ) {
					$redirect_url = $cancelled_redirect;
				}
				else {
					$booking_page_id = $rtb_controller->settings->get_setting( 'booking-page' );
					$booking_page_url = get_permalink( $booking_page_id );

					$redirect_url = add_query_arg(
						array(
							'bookingCancelled' => $success ? 'success' : 'fail'
						),
						$booking_page_url
					);
				}

				if( wp_redirect( $redirect_url ) ) {
					exit;
				}

				header( "Location: {$redirect_url}", true, 302 );
				exit;
			}
		}

		/**
		 * Get available timeslots when "Max Reservations" or "Max People" is enabled
		 * @since 2.0.0
		 */
		public function get_time_slots() {
			global $rtb_controller;

			if ( !check_ajax_referer( 'rtb-booking-form', 'nonce' ) ) {
				rtbHelper::bad_nonce_ajax();
			}

			// proessing request for this date
			$this->location = ! empty( $_POST['location'] ) ? get_term( intval( $_POST['location'] ) ) : false;

			$this->year = sanitize_text_field( $_POST['year'] );
			$this->month = sanitize_text_field( $_POST['month'] );
			$this->day = sanitize_text_field( $_POST['day'] );

			$interval = $rtb_controller->settings->get_setting( 'time-interval' ) * 60;

			// Helper functions
			$finalize_response = function ( $open_close_pair_list = array() ) {

				$valid_times = array();

				if ( ! empty( $open_close_pair_list ) ) {

					foreach ( $open_close_pair_list as $pair ) {

						$valid_times[] = array(
							'from'     => $this->format_pickadate_time( $pair['from'] ),
							'to'       => $this->format_pickadate_time( $pair['to'] ),
							'inverted' => true
						);
					}
				}

				echo json_encode( $valid_times );

				die();
			};

			$consolidating_timeslots_to_timeframes = function( $slots ) use( $interval ) {
				$timeframe = array();

				$slots_count = count( $slots );
				if( 0 < $slots_count ) {

					$current_pair = array( 'from' => $slots[ 0 ] );

					for ( $i = 1; $i < $slots_count; $i++) {
						if( $slots[ $i ] - $slots[ $i - 1 ] !== $interval ) {
							$current_pair[ 'to' ] = $slots[ $i - 1 ];
							$timeframe[] = $current_pair;

							$current_pair = array( 'from' => $slots[ $i ] );
						}
					}

					$current_pair[ 'to' ] = $slots[ $i - 1 ];
					$timeframe[] = $current_pair;
				}

				return $timeframe;
			};

			// Get opening/closing times for this particular day
			$hours = $this->get_opening_hours();

			// If the restaurant is closed that day
			// If Enable Max Reservation not set
			if ( 1 > count( $hours ) or ! $rtb_controller->settings->get_setting( 'rtb-enable-max-tables' ) ) {
				
				$finalize_response( $hours );
			}

			$location_id = ! empty( $this->location ) ? $this->location->term_id : false;

			$location_slug = ! empty( $this->location ) ? $this->location->slug : false;

			$dining_block_seconds = (int) $rtb_controller->settings->get_setting( 'rtb-dining-block-length' ) * 60;

			$min_party_size = (int) $rtb_controller->settings->get_setting( 'party-size-min' );

			$all_possible_slots = $this->get_all_possible_timeslots( $hours );

			// Get all current bookings sorted by date
			$args = array(
				'posts_per_page' => -1,
				'date_range'     => 'dates',
				'start_date'     => $this->year . '-' . $this->month . '-' . $this->day,
				'end_date'       => $this->year . '-' . $this->month . '-' . $this->day,
				'post_status'    => ['pending', 'payment_pending', 'confirmed', 'arrived']
			);

			// If there are multiple locations, a location is selected, and 
			// max reservations and/or seats has been enabled for this specific location
			if ( ! empty( $location_slug ) and ( $rtb_controller->settings->is_location_setting_enabled( 'rtb-max-tables-count', $location_slug ) or $rtb_controller->settings->is_location_setting_enabled( 'rtb-max-people-count', $location_slug ) ) ) {

				$tax_query = array(
					array(
						'taxonomy'	=> $rtb_controller->locations->location_taxonomy,
						'field'		=> 'term_id',
						'terms'		=> $this->location->term_id
					)
				);

				$args['tax_query'] = $tax_query;
			}

			$query = new rtbQuery( $args, 'ajax-get-time-slots' );
			$query->prepare_args();
			$bookings = $query->get_bookings();

			// This array holds bookings for all slots by expanding the booking by 
			// dining block length to help finding the overlapped bookings for time-slots
			$all_bookings_by_slots = [];
			foreach ( $bookings as $key => $booking ) {
				// Convert booking date to seconds from UNIX
				$booking_time = ( new DateTime( $booking->date , wp_timezone() ) )->format( 'U' );
				if( ! array_key_exists( $booking_time, $all_bookings_by_slots ) ) {
					$all_bookings_by_slots[$booking_time] = [
						'total_bookings' => 0,
						'total_guest'    => 0,
						'overlapped'     => false
					];
				}
				$all_bookings_by_slots[$booking_time]['total_bookings']++;
				$all_bookings_by_slots[$booking_time]['total_guest'] += intval( $booking->party );

				/**
				 * Expanding bookings
				 * Example: If I have someone booked at 1pm who will be in the restaurant for 120 minutes, 
				 * that means they will be in the restaurant until 3pm. There is another booking at 2pm. 
				 * That means, from 2pm to 3pm, there are already two separate reservations in the restaurant.
				 */
				$end = $booking_time + $dining_block_seconds;
				$next = $booking_time + $interval;
				while($next < $end) {
					if( ! array_key_exists( $next, $all_bookings_by_slots ) ) {
						$all_bookings_by_slots[$next] = [
							'total_bookings' => 0,
							'total_guest'    => 0,
							'overlapped'     => false
						];
					}
					$all_bookings_by_slots[$next]['overlapped'] = true;
					$all_bookings_by_slots[$next]['total_bookings']++;
					$all_bookings_by_slots[$next]['total_guest'] += intval( $booking->party );
					$next += $interval;
				}
			}

			$all_blocked_slots = [];

			// Go through all bookings and figure out when we're at or above the 
			// max reservation or max people and mark that slot as blocked
			foreach ( $all_bookings_by_slots as $slot => $data ) {

				$datetime = ( new DateTime( '@' . $slot ) )->setTimezone( wp_timezone() )->format( 'Y-m-d H:i:s');

				$timeslot = rtb_get_timeslot( $datetime, $location_id );

				$max_reservations = (int) $rtb_controller->settings->get_setting( 'rtb-max-tables-count', $location_slug, $timeslot );

				$max_people = (int) $rtb_controller->settings->get_setting( 'rtb-max-people-count', $location_slug, $timeslot );

				if ( isset( $max_reservations ) and $max_reservations > 0 ) {
					if( $max_reservations <= $data['total_bookings'] ) {
						$all_blocked_slots[] = $slot;
					}
				}
				else if ( isset( $max_people ) and $max_people > 0 ) {
					/**
					 * min_party_size = 10, max_people = 100, 6 bookings of total 91 guests
					 * Now, if anybody wants to book for at least 10 people, it is not possible
					 * because the total will surpass the max_people (100)
					 * thus reducing min_party_size from max_people
					 *
					 * $max_people can be zero when min_party_size is same as max_people
					 */
					$max_people = $max_people - $min_party_size;

					if( $max_people < $data['total_guest'] ) {
						$all_blocked_slots[] = $slot;
					}
				}
			}

			// Mark slots unavailable, due to dinning block length
			$additional_blocked_slots = [];
			foreach ($all_blocked_slots as $slot) {
				// blocking before this slot
				$begin = $slot - $dining_block_seconds;
				/**
				 * interval 30 minutes, dinning_length 120 minutes, slot 10:00am
				 * additional blockings before shall be 8:30am,9:00am and 9:30am
				 * thus skipping 8:00am which is valid
				 * 
				 * @var unix timestamp
				 */
				$next = $begin + $interval;
				while($next < $slot) {
					$additional_blocked_slots[] = $next;
					$next += $interval;
				}

				// block after this slot only when this slot is not overlapped
				// Overlapped slots should block only backwards, but not afterward
				if( $all_bookings_by_slots[$slot]['overlapped'] ) {
					continue;
				}

				// blocking after this slot
				$end = $slot + $dining_block_seconds;
				/**
				 * interval 30 minutes, dinning_length 120 minutes, slot 10:00am
				 * additional blockings after shall be 10:30am,11:00am and 1130am
				 * thus skipping 12:00pm which is valid
				 * 
				 * @var unix timestamp
				 */
				$next = $slot + $interval;
				while($next < $end) {
					$additional_blocked_slots[] = $next;
					$next += $interval;
				}
			}

			$all_blocked_slots = array_unique(
				array_merge( $all_blocked_slots, $additional_blocked_slots ),
				SORT_NUMERIC
			);

			sort( $all_blocked_slots, SORT_NUMERIC );

			// remove blocked slots from available slots
			$available_slots = array_diff( $all_possible_slots, $all_blocked_slots );
			sort( $available_slots, SORT_NUMERIC );

			// consolidating timeslots to timeframes
			$timeframes = $consolidating_timeslots_to_timeframes( $available_slots );

			$finalize_response( $timeframes );
		}

		public function get_opening_hours() {
			global $rtb_controller;

			$location_slug = ! empty( $this->location ) ? $this->location->slug : false;

			$schedule_closed = $rtb_controller->settings->get_setting( 'schedule-closed', $location_slug );
			$schedule_closed = is_array( $schedule_closed ) ? $schedule_closed : array();

			$valid_times = array();

			// Check if this date is an exception to the rules
			if ( $schedule_closed !== 'undefined' ) {

				$selected_date = ( new DateTime('now', wp_timezone() ) )
					->setTime(0, 0, 2)
					->setDate( $this->year, $this->month, $this->day );

				foreach ( $schedule_closed as $ids => $closing ) {
					if( array_key_exists( 'date_range', $closing ) ) {

						$start = ! empty( $closing['date_range']['start'] )
							? new DateTime( $closing['date_range']['start'], wp_timezone() )
							: new DateTime( 'now', wp_timezone() );
						$start->setTime(0, 0);

						$end = !empty( $closing['date_range']['end'] )
							? new DateTime( $closing['date_range']['end'], wp_timezone() )
							: ( new DateTime( 'now', wp_timezone() ) )->add( new DateInterval( 'P10Y' ) );
						$end->setTime(23, 59, 58);

						if( $start < $selected_date && $selected_date < $end ) {
							$exception = clone $selected_date;
						}
						else {
							// Set anything to void this rule
							$exception = clone $selected_date;
							$exception->add( new DateInterval( 'P1Y' ) );
						}
					}
					else {
						$exception = ( new DateTime( $closing['date'], wp_timezone() ) )->setTime(0, 0, 2);
					}

					if ( $exception == $selected_date ) {

						// Closed all day
						if ( ! isset( $closing['time'] ) || $closing['time'] == 'undefined' ) {
							return false;
						}

						if ( $closing['time']['start'] !== 'undefined' ) {
							$open_time = ( new DateTime( $exception->format( 'Y-m-d' ) . ' ' . $closing['time']['start'], wp_timezone() ) )->format( 'U' );
						}
						else {

							// Start of the day
							$open_time = ( new DateTime( $exception->format( 'Y-m-d' ), wp_timezone() ) )->format('U');
						}

						if ( $closing['time']['end'] !== 'undefined' ) {
							$close_time = ( new DateTime( $exception->format( 'Y-m-d' ) . ' ' . $closing['time']['end'], wp_timezone() ) )->format( 'U' );
						}
						else {

							// End of the day
							$close_time = ( new DateTime( $exception->format( 'Y-m-d' ) . ' 23:59:59', wp_timezone() ) )->format( 'U' );
						}

						$open_time = $this->get_earliest_time( $open_time );

						if ( $open_time <= $close_time ) {
							$valid_times[] = ['from' => $open_time, 'to' => $close_time];
						}
					}
				}

				// Exit early if this date is an exception
				if ( isset( $open_time ) ) {
					return $valid_times;
				}
			}

			$schedule_open = $rtb_controller->settings->get_setting( 'schedule-open', $location_slug );
			$schedule_open = is_array( $schedule_open ) ? $schedule_open : array();

			// Get any rules which apply to this weekday
			$day_of_week =  strtolower(
				( new DateTime( $this->year . '-' . $this->month . '-' . $this->day . ' 1:00:00', wp_timezone() ) )->format( 'l' )
			);

			$selected_date = ( new DateTime('now', wp_timezone() ) )
					->setTime(0, 0, 2)
					->setDate( $this->year, $this->month, $this->day );

			foreach ( $schedule_open as $opening ) {

				if ( $opening['weekdays'] !== 'undefined' ) {

					foreach ( $opening['weekdays'] as $weekday => $value ) {

						if ( $weekday == $day_of_week ) {

							if ( isset( $opening['time'] ) && $opening['time']['start'] !== 'undefined' ) {

								$open_time = ( new DateTime( $selected_date->format( 'Y-m-d' ) .' '. $opening['time']['start'], wp_timezone() ) )->format( 'U' );
							}
							else {

								// Start of the day
								$open_time = ( new DateTime( $selected_date->format( 'Y-m-d' ), wp_timezone() ) )->format('U');
							}

							if ( isset( $opening['time'] ) && $opening['time']['end'] !== 'undefined' ) {

								$close_time = ( new DateTime( $selected_date->format( 'Y-m-d' ) .' '. $opening['time']['end'], wp_timezone() ) )->format( 'U' );
							}
							else {

								// End of the day
								$close_time = ( new DateTime( $selected_date->format( 'Y-m-d' ) . ' 23:59:59', wp_timezone() ) )->format( 'U' ); 
							}

							$open_time = $this->get_earliest_time( $open_time );

							if ( $open_time <= $close_time ) {

								$valid_times[] = ['from' => $open_time, 'to' => $close_time];
							}
						}
					}
				}
			}

			// Pass any valid times located
			return $valid_times;
		}

		public function get_earliest_time( $open_time ) {
			global $rtb_controller;

			$interval = $rtb_controller->settings->get_setting( 'time-interval' ) * 60;

			$selected_date = ( new DateTime('now', wp_timezone() ) )
					->setTime(0, 0, 2)
					->setDate( $this->year, $this->month, $this->day );

			// adjust open time with respect to the current time of the day for upcoming timeslots
			$current_time = ( new DateTime( 'now', wp_timezone() ) )->format( 'U' );

			$late_bookings = ( is_admin() && current_user_can( 'manage_bookings' ) ) ? '' : $rtb_controller->settings->get_setting( 'late-bookings' );

			if( $current_time > $open_time ) {
				while( $current_time > $open_time ) {
					$open_time += $interval;
				}
			}

			// adjust the open time for the Late Bookings option
			if ( is_numeric($late_bookings) && $late_bookings % 1 === 0 ) {
				$time_calc = ( new DateTime( 'now', wp_timezone() ) )->format( 'U' ) + ( $late_bookings * 60 );
				while ($time_calc > $open_time) {
					$open_time = $open_time + $interval;
				}
			}

			return $open_time;
		}

		/**
		 * Gets all of the pick-a-date timeslots for a given set of hours 
		 * @since 2.6.11
		 */
		public function get_all_possible_timeslots( $pairs ) {
			global $rtb_controller;

			if ( ! is_array( $pairs ) )  { return $pairs; }

			$midnight = ( new DateTime( $this->year . '-' . $this->month . '-' . $this->day . ' 00:00:00', wp_timezone() ) )->format( 'U' );

			$all_slots  = array();
			$current_slot_time = $midnight;
			$interval = $interval = $rtb_controller->settings->get_setting( 'time-interval' ) * 60;

			while ( $current_slot_time < ( $midnight + DAY_IN_SECONDS - 1 ) ) {

				$all_slots[$current_slot_time] = false;

				$current_slot_time += $interval;
			}

			foreach ( $pairs as $pair ) {

				foreach ( $all_slots as $slot_time => $available ) {

					if ( $slot_time >= $pair['from'] and $slot_time <= $pair['to'] ) { $all_slots[ $slot_time ] = true; }
				}
			}

			return array_keys( array_filter( $all_slots ) );
		}
		
		/**
		 * Get number of seats remaining avilable to be booked
		 * @since 2.1.5
		 */
		public function get_available_party_size() {
			global $rtb_controller;

			if ( !check_ajax_referer( 'rtb-booking-form', 'nonce' ) ) {
				rtbHelper::bad_nonce_ajax();
			}

			$this->location = ! empty( $_POST['location'] ) ? get_term( intval( $_POST['location'] ) ) : false;
			$this->year = sanitize_text_field( $_POST['year'] );
			$this->month = sanitize_text_field( $_POST['month'] );
			$this->day = sanitize_text_field( $_POST['day'] );
			$this->time = sanitize_text_field( $_POST['time'] );

			$location_slug = ! empty( $this->location ) ? $this->location->slug : false;
			$location_id = ! empty( $this->location ) ? $this->location->term_id : false;

			$datetime = $this->year . '-' . $this->month . '-' . $this->day . ' ' . $this->time;
			$timeslot = rtb_get_timeslot( $datetime, $location_id );

			$max_people = (int) $rtb_controller->settings->get_setting( 'rtb-max-people-count', $location_slug, $timeslot );

			$dining_block_seconds = (int) $rtb_controller->settings->get_setting( 'rtb-dining-block-length' )* 60 - 1;  // Take 1 second off, to avoid bookings that start or end exactly at the beginning of a booking block
			
			// Get opening/closing times for this particular day
			$hours = $this->get_opening_hours();
			
			// If the restaurant is closed that day, return false
			if ( 1 > count( $hours ) ) { die(); }

			// If no time is selected, return false
			if ( ! $this->time ) { die(); }
			
			$args = array(
				'posts_per_page' => -1,
				'date_range' => 'dates',
				'start_date' => $this->year . '-' . $this->month . '-' . $this->day,
				'end_date' => $this->year . '-' . $this->month . '-' . $this->day
			);

			// Ignore cancelled bookings when restricting party size
			$args['post_status'] = array_diff( array_keys( $rtb_controller->cpts->booking_statuses ), array( 'cancelled' ) );

			// If there are multiple locations, a location is selected, and 
			// max seats has been enabled for this specific location
			if ( ! empty( $location_slug ) and $rtb_controller->settings->is_location_setting_enabled( 'rtb-max-people-count', $location_slug ) ) {

				$tax_query = array(
					array(
						'taxonomy'	=> $rtb_controller->locations->location_taxonomy,
						'field'		=> 'term_id',
						'terms'		=> $this->location->term_id
					)
				);

				$args['tax_query'] = $tax_query;
			}
			
			$query = new rtbQuery( $args );
			$query->prepare_args();
				
			// Get all current bookings sorted by date
			$bookings = $query->get_bookings();

			$tmzn = wp_timezone();

			$selected_date_time = ( new DateTime( $this->year . '-' . $this->month . '-' . $this->day . ' ' . $this->time, $tmzn ) )->format( 'U' );
			$selected_date_time_start = $selected_date_time - $dining_block_seconds;
			$selected_date_time_end = $selected_date_time + $dining_block_seconds;
			$party_sizes = [];

			if ($max_people != 'undefined' and $max_people != 0) {

				$max_time_size = 0;
				$current_times = array();
				$party_sizes = array();

				// Go through all current booking and collect the total party size
				foreach ( $bookings as $key => $booking ) {

					// Convert booking date to seconds from UNIX
					$booking_time = ( new DateTime( $booking->date, $tmzn ) )->format( 'U' );
					
					// Ignore bookings outside of our time range
					if ($booking_time < $selected_date_time_start or $booking_time > $selected_date_time_end) { continue; }
					
					$current_times[] = $booking_time;
					$party_sizes[] = (int) $booking->party;
					
					while ( sizeOf( $current_times ) > 0 and reset( $current_times ) < $booking_time - $dining_block_seconds ) { 
						//save the time to know when the blocking potentially ends
						$removed_time = reset( $current_times );

						// remove the expired time and party size
						array_shift( $current_times ); 
						array_shift( $party_sizes ); 
					}
					
					$max_time_size = max( $max_time_size, array_sum( $party_sizes ) );
				}

				$response = (object) array( 'available_spots' => $max_people - $max_time_size);

				echo json_encode($response);
				
				die();
			} else {
				return false;
			}
		}

		/**
		 * Resets the table-style notifications entry, so they can be re-imported
		 * @since 2.6.5
		 */
		public function reset_notifications() {
			global $rtb_controller;

			if ( !check_ajax_referer( 'rtb-admin', 'nonce' ) ) {
				rtbHelper::bad_nonce_ajax();
			}

			$rtb_controller->settings->set_setting( 'booking-notifications', null );

  			$rtb_controller->settings->save_settings();

  			die();
		}

		/**
		 * Get tables available to be booked at a specific time and party size
		 * @since 2.1.7
		 */
		public function get_available_tables() {
			global $rtb_controller;

			if ( !check_ajax_referer( 'rtb-booking-form', 'nonce' ) ) {
				rtbHelper::bad_nonce_ajax();
			}

			$this->booking_id 	= isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
			$this->location_id 	= isset( $_POST['location_id'] ) ? intval( $_POST['location_id'] ) : 0;
			$this->year 		= isset( $_POST['year'] ) ? sanitize_text_field( $_POST['year'] ) : false;
			$this->month 		= isset( $_POST['month'] ) ? sanitize_text_field( $_POST['month'] ) : false;
			$this->day 			= isset( $_POST['day'] ) ? sanitize_text_field( $_POST['day'] ) : false;
			$this->time 		= isset( $_POST['time'] ) ? sanitize_text_field( $_POST['time'] ) : false;
			$this->party 		= isset( $_POST['party'] ) ? sanitize_text_field( $_POST['party'] ) : false;

			if ( ! isset( $this->year ) or ! isset( $this->month ) or ! isset( $this->day ) or ! isset( $this->time ) ) { return false; }

			$datetime = $this->year . '-' . $this->month . '-' . $this->day . ' ' . $this->time;

			$tables = $rtb_controller->settings->get_sorted_tables( $datetime, $this->location_id );

			$valid_tables = rtb_get_valid_tables( $datetime, $this->location_id);

			if ( $this->booking_id ) {
				
				$current_booking = new rtbBooking();
				$current_booking->load_post( $this->booking_id );

				if ( $current_booking->table ) { $valid_tables = array_merge( $valid_tables, $current_booking->table ); }
			}

			$return_tables = array();

			if ( isset( $this->party ) ) {

				$possible_combinations = array();
				foreach ( $valid_tables as $valid_table ) {

					// If the party size is between the min and max for the table, great
					if ( $tables[ $valid_table ]->min_people <= $this->party and $tables[ $valid_table ]->max_people >= $this->party ) {
						
						$possible_combinations[] = $valid_table;
					}
					// If the party is above the minimum for the table, look to see if combinations could work
					elseif ( $tables[ $valid_table ]->min_people <= $this->party ) {

						$combination = $this->get_combinations_chain( $tables, $valid_tables, $valid_table, $tables[ $valid_table ]->max_people, $this->party );

						if ( $combination ) { 
							$possible_combinations[] = $combination; 
						}
					}

					$return_tables = $this->format_tables( $possible_combinations );
				}
			}
			else {
				$return_tables = $this->format_tables( $valid_tables );
			}
			//update_option( "EWD_Debugging", 'tables: ' . print_r( $return_tables, true) );
			$selected_table = ( isset( $current_booking ) and $current_booking->table ) ? implode(', ', $current_booking->table ) : -1;

			$response = (object) array( 'available_tables' => $return_tables, 'selected_table' => $selected_table );

			echo json_encode($response);

			die();
		} 

		/**
		 * Recursively go through table combinations to find one that has enough seats
		 * @since 2.1.7
		 */
		public function get_combinations_chain(
			$tables, 
			$valid_tables, 
			$current_table, 
			$current_size, 
			$needed_size
		) {
			$table_chain[] = $current_table;

			// No combination specified
			if ( ! $tables[ $current_table ]->combinations ) {
				return false;
			}

			$possible_tables = explode( ',', $tables[ $current_table ]->combinations );

			foreach ( $possible_tables as $possible_table ) {

				// If the table has already been booked, continue
				if ( !in_array( $possible_table, $valid_tables) ) {
					continue;
				}

				// If the table can hold the group on its own, continue
				if ( $tables[ $possible_table ]->max_people >= $needed_size ) {
					continue;
				}

				$current_size += $tables[ $possible_table ]->max_people;
				$table_chain[] = $possible_table;

				if ( $current_size >= $needed_size ) {
					return implode(',', $table_chain);
				}
			}

			//no viable combination found
			return false;
		}

		/**
		 * Format the tables available to be booked as number(s)_string => human_value pairs
		 * @since 2.1.7
		 */
		public function format_tables( $table_numbers ) {
			global $rtb_controller;

			$formatted_tables = array();

			$location_slug = ! empty( $this->location_id ) ? get_term_field( 'slug', $this->location_id ) : false;
			
			$datetime = $this->year . '-' . $this->month . '-' . $this->day . ' ' . $this->time;
			$timeslot = rtb_get_timeslot( $datetime, $this->location_id );

			$deposits_enabled = ( $rtb_controller->settings->get_setting( 'require-deposit' ) and $rtb_controller->settings->get_setting( 'rtb-deposit-type' ) == 'table' );

			$tables = json_decode( html_entity_decode( $rtb_controller->settings->get_setting( 'rtb-tables', $location_slug, $timeslot ) ) );
			$tables = is_array( $tables ) ? $tables : array();

			foreach ( $table_numbers as $table_number ) {

				$table_parts = explode( ',', $table_number );

				$table_values = array(
					'numbers' 		=> '',
					'min_people'	=> 0,
					'max_people'	=> 0,
					'deposit'		=> 0,
				);

				foreach ( $tables as $table ) {
					if ( in_array($table->number, $table_parts) ) {
						$table_values['numbers'] .= ( strlen( $table_values['numbers'] ) ? ', ' : '' ) . $table->number;
						$table_values['min_people'] += $table->min_people;
						$table_values['max_people'] += $table->max_people;
						$table_values['deposit']	+= ( ! empty( $table->table_deposit ) ? $table->table_deposit : 0 );

						if ( ! isset( $table_values['section_name'] ) ) { $table_values['section_name'] = $this->get_section_name( $table->section ); }
					}
				}

				$table_values['deposit'] = $rtb_controller->settings->get_setting( 'rtb-currency-symbol-location' ) == 'before' ? $rtb_controller->settings->get_setting( 'rtb-stripe-currency-symbol' ) . $table_values['deposit'] : $table_values['deposit'] . $rtb_controller->settings->get_setting( 'rtb-stripe-currency-symbol' );

				$formatted_tables[ $table_values['numbers'] ] = $table_values['numbers'] . ' - ' . 
																$table_values['section_name'] . 
																' (min. ' . $table_values['min_people'] . 
																'/max. ' . $table_values['max_people'] . 
																( $deposits_enabled ? ( ' ' . $table_values['deposit'] ) : '' ) . ')';

				unset( $section_name );
			}

			return $formatted_tables;
		}

		public function get_section_name( $section_id ) {
			global $rtb_controller;

			$location_slug = ! empty( $this->location_id ) ? get_term_field( 'slug', $this->location_id ) : false;

			$sections = json_decode( html_entity_decode( $rtb_controller->settings->get_setting( 'rtb-table-sections', $location_slug ) ) );
			$sections = is_array( $sections ) ? $sections : array();

			foreach ( $sections as $section ) {

				if ( $section->section_id == $section_id ) { return $section->name; }
			}

			return false;
		}

		public function format_pickadate_time( $time ) {
			$obj = ( new DateTime( 'now' , wp_timezone() ) )->setTimestamp( $time );
			return array( $obj->format( 'G' ), $obj->format( 'i' ) );
		}
	}
}