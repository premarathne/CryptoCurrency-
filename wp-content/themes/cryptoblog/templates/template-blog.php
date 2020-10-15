<?php
/*
* Template Name: Blog
*/

get_header(); 

// Theme Init
$theme_init = new Cryptoblog_Init_Class;
?>

<!-- HEADER TITLE BREADCRUBS SECTION -->
<?php echo wp_kses_post(cryptoblog_header_title_breadcrumbs()); ?>


<?php
wp_reset_postdata();
$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
$args = array(
    'post_type'        => 'post',
    'post_status'      => 'publish',
    'paged'            => $paged,
);
$posts = new WP_Query( $args );
?>
<!-- Blog content -->
<div class="container blog-posts high-padding">
    
    <div class="row">

        <div class="col-md-9 main-content">
            <?php if ( $posts->have_posts() ) : ?>
                <?php /* Start the Loop */ ?>
                <div class="row">

                    <?php /* Start the Loop */ ?>
                    <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
                        <?php /* Loop - Variant 1 */ ?>
                        <?php get_template_part( 'content', $theme_init->cryptoblog_blogloop_variant() ); ?>
                    <?php endwhile; ?>
                    
                </div>
            <?php else : ?>
                <?php get_template_part( 'content', 'none' ); ?>
            <?php endif; ?>
            
            <div class="clearfix"></div>

            <?php 
            $wp_query = new WP_Query( $args );
            global  $wp_query;
            if ($wp_query->max_num_pages != 1) { ?>                
            <div class="cryptoblog-pagination-holder col-md-12">           
                <div class="cryptoblog-pagination pagination">           
                    <?php the_posts_pagination(); ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <div class="col-md-3 sidebar-content">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php
get_footer();
?>