/* Javascript for Restaurant Reservations admin */
jQuery(document).ready(function ($) {

	// Only if on bookings page
	if( 
		'rtb-bookings' == ( new URL( window.location.href ) ).searchParams.get( 'page' )
		&&
		0 < parseInt( rtb_admin.refresh_booking_listing )
	) {
		let refresh_interval = ( parseInt( rtb_admin.refresh_booking_listing ) * 1000);

		let refresh = setTimeout(function() {
			window.location = window.location.href;
		}, refresh_interval);

		$(document).on('click', function(event) {
			clearTimeout(refresh);

			refresh = setTimeout(function() {
				window.location = window.location.href;
			}, refresh_interval);
		});
	}

	// Show/hide date filter in bookings list
	$( '#rtb-date-filter-link' ).click( function() {
		$( '#rtb-filters' ).toggleClass( 'date-filters-visible' );
	});

	// Name filter helper
	$(document)
		.on('click', '#rtb-filters .filter_submit a' , function(ev) {
			ev.preventDefault();
			filterByDetails();
		})
		.on('keydown', '#rtb-filters .filter_name input, #rtb-filters .filter_email input, #rtb-filters .filter_phone input, #rtb-filters .filter_table input' , function(ev) {
			if(event.keyCode == 13) {
				event.preventDefault();
				filterByDetails();
				return false;
			}
		});

	function filterByDetails() {
		let name = $('#rtb-filters .filter_name input').val();
		let email = $('#rtb-filters .filter_email input').val();
		let phone = $('#rtb-filters .filter_phone input').val();
		let table = $('#rtb-filters .filter_table input').val();

		let href = $('#rtb-filters .filter_submit a').prop('href');

		if ( name ) { href += '&filter_name='+encodeURIComponent(name); }
		if ( email ) { href += '&filter_email='+encodeURIComponent(email); }
		if ( phone ) { href += '&filter_phone='+encodeURIComponent(phone); }
		if ( table ) { href += '&filter_table='+encodeURIComponent(table); }
		
		window.location = href;
	}

	// Construct anchor URL for Specific date filter to send a GET request instead
	// of post request. This will let pagniation work properly
	$(document).on('click', '.date-filters input[type="submit"]', function(event) {
		event.preventDefault();
		
		let args = [];
		let url = new URL(window.location.href);

		$('.date-filters input[type="hidden"]').each((i, x) => {
			'' === $(x).val() ? null : args.push([$(x).prop('name'), $(x).val()]);
		});

		args.push(['page', url.searchParams.get('page')]);
		args = new URLSearchParams(args);

		window.location = `${url.origin}${url.pathname}?${args.toString()}`;
	});

	// Add date picker to date filter in admin
	$( '#start-date, #end-date' ).each( function() {
		var input = $(this);

		input.pickadate({
			format: rtb_pickadate.date_format,
			formatSubmit: 'yyyy/mm/dd',
			hiddenName: true,

			onStart: function() {
				if ( input.val() !== '' ) {
					var date = new Date( input.val() );
					if ( Object.prototype.toString.call( date ) === "[object Date]" ) {
						this.set( 'select', date );
					}
				}
			}
		});
	});

	// Add a time picker for filtering in admin
	$ ( '#start-time, #end-time' ).each( function() {
		var $time_input = $( this );

		$time_input.pickatime({
			format: rtb_pickadate.time_format,
			formatSubmit: 'h:i A',
			hiddenName: true,
			interval: parseInt( rtb_pickadate.time_interval, 10 ),
			container: 'body',

			// Select the value when loaded if a value has been set
			onStart: function() {
				if ( $time_input.val()	!== '' ) {
					var today = new Date();
					var today_date = today.getFullYear() + '/' + ( today.getMonth() + 1 ) + '/' + today.getDate();
					var time = new Date( today_date + ' ' + $time_input.val() );
					if ( Object.prototype.toString.call( time ) === "[object Date]" ) {
						this.set( 'select', time );
					}

				}
			}
		});
	});

	// Show or hide extra booking details in the bookings table
	$( '.rtb-show-details' ).click( function (e) {
		e.preventDefault();
		rtb_toggle_details_modal( true, $(this).siblings( '.rtb-details-data' ).html() );
	});

	// Register clicks on action links
	$( document ).on('click', '#rtb-bookings-table tr .actions, #rtb-details-modal .actions', function(e) {

		var target = $(e.target);
		var action = target.data( 'action' );

		if ( !action ) {
			return;
		}

		var cell = target.parent().parent();

		if ( target.data( 'action' ) == 'edit' ) {
			rtb_booking_loading_spinner( true, cell );
			rtb_get_booking( target.data( 'id' ), cell );

		} else if ( target.data( 'action' ) == 'trash' ) {
			rtb_booking_loading_spinner( true, cell );
			rtb_trash_booking( target.data( 'id' ), cell );

		} else if ( target.data( 'action' ) == 'email') {
			rtb_toggle_details_modal(false);
			rtb_toggle_email_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'name' ) );

		} else if ( target.data( 'action' ) == 'ban') {
			rtb_toggle_ban_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'ip' ) );

		} else if ( target.data( 'action' ) == 'delete') {
			rtb_toggle_delete_modal( true, target.data( 'id'), target.data( 'email' ) );
		}

		e.preventDefault();
	});

	// Register clicks on the 'Send Email' bulk action
	$( '#rtb-bookings-table .bulkactions #doaction' ).on( 'click', function(e) {

		var target = $( e.target );
		var select = target.parent().find( 'select' ).first();

		if ( select.val() != 'send-email' ) { return; }

		e.stopPropagation();
		e.preventDefault();

		var booking_ids = [];

		$( 'input[name="bookings[]"]:checked' ).each( function() {
			booking_ids.push( $( this ).val() );
		});

		rtb_toggle_email_modal( true, booking_ids.join(','), 'multiple-emails', 'Multiple Bookings' );
	});

	// Show booking form modal
	$( '.add-booking' ).click( function( e ) {
		e.preventDefault();
		rtb_toggle_booking_form_modal( true );
	});
	$( document ).on( 'click', '.rtb-service-save', function() {
		var button = $( this );
		var row = button.closest( 'tr[data-booking-id]' );
		var field = button.data( 'field' );
		var feedback = row.find( '.rtb-service-feedback' );
		var params = {
			action: 'rtb-admin-service-update',
			nonce: rtb_admin.nonce,
			booking_id: row.data( 'booking-id' ),
			revision: row.attr( 'data-revision' ),
			override: row.find( '.rtb-service-override-input' ).is( ':checked' ) ? 1 : 0
		};
		if ( field === 'status' ) { params.status = row.find( '.rtb-service-status' ).val(); }
		if ( field === 'table' ) {
			params.table_present = 1;
			params.table = row.find( '.rtb-service-tables' ).val() || [];
		}
		button.prop( 'disabled', true );
		feedback.text( '' );
		$.post( ajaxurl, params ).done( function( response ) {
			if ( response.success ) {
				row.attr( 'data-revision', response.data.revision );
				// Use the persisted status, preserving extension/alternating classes.
				if ( ( rtb_admin.booking_statuses || [] ).indexOf( response.data.status ) !== -1 ) {
					row.removeClass( rtb_admin.booking_statuses.join( ' ' ) ).addClass( response.data.status );
					row.find( '.rtb-service-status' ).val( response.data.status );
				}
				feedback.text( 'Saved.' );
			} else {
				feedback.text( response.data && response.data.msg ? response.data.msg : 'Booking changed. Reload and retry.' );
			}
		} ).fail( function( xhr ) {
			var response = xhr.responseJSON;
			feedback.text( response && response.data && response.data.msg ? response.data.msg : 'Could not save. Reload and retry.' );
		} ).always( function() { button.prop( 'disabled', false ); } );
	});
	$( document ).on( 'change', 'input[name="rtb-walk-in"]', function() {
		if ( ! this.checked || rtb_booking_modal.find( 'input[name=ID]' ).val() ) { return; }
		rtb_booking_modal_fields.find( '#rtb-post-status' ).val( 'arrived' );
		var date = rtb_booking_modal_fields.find( '#rtb-date' );
		var time = rtb_booking_modal_fields.find( '#rtb-time' );
		if ( ! date.val() ) {
			var datePicker = date.pickadate( 'picker' );
			if ( datePicker ) { datePicker.set( 'select', rtb_booking_modal.data( 'today' ), { format: 'yyyy/mm/dd' } ); }
			else { date.val( rtb_booking_modal.data( 'today' ) ); }
		}
		if ( ! time.val() ) {
			var timePicker = time.pickatime( 'picker' );
			if ( timePicker ) { timePicker.set( 'select', rtb_booking_modal.data( 'now' ), { format: 'h:i A' } ); }
			else { time.val( rtb_booking_modal.data( 'now' ) ); }
		}
	});

	// Show column configuration modal
	$( '.rtb-columns-button' ).click( function( e ) {
		e.preventDefault();
		rtb_toggle_column_modal( true );
	});

	/**
	 * Show/hide loading spinner when edit/delete link clicked
	 */
	function rtb_booking_loading_spinner( loading, cell ) {
		if ( loading ) {
			cell.addClass( 'loading' );
		} else {
			cell.removeClass( 'loading' );
		}
	}

	/**
	 * Modals for the admin page
	 */
	var rtb_booking_modal = $( '#rtb-booking-modal' ),
		rtb_booking_modal_fields = rtb_booking_modal.find( '#rtb-booking-form-fields' ),
		rtb_booking_modal_submit = rtb_booking_modal.find( 'button' ),
		rtb_booking_modal_cancel = rtb_booking_modal.find( '#rtb-cancel-booking-modal' ),
		rtb_booking_modal_action_status = rtb_booking_modal.find( '.action-status' ),
		rtb_email_modal = $( '#rtb-email-modal' ),
		rtb_email_modal_submit = rtb_email_modal.find( 'button' ),
		rtb_email_modal_cancel = rtb_email_modal.find( '#rtb-cancel-email-modal' ),
		rtb_email_modal_action_status = rtb_email_modal.find( '.action-status' ),
		rtb_column_modal = $( '#rtb-column-modal' ),
		rtb_column_modal_submit = rtb_column_modal.find( 'button' ),
		rtb_column_modal_cancel = rtb_column_modal.find( '#rtb-cancel-column-modal' ),
		rtb_column_modal_action_status = rtb_column_modal.find( '.action-status' ),
		rtb_details_modal = $( '#rtb-details-modal' ),
		rtb_details_modal_close = rtb_details_modal.find( '#rtb-close-details-modal' ),
		rtb_details_modal_cancel = rtb_details_modal.find( '#rtb-cancel-details-modal' ),
		rtb_booking_modal_error = $( '#rtb-error-modal' ),
		rtb_booking_modal_error_msg = rtb_booking_modal_error.find( '.rtb-error-msg' ),
		rtb_ban_modal = $( '#rtb-ban-modal' ),
		rtb_ban_modal_submit_email = rtb_ban_modal.find( '#rtb-ban-modal-email-btn' ),
		rtb_ban_modal_submit_ip = rtb_ban_modal.find( '#rtb-ban-modal-ip-btn' ),
		rtb_ban_modal_cancel = rtb_ban_modal.find( '#rtb-cancel-ban-modal' ),
		rtb_ban_modal_action_status = rtb_ban_modal.find( '.action-status' ),
		rtb_delete_modal = $( '#rtb-delete-modal' ),
		rtb_delete_modal_submit_btn = rtb_delete_modal.find( '#rtb-delete-modal-btn' ),
		rtb_delete_modal_cancel = rtb_delete_modal.find( '#rtb-cancel-delete-modal' ),
		rtb_delete_modal_action_status = rtb_delete_modal.find( '.action-status' ),
		rtb_delete_modal_status = rtb_delete_modal.find( '#rtb-delete-status' ),
		rtb_delete_modal_progress = rtb_delete_modal_status.find( '#rtb-delete-status-progress' ),
		rtb_delete_modal_deleted = rtb_delete_modal_status.find( '#rtb-delete-status-deleted' );

	/**
	 * Show or hide the booking form modal
	 */
	function rtb_toggle_booking_form_modal( show, fields, booking ) {

		if ( show ) {
			rtb_booking_modal.scrollTop( 0 ).addClass( 'is-visible' );

			if ( typeof fields !== 'undefined' ) {
				rtb_booking_modal_fields.html( fields );
				rtb_init_booking_form_modal_fields();
			}

			if ( typeof booking == 'undefined' ) {
				rtb_booking_modal_fields.find( '#rtb-post-status' ).val( 'confirmed' );
				rtb_booking_modal.find( 'input[name="rtb-revision"]' ).val( '' );
				rtb_booking_modal_submit.html( rtb_admin.strings.add_booking );
			} else {
				rtb_booking_modal_submit.html( rtb_admin.strings.edit_booking );
				rtb_booking_modal.find( 'input[name=ID]' ).val( booking.ID );
				if ( rtb_pickadate.enable_tables ) { rtb_booking_form.update_possible_tables(); }
			}

			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_booking_modal.removeClass( 'is-visible' );
			rtb_booking_modal.find( '.rtb-error' ).remove();
			rtb_booking_modal.find( '.notifications-description' ).removeClass( 'is-visible' );
			rtb_booking_modal_action_status.removeClass( 'is-visible' );
			rtb_reset_booking_form_modal_fields();
			rtb_booking_modal_submit.removeData( 'id' );
			rtb_booking_modal_submit.prop( 'disabled', false );
			rtb_booking_modal_cancel.prop( 'disabled', false );
			rtb_booking_modal.find( 'input[name=ID]' ).val( '' );
			rtb_booking_modal.find( 'input[name="rtb-revision"]' ).val( '' );

			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the booking form error modal
	 */
	function rtb_toggle_booking_form_error_modal( show, msg ) {

		if ( show ) {
			rtb_booking_modal_error_msg.html( msg );
			rtb_booking_modal_error.addClass( 'is-visible' );

		} else {
			rtb_booking_modal_error.removeClass( 'is-visible' );
		}
	}

	/**
	 * Show or hide the email form modal
	 */
	function rtb_toggle_email_modal( show, id, email, name ) {

		if ( show ) {
			rtb_email_modal.scrollTop( 0 ).addClass( 'is-visible' );
			rtb_email_modal.find( 'input[name=ID]' ).val( id );
			rtb_email_modal.find( 'input[name=email]' ).val( email );
			rtb_email_modal.find( 'input[name=name]' ).val( name );
			rtb_email_modal.find( '.rtb-email-to' ).text( name + ' <' + email + '>' );

			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_email_modal.removeClass( 'is-visible' );
			rtb_email_modal.find( '.rtb-email-to' ).html( '' );
			rtb_email_modal.find( 'textarea, input[type="hidden"], input[type="text"]' ).val( '' );
			rtb_email_modal_submit.prop( 'disabled', false );
			rtb_email_modal_cancel.prop( 'disabled', false );

			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the column configuration modal
	 */
	function rtb_toggle_column_modal( show ) {

		if ( show ) {
			rtb_column_modal.scrollTop( 0 ).addClass( 'is-visible' );
			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_column_modal.removeClass( 'is-visible' );
			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the booking details modal
	 */
	var rtb_details_cleanup_timer;
	function rtb_toggle_details_modal( show, content ) {
		clearTimeout( rtb_details_cleanup_timer );

		if ( show ) {
			rtb_details_modal.addClass( 'is-visible' ).scrollTop( 0 )
				.find( '.rtb-details-data' ).html( content );
			// Complete legacy text formatting before asynchronous history takes ownership
			// of its panel. Replacing .value HTML afterwards detaches the pending request's node.
			rtb_details_modal.find( '.details .value' ).each(function () {
				if ( $(this).find( '.rtb-notification-history' ).length ) { return; }
				var original = $(this).html(), formatted = original.replace(/&lt;(.*?)br(.*?)\/&gt;/gmi, '<br>');
				if ( formatted !== original ) { $(this).html( formatted ); }
			});
			$( 'body' ).addClass( 'rtb-hide-body-scroll' );
			rtb_details_modal.find( '.actions' ).click( function(e) {
				var target = $( e.target );
				rtb_toggle_details_modal( false );
				if ( target.data( 'action' ) == 'email') {
					rtb_toggle_email_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'name' ) );
				} else if ( target.data( 'action' ) == 'ban') {
					rtb_toggle_ban_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'ip' ) );
				} else if ( target.data( 'action' ) == 'delete') {
					rtb_toggle_delete_modal( true, target.data( 'id'), target.data( 'email' ) );
				}
			});
			rtb_details_modal.trigger('rtb:details-open');

		} else {
			rtb_details_modal.removeClass( 'is-visible' );
			rtb_details_modal.trigger('rtb:details-close');
			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
			rtb_details_cleanup_timer = setTimeout( function() {
				rtb_details_modal.find( '.rtb-details-data' ).empty();
			}, 300 );
		}
	}

	/**
	 * Show or hide the ban ip/email form modal
	 */
	function rtb_toggle_ban_modal( show, id, email, ip ) {

		if ( show ) {
			rtb_ban_modal.scrollTop( 0 ).addClass( 'is-visible' );
			rtb_ban_modal.find( '#rtb-ban-modal-email' ).text( email );
			rtb_ban_modal.find( '#rtb-ban-modal-ip' ).text( ip );
			if ( rtb_admin.banned_emails.indexOf( email ) > -1 ) {
				rtb_ban_modal_submit_email.prop( 'disabled', true );
			}
			if ( rtb_admin.banned_ips.indexOf( ip ) > -1 ) {
				rtb_ban_modal_submit_ip.prop( 'disabled', true );
			}

			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_ban_modal.removeClass( 'is-visible' );
			rtb_ban_modal.find( '#rtb-ban-modal-email' ).text( '' );
			rtb_ban_modal.find( '#rtb-ban-modal-ip' ).text( '' );
			rtb_ban_modal_submit_email.prop( 'disabled', false );
			rtb_ban_modal_submit_ip.prop( 'disabled', false );
			rtb_ban_modal_cancel.prop( 'disabled', false );

			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the delete customer form modal
	 */
	function rtb_toggle_delete_modal( show, id, email ) {

		if ( show ) {
			rtb_delete_modal.scrollTop( 0 ).addClass( 'is-visible' );
			rtb_delete_modal.find( '#rtb-delete-modal-email' ).text( email );
			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_delete_modal.removeClass( 'is-visible' );
			rtb_delete_modal.find( '#rtb-ban-modal-email' ).text( '' );
			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Initialize form field events
	 */
	function rtb_init_booking_form_modal_fields() {

		// Run init on the form
		rtb_booking_form.init();

		// Show full description for notifications toggle
		rtb_booking_modal_fields.find( '.rtb-description-prompt' ).click( function() {
			$(this).parent().siblings( '.rtb-description' ).addClass( 'is-visible' );
		});
	}

	/**
	 * Reset booking form fields
	 */
	function rtb_reset_booking_form_modal_fields() {
		rtb_booking_modal_fields.find( 'input,select, textarea' ).not( 'input[type="checkbox"],input[type="radio"]' ).val( '' );
		rtb_booking_modal_fields.find( 'input[name=rtb-notifications]' ).removeAttr( 'checked' );
		rtb_booking_modal_fields.find( 'input[name="rtb-walk-in"]' ).prop( 'checked', false );
	}

	/**
	 * Retrieve booking from the database
	 */
	function rtb_get_booking( id, cell ) {

		var params = {};

		params.action = 'rtb-admin-booking-modal';
		params.nonce = rtb_admin.nonce;
		params.booking = {
			'ID':	id
		};

		var data = $.param( params );

		var jqhxr = $.get( ajaxurl, data, function( r ) {

			if ( r.success ) {
				rtb_toggle_booking_form_modal( true, r.data.fields, r.data.booking );
				rtb_booking_modal.find( 'input[name="rtb-revision"]' ).val( r.data.revision );

			} else {

				if ( typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}
			}

			rtb_booking_loading_spinner( false, cell );
		});
	}

	/**
	 * Trash booking
	 */
	function rtb_trash_booking( id, cell ) {

		var params = {};

		params.action = 'rtb-admin-trash-booking';
		params.nonce = rtb_admin.nonce;
		params.booking = id;

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				cell.parent().fadeOut( 500, function() {
					$(this).remove();
				});

				var trash_count_el = $( '#rtb-bookings-table .subsubsub .trash .count' );
				var trash_count = parseInt( trash_count_el.html().match(/\d+/), 10 ) + 1;
				trash_count_el.html( '(' + trash_count + ')' );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}
			}

			rtb_booking_loading_spinner( false, cell );
		});

	}

	/**
	 * Show the appropriate result status icon
	 */
	function rtb_show_action_status( el, status ) {

		el.find( 'span' ).hide();

		if ( status === true ) {
			el.find( '.success' ).show();
		} else if ( status === false ) {
			el.find( '.error' ).show();
		} else {
			el.find( '.spinner' ).show();
		}
	}

	// Reset the forms on load
	// This fixes a strange bug in Firefox where disabled buttons would
	// persist after the page refreshed. I'm guessing its a cache issue
	// but this will just reset everything again
	rtb_toggle_booking_form_modal( false );
	rtb_toggle_email_modal( false );
	rtb_toggle_column_modal( false );

	// Close booking form modal when background or cancel button is clicked
	rtb_booking_modal.click( function(e) {
		if ( $(e.target).is( rtb_booking_modal ) ) {
			rtb_toggle_booking_form_modal( false );
		}

		if ( $(e.target).is( rtb_booking_modal_cancel ) && rtb_booking_modal_cancel.prop( 'disabled' ) === false ) {
			rtb_toggle_booking_form_modal( false );
		}
	});

	// Close email modal when background or cancel button is clicked
	rtb_email_modal.click( function(e) {
		if ( $(e.target).is( rtb_email_modal ) ) {
			rtb_toggle_email_modal( false );
		}

		if ( $(e.target).is( rtb_email_modal_cancel ) && rtb_email_modal_cancel.prop( 'disabled' ) === false ) {
			rtb_toggle_email_modal( false );
		}
	});

	// Close column modal when background or cancel button is clicked
	rtb_column_modal.click( function(e) {
		if ( $(e.target).is( rtb_column_modal ) ) {
			rtb_toggle_column_modal( false );
		}

		if ( $(e.target).is( rtb_column_modal_cancel ) && rtb_column_modal_cancel.prop( 'disabled' ) !== true ) {
			rtb_toggle_column_modal( false );
		}
	});

	// Close details modal when background or cancel button is clicked
	rtb_details_modal.click( function(e) {
		if ( $(e.target).is( rtb_details_modal ) ) {
			rtb_toggle_details_modal( false );
		}

		if ( $(e.target).is( rtb_details_modal_cancel ) ) {
			rtb_toggle_details_modal( false );
		}
	});

	// Close booking form error modal when background or cancel button is clicked
	rtb_booking_modal_error.click( function(e) {
		if ( $(e.target).is( rtb_booking_modal_error ) || $(e.target).is( rtb_booking_modal_error.find( 'a.button' ) ) ) {
			rtb_toggle_booking_form_error_modal( false );
		}
	});

	// Close ban modal when background or cancel button is clicked
	rtb_ban_modal.click( function(e) {
		if ( $(e.target).is( rtb_ban_modal ) ) {
			rtb_toggle_ban_modal( false );
		}

		if ( $(e.target).is( rtb_ban_modal_cancel ) && rtb_ban_modal_cancel.prop( 'disabled' ) !== true ) {
			rtb_toggle_ban_modal( false );
		}
	});

	// Close delete modal when background or cancel button is clicked
	rtb_delete_modal.click( function(e) {
		if ( $(e.target).is( rtb_delete_modal ) ) {
			rtb_toggle_delete_modal( false );
		}

		if ( $(e.target).is( rtb_delete_modal_cancel ) && rtb_delete_modal_cancel.prop( 'disabled' ) !== true ) {
			rtb_toggle_delete_modal( false );
		}
	});

	// Close modals when ESC is keyed
	$(document).keyup( function(e) {
		if ( e.which == '27' ) {
			rtb_toggle_booking_form_modal( false );
			rtb_toggle_email_modal( false );
			rtb_toggle_column_modal( false );
			rtb_toggle_details_modal( false );
			rtb_toggle_booking_form_error_modal( false );
			rtb_toggle_ban_modal( false );
			rtb_toggle_delete_modal( false );
		}
	});

	// Submit booking form modal
	rtb_booking_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		rtb_booking_modal_submit.prop( 'disabled', true );
		rtb_booking_modal_cancel.prop( 'disabled', true );
		rtb_booking_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_booking_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-booking-modal';
		params.nonce = rtb_admin.nonce;
		params.booking = rtb_booking_modal.find( 'form' ).serializeArray();

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				// Refresh the page so that the new details are visible
				window.location.reload();

			} else {

				// Validation failed
				if ( r.data.error == 'invalid_booking_data' ) {

					// Replace form fields with HTML returned
					rtb_booking_modal_fields.html( r.data.fields );
					rtb_init_booking_form_modal_fields();

				// Logged out
				} else if ( r.data.error == 'loggedout' ) {
					rtb_booking_modal_fields.after( '<div class="rtb-error">' + r.data.msg + '</div>' );

				// Unspecified error
				} else {
					rtb_booking_modal_fields.after( '<div class="rtb-error">' + rtb_admin.strings.error_unspecified + '</div>' );
				}

				rtb_booking_modal_cancel.prop( 'disabled', false );
				rtb_booking_modal_submit.prop( 'disabled', false );
			}

			rtb_show_action_status( rtb_booking_modal_action_status, r.success );

			// Hide result status icon after a few seconds
			setTimeout( function() {
				rtb_booking_modal.find( '.action-status' ).removeClass( 'is-visible' );
			}, 4000 );
		});
	});

	// Submit email form modal
	rtb_email_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		rtb_email_modal_submit.prop( 'disabled', true );
		rtb_email_modal_cancel.prop( 'disabled', true );
		rtb_email_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_email_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-email-modal';
		params.nonce = rtb_admin.nonce;
		params.email = rtb_email_modal.find( 'form' ).serializeArray();

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				rtb_show_action_status( rtb_email_modal_action_status, r.success );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_email_modal.find( '.action-status' ).removeClass( 'is-visible' );
					rtb_toggle_email_modal( false );
				}, 1000 );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				rtb_email_modal_cancel.prop( 'disabled', false );
				rtb_email_modal_submit.prop( 'disabled', false );

				rtb_show_action_status( rtb_email_modal_action_status, false );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_email_modal.find( '.action-status' ).removeClass( 'is-visible' );
				}, 4000 );
			}
		});
	});

	// Submit column configuration modal
	rtb_column_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		rtb_column_modal_submit.prop( 'disabled', true );
		rtb_column_modal_cancel.prop( 'disabled', true );
		rtb_column_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_column_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-column-modal';
		params.nonce = rtb_admin.nonce;

		params.columns = [];
		rtb_column_modal.find( 'input[name="rtb-columns-config"]:checked' ).each( function() {
			params.columns.push( $(this).val() );
		});

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				// Refresh the page so that the new details are visible
				window.location.reload();

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				rtb_column_modal_cancel.prop( 'disabled', false );
				rtb_column_modal_submit.prop( 'disabled', false );
			}

			rtb_show_action_status( rtb_column_modal_action_status, r.success );

			// Hide result status icon after a few seconds
			setTimeout( function() {
				rtb_column_modal.find( '.action-status' ).removeClass( 'is-visible' );
			}, 4000 );
		});
	});

	// Shared function for banning emails and IPs
	function rtb_ban_modal_submit( e, type ) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		if ( type === 'email' ) {
			rtb_ban_modal_submit_email.prop( 'disabled', true );
		} else if ( type === 'ip' ) {
			rtb_ban_modal_submit_ip.prop( 'disabled', true );
		}
		rtb_ban_modal_cancel.prop( 'disabled', true );
		rtb_ban_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_ban_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-ban-modal';
		params.nonce = rtb_admin.nonce;
		if ( type === 'email' ) {
			params.email = rtb_ban_modal.find( '#rtb-ban-modal-email' ).text();
		} else if ( type === 'ip' ) {
			params.ip = rtb_ban_modal.find( '#rtb-ban-modal-ip' ).text();
		}

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				if ( type === 'email' ) {
					rtb_admin.banned_emails.push( params.email );
				} else if ( type === 'ip' ) {
					rtb_admin.banned_ips.push( params.ip );
				}

				rtb_show_action_status( rtb_ban_modal_action_status, r.success );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_ban_modal.find( '.action-status' ).removeClass( 'is-visible' );
				}, 1000 );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				rtb_ban_modal_cancel.prop( 'disabled', false );
				if ( type === 'email' ) {
					rtb_ban_modal_submit_email.prop( 'disabled', false );
				} else if ( type === 'ip' ) {
					rtb_ban_modal_submit_ip.prop( 'disabled', false );
				}

				rtb_show_action_status( rtb_ban_modal_action_status, false );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_ban_modal.find( '.action-status' ).removeClass( 'is-visible' );
				}, 4000 );
			}
		});
	}

	// Submit ban email form modal
	rtb_ban_modal_submit_email.click( function(e) {
		rtb_ban_modal_submit( e, 'email' );
	});

	// Submit ban ip form modal
	rtb_ban_modal_submit_ip.click( function(e) {
		rtb_ban_modal_submit( e, 'ip' );
	});

	// Delete customers by email
	function rtb_delete_modal_submit( e ) {

		e.preventDefault();
		e.stopPropagation();

		// Loading
		rtb_delete_modal_submit_btn.prop( 'disabled', true );
		rtb_delete_modal_cancel.prop( 'disabled', true );
		rtb_delete_modal_action_status.addClass( 'is-visible' );
		rtb_delete_modal_status.addClass('is-visible');
		rtb_show_action_status( rtb_delete_modal_action_status, 'loading' );

		function delete_page( params ) {

			var jqhxr = $.post( ajaxurl, $.param( params ), function( r ) {

				if ( r.success ) {

					processed = processed + r.data.processed;
					deleted = deleted + r.data.deleted;
					var percent = Math.ceil( ( processed / r.data.total ) * 100 );
					rtb_delete_modal_progress.css( 'width', percent + '%' );
					rtb_delete_modal_deleted.text( deleted + ' bookings deleted' );

					if ( processed < r.data.total ) {
						params.page++;
						delete_page( params );
					} else {
						rtb_delete_modal_cancel.click(function(e) {
							window.location.reload();
						});
						rtb_delete_modal_deleted.text( 'Finished! ' + deleted + ' bookings deleted' );
						rtb_delete_modal_cancel.prop( 'disabled', false );
						rtb_delete_modal_submit_btn.css('display', 'none');
						rtb_delete_modal_action_status.removeClass( 'is-visible' );
						rtb_show_action_status( rtb_delete_modal_action_status, false );
					}

				} else {

					if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
						rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
					} else {
						rtb_toggle_booking_form_error_modal( true, r.data.msg );
					}

					rtb_delete_modal_submit_btn.prop( 'disabled', false );
					rtb_delete_modal_cancel.prop( 'disabled', false );
					rtb_delete_modal_action_status.removeClass( 'is-visible' );
					rtb_delete_modal_status.removeClass( 'is-visible' );
					rtb_show_action_status( rtb_delete_modal_action_status, false );
				}
			});
		}

		var processed = 0;
		var deleted = 0;
		delete_page({
			action: 'rtb-admin-delete-modal',
			nonce: rtb_admin.nonce,
			email: rtb_delete_modal.find( '#rtb-delete-modal-email' ).text(),
			page: 1,
		});
	}

	// Submit ban email form modal
	rtb_delete_modal_submit_btn.click( function(e) {
		rtb_delete_modal_submit( e );
	});

});


/*NEW DASHBOARD MOBILE MENU AND WIDGET TOGGLING*/
jQuery(document).ready(function($){
	$('#rtb-dash-mobile-menu-open').click(function(){
		$('.rtb-admin-header-menu .nav-tab:nth-of-type(1n+2)').toggle();
		$('#rtb-dash-mobile-menu-up-caret').toggle();
		$('#rtb-dash-mobile-menu-down-caret').toggle();
		return false;
	});

	$(function(){
		$(window).resize(function(){
			if($(window).width() > 800){
				$('.rtb-admin-header-menu .nav-tab:nth-of-type(1n+2)').show();
			}
			else{
				$('.rtb-admin-header-menu .nav-tab:nth-of-type(1n+2)').hide();
				$('#rtb-dash-mobile-menu-up-caret').hide();
				$('#rtb-dash-mobile-menu-down-caret').show();
			}
		}).resize();
	});	

	$('#rtb-dashboard-support-widget-box .rtb-dashboard-new-widget-box-top').click(function(){
		$('#rtb-dashboard-support-widget-box .rtb-dashboard-new-widget-box-bottom').toggle();
		$('#rtb-dash-mobile-support-up-caret').toggle();
		$('#rtb-dash-mobile-support-down-caret').toggle();
	});

	$('#rtb-dashboard-optional-table .rtb-dashboard-new-widget-box-top').click(function(){
		$('#rtb-dashboard-optional-table .rtb-dashboard-new-widget-box-bottom').toggle();
		$('#rtb-dash-optional-table-up-caret').toggle();
		$('#rtb-dash-optional-table-down-caret').toggle();
	});

	$('.rtb-ultimate-upgrade-dismiss').on('click', function() {
		$('#rtb-dashboard-upgrade-box').addClass('rtb-hidden');
		$('#rtb-dashboard-show-upgrade-box-link').removeClass('rtb-hidden');

		var params = {
			action: 'rtb_hide_upgrade_box',
			nonce: rtb_admin.nonce
		};

		var data = $.param( params );
		$.post( ajaxurl, data );
	});

	$('#rtb-dashboard-show-upgrade-box-link').on('click', function() {
		$('#rtb-dashboard-show-upgrade-box-link').addClass('rtb-hidden');
		$('#rtb-dashboard-upgrade-box').removeClass('rtb-hidden');

		var params = {
			action: 'rtb_display_upgrade_box',
			nonce: rtb_admin.nonce
		};

		var data = $.param( params );
		$.post( ajaxurl, data );
	});
});


//OPTIONS PAGE YES/NO TOGGLE SWITCHES
jQuery(document).ready(function($){
	$('.rtb-admin-option-toggle').on('change', function() {
		var Input_Name = $(this).data('inputname'); 
		if ($(this).is(':checked')) {
			$('input[name="' + Input_Name + '"][value="1"]').prop('checked', true).trigger('change');
			$('input[name="' + Input_Name + '"][value=""]').prop('checked', false);
		}
		else {
			$('input[name="' + Input_Name + '"][value="1"]').prop('checked', false).trigger('change');
			$('input[name="' + Input_Name + '"][value=""]').prop('checked', true);
		}
	});
});

/* Handle Trial Type Selection */
jQuery(document).ready(function($) {
	
	jQuery( '.fsp-premium-helper-dashboard-new-trial-button' ).on('click', function() {

		jQuery( '.rtb-trial-version-select-modal-background , .rtb-trial-version-select-modal' ).removeClass( 'rtb-hidden' );

		return false;
	});

	jQuery( '.rtb-trial-version-select-modal-submit' ).on( 'click', function() {

		var selected_version = jQuery( 'input[name="rtb-trial-version"]:checked' ).val();

		if ( selected_version == 'ultimate' ) { jQuery( 'input[name="plugin_name"]').val( 'RTU' ); }
		
		jQuery( '#fsp-trial-form' ).submit();
	});
});


// About Us Page
jQuery( document ).ready( function( $ ) {

	jQuery( '.rtb-about-us-tab-menu-item' ).on( 'click', function() {

		jQuery( '.rtb-about-us-tab-menu-item' ).removeClass( 'rtb-tab-selected' );
		jQuery( '.rtb-about-us-tab' ).addClass( 'rtb-hidden' );

		var tab = jQuery( this ).data( 'tab' );

		jQuery( this ).addClass( 'rtb-tab-selected' );
		jQuery( '.rtb-about-us-tab[data-tab="' + tab + '"]' ).removeClass( 'rtb-hidden' );
	} );

	jQuery( '.rtb-about-us-send-feature-suggestion' ).on( 'click', function() {

		var feature_suggestion = jQuery( '.rtb-about-us-feature-suggestion textarea' ).val();
		var email_address = jQuery( '.rtb-about-us-feature-suggestion input[name="feature_suggestion_email_address"]' ).val();
	
		var params = {};

		params.nonce  				= rtb_admin.nonce;
		params.action 				= 'rtb_send_feature_suggestion';
		params.feature_suggestion	= feature_suggestion;
		params.email_address 		= email_address;

		var data = jQuery.param( params );
		jQuery.post( ajaxurl, data, function() {} );

		jQuery( '.rtb-about-us-feature-suggestion' ).prepend( '<p>Thank you, your feature suggestion has been submitted.' );
	} );
} );


//SETTINGS PREVIEW SCREENS

jQuery( document ).ready( function() {

	jQuery( '.rtb-settings-preview' ).prevAll( 'h2' ).hide();
	jQuery( '.rtb-settings-preview' ).prevAll( '.sap-tutorial-toggle' ).hide();
	jQuery( '.rtb-settings-preview .sap-tutorial-toggle' ).hide();
});

// NEW PLUGIN NOTICE

jQuery( document ).ready( function( $ ) {

  jQuery(document).on( 'click', '.ait-iat-new-plugin-notice .notice-dismiss', function( event ) {
    var data = jQuery.param({
      action: 'rtb_hide_new_plugin_notice',
      plugin: 'ait_iat',
      nonce: rtb_admin.nonce
    });

    jQuery.post( ajaxurl, data, function() {} );
  });
});
