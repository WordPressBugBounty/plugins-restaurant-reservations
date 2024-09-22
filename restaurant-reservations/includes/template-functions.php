<?php
/**
 * Template functions for rendering booking forms, etc.
 */

/**
 * Create a shortcode to render the booking form
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_booking_form_shortcode' ) ) {
function rtb_booking_form_shortcode( $args = array() ) {

	$args = shortcode_atts(
		array(
			'location' => 0,
		),
		$args,
		'booking-form'
	);

	return rtb_print_booking_form( $args );
}
add_shortcode( 'booking-form', 'rtb_booking_form_shortcode' );
} // endif;

/**
 * Print the booking form's HTML code, including error handling and confirmation
 * notices.
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_print_booking_form' ) ) {
function rtb_print_booking_form( $args = array() )
{
	global $rtb_controller;

	do_action( 'rtb_booking_form_init', $args );

	// Only allow the form to be displayed once on a page
	if ( $rtb_controller->form_rendered === true ) {
		return;
	} else {
		$rtb_controller->form_rendered = true;
	}

	// Run cancellation request if parameters are included
	if ( isset($_GET['action']) and $_GET['action'] == 'cancel' ) {
		$rtb_controller->ajax->cancel_reservation( false );
	}

	// Sanitize incoming arguments
	if ( isset( $args['location'] ) ) {
		$args['location'] = $rtb_controller->locations->get_location_term_id( $args['location'] );
	} else {
		$args['location'] = 0;
	}

	// Enqueue assets for the form
	rtb_enqueue_assets();

	// Custom styling
	$styling = rtb_add_custom_styling();

	// Allow themes and plugins to override the booking form's HTML output.
	$output = apply_filters( 'rtb_booking_form_html_pre', '' );
	if ( !empty( $output ) ) {
		return $output;
	}

	// Process a booking request
	if ( !empty( $_POST['action'] ) and $_POST['action'] == 'booking_request' ) {

		if ( get_class( $rtb_controller->request ) === 'stdClass' ) {
			
			$rtb_controller->request = new rtbBooking();
		}

		$rtb_controller->request->insert_booking();
	}

	// Define the form's action parameter
	$booking_page = $rtb_controller->settings->get_setting( 'booking-page' );
	if ( !empty( $booking_page ) ) {
		$booking_page = get_permalink( $booking_page );
	}

	// Retrieve the form fields
	$fields = $rtb_controller->settings->get_booking_form_fields( $rtb_controller->request, $args );

	ob_start();

	?>

	<script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>

    <?php echo $styling; ?>

    <?php echo apply_filters( 'rtb_booking_form_before_html', '' ); ?>

<div class="rtb-booking-form">
	<?php if ( ( $rtb_controller->request->request_inserted === true and ( ! $rtb_controller->settings->get_setting( 'require-deposit' ) or empty( $rtb_controller->request->calculate_deposit() ) ) ) or 
			   ( isset($_GET['payment']) and $_GET['payment'] == 'paid' ) ) : ?>

		<?php $post_status = isset( $_GET['booking_id'] ) ? get_post_status( intval( $_GET['booking_id'] ) ) : $rtb_controller->request->post_status;	?>

		<?php
		
		$success_redirect_location = '';
		$success_message = '';

		if ( 'confirmed' == $post_status ) {
			if( ! empty( $rtb_controller->settings->get_setting('confirmed-redirect-page') ) ) {
				$success_redirect_location = $rtb_controller->settings->get_setting( 'confirmed-redirect-page' );
			}
			else {
				$success_message = $rtb_controller->settings->get_setting( 'confirmed-message' );
			}
		}
		else {
			if( ! empty( $rtb_controller->settings->get_setting('pending-redirect-page') ) ) {
				$success_redirect_location = $rtb_controller->settings->get_setting( 'pending-redirect-page' );
			}
			else {
				$success_message = $rtb_controller->settings->get_setting( 'success-message' );
			}
		}

		if( ! empty($success_redirect_location) ) {
			$success_redirect_location = apply_filters(
				'rtb_booking_submit_success_redirect',
				$success_redirect_location,
				$post_status,
				$rtb_controller->request
			);
			
			header( 'Location:' . $success_redirect_location );
		}
		else {
			?>
			<div class="rtb-message">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php
		}

	elseif ( $rtb_controller->request->request_inserted === true or isset($_GET['payment']) ) :
		if ( isset($_GET['payment']) && 'rtb-delayed-deposit' != $_GET['payment'] ) { ?>
			<div class="rtb-message">
				<p><?php printf( __( 'Your reservation deposit payment has failed with the following message "%s" Please contact the site administrator for assistance.', 'restaurant-reservations' ), isset( $_GET['error_code'] ) ? esc_html( urldecode( $_GET['error_code'] ) ) : ' unknown error.' ); ?></p>
			</div>
		<?php }

		$booking_id = isset( $_GET['booking_id'] ) 
		  ? intval( $_GET['booking_id'] ) 
		  : $rtb_controller->request->ID;

		$booking = new rtbBooking();
		$booking->load_post( $booking_id );

		?> <div class="booking-payment-wrapper"> <?php
		$rtb_controller
			->payment_manager
			->set_booking( $booking )
			->print_payment_summary()
			->print_payment_form();
		?> </div> <!-- booking-payment-wrapper --> <?php

	elseif ( isset($_GET['bookingCancelled']) and $_GET['bookingCancelled'] == 'success') : ?>
	<div class="rtb-message">
		<p><?php _e( 'Your reservation has been successfully cancelled.', 'restaurant-reservations' ) ?></p>
	</div>
	<?php else : ?>

	<?php if ( $rtb_controller->settings->get_setting( 'allow-cancellations' ) ) : ?>
		<div class="rtb-modification-toggle">
			<?php echo esc_html( $rtb_controller->settings->get_setting( 'label-modify-reservation'  ) ); ?>
		</div>
		<div class="rtb-clear"></div>
		<form class="rtb-modification-form rtb-hidden">
			<div>
				<?php echo esc_html( $rtb_controller->settings->get_setting( 'label-modify-using-form'  ) ); ?>
			</div>
			<label for="rtb-modification-email">
				<?php echo esc_html( $rtb_controller->settings->get_setting( 'label-modify-form-email'  ) ); ?>
			</label>
			<input type="email" name="rtb_modification_email" />
			<div class="rtb-clear"></div>
			<div class="rtb-find-reservation-button">
				<?php echo esc_html( $rtb_controller->settings->get_setting( 'label-modify-find-reservations'  ) ); ?>
			</div>
			<div class="rtb-clear"></div>
			<div class="rtb-bookings-results"></div>
		</form>
	<?php endif; ?>

	<?php 
	$tables_graphic = $rtb_controller->settings->get_setting( 'enable-tables-graphic' );
	$tables_graphic_location = $rtb_controller->settings->get_setting( 'tables-graphic-location' );
	?>

	<?php if ( $tables_graphic ) { ?><div class="rtb-booking-form-with-tables-graphic <?php echo esc_attr( $tables_graphic_location ); ?>"><?php } ?>

	<form method="POST" action="<?php echo esc_attr( $booking_page ); ?>" class="rtb-booking-form-form">
		<input type="hidden" name="action" value="booking_request">

		<?php if ( !empty( $args['location'] ) ) : ?>
			<input type="hidden" name="rtb-location" value="<?php echo absint( $args['location'] ); ?>">
		<?php endif; ?>

		<?php if ( ! empty( $_GET['selected_date'] ) ) { ?>
			<input type='hidden' class='rtb-selected-date' value='<?php echo esc_attr( $_GET['selected_date'] ); ?>' />
		<?php } ?>

		<?php do_action( 'rtb_booking_form_before_fields' ); ?>

		<?php foreach( $fields as $fieldset => $contents ) :
			$fieldset_classes = isset( $contents['callback_args']['classes'] ) ? $contents['callback_args']['classes'] : array();
			$legend_classes = isset( $contents['callback_args']['legend_classes'] ) ? $contents['callback_args']['legend_classes'] : array();
		?>
		<fieldset <?php echo rtb_print_element_class( $fieldset, $fieldset_classes ); ?>>

			<?php if ( !empty( $contents['legend'] ) ) : ?>
			<legend <?php echo rtb_print_element_class( '', $legend_classes ); ?>>
				<?php echo $contents['legend']; ?>
			</legend>
			<?php endif; ?>

			<?php
				foreach( $contents['fields'] as $slug => $field ) {

					if ( empty( $field['callback'] ) ) { continue; }

					$callback_args = empty( $field['callback_args'] ) ? array() : $field['callback_args'];

					if ( !empty( $field['required'] ) ) {
						$callback_args = array_merge( $callback_args, array( 'required' => $field['required'] ) );
					}

					if ( !empty( $field['empty_option'] ) ) {
						$callback_args = array_merge( $callback_args, array( 'empty_option' => $field['empty_option'] ) );
					}

					call_user_func( $field['callback'], $slug, $field['title'], $field['request_input'], $callback_args );
				}
			?>
		</fieldset>
		<?php endforeach; ?>

		<?php do_action( 'rtb_booking_form_after_fields' ); ?>

		<fieldset class="rtb-form-footer">
			<div id='rtb_recaptcha'></div>
			<?php echo rtb_print_form_error( 'recaptcha' ); ?>

			<?php
				$button_text = $rtb_controller->settings->get_setting( 'require-deposit' ) 
					? ( $rtb_controller->settings->get_setting( 'rtb-deposit-applicable' ) == 'always'
						? esc_html( $rtb_controller->settings->get_setting( 'label-proceed-to-deposit'  ) )
						: esc_html( $rtb_controller->settings->get_setting( 'label-request-or-deposit'  ) ) )
					: esc_html( $rtb_controller->settings->get_setting( 'label-request-booking'  ) );
				
				$button = sprintf(
					'<button type="submit">%s</button>',
					apply_filters( 'rtb_booking_form_submit_label', $button_text )
				);

				echo sprintf(
					'<div class="rtb-form-submit">%s</div>',
					apply_filters( 'rtb_booking_form_submit_button', $button )
				);
			?>
		</fieldset>


	</form>

	<?php if ( $tables_graphic ) { ?>
		<div class="rtb-tables-graphic-container">
			<p><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-table-layout'  ) ); ?></p>
			<img src="<?php echo esc_url( $rtb_controller->settings->get_setting( 'tables-graphic' ) ); ?>">
		</div>
	<?php } ?>

	<?php if ( $tables_graphic ) { ?></div><?php } // rtb-booking-form-with-tables-graphic ?>

	<?php endif; ?>
</div>

	<?php

	$output = ob_get_clean();

	$output = apply_filters( 'rtb_booking_form_html_post', $output );

	return $output;
}
} // endif;

/**
 * Create a shortcode to view and (optionally) sign in bookings
 * @since 2.0.0
 */
if ( !function_exists( 'rtb_display_bookings_form_shortcode' ) ) {
function rtb_display_bookings_form_shortcode( $args = array() ) {

	$args = shortcode_atts(
		array(
			'location' => isset( $_GET['booking_location'] ) ? $_GET['booking_location'] : 0,
			'date' => isset( $_GET['date'] ) ? $_GET['date'] : ( new DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d' )
		),
		$args,
		'view-booking-form'
	);

	return rtb_print_view_bookings_form( $args );
}
add_shortcode( 'view-bookings-form', 'rtb_display_bookings_form_shortcode' );
} // endif;

/**
 * Print the display bookings form's HTML code, including error handling and confirmation
 * notices.
 * @since 2.0.0
 */
if ( !function_exists( 'rtb_print_view_bookings_form' ) ) {
function rtb_print_view_bookings_form( $args = array() ) {

	global $rtb_controller;

	// Only allow the form to be displayed once on a page
	if ( $rtb_controller->display_bookings_form_rendered === true ) {
		return;
	} else {
		$rtb_controller->display_bookings_form_rendered = true;
	}

	// Sanitize incoming arguments
	$args['location'] = isset( $args['location'] ) ? $rtb_controller->locations->get_location_term_id( $args['location'] ) : 0;

	// Enqueue assets for the form
	rtb_enqueue_assets();

	// Allow themes and plugins to override the booking form's HTML output.
	$output = apply_filters( 'rtb_display_bookings_form_html_pre', '' );
	if ( !empty( $output ) ) {
		return $output;
	}

	$params = array(
		'post_type' => 'rtb-booking',
		'posts_per_page' => -1,
		'date_query' => array(
			'year' => substr( $args['date'], 0, 4 ),
			'month' => substr( $args['date'], 5, 2 ),
			'day' => substr( $args['date'], 8, 2 )
		),
		'post_status' => array_keys( $rtb_controller->cpts->booking_statuses ),
		'orderby' => 'date',
		'order' => 'ASC'
	);

	$query = new rtbQuery( $params );

	$query->parse_request_args();
	$query->prepare_args();

	$display_table = $rtb_controller->permissions->check_permission( 'premium_table_restrictions' ) && $rtb_controller->settings->get_setting( 'enable-tables' );

	$view_bookings_columns = $rtb_controller->settings->get_setting( 'rtb-view-bookings-columns' );

	$custom_fields = rtb_get_custom_fields();

	ob_start();

	?>

<script type="text/javascript">
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>

<?php echo apply_filters( 'rtb_display_bookings_form_before_html', '' ); ?>

<div class="rtb-view-bookings-form">

	<div class='rtb-view-bookings-form-date-selector-div'>

		<select class='rtb-view-bookings-form-date-selector'>
			<?php for ( $i=0; $i<7; $i++ ) { ?>
				<?php $timestamp = time() + $i * 3600*24; ?>
				<option value='<?php echo date_i18n('Y-m-d', $timestamp); ?>' <?php echo ( date_i18n('Y-m-d', $timestamp) == $args['date'] ? 'selected="selected"' : '' ); ?> ><?php echo date_i18n( get_option( 'date_format' ), $timestamp); ?></option>
			<?php } ?>
		</select>

		<?php do_action( 'rtb_view_bookings_form_filters', $args ); ?>
	</div>

	<div class='rtb-view-bookings-form-confirmation-div rtb-hidden'>
		<div class='rtb-view-bookings-form-confirmation-div-inside'>
			<div id="rtb-view-bookings-form-close"><span>x</span></div>
			<div class='rtb-view-bookings-form-confirmation-div-title'>
				<?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-set-status-arrived' ) ); ?>
			</div>
			<div class='rtb-view-bookings-form-confirmation-accept'><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-arrived-yes' ) ); ?></div>
			<div class='rtb-view-bookings-form-confirmation-decline'><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-arrived-no' ) ); ?></div>
		</div>
	</div>
	<div class='rtb-view-bookings-form-confirmation-background-div rtb-hidden'></div>

 	<table class='rtb-view-bookings-table'>
		<thead>
			<tr>
				<?php if ( $rtb_controller->settings->get_setting( 'view-bookings-arrivals' ) ) {?> <th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-arrived' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'time', $view_bookings_columns ) ) {?><th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-time' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'party', $view_bookings_columns ) ) {?><th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-party' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'name', $view_bookings_columns ) ) {?><th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-name' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'email', $view_bookings_columns ) ) {?><th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-email' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'phone', $view_bookings_columns ) ) {?><th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-phone' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'table', $view_bookings_columns ) and $display_table ) {?> <th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-table' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'status', $view_bookings_columns ) ) {?><th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-status' ) ); ?></th><?php } ?>
				<?php if ( in_array( 'details', $view_bookings_columns ) ) {?><th><?php echo esc_html( $rtb_controller->settings->get_setting( 'label-view-details' ) ); ?></th><?php } ?>

				<?php foreach ( $custom_fields as $custom_field ) { ?>

					<?php if ( in_array( $custom_field->slug, $view_bookings_columns ) ) { ?><th><?php echo esc_html( $custom_field->title ); ?></th><?php } ?>

				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $query->get_bookings() as $booking_object ) {

				$details = array();

				if ( trim( $booking_object->message ) ) {

					$details[] = array(
						'label' => __( 'Message', 'restaurant-reservations' ),
						'value' => esc_html( $booking_object->message ),
					);
				}

				$details = apply_filters( 'rtb_bookings_table_column_details', $details, $booking_object );

				?>

				<tr>
					<?php if ( $rtb_controller->settings->get_setting( 'view-bookings-arrivals' ) ) {?>
						<?php if ( $booking_object->post_status != 'arrived' ) : ?><td><input type='checkbox' class='rtb-edit-view-booking' data-bookingid='<?php echo $booking_object->ID; ?>' /></td>
						<?php else : ?><td><input type='checkbox' class='rtb-edit-view-booking' checked disabled /></td>
						<?php endif; ?>
					<?php } ?>
					<?php if ( in_array( 'time', $view_bookings_columns ) ) {?><td><?php echo ( new DateTime( $booking_object->date, wp_timezone() ) )->format( get_option( 'time_format' ) ); ?></td><?php } ?>
					<?php if ( in_array( 'party', $view_bookings_columns ) ) {?><td><?php echo esc_html( $booking_object->party ); ?></td><?php } ?>
					<?php if ( in_array( 'name', $view_bookings_columns ) ) {?><td><?php echo esc_html( $booking_object->name ); ?></td><?php } ?>
					<?php if ( in_array( 'email', $view_bookings_columns ) ) {?><td><?php echo esc_html( $booking_object->email ); ?></td><?php } ?>
					<?php if ( in_array( 'phone', $view_bookings_columns ) ) {?><td><?php echo esc_html( $booking_object->phone ); ?></td><?php } ?>
					<?php if ( in_array( 'table', $view_bookings_columns ) and $display_table ) { ?><td><?php echo esc_html( implode(', ', $booking_object->table ) ); ?></td><?php } ?>
					<?php if ( in_array( 'status', $view_bookings_columns ) ) {?><td><?php echo esc_html( $rtb_controller->cpts->booking_statuses[$booking_object->post_status]['label'] ); ?></td><?php } ?>
					<?php if ( in_array( 'details', $view_bookings_columns ) ) { ?>

						<td>
							<ul class='rtb-view-booking-details'>

								<?php foreach ( $details as $detail ) { ?>

									<li>
										<label class='rtb-view-booking-details-label'>
											<?php echo esc_html( $detail['label'] ); ?>
										</label>
										<span class='rtb-view-booking-details-value'>
											<?php echo esc_html( $detail['value'] ); ?>
										</span>
									</li>
								<?php } ?>

							</ul>
						</td>

					<?php } ?>

					<?php foreach ( $custom_fields as $custom_field ) { ?>

						<?php if ( ! in_array( $custom_field->slug, $view_bookings_columns ) ) { continue; } ?>
						
						<?php if ( ! isset( $booking_object->custom_fields[ $custom_field->slug ] ) ) { echo '<td></td>'; continue; } ?>

						<td>
							<?php echo wp_kses_post( $rtb_controller->fields->get_display_value( $booking_object->custom_fields[ $custom_field->slug ], $custom_field ) ); ?>
						</td>

					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>

</div>

	<?php

	wp_reset_postdata();

	$output = ob_get_clean();

	$output = apply_filters( 'rtb_display_bookings_form_html_post', $output );

	return $output;
}
} // endif;

/**
 * Enqueue the front-end CSS and Javascript for the booking form
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_enqueue_assets' ) ) {
function rtb_enqueue_assets() {

	global $rtb_controller;

	wp_enqueue_style( 'rtb-booking-form' );

	wp_enqueue_style( 'pickadate-default' );
	wp_enqueue_style( 'pickadate-date' );
	wp_enqueue_style( 'pickadate-time' );
	wp_enqueue_script( 'pickadate' );
	wp_enqueue_script( 'pickadate-date' );
	wp_enqueue_script( 'pickadate-time' );
	wp_enqueue_script( 'pickadate-legacy' );
	wp_enqueue_script( 'pickadate-i8n' ); // only registered if needed
	wp_enqueue_style( 'pickadate-rtl' ); // only registered if needed

	wp_enqueue_script( 'rtb-booking-form' );

	if ( $rtb_controller->settings->get_setting( 'enable-captcha' ) ) {
		$site_key = $rtb_controller->settings->get_setting( 'captcha-site-key' );

		wp_enqueue_script( 'rtb-google-recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() . '&render=explicit&onload=rtbLoadRecaptcha' );
		wp_enqueue_script( 'rtb-process-recaptcha', RTB_PLUGIN_URL . '/assets/js/rtb-recaptcha.js', array( 'rtb-google-recaptcha' ) );

		wp_localize_script( 'rtb-process-recaptcha', 'rtb_recaptcha', array( 'site_key' => $site_key ) );
	}

	if ( function_exists('get_current_screen') ) {
		$screen = get_current_screen();
		$screenID = is_object( $screen ) ? $screen->id : '';
	}
	else {
		$screenID = '';
	}
	
	if( $rtb_controller->settings->get_setting( 'rtb-styling-layout' ) == 'contemporary' && $screenID != 'toplevel_page_rtb-bookings' ){
		wp_enqueue_style( 'rtb-contemporary-css', RTB_PLUGIN_URL . '/assets/css/contemporary.css' );
	}
	if( $rtb_controller->settings->get_setting( 'rtb-styling-layout' ) == 'minimal' && $screenID != 'toplevel_page_rtb-bookings' ){
		wp_enqueue_style( 'rtb-minimal', RTB_PLUGIN_URL . '/assets/css/minimal.css' );
	}
	if( $rtb_controller->settings->get_setting( 'rtb-styling-layout' ) == 'columns' && $screenID != 'toplevel_page_rtb-bookings' ){
		wp_enqueue_style( 'rtb-columns-css', RTB_PLUGIN_URL . '/assets/css/columns.css' );
		wp_enqueue_script( 'rtb-columns-js', RTB_PLUGIN_URL . '/assets/js/columns.js', array( 'jquery' ), '', true );
	}
	if( $rtb_controller->settings->get_setting( 'rtb-styling-layout' ) == 'columns_alternate' && $screenID != 'toplevel_page_rtb-bookings' ){
		wp_enqueue_style( 'rtb-columns-alternate', RTB_PLUGIN_URL . '/assets/css/columns-alternate.css' );
		wp_enqueue_script( 'rtb-columns', RTB_PLUGIN_URL . '/assets/js/columns.js', array( 'jquery' ), '', true );
	}

	// Pass date and time format settings to the pickadate controls
	wp_localize_script(
		'rtb-booking-form',
		'rtb_pickadate',
		apply_filters(
			'rtb_pickadate_args',
			array(
				'date_format' 					=> rtb_esc_js( $rtb_controller->settings->get_setting( 'date-format' ) ),
				'time_format'  					=> rtb_esc_js( $rtb_controller->settings->get_setting( 'time-format' ) ),
				'disable_dates'					=> rtb_get_datepicker_rules(),
				'schedule_open' 				=> $rtb_controller->settings->get_setting( 'schedule-open' ),
				'schedule_closed' 				=> $rtb_controller->settings->get_setting( 'schedule-closed' ),
				'multiple_locations_enabled'	=> $rtb_controller->locations->do_locations_exist(),
				'early_bookings' 				=> is_admin() && current_user_can( 'manage_bookings' ) ? '' : $rtb_controller->settings->get_setting( 'early-bookings' ),
				'late_bookings' 				=> is_admin() && current_user_can( 'manage_bookings' ) ? '' : $rtb_controller->settings->get_setting( 'late-bookings' ),
				'enable_max_reservations' 		=> is_admin() && current_user_can( 'manage_bookings' ) ? false : $rtb_controller->settings->get_setting( 'rtb-enable-max-tables' ),
				'max_people' 					=> is_admin() && current_user_can( 'manage_bookings' ) ? 100 : $rtb_controller->settings->get_setting( 'rtb-max-people-count' ),
				'enable_tables' 				=> $rtb_controller->settings->get_setting( 'enable-tables' ),
				'date_onload' 					=> $rtb_controller->settings->get_setting( 'date-onload' ),
				'time_interval' 				=> $rtb_controller->settings->get_setting( 'time-interval' ),
				'first_day' 					=> $rtb_controller->settings->get_setting( 'week-start' ),
				'allow_past' 					=> is_admin() && current_user_can( 'manage_bookings' ),
				'date_today_label'				=> rtb_esc_js( $rtb_controller->settings->get_setting( 'label-date-today' ) ),
				'date_clear_label'				=> rtb_esc_js( $rtb_controller->settings->get_setting( 'label-date-clear' ) ),
				'date_close_label'				=> rtb_esc_js( $rtb_controller->settings->get_setting( 'label-date-close' ) ),
				'time_clear_label'				=> rtb_esc_js( $rtb_controller->settings->get_setting( 'label-time-clear' ) ),
			)
		)
	);

}
} // endif;

/**
 * Get rules for datepicker date ranges
 * See: http://amsul.ca/pickadate.js/date/#disable-dates
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_get_datepicker_rules' ) ) {
function rtb_get_datepicker_rules( $location_slug = '' ) {

	global $rtb_controller;

	// First day of the week
	$first_day = (int) $rtb_controller->settings->get_setting( 'week-start' );

	$disable_rules = array();

	$disabled_weekdays = array(
		'sunday'	=> ( 1 - $first_day ) === 0 ? 7 : 1,
		'monday'	=> 2 - $first_day,
		'tuesday'	=> 3 - $first_day,
		'wednesday'	=> 4 - $first_day,
		'thursday'	=> 5 - $first_day,
		'friday'	=> 6 - $first_day,
		'saturday'	=> 7 - $first_day,
	);

	// Determine which weekdays should be disabled
	$enabled_dates = array();
	$schedule_open = $rtb_controller->settings->get_setting( 'schedule-open', $location_slug );
	if ( is_array( $schedule_open ) ) {
		foreach ( $schedule_open as $rule ) {
			if ( !empty( $rule['weekdays'] ) ) {
				foreach ( $rule['weekdays'] as $weekday => $value ) {
					unset( $disabled_weekdays[ $weekday ] );
				}
			}
		}

		if ( count( $disabled_weekdays ) < 7 ) {
			foreach ( $disabled_weekdays as $weekday ) {
				$disable_rules[] = $weekday;
			}
		}
	}

	// Handle exception dates
	$schedule_closed = $rtb_controller->settings->get_setting( 'schedule-closed', $location_slug );
	if ( is_array( $schedule_closed ) ) {
		foreach ( $schedule_closed as $rule ) {

			$formatted_rule = null;

			// Exception dates
			if ( !empty( $rule['date'] ) ) {
				$date = new DateTime( $rule['date'], wp_timezone() );
				$formatted_rule = array( $date->format( 'Y' ), ( $date->format( 'n' ) - 1 ), $date->format( 'j' ) );

				$formatted_rule = empty( $rule['time'] )
					// Disable exception date that are closed all day
					? $formatted_rule
					// Enable exception dates that have opening/closing times
					: array_merge( $formatted_rule, ['inverted'] );
			}
			// Exception date ranges
			elseif ( !empty( $rule['date_range'] ) ) {
				$start = !empty( $rule['date_range']['start'] ) 
					? new DateTime( $rule['date_range']['start'] , wp_timezone() )
					: new DateTime( 'now', wp_timezone() );

				$end = !empty( $rule['date_range']['end'] )
					? new DateTime( $rule['date_range']['end'], wp_timezone() )
					// Disable future dates for 10 years when no end date is given for exception
					: (new DateTime( 'now', wp_timezone() ) )->add( new DateInterval( 'P10Y' ) );

				$formatted_rule = array(
					'from' => array(
						$start->format( 'Y' ), ( $start->format( 'n' ) - 1 ), $start->format( 'j' )
					),
					'to' => array(
						$end->format( 'Y' ), ( $end->format( 'n' ) - 1 ), $end->format( 'j' )
					)
				);

				$formatted_rule = empty( $rule['time'] )
					// Disable exception date that are closed all day
					? $formatted_rule
					// Enable exception dates that have opening/closing times
					: array_merge( $formatted_rule, ['inverted' => true] );
			}

			$disable_rules[] = $formatted_rule;

		}
	}

	return apply_filters( 'rtb_datepicker_disable_rules', $disable_rules, $schedule_open, $schedule_closed );

}
} // endif;

/**
 * Print a text input form field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_text_field' ) ) {
function rtb_print_form_text_field( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$type = empty( $args['input_type'] ) ? 'text' : esc_attr( $args['input_type'] );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-text';
	$required = isset( $args['required'] ) && $args['required'] ? ' required aria-required="true"' : '';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<input type="<?php echo $type; ?>" name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>" value="<?php echo esc_attr( $value ); ?>"<?php echo $required; ?>>
	</div>

	<?php

}
} // endif;

/**
 * Print a textarea form field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_textarea_field' ) ) {
function rtb_print_form_textarea_field( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	// Strip out <br> tags when placing in a textarea
	$value = preg_replace('/\<br(\s*)?\/?\>/i', '', $value);
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-textarea';
	$required = isset( $args['required'] ) && $args['required'] ? ' required aria-required="true"' : '';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<textarea name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>"<?php echo $required; ?>><?php echo esc_html( $value ); ?></textarea>
	</div>

	<?php

}
} // endif;

/**
 * Print a select form field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_select_field' ) ) {
function rtb_print_form_select_field( $slug, $title, $value, $args ) {
	$slug = esc_attr( $slug );
	$value =  is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( $value );
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-select';
	$required = isset( $args['required'] ) && $args['required'] ? ' required aria-required="true"' : '';
	$empty_option = isset( $args['empty_option'] ) ? true : false;
	
	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<select name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>"<?php echo $required; ?> <?php echo ( isset( $args['disabled'] ) and $args['disabled'] ) ? 'disabled' : ''; ?> data-selected="<?php echo ! is_array( $value ) && ! empty( $value ) ? $value : ''; ?>">
			<?php if ( $empty_option ) { ?> <option <?php ! is_array( $value ) ? selected( false, $value ) : false; ?>></option> <?php } ?>
			<?php foreach ( $options as $opt_value => $opt_label ) : ?>
			<option value="<?php echo esc_attr( $opt_value ); ?>" <?php ! is_array( $value ) ? selected( $opt_value, $value ) : false; ?>><?php echo esc_attr( $opt_label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php

}
} // endif;

/**
 * Print a checkbox form field
 *
 * @since 1.3.1
 */
if ( !function_exists( 'rtb_print_form_checkbox_field' ) ) {
function rtb_print_form_checkbox_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = !empty( $value ) ? array_map( 'esc_attr', $value ) : array();
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-checkbox';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label>
			<?php echo $title; ?>
		</label>
		<?php foreach ( $options as $opt_value => $opt_label ) : ?>
		<label>
			<input type="checkbox" name="rtb-<?php echo $slug; ?>[]" id="rtb-<?php echo $slug; ?>-<?php echo esc_attr( $opt_value ); ?>" value="<?php echo esc_attr( $opt_value ); ?>"<?php echo !empty( $value ) && in_array( $opt_value, $value ) ? ' checked' : ''; ?>>
			<?php echo $opt_label; ?>
		</label>
		<?php endforeach; ?>
	</div>

	<?php
}
} // endif;

/**
 * Print a radio button form field
 *
 * @since 1.3.1
 */
if ( !function_exists( 'rtb_print_form_radio_field' ) ) {
function rtb_print_form_radio_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-radio';
	$required = isset( $args['required'] ) && $args['required'] ? ' required aria-required="true"' : '';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label>
			<?php echo $title; ?>
		</label>
		<?php foreach ( $options as $opt_value => $opt_label ) : ?>
		<label>
			<input type="radio" name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>" value="<?php echo esc_attr( $opt_value ); ?>" <?php checked( $opt_value, $value ); ?><?php echo $required; ?>>
			<?php echo $opt_label; ?>
		</label>
		<?php endforeach; ?>
	</div>

	<?php
}
} // endif;

/**
 * Print a confirm prompt form field
 *
 * @since 1.3.1
 */
if ( !function_exists( 'rtb_print_form_confirm_field' ) ) {
function rtb_print_form_confirm_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-confirm';
	$required = isset( $args['required'] ) && $args['required'] ? ' required aria-required="true"' : '';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<input type="checkbox" name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>" value="1" <?php checked( $value, 1 ); ?><?php echo $required; ?>>
			<?php echo $title; ?>
		</label>
	</div>

	<?php

}
} // endif;

/**
 * Print the Add Message link to display the message field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_message_link' ) ) {
function rtb_print_form_message_link( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<a href="#">
			<?php echo $title; ?>
		</a>
	</div>

	<?php

}
} // endif;

/**
 * Print a form validation error
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_print_form_error' ) ) {
function rtb_print_form_error( $field ) {

	global $rtb_controller;

	if ( !empty( $rtb_controller->request ) && !empty( $rtb_controller->request->validation_errors ) ) {
		foreach ( $rtb_controller->request->validation_errors as $error ) {
			if ( $error['field'] == $field ) {
				echo '<div class="rtb-error">' . $error['message'] . '</div>';
			}
		}
	}
}
} // endif;

/**
 * Print a class attribute based on the slug and optional classes, provided with arguments
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_element_class' ) ) {
function rtb_print_element_class( $slug, $additional_classes = array() ) {
	$classes = empty( $additional_classes ) ? array() : $additional_classes;

	if ( ! empty( $slug ) ) {
		array_push( $classes, $slug );
	}

	$class_attr = esc_attr( join( ' ', $classes ) );

	return empty( $class_attr ) ? '' : sprintf( 'class="%s"', $class_attr );

}
} // endif;


/**
 * Retrieve an array of custom `cffrtbField` objects
 *
 * @since 0.1
 */
if ( !function_exists( 'rtb_get_custom_fields' ) ) {
function rtb_get_custom_fields() {

	$fields = array();

	// Avoid use of WP_Query here so that we don't tamper with the $post global.
	// This function gets called during `init` in the admin area, before
	// any $post global is setup, so wp_reset_postdata is unable to restore it
	// to `null` after modifying it. This caused issues, such as overriding the
	// date folder media uploads are saved into.
	$posts = get_posts( array( 'post_type' => 'cffrtb_field', 'post_status' => 'publish', 'posts_per_page' => 1000 ) );

	foreach ($posts as $post) {
		$fields[] = new cffrtbField(
			array(
				'ID'	=> $post->ID,
			)
		);
	}

	return $fields;
}
}

/**
 * Retrieve the submitted request data for a custom field
 *
 * @lookup int|string field ID or slug
 * @since 0.1
 */
if ( !function_exists( 'rtb_get_request_input' ) ) {
function rtb_get_request_input( $lookup, $request ) {

	// Retrieve slug from ID
	if ( is_int( $lookup ) ) {
		$post = get_post( $lookup );
		$lookup = $post->post_name;
	}

	if ( empty( $request->custom_fields ) || !isset( $request->custom_fields[ $lookup ] ) ) {
		return '';
	}

	return $request->custom_fields[ $lookup ];
}
}

if ( !function_exists( 'rtb_add_custom_styling' ) ) {
	function rtb_add_custom_styling() {
		global $rtb_controller;
		$styling = '<style>';
			if ( $rtb_controller->settings->get_setting('display-unavailable-time-slots') == '' ) { $styling .= '#rtb-time_root .picker__list .picker__list-item.picker__list-item--disabled { display: none; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-section-title-font-family') != '' ) { $styling .= '.rtb-booking-form fieldset legend { font-family: \'' . $rtb_controller->settings->get_setting('rtb-styling-section-title-font-family') . '\' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-section-title-font-size') != '' ) { $styling .=  '.rtb-booking-form fieldset legend { font-size: ' . $rtb_controller->settings->get_setting('rtb-styling-section-title-font-size') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-section-title-color') != '' ) { $styling .=  '.rtb-booking-form fieldset legend { color: ' . $rtb_controller->settings->get_setting('rtb-styling-section-title-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-section-background-color') != '' ) { $styling .=  '.rtb-booking-form fieldset { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-section-background-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-section-border-color') != '' ) { $styling .=  '.rtb-booking-form fieldset { border-color: ' . $rtb_controller->settings->get_setting('rtb-styling-section-border-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-section-border-size') != '' ) { $styling .=  '.rtb-booking-form fieldset { border-width: ' . $rtb_controller->settings->get_setting('rtb-styling-section-border-size') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-label-font-family') != '' ) { $styling .=  '.rtb-booking-form fieldset label, .rtb-booking-form .add-message a { font-family: \'' . $rtb_controller->settings->get_setting('rtb-styling-label-font-family') . '\' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-label-font-size') != '' ) { $styling .=  '.rtb-booking-form fieldset label { font-size: ' . $rtb_controller->settings->get_setting('rtb-styling-label-font-size') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-label-color') != '' ) { $styling .=  '.rtb-booking-form fieldset label { color: ' . $rtb_controller->settings->get_setting('rtb-styling-label-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-add-message-button-background-color') != '' ) { $styling .=  '.rtb-booking-form .add-message a { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-add-message-button-background-color') . ' !important; border-color: ' . $rtb_controller->settings->get_setting('rtb-styling-add-message-button-background-color') . ' !important; padding: 6px 12px !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-add-message-button-background-hover-color') != '' ) { $styling .=  '.rtb-booking-form .add-message a:hover { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-add-message-button-background-hover-color') . ' !important; border-color: ' . $rtb_controller->settings->get_setting('rtb-styling-add-message-button-background-hover-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-add-message-button-text-color') != '' ) { $styling .=  '.rtb-booking-form .add-message a { color: ' . $rtb_controller->settings->get_setting('rtb-styling-add-message-button-text-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-add-message-button-text-hover-color') != '' ) { $styling .=  '.rtb-booking-form .add-message a:hover { color: ' . $rtb_controller->settings->get_setting('rtb-styling-add-message-button-text-hover-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-background-color') != '' ) { $styling .=  '.rtb-booking-form form button { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-background-color') . ' !important; border-color: ' . $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-background-color') . ' !important; padding: 13px 28px !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-background-hover-color') != '' ) { $styling .=  '.rtb-booking-form form button:hover { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-background-hover-color') . ' !important; border-color: ' . $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-background-hover-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-text-color') != '' ) { $styling .=  '.rtb-booking-form form button { color: ' . $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-text-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-text-hover-color') != '' ) { $styling .=  '.rtb-booking-form form button:hover { color: ' . $rtb_controller->settings->get_setting('rtb-styling-request-booking-button-text-hover-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-cancel-button-background-color') != '' ) { $styling .=  '.rtb-modification-toggle { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-cancel-button-background-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-cancel-button-background-hover-color') != '' ) { $styling .=  '.rtb-modification-toggle:hover { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-cancel-button-background-hover-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-cancel-button-text-color') != '' ) { $styling .=  '.rtb-modification-toggle { color: ' . $rtb_controller->settings->get_setting('rtb-styling-cancel-button-text-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-cancel-button-text-hover-color') != '' ) { $styling .=  '.rtb-modification-toggle:hover { color: ' . $rtb_controller->settings->get_setting('rtb-styling-cancel-button-text-hover-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-background-color') != '' ) { $styling .=  '.rtb-find-reservation-button { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-background-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-background-hover-color') != '' ) { $styling .=  '.rtb-find-reservation-button:hover { background-color: ' . $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-background-hover-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-text-color') != '' ) { $styling .=  '.rtb-find-reservation-button { color: ' . $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-text-color') . ' !important; }'; }
			if ( $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-text-hover-color') != '' ) { $styling .=  '.rtb-find-reservation-button:hover { color: ' . $rtb_controller->settings->get_setting('rtb-styling-find-reservations-button-text-hover-color') . ' !important; }'; }
		$styling .=   '</style>';
		return $styling;
	}
}

/**
 * Retrieve tables that are available to be booked at a specific date/time
 *
 * @datetime int|string of the date/time to check
 * @since 2.1.7
 */
if ( ! function_exists( 'rtb_get_valid_tables') ) {
	function rtb_get_valid_tables( $datetime, $location_id = 0 ) {
		global $rtb_controller;

		$request_time = new DateTime( $datetime, wp_timezone() );

		if ( ! $request_time ) { return array(); }

		$tables = $rtb_controller->settings->get_sorted_tables( $datetime, $location_id );

		$table_numbers = array_keys( $tables );
		
		if ( empty( $table_numbers ) ) { return $table_numbers; }

		$dining_block_seconds = (int) $rtb_controller->settings->get_setting( 'rtb-dining-block-length' ) * 60 - 1; // Take 1 second off, to avoid bookings that start or end exactly at the beginning of a booking block

		$args = array(
			'posts_per_page' => -1,
			'date_range' => 'dates',
			'start_date' => $request_time->format( 'Y-m-d' ),
			'end_date' => $request_time->format( 'Y-m-d' )
		);

		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );
		$query = new rtbQuery( $args );
		$query->prepare_args();

		// Get all current bookings sorted by date
		$bookings = $query->get_bookings();

		$request_time_start = intval( $request_time->format( 'U' ) ) - $dining_block_seconds;
		$request_time_end = intval( $request_time->format( 'U' ) ) + $dining_block_seconds;

		$tmzn = wp_timezone();

		foreach ( $bookings as $booking ) {
			if ( $booking->post_status == 'cancelled' ) { continue; }

			$booking_time = ( new DateTime( $booking->date, $tmzn ) )->format( 'U' );

			if ( $booking_time < $request_time_start or $booking_time > $request_time_end ) { continue; }

			if ( ! isset( $booking->table ) or ! is_array( $booking->table ) ) { continue; }

			$remaining_tables = array_diff( $table_numbers, $booking->table );
			$table_numbers = $remaining_tables;
		}
		
		return $table_numbers;
	}
}

if ( ! function_exists( 'rtb_get_timeslot' ) ) {
function rtb_get_timeslot( $datetime, $location_id ) {
	global $rtb_controller;

	$selected_datetime = new DateTime( $datetime, wp_timezone() );
	$selected_weekday = strtolower( $selected_datetime->format( 'l' ) );

	$location_slug = ! empty( $location_id ) ? get_term_field( 'slug', $location_id ) : false;

	$schedule_closed = $rtb_controller->settings->get_setting( 'schedule-closed', $location_slug );
	$schedule_closed = is_array( $schedule_closed ) ? $schedule_closed : array();

	$exceptions_prefix = $rtb_controller->settings->is_location_setting_enabled( 'schedule-closed', $location_slug ) ? $location_slug . '_e_' : 'e_';

	// Check if this date is an exception to the rules
	if ( $schedule_closed !== 'undefined' ) {

		foreach ( $schedule_closed as $id => $closing ) {

			if ( array_key_exists( 'date_range', $closing ) ) {

				$start = ! empty( $closing['date_range']['start'] )
					? new DateTime( $closing['date_range']['start'], wp_timezone() )
					: new DateTime( 'now', wp_timezone() );
				$start->setTime(0, 0);

				$end = !empty( $closing['date_range']['end'] )
					? new DateTime( $closing['date_range']['end'], wp_timezone() )
					: ( new DateTime( 'now', wp_timezone() ) )->add( new DateInterval( 'P10Y' ) );
				$end->setTime(23, 59, 58);

				if ( $start < $selected_datetime && $selected_datetime < $end ) {
					$exception = clone $selected_datetime;
				}
				else {
					// Set anything to void this rule
					$exception = clone $selected_datetime;
					$exception->add( new DateInterval( 'P1Y' ) );
				}
			}
			else {
				$exception = ( new DateTime( $closing['date'], wp_timezone() ) )->setTime(0, 0, 2);
			}

			if ( $exception->format( 'Y-m-d' ) == $selected_datetime->format( 'Y-m-d' ) ) {

				// Closed all day
				if ( ! isset( $closing['time'] ) || $closing['time'] == 'undefined' ) {
					return $exceptions_prefix . $id;
				}

				if ( $closing['time']['start'] !== 'undefined' ) {
					$open_time = ( new DateTime( $exception->format( 'Y-m-d' ) . ' ' . $closing['time']['start'], wp_timezone() ) )->format( 'U' );
				}
				else {

					// Start of the day
					$open_time = ( new DateTime( $exception->format( 'Y-m-d' ), wp_timezone() ) )->format('U');
				}

				if ( $closing['time']['end'] !== 'undefined' ) {
					$close_time = ( new DateTime( $exception->format( 'Y-m-d' ) . ' ' . $closing['time']['end'], wp_timezone() ) )->format( 'U' );
				}
				else {

					// End of the day
					$close_time = ( new DateTime( $exception->format( 'Y-m-d' ) . ' 23:59:59', wp_timezone() ) )->format( 'U' );
				}
				
				if ( $open_time <= $selected_datetime->format( 'U' ) and $selected_datetime->format( 'U' ) <= $close_time ) {
					return $exceptions_prefix . $id;
				}
			}
		}
	}

	$schedule_open = $rtb_controller->settings->get_setting( 'schedule-open', $location_slug );
	$schedule_open = is_array( $schedule_open ) ? $schedule_open : array();

	$schedule_prefix = $rtb_controller->settings->is_location_setting_enabled( 'schedule-open', $location_slug ) ? $location_slug . '_s_' : 's_';

	foreach ( $schedule_open as $id => $opening ) {
		
		if ( $opening['weekdays'] !== 'undefined' ) {
			
			foreach ( $opening['weekdays'] as $weekday => $value ) {
				
				if ( $weekday == $selected_weekday ) {
					
					if ( isset( $opening['time'] ) && $opening['time']['start'] !== 'undefined' ) {

						$open_time = ( new DateTime( $selected_datetime->format( 'Y-m-d' ) .' '. $opening['time']['start'], wp_timezone() ) )->format( 'U' );
					}
					else {

						// Start of the day
						$open_time = ( new DateTime( $selected_datetime->format( 'Y-m-d' ), wp_timezone() ) )->format('U');
					}

					if ( isset( $opening['time'] ) && $opening['time']['end'] !== 'undefined' ) {

						$close_time = ( new DateTime( $selected_datetime->format( 'Y-m-d' ) .' '. $opening['time']['end'], wp_timezone() ) )->format( 'U' );
					}
					else {

						// End of the day
						$close_time = ( new DateTime( $selected_datetime->format( 'Y-m-d' ) . ' 23:59:59', wp_timezone() ) )->format( 'U' ); 
					}
					
					if ( $open_time <= $selected_datetime->format( 'U' ) and $selected_datetime->format( 'U' ) <= $close_time ) {
						return $schedule_prefix . $id;
					}
				}
			}
		}
	}

	return false;
}
}

if ( ! function_exists( 'rtb_decode_infinite_table_setting' ) ) {
function rtb_decode_infinite_table_setting( $values ) {

	if ( empty( $values ) ) { return array(); }
	
	return is_array( json_decode( html_entity_decode( $values ) ) ) ? json_decode( html_entity_decode( $values ) ) : array();
}
}

if ( ! function_exists( 'rtb_esc_js' ) ) {
	function rtb_esc_js( $value ) {

		return preg_replace( '/[^\p{L}\p{N} ,.-:\/]+/u', '', $value ); 
	}
}


// Temporary addition, so that versions of WP before 5.3.0 are supported
if ( ! function_exists( 'wp_timezone') ) {
	function wp_timezone() {
		$timezone_string = get_option( 'timezone_string' );
 
    	if ( ! $timezone_string ) {
        	$offset  = (float) get_option( 'gmt_offset' );
    		$hours   = (int) $offset;
    		$minutes = ( $offset - $hours );

    		$sign      = ( $offset < 0 ) ? '-' : '+';
    		$abs_hour  = abs( $hours );
    		$abs_mins  = abs( $minutes * 60 );
    		$timezone_string = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
    	}

    	return new DateTimeZone( $timezone_string );
	}
}