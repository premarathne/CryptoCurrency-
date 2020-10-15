<?php $coinbar = get_theme_mod( 'coin_index_bar' );
$bitcoinee_header_style = get_theme_mod( 'bitcoinee_header' );
if ( 1 == $coinbar ) {		?>

	<div id="cryptoindex" class='<?php
		
		if( 'header_classic' === $bitcoinee_header_style ) {
			echo "container";
		}
		elseif( 'header_center' === $bitcoinee_header_style ) {
			echo "container-fluid";
		}
		?>'>

			<?php if( is_active_sidebar( 'bitcoinee-crypto-market-index' ) ) : ?>
				<div id="cryptoindex-inner" class="container" role="complementary">
					<?php dynamic_sidebar( 'bitcoinee-crypto-market-index' ); ?>
				</div><!-- #primary-sidebar -->
			<?php endif; ?>
	</div>

		<?php
}
?>
