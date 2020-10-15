<?php
/**
 * Plugin Name:Cryptocurrency Widgets
 * Description:Cryptocurrency Widgets WordPress plugin displays current prices of crypto coins - bitcoin, ethereum, ripple etc. similar like CoinMarketCap. Add <strong><a href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050">premium cryptocurrency widgets</a></strong> inside your crypto blog or website. <strong><a href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844">Click to create a website similar like coinmarketcap.com.</a></strong>
 * Author:Cool Plugins
 * Author URI:https://coolplugins.net/
 * Plugin URI:https://cryptowidget.coolplugins.net/
 * Version: 2.0.2
 * License: GPL2
 * Text Domain:ccpw
 * Domain Path: languages
 *
 *@package Cryptocurrency Price Ticker Widget*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'Crypto_Currency_Price_Widget_VERSION' ) ) {
	return;
}

/*
	Defined constent for later use
*/
define( 'Crypto_Currency_Price_Widget_VERSION', '2.0.2' );
define( 'Crypto_Currency_Price_Widget_FILE', __FILE__ );
define( 'Crypto_Currency_Price_Widget_PATH', plugin_dir_path( Crypto_Currency_Price_Widget_FILE ) );
define( 'Crypto_Currency_Price_Widget_URL', plugin_dir_url( Crypto_Currency_Price_Widget_FILE ) );
define( 'CRYPTO_API' , "https://api-beta.coinexchangeprice.com/v1/" );

register_deactivation_hook( Crypto_Currency_Price_Widget_FILE, array( 'Crypto_Currency_Price_Widget', 'ccpw_deactivate' ) );


/**
 * Class Crypto_Currency_Price_Widget
 */
final class Crypto_Currency_Price_Widget {

	/**
	 * Plugin instance.
	 *
	 * @var Crypto_Currency_Price_Widget
	 * @access private
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return Crypto_Currency_Price_Widget
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {

		register_activation_hook( Crypto_Currency_Price_Widget_FILE, array( $this , 'ccpw_activate' ) );
	
		$this->ccpw_includes();
		$this->ccpw_installation_date();
		add_action('init', array($this,'ccpw_plugin_version_verify' ));
		add_action( 'plugins_loaded', array( $this, 'ccpw_plugin_init' ) );
		//main plugin shortcode for list widget
		add_shortcode( 'ccpw', array( $this, 'ccpw_shortcode' ));

		add_action( 'save_post', array( $this,'save_ccpw_shortcode'),10, 3 );

		//creating posttype for plugin settings panel
		add_action( 'init','ccpw_post_type');
		// integrating cmb2 metaboxes in post type
		add_action( 'cmb2_admin_init','cmb2_ccpw_metaboxes');
		add_action( 'add_meta_boxes','register_ccpw_meta_box');

		// ajax call for datatable server processing
		add_action('wp_ajax_ccpw_get_coins_list', array($this, 'ccpw_get_coins_list'));
		add_action('wp_ajax_nopriv_ccpw_get_coins_list', array($this, 'ccpw_get_coins_list'));

		// check coin market cap plugin is activated.
		add_action('admin_init', array($this, 'ccpw_check_cmc_activated'));

		// update database only if required.
		add_action('init', array($this, 'ccpw_cron_coins_autoupdater'));

		add_action( 'wp_footer', array($this,'ticker_in_footer'));
		add_action( 'wp_footer', array($this,'ccpw_enable_ticker') );

		if(is_admin()){
			add_action( 'admin_init',array($this,'ccpw_check_installation_time'));
			add_action( 'admin_init',array($this,'ccpw_spare_me'), 5 );
			
			add_action('admin_enqueue_scripts', array($this,'ccpw_load_scripts'));
			add_action('admin_head-edit.php', array($this, 'ccpw_custom_btn'));	
			add_action( 'add_meta_boxes_ccpw','ccpw_add_meta_boxes');

			add_filter( 'manage_ccpw_posts_columns',array($this,'set_custom_edit_ccpw_columns'));
			add_action( 'manage_ccpw_posts_custom_column' ,array($this,'custom_ccpw_column'), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'ccpw_add_widgets_action_links'));
		
		}
		add_filter('cron_schedules', array($this, 'ccpw_cron_schedules')); 
		add_action('ccpw_coins_autosave', array($this, 'ccpw_cron_coins_autoupdater'));
	}

	/**
	 * Cron status schedule(s)
	 */
	function ccpw_cron_schedules($schedules)
	{
		// 5 minute schedule for grabing all coins 
		if (!isset($schedules["5min"])) {
			$schedules["5min"] = array(
				'interval' => 5 * 60,
				'display' => __('Once every 5 minutes')
			);
		}
		return $schedules;
	}

	/**
	 * initialize cron : MUST USE ON PLUGIN ACTIVATION
	 */
	public function ccpw_cron_job_init(){
		if (!wp_next_scheduled('ccpw_coins_autosave')) {
			wp_schedule_event(time(), '5min', 'ccpw_coins_autosave');
		}
	}

	/*
	|-----------------------------------------------------------
	|	This will update database after specific interval
	|-----------------------------------------------------------
	|	Always use this function to update database
	|-----------------------------------------------------------
	*/
	public function ccpw_cron_coins_autoupdater(){

		/**
		 * provide filter to let developer choose between different APIs
		 * 
		 * simple use example:
		 * add_filter('CCPW_CryptoWidgetApi', 
		 * 		function( $api ){
		 *			return 'CoinExchangePrice';
		 * 		}
		 * 	);
		 * 
		 * */	
		$api = strtolower( apply_filters( 'CCPW_CryptoWidgetApi', 'CoinGecko' ) );
				
		switch( $api ){
			case 'coinexchangeprice':
				ccpw_get_coinexchangeprice_api_data();
			break;
			case 'coingecko':
			default:
				ccpw_get_coin_gecko_data();
			break;
		}
	}

	/**
	 * Load plugin function files here.
	 */
	public function ccpw_includes() {

		/**
		 * Get the bootstrap!
		 */
		if ($this->ccpw_get_post_type_page() == "ccpw") {
			require_once __DIR__ . '/cmb2/init.php';
			require_once __DIR__ . '/cmb2/cmb2-conditionals.php';
			require_once __DIR__ . '/cmb2/cmb-field-select2/cmb-field-select2.php';
		}

		//loading required functions
		require_once __DIR__ . '/includes/ccpw-db-helper.php';
		require_once __DIR__ . '/includes/ccpw-functions.php';
		require_once __DIR__ . '/includes/ccpw-widget.php';
	}
	
	// loading required assets according to the type of widget
function ccpw_enqueue_assets($type, $post_id){
	if (!wp_script_is('jquery', 'done')) {
		wp_enqueue_script('jquery');
	}
	wp_enqueue_style('ccpw-bootstrap', Crypto_Currency_Price_Widget_URL.'assets/css/bootstrap.min.css');
	wp_enqueue_style('ccpw-custom-icons', Crypto_Currency_Price_Widget_URL.'assets/css/ccpw-icons.css');
	// ccpw main styles file
	wp_enqueue_style('ccpw-styles', Crypto_Currency_Price_Widget_URL. 'assets/css/ccpw-styles.css', array(), null, null, 'all');

	// loading Scripts for ticker widget
	if($type=="ticker"){
	$ticker_id = "ccpw-ticker-widget-" . $post_id;
	wp_enqueue_script('ccpw_bxslider_js', '//cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js', array('jquery'), null, true);
	wp_add_inline_script('ccpw_bxslider_js', 'jQuery(document).ready(function($){
		$(".ccpw-ticker-cont #'.$ticker_id.'").each(function(index){
			var tickerCon=$(this);
			var ispeed=Number(tickerCon.attr("data-tickerspeed"));
			$(this).bxSlider({
				ticker:true,
				minSlides:1,
				maxSlides:12,
				slideWidth:"auto",
				tickerHover:true,
				wrapperClass:"tickercontainer",
				speed: ispeed+ispeed,
				infiniteLoop:true
			});
		});
	});' );

	// multicurrency tab script
	} else if($type=="multi-currency-tab"){
		wp_enqueue_script('ccpw_script', Crypto_Currency_Price_Widget_URL. 'assets/js/ccpw-script.js',array('jquery'));
	}else if( $type == "table-widget"){
		// loads advance table scripts and styles
		wp_enqueue_script('ccpw-datatable', Crypto_Currency_Price_Widget_URL. 'assets/js/jquery.dataTables.min.js');
		wp_enqueue_script('ccpw-headFixer', Crypto_Currency_Price_Widget_URL. 'assets/js/tableHeadFixer.js');
		wp_enqueue_style('ccpw-custom-datatable-style', Crypto_Currency_Price_Widget_URL. 'assets/css/ccpw-custom-datatable.css');
		wp_enqueue_script('ccpw-table-script', Crypto_Currency_Price_Widget_URL. 'assets/js/ccpw-table-widget.min.js',array('jquery'));
		wp_localize_script(
			'ccpw-table-script',
			'ccpw_js_objects',
			array('ajax_url' => admin_url('admin-ajax.php'))
		);
		wp_enqueue_script('ccpw-numeral', Crypto_Currency_Price_Widget_URL. 'assets/js/numeral.min.js',array('jquery'));
		wp_enqueue_script('ccpw-table-sort', Crypto_Currency_Price_Widget_URL. 'assets/js/tablesort.min.js',array('jquery'));
	}
	
}

	/**
	 * Crypto Widget Main Shortcode
	 */

	function ccpw_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'id'  => '',
			'class' => ''
		), $atts, 'ccpw' );
	
		$post_id=$atts['id'];
	
		/*
		 *	Return if post status is anything other than 'publish'
		 */
		if( get_post_status( $post_id ) != "publish" ){
			return;
		}

			// calling cron function to update database if required
			$this->ccpw_cron_coins_autoupdater();
		
		// Grab the metadata from the database
		$type = get_post_meta($post_id,'type', true );
		$currency = get_post_meta($post_id, 'currency', true);
		$enable_formatting = get_post_meta($post_id, 'enable_formatting', true);
		$show_credit = get_post_meta($post_id, 'ccpw_coinexchangeprice_credits', true);
		$credit_html	=	'<div class="ccpw-credits"><a href="https://www.coingecko.com" target="_blank" rel="nofollow">Powered by CoinGecko API</a></div>';
		$fiat_currency= $currency ? $currency :"USD";
		$ticker_position = get_post_meta($post_id,'ticker_position', true );
		$header_ticker_position = get_post_meta($post_id,'header_ticker_position', true );
		$ticker_speed =(int) get_post_meta($post_id,'ticker_speed', true ) ;
		$t_speed=$ticker_speed*1000;
	
		$display_currencies = get_post_meta($post_id,'display_currencies', true );
		if($display_currencies==false){
			$display_currencies=array();
		}
		$datatable_currencies	= get_post_meta($post_id,'display_currencies_for_table', true);
		$datatable_pagination	= get_post_meta($post_id,'pagination_for_table',true);
		
		$output='';$cls='';$crypto_html='';
		$display_changes = get_post_meta($post_id,'display_changes', true );
		$back_color = get_post_meta($post_id,'back_color', true );
		$font_color = get_post_meta($post_id,'font_color', true );
		$custom_css = get_post_meta($post_id,'custom_css', true );
		$id = "ccpw-ticker" . $post_id . rand(1, 20);
		$is_cmc_enabled = get_option('cmc-dynamic-links');
		// Initialize Titan for cmc links
			if (class_exists('TitanFramework')) {
				$cmc_titan = TitanFramework::getInstance('cmc_single_settings');
	
				$cmc_slug = $cmc_titan->getOption('single-page-slug');
	
				if (empty($cmc_slug)) {
					$cmc_slug = 'currencies';
				}
			} else {
				$cmc_slug = 'currencies';
			}
	
		$this->ccpw_enqueue_assets($type, $post_id);
		
		/* dynamic styles */
		$dynamic_styles="";
		$styles='';
		$dynamic_styles_list="";
		$dynamic_styles_multicurrency="";
		$bg_color=!empty($back_color)? "background-color:".$back_color.";":"background-color:#fff;";
		$bg_coloronly=!empty($back_color)? ":".$back_color."d9;":":#ddd;";
		$fnt_color=!empty($font_color)? "color:".$font_color.";":"color:#000;";
		$fnt_coloronly=!empty($font_color)? ":".$font_color."57;":":#666;";
		$fnt_colorlight=!empty($font_color)? ":".$font_color."1F;":":#eee;";
		$ticker_top=!empty($header_ticker_position)? "top:".$header_ticker_position."px !important;":"top:0px !important;";
	
		// ticker dynamic styles
		if ($type == "ticker") {
			$id = "ccpw-ticker-widget-" . $post_id;	
			$dynamic_styles.=".tickercontainer #".$id."{".$bg_color."}
			.tickercontainer #".$id." span.name,
			.tickercontainer #".$id." .ccpw-credits a {".$fnt_color."}	
			.tickercontainer #".$id." span.coin_symbol {".$fnt_color."}			
			.tickercontainer #".$id." span.price {".$fnt_color."} .tickercontainer .price-value{".$fnt_color."}
			.ccpw-header-ticker-fixedbar{".$ticker_top."}";
		
		}
		else if ($type == "price-label") {
			$id = "ccpw-label-widget-" . $post_id;	
			$dynamic_styles .= "#".$id.".ccpw-price-label li a , #".$id.".ccpw-price-label li{" . $fnt_color . "}
			";
		}
		else if($type == "list-widget"){
				$id = "ccpw-list-widget-" . $post_id;	
				$dynamic_styles .="
				#".$id.".ccpw-widget .ccpw_table tr{".$bg_color.$fnt_color."}
				#".$id.".ccpw-widget .ccpw_table tr th, #".$id.".ccpw-widget .ccpw_table tr td,
				#".$id.".ccpw-widget .ccpw_table tr td a{".$fnt_color."}
				";
		}
		else if ($type == "multi-currency-tab") {
				$id = "ccpw-multicurrency-widget-" . $post_id;	
				$dynamic_styles .=".currency_tabs#".$id.",.currency_tabs#".$id." ul.multi-currency-tab li.active-tab{".$bg_color."}
				.currency_tabs#".$id." .mtab-content, .currency_tabs#".$id." ul.multi-currency-tab li, .currency_tabs#".$id." .mtab-content a{".$fnt_color."}";
		}
	
		 if($type=="multi-currency-tab"){
			  $usd_conversions=(array)ccpw_usd_conversions('all');
			}else{
			  $usd_conversions=array();
			}
			/*
				grab only selected coins data
			*/
			if( $type!='table-widget' ){
				// fetch data from db
				$all_coin_data = ccpw_get_all_coins_details($display_currencies);
					// create coin id based index for later use
				if (is_array($all_coin_data) && count($all_coin_data)>0 ) {
					$selected_coins=[];
					foreach ($all_coin_data as $currency) {
						// gather data from database
						if( $currency != false ){
							$coin_id=$currency['coin_id'];
							$selected_coins[$coin_id] = $currency;	
						}
						}
						// generate html according to the coin selection
						foreach($display_currencies as $currency){
							$coin=$selected_coins[$currency];
							require(__DIR__ . '/includes/ccpw-generate-html.php');
							$crypto_html .= $coin_html;
							}
				} else {
					$error = _e('You have not selected any currencies to display', 'ccpw');
					return $error.'<!-- Cryptocurrency Widget ID: '.$post_id.' !-->';
				}	
			}
			if ($type=="ticker"){
					$id = "ccpw-ticker-widget-" . $post_id;	
					 if($ticker_position=="footer"||$ticker_position=="header"){
						 $cls='ccpw-sticky-ticker';
						 if($ticker_position=="footer"){
							 $container_cls='ccpw-footer-ticker-fixedbar';
						 }else{
							 $container_cls='ccpw-header-ticker-fixedbar';
						 }
						 
					 }else{
						 $cls='ccpw-ticker-cont';
						 $container_cls='';
					 }
	
				$output .= '<div style="display:none" class="ccpw-container ccpw-ticker-cont '.$container_cls.'">';
				$output .= '<div  class="tickercontainer" style="height: auto; overflow: hidden;">
				';
				$output .= '<ul   data-tickerspeed="'.$t_speed.'" id="'.$id.'">';
				$output .= $crypto_html;
				if( $show_credit ){
					$output .= '<li ="ccpw-ticker-credit">'.$credit_html.'</li>';
				}
				$output	.=	'</ul></div></div>';
	
		}else if($type == "price-label"){
				$id = "ccpw-label-widget-".$post_id;	
				$output .='<div id="'.$id.'" class="ccpw-container ccpw-price-label"><ul class="lbl-wrapper">';
				$output .= $crypto_html;
				$output .= '</ul></div>';
				if( $show_credit ){
					$output .= $credit_html;
				}
		 
			}else if($type=="list-widget"){
				$cls='ccpw-widget';
				$id="ccpw-list-widget-".$post_id;	
				$output .= '<div id="'.$id.'" class="'.$cls.'"><table class="ccpw_table" style="border:none!important;"><thead>
				<th>'.__('Name','ccpw').'</th>
				<th>'.__('Price','ccpw').'</th>';
				if($display_changes){
				$output .='<th>'.__('24H (%)','ccpw').'</th>';
					}
				$output .='</thead><tbody>';
				$output .= $crypto_html;
				$output .= '</tbody></table></div>';
				
				if( $show_credit ){
					$output .= $credit_html;
				}
			
		  }else if($type=="multi-currency-tab"){
					$id = "ccpw-multicurrency-widget-" . $post_id;	
					$output .= '<div class="currency_tabs" id="'.$id.'">';
					  $output .= '<ul class="multi-currency-tab">
					  <li data-currency="usd" class="active-tab">'.__("USD","ccpwx").'</li>
					  <li data-currency="eur">'.__("EUR","ccpwx").'</li>
					  <li data-currency="gbp">'.__("GPB","ccpwx").'</li>
					  <li data-currency="aud">'.__("AUD","ccpwx").'</li>
					  <li data-currency="jpy">'.__("JPY","ccpwx").'</li>
					  </ul>';
					$output .= '<div><ul class="multi-currency-tab-content">';
					  $output .= $crypto_html;
					  $output .= '</ul></div></div>';
					if( $show_credit ){
						$output .= $credit_html;
					}
		}else if( $type == "table-widget"){
			$cls='ccpw-coinslist_wrapper';
			$preloader_url = Crypto_Currency_Price_Widget_URL . 'assets/chart-loading.svg';
			$ccpw_prev_coins= __('Previous','ccpw');
			$ccpw_next_coins= __('Next','ccpw');
			$coin_loading_lbl= __('Loading...','ccpw');
			$ccpw_no_data= __('No Coin Found','ccpw');
	
				$id="ccpw-coinslist_wrapper";	
				$output .= '<div id="'.$id.'" class="'.$cls.'">
				
				<table id="ccpw-datatable-'.$post_id.'" 
				class="display ccpw_table_widget table-striped table-bordered no-footer" 
				data-currency-type="'.$fiat_currency.'" data-next-coins="'.$ccpw_next_coins.'" data-loadinglbl="'.$coin_loading_lbl.'" 
				data-prev-coins="'.$ccpw_prev_coins.'" data-dynamic-link="'.$is_cmc_enabled.'" data-currency-slug="'.esc_url(home_url($cmc_slug)).'"
				data-required-currencies="'.$datatable_currencies.'" data-zero-records="'.$ccpw_no_data.'" data-pagination="'.$datatable_pagination.'" 
				data-number-formating="'.$enable_formatting.'" data-currency-symbol="'.ccpw_currency_symbol($fiat_currency).'" data-currency-rate="'.ccpw_usd_conversions($fiat_currency).'" 
				style="border:none!important;">

				<thead data-preloader="'.$preloader_url.'">
				<th data-classes="desktop ccpw_coin_rank" data-index="rank">'.__('#','ccpw').'</th>
				<th data-classes="desktop ccpw_name" data-index="name">'.__('Name','ccpw').'</th>
				<th data-classes="desktop ccpw_coin_price" data-index="price">'.__('Price','ccpw').'</th>
				<th data-classes="desktop ccpw_coin_change24h" data-index="change_percentage_24h">'.__('Changes 24h','ccpw').'</th>
				<th data-classes="desktop ccpw_coin_market_cap" data-index="market_cap">'.__('Market CAP','ccpw').'</th>';

				$output .='<th data-classes="ccpw_coin_total_volume" data-index="total_volume">'.__('Volume','ccpw').'</th>
				<th data-classes="ccpw_coin_supply" data-index="supply">'.__('Supply','ccpw').'</th>';
				
				$output .='</tr></thead><tbody>';
				$output .= '</tbody><tfoot>
						</tfoot></table>';
	
				if( $show_credit ){
					$output .= $credit_html;
				}
	
				$output .= '</div>';
	
		}
					$ccpwcss= $dynamic_styles . $custom_css;
				
			wp_add_inline_style('ccpw-styles', $ccpwcss);
	
			$ccpwv='<!-- Cryptocurrency Widgets - Version:- '.Crypto_Currency_Price_Widget_URL.' By Cool Plugins (CoolPlugins.net) -->';	
				return  $ccpwv.$output;	
			 
		/*	}else{
				 return _e('There is something wrong with the server','ccpw');
			} */
	}
	
		/**
		 * Code you want to run when all other plugins loaded.
		 */
		public function ccpw_plugin_init() {
			load_plugin_textdomain('ccpw', false, basename(dirname(__FILE__)) . '/languages/' );
		}

	/**
	 * Run when activate plugin.
	 */
	public function ccpw_activate() {
		
		$DB = new ccpw_database();
        $DB->create_table();
		$this->ccpw_cron_job_init();
		
	}

	/**
	 * Run when deactivate plugin.
	 */
	public static function ccpw_deactivate() {

		if ( get_option('cmc-dynamic-links') === false || get_option('cmc-dynamic-links') == '' ){
			
			if( wp_next_scheduled('ccpw_coins_autosave') ){
				wp_clear_scheduled_hook('ccpw_coins_autosave');
			}
			$db = new ccpw_database();
			$db->drop_table();
			delete_transient('cmc-saved-coindata');
		}

	}


	/**
	 * server side processing ajax callback
	 */
	function ccpw_get_coins_list(){
		require_once( Crypto_Currency_Price_Widget_PATH.'includes/ccpw-serverside-processing.php' );
		ccpw_get_ajax_data();
		wp_die();
	}

	/**
	 * Save shortcode when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
function save_ccpw_shortcode( $post_id, $post, $update ) {
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		        return;
		// AJAX? Not used here
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
		        return;
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
		        return;
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) )
		        return;
    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $post_type = get_post_type($post_id);

    // If this isn't a 'ccpw' post, don't update it.
    if ( "ccpw" != $post_type ) return;
	    // - Update the post's metadata.
    	if(isset($_POST['ticker_position'])&& in_array($_POST['ticker_position'],array('header','footer'))){
		    update_option('ccpw-p-id',$post_id);
		    update_option('ccpw-shortcode',"[ccpw id=".$post_id."]");
			}

		delete_transient( 'ccpw-coins' ); // Site Transient
	}

	/*
		Added ticker shortcode in footer hook for footer ticker
	*/

	function ticker_in_footer(){
		if (!wp_script_is('jquery', 'done')) {
			wp_enqueue_script('jquery');
		}
		 $id=get_option('ccpw-p-id');
		if($id){
				$ticker_position = get_post_meta($id,'ticker_position', true );
    			$type = get_post_meta($id,'type', true );
  		
    			if($type=="ticker"){
    			if($ticker_position=="header"||$ticker_position=="footer"){
					 $shortcode=get_option('ccpw-shortcode');
					echo do_shortcode($shortcode);
				 }
				}
			}	
	}

	// Re-enable ticker after dom load
	function ccpw_enable_ticker(){

		wp_add_inline_script('ccpw_bxslider_js',
			'jQuery(document).ready(function($){
				$(".ccpw-ticker-cont").fadeIn();     
			});'
		,'before');

	}

	/*
	For ask for reviews code
	*/

	function ccpw_installation_date(){
		 $get_installation_time = strtotime("now");
   	 	  add_option('ccpw_activation_time', $get_installation_time ); 
	}	

	//check if review notice should be shown or not

	function ccpw_check_installation_time() {
		$spare_me = get_option('ccpw_spare_me');
  		if(get_option('ccpw_spare_me')==false){
		  $install_date = get_option( 'ccpw_activation_time' );
	        $past_date = strtotime( '-1 days' );
	      if ( $past_date >= $install_date ) {
	     	 add_action( 'admin_notices', array($this,'ccpw_display_admin_notice'));
	     		}
	    }
	}

	/**
	* Display Admin Notice, asking for a review
	**/
	function ccpw_display_admin_notice() {
	    // wordpress global variable 
	    global $pagenow;
	//    if( $pagenow == 'index.php' ){
	        $dont_disturb = esc_url( get_admin_url() . '?spare_me=1' );
	        $plugin_info = get_plugin_data( __FILE__ , true, true );       
	        $reviewurl = esc_url( 'https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post' );
			echo $html='<div class="ccpw-review wrap"><img src="'.plugin_dir_url(__FILE__).'assets/crypto-widget.png" />
			<p>You have been using <b> '.$plugin_info['Name']. '</b> for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !<br/>
			<br/><a href="'.$reviewurl.'" class="button button-primary" target=
				"_blank">Rate Now! ★★★★★</a>
				<a href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050" class="button button-secondary" style="margin-left: 10px !important;" target="_blank"> Try Crypto Widgets Pro !</a>
				<a href="'.$dont_disturb.'" class="ccpw-review-done button button-secondary"> Already Done !</a></p></div>';
	       
	   // }
	}

	/*
	|--------------------------------------------------------------------------
	|  Check if plugin is just updated from older version to new
	|--------------------------------------------------------------------------
	*/	
	public function ccpw_plugin_version_verify( ) {
		
		$CCPW_VERSION = get_option('CCPW_FREE_VERSION');
		if( !isset($CCPW_VERSION) || version_compare( $CCPW_VERSION, Crypto_Currency_Price_Widget_VERSION, '<' ) ){
			$this->ccpw_activate();
			update_option('ccpw_spare_me',false);
			update_option('CCPW_FREE_VERSION', Crypto_Currency_Price_Widget_VERSION );
		}
	}	// end of cmc_plugin_version_verify()

 	 function set_custom_edit_ccpw_columns($columns) {
	   $columns['type'] = __( 'Widget Type', 'ccpwx' );
	    $columns['shortcode'] = __( 'Shortcode', 'ccpwx' );
	   return $columns;
	}

	function custom_ccpw_column( $column, $post_id ) {
	    switch ( $column ) {
			case 'type' :
	          $type=get_post_meta( $post_id , 'type' , true ); 
			switch ($type){
				case "ticker":
					_e('Ticker','ccpwx');
				break;
				case "price-label":
						_e('Price Label', 'ccpwx');
				break;
				case "multi-currency-tab":
						_e('Multi Currency Tabs', 'ccpwx');
				break;
				case "table-widget":
					_e('Table Widget','ccpwx');
				break;
				default:
					_e('List Widget','ccpwx');
	        }
	      	 break;
		    case 'shortcode' :
	            echo '<code>[ccpw id="'.$post_id.'"]</code>'; 
	            break;
	    }
	}

	/*
	 check admin side post type page
	*/
	function ccpw_get_post_type_page() {
    global $post, $typenow, $current_screen;
 
	 if ( $post && $post->post_type ){
	        return $post->post_type;
	 }elseif( $typenow ){
	        return $typenow;
	  }elseif( $current_screen && $current_screen->post_type ){
	        return $current_screen->post_type;
	 }
	 elseif( isset( $_REQUEST['post_type'] ) ){
	        return sanitize_key( $_REQUEST['post_type'] );
	 }
	 elseif ( isset( $_REQUEST['post'] ) ) {
	   return get_post_type( $_REQUEST['post'] );
	 }
	  return null;
	}

	function ccpw_admin_css(){

	}
	// remove the notice for the user if review already done or if the user does not want to
	function ccpw_spare_me(){    
	    if( isset( $_GET['spare_me'] ) && !empty( $_GET['spare_me'] ) ){
			$spare_me = $_GET['spare_me'];
		
	        if( $spare_me == 1 ){
				update_option('ccpw_spare_me',true);
	        }
	    }
	}

	//check coin market cap plugin is activated. then enable links
	function ccpw_check_cmc_activated()
	{
		if (is_plugin_active('coin-market-cap/coin-market-cap.php') || class_exists('CoinMarketCap')) {
			update_option('cmc-dynamic-links', true);
		} else {
			update_option('cmc-dynamic-links', false);
		}
	}
	
	public function ccpw_custom_btn()
	{
		global $current_screen;

    // Not our post type, exit earlier
 		if ('ccpw' != $current_screen->post_type) {
			return;
		}

		?>
        <script type="text/javascript">
            jQuery(document).ready( function($)
            {
				$(".wrap").find('a.page-title-action').after("<a  id='ccpw_add_premium' href='https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050' target='_blank' class='add-new-h2'>Add Premium Widgets</a>");
                
            });
        </script>
    <?php

	}
	// custom links for add widgets in all plugins section
	function ccpw_add_widgets_action_links($links){
			$links[] = '<a style="font-weight:bold" href="'. esc_url( get_admin_url(null, 'post-new.php?post_type=ccpw') ) .'">Add Widgets</a>';
			$links[] = '<a  style="font-weight:bold" href="https://cryptowidgetpro.coolplugins.net/" target="_blank">PRO Demos</a>';
			return $links;
		
	}
	
	
	function ccpw_load_scripts($hook) { 

			 wp_enqueue_style( 'ccpw-custom-styles', Crypto_Currency_Price_Widget_URL.'assets/css/ppcw-admin-styles.css');
		
	}
}

function Crypto_Currency_Price_Widget() {
	return Crypto_Currency_Price_Widget::get_instance();
}

$GLOBALS['Crypto_Currency_Price_Widget'] = Crypto_Currency_Price_Widget();
