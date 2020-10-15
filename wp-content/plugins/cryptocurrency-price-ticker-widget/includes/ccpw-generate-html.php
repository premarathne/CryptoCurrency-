<?php
/*
 Generating HTML for Ticker and list widget
 */

$coin_html = '';
// creating vars for later use
$coin_name = $coin['name'];
$coin_id = $coin['coin_id'];
$coin_symbol = $coin['symbol'];
if( ccpw_get_coin_logo($coin_id, $size = 32)==false){
  $apiLogo=$coin['logo'];
  $coin_logo_html ='<img  alt="'.$coin_name.'" src="'.$apiLogo.'">';
}else{
$coin_logo_html = ccpw_get_coin_logo($coin_id, $size = 32);
}
$coin_slug = strtolower($coin_name);

$coin_price =isset($coin['price'])? $coin['price']: $coin['price'];

if( $fiat_currency!='USD'){
  $coin_price = ccpw_usd_conversions( strtoupper($fiat_currency) )*$coin_price;
}

$coin_price_html= ccpw_currency_symbol($fiat_currency). ccpw_format_number($coin_price);
$percent_change_24h= number_format($coin['percent_change_24h'],2,'.',',')."%";
$change_sign = '<i class="ccpw_icon-up" aria-hidden="true"></i>';
$change_class = "up";
$change_sign_minus = "-";
$coin_link_start='';
$coin_link_end= '';

if ( $is_cmc_enabled == true) {
  $coin_url = esc_url(home_url($cmc_slug . '/' . $coin_symbol . '/' . $coin_id . '/'));
  $coin_link_start= '<a class="cmc_links" title="'.$coin_name.'" href="' . $coin_url . '">';
  $coin_link_end = '</a>';

}


if (strpos($coin['percent_change_24h'], $change_sign_minus) !== false) {
  $change_sign = '<i class="ccpw_icon-down" aria-hidden="true"></i>';
  $change_class = "down";
}

if ($type == "ticker") {

	/* 
  	Generating Ticker HTML
   */
  $coin_html .= '<li id="' . esc_attr($coin_id) . '">';
  $coin_html .= '<div class="coin-container">';
  $coin_html .=  $coin_link_start;
  $coin_html .= '<span class="ccpw_icon">' . $coin_logo_html . '</span>';
  $coin_html .= '<span class="name">' . $coin_name . '(' . $coin_symbol . ')</span>';
  $coin_html .= $coin_link_end;
  $coin_html .= '<span class="price">' . $coin_price_html . '</span>'; 
  if ($display_changes) {
    $coin_html .= '<span class="changes ' . $change_class . '">';
    $coin_html .= $change_sign . $percent_change_24h;
    $coin_html .= '</span>';

  }
  $coin_html .= '</div></li>';

}else if($type == "price-label"){

  $coin_html .= '<li id="' . esc_attr($coin_id) . '">';
  $coin_html .= '<div class="coin-container">';
  $coin_html .= $coin_link_start;
  $coin_html .= '<span class="ccpw_icon">' . $coin_logo_html . '</span>';
  $coin_html .= '<span class="name">' . $coin_name . '</span>';
  $coin_html .= $coin_link_end;
  $coin_html .= '<span class="price">' . $coin_price_html . '</span>';
  if ($display_changes) {
    $coin_html .= '<span class="changes ' . $change_class . '">';
    $coin_html .= $change_sign . $percent_change_24h;
    $coin_html .= '</span>';

  }
  $coin_html .= '</div></li>';

} else if ($type == "multi-currency-tab") {

  $coin_price = $coin['price'];
  $EUR = isset($usd_conversions['EUR']) ? $usd_conversions['EUR'] : 0.811573;
  $GBP = isset($usd_conversions['GBP']) ? $usd_conversions['GBP'] : 0.70916;
  $AUD = isset($usd_conversions['AUD']) ? $usd_conversions['AUD'] : 1.295134;
  $JPY = isset($usd_conversions['JPY']) ? $usd_conversions['JPY'] : 105.03116667;
  $euro_price = ccpw_currency_symbol('EUR').ccpw_format_number($coin_price * $EUR);
  $gbp_price = ccpw_currency_symbol('GBP').ccpw_format_number($coin_price * $GBP);
  $aud_price = ccpw_currency_symbol('AUD').ccpw_format_number($coin_price * $AUD);
  $jpy_price = ccpw_currency_symbol('JPY').ccpw_format_number($coin_price * $JPY);

  $usd_price = ccpw_currency_symbol('USD').ccpw_format_number($coin_price);
  $coin_html .= '<li id="' . esc_attr($coin_id) . '">';
  $coin_html .= '<div class="mtab-content">';
  $coin_html .= $coin_link_start;
  $coin_html .= '<span class="mtab_icon">' . $coin_logo_html . '</span>';

  $coin_html .= '<span class="mtab_name">' . $coin_name . '(' . $coin_symbol . ')</span>';
  $coin_html .= $coin_link_end;
  $coin_html .= '<div class="tab-price-area"><span data-aud="' . $aud_price . '" data-jpy="' . $jpy_price . '" data-gbp="' . $gbp_price . '" data-eur="' . $euro_price . '" data-usd="' . $usd_price . '" class="mtab_price">' . ccpw_currency_symbol('USD') . ccpw_format_number($coin_price) . '</span>';

  if ($display_changes) {
    $coin_html .= '<span class="mtab_ ' . $change_class . '">';
    $coin_html .= $change_sign . $percent_change_24h;
    $coin_html .= '</span>';
  }
  $coin_html .= '</div></div></li>';

} else if( $type == "list-widget"){

	/*
			List widget HTML 
   */

  $coin_html .= '<tr id="' . esc_attr($coin_id) . '">';
  $coin_html .= '<td>';
  $coin_html .= $coin_link_start;
  $coin_html .= '<div class="ccpw_icon ccpw_coin_logo">' . $coin_logo_html . '</div>';

  $coin_html .= '<div class="ccpw_coin_info">
				<span class="name">' . $coin_name . '</span>
				<span class="coin_symbol">(' . $coin_symbol . ')</span>
				</div></td><td class="price"><div class="price-value">' . $coin_price_html . '</div>
				';
  $coin_html .= $coin_link_end;
  $coin_html .='</td>';
  if ($display_changes) {
    $coin_html .= '<td><span class="changes ' . $change_class . '">';
    $coin_html .= $change_sign . $percent_change_24h;
    $coin_html .= '</span></td>';
  }
  $coin_html .= '</tr>';
}