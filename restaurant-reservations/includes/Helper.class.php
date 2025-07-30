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

  // Links for the help button
  private static $documentation_link = 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/';
  private static $tutorials_link = 'https://www.youtube.com/playlist?list=PLEndQUuhlvSpWIb_sbRdFsHSkDADYU7JF';
  private static $support_center_link = 'https://www.fivestarplugins.com/support-center/?Plugin=RTB&Type=FAQs';

  // Values for when to trigger the help button to display
  private static $post_types = array( RTB_BOOKING_POST_TYPE );
  private static $additional_pages = array( 'rtb-settings', 'rtb-bookings', 'cffrtb-editor' );

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
   *  method, recursively
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

  public static function display_help_button() {

    if ( ! rtbHelper::should_button_display() ) { return; }

    rtbHelper::enqueue_scripts();

    $page_details = self::get_page_details();

    ?>
      <button class="rtb-dashboard-help-button" aria-label="Help">?</button>

      <div class="rtb-dashboard-help-modal rtb-hidden">
        <div class="rtb-dashboard-help-description">
          <?php echo esc_html( $page_details['description'] ); ?>
        </div>
        <div class="rtb-dashboard-help-tutorials">
          <?php foreach ( $page_details['tutorials'] as $tutorial ) { ?>
            <a href="<?php echo esc_url( $tutorial['url'] ); ?>" target="_blank">
              <?php echo esc_html( $tutorial['title'] ); ?>
            </a>
          <?php } ?>
        </div>
        <div class="rtb-dashboard-help-links">
          <?php if ( ! empty( self::$documentation_link ) ) { ?>
              <a href="<?php echo esc_url( self::$documentation_link ); ?>" target="_blank" aria-label="Documentation">
                <?php _e( 'Documentation', 'restaurant-reservations' ); ?>
              </a>
          <?php } ?>
          <?php if ( ! empty( self::$tutorials_link ) ) { ?>
              <a href="<?php echo esc_url( self::$tutorials_link ); ?>" target="_blank" aria-label="YouTube Tutorials">
                <?php _e( 'YouTube Tutorials', 'restaurant-reservations' ); ?>
              </a>
          <?php } ?>
          <?php if ( ! empty( self::$support_center_link ) ) { ?>
              <a href="<?php echo esc_url( self::$support_center_link ); ?>" target="_blank" aria-label="Support Center">
                <?php _e( 'Support Center', 'restaurant-reservations' ); ?>
              </a>
          <?php } ?>
        </div>
      </div>
    <?php
  }

  public static function should_button_display() {
    global $post;
    
    $page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

    if ( isset( $_GET['post'] ) ) {

      $post = get_post( intval( $_GET['post'] ) );
      $post_type = $post->post_type;
    }
    else {
      
      $post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
    }

    if ( in_array( $post_type, self::$post_types ) ) { return true; }

    if ( in_array( $page, self::$additional_pages ) ) { return true; }

    return false;
  }

  public static function enqueue_scripts() {

    wp_enqueue_style( 'rtb-admin-helper-button', RTB_PLUGIN_URL . '/assets/css/helper-button.css', array(), RTB_VERSION );

    wp_enqueue_script( 'rtb-admin-helper-button', RTB_PLUGIN_URL . '/assets/js/helper-button.js', array( 'jquery' ), RTB_VERSION, true );
  }

  public static function get_page_details() {
    global $post;

    $page_details = array(
      'rtb-bookings' => array(
        'description' => __( 'Easily view, filter, and manage all your restaurant bookings in one place. You can confirm, reject, email, or edit bookings individually or in bulk, and export them by day, upcoming, or a specific date range. Export formats include PDF, Excel, and iCal, with filters for booking statuses like pending, confirmed, closed, or arrived.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/bookings/find-bookings',
            'title' => 'Find Bookings'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/bookings/confirm-reject-bookings',
            'title' => 'Manage Bookings'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/bookings/ban-customers',
            'title' => 'Ban Abusive Customers'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/bookings/booking-manager',
            'title' => 'Booking Manager Role'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/notifications/send-emails',
            'title' => 'Manually Send an Email to One or More Guests'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/export-bookings/export',
            'title' => 'Export Your Bookings'
          ),
        )
      ),
      'rtb-settings' => array(
        'description' => __( 'Set your weekly availability for accepting bookings and define custom scheduling rules for holidays or special events. Control how far in advance or how last-minute bookings can be made, and customize options like time intervals, date pre-selection, and the first day of the week.', 'restaurant-reservations' ),
        'tutorials'   => array(
        )
      ),
      'rtb-settings-rtb-schedule-tab' => array(
        'description' => __( 'Set your weekly availability for accepting bookings and define custom scheduling rules for holidays or special events. Control how far in advance or how last-minute bookings can be made, and customize options like time intervals, date pre-selection, and the first day of the week.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/bookings/schedule',
            'title' => 'Set the Booking Schedule'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/multiple-locations/',
            'title' => 'Multiple Locations'
          ),
        )
      ),
      'rtb-settings-rtb-basic' => array(
        'description' => __( 'Adjust essential booking form settings such as party size limits, required contact fields, and default messages. You can enable auto-confirmation, guest cancellations, and configure redirects for different booking outcomes. Additional options let you manage privacy, spam protection, and how long reservation data is stored.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/bookings/modify-cancel-bookings',
            'title' => 'Allow Customers to Cancel Bookings'
          ),
        )
      ),
      'rtb-settings-rtb-advanced-tab' => array(
        'description' => __( 'Set seat and reservation limits to control capacity during peak times, and configure auto-confirmation rules based on party size or total bookings. Enable front-end table selection and create customizable table and section layouts, including an optional table layout graphic. Additional tools let you configure a guest check-in form, connect MailChimp, and manage access to the booking view page.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/view-bookings/',
            'title' => 'View Bookings Page'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/tables/',
            'title' => 'Manage Tables'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/mailchimp/connect',
            'title' => 'Connect to MailChimp'
          ),
        )
      ),
      'rtb-settings-rtb-notifications-tab' => array(
        'description' => __( 'Configure email and SMS alerts for every stage of the booking process, including pending, confirmed, cancelled, and completed reservations. Customize message content using template tags, set reply-to details, and control which notifications are sent to admins and customers. You can also enable daily summary emails and automate reminders or follow-ups with flexible timing options.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/notifications/email-content',
            'title' => 'Email Content (Free)'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/notifications/notifications-table',
            'title' => 'Notifications Table (Premium & Ultimate)'
          ),
        )
      ),
      'rtb-settings-rtb-payments-tab' => array(
        'description' => __( 'You can require guests to pay a deposit when booking, choosing between PayPal or Stripe as the payment gateway. Deposits can be set per reservation, guest, or table, with flexible rules for when and how much to charge. Stripe also supports advanced options like Strong Customer Authentication and payment holds.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/payments/custom-gateway',
            'title' => 'Custom Payment Gateway'
          ),
        )
      ),
      'rtb-settings-rtb-styling-tab' => array(
        'description' => __( 'Customize the look and feel of your reservation form. You can pick from different layouts like Default, Minimal, or Contemporary, and adjust fonts, colors, and sizes for section titles, labels, and buttons. There are also detailed color controls for all buttons, including normal and hover states, so your booking form matches your restaurant’s brand perfectly.', 'restaurant-reservations' ),
        'tutorials'   => array(
        )
      ),
      'rtb-settings-rtb-labelling-tab' => array(
        'description' => __( 'Customize or translate all the wording in the plugin. You can easily change labels to suit your brand’s voice or translate the plugin into a different language without needing extra translation plugins. Plus, the plugin is fully localized, so it works seamlessly with third-party translation tools if needed.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/labelling/translating',
            'title' => 'Create your own Translation'
          ),
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/labelling/poedit',
            'title' => 'Translating with Poedit'
          ),
        )
      ),
      'rtb-settings-rtb-export-tab' => array(
        'description' => __( 'In the Export settings, you can choose your preferred paper size for PDFs, with A4 as the default. For PDF generation, select between mPDF (better visuals) or TCPDF (more compatible). You can also customize the date format used in Excel or CSV exports, which is handy if you need a specific machine-readable date style different from your WordPress default.', 'restaurant-reservations' ),
        'tutorials'   => array(
        )
      ),
      'cffrtb-editor' => array(
        'description' => __( 'Tailor your booking form to collect important details like special seating requests, dietary needs, and more.', 'restaurant-reservations' ),
        'tutorials'   => array(
        )
      ),
      'rtb-settings-rtb-settings-api-tab' => array(
        'description' => __( 'You can manage your app API keys by adding new keys and controlling access. Additionally, there’s an error log that displays the 10 most recent error notifications from the last week, helping you monitor any issues.', 'restaurant-reservations' ),
        'tutorials'   => array(
          array(
            'url'   => 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/user/fsrm/',
            'title' => 'Install and configure the Restaurant Manager mobile app'
          ),
        )
      ),
    );

    $tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
    $page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

    if ( isset( $_GET['post'] ) ) {

      $post = get_post( intval( $_GET['post'] ) );
      $post_type = $post->post_type;
    }
    else {
      
      $post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
    }

    if ( in_array( $page . '-' . $tab, array_keys( $page_details ) ) ) { return $page_details[ $page . '-' . $tab ]; }

    if ( in_array( $page, array_keys( $page_details ) ) ) { return $page_details[ $page ]; }

    if ( in_array( $post_type, array_keys( $page_details ) ) ) { return $page_details[ $post_type ]; }

    return array( 'description', 'tutorials' => array() );
  }
}

}