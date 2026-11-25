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
