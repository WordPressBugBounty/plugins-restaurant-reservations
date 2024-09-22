/* Hiding empty settings sections */
jQuery( document ).ready( function() {

    jQuery( 'select[name="rtb-settings[location-select]"]' ).change(function() {

        manageHidingSections();
    });

    manageHidingSections();
});

function manageHidingSections() {

    jQuery( '.sap-settings-page .form-table' ).each(function(){

        var table = jQuery( this );
        var action = 'hide';

        if( table.find( '> tbody > tr:not(.sap-hidden)' ).length ) {
            action = 'show';
        }

        table[action]();
        if( ( ovrly = table.prev( '.sap-premium-options-table-overlay' ) ).length ) {
            ovrly[action]();
            ovrly.prev( 'h2' )[action]();
        }
        else {
            table.prev( 'h2' )[action]();
        }
    });
}


jQuery(document).ready(function() {
	jQuery('.rtb-spectrum').spectrum({
		showInput: true,
		showInitial: true,
		preferredFormat: "hex",
		allowEmpty: true
	});

	jQuery('.rtb-spectrum').css('display', 'inline');

	jQuery('.rtb-spectrum').on('change', function() {
		if (jQuery(this).val() != "") {
			jQuery(this).css('background', jQuery(this).val());
			var rgb = RTB_hexToRgb(jQuery(this).val());
			var Brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
			if (Brightness < 100) {jQuery(this).css('color', '#ffffff');}
			else {jQuery(this).css('color', '#000000');}
		}
		else {
			jQuery(this).css('background', 'none');
		}
	});

	jQuery('.rtb-spectrum').each(function() {
		if (jQuery(this).val() != "") {
			jQuery(this).css('background', jQuery(this).val());
			var rgb = RTB_hexToRgb(jQuery(this).val());
			var Brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
			if (Brightness < 100) {jQuery(this).css('color', '#ffffff');}
			else {jQuery(this).css('color', '#000000');}
		}
	});
});

function RTB_hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}
