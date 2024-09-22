<?php
/**
 * View bookings form
 */
return array(
    'title'       =>	__( 'View Bookings', 'restaurant-reservations' ),
    'description' =>	_x( 'Adds the view bookings form.', 'Block pattern description', 'restaurant-reservations' ),
    'categories'  =>	array( 'rtb-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"rtb-pattern-view-bookings"} -->
                        <div class="wp-block-group rtb-pattern-view-bookings"><!-- wp:shortcode -->
                        [view-bookings-form]
                        <!-- /wp:shortcode --></div>
                        <!-- /wp:group -->',
);
