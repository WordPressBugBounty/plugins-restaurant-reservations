<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbHelper' ) ) {
/**
 * Class to to provide helper functions
 *
 * @since 2.4.10
 */
class rtbHelper {

  // Hold the class instance.
  private static $instance = null;

  /**
   * The constructor is private
   * to prevent initiation with outer code.
   * 
   **/
  private function __construct() {}

  /**
   * The object is created from within the class itself
   * only if the class has no instance.
   */
  public static function getInstance() {

    if ( self::$instance == null ) {

      self::$instance = new rtbHelper();
    }
 
    return self::$instance;
  }

  /**
   * Handle ajax requests from the admin bookings area from logged out users
   * @since 2.4.10
   */
  public static function admin_nopriv_ajax() {

    wp_send_json_error(
      array(
        'error' => 'loggedout',
        'msg' => sprintf( __( 'You have been logged out. Please %slogin again%s.', 'restaurant-reservations' ), '<a href="' . wp_login_url( admin_url( 'admin.php?page=rtb-dashboard' ) ) . '">', '</a>' ),
      )
    );
  }

  /**
   * Handle ajax requests where an invalid nonce is passed with the request
   * @since 2.4.10
   */
  public static function bad_nonce_ajax() {

    wp_send_json_error(
      array(
        'error' => 'badnonce',
        'msg' => __( 'The request has been rejected because it does not appear to have come from this site.', 'restaurant-reservations' ),
      )
    );
  }

  /**
   * sanitize_text_field for array's each value, recusivly
   * @since 2.4.10
   */
  public static function sanitize_text_field_recursive( $input ) {

    if ( is_array( $input ) || is_object( $input ) ) {

      foreach ( $input as $key => $value ) {

        $input[ sanitize_key( $key ) ] = self::sanitize_text_field_recursive( $value );
      }

      return $input;
    }

    return sanitize_text_field( $input );
  }

  /**
   * sanitize_recursive for array's each value by applying given sanitization
   *  method, recusivly
   * @since 2.4.10
   */
  public static function sanitize_recursive( $input, $method ) {

    if ( is_array( $input ) || is_object( $input ) ) {

      foreach ( $input as $key => $value ) {

        $input[ sanitize_key( $key ) ] = self::sanitize_recursive( $value, $method);
      }

      return $input;
    }

    return $method( $input );
  }
}

}