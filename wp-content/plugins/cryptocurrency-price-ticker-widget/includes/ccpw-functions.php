<?php

/*
|--------------------------------------------------------------------------
| getting all coins details from database
|--------------------------------------------------------------------------
*/
function ccpw_get_all_coins_details($coin_id_arr)
{
    $DB = new ccpw_database;
    $coin_data = $DB->get_coins(array('coin_id' => $coin_id_arr, 'number' => '1000','orderby'=>'coin_id'));
    if (is_array($coin_data) && isset($coin_data)) {
        $coin_data = ccpw_objectToArray($coin_data);
        return $coin_data;
    } else {
        return false;
    }

}

/*
|--------------------------------------------------------------------------
| getting all coin ids from database
|--------------------------------------------------------------------------
*/
function ccpw_get_all_coin_ids()
{
    $DB = new ccpw_database;
    $coin_data = $DB->get_coins(array('number' => '1000'));
    if (is_array($coin_data) && isset($coin_data)) {
        $coin_data = ccpw_objectToArray($coin_data);
        $coins = array();
        foreach ($coin_data as $coin) {
            $coins[$coin['coin_id']] = $coin['name'];
        }
        return $coins;
    } else {
        return false;
    }

}

/*
|-----------------------------------------------------------
| Fetching data through CoinGecko API and save in database
|-----------------------------------------------------------
| MUST NOT CALL THIS FUNCTION DIRECTLY
|-----------------------------------------------------------
*/
function ccpw_get_coin_gecko_data()
{
    $update_api_name = 'ccpw-active-api';
    $data_cache_name = 'cmc-saved-coindata';
    $activate_api = get_transient($update_api_name);
    $cache = get_transient($data_cache_name);

    // Avoid updating database if cache exist and same API is requested
    if ($activate_api == 'CoinGecko' && false != $cache ) {
        return;
    }

    $coins = array();
    $api_url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=250&page=1&sparkline=false';

    $request = wp_remote_get($api_url, array('timeout' => 120));
    if (is_wp_error($request)) {
        return false; // Bail early
    }
    $body = wp_remote_retrieve_body($request);
    $coins = json_decode($body);
    $response = array();
    $coin_data = array();

    if (isset($coins) && $coins != "" && is_array($coins)) {
        foreach ($coins as $coin) {
            $response['coin_id'] = $coin->id;
            $response['rank'] = $coin->market_cap_rank;
            $response['name'] = $coin->name;
            $response['symbol'] = strtoupper($coin->symbol);
            $response['price'] = ccpw_set_default_if_empty($coin->current_price, 0.00);
            $response['percent_change_24h'] = ccpw_set_default_if_empty($coin->price_change_percentage_24h, 0);
            $response['market_cap'] = ccpw_set_default_if_empty($coin->market_cap, 0);
            $response['total_volume'] = ccpw_set_default_if_empty($coin->total_volume);
            $response['circulating_supply'] = ccpw_set_default_if_empty($coin->circulating_supply);
            $response['logo'] =$coin->image;
            $coin_data[] = $response;
        }
        $DB = new ccpw_database();
        $DB->create_table();
        $DB->ccpw_insert($coin_data);
        set_transient($data_cache_name, date('H:s:i'), 5 * MINUTE_IN_SECONDS);
        set_transient($update_api_name, 'CoinGecko', 0);

    }

}

/*
|-----------------------------------------------------------
| Fetching Coins data from api-beta.coinexchangeprice.com
|-----------------------------------------------------------
| MUST NOT CALL THIS FUNCTION DIRECTLY
|-----------------------------------------------------------
*/
function ccpw_get_coinexchangeprice_api_data()
{
    //check if cmc cache not exists
    $coin_data = array();
    $update_api_name = 'ccpw-active-api';
    $data_cache_name = 'cmc-saved-coindata';
    $activate_api = get_transient($update_api_name);
    $cache = get_transient($data_cache_name);

    $data_cache_name = 'cmc-saved-coindata';

    // Avoid updating database if cache exist and same API is requested
    if ( false != $cache && $activate_api == 'CoinExchangePrice') {
        return;
    }

    $API_URL = CRYPTO_API . "coins/all?weekly=false&info=false";

    $request = wp_remote_get($API_URL, array('timeout' => 300));

    if (is_wp_error($request)) {
        return false;
    }

    $body = wp_remote_retrieve_body($request);
    $coinslist = json_decode($body);

    if ($coinslist) {

        $coins = ccpw_objectToArray($coinslist->data);

        $DB = new ccpw_database();
        $DB->create_table();
        $DB->ccpw_insert($coins);
        set_transient($data_cache_name, date('H:s:i'), 5 * MINUTE_IN_SECONDS);
        set_transient($update_api_name, 'CoinExchangePrice', 0);
    }

}

/**
 * Check if provided $value is empty or not.
 * Return $default if $value is empty
 */
function ccpw_set_default_if_empty($value, $default = 'N/A')
{
    return $value ? $value : $default;
}

/*
Adding coins SVG logos
*/
function ccpw_get_coin_logo($coin_id, $size = 32, $HTML = true)
{
    $logo_html = '';
    $coin_svg = Crypto_Currency_Price_Widget_PATH . '/assets/coin-logos/' . strtolower($coin_id) . '.svg';
    $coin_png = Crypto_Currency_Price_Widget_PATH . '/assets/coin-logos/' . strtolower($coin_id) . '.png';

    if (file_exists($coin_svg)) {
        $coin_svg = Crypto_Currency_Price_Widget_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.svg';
        if ($HTML == true) {
            $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_svg . '">';
        } else {
            $logo_html = $coin_svg;
        }
    return $logo_html;

    } else if(file_exists($coin_png)) {
        $coin_png = Crypto_Currency_Price_Widget_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.png';
        if ($HTML == true) {
            $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_png . '">';
        } else {
            $logo_html = $coin_png;
        }
    return $logo_html;

    }else{
        return false;
    }
}

/* USD conversions */
function ccpw_usd_conversions($currency)
{
    // use common transient between cmc and ccpw
    $conversions = get_transient('cmc_usd_conversions');
    if (empty($conversions) || $conversions === "") {
        $request = wp_remote_get('https://api-beta.coinexchangeprice.com/v1/exchange-rates');

        if (is_wp_error($request)) {
            return false;
        }

        $currency_ids = array("USD", "AUD", "BRL", "CAD", "CZK", "DKK", "EUR", "HKD", "HUF", "ILS", "INR", "JPY", "MYR", "MXN", "NOK", "NZD", "PHP", "PLN", "GBP", "SEK", "CHF", "TWD", "THB", "TRY", "CNY", "KRW", "RUB", "SGD", "CLP", "IDR", "PKR", "ZAR");
        $body = wp_remote_retrieve_body($request);
        $conversion_data = json_decode($body);

        if (isset($conversion_data->rates)) {
            $conversion_data = (array) $conversion_data->rates;
        } else {
            $conversion_data = array();
        }

        if (is_array($conversion_data) && count($conversion_data) > 0) {
            foreach ($conversion_data as $key => $currency_price) {
                if (in_array($key, $currency_ids)) {
                    $conversions[$key] = $currency_price;
                }

            }

            uksort($conversions, function ($key1, $key2) use ($currency_ids) {
                return (array_search($key1, $currency_ids) > array_search($key2, $currency_ids));
            });

            set_transient('cmc_usd_conversions', $conversions, 12 * HOUR_IN_SECONDS);
        }
    }

    if ($currency == "all") {

        return $conversions;

    } else {
        if (isset($conversions[$currency])) {
            return $conversions[$currency];
        }
    }
}

function ccpw_format_number($n)
{
    $formatted = $n;
    if ($n <= -1) {
        $formatted = number_format($n, 2, '.', ',');
    } else if ($n < 0.50) {
        $formatted = number_format($n, 6, '.', ',');
    } else {
        $formatted = number_format($n, 2, '.', ',');
    }
    return $formatted;
}

// object to array conversion
function ccpw_objectToArray($d)
{
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
         * Return array converted to object
         * Using __FUNCTION__ (Magic constant)
         * for recursive call
         */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
}

/*
Added meta boxes for shortcode
*/
function register_ccpw_meta_box()
{
    add_meta_box('ccpw-shortcode', 'Crypto Widget Shortcode', 'ccpw_p_shortcode_meta', 'ccpw', 'side', 'high');
}

/*
Plugin Shortcode meta section
*/
function ccpw_p_shortcode_meta()
{
    $id = get_the_ID();
    $dynamic_attr = '';
    _e(' <p>Paste this shortcode anywhere in Page/Post.</p>', 'ccpwx');

    $element_type = get_post_meta($id, 'pp_type', true);
    $dynamic_attr .= "[ccpw id=\"{$id}\"";
    $dynamic_attr .= ']';
    ?>
	    <input style="width:100%" onClick="this.select();" type="text" class="regular-small" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo htmlentities($dynamic_attr); ?>" readonly/>
      <div>
        <br/>
	   	<a class="button button-secondary red" target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844">How to create CoinMarketCap.com clone?</a>
     </div>
	    <?php
}

function ccpw_add_meta_boxes($post)
{
    add_meta_box(
        'ccpw-feedback-section',
        __('Hopefully you are Happy with our Cool Crypto Widgets Plugin', 'ccpwx'),
        'ccpw_right_section',
        'ccpw',
        'side',
        'low'
    );
}

/*
Admin notice for plugin feedback
*/
function ccpw_right_section($post, $callback)
{
    global $post;
    $pro_add = '';
    $pro_add .=

    __('	<p>You have been using <b>Cryptocurrency Widgets</b> for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !', 'ccpwx') .
    '<br/><br/><a href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post" class="button button-primary" target="_blank">Submit Review ★★★★★</a>
        <hr>
         <div>
         <a class="button button-primary" target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050s">' . __('Buy Now', 'ccpwx') . ' ($24)</a>
         <a  class="button button-secondary" target="_blank" href="http://cryptowidgetpro.coolplugins.net/">' . __('VIEW ALL DEMOS', 'ccpwx') . '</a>

         <h3>Crypto Widgets Pro Features:-</h3>
      <ol style="list-style:disc;"><li> You can display real time live price changes. - <a href="http://cryptowidgetpro.coolplugins.net/list-widget/#live-changes-demo" target="_blank">DEMO</a></li>
		<li>  Create widgets for 3700+ crypto coins in pro version.</li>
		<li>  Create historical price charts & tradingview candlestick charts. - <a href="http://cryptowidgetpro.coolplugins.net/coin-price-chart/" target="_blank">DEMO</a></li>
		<li>  You can create beautiful price label and crypto price card designs.</li>
         <li>  Display latest crypto news feed from popular websites. - <a href="http://cryptowidgetpro.coolplugins.net/news-feed/" target="_blank">DEMO</a></li>
		<li>  Display market cap and volume of virtual crypto coins.</li>
		<li>  32+ fiat currencies support - USD, GBP, EUR, INR, JPY, CNY, ILS, KRW, RUB, DKK, PLN, AUD, BRL, MXN, SEK, CAD, HKD, MYR, SGD, CHF, HUF, NOK, THB, CLP, IDR, NZD, TRY, PHP, TWD, CZK, PKR, ZAR.</li>
        <li> Create an advance table with charts and extra values</li>
        <li> Create Elegent Price widget with accordion layout</li>
        <li>Display Coin Price in 3 type of Price Block layouts</li>
        <li>Create a beautiful coin price slider.
		</ol>
     
		</div>';
    echo $pro_add;

}

// currencies symbol
function ccpw_currency_symbol($name)
{
    $cc = strtoupper($name);
    $currency = array(
        "USD" => "&#36;", //U.S. Dollar
        "CLP" => "&#36;", //CLP Dollar
        "SGD" => "S&#36;", //Singapur dollar
        "AUD" => "&#36;", //Australian Dollar
        "BRL" => "R&#36;", //Brazilian Real
        "CAD" => "C&#36;", //Canadian Dollar
        "CZK" => "K&#269;", //Czech Koruna
        "DKK" => "kr", //Danish Krone
        "EUR" => "&euro;", //Euro
        "HKD" => "&#36", //Hong Kong Dollar
        "HUF" => "Ft", //Hungarian Forint
        "ILS" => "&#x20aa;", //Israeli New Sheqel
        "INR" => "&#8377;", //Indian Rupee
        "IDR" => "Rp", //Indian Rupee
        "KRW" => "&#8361;", //WON
        "CNY" => "&#165;", //CNY
        "JPY" => "&yen;", //Japanese Yen
        "MYR" => "RM", //Malaysian Ringgit
        "MXN" => "&#36;", //Mexican Peso
        "NOK" => "kr", //Norwegian Krone
        "NZD" => "&#36;", //New Zealand Dollar
        "PHP" => "&#x20b1;", //Philippine Peso
        "PLN" => "&#122;&#322;", //Polish Zloty
        "GBP" => "&pound;", //Pound Sterling
        "SEK" => "kr", //Swedish Krona
        "CHF" => "Fr", //Swiss Franc
        "TWD" => "NT&#36;", //Taiwan New Dollar
        "PKR" => "Rs", //Rs
        "THB" => "&#3647;", //Thai Baht
        "TRY" => "&#8378;", //Turkish Lira
        "ZAR" => "R", //zar
        "RUB" => "&#8381;", //rub
    );

    if (array_key_exists($cc, $currency)) {
        return $currency[$cc];
    }
}

// Register Custom Post Type of Crypto Widget
function ccpw_post_type()
{

    $labels = array(
        'name' => _x('CryptoCurrency Price Widget', 'Post Type General Name', 'ccpwx'),
        'singular_name' => _x('CryptoCurrency Price Widget', 'Post Type Singular Name', 'ccpwx'),
        'menu_name' => __('Crypto Widgets', 'ccpwx'),
        'name_admin_bar' => __('Post Type', 'ccpwx'),
        'archives' => __('Item Archives', 'ccpwx'),
        'attributes' => __('Item Attributes', 'ccpwx'),
        'parent_item_colon' => __('Parent Item:', 'ccpwx'),
        'all_items' => __('All Shortcodes', 'ccpwx'),
        'add_new_item' => __('Add New Shortcode', 'ccpwx'),
        'add_new' => __('Add New', 'ccpwx'),
        'new_item' => __('New Item', 'ccpwx'),
        'edit_item' => __('Edit Item', 'ccpwx'),
        'update_item' => __('Update Item', 'ccpwx'),
        'view_item' => __('View Item', 'ccpwx'),
        'view_items' => __('View Items', 'ccpwx'),
        'search_items' => __('Search Item', 'ccpwx'),
        'not_found' => __('Not found', 'ccpwx'),
        'not_found_in_trash' => __('Not found in Trash', 'ccpwx'),
        'featured_image' => __('Featured Image', 'ccpwx'),
        'set_featured_image' => __('Set featured image', 'ccpwx'),
        'remove_featured_image' => __('Remove featured image', 'ccpwx'),
        'use_featured_image' => __('Use as featured image', 'ccpwx'),
        'insert_into_item' => __('Insert into item', 'ccpwx'),
        'uploaded_to_this_item' => __('Uploaded to this item', 'ccpwx'),
        'items_list' => __('Items list', 'ccpwx'),
        'items_list_navigation' => __('Items list navigation', 'ccpwx'),
        'filter_items_list' => __('Filter items list', 'ccpwx'),
    );
    $args = array(
        'label' => __('CryptoCurrency Price Widget', 'ccpwx'),
        'description' => __('Post Type Description', 'ccpwx'),
        'labels' => $labels,
        'supports' => array('title'),
        'taxonomies' => array(''),
        'hierarchical' => false,
        'public' => false, // it's not public, it shouldn't have it's own permalink, and so on
        'show_ui' => true,
        'show_in_nav_menus' => false, // you shouldn't be able to add it to menus
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => false, // it shouldn't have archive page
        'rewrite' => false, // it shouldn't have rewrite rules
        'exclude_from_search' => true,
        'publicly_queryable' => true,
        'menu_icon' => Crypto_Currency_Price_Widget_URL . '/assets/ccpw-icon.png',
        'capability_type' => 'page',
    );
    register_post_type('ccpw', $args);

}

/**
 * Define the metabox and field configurations.
 */
function cmb2_ccpw_metaboxes()
{

    // Start with an underscore to hide fields from custom fields list
    $prefix = 'ccpw_';
    $currencies_arr = array(
        'USD' => 'USD',
        'GBP' => 'GBP',
        'EUR' => 'EUR',
        'INR' => 'INR',
        'JPY' => 'JPY',
        'CNY' => 'CNY',
        'ILS' => 'ILS',
        'KRW' => 'KRW',
        'RUB' => 'RUB',
        'DKK' => 'DKK',
        'PLN' => 'PLN',
        'AUD' => 'AUD',
        'BRL' => 'BRL',
        'MXN' => 'MXN',
        'SEK' => 'SEK',
        'CAD' => 'CAD',
        'HKD' => 'HKD',
        'MYR' => 'MYR',
        'SGD' => 'SGD',
        'CHF' => 'CHF',
        'HUF' => 'HUF',
        'NOK' => 'NOK',
        'THB' => 'THB',
        'CLP' => 'CLP',
        'IDR' => 'IDR',
        'NZD' => 'NZD',
        'TRY' => 'TRY',
        'PHP' => 'PHP',
        'TWD' => 'TWD',
        'CZK' => 'CZK',
        'PKR' => 'PKR',
        'ZAR' => 'ZAR',
    );
    /**
     * Initiate the metabox
     */
    
    $cmb2 = new_cmb2_box( array(
        'id'            => 'live_preview',
        'title'         => __( 'Crypto Widget Live Preview', 'cmb2' ),
        'object_types'  => array( 'ccpw'), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
    ) );
    $cmb = new_cmb2_box(array(
        'id' => 'generate_shortcode',
        'title' => __('Crypto Widget Settings', 'cmb2'),
        'object_types' => array('ccpw'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
    ));


    $cmb->add_field(array(
        'name' => 'Widget Type<span style="color:red;">*</span>',
        'id' => 'type',
        'type' => 'select',
        'default' => 'table-widget',
        'options' => array(
            'table-widget' => __('Advanced Table', 'cmb2'),
            'list-widget' => __('Simple List', 'cmb2'),
            'ticker' => __('Ticker / Marquee', 'cmb2'),
            'multi-currency-tab' => __('Multi Currency Tabs', 'cmb2'),
            'price-label' => __('Price Label', 'cmb2'),

        ),
    ));

    $cmb->add_field(array(
        'name' => 'Select CryptoCurrencies<span style="color:red;">*</span>',
        'id' => 'display_currencies',
        'desc' => 'Select CryptoCurrencies (Press CTRL key to select multiple)',
        'type' => 'pw_multiselect',
        'options' => ccpw_get_all_coin_ids(),
        'attributes' => array(
            'required' => true,
            'data-conditional-id' => 'type',
            'data-conditional-value' => json_encode(array('price-label', 'list-widget', 'multi-currency-tab', 'ticker')),
        ),
    ));

    //select currency
    $cmb->add_field(array(
        'name' => 'Select Fiat Currency',
        'desc' => '',
        'id' => 'currency',
        'type' => 'select',
        'show_option_none' => false,
        'default' => 'custom',
        'options' => $currencies_arr,
        'default' => 'USD',
        'attributes' => array(
            'data-conditional-id' => 'type',
            'data-conditional-value' => json_encode(array('price-label', 'list-widget', 'ticker', 'table-widget')),
        ),
    ));

    $cmb->add_field(
        array(
            'name' => 'Select CryptoCurrencies<span style="color:red;">*</span>',
            'id' => 'display_currencies_for_table',
            'type' => 'select',
            'options' => array(
                'top-10' => 'Top 10',
                'top-50' => 'Top 50',
                'top-100' => 'Top 100',
                'all' => 'All',
            )
            ,
            'attributes' => array(
                'data-conditional-id' => 'type',
                'data-conditional-value' => json_encode(array('table-widget')),
            ),
        ));

    $cmb->add_field(
        array(
            'name' => 'Records Per Page',
            'id' => 'pagination_for_table',
            'type' => 'select',
            'options' => array(
                '10' => '10',
                '25' => '25',
                '50' => '50',
                '100' => '100',
            )
            ,
            'attributes' => array(
                'data-conditional-id' => 'type',
                'data-conditional-value' => json_encode(array('table-widget')),
            ),
        ));

    $cmb->add_field(array(
        'name' => 'Enable Formatting',
        'desc' => 'Select if you want to display marketcap, volume and supply in <strong>(Million/Billion)</strong>',
        'id' => 'enable_formatting',
        'type' => 'checkbox',
        'default' => ccpw_set_checkbox_default_for_new_post(true),
        'attributes' => array(
            'data-conditional-id' => 'type',
            'data-conditional-value' => json_encode(array('table-widget')),
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Display 24 Hours changes? (Optional)',
        'desc' => 'Select if you want to display Currency changes in price',
        'default' => ccpw_set_checkbox_default_for_new_post(true),
        'id' => 'display_changes',
        'type' => 'checkbox',
        'attributes' => array(
            // 'required' => true,
            'data-conditional-id' => 'type',
            'data-conditional-value' => json_encode(array('price-label', 'list-widget', 'multi-currency-tab', 'ticker')),
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Where Do You Want to Display Ticker? (Optional)',
        'desc' => '<br>Select the option where you want to display ticker.<span class="warning">Important: Do not add shortcode in a page if Header/Footer position is selected.</span>',
        'id' => 'ticker_position',
        'type' => 'radio_inline',
        'options' => array(
            'header' => __('Header', 'cmb2'),
            'footer' => __('Footer', 'cmb2'),
            'shortcode' => __('Anywhere', 'cmb2'),
        ),
        'default' => 'shortcode',

        'attributes' => array(
            // 'required' => true,
            'data-conditional-id' => 'type',
            'data-conditional-value' => 'ticker',
        ),

    ));

    $cmb->add_field(array(
        'name' => 'Ticker Position(Top)',
        'desc' => 'Specify Top Margin (in px) - Only For Header Ticker',
        'id' => 'header_ticker_position',
        'type' => 'text',
        'default' => '33',
        'attributes' => array(
            // 'required' => true,
            'data-conditional-id' => 'type',
            'data-conditional-value' => 'ticker',
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Speed of Ticker',
        'desc' => 'Low value = high speed. (Best between 10 - 60) e.g 10*1000 = 10000 miliseconds',
        'id' => 'ticker_speed',
        'type' => 'text',
        'default' => '35',
        'attributes' => array(
            // 'required' => true,
            'data-conditional-id' => 'type',
            'data-conditional-value' => 'ticker',
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Background Color',
        'desc' => 'Select background color',
        'id' => 'back_color',
        'type' => 'colorpicker',
        'default' => '#eee',
        'attributes' => array(
            'data-conditional-id' => 'type',
            'data-conditional-value' => json_encode(array('multi-currency-tab', 'list-widget', 'ticker')),
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Font Color',
        'desc' => 'Select font color',
        'id' => 'font_color',
        'type' => 'colorpicker',
        'default' => '#000',
        'attributes' => array(
            'data-conditional-id' => 'type',
            'data-conditional-value' => json_encode(array('multi-currency-tab', 'list-widget', 'ticker')),
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Custom CSS',
        'desc' => 'Enter custom CSS',
        'id' => 'custom_css',
        'type' => 'textarea',

    ));

    $cmb->add_field(array(
        'name' => 'Show API Credits',
        'desc' => 'Link back or a mention of ‘<strong>Powered by CoinGecko API</strong>’ would be appreciated!',
        'id' => 'ccpw_coinexchangeprice_credits',
        'default' => ccpw_set_checkbox_default_for_new_post(false),
        'type' => 'checkbox',
        'attributes' => array(
            // 'required' => true,
            'data-conditional-id' => 'type',
            'data-conditional-value' => json_encode(array('ticker', 'price-label', 'list-widget', 'multi-currency-tab', 'table-widget')),
        ),

    ));
    $cmb->add_field(array(
        'name' => '',
        'desc' => '
  <h3>Check Our Cool Premium Crypto Plugins - Now Create Website Similar Like CoinMarketCap.com<br/></h3>
  <div class="cmc_pro">
  <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050"><img style="max-width:100%;" src="https://res.cloudinary.com/coolplugins/image/upload/v1530694709/crypto-exchanges-plugin/banner-crypto-widget-pro.png"></a>
  </div><hr/>
    <div class="cmc_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844"><img style="max-width:100%;"src="https://res.cloudinary.com/coolplugins/image/upload/v1530695051/crypto-exchanges-plugin/banner-coinmarketcap.png"></a>
   </div><hr/>
    <div class="cmc_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-exchanges-list-pro-wordpress-plugin%2F22098669"><img style="max-width:100%;"src="https://res.cloudinary.com/coolplugins/image/upload/v1530694721/crypto-exchanges-plugin/banner-crypto-exchanges.png"></a> </div>',
        'type' => 'title',
        'id' => 'cmc_title',
    ));
    // Add other metaboxes as needed

    
$cmb2->add_field( array(
	'name' => '',
	'desc' =>display_live_preview(),
	'type' => 'title',
	'id'   => 'live_preview'
) );

}


function display_live_preview(){
    $output='';
    if( isset($_REQUEST['post']) && !is_array($_REQUEST['post'])){
      $id = $_REQUEST['post'];
      $type = get_post_meta($id, 'type', true);
         $output='<p><strong class="micon-info-circled"></strong>Backend preview may be a little bit different from frontend / actual view. Add this shortcode on any page for frontend view - <code>[ccpw id='.$id.']</code></p>'.do_shortcode("[ccpw id='".$id."']");
         $output.='<script type="text/javascript">
         jQuery(document).ready(function($){
           $(".ccpw-ticker-cont").fadeIn();     
         });
         </script>
         <style type="text/css">
         .ccpw-footer-ticker-fixedbar, .ccpw-header-ticker-fixedbar{
           position:relative!important;
         }
         .tickercontainer li{
             float:left!important;
             width:auto!important;
         }
         .ccpw-container-rss-view ul li.ccpw-news {
          margin-bottom: 30px;
          float: none;
          width: auto;
      }
      .ccpw-news-ticker .tickercontainer li{
        width: auto!important;
      }
         </style>';
         return $output;
     
       }else{
      return  $output='<h4><strong class="micon-info-circled"></strong> Publish to preview the widget.</h4>';
  
       }
  }

function ccpw_set_checkbox_default_for_new_post($default)
{
    return isset($_GET['post']) ? '' : ($default ? (string) $default : '');
}