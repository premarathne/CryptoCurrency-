<?php

if ( ! isset( $content_width ) ) {
    $content_width = 640; /* pixels */
}

/**
||-> cryptoblog_setup
*/
function cryptoblog_setup() {

    /*
     * Make theme available for translation.
     * Translations can be filed in the /languages/ directory.
     * If you're building a theme based on cryptoblog, use a find and replace
     * to change 'cryptoblog' to the name of your theme in all the template files
     */
    load_theme_textdomain( 'cryptoblog', get_template_directory() . '/languages' );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus( array(
        'primary' => esc_html__( 'Primary menu', 'cryptoblog' )
    ) );

    // ADD THEME SUPPORT
    // OTHER SUPPORT
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    $args_custom_header = array(
        'flex-width'    => true,
        'width'         => 980,
        'flex-height'    => true,
        'height'        => 200,
    );
    add_theme_support( 'custom-header', $args_custom_header );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
    ) );
    // Switch default core markup for search form, comment form, and comments to output valid HTML5.
    // Enable support for Post Formats.
    add_theme_support( 'custom-background', apply_filters( 'smartowl_custom_background_args', array(
        'default-color' => 'ffffff',
        'default-image' => '',
    ) ) );// Set up the WP core custom background feature.
    add_theme_support( 'custom-logo' );


    /* ========= RESIZE IMAGES ===================================== */
    add_image_size( 'cryptoblog_related_post_pic500x300', 500, 300, true );
    add_image_size( 'cryptoblog_post_pic700x450', 700, 450, true );

    // Blogloop-v2
    add_image_size( 'cryptoblog_blog_900x550', 900, 550, true );
    add_image_size( 'cryptoblog_blog_1400x350', 1400, 350, true );

}
add_action( 'after_setup_theme', 'cryptoblog_setup' );

/* CUSTOM CUSTOMIZER */
include get_template_directory() . '/customizer/customizer.php';

/**
||-> Register widget areas.
*/
function cryptoblog_widgets_init() {

    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar Main', 'cryptoblog' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Main Theme Sidebar', 'cryptoblog' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h1 class="widget-title">',
        'after_title'   => '</h1>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar Secondary', 'cryptoblog' ),
        'id'            => 'sidebar-2',
        'description'   => esc_html__( 'Secondary Theme Sidebar - Used on templates with both sidebars', 'cryptoblog' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h1 class="widget-title">',
        'after_title'   => '</h1>',
    ) );
    
}
add_action( 'widgets_init', 'cryptoblog_widgets_init' );


/**
||-> Enqueue scripts and styles.
*/
function cryptoblog_scripts() {

    //STYLESHEETS
    wp_enqueue_style( "font-awesome", get_template_directory_uri().'/css/font-awesome.min.css' );
    wp_enqueue_style( "bootstrap", get_template_directory_uri().'/css/bootstrap.min.css' );
    wp_enqueue_style( "cryptoblog-media-screens", get_template_directory_uri().'/css/media-screens.css' );
    wp_enqueue_style( "jquery-owl-carousel", get_template_directory_uri().'/css/jquery.owl.carousel.css' );
    wp_enqueue_style( "animate", get_template_directory_uri().'/css/animate.css' );
    wp_enqueue_style( "cryptoblog-style", get_template_directory_uri().'/css/styles.css' );
    wp_enqueue_style( 'cryptoblog-mt-style', get_stylesheet_uri() );
    wp_enqueue_style( "cryptoblog-blogloops-style", get_template_directory_uri().'/css/styles-module-blogloops.css' );
    wp_enqueue_style( "cryptoblog-navigations-style", get_template_directory_uri().'/css/styles-module-navigations.css' );
    wp_enqueue_style( "cryptoblog-header-style", get_template_directory_uri().'/css/styles-headers.css' );
    wp_enqueue_style( "cryptoblog-footer-style", get_template_directory_uri().'/css/styles-footer.css' );
    wp_enqueue_style( "loaders", get_template_directory_uri().'/css/loaders.css' );
    wp_enqueue_style( "simple-line-icons", get_template_directory_uri().'/css/simple-line-icons.css' );
    wp_enqueue_style( "montserrat", '//fonts.googleapis.com/css?family=Montserrat:400,400i,600,700,800&amp;subset=latin-ext' );

    //SCRIPTS
    wp_enqueue_script( 'modernizr-custom', get_template_directory_uri() . '/js/modernizr.custom.min.js', array('jquery'), '2.6.2', true );
    wp_enqueue_script( 'classie', get_template_directory_uri() . '/js/classie.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'jquery-sticky', get_template_directory_uri() . '/js/jquery.sticky.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'jquery-owl-carousel', get_template_directory_uri() . '/js/jquery.owl.carousel.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'modernizr-viewport', get_template_directory_uri() . '/js/modernizr.viewport.min.js', array('jquery'), '2.6.2', true );
    wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '3.3.1', true );
    wp_enqueue_script( 'loaders', get_template_directory_uri() . '/js/loaders.css.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'cryptoblog-custom-js', get_template_directory_uri() . '/js/cryptoblog-custom.js', array('jquery'), '1.0.0', true );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'cryptoblog_scripts' );



/**
||-> Other required parts/files
*/
/* ========= LOAD CUSTOM FUNCTIONS ===================================== */
require_once get_template_directory() . '/inc/custom-functions.php';
require_once get_template_directory() . '/inc/custom-functions.header.php';
require_once get_template_directory() . '/inc/custom-functions.footer.php';
/* ========= Load Jetpack compatibility file. ===================================== */
require_once get_template_directory() . '/inc/jetpack.php';
/* ========= Include the TGM_Plugin_Activation class. ===================================== */
require_once get_template_directory() . '/inc/tgm/include_plugins.php';
/* ========= CUSTOM COMMENTS ===================================== */
require_once get_template_directory() . '/inc/custom-comments.php';
/* ========= THEME DEFAULTS ===================================== */
require_once get_template_directory() . '/inc/theme-defaults.php';
/* ========= Include Theme Info page. ===================================== */
require get_template_directory() . '/inc/theme-info.php';




/**
||-> BREADCRUMBS
*/
function cryptoblog_breadcrumb() {
    
    $delimiter = '';
    $html =  '';

    $name = esc_html__("Home", "cryptoblog");
    $currentBefore = '<li class="active">';
    $currentAfter = '</li>';

        if (!is_home() && !is_front_page() || is_paged()) {
            global  $post;
            $home = esc_url(home_url('/'));
            $html .= '<li><a href="' . esc_url($home) . '">' . esc_attr($name) . '</a></li> ' . esc_attr($delimiter) . '';
        
        if (is_category()) {
            global  $wp_query;
            $cat_obj = $wp_query->get_queried_object();
            $thisCat = $cat_obj->term_id;
            $thisCat = get_category($thisCat);
            $parentCat = get_category($thisCat->parent);
                if ($thisCat->parent != 0)
            $html .= (get_category_parents($parentCat, true, '' . esc_attr($delimiter) . ''));
            $html .= $currentBefore . get_the_archive_title() . $currentAfter;
        }elseif (is_tax()) {
            global  $wp_query;
            $html .= $currentBefore . get_the_archive_title() . $currentAfter;
        } elseif (is_day()) {
            $html .= '<li><a href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . get_the_time('Y') . '</a></li> ' . esc_attr($delimiter) . '';
            $html .= '<li><a href="' . esc_url(get_month_link(get_the_time('Y')), get_the_time('m')) . '">' . get_the_time('F') . '</a></li> ' . esc_attr($delimiter) . ' ';
            $html .= $currentBefore . get_the_time('d') . $currentAfter;
        } elseif (is_month()) {
            $html .= '<li><a href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . get_the_time('Y') . '</a></li> ' . esc_attr($delimiter) . '';
            $html .= $currentBefore . get_the_time('F') . $currentAfter;
        } elseif (is_year()) {
            $html .= $currentBefore . get_the_time('Y') . $currentAfter;
        } elseif (is_attachment()) {
            $html .= $currentBefore;
            $html .= get_the_title();
            $html .= $currentAfter;
        } elseif (is_single()) {
            if (get_the_category()) {
                $cat = get_the_category();
                $cat = $cat[0];
                $html .= '<li>' . get_category_parents($cat, true, ' ' . esc_attr($delimiter) . '') . '</li>';
            }
            $html .= $currentBefore;
            $html .= get_the_title();
            $html .= $currentAfter;
        } elseif (is_page() && !$post->post_parent) {
            $html .= $currentBefore;
            $html .= get_the_title();
            $html .= $currentAfter;
        } elseif (is_page() && $post->post_parent) {
            $parent_id = $post->post_parent;
            $breadcrumbs = array();
            while ($parent_id) {
                $page = get_page($parent_id);
                $breadcrumbs[] = '<li><a href="' . esc_url(get_permalink($page->ID)) . '">' . get_the_title($page->ID) . '</a></li>';
                $parent_id = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            foreach ($breadcrumbs as $crumb)
                $html .= $crumb . ' ' . esc_attr($delimiter) . ' ';
            $html .= $currentBefore;
            $html .= get_the_title();
            $html .= $currentAfter;
        } elseif (is_search()) {
            $html .= $currentBefore . get_search_query() . $currentAfter;
        } elseif (is_tag()) {
            $html .= $currentBefore . get_the_archive_title() . $currentAfter;
        } elseif (is_author()) {
            global  $author;
            $userdata = get_userdata($author);
            $html .= $currentBefore . $userdata->display_name . $currentAfter;
        } elseif (is_404()) {
            $html .= $currentBefore . esc_html__('404 Not Found','cryptoblog') . $currentAfter;
        }
        if (get_query_var('paged')) {
            if (is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author())
                $html .= $currentBefore;
            $html .= esc_html__('Page','cryptoblog') . ' ' . get_query_var('paged');
            if (is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author())
                $html .= $currentAfter;
        }
    }

    return $html;
}



/**
||-> FUNCTION: ADD EDITOR STYLE
*/
function cryptoblog_add_editor_styles() {
    add_editor_style( 'css/custom-editor-style.css' );
}
add_action( 'admin_init', 'cryptoblog_add_editor_styles' );


?>