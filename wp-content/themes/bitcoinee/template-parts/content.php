<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package bitcoinee
 */

?>
<?php
	if( true == is_sticky() ) { ?>
		<?php /* Display customize layout if current post is sticky */ ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'row' ); ?>>
	
	<figure class="entry-thumbnail col-lg-12 col-md-12 col-sm-12 col-12">
		<?php if( has_post_thumbnail() ) {
			the_post_thumbnail( 'large' );	
		} else {
			echo '<img src="'. get_template_directory_uri() . '/images/no-thumbnail.jpg'.'">';
		}

		 ?>
	</figure>
	<div class="entry-summary col-lg-12 col-md-12 col-sm-12 col-12">
		<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
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
			the_excerpt( sprintf(
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
	<?php if( is_single() ) { ?>
		<footer class="entry-footer">
		<?php bitcoinee_entry_footer(); ?>
		</footer><!-- .entry-footer -->
	<?php } ?>
	
	</div><!--/ .entry-summary-->
	<hr class="hline">
</article> <?php
	} else { // normal articles ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'row' ); ?>>
	<figure class="entry-thumbnail col-lg-4 col-md-4 col-sm-12 col-12">
		<?php if( has_post_thumbnail() && false == is_singular() ) {
			the_post_thumbnail( 'thumbnail' );	
		} else {
			echo '<img alt="no thumbnail" src="'. get_template_directory_uri() . '/images/no-thumbnail.jpg'.'">';
		}

		 ?>
	</figure>
	<div class="entry-summary col-lg-8 col-md-8 col-sm-12 col-12">
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
			the_excerpt( sprintf(
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
	<?php if( is_single() ) { ?>
		<footer class="entry-footer">
		<?php bitcoinee_entry_footer(); ?>
		</footer><!-- .entry-footer -->
	<?php } ?>
	
	</div><!--/ .entry-summary-->
	<hr class="hline">
</article><!-- #post-<?php the_ID(); ?> -->
<?php } ?>
