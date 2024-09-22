<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbCron' ) ) {
/**
 * This class handles scheduling of cron jobs for different notifications
 * such as reservation reminders or when customers are late for their reservations
 *
 * @since 2.0.0
 */
class rtbCron {

	/**
	 * Adds the necessary filter and action calls
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array($this, 'add_cron_interval') );

		add_filter( 'sanitize_option_rtb-settings', array( $this, 'check_cron_on_settings_save' ) );

		add_action( 'rtb_cron_jobs', array($this, 'handle_reminder_task') );
		add_action( 'rtb_cron_jobs', array($this, 'handle_late_arrivals_task') );
		add_action( 'rtb_cron_jobs', array($this, 'handle_post_reservation_follow_up_task') );

		add_action( 'rtb_cron_jobs', array( $this, 'handle_table_notifications' ) );

		// if ( isset($_GET['debug']) ) { add_action('admin_init', array($this, 'handle_post_reservation_follow_up_task') ); } // Used for testing
	}

	/**
	 * Adds in 10 minute cron interval
	 *
	 * @var array $schedules
	 * @since 2.0.0
	 */
	public function add_cron_interval( $schedules ) {
		$schedules['ten_minutes'] = array(
			'interval' => 600,
			'display' => esc_html__( 'Every Ten Minutes' )
		);

		return $schedules;
	}

	/**
	 * Creates a scheduled action called by wp_cron every 10 minutes 
	 * The class hooks into those calls for reminders and late arrivals
	 *
	 * @since 2.0.0
	 */
	public function schedule_events() {
		if (! wp_next_scheduled ( 'rtb_cron_jobs' )) {
			wp_schedule_event( time(), 'ten_minutes', 'rtb_cron_jobs' );
		}
	}

	/**
	 * Clears the rtb_cron_job hook so that it's no longer called after the plugin is deactivated
	 *
	 * @since 2.0.0
	 */
	public function unschedule_events() {
		wp_clear_scheduled_hook( 'rtb_cron_jobs' );
	}

	/**
	 * Confirm that the rtb_cron_jobs task is scheduled on settings page save
	 *
	 * @since 2.7.0
	 */
	public function check_cron_on_settings_save( $val ) {

		$this->schedule_events();

		return $val;
	}

	/**
	 * Handles the notification reminders event when called by wp_scheduler
	 *
	 * @since 2.0.0
	 */
	public function handle_reminder_task() {
		global $rtb_controller;

		if ( empty( $rtb_controller->settings->get_setting( 'time-reminder-user' ) ) ) { return; }

		if ( ! empty( $rtb_controller->settings->get_setting( 'booking-notifications' ) ) ) { return; }

		require_once( RTB_PLUGIN_DIR . '/includes/Notification.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.Email.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.SMS.class.php' );

		$time_interval = $this->get_time_interval( 'time-reminder-user' );
		
		$start_time_seconds = time() - ( $time_interval + 3600 );
		$end_time_seconds = time() + $time_interval;

		$bookings = $this->get_booking_posts( $start_time_seconds, $end_time_seconds );
 		
		foreach ($bookings as $booking) {
			
			if ( ! $booking->reminder_sent ) {
				if ( $rtb_controller->settings->get_setting( 'reminder-notification-format' ) == 'text' ) {
					$notification = new rtbNotificationSMS( 'reminder', 'user' ); 
				}
				elseif ( $rtb_controller->settings->get_setting( 'reminder-notification-format' ) == 'email' ) {
					$notification = new rtbNotificationEmail( 'reminder', 'user' ); 
				}
				
				$notification->set_booking($booking);
				
				$notification->prepare_notification();

				do_action( 'rtb_send_notification_before', $notification );
				$sent = $notification->send_notification();
				do_action( 'rtb_send_notification_after', $notification );

				if ( $sent ) {
					$booking->reminder_sent = true;
					$booking->insert_post_meta();
				}
			}
		}

		wp_reset_postdata();
	}

	/**
	 * Handles the late arrival event when called by wp_scheduler
	 *
	 * @since 2.0.0
	 */
	public function handle_late_arrivals_task() {
		global $rtb_controller;

		if ( empty( $rtb_controller->settings->get_setting( 'time-late-user' ) ) ) { return; }

		if ( ! empty( $rtb_controller->settings->get_setting( 'booking-notifications' ) ) ) { return; }

		require_once( RTB_PLUGIN_DIR . '/includes/Notification.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.Email.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.SMS.class.php' );

		$time_interval = $this->get_time_interval( 'time-late-user' );

		$start_time_seconds = time() - ( $time_interval + 3600 );
		$end_time_seconds = time() - $time_interval;

		$bookings = $this->get_booking_posts( $start_time_seconds, $end_time_seconds );

		foreach ($bookings as $booking) {
			
			if ( ! $booking->late_arrival_sent ) {
				if ( $rtb_controller->settings->get_setting( 'late-notification-format' ) == 'text' ) {
					$notification = new rtbNotificationSMS( 'late_user', 'user' ); 
				}
				elseif ( $rtb_controller->settings->get_setting( 'late-notification-format' ) == 'email' ) {
					$notification = new rtbNotificationEmail( 'late_user', 'user' ); 
				}

				$notification->set_booking($booking);

				$notification->prepare_notification();

				do_action( 'rtb_send_notification_before', $notification );
  				$sent = $notification->send_notification(); 
  				do_action( 'rtb_send_notification_after', $notification );

  				if ( $sent ) {
  					$booking->late_arrival_sent = true;
  					$booking->insert_post_meta();
  				}
			}
		}

		wp_reset_postdata();
	}

	/**
	 * Handles the post reservation event when called by wp_scheduler
	 *
	 * @since 2.5.14
	 */
	public function handle_post_reservation_follow_up_task() {
		global $rtb_controller;

		if ( empty( $rtb_controller->settings->get_setting( 'time-post-reservation-follow-up-user' ) ) ) { return; }

		if ( ! empty( $rtb_controller->settings->get_setting( 'booking-notifications' ) ) ) { return; }

		require_once( RTB_PLUGIN_DIR . '/includes/Notification.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.Email.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.SMS.class.php' );

		$time_interval = $this->get_time_interval( 'time-post-reservation-follow-up-user' );

		$start_time_seconds = time() - ( $time_interval + 3600 );
		$end_time_seconds = time() - $time_interval;

		$bookings = $this->get_booking_posts( $start_time_seconds, $end_time_seconds );
		
		foreach ( $bookings as $booking ) {
			
			if ( ! $booking->post_reservation_follow_up_sent ) {
				if ( $rtb_controller->settings->get_setting( 'post-reservation-follow-up-notification-format' ) == 'text' ) {
					$notification = new rtbNotificationSMS( 'post_reservation_follow_up_user', 'user' ); 
				}
				elseif ( $rtb_controller->settings->get_setting( 'post-reservation-follow-up-notification-format' ) == 'email' ) {
					$notification = new rtbNotificationEmail( 'post_reservation_follow_up_user', 'user' ); 
				}

				$notification->set_booking( $booking );

				$notification->prepare_notification();

				do_action( 'rtb_send_notification_before', $notification );
  				$sent = $notification->send_notification(); 
  				do_action( 'rtb_send_notification_after', $notification );

  				if ( $sent ) {
  					$booking->post_reservation_follow_up_sent = true;
  					$booking->insert_post_meta();
  				}
			}
		}

		wp_reset_postdata();
	}

	/**
	 * Handles processing events saved in the table format notifications
	 *
	 * @since 2.6.3
	 */
	public function handle_table_notifications() {
		global $rtb_controller;

		require_once( RTB_PLUGIN_DIR . '/includes/Notification.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.Email.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Notification.SMS.class.php' );

		$notifications = rtb_decode_infinite_table_setting( $rtb_controller->settings->get_setting( 'booking-notifications' ) );

		$cron_events = array(
			'booking_reminder',
			'late_for_booking',
			'post_booking_follow_up',
		);

		foreach ( $notifications as $notification ) {

			if ( ! $notification->enabled ) { continue; }

			if ( ! in_array( $notification->event, $cron_events ) ) { continue; }

			$time_interval = $this->convert_count_unit_to_time( $notification->timing1, $notification->timing2 );

			$start_time_seconds = time() - ( $time_interval + 3600 );
			$end_time_seconds = $notification->event == 'booking_reminder' ? time() + $time_interval : time() - $time_interval; // retrieve bookings post-reservation, except for reminders

			$bookings = $this->get_booking_posts( $start_time_seconds, $end_time_seconds );
			
			foreach ( $bookings as $booking ) {

				if ( in_array( $notification->id, $booking->reservation_notifications ) ) { continue; }

				$booking_notification = $notification->type == 'sms' ? new rtbNotificationSMS( 'table_event', $notification->target ) : new rtbNotificationEmail( 'table_event', $notification->target );

				$booking_notification->set( 'message', $notification->message );
				$booking_notification->set( 'subject', $notification->subject );

				$booking_notification->notification_id = $notification->id;

				$booking_notification->set_booking( $booking );

				if ( $booking_notification->prepare_notification() ) { 
				
					do_action( 'rtb_send_notification_before', $booking_notification );
				
					$sent = $booking_notification->send_notification(); 
				
					do_action( 'rtb_send_notification_after', $booking_notification );

					if ( $sent ) {

  						$booking->reservation_notifications[] = $notification->id;
  						$booking->insert_post_meta();
  					}
				}
			}
		}
	}

	/**
	 * Gets the bookings between a specified start and end time (unix timestamp)
	 *
	 * @since 2.6.3
	 */
	public function get_booking_posts( $start_time_seconds, $end_time_seconds ) {
		global $rtb_controller;

		$time_interval = $this->get_time_interval( 'time-late-user' );

		$after_datetime = new DateTime( 'now', wp_timezone() );
		$before_datetime = new DateTime( 'now', wp_timezone() );

		$after_datetime->setTimestamp( $start_time_seconds );
		$before_datetime->setTimestamp( $end_time_seconds );

		$args = array(
			'post_status' => 'confirmed,',
			'posts_per_page' => -1,
			'date_query' => array(
				'before' => $before_datetime->format( 'Y-m-d H:i:s' ),
				'after' => $after_datetime->format( 'Y-m-d H:i:s' ),
				'column' => 'post_date'
			)
		);
		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );
		$query = new rtbQuery( $args );

		$query->prepare_args();

		return $query->get_bookings();
	}

	/**
	 * Returns the time value in seconds for a given count setting
	 *
	 * @since 2.0.0
	 */
	public function get_time_interval( $setting ) {
		global $rtb_controller;

		$late_arrival_time = $rtb_controller->settings->get_setting( $setting );

		$count = intval( substr( $late_arrival_time, 0, strpos( $late_arrival_time, "_" ) ) );
		$unit = substr( $late_arrival_time, strpos( $late_arrival_time, "_" ) + 1 );

		return $this->convert_count_unit_to_time( $count, $unit );
	}

	/**
	 * Converts a time unit and interval into its value in seconds
	 *
	 * @since 2.6.3
	 */
	public function convert_count_unit_to_time( $count, $unit ) {

		switch ($unit) {
			case 'days':
				$multiplier = 24*3600;
				break;
			case 'hours':
				$multiplier = 3600;
				break;
			case 'minutes':
				$multiplier = 60;
				break;
			
			default:
				$multiplier = 1;
				break;
		}

		return $count * $multiplier;
	}

}
} // endif;
