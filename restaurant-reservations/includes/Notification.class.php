<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbNotification' ) ) {
/**
 * Base class to handle a notification for Restaurant Reservations
 *
 * This class sets up the notification content and sends it when run by
 * rtbNotifications. This class should be extended for each type of
 * notification. So, there would be a rtbNotificationEmail class or a
 * rtbNotificationSMS class.
 *
 * @since 0.0.1
 */
abstract class rtbNotification {

	/**
	 * Event which should trigger this notification
	 * @since 0.0.1
	 */
	public $event;

	/**
	 * Target of the notification (who/what will receive it)
	 * @since 0.0.1
	 */
	public $target;

	/**
	 * The ID of the associated notification table element, if any
	 * @since 2.7.0
	 */
	public $notification_id;

	/**
	 * Define the notification essentials
	 * @since 0.0.1
	 */
	public function __construct( $event, $target ) {

		$this->event = $event;
		$this->target = $target;

	}

	/**
	 * Set booking data passed from rtbNotifications
	 *
	 * @var object $booking
	 * @since 0.0.1
	 */
	public function set_booking( $booking ) {
		$this->booking = $booking;
	}

	/**
	 * Set the value for a specific property
	 *
	 * @var string $property
	 * @var mixed $value
	 * @since 2.6.9
	 */
	public function set( $property, $value ) {
		$this->$property = $value;
	}

	/**
	 * Prepare and validate notification data
	 *
	 * @return boolean if the data is valid and ready for transport
	 * @since 0.0.1
	 */
	abstract public function prepare_notification();

	/**
	 * Retrieve a notification template
	 * @since 0.0.1
	 */
	public function get_template( $type ) {

		global $rtb_controller;

		$template = $rtb_controller->settings->get_setting( $type );

		if ( $template === null ) {
			return '';
		} else {
			return $template;
		}
	}

	/**
	 * Process a template and insert booking details
	 * @since 0.0.1
	 */
	public function process_template( $message ) {
		global $rtb_controller;

		if ( empty( $this->booking ) ) { return; }

		$booking_page_id = $rtb_controller->settings->get_setting( 'booking-page' );
		$booking_page_url = get_permalink( $booking_page_id );

		$cancellation_url = add_query_arg(
			array(
				'action' => 'cancel',
				'booking_id' => $this->booking->ID,
				'booking_email' => $this->booking->email
			),
			$booking_page_url
		);

		$template_tags = array(
			'{booking_id}'			=> $this->booking->ID,
			'{user_email}'			=> $this->booking->email,
			'{user_name}'			=> $this->booking->name,
			'{party}'				=> $this->booking->party,
			'{table}'				=> implode(',', $this->booking->table ), 
			'{date}'				=> $this->booking->format_date( $this->booking->date ),
			'{phone}'				=> $this->booking->phone,
			'{message}'				=> $this->booking->message,
			'{booking_url}'			=> $booking_page_url,
			'{cancellation_url}'	=> $cancellation_url,
			'{bookings_link_url}'	=> admin_url( 'admin.php?page=rtb-bookings&status=pending' ),
			'{confirm_link_url}'	=> admin_url( 'admin.php?page=rtb-bookings&rtb-quicklink=confirm&booking=' . esc_attr( $this->booking->ID ) ),
			'{close_link_url}'		=> admin_url( 'admin.php?page=rtb-bookings&rtb-quicklink=close&booking=' . esc_attr( $this->booking->ID ) ),
			'{site_link_url}'		=> home_url( '/' ),
			'{booking_page_link}'	=> '<a href="' . esc_attr( $booking_page_url ) . '">' . esc_html( __( 'booking page' ) ) . '</a>',
			'{bookings_link}'		=> '<a href="' . admin_url( 'admin.php?page=rtb-bookings&status=pending' ) . '">' . esc_html( $rtb_controller->settings->get_setting( 'label-bookings-link-tag'  ) ) . '</a>',
			'{cancel_link}'			=> '<a href="' . esc_attr( $cancellation_url ) . '">' . esc_html( $rtb_controller->settings->get_setting( 'label-cancel-link-tag'  ) ) . '</a>',
			'{confirm_link}'		=> '<a href="' . admin_url( 'admin.php?page=rtb-bookings&rtb-quicklink=confirm&booking=' . esc_attr( $this->booking->ID ) ) . '">' . esc_html( $rtb_controller->settings->get_setting( 'label-confirm-link-tag'  ) ) . '</a>',
			'{close_link}'			=> '<a href="' . admin_url( 'admin.php?page=rtb-bookings&rtb-quicklink=close&booking=' . esc_attr( $this->booking->ID ) ) . '">' . esc_html( $rtb_controller->settings->get_setting( 'label-close-link-tag'  ) ) . '</a>',
			'{site_name}'			=> get_bloginfo( 'name' ),
			'{site_link}'			=> '<a href="' . home_url( '/' ) . '">' . get_bloginfo( 'name' ) . '</a>',
			'{current_time}'		=> date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ) . ' ' . date_i18n( get_option( 'time_format' ), current_time( 'timestamp' ) ),
		);

		$template_tags = apply_filters( 'rtb_notification_template_tags', $template_tags, $this );

		return str_replace( array_keys( $template_tags ), array_values( $template_tags ), $message );

	}

	/**
	 * Send notification
	 * @since 0.0.1
	 */
	abstract public function send_notification();

}
} // endif;
