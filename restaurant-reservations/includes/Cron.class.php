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
		add_filter( 'option_cron', array( $this, 'normalize_payment_pending_args' ) );

		add_filter( 'sanitize_option_rtb-settings', array( $this, 'check_cron_on_settings_save' ) );

		add_action( 'rtb_cron_jobs', array($this, 'handle_reminder_task') );
		add_action( 'rtb_cron_jobs', array($this, 'handle_late_arrivals_task') );
		add_action( 'rtb_cron_jobs', array($this, 'handle_post_reservation_follow_up_task') );

		add_action( 'rtb_cron_jobs', array( $this, 'handle_table_notifications' ) );

		// if ( isset($_GET['debug']) ) { add_action('admin_init', array($this, 'handle_post_reservation_follow_up_task') ); } // Used for testing
	}

	/** Adapt already queued legacy jobs as well as new jobs, without changing other hooks. */
	public function normalize_payment_pending_args( $crons ) {
		if ( ! is_array( $crons ) ) { return $crons; }
		foreach ( $crons as $timestamp => $hooks ) {
			if ( ! is_array( $hooks ) || empty( $hooks['rtb_booking_payment_pending'] ) || ! is_array( $hooks['rtb_booking_payment_pending'] ) ) { continue; }
			foreach ( $hooks['rtb_booking_payment_pending'] as $key => $event ) {
				if ( ! is_array( $event ) || ! isset( $event['args'] ) || ! is_array( $event['args'] )
					|| 1 !== count( $event['args'] ) || ! array_key_exists( 'booking', $event['args'] ) ) { continue; }
				$event['args'] = array_values( $event['args'] );
				// WordPress unschedules by the hash of args. Re-key too, or old jobs would repeat.
				$normalized_key = md5( serialize( $event['args'] ) );
				unset( $crons[ $timestamp ]['rtb_booking_payment_pending'][ $key ] );
				if ( ! isset( $crons[ $timestamp ]['rtb_booking_payment_pending'][ $normalized_key ] ) ) {
					$crons[ $timestamp ]['rtb_booking_payment_pending'][ $normalized_key ] = $event;
				}
			}
		}
		return $crons;
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
		if ( ! rtbPaidFeatures::can( 'reminders' ) || ! class_exists( 'fspphRTBReminderTasks', false ) ) { return; }
		return fspphRTBReminderTasks::handle_reminder_task(  );
	}

	/**
	 * Handles the late arrival event when called by wp_scheduler
	 *
	 * @since 2.0.0
	 */
	public function handle_late_arrivals_task() {
		if ( ! rtbPaidFeatures::can( 'reminders' ) || ! class_exists( 'fspphRTBReminderTasks', false ) ) { return; }
		return fspphRTBReminderTasks::handle_late_arrivals_task(  );
	}

	/**
	 * Handles the post reservation event when called by wp_scheduler
	 *
	 * @since 2.5.14
	 */
	public function handle_post_reservation_follow_up_task() {
		if ( ! rtbPaidFeatures::can( 'reminders' ) || ! class_exists( 'fspphRTBReminderTasks', false ) ) { return; }
		return fspphRTBReminderTasks::handle_post_reservation_follow_up_task(  );
	}

	/**
	 * Handles processing events saved in the table format notifications
	 *
	 * @since 2.6.3
	 */
	public function handle_table_notifications() {
		if ( ! rtbPaidFeatures::can( 'reminders' ) || ! class_exists( 'fspphRTBReminderTasks', false ) ) { return; }
		return fspphRTBReminderTasks::handle_table_notifications(  );
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

		if ( empty( $late_arrival_time ) ) { return 0; }

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
