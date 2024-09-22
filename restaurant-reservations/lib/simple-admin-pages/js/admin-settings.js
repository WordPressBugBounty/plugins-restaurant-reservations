jQuery(document).ready(function() {

    if ( ! jQuery.isFunction( jQuery.fn.spectrum ) ) { return; }

    jQuery('.sap-spectrum').spectrum({
        showInput: true,
        showInitial: true,
        preferredFormat: "hex",
        allowEmpty: true
    });

    jQuery('.sap-spectrum').css('display', 'inline');

    jQuery('.sap-spectrum').on('change', function() {
        if (jQuery(this).val() != "") {
            jQuery(this).css('background', jQuery(this).val());
            var rgb = EWD_SAP_hexToRgb(jQuery(this).val());
            var Brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
            if (Brightness < 100) {jQuery(this).css('color', '#ffffff');}
            else {jQuery(this).css('color', '#000000');}
        }
        else {
            jQuery(this).css('background', 'none');
        }
    });

    jQuery('.sap-spectrum').each(function() {
        if (jQuery(this).val() != "") {
            jQuery(this).css('background', jQuery(this).val());
            var rgb = EWD_SAP_hexToRgb(jQuery(this).val());
            var Brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
            if (Brightness < 100) {jQuery(this).css('color', '#ffffff');}
            else {jQuery(this).css('color', '#000000');}
        }
    });

    jQuery( 'fieldset[data-conditional_on]' ).each( function() {
        
        var option = jQuery( this );
        var option_name = jQuery( 'input[name="option_page"]' ).val() + '[' + option.data( 'conditional_on' ) + ']';

        jQuery( '[name="' + option_name + '"], [name="' + option_name + '[]"]' ).on( 'change', function() { 
            
            var conditional_on_value = String( option.data( 'conditional_on_value' ) ).split( ',' ); 

            var option_value = jQuery( this ).attr( 'type' ) != 'checkbox' ? jQuery( this ).val() : 
                                ( ( option.data( 'conditional_on_value' ) == 1 || option.data( 'conditional_on_value' ) == '' ) ? jQuery( this ).is( ':checked' ) : 
                                    ( jQuery( this ).is( ':checked' ) ? option.data( 'conditional_on_value' ) : false ) ); 
                                
            if ( jQuery.inArray( option_value, conditional_on_value ) !== -1 || ( option_value === true && conditional_on_value[0] === '1' ) ) {

                option.parent().parent().removeClass( 'sap-hidden' );
            }
             else {

                option.parent().parent().addClass( 'sap-hidden' );
            }
        });
    });

    jQuery( 'fieldset[data-setting_type]' ).each( function() {
        
        var option = jQuery( this ); 
        
        var setting_types = option.data( 'setting_type' ).toString().split(',');
        var setting_type_values = option.data( 'setting_type_value' ).toString().split(',');

        jQuery( setting_types ).each( function ( index, setting_type ) {

            jQuery( 'select[name="' + setting_type + '"]' ).on( 'change', function() { check_option_setting_display( option ); } );
        } );
    });

    jQuery( '.sap-settings-type-toggle-option select' ).on( 'change', function() {

        var referrer_url = jQuery( this ).closest( 'form' ).find( 'input[name="_wp_http_referer"]' ); console.log( referrer_url.val() );
        var [ base , params ] = referrer_url.val().split( '?' );

        search_params = new URLSearchParams( params );

        if ( ! jQuery( this ).val() ) {

            jQuery( '.sap-settings-type-toggle-option select' ).prop( 'disabled', false );

            search_params.delete( 'setting_type' );
            search_params.delete( 'setting_type_value' ); console.log( base + '?' + search_params.toString() );

            referrer_url.val( base + '?' + search_params.toString() );
        }
        else {

            jQuery( '.sap-settings-type-toggle-option select' ).prop( 'disabled', true );
            jQuery( this ).prop( 'disabled', false );

            search_params.set( 'setting_type', jQuery( this ).attr( 'name' ) );
            search_params.set( 'setting_type_value', jQuery( this ).val() ); console.log( base + '?' + search_params.toString() );

            referrer_url.val( base + '?' + search_params.toString() );
        }
    })
});

function check_option_setting_display( option ) {

    var setting_types = option.data( 'setting_type' ).toString().split(',');
    var setting_type_values = option.data( 'setting_type_value' ).toString().split(',');

    var display = true;
    
    jQuery( setting_types ).each( function ( index, setting_type ) {

        if ( jQuery( 'select[name="' + setting_type + '"]' ).val() != setting_type_values[ index ] ) { display = false; }
    } );

    if ( display ) { option.parent().parent().removeClass( 'sap-hidden' ); }
    else { option.parent().parent().addClass( 'sap-hidden' ); }
}

function EWD_SAP_hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

//OPTIONS PAGE YES/NO TOGGLE SWITCHES
jQuery(document).ready(function($){
	$('.sap-admin-option-toggle').on('change', function() {
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

/*LOCK BOXES*/
jQuery( document ).ready( function() {

    function resizeLockdownBoxes() {
        jQuery('.sap-premium-options-table-overlay').each(function(){
            
            var eachProTableOverlay = jQuery( this );
            var associatedTable = eachProTableOverlay.next();
            associatedTable.css('min-height', '260px');
            var tablePosition = associatedTable.position();

            eachProTableOverlay.css( 'width', associatedTable.outerWidth(true) + 'px' );
            eachProTableOverlay.css( 'height', associatedTable.outerHeight() + 'px' );
            eachProTableOverlay.css( 'left', tablePosition.left + 'px' );
            eachProTableOverlay.css( 'top', tablePosition.top + 'px' );
        });
    }

    setTimeout( resizeLockdownBoxes, 750 );
    setInterval( resizeLockdownBoxes, 3000 );
    jQuery( window ).on( 'resize', resizeLockdownBoxes );
});

/* TUTORIAL VIDEOS */
jQuery( document ).ready( function() {

    jQuery( '.sap-parent-form h2:first-of-type' ).wrap( '<div class="sap-heading-container"></div>' );

    if ( ! jQuery( '.sap-tutorial-div' ).length ) { return; }

    var tutorial_div = jQuery( '.sap-tutorial-div' );

    jQuery( '.sap-heading-container h2' ).after( '<div class="sap-tutorial-toggle button button-primary">Video Tutorial</div>' );

    jQuery( '.sap-tutorial-toggle' ).after( tutorial_div );

    jQuery( document ).on('click', '.sap-tutorial-toggle', function( event ) { 

        tutorial_div.toggle();
    } );
} );

/* SETTINGS TOGGLE */
jQuery( document ).ready( function() {

    if ( ! jQuery( '.sap-settings-type-toggle-div' ).length ) { return; }

    var settings_type_toggle_div = jQuery( '.sap-settings-type-toggle-div' );

    jQuery( '.sap-heading-container' ).after( '<div class="sap-settings-type-toggle-container"><div class="sap-settings-type-toggle-switch button show-settings">Global</div></div>' );

    jQuery( '.sap-settings-type-toggle-switch' ).after( settings_type_toggle_div );

    if ( ! jQuery( '.sap-settings-type-toggle-div' ).hasClass( 'sap-hidden' ) ) { jQuery( '.sap-settings-type-toggle-switch' ).addClass( 'open' ); }

    jQuery( document ).on('click', '.sap-settings-type-toggle-switch', function( event ) { 

        settings_type_toggle_div.toggleClass( 'sap-hidden' );

        jQuery( '.sap-settings-type-toggle-switch' ).toggleClass( 'open' );
    } );
} );