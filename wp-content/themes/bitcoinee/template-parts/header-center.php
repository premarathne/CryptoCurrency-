<header id="masthead" class="site-header container-fluid">
	<div class="headerwrap container-fluid">
			
		<div class="site-branding container">
			
		<?php if ( function_exists( 'the_custom_logo' ) && ( has_custom_logo() ) ) { ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php bloginfo('name'); ?>"><?php the_custom_logo(); ?></a>
		<?php } else { ?>
		<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
		<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>	
		<?php } ?>
		
		</div><!-- .site-branding -->
		
		<nav id="site-navigation" class="main-navigation container navbar navbar-expand-lg">
			<button type="button" class="navbar-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_html_e( 'Toggle Navigation', 'bitcoinee' ); ?>" data-toggle="collapse" data-target="#bitcoineeNavbar"><?php esc_html_e( 'Open Menu', 'bitcoinee' ); ?></button>
			
			<div class="collapse navbar-collapse" id="bitcoineeNavbar">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'primary-menu',
					'container'      => false,
					'depth'          => 2,
					'menu_class'     => 'navbar-nav ml-auto',
					'walker'         => new Bootstrap_NavWalker(),
					'fallback_cb'    => 'Bootstrap_NavWalker::fallback',
				) );
				?>

			</div>
			
		</nav><!-- #site-navigation -->
		
	</div><!-- /.headerwrap -->

	<?php get_template_part( 'template-parts/slider' ); ?>

</header><!-- #masthead -->