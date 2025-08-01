<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( !class_exists( 'rtbBookingsTable' ) ) {
/**
 * Bookings Table Class
 *
 * Extends WP_List_Table to display the list of bookings in a format similar to
 * the default WordPress post tables.
 *
 * @h/t Easy Digital Downloads by Pippin: https://easydigitaldownloads.com/
 * @since 0.0.1
 */
class rtbBookingsTable extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $base_url;

	/**
	 * Array of booking counts by total and status
	 *
	 * @var array
	 * @since 0.0.1
	 */
	public $booking_counts;

	/**
	 * Array of bookings
	 *
	 * @var array
	 * @since 0.0.1
	 */
	public $bookings;

	/**
	 * Current date filters
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $filter_start_date = null;
	public $filter_end_date = null;

	/**
	 * Current time filters
	 *
	 * @var string
	 * @since 2.2.0
	 */
	public $filter_start_time = null;
	public $filter_end_time = null;

	/**
	 * Current location filter
	 *
	 * @var int
	 * @since 1.6
	 */
	public $filter_location = 0;

	/**
	 * Current name filter
	 *
	 * @var string
	 * @since 2.4.4
	 */
	public $filter_name = '';

	/**
	 * Current email filter
	 *
	 * @var string
	 * @since 2.7.0
	 */
	public $filter_email = '';

	/**
	 * Current phone filter
	 *
	 * @var string
	 * @since 2.7.0
	 */
	public $filter_phone = '';

	/**
	 * Current table filter
	 *
	 * @var string
	 * @since 2.7.0
	 */
	public $filter_table = '';

	/**
	 * Current query string
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $query_string;

	/**
	 * Results of a bulk or quick action
	 *
	 * @var array
	 * @since 1.4.6
	 */
	public $action_result = array();

	/**
	 * Type of bulk or quick action last performed
	 *
	 * @var string
	 * @since 1.4.6
	 */
	public $last_action = '';

	/**
	 * Stored reference to visible columns
	 *
	 * @var string
	 * @since 1.5
	 */
	public $visible_columns = array();

	/**
	 * Stored reference to rtb_booking post statuses
	 *
	 * @var array
	 * @since 2.4.4
	 */
	public $booking_statuses = array();

	/**
	 * Initialize the table and perform any requested actions
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'Booking', 'restaurant-reservations' ),
			'plural'    => __( 'Bookings', 'restaurant-reservations' ),
			'ajax'      => false
		) );

		$this->populate_booking_status();

		// Set the date filter
		$this->set_date_filter();
		
		// Set the name filter
		$this->set_other_filter();

		// Strip unwanted query vars from the query string or ensure the correct
		// vars are used
		$this->query_string_maintenance();

		// Run any bulk action requests
		$this->process_bulk_action();

		// Run any quicklink requests
		$this->process_quicklink_action();

		// Retrieve a count of the number of bookings by status
		$this->get_booking_counts();

		// Retrieve bookings data for the table
		$this->bookings_data();

		$this->base_url = admin_url( 'admin.php?page=' . RTB_BOOKING_POST_TYPE );

		// Add default items to the details column if they've been hidden
		add_filter( 'rtb_bookings_table_column_details', array( $this, 'add_details_column_items' ), 10, 2 );
	}

	public function populate_booking_status()
	{
		global $rtb_controller;

		foreach ( $rtb_controller->cpts->booking_statuses as $status => $data ) {
			$this->booking_statuses[ $status ] = array(
				'label' => $data['label'],
				'count' => $data['label_count']['singular']
			);
		}
	}

	/**
	 * Set the correct date filter
	 *
	 * $_POST values should always overwrite $_GET values
	 *
	 * @since 0.0.1
	 */
	public function set_date_filter( $start_date = null, $end_date = null, $start_time = null, $end_time = null ) {

		if ( !empty( $_GET['action'] ) && $_GET['action'] == 'clear_date_filters' ) {
			$this->filter_start_date 	= null;
			$this->filter_end_date 		= null;
			$this->filter_start_time 	= null;
			$this->filter_end_time 		= null;
		}

		$this->filter_start_date 	= $start_date;
		$this->filter_end_date 		= $end_date;
		$this->filter_start_time 	= $start_time;
		$this->filter_end_time 		= $end_time;

		if ( $start_date === null ) {
			$this->filter_start_date = !empty( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : null;
			$this->filter_start_date = !empty( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : $this->filter_start_date;
		}

		if ( $end_date === null ) {
			$this->filter_end_date = !empty( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : null;
			$this->filter_end_date = !empty( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : $this->filter_end_date;
		}

		if ( $start_time === null ) {
			$this->filter_start_time = !empty( $_GET['start_time'] ) ? sanitize_text_field( $_GET['start_time'] ) : null;
			$this->filter_start_time = !empty( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : $this->filter_start_time;
		}

		if ( $end_time === null ) {
			$this->filter_end_time = !empty( $_GET['end_time'] ) ? sanitize_text_field( $_GET['end_time'] ) : null;
			$this->filter_end_time = !empty( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : $this->filter_end_time;
		}
	}

	/**
	 * Set filters like name, email, phone, and table 
	 *
	 * @since 2.4.4
	 */
	public function set_other_filter() {

		if( isset( $_GET['filter_name'] ) && ! empty( $_GET['filter_name'] ) ) {

			$this->filter_name = sanitize_text_field( $_GET['filter_name'] );
		}

		if( isset( $_GET['filter_email'] ) && ! empty( $_GET['filter_email'] ) ) {

			$this->filter_email = sanitize_text_field( $_GET['filter_email'] );
		}

		if( isset( $_GET['filter_phone'] ) && ! empty( $_GET['filter_phone'] ) ) {

			$this->filter_phone = sanitize_text_field( $_GET['filter_phone'] );
		}

		if( isset( $_GET['filter_table'] ) && ! empty( $_GET['filter_table'] ) ) {

			$this->filter_table = sanitize_text_field( $_GET['filter_table'] );
		}
	}

	/**
	 * Get the current date range
	 *
	 * @since 1.3
	 */
	public function get_current_date_range() {

		$range = empty( $this->filter_start_date ) ? _x( '*', 'No date limit in a date range, eg 2014-* would mean any date from 2014 or after', 'restaurant-reservations' ) : $this->filter_start_date;
		$range .= empty( $this->filter_start_date ) || empty( $this->filter_end_date ) ? '' : _x( '&mdash;', 'Separator between two dates in a date range', 'restaurant-reservations' );
		$range .= empty( $this->filter_end_date ) ? _x( '*', 'No date limit in a date range, eg 2014-* would mean any date from 2014 or after', 'restaurant-reservations' ) : $this->filter_end_date;

		return $range;
	}

	/**
	 * Get the guest count for the current date range
	 *
	 * @since 1.3
	 */
	public function get_current_date_range_guest_count() {

		return (string) array_sum( array_column( $this->bookings, 'party' ) ) . ( $this->booking_counts['non_trash'] != sizeof( $this->bookings ) ? ' (on this page of results)' : '' );
	}

	/**
	 * Strip unwanted query vars from the query string or ensure the correct
	 * vars are passed around and those we don't want to preserve are discarded.
	 *
	 * @since 0.0.1
	 */
	public function query_string_maintenance() {

		$this->query_string = remove_query_arg( array( 'action', 'start_date', 'end_date' ) );

		if ( $this->filter_start_date !== null ) {
			$this->query_string = add_query_arg( array( 'start_date' => $this->filter_start_date ), $this->query_string );
		}

		if ( $this->filter_end_date !== null ) {
			$this->query_string = add_query_arg( array( 'end_date' => $this->filter_end_date ), $this->query_string );
		}

		if ( $this->filter_start_time !== null ) {
			$this->query_string = add_query_arg( array( 'start_time' => $this->filter_start_time ), $this->query_string );
		}

		if ( $this->filter_end_time !== null ) {
			$this->query_string = add_query_arg( array( 'end_time' => $this->filter_end_time ), $this->query_string );
		}

		$this->filter_location = ! isset( $_GET['location'] ) ? 0 : intval( $_GET['location'] );
		$this->filter_location = ! isset( $_POST['location'] ) ? $this->filter_location : intval( $_POST['location'] );
		$this->query_string = remove_query_arg( 'location', $this->query_string );
		if ( !empty( $this->filter_location ) ) {
			$this->query_string = add_query_arg( array( 'location' => $this->filter_location ), $this->query_string );
		}

	}

	/**
	 * Show the time views, date filters and the search box
	 * @since 0.0.1
	 */
	public function advanced_filters() {

		// Show the date_range views (today, upcoming, all)
		if ( !empty( $_GET['date_range'] ) ) {
			$date_range = sanitize_text_field( $_GET['date_range'] );
		} else {
			$date_range = '';
		}

		// Use a custom date_range if a date range has been entered
		if ( $this->filter_start_date !== null || $this->filter_end_date !== null ) {
			$date_range = 'custom';
		}

		// Strip out existing date filters from the date_range view urls
		$date_range_query_string = remove_query_arg(
			array( 'date_range', 'start_date', 'end_date' ), 
			$this->query_string
		);

		$date_views = array(
			'upcoming' => sprintf( 
				'<a href="%s"%s>%s</a>', 
				esc_url( 
					add_query_arg( 
						array( 'paged' => FALSE ), 
						$date_range_query_string 
					) 
				), 
				$date_range === '' ? ' class="current"' : '', 
				__( 'Upcoming', 'restaurant-reservations' ) ), 

			'today' => sprintf( 
				'<a href="%s"%s>%s</a>', 
				esc_url( 
					add_query_arg( 
						array( 'date_range' => 'today', 'paged' => FALSE ), 
						$date_range_query_string 
					) 
				), 
				$date_range === 'today' ? ' class="current"' : '', 
				__( 'Today', 'restaurant-reservations' ) ),

			'past' => sprintf( 
				'<a href="%s"%s>%s</a>', 
				esc_url( 
					add_query_arg( 
						array( 'date_range' => 'past', 'paged' => FALSE ), 
						$date_range_query_string
					)
				), 
				$date_range === 'past' ? ' class="current"' : '', 
				__( 'Past', 'restaurant-reservations' ) ),

			'all' => sprintf( 
				'<a href="%s"%s>%s</a>', 
				esc_url( 
					add_query_arg( 
						array( 'date_range' => 'all', 'paged' => FALSE ), 
						$date_range_query_string 
					) 
				), 
				$date_range == 'all' ? ' class="current"' : '', 
				__( 'All', 'restaurant-reservations' ) 
			),
		);

		if ( $date_range == 'custom' ) {
			$date_views['date'] = '<span class="date-filter-range current">' . $this->get_current_date_range() . '</span>';
			$date_views['date'] .= '<a id="rtb-date-filter-link" href="#"><span class="dashicons dashicons-calendar"></span> <span class="rtb-date-filter-label">Change date range</span></a>';
		} else {
			$date_views['date'] = '<a id="rtb-date-filter-link" href="#">' . esc_html__( 'Specific Date(s)/Time', 'restaurant-reservations' ) . '</a>';
		}

		// Strip out existing detail filters from the details view urls
		$details_query_string = remove_query_arg(
			array( 'filter_name', 'filter_email', 'filter_phone', 'filter_table' ), 
			$this->query_string
		);

		$detail_views = array(
			'filter_name' => sprintf( 
				'<input type="text" value="%s" placeholder="' . __( 'Name', 'restaurant-reservations' ) . '">', 
				esc_attr( $this->filter_name )
			),
			'filter_email' => sprintf( 
				'<input type="text" value="%s" placeholder="' . __( 'Email', 'restaurant-reservations' ) . '">', 
				esc_attr( $this->filter_email )
			),
			'filter_phone' => sprintf( 
				'<input type="text" value="%s" placeholder="' . __( 'Phone', 'restaurant-reservations' ) . '">', 
				esc_attr( $this->filter_phone )
			),
			'filter_table' => sprintf( 
				'<input type="text" value="%s" placeholder="' . __( 'Table', 'restaurant-reservations' ) . '">', 
				esc_attr( $this->filter_table )
			),
			'filter_submit' => sprintf( 
				'<a href="%s" class="rtb-details-search">%s</a>', 
				esc_url( 
					add_query_arg( 
						array( 'paged' => FALSE ), 
						$details_query_string 
					) 
				),
				'<span class="dashicons dashicons-search"></span>'
			)
		);

		$date_views = apply_filters( 'rtb_bookings_table_views_date_range', $date_views );
		$detail_views = apply_filters( 'rtb_bookings_table_views_details', $detail_views )
		?>

		<div id="rtb-filters">
			<ul class="subsubsub rtb-views-date_range">
				<?php
					$total = count( $date_views );
					$index = 1;
					foreach ($date_views as $class => $value) {
						$separator = $index != $total ? ' |' : '';
						echo "<li class=\"{$class}\">{$value}{$separator}</li>";
						$index++;
					}
				?>
			</ul>

			<ul class="subsubsub rtb-views-details">
				<?php
					$total = count( $detail_views );
					$index = 1;
					foreach ($detail_views as $class => $value) {
						echo "<li class=\"{$class}\">{$value}</li>";
						$index++;
					}
				?>
			</ul>

			<div class="date-filters">
				<div class="rtb-admin-bookings-filters-start">
					<label for="start-date" class="screen-reader-text"><?php _e( 'Start Date:', 'restaurant-reservations' ); ?></label>
					<input type="text" id="start-date" name="start_date" class="datepicker" value="<?php echo esc_attr( $this->filter_start_date ); ?>" placeholder="<?php _e( 'Start Date', 'restaurant-reservations' ); ?>" />
					<input type="text" id="start-time" name="start_time" class="timepicker" value="<?php echo esc_attr( $this->filter_start_time ); ?>" placeholder="<?php _e( 'Start Time', 'restaurant-reservations' ); ?>" />
				</div>	
				<div class="rtb-admin-bookings-filters-end">
					<label for="end-date" class="screen-reader-text"><?php _e( 'End Date:', 'restaurant-reservations' ); ?></label>
					<input type="text" id="end-date" name="end_date" class="datepicker" value="<?php echo esc_attr( $this->filter_end_date ); ?>" placeholder="<?php _e( 'End Date', 'restaurant-reservations' ); ?>" />
					<input type="text" id="end-time" name="end_time" class="timepicker" value="<?php echo esc_attr( $this->filter_end_time ); ?>" placeholder="<?php _e( 'End Time', 'restaurant-reservations' ); ?>" />
				</div>
				
				<input type="submit" class="button button-secondary" value="<?php _e( 'Apply', 'restaurant-reservations' ); ?>"/>
				
				<?php if( !empty( $this->filter_start_date ) || !empty( $this->filter_end_date ) || !empty( $this->filter_start_time ) || !empty( $this->filter_end_time ) ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'clear_date_filters' ) ) ); ?>" class="button button-secondary"><?php _e( 'Clear Filter', 'restaurant-reservations' ); ?></a>
				<?php endif; ?>

				<?php if( !empty( $_GET['status'] ) ) : ?>
					<input type="hidden" name="status" value="<?php echo esc_attr( sanitize_text_field( $_GET['status'] ) ); ?>"/>
				<?php endif; ?>

				</div>
		</div>

<?php
	}

	/**
	 * Retrieve the view types
	 * @since 0.0.1
	 */
	public function get_views() {

		$current = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

		$this->booking_statuses[ 'all' ] = array( 
			'label' => __( 'All', 'restaurant-reservations' ),
			'count' => _n_noop(
				'All <span class="count">(%s)</span>', 
				'All <span class="count">(%s)</span>', 
				'restaurant-reservations' 
			)[ 'singular' ]
		);
		$this->booking_statuses[ 'trash' ] = array( 
			'label' => __( 'Trash', 'restaurant-reservations' ),
			'count' => _n_noop(
				'Trash <span class="count">(%s)</span>', 
				'Trash <span class="count">(%s)</span>', 
				'restaurant-reservations' 
			)[ 'singular' ]
		);

		ksort( $this->booking_statuses );

		$views = [];

		foreach ( $this->booking_statuses as $status => $data )
		{
			$url = 'all' == $status 
				? esc_url( 
			      remove_query_arg( 
			        array( 'status', 'paged' ), 
			        $this->query_string 
			      ) 
			    )
				: esc_url( 
			      add_query_arg( 
			        array( 'status' => $status, 'paged' => FALSE ), 
			        $this->query_string 
			      ) 
			    );

			$views[ $status ] = sprintf(
		    '<a href="%s"%s>%s</a>', 
		    $url, 
		    $current === $status ? ' class="current"' : '', 
		    sprintf( $data['count'], $this->booking_counts[ $status ] )
		  );
		}

		return apply_filters( 'rtb_bookings_table_views_status', $views );
	}

	/**
	 * Generates content for a single row of the table
	 * @since 0.0.1
	 */
	public function single_row( $item ) {
		static $row_alternate_class = 'alternate';
		$row_alternate_class = ( $row_alternate_class == 'alternate' ? '' : 'alternate' );

		$row_classes = array( esc_attr( $item->post_status ) );

		if ( !empty( $row_alternate_class ) ) {
			$row_classes[] = $row_alternate_class;
		}

		$row_classes = apply_filters( 'rtb_admin_bookings_list_row_classes', $row_classes, $item );

		echo '<tr class="' . implode( ' ', $row_classes ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 0.0.1
	 */
	public function get_columns() {

		// Prevent the lookup from running over and over again on a single
		// page load
		if ( !empty( $this->visible_columns ) ) {
			return $this->visible_columns;
		}

		$all_default_columns = $this->get_all_default_columns();
		$all_columns = $this->get_all_columns();

		global $rtb_controller;
		$visible_columns = $rtb_controller->settings->get_setting( 'bookings-table-columns' );
		if ( empty( $visible_columns ) ) {
			$columns = $all_default_columns;
		} else {
			$columns = array();
			$columns['cb'] = $all_default_columns['cb'];
			$columns['date'] = $all_default_columns['date'];

			foreach( $all_columns as $key => $column ) {
				if ( in_array( $key, $visible_columns ) ) {
					$columns[$key] = $all_columns[$key];
				}
			}
			$columns['details'] = $all_default_columns['details'];
		}

		$this->visible_columns = apply_filters( 'rtb_bookings_table_columns', $columns );

		return $this->visible_columns;
	}

	/**
	 * Retrieve all default columns
	 *
	 * @since 1.5
	 */
	public function get_all_default_columns() {
		global $rtb_controller;

		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'date'     	=> __( 'Date', 'restaurant-reservations' ),
			'id'     	=> __( 'ID', 'restaurant-reservations' ),
			'party'  	=> __( 'Party', 'restaurant-reservations' ),
			'name'  	=> __( 'Name', 'restaurant-reservations' ),
			'email'  	=> __( 'Email', 'restaurant-reservations' ),
			'phone'  	=> __( 'Phone', 'restaurant-reservations' ),
			'status'  	=> __( 'Status', 'restaurant-reservations' ),
		);

		if ( $rtb_controller->settings->get_setting( 'require-deposit' ) ) { $columns['deposit'] = __( 'Deposit', 'restaurant-reservations' ) ; }
		if ( $rtb_controller->settings->get_setting( 'enable-tables' ) ) { $columns['table'] = __( 'Table', 'restaurant-reservations' ) ; }

		// This is so that deposit comes before details, is there a better way to do this?
		$columns['details'] = __( 'Details', 'restaurant-reservations' );

		return $columns;
	}

	/**
	 * Retrieve all available columns
	 *
	 * This is used to get all columns including those deactivated and filtered
	 * out via get_columns().
	 *
	 * @since 1.5
	 */
	public function get_all_columns() {
		$columns = $this->get_all_default_columns();
		$columns['submitted-by'] = __( 'Submitted By', 'restaurant-reservations' );
		return apply_filters( 'rtb_bookings_all_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 * @since 0.0.1
	 */
	public function get_sortable_columns() {
		$columns = array(
			'id' 		=> array( 'ID', true ),
			'date' 		=> array( 'date', true ),
			'name' 		=> array( 'title', true ),
			'status' 	=> array( 'status', true ),
		);
		return apply_filters( 'rtb_bookings_table_sortable_columns', $columns );
	}

	/**
	 * This function renders most of the columns in the list table.
	 * @since 0.0.1
	 */
	public function column_default( $booking, $column_name ) {
		global $rtb_controller;

		switch ( $column_name ) {
			case 'date' :
				$value = $booking->format_date( $booking->date );
				$value .= '<div class="status"><span class="spinner"></span> ' . __( 'Loading', 'restaurant-reservations' ) . '</div>';

				if ( $booking->post_status !== 'trash' ) {
					$value .= '<div class="actions">';
					$value .= '<a href="#" data-id="' . esc_attr( $booking->ID ) . '" data-action="edit">' . __( 'Edit', 'restaurant-reservations' ) . '</a>';
					$value .= ' | <a href="#" class="trash" data-id="' . esc_attr( $booking->ID ) . '" data-action="trash">' . __( 'Trash', 'restaurant-reservations' ) . '</a>';
					$value .= '</div>';
				}

				break;

			case 'id' :
				$value = $booking->ID;
				break;

			case 'party' :
				$value = $booking->party;
				break;

			case 'name' :
				$value = esc_html( $booking->name );
				break;

			case 'email' :
				$value = esc_html( $booking->email );
				$value .= '<div class="actions">';
				$value .= '<a href="#" data-id="' . esc_attr( $booking->ID ) . '" data-action="email" data-email="' . esc_attr( $booking->email ) . '" data-name="' . esc_attr( $booking->name ) . '">' . __( 'Send Email', 'restaurant-reservations' ) . '</a>';
				$value .= '</div>';
				break;

			case 'phone' :
				$value = esc_html( $booking->phone );
				break;

			case 'deposit' :
				$currency_symbol = $rtb_controller->settings->get_setting( 'rtb-stripe-currency-symbol' );
				$value = ( $currency_symbol ? $currency_symbol : '$' ) . esc_html( $booking->deposit );
				break;

			case 'table' :
				$table = is_array( $booking->table ) ? $booking->table : array();
				$value = esc_html( implode( ',', $table ) );
				break;

			case 'status' :
				global $rtb_controller;
				if ( !empty( $rtb_controller->cpts->booking_statuses[$booking->post_status] ) ) {
					$value = $rtb_controller->cpts->booking_statuses[$booking->post_status]['label'];
				} elseif ( $booking->post_status == 'trash' ) {
					$value = _x( 'Trash', 'Status label for bookings put in the trash', 'restaurant-reservations' );
				} else {
					$value = $booking->post_status;
				}
				break;

			case 'details' :
				$value = '';

				$details = array();
				if ( trim( $booking->message ) ) {
					$details[] = array(
						'label' => __( 'Message', 'restaurant-reservations' ),
						'value' => esc_html( $booking->message ),
					);
				}

				if ( $booking->post_status == 'payment_failed' ) {
					$details[] = array(
						'label' => __( 'Payment Failure Reason', 'restaurant-reservations' ),
						'value' => isset( $booking->payment_failure_message ) ? $booking->payment_failure_message : __( 'Unknown payment failure reason. Check with payment processor.', 'restaurant-reservations' )
					);
				}

				if ( ! empty( $booking->confirmed_user ) ) {
					$details[] = array(
						'label' => __( 'Reservation confirmed by:', 'restaurant-reservations' ),
						'value' => $this->get_reservation_confirmation_reason( $booking ),
					);
				}

				$details = apply_filters( 'rtb_bookings_table_column_details', $details, $booking );

				if ( !empty( $details ) ) {
					$value = '<a href="#" class="rtb-show-details" data-id="details-' . esc_attr( $booking->ID ) . '"><span class="dashicons dashicons-testimonial"></span></a>';
					$value .= '<div class="rtb-details-data"><ul class="details">';
					foreach( $details as $detail ) {
						$value .= '<li><div class="label">' . $detail['label'] . '</div><div class="value">' . $detail['value'] . '</div></li>';
					}
					$value .= '</ul></div>';
				}
				break;

			case 'submitted-by' :
				global $rtb_controller;
				$ip = !empty( $booking->ip ) ? $booking->ip : __( 'Unknown IP', 'restaurant-reservations' );
				$date_submission = !empty( $booking->date_submission ) ? $booking->format_timestamp( $booking->date_submission ) : __( 'Unknown Date', 'restaurant-reservations' );
				$value = sprintf( esc_html__( 'Request from %s on %s.', 'restaurant-reservations' ), $ip, $date_submission );
				if ( $rtb_controller->settings->get_setting( 'require-consent' ) ) {
					if ( !empty( $booking->consent_acquired ) ) {
						$value .= '<div class="consent">' . sprintf( esc_html__( '✓ Consent acquired', 'restaurant-reservations' ) ) . '</div>';
					} else {
						$value .= '<div class="consent">' . sprintf( esc_html__( '✘ Consent not acquired', 'restaurant-reservations' ) ) . '</div>';
					}
				}
				$value .= '<div class="actions">';
				$value .= '<a href="#" data-action="ban" data-email="' . esc_attr( $booking->email ) . '" data-id="' . absint( $booking->ID ) . '" data-ip="' . $ip . '">';
				$value .= __( 'Ban', 'restaurant-reservations' );
				$value .= '</a>';
				$value .= ' | <a href="#" data-action="delete" data-email="' . esc_attr( $booking->email ) . '" data-id="' . absint( $booking->ID ) . '">';
				$value .= __( 'Delete Customer', 'restaurant-reservations' );
				$value .= '</a>';
				$value .= '</div>';
				break;

			default:
				$value = isset( $booking->$column_name ) ? $booking->$column_name : '';
				break;

		}

		return apply_filters( 'rtb_bookings_table_column', $value, $booking, $column_name );
	}

	/**
	 * Render the checkbox column
	 * @since 0.0.1
	 */
	public function column_cb( $booking ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'bookings',
			$booking->ID
		);
	}

	/**
	 * Add hidden columns values to the details column
	 *
	 * This only handles the default columns. Custom data needs to hook in and
	 * add it's own items to the $details array.
	 *
	 * @since 1.5
	 */
	public function add_details_column_items( $details, $booking ) {
		global $rtb_controller;
		$visible_columns = $this->get_columns();
		$all_columns = $this->get_all_columns();

		$detail_columns = array_diff( $all_columns, $visible_columns );

		foreach( $detail_columns as $key => $label ) {

			$value = $this->column_default( $booking, $key );
			if ( empty( $value ) ) {
				continue;
			}

			$details[] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		return $details;
	}

	/**
	 * Retrieve the bulk actions
	 * @since 0.0.1
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                => __( 'Delete',                	'restaurant-reservations' ),
			'set-status-confirmed'  => __( 'Set To Confirmed',      	'restaurant-reservations' ),
			'set-status-pending'    => __( 'Set To Pending Review', 	'restaurant-reservations' ),
			'set-status-closed'     => __( 'Set To Closed',         	'restaurant-reservations' ),
			'send-email'      		=> __( 'Send Email',         		'restaurant-reservations' )
		);

		return apply_filters( 'rtb_bookings_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 * @since 0.0.1
	 */
	public function process_bulk_action() {
		$ids    = isset( $_POST['bookings'] ) 
			? rtbHelper::sanitize_recursive( $_POST['bookings'], 'absint' ) 
			: false;
		$action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : false;

		// Check bulk actions selector below the table
		$action = $action == '-1' && isset( $_POST['action2'] ) 
			? sanitize_text_field( $_POST['action2'] ) 
			: $action;

		if( empty( $action ) || $action == '-1' ) {
			return;
		}

		if ( !current_user_can( 'manage_bookings' ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		global $rtb_controller;
		$results = array();
		foreach ( $ids as $id ) {
			if ( 'delete' === $action ) {
				$results[$id] = $rtb_controller->cpts->delete_booking( intval( $id ) );
			}

			if ( 'set-status-confirmed' === $action ) {
				$results[$id] = $rtb_controller->cpts->update_booking_status( intval( $id ), 'confirmed' );
			}

			if ( 'set-status-pending' === $action ) {
				$results[$id] = $rtb_controller->cpts->update_booking_status( intval( $id ), 'pending' );
			}

			if ( 'set-status-closed' === $action ) {
				$results[$id] = $rtb_controller->cpts->update_booking_status( intval( $id ), 'closed' );
			}

			$results = apply_filters( 'rtb_bookings_table_bulk_action', $results, $id, $action );
		}

		if( count( $results ) ) {
			$this->action_result = $results;
			$this->last_action = $action;
			add_action( 'rtb_bookings_table_top', array( $this, 'admin_notice_bulk_actions' ) );
		}
	}

	/**
	 * Process quicklink actions sent out in notification emails
	 * @since 0.0.1
	 */
	public function process_quicklink_action() {

		if ( empty( $_REQUEST['rtb-quicklink'] ) ) {
			return;
		}

		if ( !current_user_can( 'manage_bookings' ) ) {
			return;
		}

		global $rtb_controller;

		$results = array();

		$id = !empty( $_REQUEST['booking'] ) ? intval( $_REQUEST['booking'] ) : false;

		if ( $_REQUEST['rtb-quicklink'] == 'confirm' ) {
			$results[$id] = $rtb_controller->cpts->update_booking_status( $id, 'confirmed' );
			$this->last_action = 'set-status-confirmed';
		} elseif ( $_REQUEST['rtb-quicklink'] == 'close' ) {
			$results[$id] = $rtb_controller->cpts->update_booking_status( $id, 'closed' );
			$this->last_action = 'set-status-closed';
		}

		if( count( $results ) ) {
			$this->action_result = $results;
			add_action( 'rtb_bookings_table_top', array( $this, 'admin_notice_bulk_actions' ) );
		}
	}

	/**
	 * Display an admin notice when a bulk action is completed
	 * @since 0.0.1
	 */
	public function admin_notice_bulk_actions() {

		$success = 0;
		$failure = 0;
		foreach( $this->action_result as $id => $result ) {
			if ( $result === true || $result === null ) {
				$success++;
			} else {
				$failure++;
			}
		}

		if ( $success > 0 ) :
		?>

		<div id="rtb-admin-notice-bulk-<?php esc_attr( $this->last_action ); ?>" class="updated">

			<?php if ( $this->last_action == 'delete' ) : ?>
			<p><?php echo sprintf( _n( '%d booking deleted successfully.', '%d bookings deleted successfully.', $success, 'restaurant-reservations' ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-confirmed' ) : ?>
			<p><?php echo sprintf( _n( '%d booking confirmed.', '%d bookings confirmed.', $success, 'restaurant-reservations' ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-pending' ) : ?>
			<p><?php echo sprintf( _n( '%d booking set to pending.', '%d bookings set to pending.', $success, 'restaurant-reservations' ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-closed' ) : ?>
			<p><?php echo sprintf( _n( '%d booking closed.', '%d bookings closed.', $success, 'restaurant-reservations' ), $success ); ?></p>

			<?php endif; ?>
		</div>

		<?php
		endif;

		if ( $failure > 0 ) :
		?>

		<div id="rtb-admin-notice-bulk-<?php esc_attr( $this->last_action ); ?>" class="error">
			<p><?php echo sprintf( _n( '%d booking had errors and could not be processed.', '%d bookings had errors and could not be processed.', $failure, 'restaurant-reservations' ), $failure ); ?></p>
		</div>

		<?php
		endif;
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * This outputs a separate set of options above and below the table, in
	 * order to make room for the locations.
	 *
	 * @since 1.6
	 */
	public function display_tablenav( $which ) {

		global $rtb_controller;

		// Just call the parent method if locations aren't activated
		if ( 'top' === $which && empty( $rtb_controller->locations->post_type ) ) {
			$this->add_notification();
			parent::display_tablenav( $which );
			return;
		}

		// Just call the parent method for the bottom nav
		if ( 'bottom' == $which ) {
			parent::display_tablenav( $which );
			return;
		}

		$locations = $rtb_controller->locations->get_location_options();
		$all_locations = $rtb_controller->locations->get_location_options( false );
		$inactive_locations = array_diff( $all_locations, $locations );
		?>

		<div class="tablenav top rtb-top-actions-wrapper">
			<?php wp_nonce_field( 'bulk-' . $this->_args['plural'] ); ?>
			<?php $this->extra_tablenav( $which ); ?>
		</div>

		<?php $this->add_notification(); ?>

		<div class="rtb-table-header-controls">
			<?php if ( $this->has_items() ) : ?>
				<div class="actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
			<?php endif; ?>
			<ul class="rtb-locations">
				<li<?php if ( empty( $this->filter_location ) ) : ?> class="current"<?php endif; ?>>
					<a href="<?php echo esc_url( remove_query_arg( 'location', $this->query_string ) ); ?>"><?php esc_html_e( 'All Locations', 'restaurant-reservations' ); ?></a>
				</li>
				<?php
					$i = 0;
					foreach( $locations as $term_id => $name ) :
						if ( $i > 15 ) {
							break;
						} else {
							$i++;
						}
						?>

						<li<?php if ( $this->filter_location == $term_id ) : ?> class="current"<?php endif; ?>>
							<a href="<?php echo esc_url( add_query_arg( 'location', $term_id, $this->query_string ) ); ?>">
								<?php esc_html_e( $name ); ?>
							</a>
						</li>
				<?php endforeach; ?>
			</ul>
			<div class="rtb-location-switch">
				<select name="location">
					<option><?php esc_attr_e( 'All Locations', 'restaurant-reservations' ); ?></option>
					<?php foreach( $locations as $term_id => $name ) : ?>
						<option value="<?php esc_attr_e( $term_id ); ?>"<?php if ( $this->filter_location == $term_id ) : ?> selected="selected"<?php endif; ?>>
							<?php esc_attr_e( $name ); ?>
						</option>
					<?php endforeach; ?>
					<?php if ( !empty( $inactive_locations ) ) : ?>
						<optgroup label="<?php esc_attr_e( 'Inactive Locations' ); ?>">
							<?php foreach( $inactive_locations as $term_id => $name ) : ?>
								<option value="<?php esc_attr_e( $term_id ); ?>"<?php if ( $this->filter_location == $term_id ) : ?> selected="selected"<?php endif; ?>>
									<?php esc_attr_e( $name ); ?>
								</option>
							<?php endforeach; ?>
						</optgroup>
					<?php endif; ?>
				</select>
				<input type="submit" class="button rtb-locations-button" value="<?php esc_attr_e( 'Switch', 'restaurant-reservations' ); ?>">
			</div>
		</div>

		<?php
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string pos Position of this tablenav: `top` or `btm`
	 * @since 1.4.1
	 */
	public function extra_tablenav( $pos ) {
		do_action( 'rtb_bookings_table_actions', $pos );
	}

	/**
	 * Add notifications above the table to indicate which bookings are
	 * being shown.
	 * @since 1.3
	 */
	public function add_notification() {

		global $rtb_controller;

		$notifications = array();

		$status = '';
		if ( !empty( $_GET['status'] ) ) {
			$status = sanitize_text_field( $_GET['status'] );
			if ( $status == 'trash' ) {
				$notifications['status'] = __( "You're viewing bookings that have been moved to the trash.", 'restaurant-reservations' );
			} elseif ( !empty( $rtb_controller->cpts->booking_statuses[ $status ] ) ) {
				$notifications['status'] = sprintf( _x( "You're viewing bookings that have been marked as %s.", 'Indicates which booking status is currently being filtered in the list of bookings.', 'restaurant-reservations' ), $rtb_controller->cpts->booking_statuses[ $_GET['status'] ]['label'] );
			}
		}

		if ( !empty( $this->filter_start_date ) || !empty( $this->filter_end_date ) ) {
			$notifications['date'] = sprintf( _x( 'Only bookings from %s are being shown.', 'Notification of booking date range, eg - bookings from 2014-12-02-2014-12-05', 'restaurant-reservations' ), $this->get_current_date_range() );
		} elseif ( !empty( $_GET['date_range'] ) && $_GET['date_range'] == 'today' ) {
			$notifications['date'] = __( "Only today's bookings are being shown.", 'restaurant-reservations' );
		} elseif ( empty( $_GET['date_range'] ) ) {
			$notifications['date'] = __( 'Only upcoming bookings are being shown.', 'restaurant-reservations' );
		}

		if ( !empty( $this->filter_start_date ) || !empty( $this->filter_end_date ) ) {

			$booking_data = $this->get_current_date_range_booking_data();

			$notifications['count'] = sprintf( _x( 'There are %d bookings with a total of %s guests in this date range.', 'Gives the total number of bookings and guests for a date-filtered range in the admin area.', 'restaurant-reservations' ), $this->booking_counts['non_trash'], $this->get_current_date_range_guest_count() );
		}

		$notifications = apply_filters( 'rtb_admin_bookings_table_filter_notifications', $notifications );

		if ( !empty( $notifications ) ) :
		?>

			<div class="rtb-notice <?php echo esc_attr( $status ); ?>">
				<?php echo join( ' ', $notifications ); ?>
			</div>

		<?php
		endif;
	}

	/**
	 * Retrieve the counts of bookings
	 * @since 0.0.1
	 */
	public function get_booking_counts() {

		global $wpdb;

		$where = "WHERE p.post_type = '" . RTB_BOOKING_POST_TYPE . "'";

		if ( $this->filter_start_date !== null || $this->filter_end_date !== null ) {

			if ( $this->filter_start_date !== null ) {
				$start_date = new DateTime( $this->filter_start_date . ' ' . $this->filter_start_time );
				$where .= " AND p.post_date >= '" . $start_date->format( 'Y-m-d H:i:s' ) . "'";
			}

			if ( $this->filter_end_date !== null ) {
				if( empty( $this->filter_end_time ) ) {
					$this->filter_end_time = '23:59:58';
				}
				$end_date = new DateTime( $this->filter_end_date . ' ' . $this->filter_end_time );
				$where .= " AND p.post_date <= '" . $end_date->format( 'Y-m-d H:i:s' ) . "'";
			}

		} elseif ( !empty( $_GET['date_range'] ) ) {

			if ( $_GET['date_range'] ==  'today' ) {
				$where .= " AND p.post_date >= '" . date( 'Y-m-d', current_time( 'timestamp' ) ) . "' AND p.post_date <= '" . date( 'Y-m-d', current_time( 'timestamp' ) + 86400 ) . "'";
			}

		// Default date setting is to show upcoming bookings
		} else {
			$where .= " AND p.post_date >= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 3600 ) . "'";
		}

		// Filter by name
		if( ! empty( $this->filter_name ) ) {
			$where .= " AND p.post_title LIKE '%".esc_sql( $wpdb->esc_like( $this->filter_name ) )."%'";
		}

		$join = '';

		if ( ! empty( $this->filter_email ) or ! empty( $this->filter_phone ) or ! empty( $this->filter_table ) ) {

			$join .= " LEFT JOIN $wpdb->postmeta pm ON (pm.post_id=p.ID)";

			$where .= " AND pm.meta_key='rtb'";

			if ( ! empty( $this->filter_email ) ) { 

				$where .= " AND pm.meta_value LIKE '%\"email\";s:%:\"%" . esc_sql( $wpdb->esc_like( $this->filter_email ) ) . "%\"%'";
			}

			if ( ! empty( $this->filter_phone ) ) { 

				$where .= " AND pm.meta_value LIKE '%\"phone\";s:%:\"%" . esc_sql( $wpdb->esc_like( $this->filter_phone ) ) . "%\"%'";
			}

			if ( ! empty( $this->filter_table ) ) { 

				$where .= " AND pm.meta_value LIKE '%\"table\";a:%;s:" . strlen( $this->filter_table ) . ":\"" . esc_sql( $this->filter_table ) . "\"%'";
			}
		}

		if ( $this->filter_location ) {
			$join .= " LEFT JOIN $wpdb->term_relationships t ON (t.object_id=p.ID)";
			$where .= " AND t.term_taxonomy_id=" . absint( $this->filter_location );
		}

		$query = "SELECT p.post_status,count( * ) AS num_posts
			FROM $wpdb->posts p
			$join
			$where
			GROUP BY p.post_status
		";

		$count = $wpdb->get_results( $query, ARRAY_A );

		$this->booking_counts = array();
		foreach ( get_post_stati() as $state ) {
			$this->booking_counts[$state] = 0;
		}

		$this->booking_counts['all'] = 0;
		$this->booking_counts['non_trash'] = 0;
		foreach ( (array) $count as $row ) {
			$this->booking_counts[$row['post_status']] = $row['num_posts'];
			$this->booking_counts['all'] += $row['num_posts'];
			if ( $row['post_status'] != 'trash' ) { $this->booking_counts['non_trash'] += $row['num_posts']; }
		}
	}

	/**
	 * Retrieve all the data for all the bookings
	 * @since 0.0.1
	 */
	public function bookings_data() {

		$args = array(
			'posts_per_page'	=> $this->per_page,
		);

		if ( !empty( $this->filter_start_date ) ) {
			$args['start_date'] = $this->filter_start_date;
			$args['start_time'] = $this->filter_start_time;
		}

		if ( !empty( $this->filter_end_date ) ) {
			$args['end_date'] = $this->filter_end_date;
			$args['end_time'] = $this->filter_end_time;
		}

		if ( ! empty( $this->filter_name ) ) {
			$args['filter_name'] = $this->filter_name;
		}

		$query = new rtbQuery( $args, 'bookings-table' );
		$query->parse_request_args();
		$query->prepare_args();

		// Sort all bookings by newest first if no specific orderby is in play
		if ( $query->args['date_range'] == 'all' && !isset( $_REQUEST['orderby'] ) ) {
			$query->args['order'] = 'DESC';
		}

		$query->args = apply_filters( 'rtb_bookings_table_query_args', $query->args );

		$filtered_bookings = array();

		$email = ! empty( $_GET['filter_email'] ) ? sanitize_text_field( $_GET['filter_email'] ) : false;
		$phone = ! empty( $_GET['filter_phone'] ) ? $_GET['filter_phone'] : false;
		$table = ! empty( $_GET['filter_table'] ) ? sanitize_text_field( $_GET['filter_table'] ) : false;

		$filtered_bookings = array();

		foreach ( $query->get_bookings() as $reservation ) {

			if ( ! empty( $email ) and strpos( $reservation->email, $email ) === false ) { continue; }
			if ( ! empty( $phone ) and strpos( $reservation->phone, $phone ) === false ) { continue; }
			if ( ! empty( $table ) and ! in_array( $table, $reservation->table ) ) { continue; }

			$filtered_bookings[] = $reservation;
		}

		$this->bookings = $filtered_bookings;
	}

	/**
	 * Setup the final data for the table
	 * @since 0.0.1
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->bookings;

		$total_items   = empty( $_GET['status'] ) ? $this->booking_counts['non_trash'] : $this->booking_counts[$_GET['status']];

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}

	/**
	 * Gets the reason why a reservation was confirmed, either the rule that
	 * caused the confirmation, or the name of the user who manually confirmed it
	 * @since 2.6.3
	 */
	public function get_reservation_confirmation_reason( $booking ) {

		if ( $booking->confirmed_user > 0 ) {

			$user = get_user_by( 'id', $booking->confirmed_user );

			return $user ? $user->display_name : '';
		}
		elseif ( $booking->confirmed_user == -1 ) {

			return __( 'Party Size Auto-Confirm', 'restaurant-reservations' );
		}
		elseif ( $booking->confirmed_user == -2 ) {

			return __( 'Max Reservations Auto-Confirm', 'restaurant-reservations' );
		}
		elseif ( $booking->confirmed_user == -3 ) {

			return __( 'Max Seats Auto-Confirm', 'restaurant-reservations' );
		}


		return '';
	}

}
} // endif;
