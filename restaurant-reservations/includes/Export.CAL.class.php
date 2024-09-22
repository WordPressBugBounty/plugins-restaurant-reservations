<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ebfrtbExportCAL' ) ) {
/**
 * Handle iCal exports using Zap Cal Library
 *
 * @since 2.6.3
 */
class ebfrtbExportCAL extends ebfrtbExport {

	/**
	 * Arguments for the query used to fetch
	 * bookings for this export
	 *
	 * @since 2.6.3
	 */
	public $query_args;

	/**
	 * Insantiate the iCal export
	 *
	 * @since 2.6.3
	 */
	public function __construct( $bookings, $args = array() ) {

		$this->bookings = $bookings;

		// Query arguments
		if ( !empty( $args['query_args'] ) ) {
			$this->query_args = $args['query_args'];
		}
	}

	/**
	 * Compile the PDF file
	 *
	 * This routes to the appropriate export method
	 * depending on the PDF library being used.
	 *
	 * @since 2.6.3
	 */
	public function export() {
		global $rtb_controller;

		require_once( RTB_PLUGIN_DIR . '/lib/zapcal/zapcallib.php' );

		$ical = new ZCiCal();

		$timezone = wp_timezone_string();

		ZCTimeZoneHelper::getTZNode( $this->get_event_start_year(), $this->get_event_end_year(), $timezone, $ical->curnode );

		foreach ( $this->bookings as $booking ) {

			$booking_end_datetime = new DateTime( $booking->date, wp_timezone() );
			$booking_end_datetime->add( new DateInterval( 'PT' . $rtb_controller->settings->get_setting( 'rtb-dining-block-length' ) . 'M' ) );

			$event = new ZCiCalNode( 'VEVENT', $ical->curnode );

			$event->addNode( new ZCiCalDataNode( 'SUMMARY:' . $booking->name . ' - ' . $booking->party ) );

			$event->addNode( new ZCiCalDataNode( 'DTSTART:' . ZCiCal::fromSqlDateTime( $booking->date ) ) );

			$event->addNode( new ZCiCalDataNode( 'DTEND:' . ZCiCal::fromSqlDateTime( $booking_end_datetime->format( 'Y-m-d H:i:s' ) ) ) );

			$event->addNode( new ZCiCalDataNode( 'UID:' . $booking->ID . get_site_url() ) );

			$event->addNode( new ZCiCalDataNode( 'DTSTAMP:' . ZCiCal::fromSqlDateTime() ) );

			$event->addNode( new ZCiCalDataNode( 'DESCRIPTION:' . ZCiCal::formatContent( 'Email: ' . $booking->email . ' Phone: ' . $booking->phone ) ) );
		}

		$this->export = $ical;

		return $this->export;
	}

	/**
	 * Deliver the PDF to the browser
	 *
	 * @since 2.6.3
	 */
	public function deliver() {

		// Generate the export if it's not been done yet
		if ( empty( $this->export ) ) {
			$this->export();
		}

		$filename = apply_filters( 'ebfrtb_export_ical_filename', sanitize_file_name( $this->get_date_phrase() ) . '.ics' );

		// Clean any stray errors, warnings or notices that may have been
		// printed to the buffer
		ob_get_clean();

		echo header( 'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
		echo header( 'Content-Description: File Transfer' );
		echo header( 'Content-type:text/calendar' );
		echo header( 'Content-Disposition: attachment; filename=' . $filename );

		echo $this->export->export();

		exit();
	}

	/**
	 * Get the earliest year within the bookings
	 *
	 * @since 2.6.3
	 */
	public function get_event_start_year() {

		$start_year = 9999;

		foreach ( $this->bookings as $booking ) {
			
			$booking_date = new DateTime( $booking->date, wp_timezone() );

			$start_year = min( $start_year, (int) $booking_date->format( 'Y' ) );
		}

		return $start_year;
	}

	/**
	 * Get the latest year within the bookings
	 *
	 * @since 2.6.3
	 */
	public function get_event_end_year() {

		$end_year = 0;

		foreach ( $this->bookings as $booking ) {
			
			$booking_date = new DateTime( $booking->date, wp_timezone() );

			$end_year = max( $end_year, (int) $booking_date->format( 'Y' ) );
		}

		return $end_year;
	}
}
} // endif
