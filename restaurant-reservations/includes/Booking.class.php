<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbBooking' ) ) {
/**
 * Class to handle a booking for Restaurant Table Bookings
 *
 * @since 0.0.1
 */
class rtbBooking {

	/**
	 * Whether or not this request has been processed. Used to prevent
	 * duplicate forms on one page from processing a booking form more than
	 * once.
	 * @since 0.0.1
	 */
	public $request_processed = false;

	/**
	 * Whether or not this request was successfully saved to the database.
	 * @since 0.0.1
	 */
	public $request_inserted = false;

	/**
	 * Raw input, clone of $_POST
	 * @var array
	 * @since 2.3.0
	 */
	public $raw_input = array();

	/**
	 * Whether or not this request was initiated from the admin area
	 * @since 2.6.17
	 */
	public $by_admin = false;

	/**
	 * Holds all validation errors found during booking request validation
	 * @since 2.7.0
	 */
	public $validation_errors = array();

	// The requested booking date ('Y/m/d' format)
	public $request_date;

	// The requested booking date ('h:i A' format)
	public $request_time;

	// from  WP post
	public $post;
	public $ID;
	public $name;
	public $date;
	public $message;
	public $post_status;

	// location
	public $location;

	// post meta
	public $party;
	public $email;
	public $phone;
	public $date_submission;
	public $logs;
	public $ip;
	public $consent_acquired;
	public $confirmed_user;
	public $deposit;
	public $table;
	public $payment_failure_message;
	public $receipt_id;
	public $mc_optin;

	// notifications
	public $reminder_sent;
	public $late_arrival_sent;
	public $post_reservation_follow_up_sent;
	public $reservation_notifications;

	// custom fields
	public $custom_fields;

	// payments
	public $stripe_payment_intent_id;
	public $stripe_payment_hold_status;
	public $payment_gateway;

	// The user ID (or rule code, if negative) that confirmed a particular booking
	public $temp_confirmed_user;

	// Disable sending notifications for a particular booking if false
	// Is this used by the notification class?
	public $send_notifications;

	public function __construct() {}

	/**
	 * Load the booking information from a WP_Post object or an ID
	 *
	 * @uses load_wp_post()
	 * @since 0.0.1
	 */
	public function load_post( $post ) {

		if ( is_int( $post ) || is_string( $post ) ) {
			$post = get_post( $post );
		}

		if ( is_object( $post ) && get_class( $post ) == 'WP_Post' && $post->post_type == RTB_BOOKING_POST_TYPE ) {
			$this->load_wp_post( $post );
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Load data from WP post object and retrieve metadata
	 *
	 * @uses load_post_metadata()
	 * @since 0.0.1
	 */
	public function load_wp_post( $post ) {
		// Store post for access to other data if needed by extensions
		$this->post = $post;

		$this->ID = $post->ID;
		$this->name = $post->post_title;
		$this->date = $post->post_date;
		$this->message = $post->post_content;
		$this->post_status = $post->post_status;

		$this->load_post_metadata();

		do_action( 'rtb_booking_load_post_data', $this, $post );
	}

	/**
	 * Store metadata for post
	 * @since 0.0.1
	 */
	public function load_post_metadata() {
		global $rtb_controller;

		$meta_defaults = array(
			'party' => '',
			'email' => '',
			'phone' => '',
			'date_submission' => '',
			'logs' => array(),
			'ip' => '',
			'consent_acquired' => '',
			'confirmed_user' => 0,
			'deposit' => '0',
			'table' => array(),
			'payment_failure_message' => '',
			'receipt_id' => '',
			'reminder_sent' => false,
			'late_arrival_sent' => false,
			'post_reservation_follow_up_sent' => false,
			'reservation_notifications'	=> array(),
			'mc_optin' => false
		);

		$meta_defaults = apply_filters( 'rtb_booking_metadata_defaults', $meta_defaults );

		if ( is_array( $meta = get_post_meta( $this->ID, 'rtb', true ) ) ) {
			$meta = array_merge( $meta_defaults, get_post_meta( $this->ID, 'rtb', true ) );
		} else {
			$meta = $meta_defaults;
		}

		$this->party = $meta['party'];
		$this->email = $meta['email'];
		$this->phone = $meta['phone'];
		$this->date_submission = $meta['date_submission'];
		$this->logs = $meta['logs'];
		$this->ip = $meta['ip'];
		$this->consent_acquired = $meta['consent_acquired'];
		$this->confirmed_user = $meta['confirmed_user'];
		$this->deposit = $meta['deposit'];
		$this->table = $meta['table'];
		$this->payment_failure_message = $meta['payment_failure_message'];
		$this->receipt_id = $meta['receipt_id'];

		$this->reminder_sent = $meta['reminder_sent'];
		$this->late_arrival_sent = $meta['late_arrival_sent'];
		$this->post_reservation_follow_up_sent = $meta['post_reservation_follow_up_sent'];

		$this->reservation_notifications = $meta['reservation_notifications'];

		// Did they opt out?
		$optout = $rtb_controller->settings->get_setting( 'mc-optout' );
		// Because mcfrtbInit::reload_booking_meta() does not fire when needed for unknown reason
		if ( $optout != 'no' ) {
			$this->mc_optin = $meta['mc_optin'];
		}
	}

	/**
	 * Prepare booking data loaded from the database for display in a booking
	 * form as request fields. This is used, eg, for splitting datetime values
	 * into date and time fields.
	 * @since 1.3
	 */
	public function prepare_request_data() {

		// Split $date to $request_date and $request_time
		if ( empty( $this->request_date ) || empty( $this->request_time ) ) {
			$date = new DateTime( $this->date, wp_timezone() );
			$this->request_date = $date->format( 'Y/m/d' );
			$this->request_time = $date->format( 'h:i A' );
		}
	}

	/**
	 * Format date
	 * @since 0.0.1
	 */
	public function format_date( $date ) {
		$date = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $date);
		return apply_filters( 'get_the_date', $date );
	}

	/**
	 * Format a timestamp into a human-readable date
	 *
	 * @since 1.7.1
	 */
	public function format_timestamp( $timestamp ) {
		$timestamp = $timestamp instanceof DateTime ? $timestamp->format('U') : $timestamp;
		$time = DateTime::createFromFormat( 'U', $timestamp ); 
		$time->setTimezone( wp_timezone() );
		
		return $time->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
	}

	/**
	 * Calculates the deposit required for a reservation, if any
	 *
	 * @since 2.1.0
	 */
	public function calculate_deposit() {
		global $rtb_controller;

		// table-based deposits
		if ( $rtb_controller->settings->get_setting( 'rtb-deposit-type' ) == 'table' ) { 

			// if invalid table, no deposit applied
			if ( empty( $this->table ) or ! is_array( $this->table ) ) { $deposit = 0; }
			
			// get all tables and select the deposits for any selected
			else {

				if ( empty( $this->location ) or ! term_exists( $this->location ) ) { $location_slug = false; }
				else {

					$location = get_term( $this->location );

					$location_slug = $location->slug;
				}

				$tables = json_decode( html_entity_decode( $rtb_controller->settings->get_setting( 'rtb-tables', $location_slug ) ) );
				$tables = is_array( $tables ) ? $tables : array();

				$deposit = 0;

				foreach ( $tables as $table ) {

					if ( in_array( $table->number, $this->table ) ) { $deposit += $table->table_deposit; }
				}
			}
		}
		// other deposit types
		else {

			$deposit = $rtb_controller->settings->get_setting( 'rtb-deposit-amount' );
	
			if ( $rtb_controller->settings->get_setting( 'rtb-deposit-applicable' ) == 'size_based' ) {
	
				$deposit =  $this->party < $rtb_controller->settings->get_setting( 'rtb-deposit-min-party-size' ) ? 0 : $deposit; 
			}
	
			if ( $rtb_controller->settings->get_setting( 'rtb-deposit-applicable' ) == 'time_based' ) {
	
				$deposit =  empty( $this->is_time_based_deposit_applicable() ) ? 0 : $deposit; 
			}
	
			if ( $rtb_controller->settings->get_setting( 'rtb-deposit-type' ) == 'guest' ) { $deposit = $deposit * $this->party; }
		}

		return apply_filters( 'rtb_booking_deposit_amount', $deposit, $this );
	}

	/**
	 * Flags a request as being initiated from the admin area
	 * 
	 * @since 2.6.17
	 */
	public function set_admin_request() {

		$this->by_admin = true;
	}


	/**
	 * Insert a new booking submission into the database
	 *
	 * Validates the data, adds it to the database and executes notifications
	 * @since 0.0.1
	 */
	public function insert_booking() {

		// Check if this request has already been processed. If multiple forms
		// exist on the same page, this prevents a single submission from
		// being added twice.
		if ( $this->request_processed === true ) {
			return true;
		}

		$this->request_processed = true;

		if ( empty( $this->ID ) ) {
			$action = 'insert';
		} else {
			$action = 'update';
		}

		$this->validate_submission($action);
		if ( $this->is_valid_submission() === false ) {
			return false;
		}

		if ( $this->insert_post_data() === false ) { 
			return false;
		} else {
			$this->request_inserted = true;
		}

		do_action( 'rtb_' . $action . '_booking', $this );

		return true;
	}

	/**
	 * Validate submission data. Expects to find data in $_POST.
	 *
	 * ************************** NOTE **************************
	 * This function also create and assign all the required member variable with 
	 * the acurate values which will be insreted in the DB. One special member, 
	 * raw_input of type array holds the exact copy of $_POST
	 * 
	 * Example:
	 * class a {
	 *     public function a() {
	 *         $this->name = 'John Doe';
	 *     }
	 *     public function b() {
	 *         echo $this->name;
	 *     }
	 * }
	 * 
	 * $a = new a();
	 * 
	 * var_dump($a);
	 * object(a)#1 (0) {
	 * }
	 * 
	 * $a->a();
	 * 
	 * var_dump($a);
	 * object(a)#1 (1) {
	 *     ["name"]=>
	 *     string(8) "John Doe"
	 * }
	 * 
	 * $a->b();
	 * John Doe
	 * 
	 * @since 0.0.1
	 */
	public function validate_submission($action = null) {

		global $rtb_controller;

		$this->validation_errors = array();
		/**
		 * Raw, unprocessed value so that it can be used to preselect the form 
		 * field values, eg. table and pass the value with the request. This way, 
		 * hooked code doesn't have to check $_POST or $_GET for the data and can 
		 * access everything posted from aw_input.
		 * 
		 * Its name implies the requirement of sanitization explicitly
		 */
		$this->raw_input =& $_POST;

		do_action( 'rtb_pre_validate_booking_submission', $this );

		// Date
		$date = empty( $_POST['rtb-date'] ) ? false : sanitize_text_field( $_POST['rtb-date'] );
		if ( $date === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'date',
				'error_msg'	=> 'Booking request missing date',
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-enter-date-to-book' ) ),
			);

		} else {
			try {
				$date = new DateTime( sanitize_text_field( $_POST['rtb-date'] ), wp_timezone() );
			} catch ( Exception $e ) {
				$this->validation_errors[] = array(
					'field'		=> 'date',
					'error_msg'	=> $e->getMessage(),
					'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-date-entered-not-valid' ) ),
				);
			}
		}

		// Time
		$time = empty( $_POST['rtb-time'] ) ? false : sanitize_text_field( $_POST['rtb-time'] );
		if ( $time === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'time',
				'error_msg'	=> 'Booking request missing time',
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-enter-time-to-book' ) ),
			);

		} else {
			try {
				$time = new DateTime( sanitize_text_field( $_POST['rtb-time'] ), wp_timezone() );
			} catch ( Exception $e ) {
				$this->validation_errors[] = array(
					'field'		=> 'time',
					'error_msg'	=> $e->getMessage(),
					'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-time-entered-not-valid' ) ),
				);
			}
		}

		// Check against valid open dates/times
		if ( is_object( $time ) && is_object( $date ) ) {

			$request = new DateTime( $date->format( 'Y-m-d' ) . ' ' . $time->format( 'H:i:s' ), wp_timezone() );
			$this->date_submission = new DateTime( 'now', wp_timezone() );

			// Exempt Bookings Managers from the early and late bookings restrictions
			if ( !current_user_can( 'manage_bookings' ) ) {

				$early_bookings = $rtb_controller->settings->get_setting( 'early-bookings' );
				if ( !empty( $early_bookings ) && is_numeric( $early_bookings ) ) {
					$upper_bound = ( new DateTime( 'now', wp_timezone() ) )->setTime( 23, 59 );
					$upper_bound->add( new DateInterval( "P{$early_bookings}D" ) );

					if ( $request > $upper_bound ) {
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request too far in the future',
							'message'	=> sprintf( esc_html( $rtb_controller->settings->get_setting( 'label-bookings-cannot-be-made-more-than-days-in-advance' ) ), $early_bookings ),
						);
					}
				}

				$late_bookings = $rtb_controller->settings->get_setting( 'late-bookings' );
				if ( empty( $late_bookings ) ) {
					if ( $request->format( 'U' ) < $this->date_submission->format( 'U' ) ) {
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request in the past',
							'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-bookings-cannot-be-made-in-past' ) ),
						);
					}

				} elseif ( $late_bookings === 'same_day' ) {
					if ( $request->format( 'Y-m-d' ) == $this->date_submission->format( 'Y-m-d' ) ) {
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request made on same day',
							'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-bookings-cannot-be-made-same-day' ) ),
						);
					}

				} elseif( is_numeric( $late_bookings ) ) {
					$late_bookings_seconds = $late_bookings * 60; // Late bookings allowance in seconds
					if ( $request->format( 'U' ) < ( $this->date_submission->format( 'U' ) + $late_bookings_seconds ) ) {
						if ( $late_bookings >= 1440 ) {
							$late_bookings_message = sprintf( esc_html( $rtb_controller->settings->get_setting( 'label-bookings-must-be-made-more-than-days-in-advance' ) ), $late_bookings / 1440 );
						} elseif ( $late_bookings >= 60 ) {
							$late_bookings_message = sprintf( esc_html( $rtb_controller->settings->get_setting( 'label-bookings-must-be-made-more-than-hours-in-advance' ) ), $late_bookings / 60 );
						} else {
							$late_bookings_message = sprintf( esc_html( $rtb_controller->settings->get_setting( 'label-bookings-must-be-made-more-than-minutes-in-advance' ) ), $late_bookings );
						}
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request made too close to the reserved time',
							'message'	=> $late_bookings_message,
						);
					}
				}
			}

			// Check against scheduling exception rules
			$exception_rules = $rtb_controller->settings->get_setting( 'schedule-closed' );
			$exception_is_active = false;
			if (
				empty( $this->validation_errors )
				&& !empty( $exception_rules )
				&& !current_user_can( 'manage_bookings' )
			) {

				/**
				 * We are checking the booking againt exceptions which consists blacklist and whitelist rules
				 * - Any rule without time is a blacklist entry
				 * - Any rule with time is a modified white entry
				 * Thus consider the request as legit by default and terminate the loop when we hit first 
				 * blacklisted rule which applies to the request
				 *
				 * $exception_is_active This prevent validation againt normal open rules
				 * $datetime_is_valid This is to throw error as soon as we encounter a blacklisted exception
				 */
				$exception_is_active = false;
				$datetime_is_valid = true;

				foreach( $exception_rules as $excp_rule ) {

					if( array_key_exists( 'date_range', $excp_rule ) )
					{
						$start = ! empty( $excp_rule['date_range']['start'] )
							? new DateTime( $excp_rule['date_range']['start'], wp_timezone() )
							: new DateTime( 'now', wp_timezone() );
						$start->setTime(0, 0);

						$end = !empty( $excp_rule['date_range']['end'] )
							? new DateTime( $excp_rule['date_range']['end'], wp_timezone() )
							: ( new DateTime( 'now', wp_timezone() ) )->add( new DateInterval( 'P10Y' ) );
						$end->setTime(23, 59, 58);

						if( $start < $request && $request < $end ) {
							$excp_rule_obj = clone $request;
						}
						else {
							// Set anything to void this rule for following check
							$excp_rule_obj = clone $request;
							$excp_rule_obj->add( new DateInterval( 'P1Y' ) );
						}
					}
					else {
						$excp_rule_obj = ( new DateTime( $excp_rule['date'], wp_timezone() ) )->setTime(0, 0, 2);
					}

					if ( $excp_rule_obj->format( 'Y-m-d' ) == $request->format( 'Y-m-d' ) ) {
						// This rule applies so far, thus consider this request under exception
						// whielist or blacklist, yet to be determined
						$exception_is_active = true;

						// Closed all day
						// Request denied, falls under blacklist, terminate loop
						if ( empty( $excp_rule['time'] ) ) {
							$datetime_is_valid = false;
							break;
						}

						$excp_start_time = empty( $excp_rule['time']['start'] )
							? $request
							: new DateTime( $excp_rule_obj->format( 'Y-m-d' ) . ' ' . $excp_rule['time']['start'], wp_timezone() );

						$excp_end_time = empty( $excp_rule['time']['end'] )
							? $request
							: new DateTime( $excp_rule_obj->format( 'Y-m-d' ) . ' ' . $excp_rule['time']['end'], wp_timezone() );

						if (
							$excp_start_time->format( 'U' )
								<= $request->format( 'U' ) && $request->format( 'U' ) <=
							$excp_end_time->format( 'U' )
						) {
							// If we reach here, means request is under modified whitelist rules
							$datetime_is_valid = true;
							break;
						}
						else {
							// else this request falls in blacklisted area based on modified whitelist rules.
							$datetime_is_valid = false;
						}
					}
				}

				if ( $exception_is_active && !$datetime_is_valid ) {
					$this->validation_errors[] = array(
						'field'		=> 'date',
						'error_msg'	=> 'Booking request made on invalid date or time in an exception rule',
						'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-no-bookings-accepted-then' ) ),
					);
				}
			}

			// Check against weekly scheduling rules
			$rules = $rtb_controller->settings->get_setting( 'schedule-open' );

			// Order of conditions in if matters to prevent unnecessary warnings
			if (
				empty( $this->validation_errors )
				&& !empty( $rules )
				&& !current_user_can( 'manage_bookings' )
				&& !$exception_is_active
			) {
				$request_weekday = strtolower( $request->format( 'l' ) );
				$time_is_valid = null;
				$day_is_valid = null;
				foreach( $rules as $rule ) {

					if ( !empty( $rule['weekdays'][ $request_weekday ] ) ) {
						$day_is_valid = true;

						if ( empty( $rule['time'] ) ) {
							$time_is_valid = true; // Days with no time values are open all day
							break;
						}

						$too_early = true;
						$too_late = true;

						// Too early
						if ( !empty( $rule['time']['start'] ) ) {
							$rule_start_time = new DateTime( $request->format( 'Y-m-d' ) . ' ' . $rule['time']['start'], wp_timezone() );
							if ( $rule_start_time->format( 'U' ) <= $request->format( 'U' ) ) {
								$too_early = false;
							}
						}

						// Too late
						if ( !empty( $rule['time']['end'] ) ) {
							$rule_end_time = new DateTime( $request->format( 'Y-m-d' ) . ' ' . $rule['time']['end'], wp_timezone() );
							if ( $rule_end_time->format( 'U' ) >= $request->format( 'U' ) ) {
								$too_late = false;
							}
						}

						// Valid time found
						if ( $too_early === false && $too_late === false) {
							$time_is_valid = true;
							break;
						}
					}
				}

				if ( !$day_is_valid ) {
					$this->validation_errors[] = array(
						'field'		=> 'date',
						'error_msg'	=> 'Booking request made on an invalid date',
						'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-no-bookings-accepted-on-that-date' ) ),
					);
				} elseif ( !$time_is_valid ) {
					$this->validation_errors[] = array(
						'field'		=> 'time',
						'error_msg'	=> 'Booking request made at an invalid time',
						'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-no-bookings-accepted-at-that-time' ) ),
					);
				}
			}

			// Accept the date if it has passed validation
			if ( empty( $this->validation_errors ) ) {
				$this->date = $request->format( 'Y-m-d H:i:s' );
			}
		}

		// Save requested date/time values in case they need to be
		// printed in the form again
		$this->request_date = empty( $_POST['rtb-date'] ) ? '' : sanitize_text_field( $_POST['rtb-date'] );
		$this->request_time = empty( $_POST['rtb-time'] ) ? '' : sanitize_text_field( $_POST['rtb-time'] );

		// Name
		$this->name = empty( $_POST['rtb-name'] ) ? '' : wp_strip_all_tags( sanitize_text_field( $_POST['rtb-name'] ), true ); // @todo should I limit length?
		if ( empty( $this->name ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'name',
				'post_variable'	=> $this->name,
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-enter-name-for-booking' ) ),
			);
		}

		// Party
		$this->party = empty( $_POST['rtb-party'] ) ? '' : absint( $_POST['rtb-party'] );
		if ( empty( $this->party ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'party',
				'post_variable'	=> $this->party,
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-how-many-people-in-party' ) ),
			);

		// Check party size
		} else {
			$party_size = $rtb_controller->settings->get_setting( 'party-size' );
			if ( ! empty( $party_size ) && $party_size < $this->party ) {
				$this->validation_errors[] = array(
					'field'			=> 'party',
					'post_variable'	=> $this->party,
					'message'	=> sprintf( esc_html( $rtb_controller->settings->get_setting( 'label-only-accept-bookings-for-parties-up-to' ) ), $party_size ),
				);
			}
			$party_size_min = $rtb_controller->settings->get_setting( 'party-size-min' );
			if ( ! empty( $party_size_min ) && $party_size_min > $this->party ) {
				$this->validation_errors[] = array(
					'field'			=> 'party',
					'post_variable'	=> $this->party,
					'message'	=> sprintf( esc_html( $rtb_controller->settings->get_setting( 'label-only-accept-bookings-for-parties-more-than' ) ), $party_size_min ),
				);
			}
		}

		// Email
		$this->email = empty( $_POST['rtb-email'] ) ? '' : sanitize_email( $_POST['rtb-email'] ); // @todo email validation? send notification back to form on bad email address.
		if ( empty( $this->email ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'email',
				'post_variable'	=> $this->email,
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-enter-email-address-to-confirm-booking' ) ),
			);
		} elseif ( !is_email( $this->email ) && apply_filters( 'rtb_require_valid_email', true ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'email',
				'post_variable'	=> $this->email,
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-enter-valid-email-address-to-confirm-booking' ) ),
			);
		}

		// Phone
		$this->phone = empty( $_POST['rtb-phone'] ) ? '' : sanitize_text_field( $_POST['rtb-phone'] );
		$phone_required = $rtb_controller->settings->get_setting( 'require-phone' );
		if ( $phone_required && empty( $this->phone ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'phone',
				'post_variable'	=> $this->phone,
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-provide-phone-number-to-confirm-booking' ) ),
			);
		}

		// Table
		$table = empty( $_POST['rtb-table'] ) ? array() : explode( ',', sanitize_text_field( $_POST['rtb-table'] ) );
		$this->table = is_array( $table ) ? array_map( 'sanitize_text_field', $table ) : array();

		$table_required = $rtb_controller->settings->get_setting( 'require-table' );
		if ( $table_required && empty( $this->table ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'table',
				'post_variable'	=> $this->table,
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-select-table-for-booking' ) ),
			);
		}

		// check whether there is a time conflict for a particular table
		$valid_table = $this->table ? $this->is_valid_table() : true;
		if ( ! $valid_table ) {
			$this->validation_errors[] = array(
				'field'			=> 'table',
				'post_variable'	=> $this->table,
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-select-valid-table-for-booking' ) ),
			);
		}

		// reCAPTCHA
		if ( $rtb_controller->settings->get_setting( 'enable-captcha' ) && !is_admin() ) {
			if ( ! isset($_POST['g-recaptcha-response']) ) {
				$this->validation_errors[] = array(
					'field'		=> 'recaptcha',
					'error_msg'	=> 'No reCAPTCHA code',
					'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-fill-out-recaptcha' ) ),
				);
			}
			else {
				$secret_key = $rtb_controller->settings->get_setting( 'captcha-secret-key' );
				$captcha = $_POST['g-recaptcha-response'];

				$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secret_key) .  '&response=' . urlencode($captcha);
				$json_response = file_get_contents( $url );
				$response = json_decode( $json_response );

				$reCaptcha_error = false;
				if(json_last_error() != JSON_ERROR_NONE) {
					$response = new stdClass();
					$response->success = false;
					$reCaptcha_error = true;
					if(defined('WP_DEBUG') && WP_DEBUG) {
						error_log('RTB reCAPTCHA error. Raw respose: '.print_r([$json_response], true));
					}
				}

				if ( ! $response->success ) {
					$message = esc_html( $rtb_controller->settings->get_setting( 'label-fill-out-recaptcha-again' ) );
					if($reCaptcha_error) {
						$message .= esc_html( $rtb_controller->settings->get_setting( 'label-if-encounter-multiple-recaptcha-errors' ) );
					}
					$this->validation_errors[] = array(
						'field'		=> 'recaptcha',
						'error_msg'	=> 'Invalid reCAPTCHA code',
						'message'	=> $message,
					);
				}
			}
		}

		// Message
		$this->message = empty( $_POST['rtb-message'] ) ? '' : sanitize_text_field( nl2br( $_POST['rtb-message'] ) );

		// Post Status (define a default post status if none passed)
		$this->determine_status();

		// Consent
		$require_consent = $rtb_controller->settings->get_setting( 'require-consent' );
		$consent_statement = $rtb_controller->settings->get_setting( 'consent-statement' );
		if ( $require_consent && $consent_statement ) {
			// Don't change consent status once initial consent has been collected
			if ( empty( $this->consent_acquired ) ) {
				$this->consent_acquired = !empty( $_POST['rtb-consent-statement'] );
			}
		}

		// Check if any required fields are empty
		$required_fields = $rtb_controller->settings->get_required_fields();
		foreach( $required_fields as $slug => $field ) {
			if ( !$this->field_has_error( $slug ) && $this->is_field_empty( $slug ) ) {
				$this->validation_errors[] = array(
					'field'			=> $slug,
					'post_variable'	=> '',
					'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-complete-this-field-to-request-booking' ) ),
				);
			}
		}

		// Check if the email or IP is banned
		if ( !current_user_can( 'manage_bookings' ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
			if ( !$this->is_valid_ip( $ip ) || !$this->is_valid_email( $this->email ) ) {
				$this->validation_errors[] = array(
					'field'			=> 'date',
					'post_variable'	=> $ip,
					'message'		=> esc_html( $rtb_controller->settings->get_setting( 'label-booking-has-been-rejected' ) ),
				);
			} elseif ( empty( $this->ip ) and ! $rtb_controller->settings->get_setting( 'disable-ip-capture' ) ) {
				$this->ip = sanitize_text_field( $ip );
			}
		} elseif ( empty( $this->ip ) and ! $rtb_controller->settings->get_setting( 'disable-ip-capture' ) ) {
			$this->ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		}
		
		// Check to make sure that the maximum number of reservations has not already been made
		if ( ! $this->is_under_max_reservations() && ( ! $this->by_admin || empty( $rtb_controller->settings->get_setting( 'rtb-admin-ignore-maximums' ) ) ) ) {
			$this->validation_errors[] = array(
				'field'		=> 'time',
				'error_msg'	=> 'maximum reservations exceeded',
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-maximum-reservations-reached' ) ),
			);
		}

		// Check to make sure that the maximum number of seats has not already been made
		if ( ! $this->is_under_max_seats() && ( ! $this->by_admin || empty( $rtb_controller->settings->get_setting( 'rtb-admin-ignore-maximums' ) ) ) ) {
			$this->validation_errors[] = array(
				'field'		=> 'time',
				'error_msg'	=> 'maximum seats exceeded',
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-maximum-seats-reached' ) ),
			);
		}

		// Check if there is a booking already made with the exact same information, to prevent double bookings on refresh
		if ( ( ! $this->by_admin || $this->by_admin && $action !== 'update' ) && $this->is_duplicate_booking() ) {
			$this->validation_errors[] = array(
				'field'		=> 'date',
				'error_msg'	=> 'duplicate booking',
				'message'	=> esc_html( $rtb_controller->settings->get_setting( 'label-booking-info-exactly-matches' ) ),
			);
		}

		do_action( 'rtb_validate_booking_submission', $this );

	}

	/**
	 * Check if submission is valid
	 *
	 * @since 0.0.1
	 */
	public function is_valid_submission() {

		if ( !count( $this->validation_errors ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a field already has an error attached to it
	 *
	 * @field string Field slug
	 * @since 1.3
	 */
	public function field_has_error( $field_slug ) {

		foreach( $this->validation_errors as $error ) {
			if ( $error['field'] == $field_slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a field is missing
	 *
	 * Checks for empty strings and arrays, but accepts '0'
	 * @since 0.1
	 */
	public function is_field_empty( $slug ) {

		$field_key = 'rtb-' . $slug;

		if (
			 ! isset( $_POST[ $field_key ] )
			|| ( is_string( $_POST[ $field_key ] ) && trim( $_POST[ $field_key ] ) == '' )
			|| ( is_array( $_POST[ $field_key ] ) && empty( $_POST[ $field_key ] ) )
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if an IP address has been banned
	 *
	 * @param string $ip
	 * @return bool
	 * @since 1.7
	 */
	public function is_valid_ip( $ip = null ) {
		global $rtb_controller;

		if ( is_null( $ip ) ) {
			$ip = isset( $this->ip ) ? $this->ip : null;
			if ( is_null( $ip ) ) {
				return false;
			}
		}

		$banned_ips = ! empty( $rtb_controller->settings->get_setting( 'ban-ips' ) ) ? array_filter( explode( "\n", $rtb_controller->settings->get_setting( 'ban-ips' ) ) ) : array();

		foreach( $banned_ips as $banned_ip ) {
			if ( $ip == trim( $banned_ip ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if an email address has been banned
	 *
	 * @param string $email
	 * @return bool
	 * @since 1.7
	 */
	public function is_valid_email( $email = null ) {

		if ( is_null( $email ) ) {
			$email = isset( $this->email ) ? $this->email : null;
			if ( is_null( $email ) ) {
				return false;
			}
		}

		global $rtb_controller;

		$banned_emails = ! empty( $rtb_controller->settings->get_setting( 'ban-emails' ) ) ? array_filter( explode( "\n", $rtb_controller->settings->get_setting( 'ban-emails' ) ) ) : array();

		foreach( $banned_emails as $banned_email ) {
			if ( $email == trim( $banned_email ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if a table(s) is valid (not already taken during a specific timeslot)
	 *
	 * @return bool
	 * @since 2.1.7
	 */
	public function is_valid_table() {
		global $rtb_controller;

		if ( ! $this->table or ! is_array( $this->table ) ) { return false; }

		$location_id = isset( $this->location ) ? $this->location : 0;

		$valid_tables = rtb_get_valid_tables( $this->date, $location_id );

		if ( isset( $this->ID ) ) {
			
			$post_meta = get_post_meta( $this->ID, 'rtb', true );

			if ( isset( $post_meta['table'] ) and is_array( $post_meta['table'] ) ) { $valid_tables = array_merge( $valid_tables, $post_meta['table'] ); }
		}
		
		return $this->table == array_intersect( $this->table, $valid_tables );
	}

	/**
	 * Add a location_name property to the booking, if location property exists
	 *
	 * @return bool
	 * @since 2.5.19
	 */
	public function set_location_name() {
		global $rtb_controller;

		if ( empty( $this->location ) or ! term_exists( $this->location ) ) { 

			$this->location_name = '';

			return false; 
		}

		$location = get_term( $this->location );

		$this->location_name = $location->name;
		
		return $this->location_name;
	}

	/**
	 * Check if this booking would put the restaurant over the maximum, if set
	 *
	 * @return bool
	 * @since 2.1.20
	 */
	public function is_under_max_reservations() { 
		global $rtb_controller;

		// Date validation has failed, return true to avoid adding additional errors
		if ( empty( $this->date ) ) { return true; }

		$location = ( ! empty( $this->location ) and term_exists( $this->location ) ) ? get_term( $this->location ) : false;
		$location_slug = ! empty( $location ) ? $location->slug : false;

		$max_reservations_enabled = $rtb_controller->settings->get_setting( 'rtb-enable-max-tables', $location_slug );

		if ( ! $max_reservations_enabled ) { return true; }

		$timeslot = rtb_get_timeslot( $this->date, $this->location );

		$max_reservations = (int) $rtb_controller->settings->get_setting( 'rtb-max-tables-count', $location_slug, $timeslot );

		if ( $max_reservations == 'undefined' or ! $max_reservations ) { return true; }

		$dining_block_seconds = (int) $rtb_controller->settings->get_setting( 'rtb-dining-block-length' ) * 60 - 1; // Take 1 second off, to avoid bookings that start or end exactly at the beginning of a booking block

		$tmp = ( new DateTime( $this->date, wp_timezone() ) )->format( 'U' );
		$after_time = $tmp - $dining_block_seconds;
		$before_time = $tmp + $dining_block_seconds;

    	$args = array(
    	  'posts_per_page' => -1,
    	  'post_status'    => array( 'pending', 'payment_pending', 'confirmed', 'arrived' ),
    	  'date_query'     => array(
    	    'before' => date( 'c', $before_time ),
    	    'after'  => date( 'c', $after_time )
    	  )
    	);

    	// If there are multiple locations, a location is selected, and 
		// max seats has been enabled for this specific location
		if ( ! empty( $location_slug ) and $rtb_controller->settings->is_location_setting_enabled( 'rtb-max-tables-count', $location_slug ) ) {

			$tax_query = array(
				array(
					'taxonomy'	=> $rtb_controller->locations->location_taxonomy,
					'field'		=> 'term_id',
					'terms'		=> $location->term_id
				)
			);

			$args['tax_query'] = $tax_query;
		}

		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );
		$query = new rtbQuery( $args );
		$query->prepare_args();

		$tmzn = wp_timezone();

		$times = array();
		foreach ( $query->get_bookings() as $booking ) {

			if ( isset( $this->ID ) and $booking->ID == $this->ID ) { continue; }
			
			$times[] = ( new DateTime( $booking->date, $tmzn ) )->format( 'U' );
		}
		
		sort( $times );

		$current_times = array();
		foreach ( $times as $time ) {
			
			$current_times[] = $time;
			
			if ( reset( $current_times ) < ( $time - $dining_block_seconds ) ) { array_shift( $current_times ); }

			// Check if we go above the max confirmation number
			if ( sizeOf( $current_times ) + 1 > $max_reservations ) { return false; } 
		}

		return true;
	}

	/**
	 * Check if this booking would put the restaurant over the maximum number of people, if set
	 *
	 * @return bool
	 * @since 2.1.20
	 */
	public function is_under_max_seats() {
		global $rtb_controller;

		// Date validation has failed, return true to avoid adding additional errors
		if ( empty( $this->date ) ) { return true; }

		$location = ( ! empty( $this->location ) and term_exists( $this->location ) ) ? get_term( $this->location ) : false;
		$location_slug = ! empty( $location ) ? $location->slug : false;

		$max_reservations_enabled = $rtb_controller->settings->get_setting( 'rtb-enable-max-tables', $location_slug );

		if ( ! $max_reservations_enabled ) { return true; }

		$timeslot = rtb_get_timeslot( $this->date, $this->location );

		$max_seats = (int) $rtb_controller->settings->get_setting( 'rtb-max-people-count', $location_slug, $timeslot );

		if ( $max_seats == 'undefined' or ! $max_seats ) { return true; } 
		if ( $this->party > $max_seats ) { return false; }

		$dining_block_seconds = (int) $rtb_controller->settings->get_setting( 'rtb-dining-block-length' ) * 60 - 1; // Take 1 second off, to avoid bookings that start or end exactly at the beginning of a booking block

		$tmp = ( new DateTime( $this->date, wp_timezone() ) )->format( 'U' );
		$after_time = $tmp - $dining_block_seconds;
		$before_time = $tmp + $dining_block_seconds;

		$args = array(
			'posts_per_page' => -1,
			'post_status'    => array( 'pending', 'payment_pending', 'confirmed', 'arrived' ),
			'date_query'     => array(
				'before' => date( 'c', $before_time ),
				'after'  => date( 'c', $after_time )
			)
		);

		// If there are multiple locations, a location is selected, and 
		// max seats has been enabled for this specific location
		if ( ! empty( $location_slug ) and $rtb_controller->settings->is_location_setting_enabled( 'rtb-max-people-count', $location_slug ) ) {

			$tax_query = array(
				array(
					'taxonomy'	=> $rtb_controller->locations->location_taxonomy,
					'field'		=> 'term_id',
					'terms'		=> $location->term_id
				)
			);

			$args['tax_query'] = $tax_query;
		}

		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );
		$query = new rtbQuery( $args );
		
		$tmzn = wp_timezone();
		$times = array();
		foreach ( $query->get_bookings() as $booking ) {

			if ( isset( $this->ID ) and $booking->ID == $this->ID ) { continue; }

			$booking_time = (new DateTime( $booking->date, $tmzn ) )->format( 'U' );
			if ( isset( $times[$booking_time] ) ) { $times[$booking_time] +=  intval( $booking->party ); }
			else { $times[$booking_time] = (int) $booking->party; }
		}

		ksort( $times );
		
		$current_seats = array();
		foreach ( $times as $time  => $seats ) {
			
			$current_seats[$time] = $seats;

			reset( $current_seats );

			if ( key ( $current_seats ) < $time - $dining_block_seconds ) { array_shift( $current_seats ); }

			// Check if adding the current party puts us above the max confirmation number
			if ( array_sum( $current_seats ) + $this->party > $max_seats ) { return false; } 
		}

		return true;

	}

	/**
	 * Check if the information in a booking exactly matches another booking
	 *
	 * @return bool
	 * @since 2.1.20
	 */
	public function is_duplicate_booking() {
		global $wpdb, $rtb_controller;

		if( 0 < count($this->validation_errors) ) {
			/**
			 * Do not run this check if there is an error already
			 * There could abe a moment when someminfo could be missing, which is required
			 * for this qurey to function.
			 */
			return null;
		}

		$valid_status = ['confirmed', 'pending'];

		// This is an intermediate status when payment is pending
		if ( $rtb_controller->settings->get_setting( 'require-deposit' ) ) {
			$valid_status = array_merge($valid_status, ['payment_pending']);
		}

		$args = array_merge(
			array(
				RTB_BOOKING_POST_TYPE,
				$this->date,
				$this->name
			),
			$valid_status
		);

		$status_placeholder = implode( ',', array_fill( 0, count( $valid_status ), '%s' ) );

		$sql  = "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_date=%s AND post_title=%s AND post_status IN ({$status_placeholder})";

		if ( isset( $this->ID ) ) {
			$sql 	.= ' AND ID!=%d';
			$args[] = $this->ID;
		}

		$booking_result = $wpdb->get_row( $wpdb->prepare( $sql, $args ) );

		if ( $booking_result ) {
			$meta = get_post_meta( $booking_result->ID, 'rtb', true );
			$meta = is_array( $meta ) ? $meta : array();
			
			if ( $this->party == $meta['party'] and $this->email == $meta['email'] and $this->phone == $meta['phone'] ) {

				return true;
			}
		}

		return false;
	}

/**
 * Should we ask for deposit based on required minimum party size?
 * @param string $value [description]
 */
	public function is_size_based_deposit_applicable() {
		global $rtb_controller;

		if ($this->party < $rtb_controller->settings->get_setting( 'rtb-deposit-min-party-size' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Shoudl we ask for deposit or not based on the given schedule?
	 *
	 * @since 2.0.0
	 */
	public function is_time_based_deposit_applicable() {
		global $rtb_controller;

		$deposit_applicable = is_array( $rtb_controller->settings->get_setting( 'rtb-deposit-schedule' ) )
			? $rtb_controller->settings->get_setting( 'rtb-deposit-schedule' )
			: array();

		// Get any rules which apply to this weekday
		if ( $deposit_applicable != 'undefined' ) {

			$tmzn = wp_timezone();
			$date_object = new DateTime( $this->date, $tmzn );

			$time = $date_object->format( 'U' );

			$day_of_week = strtolower( $date_object->format( 'l' ) );

			foreach ( $deposit_applicable as $applicable ) {
					
				if ( $applicable['weekdays'] !== 'undefined' ) {

					foreach ( $applicable['weekdays'] as $weekday => $value ) {

						if ( $weekday == $day_of_week ) {

							// applicable all day
							if ( !isset( $applicable['time'] ) || $applicable['time'] == 'undefined' ) {

								return true;
							}

							if ( $applicable['time']['start'] !== 'undefined' ) {

								$applicable_start_time = ( new DateTime( $date_object->format( 'Y-m-d' ) . ' ' . $applicable['time']['start'], $tmzn ) )->format( 'U' );
							}
							else {

								$applicable_start_time = ( new DateTime( $date_object->format( 'Y-m-d' ), $tmzn ) )->format( 'U' );
							}

							if ( $applicable['time']['end'] !== 'undefined' ) {

								$applicable_end_time = ( new DateTime( $date_object->format( 'Y-m-d' ) . ' ' . $applicable['time']['end'], $tmzn ) )->format( 'U' );
							}
							else {
								// End of the day
								$applicable_end_time = ( new DateTime( $date_object->format( 'Y-m-d' ) . ' 23:59:59', $tmzn ) )->format( 'U' );
							}

							if ( $time > $applicable_start_time and $time < $applicable_end_time ) {

								return true;
							}
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check whether the number of reservations occurring at the same time is below the threshold
	 * where reservations get automatically confirmed
	 *
	 * @since 2.0.0
	 */
	public function under_max_confirm_reservations() {
		global $rtb_controller;

		$max_reservations = (int) $rtb_controller->settings->get_setting( 'auto-confirm-max-reservations' );

		if ( $max_reservations == 'undefined' or $max_reservations <= 1 ) { return false; }

		$dining_block_seconds = (int) $rtb_controller->settings->get_setting( 'rtb-dining-block-length' ) * 60 - 1; // Take 1 second off, to avoid bookings that start or end exactly at the beginning of a booking block

		$tmp = (new DateTime( $this->date, wp_timezone() ) )->format( 'U' );
		$after_time = $tmp - $dining_block_seconds;
		$before_time = $tmp + $dining_block_seconds;

		$args = array(
			'posts_per_page' => -1,
      'post_status'    => ['confirmed', 'arrived'],
			'date_query'     => array(
				'before' => date( 'c', $before_time ),
				'after'  => date( 'c', $after_time )
			)
		);

		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );
		$query = new rtbQuery( $args );
		$query->prepare_args();
		
		$tmzn = wp_timezone();
		$times = array();
		foreach ( $query->get_bookings() as $booking ) {
			$times[] = (new DateTime( $booking->date,  $tmzn ) )->format( "U" );
		}
		
		sort( $times );

		$auto_confirm = true;
		$current_times = array();
		foreach ( $times as $time ) {
			$current_times[] = $time;
			
			if ( reset( $current_times ) < ($time - $dining_block_seconds) ) { array_shift( $current_times ); }

			// Check if we've reached 1 below the max confirmation number, since adding the current booking will put us at the threshold
			if ( sizeOf( $current_times ) + 1 >= $max_reservations ) { $auto_confirm = false; break; } 
		}

		return $auto_confirm;
	}

	/**
	 * Check whether the number of seats occurring at the same time is below the threshold
	 * where reservations get automatically confirmed
	 *
	 * @since 2.0.0
	 */
	public function under_max_confirm_seats() {
		global $rtb_controller;

		$max_seats = (int) $rtb_controller->settings->get_setting( 'auto-confirm-max-seats' );

		if ( $max_seats == 'undefined' or $max_seats < 2 or $this->party >= $max_seats ) { return false; }

		$dining_block_seconds = (int) $rtb_controller->settings->get_setting( 'rtb-dining-block-length' ) * 60 - 1; // Take 1 second off, to avoid bookings that start or end exactly at the beginning of a booking block

		$tmp = (new DateTime( $this->date, wp_timezone() ) )->format( 'U' );
		$after_time = $tmp - $dining_block_seconds;
		$before_time = $tmp + $dining_block_seconds;

		$args = array(
			'posts_per_page' => -1,
      'post_status'    => ['confirmed', 'arrived'],
			'date_query'     => array(
				'before' => date( 'c', $before_time ),
				'after'  => date( 'c', $after_time )
			)
		);

		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );
		$query = new rtbQuery( $args );
		
		$tmzn = wp_timezone();
		$times = array();
		foreach ( $query->get_bookings() as $booking ) {
			$booking_time = ( new DateTime( $booking->date, $tmzn ) )->format( 'U' );
			if ( isset( $times[$booking_time] ) ) { $times[$booking_time] += $booking->party; }
			else { $times[$booking_time] = $booking->party; }
		}

		ksort( $times );
		
		$auto_confirm = true;
		$current_seats = array();
		foreach ( $times as $time  => $seats ) {
			$current_seats[$time] = $seats;

			reset( $current_seats );

			if ( key ( $current_seats ) < $time - $dining_block_seconds ) { array_shift( $current_seats ); }

			// Check if adding the current party puts us at or above the max confirmation number
			if ( array_sum( $current_seats ) + $this->party >= $max_seats ) { $auto_confirm = false; break; }
		}

		return $auto_confirm;
	}

	/**
	 * Determine what status a booking should have
	 *
	 * @since 2.1.0
	 */
	public function determine_status( $payment_made = false ) {
		global $rtb_controller;

		if ( $this->by_admin && !empty( $_POST['rtb-post-status'] ) && array_key_exists( $_POST['rtb-post-status'], $rtb_controller->cpts->booking_statuses ) ) {
			$post_status = sanitize_text_field( $_POST['rtb-post-status'] );
			if ( $post_status == 'confirmed' ) { $this->temp_confirmed_user = get_current_user_id(); }
		} elseif (
			(
				$rtb_controller->settings->get_setting( 'require-deposit' )
				&& ! $payment_made
				&& ! $this->is_conditional_deposit_enabled()
			)
			||
			(
				$rtb_controller->settings->get_setting( 'require-deposit' )
				&& ! $payment_made
				&& $this->is_conditional_deposit_enabled()
				&&
				(
					( $this->is_time_based_deposit_enabled() && $this->is_time_based_deposit_applicable() )
					||
					( $this->is_size_based_deposit_enabled() && $this->is_size_based_deposit_applicable() )
				)
			)
		) {
			$post_status = 'payment_pending';
		} elseif ( $this->party < $rtb_controller->settings->get_setting( 'auto-confirm-max-party-size' ) ) {
			$post_status = 'confirmed';
			$this->temp_confirmed_user = -1;
		} elseif ($rtb_controller->settings->get_setting( 'auto-confirm-max-reservations' ) and $this->under_max_confirm_reservations() ) {
			$post_status = 'confirmed';
			$this->temp_confirmed_user = -2;
		} elseif ( $rtb_controller->settings->get_setting( 'auto-confirm-max-seats' ) and $this->under_max_confirm_seats() ) {
			$post_status = 'confirmed';
			$this->temp_confirmed_user = -3;
		} else {
			$post_status = 'pending';
		}

		$this->post_status = apply_filters( 'rtb_determine_booking_status', $post_status, $this );
	}

	/**
	 * Add a log entry to the booking
	 *
	 * @since 1.3.1
	 */
	public function add_log( $type, $title, $message = '', $datetime = null ) {

		if ( empty( $datetime ) ) {
			$datetime = date( 'Y-m-d H:i:s');
		}

		if ( empty( $this->logs ) ) {
			$this->logs = array();
		}

		array_push( $this->logs, array( $type, $title, $message, $datetime ) );
	}

	/**
	 * Insert post data for a new booking or update a booking
	 * @since 0.0.1
	 */
	public function insert_post_data() {

		$args = array(
			'post_type'		=> RTB_BOOKING_POST_TYPE,
			'post_title'	=> $this->name,
			'post_content'	=> $this->message,
			'post_date'		=> $this->date,
			'post_date_gmt'	=> get_gmt_from_date( $this->date ), // fix for post_date_gmt not being set for some bookings
			'post_status'	=> $this->post_status,
		);

		if ( !empty( $this->ID ) ) {
			$args['ID'] = $this->ID;
		}

		// Add the ID of current user if booking status is confirmed for the first time
		// Set user ID to -1, -2 or -3 if booking is automatically confirmed, depending
		// on which rule auto-confirmed the booking
		if ( $this->post_status == 'confirmed' and empty( $this->confirmed_user ) ) {
			$this->confirmed_user = $this->temp_confirmed_user;
		}

		$args = apply_filters( 'rtb_insert_booking_data', $args, $this );

		// When updating a booking, we need to update the metadata first, so that
		// notifications hooked to the status changes go out with the new metadata.
		// If we're inserting a new booking, we have to insert it before we can
		// add metadata, and the default notifications don't fire until it's all done.
		if ( !empty( $this->ID ) ) {
			$this->insert_post_meta();
			$id = wp_insert_post( $args );
		} else {
			$id = wp_insert_post( $args );
			if ( $id && !is_wp_error( $id ) ) {
				$this->ID = $id;
				$this->insert_post_meta();
			}
		}

		return !is_wp_error( $id ) && $id !== false;
	}

	/**
	 * Insert the post metadata for a new booking or when updating a booking
	 * @since 1.7.7
	 */
	public function insert_post_meta() {

		$meta = array(
			'party' => $this->party,
			'email' => $this->email,
			'phone' => $this->phone,
		);

		if ( !empty( $this->ip ) ) {
			$meta['ip'] = $this->ip;
		}

		if ( empty( $this->date_submission ) ) {
			$meta['date_submission'] = (new DateTime( 'now', wp_timezone() ))->format( 'U' );
		} else {
			$meta['date_submission'] = $this->date_submission instanceof DateTime
				? $this->date_submission->format('U')
				: $this->date_submission;
		}

		if ( !empty( $this->consent_acquired ) ) {
			$meta['consent_acquired'] = $this->consent_acquired;
		}

		if ( !empty( $this->logs ) ) {
			$meta['logs'] = $this->logs;
		}

		if ( !empty( $this->confirmed_user ) ) {
			$meta['confirmed_user'] = $this->confirmed_user;
		}

		if ( !empty( $this->table ) ) {
			$meta['table'] = $this->table;
		}

		if ( !empty( $this->deposit ) ) {
			$meta['deposit'] = $this->deposit;
		}

		if ( !empty( $this->payment_failure_message ) ) {
			$meta['payment_failure_message'] = $this->payment_failure_message;
		}

		if ( !empty( $this->receipt_id ) ) {
			$meta['receipt_id'] = $this->receipt_id;
		}

		if ( !empty( $this->reminder_sent ) ) {
			$meta['reminder_sent'] = $this->reminder_sent;
		}

		if ( !empty( $this->late_arrival_sent ) ) {
			$meta['late_arrival_sent'] = $this->late_arrival_sent;
		}

		if ( !empty( $this->post_reservation_follow_up_sent ) ) {
			$meta['post_reservation_follow_up_sent'] = $this->post_reservation_follow_up_sent;
		}

		if ( !empty( $this->reservation_notifications ) and is_array( $this->reservation_notifications ) ) {
			$meta['reservation_notifications'] = $this->reservation_notifications;
		}

		$meta = apply_filters( 'rtb_insert_booking_metadata', $meta, $this );

		return update_post_meta( $this->ID, 'rtb', $meta );
	}

	public function payment_paid()
	{
		if( isset( $this->ID ) ) {
			$this->determine_status( true );

			$this->insert_post_data();

			do_action( 'rtb_booking_paid', $this );
		}
	}

	public function payment_failed( $message = '' )
	{
		$this->post_status = 'payment_failed';
		$this->payment_failure_message = $message;

		$this->insert_post_data();

		do_action( 'rtb_booking_paid', $this );
	}

	function is_conditional_deposit_enabled() {
		global $rtb_controller;
		return in_array( $rtb_controller->settings->get_setting( 'rtb-deposit-applicable' ), ['time_based', 'size_based'] );
	}

	function is_time_based_deposit_enabled() {
		global $rtb_controller;
		return 'time_based' === $rtb_controller->settings->get_setting( 'rtb-deposit-applicable' );
	}

	function is_size_based_deposit_enabled() {
		global $rtb_controller;
		return 'size_based' === $rtb_controller->settings->get_setting( 'rtb-deposit-applicable' );
	}

}
} // endif;
