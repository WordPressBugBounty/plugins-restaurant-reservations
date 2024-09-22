<style>

	.date {
		font-size: 12pt;
	}
	.booking {
		font-size: 12pt;
		line-height: 100%;
	}
	.time {
		width: 15%;
	}
	.party {
		width: 5%;
	}
	.name {
		width: 25%;
		font-size: 12pt;
	}
	.details {
		width: 55%;
		font-size: 10pt;
		line-height: 120%;
		color: #333;
	}

</style>

<div class="bookings">

	<?php
		global $rtb_controller;
		foreach( $bookings as $booking ) :
			$booking_date = apply_filters( 'get_the_date', mysql2date( $rtb_controller->settings->get_setting( 'ebfrtb-csv-date-format' ), $booking->date ) );
			$summary_date_idx = mysql2date( 'Y-m-d', $booking->date );
	?>

	<?php // Display the date if we've hit a new day ?>
	<?php if ( !isset( $current_date ) || $summary_date_idx !== $current_date ) : ?>
		<h1 class="date" style="vertical-align:middle;"><?php echo esc_html( $booking_date ); ?></h1>
		<h4><?php echo sprintf( __( ' %d reservation(s), %d guest(s)', 'restaurant-reservations' ), esc_html( $this->bookings_summary[ $summary_date_idx ]['reservations'] ), esc_html( $this->bookings_summary[ $summary_date_idx ]['seats'] ) ); ?></h4>
	<?php $current_date = $summary_date_idx; ?>
	<?php endif; ?>

	<table class="booking">
		<tr>
			<td class="time">
				<?php echo esc_html( apply_filters( 'get_the_date', mysql2date( get_option( 'time_format' ), $booking->date ) ) ); ?>
			</td>
			<td class="party">
				<?php echo esc_html( $booking->party ); ?>
			</td>
			<td class="name">
				<?php echo esc_html( $booking->name ); ?>
			</td>
			<td class="details">
				<?php do_action( 'ebfrtb_tcpdf_before_details', $booking ); ?>
					<?php
						global $rtb_controller;
						if ( empty( $this->query_args['location'] ) && !empty( $booking->location ) ) {
							$term = get_term( $booking->location );
							if ( is_a( $term, 'WP_Term' ) ) :
								?>
								<div class="location">
									<?php echo esc_html( $term->name ); ?>
								</div>
								<?php
							endif;
						}
					?>
				<div class="email"><?php echo esc_html( $booking->email ); ?></div>

				<?php if ( !empty( $booking->phone ) ) : ?>
				<div class="phone"><?php echo esc_html( $booking->phone ); ?></div>
				<?php endif; ?>

				<?php if ( !empty( $booking->message ) ) : ?>
				<div class="message"><?php echo esc_html( $booking->message ); ?></div>
				<?php endif; ?>
				<?php do_action( 'ebfrtb_tcpdf_after_details', $booking ); ?>
			</td>
		</tr>
		<tr><td colspan="4"></td></tr>
	</table>

	<?php endforeach; ?>

</div>
