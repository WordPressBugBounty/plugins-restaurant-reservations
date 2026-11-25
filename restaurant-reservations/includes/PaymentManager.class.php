<?php
/** Shared payment-policy, booking metadata and unavailable-provider facade. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
class rtbPaymentManager {
	public $defaults = array();
	public $available_gateway_list = array();
	public $enabled_gateway_list = array();
	public $in_use_gateway = '';
	public $booking;
	public $booking_form_field_slug = 'payment_gateway';
	private $provider;
	public function __construct() {
		add_action( 'init', array( $this, 'load_basics' ), 10 );
		add_action( 'rtb_insert_booking', array( $this, 'maybe_schedule_payment_pending_event' ) );
		add_filter( 'rtb_insert_booking_metadata', array( $this, 'save_booking_gateway_used' ), 30, 2 );
		add_action( 'rtb_booking_load_post_data', array( $this, 'populate_booking_gateway_used' ), 30, 2 );
		add_action( 'rtb_validate_booking_submission', array( $this, 'guard_unavailable_provider' ), 99 );
		add_action( 'init', array( $this, 'reject_unavailable_callback' ), 11 );
		foreach ( array( 'rtb_stripe_get_intent', 'rtb_stripe_pmt_succeed' ) as $action ) {
			add_action( 'wp_ajax_' . $action, array( $this, 'reject_unavailable_ajax' ), 1 );
			add_action( 'wp_ajax_nopriv_' . $action, array( $this, 'reject_unavailable_ajax' ), 1 );
		}
	}
	/** Never acknowledge an unverified settlement or invite a replacement charge. */
	public function reject_unavailable_callback() {
		if ( $this->provider_available() || ( ! isset( $_POST['ipn_track_id'] ) && ! isset( $_POST['stripeToken'] ) ) ) { return; }
		wp_die( esc_html__( 'Payment processing is temporarily unavailable. Existing payments are preserved; please do not pay again. Contact the restaurant for assistance.', 'restaurant-reservations' ), '', array( 'response' => 503 ) );
	}
	public function reject_unavailable_ajax() {
		if ( $this->provider_available() ) { return; }
		wp_send_json_error( array( 'message' => __( 'Payment processing is temporarily unavailable. Please do not pay again; contact the restaurant.', 'restaurant-reservations' ) ), 503 );
	}
	public function load_basics() {
		if ( ! rtbPaidFeatures::can( 'payments' ) || ! class_exists( 'fspphRTBPaymentManager', false ) || $this->provider ) { return; }
		$this->provider = new fspphRTBPaymentManager();
		foreach ( array( 'defaults', 'available_gateway_list', 'enabled_gateway_list', 'in_use_gateway', 'booking', 'booking_form_field_slug' ) as $property ) { $this->$property =& $this->provider->$property; }
		$this->provider->load_basics();
	}
	public function provider_available() { return $this->provider && rtbPaidFeatures::can( 'payments' ); }
	public function is_payment_enabled() {
		global $rtb_controller;
		return ! empty( $rtb_controller->settings->get_setting( 'require-deposit' ) );
	}
	public function guard_unavailable_provider( $booking ) {
		if ( $this->provider_available() || ! $this->is_payment_enabled() || ! empty( $booking->by_admin ) || ! empty( $booking->staff_walk_in ) ) { return; }
		$requires = ! $booking->is_conditional_deposit_enabled() || ( $booking->is_time_based_deposit_enabled() && $booking->is_time_based_deposit_applicable() ) || ( $booking->is_size_based_deposit_enabled() && $booking->is_size_based_deposit_applicable() );
		if ( $requires ) { $booking->validation_errors[] = array( 'field' => 'payment_gateway', 'message' => __( 'Online deposits are temporarily unavailable. Please contact the restaurant to arrange your booking.', 'restaurant-reservations' ) ); }
	}
  public function maybe_schedule_payment_pending_event( $booking ) {

    if ( $booking->post_status != 'payment_pending' ) { return; }

    // WordPress passes these values to callbacks; string keys become PHP 8 named arguments.
    $args = array( $booking );

    wp_schedule_single_event( time() + apply_filters( 'rtb_payment_pending_delay', 15 ) * 60, 'rtb_booking_payment_pending', $args );
  }
  public function save_booking_gateway_used( $meta, rtbBooking $booking )
  {
    if ( isset( $booking->{$this->booking_form_field_slug} ) ) {
      $meta[$this->booking_form_field_slug] = $booking->{$this->booking_form_field_slug};
    }

    return $meta;
  }
  public function populate_booking_gateway_used( rtbBooking $booking, $wp_post )
  {
    if ( is_array( $meta = get_post_meta( $booking->ID, 'rtb', true ) ) ) {
      $booking->{$this->booking_form_field_slug} = isset( $meta[ $this->booking_form_field_slug ] )
        ? $meta[$this->booking_form_field_slug]
        : '';

        // property has been renamed
        if ( empty( $booking->{$this->booking_form_field_slug} ) ) {
          $booking->{$this->booking_form_field_slug} = isset( $meta[ 'payment-gateway' ] )
          ? $meta[ 'payment-gateway' ]
          : '';
        }
    }
  }
  public function set_booking(rtbBooking $booking)
  {
    $this->booking = $booking;

    return $this;
  }
  public function set_gateway_in_use( $gateway )
  {
    $this->in_use_gateway = $gateway;
  }
  public function get_gateway_in_use()
  {
    if ( $this->isset_gateway_in_use() ) {
      return $this->in_use_gateway;
    }

    return '';
  }
  public function isset_gateway_in_use()
  {
    return in_array( $this->in_use_gateway, $this->enabled_gateway_list );
  }
  public function get_available_gateway_list()
  {
		if ( $this->provider_available() ) { return $this->provider->get_available_gateway_list(  ); }
		return array();
  }
  public function get_enabled_gateway_list()
  {
		if ( $this->provider_available() ) { return $this->provider->get_enabled_gateway_list(  ); }
		return array();
  }
  public function add_field_booking_form_gateway( $fields, $request, $args ) {
		if ( $this->provider_available() ) { return $this->provider->add_field_booking_form_gateway( $fields, $request, $args ); }
		return $fields;
  }
  public function validate_booking_form_gateway( $request )
  {
		if ( $this->provider_available() ) { return $this->provider->validate_booking_form_gateway( $request ); }
		return false;
  }
  public function print_payment_summary()
  {
		if ( $this->provider_available() ) { return $this->provider->print_payment_summary(  ); }
		return $this;
  }
  public function print_payment_form()
  {
		if ( $this->provider_available() ) { return $this->provider->print_payment_form(  ); }
		echo esc_html__( 'Payment processing is unavailable. Existing payments are preserved; please do not pay again. Contact the restaurant for assistance.', 'restaurant-reservations' );
		return false;
  }
  public function print_invalid_gateway()
  {
		if ( $this->provider_available() ) { return $this->provider->print_invalid_gateway(  ); }
		echo esc_html__( 'Payment processing is unavailable. Existing payments are preserved; please do not pay again. Contact the restaurant for assistance.', 'restaurant-reservations' );
		return false;
  }
  public function process_payment()
  {
		if ( $this->provider_available() ) { return $this->provider->process_payment(  ); }
		return false;
  }
  public function is_payment_processed()
  {
		if ( $this->provider_available() ) { return $this->provider->is_payment_processed(  ); }
		return false;
  }
  public function payment_processing_status()
  {
		if ( $this->provider_available() ) { return $this->provider->payment_processing_status(  ); }
		return false;
  }
  public function strip_invalid_gateway() {
		if ( $this->provider_available() ) { return $this->provider->strip_invalid_gateway(  ); }
		return false;
  }
}
