<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'cffrtbField' ) ) {
/**
 * Field class to add, edit, save, update, validate and process a
 * custom field added to the booking form.
 *
 * @since 0.1
 */
class cffrtbField {

	/**
	 * ID
	 *
	 * @since 0.1
	 */
	public $ID;

	/**
	 * Type
	 *
	 * eg - text, options, confirm
	 *
	 * @since 0.1
	 */
	public $type;

	/**
	 * Subtype
	 *
	 * eg - text/textarea, select/checkbox/radio, confirm
	 *
	 * @since 0.1
	 */
	public $subtype;

	/**
	 * Label
	 *
	 * @since 0.1
	 */
	public $title;

	/**
	 * Slug
	 *
	 * @since 0.1
	 */
	public $slug;

	/**
	 * Required
	 *
	 * @since 0.1
	 */
	public $required;

	/**
	 * Fieldset
	 *
	 * @since 0.1
	 */
	public $fieldset;

	/**
	 * Order of the field in its fieldset
	 *
	 * @since 0.1
	 */
	public $order;

	/**
	 * Options (used in some field types)
	 *
	 * @since 0.1
	 */
	public $options;

	/**
	 * Post status
	 *
	 * @since 0.1
	 */
	public $status;

	/**
	 * Get it started
	 *
	 * @since 0.1
	 */
	public function __construct( $args = array() ) {

		// Load from the db if an ID is passed
		if ( !empty( $args['ID'] ) ) {
			$args = array_merge( $this->load_from_db( $args['ID'] ), $args );
		}

		// Set up the field object
		$this->setup_field( $args );

	}

	/**
	 * Load an object based on the arguments passed
	 *
	 * @since 0.1
	 */
	public function load_from_db( $id ) {

		$post = get_post( $id, 'ARRAY_A' );

		$post['title'] = $post['post_title'];
		$post['slug'] = $post['post_name'];
		$post['order'] = $post['menu_order'];
		$post['status'] = $post['post_status'];

		$post_meta = get_post_meta( $id, 'cffrtb', true );

		if( !empty( $post_meta['type'] ) ) { $post['type'] = $post_meta['type']; }
		if( !empty( $post_meta['subtype'] ) ) { $post['subtype'] = $post_meta['subtype']; }
		if( !empty( $post_meta['required'] ) ) { $post['required'] = $post_meta['required']; }
		if( !empty( $post_meta['fieldset'] ) ) { $post['fieldset'] = $post_meta['fieldset']; }
		if( !empty( $post_meta['options'] ) ) { $post['options'] = $post_meta['options']; }

		return $post;
	}

	/**
	 * Set up a new field object
	 *
	 * @since 0.1
	 */
	public function setup_field( $args ) {
		global $rtb_controller;

		// Use defaults for missing args
		$args = array_merge( $rtb_controller->fields->get_default_field_values(), $args );

		// Set up the object
		$this->ID = empty( $args['ID'] ) ? null : (int) $args['ID'];
		$this->type = empty( $args['type'] ) ? $this->get_valid_type( '' ) : $this->get_valid_type( $args['type'] );
		$this->subtype = empty( $args['subtype'] ) ? $this->get_valid_subtype( '' ) : $this->get_valid_subtype( $args['subtype'] );
		$this->title = empty( $args['title'] ) ? null : sanitize_text_field( $args['title'] );
		$this->slug = empty( $args['slug'] ) ? null :  sanitize_key( $args['slug'] );
		$this->required = empty( $args['required'] ) ? false : (bool) $args['required'];
		$this->fieldset = empty( $args['fieldset'] ) ? null : sanitize_key( $args['fieldset'] );
		$this->order = empty( $args['order'] ) ? null : (int) $args['order'];
		$this->options = empty( $args['options'] ) ? null : $this->get_valid_options( $args['options'] );
		$this->status = empty( $args['status'] ) ? 'publish' : sanitize_key( $args['status'] );

		do_action( 'cffrtb_setup_field_object', $this, $args );
	}

	/**
	 * Check if type is valid
	 *
	 * @since 0.1
	 */
	public function is_valid_type( $type ) {
		global $rtb_controller;

		$types = $rtb_controller->fields->get_valid_field_types();
		return !empty( $type ) && array_key_exists( $type, $types );
	}

	/**
	 * Return a valid type. Falls back to default if specified type is
	 * not valid. Also allows a `fieldset` type to pass
	 *
	 * @since 0.1
	 */
	public function get_valid_type( $type ) {
		global $rtb_controller;

		if ( $type == 'fieldset' ) {
			return $type;
		}

		if ( $this->is_valid_type( $type ) ) {
			return $type;
		}

		$defaults = $rtb_controller->fields->get_default_field_values();
		return $defaults['type'];
	}

	/**
	 * Return a valid subtype. Falls back to first subtype of type if
	 * subtype is not valid.
	 *
	 * @since 0.1
	 */
	public function get_valid_subtype( $subtype ) {
		global $rtb_controller;

		if( $subtype == 'fieldset' ) {
			return $subtype;
		}

		$types = $rtb_controller->fields->get_valid_field_types();
		if ( array_key_exists( $subtype, $types[ $this->type ]['subtypes'] ) ) {
			return $subtype;
		}

		reset( $types[ $this->type ][ 'subtypes' ] );

		return key( $types[ $this->type ][ 'subtypes' ] );
	}

	/**
	 * Sanitize options
	 *
	 * @since 0.1
	 */
	public function get_valid_options( $options ) {
		global $rtb_controller;

		$new_options = array();
		foreach( $options as $key => $option ) {

			// Store key for fields that were generated before the change in
			// version 1.1, when the key represented the field id
			if ( !isset( $option['id'] ) ) {
				$option['id'] = $key;
			}

			$new_options[] = array(
				'id' => substr( $option['id'], 0, 3 ) == 'new' ? sanitize_text_field( $option['id'] ) : absint( $option['id'] ),
				'value' => isset( $option['value'] ) ? sanitize_text_field( $option['value'] ) : '',
				'disabled' => empty( $option['disabled'] ) ? false : true,
				// An order param may not exist if the option was created in an
				// earlier version of the plugin
				'order' => isset( $option['order'] ) ? absint( $option['order'] ) : 0,
			);
		}

		usort( $new_options, array( $rtb_controller->fields, 'sort_by_order' ) );

		return $new_options;
	}

	/**
	 * Prepare an options array for input as `post_meta`
	 *
	 * Each option must have a unique, permanent ID. If no ID exists, create a
	 * unique ID
	 *
	 * @since 0.1
	 */
	public function prepare_options_input() {
		if ( ! rtbPaidFeatures::can( 'custom_fields' ) || ! class_exists( 'fspphRTBFieldWriter', false ) ) { return array( false, array( 'error' => 'paid_runtime_unavailable' ) ); }
		return fspphRTBFieldWriter::prepare_options_input( $this );
	}

	/**
	 * Check if this field has an id
	 *
	 * @since 0.1
	 */
	public function has_id() {
		return !empty( $this->ID );
	}

	/**
	 * Check if an option is valid
	 *
	 * @since 0.1
	 */
	public function is_valid_option( $value ) {
		return false !== $this->get_option_by_id( $value, true );
	}

	public function get_option_by_id( $id, $active_only = false ) {
		foreach ( (array) $this->options as $key => $option ) {
			$option_id = isset( $option['id'] ) ? $option['id'] : $key;
			if ( (string) $option_id !== (string) $id ) { continue; }
			if ( $active_only && ! empty( $option['disabled'] ) ) { return false; }

			return $option;
		}

		return false;
	}

	/**
	 * Save the label of this field
	 *
	 * @since 0.1
	 */
	public function save_label() {
		if ( ! rtbPaidFeatures::can( 'custom_fields' ) || ! class_exists( 'fspphRTBFieldWriter', false ) ) { return array( false, array( 'error' => 'paid_runtime_unavailable' ) ); }
		return fspphRTBFieldWriter::save_label( $this );
	}

	/**
	 * Save a field
	 *
	 * @since 0.1
	 */
	public function save_field() {
		if ( ! rtbPaidFeatures::can( 'custom_fields' ) || ! class_exists( 'fspphRTBFieldWriter', false ) ) { return array( false, array( 'error' => 'paid_runtime_unavailable' ) ); }
		return fspphRTBFieldWriter::save_field( $this );
	}

	/**
	 * Validate the input for a field
	 *
	 * @since 0.1
	 */
	public function validate_input( $booking ) {

		// Don't validate fieldsets
		if ( $this->type === 'fieldset' ) {
			return;
		}

		// Instantiate the custom fields array if it's missing
		if ( !isset( $booking->custom_fields ) ) {
			$booking->custom_fields = array();
		}

		$input = isset( $_POST['rtb-' . $this->slug ] )
			? ( is_array( $_POST['rtb-' . $this->slug ] )
				? array_map( 'absint', wp_unslash( $_POST['rtb-' . $this->slug ] ) )
				: sanitize_text_field( wp_unslash( $_POST['rtb-' . $this->slug] ) ) )
			: '';

		// Skip empty fields but do not skip checkboxes.
		// required checks are performed by base plugin validation

		$checkbox = 'options' === $this->type && 'checkbox' === $this->subtype;

		if ( ( is_string( $input ) && trim( $input ) == '' ) ||
			( is_array( $input ) && empty( $input ) ) ) {
			
			// When a checkbox is unselected, it will not override the previously selected
			// value because HTML Form will not submit empty checkboxes
			if($checkbox) {
				$input = [];
			}
			else {
				return;
			}
		}
		
		// Option fields
		if ( $this->type == 'options' ) {

			if ( !is_array( $input ) ) {
				$input = array( $input );
			}

			foreach( $input as $input_i ) {
				if ( !$this->is_valid_option( $input_i ) ) {
					$booking->validation_errors[] = array(
						'field'			=> $this->slug,
						'post_variable'	=> $input,
						'message'		=> __( 'The option you selected is not valid. Please make another choice.', 'custom-fields-for-rtb' )
					);

					return;
				}
			}

			if ( $this->subtype === 'select' || $this->subtype === 'radio' ) {
				$val = absint( $input[0] );
				$booking->custom_fields[ $this->slug ] = $val;
			} elseif ( $this->subtype === 'checkbox' ) {
				$val = array();
				foreach( $input as $input_i ) {
					$val[] = absint( $input_i );
				}
				$booking->custom_fields[ $this->slug ] = $val;
			}

		// Confirm fields (always true if we've reached this stage)
		} elseif ( $this->type === 'confirm' ) {
			$booking->custom_fields[ $this->slug ] = true;

		// Text fields just need to be sanitized
		} elseif ( $this->type === 'text' ) {
			$booking->custom_fields[ $this->slug ] = sanitize_text_field( $input );
		}
	}

}
} // endif;
