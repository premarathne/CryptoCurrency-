<?php

$bitcoinee_category = get_theme_mod( 'bitcoinee_slides' );

$bitcoinee_slider_toggle = get_theme_mod( 'bitcoinee_slider_show' );
if ( ! empty( $bitcoinee_slider_toggle ) ) {
	if ( is_home() || is_front_page() ) {		

$slides_args = array(
	'post_type'			=> 'post',
	'posts_per_page'	=> 5,
	'category__in'		=> $bitcoinee_category,
	'no_found_rows'		=> true,
);
$bitcoinee_slides = new WP_Query( $slides_args );
if( $bitcoinee_slides->have_posts() ) : ?>
	<div id="slider" class="slider owl-carousel">				
	
	<?php while( $bitcoinee_slides->have_posts() ) : $bitcoinee_slides->the_post();
	$attachment_id = get_post_thumbnail_id();
	$image_attributes = wp_get_attachment_image_src( $attachment_id, 'large' ); ?>
					
		<div class="slide-item">
			<div class="slide-caption animated slideInRight">
	        	<h3><?php echo get_the_title(); ?></h3>
	        	<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="slide-cta"><?php _e('View more', 'bitcoinee'); ?></a>         
			</div>
			<img src="<?php echo esc_url( $image_attributes[0] ); ?>">
		</div>
	
	<?php endwhile;  wp_reset_postdata(); ?>
	</div><!-- /slider -->
	<?php endif; 

	}
} 
