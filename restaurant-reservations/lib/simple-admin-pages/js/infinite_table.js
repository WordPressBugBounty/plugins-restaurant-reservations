/**
 * Javascript functions for Infinite Table
 *
 * @package Simple Admin Pages
 */

jQuery(document).ready(function ($) {

  // disable options where not required initially
  $('.sap-infinite-table table tbody tr').each((idx_tr, tr) => {
    let val = $(tr).find('[data-name="cf_type"]').val();
    
    if(!['dropdown', 'checkbox', 'radio'].includes(val)) {
      $(tr).find('[data-name="cf_options"]').val('').prop('readonly', true);
    }
  });

  // process fields
  $('.sap-parent-form').on('submit', function (ev) {
    var _form = $(this), ignore;

    $('.sap-infinite-table').each( function() {

      var main_input = $(this).find('#sap-infinite-table-main-input');

      var main_input_val = [];

      $(this).find('table tbody tr').each((idx_tr, tr) => {
        let record = {}; ignore = false;
  
        $(tr).find('td').each((idx_td, td) => {
          let elm = $(td).find('select, input, textarea, checkbox');
  
          ignore =  'cf_field_name' == elm.data('name') && elm.val().length < 1 ? true : ignore;
  
          if(!ignore) {
            
            if ( elm.prop( 'type' ) == 'checkbox' ) { record[ elm.data('name') ] = elm.is( ':checked' ); }
            else { record[elm.data('name')] = elm.val(); }
          }
        });
  
        !ignore ? main_input_val.push(record) : null;
      }); 

      main_input.val(JSON.stringify(main_input_val));

    });
  });

  // Add new field
  $('.sap-infinite-table-add-row .sap-new-admin-add-button').on('click', function (ev) { 
    let id_field = 1;
    let _list = [];
    $( this ).parents( 'tfoot' ).siblings( 'tbody' ).find( 'tr td' ).each((i, x) => {
      let f_type = $(x).data( 'field-type' );
      if( 'id' == f_type ) {
        _list.push( parseInt( $(x).find( '.sap-infinite-table-id-html' ).eq(0).html() ) );
      }
    });

    _list.sort( function( a, b ) { return a - b; } );
    if( 0 < _list.length ) {
      id_field = _list[ _list.length - 1 ] + 1;
    }

    let row_id = 0;
    $( this ).parents( 'tfoot' ).siblings( 'tbody' ).find( 'tr' ).each((i, x) => {
      let existing_id = parseInt( $(x).attr( 'data-row_id' ), 10 );
      if ( ! isNaN( existing_id ) ) { row_id = Math.max( row_id, existing_id + 1 ); }
    });
    
    let _template_tr = $( this ).parents( 'tfoot' ).find( '.sap-infinite-table-row-template' ).clone();
    _template_tr
      .hide()
      .removeClass()
      .addClass( 'sap-infinite-table-row' );
    
    $( this ).parents( 'table' ).first().find( 'tbody' ).append( _template_tr );
    _template_tr.attr( 'data-row_id', row_id );
    _template_tr.find( '.sap-infinite-table-id-html' ).eq(0).siblings( 'input' ).val( id_field );
    _template_tr.find( '.sap-infinite-table-id-html' ).eq(0).html( id_field );
    _template_tr.fadeIn( 'fast' );
    _template_tr.find( '[data-name="cf_options"]' ).prop( 'readonly' , true );
      
  });

  // update options field
  $(document).on('change', '.sap-infinite-table-row [data-name="cf_type"]', function (ev) {
    let parent_tr = $(this).parents('tr').eq(0);
    
    if(!['dropdown', 'checkbox', 'radio'].includes($(this).val())) {
      parent_tr.find('[data-name="cf_options"]').val('').prop('readonly', true);
    }
    else {
      parent_tr.find('[data-name="cf_options"]').prop('readonly', false);
    }
  });

  // open/close an editor field and sync data with that row's hidden field
  $(document).on('click', 'td[data-field-type="editor"]', function (ev) {
    ev.preventDefault();
    if ( $(this).closest( '.sap-infinite-table' ).hasClass( 'disabled' ) ) { return; }
    let fieldset = $(this).parents( 'fieldset' ).first();

    let tiny_mce_div = fieldset.find( '.sap-infinite-table-editor-container' ).first();

    tiny_mce_div.removeClass( 'sap-hidden' );
    // Row numbers are only table-local metadata. Bind this editing session
    // to the actual cell so reordering, duplicate IDs and other tables are safe.
    tiny_mce_div.data( 'target_cell', this );

    let editor_id = tiny_mce_div.data( 'editor_id' ); 

    if ( $( '#wp-' + editor_id + '-wrap' ).hasClass( 'tmce-active' ) ) {
      tinyMCE.get(editor_id).setContent( $(this).find( 'input' ).first().val() ); 
    }
    else {
      jQuery( '#' + editor_id ).val( $(this).find( 'input' ).first().val() );
    } 
  });

  $(document).on('click', '.sap-infinite-table-editor-save', function (ev) {

    let tiny_mce_div = $(this).parents( 'div.sap-infinite-table-editor-container' ).first();

    let editor_id = tiny_mce_div.data('editor_id');
    let fieldset = $(this).parents( 'fieldset' ).first();
    let cell = $( tiny_mce_div.data( 'target_cell' ) );

    // Never fall back to a row-number lookup if the edited cell was removed.
    if ( ! fieldset.length || ! cell.length || ! $.contains( fieldset[0], cell[0] ) || cell.closest( '.sap-infinite-table' ).hasClass( 'disabled' ) ) {
      tiny_mce_div.addClass( 'sap-hidden' ).removeData( 'target_cell' );
      return;
    }
    
    let content = $( '#wp-' + editor_id + '-wrap' ).hasClass( 'tmce-active' ) ? tinyMCE.get( editor_id ).getContent() : jQuery( '#' + editor_id ).val();
    
    // Template contents are inert; render only text, never executable preview HTML.
    let preview = document.createElement( 'template' );
    preview.innerHTML = content;
    preview.content.querySelectorAll( 'script, style' ).forEach( node => node.remove() );
    let text = preview.content.textContent || '';
    cell.find( '.sap-infinite-table-editor-value' ).first().text( text.slice( 0, 60 ) + ( text.length > 60 ? '...' : '' ) );
    cell.find( '.sap-infinite-table-editor-input' ).first().val( content );

    tiny_mce_div.addClass( 'sap-hidden' ).removeData( 'target_cell' );
  });

  $( document ).on( 'click', '.sap-infinite-table-editor-cancel', function ( ev ) {

    $(this).closest( '.sap-infinite-table-editor-container' ).addClass( 'sap-hidden' ).removeData( 'target_cell' );
  } );

  // Remove field
  $(document).on('click', '.sap-infinite-table-row .sap-infinite-table-row-delete', function (ev) {
    let parent_tr = $(this).parents('tr').eq(0);
    parent_tr.fadeOut('fast', () => parent_tr.remove());
  });

  $('.sap-infinite-table table tbody').sortable({
    axis: 'y'
  });

  // Handle conditional field display 
  jQuery( 'span.sap-infinite-table-td-content[data-conditional_on]' ).each( function() {
        
        var field = jQuery( this );
        var row = field.closest( 'tr' );
        
        row.find( '[data-name="' + field.data( 'conditional_on' ) + '"]' ).on( 'change', function() {

          var conditional_on_value = String( field.data( 'conditional_on_value' ) ).split( ',' );
            
            var field_value = jQuery( this ).attr( 'type' ) != 'checkbox' ? jQuery( this ).val() : 
                                ( ( option.data( 'conditional_on_value' ) == 1 || option.data( 'conditional_on_value' ) == '' ) ? jQuery( this ).is( ':checked' ) : 
                                    ( jQuery( this ).is( ':checked' ) ? option.data( 'conditional_on_value' ) : false ) );

            if ( jQuery.inArray( field_value, conditional_on_value ) !== -1 || ( field_value === true && conditional_on_value[0] === '1' ) ) {

                field.removeClass( 'sap-hidden' );
            }
             else {

                field.addClass( 'sap-hidden' );
            }
        });
    });

})
