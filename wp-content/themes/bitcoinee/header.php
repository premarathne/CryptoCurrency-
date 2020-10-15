<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package bitcoinee
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> itemscope itemtype="http://schema.org/WebPage">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<?php
$bitcoinee_header_style = 'header_center';
?>

<body <?php body_class($bitcoinee_header_style); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'bitcoinee' ); ?></a>

		<?php
		if( 'header_classic' === $bitcoinee_header_style ) {
			get_template_part( 'template-parts/header', 'classic' );
		}
		elseif( 'header_center' === $bitcoinee_header_style ) {
			get_template_part( 'template-parts/header', 'center' );
		}
		?>

	<?php get_template_part( 'template-parts/coin-index' ); ?>

	<div id="content" class="site-content container">
		
	<?php $bitcoinee_breakingnews_toggle = get_theme_mod( 'bitcoinee_breaking_news' );
if ( ! empty( $bitcoinee_breakingnews_toggle ) ) {
		if ( is_home() || is_front_page() ) {
				echo "<div class='posts-heading row'><h3 class='col-lg-2 col-md-3 col-sm-12 col-xs-12'>" . esc_html__( 'Breaking News', 'bitcoinee' ) . "</h3>"; ?>
					<div class="breakingnews-list col-lg-10 col-md-9 col-sm-12 col-xs-12">
					<?php
					// WP_Query arguments
					$args = array(
						'post_type'     => array( 'post' ),
						'post_status'   => array( 'publish' ),
						'tag'			=> array( 'breakingnews' ),
						'orderby'		=> 'date',
						'no_found_rows' => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'fields'				=> 'ids',
					);

					// The Query
					$breakingnews = new WP_Query( $args );

					// The Loop
					if ( $breakingnews->have_posts() ) { 
						while ( $breakingnews->have_posts() ) {
							$breakingnews->the_post(); ?>
							<a class="breaking-title" href="<?php esc_url( the_permalink() ); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					<?php } 
					} else {
						echo '<p>'.esc_html__( 'Posts with tagged "breakingnews" will show up here!', 'bitcoinee' ).'</p>';
					}

					// Restore original Post Data
					wp_reset_postdata(); ?>

					<?php echo "</div></div>"; // end .posts-heading
} } ?>

			<?php
			if ( function_exists('yoast_breadcrumb') && !is_home() ) {
			yoast_breadcrumb('
			<div id="breadcrumbs">','</div>
			');
			}
			?>
		<?php //support Yoast SEO breadcrumb ?>
		<div class="row">
