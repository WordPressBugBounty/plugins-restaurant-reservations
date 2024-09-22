<?php
/**
 * Modify booking form
 */
return array(
    'title'       =>	__( 'Modify Booking', 'restaurant-reservations' ),
    'description' =>	_x( 'Adds the booking form opened directly to the modify reservation form. (Allow Cancellations option must be enabled.)', 'Block pattern description', 'restaurant-reservations' ),
    'categories'  =>	array( 'rtb-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"rtb-pattern-modify-booking"} -->
                        <div class="wp-block-group rtb-pattern-modify-booking"><!-- wp:restaurant-reservations/booking-form /--></div>
                        <!-- /wp:group -->',
);
