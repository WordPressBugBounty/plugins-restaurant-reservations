<?php
/**
 * Plugin Name: Five Star Restaurant Reservations - WordPress Booking Plugin
 * Plugin URI: http://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/
 * Description: Restaurant reservations made easy. Accept bookings online. Quickly confirm or reject reservations, send email notifications, set booking times and more.
 * Version: 2.6.26
 * Author: Five Star Plugins
 * Author URI: https://www.fivestarplugins.com/
 * Text Domain: restaurant-reservations
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'rtbInit' ) ) {
class rtbInit {

	// pointers to classes used by the plugin, where needed
	public $ajax;
	public $bookings;
	public $cpts;
	public $cron;
	public $custom_fields;
	public $editor;
	public $email_templates;
	public $exports;
	public $fields;
	public $locations;
	public $mailchimp;
	public $migrationManager;
	public $notifications;
	public $payment_manager;
	public $permissions;
	public $settings;

	/**
	 * Set a flag which tracks whether the form has already been rendered on
	 * the page. Only one form per page for now.
	 * @todo support multiple forms per page
	 */
	public $form_rendered = false;

	/**
	* Set a flag which tracks whether the view bookings form has already been 
	* rendered on the page. Only one form per page for now.
	*/
	public $display_bookings_form_rendered = false;

	/**
	 * An object which stores a booking request, or an empty object if
	 * no request has been processed.
	 */
	public $request;

	/**
	 * Initialize the plugin and register hooks
	 */
	public function __construct() {

		// Common strings
		define( 'RTB_VERSION', '2.6.26' );
		define( 'RTB_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'RTB_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'RTB_PLUGIN_FNAME', plugin_basename( __FILE__ ) );
		define( 'RTB_BOOKING_POST_TYPE', 'rtb-booking' );
		define( 'RTB_BOOKING_POST_TYPE_SLUG', 'booking' );

		// Initialize the plugin
		add_action( 'plugins_loaded', array( $this, 'plugin_loaded_action_hook' ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );

		add_action( 'init', array( $this, 'output_buffer_start' ) );
		add_action( 'shutdown', array( $this, 'output_buffer_end' ) );

		// Set up empty request object
		$this->request = new stdClass();
		$this->request->raw_input = array();
		$this->request->request_processed = false;
		$this->request->request_inserted = false;
	}

	public function boot() {

		// Load booking post wrapper class
		require_once( RTB_PLUGIN_DIR . '/includes/Booking.class.php' );

		// Load helper class
		require_once( RTB_PLUGIN_DIR . '/includes/Helper.class.php' );

		// Load query class
		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );

		// Add custom roles and capabilities
		add_action( 'init', array( $this, 'add_roles' ) );

		// Load the plugin permissions
		require_once( RTB_PLUGIN_DIR . '/includes/Permissions.class.php' );
		$this->permissions = new rtbPermissions();
		$this->handle_combination();

		// Load custom post types
		require_once( RTB_PLUGIN_DIR . '/includes/CustomPostTypes.class.php' );
		$this->cpts = new rtbCustomPostTypes();

		// Load deactivation survey
		require_once( RTB_PLUGIN_DIR . '/includes/DeactivationSurvey.class.php' );
		new rtbDeactivationSurvey();

		// Load review ask
		require_once( RTB_PLUGIN_DIR . '/includes/ReviewAsk.class.php' );
		new rtbReviewAsk();

		// Load multiple location support
		require_once( RTB_PLUGIN_DIR . '/includes/MultipleLocations.class.php' );
		$this->locations = new rtbMultipleLocations();

		// Flush the rewrite rules for the custom post types
		register_activation_hook( __FILE__, array( $this, 'rewrite_flush' ) );

		// Make any changes necessary between versions
		register_activation_hook( __FILE__, array( $this, 'load_migrations' ) );
		
		// Autoupdates are called via cron and cron doesn't deactivate the plugin
		// thus, we can't rely on activation hook alone to execute migrations
		add_action( 'upgrader_process_complete', array( $this, 'load_migrations' ), 10, 2 );

		// Load the template functions which print the booking form, etc
		require_once( RTB_PLUGIN_DIR . '/includes/template-functions.php' );

		// Load the admin bookings page
		require_once( RTB_PLUGIN_DIR . '/includes/AdminBookings.class.php' );
		$this->bookings = new rtbAdminBookings();

		// Load assets
		add_action( 'admin_notices', array($this, 'display_header_area') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		// Handle notifications
		require_once( RTB_PLUGIN_DIR . '/includes/Notifications.class.php' );
		$this->notifications = new rtbNotifications();

		// Load settings
		require_once( RTB_PLUGIN_DIR . '/includes/Settings.class.php' );
		$this->settings = new rtbSettings();

		// Load Payment Manager and GatewayInterface
		require_once( RTB_PLUGIN_DIR . "/includes/PaymentGateway.interface.php" );
		require_once( RTB_PLUGIN_DIR . '/includes/PaymentManager.class.php' );
		$this->payment_manager = new rtbPaymentManager();

		// Load plugin dashboard
		require_once( RTB_PLUGIN_DIR . '/includes/Dashboard.class.php' );
		new rtbDashboard();

		// Load about us page
		require_once( RTB_PLUGIN_DIR . '/includes/AboutUs.class.php' );
		new rtbAboutUs();
		
		// Load walk-through
		require_once( RTB_PLUGIN_DIR . '/includes/InstallationWalkthrough.class.php' );
		new rtbInstallationWalkthrough();
		register_activation_hook( __FILE__, array( $this, 'run_walkthrough' ) );

		// Create cron jobs for reminders and late arrivals
		require_once( RTB_PLUGIN_DIR . '/includes/Cron.class.php' );
		$this->cron = new rtbCron();
		register_activation_hook( __FILE__, array( $this, 'cron_schedule_events' ) );
		register_deactivation_hook( __FILE__, array( $this, 'cron_unschedule_events' ) );

		// Handle AJAX actions
		require_once( RTB_PLUGIN_DIR . '/includes/Ajax.class.php' );
		$this->ajax = new rtbAJAX();

		// Handle setting up exports
		require_once( RTB_PLUGIN_DIR . '/includes/ExportHandler.class.php' );
		$this->exports = new rtbExportHandler();

		// Handle setting up exports
		require_once( RTB_PLUGIN_DIR . '/includes/EmailTemplates.class.php' );
		$this->email_templates = new rtbEmailTemplates();

		// Load the custom fields
		require_once( RTB_PLUGIN_DIR . '/includes/CustomFields.class.php' );
		$this->custom_fields = new rtbCustomFields();

		// Load in the custom fields controller
		require_once( RTB_PLUGIN_DIR . '/includes/Field.Controller.class.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/Field.class.php' );
		$this->fields = new rtbFieldController();

		// Load the custom fields editor page
		require_once( RTB_PLUGIN_DIR . '/includes/Editor.class.php' );
		$this->editor = new cffrtbEditor();

		// Load MailChimp integration
		require_once( RTB_PLUGIN_DIR . '/includes/MailChimp.class.php' );
		$this->mailchimp = new mcfrtbInit();

		// Append booking form to a post's $content variable
		add_filter( 'the_content', array( $this, 'append_to_content' ) );

		// Register the widget
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		// Add links to plugin listing
		add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2);

		// Load integrations with other plugins
		require_once( RTB_PLUGIN_DIR . '/includes/integrations/business-profile.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/integrations/woocommerce.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/integrations/page-builder.php' );

		// Load Gutenberg blocks
		require_once( RTB_PLUGIN_DIR . '/includes/Blocks.class.php' );
		new rtbBlocks();

		// Load Gutenberg block patterns
		require_once( RTB_PLUGIN_DIR . '/includes/Patterns.class.php' );
		if ( function_exists( 'register_block_pattern' ) ) { new rtbPatterns(); }

		// Load backwards compatibility functions
		require_once( RTB_PLUGIN_DIR . '/includes/Compatibility.class.php' );
		new rtbCompatibility();

		// Delete old reservations if necessary 
		add_action( 'admin_init', array( $this, 'maybe_delete_payment_pending_reservations' ), 8 );
		add_action( 'admin_init', array( $this, 'maybe_delete_older_reservations' ) );
		add_filter( 'sanitize_option_rtb-settings', array( $this, 'maybe_delete_older_reservations' ), 100 );

		// Handle the helper notice
		add_action( 'admin_notices', array( $this, 'maybe_display_helper_notice' ) );
		add_action( 'wp_ajax_rtb_hide_helper_notice', array( $this, 'hide_helper_notice' ) );
	}

	/**
	 * Flush the rewrite rules when this plugin is activated to update with
	 * custom post types
	 * @since 0.0.1
	 */
	public function rewrite_flush() {
		$this->cpts->load_cpts();
		flush_rewrite_rules();
	}

	/**
	 * Make any database changes needed between versions
	 * @since 2.2.5
	 */
	public function load_migrations( $upgrader = null, $args = [] ) {

		/** Plugin_Upgrader class */
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		// When something has been upgraded
		// Check if its a plugin update and see if it is ours.
		if( null != $upgrader && ! ( $upgrader instanceof Plugin_Upgrader ) ) {
			return;
		}

		require_once( RTB_PLUGIN_DIR . '/includes/Migration.class.php' );

		$this->migrationManager = new rtbMigrationManager();
	}

	/**
	 * Allow third-party plugins to interact with the plugin, if necessary
	 * @since 2.5.0
	 */
	public function plugin_loaded_action_hook() {

		do_action( 'rtb_initialized' );
	}

	/**
	 * Load the plugin textdomain for localistion
	 * @since 0.0.1
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'restaurant-reservations', false, plugin_basename( dirname( __FILE__ ) ) . "/languages/" );
	}

	/**
	 * Set a transient so that the walk-through gets run
	 * @since 2.0
	 */
	public function run_walkthrough() {
		set_transient('rtb-getting-started', true, 30);
	} 

	/**
	 * Add a role to manage the bookings and add the capability to Editors,
	 * Administrators and Super Admins
	 * @since 0.0.1
	 */
	public function add_roles() {

		// The booking manager should be able to access the bookings list and
		// update booking statuses, but shouldn't be able to touch anything else
		// in the account.
		$booking_manager = add_role(
			'rtb_booking_manager',
			__( 'Booking Manager', 'restaurant-reservations' ),
			array(
				'read'				=> true,
				'manage_bookings'	=> true,
			)
		);

		$manage_bookings_roles = apply_filters(
			'rtb_manage_bookings_roles',
			array(
				'administrator',
				'editor',
			)
		);

		global $wp_roles;
		foreach ( $manage_bookings_roles as $role ) {
			$wp_roles->add_cap( $role, 'manage_bookings' );
		}
	}

	/**
	 * Append booking form to a post's $content variable
	 * @since 0.0.1
	 */
	function append_to_content( $content ) {
		global $post;

		if ( !is_main_query() || !in_the_loop() || post_password_required() ) {
			return $content;
		}

		if ( $post->ID == $this->settings->get_setting( 'booking-page' ) ) {
			return $content . rtb_print_booking_form();
		}

		if ( $post->ID == $this->settings->get_setting( 'view-bookings-page' ) ) {

			if ( $this->settings->get_setting( 'view-bookings-private' ) and ! is_user_logged_in() ) { return $content; }

			$args = array(
				'location' => isset( $_GET['booking_location'] ) ? $_GET['booking_location'] : 0,
				'date' => isset( $_GET['date'] ) ? $_GET['date'] : date('Y-m-d')
			);

			return $content . rtb_print_view_bookings_form( $args );
		}

		return $content;
	}

	/**
	 * Deletes older reservations, after a user-specified number of days (return if no number specified)
	 * @since 2.4.11
	 */
	public function maybe_delete_older_reservations( $val = null ) {
		global $rtb_controller;

		if ( get_transient( 'rtb-delete-reservations-check' ) and empty( $val ) ) { return $val; }

		set_transient( 'rtb-delete-reservations-check', true, 3600*12 );

		if ( empty( $rtb_controller->settings->get_setting( 'delete-data-days' ) ) ) { return $val; }

		$reservation_statuses = is_array( $rtb_controller->cpts->booking_statuses ) ? array_keys( $rtb_controller->cpts->booking_statuses ) : array();

		$args = array( 
			'numberposts' 	=> -1, 
			'post_type' 	=> RTB_BOOKING_POST_TYPE,
			'post_status'	=> $reservation_statuses,
			'date_query' 	=> array(
				array(
					'before' => date('Y-m-d h:i:s', time() - $rtb_controller->settings->get_setting( 'delete-data-days' ) * 24*3600 )
				)
			)
		);

		$query = new WP_Query( $args );

		$delete_posts = $query->get_posts();

		foreach ( $delete_posts as $delete_post ) {

			wp_delete_post( $delete_post->ID, true );
		}

		return $val;
	}

	/**
	 * Delete payment pending reservations older than 7 days

	 * Uses the same transient as `maybe_delete_older_reservations`,
	 * so needs to run with higher priority
	 * @since 2.0
	 */
	public function maybe_delete_payment_pending_reservations() {
		global $rtb_controller;

		if ( get_transient( 'rtb-delete-reservations-check' ) ) { return; }

		// Only run if payments are enabled
		if (  empty( $rtb_controller->settings->get_setting( 'require-deposit' ) ) ) { return; }

		if ( ! in_array( 'payment_pending', get_post_stati() ) ) { return; }

		// Delete payment pending reservations after 7 days (filterable) 
		$args = array( 
			'numberposts' 		=> -1, 
			'post_type' 		=> RTB_BOOKING_POST_TYPE,
			'post_status'		=> 'payment_pending',
			'suppress_filters'	=> true,
			'date_query' 		=> array(
				array(
					'before' => date( 'Y-m-d h:i:s', time() - apply_filters( 'rtb_payment_pending_days_delete', 7 ) * 24 * 3600 )
				)
			)
		);
	
		$query = new WP_Query( $args );
		
		$payment_pending_posts = $query->get_posts();

		foreach ( $payment_pending_posts as $delete_post ) {

			wp_delete_post( $delete_post->ID, true );
		}
	}

	/**
	 * Adds in a menu bar for the plugin
	 * @since 2.0
	 */
	public function display_header_area() {
		global $rtb_controller, $admin_page_hooks, $post;

		$screen = get_current_screen();
		$screenID = $screen->id;
		
		if ( $screenID != $admin_page_hooks['rtb-bookings'] . '_page_rtb-settings' && $screenID != 'toplevel_page_rtb-bookings' && $screenID != $admin_page_hooks['rtb-bookings'] . '_page_rtb-dashboard' && $screenID != $admin_page_hooks['rtb-bookings'] . '_page_cffrtb-editor' && $screenID != $admin_page_hooks['rtb-bookings'] . '_page_rtb-about-us' ) {return;}

		if ( ! $rtb_controller->permissions->check_permission( 'styling' ) or get_option("RTB_Trial_Happening") == "Yes" or get_option("RTU_Trial_Happening") == "Yes" ) {
			?>
			<div class="rtb-dashboard-new-upgrade-banner">
				<div class="rtb-dashboard-banner-icon"></div>
				<div class="rtb-dashboard-banner-buttons">
					<a class="rtb-dashboard-new-upgrade-button" href="https://www.fivestarplugins.com/license-payment/?Selected=RTB&Quantity=1&utm_source=rtb_admin&utm_content=banner" target="_blank">UPGRADE NOW</a>
				</div>
				<div class="rtb-dashboard-banner-text">
					<div class="rtb-dashboard-banner-title">
						GET FULL ACCESS WITH OUR PREMIUM VERSION
					</div>
					<div class="rtb-dashboard-banner-brief">
						New layouts, custom fields, MailChimp integration and more!
					</div>
				</div>
			</div>
			<?php
		}

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( get_option( 'rtb-pro-was-active' ) > time() - 7*24*3600 ) {
			echo "<div class='rtb-deactivate-pro'>";
			echo "<p>We've combined the code base for the free and pro versions into one plugin file for easier management.</p>";
			echo "<p>You still have access to the premium features you purchased, and you can read more about why we've combined them <a href='http://www.fivestarplugins.com/2019/10/21/five-star-restaurant-reservations-new-features-more-options/'>on our blog</a></p>";
			echo "</div>";
		}
		
		?>
		<div class="rtb-admin-header-menu">
			<h2 class="nav-tab-wrapper">
				<a id="rtb-dash-mobile-menu-open" href="#" class="menu-tab nav-tab">
					<?php _e("MENU", 'restaurant-reservations'); ?>
					<span id="rtb-dash-mobile-menu-down-caret">&nbsp;&nbsp;&#9660;</span>
					<span id="rtb-dash-mobile-menu-up-caret">&nbsp;&nbsp;&#9650;</span>
				</a>

				<?php if( current_user_can( 'manage_options' ) ) { ?>
					<a id="dashboard-menu" href='admin.php?page=rtb-dashboard'
						class="menu-tab nav-tab <?php echo 'bookings_page_rtb-dashboard' == $screenID ? 'nav-tab-active' : ''; ?>">
						<?php _e("Dashboard", 'restaurant-reservations'); ?>
					</a>
				<?php } ?>

				<a id="bookings-menu" href='admin.php?page=rtb-bookings'
					class="menu-tab nav-tab <?php echo 'toplevel_page_rtb-bookings' == $screenID ? 'nav-tab-active' : ''; ?>">
					<?php _e("Bookings", 'restaurant-reservations'); ?>
				</a>

				<?php if( current_user_can( 'manage_options' ) ) { ?>
					<a id="options-menu" href='admin.php?page=rtb-settings'
						class="menu-tab nav-tab <?php echo 'bookings_page_rtb-settings' == $screenID ? 'nav-tab-active' : ''; ?>">
						<?php _e("Settings", 'restaurant-reservations'); ?>
					</a>
				<?php } ?>
				
				<?php if ( current_user_can( 'manage_options' ) && $rtb_controller->permissions->check_permission( 'custom_fields' ) ) { ?>
					<a id="customfields-menu" href='admin.php?page=cffrtb-editor'
						class="menu-tab nav-tab <?php echo 'bookings_page_cffrtb-editor' == $screenID ? 'nav-tab-active' : '';?>">
						<?php _e("Custom Fields", 'restaurant-reservations'); ?>
					</a>
				<?php } ?>
			</h2>
		</div>
		<?php
	}

	/**
	 * Enqueue the admin-only CSS and Javascript
	 * @since 0.0.1
	 */
	public function enqueue_admin_assets() {

		global $rtb_controller;

		wp_enqueue_script( 'rtb-helper-notice', RTB_PLUGIN_URL . '/assets/js/helper-install-notice.js', array( 'jquery' ), RTB_VERSION, true );
		wp_localize_script(
			'rtb-helper-notice',
			'rtb_helper_notice',
			array( 'nonce' => wp_create_nonce( 'rtb-helper-notice' ) )
		);

		wp_enqueue_style( 'rtb-helper-notice', RTB_PLUGIN_URL . '/assets/css/helper-install-notice.css', array(), RTB_VERSION );

		// Use the page reference in $admin_page_hooks because
		// it changes in SOME hooks when it is translated.
		// https://core.trac.wordpress.org/ticket/18857
		global $admin_page_hooks;

		$screen = get_current_screen();
		if ( empty( $screen ) || empty( $admin_page_hooks['rtb-bookings'] ) ) {
			return;
		}

		if (
			$screen->base == 'toplevel_page_rtb-bookings' 
			|| $screen->base == $admin_page_hooks['rtb-bookings'] . '_page_rtb-settings' 
			|| $screen->base == $admin_page_hooks['rtb-bookings'] . '_page_rtb-addons' 
			|| $screen->base == $admin_page_hooks['rtb-bookings'] . '_page_cffrtb-editor'
			|| $screen->base == $admin_page_hooks['rtb-bookings'] . '_page_rtb-dashboard'
			|| $screen->base == $admin_page_hooks['rtb-bookings'] . '_page_rtb-about-us'
		) {
			wp_enqueue_style( 'rtb-admin-css', RTB_PLUGIN_URL . '/assets/css/admin.css', array(), RTB_VERSION );
			wp_enqueue_script( 'rtb-admin-js', RTB_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery' ), '', true );
			wp_enqueue_style( 'rtb-spectrum-css', RTB_PLUGIN_URL . '/assets/css/spectrum.css' );
			wp_enqueue_script( 'rtb-spectrum-js', RTB_PLUGIN_URL . '/assets/js/spectrum.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'rtb-admin-settings-js', RTB_PLUGIN_URL . '/assets/js/admin-settings.js', array( 'jquery' ), '', true );

			$refresh_time = $rtb_controller->settings->get_setting('refresh-booking-listing');
			if( empty( $refresh_time ) || 1 > intval( $refresh_time ) ) {
				$refresh_time = 0;
			}
			else {
				$refresh_time = intval( $refresh_time ) * 60;
			}

			wp_localize_script(
				'rtb-admin-js',
				'rtb_admin',
				array(
					'nonce'		=> wp_create_nonce( 'rtb-admin' ),
					'strings'	=> array(
						'add_booking'		=> __( 'Add Booking', 'restaurant-reservations' ),
						'edit_booking'		=> __( 'Edit Booking', 'restaurant-reservations' ),
						'error_unspecified'	=> __( 'An unspecified error occurred. Please try again. If the problem persists, try logging out and logging back in.', 'restaurant-reservations' ),
					),
					'banned_emails' => preg_split( '/\r\n|\r|\n/', (string) $rtb_controller->settings->get_setting( 'ban-emails' ) ),
					'banned_ips' => preg_split( '/\r\n|\r|\n/', (string) $rtb_controller->settings->get_setting( 'ban-ips' ) ),
					'export_url' => admin_url( '?action=ebfrtb-export' ),
					'refresh_booking_listing' => $refresh_time
				)
			);
		}

		// Enqueue frontend assets to add/edit bookins on the bookings page
		if ( $screen->base == 'toplevel_page_rtb-bookings' ) {
			$this->register_assets();
			rtb_enqueue_assets();
		}
	}

	/**
	 * Register the front-end CSS and Javascript for the booking form
	 * @since 0.0.1
	 */
	function register_assets() {
		global $rtb_controller;

		if ( !apply_filters( 'rtb-load-frontend-assets', true ) ) {
			return;
		}

		wp_register_style( 'pickadate-default', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/default.css' );
		wp_register_style( 'pickadate-date', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/default.date.css' );
		wp_register_style( 'pickadate-time', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/default.time.css' );
		wp_register_script( 'pickadate', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/picker.js', array( 'jquery' ), '', true );
		wp_register_script( 'pickadate-date', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/picker.date.js', array( 'jquery' ), '', true );
		wp_register_script( 'pickadate-time', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/picker.time.js', array( 'jquery' ), '', true );
		wp_register_script( 'pickadate-legacy', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/legacy.js', array( 'jquery' ), '', true );

		$i8n = $this->settings->get_setting( 'i8n' );
		if ( !empty( $i8n ) ) {
			wp_register_script( 'pickadate-i8n', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/translations/' . esc_attr( $i8n ) . '.js', array( 'jquery' ), '', true );

			// Arabic and Hebrew are right-to-left languages
			if ( $i8n == 'ar' || $i8n == 'he_IL' ) {
				wp_register_style( 'pickadate-rtl', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/rtl.css' );
			}
		}

		wp_register_style( 'rtb-booking-form', RTB_PLUGIN_URL . '/assets/css/booking-form.css' );
		wp_register_script( 'rtb-booking-form', RTB_PLUGIN_URL . '/assets/js/booking-form.js', array( 'jquery' ) );
		wp_localize_script(
			'rtb-booking-form',
			'rtb_booking_form_js_localize',
			array(
				'nonce'                  => wp_create_nonce( 'rtb-booking-form' ),
				'cancellation_cutoff'    => $rtb_controller->settings->get_setting( 'late-cancellations' ),
				'want_to_modify'         => esc_html( $rtb_controller->settings->get_setting( 'label-modify-reservation'  ) ),
				'make'                   => esc_html( $rtb_controller->settings->get_setting( 'label-modify-make-reservation'  ) ),
				'guest'                  => esc_html( $rtb_controller->settings->get_setting( 'label-modify-guest'  ) ),
				'guests'                 => esc_html( $rtb_controller->settings->get_setting( 'label-modify-guests'  ) ),
				'cancel'                 => esc_html( $rtb_controller->settings->get_setting( 'label-modify-cancel'  ) ),
				'cancelled'              => esc_html( $rtb_controller->settings->get_setting( 'label-modify-cancelled'  ) ),
				'deposit'                => esc_html( $rtb_controller->settings->get_setting( 'label-modify-deposit'  ) ),
				'tables_graphic_width'   => esc_html( $rtb_controller->settings->get_setting( 'tables-graphic-width'  ) ),
				'error'                  => array(
					'smthng-wrng-cntct-us'  => esc_html( $rtb_controller->settings->get_setting( 'label-something-went-wrong'  ) ),
					'no-slots-available'    => esc_html( $rtb_controller->settings->get_setting( 'label-no-times-available'  ) ),
					'no-table-available'    => esc_html( $rtb_controller->settings->get_setting( 'label-no-table-available'  ) )
				)
			)
		);
	}

	/**
	 * Register the widgets
	 * @since 0.0.1
	 */
	public function register_widgets() {
		require_once( RTB_PLUGIN_DIR . '/includes/WP_Widget.BookingFormWidget.class.php' );
		register_widget( 'rtbBookingFormWidget' );
	}

	/**
	 * Add links to the plugin listing on the installed plugins page
	 * @since 0.0.1
	 */
	public function plugin_action_links( $links, $plugin ) {
		global $rtb_controller;
		
		if ( $plugin == RTB_PLUGIN_FNAME ) {

			if ( ! $rtb_controller->permissions->check_permission( 'premium' ) ) {

				array_unshift( $links, '<a class="rtb-plugin-page-upgrade-link" href="https://www.fivestarplugins.com/license-payment/?Selected=RTB&Quantity=1&utm_source=wp_admin_plugins_page" title="' . __( 'Try Premium', 'restaurant-reservations' ) . '" target="_blank">' . __( 'Try Premium', 'restaurant-reservations' ) . '</a>' );
			}

			$links['help'] = '<a href="http://doc.fivestarplugins.com/plugins/restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%Help&utm_campaign=Restaurant%20Reservations" title="' . __( 'View the help documentation for Restaurant Reservations', 'restaurant-reservations' ) . '">' . __( 'Help', 'restaurant-reservations' ) . '</a>';
		}

		return $links;

	}

	/**
	 * Register the cron hook that the plugin uses
	 * @since 2.0
	 */
	public function cron_schedule_events() {
		$this->cron->schedule_events();
	}

	/**
	 * Unregister the cron hook that the plugin uses
	 * @since 2.0
	 */
	public function cron_unschedule_events() {
		$this->cron->unschedule_events();
	}

	/**
	 * Handle the codebase combination
	 * @since 2.0
	 */
	public function handle_combination() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( "custom-fields-for-rtb/custom-fields-for-rtb.php" ) ) {
			update_option('rtb-pro-was-active', time());
			deactivate_plugins("custom-fields-for-rtb/custom-fields-for-rtb.php");
		}

		if ( is_plugin_active( "email-templates-for-rtb/email-templates-for-rtb.php" ) ) {
			update_option('rtb-pro-was-active', time());
			deactivate_plugins("email-templates-for-rtb/email-templates-for-rtb.php");
		}

		if ( is_plugin_active( "export-bookings-for-rtb/export-bookings-for-rtb.php" ) ) {
			update_option('rtb-pro-was-active', time());
			deactivate_plugins("export-bookings-for-rtb/export-bookings-for-rtb.php");
		}

		if ( is_plugin_active( "mailchimp-for-rtb/mailchimp-for-rtb.php" ) ) {
			update_option('rtb-pro-was-active', time());
			deactivate_plugins("mailchimp-for-rtb/mailchimp-for-rtb.php");
		}
	}

	/**
	 * Start an output buffer
	 * @since 2.0
	 */
	public function output_buffer_start() {
		
		ob_start();
	}

	/**
	 * Close our output buffer, if not already closed
	 * @since 2.0
	 */
	public function output_buffer_end() {

		if ( count( ob_list_handlers() ) ) {

			ob_end_flush();
		}
	}

	public function maybe_display_helper_notice() {
		global $rtb_controller;
	
		if ( empty( $rtb_controller->permissions->check_permission( 'premium' ) ) ) { return; }
	
		if ( is_plugin_active( 'fsp-premium-helper/fsp-premium-helper.php' ) ) { return; }
	
		if ( get_transient( 'fsp-helper-notice-dismissed' ) ) { return; }
	
		?>
	
		<div class='notice notice-error is-dismissible rtb-helper-install-notice'>
				
			<div class='rtb-helper-install-notice-img'>
				<img src='<?php echo RTB_PLUGIN_URL . '/lib/simple-admin-pages/img/options-asset-exclamation.png' ; ?>' />
			</div>
	
			<div class='rtb-helper-install-notice-txt'>
				<?php _e( 'You\'re using the Five-Star Restaurant Reservations premium version, but the premium helper plugin is not active.', 'restaurant-reservations' ); ?>
				<br />
				<?php echo sprintf( __( 'Please re-activate the helper plugin, or <a target=\'_blank\' href=\'%s\'>download and install it</a> if the plugin is no longer installed to ensure continued access to the premium features of the plugin.', 'restaurant-reservations' ), 'https://www.fivestarplugins.com/2021/12/23/requiring-premium-helper-plugin/' ); ?>
			</div>
	
			<div class='rtb-clear'></div>
	
		</div>
	
		<?php 
	}
	
	public function hide_helper_notice() {
	
		// Authenticate request
		if ( ! check_ajax_referer( 'rtb-helper-notice', 'nonce' ) or ! current_user_can( 'manage_options' ) ) {
				
			wp_send_json_error(
				array(
					'error' => 'loggedout',
					'msg' => sprintf( __( 'You have been logged out. Please %slogin again%s.', 'restaurant-reservations' ), '<a href="' . wp_login_url( admin_url( 'admin.php?page=rtb-dashboard' ) ) . '">', '</a>' ),
				)
			);
		}
	
		set_transient( 'fsp-helper-notice-dismissed', true, 3600*24*7 );
	
		die();
	}

}
} // endif;

global $rtb_controller;
$rtb_controller = new rtbInit();

/**
 * Because we refer $rtb_controller in many other modules during their object
 * construction, separating the object construction from its uses. Please refer 
 * to the link after the constructor's official PHP definition.
 * 
 * "Classes which have a constructor method call this method on each newly-
 * created object, so it is suitable for any initialization that the object 
 * may need before it is used."
 * 
 * https://www.php.net/manual/en/language.oop5.decon.php
 */
$rtb_controller->boot();