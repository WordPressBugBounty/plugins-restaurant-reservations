<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * ----------------------------------------------------------------------------
 *                               WP Bakery
 * ----------------------------------------------------------------------------
 * 
 * Ref: https://kb.wpbakery.com/docs/inner-api/vc_map/
 * 
 * Add our shortcode as an element under our own category
 * 
 * */
if ( function_exists( 'vc_map' ) ) {

add_action( 'admin_init', function() {
    /**
     * Booking form shortcode
     * */
    vc_map( array(
        "name"        => "Booking Form",
        "base"        => "booking-form",
        "description" => "Five Star Restaurant Reservations",
        "category"    => "Five Star Plugins",
        "params"      => array(
            array(
                "param_name"  => "location",
                "heading"     => "Location",
                "type"        => "textfield",
                "description" => "Location specific booking form. Put location ID here. You can get this ID from General Settings page, location selector dropdown.",
                "value" => ""
            )
        )
    ) );

    /**
     * View Bookings shortcode
     * */
    vc_map( array(
        "name"        => "View Bookings",
        "base"        => "view-bookings-form",
        "description" => "Five Star Restaurant Reservations",
        "category"    => "Five Star Plugins",
        "params"      => array(
            array(
                "param_name"  => "location",
                "heading"     => "Location",
                "type"        => "textfield",
                "description" => "List location specific bookings. Put location ID here. You can get this ID from General Settings page, location selector dropdown.",
                "value" => ""
            )
        )
    ) );
});

}


/**
 * ----------------------------------------------------------------------------
 *                               WP Bakery
 * ----------------------------------------------------------------------------
 * 
 * Ref: 
 * 
 * Add our shortcode as an element
 * 
 * */

// This neds more time
// https://visualcomposer.com/blog/how-to-create-custom-elements-using-visual-composer-api/#ce_development
// https://dev.visualcomposer.com/tutorials/how-to-create-button-element


/**
 * ----------------------------------------------------------------------------
 *                               Elementor
 * ----------------------------------------------------------------------------
 * 
 * 
 * Ref: https://developers.elementor.com/docs/widgets/
 * 
 * Add our shortcode as an element
 * 
 * */
add_action( 'init', function() {

if ( class_exists( '\Elementor\Widget_Base' ) ) {

/**
 * Register category for our Widget
 * */
function rtb_add_elementor_widget_categories( $elements_manager ) {

    $elements_manager->add_category(
        'five-star-plugins',
        [
            'title' => esc_html__( 'Five Star Plugins', 'restaurant-reservations' ),
            'icon' => 'fa fa-plug',
        ]
    );
}
add_action( 'elementor/elements/categories_registered', 'rtb_add_elementor_widget_categories' );

/**
 * This is our custom element for Booking Form
 * */
class Elementor_RTB_Widget_Booking_form extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @access public
     *
     * @return string Widget name.
     * */
    public function get_name() {
        return 'rtb_booking_form';
    }

    /**
     * Get widget title.
     * 
     * @access public
     *
     * @return string Widget title.
     * */
    public function get_title() {
        return esc_html__( 'Booking Form', 'restaurant-reservations' );
    }

    /**
     * Get widget icon.
     * 
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-calendar';
    }

    public function get_custom_help_url() {
        return 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/';
    }

    /**
     * Get widget categories.

     * Retrieve the list of categories our widget belongs to.
     * 
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['five-star-plugins'];
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     * 
     * @access public
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return [ 'five', 'star', 'reservation', 'restaurant', 'booking'];
    }

    /**
     * Lets you define the JS files required to run the widget
     * 
     * @return array List of registered script handles
     * */
    public function get_script_depends() {
        return array( 'rtb-booking-form' );
    }

    /**
     * Lets you define the CSS files required to run the widget
     * 
     * @return array List of registered style handles
     * */
    public function get_style_depends() {
        return array( 'rtb-booking-form' );
    }

    /**
     * Register widget controls.
     *
     * Adds location field to let the user choose specific location only.
     * 
     * @access protected
     */
    protected function register_controls() {
        global $rtb_controller;

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Location', 'restaurant-reservations' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'location',
            [
                'type' => \Elementor\Controls_Manager::SELECT,
                'label' => esc_html__( 'Select Location', 'restaurant-reservations' ),
                'options' => 
                    array( '' => esc_html__( 'Global', 'restaurant-reservations' ) )
                    +
                    $rtb_controller->locations->get_location_options(),
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     * 
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();

        echo do_shortcode( '[booking-form location="'.$settings['location'].'"]' );
    }

}

/**
 * This is our custom element for Booking Form
 * */
class Elementor_RTB_Widget_View_Bookings extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @access public
     *
     * @return string Widget name.
     * */
    public function get_name() {
        return 'rtb_view_bookings';
    }

    /**
     * Get widget title.
     * 
     * @access public
     *
     * @return string Widget title.
     * */
    public function get_title() {
        return esc_html__( 'View Booking', 'restaurant-reservations' );
    }

    /**
     * Get widget icon.
     * 
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-table-of-contents';
    }

    public function get_custom_help_url() {
        return 'https://doc.fivestarplugins.com/plugins/restaurant-reservations/';
    }

    /**
     * Get widget categories.

     * Retrieve the list of categories our widget belongs to.
     * 
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['five-star-plugins'];
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     * 
     * @access public
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return [ 'five', 'star', 'reservation', 'restaurant', 'booking', 'view', 'list'];
    }

    /**
     * Lets you define the JS files required to run the widget
     * 
     * @return array List of registered script handles
     * */
    public function get_script_depends() {
        return array( 'rtb-booking-form' );
    }

    /**
     * Lets you define the CSS files required to run the widget
     * 
     * @return array List of registered style handles
     * */
    public function get_style_depends() {
        return array( 'rtb-booking-form' );
    }

    /**
     * Register widget controls.
     *
     * Adds location field to let the user choose specific location only.
     * 
     * @access protected
     */
    protected function register_controls() {
        global $rtb_controller;

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Location', 'restaurant-reservations' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'location',
            [
                'type' => \Elementor\Controls_Manager::SELECT,
                'label' => esc_html__( 'Select Location', 'restaurant-reservations' ),
                'options' => 
                    array( '' => esc_html__( 'Global', 'restaurant-reservations' ) )
                    +
                    $rtb_controller->locations->get_location_options(),
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     * 
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();

        echo do_shortcode( '[view-bookings-form location="'.$settings['location'].'"]' );
    }

}

/**
 * Regsiter our widgets for Elementor
 * */
function rtb_elmntr_register_new_widgets( $widgets_manager ) {

    $widgets_manager->register( new Elementor_RTB_Widget_Booking_form() );
    $widgets_manager->register( new Elementor_RTB_Widget_View_Bookings() );
}
add_action( 'elementor/widgets/register', 'rtb_elmntr_register_new_widgets' );

}

});