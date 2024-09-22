<?php
/**
 * Class to create the 'About Us' submenu
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'rtbAboutUs' ) ) {
class rtbAboutUs {

	public function __construct() {

		add_action( 'wp_ajax_rtb_send_feature_suggestion', array( $this, 'send_feature_suggestion' ) );

		add_action( 'admin_menu', array( $this, 'register_menu_screen' ), 11 );
	}

	/**
	 * Adds About Us submenu page
	 * @since 2.6.0
	 */
	public function register_menu_screen() {
		global $rtb_controller;

		add_submenu_page(
			'rtb-bookings', 
			esc_html__( 'About Us', 'restaurant-reservations' ),
			esc_html__( 'About Us', 'restaurant-reservations' ),
			'manage_options', 
			'rtb-about-us',
			array( $this, 'display_admin_screen' )
		);
	}

	/**
	 * Displays the About Us page
	 * @since 2.6.0
	 */
	public function display_admin_screen() { ?>

		<div class='rtb-about-us-logo'>
			<img src='<?php echo plugins_url( "../assets/img/fsplogo.png", __FILE__ ); ?>'>
		</div>

		<div class='rtb-about-us-tabs'>

			<ul id='rtb-about-us-tabs-menu'>

				<li class='rtb-about-us-tab-menu-item rtb-tab-selected' data-tab='who_we_are'>
					<?php _e( 'Who We Are', 'restaurant-reservations' ); ?>
				</li>

				<li class='rtb-about-us-tab-menu-item' data-tab='lite_vs_premium'>
					<?php _e( 'Lite vs. Premium vs. Ultimate', 'restaurant-reservations' ); ?>
				</li>

				<li class='rtb-about-us-tab-menu-item' data-tab='getting_started'>
					<?php _e( 'Getting Started', 'restaurant-reservations' ); ?>
				</li>

				<li class='rtb-about-us-tab-menu-item' data-tab='suggest_feature'>
					<?php _e( 'Suggest a Feature', 'restaurant-reservations' ); ?>
				</li>

			</ul>

			<div class='rtb-about-us-tab' data-tab='who_we_are'>

				<p>
					<strong>Five Star Plugins focuses on creating high-quality, easy-to-use WordPress plugins centered around the restaurant, hospitality and business industries.</strong> With over <strong>50,000 active users worldwide</strong>, our plugins bring a great amount of value to many websites and business owners every day, by offering them solutions that are simple to implement and that provide powerful functionality necessary for their operations. Our <a href='https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_admin_about_us' target='_blank'>WordPress restaurant reservations plugin</a> and <a href='https://www.fivestarplugins.com/plugins/five-star-restaurant-menu/?utm_source=rtb_admin_about_us' target='_blank'>WordPress restaurant menu plugin</a> are both rich in features, responsive and highly customizable. Our <a href='https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_admin_about_us' target='_blank'>business profile WordPress plugin</a> and <a href='https://www.fivestarplugins.com/plugins/five-star-restaurant-reviews/?utm_source=rtb_admin_about_us' target='_blank'>WordPress restaurant reviews plugin</a> allow you to extend the functionality of your site and offer a full WordPress restaurant solution.
				</p>

				<p>
					<strong>On top of this, we pride ourselves on offering great and timely support and customer service.</strong>
				</p>

				<p>
					Our team is made up of developers, graphic designers, marketing associates and support specialists. Our partnership with <a href='https://www.etoilewebdesign.com/?utm_source=rtb_admin_about_us' target='_blank'>Etoile Web Design</a> gives us access to their fantastic support team and allows us to offer unparalleled customer service and technical support via multiple channels.
				</p>

			</div>

			<div class='rtb-about-us-tab rtb-hidden' data-tab='lite_vs_premium'>

				<p><?php _e( 'The premium version includes several advanced features that let you extend the functionality of the plugin and offer a great booking experience for you and your guests. These include the ability to set a dining block length and <strong>limit either the number of reservations or guests</strong>, to add <strong>custom fields</strong> to your booking form and to add a view bookings page to your site, so staff can manage bookings from the front end. It also comes with <strong>advanced layout and styling options</strong>, and much more!', 'restaurant-reservations' ); ?></p>

				<p><?php _e( 'With the ultimate version, you can take the experience to the next level, with features like <strong>table selection</strong> as well as <strong>SMS notifications</strong> for reservation reminders, late arrival notices and post-reservation follow-up messages. It also includes the ability to <strong>require payment deposits</strong> and to sync with the <strong>Five Star Restaurant Manager mobile app</strong>.', 'restaurant-reservations' ); ?></p>

				<p><em><?php _e( 'The following table provides a comparison of the lite, premium and ultimate versions.', 'restaurant-reservations' ); ?></em></p>

				<div class='rtb-about-us-premium-table'>
					<div class='rtb-about-us-premium-table-head'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Feature', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Lite Version', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Premium Version', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Ultimate Version', 'restaurant-reservations' ); ?></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Customized restaurant reservation form', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Gutenberg block, patterns and shortcode to display booking form', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Create unlimted scheduling rules and exceptions', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Customizable admin and customer email notifications', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Easily confirm and reject bookings', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Edit bookings in the easy-to-use admin panel', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Multiple location support', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Limit the number of reservations at one time', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Or limit the number of people at one time', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Automatic booking confirmation options', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Multiple layout options ', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Add custom fields to your booking form', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Add a View Bookings page to let staff manage reservations from the front end', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'MailChimp integration', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Export bookings to PDF or spreadsheet', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Email template designer', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Advanced styling options', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Advanced labelling options', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'SMS or email reservation reminders', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'SMS or email late arrival notifications', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'SMS or email post-reservation follow-up messages', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Table selection and assignment', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Require payment/deposit for booking', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Hold option for deposits', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='rtb-about-us-premium-table-body'>
						<div class='rtb-about-us-premium-table-cell'><?php _e( 'Syncs with Five Star Restaurant Manager mobile app to manage bookings', 'restaurant-reservations' ); ?></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'></div>
						<div class='rtb-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
				</div>

				<div class='rtb-about-us-tab-buttons'>
					<?php printf( __( '<a href="%s" target="_blank" class="rtb-about-us-tab-button rtb-about-us-tab-button-purchase-alternate">Buy Premium Version</a>', 'restaurant-reservations' ), 'https://www.fivestarplugins.com/license-payment/?Selected=RTB&Quantity=1&utm_source=admin_about_us' ); ?>
					<?php printf( __( '<a href="%s" target="_blank" class="rtb-about-us-tab-button rtb-about-us-tab-button-purchase">Buy Ultimate Version</a>', 'restaurant-reservations' ), 'https://www.fivestarplugins.com/license-payment/?Selected=RTU&Quantity=12&utm_source=admin_about_us' ); ?>
				</div>

			</div>

			<div class='rtb-about-us-tab rtb-hidden' data-tab='getting_started'>

				<p><?php _e( 'The walk-though that ran when you first activated the plugin offers a quick way to get started with setting it up. If you would like to run through it again, just click the button below', 'restaurant-reservations' ); ?></p>

				<?php printf( __( '<a href="%s" class="rtb-about-us-tab-button rtb-about-us-tab-button-walkthrough">Re-Run Walk-Through</a>', 'restaurant-reservations' ), admin_url( '?page=rtb-getting-started' ) ); ?>

				<p><?php _e( 'We also have a series of video tutorials that cover the available settings as well as key features of the plugin.', 'restaurant-reservations' ); ?></p>

				<?php printf( __( '<a href="%s" target="_blank" class="rtb-about-us-tab-button rtb-about-us-tab-button-youtube">YouTube Playlist</a>', 'restaurant-reservations' ), 'https://www.youtube.com/playlist?list=PLEndQUuhlvSpWIb_sbRdFsHSkDADYU7JF' ); ?>

				
			</div>

			<div class='rtb-about-us-tab rtb-hidden' data-tab='suggest_feature'>

				<div class='rtb-about-us-feature-suggestion'>

					<p><?php _e( 'You can use the form below to let us know about a feature suggestion you might have.', 'restaurant-reservations' ); ?></p>

					<textarea placeholder="<?php _e( 'Please describe your feature idea...', 'restaurant-reservations' ); ?>"></textarea>
					
					<br>
					
					<input type="email" name="feature_suggestion_email_address" placeholder="<?php _e( 'Email Address', 'restaurant-reservations' ); ?>">
				
				</div>
				
				<div class='rtb-about-us-tab-button rtb-about-us-send-feature-suggestion'>Send Feature Suggestion</div>
				
			</div>

		</div>

	<?php }

	/**
	 * Sends the feature suggestions submitted via the About Us page
	 * @since 2.6.0
	 */
	public function send_feature_suggestion() {
		global $rtb_controller;
		
		if (
			! check_ajax_referer( 'rtb-admin', 'nonce' ) 
			|| 
			! current_user_can( 'manage_options' )
		) {
			rtbHelper::admin_nopriv_ajax();
		}

		$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";  
	    $feedback = sanitize_text_field( $_POST['feature_suggestion'] );
		$feedback .= '<br /><br />Email Address: ';
	  	$feedback .=  sanitize_email( $_POST['email_address'] );
	
	  	wp_mail( 'contact@fivestarplugins.com', 'RTB Feature Suggestion', $feedback, $headers );
	
	  	die();
	} 

}
} // endif;