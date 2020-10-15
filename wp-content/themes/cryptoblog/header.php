<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php esc_attr(bloginfo( 'charset' )); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php
        echo '<div class="cryptoblog_preloader_holder">'.wp_kses_post(cryptoblog_loader_animation()).'</div>'; 
    ?>

    <?php /* SEARCH BLOCK */ ?>
    <!-- Fixed Search Form -->
    <div class="fixed-search-overlay">
        <!-- Close Sidebar Menu + Close Overlay -->
        <i class="icon-close icons"></i>
        <!-- INSIDE SEARCH OVERLAY -->
        <div class="fixed-search-inside">
            <div class="cryptoblog-search">
                <?php echo get_search_form(); ?>
            </div>
        </div>
    </div>


    <?php /* BURGER MENU */ ?>
    <!-- Fixed Sidebar Overlay -->
    <div class="fixed-sidebar-menu-overlay"></div>
    <!-- Fixed Sidebar Menu -->
    <div class="relative fixed-sidebar-menu-holder header7">
        <div class="fixed-sidebar-menu">
            <!-- Close Sidebar Menu + Close Overlay -->
            <i class="icon-close icons"></i>
            <!-- Sidebar Menu Holder -->
            <div class="header7 sidebar-content">
                <!-- RIGHT SIDE -->
                <div class="left-side">

                <?php $custom_logo_id = get_theme_mod( 'custom_logo' );
                $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                if ( has_custom_logo() ) { ?>
                    <h1 class="logo">
                      <a href="<?php echo esc_url(home_url()); ?>">
                          <img src="<?php echo esc_url( $logo[0] ); ?>" alt="<?php echo esc_attr(get_bloginfo()); ?>" />
                      </a>
                  </h1>
                <?php } else { ?>
                    <h1 class="logo no-logo">
                      <a href="<?php echo esc_url(home_url()); ?>">
                        <?php echo esc_html(get_bloginfo()); ?>
                      </a>
                    </h1>
                <?php } ?>

                </div>
            </div>
        </div>
    </div>


    <!-- PAGE #page -->
    <div id="page" class="hfeed site">
        <?php          
            get_template_part( 'templates/template-header2');
        ?>