<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** One-time, site-wide Black Friday 2026 promotion. */
class rtbBlackFriday2026Notice {
	// EST (UTC-5), independent of the WordPress site's configured timezone.
	const START = 1795669260; // November 26, 2026 at 00:01 EST.
	const END = 1796101200;   // December 1, 2026 at 00:00 EST (exclusive).
	const HIDDEN = 'rtb-bf2026-hidden';
	const SNOOZED = 'rtb-bf2026-snoozed';
	const ACTION = 'rtb_bf2026_dismiss';

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'render' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'dismiss' ) );
	}

	protected function now() {
		return time();
	}

	private function audience() {
		$now = $this->now();
		if ( $now < self::START || $now >= self::END || ! current_user_can( 'manage_options' )
			|| false !== get_transient( self::HIDDEN ) || false !== get_transient( self::SNOOZED ) ) {
			return '';
		}

		global $rtb_controller;
		$contract = isset( $rtb_controller->permissions ) ? $rtb_controller->permissions->get_contract() : array();
		if ( ! empty( $contract['ultimate']['active'] ) ) { return ''; }
		// RTB's active non-trial Premium states are already bound to a saved key by the local v2 contract.
		if ( ! empty( $contract['premium']['active'] ) && 'trial' !== ( $contract['premium']['status'] ?? 'trial' ) ) { return 'premium'; }
		return 'free';
	}

	public function enqueue_assets() {
		if ( '' === $this->audience() ) { return; }
		wp_enqueue_style( 'rtb-bf2026-notice', RTB_PLUGIN_URL . '/assets/css/black-friday-2026-notice.css', array(), RTB_VERSION );
		wp_enqueue_script( 'rtb-bf2026-notice', RTB_PLUGIN_URL . '/assets/js/black-friday-2026-notice.js', array( 'jquery' ), RTB_VERSION, true );
		wp_localize_script( 'rtb-bf2026-notice', 'rtb_bf2026_notice', array(
			'url'    => admin_url( 'admin-ajax.php' ),
			'nonce'  => wp_create_nonce( self::ACTION ),
			'action' => self::ACTION,
			'error'  => __( 'Could not hide this notice. Please try again.', 'restaurant-reservations' ),
		) );
	}

	public function render() {
		$audience = $this->audience();
		if ( '' === $audience ) { return; }
		?>
		<div class="notice notice-info rtb-bf2026-notice">
			<img class="rtb-bf2026-icon" src="<?php echo esc_url( RTB_PLUGIN_URL . '/assets/img/rtb-icon.png' ); ?>" width="100" height="100" alt="">
			<div class="rtb-bf2026-content">
				<?php if ( 'premium' === $audience ) : ?>
					<p><?php esc_html_e( 'Take advantage of our Black Friday sale to upgrade to Restaurant Reservations Ultimate! As a thank you for being a premium user, get your first year of Ultimate for only $130 or €130! Sale ends November 30th at 11:59:59 PM EST.', 'restaurant-reservations' ); ?></p>
					<p class="rtb-bf2026-actions">
						<a class="button button-primary" href="https://www.paypal.com/webapps/billing/plans/subscribe?plan_id=P-9AM12631B8138902YNEFBASQ" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade (EUR)', 'restaurant-reservations' ); ?></a>
						<a class="button button-primary" href="https://www.paypal.com/webapps/billing/plans/subscribe?plan_id=P-4TD22284E5568860XNBDP5IY" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade (USD)', 'restaurant-reservations' ); ?></a>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( "Take advantage of our Black Friday sale to upgrade to Restaurant Reservations Ultimate or Premium! Use code 'BF2026' at checkout to get 20% off of the regular price. Sale ends November 30th at 11:59:59 PM EST.", 'restaurant-reservations' ); ?></p>
					<p><a class="button button-primary" href="https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=plugin_admin_bf2026" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Restaurant Reservations Plans', 'restaurant-reservations' ); ?></a></p>
				<?php endif; ?>
				<p class="rtb-bf2026-actions">
					<button type="button" class="button rtb-bf2026-dismiss" data-dismissal="temporary"><?php esc_html_e( 'Hide Temporarily', 'restaurant-reservations' ); ?></button>
					<button type="button" class="button rtb-bf2026-dismiss" data-dismissal="permanent"><?php esc_html_e( "Don't Show Again", 'restaurant-reservations' ); ?></button>
					<span class="rtb-bf2026-status" role="status" aria-live="polite"></span>
				</p>
			</div>
		</div>
		<?php
	}

	public function dismiss() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( self::ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not authorized to hide this notice.', 'restaurant-reservations' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only the fixed values below are permitted.
		$mode = isset( $_POST['dismissal'] ) && is_string( $_POST['dismissal'] ) ? wp_unslash( $_POST['dismissal'] ) : '';
		if ( ! in_array( $mode, array( 'temporary', 'permanent' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid dismissal choice.', 'restaurant-reservations' ) ), 400 );
		}
		$now = $this->now();
		if ( $now >= self::START && $now < self::END ) {
			$remaining = self::END - $now;
			$key = 'permanent' === $mode ? self::HIDDEN : self::SNOOZED;
			$ttl = 'permanent' === $mode ? $remaining : min( HOUR_IN_SECONDS, $remaining );
			// A snooze from another tab cannot undo Don't Show Again.
			if ( ! set_transient( $key, 1, $ttl ) && false === get_transient( $key ) ) {
				wp_send_json_error( array( 'message' => __( 'Could not hide this notice. Please try again.', 'restaurant-reservations' ) ), 500 );
			}
		}
		wp_send_json_success();
	}
}
