jQuery( document ).ready( function( $ ) {

  jQuery(document).on( 'click', '.rtb-helper-install-notice .notice-dismiss', function( event ) {
    var data = jQuery.param({
      action: 'rtb_hide_helper_notice',
      nonce: rtb_helper_notice.nonce
    });

    jQuery.post( ajaxurl, data, function() {} );
  });
});

/* NEW PLUGIN NOTICE */

jQuery( document ).ready( function( $ ) {

  jQuery(document).on( 'click', '.ait-aiaa-new-plugin-notice .notice-dismiss', function( event ) {
    var data = jQuery.param({
      action: 'rtb_hide_helper_notice',
      plugin: 'ait_aiaa',
      nonce: rtb_helper_notice.nonce
    });

    jQuery.post( ajaxurl, data, function() {} );
  });
});