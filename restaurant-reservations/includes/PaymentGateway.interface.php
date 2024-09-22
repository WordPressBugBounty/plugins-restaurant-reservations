<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !interface_exists( 'rtbPaymentGateway' ) ) {
/**
 * Base interface to implement a Payment Gateway for Restaurant Reservations
 *
 * This class enforces the Payment Gateway base settings and their processing
 * methods. This class should be implemented for each type of
 * Payment Gateway. So, there would be a rtbPaymentStripe class or a
 * rtbPaymentPayPal class.
 *
 * @since 2.3.0
 */
interface rtbPaymentGateway
{
  /**
   * The gateway name, which will be used to identify the gateway
   * Consider this string as a slug or an array index
   * 
   * @type string
   * 
   * public $gateway_identifier;
   * 
   * -----------------------------------------------------------------------------------------------
   * 
   * Why a comment?
   * https://www.php.net/manual/en/language.oop5.interfaces.php#language.oop5.interfaces.constants
   * 
   * Constants
   * It's possible for interfaces to have constants. Interface constants work exactly like
   * class constants. Prior to PHP 8.1.0, they cannot be overridden by a class/interface
   * that inherits them.
   * -----------------------------------------------------------------------------------------------
   * */

  /**
   * Register the gateway.
   * 
   * $gateway_list['stripe'] => array(
   *     'label'    => __( 'Stripe', 'restaurant-reservations' ),
   *     'instance' => $this
   * );
   * 
   * @param  array  $gateway_list
   * @return array  $gateway_list
   */
  public static function register_gateway( array $gateway_list );

  /**
   * Print the payment form. The booking has been made and is on payment_pending
   * status. Display the payment form/button here
   * 
   * @param  rtbBooking $booking booking for which the payment is required
   * @return void                        Print the HTML
   */
  public function print_payment_form( $booking );

}

}