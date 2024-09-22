<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbPaymentGatewayStripe' ) ) {
/**
 * This class is responsible for payment processing via Stripe
 * 
 * @since 2.3.0
 */
class rtbPaymentGatewayStripe implements rtbPaymentGateway {

  public static $gateway_identifier = 'stripe';

  private static $_instance;

  private final function __construct() {

    add_action( 'rtb_booking_form_init', array( $this, 'process_payment' ) );

    add_action( 'wp_ajax_rtb_stripe_get_intent', array( $this, 'create_stripe_pmtIntnt' ) );
    add_action( 'wp_ajax_nopriv_rtb_stripe_get_intent', array( $this, 'create_stripe_pmtIntnt' ) );

    add_action( 'wp_ajax_rtb_stripe_pmt_succeed', array( $this, 'stripe_sca_succeed' ) );
    add_action( 'wp_ajax_nopriv_rtb_stripe_pmt_succeed', array( $this, 'stripe_sca_succeed' ) );

    add_filter( 'rtb_booking_metadata_defaults', array( $this, 'default_booking_stripe_info' ), 30, 1 );
    add_action( 'rtb_booking_load_post_data', array( $this, 'populate_booking_stripe_info' ), 30, 1 );
    add_filter( 'rtb_insert_booking_metadata', array( $this, 'save_booking_gateway_info' ), 30, 2 );

    /**
     * Adding info and capability to charge the hold manually in the bookings table for admin
     */
    add_filter( 'rtb_admin_bookings_list_row_classes', array( $this, 'add_hold_class' ), 30, 2 );
    add_filter( 'rtb_bookings_table_column_details', array( $this, 'add_hold_detail' ), 30, 2 );
    add_filter( 'rtb_bookings_table_bulk_actions', array( $this, 'add_bulk_action' ), 30, 1 );
    add_filter( 'rtb_bookings_table_bulk_action', array( $this, 'charge_the_hold' ), 30, 3 );
    add_action( 'rtb_payment_summary', array( $this, 'payment_summary' ) );
  }

  /**
   * Register the gateway by adding it to the available gateway list
   * */
  public static function register_gateway (array $gateway_list ) {

    return array_merge(
      $gateway_list,
      array(
        self::$gateway_identifier => array(
          'label'    => __( 'Stripe', 'restaurant-reservations' ),
          'instance' => self::get_instance()
        )
      )
    );
  }

  /**
   * Get singleton instance of the class
   * 
   * @return rtbPaymentGatewayStripe instance
   */
  public static function get_instance() {

    if( ! isset( self::$_instance ) ) {

      self::$_instance = new rtbPaymentGatewayStripe();
    }
    
    return self::$_instance;
  }

  public function print_payment_form( $booking ) {
    global $rtb_controller;

    $btn_disabled = $rtb_controller->settings->get_setting( 'rtb-stripe-sca' ) ? "disabled='disabled'" : '';
    $stripe_lib_version = $rtb_controller->settings->get_setting( 'rtb-stripe-sca' ) ? 'v3' : 'v2';

    // Stripe Lib
    wp_enqueue_script(
      'rtb-stripe', 
      "https://js.stripe.com/{$stripe_lib_version}/", 
      array( 'jquery' ), 
      RTB_VERSION, 
      true
    );

    // Stripe-JS processing logic
    wp_enqueue_script(
      'rtb-stripe-payment', 
      RTB_PLUGIN_URL . '/assets/js/stripe-payment.js', 
      array( 'jquery', 'rtb-stripe' ), 
      RTB_VERSION, 
      true
    );

    wp_localize_script(
      'rtb-stripe-payment',
      'rtb_stripe_payment',
      array(
        'nonce'                 => wp_create_nonce( 'rtb-stripe-payment' ),
        'amount'                => $rtb_controller->settings->get_setting( 'rtb-currency' ) != 'JPY' ? $booking->calculate_deposit() * 100 : $booking->calculate_deposit(),
        'currency'              => $rtb_controller->settings->get_setting( 'rtb-currency' ),
        'hold'                  => $rtb_controller->settings->get_setting( 'rtb-stripe-hold' ),
        'stripe_mode'           => $rtb_controller->settings->get_setting( 'rtb-stripe-mode' ),
        'stripe_sca'            => $rtb_controller->settings->get_setting( 'rtb-stripe-sca' ),
        'live_publishable_key'  => $rtb_controller->settings->get_setting( 'rtb-stripe-live-publishable' ),
        'test_publishable_key'  => $rtb_controller->settings->get_setting( 'rtb-stripe-test-publishable' ),
      )
    );

    $cc_exp_single_field = $rtb_controller->settings->get_setting( 'rtb-expiration-field-single' ) != null
      ? "<input type='text' data-stripe='exp_month_year' class='single-masked'>"
      : "<input type='text' size='2' data-stripe='exp_month'>
        <span> / </span>
        <input type='text' size='4' data-stripe='exp_year'>";
    ?>

    <div class='payment-errors'></div>

    <form 
      action='#' 
      method='POST' 
      id='stripe-payment-form' 
      data-booking_id='<?php echo esc_attr( $booking->ID ) ;?>'>

      <?php wp_nonce_field( 'rtb-stripe-payment', 'nonce' ) ?>

      <?php if( $rtb_controller->settings->get_setting( 'rtb-stripe-sca' ) ) { ?>

        <div class='form-row'>
          <label>
            <?php echo esc_html( $rtb_controller->settings->get_setting( 'label-card-detail' ) ); ?>
          </label>
          <span id="cardElement"></span>
        </div>

      <?php } else { ?>

        <div class='form-row'>
          <label>
            <?php echo esc_html( $rtb_controller->settings->get_setting( 'label-card-number' ) ); ?>
          </label>
          <input type='text' size='20' autocomplete='off' data-stripe='card_number'/>
        </div>
        <div class='form-row'>
          <label>
            <?php echo esc_html( $rtb_controller->settings->get_setting( 'label-cvc' ) ); ?>
          </label>
          <input type='text' size='4' autocomplete='off' data-stripe='card_cvc'/>
        </div>
        <div class='form-row'>
          <label>
            <?php echo esc_html( $rtb_controller->settings->get_setting( 'label-expiration' ) ); ?>
          </label>
          <?php echo $cc_exp_single_field; ?>
        </div>
        <input type='hidden' name='action' value='rtb_stripe_booking_payment'/>
        <input type='hidden' name='currency' value='<?php echo esc_attr( $rtb_controller->settings->get_setting( 'rtb-currency' ) ); ?>' data-stripe='currency' />
        <input type='hidden' name='payment_amount' value='<?php echo esc_attr( $booking->calculate_deposit() ); ?>' />
        <input type='hidden' name='booking_id' value='<?php echo esc_attr( $booking->ID ); ?>' />

      <?php } ?>
      
      <p class="stripe-payment-help-text">
        <?php echo esc_html( $rtb_controller->settings->get_setting( 'label-please-wait' ) ); ?>
      </p>

      <button type='submit' id='stripe-submit' <?php echo $btn_disabled; ?>>
        <?php echo esc_html( $rtb_controller->settings->get_setting( 'label-make-deposit' ) ); ?>
      </button>

    </form>
    <?php
  }

  /**
   * Maybe process payment, if coming from Stripe payment form
   * 
   * @return void Redirect and exit or return
   */
  public function process_payment() {
    global $rtb_controller;

    if ( ! isset( $_POST['stripeToken'] ) ||  ! isset( $_POST['booking_id'] ) ) {
      return;
    }

    $booking_id = intval( $_POST['booking_id'] );

    // Define the form's action parameter
    $booking_page = ! empty( $rtb_controller->settings->get_setting( 'booking-page' ) ) ? get_permalink( $rtb_controller->settings->get_setting( 'booking-page' ) ) : get_permalink();

    if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'rtb-stripe-payment' ) ) {
      // Invalid request
      $redirect = add_query_arg(
        array(
          'payment' => 'failed',
          'booking_id' => $booking_id,
          'error_code' => urlencode( __( 'The request has been rejected because it does not appear to have come from this site.', 'restaurant-reservations' ) )
        ),
        $booking_page
      );

      // redirect back to our previous page with the added query variable
      wp_redirect( $redirect ); 

      exit;
    }

    // load the stripe libraries
    require_once( RTB_PLUGIN_DIR . '/lib/stripe/init.php' );

    // retrieve the token generated by stripe.js
    $token = sanitize_text_field( $_POST['stripeToken'] );

    // JPY currency does not have any decimal palces
    $payment_amount = $rtb_controller->settings->get_setting( 'rtb-currency' ) != 'JPY' ? ( intval( $_POST['payment_amount'] ) * 100 ) : intval( $_POST['payment_amount'] );
   
    $booking = new rtbBooking();
    $booking->load_post( $booking_id );

    try {

      $metadata = array_filter( 
        array(
          'Booking ID' => $booking->ID,
          'Email'      => $booking->email,
          'Name'       => $booking->name,
          'Date'       => $booking->date,
          'Party'      => $booking->party
        )
      );

      \Stripe\Stripe::setApiKey( $this->get_secret() );
      $charge = \Stripe\Charge::create(
        array(
          'amount'    => $payment_amount, 
          'currency'  => strtolower( $rtb_controller->settings->get_setting( 'rtb-currency' ) ),
          'card'      => $token,
          'metadata'  => $metadata
        )
      );

      $booking->deposit = $rtb_controller->settings->get_setting( 'rtb-currency' ) != 'JPY' ? $payment_amount / 100 : $payment_amount;
      $booking->receipt_id = $charge->id;

      $booking->determine_status( true );

      $booking->insert_post_data();

      do_action( 'rtb_booking_paid', $booking );

      // redirect on successful payment
      $redirect = add_query_arg(
        array(
          'payment' => 'paid',
          'booking_id' => $booking_id
        ),
        $booking_page
      );

    }
    catch ( Exception $ex ) {

      $booking->payment_failed( $ex->getMessage() );

      // redirect on failed payment
      $redirect = add_query_arg(
        array(
          'payment' => 'failed',
          'booking_id' => $booking_id,
          'error_code' => urlencode( $ex->getMessage() )
        ),
        $booking_page
      );
    }

    // redirect back to our previous page with the added query variable
    wp_redirect( $redirect ); 

    exit;
  }

  /**
   * Create Stripe payment intent for reservation deposits
   * Respond to AJAX/XHR request
   * 
   * @since 2.2.8
   */
  public function create_stripe_pmtIntnt() {
    global $rtb_controller;

    if ( !check_ajax_referer( 'rtb-stripe-payment', 'nonce' ) ) { $this->response_message( false, __( 'The request has been rejected because it does not appear to have come from this site.', 'restaurant-reservations' ) ); }

    if ( ! isset( $_POST['booking_id'] ) ) { $this->response_message( false, __( 'Invalid booking.', 'restaurant-reservations' ) ); }

    $booking = new rtbBooking();
    $booking->load_post( intval( $_POST['booking_id'] ) );

    $return_url = ! empty( $_POST['return_url'] ) ? sanitize_url( $_POST['return_url'] ) : get_permalink();

    $payment_amount = $rtb_controller->settings->get_setting( 'rtb-currency' ) != 'JPY' ? $booking->calculate_deposit() * 100 : $booking->calculate_deposit();

    // load the stripe libraries
    require_once( RTB_PLUGIN_DIR . '/lib/stripe/init.php' );

    try {

      \Stripe\Stripe::setApiKey( $this->get_secret() );

      // $customer = \Stripe\Customer::create(array(
      //  'email' => $booking->email,
      //  'name' => $booking->name
      // );

      // $booking->stripe_customer_id = $customer->id;
      // $booking->insert_post_data();

      $metadata = array_filter(
        array(
          'Booking ID' => $booking->ID,
          'Email'      => $booking->email,
          'Name'       => $booking->name,
          'Date'       => $booking->date,
          'Party'      => $booking->party
        )
      );

      if( is_array( $booking->table ) && ! empty( $booking->table ) ) {

        $metadata['Table'] = implode('+', $booking->table);
      }

      $desc = implode(', ', $metadata );
      $stmt_desc = substr( implode( ';', $metadata ), 0, 22 );

      $intent_data = array(
        'amount'                      => $payment_amount,
        'currency'                    => $rtb_controller->settings->get_setting( 'rtb-currency' ),
        'automatic_payment_methods'   => array(
          'enabled'                     => true,
        ),
        'receipt_email'               => $booking->email,
        'description'                 => apply_filters( 'rtb-stripe-payment-desc', $desc ),
        'statement_descriptor_suffix' => apply_filters( 'rtb-stripe-payment-stmnt-desc', $stmt_desc ),
        'metadata'                    => $metadata,
      );

      $booking->stripe_payment_hold_status = 'not-placed';

      if ( $rtb_controller->settings->get_setting( 'rtb-stripe-hold' ) ) {

        $booking->stripe_payment_hold_status = 'hold-placed';
        $intent_data['capture_method'] = 'manual';
      }

      $intent = \Stripe\PaymentIntent::create( $intent_data );

      // Used this for verification of two step payment processing under SCA
      $booking->stripe_payment_intent_id = $intent->id;

      $booking->insert_post_data();

      $args = array(
        'clientSecret'  => $intent->client_secret,
        'name'          => $booking->name,
        'email'         => $booking->email,
        'redirect_url'  => $return_url,
      );

      $this->response_message( true, __( 'Payment Intent generated succsssfully', 'restaurant-reservations' ), $args );
    }
    catch( Exception $ex ) {

      $this->response_message( false, 'Please try again.', array( 'error' => $ex->getError() ) );
    }
  }

  /**
   * Stripe SCA payment's final status for reservation deposits
   * Respond to AJAX/XHR request
   * 
   * @since 2.2.8
   */
  public function stripe_sca_succeed() {
    global $rtb_controller;

    if ( !check_ajax_referer( 'rtb-stripe-payment', 'nonce' ) ) {

      $this->response_message( false, __( 'The request has been rejected because it does not appear to have come from this site.', 'restaurant-reservations' ) );
    }

    // Response variables with fallback defaults
    $success = false;
    $url_params = '';
    $data = array();

    if ( ! isset( $_POST['booking_id'] ) ) { $this->sca_response( $success, $url_params, $data ); }

    $booking = new rtbBooking();
    $loaded = $booking->load_post( intval( $_POST['booking_id'] ) );

    try {

      if ( isset( $_POST['success'] ) && 'false' != sanitize_text_field( $_POST['success'] ) ) {

        if ( ! $loaded || ! $this->valid_payment( $booking ) ) {

          throw new Exception( __( 'Invalid submission. Please contact admin', 'restaurant-reservations' ) );
        }

        $booking->deposit = $rtb_controller->settings->get_setting( 'rtb-currency' ) != 'JPY' ? intval( $_POST['payment_amount'] ) / 100 : intval( $_POST['payment_amount'] );

        $booking->receipt_id = sanitize_text_field( $_POST['payment_id'] );

        // Not needed anymore
        unset( $booking->stripe_payment_intent_id );
        $booking->payment_paid();

        // url_params on successful payment
        $success = true;

        $url_params = array(
          'payment'    => 'paid',
          'booking_id' => intval( $booking->ID )
        );

      }
      else {

        $payment_failure_message = ! empty( $_POST['message'] ) 
          ? sanitize_text_field( $_POST['message'] ) 
          : __( 'Payment charge failed. Please try again', 'restaurant-reservations' );

        throw new Exception( $payment_failure_message );
      }
    }
    catch(Exception $ex) {

      $loaded && $booking->payment_failed( $ex->getMessage() );
      $data['message'] = $ex->getMessage();
    }

    $this->sca_response( $success, $url_params, $data );
  }

  /**
   * Validate the payment success request by verifing the payment_intent ID
   * 
   * @return bool true on valid else false
   */
  public function valid_payment( $booking ) {

    return sanitize_text_field( $_POST['payment_id'] ) == $booking->stripe_payment_intent_id;
  }

  /**
   * Repopulate $booking with stripe meta information
   * 
   * @param  rtbBooking $booking
   */
  public function populate_booking_stripe_info( $booking ) {

    $meta = get_post_meta( $booking->ID, 'rtb', true );

    if ( is_array( $meta ) && isset( $meta['stripe_customer_id'] ) ) {

      $booking->stripe_customer_id = $meta['stripe_customer_id'];
    }

    if ( is_array( $meta ) && isset( $meta['stripe_payment_intent_id'] ) ) {

      $booking->stripe_payment_intent_id = $meta['stripe_payment_intent_id'];
    }

    if ( is_array( $meta ) && isset( $meta['stripe_payment_hold_status'] ) ) {

      $booking->stripe_payment_hold_status = $meta['stripe_payment_hold_status'];
    }
  }

  /**
   * Set $booking's default stripe meta information
   * 
   * @param  rtbBooking $booking
   */
  public function default_booking_stripe_info( $info_list ) {

    $info_list['stripe_customer_id'] = '';

    return $info_list;
  }

  /**
   * Store permanently $booking's default stripe meta information
   * 
   * @param  arrray $meta
   * @param  rtbBooking $booking
   */
  public function save_booking_gateway_info ( $meta, $booking ) {

    if ( isset( $booking->stripe_customer_id ) && !empty( $booking->stripe_customer_id ) ) {

      $meta['stripe_customer_id'] = $booking->stripe_customer_id;
    }

    if ( isset( $booking->stripe_payment_intent_id ) && !empty( $booking->stripe_payment_intent_id ) ) {

      $meta['stripe_payment_intent_id'] = $booking->stripe_payment_intent_id;
    }

    if ( isset( $booking->stripe_payment_hold_status ) ) {

      $meta['stripe_payment_hold_status'] = $booking->stripe_payment_hold_status;
    }

    return $meta;
  }

  /**
   * Add the CSS class to admin booking listing
   * @param array $row_class_list css classes for the row
   * @param rtbBooking $booking
   */
  public function add_hold_class( $row_class_list, $booking ) {

    if( $this->is_payment_on_hold($booking) ) {

      $row_class_list[] = 'payment-on-hold';
    }

    return $row_class_list;
  }

  /**
   * Add hold information in the details popup of the booking for admin
   * @param array $details Label/value item array
   * @param rtbBooking $booking
   */
  public function add_hold_detail( $details, $booking ) {

    if ( ! is_array( $details ) ) { return; }

    if ( $this->is_payment_on_hold( $booking ) ) {

      $details[] = array(
        'label' => __('Payment on Hold', 'restaurant-reservations'),
        'value' => __('Payment has been held on the card, but not charged yet.', 'restaurant-reservations')
      );
    }
    else if ( $this->is_payment_hold_captured( $booking ) ) {

      $details[] = array(
        'label' => __('Held Payment Captured', 'restaurant-reservations'),
        'value' => __('Payment has been captured.', 'restaurant-reservations')
      );
    }

    return $details;
  }

  /**
   * Add Bulk action to booking page for admin to charge the hold manually
   * @param array $actions
   */
  public function add_bulk_action( $actions ) {

    $actions['capture-payment'] = __( 'Charge Payment on Hold',  'restaurant-reservations' );

    return $actions;
  }

  /**
   * Complete the hold and charge the customer
   * @param  array $result Array with booking ID as key and value as message
   * @param  int $id booking-id
   * @param  string $action bulk action
   * @return $result result array with result if we processed the booking
   */
  public function charge_the_hold( $result, $id, $action ) {

    if ( 'capture-payment' !== $action ) {

      return $result;
    }

    $booking = new rtbBooking();

    if ( $booking->load_post( intval( $id ) ) ) {

      if ( $this->is_payment_on_hold( $booking ) ) {

        try {

          // load the stripe libraries
          require_once( RTB_PLUGIN_DIR . '/lib/stripe/init.php' );

          \Stripe\Stripe::setApiKey( $this->get_secret() );

          $intent = \Stripe\PaymentIntent::retrieve( $booking->receipt_id );
          $intent->capture();

          if( 'succeeded' == $intent->status ) {

            $this->hold_captured( $booking );
            // Payment has been captured successfully
            $result[$id] = true;
          }
          else {

            $result[ $id ] = false;

            if( defined('WP_DEBUG') and WP_DEBUG ) {
              error_log(sprintf( __( 'Five Star RTB: Stripe Payment capture failed. Reason: %s', 'restaurant-reservations' ), $intent->status ));
            }
          }
        }
        catch( Exception $ex ) {

          $result[ $id ] = false;

          if( defined('WP_DEBUG') and WP_DEBUG ) {
            error_log( sprintf( __( 'Five Star RTB: Stripe Payment capture failed. Reason: %s', 'restaurant-reservations' ), $ex->getMessage() ) );
          }
        }
      }
      else {

        // We do not have a hold for this Booking
        // $result[$id] = true;
      }
    }
    else {

      $result[ $id ] = false;

      if( defined('WP_DEBUG') and WP_DEBUG ) {

        error_log( sprintf( __( 'Unable to find the Booking for ID %s', 'restaurant-reservations' ), $id ) );
      }
    }

    return $result;
  }

  /**
   * Sends a response with success/failure of payment
   * @param  boolean $success
   * @param  string $msg
   * @param  array $data
   * @return null
   */
  public function response_message( $success, $msg, $data = array() ) {

    echo json_encode(
      array_merge(
        array(
          'success' => $success,
          'message' => $msg
        ), 
        $data
      )
    );

    exit(0);
  }

  /**
   * Sends a final response after checking whether payment intent was successfully processed
   * @param  boolean $success
   * @param  mixed $url_params
   * @param  array $data
   * @return null
   */
  public function sca_response( $success, $url_params, $data = array() ) {

    echo json_encode(
      array_merge(
        array(
          'success' => $success,
          'urlParams' => $url_params
        ), 
        $data
      )
    );

    exit(0);
  }

  /**
   * Check whether the payment is on hold or not
   * @param  rtbBooking  $booking
   * @return boolean
   */
  public function is_payment_on_hold( $booking ) {

    return isset( $booking->stripe_payment_hold_status ) && 'hold-placed' == $booking->stripe_payment_hold_status;
  }

  /**
   * Check whether the payment is on hold or not
   * @param  rtbBooking  $booking
   * @return boolean
   */
  public function is_payment_hold_captured( $booking ) {

    return isset( $booking->stripe_payment_hold_status ) && 'hold-captured' == $booking->stripe_payment_hold_status;
  }

  /**
   * Mark the Payment Hold for the booking as Captured
   * @param  rtbBooking $booking
   * @return rtbPaymentGatewayStripe
   */
  public function hold_captured( $booking ) {

    $booking->stripe_payment_hold_status = 'hold-captured';
    $booking->insert_post_data();

    return $this;
  }

  /**
   * Get Stripe secret
   * @return string
   */
  public function get_secret() {
    global $rtb_controller;

    return 'test' == $rtb_controller->settings->get_setting( 'rtb-stripe-mode' ) ? $rtb_controller->settings->get_setting( 'rtb-stripe-test-secret' ) : $rtb_controller->settings->get_setting( 'rtb-stripe-live-secret' );
  }

  public function payment_summary() {
    global $rtb_controller;
    
    if ( self::$gateway_identifier == $rtb_controller->payment_manager->get_gateway_in_use() and $rtb_controller->settings->get_setting('rtb-stripe-sca') and $rtb_controller->settings->get_setting( 'rtb-stripe-hold' ) ) {

      echo '<p class="stripe-payment-hold-msg">' . esc_html( $rtb_controller->settings->get_setting( 'label-deposit-placing-hold'  ) ) . '</p>';
    }
  }
}

}

/**
 * Gateway has to register itself
 */
add_filter( 'rtb-payment-gateway-register', array( 'rtbPaymentGatewayStripe', 'register_gateway' ) );