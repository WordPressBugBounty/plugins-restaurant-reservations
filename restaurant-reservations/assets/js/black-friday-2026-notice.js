/* global jQuery, rtb_bf2026_notice */
(function( $ ) {
	'use strict';
	if ( typeof rtb_bf2026_notice === 'undefined' ) { return; }
	$( function() {
		$( '.rtb-bf2026-notice' ).on( 'click', '.rtb-bf2026-dismiss', function() {
			var notice = $( this ).closest( '.rtb-bf2026-notice' );
			var buttons = notice.find( '.rtb-bf2026-dismiss' );
			var status = notice.find( '.rtb-bf2026-status' );
			var mode = $( this ).attr( 'data-dismissal' );
			buttons.prop( 'disabled', true );
			notice.attr( 'aria-busy', 'true' );
			status.text( '' );
			function failed( response ) {
				buttons.prop( 'disabled', false );
				notice.removeAttr( 'aria-busy' );
				status.text( response && response.data && response.data.message ? response.data.message : rtb_bf2026_notice.error );
			}
			$.ajax( {
				url: rtb_bf2026_notice.url,
				method: 'POST',
				dataType: 'json',
				data: { action: rtb_bf2026_notice.action, nonce: rtb_bf2026_notice.nonce, dismissal: mode }
			} ).done( function( response ) {
				if ( response && response.success ) { notice.remove(); }
				else { failed( response ); }
			} ).fail( function() { failed(); } );
		} );
	} );
})( jQuery );
