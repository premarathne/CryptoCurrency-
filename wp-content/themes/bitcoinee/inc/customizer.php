<?php
/**
 * bitcoinee Theme Customizer
 *
 * @package bitcoinee
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

if ( !class_exists( '\WP_Customize_Control' ) ) {
    return null;
}

include( get_template_directory() . '/inc/class-customizer-toggle-control.php' );
include( get_template_directory() . '/inc/class-customizer-posts-dropdown-control.php' );

function bitcoinee_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
    

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '.site-title a',
			'render_callback' => 'bitcoinee_customize_partial_blogname',
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '.site-description',
			'render_callback' => 'bitcoinee_customize_partial_blogdescription',
		) );
	}

	$wp_customize->add_section( 'bitcoinee_misc_settings', array(
        'title'          => esc_html__( 'Bitcoinee Settings', 'bitcoinee' ),
        'priority'       => 106,
    ) );

    $wp_customize->add_section( 'bitcoinee_slider_settings', array(
    	'title'			=> esc_html__( 'Bitcoinee Slider', 'bitcoinee' ),
    	'description'	=> esc_html__( 'Edit Slider below Main Menu', 'bitcoinee' ),
    	'priority'		=> 105,
    ) );
 
    $wp_customize->add_setting( 'footer_credit_text', array(
        'default'        	=> 'Kades Themes',
        'sanitize_callback'	=> 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_setting( 'footer_credit_text_link', array(
        'default'           => 'https://kadesthemes.com/',
        'sanitize_callback'	=> 'esc_url_raw',
        'transport'         => 'postMessage',
    ) );
 	
    /*$wp_customize->add_setting( 'bitcoinee_header', array(
        'default'           => 'header_center',
        'sanitize_callback' => 'bitcoinee_sanitize_radio',
        'transport'         => 'postMessage',
    ) );*/

    /*$wp_customize->add_control( new WP_Customize_Control( $wp_customize,
        'bitcoinee_header_style',
        array(
            'label'          => __( 'Choose Header Style', 'bitcoinee' ),
            'section'        => 'bitcoinee_misc_settings',
            'settings'       => 'bitcoinee_header',
            'type'           => 'radio',
            'choices'        => array(
                'header_classic'   => __( 'Classic Header', 'bitcoinee' ),
                'header_center'  => __( 'Center Header', 'bitcoinee' )
                )
            )
        )
    );*/
    
	$wp_customize->add_setting( 'coin_index_bar', array(
        'default'        	=> '',
        'sanitize_callback'	=> 'bitcoinee_sanitize_checkbox',
    ) );

    $wp_customize->add_control( new Bitcoinee_Customizer_Toggle_Control( $wp_customize, 'coin_index_bar', array(
		'label'	      => esc_html__( 'Display Coin Index?', 'bitcoinee' ),
		'description' => esc_html__( 'Show or Hide coin index below Slider', 'bitcoinee' ),
		'section'     => 'bitcoinee_misc_settings',
		'settings'    => 'coin_index_bar',
		'type'        => 'light', // light, ios, flat
	) ) );

    $wp_customize->add_setting( 'bitcoinee_breaking_news', array(
        'default'           => '',
        'sanitize_callback' => 'bitcoinee_sanitize_checkbox',
    ) );

    $wp_customize->add_control( new Bitcoinee_Customizer_Toggle_Control( $wp_customize, 'bitcoinee_breaking_news', array(
        'label'       => esc_html__( 'Display Breaking News?', 'bitcoinee' ),
        'description' => esc_html__( 'Show or Hide Breaking News section', 'bitcoinee' ),
        'section'     => 'bitcoinee_misc_settings',
        'settings'    => 'bitcoinee_breaking_news',
        'type'        => 'light',
    ) ) );

    $wp_customize->add_control( 'footer_credit_text', array(
        'label'   => esc_html__( 'Footer Credit Text', 'bitcoinee' ),
        'section' => 'bitcoinee_misc_settings',
        'type'    => 'text',
    ) );

    $wp_customize->add_control( 'footer_credit_text_link', array(
    	'label'		=> esc_html__( 'Footer Credit Text URL', 'bitcoinee' ),
    	'section'	=> 'bitcoinee_misc_settings',
    	'type'		=> 'url'
    ) );
 
 	$wp_customize->add_setting( 'bitcoinee_slider_show', array(
        'default'        	=> '',
        'sanitize_callback'	=> 'bitcoinee_sanitize_checkbox',
    ) );

    $wp_customize->add_setting( 'bitcoinee_slides', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ) );

    $wp_customize->add_control( new Bitcoinee_Customizer_Toggle_Control( $wp_customize, 'bitcoinee_slider_toggle', array(
        'label'       => esc_html__( 'Show Slider?', 'bitcoinee' ),
        'description' => esc_html__( 'Slider will display below main menu', 'bitcoinee' ),
        'section'     => 'bitcoinee_slider_settings',
        'settings'    => 'bitcoinee_slider_show',
        'type'        => 'light',
    ) ) );

    $wp_customize->add_control( new Bitcoinee_Category_Control( $wp_customize, 'bitcoinee_slides_content', array(
        'label'             => esc_html__( 'Choose posts for slides', 'bitcoinee' ),
        'section'           => 'bitcoinee_slider_settings',
        'settings'          => 'bitcoinee_slides',
        'type'              => 'select',
    ) ) );

    // Sanitize callback
    function bitcoinee_sanitize_checkbox( $input ){
        return ( ( isset( $input ) && true == $input ) ? true : false );
    }

    // Sannitize radio type
    function bitcoinee_sanitize_radio( $input ) {
        $valid = array(
            'header_classic' => __( 'Classic Header', 'bitcoinee' ),
            'header_center' => __( 'Center Header', 'bitcoinee' ),
        );

        if ( array_key_exists( $input, $valid ) ) {
            return $input;
        }

        return '';
    }

}
add_action( 'customize_register', 'bitcoinee_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function bitcoinee_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function bitcoinee_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function bitcoinee_customize_preview_js() {
	wp_enqueue_script( 'bitcoinee-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '250418', true );
}
add_action( 'customize_preview_init', 'bitcoinee_customize_preview_js' );
