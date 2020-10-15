<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package bitcoinee
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<div class="entry-summary">
		<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;

		if ( 'post' === get_post_type() ) : ?>
		<div class="entry-meta">
			<?php bitcoinee_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php
		endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
			the_content( sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'bitcoinee' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			) );

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'bitcoinee' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
	<?php bitcoinee_entry_footer(); ?>
	</footer><!-- .entry-footer -->

	<!-- Related Posts -->
	<?php

	global $post;
	$categories = get_the_category( $post->ID );

	if( $categories ) {
		$category_ids = array();
		foreach ( $categories as $category ) {
			$category_ids[] = $category->term_id;
		}
	}
	$relatedposts = array(
		'post_type'              	=> array( 'post' ),
		'post_status'            	=> array( 'publish' ),
		'category__in'				=>	$category_ids,
		'post__not_in'				=> array($post->ID),
		'posts_per_page'         	=> 4,
		'ignore_sticky_posts'    	=> 1,
		'no_found_rows'				=> true, // Don't need pagination, speed up database queries
	);

	$bitcoinee_relatedposts = new WP_Query( $relatedposts );

	if ( $bitcoinee_relatedposts->have_posts() ) {
		echo '<div class="related-posts row"><h3 class="related-title">' . esc_html__( "Related Articles", "bitcoinee" ) . '</h3>';
		while ( $bitcoinee_relatedposts->have_posts() ) {
			$bitcoinee_relatedposts->the_post(); ?>
			<article class="relatedpost col-lg-3 col-6">
				<a title="<?php the_title_attribute(); ?>" href="<?php echo esc_url( get_the_permalink() ); ?>">
				<?php 
				if( has_post_thumbnail() ) { the_post_thumbnail( 'thumbnail' ); } ?>
				<?php the_title( '<h4 class="relatedpost-title">', '</h4>' ); ?>
				</a>
				
			</article>
			<?php
		}
		echo '</div>';
	} else {
		// no posts found
	}

	wp_reset_postdata();
	?>

	<?php if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif; ?>
	
	</div><!--/ .entry-summary-->
	
</article><!-- #post-<?php the_ID(); ?> -->
