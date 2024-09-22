<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbPatterns' ) ) {
/**
 * Class to create, edit and display block patterns for the Gutenberg editor
 *
 * @since 2.5.14
 */
class rtbPatterns {

	/**
	 * Add hooks
	 * @since 2.5.14
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'rtb_add_pattern_category' ) );
		add_action( 'init', array( $this, 'rtb_add_patterns' ) );
	}

	/**
	 * Register block patterns
	 * @since 2.5.14
	 */
	public function rtb_add_patterns() {

		$block_patterns = array(
			'booking-form',
			'modify-booking',
			'view-bookings',
		);
	
		foreach ( $block_patterns as $block_pattern ) {
			$pattern_file = RTB_PLUGIN_DIR . '/includes/patterns/' . $block_pattern . '.php';
	
			register_block_pattern(
				'restaurant-reservations/' . $block_pattern,
				require $pattern_file
			);
		}
	}

	/**
	 * Create a new category of block patterns to hold our pattern(s)
	 * @since 2.5.14
	 */
	public function rtb_add_pattern_category() {
		
		register_block_pattern_category(
			'rtb-block-patterns',
			array(
				'label' => __( 'Five Star Restaurant Reservations', 'restaurant-reservations' )
			)
		);
	}
}
} // endif
