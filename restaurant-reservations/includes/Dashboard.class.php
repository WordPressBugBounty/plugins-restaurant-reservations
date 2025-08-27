<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbDashboard' ) ) {
/**
 * Class to handle plugin dashboard
 *
 * @since 2.0.0
 */
class rtbDashboard {

	public $message;
	public $status = true;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_dashboard_to_menu' ), 99 );

		// add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_rtb_hide_upgrade_box', array($this, 'hide_upgrade_box') );
		add_action( 'wp_ajax_rtb_display_upgrade_box', array($this, 'display_upgrade_box') );
	}

	public function add_dashboard_to_menu() {
		global $menu, $submenu;

		add_submenu_page( 
			'rtb-bookings', 
			'Dashboard', 
			'Dashboard', 
			'manage_options', 
			'rtb-dashboard', 
			array($this, 'display_dashboard_screen') 
		);

		// Create a new sub-menu in the order that we want
		$new_submenu = array();
		$menu_item_count = 3;

		if ( ! isset( $submenu['rtb-bookings'] ) or  ! is_array($submenu['rtb-bookings']) ) { return; }
		
		foreach ( $submenu['rtb-bookings'] as $key => $sub_item ) {
			if ( $sub_item[0] == 'Dashboard' ) { $new_submenu[0] = $sub_item; }
			elseif ( $sub_item[0] == 'Bookings' ) { $new_submenu[1] = $sub_item; }
			elseif ( $sub_item[0] == 'Settings' ) { $new_submenu[2] = $sub_item; }
			else {
				$new_submenu[$menu_item_count] = $sub_item;
				$menu_item_count++;
			}
		}
		ksort($new_submenu);
		
		$submenu['rtb-bookings'] = $new_submenu;
		
		if ( isset( $dashboard_key ) ) {
			$submenu['rtb-bookings'][0] = $submenu['rtb-bookings'][$dashboard_key];
			unset($submenu['rtb-bookings'][$dashboard_key]);
		}
	}

	// Enqueues the admin script so that our hacky sub-menu opening function can run
	public function enqueue_scripts() {
		global $admin_page_hooks;

		$currentScreen = get_current_screen();
		if ( $currentScreen->id == $admin_page_hooks['rtb-bookings'] . '_page_rtb-dashboard' ) {
			wp_enqueue_style( 'rtb-admin-css', RTB_PLUGIN_URL . '/assets/css/admin.css', array(), RTB_VERSION );
			wp_enqueue_script( 'rtb-admin-js', RTB_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery' ), RTB_VERSION, true );
		}
	}

	public function display_dashboard_screen() { 
		global $rtb_controller;

		$permission = $rtb_controller->permissions->check_permission( 'styling' );
		$ultimate = $rtb_controller->permissions->check_permission( 'payments' );

		?>
		<div id="rtb-dashboard-content-area">

			<?php if ( ! $permission or ! $ultimate or get_option("RTB_Trial_Happening") == "Yes" or get_option("RTU_Trial_Happening") == "Yes" ) {
				$premium_info = '<div class="rtb-dashboard-visit-our-site">';
				$premium_info .= sprintf( __( '<a href="%s" target="_blank">Visit our website</a> to learn how to upgrade to premium.', 'restaurant-reservations' ), 'https://www.fivestarplugins.com/premium-upgrade-instructions/?utm_source=fdm_dashboard&utm_content=visit_our_site_link' );
				$premium_info .= '</div>';

				$premium_info = apply_filters( 'fsp_dashboard_top', $premium_info, 'RTB', 'https://www.fivestarplugins.com/license-payment/?Selected=RTB&Quantity=1' );

				if ( $permission and get_option("RTU_Trial_Happening") != "Yes" ) {
					$ultimate_premium_notice = '<div class="rtb-ultimate-notification">';
					$ultimate_premium_notice .= __( 'Thanks for being a premium user! <strong>If you\'re looking to upgrade to our ultimate version, enter your new product key below.</strong>', 'restaurant-reservations'  );
					$ultimate_premium_notice .= '</div>';
					$ultimate_premium_notice .= '<div class="rtb-ultimate-upgrade-dismiss"></div>';

					$premium_info = str_replace('<div class="fsp-premium-helper-dashboard-new-widget-box-top">', '<div class="fsp-premium-helper-dashboard-new-widget-box-top">' . $ultimate_premium_notice, $premium_info);
				}

				echo $premium_info;
			} ?>

			<ul class="rtb-dashboard-support-widgets">
				<li>
					<div class="rtb-dashboard-support-widgets-title"><?php _e('YouTube Tutorials', 'restaurant-reservations'); ?></div>
					<div class="rtb-dashboard-support-widgets-text-and-link">
						<div class="rtb-dashboard-support-widgets-text"><span class="dashicons dashicons-star-empty"></span>Get help with our video tutorials</div>
						<a class="rtb-dashboard-support-widgets-link" href="https://www.youtube.com/watch?v=b6x0QkgHBKI&list=PLEndQUuhlvSpWIb_sbRdFsHSkDADYU7JF" target="_blank"><?php _e('View', 'restaurant-reservations'); ?></a>
					</div>
				</li>
				<li>
					<div class="rtb-dashboard-support-widgets-title"><?php _e('Documentation', 'restaurant-reservations'); ?></div>
					<div class="rtb-dashboard-support-widgets-text-and-link">
						<div class="rtb-dashboard-support-widgets-text"><span class="dashicons dashicons-star-empty"></span>View our in-depth plugin documentation</div>
						<a class="rtb-dashboard-support-widgets-link" href="http://doc.fivestarplugins.com/plugins/restaurant-reservations/?utm_source=rtb_dashboard&utm_content=icons_documentation" target="_blank"><?php _e('View', 'restaurant-reservations'); ?></a>
					</div>
				</li>
				<li>
					<div class="rtb-dashboard-support-widgets-title"><?php _e('Plugin FAQs', 'restaurant-reservations'); ?></div>
					<div class="rtb-dashboard-support-widgets-text-and-link">
						<div class="rtb-dashboard-support-widgets-text"><span class="dashicons dashicons-star-empty"></span>Access plugin and info and FAQs here.</div>
						<a class="rtb-dashboard-support-widgets-link" href="https://wordpress.org/plugins/restaurant-reservations/#faq" target="_blank"><?php _e('View', 'restaurant-reservations'); ?></a>
					</div>
				</li>
				<li>
					<div class="rtb-dashboard-support-widgets-title"><?php _e('Get Support', 'restaurant-reservations'); ?></div>
					<div class="rtb-dashboard-support-widgets-text-and-link">
						<div class="rtb-dashboard-support-widgets-text"><span class="dashicons dashicons-star-empty"></span>Need more help? Get in touch.</div>
						<a class="rtb-dashboard-support-widgets-link" href="https://www.fivestarplugins.com/support-center/?utm_source=rtb_dashboard&utm_content=icons_get_support" target="_blank"><?php _e('View', 'restaurant-reservations'); ?></a>
					</div>
				</li>
			</ul>
	
			<div class="rtb-dashboard-catalogs">
				<div class="rtb-dashboard-catalogs-title"><?php _e('Bookings Summary', 'restaurant-reservations'); ?></div>
					<table class='rtb-overview-table wp-list-table widefat fixed striped posts'>
						<thead>
							<tr>
								<th><?php _e("Date", 'restaurant-reservations'); ?></th>
								<th><?php _e("Party", 'restaurant-reservations'); ?></th>
								<th><?php _e("Name", 'restaurant-reservations'); ?></th>
								<th><?php _e("Status", 'restaurant-reservations'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );
								$query = new rtbQuery( array() );
								$query->prepare_args();

								$bookings = $query->get_bookings();
								$booking_statuses = $rtb_controller->cpts->booking_statuses;
								
								if (sizeOf($bookings) == 0) {echo "<tr><td colspan='4'>" . __("No bookings to display yet. Create a booking for it to be displayed here.", 'restaurant-reservations') . "</td></tr>";}
								else {
									foreach ($bookings as $booking) {
									?>

										<tr>
											<td><?php echo esc_html( $booking->date ); ?></td>
											<td><?php echo esc_html( $booking->party ); ?></td>
											<td><?php echo esc_html( $booking->name ); ?></td>
											<td><?php echo esc_html( $booking_statuses[$booking->post_status]['label'] ); ?></td>
										</tr>
									<?php }
								}
							?>
						</tbody>
					</table>
			</div>

			<?php if ( ! $permission or get_option("RTB_Trial_Happening") == "Yes" or get_option("RTU_Trial_Happening") == "Yes" ) { ?>
				<div class="rtb-dashboard-get-premium-and-trial<?php echo ( get_option( 'RTB_Trial_Happening' ) == 'Yes' or get_option( 'RTU_Trial_Happening' ) == 'Yes' ) ? ' trial-happening' : ''; ?>">
					<div id="rtb-dashboard-new-footer-one">
						<div class="rtb-dashboard-new-footer-one-inside">
							<div class="rtb-dashboard-new-footer-one-left">
								<div class="rtb-dashboard-new-footer-one-title">What's Included in Our Premium Version?</div>
								<ul class="rtb-dashboard-new-footer-one-benefits">
									<li>Multiple Form Layouts</li>
									<li>Custom Form Fields</li>
									<li>Advanced Email Designer</li>
									<li>MailChimp Integration</li>
									<li>Set Table and Seat Restrictions</li>
									<li>Automatic Booking Confirmation</li>
									<li>Bookings Page for Staff</li>
									<li>Export Bookings</li>
									<li>Advanced Styling Options</li>
								</ul>
							</div>
							<div class="rtb-dashboard-new-footer-one-buttons">
								<a class="rtb-dashboard-new-upgrade-button" href="https://www.fivestarplugins.com/license-payment/?Selected=RTB&Quantity=1&utm_source=fdm_dashboard&utm_content=footer_upgrade" target="_blank">UPGRADE NOW</a>
								<?php if ( ! get_option("RTB_Trial_Happening") and ! get_option( "RTU_Trial_Happening" ) ) { 
									$version_select_modal = '<div class="rtb-trial-version-select-modal rtb-hidden">';
									$version_select_modal .= '<div class="rtb-trial-version-select-modal-title">' . __( 'Select version to trial', 'restaurant-reservations' ) . '</div>';
									$version_select_modal .= '<div class="rtb-trial-version-select-modal-option"><input type="radio" value="premium" name="rtb-trial-version" checked /> ' . __( 'Premium', 'restaurant-reservations' ) . '</div>';
									$version_select_modal .= '<div class="rtb-trial-version-select-modal-option"><input type="radio" value="ultimate" name="rtb-trial-version" /> ' . __( 'Ultimate', 'restaurant-reservations' ) . '</div>';
									$version_select_modal .= '<div class="rtb-trial-version-select-modal-explanation">' . __( 'SMS messaging will not work in the ultimate version trial.', 'restaurant-reservations' ) . '</div>';
									$version_select_modal .= '<div class="rtb-trial-version-select-modal-submit">' . __( 'Select', 'restaurant-reservations' ) . '</div>';
									$version_select_modal .= '</div>';

									$trial_info = apply_filters( 'fsp_trial_button', $trial_info, 'RTB' );

									$trial_info = str_replace( '</form>', '</form>' . $version_select_modal, $trial_info );

									echo $trial_info;
								} ?>
							</div>
						</div>
					</div>
					<?php if ( get_option( "RTB_Trial_Happening" ) == "Yes" ) { ?>
						<div class="rtb-dashboard-trial-container">
							<?php do_action( 'fsp_trial_happening', 'RTB' ); ?>
						</div>
					<?php } ?>
					<?php if ( get_option( "RTU_Trial_Happening" ) == "Yes" ) { ?>
						<div class="rtb-dashboard-trial-container">
							<?php do_action( 'fsp_trial_happening', 'RTU' ); ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>	

			<div class="rtb-dashboard-testimonials-and-other-plugins">

				<div class="rtb-dashboard-testimonials-container">
					<div class="rtb-dashboard-testimonials-container-title"><?php _e( 'What People Are Saying', 'restaurant-reservations' ); ?></div>
					<ul class="rtb-dashboard-testimonials">
						<?php $randomTestimonial = rand(0,2);
						if($randomTestimonial == 0){ ?>
							<li id="rtb-dashboard-testimonial-one">
								<img src="<?php echo plugins_url( '../assets/img/dash-asset-stars.png', __FILE__ ); ?>">
								<div class="rtb-dashboard-testimonial-title">"Exactly as the name says – a Five Star plugin!"</div>
								<div class="rtb-dashboard-testimonial-author">- @manuelayar</div>
								<div class="rtb-dashboard-testimonial-text">Works flawlessly across devices. Plus, the support is outstanding, quick, helpful and reliable! Highly recommended! <a href="https://wordpress.org/support/topic/exactly-as-the-name-says-a-five-star-plugin/" target="_blank">read more</a></div>
							</li>
						<?php }
						if($randomTestimonial == 1){ ?>
							<li id="rtb-dashboard-testimonial-two">
								<img src="<?php echo plugins_url( '../assets/img/dash-asset-stars.png', __FILE__ ); ?>">
								<div class="rtb-dashboard-testimonial-title">"Excellent plugin, amazing support!"</div>
								<div class="rtb-dashboard-testimonial-author">- @terkwong</div>
								<div class="rtb-dashboard-testimonial-text">We migrated from a Google plugin reservation system to Fivestarrestaurant and are well pleased with how this is working for us! <a href="https://wordpress.org/support/topic/excellent-plugin-amazing-support-32/" target="_blank">read more</a></div>
							</li>
						<?php }
						if($randomTestimonial == 2){ ?>
							<li id="rtb-dashboard-testimonial-three">
								<img src="<?php echo plugins_url( '../assets/img/dash-asset-stars.png', __FILE__ ); ?>">
								<div class="rtb-dashboard-testimonial-title">"Wish I could give this 10 stars!"</div>
								<div class="rtb-dashboard-testimonial-author">- @timboc</div>
								<div class="rtb-dashboard-testimonial-text">This is above and beyond and I cannot recommend this plugin highly enough... <a href="https://wordpress.org/support/topic/wish-i-could-give-this-10-stars/" target="_blank">read more</a></div>
							</li>
						<?php } ?>
					</ul>
				</div>

				<div class="rtb-dashboard-other-plugins-container">
					<div class="rtb-dashboard-other-plugins-container-title"><?php _e('Other plugins by Etoile', 'restaurant-reservations'); ?></div>
					<ul class="rtb-dashboard-other-plugins">
						<li>
							<a href="https://wordpress.org/plugins/restaurant-reservations/" target="_blank"><img src="<?php echo plugins_url( '../assets/img/fdm-icon.png', __FILE__ ); ?>"></a>
							<div class="rtb-dashboard-other-plugins-text">
								<div class="rtb-dashboard-other-plugins-title">Restaurant Menu and Food Ordering</div>
								<div class="rtb-dashboard-other-plugins-blurb">Quickly set up and display a responsive menu and allow food ordering directly from your site!</div>
							</div>
						</li>
						<li>
							<a href="https://wordpress.org/plugins/business-profile/" target="_blank"><img src="<?php echo plugins_url( '../assets/img/bpfwp-icon.png', __FILE__ ); ?>"></a>
							<div class="rtb-dashboard-other-plugins-text">
								<div class="rtb-dashboard-other-plugins-title">Business Profile and Schema</div>
								<div class="rtb-dashboard-other-plugins-blurb">Easily add schema strutured data to any page on your site, and also create a contact card</div>
							</div>
						</li>
					</ul>
				</div>

			</div>

			<?php if ( ! $permission or get_option("RTB_Trial_Happening") == "Yes" or get_option("RTU_Trial_Happening") == "Yes" ) { ?>
				<div class="rtb-dashboard-guarantee">
					<img src="<?php echo plugins_url( '../assets/img/dash-asset-badge.png', __FILE__ ); ?>" alt="14-Day 100% Money-Back Guarantee">
					<div class="rtb-dashboard-guarantee-title-and-text">
						<div class="rtb-dashboard-guarantee-title">14-Day 100% Money-Back Guarantee</div>
						<div class="rtb-dashboard-guarantee-text">If you're not 100% satisfied with the premium version of our plugin - no problem. You have 14 days to receive a FULL REFUND. We're certain you won't need it, though.</div>
					</div>
				</div>
			<?php } ?>

		</div> <!-- rtb-dashboard-content-area -->
		
		<div id="rtb-dashboard-new-footer-two">
			<div class="rtb-dashboard-new-footer-two-inside">
				<img src="<?php echo plugins_url( '../assets/img/fivestartextlogowithstar.png', __FILE__ ); ?>" class="rtb-dashboard-new-footer-two-icon">
				<div class="rtb-dashboard-new-footer-two-blurb">
					At Five Star Plugins, we build powerful, easy-to-use WordPress plugins with a focus on the restaurant, hospitality and business industries. With a modern, responsive look and a highly-customizable feature set, Five Star Plugins can be used as out-of-the-box solutions and can also be adapted to your specific requirements.
				</div>
				<ul class="rtb-dashboard-new-footer-two-menu">
					<li>SOCIAL</li>
					<li><a href="https://www.facebook.com/fivestarplugins/" target="_blank">Facebook</a></li>
					<li><a href="https://x.com/wpfivestar" target="_blank">Twitter</a></li>
					<li><a href="https://www.fivestarplugins.com/category/blog/?utm_source=rtb_dashboard&utm_content=footer_blog" target="_blank">Blog</a></li>
				</ul>
				<ul class="rtb-dashboard-new-footer-two-menu">
					<li>SUPPORT</li>
					<li><a href="https://www.youtube.com/watch?v=b6x0QkgHBKI&list=PLEndQUuhlvSpWIb_sbRdFsHSkDADYU7JF" target="_blank">YouTube Tutorials</a></li>
					<li><a href="http://doc.fivestarplugins.com/plugins/restaurant-reservations/?utm_source=rtb_dashboard&utm_content=footer_documentation" target="_blank">Documentation</a></li>
					<li><a href="https://www.fivestarplugins.com/support-center/?utm_source=rtb_dashboard&utm_content=footer_get_support" target="_blank">Get Support</a></li>
					<li><a href="https://wordpress.org/plugins/restaurant-reservations/#faq" target="_blank">FAQs</a></li>
					<li><a id="rtb-dashboard-show-upgrade-box-link" href="#rtb-dashboard-upgrade-box">Ultimate Upgrade</a></li>
				</ul>
			</div>
		</div> <!-- rtb-dashboard-new-footer-two -->
		
	<?php }

	public function get_term_from_array($terms, $term_id) {
		foreach ($terms as $term) {if ($term->term_id == $term_id) {return $term;}}

		return array();
	}

	public function display_notice() {
		if ( $this->status ) {
			echo "<div class='updated'><p>" . esc_textarea( $this->message ) . "</p></div>";
		}
		else {
			echo "<div class='error'><p>" . esc_textarea( $this->message ) . "</p></div>";
		}
	}

	public function hide_upgrade_box() {
		if ( !check_ajax_referer( 'rtb-admin', 'nonce' ) ) {
			rtbHelper::admin_nopriv_ajax();
		}

		update_option( 'rtb-hide-upgrade-box', true );
	}

	public function display_upgrade_box() {
		if ( !check_ajax_referer( 'rtb-admin', 'nonce' ) ) {
			rtbHelper::admin_nopriv_ajax();
		}

		update_option( 'rtb-hide-upgrade-box', false );
	}
}
} // endif
