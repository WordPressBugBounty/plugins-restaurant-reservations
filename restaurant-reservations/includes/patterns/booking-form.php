<?php
/**
 * Booking form
 */
return array(
    'title'       =>	__( 'Booking Form', 'restaurant-reservations' ),
    'description' =>	_x( 'Adds your restaurant booking form.', 'Block pattern description', 'restaurant-reservations' ),
    'categories'  =>	array( 'rtb-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"rtb-pattern-booking-form"} -->
                        <div class="wp-block-group rtb-pattern-booking-form"><!-- wp:restaurant-reservations/booking-form /--></div>
                        <!-- /wp:group -->',
);
