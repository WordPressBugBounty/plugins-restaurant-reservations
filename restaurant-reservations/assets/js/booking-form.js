/* Javascript for Restaurant Reservations booking form */

var rtb_booking_form = rtb_booking_form || {};

jQuery(document).ready(function ($) {

	/**
	 * Initialize the booking form when loaded
	 */
	rtb_booking_form.init = function() {

		rtb_pickadate.init_complete = false;

		// Scroll to the first error message on the booking form
		if ( $( '.rtb-booking-form .rtb-error' ).length ) {
			$('html, body').animate({
				scrollTop: $( '.rtb-booking-form .rtb-error' ).first().offset().top + -40
			}, 500);
		}

		// Show the message field on the booking form
		$( '.rtb-booking-form .add-message a' ).click( function() {
			$(this).hide();
			$(this).parent().siblings( '.message' ).addClass( 'message-open' )
				.find( 'label' ).focus();

			return false;
		});

		// Show the message field on load if not empty
		if ( $.trim( $( '.rtb-booking-form .message textarea' ).val() ) ) {
			$( '.rtb-booking-form .add-message a' ).trigger( 'click' );
		}

		// Disable the submit button when the booking form is submitted
		$( '.rtb-booking-form form' ).submit( function() {
			$(this).find( 'button[type="submit"]' ).prop( 'disabled', 'disabled' );
			return true;
		} );

		// Remove required attribute from all checkboxes in a group if one is checked
		$( '.rtb-booking-form form button[type="submit"]' ).on( 'click', function() {

			$( '.rtb-booking-form form input[required]:checkbox' ).each( function() {
				 var checkbox_name = $( this ).attr('name');

				 var checkbox_group = $( '.rtb-booking-form form input[name="' + checkbox_name + '"]' );

				 if ( checkbox_group.is( ':checked' ) ) {

				 	checkbox_group.prop( 'required', false );
				 }
			});
		});

		// Enable datepickers on load
		if ( typeof rtb_pickadate !== 'undefined' ) {

			// Declare datepicker
			var $date_input = $( '#rtb-date' );
			if ( $date_input.length ) {
				var date_input = $date_input.pickadate({
					format: rtb_pickadate.date_format,
					formatSubmit: 'yyyy/mm/dd',
					hiddenName: true,
					min: !rtb_pickadate.allow_past,
					container: 'body',
					firstDay: rtb_pickadate.first_day,
					today: rtb_pickadate.date_today_label,
					clear: rtb_pickadate.date_clear_label,
					close: rtb_pickadate.date_close_label,

					onStart: function() {

						// Block dates beyond early bookings window
						if ( rtb_pickadate.early_bookings !== '' ) {
							this.set( 'max', parseInt( rtb_pickadate.early_bookings, 10 ) );
						}

						// Select the value when loaded if a value has been set
						if ( $date_input.val()	!== '' ) {
							var date = new Date( $date_input.val() );
							if ( Object.prototype.toString.call( date ) === "[object Date]" ) {
								this.set( 'select', date );
							}
						}
						else if ( jQuery( '.rtb-selected-date' ).length ) {
							var date = new Date( jQuery( '.rtb-selected-date' ).val() );
							if ( Object.prototype.toString.call( date ) === "[object Date]" ) {
								this.set( 'select', date );
							}
						}
					}
				});

				rtb_booking_form.datepicker = date_input.pickadate( 'picker' );
			}

			// Declare timepicker
			var $time_input = $( '#rtb-time' );
			if ( $time_input.length ) {
				var time_input = $time_input.pickatime({
					format: rtb_pickadate.time_format,
					formatSubmit: 'h:i A',
					hiddenName: true,
					interval: parseInt( rtb_pickadate.time_interval, 10 ),
					container: 'body',
					clear: rtb_pickadate.time_clear_label,

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

				rtb_booking_form.timepicker = time_input.pickatime( 'picker' );
			}

			// We need to check both to support different jQuery versions loaded
			// by older versions of WordPress. In jQuery v1.10.2, the property
			// is undefined. But in v1.11.3 it's set to null.
			if ( rtb_booking_form.datepicker === null || typeof rtb_booking_form.datepicker == 'undefined' ) {
				return;
			}

			// Update disabled dates
			rtb_booking_form.update_disabled_dates();

			if ( typeof rtb_pickadate.late_bookings === 'string' ) {
				if ( rtb_pickadate.late_bookings == 'same_day' ) {
					rtb_booking_form.datepicker.set( 'min', 1 );
				} else if ( rtb_pickadate.late_bookings !== '' ) {
					rtb_pickadate.late_bookings = parseInt( rtb_pickadate.late_bookings, 10 );
					if ( rtb_pickadate.late_bookings % 1 === 0 && rtb_pickadate.late_bookings >= 1440 ) {
						var min = Math.floor( rtb_pickadate.late_bookings / 1440 );
						rtb_booking_form.datepicker.set( 'min', min );
					}
				}
			}

			// If no date has been set, select today's date if it's a valid
			// date. User may opt not to do this in the settings.
			if ( $date_input.val() === '' && !$( '.rtb-booking-form .date .rtb-error' ).length ) {

				if ( rtb_pickadate.date_onload == 'soonest' ) {
					rtb_booking_form.datepicker.set( 'select', new Date() );
				} else if ( rtb_pickadate.date_onload !== 'empty' ) {
					var dateToVerify = rtb_booking_form.datepicker.component.create( new Date() );
					var isDisabled = rtb_booking_form.datepicker.component.disabled( dateToVerify );
					if ( !isDisabled ) {
						rtb_booking_form.datepicker.set( 'select', dateToVerify );
					}
				}
			}

			if ( rtb_booking_form.timepicker === null || typeof rtb_booking_form.timepicker == 'undefined' ) {
				return;
			}

			// Update timepicker on pageload and whenever the datepicker is closed
			rtb_booking_form.update_timepicker_range();
			rtb_booking_form.datepicker.on( {
				open: function () {
					
					rtb_booking_form.before_change_value = rtb_booking_form.datepicker.get();
				},

				close: function() {

					rtb_booking_form.after_change_value = rtb_booking_form.datepicker.get();

					if(rtb_booking_form.before_change_value != rtb_booking_form.after_change_value) {
						// clear time value if date changed
						rtb_booking_form.timepicker.clear();
					}

					rtb_booking_form.update_timepicker_range();
					rtb_booking_form.update_party_size_select();
					rtb_booking_form.update_possible_tables();
				}
			});

			rtb_booking_form.timepicker.on( {
				close: function() {
					rtb_booking_form.update_party_size_select();
					rtb_booking_form.update_possible_tables();
				}
			});

			$( '#rtb-party' ).on( 'change', function() {
				rtb_booking_form.update_possible_tables();
			});

			$( '#rtb-location' ).on( 'change', function() {

				if ( ! rtb_pickadate.multiple_locations_enabled ) { return; }

				rtb_booking_form.timepicker.clear();
				rtb_booking_form.datepicker.clear();

				rtb_booking_form.update_base_data_for_selected_location();

				rtb_booking_form.update_datepicker();

				rtb_booking_form.update_timepicker_range();

				rtb_booking_form.update_party_size_select();

				rtb_booking_form.update_possible_tables();
			});

			if ( 'rtb-bookings' != ( new URL( window.location.href ) ).searchParams.get( 'page' ) ) { rtb_booking_form.update_possible_tables(); }
		}
	};

	/**
	 * Update base data for date/time picker as per the selected location
	 * @return object
	 */
	rtb_booking_form.update_base_data_for_selected_location = function () {
		const selected_location = jQuery( '#rtb-location' ).length ? jQuery( '#rtb-location' ).val() : '';
		
		if( '' == selected_location ) {
			// Set global settings
			return Object.assign( rtb_pickadate, rtb_location_data.global );
		}

		return Object.assign( rtb_pickadate, rtb_location_data[selected_location] );
	}

	rtb_booking_form.update_datepicker = function () {
		
		// Reset enabled/disabled rules on this datepicker
		rtb_booking_form.datepicker.set( 'enable', false );
		rtb_booking_form.datepicker.set( 'disable', false );

		rtb_booking_form.update_disabled_dates();
	}

	/**
	 * Update datepicker to change the disabled dates based on location
	 */
	rtb_booking_form.update_disabled_dates = function() {

		// Pass conditional configuration parameters
		if ( rtb_pickadate.disable_dates.length ) {

			var disable_dates = jQuery.extend( true, [], rtb_pickadate.disable_dates );

			// Update weekday dates if start of the week has been modified
			if ( typeof rtb_booking_form.datepicker.component.settings.firstDay == 'number' ) {
				var weekday_num = 0;
				for ( var disable_key in rtb_pickadate.disable_dates ) {
					if ( typeof rtb_pickadate.disable_dates[disable_key] == 'number' ) {
						weekday_num = rtb_pickadate.disable_dates[disable_key] - rtb_booking_form.datepicker.component.settings.firstDay;
						if ( weekday_num < 1 ) {
							weekday_num = 7;
						}
						disable_dates[disable_key] =  weekday_num;
					}
				}
			}

			rtb_booking_form.datepicker.set( 'disable', disable_dates );

		}
	}

	/**
	 * Update the timepicker's range based on the currently selected date
	 */
	rtb_booking_form.update_timepicker_range = function() {

		// Reset enabled/disabled rules on this timepicker
		rtb_booking_form.timepicker.set( 'enable', false );
		rtb_booking_form.timepicker.set( 'disable', false );

		if ( rtb_booking_form.datepicker.get() === '' ) {
			rtb_booking_form.timepicker.set( 'disable', true );
			return;
		}

		var selected_date = new Date( rtb_booking_form.datepicker.get( 'select', 'yyyy/mm/dd' ) ),
			selected_date_year = selected_date.getFullYear(),
			selected_date_month = selected_date.getMonth(),
			selected_date_date = selected_date.getDate(),
			current_date = new Date();

		selected_date.setHours(0, 0, 0), selected_date.setMilliseconds(100);

		// Declaring the first element true inverts the timepicker settings. All
		// times subsequently declared are valid. Any time that doesn't fall
		// within those declarations is invalid.
		// See: http://amsul.ca/pickadate.js/time/#disable-times-all
		var valid_times = [ rtb_booking_form.get_outer_time_range() ];

		if ( rtb_pickadate.enable_max_reservations || rtb_pickadate.multiple_locations_enabled ) {
			selected_location = jQuery( '#rtb-location' ).length ? jQuery( '#rtb-location' ).val() : '';

			let hidden_location = jQuery('.rtb-booking-form-form input[name="rtb-location"]');
			if('' == selected_location && hidden_location.length ) {
				selected_location = hidden_location.val();
			}

			selected_date_month = ('0' + (selected_date_month + 1)).slice(-2);
			selected_date_date = ('0' + selected_date_date).slice(-2);

			var params = {};

			params.action = 'rtb_get_available_time_slots';
			params.nonce  = rtb_booking_form_js_localize.nonce;
			params.year   = selected_date_year;
			params.month  = selected_date_month;
			params.day    = selected_date_date;
			params.location = selected_location;

			var data = jQuery.param( params );
			jQuery.post( ajaxurl, data, function( response ) {

				if( rtb_pickadate.init_complete ) {
					clearPrevFieldError( 'date' );
					clearPrevFieldError( 'time' );
				}

				if ( ! response ) {
					clearPrevFieldError( 'date' );
					displayFieldError( 'date', rtb_booking_form_js_localize.error['smthng-wrng-cntct-us'] );
					rtb_booking_form.timepicker.set( 'disable', true );

					return;
				}

				var additional_valid_times = jQuery.parseJSON( response );

				// If today is all day open, only add one valid date/time rule
				let outer_range = valid_times[0];
				if(
					additional_valid_times.length == 1
					&& additional_valid_times[0].from[0] == outer_range.from[0]
					&& additional_valid_times[0].from[1] == outer_range.from[1]
					&& additional_valid_times[0].to[0] == outer_range.to[0]
					&& additional_valid_times[0].to[1] == outer_range.to[1] ) {
					var all_valid_times = [ additional_valid_times[0] ];
				}
				else {
					var all_valid_times = valid_times.concat( additional_valid_times );
				}

				if( !Array.isArray( additional_valid_times ) || 1 > additional_valid_times.length ) {
					clearPrevFieldError( 'time' );
					displayFieldError( 'time', rtb_booking_form_js_localize.error['no-slots-available'] );
				}

				jQuery( all_valid_times ).each( function( index, valid_time ) {
					valid_time.to = rtb_booking_form.get_latest_viable_time( parseInt( valid_time.to[0] ), parseInt( valid_time.to[1] ) );
				} );

				rtb_booking_form.timepicker.set( 'disable', all_valid_times );
				rtb_pickadate.init_complete = true;
			})
			.fail(function( args ) {
				clearPrevFieldError( 'date' );
				displayFieldError( 'date', rtb_booking_form_js_localize.error['smthng-wrng-cntct-us'] );
				rtb_booking_form.timepicker.set( 'disable', true );

				return;
			});
		}

		else {
			// Check if this date is an exception to the rules
			if ( typeof rtb_pickadate.schedule_closed !== 'undefined' ) {

				var excp_date = [];
				var excp_start_date = [];
				var excp_start_time = [];
				var excp_end_date = [];
				var excp_end_time = [];
				for ( var closed_key in rtb_pickadate.schedule_closed ) {

					let rule = rtb_pickadate.schedule_closed[closed_key];
					if( rule.hasOwnProperty('date_range') ) {
						let start = '' != rule.date_range.start ? new Date( rule.date_range.start ) : new Date();
						start.setHours(0, 0, 0), start.setMilliseconds(0);
						start = start.getTime();

						let end = '' != rule.date_range.end ? new Date( rule.date_range.end ) : 9999999999999;
						'number' != typeof end && end.setHours(23, 59, 58) && end.setMilliseconds(0);
						end = 'number' != typeof end ? end.getTime() : end;

						if( start < selected_date.getTime() && selected_date.getTime() < end ) {
							excp_date = selected_date;
						}
						else {
							// Set anything to void this rule
							// Dates assign with copy, thus creating a new one
							excp_date = new Date( selected_date.getTime() );
							excp_date.setDate( selected_date_year + 1 );
						}
					}
					else {
						excp_date = new Date( rule.date );
					}

					if ( excp_date.getFullYear() == selected_date_year &&
							excp_date.getMonth() == selected_date_month &&
							excp_date.getDate() == selected_date_date
							) {

						// Closed all day
						if ( typeof rtb_pickadate.schedule_closed[closed_key].time == 'undefined' ) {
							rtb_booking_form.timepicker.set( 'disable', [ true ] );

							return;
						}

						if ( typeof rtb_pickadate.schedule_closed[closed_key].time.start !== 'undefined' ) {
							excp_start_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_closed[closed_key].time.start );
							excp_start_time = [ excp_start_date.getHours(), excp_start_date.getMinutes() ];
						} else {
							excp_start_time = [ 0, 0 ]; // Start of the day
						}

						if ( typeof rtb_pickadate.schedule_closed[closed_key].time.end !== 'undefined' ) {
							excp_end_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_closed[closed_key].time.end );
							excp_end_time = [ excp_end_date.getHours(), excp_end_date.getMinutes() ];
						} else {
							excp_end_time = [ 24, 0 ]; // End of the day
						}

						excp_start_time = rtb_booking_form.get_earliest_time( excp_start_time, selected_date, current_date );

						valid_times.push( { from: excp_start_time, to: excp_end_time, inverted: true } );
					}
				}

				excp_date = excp_start_date = excp_start_time = excp_end_date = excp_end_time = null;

				// Exit early if this date is an exception
				if ( valid_times.length > 1 ) {
					rtb_booking_form.timepicker.set( 'disable', valid_times );

					return;
				}
			}

			// Get any rules which apply to this weekday
			if ( typeof rtb_pickadate.schedule_open != 'undefined' ) {
	
				var selected_date_weekday = selected_date.getDay();
	
				var weekdays = {
					sunday: 0,
					monday: 1,
					tuesday: 2,
					wednesday: 3,
					thursday: 4,
					friday: 5,
					saturday: 6,
				};
	
				var rule_start_date = [];
				var rule_start_time = [];
				var rule_end_date = [];
				var rule_end_time = [];
				for ( var open_key in rtb_pickadate.schedule_open ) {
	
					if ( typeof rtb_pickadate.schedule_open[open_key].weekdays !== 'undefined' ) {
						for ( var weekdays_key in rtb_pickadate.schedule_open[open_key].weekdays ) {
							if ( weekdays[weekdays_key] == selected_date_weekday ) {
	
								// Closed all day
								if ( typeof rtb_pickadate.schedule_open[open_key].time == 'undefined' ) {
									rtb_booking_form.timepicker.set( 'disable', [ true ] );
	
									return;
								}
	
								if ( typeof rtb_pickadate.schedule_open[open_key].time.start !== 'undefined' ) {
									rule_start_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_open[open_key].time.start );
									rule_start_time = [ rule_start_date.getHours(), rule_start_date.getMinutes() ];
								} else {
									rule_start_time = [ 0, 0 ]; // Start of the day
								}
	
								if ( typeof rtb_pickadate.schedule_open[open_key].time.end !== 'undefined' ) {
									rule_end_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_open[open_key].time.end );
									rule_end_time = rtb_booking_form.get_latest_viable_time( rule_end_date.getHours(), rule_end_date.getMinutes() );
								} else {
									rule_end_time = [ 24, 0 ]; // End of the day
								}
	
								rule_start_time = rtb_booking_form.get_earliest_time( rule_start_time, selected_date, current_date );
	
								valid_times.push( { from: rule_start_time, to: rule_end_time, inverted: true } );
	
							}
						}
					}
				}
	
				rule_start_date = rule_start_time = rule_end_date = rule_end_time = null;
	
				// Pass any valid times located
				if ( valid_times.length > 1 ) {
					rtb_booking_form.timepicker.set( 'disable', valid_times );
	
					return;
				}
	
			}

			// Set it to always open if no rules have been defined
			rtb_booking_form.timepicker.set( 'enable', true );
			rtb_booking_form.timepicker.set( 'disable', false );
		}

		return;
	};

	/**
	 * Get the outer times to exclude based on the time interval
	 *
	 * This is a work-around for a bug in pickadate.js
	 * See: https://github.com/amsul/pickadate.js/issues/614
	 */
	rtb_booking_form.get_outer_time_range = function() {

		var interval = rtb_booking_form.timepicker.get( 'interval' );

		var hour = 0;
		var minutes = 0;

		while ( ( hour + ( minutes / 60) + ( interval / 60 ) ) < 24 ) {
			hour += Math.floor( ( minutes + interval ) / 60 );
			minutes = ( minutes + interval ) % 60;
		}

		return { from: [0, 0], to: [ hour, minutes ] };
	};

	/**
	 * Get the latest working opening hour/minute value
	 *
	 * This is a workaround for a bug in pickadate.js. The end time of a valid
	 * time value must NOT fall within the last timepicker interval and midnight
	 * See: https://github.com/amsul/pickadate.js/issues/614
	 */
	rtb_booking_form.get_latest_viable_time = function( hour, minute ) {

		var interval = rtb_booking_form.timepicker.get( 'interval' );

		var outer_time_range = this.get_outer_time_range(); 

		/* 
		* Adjust the last time for wide intervals, so that the last time entered
		* corresponds to an interval time. A pickadate bug causes a later time to
		* be available for booking otherwise.
		 */
		if ( interval > 60) {

			var last_hour = 0;
			var last_minute = 0;
			var last_time_minutes = 0;

			var end_time_minutes = 60 * hour + minute;

			while ( ( last_time_minutes + interval ) <= end_time_minutes ) {
				
				var remainder = interval + last_minute;

				while ( remainder >= 60 ) {
					last_hour++;
					remainder -= 60;
				}

				last_minute = remainder;

				last_time_minutes = 60 * last_hour + last_minute;
			}

			var long_interval_viable_time = [ last_hour, last_minute ];
		}

		if ( interval > 60 ) {
			
			return long_interval_viable_time;
		}
		else if ( hour > outer_time_range.to[0] && minute > outer_time_range.to[1] ) {
			
			return [ outer_time_range.to[0], outer_time_range.to[1] ];
		} else {
			
			return [ hour, minute ];
		}
	};

	/**
	 * Get the earliest valid time
	 *
	 * This checks the valid time for the day and, if a current day, applies
	 * any late booking restrictions. It also ensures that times in the past
	 * are not availabe.
	 *
	 * @param array start_time
	 * @param array selected_date
	 * @param array current_date
	 */
	rtb_booking_form.get_earliest_time = function( start_time, selected_date, current_date ) {

		// Only make adjustments for current day selections
		if ( selected_date.toDateString() !== current_date.toDateString() ) {
			return start_time;
		}

		// Get the number of minutes after midnight to compare
		var start_minutes = ( start_time[0] * 60 ) + start_time[1],
			current_minutes = ( current_date.getHours() * 60 ) + current_date.getMinutes(),
			late_booking_minutes;

		start_minutes = start_minutes > current_minutes ? start_minutes : current_minutes;

		if ( typeof rtb_pickadate.late_bookings === 'number' && rtb_pickadate.late_bookings % 1 === 0 ) {
			late_booking_minutes = current_minutes + rtb_pickadate.late_bookings;
			if ( late_booking_minutes > start_minutes ) {
				start_minutes = late_booking_minutes;
			}
		}

		start_time = [ Math.floor( start_minutes / 60 ), start_minutes % 60 ];

		return start_time;
	};

	rtb_booking_form.update_party_size_select = function() {
		
		if ( rtb_pickadate.enable_max_reservations && ( rtb_pickadate.max_people || rtb_pickadate.multiple_locations_enabled ) ) {
			var partySelect = $('#rtb-party'),
			selected_location = jQuery( '#rtb-location' ).length ? jQuery( '#rtb-location' ).val() : '',
			selected_date = new Date( rtb_booking_form.datepicker.get( 'select', 'yyyy/mm/dd' ) ),
			selected_date_year = selected_date.getFullYear(),
			selected_date_month = selected_date.getMonth(),
			selected_date_date = selected_date.getDate(),
			selected_time = rtb_booking_form.timepicker.get('value');

			if ( ! selected_time ) { return; }

			selected_date_month = ('0' + (selected_date_month + 1)).slice(-2);
			selected_date_date = ('0' + selected_date_date).slice(-2);

			//reset party size
			partySelect.prop("selectedIndex", 0).change();

			var params = {};

			params.action = 'rtb_get_available_party_size';
			params.nonce  = rtb_booking_form_js_localize.nonce;
			params.year   = selected_date_year;
			params.month  = selected_date_month;
			params.day    = selected_date_date;
			params.time   = selected_time;
			params.location = selected_location;

			var data = jQuery.param( params );
			jQuery.post( ajaxurl, data, function( response ) {
				if ( ! response ) {
					return;
				}

				response = jQuery.parseJSON(response);

				var available_spots = response.available_spots;

				partySelect.prop('disabled', false);

				if ( rtb_booking_form_js_localize.is_admin && rtb_booking_form_js_localize.admin_ignore_maximums ) {

					partySelect.find('> option').prop( 'disabled', false );
				}
				else {

					partySelect.find('> option').each(function() {
						var that = $(this); 
						if (this.value > available_spots) {
							that.prop('disabled', true);
						} else {
							that.prop('disabled', false);
						}
					});
				}
			});
		}
	}

	rtb_booking_form.update_possible_tables = function() {
		
		if ( rtb_pickadate.enable_tables ) { 

			var table_select = $('#rtb-table'),
			party = $('#rtb-party').val(),
			selected_location = jQuery( '#rtb-location' ).length ? jQuery( '#rtb-location' ).val() : '',
			selected_date = new Date( rtb_booking_form.datepicker.get( 'select', 'yyyy/mm/dd' ) ),
			selected_date_year = selected_date.getFullYear(),
			selected_date_month = selected_date.getMonth(),
			selected_date_date = selected_date.getDate(),
			selected_time = rtb_booking_form.timepicker.get('value');

			let hidden_location = jQuery('.rtb-booking-form-form input[name="rtb-location"]');
			
			if ( '' == selected_location && hidden_location.length ) {

				selected_location = hidden_location.val();
			}

			if ( ! selected_time || ! party || party == 0 ) { return; }

			selected_date_month = ('0' + (selected_date_month + 1)).slice(-2);
			selected_date_date = ('0' + selected_date_date).slice(-2);

			table_select.prop('disabled', true);

			//reset table selection
			table_select.prop("selectedIndex", 0).change();

			var booking_id = $( '.rtb-booking-form form input[name="ID"]').length ? $( '.rtb-booking-form form input[name="ID"]').val() : 0;

			var params = {};

			params.action = 'rtb_get_available_tables';
			params.nonce  = rtb_booking_form_js_localize.nonce;
			params.year   = selected_date_year;
			params.month  = selected_date_month;
			params.day    = selected_date_date;
			params.time   = selected_time;
			params.party  = party;
			params.booking_id = booking_id
			params.location_id = selected_location;

			var data = jQuery.param( params );
			jQuery.post( ajaxurl, data, function( response ) {
				
				if ( ! response ) { return; }

				//remove tables
				table_select.find('> option').remove();

				table_select.prop('disabled', false);

				response = jQuery.parseJSON(response);

				var available_tables = response.available_tables;

				if( 1 > available_tables.length ) {
					displayFieldError( 'table', rtb_booking_form_js_localize.error['no-table-available'] );
				}
				else {
					clearPrevFieldError( 'table' );
				}

				if ( rtb_booking_form_js_localize.is_admin ) {
					table_select.append( '<option></option>' );
				}

				jQuery.each(available_tables, function(index, element) {

					table_select.append( '<option value="' + index + '">' + element + '</option>' );
				});

				// pre-select table if it was selected before and is available
				if ( response.selected_table != -1 ) {
					table_select.val( response.selected_table );
				}
				else if( '' != table_select.data('selected') ) {
					table_select.val( table_select.data('selected') );
				}			
			});
		}

	}

	rtb_booking_form.init();
});

//Handle reservation modification
jQuery( document ).ready( function() {

	jQuery( '.rtb-pattern-modify-booking .rtb-modification-form' ).removeClass( 'rtb-hidden' );
	jQuery( '.rtb-pattern-modify-booking .rtb-booking-form-form' ).addClass( 'rtb-hidden' );
	jQuery( '.rtb-pattern-modify-booking .rtb-modification-toggle' ).html( rtb_booking_form_js_localize.make );

	jQuery( '.rtb-modification-toggle' ).on( 'click', function() {
		jQuery('.rtb-modification-form, .rtb-booking-form-form' ).toggleClass( 'rtb-hidden' );

		if ( jQuery( '.rtb-modification-form' ).hasClass( 'rtb-hidden' ) ) {
			jQuery( '.rtb-modification-toggle' ).html( rtb_booking_form_js_localize.want_to_modify );
		}
		else {
			jQuery( '.rtb-modification-toggle' ).html( rtb_booking_form_js_localize.make );
		}
	});

	var modify_booking = function(ev) {
		var booking_email = jQuery('input[name="rtb_modification_email"]').val();

		var params = {};

		params.action = 'rtb_find_reservations';
		params.nonce  = rtb_booking_form_js_localize.nonce;
		params.booking_email   = booking_email;

		var data = jQuery.param( params );
		jQuery.post(ajaxurl, data, function(response) {

			if (response.success) {
				var booking_html = '';
				var guest_txt = '';
				var pay_btn = '';

				jQuery(response.data.bookings).each(function( index, val) {
					pay_btn = '';
					guest_txt = val.party > 1 ? rtb_booking_form_js_localize.guests : rtb_booking_form_js_localize.guest;
					
					if('payment_pending' == val.status || 'payment_failed' == val.status) {
						pay_btn = `
							<div class="rtb-deposit-booking" data-bookingid="${val.ID}" data-bookingemail="${val.email}">
								${rtb_booking_form_js_localize.deposit}
							</div>
						`;
					}

					booking_html += '<div class="rtb-cancel-booking-div">';

					// Only show cancellation button if not passed cancellation cutoff
					if ( val.datetime_u > ( Date.now() / 1000) + rtb_booking_form_js_localize.cancellation_cutoff * 60 ) {

						booking_html += `
							<div class="rtb-cancel-booking" data-bookingid="${val.ID}" data-bookingemail="${val.email}">
								${rtb_booking_form_js_localize.cancel}
							</div>
						`;
					}

					booking_html += `
							${pay_btn}
							<div class="rtb-booking-information">${val.datetime} - ${val.party} ${guest_txt} (${val.status_lbl})</div>
						</div>
					`;
				});

				jQuery('.rtb-bookings-results').html(booking_html);

				cancellationHandler();
				delayedPaymentHandler();
			}
			else {jQuery('.rtb-bookings-results').html(response.data.msg);}
		});
	};

	jQuery(document).on('click', '.rtb-find-reservation-button', modify_booking);
	jQuery(document).on('keypress', '.rtb-modification-form input', function (ev) {
		// Capture enter key
		if(13 == ev.which) {
			ev.preventDefault();
			modify_booking(ev);
		}
	});
});

function cancellationHandler() {
	jQuery('.rtb-cancel-booking:not(.cancelled)').off('click');
	jQuery('.rtb-cancel-booking:not(.cancelled)').on('click', function() {
		var btn = jQuery(this);

		if(btn.hasClass('processing')) {
			return;
		}

		btn.addClass('processing');

		var booking_id = btn.data('bookingid');
		var booking_email = btn.data('bookingemail');

		var params = {};

		params.action = 'rtb_cancel_reservations';
		params.nonce  = rtb_booking_form_js_localize.nonce;
		params.booking_id = booking_id;
		params.booking_email = booking_email;

		var data = jQuery.param( params );
		jQuery.post(ajaxurl, data, function(response) {
			if (response.success) {
				if (response.data.hasOwnProperty('cancelled_redirect')) {
					window.location.href = response.data.cancelled_redirect;
				}
				else {
					btn.off('click');
					btn.addClass('cancelled');
					btn.text(rtb_booking_form_js_localize.cancelled);
				}
			}
			else {
				btn.parent().after(`<p class="alert error">${response.data.msg}</p>`);
			}

			btn.removeClass('processing');
		});
	});
}

function delayedPaymentHandler() {
	jQuery('.rtb-deposit-booking').off('click');
	jQuery('.rtb-deposit-booking').on('click', function() {
		var btn = jQuery(this);

		if(btn.hasClass('processing')) {
			return;
		}

		btn.addClass('processing');

		var booking_id = btn.data('bookingid');
		var booking_email = btn.data('bookingemail');

		var data = {
			'booking_id': booking_id,
			'booking_email': booking_email,
			'payment': 'rtb-delayed-deposit'
		};

		let current_loc = window.location;
		let params = new URLSearchParams();
		Object.keys( data ).map( function( param ) { params.append( param, data[ param ] ) } );

		window.location = current_loc.origin + current_loc.pathname + '?' + params.toString();

	});
}

function displayFieldError( field, message ) {

	const fieldSelector = '.'+field;
	var fieldElm = jQuery('form.rtb-booking-form-form '+fieldSelector);

	if( fieldElm.length ) {

		clearPrevFieldError( field );

		fieldElm.prepend(`
			<div class="rtb-error">${message}</div>
		`);
	}
}

function clearPrevFieldError( field ) {
	if( field_has_error( field ) ) {
		get_field_with_error( field ).each( (idx, x) => x.remove() );
	}
}

function field_has_error( field ) {
	return get_field_with_error( field ).length;
}

function get_field_with_error( field ) {
	const fieldSelector = '.'+field;
	var errorElms = jQuery('form.rtb-booking-form-form ' + fieldSelector + ' .rtb-error');

	return errorElms;
}

// Functions for the 'View Bookings' shortcode
jQuery(document).ready(function ($) {
	jQuery('.rtb-view-bookings-form-date-selector').on('change', function() {
		window.location.href = replaceUrlParam(window.location.href, 'date', jQuery(this).val());
	});

	jQuery('.rtb-view-bookings-form-location-selector').on('change', function() {
		window.location.href = replaceUrlParam(window.location.href, 'booking_location', jQuery(this).val());
	});

	jQuery('.rtb-edit-view-booking').on('click', function() {
		jQuery('.rtb-view-bookings-form-confirmation-div, .rtb-view-bookings-form-confirmation-background-div').removeClass('rtb-hidden');

		jQuery('.rtb-view-bookings-form-confirmation-div').data('bookingid', jQuery(this).data('bookingid'));

		jQuery(this).prop('checked', false);
	});

	jQuery('.rtb-view-bookings-form-confirmation-accept').on('click', function() {
		var booking_id = jQuery('.rtb-view-bookings-form-confirmation-div').data('bookingid');

		var params = {};

		params.action = 'rtb_set_reservation_arrived';
		params.nonce = rtb_booking_form_js_localize.nonce;
		params.booking = {
			'ID':	booking_id
		};

		var data = $.param( params );

		jQuery.post(ajaxurl, data, function(response) {

			if (response.success) {window.location.href = window.location.href}
			else {jQuery('.rtb-view-bookings-form-confirmation-div').html(response.data.msg);}
		});
	});

	jQuery('.rtb-view-bookings-form-confirmation-decline').on('click', function() {
		jQuery('.rtb-view-bookings-form-confirmation-div, .rtb-view-bookings-form-confirmation-background-div').addClass('rtb-hidden');
	});
	jQuery('.rtb-view-bookings-form-confirmation-background-div').on('click', function() {
		jQuery('.rtb-view-bookings-form-confirmation-div, .rtb-view-bookings-form-confirmation-background-div').addClass('rtb-hidden');
	});
	jQuery('#rtb-view-bookings-form-close').on('click', function() {
		jQuery('.rtb-view-bookings-form-confirmation-div, .rtb-view-bookings-form-confirmation-background-div').addClass('rtb-hidden');
	});
});

function replaceUrlParam(url, paramName, paramValue)
{
    if (paramValue == null) {
        paramValue = '';
    }
    var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
    if (url.search(pattern)>=0) {
        return url.replace(pattern,'$1' + paramValue + '$2');
    }
    url = url.replace(/[?#]$/,'');
    return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
}

// TABLES GRAPHIC
jQuery( document ).ready( function() {

	var tables_graphic_width = rtb_booking_form_js_localize.tables_graphic_width;
	var booking_form_width = 95 - rtb_booking_form_js_localize.tables_graphic_width;

	if ( tables_graphic_width != '' ) {
		
		jQuery( '.rtb-booking-form-with-tables-graphic.left .rtb-booking-form-form' ).css( 'width', booking_form_width+'%' );
		jQuery( '.rtb-booking-form-with-tables-graphic.right .rtb-booking-form-form' ).css( 'width', booking_form_width+'%' );
		jQuery( '.rtb-tables-graphic-container' ).css( 'width', tables_graphic_width+'%' );
	}
});
